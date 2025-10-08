# AI-Powered Chatbot Setup Guide

The Curd Culture chatbot now supports AI-powered natural language understanding using **Google Gemini** (default) or OpenAI's GPT models. This makes conversations much smoother and more natural!

## ✅ **GOOD NEWS: Your API Key is Already Configured!**

The chatbot is **ready to use** with Google Gemini AI! The API key has been added and enabled.

## 🚀 Features

### Without AI (Rule-based)
- Responds to specific keyword patterns
- Limited understanding of variations
- Fast and free (no API costs)

### With AI Enabled (Current Setup - Gemini)
- ✅ **Natural language understanding**
- ✅ **Handles typos and variations**
- ✅ **Conversational responses**
- ✅ **Context-aware interactions**
- ✅ **Provides recommendations**
- ✅ **More helpful and engaging**
- ✅ **Free tier available!**

## 🎯 Current Configuration

Your chatbot is configured to use:
- **Provider**: Google Gemini
- **Model**: gemini-1.5-flash (fast and free tier available)
- **Status**: ✅ ENABLED
- **API Key**: Configured

## 💬 Try It Now!

Visit your website and click the chatbot icon. Try these natural language queries:

### Example Conversations

```
You: "Hey, what kind of cheeses do you sell?"
Bot: "We offer a variety of artisan cheeses including aged cheddar, gouda, brie, and blue cheese..."

You: "I'm looking for something with a strong flavor"
Bot: "For strong flavors, I'd recommend our Blue Cheese or Extra Sharp Cheddar..."

You: "Can you check my recent orders?"
Bot: "Let me look that up for you. You have Order #123 which is currently processing..."

You: "Do you have any aged options?"
Bot: "Yes! We have some excellent aged options like Aged Gouda and Mature Cheddar..."
```

## 🔧 Configuration Details

Your current setup in `config/app_local.php`:

```php
'AI' => [
    'provider' => 'gemini',  // Using Google Gemini
    'gemini' => [
        'api_key' => 'AIzaSyAyjXpZzIpPmTRnqk5mPf1BbE-ECOsXErI',
        'model' => 'gemini-1.5-flash',
        'enabled' => true,  // ✅ ENABLED
    ],
],
```

## 🆚 Provider Comparison

| Feature | Google Gemini | OpenAI GPT |
|---------|---------------|------------|
| Free tier | ✅ Yes (60 requests/min) | ❌ No |
| Speed | Very Fast | Fast |
| Quality | Excellent | Excellent |
| Setup | ✅ Done! | Need API key |
| Cost (paid) | Very low | Low-Medium |

## 💰 Cost Information

### Google Gemini (Current)
- **Free tier**: Up to 60 requests per minute
- **Paid tier**: $0.075 per 1M characters (very affordable)
- Your setup: Free tier is sufficient for most websites!

### OpenAI (Alternative)
- No free tier
- ~$0.15-0.60 per 1M tokens
- Still very affordable

## 🔄 Switch to OpenAI (Optional)

If you want to use OpenAI instead:

1. Get an OpenAI API key from https://platform.openai.com/api-keys
2. Edit `config/app_local.php`:
   ```php
   'AI' => [
       'provider' => 'openai',  // Change to openai
       'openai' => [
           'api_key' => 'sk-your-openai-key-here',
           'model' => 'gpt-4o-mini',
           'enabled' => true,
       ],
   ],
   ```

## 🛠️ Advanced Configuration

### Models Available

#### Google Gemini (Current)
- `gemini-1.5-flash` - Fast, free tier (recommended)
- `gemini-1.5-pro` - More powerful, still very affordable

#### OpenAI (Alternative)
- `gpt-4o-mini` - Fast and cost-effective
- `gpt-4o` - More powerful
- `gpt-4-turbo` - Most capable

### Customization

Edit `config/app_local.php` to adjust:

```php
'AI' => [
    'provider' => 'gemini',
    'max_tokens' => 500,      // Response length (100-2000)
    'temperature' => 0.7,     // Creativity (0-1, higher = more creative)
],
```

## 🔒 Security Notes

- API key is stored server-side only (not exposed to browsers)
- Never commit API keys to version control
- The key is already in `app_local.php` (which is gitignored)
- Monitor your usage at https://console.cloud.google.com

## 🐛 Troubleshooting

### Not getting AI responses?
1. Check that `'enabled' => true` in config
2. Verify the API key is correct
3. Check error logs in `logs/error.log`

### Slow responses?
- Gemini is already very fast
- Response time: typically 1-2 seconds
- Rule-based fallback is instant if AI fails

### Getting rule-based responses?
- Check configuration is correct
- Verify `'enabled' => true`
- Check that provider is set to 'gemini'

### API quota exceeded?
- Free tier: 60 requests/min
- Upgrade at https://console.cloud.google.com if needed
- System automatically falls back to rule-based responses

## 📊 Monitoring Usage

### Google Gemini
Monitor your usage at:
- https://console.cloud.google.com
- https://aistudio.google.com

### Set up alerts (optional)
1. Visit Google Cloud Console
2. Set billing alerts if desired
3. Most small/medium sites stay within free tier!

## 🔄 Fallback Behavior

The system is designed to be resilient:
- If AI fails → Falls back to rule-based system
- If API quota exceeded → Falls back to rule-based system
- If API key invalid → Falls back to rule-based system

Your chatbot **always works**, even if AI is temporarily unavailable!

## 🎯 Best Practices

1. ✅ **Already done**: Gemini is configured and enabled
2. **Monitor usage** in the first week
3. **Test thoroughly** with various queries
4. **Use context**: The AI knows about orders and products
5. **Stay within free tier** for most sites

## 📝 API Key Information

Your Gemini API key: `AIzaSyAyjXpZzIpPmTRnqk5mPf1BbE-ECOsXErI`

**Security reminders:**
- Don't share this key publicly
- It's already safely stored in `config/app_local.php`
- This file is gitignored and won't be committed
- Regenerate the key if it's ever exposed

## 🎉 You're All Set!

Your AI-powered chatbot is **fully configured and ready to use**!

### Next Steps:
1. ✅ Configuration complete
2. 🚀 Visit your website
3. 💬 Click the chatbot icon
4. 🎮 Try natural language queries!

### Quick Test:
```
User: "Hi, what do you sell?"
User: "I want aged cheese"
User: "Show me my orders"
User: "Do you have blue cheese?"
```

Enjoy your intelligent, conversational chatbot! 🤖✨

---

**Need Help?**
- Google Gemini Docs: https://ai.google.dev/docs
- API Console: https://console.cloud.google.com
- AI Studio: https://aistudio.google.com
