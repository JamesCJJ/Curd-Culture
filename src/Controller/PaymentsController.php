<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Stripe\StripeClient;

class PaymentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function checkout()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to checkout.');
            return $this->redirect([
                'controller' => 'Users',
                'action'     => 'login',
                '?'          => ['redirect' => $this->request->getRequestTarget()],
            ]);
        }

        $userId = (int)$identity->get('id');

        $Carts      = $this->getTableLocator()->get('Carts');
        $CartItems  = $this->getTableLocator()->get('CartItems');
        $Products   = $this->getTableLocator()->get('Products');

        $cart = $Carts->find()->where(['user_id' => $userId, 'status' => 'open'])->first();
        if (!$cart) {
            $this->Flash->error('Your cart is empty.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        $rows = $CartItems->find()
            ->select(['product_id', 'qty', 'price', 'currency'])
            ->where(['cart_id' => $cart->id])
            ->enableHydration(false)
            ->all()
            ->toArray();

        if (empty($rows)) {
            $this->Flash->error('Your cart is empty.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        $pids = array_column($rows, 'product_id');
        $map  = [];
        foreach ($Products->find()->select(['id','name'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
            $map[(int)$p['id']] = $p['name'];
        }

        $currency  = 'AUD';
        $subtotal  = 0.0;
        $lineItems = [];
        foreach ($rows as $it) {
            $name  = $map[(int)$it['product_id']] ?? ('Product #'.$it['product_id']);
            $qty   = (int)$it['qty'];
            $price = (float)$it['price'];
            $subtotal += $price * $qty;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => $name],
                    'unit_amount'  => (int) round($price * 100),
                ],
                'quantity' => $qty,
            ];
        }

        $data = (array)$this->request->getData();
        $required = ['full_name','email','address','city','postcode','country'];
        foreach ($required as $f) {
            if (empty(trim((string)($data[$f] ?? '')))) {
                $this->Flash->error('Please fill all required fields.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }

        $method = (string)($data['fulfillment_method'] ?? 'delivery');
        $method = in_array($method, ['delivery','pickup'], true) ? $method : 'delivery';

        $deliveryDateStr      = (string)($data['delivery_date']   ?? '');
        $deliverySlotId       = (int)($data['delivery_slot_id']   ?? 0);
        $pickupLocationId     = (int)($data['pickup_location_id'] ?? 0);
        $deliveryInstructions = (string)($data['delivery_instructions'] ?? '');

        if ($method === 'pickup') {
            if ($pickupLocationId <= 0) {
                $this->Flash->error('Please choose a pickup location.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        } else {
            if ($deliverySlotId <= 0 || $deliveryDateStr === '') {
                $this->Flash->error('Please choose a delivery date and time slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        if ($method === 'pickup') {
            $shipping = 0.0;
        }
        if ($shipping > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount'  => (int) round($shipping * 100),
                ],
                'quantity' => 1,
            ];
        }
        $total = $subtotal + $shipping;

        $secret = (string) (Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->error('Stripe secret key is not configured.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
        }

        $stripe = new StripeClient($secret);

        $successUrl = Router::url(['controller' => 'Cart', 'action' => 'complete'], true);
        $cancelUrl  = Router::url(['controller' => 'Cart', 'action' => 'checkout'], true);

        $metadata = [
            'user_id'  => (string)$userId,
            'cart_id'  => (string)$cart->id,
            'email'    => (string)$data['email'],
            'full_name'=> (string)$data['full_name'],
            'address'  => (string)$data['address'],
            'city'     => (string)$data['city'],
            'postcode' => (string)$data['postcode'],
            'country'  => (string)$data['country'],
            'fulfillment_method' => $method,
            'delivery_instructions' => $deliveryInstructions,
        ];
        if ($method === 'pickup') {
            $metadata['pickup_location_id'] = (string)$pickupLocationId;
        } else {
            $metadata['delivery_date']      = (string)$deliveryDateStr;
            $metadata['delivery_slot_id']   = (string)$deliverySlotId;
        }

        $session = $stripe->checkout->sessions->create([
            'mode'           => 'payment',
            'customer_email' => (string)$data['email'],
            'line_items'     => $lineItems,
            'success_url'    => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => $cancelUrl,
            'metadata'       => $metadata,
        ]);

        return $this->redirect($session->url);
    }

    public function success() {}

    public function cancel()
    {
        $this->Flash->warning('Payment was cancelled.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
    }
}
