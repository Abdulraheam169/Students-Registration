CREATE DATABASE IF NOT EXISTS `student_registration_system`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_registration_system`;

DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 12:30 AM
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
-- Database: `student_registration_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `phone`, `address`, `profile_picture`, `created_at`) VALUES
(1, 'Ahmad Al-Salem', 'ahmad@example.com', '0999123456', 'Damascus, Al-Malki', NULL, '2026-10-28 23:15:21'),
(2, 'Layla Hassan', 'layla@example.com', '0998234567', 'Homs , Al-Inshaat', NULL, '2026-10-28 23:15:21'),
(3, 'Omar Khalid', 'omar@example.com', '0997345678', 'Tartous', NULL, '2026-10-28 23:15:21'),
(4, 'Sara Mansour', 'sara@example.com', '0996456789', 'Aleppo , A-Fardos', NULL, '2026-10-28 23:15:21'),
(5, 'Yousef Nasser', 'yousef@example.com', '0995567890', 'Latakia, City Center', NULL, '2026-10-28 23:15:21'),
(6, 'Mariam Saleh', 'mariam@example.com', '0994678901', 'Damascus', NULL, '2026-10-28 23:15:21'),
(7, 'Khaled Odeh', 'khaled@example.com', '0993789012', 'Tartous', NULL, '2026-10-28 23:15:21'),
(8, 'Dina Farah', 'dina@example.com', '0992890123', 'Hama , Al-Kosor', NULL, '2026-10-28 23:15:21'),
(9, 'Bashar Zaid', 'bashar@example.com', '0991901234', 'Hama', NULL, '2026-10-28 23:15:21'),
(10, 'Huda Rashid', 'huda@example.com', '0990012345', 'Edleb', NULL, '2026-10-28 23:15:21'),
(11, 'Tariq Sami', 'tariq@example.com', '0989112233', 'Banyas', NULL, '2026-10-28 23:15:21'),
(12, 'Rana Ibrahim', 'rana@example.com', '0988223344', 'Halab, Al-Shahbaa', NULL, '2026-10-28 23:15:21'),
(13, 'Jana Mh', 'jana123@gmail.com', '0936167888', 'Damascus', 'student_20261029_021926_f794d0bf.jpg', '2026-10-28 23:19:26'),
(14, 'Ali', 'Ali12!@gmail.com', '0988665595', 'Homs', 'student_20261029_022008_70f36d57.jpg', '2026-10-28 23:20:08'),
(15, 'Ola', 'Ola34@gmail.com', '0936167869', 'Latakia', 'student_20261029_022037_d0be1582.gif', '2026-10-28 23:20:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$S7XvHRLbgG.erB/ZCjrzKe.iQNfcIk/VrEK4pas5qNPgeT0grJ2bi', '2026-10-28 23:15:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_students_email` (`email`),
  ADD KEY `idx_students_name` (`name`),
  ADD KEY `idx_students_phone` (`phone`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
