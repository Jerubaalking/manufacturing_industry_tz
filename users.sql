-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 18, 2022 at 10:48 AM
-- Server version: 10.3.35-MariaDB-log-cll-lve
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `misaznmx_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'kelvin cosmas', 'kevo93mushy@gmail.com', '0786363636', 'Manager', NULL, '$2y$10$RMtKqDxSrmS0bx2Jn7QqyONXwzXP89T2RgXffrAKTTp51/5V4No0u', 'fl8yLbbXYIs3NDPr5bpJ5GeJsFP0tU9XEIJWdFL9CfoeX0h3Cp6oxyDEIR2K', '2021-04-18 11:59:28', '2021-04-18 11:59:28'),
(10, 'Justine', 'justinmisana92@gmail.com', '0786363636', 'Superadministrator', NULL, '$2y$10$uBOvACCV57w0jtAhCZ0NJOwaVGz5uufiBtMPBwF7c39dqio/dCAjq', 'VSidrN7jDoFFBHjQxbNJ0Q1wyU1hSXCHWgyzLA7lsjpfYx12DzA2R9lleB6v', NULL, NULL),
(15, 'HUSSEIN IBUVA', 'ibuva85@gmail.com', '0625997916', 'Manager', NULL, '$2y$10$9JFKCpSNIYn/ooSswjukT.KOXZaPxXbgDe7Jg4eCmFJdkR183rNpi', 'PZ2Syyoi4SKC91iXPmOFeInSHEIzGTLLWPXxl27XXZeE9indLU1HcQjFMTFK', NULL, NULL),
(16, 'Mussa Mvutakamba selemani', 'mmvutakamba@gmail.com', '0744358194', 'Accountant', NULL, '$2y$10$k6DnMT7cJjfDmCpvfIhVn.69US2jzHH3OeFZ84FwxSkJbCXEfpJ62', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
