# AI Chatbot Fix - October 13, 2025

## What Was Fixed

The AI chatbot was not working due to two main issues:

1. **Outdated Gemini Model**: The code was using `gemini-1.5-flash` which no longer exists
2. **Wrong API Version**: Using `v1beta` instead of `v1`
3. **Missing Database Columns**: The migration for order fulfillment fields wasn't run

## Changes Made

### 1. Updated Gemini Model
- Changed from `gemini-1.5-flash` → `gemini-2.0-flash`
- Updated API endpoint from `v1beta` → `v1`
- Files updated:
  - `src/Service/AIService.php`
  - `config/app_local.example.php`

### 2. Ran Database Migration
- Executed migration: `20251012054614_AddFulfillmentFieldsToOrders.php`
- This added the missing `delivery_date`, `delivery_slot_id`, `fulfillment_method`, and `pickup_point_id` columns to the `orders` table

## Setup Instructions for Team Members

### Step 1: Pull Latest Changes
```bash
git pull origin main
```

### Step 2: Run Database Migrations
```bash
bin/cake migrations migrate
```

### Step 3: Update Configuration
You have two options:

#### Option A: Copy the Config File (Recommended)
If you don't have `config/app_local.php`, copy from the example:
```bash
cp config/app_local.example.php config/app_local.php
```

Then edit `config/app_local.php` and update the AI section to use the correct API key:
```php
'AI' => [
    'provider' => 'gemini',
    'gemini' => [
        'api_key' => 'AIzaSyAyjXpZzIpPmTRnqk5mPf1BbE-ECOsXErI',
        'model' => 'gemini-2.0-flash',
        'enabled' => true,
    ],
    // ... rest of config
],
```

#### Option B: Use Environment Variables
Set these environment variables:
```bash
export GEMINI_API_KEY="AIzaSyAyjXpZzIpPmTRnqk5mPf1BbE-ECOsXErI"
export GEMINI_MODEL="gemini-2.0-flash"
export GEMINI_ENABLED="true"
export AI_PROVIDER="gemini"
```

### Step 4: Clear Cache (Optional but Recommended)
```bash
# Clear browser cache: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)

# Clear CakePHP cache
rm -rf tmp/cache/models/*
rm -rf tmp/cache/persistent/*
```

### Step 5: Test the Chatbot
1. Open the website in your browser
2. Look for a **blue floating button** in the bottom-right corner
3. Click it to open the chat
4. Type "hello" or "what cheeses do you have?"
5. You should get an AI-powered response!

## Troubleshooting

### Chatbot button not visible?
- Check browser console for JavaScript errors
- Make sure `webroot/js/copilot.js` exists
- Clear browser cache

### "Sorry, I had trouble reaching the server"?
- Check that the AI configuration is correct in `config/app_local.php`
- Verify the Gemini API key is set
- Check `logs/error.log` for detailed error messages

### Database errors?
- Run `bin/cake migrations status` to check migration status
- Run `bin/cake migrations migrate` to apply pending migrations

## Technical Details

### Current Gemini API Setup
- **API Version**: v1
- **Model**: gemini-2.0-flash
- **Endpoint**: `https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent`
- **Temperature**: 0.7
- **Max Tokens**: 500

### Fallback Behavior
If AI fails or is disabled, the chatbot falls back to a rule-based system that can still:
- Answer questions about products
- Search for cheese
- Show order information
- Provide delivery and payment info

## Commits
- `80c64af` - Fix AI chatbot: Update Gemini API to v1 and use gemini-2.0-flash model
- `9196885` - Add migration for order fields

---

**Last Updated**: October 13, 2025
**Author**: Team 202

