<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class FixProductsPasteurisedField extends BaseMigration
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
        // Use raw SQL to modify the pasteurised column to enum
        $this->execute("ALTER TABLE products MODIFY COLUMN pasteurised ENUM('yes', 'no') DEFAULT NULL");
    }
}
