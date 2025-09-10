<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateProducts extends BaseMigration
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
        $table = $this->table('products');
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 180,
            'null' => false,
        ]);
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 190,
            'null' => false,
        ]);
        $table->addColumn('price', 'decimal', [
            'precision' => 10,
            'scale' => 2,
            'default' => 0.00,
            'null' => false,
        ]);
        $table->addColumn('currency', 'char', [
            'default' => 'AUD',
            'limit' => 3,
            'null' => false,
        ]);
        $table->addColumn('summary', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('description', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('image_url', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('gallery', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('rating', 'decimal', [
            'precision' => 3,
            'scale' => 2,
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('stock', 'integer', [
            'default' => 0,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('origin_country', 'string', [
            'default' => null,
            'limit' => 64,
            'null' => true,
        ]);
        $table->addColumn('milk_type', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ]);
        $table->addColumn('age', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ]);
        $table->addColumn('style', 'string', [
            'default' => null,
            'limit' => 64,
            'null' => true,
        ]);
        $table->addColumn('rennet', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ]);
        $table->addColumn('pasteurised', 'string', [
            'default' => null,
            'limit' => 3,
            'null' => true,
        ]);
        $table->addColumn('fat_content', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ]);
        $table->addColumn('allergens', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('vegetarian', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('gluten_free', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('lactose_free', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('pairing_notes', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('awards', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addIndex(['slug'], ['name' => 'slug', 'unique' => true]);
        $table->create();
    }
}
