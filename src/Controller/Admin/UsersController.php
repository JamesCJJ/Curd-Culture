<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

class UsersController extends AppController
{
    public function login()
    {
        if ($this->request->is('post')) {
            $email    = (string)$this->request->getData('email');
            $password = (string)$this->request->getData('password');

            $Users = $this->fetchTable('Users');
            $user = $Users->find()
                ->select(['id', 'email', 'password', 'role'])
                ->where(['email' => $email])
                ->first();

            if ($user && password_verify($password, (string)$user->password) && $user->role === 'admin') {

                $this->request->getSession()->write('Auth.AdminUser', [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'role'  => $user->role,
                ]);

                $this->Flash->success('Welcome back!');
                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Dashboard',
                    'action' => 'index'
                ]);
            }

            $this->Flash->error('Invalid credentials or not an admin.');
        }
    }

    public function logout()
    {
        $this->request->getSession()->delete('Auth.AdminUser');
        $this->Flash->success('You have been logged out.');
        return $this->redirect(['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']);
    }
}
