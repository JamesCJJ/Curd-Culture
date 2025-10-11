<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;           // ← read keys from app_local.php (Security.recaptcha)
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
        // Allow unauthenticated access
        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }

    /**
     * Verify Google reCAPTCHA v2 (checkbox) using the same approach as ContactMessagesController.
     */
    private function verifyRecaptcha(?string $token): bool
    {
        $secret = (string)(Configure::read('Security.recaptcha.secret_key') ?? '');
        if ($secret === '' || empty($token)) {
            return false;
        }

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_POSTFIELDS     => http_build_query([
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $this->request->clientIp(),
            ]),
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);

        if (!$raw) {
            return false;
        }
        $json = json_decode($raw, true);
        return !empty($json['success']);
    }

    /**
     * GET/POST /users/login
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        // Pass site key to the view
        $this->set('siteKey', (string)Configure::read('Security.recaptcha.site_key'));

        // On POST, require captcha before Authentication
        if ($this->request->is('post')) {
            $token = (string)($this->request->getData('g-recaptcha-response') ?? '');
            if (!$this->verifyRecaptcha($token)) {
                $this->Authentication->logout(); // make sure no identity sticks
                $this->Flash->error('Captcha validation failed. Please check the box and try again.');
                return $this->render('login');
            }
        }

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

        if ($this->request->is('post') && (!$result || !$result->isValid())) {
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

            $errors = $user->getErrors();
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

    public function dashboard()
    {
        $this->request->allowMethod(['get']);
        $user = $this->request->getAttribute('identity');
        $this->set(compact('user'));
    }

    public function logout()
    {
        $this->Authentication->logout();
        $this->Flash->success('Signed out successfully.');
        return $this->redirect(['action' => 'login']);
    }
}
