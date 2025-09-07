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
            // ★ 核心修正：显式持久化身份到 Session
            //   某些环境下如果不手动 setIdentity，下一跳请求拿不到 identity
            $this->Authentication->setIdentity($result->getData());

            $identity = $this->request->getAttribute('identity');
            // 如果上一句还没把 request attribute 刷新，可以直接用 $result->getData()
            $role     = strtolower((string)($identity?->get('role') ?? $result->getData()?->get('role') ?? ''));

            // 优先尊重 redirect 参数（AuthenticationService 的 queryParam）
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
            // 诊断分支（你原来的逻辑保留即可）
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
