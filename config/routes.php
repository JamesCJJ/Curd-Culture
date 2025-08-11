<?php
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

return static function (\Cake\Routing\RouteBuilder $routes) {
    $routes->setRouteClass(DashedRoute::class);

    // Landing page -> Pages::home
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    // Default routes
    $routes->scope('/', function ($builder) {
        $builder->fallbacks();
    });

    // Admin prefix
    $routes->prefix('Admin', function ($builder) {
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->fallbacks();
    });
};
