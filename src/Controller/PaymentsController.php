<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;
use Cake\Validation\Validation;
use Stripe\StripeClient;

/**
 * PaymentsController
 * Handles checkout session creation (Stripe) and simple success/cancel endpoints.
 * Notes:
 * - Totals are recomputed on the server from the cart; client values are not trusted.
 * - This controller only creates a Checkout Session; finalization should happen via webhooks.
 */
class PaymentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Use the Authentication component to require login where needed.
        $this->loadComponent('Authentication.Authentication');
    }

    /**
     * POST /payments/checkout
     * Creates a Stripe Checkout Session from the user's open cart and redirects to Stripe.
     */
    public function checkout()
    {
        $this->request->allowMethod(['post']);

        // Require a signed-in user. Preserve intended URL for redirect after login.
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

        // Tables we need for recomputing totals and validating fulfillment options.
        $Carts           = $this->getTableLocator()->get('Carts');
        $CartItems       = $this->getTableLocator()->get('CartItems');
        $Products        = $this->getTableLocator()->get('Products');
        $DeliverySlots   = $this->getTableLocator()->get('DeliverySlots');
        $PickupLocations = $this->getTableLocator()->get('PickupLocations');

        // Fetch the user's open cart (server is the source of truth).
        $cart = $Carts->find()->where(['user_id' => $userId, 'status' => 'open'])->first();
        if (!$cart) {
            $this->Flash->error('Your cart is empty.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        // Pull lightweight cart item rows (no hydration).
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

        // Map product_id → name for nicer line item titles.
        $pids = array_column($rows, 'product_id');
        $nameMap = [];
        foreach ($Products->find()->select(['id', 'name'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
            $nameMap[(int)$p['id']] = (string)$p['name'];
        }

        // Basic form validation for customer/fulfillment info.
        $data = (array)$this->request->getData();

        $required = ['full_name', 'email', 'address', 'city', 'postcode', 'country'];
        foreach ($required as $f) {
            if (trim((string)($data[$f] ?? '')) === '') {
                $this->Flash->error('Please fill all required fields.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }

        $email = (string)$data['email'];
        if (!Validation::email($email)) {
            $this->Flash->error('Please provide a valid email.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
        }

        // Enforce a single currency in the session (Stripe requires one).
        $currency = strtoupper((string)($rows[0]['currency'] ?? 'AUD')) ?: 'AUD';
        foreach ($rows as $r) {
            $rc = strtoupper((string)($r['currency'] ?? $currency)) ?: $currency;
            if ($rc !== $currency) {
                $this->Flash->error('Mixed currencies are not supported in a single checkout.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
            }
        }

        // Recompute totals and build Stripe line items. Never trust client totals.
        $subtotal  = 0.0;
        $lineItems = [];
        foreach ($rows as $it) {
            $pid   = (int)$it['product_id'];
            $name  = $nameMap[$pid] ?? ('Product #' . $pid);
            $qty   = max(0, (int)$it['qty']);
            $price = max(0.0, (float)$it['price']);

            if ($qty < 1 || $price <= 0) {
                $this->Flash->error('Invalid cart item quantity or price.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
            }

            $subtotal += $price * $qty;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => $name],
                    'unit_amount'  => (int)round($price * 100), // cents
                ],
                'quantity' => $qty,
            ];
        }

        if ($subtotal <= 0) {
            $this->Flash->error('Your cart total is invalid.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        // Fulfillment: delivery or pickup. Validate advanced fields below.
        $method = (string)($data['fulfillment_method'] ?? 'delivery');
        $method = in_array($method, ['delivery', 'pickup'], true) ? $method : 'delivery';

        $deliveryDateStr      = trim((string)($data['delivery_date']   ?? ''));
        $deliverySlotId       = (int)($data['delivery_slot_id']   ?? 0);
        $pickupLocationId     = (int)($data['pickup_location_id'] ?? 0);
        $deliveryInstructions = (string)($data['delivery_instructions'] ?? '');

        // Configurable rules for delivery validation.
        $maxDaysAhead      = (int)(Configure::read('Checkout.delivery_days_max') ?? 30);
        $allowWeekends     = (bool)(Configure::read('Checkout.delivery_allow_weekends') ?? true);
        $requireActiveSlot = (bool)(Configure::read('Checkout.require_active_slot') ?? true);

        if ($method === 'pickup') {
            // Pickup requires a valid location ID.
            if ($pickupLocationId <= 0) {
                $this->Flash->error('Please choose a pickup location.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
            $loc = $PickupLocations->find()->select(['id'])->where(['id' => $pickupLocationId])->first();
            if (!$loc) {
                $this->Flash->error('Invalid pickup location.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        } else {
            // ===== DELIVERY validation (date/slot consistency, weekend policy, capacity) =====
            if ($deliverySlotId <= 0 || $deliveryDateStr === '') {
                $this->Flash->error('Please choose a delivery date and time slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 1) Strict YYYY-MM-DD format.
            if (!$this->isValidYmd($deliveryDateStr)) {
                $this->Flash->error('Please choose a valid delivery date (YYYY-MM-DD).');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 2) Compare timestamps using FrozenTime (avoids platform quirks).
            $todayStart    = (new FrozenTime('today'))->startOfDay();
            $selectedStart = (new FrozenTime($deliveryDateStr))->startOfDay();

            if ($selectedStart->getTimestamp() < $todayStart->getTimestamp()) {
                $this->Flash->error('Delivery date cannot be in the past.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            if ($maxDaysAhead > 0) {
                $maxEnd = (clone $todayStart)->addDays($maxDaysAhead)->endOfDay();
                if ($selectedStart->getTimestamp() > $maxEnd->getTimestamp()) {
                    $this->Flash->error("Delivery date must be within {$maxDaysAhead} days from today.");
                    return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
                }
            }

            // 3) Weekend policy.
            $dow = (int)$selectedStart->format('N'); // 1..7 (Mon..Sun)
            $isWeekend = ($dow >= 6);
            if (!$allowWeekends && $isWeekend) {
                $this->Flash->error('Delivery on weekends is not available. Please choose a weekday.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 4) Slot must exist (and be active if that column is present).
            $slotQ = $DeliverySlots->find()->where(['id' => $deliverySlotId]);
            if ($requireActiveSlot && $DeliverySlots->getSchema()->hasColumn('active')) {
                $slotQ->andWhere(['active' => true]);
            }
            $slot = $slotQ->first();
            if (!$slot) {
                $this->Flash->error('Invalid delivery time slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // Optional: weekday alignment check if the slot has a weekday field.
            if (isset($slot->weekday)) {
                $slotDow = (int)$slot->weekday;
                if ($slotDow >= 1 && $slotDow <= 7 && $slotDow !== $dow) {
                    $this->Flash->error('Selected time slot does not match the chosen date.');
                    return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
                }
            }

            // Optional capacity check if the slot exposes a remaining field.
            if (isset($slot->remaining) && is_numeric($slot->remaining) && (int)$slot->remaining <= 0) {
                $this->Flash->error('The selected time slot is full. Please choose another slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }

        // Shipping fee (simple flat-rate example; pickup is free).
        $shipping = ($method === 'pickup') ? 0.0 : (($subtotal > 0) ? 12.90 : 0.0);
        if ($shipping > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount'  => (int)round($shipping * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Load Stripe secret key from config/env. Abort if missing.
        $secret = (string)(Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->error('Stripe secret key is not configured.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
        }

        $stripe = new StripeClient($secret);

        // After Stripe returns, success goes to Cart::complete; cancel returns to checkout.
        $successUrl = Router::url(['controller' => 'Cart', 'action' => 'complete'], true);
        $cancelUrl  = Router::url(['controller' => 'Cart', 'action' => 'checkout'], true);

        // Trim metadata to a safe length so we don't hit provider limits.
        $truncate = function ($v, int $max = 350): string {
            $s = trim((string)$v);
            if (mb_strlen($s) > $max) $s = mb_substr($s, 0, $max);
            return $s;
        };

        // Minimal customer and fulfillment details for later processing in webhooks.
        $metadata = [
            'user_id'               => (string)$userId,
            'cart_id'               => (string)$cart->id,
            'email'                 => $truncate($data['email'] ?? ''),
            'full_name'             => $truncate($data['full_name'] ?? ''),
            'address'               => $truncate($data['address'] ?? ''),
            'city'                  => $truncate($data['city'] ?? ''),
            'postcode'              => $truncate($data['postcode'] ?? ''),
            'country'               => $truncate($data['country'] ?? ''),
            'fulfillment_method'    => $method,
            'delivery_instructions' => $truncate($deliveryInstructions, 500),
        ];
        if ($method === 'pickup') {
            $metadata['pickup_location_id'] = (string)$pickupLocationId;
        } else {
            $metadata['delivery_date']    = $deliveryDateStr;
            $metadata['delivery_slot_id'] = (string)$deliverySlotId;
        }

        // Create the Checkout Session and redirect the user to Stripe-hosted payment page.
        $session = $stripe->checkout->sessions->create([
            'mode'           => 'payment',
            'customer_email' => $email,
            'line_items'     => $lineItems,
            'success_url'    => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => $cancelUrl,
            'metadata'       => $metadata,
        ]);

        return $this->redirect($session->url);
    }

    // Landing page after Stripe success (actual order finalization should be webhook-driven).
    public function success() {}

    // Simple cancel route to get back to checkout.
    public function cancel()
    {
        $this->Flash->warning('Payment was cancelled.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
    }

    /**
     * Validate strict YYYY-MM-DD format.
     * Returns true for valid dates (e.g., 2025-10-20), false otherwise.
     */
    private function isValidYmd(string $ymd): bool
    {
        if (!preg_match('/^(?<y>\d{4})-(?<m>\d{2})-(?<d>\d{2})$/', $ymd, $m)) {
            return false;
        }
        $y = (int)$m['y']; $mm = (int)$m['m']; $d = (int)$m['d'];
        return checkdate($mm, $d, $y);
    }
}
