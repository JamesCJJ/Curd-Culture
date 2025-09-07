<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }

    /**
     * GET/POST /users/login
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $this->Authentication->setIdentity($result->getData());

            $identity = $this->request->getAttribute('identity');
            $role     = strtolower((string)($identity?->get('role') ?? $result->getData()?->get('role') ?? ''));

            $redirect = $this->request->getQuery('redirect');
            if (!empty($redirect)) {
                return $this->redirect($redirect);
            }

            if ($role === 'admin') {
                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']);
            }
            return $this->redirect(['action' => 'dashboard']);
        }

        if ($this->request->is('post')) {
            $email = trim((string)$this->request->getData('email'));
            $pwd   = (string)$this->request->getData('password');

            $Users = $this->fetchTable('Users');
            $u = $Users->find()->select(['id','email','password','role'])->where(['email' => $email])->first();

            if (!$u) {
                $this->Flash->error('Invalid email or password, please try again. (No such email)');
            } elseif (!password_verify($pwd, (string)$u->password)) {
                $this->Flash->error('Invalid email or password, please try again. (Password mismatch)');
            } else {
                $this->Flash->error('Invalid email or password, please try again. (Auth chain not matching)');
            }
        }
    }


    /**
     * GET/POST /users/register
     */
    public function register()
    {
        $this->request->allowMethod(['get', 'post']);
        $Users = $this->fetchTable('Users');
        $user  = $Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();
            $data['role'] = 'customer';

            $user = $Users->patchEntity($user, $data, [
                'fields' => ['name', 'email', 'password', 'role'],
                'validate' => 'default',
            ]);

            if ($Users->save($user)) {
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Account created successfully. Welcome!');
                return $this->redirect(['action' => 'dashboard']);
            }

            $this->Flash->error('Failed to create account. Please check the form.');
        }

        $this->set(compact('user'));
    }

    /**
     * GET /users/dashboard
     */
    public function dashboard()
    {
        $this->request->allowMethod(['get']);
        $user = $this->request->getAttribute('identity');
        $this->set(compact('user'));
    }

    /**
     * GET /users/logout
     */
    public function logout()
    {
        $this->Authentication->logout();
        $this->Flash->success('Signed out successfully.');
        return $this->redirect(['action' => 'login']);
    }
}
