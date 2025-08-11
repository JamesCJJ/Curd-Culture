<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateContactMessages extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contact_messages');
        $table
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addColumn('is_spam', 'boolean', ['default' => 0, 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
