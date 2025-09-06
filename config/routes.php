<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    //  DashedRoute
    $routes->setRouteClass(DashedRoute::class);

    //  -> Pages::display('home')
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    //
    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->fallbacks(DashedRoute::class);
    });

    $routes->connect(
        '/contact-messages/export/prepare',
        ['controller'=>'ContactMessages','action'=>'exportPrepare'],
        ['_method'=>['GET','POST']]
    );
    $routes->connect(
        '/contact-messages/export/download/*',
        ['controller'=>'ContactMessages','action'=>'exportDownload'],
        ['_method'=>'GET']
    );

    $routes->connect('/login', ['controller' => 'Customers', 'action' => 'login']);
    $routes->connect('/register', ['controller' => 'Customers', 'action' => 'register']);
    $routes->fallbacks(\Cake\Routing\Route\DashedRoute::class);

    $routes->connect('/settings', ['controller' => 'Settings', 'action' => 'index']);

    // Admin
    $routes->prefix('Admin', function ($builder) {

        $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);


        $builder->connect('/', ['controller' => 'ContactMessages', 'action' => 'index']);

        $builder->fallbacks();
    });
};
