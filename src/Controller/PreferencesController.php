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
        $this->loadComponent('AppPrefs');

        // Allow JSON + form POST
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
        if ($this->request->is('json')) {
            // If you send raw JSON fetch
            $raw = (string)$this->request->getBody();
            if ($raw) {
                $json = json_decode($raw, true);
                if (is_array($json)) $data = $json + $data;
            }
        }

        // Only accept known keys
        $payload = [];
        foreach (['theme','contrast','font_scale','language','email_optin','cookie_consent'] as $k) {
            if (array_key_exists($k, $data)) $payload[$k] = $data[$k];
        }
        if (!$payload) {
            $this->set(['ok'=>false,'error'=>'Nothing to update']);
            $this->viewBuilder()->setOption('serialize',['ok','error']);
            return;
        }

        try {
            $prefs = $this->AppPrefs->updateDbAndSession($payload);
            $this->set(['ok'=>true, 'prefs'=>$prefs]);
        } catch (\Throwable $e) {
            $this->set(['ok'=>false,'error'=>$e->getMessage()]);
        }

        $this->viewBuilder()->setOption('serialize',['ok','prefs','error']);
    }
}
