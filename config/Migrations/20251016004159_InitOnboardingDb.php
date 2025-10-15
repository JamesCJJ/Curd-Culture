<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InitOnboardingDb extends AbstractMigration
{
    public function change(): void
    {
        // -------------------- users --------------------
        $this->table('users', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('reset_code_hash', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('reset_expires', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('reset_attempts', 'integer', ['default' => 0, 'null' => false])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('role', 'string', ['limit' => 20, 'default' => 'user', 'null' => false])
        ->addColumn('status', 'string', ['limit' => 50, 'default' => 'active', 'null' => true])
        ->addColumn('timezone', 'string', ['limit' => 64, 'default' => 'UTC', 'null' => true])
        ->addColumn('language', 'string', ['limit' => 8, 'default' => 'en', 'null' => true])
        ->addColumn('theme', 'string', ['limit' => 20, 'default' => 'auto', 'null' => true])
        ->addColumn('notify_email', 'boolean', ['default' => 1, 'null' => true])
        ->addColumn('notify_push', 'boolean', ['default' => 0, 'null' => true])
        ->addColumn('pref_theme', 'string', ['limit' => 10, 'default' => 'auto', 'null' => true])
        ->addColumn('pref_contrast', 'string', ['limit' => 10, 'default' => 'normal', 'null' => true])
        ->addColumn('pref_font_scale', 'decimal', ['precision' => 3, 'scale' => 2, 'default' => '1.00', 'null' => true])
        ->addColumn('pref_lang', 'string', ['limit' => 8, 'default' => 'en', 'null' => true])
        ->addColumn('email_optin', 'boolean', ['default' => 1, 'null' => true])
        ->addColumn('cookie_consent', 'boolean', ['default' => 0, 'null' => true])
        ->addIndex(['reset_expires'], ['name' => 'idx_users_reset_expires'])
        ->create();

        // -------------------- addresses --------------------
        $this->table('addresses', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
        ->addColumn('user_id', 'integer', ['null' => false])
        ->addColumn('type', 'string', ['limit' => 20, 'default' => 'billing', 'null' => false])
        ->addColumn('first_name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('last_name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('company', 'string', ['limit' => 150, 'null' => true, 'default' => null])
        ->addColumn('address_line_1', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('address_line_2', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('suburb', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('state', 'string', ['limit' => 50, 'null' => false])
        ->addColumn('postcode', 'string', ['limit' => 10, 'null' => false])
        ->addColumn('country', 'string', ['limit' => 100, 'default' => 'Australia', 'null' => false])
        ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'default' => null])
        ->addColumn('is_default', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['user_id'], ['name' => 'idx_addresses_user'])
        ->addIndex(['user_id', 'type'], ['name' => 'idx_addresses_user_type'])
        ->create();

        // -------------------- contacts --------------------
        $this->table('contacts', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
        ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('message', 'text', ['null' => false])
        ->addColumn('created', 'datetime', ['null' => false])
        ->addColumn('modified', 'datetime', ['null' => false])
        ->create();

        // -------------------- articles --------------------
        $this->table('articles', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('user_id', 'integer', ['null' => false])
        ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('slug', 'string', ['limit' => 191, 'null' => false])
        ->addColumn('body', 'text', ['null' => true, 'default' => null])
        ->addColumn('published', 'boolean', ['default' => 0, 'null' => true])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['slug'], ['unique' => true, 'name' => 'slug'])
        ->addIndex(['user_id'], ['name' => 'user_key'])
        ->create();

        // -------------------- tags --------------------
        $this->table('tags', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('title', 'string', ['limit' => 191, 'null' => true, 'default' => null])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['title'], ['unique' => true, 'name' => 'title'])
        ->create();

        // -------------------- articles_tags (join) --------------------
        $this->table('articles_tags', [
            'id' => false,
            'primary_key' => ['article_id', 'tag_id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('article_id', 'integer', ['null' => false])
        ->addColumn('tag_id', 'integer', ['null' => false])
        ->addIndex(['tag_id'], ['name' => 'tag_key'])
        ->create();

        // -------------------- products --------------------
        $this->table('products', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('name', 'string', ['limit' => 180, 'null' => false])
        ->addColumn('slug', 'string', ['limit' => 190, 'null' => false])
        ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'null' => false])
        ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'AUD', 'null' => false])
        ->addColumn('summary', 'text', ['null' => true, 'default' => null])
        ->addColumn('description', 'text', ['null' => true, 'default' => null])
        ->addColumn('image_url', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('gallery', 'text', ['null' => true, 'default' => null])
        ->addColumn('rating', 'decimal', ['precision' => 3, 'scale' => 2, 'null' => true, 'default' => null])
        ->addColumn('stock', 'integer', ['default' => 0, 'null' => false])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('origin_country', 'string', ['limit' => 64, 'null' => true, 'default' => null])
        ->addColumn('milk_type', 'string', ['limit' => 32, 'null' => true, 'default' => null])
        ->addColumn('age', 'string', ['limit' => 32, 'null' => true, 'default' => null])
        ->addColumn('style', 'string', ['limit' => 64, 'null' => true, 'default' => null])
        ->addColumn('rennet', 'string', ['limit' => 32, 'null' => true, 'default' => null])
        ->addColumn('pasteurised', 'enum', ['values' => ['yes','no'], 'null' => true, 'default' => null])
        ->addColumn('fat_content', 'string', ['limit' => 32, 'null' => true, 'default' => null])
        ->addColumn('allergens', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('vegetarian', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('gluten_free', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('lactose_free', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('pairing_notes', 'text', ['null' => true, 'default' => null])
        ->addColumn('awards', 'text', ['null' => true, 'default' => null])
        ->addIndex(['slug'], ['unique' => true, 'name' => 'idx_products_slug'])
        ->create();

        // -------------------- carts --------------------
        $this->table('carts', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('user_id', 'integer', ['null' => false])
        ->addColumn('status', 'string', ['limit' => 20, 'default' => 'open', 'null' => false])
        ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'AUD', 'null' => true])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['user_id', 'status'], ['name' => 'user_status'])
        ->create();

        // -------------------- cart_items --------------------
        $this->table('cart_items', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('cart_id', 'integer', ['null' => false])
        ->addColumn('product_id', 'integer', ['null' => false])
        ->addColumn('qty', 'integer', ['default' => 1, 'null' => false])
        ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
        ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'AUD', 'null' => true])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['cart_id', 'product_id'], ['unique' => true, 'name' => 'cart_product'])
        ->addIndex(['product_id'], ['name' => 'fk_ci_prod'])
        ->create();

        // -------------------- delivery_slots --------------------
        $this->table('delivery_slots', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('dow', 'integer', ['limit' => 4, 'null' => true, 'default' => null])
        ->addColumn('window_start', 'time', ['null' => false])
        ->addColumn('window_end', 'time', ['null' => false])
        ->addColumn('capacity', 'integer', ['null' => true, 'default' => null])
        ->addColumn('is_active', 'boolean', ['default' => 1, 'null' => false])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->create();

        // -------------------- pickup_locations --------------------
        $this->table('pickup_locations', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('address', 'string', ['limit' => 255, 'default' => '', 'null' => false])
        ->addColumn('opening_hours', 'string', ['limit' => 100, 'null' => true, 'default' => null])
        ->addColumn('is_active', 'boolean', ['default' => 1, 'null' => false])
        ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
        ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
        ->addColumn('address_line_1', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('address_line_2', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('city', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('postcode', 'string', ['limit' => 50, 'null' => true, 'default' => null])
        ->addColumn('state', 'string', ['limit' => 100, 'null' => true, 'default' => null])
        ->addColumn('suburb', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->create();

        // -------------------- orders --------------------
        $this->table('orders', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('user_id', 'integer', ['null' => true, 'default' => null])
        ->addColumn('email', 'string', ['limit' => 190, 'null' => false])
        ->addColumn('full_name', 'string', ['limit' => 190, 'null' => false])
        ->addColumn('address', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('city', 'string', ['limit' => 120, 'null' => false])
        ->addColumn('postcode', 'string', ['limit' => 20, 'null' => false])
        ->addColumn('country', 'string', ['limit' => 120, 'null' => false])
        ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'AUD', 'null' => false])
        ->addColumn('subtotal', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'null' => false])
        ->addColumn('shipping_fee', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'null' => false])
        ->addColumn('discount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'null' => false])
        ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00', 'null' => false])
        ->addColumn('status', 'string', ['limit' => 16, 'default' => 'pending', 'null' => false])
        ->addColumn('payment_status', 'string', ['limit' => 20, 'default' => 'unpaid', 'null' => false])
        ->addColumn('fulfillment_method', 'string', ['limit' => 20, 'default' => 'delivery', 'null' => false])
        ->addColumn('delivery_date', 'date', ['null' => true, 'default' => null])
        ->addColumn('delivery_slot_id', 'integer', ['null' => true, 'default' => null])
        ->addColumn('pickup_location_id', 'integer', ['null' => true, 'default' => null])
        ->addColumn('delivery_instructions', 'string', ['limit' => 500, 'null' => true, 'default' => null])
        ->addColumn('payment_method', 'string', ['limit' => 40, 'null' => true, 'default' => null])
        ->addColumn('stock_deducted', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('stock_deducted_at', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('payment_ref', 'string', ['limit' => 80, 'null' => true, 'default' => null])
        ->addColumn('paid_at', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('notes', 'text', ['null' => true, 'default' => null])
        ->addColumn('created', 'datetime', ['null' => false])
        ->addColumn('modified', 'datetime', ['null' => false])
        ->addIndex(['user_id'], ['name' => 'idx_orders_user'])
        ->addIndex(['created'], ['name' => 'idx_orders_created'])
        ->addIndex(['status'], ['name' => 'idx_orders_status'])
        ->addIndex(['delivery_slot_id'], ['name' => 'idx_orders_delivery_slot'])
        ->addIndex(['pickup_location_id'], ['name' => 'idx_orders_pickup_location'])
        ->create();

        // -------------------- order_items --------------------
        $this->table('order_items', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('order_id', 'integer', ['null' => false])
        ->addColumn('product_id', 'integer', ['null' => true, 'default' => null])
        ->addColumn('name', 'string', ['limit' => 190, 'null' => false])
        ->addColumn('slug', 'string', ['limit' => 190, 'null' => true, 'default' => null])
        ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
        ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'AUD', 'null' => false])
        ->addColumn('qty', 'integer', ['signed' => false, 'null' => false])
        ->addColumn('line_total', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
        ->addColumn('snapshot', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM, 'null' => true, 'default' => null])
        ->addColumn('created', 'datetime', ['null' => false])
        ->addColumn('modified', 'datetime', ['null' => false])
        ->addIndex(['order_id'], ['name' => 'idx_items_order'])
        ->addIndex(['product_id'], ['name' => 'idx_items_product'])
        ->create();

        // -------------------- contact_messages --------------------
        $this->table('contact_messages', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
        ])
        ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('message', 'text', ['null' => false])
        ->addColumn('status', 'string', ['limit' => 50, 'default' => 'new', 'null' => false])
        ->addColumn('replied_at', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('reply_note', 'text', ['null' => true, 'default' => null])
        ->addColumn('replied_by', 'integer', ['null' => true, 'default' => null])
        ->addColumn('is_spam', 'boolean', ['default' => 0, 'null' => false])
        ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
        ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
        ->create();

        // -------------------- site_settings --------------------
        $this->table('site_settings', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci',
        ])
        ->addColumn('setting_key', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('setting_value', 'text', ['null' => true, 'default' => null])
        ->addColumn('setting_type', 'string', ['limit' => 20, 'default' => 'text', 'null' => false])
        ->addColumn('created', 'datetime', ['null' => true, 'default' => null])
        ->addColumn('modified', 'datetime', ['null' => true, 'default' => null])
        ->addIndex(['setting_key'], ['unique' => true, 'name' => 'UNIQUE_SETTING_KEY'])
        ->create();

        // -------------------- Foreign Keys --------------------
        // addresses.user_id -> users.id
        $this->table('addresses')
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'fk_addresses_users'])
            ->update();

        // articles.user_id -> users.id
        $this->table('articles')
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'user_key'])
            ->update();

        // articles_tags
        $this->table('articles_tags')
            ->addForeignKey('article_id', 'articles', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'article_key'])
            ->addForeignKey('tag_id', 'tags', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'tag_key'])
            ->update();

        // carts.user_id -> users.id
        $this->table('carts')
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'fk_carts_user'])
            ->update();

        // cart_items
        $this->table('cart_items')
            ->addForeignKey('cart_id', 'carts', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION', 'constraint' => 'fk_ci_cart'])
            ->addForeignKey('product_id', 'products', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'fk_ci_prod'])
            ->update();

        // orders FKs
        $this->table('orders')
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION', 'constraint' => 'fk_orders_users'])
            ->addForeignKey('delivery_slot_id', 'delivery_slots', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE', 'constraint' => 'fk_u25_orders_delivery_slot_1'])
            ->addForeignKey('pickup_location_id', 'pickup_locations', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE', 'constraint' => 'fk_u25_orders_pickup_location_1'])
            ->update();

        // order_items
        $this->table('order_items')
            ->addForeignKey('order_id', 'orders', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE', 'constraint' => 'fk_order_items_order'])
            ->addForeignKey('product_id', 'products', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE', 'constraint' => 'fk_order_items_product'])
            ->update();
    }
}
