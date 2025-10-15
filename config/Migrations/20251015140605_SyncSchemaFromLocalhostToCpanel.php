<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

final class SyncSchemaFromLocalhostToCpanel extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('ALTER TABLE `pickup_locations` ADD `open_from` time DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` ADD `open_to` time DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `address_line_1` varchar(255) NOT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `created` datetime DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `modified` datetime DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `name` varchar(120) NOT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `postcode` varchar(10) NOT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `state` varchar(50) NOT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `suburb` varchar(100) NOT NULL;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `language` varchar(255) NOT NULL;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `notify_email` tinyint(1) NOT NULL DEFAULT 1;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `notify_push` tinyint(1) NOT NULL DEFAULT 0;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `theme` varchar(255) NOT NULL;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `timezone` varchar(255) NOT NULL;');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `timezone` varchar(64) DEFAULT \'UTC\';');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `theme` varchar(20) DEFAULT \'auto\';');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `notify_push` tinyint(1) DEFAULT 0;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `notify_email` tinyint(1) DEFAULT 1;');
        $this->execute('ALTER TABLE `users` MODIFY COLUMN `language` varchar(8) DEFAULT \'en\';');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `suburb` varchar(255) DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `state` varchar(100) DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `postcode` varchar(50) DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `name` varchar(100) NOT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `modified` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp();');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `created` datetime DEFAULT current_timestamp();');
        $this->execute('ALTER TABLE `pickup_locations` MODIFY COLUMN `address_line_1` varchar(255) DEFAULT NULL;');
        $this->execute('ALTER TABLE `pickup_locations` DROP COLUMN `open_to`;');
        $this->execute('ALTER TABLE `pickup_locations` DROP COLUMN `open_from`;');
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
