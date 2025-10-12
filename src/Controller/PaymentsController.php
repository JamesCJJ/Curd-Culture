<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;
use Cake\Validation\Validation;
use Stripe\StripeClient;

class PaymentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    /**
     * POST /payments/checkout
     */
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

        $Carts           = $this->getTableLocator()->get('Carts');
        $CartItems       = $this->getTableLocator()->get('CartItems');
        $Products        = $this->getTableLocator()->get('Products');
        $DeliverySlots   = $this->getTableLocator()->get('DeliverySlots');
        $PickupLocations = $this->getTableLocator()->get('PickupLocations');

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

        // 商品名映射
        $pids = array_column($rows, 'product_id');
        $nameMap = [];
        foreach ($Products->find()->select(['id', 'name'])->where(['id IN' => $pids])->enableHydration(false)->all() as $p) {
            $nameMap[(int)$p['id']] = (string)$p['name'];
        }

        // 表单基础校验
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

        // 统一货币
        $currency = strtoupper((string)($rows[0]['currency'] ?? 'AUD')) ?: 'AUD';
        foreach ($rows as $r) {
            $rc = strtoupper((string)($r['currency'] ?? $currency)) ?: $currency;
            if ($rc !== $currency) {
                $this->Flash->error('Mixed currencies are not supported in a single checkout.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
            }
        }

        // 计算金额 & line items
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
                    'unit_amount'  => (int)round($price * 100),
                ],
                'quantity' => $qty,
            ];
        }

        if ($subtotal <= 0) {
            $this->Flash->error('Your cart total is invalid.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'index']);
        }

        // 履约方式 & 高级校验（delivery / pickup）
        $method = (string)($data['fulfillment_method'] ?? 'delivery');
        $method = in_array($method, ['delivery', 'pickup'], true) ? $method : 'delivery';

        $deliveryDateStr      = trim((string)($data['delivery_date']   ?? ''));
        $deliverySlotId       = (int)($data['delivery_slot_id']   ?? 0);
        $pickupLocationId     = (int)($data['pickup_location_id'] ?? 0);
        $deliveryInstructions = (string)($data['delivery_instructions'] ?? '');

        // 规则配置
        $maxDaysAhead      = (int)(Configure::read('Checkout.delivery_days_max') ?? 30);
        $allowWeekends     = (bool)(Configure::read('Checkout.delivery_allow_weekends') ?? true);
        $requireActiveSlot = (bool)(Configure::read('Checkout.require_active_slot') ?? true);

        if ($method === 'pickup') {
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
            // ===== DELIVERY 严格校验（修复 lt()/gt()）=====
            if ($deliverySlotId <= 0 || $deliveryDateStr === '') {
                $this->Flash->error('Please choose a delivery date and time slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 1) 严格 YYYY-MM-DD
            if (!$this->isValidYmd($deliveryDateStr)) {
                $this->Flash->error('Please choose a valid delivery date (YYYY-MM-DD).');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 2) 用 FrozenTime + 时间戳比较（兼容所有版本）
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

            // 3) 周末限制
            $dow = (int)$selectedStart->format('N'); // 1..7 (Mon..Sun)
            $isWeekend = ($dow >= 6);
            if (!$allowWeekends && $isWeekend) {
                $this->Flash->error('Delivery on weekends is not available. Please choose a weekday.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // 4) 时段有效性
            $slotQ = $DeliverySlots->find()->where(['id' => $deliverySlotId]);
            if ($requireActiveSlot && $DeliverySlots->getSchema()->hasColumn('active')) {
                $slotQ->andWhere(['active' => true]);
            }
            $slot = $slotQ->first();
            if (!$slot) {
                $this->Flash->error('Invalid delivery time slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }

            // weekday 字段匹配（如有）
            if (isset($slot->weekday)) {
                $slotDow = (int)$slot->weekday;
                if ($slotDow >= 1 && $slotDow <= 7 && $slotDow !== $dow) {
                    $this->Flash->error('Selected time slot does not match the chosen date.');
                    return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
                }
            }

            // 容量（如有 remaining 字段）
            if (isset($slot->remaining) && is_numeric($slot->remaining) && (int)$slot->remaining <= 0) {
                $this->Flash->error('The selected time slot is full. Please choose another slot.');
                return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
            }
        }

        // 运费
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

        // Stripe key
        $secret = (string)(Configure::read('Stripe.secret_key') ?: env('STRIPE_SECRET_KEY', ''));
        if ($secret === '') {
            $this->Flash->error('Stripe secret key is not configured.');
            return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
        }

        $stripe = new StripeClient($secret);

        $successUrl = Router::url(['controller' => 'Cart', 'action' => 'complete'], true);
        $cancelUrl  = Router::url(['controller' => 'Cart', 'action' => 'checkout'], true);

        // 安全裁剪 metadata
        $truncate = function ($v, int $max = 350): string {
            $s = trim((string)$v);
            if (mb_strlen($s) > $max) $s = mb_substr($s, 0, $max);
            return $s;
        };

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

    public function success() {}

    public function cancel()
    {
        $this->Flash->warning('Payment was cancelled.');
        return $this->redirect(['controller' => 'Cart', 'action' => 'checkout']);
    }

    /**
     * 严格校验 YYYY-MM-DD
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
