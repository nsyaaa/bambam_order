-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 08:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bambam`
--

-- --------------------------------------------------------

--
-- Table structure for table `iventory`
--

CREATE TABLE `iventory` (
  `iven_ID` int(3) NOT NULL,
  `iven_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iventory`
--

INSERT INTO `iventory` (`iven_ID`, `iven_Name`) VALUES
(1, 'Roti Burger'),
(2, 'Roti Oblong'),
(3, 'Daging Patty'),
(4, 'Ayam Patty'),
(5, 'Itik Patty'),
(6, 'Sosej'),
(7, 'Kambing'),
(8, 'Wagyu Patty'),
(9, 'mozarella'),
(10, 'cherddar'),
(11, 'Telur'),
(12, 'bawang'),
(13, 'Salad'),
(14, 'Tomato'),
(15, 'Sos Cili'),
(16, 'Sos Mayonnaise'),
(17, 'Sos Cheese'),
(18, 'Sos Blackpaper'),
(19, 'Ayam Mentah'),
(20, 'Fries'),
(21, 'Nugget Tempura'),
(22, 'Orange'),
(23, 'Apple'),
(24, 'Carrot');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_Id` int(11) NOT NULL,
  `menu_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_Id`, `menu_Name`) VALUES
(1, 'XL Daging'),
(2, 'XL Ayam'),
(3, 'Lava Cheese Daging'),
(4, 'Lava Cheese Ayam'),
(5, 'Sosej Jumbo'),
(6, 'Burger Ayam Goreng Krup Krap'),
(7, 'Burger Ayam Goreng Krup Krap XL'),
(8, 'Burger Chicken Grill'),
(9, 'Burger Sate Ayam'),
(10, 'CheeseSteak'),
(11, 'Burger Hawaian '),
(12, 'Smash Burger'),
(13, 'Itik'),
(14, 'Kambing'),
(15, 'Burger Mix'),
(16, 'Burger Mix XL'),
(17, 'Daging Biasa'),
(18, 'Ayam Biasa'),
(19, 'Benjo'),
(20, 'Wagyu Burger'),
(21, 'Ayam Goreng '),
(22, 'Fries '),
(23, 'Ayam Popcorn '),
(24, 'Nugget Tempura'),
(25, 'Cheezy Wedges'),
(26, 'Chocolate'),
(27, 'IndoCafe'),
(28, 'IndoCafe \'O\''),
(29, 'Teh'),
(30, 'Teh \'O\''),
(31, 'Teh \'O\' Limau'),
(32, 'Teh \'O\' Laici'),
(33, 'Kopi'),
(34, 'Kopi \'O\''),
(35, 'Limau'),
(36, 'Ribena'),
(37, 'Green Tea'),
(38, 'Limau Asam Boi'),
(39, 'Sirap'),
(40, 'Sirap Bandung'),
(41, 'Sirap Limau'),
(42, 'Oren Sunquick'),
(43, 'Laici'),
(44, 'ExtraJoss Susu'),
(45, 'ExtraJoss '),
(46, 'Jus Oren'),
(47, 'Jus Apple'),
(48, 'Corrot Susu'),
(49, 'F&N'),
(50, 'A&W');

-- --------------------------------------------------------

--
-- Table structure for table `price`
--

CREATE TABLE `price` (
  `Price_ID` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `size` varchar(50) NOT NULL,
  `Price` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price`
--

INSERT INTO `price` (`Price_ID`, `menu_id`, `size`, `Price`) VALUES
(1, 1, 'Single', 5.00),
(2, 1, 'Double', 9.00),
(3, 1, 'Tripple', 12.50),
(4, 2, 'Single', 5.00),
(5, 2, 'Double', 9.00),
(6, 2, 'Tripple', 12.50),
(7, 3, 'Single', 7.00),
(8, 3, 'Double', 13.00),
(9, 3, 'Tripple', 19.00),
(10, 4, 'Single', 7.00),
(11, 4, 'Double', 13.00),
(12, 4, 'Tripple', 19.00),
(13, 5, 'Single', 5.00),
(14, 5, 'Double', 9.00),
(15, 5, 'Tripple', 12.00),
(16, 6, 'Single', 8.00),
(17, 6, 'Double', 10.00),
(18, 6, 'Tripple', 14.50),
(19, 7, 'Single', 10.00),
(20, 7, 'Double', 18.00),
(21, 7, 'Tripple', 24.00),
(22, 8, 'Single', 8.50),
(23, 8, 'Double', 16.00),
(24, 8, 'Tripple', 21.00),
(25, 9, 'Single', 7.00),
(26, 9, 'Double', 13.00),
(27, 9, 'Tripple', 18.00),
(28, 10, 'Single', 9.50),
(29, 11, 'Single', 8.00),
(30, 11, 'Double', 15.00),
(31, 11, 'Tripple', 21.00),
(32, 12, 'Single', 7.50),
(33, 12, 'Double', 14.00),
(34, 12, 'Tripple', 20.00),
(35, 13, 'Single', 16.00),
(36, 14, 'Single', 13.00),
(37, 15, 'Single', 7.00),
(38, 16, 'Single', 8.00),
(39, 17, 'Single', 4.50),
(40, 17, 'Double', 7.00),
(41, 17, 'Tripple', 10.00),
(42, 18, 'Single', 4.50),
(43, 18, 'Double', 7.00),
(44, 18, 'Tripple', 10.00),
(45, 19, 'Single', 3.50),
(46, 19, 'Double', 5.00),
(47, 19, 'Tripple', 6.50),
(48, 20, 'Single', 20.00),
(49, 21, '1pcs', 3.50),
(50, 21, 'Isi', 4.00),
(51, 22, '1 Set', 5.00),
(52, 23, '1 Cup', 5.00),
(53, 24, '6pcs', 5.00),
(54, 24, '13pcs', 10.00),
(55, 25, '1 Set', 6.00),
(56, 26, 'Sejuk', 3.00),
(57, 26, 'Panas', 2.50),
(58, 27, 'Sejuk', 3.00),
(59, 27, 'Panas', 2.50),
(60, 28, 'Sejuk', 2.50),
(61, 28, 'Panas', 2.00),
(62, 29, 'Sejuk', 2.50),
(63, 29, 'Panas', 2.00),
(64, 30, 'Sejuk', 2.00),
(65, 30, 'Panas', 1.50),
(66, 31, 'Sejuk', 2.50),
(67, 31, 'Panas', 2.00),
(68, 32, 'Sejuk', 3.00),
(69, 33, 'Sejuk', 2.50),
(70, 33, 'Panas', 2.00),
(71, 34, 'Sejuk', 2.00),
(72, 34, 'Panas', 1.50),
(73, 35, 'Sejuk', 2.00),
(74, 35, 'Panas', 1.50),
(75, 36, 'Sejuk', 2.00),
(76, 37, 'Sejuk', 3.00),
(77, 37, 'Panas', 2.50),
(78, 38, 'Sejuk', 2.50),
(79, 39, 'Sejuk', 2.00),
(80, 40, 'Sejuk', 2.50),
(81, 41, 'Sejuk', 2.50),
(82, 42, 'Sejuk', 2.00),
(83, 43, 'Sejuk', 2.50),
(84, 44, 'Sejuk', 2.50),
(85, 45, 'Sejuk', 2.00),
(86, 46, 'Sejuk', 3.50),
(87, 47, 'Sejuk', 3.50),
(88, 48, 'Sejuk', 3.50),
(89, 49, 'Sejuk', 2.00),
(90, 50, 'Sejuk', 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_ID` int(3) NOT NULL,
  `staff_Name` varchar(100) NOT NULL,
  `staff_Email` varchar(100) NOT NULL,
  `staff_No` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_ID`, `staff_Name`, `staff_Email`, `staff_No`) VALUES
(1, 'Adam Bin Kamarudin', 'adam@gmail.com', '01158964852'),
(2, 'Danial Bin Abdullah', 'danial@gmail.com', '01935348562'),
(3, 'Lukman Hakim Bin Zaidi', 'lukman_hakim@gmail.com', '0182536499'),
(4, 'Zharfan Fayd Bin Zulkefly', 'Zharfanfayd@gmail.com', '0199444838'),
(5, 'Aiman Bin Wahab', 'aiman@gmail.com', '01284659526'),
(6, 'Adrian Bin Muhammad', 'adrian@gmail.com', '01248659725');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iventory`
--
ALTER TABLE `iventory`
  ADD PRIMARY KEY (`iven_ID`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_Id`);

--
-- Indexes for table `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`Price_ID`),
  ADD KEY `fk_menu` (`menu_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iventory`
--
ALTER TABLE `iventory`
  MODIFY `iven_ID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `price`
--
ALTER TABLE `price`
  MODIFY `Price_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_ID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `price`
--
ALTER TABLE `price`
  ADD CONSTRAINT `fk_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
