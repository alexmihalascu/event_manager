-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2023 at 07:17 PM
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
-- Database: `event_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Party'),
(2, 'Wedding'),
(3, 'Concert'),
(4, 'Freshman Prom'),
(5, 'Festival');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `event_id`, `user_id`, `comment`, `comment_date`) VALUES
(8, 21, 2, 'particip\r\n', '2023-12-10 14:46:14'),
(9, 21, 5, 'si eu particip', '2023-12-10 14:46:32'),
(12, 17, 2, 'Buna', '2023-12-10 08:51:53'),
(14, 18, 2, 'particip\r\n', '2023-12-10 14:29:49'),
(15, 19, 2, 'particip', '2023-12-10 14:30:12'),
(16, 19, 5, 'hai beto\r\n', '2023-12-10 14:30:25'),
(17, 21, 6, 'si eu particip\r\n', '2023-12-11 07:02:39'),
(18, 20, 7, 'eyo azteca bro', '2023-12-11 08:25:38'),
(20, 21, 3, 'si eu voi participa\r\n', '2023-12-11 12:00:33'),
(21, 20, 2, 'test', '2023-12-11 19:06:55'),
(22, 12, 9, 'pacat ca nu sunt din galati ca veneam\r\n', '2023-12-13 08:04:58'),
(23, 15, 9, 'vine si Jador?', '2023-12-13 08:06:01'),
(24, 15, 2, 'da\r\n', '2023-12-13 08:14:52'),
(25, 12, 2, 'trist prietene', '2023-12-13 08:15:14');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `top_event` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `category_id`, `name`, `location`, `event_date`, `event_time`, `price`, `photo`, `top_event`) VALUES
(12, 3, 'IAN', 'Divino Club Galati', '2022-04-01', '20:00:00', 50.00, 'uploads/events/event_12_photo.png', 1),
(13, 4, 'RAVA', 'Divino Club Galati', '2022-03-30', '20:00:00', 50.00, 'uploads/events/event_13_photo.png', 0),
(14, 1, 'OSCAR', 'Union Jack Studio Galati', '2022-06-10', '00:00:00', 80.00, 'uploads/events/event_14_photo.png', 0),
(15, 1, 'ALEX VELEA', 'Union Jack Studio Galati', '2022-08-20', '00:00:00', 60.00, 'uploads/events/event_15_photo.png', 0),
(16, 3, 'ALBERTO GRASU', 'Divino Club Galati', '2022-10-07', '00:00:00', 80.00, 'uploads/events/event_16_photo.png', 0),
(17, 3, 'BERECHET', 'Divino Club Galati', '2022-10-14', '20:00:00', 80.00, 'uploads/events/event_17_photo.png', 1),
(18, 1, 'IDK', 'Divino Club Galati', '2022-10-21', '20:00:00', 70.00, 'uploads/events/event_18_photo.png', 0),
(19, 1, 'ALBERTNBN', 'Divino Club Galati', '2022-10-28', '20:00:00', 90.00, 'uploads/events/event_19_photo.png', 1),
(20, 1, 'AZTECA', 'Divino Club Galati', '2022-11-18', '20:00:00', 100.00, 'uploads/events/event_20_photo.png', 0),
(21, 1, 'MARKO GLASS X BVCOVIA', 'Divino Club Galati', '2022-12-02', '20:00:00', 100.00, 'uploads/events/event_21_photo.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendance_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_attendance`
--

INSERT INTO `event_attendance` (`id`, `user_id`, `event_id`, `attendance_date`) VALUES
(2, 2, 13, '2023-12-11 21:07:04'),
(4, 5, 21, '2023-12-11 22:54:52'),
(10, 2, 21, '2023-12-11 23:08:03'),
(12, 5, 20, '2023-12-11 23:08:44'),
(13, 5, 25, '2023-12-11 23:08:48'),
(14, 5, 17, '2023-12-11 23:08:52'),
(15, 12, 21, '2023-12-11 23:09:07'),
(16, 12, 20, '2023-12-11 23:09:09'),
(17, 12, 25, '2023-12-11 23:09:12'),
(18, 12, 19, '2023-12-11 23:09:16'),
(19, 2, 20, '2023-12-11 23:10:37'),
(20, 2, 26, '2023-12-11 23:27:58'),
(21, 2, 19, '2023-12-12 00:04:03');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`user_id`, `bio`, `avatar`) VALUES
(2, '', 'alex_avatar.jpg'),
(3, '', 'test_avatar.png'),
(4, '', 'default_avatar.png'),
(5, '', 'pencea_avatar.jpg'),
(6, '', 'shotgun_avatar.png'),
(7, '', 'tjmiles_avatar.jpg'),
(8, '', 'default_avatar.png'),
(9, '', 'mihaibzn_avatar.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`) VALUES
(2, 'alex', '$2y$10$GxGg3auH7Kg8GMJ8JStH8eUUkLBmtQpiTWkHF3YQY2XypE6WR6sHK', 1),
(3, 'test', '$2y$10$G5D4UNcFov1p27iBBcjCR.SMdBIccjBKjDyuz7sDb.hGFFZNuzDmW', 0),
(4, 'gigel', '$2y$10$F37QFulJEW3RkuByEoAbQey5aqgxHhACW8hFoJgdi1Bo1zO6p4Fri', 0),
(5, 'pencea', '$2y$10$eGjoL9RJ/xJzxZYN1EKkduwvyH3xZ/7kpnNTlYsidasyzEhMl8lQW', 0),
(6, 'shotgun', '$2y$10$xnk78F.dXH/CW70n6vYkl.BPf6WHzhOWLlYvEf5pJy8gk30pOFlfe', 0),
(7, 'tjmiles', '$2y$10$17rnQi2ojrCD9VvrkuO6IOwPQ.T3TeCn8tgW6I3uChebUaXZbznci', 0),
(8, 'dragos', '$2y$10$V3kZBKmBZe1M52EPt98o9.Y6/vQZUO1.XJ0rm9h/0us4mdyVbSPF6', 1),
(9, 'mihaibzn', '$2y$10$VzjVSVA39fDetftrK4bDK.8EFZNxrsi5WTtLwMe6aD0Ki7F9rFuAC', 0),
(10, 'mortiitai', '$2y$10$As/Dv3h7ve76BvPoeGKWlOLiTiNz8I1lJlHoOdFnhViss5L.m2Pu6', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
