<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use DateTimeImmutable;

class AppPrefsComponent extends Component
{

    private function cookiePath(): string
    {
        $base = (string)(Configure::read('App.base') ?? '/');
        return $base === '' ? '/' : $base;
    }


    public function withPrefCookies(Response $response, $user): Response
    {
        $exp  = new DateTimeImmutable('+180 days');
        $path = $this->cookiePath();


        $prefTheme = (string)(
            $user->pref_theme
            ?? $user->theme
            ?? 'auto'
        );
        $prefLang = (string)(
            $user->pref_lang
            ?? $user->language
            ?? 'en'
        );
        $prefContrast  = (string)($user->pref_contrast   ?? 'normal');
        $prefFontScale = (string)((float)($user->pref_font_scale ?? 1.0));
        $emailOptin    = (string)((int)($user->email_optin      ?? 1));
        $cookieConsent = (string)((int)($user->cookie_consent   ?? 0));

        $pairs = [
            'pref_theme'          => $prefTheme,
            'pref_contrast'       => $prefContrast,
            'pref_font_scale'     => $prefFontScale,
            'pref_lang'           => $prefLang,
            'pref_email_optin'    => $emailOptin,
            'pref_cookie_consent' => $cookieConsent,
        ];

        foreach ($pairs as $k => $v) {
            $cookie   = new Cookie($k, $v, $exp, $path, null, false, false, 'Lax');
            $response = $response->withCookie($cookie);
        }

        return $response;
    }


    public function clearPrefCookies(Response $response): Response
    {
        $names = [
            'pref_theme','pref_contrast','pref_font_scale','pref_lang',
            'pref_email_optin','pref_cookie_consent'
        ];

        $paths = array_unique(['/', $this->cookiePath()]);
        foreach ($names as $k) {
            foreach ($paths as $p) {
                $cookie   = new Cookie($k, '', null, $p, null, false, false, 'Lax');
                $response = $response->withExpiredCookie($cookie);
            }
        }


        $flag = new Cookie('prefs_cleared', '1', new DateTimeImmutable('+2 minutes'), '/', null, false, false, 'Lax');
        return $response->withCookie($flag);
    }
}
