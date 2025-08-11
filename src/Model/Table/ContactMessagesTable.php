<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ContactMessagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('contact_messages');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')->maxLength('name', 100)->requirePresence('name')->notEmptyString('name')
            ->email('email')->requirePresence('email')->notEmptyString('email')
            ->scalar('message')->requirePresence('message')->notEmptyString('message');

        // 'captcha' will be checked in controller (needs session), here we only require it
        $validator->requirePresence('captcha')->notEmptyString('captcha', 'Please answer the captcha question.');

        return $validator;
    }
}
