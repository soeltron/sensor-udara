-- phpMyAdmin SQL Dump
-- Database: `laravel` (Sesuaikan dengan nama database Anda)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `temperature` double(8,2) DEFAULT NULL,
  `humidity` double(8,2) DEFAULT NULL,
  `air_quality` double(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_settings`
--

CREATE TABLE `device_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `max_temperature` double(8,2) NOT NULL DEFAULT 30.00,
  `max_air_quality` double(8,2) NOT NULL DEFAULT 500.00,
  `led_red_status` tinyint(1) NOT NULL DEFAULT 0,
  `led_green_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `device_settings`
--

INSERT INTO `device_settings` (`id`, `max_temperature`, `max_air_quality`, `led_red_status`, `led_green_status`, `created_at`, `updated_at`) VALUES
(1, 30.00, 500.00, 0, 0, NOW(), NOW());

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_settings`
--
ALTER TABLE `device_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_settings`
--
ALTER TABLE `device_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;
