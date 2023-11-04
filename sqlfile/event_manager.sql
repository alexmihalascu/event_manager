-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2023 at 10:16 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `photo` varchar(255) DEFAULT 'default_event.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `location`, `event_date`, `event_time`, `price`, `photo`) VALUES
(12, 'IAN', 'Divino Club Galați', '2022-04-01', '20:00:00', 50.00, 'uploads/events/event_12_photo.png'),
(13, 'RAVA', 'Divino Club Galați', '2022-03-30', '20:00:00', 50.00, 'uploads/events/event_13_photo.png'),
(14, 'OSCAR', 'Union Jack Studio Galați', '2022-06-10', '00:00:00', 80.00, 'uploads/events/event_14_photo.png'),
(15, 'ALEX VELEA', 'Union Jack Studio Galați', '2022-08-20', '00:00:00', 60.00, 'uploads/events/event_15_photo.png'),
(16, 'ALBERTO GRASU', 'Divino Club Galați', '2022-10-07', '00:00:00', 80.00, 'uploads/events/event_16_photo.png'),
(17, 'BERECHET', 'Divino Club Galați', '2022-10-14', '20:00:00', 80.00, 'uploads/events/event_17_photo.png'),
(18, 'IDK', 'Divino Club Galați', '2022-10-21', '20:00:00', 70.00, 'uploads/events/event_18_photo.png'),
(19, 'ALBERTNBN', 'Divino Club Galați', '2022-10-28', '20:00:00', 90.00, 'uploads/events/event_19_photo.png'),
(20, 'AZTECA', 'Divino Club Galați', '2022-11-18', '20:00:00', 100.00, 'uploads/events/event_20_photo.png'),
(21, 'MARKO GLASS X BVCOVIA', 'Divino Club Galați', '2022-12-02', '20:00:00', 100.00, 'uploads/events/event_21_photo.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`, `avatar`) VALUES
(24, 'alex', '$2y$10$GslzT.l6i6y4E8sT902hc.VIVuUYOlFEi7QpffqbBoOcVfM/lvPjW', 1, 'alex_avatar.png'),
(25, 'admin', '$2y$10$FbUjJ1T4G.qw7yoc.B0f1OA2JU8JcctbeghjWe7LehGGPuS0lDQT6', 1, 'defaultavatar.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
