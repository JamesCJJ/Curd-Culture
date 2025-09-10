<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCarts extends BaseMigration
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
        $table = $this->table('carts');
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('status', 'string', [
            'default' => 'open',
            'limit' => 20,
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
        $table->addIndex(['user_id', 'status'], ['name' => 'user_status']);
        $table->create();
    }
}
