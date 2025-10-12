<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;
use Cake\Mailer\Mailer;
use Cake\I18n\FrozenTime;
use Authentication\PasswordHasher\DefaultPasswordHasher;

class SettingsController extends AppController
{
    /**
     * Send the one-time reset code via email (same template as public flow).
     */
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

    /**
     * Create & persist the reset code (hashed), expiry, attempts.
     */
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
     * Admin Settings (prefs + optional “send reset code” action)
     */
    public function index()
    {
        $this->request->allowMethod(['get', 'post']);

        // current admin identity (for showing email & sending reset)
        $identity   = $this->request->getAttribute('identity');
        $adminEmail = (string)($identity->email ?? '');
        $this->set('adminEmail', $adminEmail);

        if ($this->request->is('post')) {
            $data = (array)$this->request->getData();
            $action = (string)($data['action'] ?? 'prefs');

            // ---- A) Handle “send reset code” --------------------------------
            if ($action === 'request_reset') {
                try {
                    $Users = $this->fetchTable('Users');
                    $user  = $Users->find()->where(['id' => $identity->id])->firstOrFail();
                    $this->issueResetCode($user);

                    $this->Flash->success('A verification code has been emailed to ' . $adminEmail . '.');
                } catch (\Throwable $e) {
                    $this->log('Admin reset code failed: ' . $e->getMessage(), 'error');
                    $this->Flash->error('Failed to send the reset code. Please try again.');
                }
                // stay on the page
                return $this->redirect($this->request->getRequestTarget());
            }

            // ---- B) Handle preference save (existing behavior) --------------
            $cookies = new CookieCollection([]);
            $expires = new \DateTimeImmutable('+180 days');
            $make = function (string $name, string $value) use ($expires) {
                return new Cookie(
                    $name,
                    $value,
                    $expires,
                    '/',
                    null,
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    false,
                    'Lax'
                );
            };

            if (isset($data['theme']))          $cookies = $cookies->add($make('pref_theme', (string)$data['theme']));
            if (isset($data['contrast']))       $cookies = $cookies->add($make('pref_contrast', (string)$data['contrast']));
            if (isset($data['font_scale']))     $cookies = $cookies->add($make('pref_font_scale', (string)$data['font_scale']));
            if (isset($data['language']))       $cookies = $cookies->add($make('pref_lang', (string)$data['language']));
            if (isset($data['email_optin']))    $cookies = $cookies->add($make('pref_email_optin', $data['email_optin'] ? '1' : '0'));
            if (isset($data['cookie_consent'])) $cookies = $cookies->add($make('pref_cookie_consent', $data['cookie_consent'] ? '1' : '0'));

            $this->response = $this->response->withCookieCollection($cookies);
            $this->Flash->success('Preferences saved.');
            return $this->redirect($this->request->getRequestTarget());
        }

        // Prefs → view
        $c = $this->request->getCookieParams();
        $prefs = [
            'theme'          => $c['pref_theme']        ?? 'light',
            'contrast'       => $c['pref_contrast']     ?? 'normal',
            'font_scale'     => $c['pref_font_scale']   ?? '1.0',
            'language'       => $c['pref_lang']         ?? 'en',
            'email_optin'    => ($c['pref_email_optin'] ?? '1') === '1',
            'cookie_consent' => ($c['pref_cookie_consent'] ?? '0') === '1',
        ];
        $this->set(compact('prefs'));
    }
}
