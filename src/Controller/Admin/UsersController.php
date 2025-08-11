<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Allow unauthenticated access to login
        $this->Authentication->addUnauthenticatedActions(['login']);
    }

    public function login()
    {
        $this->request->allowMethod(['get','post']);
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $target = $this->request->getQuery('redirect', '/admin');
            return $this->redirect($target);
        }
        if ($this->request->is('post') && (!$result || !$result->isValid())) {
            $this->Flash->error(__('Invalid email or password'));
        }
    }

    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect('/admin/users/login');
    }
}
