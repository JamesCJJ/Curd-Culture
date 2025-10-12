-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-10-12 02:29:34
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
-- 表的结构 `addresses`
--

CREATE TABLE `addresses` (
                             `id` int(11) NOT NULL,
                             `user_id` int(11) NOT NULL,
                             `type` varchar(20) NOT NULL DEFAULT 'billing',
                             `first_name` varchar(100) NOT NULL,
                             `last_name` varchar(100) NOT NULL,
                             `company` varchar(150) DEFAULT NULL,
                             `address_line_1` varchar(255) NOT NULL,
                             `address_line_2` varchar(255) DEFAULT NULL,
                             `suburb` varchar(100) NOT NULL,
                             `state` varchar(50) NOT NULL,
                             `postcode` varchar(10) NOT NULL,
                             `country` varchar(100) NOT NULL DEFAULT 'Australia',
                             `phone` varchar(20) DEFAULT NULL,
                             `is_default` tinyint(1) NOT NULL DEFAULT 0,
                             `created` datetime DEFAULT NULL,
                             `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `type`, `first_name`, `last_name`, `company`, `address_line_1`, `address_line_2`, `suburb`, `state`, `postcode`, `country`, `phone`, `is_default`, `created`, `modified`) VALUES
    (11, 5, 'shipping', 'Junjue', 'Chang', 'HutaTai Securities', 'U1  1 Browns Rd', 'U 4  15 BROWNS RD', 'CLAYTON', 'VIC', '3168', 'Australia', '0451823464', 0, '2025-10-08 09:01:20', '2025-10-08 12:38:11');

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
                                                                                       (2, 5, 'ordered', 'AUD', NULL, NULL),
                                                                                       (3, 5, 'ordered', 'AUD', '2025-09-10 10:37:15', '2025-09-10 10:37:15'),
                                                                                       (4, 5, 'ordered', 'AUD', '2025-09-10 13:39:20', '2025-09-10 13:39:20'),
                                                                                       (5, 5, 'ordered', 'AUD', '2025-09-10 14:37:59', '2025-09-10 14:37:59'),
                                                                                       (6, 5, 'ordered', 'AUD', '2025-09-11 06:24:55', '2025-09-11 06:24:55'),
                                                                                       (7, 5, 'ordered', 'AUD', '2025-10-04 17:11:42', '2025-10-04 17:11:42'),
                                                                                       (8, 5, 'ordered', 'AUD', '2025-10-04 17:56:53', '2025-10-04 17:56:53'),
                                                                                       (9, 5, 'ordered', 'AUD', '2025-10-04 17:58:34', '2025-10-04 17:58:34'),
                                                                                       (10, 5, 'ordered', 'AUD', '2025-10-04 18:34:10', '2025-10-04 18:34:10'),
                                                                                       (11, 5, 'ordered', 'AUD', '2025-10-04 18:42:38', '2025-10-04 18:42:38'),
                                                                                       (12, 5, 'ordered', 'AUD', '2025-10-06 12:54:25', '2025-10-06 12:54:25'),
                                                                                       (13, 5, 'ordered', 'AUD', '2025-10-06 13:41:38', '2025-10-06 13:41:38'),
                                                                                       (14, 5, 'ordered', 'AUD', '2025-10-06 13:41:51', '2025-10-06 13:41:51'),
                                                                                       (15, 5, 'ordered', 'AUD', '2025-10-06 14:19:36', '2025-10-06 14:19:36'),
                                                                                       (16, 5, 'ordered', 'AUD', '2025-10-06 17:41:34', '2025-10-06 17:41:34'),
                                                                                       (17, 5, 'ordered', 'AUD', '2025-10-06 19:05:28', '2025-10-06 19:05:28'),
                                                                                       (18, 5, 'ordered', 'AUD', '2025-10-06 19:20:36', '2025-10-06 19:20:36'),
                                                                                       (19, 5, 'ordered', 'AUD', '2025-10-06 19:28:36', '2025-10-06 19:28:36'),
                                                                                       (20, 5, 'ordered', 'AUD', '2025-10-06 19:37:31', '2025-10-06 19:37:31'),
                                                                                       (21, 5, 'ordered', 'AUD', '2025-10-06 19:50:55', '2025-10-06 19:50:55'),
                                                                                       (22, 5, 'ordered', 'AUD', '2025-10-06 20:30:04', '2025-10-06 20:30:04'),
                                                                                       (23, 5, 'ordered', 'AUD', '2025-10-06 20:31:33', '2025-10-06 20:31:33'),
                                                                                       (24, 5, 'open', 'AUD', '2025-10-08 08:42:18', '2025-10-08 08:42:18'),
                                                                                       (25, 4, 'ordered', 'AUD', '2025-10-12 00:20:45', '2025-10-12 00:20:45'),
                                                                                       (26, 4, 'ordered', 'AUD', '2025-10-12 00:27:47', '2025-10-12 00:27:47');

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
-- 表的结构 `contacts`
--

CREATE TABLE `contacts` (
                            `id` int(11) NOT NULL,
                            `name` varchar(255) NOT NULL,
                            `email` varchar(255) NOT NULL,
                            `message` text NOT NULL,
                            `created` datetime NOT NULL,
                            `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created`, `modified`) VALUES
                                                                                     (1, 'John Smith', 'john.smith@example.com', 'Hello! I am interested in your services. Could you please provide more information about your pricing plans?', '2025-08-22 09:24:25', '2025-08-22 09:24:25'),
                                                                                     (2, 'Sarah Johnson', 'sarah.j@email.com', 'I had a great experience with your team last month. I would like to schedule another consultation to discuss expanding our current project scope. When would be a good time to connect?', '2025-08-23 09:24:25', '2025-08-23 09:24:25'),
                                                                                     (3, 'Michael Brown', 'mbrown@company.org', 'Technical support needed - experiencing issues with login functionality.', '2025-08-24 04:24:25', '2025-08-24 04:24:25'),
                                                                                     (4, 'Lisa Davis', 'lisa.davis@startup.co', 'We are a new startup looking for development partners. Would love to discuss potential collaboration opportunities. Our budget is flexible and we are looking for long-term partnership.', '2025-08-24 07:24:25', '2025-08-24 07:24:25'),
                                                                                     (5, 'David Wilson', 'david.w@consulting.net', 'Question about API integration.', '2025-08-24 08:54:25', '2025-08-24 08:54:25');

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
                                                                                                                                                            (10, 'TestCustomer', 'TestCustomer@gmail.com', 'New order #1 placed. Total: AUD 23.40', 'read', NULL, NULL, NULL, 0, '2025-09-09 18:42:15', '2025-09-09 18:44:35'),
                                                                                                                                                            (11, 'TestCustomer', 'TestCustomer@gmail.com', 'New order #2 placed. Total: AUD 23.40', 'unread', NULL, NULL, NULL, 0, '2025-09-10 09:18:49', '2025-09-10 09:18:49'),
                                                                                                                                                            (12, 'Junjue Chang', 'TestCustomer@gmail.com', 'New order #3 placed. Total: AUD 44.40', 'unread', NULL, NULL, NULL, 0, '2025-09-10 13:04:44', '2025-09-10 13:04:44'),
                                                                                                                                                            (13, 'TestCustomer', 'TestCustomer@gmail.com', 'New order #4 placed. Total: AUD 41.30', 'unread', NULL, NULL, NULL, 0, '2025-09-10 14:37:53', '2025-09-10 14:37:53'),
                                                                                                                                                            (14, 'TestCustomer', 'TestCustomer@gmail.com', 'New order #5 placed. Total: AUD 21.80', 'unread', NULL, NULL, NULL, 0, '2025-09-11 06:24:41', '2025-09-11 06:24:41'),
                                                                                                                                                            (15, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #6 placed. Total: AUD 41.20', 'unread', NULL, NULL, NULL, 0, '2025-10-04 16:39:18', '2025-10-04 16:39:18'),
                                                                                                                                                            (16, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #7 placed. Total: AUD 119.70', 'unread', NULL, NULL, NULL, 0, '2025-10-04 17:48:30', '2025-10-04 17:48:30'),
                                                                                                                                                            (17, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #8 placed. Total: AUD 30.70', 'unread', NULL, NULL, NULL, 0, '2025-10-04 17:58:14', '2025-10-04 17:58:14'),
                                                                                                                                                            (18, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #12 placed. Total: AUD 21.80', 'unread', NULL, NULL, NULL, 0, '2025-10-06 12:54:32', '2025-10-06 12:54:32'),
                                                                                                                                                            (19, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #13 placed via Bank Transfer. Total: AUD 21.80', 'unread', NULL, NULL, NULL, 0, '2025-10-06 13:41:43', '2025-10-06 13:41:43'),
                                                                                                                                                            (20, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #18 (PICKUP) Total: AUD 8.90', 'unread', NULL, NULL, NULL, 0, '2025-10-06 19:10:28', '2025-10-06 19:10:28'),
                                                                                                                                                            (21, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #19 (PICKUP) Total: AUD 11.20', 'unread', NULL, NULL, NULL, 0, '2025-10-06 19:20:45', '2025-10-06 19:20:45'),
                                                                                                                                                            (22, 'Aakhenteros', 'TestCustomer@gmail.com', 'New order #20 (PICKUP) Total: AUD 10.50', 'unread', NULL, NULL, NULL, 0, '2025-10-06 19:28:43', '2025-10-06 19:28:43'),
                                                                                                                                                            (23, 'Junjue Chang', 'TestCustomer@gmail.com', 'New order #21 (DELIVERY) Total: AUD 21.80', 'read', '2025-10-08 12:13:27', 'hi', NULL, 0, '2025-10-06 19:50:15', '2025-10-08 12:13:27'),
                                                                                                                                                            (24, 'Junjue Chang', 'Carl@curdandculture.com', 'New order #30 (PICKUP) Total: AUD 7.40', 'unread', NULL, NULL, NULL, 0, '2025-10-12 00:27:55', '2025-10-12 00:27:55');

-- --------------------------------------------------------

--
-- 表的结构 `delivery_slots`
--

CREATE TABLE `delivery_slots` (
                                  `id` int(11) NOT NULL,
                                  `name` varchar(100) NOT NULL,
                                  `dow` tinyint(4) DEFAULT NULL,
                                  `window_start` time NOT NULL,
                                  `window_end` time NOT NULL,
                                  `capacity` int(11) DEFAULT NULL,
                                  `is_active` tinyint(1) NOT NULL DEFAULT 1,
                                  `created` datetime DEFAULT NULL,
                                  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `delivery_slots`
--

INSERT INTO `delivery_slots` (`id`, `name`, `dow`, `window_start`, `window_end`, `capacity`, `is_active`, `created`, `modified`) VALUES
                                                                                                                                     (2, 'Morning', NULL, '09:00:00', '12:00:00', 20, 1, '2025-10-07 02:36:44', '2025-10-06 16:27:14'),
                                                                                                                                     (3, 'Afternoon', NULL, '12:00:00', '15:00:00', 20, 1, '2025-10-07 02:36:44', '2025-10-07 02:36:44'),
                                                                                                                                     (4, 'Evening', NULL, '15:00:00', '18:00:00', 20, 1, '2025-10-07 02:36:44', '2025-10-07 02:36:44'),
                                                                                                                                     (6, 'Weekend', NULL, '10:00:00', '14:00:00', 10, 0, '2025-10-06 16:27:46', '2025-10-06 16:27:58');

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
                          `fulfillment_method` varchar(20) NOT NULL DEFAULT 'delivery',
                          `delivery_date` date DEFAULT NULL,
                          `delivery_slot_id` int(11) DEFAULT NULL,
                          `pickup_location_id` int(11) DEFAULT NULL,
                          `delivery_instructions` varchar(500) DEFAULT NULL,
                          `payment_method` varchar(40) DEFAULT NULL,
                          `stock_deducted` tinyint(1) NOT NULL DEFAULT 0,
                          `stock_deducted_at` datetime DEFAULT NULL,
                          `payment_ref` varchar(80) DEFAULT NULL,
                          `paid_at` datetime DEFAULT NULL,
                          `notes` text DEFAULT NULL,
                          `created` datetime NOT NULL,
                          `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `email`, `full_name`, `address`, `city`, `postcode`, `country`, `currency`, `subtotal`, `shipping_fee`, `discount`, `total`, `status`, `payment_status`, `fulfillment_method`, `delivery_date`, `delivery_slot_id`, `pickup_location_id`, `delivery_instructions`, `payment_method`, `stock_deducted`, `stock_deducted_at`, `payment_ref`, `paid_at`, `notes`, `created`, `modified`) VALUES
                                                                                                                                                                                                                                                                                                                                                                                                                                 (1, 5, 'TestCustomer@gmail.com', 'TestCustomer', 'U1  1 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 10.50, 12.90, 0.00, 23.40, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-09-09 18:42:15', '2025-09-09 18:42:15'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (2, 5, 'TestCustomer@gmail.com', 'TestCustomer', 'U1  1 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 10.50, 12.90, 0.00, 23.40, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-09-10 09:18:49', '2025-09-10 09:18:49'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (3, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U 4  15 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 31.50, 12.90, 0.00, 44.40, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-09-10 13:04:44', '2025-09-10 13:04:44'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (4, 5, 'TestCustomer@gmail.com', 'TestCustomer', 'U 4  15 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 28.40, 12.90, 0.00, 41.30, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-09-10 14:37:53', '2025-09-10 14:37:53'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (5, 5, 'TestCustomer@gmail.com', 'TestCustomer', 'U 4  15 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-09-11 06:24:41', '2025-09-11 06:24:41'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (6, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 28.30, 12.90, 0.00, 41.20, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-10-04 16:39:18', '2025-10-04 16:39:18'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (7, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 106.80, 12.90, 0.00, 119.70, 'pending', 'refunded', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-10-04 18:10:58', NULL, '2025-10-04 17:48:30', '2025-10-04 18:22:57'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (8, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 17.80, 12.90, 0.00, 30.70, 'pending', 'paid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2025-10-04 18:06:31', NULL, '2025-10-04 17:58:14', '2025-10-04 18:10:32'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (9, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U 4  15 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'delivered', 'paid', 'delivery', NULL, NULL, NULL, NULL, 'card', 0, NULL, 'cs_test_b1UWREXUd5lGjo2cEMnc2oLI7L73HlaHSvkljPXT1MCDkT4ORQprlH2xdW', '2025-10-04 17:58:59', NULL, '2025-10-04 17:58:59', '2025-10-04 18:18:54'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (10, 5, 'TestCustomer@gmail.com', 'TestCustomer', '11', '1', '1', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'new', 'paid', 'delivery', NULL, NULL, NULL, NULL, 'card', 0, NULL, 'cs_test_b1M0gCFn5UAJ5fYjUZGZnKTeuH2QHFaYtA8jqZ5jllB4yxmueU97WkhVoV', '2025-10-04 18:34:41', NULL, '2025-10-04 18:34:41', '2025-10-04 18:34:41'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (11, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'paid', 'delivery', NULL, NULL, NULL, NULL, 'card', 0, NULL, 'cs_test_b11E17BRVL0YfnO9Cpg02PRPokpLdGlqIqH1JUjRA0MCCDIu68xadE0on4', '2025-10-04 18:43:01', NULL, '2025-10-04 18:43:01', '2025-10-04 18:43:01'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (12, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2025-10-06 12:54:32', '2025-10-06 12:54:32'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (13, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 13:41:43', '2025-10-06 13:41:43'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (14, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'paid', 'delivery', NULL, NULL, NULL, NULL, 'card', 0, NULL, 'cs_test_b1IwEj5PtIpMFiR0ertt1cU1my3r383x0MqkV4HbkaQFjSVkPxfmFqxowy', '2025-10-06 13:43:30', NULL, '2025-10-06 13:43:30', '2025-10-06 13:43:30'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (15, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 17.80, 0.00, 0.00, 17.80, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 17:41:08', '2025-10-06 17:41:08'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (17, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 10.50, 0.00, 0.00, 10.50, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 19:04:45', '2025-10-06 19:04:45'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (18, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 8.90, 0.00, 0.00, 8.90, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 19:10:28', '2025-10-06 19:10:28'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (19, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 11.20, 0.00, 0.00, 11.20, 'pending', 'unpaid', 'delivery', NULL, NULL, NULL, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 19:20:45', '2025-10-06 19:20:45'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (20, 5, 'TestCustomer@gmail.com', 'Aakhenteros', 'Unit 2304 551 Swanston Street', 'CARLTON', '3053', 'Australia', 'AUD', 10.50, 0.00, 0.00, 10.50, 'pending', 'unpaid', 'pickup', NULL, NULL, 3, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 19:28:43', '2025-10-06 19:28:43'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (21, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U1  1 Browns Rd, U 4  15 BROWNS RD', 'CLAYTON', '3168', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'unpaid', 'delivery', '2025-10-13', 3, NULL, 'gate 1', 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-06 19:50:15', '2025-10-06 19:50:15'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (26, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U1  1 Browns Rd, U 4  15 BROWNS RD', 'CLAYTON', '3168', 'Australia', 'AUD', 8.90, 0.00, 0.00, 8.90, 'pending', 'paid', 'pickup', NULL, NULL, 3, NULL, 'card', 0, NULL, 'cs_test_a1FPdNclImfpShQ6pxJ68E21QXZ4ZyniOHv7YfzW92ORImfwyzPmKUxVVN', '2025-10-06 20:14:14', NULL, '2025-10-06 20:14:14', '2025-10-06 20:14:14'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (27, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U1  1 Browns Rd, U 4  15 BROWNS RD', 'CLAYTON', '3168', 'Australia', 'AUD', 10.50, 0.00, 0.00, 10.50, 'pending', 'paid', 'pickup', NULL, NULL, 3, NULL, 'card', 0, NULL, 'cs_test_a1HA8RXxtKz6jbYajvnmWAL2b4zcGNFw5EWBi4DIOpvmJcMmMnBYuB5wx7', '2025-10-06 20:30:41', NULL, '2025-10-06 20:30:41', '2025-10-06 20:30:41'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (28, 5, 'TestCustomer@gmail.com', 'Junjue Chang', 'U1  1 Browns Rd, U 4  15 BROWNS RD', 'CLAYTON', '3168', 'Australia', 'AUD', 8.90, 12.90, 0.00, 21.80, 'pending', 'paid', 'delivery', '2025-10-08', 3, NULL, 'AAA', 'card', 0, NULL, 'cs_test_b19esPSj4MFJHHaZOYA24jT23Uk5xw7jKK8bDrRrId72YULKdiyy1acsoB', '2025-10-06 20:32:30', NULL, '2025-10-06 20:32:30', '2025-10-06 20:39:49'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (29, 4, 'Carl@curdandculture.com', 'Junjue Chang', 'U1  1 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 10.50, 12.90, 0.00, 23.40, 'pending', 'paid', 'delivery', '2025-10-14', 2, NULL, NULL, 'card', 0, NULL, 'cs_test_b1PRwtexw9QRgyprNxpNhjJJY7Tr19TRYk0vKMsXDPqTm0Cj4kyxUZ07g6', '2025-10-12 00:27:31', NULL, '2025-10-12 00:27:31', '2025-10-12 00:27:31'),
                                                                                                                                                                                                                                                                                                                                                                                                                                 (30, 4, 'Carl@curdandculture.com', 'Junjue Chang', 'U1  1 Browns Rd', 'CLAYTON', '3168', 'Australia', 'AUD', 7.40, 0.00, 0.00, 7.40, 'pending', 'unpaid', 'pickup', NULL, NULL, 3, NULL, 'bank_transfer', 0, NULL, NULL, NULL, NULL, '2025-10-12 00:27:55', '2025-10-12 00:27:55');

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
                                                                                                                                                            (1, 1, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 0.00, NULL, '2025-09-09 18:42:15', '2025-09-09 18:42:15'),
                                                                                                                                                            (2, 2, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 0.00, NULL, '2025-09-10 09:18:49', '2025-09-10 09:18:49'),
                                                                                                                                                            (3, 3, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 3, 31.50, NULL, '2025-09-10 13:04:44', '2025-09-10 13:04:44'),
                                                                                                                                                            (4, 4, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 2, 21.00, NULL, '2025-09-10 14:37:53', '2025-09-10 14:37:53'),
                                                                                                                                                            (5, 4, 3, 'Goat Feta', 'goat-feta', 7.40, 'AUD', 1, 7.40, NULL, '2025-09-10 14:37:53', '2025-09-10 14:37:53'),
                                                                                                                                                            (6, 5, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-09-11 06:24:41', '2025-09-11 06:24:41'),
                                                                                                                                                            (7, 6, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 2, 17.80, NULL, '2025-10-04 16:39:18', '2025-10-04 16:39:18'),
                                                                                                                                                            (8, 6, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 10.50, NULL, '2025-10-04 16:39:18', '2025-10-04 16:39:18'),
                                                                                                                                                            (9, 7, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 12, 106.80, NULL, '2025-10-04 17:48:30', '2025-10-04 17:48:30'),
                                                                                                                                                            (10, 8, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 2, 17.80, NULL, '2025-10-04 17:58:14', '2025-10-04 17:58:14'),
                                                                                                                                                            (11, 9, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-04 17:58:59', '2025-10-04 17:58:59'),
                                                                                                                                                            (12, 10, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-04 18:34:41', '2025-10-04 18:34:41'),
                                                                                                                                                            (13, 11, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-04 18:43:01', '2025-10-04 18:43:01'),
                                                                                                                                                            (14, 12, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 12:54:32', '2025-10-06 12:54:32'),
                                                                                                                                                            (15, 13, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 13:41:43', '2025-10-06 13:41:43'),
                                                                                                                                                            (16, 14, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 13:43:30', '2025-10-06 13:43:30'),
                                                                                                                                                            (17, 15, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 2, 17.80, NULL, '2025-10-06 17:41:08', '2025-10-06 17:41:08'),
                                                                                                                                                            (18, 17, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 10.50, NULL, '2025-10-06 19:04:45', '2025-10-06 19:04:45'),
                                                                                                                                                            (19, 18, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 19:10:28', '2025-10-06 19:10:28'),
                                                                                                                                                            (20, 19, 5, 'Blue Vein Classic', 'blue-vein-classic', 11.20, 'AUD', 1, 11.20, NULL, '2025-10-06 19:20:45', '2025-10-06 19:20:45'),
                                                                                                                                                            (21, 20, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 10.50, NULL, '2025-10-06 19:28:43', '2025-10-06 19:28:43'),
                                                                                                                                                            (22, 21, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 19:50:15', '2025-10-06 19:50:15'),
                                                                                                                                                            (23, 26, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 20:14:14', '2025-10-06 20:14:14'),
                                                                                                                                                            (24, 27, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 10.50, NULL, '2025-10-06 20:30:41', '2025-10-06 20:30:41'),
                                                                                                                                                            (25, 28, 1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 1, 8.90, NULL, '2025-10-06 20:32:30', '2025-10-06 20:32:30'),
                                                                                                                                                            (26, 29, 2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 1, 10.50, NULL, '2025-10-12 00:27:31', '2025-10-12 00:27:31'),
                                                                                                                                                            (27, 30, 3, 'Goat Feta', 'goat-feta', 7.40, 'AUD', 1, 7.40, NULL, '2025-10-12 00:27:55', '2025-10-12 00:27:55');

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
-- 表的结构 `pickup_locations`
--

CREATE TABLE `pickup_locations` (
                                    `id` int(11) NOT NULL,
                                    `name` varchar(120) NOT NULL,
                                    `address_line_1` varchar(255) NOT NULL,
                                    `address_line_2` varchar(255) DEFAULT NULL,
                                    `suburb` varchar(100) NOT NULL,
                                    `state` varchar(50) NOT NULL,
                                    `postcode` varchar(10) NOT NULL,
                                    `open_from` time DEFAULT NULL,
                                    `open_to` time DEFAULT NULL,
                                    `is_active` tinyint(1) NOT NULL DEFAULT 1,
                                    `created` datetime DEFAULT NULL,
                                    `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `pickup_locations`
--

INSERT INTO `pickup_locations` (`id`, `name`, `address_line_1`, `address_line_2`, `suburb`, `state`, `postcode`, `open_from`, `open_to`, `is_active`, `created`, `modified`) VALUES
    (3, 'A Shop', 'U1  1 Browns Rd', 'U1  1 Browns Rd', 'CLAYTON', 'VIC', '3168', '12:31:23', '16:32:11', 1, '2025-10-06 17:37:23', '2025-10-06 17:37:30');

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
                                                                                                                                                                                                                                                                                                                                               (1, 'Mature Cheddar', 'mature-cheddar', 8.90, 'AUD', 'Sharp, crumbly English classic.', 'Traditional farmhouse cheddar with a long lingering finish.', NULL, NULL, 4.40, 112, '2025-09-10 01:51:06', '2025-10-08 12:13:38', 'United Kingdom', 'Cow', '12 months', 'Hard', 'Animal', 'no', '34%', 'Milk', 0, 1, 0, 'Cider, crusty bread', ''),
                                                                                                                                                                                                                                                                                                                                               (2, 'Brie de Maison', 'brie-de-maison', 10.50, 'AUD', 'Rich and buttery soft cheese.', 'Creamy paste with a delicate bloomy rind and mushroomy aroma.', NULL, NULL, 4.60, 91, '2025-09-10 01:51:06', '2025-10-12 00:27:31', 'France', 'Cow', '4 weeks', 'Soft-ripened', 'Animal', 'yes', '28%', 'Milk', 0, 1, 0, 'Sparkling wine, strawberries', NULL),
                                                                                                                                                                                                                                                                                                                                               (3, 'Goat Feta', 'goat-feta', 7.40, 'AUD', 'Salty and tangy, perfect for salads.', 'Traditional brined feta made from 100% goat\'s milk.', NULL, NULL, 4.20, 139, '2025-09-10 01:51:06', '2025-10-12 00:27:55', 'Greece', 'Goat', '8 weeks', 'Fresh', 'Vegetarian', 'yes', '23%', 'Milk', 1, 1, 0, 'Watermelon, olives', NULL),
(4, 'Aged Gouda 18M', 'aged-gouda-18m', 14.90, 'AUD', 'Caramel notes and crunchy crystals.', 'Dutch gouda matured for 18 months, deep butterscotch flavor.', NULL, NULL, 4.80, 60, '2025-09-10 01:51:06', '2025-09-10 01:51:06', 'Netherlands', 'Cow', '18 months', 'Hard', 'Animal', 'no', '32%', 'Milk', 0, 1, 0, 'Brown ale, toasted nuts', 'World Cheese Awards – Gold'),
(5, 'Blue Vein Classic', 'blue-vein-classic', 11.20, 'AUD', 'Bold and spicy blue.', 'Creamy body streaked with blue veins; assertive and savory.', NULL, NULL, 4.10, 69, '2025-09-10 01:51:06', '2025-10-06 19:20:45', 'Denmark', 'Cow', '3 months', 'Blue', 'Animal', 'yes', '30%', 'Milk', 0, 1, 0, 'Port wine, pears', NULL),
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
  `reset_code_hash` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `reset_attempts` int(11) NOT NULL DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` varchar(50) DEFAULT 'active',
  `timezone` varchar(64) DEFAULT 'UTC',
  `language` varchar(8) DEFAULT 'en',
  `theme` varchar(20) DEFAULT 'auto',
  `notify_email` tinyint(1) DEFAULT 1,
  `notify_push` tinyint(1) DEFAULT 0,
  `pref_theme` varchar(10) DEFAULT 'auto',
  `pref_contrast` varchar(10) DEFAULT 'normal',
  `pref_font_scale` decimal(3,2) DEFAULT 1.00,
  `pref_lang` varchar(8) DEFAULT 'en',
  `email_optin` tinyint(1) DEFAULT 1,
  `cookie_consent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `reset_code_hash`, `reset_expires`, `reset_attempts`, `created`, `modified`, `role`, `status`, `timezone`, `language`, `theme`, `notify_email`, `notify_push`, `pref_theme`, `pref_contrast`, `pref_font_scale`, `pref_lang`, `email_optin`, `cookie_consent`) VALUES
(1, 'cakephp@example.com', '$2y$10$zRylcS5T3aPvsvdScsO5KOw5ndzDwbFF7qHhF1fuh.TFfWcBg8lKO', NULL, NULL, 0, '2025-08-12 04:25:41', '2025-08-12 04:25:41', 'user', 'active', 'UTC', 'en', 'auto', 1, 0, 'auto', 'normal', 1.00, 'en', 1, 0),
(2, 'admin@curdandculture.com', '$2y$10$S2htStkFy/.33Lm848bSq.D7hUq02ngHfg5I0ycSh4XdAEN7IC7UG', '$2y$10$Zo/7cI.iAyaCXHmMnma/r.VwXk2acwoySt4hbty/Jpew3oh39YVLC', '2025-10-11 20:24:06', 5, '2025-08-19 00:22:26', '2025-10-11 20:15:04', 'admin', 'active', 'UTC', 'en', 'auto', 1, 0, 'auto', 'normal', 1.00, 'en', 1, 0),
(4, 'Carl@curdandculture.com', '$2y$10$YP1iwuR1AyQmpMJWbb1vwelDZCgYw8kQksrEgJNWvKp3DOOYg8DBC', NULL, NULL, 0, '2025-09-07 01:02:57', '2025-10-12 00:20:25', 'customer', 'active', 'UTC', 'en', 'auto', 1, 0, 'auto', 'normal', 1.00, 'en', 1, 0),
(5, 'TestCustomer@gmail.com', '$2y$10$8TEIJnQQEIvXAqY.sF9DL.Bv.HSu4UtO7G13gfZPnazJ.RnQA9j9K', '$2y$10$q4dnx0S5VYApka9NRAZawuiMoqbYFDuZLbEY..Q3xNZYI02vyMBv6', '2025-10-11 20:51:20', 0, '2025-09-09 17:11:37', '2025-10-12 00:02:19', 'customer', 'active', 'UTC', 'en', 'auto', 1, 0, 'auto', 'normal', 1.00, 'en', 1, 0);

--
-- 转储表的索引
--

--
-- 表的索引 `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`),
  ADD KEY `idx_addresses_user_type` (`user_id`,`type`),
  ADD KEY `idx_addresses_user_default` (`user_id`,`is_default`);

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
-- 表的索引 `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `delivery_slots`
--
ALTER TABLE `delivery_slots`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_created` (`created`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_delivery_date` (`delivery_date`),
  ADD KEY `idx_orders_slot` (`delivery_slot_id`),
  ADD KEY `idx_orders_pickup` (`pickup_location_id`),
  ADD KEY `idx_orders_delivery_slot` (`delivery_slot_id`),
  ADD KEY `idx_orders_pickup_location` (`pickup_location_id`);

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
-- 表的索引 `pickup_locations`
--
ALTER TABLE `pickup_locations`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_users_reset_expires` (`reset_expires`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用表AUTO_INCREMENT `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- 使用表AUTO_INCREMENT `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- 使用表AUTO_INCREMENT `delivery_slots`
--
ALTER TABLE `delivery_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- 使用表AUTO_INCREMENT `pickup_locations`
--
ALTER TABLE `pickup_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- 限制表 `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
  ADD CONSTRAINT `fk_orders_delivery_slot` FOREIGN KEY (`delivery_slot_id`) REFERENCES `delivery_slots` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_pickup` FOREIGN KEY (`pickup_location_id`) REFERENCES `pickup_locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_pickup_location` FOREIGN KEY (`pickup_location_id`) REFERENCES `pickup_locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_slot` FOREIGN KEY (`delivery_slot_id`) REFERENCES `delivery_slots` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
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
