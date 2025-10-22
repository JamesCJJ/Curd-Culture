<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Orders table
 *
 * Purpose:
 * - Holds the order header (customer/contact, totals, payment/fulfillment fields).
 * - Relationships to lines (OrderItems) and optional scheduling entities.
 *
 * Notes for maintainers:
 * - Totals are stored as numbers (no currency math here); amounts are recomputed at the controller/service layer.
 * - We allow NULL for some fulfillment fields because pickup/delivery are mutually exclusive.
 * - Consider DB indexes on (user_id, created), (payment_ref), and (delivery_date, delivery_slot_id).
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
            'className'    => 'DeliverySlots',
            'foreignKey'   => 'delivery_slot_id',
            'propertyName' => 'delivery_slot',
            'joinType'     => 'LEFT',
        ]);

        // Optional: click & collect
        $this->belongsTo('PickupLocations', [
            'className'    => 'PickupLocations',
            'foreignKey'   => 'pickup_location_id',
            'propertyName' => 'pickup_location',
            'joinType'     => 'LEFT',
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
