<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateSiteSettings extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('site_settings');
        $table
            ->addColumn('key', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('value', 'text', ['null' => true, 'default' => null])
            ->addColumn('type', 'string', ['limit' => 20, 'null' => false, 'default' => 'text'])
            ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['key'], ['unique' => true, 'name' => 'UNIQUE_KEY'])
            ->create();
    }
}


