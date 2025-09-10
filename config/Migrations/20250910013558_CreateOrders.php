<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateOrders extends BaseMigration
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
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addColumn('email', 'string', [
            'default' => null,
            'limit' => 190,
            'null' => false,
        ]);
        $table->addColumn('full_name', 'string', [
            'default' => null,
            'limit' => 190,
            'null' => false,
        ]);
        $table->addColumn('address', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('city', 'string', [
            'default' => null,
            'limit' => 120,
            'null' => false,
        ]);
        $table->addColumn('postcode', 'string', [
            'default' => null,
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('country', 'string', [
            'default' => null,
            'limit' => 120,
            'null' => false,
        ]);
        $table->addColumn('currency', 'char', [
            'default' => 'AUD',
            'limit' => 3,
            'null' => false,
        ]);
        $table->addColumn('subtotal', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
            'null' => false,
        ]);
        $table->addColumn('shipping_fee', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
            'null' => false,
        ]);
        $table->addColumn('discount', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
            'null' => false,
        ]);
        $table->addColumn('total', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
            'null' => false,
        ]);
        $table->addColumn('status', 'string', [
            'default' => 'pending',
            'limit' => 16,
            'null' => false,
        ]);
        $table->addColumn('payment_status', 'string', [
            'default' => 'unpaid',
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('payment_method', 'string', [
            'default' => null,
            'limit' => 40,
            'null' => true,
        ]);
        $table->addColumn('payment_ref', 'string', [
            'default' => null,
            'limit' => 80,
            'null' => true,
        ]);
        $table->addColumn('paid_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('notes', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['user_id'], ['name' => 'idx_orders_user']);
        $table->addIndex(['created'], ['name' => 'idx_orders_created']);
        $table->addIndex(['status'], ['name' => 'idx_orders_status']);
        $table->create();
    }
}
