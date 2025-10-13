<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AIService;
use Authentication\IdentityInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * CopilotController
 *
 * Chat-like endpoint that:
 *  - Answers common questions about products, delivery, payments, etc.
 *  - Optionally calls an AI provider (Gemini/OpenAI) via AIService; if it fails,
 *    falls back to simple rule-based responses (never 500 to the user).
 *  - Always returns JSON; never renders a template.
 *
 * Endpoint:
 *  POST /copilot/talk
 *  Body: { message: string } (supports application/json or x-www-form-urlencoded)
 */
class CopilotController extends AppController
{
    /** @var \App\Service\AIService|null */
    private ?AIService $aiService = null;

    /** @var \App\Model\Table\ProductsTable */
    protected $Products;
    /** @var \App\Model\Table\OrdersTable */
    protected $Orders;
    /** @var \App\Model\Table\OrderItemsTable */
    protected $OrderItems;

    public function initialize(): void
    {
        parent::initialize();

        // Obtain tables explicitly (avoid loadModel coupling)
        $locator = TableRegistry::getTableLocator();
        $this->Products   = $locator->get('Products');
        $this->Orders     = $locator->get('Orders');
        $this->OrderItems = $locator->get('OrderItems');

        // Initialize AI service (reads config 'AI' in app_local.php)
        $this->aiService = new AIService();

        // If FormProtection is enabled globally, disable it here:
        // this endpoint accepts JSON and we send CSRF via header from the UI.
        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }

        // We always return JSON manually
        $this->viewBuilder()->setClassName('Json');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated POST /copilot/talk
        if (isset($this->Authentication)) {
            $this->Authentication->allowUnauthenticated(['talk']);
        }
        // If Authorization plugin is enabled, skip policy checks
        if (isset($this->Authorization)) {
            $this->Authorization->skipAuthorization();
        }
    }

    /**
     * POST /copilot/talk
     * Accepts JSON or x-www-form-urlencoded. Returns JSON.
     */
    public function talk(): Response
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        try {
            // Support both JSON and form submissions
            $isJson = stripos((string)$this->request->getHeaderLine('Content-Type'), 'application/json') !== false;
            $input  = $isJson
                ? (array)json_decode((string)$this->request->getBody(), true)
                : (array)$this->request->getData();

            $message = trim((string)($input['message'] ?? ''));
            if ($message === '') {
                return $this->json([
                    'ok'    => true,
                    'reply' => "Hi! I can help with products, delivery, payments, and orders. Ask me anything!"
                ]);
            }

            $identity = $this->request->getAttribute('identity');

            // 1) Try AI first (if enabled)
            if ($this->aiService && $this->aiService->isEnabled()) {
                try {
                    return $this->handleWithAI($message, $identity);
                } catch (\Throwable $e) {
                    // Log and continue with rules; don't 500 the user
                    Log::warning('[copilot.ai] falling back to rules: ' . $e->getMessage());
                }
            }

            // 2) Fallback: rule-based intents
            return $this->handleWithRules($message, $identity);

        } catch (\Throwable $e) {
            Log::error('[copilot.talk] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response
                ->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode(['ok' => false, 'error' => 'server_error']));
        }
    }

    /**
     * Extract user id from any kind of identity (Authentication\Identity, Entity, array).
     */
    private function userId($identity): ?int
    {
        if (!$identity) return null;

        if ($identity instanceof IdentityInterface) {
            $v = $identity->get('id');
            return $v !== null && $v !== '' ? (int)$v : null;
        }
        if ($identity instanceof EntityInterface) {
            $v = $identity->get('id');
            return $v !== null && $v !== '' ? (int)$v : null;
        }
        if (is_array($identity)) {
            return isset($identity['id']) ? (int)$identity['id'] : null;
        }
        return null;
    }

    /**
     * Check admin role from any kind of identity (Authentication\Identity, Entity, array).
     */
    private function isAdmin($identity): bool
    {
        $role = null;
        if ($identity instanceof IdentityInterface) {
            $role = $identity->get('role');
        } elseif ($identity instanceof EntityInterface) {
            $role = $identity->get('role');
        } elseif (is_array($identity)) {
            $role = $identity['role'] ?? null;
        }
        return strtolower((string)$role) === 'admin';
    }

    /**
     * Handle message by calling AI provider and optionally enrich with local data.
     */
    private function handleWithAI(string $message, $identity): Response
    {
        $payload = [];
        $context = [];

        if (($uid = $this->userId($identity)) !== null) {
            $context['user_id'] = $uid;
            try {
                $recentOrders = $this->recentOrders($identity, 3);
                if ($recentOrders) {
                    $context['recent_orders'] = $recentOrders;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        try {
            $products = $this->Products->find()
                ->select(['name'])
                ->orderAsc('name')
                ->limit(10)
                ->all()
                ->extract('name')
                ->toArray();
            if ($products) {
                $context['available_products'] = $products;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Call AI service
        $aiResponse = $this->aiService->chat($message, $context);
        if (!($aiResponse['success'] ?? false)) {
            // Fallback to rules if AI failed
            return $this->handleWithRules($message, $identity);
        }

        $reply = (string)($aiResponse['message'] ?? '');

        // Optional: enrich with local order data if the user referenced an order id
        if (preg_match('/order\s*#?\s*(\d{1,8})/i', $message, $m)) {
            $orderId   = (int)$m[1];
            $orderData = $this->lookupOrder($orderId, $identity);
            if ($orderData) {
                $payload['order'] = $orderData;
            }
        }

        // Optional: list recent orders if the user asked
        if (preg_match('/\b(my|recent)\s*(orders?|purchases?)\b/i', $message)) {
            $list = $this->recentOrders($identity, 3);
            if ($list) {
                $payload['orders'] = $list;
            }
        }

<<<<<<< Updated upstream
        // Check for general product/cheese listing questions
        if (preg_match('/(cheese|cheeses|dairy|product|products|what.*(have|sell|offer|carry|stock))/i', $message) &&
            !preg_match('/order/i', $message)) { // Don't confuse with order questions
            // Return a list of products as buttons
            $results = [];
            try {
                $results = $this->Products->find()
                    ->select(['id', 'name', 'slug', 'price', 'currency', 'image_url'])
                    ->orderAsc('name')
                    ->limit(10)
                    ->all();
                
                foreach ($results as $p) {
                    $payload['products'][] = [
                        'id' => (int)$p->get('id'),
                        'name' => (string)$p->get('name'),
                        'slug' => (string)$p->get('slug'),
                        'price' => (float)$p->get('price'),
                        'price_fmt' => $this->formatCurrency((float)$p->get('price'), (string)($p->get('currency') ?: 'AUD')),
                        'image' => $p->get('image_url'),
                        'url' => ((string)($this->request->getAttribute('webroot') ?? '/')) . 'products/view/' . rawurlencode((string)$p->get('slug')),
                    ];
                }
            } catch (\Throwable $e) {
                // Ignore DB errors
            }
        }
        
        // Check if message is searching for specific products
        $productSearchTerm = null;

        if (preg_match('/(search|find|looking for|want|need)\s+(.+)/i', $message, $m)) {
            $productSearchTerm = trim((string)($m[2] ?? ''));
            $productSearchTerm = preg_replace('/\b(cheese|product|some|any|a)\b/i', '', $productSearchTerm);
            $productSearchTerm = trim($productSearchTerm);
        }
        // Or simple product name queries (short messages without question words)
        elseif (strlen($message) < 50 && !preg_match('/\b(what|how|when|where|why|can|do|does|is|are|my|order|cheese|product)\b/i', $message)) {
            $productSearchTerm = trim($message);
        }
        
        if ($productSearchTerm && $productSearchTerm !== '' && empty($payload['products'])) {
            $results = $this->searchProducts($productSearchTerm, 6);
            if ($results) {
                $payload['products'] = $results;
                if (count($results) === 1) {
                    $webroot             = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                }
            }
        }

        return $this->json([
            'ok'         => true,
            'reply'      => $reply !== '' ? $reply : "Got it.",
            'data'       => $payload,
            'ai_powered' => true,
        ]);
    }

    /**
     * Rule-based responses (no external calls, always safe).
     */
    private function handleWithRules(string $message, $identity): Response
    {
        $payload = [];

        // Delivery / shipping / pickup
        if (preg_match('/(deliver|delivery|shipping|ship|arrive|arrival|pickup|pick.?up|how long)/i', $message)) {
            return $this->json([
                'ok'    => true,
                'reply' => 'We offer scheduled delivery slots and in-store pickup. You can choose your preferred option and see the final cost during checkout. For an existing order, please check your dashboard.'
            ]);
        }

        // Ingredients / dietary / allergy
        if (preg_match('/(gluten|ingredient|pasteuri|vegan|vegetarian|allergy|allergic|dietary|lactose)/i', $message)) {
            return $this->json([
                'ok'    => true,
                'reply' => "We list ingredients and dietary information on each product's page. Search the specific cheese to see full details. For severe allergy concerns, please contact us directly."
            ]);
        }

        // Payment methods
        if (preg_match('/(pay|payment|card|credit|debit|amex|visa|mastercard|accept.*card)/i', $message)) {
            return $this->json([
                'ok'    => true,
                'reply' => 'We accept major credit cards (Visa, Mastercard, Amex) via Stripe.'
            ]);
        }

        // Contact / support
        if (preg_match('/(contact|support|help|email|phone|reach|cancel|change.*order|modify.*order)/i', $message)) {
            return $this->json([
                'ok'    => true,
                'reply' => "For help with your order or other questions, please use our 'Contact Us' page."
            ]);
        }

        // General inventory
        if (preg_match('/(cheese|cheeses|dairy|product|products|what.*(have|sell|offer|carry|stock))/i', $message)) {
            $names = [];
            try {
                $rows = $this->Products->find()
                    ->select(['name', 'slug'])
                    ->orderAsc('name')
                    ->limit(6)
                    ->all();
                foreach ($rows as $r) {
                    $names[] = (string)$r->get('name');
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $reply = $names
                ? 'We offer artisan cheeses including: ' . implode(', ', $names) . ', and more.'
                : 'We carry artisan cheeses including cheddar, brie, gouda, and blue. Ask about a specific cheese!';
            return $this->json(['ok' => true, 'reply' => $reply]);
        }

        // "order 1234" pattern
        if (preg_match('/order\s*#?\s*(\d{1,8})/i', $message, $m)) {
            $orderId = (int)$m[1];
            $data    = null;
            try {
                $data = $this->lookupOrder($orderId, $identity);
            } catch (\Throwable $e) {
                $data = null;
            }

            if ($data) {
                $payload['order'] = $data;
                $reply = sprintf(
                    'Order #%d — status: %s, payment: %s, total: %s, placed: %s.',
                    (int)$data['id'],
                    (string)$data['status'],
                    (string)$data['payment_status'],
                    (string)$data['total_fmt'],
                    (string)$data['created_fmt']
                );
            } else {
                $reply = 'I could not find that order, or you may not have access to it.';
            }

            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // "my orders / where is my order"
        if (preg_match('/\b(my )?orders?\b|where is my order|order status/i', $message)) {
            $orders = [];
            try {
                $orders = $this->recentOrders($identity, 3);
            } catch (\Throwable $e) {
                $orders = [];
            }

            if ($orders) {
                $payload['orders'] = $orders;
                $lines = array_map(fn($o) => sprintf('#%d — %s • %s', (int)$o['id'], (string)$o['status'], (string)$o['total_fmt']), $orders);
                $reply = 'Your recent orders: ' . implode('; ', $lines) . '. You can ask: "Order 101?"';
            } else {
                $reply = 'I could not find any recent orders on your account.';
            }

            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Light-weight "search/find/have/show <term>" product search
        if (preg_match('/(search|find|have|show)(.*)/i', $message, $m)) {
            $term = trim((string)($m[2] ?? '')) ?: $message;
            $term = preg_replace('/^(for|any|some|me|products?)/i', '', $term);

            $results = [];
            try {
                $results = $this->searchProducts($term, 6);
            } catch (\Throwable $e) {
                $results = [];
            }

            if ($results) {
                $payload['products'] = $results;
                if (count($results) === 1) {
                    $webroot             = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                    $reply               = 'I found ' . $results[0]['name'] . ' priced at ' . $results[0]['price_fmt'] . '. Opening the details page...';
                } else {
                    $names = array_map(fn($p) => $p['name'], $results);
                    $reply = 'I found ' . count($results) . ' products: ' . implode(', ', $names) . '. Which one would you like?';
                }
            } else {
                $reply = 'I couldn\'t find any products matching "' . htmlspecialchars($term) . '". Try another term, or ask "what cheeses do you have?"';
            }

            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Short cheese-like keywords => treat as product search
        $lower = strtolower($message);
        $cheeseKeywords = [
            'cheddar','brie','gouda','mozzarella','feta','blue','camembert','parmesan','aged','swiss',
            'gruyere','provolone','ricotta','halloumi','manchego','edam','colby','fontina','asiago',
            'pecorino','gorgonzola','stilton','roquefort','monterey','jack','havarti','muenster',
            'taleggio','raclette','emmental','jarlsberg','comte','mimolette','reblochon','beaufort',
            'boursin','chevre','cottage','cream','rustic','classic','vein','buffalo'
        ];
        $hasKeyword = false;
        foreach ($cheeseKeywords as $kw) {
            if (strpos($lower, $kw) !== false) { $hasKeyword = true; break; }
        }

        if ($hasKeyword && strlen($message) < 50 && !preg_match('/\b(what|how|when|where|why|can|do|does|is|are)\b/i', $message)) {
            $results = [];
            try { $results = $this->searchProducts(trim($message), 6); } catch (\Throwable $e) { $results = []; }

            if ($results) {
                $payload['products'] = $results;
                if (count($results) === 1) {
                    $webroot             = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                    $reply               = 'I found ' . $results[0]['name'] . ' priced at ' . $results[0]['price_fmt'] . '. Opening the details page...';
                } else {
                    $names = array_map(fn($p) => $p['name'], $results);
                    $reply = 'I found ' . count($results) . ' matching products: ' . implode(', ', $names) . '.';
                }
            } else {
                $reply = "I couldn't find any products matching '" . htmlspecialchars(trim($message)) . "'.";
            }

            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Fallback: generic hint
        return $this->json([
            'ok'    => true,
            'reply' => "I'm not sure about that. I can help with products, delivery, payments, and orders. Try: 'what cheeses do you have?' or 'how does delivery work?'."
        ]);
    }

    /**
     * Build a JSON response with correct headers.
     */
    private function json(array $data): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->response
            ->withType('application/json')
            ->withStringBody($payload === false ? '{}' : $payload);
    }

    /**
     * Securely look up an order owned by the current user (or any if admin).
     */
    private function lookupOrder(int $orderId, $identity): ?array
    {
        $q = $this->Orders->find()
            ->select(['id', 'user_id', 'status', 'payment_status', 'currency', 'total', 'created'])
            ->where(['Orders.id' => $orderId]);

        $userId  = $this->userId($identity);
        $isAdmin = $this->isAdmin($identity);

        if (!$isAdmin) {
            if ($userId === null) {
                return null; // not logged in; no access
            }
            $q->where(['Orders.user_id' => $userId]);
        }

        $o = $q->first();
        if (!$o) {
            return null;
        }

        return [
            'id'            => (int)$o->get('id'),
            'status'        => (string)$o->get('status'),
            'payment_status'=> (string)$o->get('payment_status'),
            'total'         => (float)$o->get('total'),
            'currency'      => (string)($o->get('currency') ?: 'AUD'),
            'total_fmt'     => $this->formatCurrency((float)$o->get('total'), (string)($o->get('currency') ?: 'AUD')),
            'created'       => $o->get('created'),
            'created_fmt'   => $o->get('created') ? $o->get('created')->format('Y-m-d H:i') : '',
        ];
    }

    /**
     * Recent orders for the current user (lightweight list).
     */
    private function recentOrders($identity, int $limit = 3): array
    {
        $userId = $this->userId($identity);
        if ($userId === null) {
            return [];
        }

        $rows = $this->Orders->find()
            ->select(['id', 'status', 'currency', 'total', 'created'])
            ->where(['user_id' => $userId])
            ->orderDesc('id')
            ->limit($limit)
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'        => (int)$r->get('id'),
                'status'    => (string)$r->get('status'),
                'total'     => (float)$r->get('total'),
                'total_fmt' => $this->formatCurrency((float)$r->get('total'), (string)($r->get('currency') ?: 'AUD')),
                'created'   => $r->get('created') ? $r->get('created')->format('Y-m-d') : '',
            ];
        }
        return $out;
    }

    /**
     * Product search by name/slug (contains)
     */
    private function searchProducts(string $term, int $limit = 6): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $rows = $this->Products->find()
            ->select(['id', 'name', 'slug', 'price', 'currency', 'image_url'])
            ->where([
                'OR' => [
                    'name LIKE' => '%' . $term . '%',
                    'slug LIKE' => '%' . $term . '%',
                ]
            ])
            ->limit($limit)
            ->all();

        $out = [];
        foreach ($rows as $p) {
            $out[] = [
                'id'        => (int)$p->get('id'),
                'name'      => (string)$p->get('name'),
                'slug'      => (string)$p->get('slug'),
                'price'     => (float)$p->get('price'),
                'price_fmt' => $this->formatCurrency((float)$p->get('price'), (string)($p->get('currency') ?: 'AUD')),
                'image'     => $p->get('image_url'),
                'url'       => ((string)($this->request->getAttribute('webroot') ?? '/')) . 'products/view/' . rawurlencode((string)$p->get('slug')),
            ];
        }

        return $out;
    }

    /**
     * Simple currency formatter (client-friendly).
     */
    private function formatCurrency(float $amount, string $currency): string
    {
        $symbol = $currency;
        switch (strtoupper($currency)) {
            case 'AUD': $symbol = 'A$'; break;
            case 'USD': $symbol = '$';  break;
            case 'EUR': $symbol = '€';  break;
            case 'GBP': $symbol = '£';  break;
        }
        return $symbol . number_format($amount, 2);
    }
}
