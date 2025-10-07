<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // Home
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    // Copilot chatbot API (accept POST)
    $routes->connect('/copilot/talk', ['controller' => 'Copilot', 'action' => 'talk']);

    // Auth shortcuts
    $routes->connect('/login',    ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/register', ['controller' => 'Users', 'action' => 'register']);
    $routes->connect('/settings', ['controller' => 'Settings', 'action' => 'index']);

    // Products
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

    // Stripe
    $routes->connect('/checkout/stripe',  ['controller' => 'Payments', 'action' => 'checkout'], ['_method' => 'POST']);
    $routes->connect('/checkout/success', ['controller' => 'Payments', 'action' => 'success']);
    $routes->connect('/checkout/cancel',  ['controller' => 'Payments', 'action' => 'cancel']);
    $routes->connect('/webhooks/stripe',  ['controller' => 'Webhooks', 'action' => 'stripe'], ['_method' => 'POST']);

    // Cart / Checkout
    $routes->connect('/cart',              ['controller' => 'Cart', 'action' => 'index']);
    $routes->connect('/checkout',          ['controller' => 'Cart', 'action' => 'checkout']);
    $routes->connect('/checkout/complete', ['controller' => 'Cart', 'action' => 'complete']);

    // Customer Dashboard
    $routes->connect('/dashboard', ['controller' => 'Customer', 'action' => 'index']);
    $routes->connect('/dashboard/orders/:id',   ['controller' => 'Customer', 'action' => 'orderDetails'], ['pass' => ['id'], 'id' => '[0-9]+']);
    $routes->connect('/dashboard/orders',       ['controller' => 'Customer', 'action' => 'orders']);
    $routes->connect('/dashboard/profile',      ['controller' => 'Customer', 'action' => 'profile']);
    $routes->connect('/dashboard/settings',     ['controller' => 'Customer', 'action' => 'settings']);
    $routes->connect('/dashboard/buy-again/:id',['controller' => 'Customer', 'action' => 'buyAgain'], ['pass' => ['id'], 'id' => '[0-9]+']);
    $routes->connect('/dashboard/address/add',            ['controller' => 'Customer', 'action' => 'addAddress']);
    $routes->connect('/dashboard/address/edit/:id',       ['controller' => 'Customer', 'action' => 'editAddress'], ['pass' => ['id'], 'id' => '[0-9]+']);
    $routes->connect('/dashboard/address/delete/:id',     ['controller' => 'Customer', 'action' => 'deleteAddress'], ['pass' => ['id'], 'id' => '[0-9]+']);
    $routes->connect('/dashboard/address/default/:id',    ['controller' => 'Customer', 'action' => 'setDefaultAddress'], ['pass' => ['id'], 'id' => '[0-9]+']);
    $routes->connect('/logout', ['controller' => 'Customer', 'action' => 'logout']);

    // Admin
    $routes->prefix('Admin', function (RouteBuilder $builder) {
        $builder->connect('/login',  ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/',       ['controller' => 'ContactMessages', 'action' => 'index']);

        // Deliveries board + actions
        $builder->connect('/deliveries',             ['controller' => 'Deliveries', 'action' => 'index']);
        $builder->connect('/deliveries/bulk-update', ['controller' => 'Deliveries', 'action' => 'bulkUpdate'], ['_method' => 'POST']);
        $builder->connect('/deliveries/move',        ['controller' => 'Deliveries', 'action' => 'move'],        ['_method' => 'POST']);

        $builder->fallbacks(DashedRoute::class);
    });

    // Fallbacks
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks(DashedRoute::class);
    });
};
