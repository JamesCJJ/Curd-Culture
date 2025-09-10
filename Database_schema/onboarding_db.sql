-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-09-10 06:40:21
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `onboarding_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `body` text DEFAULT NULL,
  `published` tinyint(1) DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `title`, `slug`, `body`, `published`, `created`, `modified`) VALUES
(1, 1, 'First Post', 'first-post', 'This is the first post.', 1, '2025-08-12 04:25:41', '2025-08-12 04:25:41');

-- --------------------------------------------------------

--
-- 表的结构 `articles_tags`
--

CREATE TABLE `articles_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `currency` char(3) DEFAULT 'AUD',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `status`, `currency`, `created`, `modified`) VALUES
(1, 5, 'ordered', 'AUD', NULL, NULL),
(2, 5, 'open', 'AUD', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `currency` char(3) DEFAULT 'AUD',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `replied_at` datetime DEFAULT NULL,
  `reply_note` text DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `is_spam` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `replied_at`, `reply_note`, `replied_by`, `is_spam`, `created`, `modified`) VALUES
(1, '213', '2@1.com', '123', 'new', '2025-08-18 16:13:46', '123441', 2, 0, '2025-08-11 19:52:01', '2025-08-18 16:13:46'),
(3, 'AAA', 'abc@123.com', 'Hi,Team202', 'read', '2025-08-21 05:45:10', '12323', 2, 0, '2025-08-18 16:35:44', '2025-09-06 22:19:38'),
(4, '1', '1!1@11.com', '123', 'new', '2025-08-27 06:14:49', '123', 2, 0, '2025-08-21 05:45:29', '2025-08-27 06:14:49'),
(5, '11', '1!1@11.com', '123', 'new', NULL, NULL, NULL, 0, '2025-08-27 11:34:22', '2025-08-27 11:34:22'),
(6, '1', '1!1@11.com', '11', 'read', NULL, NULL, NULL, 0, '2025-08-27 12:06:50', '2025-09-06 22:19:38'),
(7, '1', '1!1@11.com', '123123', 'read', NULL, NULL, NULL, 0, '2025-08-28 05:35:21', '2025-09-04 05:22:59'),
(8, '123122132', '121214@1.com', 'adaw', 'in_progress', NULL, NULL, NULL, 0, '2025-09-04 05:29:08', '2025-09-06 22:29:16'),
(9, '111', '1111@111.com', '1242132213', 'unread', NULL, NULL, NULL, 0, '2025-09-08 07:46:31', '2025-09-08 07:46:31'),
(10, 'TestCustomer', 'TestCustomer@gmail.com', 'New order #1 placed. Total: AUD 23.40', 'read', NULL, NULL, NULL, 0, '2025-09-09 18:42:15', '2025-09-09 18:44:35');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `full_name` varchar(190) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(120) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `country` varchar(120) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'AUD',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(40) DEFAULT NULL,
  `payment_ref` varchar(80) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `email`, `full_name`, `address`, `city`, `postcode`, `country`, `currency`, `subtotal`, `shipping_fee`, `discount`, `total`, `status`, `payment_status`, `payment_method`, `payment_ref`, `paid_at`, `notes`, `created`, `modified`) VALUES
(1, 5, 'TestCustomer@gmail.com', 'TestCustomer', 'U1  1 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 10.50, 12.90, 0.00, 23.40, 'pending', 'unpaid', NULL, NULL, NULL, NULL, '2025-09-09 18:42:15', '2025-09-09 18:42:15');

-- --------------------------------------------------------

--
-- 表的结构 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'AUD',
  `qty` int(10) UNSIGNED NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `snapshot` mediumtext DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `slug`, `price`, `currency`, `qty`, `line_total`, `snapshot`, `created`, `modified`) VALUES
(1, 1, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 0.00, NULL, '2025-09-09 18:42:15', '2025-09-09 18:42:15');

-- --------------------------------------------------------

--
-- 表的结构 `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20250811120211, 'CreateContactMessages', '2025-08-11 19:49:01', '2025-08-11 19:49:01', 0);

-- --------------------------------------------------------

--
-- 表的结构 `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'AUD',
  `summary` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `origin_country` varchar(64) DEFAULT NULL,
  `milk_type` varchar(32) DEFAULT NULL,
  `age` varchar(32) DEFAULT NULL,
  `style` varchar(64) DEFAULT NULL,
  `rennet` varchar(32) DEFAULT NULL,
  `pasteurised` enum('yes','no') DEFAULT NULL,
  `fat_content` varchar(32) DEFAULT NULL,
  `allergens` varchar(255) DEFAULT NULL,
  `vegetarian` tinyint(1) NOT NULL DEFAULT 0,
  `gluten_free` tinyint(1) NOT NULL DEFAULT 0,
  `lactose_free` tinyint(1) NOT NULL DEFAULT 0,
  `pairing_notes` text DEFAULT NULL,
  `awards` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `price`, `currency`, `summary`, `description`, `image_url`, `gallery`, `rating`, `stock`, `created`, `modified`, `origin_country`, `milk_type`, `age`, `style`, `rennet`, `pasteurised`, `fat_content`, `allergens`, `vegetarian`, `gluten_free`, `lactose_free`, `pairing_notes`, `awards`) VALUES
(1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 'Sharp, crumbly English classic.', 'Traditional farmhouse cheddar with a long lingering finish.', NULL, NULL, 4.40, 120, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'United Kingdom', 'Cow', '12 months', 'Hard', 'Animal', 'no', '34%', 'Milk', 0, 1, 0, 'Cider, crusty bread', NULL),
(2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 'Rich and buttery soft cheese.', 'Creamy paste with a delicate bloomy rind and mushroomy aroma.', NULL, NULL, 4.60, 95, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'France', 'Cow', '4 weeks', 'Soft-ripened', 'Animal', 'yes', '28%', 'Milk', 0, 1, 0, 'Sparkling wine, strawberries', NULL),
(3, 'Goat Feta', 'goat-feta', 7.40, 'AUD', 'Salty and tangy, perfect for salads.', 'Traditional brined feta made from 100% goat\'s milk.', NULL, NULL, 4.20, 140, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Greece', 'Goat', '8 weeks', 'Fresh', 'Vegetarian', 'yes', '23%', 'Milk', 1, 1, 0, 'Watermelon, olives', NULL),
(4, 'Aged Gouda 18M', 'aged-gouda-18m', 14.90, 'AUD', 'Caramel notes and crunchy crystals.', 'Dutch gouda matured for 18 months, deep butterscotch flavor.', NULL, NULL, 4.80, 60, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Netherlands', 'Cow', '18 months', 'Hard', 'Animal', 'no', '32%', 'Milk', 0, 1, 0, 'Brown ale, toasted nuts', 'World Cheese Awards – Gold'),
(5, 'Blue Vein Classic', 'blue-vein-classic', 11.20, 'AUD', 'Bold and spicy blue.', 'Creamy body streaked with blue veins; assertive and savory.', NULL, NULL, 4.10, 70, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Denmark', 'Cow', '3 months', 'Blue', 'Animal', 'yes', '30%', 'Milk', 0, 1, 0, 'Port wine, pears', NULL),
(6, 'Manchego Curado', 'manchego-curado', 13.50, 'AUD', 'Nutty sheep’s milk from La Mancha.', 'Pressed cheese with firm texture and sweet grassy aroma.', NULL, NULL, 4.50, 55, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Spain', 'Sheep', '9 months', 'Semi-hard', 'Animal', 'no', '27%', 'Milk', 0, 1, 0, 'Quince paste, almonds', NULL),
(7, 'Parmigiano Reggiano 24M', 'parmigiano-reggiano-24m', 16.90, 'AUD', 'Grana with deep umami.', 'PDO Italian cheese aged 24 months; granular, savory, crystalline.', NULL, NULL, 4.90, 80, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Italy', 'Cow', '24 months', 'Hard', 'Animal', 'no', '29%', 'Milk', 0, 1, 1, 'Balsamic vinegar, pasta', 'DOP'),
(8, 'Camembert Rustic', 'camembert-rustic', 9.80, 'AUD', 'Earthy bloomy rind.', 'Runny heart at room temp; mushroom and hay notes.', NULL, NULL, 4.30, 88, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'France', 'Cow', '4 weeks', 'Soft-ripened', 'Animal', 'yes', '25%', 'Milk', 0, 1, 0, 'Dry cider, apples', NULL),
(9, 'Buffalo Mozzarella', 'buffalo-mozzarella', 6.90, 'AUD', 'Delicate and milky.', 'Fresh stretched-curd cheese made from buffalo milk.', NULL, NULL, 4.00, 160, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Italy', 'Buffalo', 'Fresh', 'Fresh', 'Vegetarian', 'yes', '20%', 'Milk', 1, 1, 0, 'Tomatoes, basil, olive oil', NULL),
(10, 'Taleggio Washed Rind', 'taleggio-washed-rind', 12.30, 'AUD', 'Fruity, tangy, aromatic.', 'Square washed-rind with soft interior and orange rind.', NULL, NULL, 4.20, 68, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Italy', 'Cow', '2 months', 'Washed rind', 'Animal', 'yes', '28%', 'Milk', 0, 1, 0, 'Pilsner, roasted mushrooms', NULL),
(11, 'Swiss Emmental', 'swiss-emmental', 12.00, 'AUD', 'Nutty with signature eyes.', 'Large-holed Swiss cheese with sweet buttery finish.', NULL, NULL, 4.10, 75, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Switzerland', 'Cow', '4 months', 'Semi-hard', 'Animal', 'no', '28%', 'Milk', 0, 1, 0, 'Riesling, ham sandwiches', NULL),
(12, 'Smoked Provolone', 'smoked-provolone', 9.40, 'AUD', 'Gentle smoke and stretch.', 'Southern Italian style, lightly smoked, great for melting.', NULL, NULL, 4.00, 110, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Italy', 'Cow', '3 months', 'Semi-hard', 'Vegetarian', 'yes', '26%', 'Milk', 1, 1, 0, 'Grilled vegetables, lager', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `title` varchar(191) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` varchar(50) DEFAULT 'active',
  `timezone` varchar(64) DEFAULT 'UTC',
  `language` varchar(8) DEFAULT 'en',
  `theme` varchar(20) DEFAULT 'auto',
  `notify_email` tinyint(1) DEFAULT 1,
  `notify_push` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created`, `modified`, `role`, `status`, `timezone`, `language`, `theme`, `notify_email`, `notify_push`) VALUES
(1, 'cakephp@example.com', '$2y$10$zRylcS5T3aPvsvdScsO5KOw5ndzDwbFF7qHhF1fuh.TFfWcBg8lKO', '2025-08-12 04:25:41', '2025-08-12 04:25:41', 'user', 'active', 'UTC', 'en', 'auto', 1, 0),
(2, 'admin@curdandculture.com', '$2y$10$S2htStkFy/.33Lm848bSq.D7hUq02ngHfg5I0ycSh4XdAEN7IC7UG', '2025-08-19 00:22:26', '2025-08-19 00:22:26', 'admin', 'active', 'UTC', 'en', 'auto', 1, 0),
(4, 'Carl@curdandculture.com', '$2y$10$YP1iwuR1AyQmpMJWbb1vwelDZCgYw8kQksrEgJNWvKp3DOOYg8DBC', '2025-09-07 01:02:57', '2025-09-07 01:02:57', 'customer', 'active', 'UTC', 'en', 'auto', 1, 0),
(5, 'TestCustomer@gmail.com', '$2y$10$67uG.sOhtqe8PlsDXQcqeuLFXdjD4zuK1ySP8ynZf9eD6sxe5fFqG', '2025-09-09 17:11:37', '2025-09-09 17:11:37', 'customer', 'active', 'UTC', 'en', 'auto', 1, 0);

--
-- 转储表的索引
--

--
-- 表的索引 `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_key` (`user_id`);

--
-- 表的索引 `articles_tags`
--
ALTER TABLE `articles_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_key` (`tag_id`);

--
-- 表的索引 `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_status` (`user_id`,`status`);

--
-- 表的索引 `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_product` (`cart_id`,`product_id`),
  ADD KEY `fk_ci_prod` (`product_id`);

--
-- 表的索引 `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_created` (`created`),
  ADD KEY `idx_orders_status` (`status`);

--
-- 表的索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_order` (`order_id`),
  ADD KEY `idx_items_product` (`product_id`);

--
-- 表的索引 `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- 表的索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `idx_products_slug` (`slug`);

--
-- 表的索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 限制导出的表
--

--
-- 限制表 `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `user_key` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 限制表 `articles_tags`
--
ALTER TABLE `articles_tags`
  ADD CONSTRAINT `article_key` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`),
  ADD CONSTRAINT `tag_key` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);

--
-- 限制表 `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 限制表 `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- 限制表 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 限制表 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
