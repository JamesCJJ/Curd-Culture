<?php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Throwable;

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
        $this->Authentication->addUnauthenticatedActions([
            'login', 'register', 'forgotPassword', 'resetPassword',
        ]);
    }

    /** Send the one-time code via email */
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

    /** Create & persist the reset code (hashed), expiry, attempts */
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
     * GET/POST /users/forgot-password
     * Direct-render: after POST, immediately show reset form (no redirect).
     */
    public function forgotPassword()
    {
        $this->request->allowMethod(['get', 'post']);

        if ($this->request->is('post')) {
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

            // Show reset page right away
            $this->set('email', $email);
            $this->request = $this->request->withMethod('get')->withQueryParams(['email' => $email]);

            return $this->render('reset_password');
        }
    }

    /**
     * GET/POST /users/reset-password
     * Verify code and set the new password.
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

            // ---- SAFE expiry check (no gt()) ----
            $now     = FrozenTime::now();
            $expires = $user->reset_expires;
            // Normalize to FrozenTime if needed
            if ($expires !== null && !($expires instanceof FrozenTime)) {
                $expires = new FrozenTime($expires);
            }

            $maxAttempts = (int)(Configure::read('PasswordReset.max_attempts') ?? 5);

            if (
                (int)$user->reset_attempts >= $maxAttempts ||
                !$expires ||
                $now->getTimestamp() > $expires->getTimestamp()   // ← compare timestamps
            ) {
                $this->Flash->error('Invalid code or code expired. Please request a new one.');

                return $this->redirect(['action' => 'forgotPassword']);
            }
            // -------------------------------------

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

            // Update password & clear reset fields
            $user->password        = $password;   // hashed by ORM
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
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $identity = $result->getData();

            $this->loadComponent('AppPrefs');


            $this->response = $this->AppPrefs->clearPrefCookies($this->response);


            $Users = $this->fetchTable('Users');
            $user  = $Users->get((int)$identity->get('id'));
            $this->response = $this->AppPrefs->withPrefCookies($this->response, $user);


            $redirect = (string)$this->request->getQuery('redirect', '');
            if ($redirect !== '') {
                return $this->redirect($redirect);
            }

            $role = strtolower((string)($identity->get('role') ?? ''));
            if ($role === 'admin') {
                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']);
            }
            if ($role === 'customer') {
                return $this->redirect(['controller' => 'Customer', 'action' => 'index']);
            }
            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
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
        $this->Authentication->logout();

        $this->loadComponent('AppPrefs');
        // 立即下发“过期” Set-Cookie 响应头
        $this->response = $this->AppPrefs->clearPrefCookies($this->response);

        $this->Flash->success('Signed out.');
        return $this->redirect(['action' => 'login']);
    }

}
