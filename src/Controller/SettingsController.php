<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;

/**
 * Public-facing Settings (user preferences, no admin prefix)
 * Stores preferences in cookies so it works for guest users as well.
 */
class SettingsController extends AppController
{
    public function index()
    {
        $this->request->allowMethod(['get','post']);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $cookies = new CookieCollection([]);

            $expires = new \DateTimeImmutable('+180 days');
            $make = function (string $name, string $value) use ($expires) {
                return new Cookie(
                    $name,
                    $value,
                    $expires,
                    '/',
                    null,      // domain
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // secure
                    false,     // httpOnly (need readable in JS for a11y widgets)
                    'Lax'
                );
            };

            // Persist common public prefs
            if (isset($data['theme']))       $cookies = $cookies->add($make('pref_theme', (string)$data['theme']));
            if (isset($data['contrast']))    $cookies = $cookies->add($make('pref_contrast', (string)$data['contrast']));
            if (isset($data['font_scale']))  $cookies = $cookies->add($make('pref_font_scale', (string)$data['font_scale']));
            if (isset($data['language']))    $cookies = $cookies->add($make('pref_lang', (string)$data['language']));
            if (isset($data['email_optin'])) $cookies = $cookies->add($make('pref_email_optin', $data['email_optin'] ? '1' : '0'));
            if (isset($data['cookie_consent'])) $cookies = $cookies->add($make('pref_cookie_consent', $data['cookie_consent'] ? '1' : '0'));

            $this->response = $this->response->withCookieCollection($cookies);
            $this->Flash->success('Preferences saved.');
            return $this->redirect($this->request->getRequestTarget());
        }

        // Read current prefs from cookies (fallback defaults)
        $c = $this->request->getCookieParams();
        $prefs = [
            'theme'       => $c['pref_theme']        ?? 'auto',        // auto | light | dark
            'contrast'    => $c['pref_contrast']     ?? 'normal',      // normal | high
            'font_scale'  => $c['pref_font_scale']   ?? '1.0',         // 0.9 - 1.25
            'language'    => $c['pref_lang']         ?? 'en',          // en / zh / etc.
            'email_optin' => ($c['pref_email_optin'] ?? '1') === '1',
            'cookie_consent' => ($c['pref_cookie_consent'] ?? '0') === '1',
        ];

        $this->set(compact('prefs'));
    }
}
