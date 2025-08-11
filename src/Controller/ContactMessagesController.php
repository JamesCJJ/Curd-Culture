<?php
declare(strict_types=1);

namespace App\Controller;


use Cake\Event\EventInterface;
class ContactMessagesController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // 允许未登录访问“新增留言”页面
        $this->Authentication->addUnauthenticatedActions(['add']);
    }

    public function add()
    {
        $contact = $this->ContactMessages->newEmptyEntity();
        $session = $this->request->getSession();

        if ($this->request->is('get')) {
            // Generate simple math captcha: a + b
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $session->write('Captcha.sum', $a + $b);
            $this->set(compact('a', 'b'));
        }

        if ($this->request->is('post')) {
            $contact = $this->ContactMessages->patchEntity($contact, $this->request->getData());
            // Validate the math captcha
            $expected = (int)($session->read('Captcha.sum') ?? -1);
            $given = (int)($this->request->getData('captcha') ?? -2);
            if ($expected !== $given) {
                $contact->setError('captcha', __('Incorrect captcha answer.'));
            }

            if (!$contact->getErrors()) {
                if ($this->ContactMessages->save($contact)) {
                    $this->Flash->success(__('Thanks! Your message was sent.'));
                    // regenerate captcha after success
                    $session->delete('Captcha.sum');
                    return $this->redirect(['action' => 'add']);
                }
                $this->Flash->error(__('Please correct the errors and try again.'));
            } else {
                $this->Flash->error(__('Please correct the errors and try again.'));
            }
            // regenerate captcha after post (success or fail) to avoid replay
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $session->write('Captcha.sum', $a + $b);
            $this->set(compact('a', 'b'));
        }

        $this->set(compact('contact'));
    }
}
