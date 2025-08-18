<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setPrimaryKey('id');
        $this->setDisplayField('email');


        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email', false, 'Invalid email.')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'Password is required');

        $validator
            ->scalar('role')
            ->requirePresence('role', 'create')
            ->notEmptyString('role')
            ->inList('role', ['admin', 'user'], 'Role must be admin or user');


        $validator
            ->scalar('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status')
            ->inList('status', ['active', 'inactive', 'banned'], 'Invalid status');

        return $validator;
    }
}
