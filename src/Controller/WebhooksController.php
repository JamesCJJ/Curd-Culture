<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

class WebhooksController extends AppController
{
    public $autoRender = false;

    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post']);
    }

    /** POST /webhooks/stripe */
    public function stripe()
    {
        $payload   = (string)$this->request->getBody()->getContents();
        $sigHeader = $this->request->getHeaderLine('Stripe-Signature');
        $secret    = (string)(Configure::read('Stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET', ''));

        if ($secret === '') {
            return $this->response->withStatus(400)->withStringBody('Missing webhook secret');
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid signature');
        } catch (UnexpectedValueException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid payload');
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;

            $userId  = isset($session->metadata->user_id) ? (int)$session->metadata->user_id : null;
            $cartId  = isset($session->metadata->cart_id) ? (int)$session->metadata->cart_id : 0;

            if (!$cartId) {

                $this->log('Webhook missing cart_id for session: ' . (string)$session->id, 'error');

                return $this->response->withStringBody('ok');
            }

            $Orders     = $this->getTableLocator()->get('Orders');
            $OrderItems = $this->getTableLocator()->get('OrderItems');
            $Carts      = $this->getTableLocator()->get('Carts');
            $CartItems  = $this->getTableLocator()->get('CartItems');
            /** @var \App\Model\Table\ProductsTable $Products */
            $Products   = $this->getTableLocator()->get('Products');

            $existing = $Orders->find()->where(['payment_ref' => (string)$session->id])->first();
            if ($existing) {
                return $this->response->withStringBody('ok');
            }

            $rows = $CartItems->find()
                ->select(['product_id','qty','price','currency'])
                ->where(['cart_id' => $cartId])
                ->enableHydration(false)
                ->all()
                ->toArray();

            if (empty($rows)) {
                return $this->response->withStringBody('ok');
            }

            $currency = 'AUD';
            $subtotal = 0.0;
            foreach ($rows as $it) {
                $subtotal += ((float)$it['price']) * (int)$it['qty'];
                if (!empty($it['currency'])) {
                    $currency = (string)$it['currency'];
                }
            }
            $shipping = $subtotal > 0 ? 12.90 : 0.0;
            $total    = $subtotal + $shipping;

            $conn = $Orders->getConnection();
            $conn->begin();
            try {
                $order = $Orders->newEmptyEntity();
                $order = $Orders->patchEntity($order, [
                    'user_id'        => $userId,
                    'email'          => (string)($session->customer_details->email ?? $session->customer_email ?? ''),
                    'full_name'      => (string)($session->metadata->full_name ?? ''),
                    'address'        => (string)($session->metadata->address ?? ''),
                    'city'           => (string)($session->metadata->city ?? ''),
                    'postcode'       => (string)($session->metadata->postcode ?? ''),
                    'country'        => (string)($session->metadata->country ?? ''),
                    'currency'       => $currency,
                    'subtotal'       => round($subtotal, 2),
                    'shipping_fee'   => round($shipping, 2),
                    'discount'       => 0.0,
                    'total'          => round($total, 2),
                    'status'         => 'pending',
                    'payment_status' => 'paid',
                    'payment_method' => 'card',
                    'payment_ref'    => (string)$session->id,
                    'paid_at'        => FrozenTime::now(),
                    'notes'          => null,
                ]);
                $Orders->saveOrFail($order, ['atomic' => false]);

// before foreach saving items
                $pids = array_map(fn($r) => (int)$r['product_id'], $rows);
                $prodMap = [];
                if ($pids) {
                    $rowsP = $Products->find()
                        ->select(['id','name'])
                        ->where(['id IN' => $pids])
                        ->enableHydration(false)
                        ->all()
                        ->toArray();
                    foreach ($rowsP as $p) {
                        $prodMap[(int)$p['id']] = (string)$p['name'];
                    }
                }

                foreach ($rows as $it) {
                    $pid = (int)$it['product_id'];
                    $qty = (int)$it['qty'];

                    $Products->decrementStockOrFail($pid, $qty);

                    $OrderItems->saveOrFail($OrderItems->newEntity([
                        'order_id'   => $order->id,
                        'product_id' => $pid,
                        'name'       => $prodMap[$pid] ?? ('Product #'.$pid),
                        'price'      => (float)$it['price'],
                        'qty'        => $qty,
                        'currency'   => (string)($it['currency'] ?? $currency),
                    ]), ['atomic' => false]);
                }


                $Carts->updateAll(['status' => 'ordered'], ['id' => $cartId]);
                $CartItems->deleteAll(['cart_id' => $cartId]);

                $conn->commit();
            } catch (Throwable $e) {
                $conn->rollback();
                $this->log('Webhook fulfillment error (session ' . $session->id . '): ' . $e->getMessage(), 'error');

                return $this->response->withStringBody('ok');
            }
        }

        return $this->response->withStringBody('ok');
    }
}
