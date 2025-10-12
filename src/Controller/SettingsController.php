<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;

/**
 * Public Settings page (no admin prefix)
 * - GET: render current preferences (from cookies; if missing, fallback to DB/defaults)
 * - POST: save to DB if logged in, and always refresh cookies
 */
class SettingsController extends AppController
{
    public function index()
    {
        $this->request->allowMethod(['get', 'post']);

        // Helper to clamp font scale
        $clamp = static function (float $v, float $lo, float $hi): float {
            return max($lo, min($hi, $v));
        };

        if ($this->request->is('post')) {
            $d = (array)$this->request->getData();

            $theme       = (string)($d['theme']       ?? $d['pref_theme']      ?? 'auto');
            $contrast    = (string)($d['contrast']    ?? $d['pref_contrast']   ?? 'normal');
            $fontScale   = (float)  ($d['font_scale'] ?? $d['pref_font_scale'] ?? 1.0);
            $language    = (string)($d['language']    ?? $d['pref_lang']       ?? 'en');
            $emailOptin  = !empty($d['email_optin']) ? 1 : 0;
            $cookieOK    = !empty($d['cookie_consent']) ? 1 : 0;

            $fontScale = $clamp((float)$fontScale, 0.9, 1.25);

            // Persist to DB when logged in (so it's available across devices)
            $identity = $this->request->getAttribute('identity');
            if ($identity) {
                try {
                    $Users = $this->fetchTable('Users');
                    /** @var \App\Model\Entity\User $user */
                    $user = $Users->get((int)$identity->get('id'));
                    $patch = [
                        'pref_theme'       => $theme,
                        'pref_contrast'    => $contrast,
                        'pref_font_scale'  => $fontScale,
                        'pref_lang'        => $language,
                        'email_optin'      => $emailOptin,
                        'cookie_consent'   => $cookieOK,
                    ];
                    $user = $Users->patchEntity($user, $patch, ['accessibleFields' => ['*' => true]]);
                    $Users->saveOrFail($user);
                } catch (\Throwable $e) {
                    // Do not block cookie write if DB save fails
                    $this->Flash->error('Saved to browser, but failed to save to your account.');
                }
            }

            // Always refresh cookies so UI updates instantly
            $expires = new \DateTimeImmutable('+180 days');
            $secure  = $this->request->is('ssl');
            $cookies = new CookieCollection([]);
            $write = function (string $k, string $v) use ($expires, $secure): Cookie {
                return new Cookie($k, $v, $expires, '/', null, $secure, false, 'Lax');
            };

            $cookies = $cookies->add($write('pref_theme', $theme));
            $cookies = $cookies->add($write('pref_contrast', $contrast));
            $cookies = $cookies->add($write('pref_font_scale', (string)$fontScale));
            $cookies = $cookies->add($write('pref_lang', $language));
            $cookies = $cookies->add($write('pref_email_optin', (string)$emailOptin));
            $cookies = $cookies->add($write('pref_cookie_consent', (string)$cookieOK));

            $this->response = $this->response->withCookieCollection($cookies);

            $this->Flash->success('Preferences saved.');
            return $this->redirect($this->request->getRequestTarget());
        }

        // GET: read current prefs from cookies first, then fallback to DB/defaults
        $c = $this->request->getCookieParams();
        $prefs = [
            'theme'           => $c['pref_theme']          ?? null,
            'contrast'        => $c['pref_contrast']       ?? null,
            'font_scale'      => $c['pref_font_scale']     ?? null,
            'language'        => $c['pref_lang']           ?? null,
            'email_optin'     => isset($c['pref_email_optin']) ? ($c['pref_email_optin'] === '1') : null,
            'cookie_consent'  => isset($c['pref_cookie_consent']) ? ($c['pref_cookie_consent'] === '1') : null,
        ];

        // Fallback to DB values for logged-in users if cookies are missing
        $identity = $this->request->getAttribute('identity');
        if ($identity) {
            try {
                $Users = $this->fetchTable('Users');
                $user  = $Users->get((int)$identity->get('id'), [
                    'fields' => [
                        'pref_theme', 'pref_contrast', 'pref_font_scale', 'pref_lang',
                        'email_optin', 'cookie_consent'
                    ]
                ]);

                $prefs['theme']          = $prefs['theme']          ?? (string)($user->pref_theme ?? 'auto');
                $prefs['contrast']       = $prefs['contrast']       ?? (string)($user->pref_contrast ?? 'normal');
                $prefs['font_scale']     = $prefs['font_scale']     ?? (string)($user->pref_font_scale ?? '1.0');
                $prefs['language']       = $prefs['language']       ?? (string)($user->pref_lang ?? 'en');
                $prefs['email_optin']    = $prefs['email_optin']    ?? (bool)($user->email_optin ?? 1);
                $prefs['cookie_consent'] = $prefs['cookie_consent'] ?? (bool)($user->cookie_consent ?? 0);
            } catch (\Throwable $e) {
                // fall through to defaults below
            }
        }

        // Final defaults if still missing
        $prefs['theme']          = $prefs['theme']          ?? 'auto';
        $prefs['contrast']       = $prefs['contrast']       ?? 'normal';
        $prefs['font_scale']     = (string)$clamp((float)($prefs['font_scale'] ?? 1.0), 0.9, 1.25);
        $prefs['language']       = $prefs['language']       ?? 'en';
        $prefs['email_optin']    = $prefs['email_optin']    ?? true;
        $prefs['cookie_consent'] = $prefs['cookie_consent'] ?? false;

        $this->set(compact('prefs'));
    }
}
