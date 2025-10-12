<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Client;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Throwable;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('AppPrefs');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions([
            'login', 'register', 'forgotPassword', 'resetPassword',
        ]);
    }


    private function sendResetCodeEmail(object $user, string $code, int $ttlMinutes): void
    {
        $mailer = new Mailer('default');
        $mailer
            ->setTo($user->email)
            ->setEmailFormat('both')
            ->setSubject('Your password reset code')
            ->viewBuilder()
            ->setTemplate('password_reset')
            ->setVars([
                'user'       => $user,
                'code'       => $code,
                'ttlMinutes' => $ttlMinutes,
                'appName'    => 'Curd & Culture',
            ]);
        $mailer->deliver();
    }


    private function issueResetCode(object $user): void
    {
        $ttl    = (int)(Configure::read('PasswordReset.code_ttl_minutes') ?? 10);
        $code   = (string)random_int(100000, 999999);
        $hasher = new DefaultPasswordHasher();

        $user->reset_code_hash = $hasher->hash($code);
        $user->reset_expires   = FrozenTime::now()->addMinutes($ttl);
        $user->reset_attempts  = 0;

        $Users = $this->fetchTable('Users');
        $Users->saveOrFail($user);

        $this->sendResetCodeEmail($user, $code, $ttl);
    }

    /**
     * Google reCAPTCHA（v2 checkbox）
     *
     *   Security.recaptcha.site_key
     *   Security.recaptcha.secret_key
     * Security.recaptcha.domain = "www.google.com" 或 "www.recaptcha.net"
     */
    private function verifyRecaptcha(): bool
    {
        $secret = (string)(Configure::read('Security.recaptcha.secret_key') ?? '');
        if ($secret === '') {

            return true;
        }

        $token = (string)($this->request->getData('g-recaptcha-response') ?? '');
        if ($token === '') {
            return false;
        }

        $domain = (string)(Configure::read('Security.recaptcha.domain') ?? 'www.google.com');

        try {
            $http = new Client(['timeout' => 5]);
            $res  = $http->post("https://{$domain}/recaptcha/api/siteverify", [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $this->request->clientIp(),
            ]);
            if (!$res->isOk()) {
                return false;
            }
            $json = (array)($res->getJson() ?? []);
            return !empty($json['success']);
        } catch (Throwable $e) {
            $this->log('reCAPTCHA verify error: ' . $e->getMessage(), 'warning');
            return false;
        }
    }

    /**
     * GET/POST /users/forgot-password
     */
    public function forgotPassword()
    {
        $this->request->allowMethod(['get', 'post']);

        if ($this->request->is('post')) {

            // if (!$this->verifyRecaptcha()) { $this->Flash->error('reCAPTCHA verification failed.'); return; }

            $email = trim((string)$this->request->getData('email'));

            $Users = $this->fetchTable('Users');
            $user  = $Users->find()->where(['email' => $email])->first();

            if ($user) {
                try {
                    $this->issueResetCode($user);
                } catch (Throwable $e) {
                    $this->log('Password reset issue for ' . $email . ': ' . $e->getMessage(), 'error');
                }
            }

            $this->Flash->success('If the email exists, a verification code has been sent.');


            $this->set('email', $email);
            $this->setRequest(
                $this->getRequest()->withMethod('get')->withQueryParams(['email' => $email])
            );

            return $this->render('reset_password');
        }
    }

    /**
     * GET/POST /users/reset-password
     */
    public function resetPassword()
    {
        $this->request->allowMethod(['get', 'post']);

        $email = (string)($this->request->getQuery('email') ?? $this->request->getData('email') ?? '');
        $this->set('email', $email);

        if ($this->request->is('post')) {
            $Users = $this->fetchTable('Users');
            $user  = $Users->find()->where(['email' => $email])->first();

            if (!$user) {
                $this->Flash->error('Invalid code or code expired. Please request a new one.');
                return $this->redirect(['action' => 'forgotPassword']);
            }


            $now     = FrozenTime::now();
            $expires = $user->reset_expires;
            if ($expires !== null && !($expires instanceof FrozenTime)) {
                $expires = new FrozenTime($expires);
            }
            $maxAttempts = (int)(Configure::read('PasswordReset.max_attempts') ?? 5);

            if (
                (int)$user->reset_attempts >= $maxAttempts ||
                !$expires ||
                $now->getTimestamp() > $expires->getTimestamp()
            ) {
                $this->Flash->error('Invalid code or code expired. Please request a new one.');
                return $this->redirect(['action' => 'forgotPassword']);
            }

            $code     = (string)($this->request->getData('code') ?? '');
            $password = (string)($this->request->getData('password') ?? '');
            $confirm  = (string)($this->request->getData('confirm_password') ?? '');

            if ($password === '' || $password !== $confirm) {
                $this->Flash->error('Passwords do not match.');
                return;
            }

            $hasher  = new DefaultPasswordHasher();
            $isValid = $user->reset_code_hash && $hasher->check($code, $user->reset_code_hash);

            if (!$isValid) {
                $user->reset_attempts = (int)$user->reset_attempts + 1;
                $Users->save($user);
                $this->Flash->error('Invalid code. Please try again.');
                return;
            }


            $user->password        = $password;
            $user->reset_code_hash = null;
            $user->reset_expires   = null;
            $user->reset_attempts  = 0;

            if ($Users->save($user)) {
                $this->Flash->success('Your password has been reset. You can now sign in.');
                return $this->redirect(['action' => 'login']);
            }

            $this->Flash->error('Failed to reset password. Please try again.');
        }
    }

    public function login()
    {
        $this->request->allowMethod(['get','post']);


        $this->set('siteKey', (string)(Configure::read('Security.recaptcha.site_key') ?? ''));


        if ($this->request->is('post')) {
            if (!$this->verifyRecaptcha()) {
                $this->Flash->error('reCAPTCHA verification failed. Please try again.');
                return;
            }
        }

        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {

            $this->request->getSession()->renew();


            if ($this->components()->has('AppPrefs')) {
                $this->AppPrefs->onLogin();
            }


            $redirect = $this->Authentication->getLoginRedirect();
            if ($redirect) {
                return $this->redirect($redirect);
            }


            $identity = $this->request->getAttribute('identity');
            $role = strtolower((string)($identity->role ?? ''));
            if ($role === 'admin') {
                return $this->redirect(['prefix'=>'Admin','controller'=>'Dashboard','action'=>'index']);
            }
            return $this->redirect(['controller'=>'Customer','action'=>'index']);
        }

        if ($this->request->is('post') && (!$result || !$result->isValid())) {
            $this->Flash->error('Invalid email or password, please try again.');
        }
    }

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
            $this->Flash->error($flat ? 'Failed to create account: ' . implode(' | ', $flat)
                : 'Failed to create account. Please check the form.');
        }

        $this->set(compact('user'));
    }

    public function logout()
    {

        $this->request->allowMethod(['post']);

        if ($this->components()->has('AppPrefs')) {
            $this->AppPrefs->onLogout();
        }
        $result = $this->Authentication->logout();
        return $this->redirect($result ?? '/');
    }
}
