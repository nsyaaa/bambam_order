-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 09, 2026 at 08:02 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bambam_burger`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `details` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_name`, `action`, `details`, `created_at`) VALUES
(1, 1, 'lunaa', 'Update Order', 'Order #1 -> Preparing', '2026-02-25 18:25:29'),
(2, 1, 'lunaa', 'Update Order', 'Order #1 -> Preparing', '2026-02-25 18:29:12'),
(3, 1, 'lunaa', 'Update Order', 'Order #1 -> Ready', '2026-02-25 18:29:26'),
(4, 1, 'lunaa', 'Update Order', 'Order #46 -> Preparing', '2026-02-25 18:29:34'),
(5, 1, 'lunaa', 'Update Order', 'Order #46 -> Ready', '2026-02-25 18:29:35'),
(6, 1, 'lunaa', 'Update Order', 'Order #54 -> Preparing', '2026-02-25 18:31:44'),
(7, 1, 'lunaa', 'Update Order', 'Order #54 -> Ready', '2026-02-25 18:31:51'),
(8, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Sold Out', '2026-03-03 16:28:48'),
(9, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 16:28:56'),
(10, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 16:55:25'),
(11, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 17:05:43'),
(12, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 17:22:47'),
(13, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 17:33:04'),
(14, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 17:33:13'),
(15, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 17:40:59'),
(16, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:02:41'),
(17, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:14:23'),
(18, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:14:34'),
(19, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:15:27'),
(20, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:33:09'),
(21, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:47:20'),
(22, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 18:57:33'),
(23, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 19:05:31'),
(24, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 19:38:08'),
(25, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 19:58:12'),
(26, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 20:05:39'),
(27, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 20:11:59'),
(28, 1, 'lunaa', 'Toggle Menu Item', 'Item ID 5 -> Available', '2026-03-03 20:22:09'),
(29, 1, 'lunaa', 'Mark Paid', 'Marked Order #65 as paid.', '2026-03-08 12:56:28'),
(30, 1, 'lunaa', 'Update Order', 'Order #71 -> Preparing', '2026-03-08 12:56:36'),
(31, 1, 'lunaa', 'Update Order', 'Order #66 -> Completed', '2026-03-08 12:56:48'),
(32, 1, 'lunaa', 'Update Stock', 'Updated Stock ID 1 to 100', '2026-03-25 10:02:59'),
(33, 1, 'lunaa', 'Create Staff', 'Added staff john to Kangar', '2026-03-31 18:23:04'),
(34, 1, 'lunaa', 'Create Staff', 'Added staff doe to Kangar', '2026-03-31 18:29:27'),
(35, 1, 'lunaa', 'Create Staff', 'Added staff doe to Kangar', '2026-03-31 18:34:57'),
(36, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 3', '2026-03-31 18:35:33'),
(37, 1, 'lunaa', 'Create Staff', 'Added staff doe to Beseri', '2026-03-31 18:38:22'),
(38, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 4', '2026-03-31 18:44:53'),
(39, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 2', '2026-03-31 18:44:58'),
(40, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 1', '2026-03-31 18:45:02'),
(41, 1, 'lunaa', 'Create Staff', 'Added staff doe to Kangar', '2026-03-31 18:45:07'),
(42, 1, 'lunaa', 'Create Staff', 'Added staff john to Kangar', '2026-03-31 18:58:00'),
(43, 1, 'lunaa', 'Create Staff', 'Added staff si to Jejawi', '2026-03-31 18:58:34'),
(44, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 7', '2026-03-31 18:59:14'),
(45, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 6', '2026-03-31 19:53:44'),
(46, 1, 'lunaa', 'Delete Staff', 'Deleted Staff ID 5', '2026-03-31 19:53:48'),
(47, 1, 'lunaa', 'Create Staff', 'Added staff si to Kangar', '2026-03-31 19:53:54'),
(48, 1, 'lunaa', 'Create Staff', 'Added staff john to Kangar', '2026-04-01 02:32:08'),
(49, 1, 'lunaa', 'Create Staff', 'Added staff auni to Kangar', '2026-04-01 02:32:45'),
(50, 1, 'lunaa', 'Mark Paid', 'Marked Order #74 as paid.', '2026-04-04 14:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int NOT NULL,
  `staff_id` int NOT NULL,
  `branch_id` int NOT NULL,
  `clock_in` datetime DEFAULT NULL,
  `clock_out` datetime DEFAULT NULL,
  `work_date` date NOT NULL,
  `total_hours` decimal(5,2) DEFAULT '0.00',
  `status` enum('Active','Completed') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `phone`, `is_open`) VALUES
(1, 'Kangar', '017-590 0799', 0),
(2, 'Arau', '019-5511765', 0),
(3, 'Jejawi', '013-777 1763', 1),
(4, 'Kuala Perlis', '011-1989 8669', 1),
(5, 'Beseri', '011-1006 4068', 0);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 5, 3, '2026-03-01 16:54:18'),
(2, 5, 1, '2026-03-01 17:04:01'),
(3, 5, 6, '2026-03-01 17:04:03'),
(4, 5, 5, '2026-03-01 17:04:04');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int DEFAULT '0',
  `unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'units',
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'In Stock',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `quantity`, `unit`, `status`, `updated_at`) VALUES
(1, 'roti', 100, '', 'In Stock', '2026-01-24 02:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int NOT NULL,
  `staff_id` int NOT NULL,
  `leave_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mc_doc_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `staff_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `created_at`, `mc_doc_path`, `attachment`) VALUES
(1, 1, 'Emergency', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 09:06:43', NULL, NULL),
(2, 1, 'Emergency', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 09:22:40', NULL, NULL),
(3, 1, 'Emergency', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 09:37:52', NULL, NULL),
(4, 1, 'Emergency', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 09:45:06', NULL, NULL),
(5, 1, 'Emergency', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 09:56:32', NULL, NULL),
(6, 1, 'Unpaid', '2026-04-08', '2026-04-09', 'urgent', 'Pending', '2026-04-08 10:26:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `has_protein` tinyint(1) DEFAULT '0',
  `variants` json DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `cost_price` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category`, `name`, `description`, `price`, `has_protein`, `variants`, `image`, `created_at`, `is_available`, `cost_price`) VALUES
(1, 'burger', 'Lava Cheese Burger', 'Special lava cheese sauce', 7.00, 1, '[{\"name\": \"Single\", \"price\": 7.0}, {\"name\": \"Double\", \"price\": 13.0}, {\"name\": \"Triple\", \"price\": 19.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(2, 'burger', 'Burger XL', 'Extra large buns', 5.00, 1, '[{\"name\": \"Single\", \"price\": 5.0}, {\"name\": \"Double\", \"price\": 9.0}, {\"name\": \"Triple\", \"price\": 12.5}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(3, 'burger', 'Ayam Goreng Krup Krap', 'Super crispy fried chicken', 8.00, 0, '[{\"name\": \"Single\", \"price\": 8.0}, {\"name\": \"Double\", \"price\": 10.0}, {\"name\": \"Triple\", \"price\": 14.5}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(5, 'burger', 'Chicken Grill Burger', 'Flame grilled chicken breast', 8.50, 0, '[{\"name\": \"Single\", \"price\": 8.5}, {\"name\": \"Double\", \"price\": 16.0}, {\"name\": \"Triple\", \"price\": 21.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(6, 'burger', 'Burger Sate Ayam', 'Sate peanut sauce flavor', 8.50, 0, '[{\"name\": \"Single\", \"price\": 8.5}, {\"name\": \"Double\", \"price\": 16.0}, {\"name\": \"Triple\", \"price\": 21.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(7, 'burger', 'Smash Burger', 'Crispy beef edges', 7.50, 1, '[{\"name\": \"Single\", \"price\": 7.5}, {\"name\": \"Double\", \"price\": 14.0}, {\"name\": \"Triple\", \"price\": 20.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(8, 'burger', 'Hawaiian Spicy', 'Pineapple and spice', 8.00, 1, '[{\"name\": \"Single\", \"price\": 8.0}, {\"name\": \"Double\", \"price\": 15.0}, {\"name\": \"Triple\", \"price\": 21.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(9, 'burger', 'Daging / Ayam Biasa', 'Standard classic burger', 4.50, 1, '[{\"name\": \"Single\", \"price\": 4.5}, {\"name\": \"Double\", \"price\": 7.0}, {\"name\": \"Triple\", \"price\": 10.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(10, 'burger', 'Sosej Jumbo', 'Large premium sausage', 5.00, 0, '[{\"name\": \"Single\", \"price\": 5.0}, {\"name\": \"Double\", \"price\": 9.0}, {\"name\": \"Triple\", \"price\": 12.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(11, 'burger', 'Benjo', 'Egg burger specialty', 3.50, 0, '[{\"name\": \"Single\", \"price\": 3.5}, {\"name\": \"Double\", \"price\": 4.5}, {\"name\": \"Triple\", \"price\": 6.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(12, 'special', 'Cheese Steak', 'Beef strips and cheese', 9.50, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(13, 'special', 'Burger Kambing', 'New Release Lamb Burger', 13.00, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(14, 'special', 'Burger Mix XL', 'Combined proteins XL', 8.00, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(16, 'addon', 'Mozzarella Cheese', 'Stretchy mozzarella', 4.00, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(17, 'addon', 'Cheddar Cheese', 'Classic cheddar slice', 1.50, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(18, 'addon', 'Telur', 'Extra fried egg', 1.50, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(19, 'minuman', 'Chocolate', 'Milo/Chocolate drink', 3.00, 0, '[{\"name\": \"Sejuk\", \"price\": 3.0}, {\"name\": \"Panas\", \"price\": 2.5}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(20, 'minuman', 'Indocafe', 'Instant coffee blend', 3.00, 0, '[{\"name\": \"Sejuk\", \"price\": 3.0}, {\"name\": \"Panas\", \"price\": 2.5}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(21, 'minuman', 'Teh', 'Classic tea', 2.50, 0, '[{\"name\": \"Sejuk\", \"price\": 2.5}, {\"name\": \"Panas\", \"price\": 2.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(22, 'minuman', 'Kopi', 'Traditional coffee', 2.50, 0, '[{\"name\": \"Sejuk\", \"price\": 2.5}, {\"name\": \"Panas\", \"price\": 2.0}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(23, 'minuman', 'Green Tea', 'Refreshing green tea', 3.00, 0, '[{\"name\": \"Sejuk\", \"price\": 3.0}, {\"name\": \"Panas\", \"price\": 2.5}]', NULL, '2026-01-23 23:03:22', 1, 0.00),
(24, 'minuman', 'Jus Buah', 'Apple / Orange / Carrot Susu', 3.50, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(25, 'minuman', 'Minuman Bergas', 'F&N / A&W flavors', 2.00, 0, NULL, NULL, '2026-01-23 23:03:22', 1, 0.00),
(26, 'burger', 'Burger Wagyu', 'Premium Wagyu Beef Patty', 20.00, 1, NULL, NULL, '2026-02-24 15:26:23', 1, 0.00),
(27, 'burger', 'Burger Itik', 'Special Duck Patty Burger', 16.00, 1, NULL, NULL, '2026-02-24 15:26:23', 1, 0.00),
(28, 'special', 'Ayam Popcorn', '1 Cup of crispy chicken bites', 5.00, 0, NULL, NULL, '2026-02-24 15:26:23', 1, 0.00),
(29, 'special', 'Nugget Tempura', 'Crispy tempura nuggets', 5.00, 0, '[{\"name\": \"6pcs\", \"price\": 5.0}, {\"name\": \"13pcs\", \"price\": 10.0}]', NULL, '2026-02-24 15:26:23', 1, 0.00),
(30, 'special', 'Cheezy Wedges', '1 Set with cheese sauce', 6.00, 0, NULL, NULL, '2026-02-24 15:26:23', 1, 0.00),
(31, 'minuman', 'Teh O Limau', 'Tea with lime', 2.50, 0, '[{\"name\": \"Sejuk\", \"price\": 2.5}, {\"name\": \"Panas\", \"price\": 2.0}]', NULL, '2026-02-24 15:26:24', 1, 0.00),
(32, 'minuman', 'Teh O Laici', 'Tea with lychee fruit', 3.00, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(33, 'minuman', 'Limau Asam Boi', 'Lime with dried plum', 2.50, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(34, 'minuman', 'Sirap Bandung', 'Rose syrup with milk', 2.50, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(35, 'minuman', 'Sirap Limau', 'Rose syrup with lime', 2.50, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(36, 'minuman', 'Oren Sunquick', 'Sunquick orange cordial', 2.00, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(37, 'minuman', 'ExtraJoss Susu', 'Energy drink with milk', 2.50, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(38, 'minuman', 'ExtraJoss', 'Energy drink original', 2.00, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00),
(39, 'minuman', 'Sirap', 'Rose syrup', 2.00, 0, NULL, NULL, '2026-02-24 15:26:24', 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Main',
  `order_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Dine-in',
  `customer_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `processed_by_staff_id` int DEFAULT NULL,
  `receipt_img` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `admin_reply` text COLLATE utf8mb4_general_ci,
  `review_is_approved` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `rating` int DEFAULT NULL,
  `review` text COLLATE utf8mb4_general_ci,
  `address` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `branch`, `order_type`, `customer_name`, `customer_phone`, `total_amount`, `payment_method`, `payment_status`, `paid_at`, `processed_by_staff_id`, `receipt_img`, `status`, `admin_reply`, `review_is_approved`, `created_at`, `rating`, `review`, `address`) VALUES
(1, NULL, 'Main', 'Dine-in', 'sya', NULL, 0.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Ready', NULL, 1, '2026-01-23 23:43:23', NULL, NULL, NULL),
(2, NULL, 'Main', 'Dine-in', 'sya', NULL, 0.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-01-23 23:43:31', NULL, NULL, NULL),
(3, NULL, 'Main', 'Dine-in', 'sya', NULL, 0.00, 'E-Wallet', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-01-23 23:43:56', NULL, NULL, NULL),
(4, NULL, 'Main', 'Dine-in', 'sya', NULL, 1.50, 'Cash', 'Pending', NULL, NULL, '1769212239_banner.png', 'Preparing', NULL, 1, '2026-01-23 23:50:39', NULL, NULL, NULL),
(5, NULL, 'Main', 'Dine-in', 'sya', NULL, 8.00, 'E-Wallet', 'Pending', NULL, NULL, '1769220298_bout.png', 'Served', NULL, 1, '2026-01-24 02:04:58', NULL, NULL, NULL),
(6, NULL, 'Main', 'Dine-in', 'irfan', NULL, 9.50, 'E-Wallet', 'Pending', NULL, NULL, '1769221782_bout.png', 'Served', NULL, 1, '2026-01-24 02:29:42', NULL, NULL, NULL),
(7, 5, 'Arau', 'Take-Away', 'sya', NULL, 72.00, 'Cash', 'Pending', NULL, NULL, '1770312271_banner.png', 'Served', NULL, 1, '2026-02-05 17:24:31', NULL, NULL, NULL),
(8, 7, 'Kangar', 'Take-Away', 'sya', NULL, 9.50, 'Online Transfer', 'Pending', NULL, NULL, '1770347254_bannerabout.png', 'Served', NULL, 1, '2026-02-06 03:07:34', NULL, NULL, NULL),
(11, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 16.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:23:42', NULL, NULL, NULL),
(12, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 16.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:23:48', NULL, NULL, NULL),
(14, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 16.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Ready', NULL, 1, '2026-02-08 15:23:49', NULL, NULL, NULL),
(15, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:50:49', NULL, NULL, NULL),
(16, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 1.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:51:25', NULL, NULL, NULL),
(17, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:53:56', NULL, NULL, NULL),
(18, 5, 'Kangar', 'Take-Away', 'irfan', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 15:56:51', NULL, NULL, NULL),
(19, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 13.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 16:01:45', NULL, NULL, NULL),
(20, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 16:04:41', NULL, NULL, NULL),
(21, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-08 16:07:46', NULL, NULL, NULL),
(23, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 7.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Preparing', NULL, 1, '2026-02-12 20:48:25', NULL, NULL, NULL),
(24, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 7.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-12 20:48:42', NULL, NULL, NULL),
(25, 5, 'Kangar', 'Take-Away', 'uiuu', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-12 20:53:19', NULL, NULL, NULL),
(26, 5, 'Kangar', 'Take-Away', 'jkjl', NULL, 8.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Ready', NULL, 1, '2026-02-12 20:55:15', NULL, NULL, NULL),
(27, NULL, 'Kangar', 'Take-Away', 'jkjl', NULL, 1.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 02:21:49', NULL, NULL, NULL),
(28, NULL, 'Kangar', 'Pick-Up', 'uiuu', NULL, 17.00, 'Transfer', 'Pending', NULL, NULL, 'receipt_1770949712_698e8c50c0421.jpg', 'Served', NULL, 1, '2026-02-13 02:28:32', NULL, NULL, NULL),
(29, NULL, 'Kangar', 'Delivery', 'uiuu', NULL, 8.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 18:22:49', NULL, NULL, NULL),
(30, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 18:27:50', NULL, NULL, NULL),
(31, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 8.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 18:30:58', NULL, NULL, NULL),
(32, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 8.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 18:39:23', NULL, NULL, NULL),
(33, NULL, 'Kangar', 'Delivery', 'irfan', NULL, 24.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 18:52:45', NULL, NULL, NULL),
(34, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 23.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 19:16:25', 5, '', NULL),
(35, NULL, 'Kuala Perlis', 'Delivery', 'jkjl', NULL, 8.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 19:24:25', NULL, NULL, NULL),
(36, NULL, 'Kuala Perlis', 'Delivery', 'irfan', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 19:32:26', NULL, NULL, NULL),
(37, NULL, 'Kuala Perlis', 'Delivery', 'jkjl', NULL, 2.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-13 19:37:33', NULL, NULL, NULL),
(38, NULL, 'Kuala Perlis', 'Delivery', 'uiuu', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-13 19:49:24', NULL, NULL, NULL),
(39, 5, 'Kangar', 'Delivery', 'irfan', NULL, 15.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-14 10:42:23', NULL, NULL, NULL),
(40, 5, 'Kangar', 'Pick-Up', 'alep', NULL, 7.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-19 14:28:12', NULL, NULL, NULL),
(41, 5, 'Kangar', 'Pick-Up', 'alep', NULL, 7.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-19 14:28:22', NULL, NULL, NULL),
(42, 5, 'Kangar', 'Pick-Up', 'alep', NULL, 7.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-19 14:28:23', NULL, NULL, NULL),
(43, 5, 'Kangar', 'Delivery', 'alep', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-19 14:41:19', NULL, NULL, NULL),
(45, NULL, 'Kangar', 'Delivery', 'irfan', NULL, 24.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-23 17:18:49', NULL, NULL, NULL),
(46, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 34.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Ready', NULL, 1, '2026-02-24 14:57:32', NULL, NULL, NULL),
(47, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 34.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-24 14:57:36', NULL, NULL, NULL),
(48, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 34.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-24 14:57:37', NULL, NULL, NULL),
(49, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 18.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-24 15:01:15', NULL, NULL, NULL),
(50, NULL, 'Kangar', 'Delivery', 'jkjl', NULL, 9.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-24 15:02:34', NULL, NULL, NULL),
(51, 5, 'Kangar', 'Delivery', 'uiuu', NULL, 5.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-25 02:35:52', NULL, NULL, NULL),
(53, 5, 'Kangar', 'Pick-Up', 'irfan', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-25 18:30:40', NULL, NULL, NULL),
(54, 5, 'Kangar', 'Pick-Up', 'irfan', NULL, 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-25 18:30:44', NULL, NULL, NULL),
(55, 5, 'Kangar', 'Pick-Up', 'farif', NULL, 20.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-27 02:33:53', NULL, NULL, NULL),
(56, 5, 'Kangar', 'Pick-Up', 'farif', NULL, 20.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-02-27 02:33:55', NULL, NULL, NULL),
(57, 5, 'Kangar', 'Delivery', 'jkjl', NULL, 5.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-27 15:14:59', NULL, NULL, 'cfdfe'),
(58, 5, 'Kangar', 'Delivery', 'jkjl', NULL, 5.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-02-27 15:15:06', NULL, NULL, 'cfdfe'),
(59, 5, 'Kangar', 'Delivery', 'irfan', NULL, 5.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-27 15:17:33', NULL, NULL, 'nhkh k byhu'),
(60, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 21.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-28 14:35:04', NULL, NULL, 'fcsdfaf'),
(61, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-02-28 14:48:51', NULL, NULL, 'dada'),
(62, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-03-01 16:05:49', NULL, NULL, 'rfgfhh'),
(63, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-03-01 16:06:12', NULL, NULL, 'rfgfhh'),
(64, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-03-01 16:06:16', NULL, NULL, 'rfgfhh'),
(65, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Paid', '2026-03-08 12:56:28', 1, NULL, 'Pending', NULL, 1, '2026-03-01 16:06:19', NULL, NULL, 'rfgfhh'),
(66, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 8.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-03-01 16:06:22', NULL, NULL, 'rfgfhh'),
(69, NULL, 'Beseri', 'Delivery', 'lunaa', NULL, 16.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-03-04 01:28:21', NULL, NULL, 'na'),
(70, NULL, 'Beseri', 'Delivery', 'lunaa', NULL, 16.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Preparing', NULL, 1, '2026-03-04 01:28:30', NULL, NULL, 'na'),
(71, NULL, 'Beseri', 'Delivery', 'lunaa', NULL, 15.50, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-03-04 01:57:41', NULL, NULL, 'na'),
(72, NULL, 'Beseri', 'Delivery', 'lunaa', NULL, 16.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Served', NULL, 1, '2026-03-04 02:30:18', NULL, NULL, 'na'),
(73, 5, 'Kangar', 'Delivery', 'lunaa', '0162032784', 3.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Completed', NULL, 1, '2026-03-04 02:57:59', NULL, NULL, 'na'),
(74, 10, 'Beseri', 'Delivery', 'nini', '0195711302', 332.00, 'Cash', 'Paid', '2026-04-04 14:12:15', 1, NULL, 'Completed', NULL, 1, '2026-04-01 10:03:14', NULL, NULL, 'Politeknik Tuanku Syed Sirajuddin'),
(75, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:49:30', NULL, NULL, 'bfyfyd'),
(76, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:32', NULL, NULL, 'bfyfyd'),
(77, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:35', NULL, NULL, 'bfyfyd'),
(78, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:35', NULL, NULL, 'bfyfyd'),
(79, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:36', NULL, NULL, 'bfyfyd'),
(80, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:36', NULL, NULL, 'bfyfyd'),
(81, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 16:51:36', NULL, NULL, 'bfyfyd'),
(82, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 17:05:15', NULL, NULL, 'fjhrfyf'),
(83, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 17:05:22', NULL, NULL, 'fjhrfyf'),
(84, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 17:14:54', NULL, NULL, 'dwedwedeqd'),
(85, 5, 'Jejawi', 'Delivery', 'lunaa', '0162032784', 10.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 17:26:16', NULL, NULL, 'guv htgj'),
(86, 5, 'Beseri', 'Delivery', 'lunaa', '0162032784', 6.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 18:05:50', NULL, NULL, 'uyiyli'),
(87, 5, 'Beseri', 'Delivery', 'lunaa', '0162032784', 5.00, 'Cash', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-07 18:06:34', NULL, NULL, 'ljljk'),
(89, 5, 'Arau', 'Delivery', 'lunaa', '0162032784', 7.00, 'ToyyibPay', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-08 13:06:50', NULL, NULL, 'dfgdg'),
(90, 5, 'Arau', 'Delivery', 'lunaa', '0162032784', 7.00, 'ToyyibPay', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-08 13:53:16', NULL, NULL, 'vxzvx'),
(91, 5, 'Arau', 'Delivery', 'lunaa', '0162032784', 7.00, 'ToyyibPay', 'Pending', NULL, NULL, NULL, 'Pending', NULL, 1, '2026-04-08 14:03:11', NULL, NULL, 'vxzvx');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `variant` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `protein` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int NOT NULL,
  `customization` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `variant`, `protein`, `price`, `qty`, `customization`) VALUES
(1, 4, 'Telur', 'Standard', NULL, 1.50, 1, NULL),
(2, 5, 'Ayam Goreng Krup Krap', 'Single', NULL, 8.00, 1, NULL),
(3, 6, 'Cheese Steak', 'Standard', NULL, 9.50, 1, NULL),
(4, 7, 'Burger Kambing', 'Standard', '', 13.00, 5, NULL),
(5, 7, 'Lava Cheese Burger', 'Single', 'Ayam', 7.00, 1, NULL),
(6, 8, 'Cheese Steak', 'Standard', '', 9.50, 1, NULL),
(7, 11, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(8, 11, 'Burger Sate Ayam', 'Single', '', 8.50, 1, NULL),
(9, 12, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(10, 12, 'Burger Sate Ayam', 'Single', '', 8.50, 1, NULL),
(13, 14, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(14, 14, 'Burger Sate Ayam', 'Single', '', 8.50, 1, NULL),
(15, 15, 'Ayam Goreng Krup Krap XL', 'Single', '', 10.00, 1, NULL),
(16, 16, 'Cheddar Cheese', 'Standard', '', 1.50, 1, NULL),
(17, 17, 'Burger Mix XL', 'Standard', '', 8.00, 1, NULL),
(18, 18, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(19, 19, 'Burger Kambing', 'Standard', '', 13.00, 1, NULL),
(20, 20, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(21, 21, 'Ayam Goreng Krup Krap', 'Single', '', 8.00, 1, NULL),
(22, 23, 'LAVA CHEESE BURGER', 'Single', '', 7.00, 1, ''),
(23, 24, 'LAVA CHEESE BURGER', 'Single', '', 7.00, 1, ''),
(24, 25, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(25, 26, 'BURGER SATE AYAM', 'Single', '', 8.50, 1, ''),
(26, 27, 'TELUR', 'Standard', '', 1.50, 1, ''),
(27, 28, 'CHICKEN GRILL BURGER', 'Single', '', 8.50, 2, ''),
(28, 29, 'BURGER SATE AYAM', 'Single', '', 8.50, 1, ''),
(29, 30, 'BURGER MIX XL', 'Standard', '', 8.00, 1, ''),
(30, 31, 'BURGER SATE AYAM', 'Single', '', 8.50, 1, ''),
(31, 32, 'CHICKEN GRILL BURGER', 'Single', '', 8.50, 1, ''),
(32, 33, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 3, ''),
(33, 34, 'LAVA CHEESE BURGER', 'Single', 'Ayam', 7.00, 1, ''),
(34, 34, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(35, 34, 'CHICKEN GRILL BURGER', 'Single', '', 8.50, 1, 'no onion , no sauce '),
(36, 35, 'BURGER SATE AYAM', 'Single', '', 8.50, 1, ''),
(37, 36, 'BURGER MIX XL', 'Standard', '', 8.00, 1, ''),
(38, 37, 'KOPI', 'Sejuk', '', 2.50, 1, ''),
(39, 38, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(40, 39, 'TELUR', 'Standard', '', 1.50, 10, ''),
(41, 40, 'LAVA CHEESE BURGER', 'Single', 'Ayam', 7.00, 1, ''),
(42, 41, 'LAVA CHEESE BURGER', 'Single', 'Ayam', 7.00, 1, ''),
(43, 42, 'LAVA CHEESE BURGER', 'Single', 'Ayam', 7.00, 1, ''),
(44, 43, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(46, 45, 'Custom Burger', 'Custom Build', 'Mixed', 24.50, 1, 'Bottom Bun, Tomato, Chicken Patty, Lettuce, Beef Patty, Onion Ring, Cheese Slice, Cheese Slice, Cheese Slice, Cheese Slice, Top Bun'),
(47, 46, 'Custom Burger', 'Custom Build', 'Mixed', 18.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Beef Patty, Lettuce, Cheese Slice, Top Bun'),
(48, 46, 'Custom Burger', 'Custom Build', 'Mixed', 16.00, 1, 'Bottom Bun, Tomato, Lettuce, Chicken Patty, Beef Patty, Top Bun'),
(49, 47, 'Custom Burger', 'Custom Build', 'Mixed', 18.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Beef Patty, Lettuce, Cheese Slice, Top Bun'),
(50, 47, 'Custom Burger', 'Custom Build', 'Mixed', 16.00, 1, 'Bottom Bun, Tomato, Lettuce, Chicken Patty, Beef Patty, Top Bun'),
(51, 48, 'Custom Burger', 'Custom Build', 'Mixed', 18.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Beef Patty, Lettuce, Cheese Slice, Top Bun'),
(52, 48, 'Custom Burger', 'Custom Build', 'Mixed', 16.00, 1, 'Bottom Bun, Tomato, Lettuce, Chicken Patty, Beef Patty, Top Bun'),
(53, 49, 'Custom Burger', 'Custom Build', 'Mixed', 9.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Top Bun'),
(54, 49, 'Custom Burger', 'Custom Build', 'Mixed', 9.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Top Bun'),
(55, 50, 'Custom Burger', 'Custom Build', 'Mixed', 9.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Top Bun'),
(56, 51, 'AYAM POPCORN', 'Standard', '', 5.00, 1, ''),
(59, 53, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(60, 54, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(61, 55, 'BURGER WAGYU', 'Standard', '', 20.00, 1, ''),
(62, 56, 'BURGER WAGYU', 'Standard', '', 20.00, 1, ''),
(63, 57, 'AYAM POPCORN', 'Standard', '', 5.00, 1, ''),
(64, 58, 'AYAM POPCORN', 'Standard', '', 5.00, 1, ''),
(65, 59, 'AYAM POPCORN', 'Standard', '', 5.00, 1, 'bini taknak sos'),
(66, 60, 'LAVA CHEESE BURGER', 'Single', 'Ayam', 7.00, 2, ''),
(67, 60, 'LAVA CHEESE BURGER', 'Single', 'Daging', 7.00, 1, ''),
(68, 61, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 1, ''),
(69, 62, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 1, ''),
(70, 63, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 1, ''),
(71, 64, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 1, ''),
(72, 65, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 1, ''),
(73, 66, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 1, ''),
(76, 69, 'Custom Burger', 'Custom Build', '', 16.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Beef Patty, Lettuce, Top Bun'),
(77, 70, 'Custom Burger', 'Custom Build', '', 16.00, 1, 'Bottom Bun, Tomato, Chicken Patty, Beef Patty, Lettuce, Top Bun'),
(78, 71, 'Custom Burger', 'Custom Build', '', 15.50, 1, 'Bottom Bun, Tomato, Cheese Slice, Cheese Slice, Lettuce, Beef Patty, Onion Ring, Top Bun'),
(79, 72, 'Custom Burger', 'Custom Build', '', 16.00, 1, 'Bottom Bun, Chicken Patty, Tomato, Lettuce, Beef Patty, Top Bun'),
(80, 73, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(81, 74, 'BURGER WAGYU', 'Standard', '', 20.00, 8, ''),
(82, 74, 'BURGER ITIK', 'Standard', '', 16.00, 3, ''),
(83, 74, 'TELUR', 'Standard', '', 1.50, 6, ''),
(84, 74, 'AYAM POPCORN', 'Standard', '', 5.00, 5, ''),
(85, 74, 'CHICKEN GRILL BURGER', 'Single', '', 8.50, 4, ''),
(86, 74, 'AYAM GORENG KRUP KRAP', 'Single', '', 8.00, 3, ''),
(87, 74, 'HAWAIIAN SPICY', 'Single', 'Ayam', 8.00, 4, ''),
(88, 75, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(89, 75, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(90, 76, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(91, 76, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(92, 77, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(93, 77, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(94, 78, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(95, 78, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(96, 79, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(97, 79, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(98, 80, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(99, 80, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(100, 81, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(101, 81, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(102, 82, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(103, 82, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(104, 83, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(105, 83, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(106, 84, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(107, 84, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(108, 85, 'Custom Burger', 'Custom Build', '', 7.00, 1, 'Lettuce, Beef Patty'),
(109, 85, 'INDOCAFE', 'Sejuk', '', 3.00, 1, ''),
(110, 86, 'Custom Burger', 'Custom Build', '', 6.00, 1, 'Tomato, Chicken Patty'),
(111, 87, 'AYAM POPCORN', 'Standard', '', 5.00, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','cashier','kitchen') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'kitchen',
  `branch` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `phone`, `role`, `branch`, `created_at`) VALUES
(8, 'si', 'si@gmail.com', '0162032784', 'kitchen', 'Kangar', '2026-03-31 19:53:54'),
(9, 'john', 'john@gmail.com', '0162032784', 'kitchen', 'Kangar', '2026-04-01 02:32:08'),
(10, 'auni', 'auni@gmail.com', '0162032784', 'kitchen', 'Kangar', '2026-04-01 02:32:44');

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `attendance_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `date` date NOT NULL,
  `clock_in` datetime NOT NULL,
  `clock_out` datetime DEFAULT NULL,
  `status` enum('On Time','Late','Early Departure') COLLATE utf8mb4_general_ci DEFAULT 'On Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('global_store_status', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gmail` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'user',
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `gmail`, `phone`, `password`, `reset_token`, `reset_expire`, `created_at`, `profile_pic`, `role`, `last_login`) VALUES
(1, 'lunaa', 'admin@bambam.com', '0162032784', '$2y$10$.Cj/Z8hiFnwI6l4oFLvWTex2gBMKP937yfqaYqR8yXZc9LwmnQEuS', 'bf11520b5572464c764faadeb65faf8a', '2026-01-20 19:27:30', '2026-01-19 11:13:25', 'uploads/profile_1_1769165846.jpg', 'admin', '2026-04-08 08:47:52'),
(2, 'auni', 'auni@gmail.com', '0166300089', '$2y$10$uFJGvQ4.Qqu1TVDZLrclI.ZfDOzs1xddEmEAvsWhkuPXKESFgTkPK', 'e4af24b8b26ddafd479029a6b8272aa4', '2026-01-20 18:47:42', '2026-01-20 18:15:39', NULL, 'user', NULL),
(3, 'irfan', 'irfan@gmail.com', '01169509870', '$2y$10$vXbxWiWdNw1hsvndOY/ImuG1RyBXAdSq4fQ8odImVXSXTMebJLttG', NULL, NULL, '2026-01-23 09:36:00', NULL, 'user', NULL),
(5, 'lunaa', 'kiyowosya.my@gmail.com', '0162032784', '$2y$10$E8xINqZtj6u1gwc9lqV1sOt0CETF5CbxKnyO/5zdPFx1PpfhEvNVO', NULL, NULL, '2026-01-23 16:11:55', 'uploads/profile_5_1771511561.jpg', 'user', NULL),
(6, 'iman', 'iman@gmail.com', '0111111111', '$2y$10$iEVD0fzkq8P1HtGxSLUtgOCH3AMWXHfECsgRpEpWZJ/ahmp5PRL2K', NULL, NULL, '2026-01-24 02:41:27', NULL, 'staff', NULL),
(7, 'lunaa', 'luna@gmail.com', '0162032784', '$2y$10$8SXJYPlNf8wNU0hyQOF/N.erX0evxLbvsrY28rvemn8WCcoJswigS', NULL, NULL, '2026-02-06 03:04:52', NULL, 'user', NULL),
(10, 'nini', 'niniz@gmail.com', '0195711302', '$2y$10$qQiMrZK46DK/OeK2Wc/EOufDrK.L7ittEWxf9fmPoC4TLUaWVoyJ2', NULL, NULL, '2026-04-01 08:48:28', NULL, 'user', NULL),
(11, 'abel', 'admin@gmail.com', '0162032784', '$2y$10$2AfzrczUswzLndC4YyXMK.3jJR2pdm/YuKdz9jHWTsUwqkXKD77aq', NULL, NULL, '2026-04-09 18:22:06', NULL, 'user', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`product_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`attendance_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `attendance_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
