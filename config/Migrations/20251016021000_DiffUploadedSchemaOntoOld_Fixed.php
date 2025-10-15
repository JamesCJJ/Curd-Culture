<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class DiffUploadedSchemaOntoOldFixed extends AbstractMigration
{
    /** Utilities */
    private function esc(string $v): string {
        // very basic escape for identifiers/values we control (table/index names from code)
        return str_replace('`', '``', $v);
    }

    private function colExists(string $table, string $column): bool
    {
        $table = $this->esc($table);
        $column = $this->esc($column);
        $sql = "SELECT COUNT(*) AS c
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '{$table}'
                  AND COLUMN_NAME = '{$column}'";
        $row = $this->getAdapter()->fetchRow($sql);
        return (int)$row['c'] > 0;
    }

    private function tableExistsStrict(string $table): bool
    {
        return $this->hasTable($table);
    }

    private function idxExistsByName(string $table, string $idxName): bool
    {
        $table = $this->esc($table);
        $idxName = $this->esc($idxName);
        $sql = "SELECT COUNT(*) AS c
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '{$table}'
                  AND INDEX_NAME = '{$idxName}'";
        $row = $this->getAdapter()->fetchRow($sql);
        return (int)$row['c'] > 0;
    }

    private function uniqueExistsOn(string $table, array $cols): bool
    {
        // returns true if there is ANY unique index exactly on these columns (order-insensitive)
        sort($cols);
        $table = $this->esc($table);
        $sql = "SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND NON_UNIQUE = 0
                ORDER BY INDEX_NAME, SEQ_IN_INDEX";
        $rows = $this->getAdapter()->fetchAll($sql);
        $byIndex = [];
        foreach ($rows as $r) {
            $byIndex[$r['INDEX_NAME']][] = $r['COLUMN_NAME'];
        }
        foreach ($byIndex as $index => $cList) {
            sort($cList);
            if ($cList === $cols) return true;
        }
        return false;
    }

    private function fkExists(string $table, string $constraint): bool
    {
        $table = $this->esc($table);
        $constraint = $this->esc($constraint);
        $sql = "SELECT COUNT(*) AS c
                FROM information_schema.REFERENTIAL_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND CONSTRAINT_NAME = '{$constraint}'
                  AND TABLE_NAME = '{$table}'";
        $row = $this->getAdapter()->fetchRow($sql);
        return (int)$row['c'] > 0;
    }

    private function tableCollation(string $table): ?string
    {
        $table = $this->esc($table);
        $sql = "SELECT TABLE_COLLATION FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'";
        $row = $this->getAdapter()->fetchRow($sql);
        return $row ? $row['TABLE_COLLATION'] : null;
    }

    private function ensureTableCollation(string $table, string $charset, string $collation): void
    {
        $current = $this->tableCollation($table);
        if ($current && $current !== $collation) {
            $this->write(sprintf("~ Collation of %s: %s -> %s", $table, $current, $collation));
            $this->execute(sprintf("ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s", $this->esc($table), $charset, $collation));
        }
    }

    private function addColIfMissing(string $table, string $column, string $definitionSql): void
    {
        if (!$this->colExists($table, $column)) {
            $this->write(sprintf("+ Adding column %s.%s", $table, $column));
            $this->execute(sprintf("ALTER TABLE `%s` ADD COLUMN `%s` %s", $this->esc($table), $this->esc($column), $definitionSql));
        }
    }

    private function ensureUniqueIndex(string $table, string $name, array $columns): void
    {
        if (!$this->uniqueExistsOn($table, $columns)) {
            $colsSql = '`' . implode('`,`', array_map([$this,'esc'], $columns)) . '`';
            $this->write(sprintf("+ Adding UNIQUE index %s on %s(%s)", $name, $table, implode(',', $columns)));
            $this->execute(sprintf("ALTER TABLE `%s` ADD UNIQUE KEY `%s` (%s)", $this->esc($table), $this->esc($name), $colsSql));
        }
    }

    private function ensureIndex(string $table, string $name, array $columns): void
    {
        if (!$this->idxExistsByName($table, $name)) {
            $colsSql = '`' . implode('`,`', array_map([$this,'esc'], $columns)) . '`';
            $this->write(sprintf("+ Adding index %s on %s(%s)", $name, $table, implode(',', $columns)));
            $this->execute(sprintf("ALTER TABLE `%s` ADD KEY `%s` (%s)", $this->esc($table), $this->esc($name), $colsSql));
        }
    }

    private function ensureForeignKey(
        string $table,
        string $constraint,
        array $columns,
        string $refTable,
        array $refColumns,
        string $onDelete,
        string $onUpdate
    ): void {
        if (!$this->fkExists($table, $constraint)) {
            $cols = '`' . implode('`,`', array_map([$this,'esc'], $columns)) . '`';
            $rcols = '`' . implode('`,`', array_map([$this,'esc'], $refColumns)) . '`';
            $sql = sprintf(
                "ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (%s) REFERENCES `%s` (%s) ON DELETE %s ON UPDATE %s",
                $this->esc($table), $this->esc($constraint), $cols, $this->esc($refTable), $rcols, $onDelete, $onUpdate
            );
            $this->write(sprintf("+ Adding FK %s on %s(%s) -> %s(%s)", $constraint, $table, implode(',', $columns), $refTable, implode(',', $refColumns)));
            $this->execute($sql);
        }
    }

    public function up(): void
    {
        // ===== users =====
        if (!$this->tableExistsStrict('users')) {
            $this->execute("CREATE TABLE `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('users', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('users', 'email', "varchar(255) NOT NULL");
        $this->addColIfMissing('users', 'password', "varchar(255) NOT NULL");
        $this->addColIfMissing('users', 'reset_code_hash', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('users', 'reset_expires', "datetime DEFAULT NULL");
        $this->addColIfMissing('users', 'reset_attempts', "int(11) NOT NULL DEFAULT 0");
        $this->addColIfMissing('users', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('users', 'modified', "datetime DEFAULT NULL");
        $this->addColIfMissing('users', 'role', "varchar(20) NOT NULL DEFAULT 'user'");
        $this->addColIfMissing('users', 'status', "varchar(50) DEFAULT 'active'");
        $this->addColIfMissing('users', 'timezone', "varchar(64) DEFAULT 'UTC'");
        $this->addColIfMissing('users', 'language', "varchar(8) DEFAULT 'en'");
        $this->addColIfMissing('users', 'theme', "varchar(20) DEFAULT 'auto'");
        $this->addColIfMissing('users', 'notify_email', "tinyint(1) DEFAULT 1");
        $this->addColIfMissing('users', 'notify_push', "tinyint(1) DEFAULT 0");
        $this->addColIfMissing('users', 'pref_theme', "varchar(10) DEFAULT 'auto'");
        $this->addColIfMissing('users', 'pref_contrast', "varchar(10) DEFAULT 'normal'");
        $this->addColIfMissing('users', 'pref_font_scale', "decimal(3,2) DEFAULT 1.00");
        $this->addColIfMissing('users', 'pref_lang', "varchar(8) DEFAULT 'en'");
        $this->addColIfMissing('users', 'email_optin', "tinyint(1) DEFAULT 1");
        $this->addColIfMissing('users', 'cookie_consent', "tinyint(1) DEFAULT 0");
        $this->ensureIndex('users', 'idx_users_reset_expires', ['reset_expires']);

        // ===== addresses =====
        if (!$this->tableExistsStrict('addresses')) {
            $this->execute("CREATE TABLE `addresses` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->ensureTableCollation('addresses', 'utf8mb4', 'utf8mb4_unicode_ci');
        $this->addColIfMissing('addresses', 'user_id', "int(11) NOT NULL");
        $this->addColIfMissing('addresses', 'type', "varchar(20) NOT NULL DEFAULT 'billing'");
        $this->addColIfMissing('addresses', 'first_name', "varchar(100) NOT NULL");
        $this->addColIfMissing('addresses', 'last_name', "varchar(100) NOT NULL");
        $this->addColIfMissing('addresses', 'company', "varchar(150) DEFAULT NULL");
        $this->addColIfMissing('addresses', 'address_line_1', "varchar(255) NOT NULL");
        $this->addColIfMissing('addresses', 'address_line_2', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('addresses', 'suburb', "varchar(100) NOT NULL");
        $this->addColIfMissing('addresses', 'state', "varchar(50) NOT NULL");
        $this->addColIfMissing('addresses', 'postcode', "varchar(10) NOT NULL");
        $this->addColIfMissing('addresses', 'country', "varchar(100) NOT NULL DEFAULT 'Australia'");
        $this->addColIfMissing('addresses', 'phone', "varchar(20) DEFAULT NULL");
        $this->addColIfMissing('addresses', 'is_default', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('addresses', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('addresses', 'modified', "datetime DEFAULT NULL");
        $this->ensureIndex('addresses', 'idx_addresses_user', ['user_id']);
        $this->ensureIndex('addresses', 'idx_addresses_user_type', ['user_id','type']);
        $this->ensureForeignKey('addresses', 'fk_addresses_users', ['user_id'], 'users', ['id'], 'NO ACTION', 'NO ACTION');

        // ===== contacts =====
        if (!$this->tableExistsStrict('contacts')) {
            $this->execute("CREATE TABLE `contacts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->ensureTableCollation('contacts', 'utf8mb4', 'utf8mb4_unicode_ci');
        $this->addColIfMissing('contacts', 'name', "varchar(255) NOT NULL");
        $this->addColIfMissing('contacts', 'email', "varchar(255) NOT NULL");
        $this->addColIfMissing('contacts', 'message', "text NOT NULL");
        $this->addColIfMissing('contacts', 'created', "datetime NOT NULL");
        $this->addColIfMissing('contacts', 'modified', "datetime NOT NULL");

        // ===== articles =====
        if (!$this->tableExistsStrict('articles')) {
            $this->execute("CREATE TABLE `articles` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('articles', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('articles', 'user_id', "int(11) NOT NULL");
        $this->addColIfMissing('articles', 'title', "varchar(255) NOT NULL");
        $this->addColIfMissing('articles', 'slug', "varchar(191) NOT NULL");
        $this->addColIfMissing('articles', 'body', "text DEFAULT NULL");
        $this->addColIfMissing('articles', 'published', "tinyint(1) DEFAULT 0");
        $this->addColIfMissing('articles', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('articles', 'modified', "datetime DEFAULT NULL");
        $this->ensureUniqueIndex('articles', 'slug', ['slug']);
        $this->ensureIndex('articles', 'user_key', ['user_id']);
        $this->ensureForeignKey('articles', 'user_key', ['user_id'], 'users', ['id'], 'NO ACTION', 'NO ACTION');

        // ===== tags =====
        if (!$this->tableExistsStrict('tags')) {
            $this->execute("CREATE TABLE `tags` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('tags', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('tags', 'title', "varchar(191) DEFAULT NULL");
        $this->addColIfMissing('tags', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('tags', 'modified', "datetime DEFAULT NULL");
        $this->ensureUniqueIndex('tags', 'title', ['title']);

        // ===== articles_tags =====
        if (!$this->tableExistsStrict('articles_tags')) {
            $this->execute("CREATE TABLE `articles_tags` (
                `article_id` int(11) NOT NULL,
                `tag_id` int(11) NOT NULL,
                PRIMARY KEY (`article_id`,`tag_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('articles_tags', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('articles_tags', 'article_id', "int(11) NOT NULL");
        $this->addColIfMissing('articles_tags', 'tag_id', "int(11) NOT NULL");
        $this->ensureIndex('articles_tags', 'tag_key', ['tag_id']);
        $this->ensureForeignKey('articles_tags', 'article_key', ['article_id'], 'articles', ['id'], 'NO ACTION', 'NO ACTION');
        $this->ensureForeignKey('articles_tags', 'tag_key', ['tag_id'], 'tags', ['id'], 'NO ACTION', 'NO ACTION');

        // ===== products =====
        if (!$this->tableExistsStrict('products')) {
            $this->execute("CREATE TABLE `products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('products', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('products', 'name', "varchar(180) NOT NULL");
        $this->addColIfMissing('products', 'slug', "varchar(190) NOT NULL");
        $this->addColIfMissing('products', 'price', "decimal(10,2) NOT NULL DEFAULT 0.00");
        $this->addColIfMissing('products', 'currency', "char(3) NOT NULL DEFAULT 'AUD'");
        $this->addColIfMissing('products', 'summary', "text DEFAULT NULL");
        $this->addColIfMissing('products', 'description', "text DEFAULT NULL");
        $this->addColIfMissing('products', 'image_url', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('products', 'gallery', "text DEFAULT NULL");
        $this->addColIfMissing('products', 'rating', "decimal(3,2) DEFAULT NULL");
        $this->addColIfMissing('products', 'stock', "int(11) NOT NULL DEFAULT 0");
        $this->addColIfMissing('products', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('products', 'modified', "datetime DEFAULT NULL");
        $this->addColIfMissing('products', 'origin_country', "varchar(64) DEFAULT NULL");
        $this->addColIfMissing('products', 'milk_type', "varchar(32) DEFAULT NULL");
        $this->addColIfMissing('products', 'age', "varchar(32) DEFAULT NULL");
        $this->addColIfMissing('products', 'style', "varchar(64) DEFAULT NULL");
        $this->addColIfMissing('products', 'rennet', "varchar(32) DEFAULT NULL");
        $this->addColIfMissing('products', 'pasteurised', "ENUM('yes','no') DEFAULT NULL");
        $this->addColIfMissing('products', 'fat_content', "varchar(32) DEFAULT NULL");
        $this->addColIfMissing('products', 'allergens', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('products', 'vegetarian', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('products', 'gluten_free', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('products', 'lactose_free', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('products', 'pairing_notes', "text DEFAULT NULL");
        $this->addColIfMissing('products', 'awards', "text DEFAULT NULL");
        $this->ensureUniqueIndex('products', 'idx_products_slug', ['slug']);

        // ===== carts =====
        if (!$this->tableExistsStrict('carts')) {
            $this->execute("CREATE TABLE `carts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('carts', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('carts', 'user_id', "int(11) NOT NULL");
        $this->addColIfMissing('carts', 'status', "varchar(20) NOT NULL DEFAULT 'open'");
        $this->addColIfMissing('carts', 'currency', "char(3) DEFAULT 'AUD'");
        $this->addColIfMissing('carts', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('carts', 'modified', "datetime DEFAULT NULL");
        $this->ensureIndex('carts', 'user_status', ['user_id','status']);
        $this->ensureForeignKey('carts', 'fk_carts_user', ['user_id'], 'users', ['id'], 'NO ACTION', 'NO ACTION');

        // ===== cart_items =====
        if (!$this->tableExistsStrict('cart_items')) {
            $this->execute("CREATE TABLE `cart_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('cart_items', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('cart_items', 'cart_id', "int(11) NOT NULL");
        $this->addColIfMissing('cart_items', 'product_id', "int(11) NOT NULL");
        $this->addColIfMissing('cart_items', 'qty', "int(11) NOT NULL DEFAULT 1");
        $this->addColIfMissing('cart_items', 'price', "decimal(10,2) NOT NULL");
        $this->addColIfMissing('cart_items', 'currency', "char(3) DEFAULT 'AUD'");
        $this->addColIfMissing('cart_items', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('cart_items', 'modified', "datetime DEFAULT NULL");
        $this->ensureUniqueIndex('cart_items', 'cart_product', ['cart_id','product_id']);
        $this->ensureIndex('cart_items', 'fk_ci_prod', ['product_id']);
        $this->ensureForeignKey('cart_items', 'fk_ci_cart', ['cart_id'], 'carts', ['id'], 'CASCADE', 'NO ACTION');
        $this->ensureForeignKey('cart_items', 'fk_ci_prod', ['product_id'], 'products', ['id'], 'NO ACTION', 'NO ACTION');

        // ===== delivery_slots =====
        if (!$this->tableExistsStrict('delivery_slots')) {
            $this->execute("CREATE TABLE `delivery_slots` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('delivery_slots', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('delivery_slots', 'name', "varchar(100) NOT NULL");
        $this->addColIfMissing('delivery_slots', 'dow', "tinyint(4) DEFAULT NULL");
        $this->addColIfMissing('delivery_slots', 'window_start', "time NOT NULL");
        $this->addColIfMissing('delivery_slots', 'window_end', "time NOT NULL");
        $this->addColIfMissing('delivery_slots', 'capacity', "int(11) DEFAULT NULL");
        $this->addColIfMissing('delivery_slots', 'is_active', "tinyint(1) NOT NULL DEFAULT 1");
        $this->addColIfMissing('delivery_slots', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('delivery_slots', 'modified', "datetime DEFAULT NULL");

        // ===== pickup_locations =====
        if (!$this->tableExistsStrict('pickup_locations')) {
            $this->execute("CREATE TABLE `pickup_locations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('pickup_locations', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('pickup_locations', 'name', "varchar(100) NOT NULL");
        $this->addColIfMissing('pickup_locations', 'address', "varchar(255) NOT NULL DEFAULT ''");
        $this->addColIfMissing('pickup_locations', 'opening_hours', "varchar(100) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'is_active', "tinyint(1) NOT NULL DEFAULT 1");
        $this->addColIfMissing('pickup_locations', 'created', "datetime DEFAULT current_timestamp()");
        $this->addColIfMissing('pickup_locations', 'modified', "datetime DEFAULT current_timestamp()");
        $this->addColIfMissing('pickup_locations', 'address_line_1', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'address_line_2', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'city', "varchar(255) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'postcode', "varchar(50) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'state', "varchar(100) DEFAULT NULL");
        $this->addColIfMissing('pickup_locations', 'suburb', "varchar(255) DEFAULT NULL");

        // ===== orders =====
        if (!$this->tableExistsStrict('orders')) {
            $this->execute("CREATE TABLE `orders` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('orders', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('orders', 'user_id', "int(11) DEFAULT NULL");
        $this->addColIfMissing('orders', 'email', "varchar(190) NOT NULL");
        $this->addColIfMissing('orders', 'full_name', "varchar(190) NOT NULL");
        $this->addColIfMissing('orders', 'address', "varchar(255) NOT NULL");
        $this->addColIfMissing('orders', 'city', "varchar(120) NOT NULL");
        $this->addColIfMissing('orders', 'postcode', "varchar(20) NOT NULL");
        $this->addColIfMissing('orders', 'country', "varchar(120) NOT NULL");
        $this->addColIfMissing('orders', 'currency', "char(3) NOT NULL DEFAULT 'AUD'");
        $this->addColIfMissing('orders', 'subtotal', "decimal(10,2) NOT NULL DEFAULT 0.00");
        $this->addColIfMissing('orders', 'shipping_fee', "decimal(10,2) NOT NULL DEFAULT 0.00");
        $this->addColIfMissing('orders', 'discount', "decimal(10,2) NOT NULL DEFAULT 0.00");
        $this->addColIfMissing('orders', 'total', "decimal(10,2) NOT NULL DEFAULT 0.00");
        $this->addColIfMissing('orders', 'status', "varchar(16) NOT NULL DEFAULT 'pending'");
        $this->addColIfMissing('orders', 'payment_status', "varchar(20) NOT NULL DEFAULT 'unpaid'");
        $this->addColIfMissing('orders', 'fulfillment_method', "varchar(20) NOT NULL DEFAULT 'delivery'");
        $this->addColIfMissing('orders', 'delivery_date', "date DEFAULT NULL");
        $this->addColIfMissing('orders', 'delivery_slot_id', "int(11) DEFAULT NULL");
        $this->addColIfMissing('orders', 'pickup_location_id', "int(11) DEFAULT NULL");
        $this->addColIfMissing('orders', 'delivery_instructions', "varchar(500) DEFAULT NULL");
        $this->addColIfMissing('orders', 'payment_method', "varchar(40) DEFAULT NULL");
        $this->addColIfMissing('orders', 'stock_deducted', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('orders', 'stock_deducted_at', "datetime DEFAULT NULL");
        $this->addColIfMissing('orders', 'payment_ref', "varchar(80) DEFAULT NULL");
        $this->addColIfMissing('orders', 'paid_at', "datetime DEFAULT NULL");
        $this->addColIfMissing('orders', 'notes', "text DEFAULT NULL");
        $this->addColIfMissing('orders', 'created', "datetime NOT NULL");
        $this->addColIfMissing('orders', 'modified', "datetime NOT NULL");
        $this->ensureIndex('orders', 'idx_orders_user', ['user_id']);
        $this->ensureIndex('orders', 'idx_orders_created', ['created']);
        $this->ensureIndex('orders', 'idx_orders_status', ['status']);
        $this->ensureIndex('orders', 'idx_orders_delivery_slot', ['delivery_slot_id']);
        $this->ensureIndex('orders', 'idx_orders_pickup_location', ['pickup_location_id']);
        $this->ensureForeignKey('orders', 'fk_orders_users', ['user_id'], 'users', ['id'], 'NO ACTION', 'NO ACTION');
        $this->ensureForeignKey('orders', 'fk_u25_orders_delivery_slot_1', ['delivery_slot_id'], 'delivery_slots', ['id'], 'SET NULL', 'CASCADE');
        $this->ensureForeignKey('orders', 'fk_u25_orders_pickup_location_1', ['pickup_location_id'], 'pickup_locations', ['id'], 'SET NULL', 'CASCADE');

        // ===== order_items =====
        if (!$this->tableExistsStrict('order_items')) {
            $this->execute("CREATE TABLE `order_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('order_items', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('order_items', 'order_id', "int(11) NOT NULL");
        $this->addColIfMissing('order_items', 'product_id', "int(11) DEFAULT NULL");
        $this->addColIfMissing('order_items', 'name', "varchar(190) NOT NULL");
        $this->addColIfMissing('order_items', 'slug', "varchar(190) DEFAULT NULL");
        $this->addColIfMissing('order_items', 'price', "decimal(10,2) NOT NULL");
        $this->addColIfMissing('order_items', 'currency', "char(3) NOT NULL DEFAULT 'AUD'");
        $this->addColIfMissing('order_items', 'qty', "int(10) UNSIGNED NOT NULL");
        $this->addColIfMissing('order_items', 'line_total', "decimal(10,2) NOT NULL");
        $this->addColIfMissing('order_items', 'snapshot', "mediumtext DEFAULT NULL");
        $this->addColIfMissing('order_items', 'created', "datetime NOT NULL");
        $this->addColIfMissing('order_items', 'modified', "datetime NOT NULL");
        $this->ensureIndex('order_items', 'idx_items_order', ['order_id']);
        $this->ensureIndex('order_items', 'idx_items_product', ['product_id']);
        $this->ensureForeignKey('order_items', 'fk_order_items_order', ['order_id'], 'orders', ['id'], 'CASCADE', 'CASCADE');
        $this->ensureForeignKey('order_items', 'fk_order_items_product', ['product_id'], 'products', ['id'], 'CASCADE', 'CASCADE');

        // ===== contact_messages =====
        if (!$this->tableExistsStrict('contact_messages')) {
            $this->execute("CREATE TABLE `contact_messages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        $this->ensureTableCollation('contact_messages', 'utf8mb4', 'utf8mb4_general_ci');
        $this->addColIfMissing('contact_messages', 'name', "varchar(100) NOT NULL");
        $this->addColIfMissing('contact_messages', 'email', "varchar(255) NOT NULL");
        $this->addColIfMissing('contact_messages', 'message', "text NOT NULL");
        $this->addColIfMissing('contact_messages', 'status', "varchar(50) NOT NULL DEFAULT 'new'");
        $this->addColIfMissing('contact_messages', 'replied_at', "datetime DEFAULT NULL");
        $this->addColIfMissing('contact_messages', 'reply_note', "text DEFAULT NULL");
        $this->addColIfMissing('contact_messages', 'replied_by', "int(11) DEFAULT NULL");
        $this->addColIfMissing('contact_messages', 'is_spam', "tinyint(1) NOT NULL DEFAULT 0");
        $this->addColIfMissing('contact_messages', 'created', "datetime NOT NULL DEFAULT current_timestamp()");
        $this->addColIfMissing('contact_messages', 'modified', "datetime NOT NULL DEFAULT current_timestamp()");

        // ===== site_settings =====
        if (!$this->tableExistsStrict('site_settings')) {
            $this->execute("CREATE TABLE `site_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci");
        }
        $this->ensureTableCollation('site_settings', 'latin1', 'latin1_swedish_ci');
        $this->addColIfMissing('site_settings', 'setting_key', "varchar(100) NOT NULL");
        $this->addColIfMissing('site_settings', 'setting_value', "text DEFAULT NULL");
        $this->addColIfMissing('site_settings', 'setting_type', "varchar(20) NOT NULL DEFAULT 'text'");
        $this->addColIfMissing('site_settings', 'created', "datetime DEFAULT NULL");
        $this->addColIfMissing('site_settings', 'modified', "datetime DEFAULT NULL");
        $this->ensureUniqueIndex('site_settings', 'UNIQUE_SETTING_KEY', ['setting_key']);
    }

    public function down(): void
    {
        // No destructive rollback.
    }
}
