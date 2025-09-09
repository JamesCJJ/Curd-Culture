<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('orders');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->hasMany('OrderItems', [
            'foreignKey'   => 'order_id',
            'dependent'    => true,
            'cascadeCallbacks' => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email')
            ->notEmptyString('full_name')
            ->notEmptyString('address')
            ->notEmptyString('city')
            ->notEmptyString('postcode')
            ->notEmptyString('country')
            ->numeric('subtotal')
            ->numeric('shipping_fee')
            ->numeric('discount')
            ->numeric('total');

        return $validator;
    }
}
