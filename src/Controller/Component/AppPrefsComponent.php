<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Locator\LocatorInterface;

class AppPrefsComponent extends Component
{
    /** @var Controller */
    protected $controller;

    /** @var LocatorInterface */
    protected $locator;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->controller = $this->getController();
        $this->locator    = FactoryLocator::get('Table');
    }

    public function defaults(): array
    {
        return [
            'theme'       => 'auto',      // auto|light|dark
            'contrast'    => 'normal',    // normal|high
            'font_scale'  => 1.00,        // 0.9 ~ 1.25
            'language'    => 'en',
            'email_optin' => 1,
            'cookie_consent' => 0,
        ];
    }

    /** Normalize and extract prefs from a User entity/array/identity */
    public function fromUser($user): array
    {
        $get = static function($k) use ($user) {
            if (is_array($user))   return $user[$k] ?? null;
            if (is_object($user))  return $user->{$k} ?? (method_exists($user, 'get') ? $user->get($k) : null);
            return null;
        };

        $theme   = (string)($get('pref_theme') ?? 'auto');
        $contrast= (string)($get('pref_contrast') ?? 'normal');
        $fs      = (float) ($get('pref_font_scale') ?? 1.0);
        $lang    = (string)($get('pref_lang') ?? 'en');

        // Clamp/whitelist
        if (!in_array($theme, ['auto','light','dark'], true))   $theme='auto';
        if (!in_array($contrast, ['normal','high'], true))      $contrast='normal';
        $fs = max(0.9, min(1.25, round($fs, 2)));

        return [
            'theme'       => $theme,
            'contrast'    => $contrast,
            'font_scale'  => $fs,
            'language'    => $lang,
            'email_optin' => (int)($get('email_optin') ?? 1),
            'cookie_consent' => (int)($get('cookie_consent') ?? 0),
        ];
    }

    /** Read prefs from session or build from identity/defaults */
    public function read(): array
    {
        $s = $this->controller->getRequest()->getSession();
        $prefs = $s->read('Prefs');
        if ($prefs) return $prefs;

        $identity = $this->controller->getRequest()->getAttribute('identity');
        if ($identity) {
            $prefs = $this->fromUser($identity);
        } else {
            $prefs = $this->defaults();
        }
        $s->write('Prefs', $prefs);
        return $prefs;
    }

    /** Write prefs to session */
    public function write(array $prefs): void
    {
        $this->controller->getRequest()->getSession()->write('Prefs', $prefs);
    }

    /** Update DB with $payload (already validated), refresh identity + session */
    public function updateDbAndSession(array $payload): array
    {
        $identity = $this->controller->getRequest()->getAttribute('identity');
        if (!$identity) {
            throw new \RuntimeException('Auth required');
        }
        $Users = $this->locator->get('Users');
        $user  = $Users->get((int)$identity->get('id'));

        // Map fields
        $map = [
            'theme'       => 'pref_theme',
            'contrast'    => 'pref_contrast',
            'font_scale'  => 'pref_font_scale',
            'language'    => 'pref_lang',
            'email_optin' => 'email_optin',
            'cookie_consent' => 'cookie_consent',
        ];
        $patch = [];
        foreach ($map as $in => $col) {
            if (array_key_exists($in, $payload)) $patch[$col] = $payload[$in];
        }

        // Clamp
        if (isset($patch['pref_font_scale'])) {
            $patch['pref_font_scale'] = max(0.9, min(1.25, (float)$patch['pref_font_scale']));
        }
        if (isset($patch['pref_theme']) && !in_array($patch['pref_theme'], ['auto','light','dark'], true)) {
            unset($patch['pref_theme']);
        }
        if (isset($patch['pref_contrast']) && !in_array($patch['pref_contrast'], ['normal','high'], true)) {
            unset($patch['pref_contrast']);
        }

        $user = $Users->patchEntity($user, $patch, ['accessibleFields' => ['*'=>true]]);
        $Users->saveOrFail($user);

        // Refresh identity so $this->request->getAttribute('identity') has fresh values
        if ($this->controller->components()->has('Authentication')) {
            $this->controller->Authentication->setIdentity($user);
        }

        // Update session snapshot
        $prefs = $this->fromUser($user);
        $this->write($prefs);

        return $prefs;
    }

    /** Call this right after successful login */
    public function onLogin(): void
    {
        $identity = $this->controller->getRequest()->getAttribute('identity');
        $prefs = $identity ? $this->fromUser($identity) : $this->defaults();
        $this->write($prefs);
    }

    /** Call this on logout if you want to be explicit (session will be destroyed anyway) */
    public function onLogout(): void
    {
        $this->controller->getRequest()->getSession()->delete('Prefs');
    }
}
