<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCartItems extends BaseMigration
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
        $table = $this->table('cart_items');
        $table->addColumn('cart_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('product_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('qty', 'integer', [
            'default' => 1,
            'limit' => 11,
            'null' => false,
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
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addIndex(['cart_id', 'product_id'], ['name' => 'cart_product', 'unique' => true]);
        $table->addIndex(['product_id'], ['name' => 'fk_ci_prod']);
        $table->create();
    }
}
