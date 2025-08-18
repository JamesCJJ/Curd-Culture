<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as Base;
use Cake\Event\EventInterface;

class AppController extends Base
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $controller = (string)$this->request->getParam('controller');
        $action     = (string)$this->request->getParam('action');


        if ($controller === 'Users' && in_array($action, ['login', 'logout'], true)) {
            return;
        }


        $user = $this->request->getSession()->read('Auth.AdminUser');
        if (empty($user) || (($user['role'] ?? '') !== 'admin')) {
            $this->Flash->error('Admin only. Please login.');

            $event->setResult($this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Users',
                'action' => 'login',
            ]));
            return;
        }
    }
}
