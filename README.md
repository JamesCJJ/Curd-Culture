# Curd & Culture

Live demo: https://review.u25s2202.iedev.org/team202-app_fit3047/

**Curd & Culture** is a full-stack e-commerce web application for an artisan cheese shop. It lets customers browse products, manage carts, complete checkout with card payments, track orders, and get help from an AI-powered assistant—while giving administrators tools to manage inventory, deliveries, and site content.

Built as a team onboarding project (Monash UGIE), it demonstrates production-style patterns: MVC architecture, relational data modelling, third-party API integration, and accessible, responsive UI design.

---

## Problem it solves

Small food retailers often need more than a simple storefront: scheduled delivery, pickup options, order tracking, and fast customer support. Curd & Culture combines a branded shopping experience with operational workflows (fulfilment boards, Stripe payments) and an intelligent chatbot that answers product and order questions—reducing manual support load and improving conversion.

---

## Key features

### Customer-facing
- **Product catalog** — Browse cheeses with SEO-friendly URLs (`/products/:slug`)
- **Shopping cart & checkout** — Session-based cart, delivery slots, and pickup locations
- **Stripe payments** — Secure checkout via Stripe Checkout with webhook-confirmed orders
- **Customer dashboard** — Order history, profile, saved addresses, and “buy again”
- **AI Copilot chatbot** — Natural-language help for products, orders, and FAQs (Google Gemini with rule-based fallback)
- **Accessibility** — Adjustable font size, high-contrast mode, keyboard-friendly focus styles, and read-aloud (Web Speech API)

### Admin
- **Order & delivery management** — Daily delivery board grouped by time slots
- **Bulk operations** — Bulk status updates and move orders between dates/slots
- **CSV export** — Export deliveries for a selected day
- **CMS-style home content** — Editable hero, featured sections, and CTA copy via site settings

### Technical
- **Graceful AI degradation** — Chatbot always responds; falls back to keyword rules if the LLM is unavailable
- **Role-based access** — Separate customer and admin areas with authentication middleware
- **Database migrations** — Versioned schema via CakePHP Migrations

---

## Technologies used

| Layer | Stack |
|--------|--------|
| **Backend** | PHP 8.1+, [CakePHP 5](https://cakephp.org) |
| **Database** | MySQL / MariaDB |
| **Payments** | [Stripe](https://stripe.com) (Checkout + webhooks) |
| **AI** | Google Gemini API (optional OpenAI) |
| **Frontend** | HTML5, CSS3, vanilla JavaScript |
| **Auth** | `cakephp/authentication` (session + form login) |
| **Tooling** | Composer, PHPUnit, PHP_CodeSniffer, PHPStan |

---


## Prerequisites

- **PHP** ≥ 8.1 with extensions: `intl`, `mbstring`, `pdo_mysql`
- **Composer** 2.x
- **MySQL** or **MariaDB** 10.4+
- (Optional) **Stripe** account — test keys for payments
- (Optional) **Google Gemini** API key — for AI chatbot; works without it using rule-based replies

---

## Setup & installation

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd team202-curdculture

### 2. Install PHP dependencies
composer install

### 3. Configure the application
cp config/app_local.example.php config/app_local.php
cp config/.env.example config/.env   # optional, if using dotenv

4. Create the database
mysql -u root -p -e "CREATE DATABASE curdculture CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
Or import the reference schema:
mysql -u root -p curdculture < Database_schema/onboarding_db.sql

5. Run migrations
bin/cake migrations migrate

6. Set filesystem permissions
chmod -R 775 tmp logs

7. Start the development server
bin/cake server -H localhost -p 8765
Open http://localhost:8765 in your browser.

For Apache/Nginx, point the document root to the webroot/ directory.

Running tests

composer test
# or
vendor/bin/phpunit
