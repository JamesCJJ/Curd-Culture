<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AIService;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * AI-Enhanced Copilot (chatbot)
 * - Answers questions about products and orders naturally using AI (Google Gemini or OpenAI)
 * - Falls back to rule-based parsing when AI is disabled
 * - Keeps logic server-side for security
 */
class CopilotController extends AppController
{
    private ?AIService $aiService = null;

    public function initialize(): void
    {
        parent::initialize();
        // Fetch tables explicitly (avoid loadModel to support all setups)
        $this->Products   = TableRegistry::getTableLocator()->get('Products');
        $this->Orders     = TableRegistry::getTableLocator()->get('Orders');
        $this->OrderItems = TableRegistry::getTableLocator()->get('OrderItems');
        $this->viewBuilder()->setClassName('Json');
        
        // Initialize AI service (supports Gemini and OpenAI)
        $this->aiService = new AIService();
        
        // Allow unauthenticated access
        if (isset($this->Authentication)) {
            $this->Authentication->allowUnauthenticated(['talk']);
        }
        
        // Load FormProtection but allow JSON requests
        // Disable FormProtection for this controller since we handle CSRF via header
        if ($this->components()->has('FormProtection')) {
            $this->components()->unload('FormProtection');
        }
    }

    /**
     * POST /copilot/talk
     * Body: { message: string }
     */
    public function talk(): Response
    {
        $this->request->allowMethod(['post']);

        $body = (array)$this->request->getData();
        $message = trim((string)($body['message'] ?? ''));
        if ($message === '') {
            return $this->json([
                'ok' => true,
                'reply' => "Hi! I can help with orders and products. Try asking me anything!",
            ]);
        }

        $identity = $this->request->getAttribute('identity');

        // Try AI-powered response first
        if ($this->aiService && $this->aiService->isEnabled()) {
            try {
                return $this->handleWithAI($message, $identity);
            } catch (\Throwable $e) {
                // Fall through to rule-based handler on any error
            }
        }

        // Fall back to rule-based system
        return $this->handleWithRules($message, $identity);
    }

    /**
     * Handle message with AI (OpenAI)
     */
    private function handleWithAI(string $message, ?EntityInterface $identity): Response
    {
        // Build context for AI
        $context = [];
        
        if ($identity) {
            $context['user_id'] = (int)$identity->get('id');
            
            // Get recent orders for context (tolerate DB issues)
            $recentOrders = [];
            try {
                $recentOrders = $this->recentOrders($identity, 3);
            } catch (\Throwable $e) {
                $recentOrders = [];
            }
            if (!empty($recentOrders)) {
                $context['recent_orders'] = $recentOrders;
            }
        }

        // Get some product names for context (tolerate DB issues)
        $products = [];
        try {
            $products = $this->Products->find()
                ->select(['name'])
                ->orderAsc('name')
                ->limit(10)
                ->all()
                ->extract('name')
                ->toArray();
        } catch (\Throwable $e) {
            $products = [];
        }
        if (!empty($products)) {
            $context['available_products'] = $products;
        }

        // Get AI response
        $aiResponse = $this->aiService->chat($message, $context);

        if (!$aiResponse['success']) {
            // AI failed, fall back to rules
            return $this->handleWithRules($message, $identity);
        }

        $reply = $aiResponse['message'];
        $payload = [];

        // Check if message mentions specific orders and fetch data
        if (preg_match('/order\s*#?\s*(\d{1,8})/i', $message, $m)) {
            $orderId = (int)$m[1];
            $orderData = $this->lookupOrder($orderId, $identity);
            if ($orderData) {
                $payload['order'] = $orderData;
            }
        }

        // Check if message is about recent orders
        if (preg_match('/\b(my|recent)\s*(orders?|purchases?)\b/i', $message) && $identity) {
            $ordersList = $this->recentOrders($identity, 3);
            if (!empty($ordersList)) {
                $payload['orders'] = $ordersList;
            }
        }

        // Check if message is searching for products
        $productSearchTerm = null;
        
        // Explicit search patterns
        if (preg_match('/(search|find|looking for|want|need)\s+(.+)/i', $message, $m)) {
            $productSearchTerm = trim((string)($m[2] ?? ''));
            $productSearchTerm = preg_replace('/\b(cheese|product|some|any|a)\b/i', '', $productSearchTerm);
            $productSearchTerm = trim($productSearchTerm);
        }
        // Or simple product name queries (short messages without question words)
        elseif (strlen($message) < 50 && !preg_match('/\b(what|how|when|where|why|can|do|does|is|are|my|order)\b/i', $message)) {
            $productSearchTerm = trim($message);
        }
        
        if ($productSearchTerm && $productSearchTerm !== '') {
            $results = $this->searchProducts($productSearchTerm, 6);
            if (!empty($results)) {
                $payload['products'] = $results;
                // Auto-open single product
                if (count($results) === 1) {
                    $webroot = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                }
            }
        }

        return $this->json([
            'ok' => true,
            'reply' => $reply,
            'data' => $payload,
            'ai_powered' => true,
        ]);
    }

    /**
     * Handle message with rule-based system (original logic)
     */
    private function handleWithRules(string $message, ?EntityInterface $identity): Response
    {
        $reply = null;
        $payload = [];

        // Delivery, Shipping, Pickup questions
        if (preg_match('/(deliver|delivery|shipping|ship|arrive|arrival|pickup|pick.?up|how long)/i', $message)) {
            $reply = 'We offer scheduled delivery slots and in-store pickup. You can choose your preferred option and see the final cost during checkout. For details on an existing order, please check your customer dashboard.';
            return $this->json(['ok' => true, 'reply' => $reply]);
        }

        // Product ingredients, dietary, or allergy questions
        if (preg_match('/(gluten|ingredient|pasteuri|vegan|vegetarian|allergy|allergic|dietary|lactose)/i', $message)) {
            $reply = "We list all ingredients and dietary information on each product's page. Please search for the cheese you're interested in to see its full details. For severe allergy concerns, we recommend contacting us directly.";
            return $this->json(['ok' => true, 'reply' => $reply]);
        }
        
        // Payment methods
        if (preg_match('/(pay|payment|card|credit|debit|amex|visa|mastercard|accept.*card)/i', $message)) {
            $reply = 'We accept all major credit cards (Visa, Mastercard, Amex) through our secure checkout via Stripe.';
            return $this->json(['ok' => true, 'reply' => $reply]);
        }

        // Contact or support questions
        if (preg_match('/(contact|support|help|email|phone|reach|cancel|change.*order|modify.*order)/i', $message)) {
            $reply = "For any help with your order or other questions, please use the form on our 'Contact Us' page, and our team will be happy to assist you.";
            return $this->json(['ok' => true, 'reply' => $reply]);
        }

        // General inventory question - expanded to catch more variations
        if (preg_match('/(cheese|cheeses|dairy|product|products|what.*(have|sell|offer|carry|stock))/i', $message)) {
            $names = [];
            try {
                $rows = $this->Products->find()->select(['name','slug'])->orderAsc('name')->limit(6)->all();
                foreach ($rows as $r) {
                    $names[] = (string)$r->get('name');
                }
            } catch (\Throwable $e) {
                // ignore and fall back
            }
            if (!empty($names)) {
                $reply = 'We offer a selection of artisan cheeses including: ' . implode(', ', $names) . ', and more. Would you like to know more about any specific cheese? Just type the name or ask me to search for it.';
            } else {
                $reply = 'We carry premium artisan cheeses including cheddar, brie, gouda, and blue varieties. Please ask me about a specific cheese to learn more!';
            }
            return $this->json(['ok' => true, 'reply' => $reply]);
        }

        // Order by explicit number e.g., order 1234
        if (preg_match('/order\s*#?\s*(\d{1,8})/i', $message, $m)) {
            $orderId = (int)$m[1];
            $replyData = null;
            try {
                $replyData = $this->lookupOrder($orderId, $identity);
            } catch (\Throwable $e) {
                $replyData = null;
            }
            if ($replyData) {
                $payload['order'] = $replyData;
                $reply = sprintf(
                    'Order #%d — status: %s, payment: %s, total: %s, placed: %s.',
                    (int)$replyData['id'],
                    (string)$replyData['status'],
                    (string)$replyData['payment_status'],
                    (string)$replyData['total_fmt'],
                    (string)$replyData['created_fmt']
                );
            } else {
                $reply = 'I could not find that order, or you may not have access to it.';
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // My recent orders
        if (preg_match('/\b(my )?orders?\b|where is my order|order status/i', $message)) {
            if (!$identity) {
                $reply = 'Due to privacy, I cannot show order details in this chat. Please log in and visit your customer dashboard to see your full order history.';
                return $this->json(['ok' => true, 'reply' => $reply]);
            }
            
            $ordersList = [];
            try {
                $ordersList = $this->recentOrders($identity, 3);
            } catch (\Throwable $e) {
                $ordersList = [];
            }
            if (!empty($ordersList)) {
                $payload['orders'] = $ordersList;
                $lines = array_map(function ($o) {
                    return sprintf('#%d — %s • %s', (int)$o['id'], (string)$o['status'], (string)$o['total_fmt']);
                }, $ordersList);
                $reply = 'Your recent orders: ' . implode('; ', $lines) . '. You can ask: "Order 101?"';
            } else {
                $reply = 'I could not find any recent orders on your account.';
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Product search e.g., search cheddar / find gouda / do you have brie
        if (preg_match('/(search|find|have|show)(.*)/i', $message, $m)) {
            $term = trim((string)($m[2] ?? ''));
            if ($term === '') {
                $term = $message; // fallback to whole message
            }
            $term = preg_replace('/^(for|any|some|me|products?)/i', '', $term);
            $results = [];
            try {
                $results = $this->searchProducts($term, 6);
            } catch (\Throwable $e) {
                $results = [];
            }
            if (!empty($results)) {
                $payload['products'] = $results;
                if (count($results) === 1) {
                    // Single product - provide link immediately
                    $webroot = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                    $reply = 'I found ' . $results[0]['name'] . ' priced at ' . $results[0]['price_fmt'] . '. Opening the product details page for you now...';
                } else {
                    // Multiple products - list them
                    $names = array_map(fn($p) => $p['name'], $results);
                    $reply = 'I found ' . count($results) . ' products: ' . implode(', ', $names) . '. Which one would you like to learn more about? Type the product name to see its details.';
                }
            } else {
                $reply = 'I couldn\'t find any products matching "' . htmlspecialchars($term) . '". Please try a different search term, or ask "what cheeses do you have?" to see our full range.';
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Open product by name/slug when user asks specifically
        if (preg_match('/\b(view|open|show me)\s+([a-z0-9\-]+)/i', $message, $m)) {
            $slug = strtolower(trim($m[2] ?? ''));
            
            if ($slug !== '') {
                $product = null;
                try {
                    $product = $this->Products->find()->select(['id', 'name', 'slug', 'price', 'currency'])
                        ->where(['slug' => $slug])->first();
                } catch (\Throwable $e) {
                    $product = null;
                }
                if ($product) {
                    $webroot = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($slug);
                    $reply = 'Here\'s the link to ' . (string)$product->get('name') . '. Opening the product details page for you now...';
                } else {
                    $reply = 'I couldn\'t find a product with that name. Please check the spelling or ask "what cheeses do you have?" to see available options.';
                }
                return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
            }
        }

        // Catch standalone cheese/product queries (e.g., "cheddar", "aged-gouda-18m", "brie")
        // This catches common cheese names or short product-like queries
        // Matches if message contains cheese keywords and is relatively short (under 50 chars)
        $lowerMsg = strtolower($message);
        $cheeseKeywords = ['cheddar', 'brie', 'gouda', 'mozzarella', 'feta', 'blue', 'camembert', 
                          'parmesan', 'aged', 'swiss', 'gruyere', 'provolone', 'ricotta', 'halloumi', 
                          'manchego', 'edam', 'colby', 'fontina', 'asiago', 'pecorino', 'gorgonzola', 
                          'stilton', 'roquefort', 'monterey', 'jack', 'havarti', 'muenster', 'taleggio', 
                          'raclette', 'emmental', 'jarlsberg', 'comte', 'mimolette', 'reblochon', 
                          'beaufort', 'boursin', 'chevre', 'cottage', 'cream', 'rustic', 'classic',
                          'vein', 'buffalo'];
        
        $containsCheeseKeyword = false;
        foreach ($cheeseKeywords as $keyword) {
            if (strpos($lowerMsg, $keyword) !== false) {
                $containsCheeseKeyword = true;
                break;
            }
        }
        
        // If it's a short message containing a cheese keyword, treat it as a product search
        if ($containsCheeseKeyword && strlen($message) < 50 && !preg_match('/(what|how|when|where|why|can|do|does|is|are)\b/i', $message)) {
            $term = trim($message);
            $results = [];
            try {
                $results = $this->searchProducts($term, 6);
            } catch (\Throwable $e) {
                $results = [];
            }
            if (!empty($results)) {
                $payload['products'] = $results;
                if (count($results) === 1) {
                    // Single product - provide link immediately
                    $webroot = (string)($this->request->getAttribute('webroot') ?? '/');
                    $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($results[0]['slug']);
                    $reply = 'I found ' . $results[0]['name'] . ' priced at ' . $results[0]['price_fmt'] . '. Opening the product details page for you now...';
                } else {
                    // Multiple products - list them
                    $names = array_map(fn($p) => $p['name'], $results);
                    $reply = 'I found ' . count($results) . ' matching products: ' . implode(', ', $names) . '. Which one would you like to learn more about? Type the specific product name to see its details.';
                }
            } else {
                $reply = "I couldn't find any products matching '" . htmlspecialchars($term) . "'. Please try a different search, or ask 'what cheeses do you have?' to see our complete selection.";
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Fallback
        return $this->json([
            'ok' => true,
            'reply' => "I'm not sure how to help with that. I can answer questions about products, delivery, payments, and orders. Try asking 'what cheeses do you have?' or 'how does delivery work?'.",
        ]);
    }

    private function json(array $data): Response
    {
        // Build explicit JSON response to avoid empty-body issues
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->response
            ->withType('application/json')
            ->withStringBody($payload === false ? '{}' : $payload);
    }

    /**
     * Return a public-safe order summary for current user (or admin)
     */
    private function lookupOrder(int $orderId, ?EntityInterface $identity): ?array
    {
        $query = $this->Orders->find()
            ->select(['id', 'user_id', 'status', 'payment_status', 'currency', 'total', 'created'])
            ->where(['Orders.id' => $orderId]);

        $isAdmin = $identity && strtolower((string)$identity->get('role')) === 'admin';
        if (!$isAdmin && $identity) {
            $query->where(['Orders.user_id' => (int)$identity->get('id')]);
        }

        $o = $query->first();
        if (!$o) {
            return null;
        }

        return [
            'id' => (int)$o->get('id'),
            'status' => (string)$o->get('status'),
            'payment_status' => (string)$o->get('payment_status'),
            'total' => (float)$o->get('total'),
            'currency' => (string)($o->get('currency') ?: 'AUD'),
            'total_fmt' => $this->formatCurrency((float)$o->get('total'), (string)($o->get('currency') ?: 'AUD')),
            'created' => $o->get('created'),
            'created_fmt' => $o->get('created') ? $o->get('created')->format('Y-m-d H:i') : '',
        ];
    }

    /**
     * Recent orders for current user
     */
    private function recentOrders(?EntityInterface $identity, int $limit = 3): array
    {
        if (!$identity) {
            return [];
        }
        $rows = $this->Orders->find()
            ->select(['id', 'status', 'currency', 'total', 'created'])
            ->where(['user_id' => (int)$identity->get('id')])
            ->orderDesc('id')
            ->limit($limit)
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r->get('id'),
                'status' => (string)$r->get('status'),
                'total' => (float)$r->get('total'),
                'total_fmt' => $this->formatCurrency((float)$r->get('total'), (string)($r->get('currency') ?: 'AUD')),
                'created' => $r->get('created') ? $r->get('created')->format('Y-m-d') : '',
            ];
        }
        return $out;
    }

    private function searchProducts(string $term, int $limit = 6): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }
        $q = $this->Products->find()
            ->select(['id', 'name', 'slug', 'price', 'currency', 'image_url'])
            ->where([
                'OR' => [
                    'name LIKE' => '%' . $term . '%',
                    'slug LIKE' => '%' . $term . '%'
                ]
            ])
            ->limit($limit)
            ->all();
        $out = [];
        foreach ($q as $p) {
            $out[] = [
                'id' => (int)$p->get('id'),
                'name' => (string)$p->get('name'),
                'slug' => (string)$p->get('slug'),
                'price' => (float)$p->get('price'),
                'price_fmt' => $this->formatCurrency((float)$p->get('price'), (string)($p->get('currency') ?: 'AUD')),
                'image' => $p->get('image_url'),
                'url' => ((string)($this->request->getAttribute('webroot') ?? '/')) . 'products/view/' . rawurlencode((string)$p->get('slug')),
            ];
        }
        return $out;
    }

    private function formatCurrency(float $amount, string $currency): string
    {
        $symbol = $currency;
        switch (strtoupper($currency)) {
            case 'AUD': $symbol = 'A$'; break;
            case 'USD': $symbol = '$'; break;
            case 'EUR': $symbol = '€'; break;
            case 'GBP': $symbol = '£'; break;
        }
        return $symbol . number_format($amount, 2);
    }
}



