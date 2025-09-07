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

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // 顺序：Error -> Routing -> BodyParser -> Authentication
        return $middlewareQueue
            ->add(new ErrorHandlerMiddleware())
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add(new AuthenticationMiddleware($this));
        // 如需 CSRF，可在 Routing 之后、Authentication 之前添加：
        // ->add(new \Cake\Http\Middleware\CsrfProtectionMiddleware())
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // 用 Router 生成“包含 base path”的登录路径，避免部署在子目录时匹配失败
        $loginUrl = Router::url(['prefix' => false, 'controller' => 'Users', 'action' => 'login'], false);

        $service = new AuthenticationService([
            'unauthenticatedRedirect' => $loginUrl,
            'queryParam'              => 'redirect',
        ]);

        // 标识器：使用 Users 表，email/password 字段
        $fields = ['username' => 'email', 'password' => 'password'];

        $service->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
            ],
        ]);

        // 先读 Session，再走 Form
        $service->loadAuthenticator('Authentication.Session');

        // FormAuthenticator 也使用刚刚生成的 loginUrl
        $service->loadAuthenticator('Authentication.Form', [
            'fields'   => $fields,
            'loginUrl' => $loginUrl, // 这里很关键：必须与实际访问路径完全一致（含 base）
            // 也可以用数组 URL：'loginUrl' => ['prefix'=>false,'controller'=>'Users','action'=>'login']
        ]);

        return $service;
    }
}
