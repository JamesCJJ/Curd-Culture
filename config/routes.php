<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // Home
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

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


    $routes->connect(
        '/products/view',
        ['controller' => 'Products', 'action' => 'index']
    );

    // Cart / Checkout
    $routes->connect('/cart',              ['controller' => 'Cart', 'action' => 'index']);
    $routes->connect('/checkout',          ['controller' => 'Cart', 'action' => 'checkout']);
    $routes->connect('/checkout/complete', ['controller' => 'Cart', 'action' => 'complete']);

    // Fallbacks
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks(DashedRoute::class);
    });

    // Admin
    $routes->prefix('Admin', function (RouteBuilder $builder) {
        $builder->connect('/login',  ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/',       ['controller' => 'ContactMessages', 'action' => 'index']);
        $builder->fallbacks(DashedRoute::class);
    });
};
