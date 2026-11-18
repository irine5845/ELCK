-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 09:18 AM
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
-- Database: `elck_kisumu`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','editor') DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Super Admin', 'irine5845@gmail.com', '$2y$10$kQrXMAnR4zHoOVThJZCDDeZnp8gEGs9jtxKeSCdSltiDhKIadASx6', 'super_admin', '2026-11-17 07:57:52'),
(2, 'Iryn Odhiambo', 'admin@elck.co.ke', '$2y$10$lUz1KBILU7fGDbGtmW9kY.2nKXbgLKuzAR2dMhZy7X29OufVmPoRG', 'super_admin', '2026-11-17 08:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'Welcome to ELCK KISUMU', 'We warmly welcome everyone to worship with us. Invite your family and friends to be part of our church fellowship.', '2026-11-02 07:57:11'),
(2, 'Church Activities', 'Please keep checking this page for updates about upcoming church programmes and activities.', '2026-11-02 07:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `church_info`
--

CREATE TABLE `church_info` (
  `id` int(11) NOT NULL,
  `church_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `about_text` text DEFAULT NULL,
  `paybill` varchar(50) DEFAULT NULL,
  `till_number` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `church_info`
--

INSERT INTO `church_info` (`id`, `church_name`, `address`, `phone`, `email`, `mission`, `vision`, `about_text`, `paybill`, `till_number`, `updated_at`) VALUES
(1, 'ELCK KISUMU', 'Kisumu, Kenya', NULL, NULL, 'To be updated with the official church mission.', 'To be updated with the official church vision.', 'Welcome to ELCK KISUMU. We are committed to worship,\r\n fellowship, spiritual growth and serving our community.', NULL, NULL, '2026-11-02 06:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'she', 'she@gmail.com', '0712345678', 'trial', 'trial purposes', 'read', '2026-11-17 08:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `image`, `created_at`) VALUES
(1, 'Sunday Worship Service', 'Join us for a time of worship, prayer, fellowship and the teaching of Gods Word.', '2026-11-08 09:00:00', 'ELCK KISUMU Church', NULL, '2026-11-02 07:57:11'),
(2, 'Church Prayer Meeting', 'An opportunity to come together in prayer and intercession.', '2026-11-14 15:00:00', 'ELCK KISUMU Church', NULL, '2026-11-02 07:57:11'),
(3, 'Youth Fellowship', 'A gathering for young people to grow spiritually and encourage one another.', '2026-11-21 14:00:00', 'ELCK KISUMU Church', NULL, '2026-11-02 07:57:11'),
(4, 'trial', 'Trial trail', '2026-11-17 11:12:00', 'kisumu parish', 'bf111d7aa9754450be4257ec0a8827a3.jpg', '2026-11-17 08:13:05'),
(5, 'trial', 'tial', '2026-11-20 11:19:00', 'kisumu parish', '6a18388e7bc18aa3cc5f9101af765670.jpg', '2026-11-17 08:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image`, `description`, `created_at`) VALUES
(1, 'Sunday Worship', 'sunday-worship.jpg', 'Moments of worship and praise during a Sunday service.', '2026-11-17 07:23:37'),
(2, 'Church Fellowship', 'church-fellowship.jpg', 'Members sharing fellowship and encouraging one another.', '2026-11-17 07:23:37'),
(3, 'Church Celebration', 'church-celebration.jpg', 'Celebrating together as one church family.', '2026-11-17 07:23:37'),
(4, 'trial', '968c6eec55f9387a991147d74d23705b.jpg', 'trial trial', '2026-11-17 08:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `leaders`
--

CREATE TABLE `leaders` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `position` varchar(150) NOT NULL,
  `biography` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaders`
--

INSERT INTO `leaders` (`id`, `full_name`, `position`, `biography`, `photo`, `created_at`) VALUES
(1, 'Place Holder', 'Senior Pastor', 'Committed to spiritual leadership and serving the church.', 'pastor.jpg', '2026-11-02 07:06:14'),
(2, 'Place holder', 'Senior Pastor', 'Committed to spiritual leadership and serving the church.', 'pastor.jpg', '2026-11-02 07:10:10'),
(3, 'Trial Test', 'Senior Pastor', 'Committed to spiritual leadership and serving the church.', 'female pastor.jpg', '2026-11-02 07:11:39'),
(4, 'trial', 'trial trial', 'trial trail test trial trial trial trial', '87384625f45d10a5c6f41b198e24b974.jpg', '2026-11-17 08:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `full_name`, `email`, `phone`, `gender`, `date_of_birth`, `address`, `created_at`) VALUES
(1, 'she she', 'she@gmail.com', '0712345678', 'Male', '2021-02-17', 'manyatta', '2026-11-17 07:54:04');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `request` text NOT NULL,
  `is_private` tinyint(1) DEFAULT 1,
  `status` enum('pending','prayed_for','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sermons`
--

CREATE TABLE `sermons` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `preacher` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `sermon_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sermons`
--

INSERT INTO `sermons` (`id`, `title`, `preacher`, `description`, `video_url`, `sermon_date`, `created_at`) VALUES
(1, 'Walking in Faith', 'Church Pastor', 'A message about trusting God and remaining faithful through every season of life.', 'https://www.youtube.com/watch?v=q44zrx6JusQ', '2026-10-25', '2026-11-02 07:49:53'),
(2, 'The Power of Prayer', 'Church Pastor', 'Understanding the importance of prayer in our daily walk with God.', 'https://www.youtube.com/watch?v=acL2MVicIvY', '2026-10-18', '2026-11-02 07:49:53'),
(3, 'Living in Gods Grace', 'Church Pastor', 'A teaching about the grace of God and how it transforms our lives.', 'https://www.youtube.com/watch?PIQR552Kpos', '2026-10-11', '2026-11-02 07:49:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `church_info`
--
ALTER TABLE `church_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaders`
--
ALTER TABLE `leaders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sermons`
--
ALTER TABLE `sermons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `church_info`
--
ALTER TABLE `church_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leaders`
--
ALTER TABLE `leaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sermons`
--
ALTER TABLE `sermons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
