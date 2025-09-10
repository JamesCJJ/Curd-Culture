<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddStatusToUsers extends BaseMigration
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
        $table = $this->table('users');
        $table->addColumn('status', 'string', [
            'default' => 'active',
            'limit' => 50,
            'null' => false,
        ]);
        $table->addIndex([
            'status',
        
            ], [
            'name' => 'BY_STATUS',
            'unique' => false,
        ]);
        $table->update();
    }
}
