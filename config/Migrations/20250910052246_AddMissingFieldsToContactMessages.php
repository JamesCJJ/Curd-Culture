<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMissingFieldsToContactMessages extends BaseMigration
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
        $table = $this->table('contact_messages');
        $table->addColumn('replied_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('reply_note', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
