-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 04:29 PM
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
-- Database: `volunteer_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('applied','accepted','rejected') DEFAULT 'applied',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seeker_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `place_id`, `volunteer_id`, `message`, `status`, `created_at`, `seeker_message`) VALUES
(1, 1, 2, 'I\'m amant, I love animals I\'m not scared and would definitely like to help', '', '2025-11-23 10:26:34', NULL),
(2, 1, 3, 'asd', '', '2025-11-23 13:01:50', NULL),
(3, 1, 4, 'asdf', 'accepted', '2025-11-23 13:08:21', NULL),
(4, 2, 2, 'asf', 'accepted', '2025-11-23 13:13:06', NULL),
(5, 3, 2, 'I wanna volunteer', 'accepted', '2025-11-23 13:28:28', 'not so kind hearted'),
(6, 3, 4, 'gagaga', 'accepted', '2025-11-23 13:33:01', NULL),
(7, 2, 6, 'I wanna volunteer', 'accepted', '2025-11-23 13:37:05', NULL),
(8, 1, 6, 'ga', 'accepted', '2025-11-23 14:00:29', NULL),
(9, 3, 6, 'afg', 'accepted', '2025-11-23 14:05:37', NULL),
(10, 4, 3, 'I wanna come volunteer.', 'accepted', '2025-11-23 15:11:39', NULL),
(11, 2, 3, 'I wanna volunteer', 'accepted', '2025-11-23 15:11:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `seeker_id`, `title`, `description`, `requirements`, `location`, `tags`, `image`, `created_at`) VALUES
(1, 1, 'Bear Sanctuary Prishtina', 'this is where we keep the bears that are in danger', 'just dont be afraid and you just help us with some things', 'Pristina', '#volunteer , #bears , #love4animals', '1763892971_bear.png', '2025-11-23 10:16:11'),
(2, 1, 'UNICEF KOSOVO', 'asfasf', 'KIND Hearted', 'Kosovo', '#volunteer , #children , #love4children', '1763903557_unicef.png', '2025-11-23 13:12:37'),
(3, 5, 'United Nations Volunteers', 'Volunteers for people in need.', 'Be kind', 'Albania', '#volunteer , #helpers', '1763904485_unvolunteer.png', '2025-11-23 13:28:05'),
(4, 5, 'L.A. Works Inc', 'we volunteer for everyone in LA', 'just be kind and happy', 'U.S.A', '#volunteer , #helpers , #love4people', '1763910469_laworks.png', '2025-11-23 15:07:49');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `volunteer_id`, `seeker_id`, `place_id`, `rating`, `created_at`, `message`) VALUES
(1, 4, 1, 1, 4, '2025-11-23 13:08:40', NULL),
(2, 2, 1, 2, 5, '2025-11-23 13:13:41', NULL),
(3, 2, 5, 3, 2, '2025-11-23 13:29:08', NULL),
(4, 4, 5, 3, 1, '2025-11-23 13:33:28', 'very bad guy'),
(5, 6, 1, 2, 5, '2025-11-23 13:37:51', 'asf'),
(6, 6, 1, 1, 4, '2025-11-23 14:00:50', 'kind'),
(7, 6, 5, 3, 5, '2025-11-23 14:06:42', 'very kind'),
(8, 3, 1, 2, 5, '2025-11-23 15:12:47', 'very kind guy'),
(9, 3, 5, 4, 5, '2025-11-23 15:13:14', 'very kind and hardworking guy');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('volunteer','seeker') NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `availability` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `name`, `interests`, `skills`, `bio`, `location`, `photo`, `created_at`, `is_admin`, `availability`) VALUES
(1, 'deonbeka@gmail.com', '$2y$10$zlgN0qlLkKVR2LF.8Yi2MuGeiwtH8Zkz0NFDe9Wer23U3S1oJp5WS', 'seeker', 'deon', 'no interests', 'very heavy muscle', 'asd', 'pristina', 'avatar2.png', '2025-11-23 09:39:07', 0, NULL),
(2, 'amantzabeli@gmail.com', '$2y$10$zhifjokrcZktXowIzz7fM.ay4cV3Ki1dMz3rDoF9T0XRyYMMHbaxm', 'volunteer', 'Amant', 'art,animals', 'coding', 'asd', 'Kosovo', 'avatar4.png', '2025-11-23 10:04:52', 0, NULL),
(3, 'dren@gmail.com', '$2y$10$dF5havrAUhpiKSix793K.ugjeomGuLJMnSMbKEjuiKivpsfko0tfi', 'volunteer', 'Dren', 'art,animals', 'coding, fast', 'im tall and fast', 'Albania', 'avatar3.png', '2025-11-23 13:01:17', 0, NULL),
(4, 'amar@gmail.com', '$2y$10$kxl2V/Nk8DLm8NrPyY8x8.xLLRrPg6SR4eamAGK.clB7BMyoxCsqu', 'volunteer', 'amar', 'art,animals', 'coding, fast,strong', 'asd', 'Kosovo', 'avatar1.png', '2025-11-23 13:08:02', 0, NULL),
(5, 'gert@gmail.com', '$2y$10$f1vHm8QZWq1yfJb12aJUOuRel.n8TNr2FGaxFuVTbNa1qn.JNCBie', 'seeker', 'Gert', 'no interests', 'good', 'sgag', 'Kosovo', 'avatar4.png', '2025-11-23 13:25:48', 0, NULL),
(6, 'elmedina@gmail.com', '$2y$10$oQhwthKoYNc1LKv6k.bf/O6yz0hf1ZS.iDCqSlP7aHXNf89RhepiK', 'volunteer', 'Elmedina', 'art,animals', 'coding, fast', 'gag', 'Kosovo', 'avatar1.png', '2025-11-23 13:36:35', 0, NULL),
(7, 'admin@admin.com', '$2y$10$Ti6jG32e3S1xfimYim8o.e0.F42xyvvBFvzkJStuQYxRHgNcDMN/O', 'volunteer', 'admin', 'no interests', 'no', 'Admin', 'Kosovo', 'avatar2.png', '2025-11-23 14:15:54', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`),
  ADD KEY `volunteer_id` (`volunteer_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `volunteer_id` (`volunteer_id`),
  ADD KEY `seeker_id` (`seeker_id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `places`
--
ALTER TABLE `places`
  ADD CONSTRAINT `places_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
