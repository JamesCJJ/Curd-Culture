<?php
/**
 * Quick test script for the AI-powered chatbot configuration
 * Run this from the command line: php test_chatbot.php
 */

echo "=== Curd Culture AI Chatbot Configuration Test ===\n\n";

// Check if config file exists
echo "1. Checking Configuration Files...\n";
$configFile = dirname(__FILE__) . '/config/app_local.php';

if (!file_exists($configFile)) {
    echo "   ❌ config/app_local.php not found\n";
    echo "   Please copy config/app_local.example.php to config/app_local.php\n";
    exit(1);
}

echo "   ✓ config/app_local.php exists\n";

// Load config
$config = require $configFile;

// Check AI configuration
echo "\n2. Checking AI Configuration...\n";

if (!isset($config['AI'])) {
    echo "   ❌ AI configuration not found in config/app_local.php\n";
    echo "   Please add AI configuration (see AI_CHATBOT_SETUP.md)\n";
    exit(1);
}

$aiConfig = $config['AI'];
$provider = $aiConfig['provider'] ?? 'gemini';

echo "   ✓ AI config found\n";
echo "   - Provider: " . strtoupper($provider) . "\n";
echo "   - Max Tokens: " . ($aiConfig['max_tokens'] ?? 'not set') . "\n";
echo "   - Temperature: " . ($aiConfig['temperature'] ?? 'not set') . "\n";

// Check provider-specific config
if (!isset($aiConfig[$provider])) {
    echo "   ❌ Configuration for provider '$provider' not found\n";
    exit(1);
}

$providerConfig = $aiConfig[$provider];
$enabled = $providerConfig['enabled'] ?? false;
$model = $providerConfig['model'] ?? 'not set';

echo "   - Model: " . $model . "\n";
echo "   - Enabled: " . ($enabled ? '✅ YES' : '❌ NO') . "\n";

// Check API key
$apiKey = $providerConfig['api_key'] ?? '';
if (empty($apiKey)) {
    echo "   - API Key: ❌ Not set\n\n";
    echo "⚠️  AI service is disabled (no API key)\n";
    echo "   The chatbot will use rule-based responses instead.\n\n";
} else {
    $keyPrefix = substr($apiKey, 0, 10);
    $keySuffix = substr($apiKey, -4);
    echo "   - API Key: ✓ Set ($keyPrefix...{$keySuffix})\n\n";
    
    if ($enabled) {
        echo "✅ ✅ ✅ AI CHATBOT IS ENABLED! ✅ ✅ ✅\n";
        echo "   Your chatbot will use " . strtoupper($provider) . " for natural language AI responses.\n\n";
        
        if ($provider === 'gemini') {
            echo "   🎉 Using Google Gemini - Free tier available!\n";
            echo "   📊 Monitor usage: https://console.cloud.google.com\n\n";
        } else {
            echo "   📊 Monitor usage: https://platform.openai.com/usage\n\n";
        }
    } else {
        echo "⚠️  AI chatbot is DISABLED (enabled = false)\n";
        echo "   Set 'enabled' => true in config/app_local.php to activate AI.\n\n";
    }
}

// Check Service file
echo "3. Checking Service Files...\n";
$serviceFile = dirname(__FILE__) . '/src/Service/AIService.php';
if (!file_exists($serviceFile)) {
    echo "   ❌ src/Service/AIService.php not found\n";
    exit(1);
}
echo "   ✓ AIService.php exists\n";

// Check Controller
$controllerFile = dirname(__FILE__) . '/src/Controller/CopilotController.php';
if (!file_exists($controllerFile)) {
    echo "   ❌ src/Controller/CopilotController.php not found\n";
    exit(1);
}
echo "   ✓ CopilotController.php exists\n";

// Check setup guide
$setupFile = dirname(__FILE__) . '/AI_CHATBOT_SETUP.md';
if (!file_exists($setupFile)) {
    echo "   ⚠️  AI_CHATBOT_SETUP.md not found\n";
} else {
    echo "   ✓ AI_CHATBOT_SETUP.md exists\n";
}

// Check ready guide
$readyFile = dirname(__FILE__) . '/GEMINI_AI_READY.md';
if (file_exists($readyFile)) {
    echo "   ✓ GEMINI_AI_READY.md exists\n";
}

echo "\n=== Configuration Test Complete ===\n\n";
echo "Summary:\n";
echo "--------\n";
echo "• Configuration files: ✓ Present\n";
echo "• AI provider: " . strtoupper($provider) . "\n";
echo "• Service & Controller: ✓ Updated\n";

if (!empty($apiKey) && $enabled) {
    echo "• Status: 🎉 ✅ AI ENABLED - Ready to use!\n\n";
    
    if ($provider === 'gemini') {
        echo "🚀 GOOGLE GEMINI AI IS ACTIVE! 🚀\n\n";
    } else {
        echo "🚀 OPENAI GPT IS ACTIVE! 🚀\n\n";
    }
    
    echo "Next steps:\n";
    echo "1. Visit your website\n";
    echo "2. Click the chatbot icon (bottom-right)\n";
    echo "3. Try natural queries like:\n";
    echo "   💬 'What cheeses do you have?'\n";
    echo "   💬 'I'm looking for aged cheese'\n";
    echo "   💬 'Can you show me my orders?'\n";
    echo "   💬 'Do you sell blue cheese?'\n\n";
    echo "See GEMINI_AI_READY.md for more information!\n";
} else {
    echo "• Status: ⚡ Rule-based mode (AI disabled)\n\n";
    echo "The chatbot works but uses keyword matching.\n";
    echo "See AI_CHATBOT_SETUP.md to enable AI features.\n";
}
echo "\n";
