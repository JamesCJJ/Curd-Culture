<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\UnauthorizedException;

class PreferencesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
    }

    public function update()
    {
        $identity = $this->request->getAttribute('identity');
        if (!$identity) {
            throw new UnauthorizedException('Login required');
        }

        $data = (array)$this->request->getData();

        $map = [
            'theme'            => 'pref_theme',
            'contrast'         => 'pref_contrast',
            'font_scale'       => 'pref_font_scale',
            'language'         => 'pref_lang',
            'email_optin'      => 'email_optin',
            'cookie_consent'   => 'cookie_consent',

            'pref_theme'       => 'pref_theme',
            'pref_contrast'    => 'pref_contrast',
            'pref_font_scale'  => 'pref_font_scale',
            'pref_lang'        => 'pref_lang',
        ];

        $payload = [];
        foreach ($map as $in => $col) {
            if (array_key_exists($in, $data)) {
                $payload[$col] = $data[$in];
            }
        }
        if (isset($payload['pref_font_scale'])) {
            $payload['pref_font_scale'] = max(0.9, min(1.25, (float)$payload['pref_font_scale']));
        }
        if (!$payload) {
            $this->set(['ok' => false, 'updated' => []]);
            $this->viewBuilder()->setOption('serialize', ['ok','updated']);
            return;
        }

        $Users = $this->fetchTable('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $Users->get((int)$identity->get('id'));
        $user = $Users->patchEntity($user, $payload, ['accessibleFields' => ['*' => true]]);
        $Users->saveOrFail($user);

        $this->loadComponent('AppPrefs');
        $this->response = $this->AppPrefs->withPrefCookies($this->response, $user);

        $this->set(['ok' => true, 'updated' => $payload]);
        $this->viewBuilder()->setOption('serialize', ['ok','updated']);
    }
}
