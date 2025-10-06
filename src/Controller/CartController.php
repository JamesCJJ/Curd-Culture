<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Stripe\StripeClient;

class CartController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    /**
     * Find an open cart for a given user.
     */
    private function findOpenCart(int $userId)
    {
        $Carts = $this->getTableLocator()->get('Carts');
        return $Carts->find()
            ->where(['user_id' => $userId, 'status' => 'open'])
            ->first();
    }

    /** GET /cart */
    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $userId = $identity ? (int)$identity->get('id') : 0;

        $items = [];
        $currency = 'AUD';
        $subtotal = 0.0;

        if ($userId) {
            $cart = $this->findOpenCart($userId);

            if ($cart) {
                $CartItems = $this->getTableLocator()->get('CartItems');
                $Products  = $this->getTableLocator()->get('Products');

                $rows = $CartItems->find()
                    ->select(['id', 'product_id', 'qty', 'price', 'currency'])
                    ->where(['cart_id' => $cart->id])
                    ->enableHydration(false)
                    ->all()
                    ->toArray();

                if ($rows) {
                    $pids  = array_column($rows, 'product_id');
                    $map   = [];
                    $pRows = $Products->find()
                        ->select(['id', 'name', 'slug'])
                        ->where(['id IN' => $pids])
                        ->enableHydration(false)
                        ->all();

                    foreach ($pRows as $p) {
                        $map[(int)$p['id']] = $p;
                    }

                    foreach ($rows as $r) {
                        $pid  = (int)$r['product_id'];
                        $curr = $r['currency'] ?: 'AUD';

                        $item = [
                            'id'         => (int)$r['id'],
                            'product_id' => $pid,
                            'name'       => $map[$pid]['name'] ?? ('#' . $pid),
                            'slug'       => $map[$pid]['slug'] ?? '',
                            'price'      => (float)$r['price'],
                            'currency'   => $curr,
                            'qty'        => (int)$r['qty'],
                        ];
                        $items[$item['id']] = $item;
                        $currency = $curr ?: $currency;
                        $subtotal += $item['price'] * $item['qty'];
                    }
                }
            }
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        $this->set(compact('items', 'currency', 'subtotal', 'shipping', 'total'));
    }

    /** POST /cart/update */
    public function update()
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to update your cart.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = (int)$identity->get('id');
        $cart = $this->findOpenCart($userId);
        if (!$cart) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['action' => 'index']);
        }

        $qtys = (array)$this->request->getData('qty');
        $CartItems = $this->getTableLocator()->get('CartItems');
        $Products  = $this->getTableLocator()->get('Products');

        // Load current items to get product ids
        $currentItems = $CartItems->find()
            ->select(['id','product_id','qty'])
            ->where(['cart_id' => $cart->id, 'id IN' => array_keys($qtys)])
            ->enableHydration(false)
            ->all()
            ->toArray();

        $pidMap = [];
        foreach ($currentItems as $ci) {
            $pidMap[(int)$ci['id']] = (int)$ci['product_id'];
        }

        // Fetch stock for those products
        $stocks = [];
        if ($pidMap) {
            $pids = array_values(array_unique(array_values($pidMap)));
            $stocksRows = $Products->find()
                ->select(['id','stock'])
                ->where(['id IN' => $pids])
                ->enableHydration(false)
                ->all()
                ->toArray();
            foreach ($stocksRows as $sr) {
                $stocks[(int)$sr['id']] = is_null($sr['stock']) ? null : (int)$sr['stock'];
            }
        }

        $lowered = 0;
        $removed = 0;

        foreach ($qtys as $itemId => $qty) {
            $itemId = (int)$itemId;
            $qty    = max(0, (int)$qty);

            $productId = $pidMap[$itemId] ?? null;
            $stock     = is_null($productId) ? null : ($stocks[$productId] ?? null);

            if ($qty === 0) {
                $CartItems->deleteAll(['id' => $itemId, 'cart_id' => $cart->id]);
                $removed++;
                continue;
            }

            // If stock is defined, enforce it
            if ($stock !== null) {
                if ($stock <= 0) {
                    $CartItems->deleteAll(['id' => $itemId, 'cart_id' => $cart->id]);
                    $removed++;
                    continue;
                }
                if ($qty > $stock) {
                    $qty = $stock;
                    $lowered++;
                }
            }

            $CartItems->updateAll(
                ['qty' => $qty],
                ['id' => $itemId, 'cart_id' => $cart->id]
            );
        }

        if ($removed > 0) {
            $this->Flash->warning(($removed === 1 ? 'One item' : $removed . ' items') . ' were removed due to zero stock.');
        }
        if ($lowered > 0) {
            $this->Flash->warning(($lowered === 1 ? 'One item' : $lowered . ' items') . ' were adjusted to available stock.');
        }
        if ($removed === 0 && $lowered === 0) {
            $this->Flash->success('Cart updated.');
        }

        return $this->redirect(['action' => 'index']);
    }

    /** POST /cart/remove/:id */
    public function remove(int $id)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            $this->Flash->error('Please sign in to modify your cart.');
            return $this->redirect(['action' => 'index']);
        }

        $userId = (int)$identity->get('id');
        $cart = $this->findOpenCart($userId);
        if ($cart) {
            $CartItems = $this->getTableLocator()->get('CartItems');
            $CartItems->deleteAll(['id' => (int)$id, 'cart_id' => $cart->id]);
        }

        if ($this->request->is('ajax')) {
            return $this->response->withStatus(204);
        }

        $this->Flash->success('Item removed.');
        return $this->redirect(['action' => 'index']);
    }

    /**
     * GET/POST /cart/checkout
     * - GET: render checkout page with Bank Transfer details.
     * - POST (Bank Transfer): create order, **atomically deduct stock**, persist items,
     *   then close & clear cart.
     */
    public function checkout()
    {
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
        $cart   = $this->findOpenCart($userId);
        if (!$cart) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        $CartItems = $this->getTableLocator()->get('CartItems');
        $Products  = $this->getTableLocator()->get('Products');

        $rows = $CartItems->find()
            ->select(['id', 'product_id', 'qty', 'price', 'currency'])
            ->where(['cart_id' => $cart->id])
            ->enableHydration(false)
            ->all()
            ->toArray();

        $items = [];
        $currency = 'AUD';
        $subtotal = 0.0;

        if ($rows) {
            $pids  = array_column($rows, 'product_id');
            $map   = [];
            $pRows = $Products->find()
                ->select(['id', 'name', 'slug'])
                ->where(['id IN' => $pids])
                ->enableHydration(false)
                ->all();

            foreach ($pRows as $p) {
                $map[(int)$p['id']] = $p;
            }

            foreach ($rows as $r) {
                $pid  = (int)$r['product_id'];
                $curr = $r['currency'] ?: 'AUD';
                $item = [
                    'id'         => (int)$r['id'],
                    'product_id' => $pid,
                    'name'       => $map[$pid]['name'] ?? ('#' . $pid),
                    'slug'       => $map[$pid]['slug'] ?? '',
                    'price'      => (float)$r['price'],
                    'currency'   => $curr,
                    'qty'        => (int)$r['qty'],
                ];
                $items[]  = $item;
                $currency = $curr ?: $currency;
                $subtotal += $item['price'] * $item['qty'];
            }
        }

        if (empty($items)) {
            $this->Flash->info('Your cart is empty.');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        // Fake/ephemeral bank account info for demo
        $session = $this->request->getSession();
        $bank = (array)$session->read('Checkout.bank');
        if (empty($bank)) {
            $bank = [
                'account_name' => 'Curd & Culture Pty Ltd',
                'bsb'          => sprintf('%03d-%03d', random_int(100, 999), random_int(100, 999)),
                'account_no'   => (string)random_int(100000000, 999999999),
            ];
            $session->write('Checkout.bank', $bank);
        }

        if ($this->request->is('post')) {
            // BANK TRANSFER: create an "unpaid" order and deduct stock atomically.
            $data = (array)$this->request->getData();

            $required = ['full_name','email','address','city','postcode','country'];
            foreach ($required as $f) {
                if (empty(trim((string)($data[$f] ?? '')))) {
                    $this->Flash->error('Please fill all required fields.');

                    $prefill = [
                        'full_name' => (string)($identity->get('name')  ?? ''),
                        'email'     => (string)($identity->get('email') ?? ''),
                    ];
                    $bankAccountName = $bank['account_name'];
                    $bankBsb         = $bank['bsb'];
                    $bankAccountNo   = $bank['account_no'];

                    $this->set(compact(
                        'items','currency','subtotal','shipping','total','prefill',
                        'bankAccountName','bankBsb','bankAccountNo'
                    ));
                    return;
                }
            }

            $Orders     = $this->getTableLocator()->get('Orders');
            $OrderItems = $this->getTableLocator()->get('OrderItems');
            /** @var \App\Model\Table\ProductsTable $Products */
            $Products   = $this->getTableLocator()->get('Products');
            $Carts      = $this->getTableLocator()->get('Carts');

            // Transaction: create order -> decrement stock -> create items -> close & clear cart
            $conn = $Orders->getConnection();
            $conn->begin();
            try {
                // 1) Create the "unpaid" bank transfer order
                $order = $Orders->newEntity([
                    'user_id'        => $userId,
                    'email'          => (string)$data['email'],
                    'full_name'      => (string)$data['full_name'],
                    'address'        => (string)$data['address'],
                    'city'           => (string)$data['city'],
                    'postcode'       => (string)$data['postcode'],
                    'country'        => (string)$data['country'],
                    'currency'       => $currency,
                    'subtotal'       => round($subtotal, 2),
                    'shipping_fee'   => round($shipping, 2),
                    'discount'       => 0,
                    'total'          => round($total, 2),
                    'status'         => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => 'bank_transfer',
                    // Optional: if you added these columns for idempotency
                    // 'stock_deducted'    => 0,
                    // 'stock_deducted_at' => null,
                ]);
                $Orders->saveOrFail($order, ['atomic' => false]);

                // 2) Decrement stock with row locks, then persist order items
                foreach ($items as $it) {
                    $pid = (int)$it['product_id'];
                    $qty = (int)$it['qty'];

                    // Row-level lock + stock decrement (NULL stock is treated as infinite)
                    $Products->decrementStockOrFail($pid, $qty);

                    $lineTotal = (float)round(((float)$it['price']) * $qty, 2);
                    $OrderItems->saveOrFail($OrderItems->newEntity([
                        'order_id'   => $order->id,
                        'product_id' => $pid,
                        'name'       => $it['name'],
                        'slug'       => $it['slug'],
                        'qty'        => $qty,
                        'price'      => (float)$it['price'],
                        'currency'   => $it['currency'],
                        'line_total' => $lineTotal,
                    ]), ['atomic' => false]);
                }

                // 3) (Optional) mark that stock was deducted, if you created these columns
                if (property_exists($order, 'stock_deducted')) {
                    $order->set('stock_deducted', 1);
                }
                if (property_exists($order, 'stock_deducted_at')) {
                    $order->set('stock_deducted_at', FrozenTime::now());
                }
                $Orders->saveOrFail($order, ['atomic' => false]);

                // 4) Close and clear the cart
                $Carts->updateAll(['status' => 'ordered'], ['id' => $cart->id]);
                $CartItems->deleteAll(['cart_id' => $cart->id]);

                $conn->commit();
            } catch (\Throwable $e) {
                $conn->rollback();
                $this->Flash->error('Failed to place your order: ' . $e->getMessage());
                return $this->redirect(['action' => 'checkout']);
            }

            // Fire-and-forget message to Admin inbox (best-effort)
            try {
                $ContactMsgs = $this->getTableLocator()->get('ContactMessages');
                $ContactMsgs->save($ContactMsgs->newEntity([
                    'name'    => (string)$data['full_name'],
                    'email'   => (string)$data['email'],
                    'message' => 'New order #' . $order->id . ' placed via Bank Transfer. Total: ' . $currency . ' ' . number_format((float)$order->total, 2),
                ]));
            } catch (\Throwable $e) { /* ignore */ }

            $this->Flash->success('Order placed! Please transfer the payment to the bank account shown.');
            return $this->redirect(['action' => 'complete']);
        }

        // GET render
        $prefill = [
            'full_name' => (string)($identity->get('name')  ?? ''),
            'email'     => (string)($identity->get('email') ?? ''),
        ];

        $bankAccountName = $bank['account_name'];
        $bankBsb         = $bank['bsb'];
        $bankAccountNo   = $bank['account_no'];

        $this->set(compact(
            'items','currency','subtotal','shipping','total','prefill',
            'bankAccountName','bankBsb','bankAccountNo'
        ));
    }

    /**
     * GET /checkout/complete
     *
     * Handles both:
     *  - Bank Transfer "place order" (no session_id): page renders directly.
     *  - Stripe return (with ?session_id=...): if webhook hasn't fulfilled yet, we fulfill here.
     *
     * Idempotent with webhook: if an order already exists for the session, we just render.
     */
    public function complete()
    {
        $this->request->allowMethod(['get']);

        // Bank Transfer path: no session_id -> just render the page.
        $sessionId = (string)($this->request->getQuery('session_id') ?? '');
        if ($sessionId === '') {
            return; // renders templates/Cart/complete.php
        }

        // If webhook already created the order, just show the page.
        $Orders = $this->getTableLocator()->get('Orders');
        if ($Orders->exists(['payment_ref' => $sessionId])) {
            return;
        }

        // Verify the Stripe session to ensure the payment actually succeeded.
        $secret = (string)(Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->warning('Stripe secret key is not configured.');
            return;
        }

        $stripe = new StripeClient($secret);

        try {
            /** @var \Stripe\Checkout\Session $session */
            $session = $stripe->checkout->sessions->retrieve($sessionId, []);
        } catch (\Throwable $e) {
            $this->Flash->warning('Could not verify payment: ' . $e->getMessage());
            return;
        }

        if (($session->payment_status ?? '') !== 'paid') {
            // The charge may still be settling; let the user refresh later.
            $this->Flash->warning('Payment is not completed yet. If you already paid, refresh this page in a moment.');
            return;
        }

        // Pull metadata you attached when creating the Checkout Session (user_id, cart_id, etc.).
        $meta   = $session->metadata ?? (object)[];
        $userId = isset($meta->user_id) ? (int)$meta->user_id : null;
        $cartId = isset($meta->cart_id) ? (int)$meta->cart_id : null;

        if (!$cartId) {
            $this->Flash->warning('Cart information is missing; order cannot be finalized.');
            return;
        }

        $CartItems  = $this->getTableLocator()->get('CartItems');
        /** @var \App\Model\Table\ProductsTable $Products */
        $Products   = $this->getTableLocator()->get('Products');
        $OrderItems = $this->getTableLocator()->get('OrderItems');
        $Carts      = $this->getTableLocator()->get('Carts');

        // Load cart lines
        $rows = $CartItems->find()
            ->select(['product_id','qty','price','currency'])
            ->where(['cart_id' => $cartId])
            ->enableHydration(false)
            ->all()
            ->toArray();

        // If webhook already consumed the cart, just render the page.
        if (empty($rows)) {
            return;
        }

        // Build product map (for names/slugs)
        $pids = array_column($rows, 'product_id');
        $map  = [];
        if (!empty($pids)) {
            foreach ($Products->find()
                         ->select(['id','name','slug'])
                         ->where(['id IN' => $pids])
                         ->enableHydration(false)
                         ->all() as $p) {
                $map[(int)$p['id']] = $p;
            }
        }

        $currency = 'AUD';
        $subtotal = 0.0;
        $items    = [];

        foreach ($rows as $r) {
            $pid   = (int)$r['product_id'];
            $qty   = (int)$r['qty'];
            $price = (float)$r['price'];

            $currency = $r['currency'] ?: $currency;
            $name     = $map[$pid]['name'] ?? ('Product #' . $pid);
            $slug     = $map[$pid]['slug'] ?? '';

            $subtotal += $price * $qty;
            $items[] = [
                'pid'      => $pid,
                'qty'      => $qty,
                'price'    => $price,
                'currency' => $currency,
                'name'     => $name,
                'slug'     => $slug,
            ];
        }

        $shipping = isset($meta->shipping_fee) ? (float)$meta->shipping_fee : 12.90;
        $total    = round($subtotal + $shipping, 2);

        // Transaction: create order -> decrement stock -> create items -> close & clear cart.
        $conn = $Orders->getConnection();
        $conn->begin();
        try {
            $order = $Orders->newEntity([
                'user_id'        => $userId,
                'email'          => (string)($session->customer_email ?? $meta->email ?? ''),
                'full_name'      => (string)($meta->full_name ?? ''),
                'address'        => (string)($meta->address ?? ''),
                'city'           => (string)($meta->city ?? ''),
                'postcode'       => (string)($meta->postcode ?? ''),
                'country'        => (string)($meta->country ?? ''),
                'currency'       => $currency,
                'subtotal'       => round($subtotal, 2),
                'shipping_fee'   => round($shipping, 2),
                'discount'       => 0.0,
                'total'          => $total,
                'status'         => 'pending',
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'payment_ref'    => (string)$session->id,
                'paid_at'        => FrozenTime::now(),
                'notes'          => null,
            ]);
            $Orders->saveOrFail($order, ['atomic' => false]);

            // Decrement stock first, then write items
            foreach ($items as $it) {
                $Products->decrementStockOrFail((int)$it['pid'], (int)$it['qty']);

                $OrderItems->saveOrFail($OrderItems->newEntity([
                    'order_id'   => $order->id,
                    'product_id' => (int)$it['pid'],
                    'name'       => $it['name'],
                    'slug'       => $it['slug'],
                    'price'      => (float)$it['price'],
                    'qty'        => (int)$it['qty'],
                    'currency'   => $it['currency'],
                ]), ['atomic' => false]);
            }

            // Optional: mark that stock was deducted
            if (property_exists($order, 'stock_deducted')) {
                $order->set('stock_deducted', 1);
            }
            if (property_exists($order, 'stock_deducted_at')) {
                $order->set('stock_deducted_at', FrozenTime::now());
            }
            $Orders->saveOrFail($order, ['atomic' => false]);

            // Close and clear the cart
            $Carts->updateAll(['status' => 'ordered'], ['id' => $cartId]);
            $CartItems->deleteAll(['cart_id' => $cartId]);

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollback();
            $this->Flash->error('Failed to finalize your order: ' . $e->getMessage());
            return;
        }

        $this->Flash->success('Order placed! Thank you for your purchase.');
    }
}
