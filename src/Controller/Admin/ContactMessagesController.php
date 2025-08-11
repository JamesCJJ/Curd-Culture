<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class ContactMessagesController extends AppController
{
    public function index()
    {
        $this->paginate = [
            'order' => ['ContactMessages.created' => 'DESC'],
            'limit' => 10,
        ];
        $contactMessages = $this->paginate($this->fetchTable('ContactMessages'));
        $this->set(compact('contactMessages'));
    }
}
