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
            return $this->redirect(['controller' => 'Customer', 'action' => 'index']);
        }

        // If authentication failed and this was a POST request, show error
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Invalid email or password, please try again.');
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


            $data['role']   = 'customer';
            $data['status'] = $data['status'] ?? 'active';

            $user = $Users->patchEntity($user, $data);

            if ($Users->save($user)) {
                $this->Authentication->setIdentity($user);
                $this->Flash->success('Account created successfully. Welcome!');
                return $this->redirect(['controller' => 'Customer', 'action' => 'index']);
            }


            $errors = $user->getErrors(); // array
            $flat   = [];
            foreach ($errors as $field => $msgs) {
                foreach ((array)$msgs as $msg) {
                    $flat[] = sprintf('%s: %s', ucfirst($field), $msg);
                }
            }
            if ($flat) {
                $this->Flash->error('Failed to create account: ' . implode(' | ', $flat));
            } else {
                $this->Flash->error('Failed to create account. Please check the form.');
            }
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
