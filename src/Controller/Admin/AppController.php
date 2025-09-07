<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as Base;
use Cake\Event\EventInterface;

class AppController extends Base
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        if ((string)$this->request->getParam('controller') === 'Error') {
            return;
        }

        $identity = $this->request->getAttribute('identity');
        $isAdmin = false;

        if ($identity) {
            $role = strtolower((string)($identity->get('role') ?? ''));
            $isAdmin = ($role === 'admin');
        }

        if (!$isAdmin) {
            $legacy = $this->request->getSession()->read('Auth.AdminUser');
            if (is_array($legacy) && strtolower((string)($legacy['role'] ?? '')) === 'admin') {
                $isAdmin = true;
            }
        }

        if ($isAdmin) {
            return;
        }

        $target = $this->request->getRequestTarget();
        $acceptsJson = $this->request->is('ajax')
            || strpos((string)$this->request->getHeaderLine('Accept'), 'application/json') !== false;

        if ($acceptsJson) {
            $event->setResult(
                $this->response
                    ->withStatus(401, 'Unauthorized')
                    ->withType('application/json')
                    ->withStringBody(json_encode(['error' => 'Admin only. Please sign in.']))
            );
            return;
        }

        $this->Flash->error('Admin only. Please sign in.');

        $event->setResult($this->redirect([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login',
            '?' => ['redirect' => $target],
        ]));
    }
}
