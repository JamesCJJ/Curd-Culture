<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFulfillmentFieldsToOrders extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('orders');
        
        // Add fulfillment method (delivery or pickup)
        $table->addColumn('fulfillment_method', 'string', [
            'default' => 'delivery',
            'limit' => 20,
            'null' => false,
            'after' => 'status',
        ]);
        
        // Add delivery date (for delivery orders)
        $table->addColumn('delivery_date', 'date', [
            'default' => null,
            'null' => true,
            'after' => 'fulfillment_method',
        ]);
        
        // Add delivery slot ID (foreign key to delivery_slots)
        $table->addColumn('delivery_slot_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
            'after' => 'delivery_date',
        ]);
        
        // Add pickup location ID (for pickup orders)
        $table->addColumn('pickup_location_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
            'after' => 'delivery_slot_id',
        ]);
        
        // Add indexes for better query performance
        $table->addIndex(['fulfillment_method'], ['name' => 'idx_orders_fulfillment_method']);
        $table->addIndex(['delivery_date'], ['name' => 'idx_orders_delivery_date']);
        $table->addIndex(['delivery_slot_id'], ['name' => 'idx_orders_delivery_slot']);
        $table->addIndex(['pickup_location_id'], ['name' => 'idx_orders_pickup_location']);
        
        $table->update();
    }
}
