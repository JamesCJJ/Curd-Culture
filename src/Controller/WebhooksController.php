<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

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
        $secret    = (string) (Configure::read('Stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET', ''));




        if ($secret === '') {
            return $this->response->withStatus(400)->withStringBody('Missing webhook secret');
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid signature');
        } catch (\UnexpectedValueException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid payload');
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;

            $userId  = isset($session->metadata->user_id) ? (int)$session->metadata->user_id : null;
            $cartId  = isset($session->metadata->cart_id) ? (int)$session->metadata->cart_id : null;

            $Orders     = $this->getTableLocator()->get('Orders');
            $OrderItems = $this->getTableLocator()->get('OrderItems');
            $Carts      = $this->getTableLocator()->get('Carts');
            $CartItems  = $this->getTableLocator()->get('CartItems');
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

                $rows = [];
            }

            $pids = array_column($rows, 'product_id');
            $map  = [];
            foreach ($Products->find()->select(['id','name'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
                $map[(int)$p['id']] = $p['name'];
            }

            $currency = 'AUD';
            $subtotal = 0.0;
            foreach ($rows as $it) {
                $subtotal += ((float)$it['price']) * (int)$it['qty'];
            }
            $shipping = $subtotal > 0 ? 12.90 : 0.0;
            $total    = $subtotal + $shipping;


            $order = $Orders->newEmptyEntity();
            $order = $Orders->patchEntity($order, [
                'user_id'        => $userId,
                'email'          => (string)($session->customer_details->email ?? $session->customer_email ?? ''),
                'full_name'      => (string)($session->metadata->full_name ?? ''),
                'address'        => (string)($session->metadata->address ?? ''),
                'city'           => (string)($session->metadata->city ?? ''),
                'postcode'       => (string)($session->metadata->postcode ?? ''),
                'country'        => (string)($session->metadata->country ?? ''),
                'subtotal'       => $subtotal,
                'shipping_fee'   => $shipping,
                'discount'       => 0.0,
                'total'          => $total,
                'status'         => 'new',
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'payment_ref'    => (string)$session->id,
                'paid_at'        => FrozenTime::now(),
                'notes'          => null,
            ]);
            $Orders->saveOrFail($order);

            foreach ($rows as $it) {
                $OrderItems->saveOrFail($OrderItems->newEntity([
                    'order_id'   => $order->id,
                    'product_id' => (int)$it['product_id'],
                    'name'       => (string)($map[(int)$it['product_id']] ?? ('Product #'.$it['product_id'])),
                    'price'      => (float)$it['price'],
                    'qty'        => (int)$it['qty'],
                    'currency'   => (string)($it['currency'] ?? 'AUD'),
                ]));
            }


            if ($cartId) {
                $cart = $Carts->get($cartId);
                $cart->status = 'closed';
                $Carts->save($cart);
            }
        }


        return $this->response->withStringBody('ok');
    }
}
