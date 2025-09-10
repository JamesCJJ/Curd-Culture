<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMissingFieldsToUsers extends BaseMigration
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
        $table->addColumn('timezone', 'string', [
            'default' => 'UTC',
            'limit' => 64,
            'null' => false,
        ]);
        $table->addColumn('language', 'string', [
            'default' => 'en',
            'limit' => 8,
            'null' => false,
        ]);
        $table->addColumn('theme', 'string', [
            'default' => 'auto',
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('notify_email', 'boolean', [
            'default' => 1,
            'null' => false,
        ]);
        $table->addColumn('notify_push', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->update();
    }
}
