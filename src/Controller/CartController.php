<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Stripe\StripeClient;

/**
 * CartController
 * Shows/updates the shopping cart, collects checkout details,
 * and creates orders (bank transfer flow) or finalizes card orders after Stripe.
 */
class CartController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Auth + flash messages for user feedback
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Flash');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        // Allow guests to view the cart page; updates/checkout require login
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    // Helper: fetch an open cart for the given user (or null)
    private function findOpenCart(int $userId)
    {
        $Carts = $this->getTableLocator()->get('Carts');
        return $Carts->find()->where(['user_id' => $userId, 'status' => 'open'])->first();
    }

    // Helper: active delivery slots for the checkout form
    private function getActiveDeliverySlots(): array
    {
        $DeliverySlots = $this->getTableLocator()->get('DeliverySlots');
        return $DeliverySlots->find()
            ->where(['is_active' => 1])
            ->orderAsc('window_start')
            ->enableHydration(false)
            ->all()
            ->toArray();
    }

    // Helper: active pickup locations for the checkout form
    private function getActivePickupLocations(): array
    {
        $PickupLocations = $this->getTableLocator()->get('PickupLocations');
        return $PickupLocations->find()
            ->where(['is_active' => 1])
            ->orderAsc('name')
            ->enableHydration(false)
            ->all()
            ->toArray();
    }

    /**
     * GET /cart
     * Renders the cart contents (guest-friendly).
     */
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

                // Use a lightweight query and build a denormalized view model
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

        // Flat shipping for display; real payment amounts are recomputed server-side later
        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        $this->set(compact('items', 'currency', 'subtotal', 'shipping', 'total'));
    }

    /**
     * POST /cart/update
     * Updates quantities and removes items (stock-aware).
     */
    public function update()
    {
        $this->request->allowMethod(['post']);

        // Must be signed in to mutate the cart
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

        // Fetch current rows being updated to map item → product → stock
        $currentItems = $CartItems->find()
            ->select(['id', 'product_id', 'qty'])
            ->where(['cart_id' => $cart->id, 'id IN' => array_keys($qtys)])
            ->enableHydration(false)
            ->all()
            ->toArray();

        $pidMap = [];
        foreach ($currentItems as $ci) {
            $pidMap[(int)$ci['id']] = (int)$ci['product_id'];
        }

        // Pull stock levels for affected products only (null means unlimited)
        $stocks = [];
        if ($pidMap) {
            $pids = array_values(array_unique(array_values($pidMap)));
            $stocksRows = $Products->find()
                ->select(['id', 'stock'])
                ->where(['id IN' => $pids])
                ->enableHydration(false)
                ->all()
                ->toArray();
            foreach ($stocksRows as $sr) {
                $stocks[(int)$sr['id']] = is_null($sr['stock']) ? null : (int)$sr['stock'];
            }
        }

        // Track messages for user feedback
        $lowered = 0;
        $removed = 0;

        foreach ($qtys as $itemId => $qty) {
            $itemId = (int)$itemId;
            $qty    = max(0, (int)$qty);

            $productId = $pidMap[$itemId] ?? null;
            $stock     = is_null($productId) ? null : ($stocks[$productId] ?? null);

            if ($qty === 0) {
                // Explicit remove
                $CartItems->deleteAll(['id' => $itemId, 'cart_id' => $cart->id]);
                $removed++;
                continue;
            }

            if ($stock !== null) {
                if ($stock <= 0) {
                    // No stock: remove silently but inform the user
                    $CartItems->deleteAll(['id' => $itemId, 'cart_id' => $cart->id]);
                    $removed++;
                    continue;
                }
                if ($qty > $stock) {
                    // Clamp to available stock
                    $qty = $stock;
                    $lowered++;
                }
            }

            // Persist the new qty
            $CartItems->updateAll(['qty' => $qty], ['id' => $itemId, 'cart_id' => $cart->id]);
        }

        // Summarize what happened
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

    /**
     * POST /cart/remove/:id
     * Removes a single line (supports AJAX 204).
     */
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
            // Frontend expects an empty success
            return $this->response->withStatus(204);
        }

        $this->Flash->success('Item removed.');
        return $this->redirect(['action' => 'index']);
    }

    /**
     * GET/POST /checkout
     * Shows the checkout form and, on submit, creates a pending bank-transfer order.
     * (Card payments are handled by PaymentsController + webhooks.)
     */
    public function checkout()
    {
        // Must be signed in to proceed
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

        // Build a simple items array for the template and later order creation
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

        // Flat shipping for this flow (bank transfer)
        $shipping = $subtotal > 0 ? 12.90 : 0.0;
        $total    = $subtotal + $shipping;

        // Options for the UI
        $deliverySlots   = $this->getActiveDeliverySlots();
        $pickupLocations = $this->getActivePickupLocations();

        // Fake bank details stored in session (used by the confirmation screen)
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

        // Handle form POST: validate and create a pending order with stock deduction
        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();

            // Simple required field check (more checks happen in PaymentsController when card)
            $required = ['full_name', 'email', 'address', 'city', 'postcode', 'country'];
            foreach ($required as $f) {
                if (empty(trim((string)($data[$f] ?? '')))) {
                    $this->Flash->error('Please fill all required fields.');

                    // Re-render with minimal prefill and options
                    $prefill = [
                        'full_name' => (string)($identity->get('name')  ?? ''),
                        'email'     => (string)($identity->get('email') ?? ''),
                    ];
                    $bankAccountName = $bank['account_name'];
                    $bankBsb         = $bank['bsb'];
                    $bankAccountNo   = $bank['account_no'];

                    $this->set(compact(
                        'items', 'currency', 'subtotal', 'shipping', 'total', 'prefill',
                        'bankAccountName', 'bankBsb', 'bankAccountNo',
                        'deliverySlots', 'pickupLocations'
                    ));
                    return;
                }
            }

            // Delivery vs pickup
            $fulfillmentMethod = (string)($data['fulfillment_method'] ?? 'delivery');
            $fulfillmentMethod = in_array($fulfillmentMethod, ['delivery', 'pickup'], true) ? $fulfillmentMethod : 'delivery';

            $deliveryDateStr      = (string)($data['delivery_date']   ?? '');
            $deliverySlotId       = (int)($data['delivery_slot_id']   ?? 0);
            $pickupLocationId     = (int)($data['pickup_location_id'] ?? 0);
            $deliveryInstructions = (string)($data['delivery_instructions'] ?? '');

            if ($fulfillmentMethod === 'pickup') {
                // Pickup: require a valid location; shipping becomes 0
                if ($pickupLocationId <= 0) {
                    $this->Flash->error('Please choose a pickup location.');
                    return $this->redirect(['action' => 'checkout']);
                }
                $shipping = 0.0;
                $total    = $subtotal + $shipping;
            } else {
                // Delivery: require date + slot, not in the past, slot must be active and within capacity
                if (empty($deliveryDateStr) || $deliverySlotId <= 0) {
                    $this->Flash->error('Please choose a delivery date and time slot.');
                    return $this->redirect(['action' => 'checkout']);
                }
                $today = new \DateTimeImmutable('today');
                try {
                    $chosen = new \DateTimeImmutable($deliveryDateStr);
                } catch (\Throwable $e) {
                    $this->Flash->error('Invalid delivery date.');
                    return $this->redirect(['action' => 'checkout']);
                }
                if ($chosen < $today) {
                    $this->Flash->error('Delivery date cannot be in the past.');
                    return $this->redirect(['action' => 'checkout']);
                }

                $DeliverySlots = $this->getTableLocator()->get('DeliverySlots');
                $slot = $DeliverySlots->find()
                    ->where(['id' => $deliverySlotId, 'is_active' => 1])
                    ->enableHydration(false)
                    ->first();
                if (!$slot) {
                    $this->Flash->error('Selected delivery slot is not available.');
                    return $this->redirect(['action' => 'checkout']);
                }
                if (!empty($slot['capacity'])) {
                    // Count pending-ish orders for that slot/date to enforce capacity
                    $Orders = $this->getTableLocator()->get('Orders');
                    $used = $Orders->find()
                        ->where([
                            'delivery_date'    => $deliveryDateStr,
                            'delivery_slot_id' => $deliverySlotId,
                            'status IN'        => ['pending', 'confirmed', 'processing', 'new'],
                        ])
                        ->count();
                    if ($used >= (int)$slot['capacity']) {
                        $this->Flash->error('This delivery slot is full. Please choose another.');
                        return $this->redirect(['action' => 'checkout']);
                    }
                }
            }

            // Create a pending order (bank transfer). Stock is deducted immediately.
            $Orders     = $this->getTableLocator()->get('Orders');
            $OrderItems = $this->getTableLocator()->get('OrderItems');
            /** @var \App\Model\Table\ProductsTable $Products */
            $Products   = $this->getTableLocator()->get('Products');
            $Carts      = $this->getTableLocator()->get('Carts');

            $conn = $Orders->getConnection();
            $conn->begin();
            try {
                // Header row
                $order = $Orders->newEntity([
                    'user_id'              => $userId,
                    'email'                => (string)$data['email'],
                    'full_name'            => (string)$data['full_name'],
                    'address'              => (string)$data['address'],
                    'city'                 => (string)$data['city'],
                    'postcode'             => (string)$data['postcode'],
                    'country'              => (string)$data['country'],
                    'currency'             => $currency,
                    'subtotal'             => round($subtotal, 2),
                    'shipping_fee'         => round($shipping, 2),
                    'discount'             => 0,
                    'total'                => round($total, 2),
                    'status'               => 'pending',
                    'payment_status'       => 'unpaid',
                    'payment_method'       => 'bank_transfer',
                    'fulfillment_method'   => $fulfillmentMethod,
                    'delivery_date'        => $fulfillmentMethod === 'delivery' ? $deliveryDateStr : null,
                    'delivery_slot_id'     => $fulfillmentMethod === 'delivery' ? $deliverySlotId : null,
                    'pickup_location_id'   => $fulfillmentMethod === 'pickup'   ? $pickupLocationId : null,
                    'delivery_instructions'=> $deliveryInstructions ?: null,
                ]);
                $Orders->saveOrFail($order, ['atomic' => false]);

                // Lines + stock deduction (uses ProductsTable::decrementStockOrFail)
                foreach ($items as $it) {
                    $pid = (int)$it['product_id'];
                    $qty = (int)$it['qty'];

                    $Products->decrementStockOrFail($pid, $qty);

                    $lineTotal = (float)round(((float)$it['price']) * $qty, 2);
                    $OrderItems->saveOrFail($OrderItems->newEntity([
                        'order_id'   => (int)$order->id,
                        'product_id' => $pid,
                        'name'       => $it['name'],
                        'slug'       => $it['slug'],
                        'qty'        => $qty,
                        'price'      => (float)$it['price'],
                        'currency'   => $it['currency'],
                        'line_total' => $lineTotal,
                    ]), ['atomic' => false]);
                }

                // Optional bookkeeping flags if those columns exist
                if (property_exists($order, 'stock_deducted')) {
                    $order->set('stock_deducted', 1);
                }
                if (property_exists($order, 'stock_deducted_at')) {
                    $order->set('stock_deducted_at', FrozenTime::now());
                }
                $Orders->saveOrFail($order, ['atomic' => false]);

                // Close the cart and clear its items
                $Carts->updateAll(['status' => 'ordered'], ['id' => $cart->id]);
                $CartItems->deleteAll(['cart_id' => $cart->id]);

                $conn->commit();
            } catch (\Throwable $e) {
                $conn->rollback();
                $this->Flash->error('Failed to place your order: ' . $e->getMessage());
                return $this->redirect(['action' => 'checkout']);
            }

            // Fire-and-forget lightweight notification (best effort)
            try {
                $ContactMsgs = $this->getTableLocator()->get('ContactMessages');
                $ContactMsgs->save($ContactMsgs->newEntity([
                    'name'    => (string)$data['full_name'],
                    'email'   => (string)$data['email'],
                    'message' => 'New order #' . $order->id .
                        ' (' . strtoupper($order->fulfillment_method) . ') ' .
                        'Total: ' . $currency . ' ' . number_format((float)$order->total, 2),
                ]));
            } catch (\Throwable $e) {}

            $this->Flash->success(
                $fulfillmentMethod === 'pickup'
                    ? 'Order placed! Please come to the store to collect your items.'
                    : 'Order placed! We will deliver within your selected time slot.'
            );
            return $this->redirect(['action' => 'complete']);
        }

        // First render (GET): prefill name/email from identity; try to pull default address
        $prefill = [
            'full_name' => (string)($identity->get('name')  ?? ''),
            'email'     => (string)($identity->get('email') ?? ''),
        ];
        $defaultAddress = null;
        try {
            $Addresses = $this->getTableLocator()->get('Addresses');

            // Prefer default shipping address; fall back to default billing
            $da = $Addresses->find()
                ->where(['user_id' => $userId, 'is_default' => 1, 'type' => 'shipping'])
                ->first();

            if (!$da) {
                $da = $Addresses->find()
                    ->where(['user_id' => $userId, 'is_default' => 1, 'type' => 'billing'])
                    ->first();
            }

            if ($da) {
                $defaultAddress = [
                    'id'        => (int)$da->id,
                    'full_name' => trim((string)($da->first_name ?? '') . ' ' . (string)($da->last_name ?? '')),
                    'address'   => trim((string)$da->address_line_1 . (empty($da->address_line_2) ? '' : ', ' . $da->address_line_2)),
                    'city'      => (string)($da->suburb ?? ''),
                    'postcode'  => (string)($da->postcode ?? ''),
                    'country'   => (string)($da->country ?? 'Australia'),
                    'summary'   => trim(
                        (string)$da->address_line_1 . ', ' .
                        (string)($da->suburb ?? '') . ' ' .
                        (string)($da->state ?? '')  . ' ' .
                        (string)($da->postcode ?? '')
                    ),
                ];
            }
        } catch (\Throwable $e) {
            $defaultAddress = null;
        }

        // Expose bank details & options to the view
        $bankAccountName = $bank['account_name'];
        $bankBsb         = $bank['bsb'];
        $bankAccountNo   = $bank['account_no'];

        $this->set(compact(
            'items', 'currency', 'subtotal', 'shipping', 'total', 'prefill',
            'bankAccountName', 'bankBsb', 'bankAccountNo',
            'deliverySlots', 'pickupLocations',
            'defaultAddress'
        ));
    }

    /**
     * GET /checkout/complete?session_id=...
     * For Stripe card payments: verify the session and create a paid order, then clean up the cart.
     * (If session_id is missing or not paid yet, just return with a message.)
     */
    public function complete()
    {
        $this->request->allowMethod(['get']);

        $sessionId = (string)($this->request->getQuery('session_id') ?? '');
        if ($sessionId === '') {
            // Bank-transfer flow lands here without a session_id; nothing to do
            return;
        }

        // Idempotency: if we've already linked this session to an order, stop
        $Orders = $this->getTableLocator()->get('Orders');
        if ($Orders->exists(['payment_ref' => $sessionId])) {
            return;
        }

        // Need Stripe secret to query the session
        $secret = (string)(Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->warning('Stripe secret key is not configured.');
            return;
        }

        $stripe = new StripeClient($secret);

        // Retrieve and validate the checkout session
        try {
            /** @var \Stripe\Checkout\Session $session */
            $session = $stripe->checkout->sessions->retrieve($sessionId, []);
        } catch (\Throwable $e) {
            $this->Flash->warning('Could not verify payment: ' . $e->getMessage());
            return;
        }

        if (($session->payment_status ?? '') !== 'paid') {
            $this->Flash->warning('Payment is not completed yet. If you already paid, refresh this page in a moment.');
            return;
        }

        // Pull metadata we stored during checkout (user/cart/fulfillment)
        $meta   = $session->metadata ?? (object)[];
        $userId = isset($meta->user_id) ? (int)$meta->user_id : null;
        $cartId = isset($meta->cart_id) ? (int)$meta->cart_id : null;

        $fm               = in_array((string)($meta->fulfillment_method ?? 'delivery'), ['delivery', 'pickup'], true) ? (string)$meta->fulfillment_method : 'delivery';
        $deliveryDateStr  = (string)($meta->delivery_date ?? '');
        $deliverySlotId   = isset($meta->delivery_slot_id) ? (int)$meta->delivery_slot_id : 0;
        $pickupLocationId = isset($meta->pickup_location_id) ? (int)$meta->pickup_location_id : 0;
        $instructions     = (string)($meta->delivery_instructions ?? '');

        if (!$cartId) {
            $this->Flash->warning('Cart information is missing; order cannot be finalized.');
            return;
        }

        // Rebuild items from the cart snapshot (server-side source of truth)
        $CartItems  = $this->getTableLocator()->get('CartItems');
        /** @var \App\Model\Table\ProductsTable $Products */
        $Products   = $this->getTableLocator()->get('Products');
        $OrderItems = $this->getTableLocator()->get('OrderItems');
        $Carts      = $this->getTableLocator()->get('Carts');

        $rows = $CartItems->find()
            ->select(['product_id', 'qty', 'price', 'currency'])
            ->where(['cart_id' => $cartId])
            ->enableHydration(false)
            ->all()
            ->toArray();

        if (empty($rows)) {
            // Nothing to finalize (cart deleted or already processed)
            return;
        }

        // Compute totals again (avoid trusting client)
        $currency = 'AUD';
        $subtotal = 0.0;
        $items    = [];
        foreach ($rows as $r) {
            $pid   = (int)$r['product_id'];
            $qty   = (int)$r['qty'];
            $price = (float)$r['price'];
            $currency = $r['currency'] ?: $currency;
            $subtotal += $price * $qty;
            $items[] = ['pid' => $pid, 'qty' => $qty, 'price' => $price, 'currency' => $currency];
        }

        // Normalize fulfillment-specific fields and shipping
        if ($fm === 'pickup') {
            $shipping = 0.0;
            $deliverySlotId   = null;
            $deliveryDateStr  = null;
            $pickupLocationId = $pickupLocationId > 0 ? $pickupLocationId : null;
        } else {
            $shipping = 12.90;
            $pickupLocationId = null;
            $deliverySlotId   = $deliverySlotId > 0 ? $deliverySlotId : null;
            $deliveryDateStr  = ($deliveryDateStr !== '') ? $deliveryDateStr : null;
        }

        $total = round($subtotal + $shipping, 2);

        // Create a PAID order from the session (idempotent via payment_ref)
        $conn = $Orders->getConnection();
        $conn->begin();
        try {
            $order = $Orders->newEntity([
                'user_id'              => $userId,
                'email'                => (string)($session->customer_email ?? $meta->email ?? ''),
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

            // Fetch product names/slugs in bulk for nicer order lines
            $pids = array_map(fn($i) => (int)$i['pid'], $items);
            $prodMap = [];
            if ($pids) {
                foreach ($Products->find()->select(['id', 'name', 'slug'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
                    $prodMap[(int)$p['id']] = ['name' => (string)$p['name'], 'slug' => $p['slug'] ?? null];
                }
            }

            foreach ($items as $it) {
                // Deduct stock now (assumes webhooks didn't already do it for this flow)
                $Products->decrementStockOrFail((int)$it['pid'], (int)$it['qty']);

                $pid  = (int)$it['pid'];
                $name = $prodMap[$pid]['name'] ?? ('Product #' . $pid);
                $slug = $prodMap[$pid]['slug'] ?? null;

                $OrderItems->saveOrFail($OrderItems->newEntity([
                    'order_id'   => (int)$order->id,
                    'product_id' => $pid,
                    'name'       => $name,
                    'slug'       => $slug,
                    'price'      => (float)$it['price'],
                    'qty'        => (int)$it['qty'],
                    'currency'   => $it['currency'],
                ]), ['atomic' => false]);
            }

            // Optional bookkeeping flags
            if (property_exists($order, 'stock_deducted')) {
                $order->set('stock_deducted', 1);
            }
            if (property_exists($order, 'stock_deducted_at')) {
                $order->set('stock_deducted_at', FrozenTime::now());
            }
            $Orders->saveOrFail($order, ['atomic' => false]);

            // Close the cart and clear items
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
