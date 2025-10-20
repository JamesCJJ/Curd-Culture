<?php
declare(strict_types=1);

namespace App;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

// Application entry point: HTTP middleware pipeline + authentication setup.
// - middleware(): defines the global middleware order.
// - getAuthenticationService(): configures identifiers/authenticators and login redirect.

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Global middleware order (request inwards):
        // 1) ErrorHandler  – consistent error pages/logging
        // 2) Routing       – resolve controller/action/params
        // 3) BodyParser    – parse JSON/form bodies
        // 4) Authentication– attach user identity (if any)
        return $middlewareQueue
            ->add(new ErrorHandlerMiddleware())
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add(new AuthenticationMiddleware($this));

    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // Canonical login URL (non-prefixed); used for unauthenticated redirects.
        $loginUrl = Router::url(['prefix' => false, 'controller' => 'Users', 'action' => 'login'], false);
        // Redirect unauthenticated users and preserve intended URL via ?redirect=
        $service = new AuthenticationService([
            'unauthenticatedRedirect' => $loginUrl,
            'queryParam'              => 'redirect',
        ]);


        $fields = ['username' => 'email', 'password' => 'password'];
        // Identifier: look up user via ORM and verify password hash.
        $service->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
            ],
        ]);

        // Authenticator chain:
        // 1) Session: restore existing logins (browser flows).

        $service->loadAuthenticator('Authentication.Session');


        $service->loadAuthenticator('Authentication.Form', [
            'fields'   => $fields,
            'loginUrl' => $loginUrl,

        ]);

        return $service;
    }
}
