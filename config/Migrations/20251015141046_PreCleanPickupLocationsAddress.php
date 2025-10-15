<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

final class PreCleanPickupLocationsAddress extends AbstractMigration
{
    public function up(): void
    {
        // Ensure table exists before running
        $exists = $this->fetchAll("
            SELECT COUNT(*) AS cnt
            FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = 'pickup_locations'
        ")[0]['cnt'] ?? 0;

        if (!$exists) {
            return;
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=0');

        // 1) Replace NULL with empty string to satisfy NOT NULL
        $this->execute("UPDATE `pickup_locations` SET `address_line_1` = '' WHERE `address_line_1` IS NULL");

        // 2) Create overflow log (id + overflow tail), for auditability
        $this->execute("
            CREATE TABLE IF NOT EXISTS `pickup_locations_overflow_log` (
                `id` INT NOT NULL,
                `overflow_text` TEXT NULL,
                `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 3) Log any overflows prior to trimming/moving
        $this->execute("
            INSERT INTO `pickup_locations_overflow_log` (`id`, `overflow_text`)
            SELECT `id`, SUBSTRING(`address_line_1`, 256)
            FROM `pickup_locations`
            WHERE CHAR_LENGTH(`address_line_1`) > 255
        ");

        // 4) If address_line_2 exists, move overflow into it
        $col = $this->fetchAll("
            SELECT COUNT(*) AS cnt
            FROM information_schema.columns
            WHERE table_schema = DATABASE() AND table_name = 'pickup_locations' AND column_name = 'address_line_2'
        ")[0]['cnt'] ?? 0;

        if ($col) {
            $this->execute("
                UPDATE `pickup_locations`
                SET `address_line_2` = TRIM(CONCAT(COALESCE(`address_line_2`, ''), ' ', SUBSTRING(`address_line_1`, 256)))
                WHERE CHAR_LENGTH(`address_line_1`) > 255
            ");
        }

        // 5) Trim address_line_1 to 255 chars, so we can apply the strict schema
        $this->execute("
            UPDATE `pickup_locations`
            SET `address_line_1` = LEFT(`address_line_1`, 255)
            WHERE CHAR_LENGTH(`address_line_1`) > 255
        ");

        // 6) Finally enforce localhost schema: VARCHAR(255) NOT NULL
        $this->execute("ALTER TABLE `pickup_locations` MODIFY COLUMN `address_line_1` VARCHAR(255) NOT NULL");

        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Best-effort: relax the column back to VARCHAR(512) NULL to allow manual restoration if needed
        $exists = $this->fetchAll("
            SELECT COUNT(*) AS cnt
            FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = 'pickup_locations'
        ")[0]['cnt'] ?? 0;

        if (!$exists) {
            return;
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute("ALTER TABLE `pickup_locations` MODIFY COLUMN `address_line_1` VARCHAR(512) NULL");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
