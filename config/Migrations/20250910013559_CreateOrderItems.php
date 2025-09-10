<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateOrderItems extends BaseMigration
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
        $table = $this->table('order_items');
        $table->addColumn('order_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('product_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 190,
            'null' => false,
        ]);
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 190,
            'null' => true,
        ]);
        $table->addColumn('price', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('currency', 'char', [
            'default' => 'AUD',
            'limit' => 3,
            'null' => false,
        ]);
        $table->addColumn('qty', 'integer', [
            'default' => null,
            'limit' => 10,
            'null' => false,
        ]);
        $table->addColumn('line_total', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('snapshot', 'text', [
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
        $table->addIndex(['order_id'], ['name' => 'idx_items_order']);
        $table->addIndex(['product_id'], ['name' => 'idx_items_product']);
        $table->create();
    }
}
