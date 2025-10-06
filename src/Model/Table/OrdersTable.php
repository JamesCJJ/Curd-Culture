<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Orders table
 * - Adds relations to OrderItems, Users, DeliverySlots, PickupLocations
 */
class OrdersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('orders');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType'   => 'LEFT',
        ]);

        $this->hasMany('OrderItems', [
            'foreignKey'       => 'order_id',
            'dependent'        => true,
            'cascadeCallbacks' => true,
        ]);

        // Optional: delivery scheduling
        $this->belongsTo('DeliverySlots', [
            'foreignKey' => 'delivery_slot_id',
            'joinType'   => 'LEFT',
        ]);

        // Optional: click & collect
        $this->belongsTo('PickupLocations', [
            'foreignKey' => 'pickup_location_id',
            'joinType'   => 'LEFT',
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

        // Optional enum-like constraint
        $validator->allowEmptyString('fulfillment_method')
            ->inList('fulfillment_method', ['delivery', 'pickup'], 'Invalid fulfillment method');

        return $validator;
    }
}
