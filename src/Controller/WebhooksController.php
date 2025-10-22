<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;
// WebhooksController
// Purpose:
// - Receive Stripe callbacks and finalize orders.
// - Keep the handler short and idempotent; move heavy work (emails, PDFs) to background jobs.
//
// Security:
// - Verify signature with STRIPE_WEBHOOK_SECRET.
// - Return 2xx even on internal errors we’ve already handled to avoid retry storms.
//
// Idempotency:
// - De-duplicate by Orders.payment_ref (Stripe session id). If it exists, do nothing.
//
// Logging:
// - Log only event/session ids and order ids. Avoid PII in logs.
class WebhooksController extends AppController
{
    public $autoRender = false;

    public function initialize(): void
    {
        parent::initialize();
        $this->request->allowMethod(['post']);
    }
    /**
     * POST /webhooks/stripe
     *
     * Flow:
     * 1) Read raw body + 'Stripe-Signature' header and verify using the webhook secret.
     * 2) Switch on $event->type; only handle the types we know (here: checkout.session.completed).
     * 3) Look up the cart from metadata; compute totals from server-side data (cart items).
     * 4) In a transaction: create a PAID order, insert lines, deduct stock, close the cart.
     * 5) Always return "ok" (200) at the end to acknowledge the event.
     */
    public function stripe()
    {
        // Raw payload + signature header as required by Stripe
        $payload   = (string)$this->request->getBody()->getContents();
        $sigHeader = $this->request->getHeaderLine('Stripe-Signature');
        $secret    = (string)(Configure::read('Stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET', ''));

        if ($secret === '') {
            // Misconfiguration; better fail fast with 400 to surface the issue.
            return $this->response->withStatus(400)->withStringBody('Missing webhook secret');
        }
        // Verify request authenticity before touching any state.
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid signature');
        } catch (UnexpectedValueException $e) {
            return $this->response->withStatus(400)->withStringBody('Invalid payload');
        }
        // We only care about completed checkout sessions here.

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            // Metadata set during checkout; used to find the cart and user.

            $userId = isset($session->metadata->user_id) ? (int)$session->metadata->user_id : null;
            $cartId = isset($session->metadata->cart_id) ? (int)$session->metadata->cart_id : 0;

            if (!$cartId) {
                // Nothing we can do without the cart; acknowledge to stop retries.
                $this->log('Webhook missing cart_id for session: ' . (string)$session->id, 'error');
                return $this->response->withStringBody('ok');
            }
            // Tables we need for finalization
            $Orders     = $this->getTableLocator()->get('Orders');
            $OrderItems = $this->getTableLocator()->get('OrderItems');
            $Carts      = $this->getTableLocator()->get('Carts');
            $CartItems  = $this->getTableLocator()->get('CartItems');
            /** @var \App\Model\Table\ProductsTable $Products */
            $Products   = $this->getTableLocator()->get('Products');

            // Idempotency: if an order already exists for this session, stop here.
            if ($Orders->exists(['payment_ref' => (string)$session->id])) {
                return $this->response->withStringBody('ok');
            }
            // Snapshot the cart items now (server is the source of truth).
            $rows = $CartItems->find()
                ->select(['product_id', 'qty', 'price', 'currency'])
                ->where(['cart_id' => $cartId])
                ->enableHydration(false)
                ->all()
                ->toArray();

            if (empty($rows)) {
                // Cart might have been already processed or emptied; acknowledge.
                return $this->response->withStringBody('ok');
            }
            // Compute totals from the cart rows (avoid trusting client).
            $currency = 'AUD';
            $subtotal = 0.0;
            foreach ($rows as $it) {
                $subtotal += ((float)$it['price']) * (int)$it['qty'];
                if (!empty($it['currency'])) {
                    $currency = (string)$it['currency'];
                }
            }
            // Fulfillment data was stored as metadata during the checkout step.
            $meta = $session->metadata ?? (object)[];
            $fm   = in_array((string)($meta->fulfillment_method ?? 'delivery'), ['delivery', 'pickup'], true)
                ? (string)$meta->fulfillment_method : 'delivery';

            $deliveryDateStr  = (string)($meta->delivery_date ?? '');
            $deliverySlotId   = isset($meta->delivery_slot_id) ? (int)$meta->delivery_slot_id : 0;
            $pickupLocationId = isset($meta->pickup_location_id) ? (int)$meta->pickup_location_id : 0;
            $instructions     = (string)($meta->delivery_instructions ?? '');
            // Normalize fulfillment-specific fields and shipping.

            if ($fm === 'pickup') {
                $shipping = 0.0;
                $deliveryDateStr  = null;
                $deliverySlotId   = null;
                $pickupLocationId = $pickupLocationId > 0 ? $pickupLocationId : null;
            } else {
                $shipping = ($subtotal > 0) ? 12.90 : 0.0;
                $pickupLocationId = null;
                $deliverySlotId   = $deliverySlotId > 0 ? $deliverySlotId : null;
                $deliveryDateStr  = ($deliveryDateStr !== '') ? $deliveryDateStr : null;
            }

            $total = round($subtotal + $shipping, 2);

            // Create the paid order + lines, deduct stock, and close the cart.

            $conn = $Orders->getConnection();
            $conn->begin();
            try {
                // Order header (paid via card)
                $order = $Orders->patchEntity($Orders->newEmptyEntity(), [
                    'user_id'              => $userId,
                    'email'                => (string)($session->customer_details->email ?? $session->customer_email ?? ''),
                    'full_name'            => (string)($meta->full_name ?? ''),
                    'address'              => (string)($meta->address ?? ''),
                    'city'                 => (string)($meta->city ?? ''),
                    'postcode'             => (string)($meta->postcode ?? ''),
                    'country'              => (string)($meta->country ?? ''),
                    'currency'             => $currency,
                    'subtotal'             => round($subtotal, 2),
                    'shipping_fee'         => round($shipping, 2),
                    'discount'             => 0.0,
                    'total'                => $total,
                    'status'               => 'pending',
                    'payment_status'       => 'paid',
                    'payment_method'       => 'card',
                    'payment_ref'          => (string)$session->id,
                    'paid_at'              => FrozenTime::now(),
                    'fulfillment_method'   => $fm,
                    'delivery_date'        => $deliveryDateStr,
                    'delivery_slot_id'     => $deliverySlotId,
                    'pickup_location_id'   => $pickupLocationId,
                    'delivery_instructions'=> $instructions ?: null,
                    'notes'                => null,
                ]);
                $Orders->saveOrFail($order, ['atomic' => false]);

                $pids = array_map(fn($r) => (int)$r['product_id'], $rows);
                $prodMap = [];
                if ($pids) {
                    foreach ($Products->find()->select(['id', 'name', 'slug'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
                        $prodMap[(int)$p['id']] = ['name' => (string)$p['name'], 'slug' => $p['slug'] ?? null];
                    }
                }

                foreach ($rows as $it) {
                    $pid = (int)$it['product_id'];
                    $qty = (int)$it['qty'];

                    $Products->decrementStockOrFail($pid, $qty);

                    $OrderItems->saveOrFail($OrderItems->newEntity([
                        'order_id'   => (int)$order->id,
                        'product_id' => $pid,
                        'name'       => $prodMap[$pid]['name'] ?? ('Product #' . $pid),
                        'slug'       => $prodMap[$pid]['slug'] ?? null,
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
