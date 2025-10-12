# AI Chatbot Training Guide

## Overview
The chatbot has been trained to handle common customer questions using a rule-based system. It recognizes keywords and patterns in user messages to provide helpful responses.

## Questions the Chatbot Can Handle

### 1. Delivery & Shipping Questions
**User might ask:**
- "How long does delivery take?"
- "Can I pick up my order?"
- "How much is shipping?"
- "Where do you deliver to?"
- "When will my order arrive?"

**Keywords recognized:** deliver, delivery, shipping, ship, arrive, arrival, pickup, pick-up, how long

**Bot's response:** Guides users to checkout where they can see specific delivery options and costs.

---

### 2. Product Ingredients & Dietary Information
**User might ask:**
- "Do you have any gluten-free cheese?"
- "Is the cheddar pasteurised?"
- "Do you sell vegan cheese?"
- "Are there any allergens?"
- "Is it lactose-free?"

**Keywords recognized:** gluten, ingredient, pasteuri, vegan, vegetarian, allergy, allergic, dietary, lactose

**Bot's response:** Directs users to product pages where full ingredient information is listed, and suggests contacting support for severe allergies.

---

### 3. Payment Methods
**User might ask:**
- "How can I pay?"
- "What payment methods do you accept?"
- "Can I use Amex?"
- "Do you take credit cards?"

**Keywords recognized:** pay, payment, card, credit, debit, amex, visa, mastercard, accept card

**Bot's response:** Confirms acceptance of all major credit cards via Stripe.

---

### 4. Contact & Support
**User might ask:**
- "How do I contact customer support?"
- "Can I change my order?"
- "How do I cancel my order?"
- "What's your phone number?"

**Keywords recognized:** contact, support, help, email, phone, reach, cancel, change order, modify order

**Bot's response:** Directs users to the Contact Us page for assistance.

---

### 5. Product Catalog/Inventory
**User might ask:**
- "What cheeses do you have?"
- "What do you sell?"
- "Show me your products"
- "What's in stock?"
- "Do you have dairy?"

**Keywords recognized:** cheese, cheeses, dairy, product, products, what (have/sell/offer/carry/stock)

**Bot's response:** Lists up to 6 products from the catalog and suggests how to search for specific items.

---

### 6. Order Status & History
**User might ask:**
- "Where is my order?"
- "What's my order status?"
- "Show me my orders"
- "Order 123" (specific order number)

**Keywords recognized:** my orders, order, where is my order, order status

**Bot's response:** 
- If **not logged in**: Shows privacy message directing them to log in
- If **logged in**: Shows their recent orders with status and totals

---

### 7. Product Search
**User might ask:**
- "Search for cheddar"
- "Find gouda"
- "Do you have brie?"
- "Show me blue cheese"
- Just "cheddar" (standalone cheese name)
- Just "brie"
- "aged gouda"
- "aged-gouda-18m" (product slug)
- "goat feta"

**Keywords recognized:** search, find, have, show, OR standalone cheese type names (cheddar, brie, gouda, mozzarella, feta, blue, camembert, parmesan, aged, swiss, gruyere, provolone, ricotta, halloumi, manchego, and many more)

**Bot's response:** 
- **Single product found**: Automatically provides product link with price - **NO EXTRA STEP NEEDED!**
- **Multiple products found**: Lists all matches and asks which one they want to see
- **No products found**: Provides helpful guidance on how to search

**Streamlined Interaction Flow:**
1. User types cheese name (e.g., "Goat Feta") → Bot finds product
2. Bot responds: "I found Goat Feta priced at A$7.40. Here's the link to view the full product details. Click to see information about ingredients, pairings, and more!"
3. Product page opens automatically - **DONE!** ✨

**For Multiple Matches:**
1. User types "gouda" → Bot finds multiple products
2. Bot responds: "I found 2 matching products: Aged Gouda 18M, Mature Gouda. Which one would you like to learn more about? Type the specific product name to see its details."
3. User types "Aged Gouda 18M" → Bot provides link immediately

**New Features:** 
1. **Smart Recognition**: The bot now automatically recognizes when someone types just a cheese name or product code (like "cheddar", "brie", or even "aged-gouda-18m") and will search for it without requiring keywords like "search" or "find". It intelligently detects:
   - Short messages (under 50 characters) containing cheese-related keywords
   - Hyphenated product codes (e.g., "aged-gouda-18m", "blue-vein-classic")
   - Excludes question words (what, how, when) to avoid false matches

2. **Dual Search**: The search now looks in BOTH the product name field AND the slug field, so users can type either:
   - Product name: "Aged Gouda 18M" 
   - Product slug: "aged-gouda-18m"
   - Partial matches: "gouda", "aged", "18m"

3. **Instant Links**: When a single product is found, the link is provided immediately - no need to type "yes" or "view"! This applies to ALL cheese searches.

4. **Professional Tone**: All responses are professional and conversational, providing helpful guidance

---

### 8. View Specific Product
**User might ask:**
- "View aged-gouda-18m" (using product slug)
- "Open cheddar"

**Keywords recognized:** view, open (followed by product slug)

**Bot's response:** Opens the specific product page.

---

## Training Tips

### 1. Monitor Real Usage
Add logging to track what users actually ask:
```php
// In talk() function
file_put_contents(LOGS . 'chatbot_queries.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
```

Review this log weekly to identify:
- Questions users ask that aren't being handled
- Common patterns you can add rules for
- Confusing responses that need improvement

### 2. Iterative Improvement
- Start with the most common questions (already done!)
- Add new rules based on actual user behavior
- Test each rule thoroughly before deployment
- Keep responses clear and action-oriented

### 3. Rule Ordering Matters
Rules are checked in order from top to bottom. More specific rules should come before general ones:
1. Delivery/shipping (specific)
2. Ingredients/dietary (specific)
3. Payments (specific)
4. Contact (specific)
5. Product inventory (general)
6. Orders (general)
7. Product search (very general)
8. Fallback (catches everything else)

### 4. Keyword Selection
When adding new rules:
- Include common variations (e.g., "pasteuri" catches both "pasteurised" and "pasteurized")
- Account for typos in critical words
- Use word boundaries `\b` to avoid false matches
- Test with real user queries

### 5. Response Quality
Good chatbot responses should:
- Be conversational and friendly
- Provide actionable next steps
- Guide users to the right place
- Be honest about limitations
- Stay on-brand with your business

---

## Future Enhancements

### Short-term (Easy wins)
- Add more dietary keywords (organic, raw milk, goat cheese, etc.)
- Handle common typos
- Add business hours information
- Add return/refund policy responses

### Medium-term
- Implement proper session context (remember previous questions)
- Add suggested follow-up questions
- Create rich responses with product images
- Track conversation satisfaction

### Long-term
- Full AI integration with GPT/Gemini for natural language understanding
- Multi-turn conversations with memory
- Personalized recommendations based on order history
- Integration with customer support ticketing system

---

## Testing Checklist

Test these questions regularly:
- [ ] "What cheeses do you have?"
- [ ] "How long does delivery take?"
- [ ] "Do you have gluten-free cheese?"
- [ ] "How can I pay?"
- [ ] "How do I contact support?"
- [ ] "Show me my orders" (logged in and logged out)
- [ ] "Search for cheddar"
- [ ] Just "cheddar" (standalone cheese name - NEW!)
- [ ] Just "brie" (standalone cheese name - NEW!)
- [ ] "aged gouda" (cheese name with modifier - NEW!)
- [ ] "aged-gouda-18m" (hyphenated product code - NEW!)
- [ ] "blue-vein-classic" (hyphenated product name - NEW!)
- [ ] Random unrelated question (should get fallback)

---

## Common Issues & Solutions

**Issue:** Bot responds to everything with product search
**Solution:** Check rule ordering - specific rules should come before general search

**Issue:** Bot doesn't recognize variations of a word
**Solution:** Expand the regex pattern with more variations (e.g., pickup|pick-up|pick up)

**Issue:** Bot gives wrong information
**Solution:** Review and update the response text, ensure it matches current business policies

**Issue:** Users ask questions outside the bot's scope
**Solution:** Improve fallback message to guide users to the right resource

---

Last updated: October 10, 2025

