<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\DateTime;

class CustomersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        // CakePHP Authentication plugin
        $this->loadComponent('Authentication.Authentication');


        $this->Users = $this->fetchTable('Users');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);


        $this->Authentication->allowUnauthenticated(['login', 'register']);
    }

    /**
     * GET/POST /customers/login
     *  - admin     -> /admin/dashboard/index
     *  - customer  -> /customers/dashboard
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            /** @var \Authentication\Identity $identity */
            $identity = $this->request->getAttribute('identity');
            $role = (string)($identity->get('role') ?? '');


            $redirect = $this->request->getQuery('redirect');

            if ($role === 'admin') {
                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Dashboard',
                    'action' => 'index',
                ]);
            }


            if (!empty($redirect)) {
                return $this->redirect($redirect);
            }
            return $this->redirect(['action' => 'dashboard']);
        }

        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Invalid email or password, please try again.');
        }
    }

    /**
     * GET/POST /customers/register
     */
    public function register()
    {
        $this->request->allowMethod(['get', 'post']);

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();


            $data['role'] = 'customer';


            // $data['status'] = 'active';
            // $data['created'] = DateTime::now();


            $user = $this->Users->patchEntity($user, $data, [
                'fields' => ['name', 'email', 'password', 'role'],
                'validate' => 'default',
            ]);

            if ($this->Users->save($user)) {

                $this->Authentication->setIdentity($user);

                $this->Flash->success('Account created. Welcome!');
                return $this->redirect(['action' => 'dashboard']);
            }

            $this->Flash->error('Failed to create account. Please check the form.');
        }

        $this->set(compact('user'));
    }

    /**
     * GET /customers/dashboard
     */
    public function dashboard()
    {
        $this->request->allowMethod(['get']);


        $user = $this->request->getAttribute('identity');

        $this->set(compact('user'));
    }

    /**
     * GET /customers/logout
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success('Signed out successfully.');
        }
        return $this->redirect(['action' => 'login']);
    }
}
