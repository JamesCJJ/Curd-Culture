<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UpdateSiteSettingsColumns extends BaseMigration
{
    public function change(): void
    {
        // Drop and recreate table to avoid reserved word column names
        if ($this->hasTable('site_settings')) {
            $this->table('site_settings')->drop()->save();
        }

        $table = $this->table('site_settings');
        $table
            ->addColumn('setting_key', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('setting_value', 'text', ['null' => true, 'default' => null])
            ->addColumn('setting_type', 'string', ['limit' => 20, 'null' => false, 'default' => 'text'])
            ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['setting_key'], ['unique' => true, 'name' => 'UNIQUE_SETTING_KEY'])
            ->create();
    }
}


