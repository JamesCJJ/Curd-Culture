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

    // Admin
    $routes->prefix('Admin', function (RouteBuilder $builder) {

        $builder->connect('/', ['controller' => 'Users', 'action' => 'login']);
        $builder->fallbacks(DashedRoute::class);
    });
};
