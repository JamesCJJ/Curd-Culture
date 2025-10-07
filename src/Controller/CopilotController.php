<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Lightweight in-app Copilot (chatbot)
 * - Answers basic questions about products and orders
 * - Uses simple intent parsing; keeps logic server-side for security
 */
class CopilotController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Fetch tables explicitly (avoid loadModel to support all setups)
        $this->Products   = TableRegistry::getTableLocator()->get('Products');
        $this->Orders     = TableRegistry::getTableLocator()->get('Orders');
        $this->OrderItems = TableRegistry::getTableLocator()->get('OrderItems');
        $this->viewBuilder()->setClassName('Json');
        
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
                'reply' => "Hi! I can help with orders and products. Try: 'Where is my order 100?' or 'Search cheddar'",
            ]);
        }

        $identity = $this->request->getAttribute('identity');

        $reply = null;
        $payload = [];

        // Order by explicit number e.g., order 1234
        if (preg_match('/order\s*#?\s*(\d{1,8})/i', $message, $m)) {
            $orderId = (int)$m[1];
            $replyData = $this->lookupOrder($orderId, $identity);
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
            $ordersList = $this->recentOrders($identity, 3);
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
            $results = $this->searchProducts($term, 6);
            if (!empty($results)) {
                $payload['products'] = $results;
                $names = array_map(fn($p) => $p['name'], $results);
                $reply = 'I found: ' . implode(', ', $names) . '. Say "view ' . $results[0]['slug'] . '" to open.';
            } else {
                $reply = 'No products matched that search. Try a different name (e.g., cheddar, brie, blue).';
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Open product by slug e.g., view aged-gouda-18m
        if (preg_match('/\b(view|open)\s+([a-z0-9\-]{3,})/i', $message, $m)) {
            $slug = strtolower($m[2]);
            $product = $this->Products->find()->select(['id', 'name', 'slug'])
                ->where(['slug' => $slug])->first();
            if ($product) {
                $webroot = (string)($this->request->getAttribute('webroot') ?? '/');
                $payload['open_url'] = $webroot . 'products/view/' . rawurlencode($slug);
                $reply = 'Opening ' . (string)$product->get('name') . '…';
            } else {
                $reply = 'I could not find that item.';
            }
            return $this->json(['ok' => true, 'reply' => $reply, 'data' => $payload]);
        }

        // Fallback
        return $this->json([
            'ok' => true,
            'reply' => "I can help with orders and products. Examples: 'Order 101', 'Search cheddar', 'My orders'.",
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
            ->where(['name LIKE' => '%' . $term . '%'])
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



