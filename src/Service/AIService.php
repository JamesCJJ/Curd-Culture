<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * AI Service for AI-powered chatbot interactions
 * Supports both Google Gemini and OpenAI APIs
 */
class AIService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private bool $enabled;

    public function __construct()
    {
        $config = Configure::read('AI');
        $this->provider = $config['provider'] ?? 'gemini';
        
        // Load provider-specific config
        $providerConfig = $config[$this->provider] ?? [];
        $this->apiKey = $providerConfig['api_key'] ?? '';
        $this->model = $providerConfig['model'] ?? 'gemini-1.5-flash';
        $this->enabled = $providerConfig['enabled'] ?? false;
        
        // Load shared settings
        $this->maxTokens = $config['max_tokens'] ?? 500;
        $this->temperature = $config['temperature'] ?? 0.7;
    }

    /**
     * Check if AI integration is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Send a chat message to AI and get a response
     *
     * @param string $userMessage The user's message
     * @param array $context Additional context (products, orders, etc.)
     * @return array Response with 'success', 'message', and optional 'data'
     */
    public function chat(string $userMessage, array $context = []): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'AI service is not enabled',
            ];
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($context);
            
            if ($this->provider === 'gemini') {
                $response = $this->callGemini($systemPrompt, $userMessage);
            } else {
                $response = $this->callOpenAI($systemPrompt, $userMessage);
            }

            return [
                'success' => true,
                'message' => $response['content'] ?? 'No response received',
                'data' => $this->extractData($response['content'] ?? ''),
                'provider' => $this->provider,
            ];
        } catch (\Exception $e) {
            Log::error('AI API Error (' . $this->provider . '): ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your request.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the system prompt with context
     */
    private function buildSystemPrompt(array $context): string
    {
        $prompt = "You are a helpful customer service assistant for Curd Culture, an artisan cheese shop. ";
        $prompt .= "Your role is to help customers with:\n";
        $prompt .= "1. Finding and learning about cheese products\n";
        $prompt .= "2. Checking order status and details\n";
        $prompt .= "3. Answering general questions about cheese\n";
        $prompt .= "4. Providing recommendations\n\n";

        $prompt .= "Keep responses concise (2-3 sentences max), friendly, and helpful. ";
        $prompt .= "If asked about specific orders or products, use the provided context data.\n\n";

        if (!empty($context['user_id'])) {
            $prompt .= "Current user ID: {$context['user_id']}\n";
        }

        if (!empty($context['recent_orders'])) {
            $prompt .= "User's recent orders:\n";
            foreach ($context['recent_orders'] as $order) {
                $prompt .= "- Order #{$order['id']}: {$order['status']}, {$order['total_fmt']}\n";
            }
            $prompt .= "\n";
        }

        if (!empty($context['available_products'])) {
            $prompt .= "Available products (sample): " . implode(', ', array_slice($context['available_products'], 0, 5)) . "\n\n";
        }

        $prompt .= "When users ask to view products or check orders, respond naturally and mention that you can help them find what they need.";

        return $prompt;
    }

    /**
     * Call Google Gemini API
     */
    private function callGemini(string $systemPrompt, string $userMessage): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Combine system prompt and user message for Gemini
        $combinedPrompt = $systemPrompt . "\n\nUser: " . $userMessage . "\n\nAssistant:";

        $data = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $combinedPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Gemini API returned status code: {$httpCode} - Response: {$response}");
        }

        $decoded = json_decode($response, true);
        
        if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception("Invalid response from Gemini API: " . json_encode($decoded));
        }

        return [
            'content' => $decoded['candidates'][0]['content']['parts'][0]['text']
        ];
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $systemPrompt, string $userMessage): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("OpenAI API returned status code: {$httpCode}");
        }

        $decoded = json_decode($response, true);
        if (!isset($decoded['choices'][0]['message'])) {
            throw new \Exception("Invalid response from OpenAI API");
        }

        return $decoded['choices'][0]['message'];
    }

    /**
     * Extract structured data from AI response
     * Looks for patterns like "order #123" or "product: slug"
     */
    private function extractData(string $content): array
    {
        $data = [];

        // Extract order numbers mentioned in response
        if (preg_match_all('/#(\d+)/i', $content, $matches)) {
            $data['mentioned_orders'] = array_unique($matches[1]);
        }

        return $data;
    }
}

