<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

class ContactMessagesController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }


    public function add()
    {
        $contact = $this->ContactMessages->newEmptyEntity();

        if ($this->request->is('post')) {
            $token = $this->request->getData('g-recaptcha-response') ?? '';
            if (!$this->verifyRecaptcha($token)) {
                $this->Flash->error(__('Captcha validation failed. Please try again.'));

                $contact = $this->ContactMessages->patchEntity($contact, $this->request->getData());
                $this->set(compact('contact'));
                return;
            }

            $contact = $this->ContactMessages->patchEntity($contact, $this->request->getData());
            if ($this->ContactMessages->save($contact)) {
                $this->Flash->success(__('Thanks! Your message was sent.'));
                return $this->redirect(['action' => 'add']);
            }
            
            $this->Flash->error(__('Please correct the errors and try again.'));
        }

        $this->set(compact('contact'));
    }


    private function verifyRecaptcha(string $token): bool
    {
        // In development mode, allow bypassing reCAPTCHA for testing
        if (Configure::read('debug') && $token === 'test') {
            return true;
        }

        // For development, also allow empty token if debug is enabled
        if (Configure::read('debug') && $token === '') {
            return true;
        }

        if ($token === '') {
            return false;
        }

        $secret = (string)Configure::read('Security.recaptcha.secret_key');
        if ($secret === '') {

            return false;
        }


        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret' => $secret,
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
}
