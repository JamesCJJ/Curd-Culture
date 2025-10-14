<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddProfileFieldsToUsers extends BaseMigration
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
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('language', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('theme', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('notify_email', 'boolean', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('notify_push', 'boolean', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex([
            'timezone',
        
            ], [
            'name' => 'BY_TIMEZONE',
            'unique' => false,
        ]);
        $table->addIndex([
            'language',
        
            ], [
            'name' => 'BY_LANGUAGE',
            'unique' => false,
        ]);
        $table->addIndex([
            'theme',
        
            ], [
            'name' => 'BY_THEME',
            'unique' => false,
        ]);
        $table->addIndex([
            'notify_email',
            'notify_push',
        
            ], [
            'name' => 'notify_flags_idx',
            'unique' => false,
        ]);
        $table->update();
    }
}
