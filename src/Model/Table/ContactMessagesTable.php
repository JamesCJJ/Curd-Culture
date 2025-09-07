<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
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

        $this->belongsTo('Users', [
            'foreignKey' => 'replied_by',
            'joinType'   => 'LEFT',
        ]);
    }

    public function validationDefault(Validator $v): Validator
    {

        $v->scalar('name')
            ->maxLength('name', 255, 'Name must be 255 characters or less')
            ->requirePresence('name', 'create', 'Name is required')
            ->notEmptyString('name', 'Please enter your name');

        $v->email('email', false, 'Please enter a valid email address (e.g., user@example.com)')
            ->requirePresence('email', 'create', 'Email address is required')
            ->notEmptyString('email', 'Please enter your email address');

        $v->scalar('message')
            ->requirePresence('message', 'create', 'Message is required')
            ->notEmptyString('message', 'Please enter your message');


        $v->scalar('status')
            ->allowEmptyString('status')
            ->inList('status', ['new', 'in_progress', 'closed'], 'Invalid status');

        return $v;
    }


    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['name', 'email', 'message'] as $f) {
            if (isset($data[$f])) {
                $data[$f] = trim((string)$data[$f]);
            }
        }

        if (!isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'new';
        }
    }
}
