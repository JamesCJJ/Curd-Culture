<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes): void {
    // Use dashed URLs by default, e.g. /my-controller/my-action
    $routes->setRouteClass(DashedRoute::class);

    // ─────────────────────────────────────────────────────────────
    // Public pages
    // ─────────────────────────────────────────────────────────────
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    // Copilot chatbot API
    // NOTE: restrict to POST to avoid accidental GETs and CSRF via links
    $routes->connect(
        '/copilot/talk',
        ['controller' => 'Copilot', 'action' => 'talk'],
        ['_method' => ['POST']]
    );

    // Auth shortcuts (nice clean URLs)
    $routes->connect('/login',           ['controller' => 'Users',    'action' => 'login']);
    $routes->connect('/register',        ['controller' => 'Users',    'action' => 'register']);
    $routes->connect('/forgot-password', ['controller' => 'Users',    'action' => 'forgotPassword']);
    $routes->connect('/reset-password',  ['controller' => 'Users',    'action' => 'resetPassword']);
    $routes->connect('/settings',        ['controller' => 'Settings', 'action' => 'index']);

    // Product listing & details (SEO-friendly)
    $routes->connect('/products', ['controller' => 'Products', 'action' => 'index']);
    $routes->connect(
        '/products/:key',
        ['controller' => 'Products', 'action' => 'show'],
        ['pass' => ['key'], 'key' => '[A-Za-z0-9\-]+', '_name' => 'products:show']
    );
    $routes->connect(
        '/products/view/:key',
        ['controller' => 'Products', 'action' => 'show'],
        ['pass' => ['key'], 'key' => '[A-Za-z0-9\-]+']
    );
    $routes->connect('/products/view', ['controller' => 'Products', 'action' => 'index']);

    // Articles removed: keep backward-compat redirect to home
    $routes->connect('/articles', ['controller' => 'Pages', 'action' => 'display', 'home']);
    $routes->connect('/articles/*', ['controller' => 'Pages', 'action' => 'display', 'home']);

    // Stripe checkout flow
    $routes->connect('/checkout/stripe',  ['controller' => 'Payments', 'action' => 'checkout'], ['_method' => 'POST']);
    $routes->connect('/checkout/success', ['controller' => 'Payments', 'action' => 'success']);
    $routes->connect('/checkout/cancel',  ['controller' => 'Payments', 'action' => 'cancel']);
    $routes->connect('/webhooks/stripe',  ['controller' => 'Webhooks', 'action' => 'stripe'], ['_method' => 'POST']);

    // Cart / Checkout (customer-facing)
    $routes->connect('/cart',              ['controller' => 'Cart', 'action' => 'index']);
    $routes->connect('/checkout',          ['controller' => 'Cart', 'action' => 'checkout']);
    $routes->connect('/checkout/complete', ['controller' => 'Cart', 'action' => 'complete']);

    // Customer dashboard
    $routes->connect('/dashboard', ['controller' => 'Customer', 'action' => 'index']);
    $routes->connect(
        '/dashboard/orders/:id',
        ['controller' => 'Customer', 'action' => 'orderDetails'],
        ['pass' => ['id'], 'id' => '[0-9]+']
    );
    $routes->connect('/dashboard/orders',   ['controller' => 'Customer', 'action' => 'orders']);
    $routes->connect('/dashboard/profile',  ['controller' => 'Customer', 'action' => 'profile']);
    $routes->connect('/dashboard/settings', ['controller' => 'Customer', 'action' => 'settings']);
    $routes->connect(
        '/dashboard/buy-again/:id',
        ['controller' => 'Customer', 'action' => 'buyAgain'],
        ['pass' => ['id'], 'id' => '[0-9]+']
    );

    // User preferences update (POST only)
    $routes->connect(
        '/prefs/update',
        ['controller' => 'Preferences', 'action' => 'update'],
        ['_method' => 'POST']
    );

    // Address management
    // IMPORTANT: destructive actions require POST/DELETE to avoid CSRF via GET
    $routes->connect('/dashboard/address/add', ['controller' => 'Customer', 'action' => 'addAddress']);
    $routes->connect(
        '/dashboard/address/edit/:id',
        ['controller' => 'Customer', 'action' => 'editAddress'],
        ['pass' => ['id'], 'id' => '[0-9]+']
    );
    $routes->connect(
        '/dashboard/address/delete/:id',
        ['controller' => 'Customer', 'action' => 'deleteAddress'],
        [
            'pass'    => ['id'],
            'id'      => '[0-9]+',
            '_method' => ['POST', 'DELETE'],
            '_name'   => 'dashboard:address_delete'
        ]
    );
    $routes->connect(
        '/dashboard/address/default/:id',
        ['controller' => 'Customer', 'action' => 'setDefaultAddress'],
        [
            'pass'    => ['id'],
            'id'      => '[0-9]+',
            '_method' => ['POST']
        ]
    );

    // Customer logout (adjust controller/action to your implementation)
    $routes->connect('/logout', ['controller' => 'Customer', 'action' => 'logout']);

    // ─────────────────────────────────────────────────────────────
    // Admin area
    // ─────────────────────────────────────────────────────────────
    $routes->prefix('Admin', function (RouteBuilder $builder) {
        $builder->connect('/login',  ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);

        // Admin landing page
        $builder->connect('/', ['controller' => 'ContactMessages', 'action' => 'index']);

        // CMS: Articles removed

        // Deliveries
        $builder->connect('/deliveries',             ['controller' => 'Deliveries', 'action' => 'index']);
        $builder->connect('/deliveries/bulk-update', ['controller' => 'Deliveries', 'action' => 'bulkUpdate'], ['_method' => 'POST']);
        $builder->connect('/deliveries/move',        ['controller' => 'Deliveries', 'action' => 'move'],        ['_method' => 'POST']);

        $builder->fallbacks(DashedRoute::class);
    });

    // Fallbacks for any other controllers/actions not explicitly connected above
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks(DashedRoute::class);
    });
};
