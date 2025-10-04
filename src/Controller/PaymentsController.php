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

    /** POST /checkout/stripe */
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

        // Tables
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

        // Flat shipping
        $shipping = $subtotal > 0 ? 12.90 : 0.0;
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

        // Validate checkout form payload
        $data = (array)$this->request->getData();
        $required = ['full_name','email','address','city','postcode','country'];
        foreach ($required as $f) {
            if (empty(trim((string)($data[$f] ?? '')))) {
                $this->Flash->error('Please fill all required fields.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }


        $secret = (string) (Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->error('Stripe secret key is not configured.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
        }

        $stripe = new StripeClient($secret);

        $successUrl = Router::url(['controller' => 'Cart', 'action' => 'complete'], true);
        $cancelUrl  = Router::url(['controller' => 'Cart', 'action' => 'checkout'], true);

        $session = $stripe->checkout->sessions->create([
            'mode'           => 'payment',
            'customer_email' => (string)$data['email'],
            'line_items'     => $lineItems,
            'success_url'    => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => $cancelUrl,
            'metadata'       => [
                'user_id'   => (string)$userId,
                'cart_id'   => (string)$cart->id,
                'full_name' => (string)$data['full_name'],
                'address'   => (string)$data['address'],
                'city'      => (string)$data['city'],
                'postcode'  => (string)$data['postcode'],
                'country'   => (string)$data['country'],
                'subtotal'  => number_format($subtotal, 2, '.', ''),
                'shipping'  => number_format($shipping, 2, '.', ''),
                'total'     => number_format($total, 2, '.', ''),
            ],
        ]);

        return $this->redirect($session->url);
    }

    /** GET /checkout/success  */
    public function success() {}

    /** GET /checkout/cancel  */
    public function cancel()
    {
        $this->Flash->warning('Payment was cancelled.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
    }
}
