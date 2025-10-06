<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class AddressesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('addresses');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType'   => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')->requirePresence('user_id', 'create')->notEmptyString('user_id');

        $validator->scalar('type')->maxLength('type', 20)->notEmptyString('type');

        $validator->scalar('first_name')->maxLength('first_name', 100)->notEmptyString('first_name');
        $validator->scalar('last_name')->maxLength('last_name', 100)->notEmptyString('last_name');

        $validator->scalar('company')->maxLength('company', 150)->allowEmptyString('company');

        $validator->scalar('address_line_1')->maxLength('address_line_1', 255)->notEmptyString('address_line_1');
        $validator->scalar('address_line_2')->maxLength('address_line_2', 255)->allowEmptyString('address_line_2');

        $validator->scalar('suburb')->maxLength('suburb', 100)->notEmptyString('suburb');
        $validator->scalar('state')->maxLength('state', 50)->notEmptyString('state');
        $validator->scalar('postcode')->maxLength('postcode', 10)->notEmptyString('postcode');

        $validator->scalar('country')->maxLength('country', 100)->notEmptyString('country');

        $validator->scalar('phone')->maxLength('phone', 20)->allowEmptyString('phone');

        $validator->boolean('is_default')->allowEmptyString('is_default');

        return $validator;
    }


    public function setDefaultForUser(int $userId, int $addressId): bool
    {
        return $this->getConnection()->transactional(function () use ($userId, $addressId) {
            $addr = $this->find()
                ->select(['id', 'user_id', 'type'])
                ->where(['id' => $addressId, 'user_id' => $userId])
                ->first();

            if (!$addr) {
                return false;
            }


            $this->updateAll(
                ['is_default' => 0],
                ['user_id' => $userId, 'type' => $addr->get('type')]
            );


            $this->updateAll(
                ['is_default' => 1],
                ['id' => $addressId, 'user_id' => $userId]
            );

            return true;
        });
    }


    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ((int)$entity->get('is_default') !== 1) {
            return;
        }

        $userId = (int)$entity->get('user_id');
        $type   = (string)($entity->get('type') ?? 'billing');
        $idVal  = $entity->get('id');

        $where = ['user_id' => $userId, 'type' => $type];
        if ($idVal !== null) {
            $where['id !='] = $idVal;
        }

        $this->updateAll(['is_default' => 0], $where);
    }
}
