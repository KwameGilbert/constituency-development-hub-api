-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 11:44 AM
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
-- Database: `const_dev_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `agent_code` varchar(50) DEFAULT NULL COMMENT 'Unique agent identifier',
  `supervisor_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Officer supervising this agent',
  `assigned_communities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of community names/IDs' CHECK (json_valid(`assigned_communities`)),
  `assigned_location` varchar(255) DEFAULT NULL COMMENT 'Primary area of operation',
  `can_submit_reports` tinyint(1) NOT NULL DEFAULT 1,
  `can_collect_data` tinyint(1) NOT NULL DEFAULT 1,
  `can_register_residents` tinyint(1) NOT NULL DEFAULT 0,
  `profile_image` varchar(500) DEFAULT NULL,
  `id_type` enum('ghana_card','voter_id','passport','drivers_license') DEFAULT NULL,
  `id_number` varchar(100) DEFAULT NULL,
  `id_verified` tinyint(1) NOT NULL DEFAULT 0,
  `id_verified_at` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `reports_submitted` int(11) NOT NULL DEFAULT 0,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `user_id`, `agent_code`, `supervisor_id`, `assigned_communities`, `assigned_location`, `can_submit_reports`, `can_collect_data`, `can_register_residents`, `profile_image`, `id_type`, `id_number`, `id_verified`, `id_verified_at`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `reports_submitted`, `last_active_at`, `created_at`, `updated_at`) VALUES
(1, 8, 'AGT-001', 1, '[\"Adum Central\",\"Adum North\"]', 'Adum', 1, 1, 1, NULL, 'ghana_card', 'GHA-123456789-0', 1, '2026-01-03 18:33:21', '123 Main St, Adum', 'Kofi Frimpong', '+233201111111', 25, '2026-01-03 12:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 9, 'AGT-002', 1, '[\"Bantama East\",\"Bantama West\"]', 'Bantama', 1, 1, 0, NULL, 'voter_id', 'VID-987654321', 1, '2026-01-03 18:33:21', '45 Station Road, Bantama', 'Ama Kyere', '+233202222222', 18, '2026-01-02 04:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 10, 'AGT-003', 2, '[\"Asafo Market\",\"Asafo Residential\"]', 'Asafo', 1, 1, 1, NULL, 'ghana_card', 'GHA-567890123-4', 1, '2026-01-03 18:33:21', '78 Commerce Street, Asafo', 'Yaw Mensah', '+233203333333', 32, '2026-01-01 23:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 11, 'AGT-004', 3, '[\"Subin Central\"]', 'Subin', 1, 1, 0, NULL, 'ghana_card', 'GHA-234567890-1', 0, NULL, '12 Unity Lane, Subin', 'Akua Boateng', '+233204444444', 12, '2026-01-02 05:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('general','events','infrastructure','health','education','emergency','other') NOT NULL DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `publish_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `attachment` varchar(500) DEFAULT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `created_by`, `updated_by`, `title`, `slug`, `content`, `category`, `priority`, `status`, `publish_date`, `expiry_date`, `image`, `attachment`, `views`, `is_pinned`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'school management system1', 'school-management-system1', 'i want a school management', 'events', 'high', 'published', '2026-01-13', '2026-01-31', NULL, NULL, 3, 0, '2026-01-13 16:28:51', '2026-01-13 16:28:51', '2026-01-13 17:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL COMMENT 'e.g., project, report, event',
  `entity_id` int(11) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
(1, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-03 17:42:35'),
(2, 2, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2026-01-03 17:47:49'),
(3, 2, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '{\"reason\":\"invalid_password\"}', '2026-01-03 17:48:09'),
(4, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-03 17:48:31'),
(5, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-04 13:22:21'),
(6, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-04 13:29:19'),
(7, 10, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-04 13:37:30'),
(8, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-05 10:42:00'),
(9, 9, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-09 00:16:55'),
(10, 9, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-09 00:18:21'),
(11, 3, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2026-01-09 14:35:58'),
(12, 3, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2026-01-09 14:36:11'),
(13, 3, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2026-01-09 14:36:28'),
(14, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-09 14:40:23'),
(15, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-09 14:46:36'),
(16, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-09 16:15:03'),
(17, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-09 16:44:43'),
(27, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 07:49:27'),
(28, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 07:49:53'),
(29, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 07:50:02'),
(30, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 07:57:45'),
(31, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:02:04'),
(32, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:05:57'),
(33, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 08:09:09'),
(34, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:12:26'),
(35, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:30:53'),
(36, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:37:20'),
(37, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:45:49'),
(38, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:51:52'),
(39, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 08:58:22'),
(40, NULL, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '{\"reason\":\"user_not_found\",\"email\":\"admin@taskforce.gov.gh\"}', '2026-01-10 09:09:28'),
(41, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 09:53:35'),
(42, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 09:59:21'),
(43, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 14:16:53'),
(44, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 14:57:54'),
(45, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 15:19:13'),
(47, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 15:31:50'),
(48, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 15:54:22'),
(50, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 16:23:35'),
(51, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 17:57:07'),
(52, 7, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 21:50:45'),
(53, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 22:10:52'),
(54, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 22:32:54'),
(55, 7, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 23:06:25'),
(56, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-10 23:07:30'),
(57, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 23:39:55'),
(58, 4, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 23:47:16'),
(59, 4, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-10 23:55:49'),
(60, 4, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 00:08:03'),
(61, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 08:15:19'),
(62, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 08:15:28'),
(63, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 08:18:54'),
(64, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 08:19:03'),
(65, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-11 08:21:50'),
(66, 3, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '{\"reason\":\"invalid_password\"}', '2026-01-11 08:26:08'),
(67, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 08:27:48'),
(68, NULL, 'login_failed', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '{\"reason\":\"user_not_found\",\"email\":\"webadmin@example.com\"}', '2026-01-11 08:49:49'),
(69, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 08:50:25'),
(70, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 09:02:38'),
(71, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 20:44:45'),
(72, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 21:03:31'),
(73, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 23:14:00'),
(74, 14, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-11 23:23:20'),
(75, 12, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-12 16:19:06'),
(76, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-13 13:04:31'),
(77, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-13 13:04:42'),
(78, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', NULL, '2026-01-13 13:04:47'),
(79, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 13:05:39'),
(80, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 13:13:26'),
(81, 5, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 13:57:01'),
(82, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 14:06:12'),
(83, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 14:07:51'),
(84, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 14:07:56'),
(85, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 14:08:08'),
(86, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 14:08:20'),
(88, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 15:14:49'),
(89, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 15:15:10'),
(90, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 15:26:22'),
(91, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 15:26:52'),
(92, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 23:27:03'),
(93, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 11:37:23'),
(94, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 14:09:57'),
(95, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 16:18:07'),
(96, 2, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 16:18:35'),
(97, 2, 'profile_update', NULL, NULL, NULL, NULL, NULL, NULL, '{\"updated_fields\":[\"name\",\"phone\",\"bio\"]}', '2026-01-14 17:29:41'),
(98, 4, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 17:30:28'),
(99, 3, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 17:31:08'),
(100, 7, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 17:31:33'),
(101, 6, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 17:32:26'),
(102, 14, 'login', NULL, NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 17:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `created_by`, `updated_by`, `title`, `slug`, `excerpt`, `content`, `image`, `author`, `category`, `tags`, `status`, `is_featured`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
(7, NULL, 3, 'Constituency Town Hall Meeting Scheduled for January 16', 'constituency-town-hall-meeting-scheduled-for-january-15', 'All constituents are invited to the upcoming town hall meeting to discuss development priorities for 2026.', 'The MP\'s office has announced a town hall meeting scheduled for January 15, 2026, at the Adum Community Center.\r\n\r\nThe meeting will provide an opportunity for constituents to share their concerns, ask questions, and contribute to the development planning process.\r\n\r\nAll residents are encouraged to attend and participate in this important civic engagement event.', 'http://app.comdevhub-api.com/uploads/images/blog/image_696113de3ed5c_1767969758.png', NULL, 'news', '\"[]\"', 'published', 0, 5, '2026-01-09 15:42:38', '2026-01-09 15:42:38', '2026-01-14 15:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `community_ideas`
--

CREATE TABLE `community_ideas` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('infrastructure','education','healthcare','environment','social','economic','governance','other') NOT NULL DEFAULT 'other',
  `submitter_name` varchar(255) DEFAULT NULL,
  `submitter_email` varchar(255) DEFAULT NULL,
  `submitter_contact` varchar(50) DEFAULT NULL,
  `submitter_user_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'If submitted by a registered user',
  `status` enum('pending','under_review','approved','rejected','implemented') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `votes` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `estimated_cost` varchar(100) DEFAULT NULL,
  `estimated_cost_min` decimal(15,2) DEFAULT NULL,
  `estimated_cost_max` decimal(15,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `target_beneficiaries` varchar(255) DEFAULT NULL,
  `implementation_timeline` varchar(100) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `implemented_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_ideas`
--

INSERT INTO `community_ideas` (`id`, `title`, `slug`, `description`, `category`, `submitter_name`, `submitter_email`, `submitter_contact`, `submitter_user_id`, `status`, `priority`, `votes`, `estimated_cost`, `estimated_cost_min`, `estimated_cost_max`, `location`, `target_beneficiaries`, `implementation_timeline`, `images`, `documents`, `admin_notes`, `reviewed_by`, `reviewed_at`, `implemented_at`, `created_at`, `updated_at`) VALUES
(1, 'StarLink installation', 'starlink-installation', ' starLink for network easy communication', 'education', 'Abena Ampofowaa Agyei', 'k@a.com', '', NULL, 'pending', 'medium', 1, NULL, NULL, NULL, 'Abuakwa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 17:32:03', '2026-01-14 17:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `community_idea_votes`
--

CREATE TABLE `community_idea_votes` (
  `id` int(11) UNSIGNED NOT NULL,
  `idea_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `voter_ip` varchar(45) DEFAULT NULL,
  `voter_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_idea_votes`
--

INSERT INTO `community_idea_votes` (`id`, `idea_id`, `user_id`, `voter_ip`, `voter_email`, `created_at`) VALUES
(1, 1, NULL, '127.0.0.1', NULL, '2026-01-14 17:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `community_stats`
--

CREATE TABLE `community_stats` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `value` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_stats`
--

INSERT INTO `community_stats` (`id`, `created_by`, `updated_by`, `label`, `value`, `icon`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Projects Completed', '45+', 'check-circle', 1, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 1, NULL, 'Ongoing Projects', '12', 'loader', 2, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 1, NULL, 'Beneficiaries', '350K+', 'users', 3, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 1, NULL, 'Communities Served', '67', 'home', 4, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(5, 1, NULL, 'Issues Resolved', '1,200+', 'check-square', 5, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(6, 1, NULL, 'Investment (GHS)', '25M+', 'trending-up', 6, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `constituency_events`
--

CREATE TABLE `constituency_events` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `venue_address` text DEFAULT NULL,
  `map_url` varchar(500) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled','postponed') NOT NULL DEFAULT 'upcoming',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `max_attendees` int(11) DEFAULT NULL,
  `registration_required` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `constituency_events`
--

INSERT INTO `constituency_events` (`id`, `created_by`, `updated_by`, `name`, `slug`, `description`, `event_date`, `start_time`, `end_time`, `location`, `venue_address`, `map_url`, `image`, `organizer`, `contact_phone`, `contact_email`, `status`, `is_featured`, `max_attendees`, `registration_required`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Town Hall Meeting - Q1 2026', 'town-hall-meeting-q1-2026', 'Quarterly town hall meeting to discuss constituency development updates and address community concerns.', '2026-01-15', '09:00:00', '13:00:00', 'Adum Community Center', 'Main Street, Adum, Kumasi', NULL, NULL, 'MP Office', '+233302123456', 'events@constituency.gov.gh', 'upcoming', 1, 500, 1, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 2, NULL, 'communiity meeting', 'youth-skills-training-workshop', 'Three-day intensive skills training workshop covering ICT, entrepreneurship, and business management.', '2026-01-18', '09:00:00', '17:00:00', 'Oforikrom Youth Center Tanoso', 'Youth Avenue, Oforikrom', NULL, NULL, 'Youth Development Office', '+233302345678', 'youth@constituency.gov.gh', 'upcoming', 0, 200, 1, '2026-01-03 18:33:21', '2026-01-14 00:28:29'),
(5, 2, NULL, 'Clean-Up Campaign - Asafo Market', 'cleanup-campaign-asafo-market', 'Community clean-up exercise at Asafo Market to promote hygiene and sanitation.', '2026-01-24', '06:00:00', '12:00:00', 'Asafo Market', 'Asafo Market, Kumasi', NULL, 'http://app.comdevhub-api.com/uploads/images/events/image_6966de0adfade_1768349194.png', 'Sanitation Department', '+233302567890', 'sanitation@constituency.gov.gh', 'upcoming', 0, 300, 0, '2026-01-03 18:33:21', '2026-01-14 01:06:34'),
(6, NULL, NULL, 'community meeting', 'community-meeting', 'i amd meubc lhb guaJKBKUUKA iuu huib ihbkjui huihb ', '2026-01-26', '10:29:00', '23:30:00', 'Abuakwa', NULL, NULL, 'http://app.comdevhub-api.com/uploads/images/events/image_6966da4fd0e3f_1768348239.png', NULL, NULL, NULL, 'upcoming', 0, 78, 0, '2026-01-14 00:31:01', '2026-01-14 00:57:08');

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `type` enum('address','phone','email','social') NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL COMMENT 'URL for social/email links',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_info`
--

INSERT INTO `contact_info` (`id`, `created_by`, `updated_by`, `type`, `label`, `value`, `icon`, `link`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'address', 'Main Office', 'Block A, Civic Center, Adum, Kumasi', 'map-pin', NULL, 1, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 1, NULL, 'phone', 'Office Line', '+233 302 123 456', 'phone', 'tel:+233302123456', 2, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 1, NULL, 'phone', 'Hotline', '+233 200 111 222', 'phone-call', 'tel:+233200111222', 3, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 1, NULL, 'email', 'General Enquiries', 'info@constituency.gov.gh', 'mail', 'mailto:info@constituency.gov.gh', 4, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(5, 1, NULL, 'email', 'Support', 'support@constituency.gov.gh', 'help-circle', 'mailto:support@constituency.gov.gh', 5, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(6, 1, NULL, 'social', 'Facebook', '@ConstituencyHub', 'facebook', 'https://facebook.com/ConstituencyHub', 6, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(7, 1, NULL, 'social', 'Twitter', '@ConstituencyHub', 'twitter', 'https://twitter.com/ConstituencyHub', 7, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(8, 1, NULL, 'social', 'Instagram', '@constituency_hub', 'instagram', 'https://instagram.com/constituency_hub', 8, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employment_jobs`
--

CREATE TABLE `employment_jobs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `company` varchar(255) DEFAULT NULL COMMENT 'Organization or department offering the job',
  `location` varchar(255) NOT NULL,
  `job_type` enum('full_time','part_time','contract','internship','temporary','volunteer') NOT NULL DEFAULT 'full_time',
  `salary_range` varchar(100) DEFAULT NULL,
  `salary_min` decimal(12,2) DEFAULT NULL,
  `salary_max` decimal(12,2) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `application_url` varchar(500) DEFAULT NULL,
  `application_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `status` enum('draft','published','closed','archived') NOT NULL DEFAULT 'draft',
  `category` enum('administration','technical','health','education','social_services','finance','communications','monitoring_evaluation','other') NOT NULL DEFAULT 'other',
  `experience_level` enum('entry','mid','senior','executive') NOT NULL DEFAULT 'entry',
  `applicants_count` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_jobs`
--

INSERT INTO `employment_jobs` (`id`, `created_by`, `updated_by`, `title`, `slug`, `description`, `company`, `location`, `job_type`, `salary_range`, `salary_min`, `salary_max`, `requirements`, `responsibilities`, `benefits`, `application_deadline`, `application_url`, `application_email`, `contact_phone`, `status`, `category`, `experience_level`, `applicants_count`, `views`, `is_featured`, `published_at`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'teacher', 'teacher', 'i want a teacher for me ', 'school', 'Oforikrom Youth Center', 'full_time', '5000', NULL, NULL, 'coding', 'coding ', NULL, '2026-01-18', NULL, NULL, NULL, 'published', 'technical', 'senior', 0, 0, 0, '2026-01-13 15:56:24', '2026-01-13 15:56:24', '2026-01-13 15:56:24');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `created_by`, `updated_by`, `question`, `answer`, `category`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'How can I report an issue in my community?', 'You can report community issues through our online portal by visiting the \"Report Issue\" section. Fill out the form with details about the issue, location, and any supporting images. You will receive a case ID for tracking your report.', 'Issue Reporting', 1, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 1, NULL, 'How do I track the status of my issue report?', 'Use the case ID provided when you submitted your report to track its status. Visit the \"Track Issue\" page and enter your case ID to see updates, assigned officers, and resolution progress.', 'Issue Reporting', 2, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 1, NULL, 'How can I contact my MP?', 'You can reach the MP\'s office through our contact page. We are available at our constituency office on weekdays from 8 AM to 5 PM. You can also send an email or call our hotline for urgent matters.', 'Contact', 3, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 1, NULL, 'How can I apply for support from the MP\'s Common Fund?', 'To apply for support from the Common Fund, visit our office with a formal written request, valid ID, and relevant supporting documents. Applications are reviewed monthly by the constituency development committee.', 'Support', 4, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(5, 1, NULL, 'When are town hall meetings held?', 'Town hall meetings are held quarterly on the second Saturday of January, April, July, and October. Announcements are made through our website, social media, and local information centers at least two weeks in advance.', 'Events', 5, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(6, 1, NULL, 'How can I subscribe to constituency updates?', 'You can subscribe to our newsletter through the subscription form at the bottom of our website. Provide your email and optionally your phone number to receive regular updates about development projects, events, and announcements.', 'General', 6, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(7, 1, NULL, 'What development projects are currently ongoing?', 'Visit our Projects page to see all ongoing, completed, and planned development projects. Each project page includes details about location, budget, timeline, and current progress.', 'Projects', 7, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(8, 1, NULL, 'How can I volunteer for community programs?', 'We welcome volunteers for various community programs. Contact our Youth and Community office or fill out the volunteer registration form on our website. We will match you with programs that align with your interests and skills.', 'Volunteering', 8, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `galleries`
--

CREATE TABLE `galleries` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `cover_image` varchar(500) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galleries`
--

INSERT INTO `galleries` (`id`, `title`, `slug`, `description`, `category`, `date`, `location`, `cover_image`, `images`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Cocoa season', 'cocoa-season-6967a59c3d30a', 'cocoa season for anipulatinf dynamic coding terminologies', 'General', '2026-01-12', 'Abuakwa', 'http://app.comdevhub-api.com/uploads/images/galleries/image_6967a59c37a1c_1768400284.png', '[{\"url\":\"http:\\/\\/app.comdevhub-api.com\\/uploads\\/images\\/galleries\\/items\\/image_6967a59c3858e_1768400284.png\",\"caption\":\"\"},{\"url\":\"http:\\/\\/app.comdevhub-api.com\\/uploads\\/images\\/galleries\\/items\\/image_6967a59c38c00_1768400284.png\",\"caption\":\"\"},{\"url\":\"http:\\/\\/app.comdevhub-api.com\\/uploads\\/images\\/galleries\\/items\\/image_6967a59c38ee0_1768400284.png\",\"caption\":\"\"}]', 'active', NULL, NULL, '2026-01-14 15:18:04', '2026-01-14 15:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) NOT NULL,
  `cta_label` varchar(100) DEFAULT NULL COMMENT 'Call to action button text',
  `cta_link` varchar(500) DEFAULT NULL COMMENT 'Call to action URL',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `starts_at` timestamp NULL DEFAULT NULL COMMENT 'When slide becomes visible',
  `ends_at` timestamp NULL DEFAULT NULL COMMENT 'When slide stops being visible',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `created_by`, `updated_by`, `title`, `subtitle`, `description`, `image`, `cta_label`, `cta_link`, `display_order`, `status`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(2, 1, 3, 'Your Voice Matters', 'Report Issues, Track Progress', 'We are committed to addressing your concerns. Report community issues and track our response in real-time.', 'http://app.comdevhub-api.com/uploads/banners/hero-slides/banner_696677eab5043_1768323050.png', 'Report Issue', '/report-issue', 2, 'active', NULL, NULL, '2026-01-03 18:33:21', '2026-01-13 17:51:22'),
(4, 1, 3, 'Healthcare for All', 'Bringing Quality Healthcare Closer', 'New health facilities and outreach programs ensuring accessible healthcare for every community member.', 'http://app.comdevhub-api.com/uploads/banners/hero-slides/banner_696678b5e6227_1768323253.png', 'Health Programs', '/projects?sector=healthcare', 4, 'active', NULL, NULL, '2026-01-03 18:33:21', '2026-01-13 17:54:13'),
(5, 3, 3, 'KofiBenteh3', 'MP for constituency very legit', NULL, 'http://app.comdevhub-api.com/uploads/banners/hero-slides/banner_696675f76bb08_1768322551.png', NULL, '/about', 1, 'active', NULL, NULL, '2026-01-13 17:42:31', '2026-01-14 01:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `issue_assessment_reports`
--

CREATE TABLE `issue_assessment_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `issue_report_id` int(11) UNSIGNED NOT NULL,
  `submitted_by` int(11) UNSIGNED NOT NULL COMMENT 'task_force_members.id',
  `assessment_summary` text NOT NULL,
  `findings` text DEFAULT NULL,
  `issue_confirmed` tinyint(1) NOT NULL DEFAULT 1,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `estimated_cost` decimal(12,2) DEFAULT NULL,
  `estimated_duration` varchar(100) DEFAULT NULL COMMENT 'e.g., 2 weeks, 3 days',
  `required_resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_resources`)),
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `location_verified` varchar(500) DEFAULT NULL,
  `gps_coordinates` varchar(100) DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected','needs_revision') NOT NULL DEFAULT 'draft',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_reports`
--

CREATE TABLE `issue_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `case_id` varchar(50) NOT NULL COMMENT 'Unique case reference',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of uploaded image URLs' CHECK (json_valid(`images`)),
  `reporter_name` varchar(255) DEFAULT NULL,
  `reporter_email` varchar(255) DEFAULT NULL,
  `reporter_phone` varchar(50) DEFAULT NULL,
  `submitted_by_agent_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Agent who submitted this report',
  `submitted_by_officer_id` int(11) UNSIGNED DEFAULT NULL,
  `assigned_officer_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Officer assigned to handle this issue',
  `assigned_task_force_id` int(11) UNSIGNED DEFAULT NULL,
  `assigned_agent_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Agent assigned for field work',
  `status` enum('submitted','acknowledged','in_progress','resolved','closed','rejected') NOT NULL DEFAULT 'submitted',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `allocated_budget` decimal(12,2) DEFAULT NULL,
  `allocated_resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allocated_resources`)),
  `resolution_notes` text DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `acknowledged_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Officer who acknowledged',
  `forwarded_to_admin_at` timestamp NULL DEFAULT NULL,
  `assigned_to_task_force_at` timestamp NULL DEFAULT NULL,
  `resources_allocated_at` timestamp NULL DEFAULT NULL,
  `resources_allocated_by` int(11) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) UNSIGNED DEFAULT NULL COMMENT 'Officer/Agent who resolved',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issue_reports`
--

INSERT INTO `issue_reports` (`id`, `case_id`, `title`, `description`, `category`, `location`, `latitude`, `longitude`, `images`, `reporter_name`, `reporter_email`, `reporter_phone`, `submitted_by_agent_id`, `submitted_by_officer_id`, `assigned_officer_id`, `assigned_task_force_id`, `assigned_agent_id`, `status`, `priority`, `allocated_budget`, `allocated_resources`, `resolution_notes`, `acknowledged_at`, `acknowledged_by`, `forwarded_to_admin_at`, `assigned_to_task_force_at`, `resources_allocated_at`, `resources_allocated_by`, `resolved_at`, `resolved_by`, `created_at`, `updated_at`) VALUES
(1, 'ISS-2025-0001', 'Pothole on Main Street Adum', 'Large pothole causing traffic hazards and vehicle damage near the central market.', 'Roads', 'Main Street, Adum', 6.68850000, -1.62440000, NULL, 'Kofi Ansah', 'kofi.ansah@email.com', '+233241234567', 1, NULL, 1, NULL, NULL, 'resolved', 'high', NULL, NULL, 'Pothole filled and road surface repaired by maintenance team.', '2025-12-09 18:33:21', NULL, NULL, NULL, NULL, NULL, '2025-12-14 18:33:21', NULL, '2025-12-24 18:33:21', '2026-01-03 18:33:21'),
(2, 'ISS-2025-0002', 'Broken Street Light', 'Street light not working for two weeks creating safety concerns at night.', 'Electricity', 'Station Road, Bantama', 6.70120000, -1.61890000, NULL, 'Ama Serwaa', 'ama.serwaa@email.com', '+233242345678', 2, NULL, 1, NULL, NULL, 'in_progress', 'medium', NULL, NULL, NULL, '2025-12-29 18:33:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-27 18:33:21', '2026-01-03 18:33:21'),
(3, 'ISS-2025-0003', 'Blocked Drainage Channel', 'Drainage blocked with refuse causing flooding during rains.', 'Drainage', 'Market Square, Asafo', 6.68230000, -1.61120000, NULL, 'Yaw Mensah', 'yaw.mensah@email.com', '+233243456789', 3, NULL, 1, 1, NULL, 'acknowledged', 'urgent', NULL, NULL, NULL, '2026-01-01 18:33:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 18:33:21', '2026-01-03 18:33:21'),
(4, 'ISS-2025-0004', 'Water Supply Interruption', 'No water supply for the past week affecting over 50 households.', 'Water', 'Nhyiaeso East', 6.67560000, -1.62780000, NULL, 'Akua Boateng', 'akua.boateng@email.com', '+233244567890', 4, NULL, 2, 2, NULL, 'in_progress', 'high', NULL, NULL, NULL, '2025-12-24 18:33:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-29 18:33:21', '2026-01-03 18:33:21'),
(5, 'ISS-2025-0005', 'Damaged School Roof', 'School roof damaged by recent storm, rainwater entering classrooms.', 'Education', 'Subin Primary School', 6.69340000, -1.61560000, NULL, 'Mr. John Osei', 'john.osei@school.edu.gh', '+233245678901', NULL, NULL, NULL, NULL, NULL, 'submitted', 'high', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-07 18:33:21', '2026-01-03 18:33:21'),
(6, 'ISS-2025-0006', 'Overgrown Vegetation Near Road', 'Tall grass and bushes obstructing visibility at road junction.', 'Environment', 'Tafo Junction', 6.70890000, -1.60450000, NULL, 'Kwame Adjei', 'kwame.adjei@email.com', '+233246789012', NULL, NULL, 4, NULL, NULL, 'resolved', 'medium', NULL, NULL, 'Area cleared by sanitation team.', '2025-12-19 18:33:21', NULL, NULL, NULL, NULL, NULL, '2025-12-22 18:33:21', NULL, '2025-12-12 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `issue_report_comments`
--

CREATE TABLE `issue_report_comments` (
  `id` int(11) UNSIGNED NOT NULL,
  `issue_report_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT 'Officer or Agent who commented',
  `comment` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'If true, only visible to staff',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of file URLs' CHECK (json_valid(`attachments`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_report_status_history`
--

CREATE TABLE `issue_report_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `issue_report_id` int(11) UNSIGNED NOT NULL,
  `changed_by` int(11) UNSIGNED NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_resolution_reports`
--

CREATE TABLE `issue_resolution_reports` (
  `id` int(11) UNSIGNED NOT NULL,
  `issue_report_id` int(11) UNSIGNED NOT NULL,
  `submitted_by` int(11) UNSIGNED NOT NULL COMMENT 'task_force_members.id',
  `resolution_summary` text NOT NULL,
  `work_description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `actual_cost` decimal(12,2) DEFAULT NULL,
  `resources_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resources_used`)),
  `before_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_images`)),
  `after_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_images`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `challenges_faced` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `requires_followup` tinyint(1) NOT NULL DEFAULT 0,
  `followup_notes` text DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected','needs_revision') NOT NULL DEFAULT 'draft',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applicants`
--

CREATE TABLE `job_applicants` (
  `id` int(11) UNSIGNED NOT NULL,
  `job_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `resume_url` varchar(500) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','reviewed','shortlisted','rejected','accepted') NOT NULL DEFAULT 'pending',
  `applied_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` enum('community','suburb','cottage','smaller_community') DEFAULT 'community',
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `population` int(11) UNSIGNED DEFAULT NULL,
  `area_size` decimal(10,2) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `population`, `area_size`, `latitude`, `longitude`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tanoso', 'community', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-09 17:48:21', '2026-01-09 17:48:21'),
(3, 'Kwadaso', 'community', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-09 17:54:42', '2026-01-09 17:54:42'),
(4, 'Opoku1', 'suburb', 3, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-09 18:19:32', '2026-01-09 18:19:57'),
(5, 'Dominase', 'smaller_community', 4, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-09 19:32:19', '2026-01-09 19:32:19'),
(6, 'Koo1', 'cottage', 5, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-09 20:03:27', '2026-01-09 20:03:42'),
(7, 'kofi', 'community', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-10 16:23:53', '2026-01-10 16:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `subscribed_at` timestamp NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `name`, `phone`, `status`, `subscribed_at`, `unsubscribed_at`, `created_at`, `updated_at`) VALUES
(1, 'subscriber1@email.com', 'Emmanuel Asante', '+233247890123', 'active', '2025-08-14 18:33:21', NULL, '2025-08-14 18:33:21', '2026-01-03 18:33:21'),
(2, 'subscriber2@email.com', 'Mary Osei', '+233248901234', 'active', '2025-08-06 18:33:21', NULL, '2025-08-06 18:33:21', '2026-01-03 18:33:21'),
(3, 'subscriber3@email.com', 'Peter Mensah', NULL, 'active', '2025-08-13 18:33:21', NULL, '2025-08-13 18:33:21', '2026-01-03 18:33:21'),
(4, 'subscriber4@email.com', 'Grace Boateng', '+233249012345', 'active', '2025-11-27 18:33:21', NULL, '2025-11-27 18:33:21', '2026-01-03 18:33:21'),
(5, 'subscriber5@email.com', 'Kofi Owusu', NULL, 'active', '2025-11-15 18:33:21', NULL, '2025-11-15 18:33:21', '2026-01-03 18:33:21'),
(6, 'oldsubscriber@email.com', 'Former Subscriber', NULL, 'unsubscribed', '2025-12-15 18:33:21', '2025-12-03 18:33:21', '2025-12-15 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `type` enum('info','success','warning','error','issue','project','announcement','assignment','system') DEFAULT 'info',
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `action_text` varchar(50) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL COMMENT 'Staff ID',
  `title` varchar(100) DEFAULT NULL COMMENT 'Job title',
  `department` varchar(100) DEFAULT NULL,
  `assigned_sectors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of sector IDs they manage' CHECK (json_valid(`assigned_sectors`)),
  `assigned_locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of locations they cover' CHECK (json_valid(`assigned_locations`)),
  `can_manage_projects` tinyint(1) NOT NULL DEFAULT 1,
  `can_manage_reports` tinyint(1) NOT NULL DEFAULT 1,
  `can_manage_events` tinyint(1) NOT NULL DEFAULT 0,
  `can_publish_content` tinyint(1) NOT NULL DEFAULT 0,
  `profile_image` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `office_location` varchar(255) DEFAULT NULL,
  `office_phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `officers`
--

INSERT INTO `officers` (`id`, `user_id`, `employee_id`, `title`, `department`, `assigned_sectors`, `assigned_locations`, `can_manage_projects`, `can_manage_reports`, `can_manage_events`, `can_publish_content`, `profile_image`, `bio`, `office_location`, `office_phone`, `created_at`, `updated_at`) VALUES
(1, 4, 'OFF-001', 'Senior Development Officer', 'Infrastructure', '[\"1\",\"3\"]', '[\"Adum\",\"Asafo\"]', 0, 0, 0, 0, NULL, 'Experienced development officer with 10 years in infrastructure projects', 'Block A, Office 12', '+233302123456', '2026-01-03 18:33:21', '2026-01-13 15:14:12'),
(2, 5, 'OFF-002', 'Health Programs Officer', 'Health', '[2]', '[\"Bantama\",\"Subin\"]', 1, 1, 0, 0, NULL, 'Public health specialist focused on community wellness', 'Block B, Office 5', '+233302234567', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 6, 'OFF-003', 'Education Officer', 'Education', '[4]', '[\"Nhyiaeso\",\"Oforikrom\"]', 1, 1, 1, 1, NULL, 'Education policy expert and school development coordinator', 'Block A, Office 8', '+233302345678', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 7, 'OFF-004', 'Community Liaison Officer', 'Community Relations', '[5,6]', '[\"Tafo\",\"Suame\"]', 0, 1, 1, 0, NULL, 'Community engagement and social development specialist', 'Block C, Office 3', '+233302456789', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20251227080000, 'ConstituencyHubSchema', '2026-01-03 18:33:07', '2026-01-03 18:33:08', 0),
(20251227084000, 'UsersAndRolesSchema', '2026-01-03 18:33:08', '2026-01-03 18:33:10', 0),
(20251227084500, 'AddUserRelationships', '2026-01-03 18:33:10', '2026-01-03 18:33:13', 0),
(20251227090000, 'AddTaskForceWorkflow', '2026-01-03 18:33:13', '2026-01-03 18:33:14', 0),
(20260106110000, 'AddAdminDashboardTables', '2026-01-06 16:43:10', '2026-01-06 16:43:12', 0),
(20260108120000, 'AddNewApiTables', '2026-01-09 00:48:42', '2026-01-09 00:48:43', 0),
(20260109150828, 'AddAdminRole', '2026-01-09 17:12:03', '2026-01-09 17:12:03', 0),
(20260110150000, 'AddYouthRecordsTable', '2026-01-10 16:13:41', '2026-01-10 16:13:41', 0),
(20260113140000, 'AddJobApplicantsTable', '2026-01-13 15:37:35', '2026-01-13 15:37:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `managing_officer_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Officer overseeing the project',
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sector_id` int(11) UNSIGNED NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('planning','ongoing','completed','on_hold','cancelled') NOT NULL DEFAULT 'planning',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `spent` decimal(15,2) DEFAULT 0.00,
  `progress_percent` int(11) NOT NULL DEFAULT 0 COMMENT '0-100',
  `beneficiaries` int(11) DEFAULT NULL COMMENT 'Number of people benefiting',
  `image` varchar(500) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of image URLs' CHECK (json_valid(`gallery`)),
  `contractor` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `created_by`, `updated_by`, `managing_officer_id`, `title`, `slug`, `sector_id`, `location`, `description`, `status`, `start_date`, `end_date`, `budget`, `spent`, `progress_percent`, `beneficiaries`, `image`, `gallery`, `contractor`, `contact_person`, `contact_phone`, `is_featured`, `views`, `created_at`, `updated_at`) VALUES
(2, 1, NULL, 2, 'Bantama Community Health Center', 'bantama-community-health-center', 2, 'Bantama', 'Construction of a modern community health center with outpatient services, maternal care unit, and pharmacy.', 'ongoing', '2025-01-15', '2025-12-31', 1800000.00, 900000.00, 65, 45000, NULL, NULL, 'Modern Healthcare Builders', 'Dr. Ama Boateng', '+233302222333', 1, 890, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 1, NULL, 3, 'Subin Primary School Block', 'subin-primary-school-block', 4, 'Subin', 'Construction of a 6-classroom block with library, computer lab, and sanitary facilities for Subin Primary School.', 'completed', '2024-06-01', '2025-02-28', 950000.00, 920000.00, 100, 800, NULL, NULL, 'Educational Structures Ghana', 'Mr. Yaw Adjei', '+233302333444', 0, 456, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 1, NULL, 1, 'Asafo Market Drainage System', 'asafo-market-drainage-system', 1, 'Asafo Market', 'Installation of comprehensive drainage system to prevent flooding at Asafo Market and surrounding areas.', 'ongoing', '2025-04-01', '2025-10-31', 750000.00, 225000.00, 30, 25000, NULL, NULL, 'Drainage Solutions Ghana', 'Ing. Kwame Osei', '+233302444555', 0, 320, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(5, 1, NULL, NULL, 'Nhyiaeso Water Supply Project', 'nhyiaeso-water-supply-project', 5, 'Nhyiaeso', 'Extension of pipe-borne water supply to underserved areas in Nhyiaeso with 10 standpipes and household connections.', 'planning', '2026-01-15', '2026-08-31', 1200000.00, 0.00, 0, 20000, NULL, NULL, NULL, 'Mr. Emmanuel Asare', '+233302555666', 0, 180, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(6, 1, NULL, NULL, 'Oforikrom Youth Sports Complex', 'oforikrom-youth-sports-complex', 7, 'Oforikrom', 'Multi-purpose sports complex with football pitch, basketball court, and gymnasium for youth development.', 'planning', '2026-03-01', '2027-02-28', 3500000.00, 0.00, 0, 35000, NULL, NULL, NULL, NULL, NULL, 1, 560, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(7, 1, NULL, 1, 'Rural Electrification - Tafo Farms', 'rural-electrification-tafo-farms', 8, 'Tafo Farming Communities', 'Extension of electricity grid to 15 farming communities in Tafo area to support agricultural activities.', 'ongoing', '2025-02-01', '2025-11-30', 1650000.00, 825000.00, 55, 8000, NULL, NULL, 'ECG Contractors', 'Eng. Peter Owusu', '+233302666777', 0, 290, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(8, 1, NULL, 4, 'Suame Agricultural Training Center', 'suame-agricultural-training-center', 6, 'Suame', 'Establishment of agricultural training center with demonstration farms, storage facilities, and processing units.', 'completed', '2024-01-01', '2024-12-15', 2100000.00, 2050000.00, 100, 5000, NULL, NULL, 'AgriDev Ghana', 'Dr. Akua Mensah', '+233302777888', 0, 670, '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `device_name`, `ip_address`, `user_agent`, `expires_at`, `revoked`, `revoked_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'bd978569302640ce5ce6cf08c5334df9f0f60529b18a245a3a7ddf82c0ffedbb', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-10 18:42:35', 0, NULL, '2026-01-03 18:42:35', '2026-01-03 18:42:35'),
(2, 2, '6fc57c5327b0eb356a247e93dff58b35945bfcb59d905995c9ab44807378ea88', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-10 18:48:31', 0, NULL, '2026-01-03 18:48:31', '2026-01-03 18:48:31'),
(3, 3, '57f43c4c7d6be7d41a3c18a139ef5b6b2b36b230af6a626c7559381b993b36d4', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 14:22:21', 0, NULL, '2026-01-04 14:22:21', '2026-01-04 14:22:21'),
(4, 2, 'f443014556ca893f5b231288ddb5437028b04c4ca8df64b56858d75493eebb67', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 14:29:19', 0, NULL, '2026-01-04 14:29:19', '2026-01-04 14:29:19'),
(5, 10, '03394200c83da7d3f46268941558cf8353e8a1d2eb1d325be9bf695904a9c33f', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 14:37:30', 0, NULL, '2026-01-04 14:37:30', '2026-01-04 14:37:30'),
(6, 3, 'dfc40fd546e7fc69a717ffff5348219d0a9ec0ca8bc08ff5b2b6645d145927fe', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-12 11:42:00', 0, NULL, '2026-01-05 11:42:00', '2026-01-05 11:42:00'),
(7, 9, '09aa43922fb03094cb8d5bc82474e3a25064b527432a0e8af5774ec3bd892a1c', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 01:16:55', 0, NULL, '2026-01-09 01:16:55', '2026-01-09 01:16:55'),
(8, 9, 'df13e4a4467c57386e669e2482b983a244116b3f6dc99aae9d77162f97e34f75', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-16 01:18:21', 0, NULL, '2026-01-09 01:18:21', '2026-01-09 01:18:21'),
(9, 3, '8d290bc2688669cb0ff53edc578eff0189cb0c816519f69848957e159b3a604e', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-16 15:40:23', 0, NULL, '2026-01-09 15:40:23', '2026-01-09 15:40:23'),
(10, 5, '38aadfeb16354665ac4b5ab01c573779552385bffaf023ff05e7da96dbee1b59', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-16 15:46:36', 0, NULL, '2026-01-09 15:46:36', '2026-01-09 15:46:36'),
(11, 2, 'd5e18b2c0b7ce512d657f84490bc7d3a05408125f160385b6466e4410a82427d', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-16 17:15:03', 0, NULL, '2026-01-09 17:15:03', '2026-01-09 17:15:03'),
(12, 2, 'f9818ab8e30c95fc6159d44874ba35ac15a1a456fb54b4f0fb2e24d54fe4e955', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-16 17:44:43', 0, NULL, '2026-01-09 17:44:43', '2026-01-09 17:44:43'),
(13, 2, 'cb3bf1fa6db75e74ca37670cb7bb75cdce134e45cb4f1b14371e07e73cc07a01', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 08:49:27', 0, NULL, '2026-01-10 08:49:27', '2026-01-10 08:49:27'),
(14, 2, 'b966e6ce818844514c901606cab32a7b4dc3195c24390a7112f9fd7b396721da', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 08:49:53', 0, NULL, '2026-01-10 08:49:53', '2026-01-10 08:49:53'),
(15, 2, 'aff665d300ec0a222ab9470a6f847945b3998c7eee84b1de88831474f792078f', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 08:50:02', 0, NULL, '2026-01-10 08:50:02', '2026-01-10 08:50:02'),
(16, 2, 'c08149c184c71cf59ff6fdfa6605d9494066a067d708c0e90ba51179713d72c6', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 08:57:45', 0, NULL, '2026-01-10 08:57:45', '2026-01-10 08:57:45'),
(17, 2, '4c6e2f5045da3c339da53cf7fe3f8f67d9357596353b72554fe422de52aafb80', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:02:04', 0, NULL, '2026-01-10 09:02:04', '2026-01-10 09:02:04'),
(18, 2, 'bbaf26fee0e7427660c391efe17cfac7dd70286019f5ec6648756178f62d8a16', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:05:56', 0, NULL, '2026-01-10 09:05:56', '2026-01-10 09:05:56'),
(19, 2, '33038466485f0b7cba5ce402853d73194c5c42e5d11113063efcbbd6bc678ba5', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 09:09:09', 0, NULL, '2026-01-10 09:09:09', '2026-01-10 09:09:09'),
(20, 2, 'a28956baa113592d886e4eba742026be904b3435422b63c9fae431b3ba6e03a5', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:12:26', 0, NULL, '2026-01-10 09:12:26', '2026-01-10 09:12:26'),
(21, 2, '8d808f9a5ae9e0d135435474c5ab0e2f42f55763f6ef251bb4c7ed89cc3384c0', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:30:53', 0, NULL, '2026-01-10 09:30:53', '2026-01-10 09:30:53'),
(22, 2, 'd33260c54e6a65e33f20bff35e425b08f1b730307c33cd74ef7057a8d57c2a23', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:37:20', 0, NULL, '2026-01-10 09:37:20', '2026-01-10 09:37:20'),
(23, 2, '3deb94640298d3c88b3d1dbc2d4c4743feaf93b061d9058308ac603eb7759451', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:45:49', 0, NULL, '2026-01-10 09:45:49', '2026-01-10 09:45:49'),
(24, 2, '0fc2270d3fc89d06ca10c374b15d4bd98e35f984444b2508552470d32f48c370', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:51:52', 0, NULL, '2026-01-10 09:51:52', '2026-01-10 09:51:52'),
(25, 2, '795982951352176d2c6682862e513dc32c438891a1f3b52bb68706badabacacc', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 09:58:22', 0, NULL, '2026-01-10 09:58:22', '2026-01-10 09:58:22'),
(26, 2, 'ad96fd33c4a5283f9f7d9c8bc15dab018f7dd044dd14ffd0fbf27b61ba9148ea', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 10:53:35', 0, NULL, '2026-01-10 10:53:35', '2026-01-10 10:53:35'),
(27, 2, '43079ce33cd0553c73d07ba43b67418574da79af2687aa16c56ad2b8e5718582', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 10:59:21', 0, NULL, '2026-01-10 10:59:21', '2026-01-10 10:59:21'),
(28, 2, 'b2bc66097957ed5d72014a423d7231b426201e193dfa10b1cfbfa2f7f4333f90', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 15:16:53', 0, NULL, '2026-01-10 15:16:53', '2026-01-10 15:16:53'),
(29, 3, '5143dd01dcdd3d5a279be1dae7526d56a9fdd99b7d52a75c210e7448802c80e8', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 15:57:54', 0, NULL, '2026-01-10 15:57:54', '2026-01-10 15:57:54'),
(30, 2, 'cd2fa0275c79663aee0b638e394537236a8d4109c4d6d4ca1572e7767c787fb8', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 16:19:13', 0, NULL, '2026-01-10 16:19:13', '2026-01-10 16:19:13'),
(31, 2, '1c9c4b06ae13d580da01c8a4652fe81bf7d7f7d15947a2e98e2374b7aa43820b', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 16:31:50', 0, NULL, '2026-01-10 16:31:50', '2026-01-10 16:31:50'),
(32, 2, '0569d6d9356164024dc406da9f2be56a4e423505dccde53fbf96f36a77811e47', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 16:54:22', 0, NULL, '2026-01-10 16:54:22', '2026-01-10 16:54:22'),
(33, 2, '9ddf8be36722e01956b166642d0c16e2897c938fb2d3ac64f1ce379c9825ab7e', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 17:23:35', 0, NULL, '2026-01-10 17:23:35', '2026-01-10 17:23:35'),
(34, 2, '2a3d8b8a418b05c5a2cc5514184cface747b32bfe785e2957d1cd31937db3d42', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 18:57:07', 0, NULL, '2026-01-10 18:57:07', '2026-01-10 18:57:07'),
(35, 7, 'fee038d98b5ef46022229867113fc8aff757dcbebb42bcece59da60c8a88b424', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 22:50:45', 0, NULL, '2026-01-10 22:50:45', '2026-01-10 22:50:45'),
(36, 2, '82516309bc386640ce20343cf633a271806eebc623305646fec16a14f6058ff9', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 23:10:52', 0, NULL, '2026-01-10 23:10:52', '2026-01-10 23:10:52'),
(37, 2, '35f4fdfe65cfcea0ef840959126af4c277e72a018825e1acc03e153fef57a251', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-17 23:32:53', 0, NULL, '2026-01-10 23:32:53', '2026-01-10 23:32:53'),
(38, 7, '72204ae890e40e7db235dc38de9a75d713c4cf30b4246a14533c147f7f0dafcf', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-18 00:06:25', 0, NULL, '2026-01-11 00:06:25', '2026-01-11 00:06:25'),
(39, 3, '5926662f65bcdd2815828954063993e9770ff41e517758d2eb32f9251294d77d', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-18 00:07:30', 0, NULL, '2026-01-11 00:07:30', '2026-01-11 00:07:30'),
(40, 3, '905872cbb427107b3a625ef89cd1765b78992ed5d5f94f5a7ccf6a1414202080', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 00:39:55', 0, NULL, '2026-01-11 00:39:55', '2026-01-11 00:39:55'),
(41, 4, '796fbb81058d31542895864fbaaef916ea5b7648128fb9dea7fb7858911ecc22', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 00:47:16', 0, NULL, '2026-01-11 00:47:16', '2026-01-11 00:47:16'),
(42, 4, '5f8357112485020042654c248118e64330d34cf28e3f64cef79d828ea8d84632', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 00:55:49', 0, NULL, '2026-01-11 00:55:49', '2026-01-11 00:55:49'),
(43, 4, '9b133d0fb8435cac65816787fb6db8b16d29aded242bcb1af0947b6ef27102ed', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 01:08:03', 0, NULL, '2026-01-11 01:08:03', '2026-01-11 01:08:03'),
(44, 3, '9ffa3d244dae4a4a13b8aa5425bee970434e6aca92bfbbf177ed3f855190d35d', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 09:15:19', 0, NULL, '2026-01-11 09:15:19', '2026-01-11 09:15:19'),
(45, 3, '1be701e6da972627af592447eee299e71cddf3894a1c1410d884e52d7fccad8c', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 09:15:28', 0, NULL, '2026-01-11 09:15:28', '2026-01-11 09:15:28'),
(46, 3, 'e5ae5c08a8980846a4c5775b95af3fc55eaf9029ab147aeabdb50bfcc63eb28a', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 09:18:54', 0, NULL, '2026-01-11 09:18:54', '2026-01-11 09:18:54'),
(47, 3, 'a68080dd29090be69bf26f81e271a8679ee2a7f8efbac77f5e5b01090ad022cc', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 09:19:03', 0, NULL, '2026-01-11 09:19:03', '2026-01-11 09:19:03'),
(48, 3, '8f859a98da10fc84803b6081777c9ae896093135ae5aee356cb7f58ae3028efb', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 09:21:50', 0, NULL, '2026-01-11 09:21:50', '2026-01-11 09:21:50'),
(49, 3, '6e16679442bdfbafd9deaca119fd4137e8eadf44ac9b7b99195d37b550be0f3e', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-18 09:27:48', 0, NULL, '2026-01-11 09:27:48', '2026-01-11 09:27:48'),
(50, 3, '808a9cd40c6b44cb25b2440dc5e65f7aac6d984e8ba2cec716993246cfc582a7', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-18 09:50:25', 0, NULL, '2026-01-11 09:50:25', '2026-01-11 09:50:25'),
(51, 5, 'b2ea7175a861b8214c650b17964609b932ece0fb453e0c5b9f3b4c3a596fee4a', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-18 10:02:38', 0, NULL, '2026-01-11 10:02:38', '2026-01-11 10:02:38'),
(52, 5, '70f5ec32b1eaf654424b972aab472717e260135540f95b86a32b7cc5ea89d033', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36 Edg/143.0.0.0', '2026-01-18 21:44:44', 0, NULL, '2026-01-11 21:44:44', '2026-01-11 21:44:44'),
(53, 5, 'c7d3683f98f29a6b7e0c1f0b1163def7463616b698a7eeea74a006778b76ce3b', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-18 22:03:31', 0, NULL, '2026-01-11 22:03:31', '2026-01-11 22:03:31'),
(54, 5, '6fc2107fc7cc7ed53b48c50fece7b15bfb0e4b145889fc970fb3d5322c541cad', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-19 00:14:00', 0, NULL, '2026-01-12 00:14:00', '2026-01-12 00:14:00'),
(55, 14, 'be5bf0f7b480c4fee6b2018a3a4eccd9f47cf316c9cb62fdcb4ba42e9a65493e', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-19 00:23:20', 0, NULL, '2026-01-12 00:23:20', '2026-01-12 00:23:20'),
(56, 12, 'daec70fbfc4f29100969b4afa770dd47bba49c79b3af3eae472982887ee8f4dc', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-19 17:19:06', 0, NULL, '2026-01-12 17:19:06', '2026-01-12 17:19:06'),
(57, 5, '2c40e41d25b9dae9b14a747c2384ea177d028d7c13734c33771ec6e1859fa043', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-20 14:04:31', 0, NULL, '2026-01-13 14:04:31', '2026-01-13 14:04:31'),
(58, 5, '58502e75c2c229b889064b4c081855d56e8f9d63832bd0c804e62ff40a97ecae', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-20 14:04:42', 0, NULL, '2026-01-13 14:04:42', '2026-01-13 14:04:42'),
(59, 5, 'e73d9aa660cc59a83f76f2c85124f9c002b107fce19467fad0319e03c7eecd0c', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-20 14:04:46', 0, NULL, '2026-01-13 14:04:46', '2026-01-13 14:04:46'),
(60, 5, '0ba7699f5a735387f3322e9810dab72a49bf2683681234a4ba7254784c5df177', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 14:05:39', 0, NULL, '2026-01-13 14:05:39', '2026-01-13 14:05:39'),
(61, 5, '4481525c5b64ce39d3ba2e4075059e6d806b3cb889861b74dc104e8d9c375b51', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 14:13:26', 0, NULL, '2026-01-13 14:13:26', '2026-01-13 14:13:26'),
(62, 5, '28a9eaa4713740a5ac86aa8644f49ad4ca42f1e430ee838b5d13e0c90103888b', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 14:57:01', 0, NULL, '2026-01-13 14:57:01', '2026-01-13 14:57:01'),
(63, 2, '84359351b54c45b784d503068a2abefedf6730ef88fab73f8419befda3a807ed', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 15:06:12', 0, NULL, '2026-01-13 15:06:12', '2026-01-13 15:06:12'),
(64, 2, '806d60b92291f627c4e2955bcd4b669f4ab417787b36df4faa4836ade11256c2', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 15:07:51', 0, NULL, '2026-01-13 15:07:51', '2026-01-13 15:07:51'),
(65, 2, 'df36e6e9d1046cd15fee74b6fb5b07955ca860f1e24682aea4114239dec477b7', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 15:07:55', 0, NULL, '2026-01-13 15:07:55', '2026-01-13 15:07:55'),
(66, 2, '5be92f20f2663875ab5d12a7681c0211654525d5c2475ea0babc571c66570cf7', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 15:08:08', 0, NULL, '2026-01-13 15:08:08', '2026-01-13 15:08:08'),
(67, 2, 'd2e1329f9916e4404aee68f91bdac7115594947f5070a32d0178115038a4bb82', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 15:08:20', 0, NULL, '2026-01-13 15:08:20', '2026-01-13 15:08:20'),
(68, 2, 'aa436663d27d5ff74238db487fb080de9bb83aec1aedc34255c248c9d1c97316', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 16:14:49', 0, NULL, '2026-01-13 16:14:49', '2026-01-13 16:14:49'),
(69, 2, '3c3f3ed54e577a179bb27a5322e506c32f82e447e28eb6ced94c27ec1dc8c8c9', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 16:15:10', 0, NULL, '2026-01-13 16:15:10', '2026-01-13 16:15:10'),
(70, 2, '0c7baa91bc14898702ca6d169a057cb08a8c210cb3cdddbbbe8888b6070a218b', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 16:26:22', 0, NULL, '2026-01-13 16:26:22', '2026-01-13 16:26:22'),
(71, 3, '43b85c9c211d1f0528c7a4582f3e4d87a87ad1e56742227ff0f00a1373a70c53', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-20 16:26:52', 0, NULL, '2026-01-13 16:26:52', '2026-01-13 16:26:52'),
(72, 3, '70156f16047f73a1d0afcb922ecc7e3ac75dba0dd7dedfe8bc168df518ed9485', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 00:27:02', 0, NULL, '2026-01-14 00:27:03', '2026-01-14 00:27:03'),
(73, 3, '47c7228a9783810a71813f1a9e4d597be8ef02654c156d444c0983b4327be34e', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 12:37:23', 0, NULL, '2026-01-14 12:37:23', '2026-01-14 12:37:23'),
(74, 3, '0a9ace9554e07f092b5da6f355b21ea6d6af0d33d0065b22eb611f68fbcd48f5', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 15:09:57', 0, NULL, '2026-01-14 15:09:57', '2026-01-14 15:09:57'),
(75, 3, 'a8465a9d0016ab0bca118342f5aecb4e962d71788201bde38ff89582d9c381dc', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 17:18:07', 0, NULL, '2026-01-14 17:18:07', '2026-01-14 17:18:07'),
(76, 2, '9d6471e629ea4d2e26317b59c710ccace8851dfdf49624173c74923d4441dfc6', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 17:18:35', 0, NULL, '2026-01-14 17:18:35', '2026-01-14 17:18:35'),
(77, 4, '913948800a5810389b1c4da200da137af7c3e081b2ddb15baf8c0da7bad91963', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 18:30:28', 0, NULL, '2026-01-14 18:30:28', '2026-01-14 18:30:28'),
(78, 3, 'e70969ae56aa83d30956db88c41192343ec15fbebcea0aa973cba9f5074d5284', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 18:31:08', 0, NULL, '2026-01-14 18:31:08', '2026-01-14 18:31:08'),
(79, 7, '24d8f053679eef2ab64f1e3adb0f4c136745e3311487db2e5bbb910935d66f53', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 18:31:33', 0, NULL, '2026-01-14 18:31:33', '2026-01-14 18:31:33'),
(80, 6, '7626661774a1ac1d312445d564861f65f16985d62d9ad020c6d108e307af4a84', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 18:32:26', 0, NULL, '2026-01-14 18:32:26', '2026-01-14 18:32:26'),
(81, 14, '3b9e7e2f92679f8cc4de97c4f77774524592624521f39cbd1261e29491df32d1', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-21 18:37:09', 0, NULL, '2026-01-14 18:37:09', '2026-01-14 18:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL COMMENT 'Icon class or image path',
  `color` varchar(20) DEFAULT NULL COMMENT 'Hex color for UI',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`id`, `created_by`, `updated_by`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Infrastructure3', 'infrastructure', 'Roads, bridges, drainage systems, and public buildings development', 'building', '#3bf4f7', 1, 'active', '2026-01-03 18:33:21', '2026-01-13 15:23:10'),
(2, 1, NULL, 'Healthcare', 'healthcare', 'Hospitals, clinics, medical equipment, and health programs', 'heart-pulse', '#EF4444', 2, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 1, NULL, 'Roads & Transport', 'roads-transport', 'Road construction, rehabilitation, and transport infrastructure', 'road', '#F59E0B', 3, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(4, 1, NULL, 'Education', 'education', 'Schools, educational facilities, and learning programs', 'graduation-cap', '#10B981', 4, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(5, 1, NULL, 'Water & Sanitation', 'water-sanitation', 'Clean water supply, sanitation facilities, and waste management', 'droplet', '#06B6D4', 5, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(6, 1, NULL, 'Agriculture', 'agriculture', 'Farming support, irrigation systems, and agricultural development', 'wheat', '#84CC16', 6, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(7, 1, NULL, 'Youth & Sports', 'youth-sports', 'Youth development programs, sports facilities, and recreational activities', 'users', '#8B5CF6', 7, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(8, 1, NULL, 'Electricity', 'electricity', 'Power supply, electrical infrastructure, and rural electrification', 'zap', '#FBBF24', 8, 'active', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `task_force_members`
--

CREATE TABLE `task_force_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `specialization` enum('infrastructure','health','education','water_sanitation','electricity','roads','general') NOT NULL DEFAULT 'general',
  `assigned_sectors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_sectors`)),
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `can_assess_issues` tinyint(1) NOT NULL DEFAULT 1,
  `can_resolve_issues` tinyint(1) NOT NULL DEFAULT 1,
  `can_request_resources` tinyint(1) NOT NULL DEFAULT 0,
  `profile_image` varchar(500) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `id_number` varchar(100) DEFAULT NULL,
  `id_verified` tinyint(1) NOT NULL DEFAULT 0,
  `id_verified_at` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `assessments_completed` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `resolutions_completed` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_force_members`
--

INSERT INTO `task_force_members` (`id`, `user_id`, `employee_id`, `title`, `specialization`, `assigned_sectors`, `skills`, `can_assess_issues`, `can_resolve_issues`, `can_request_resources`, `profile_image`, `id_type`, `id_number`, `id_verified`, `id_verified_at`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `assessments_completed`, `resolutions_completed`, `last_active_at`, `created_at`, `updated_at`) VALUES
(1, 12, 'TFM-001', 'Infrastructure Specialist', 'infrastructure', '[1,3]', '[\"road construction\",\"drainage systems\",\"building inspection\"]', 1, 1, 1, NULL, 'ghana_card', 'GHA-111222333-4', 1, '2026-01-03 18:33:21', '56 Engineers Ave, Kumasi', 'Mary Tetteh', '+233205555555', 15, 12, '2026-01-03 01:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 13, 'TFM-002', 'Water & Sanitation Expert', 'water_sanitation', '[5]', '[\"water supply\",\"sanitation systems\",\"environmental health\"]', 1, 1, 0, NULL, 'ghana_card', 'GHA-444555666-7', 1, '2026-01-03 18:33:21', '89 Water Works Rd, Kumasi', 'Peter Amoako', '+233206666666', 22, 18, '2026-01-03 12:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('web_admin','officer','agent','task_force','admin') NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','suspended','pending') NOT NULL DEFAULT 'pending',
  `first_login` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `email_verified`, `email_verified_at`, `status`, `first_login`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@eventic.com', NULL, '$argon2id$v=19$m=65536,t=4,p=2$eS9QZHdPVG5xQk96T0E4Uw$SZjba/QDub+aelbZ63IEsz3tNSFsht7x5FBPpZpsL5s', '', 1, '2026-01-03 18:33:19', 'active', 0, NULL, NULL, NULL, '2026-01-03 18:33:19', '2026-01-03 18:33:19'),
(2, 'Super Admin1', 'superadmin@constituency.gov.gh', '+233201234567', '$argon2id$v=19$m=65536,t=4,p=2$SzhLVTAybS4vcXRRbnpCSw$RPPEQ0g2UPO0MLjdvI/5e3oPmGGcybsk/xTMUyG2AvQ', 'admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-01-14 17:18:35', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:29:41'),
(3, 'John Mensah', 'john.mensah@constituency.gov.gh', '+233202345678', '$argon2id$v=19$m=65536,t=4,p=2$UUV1alVJMndiY0R0cjhlMg$qZ+uoNNVFb2I8xZZyvWOIjituIbq6r1gbGLbpTxrBk4', 'web_admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-01-14 18:31:08', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:31:08'),
(4, 'Abena Osei1', 'abena.osei@constituency.gov.gh', '+233203456789', '$argon2id$v=19$m=65536,t=4,p=2$R1d1UTJVTUhyTHY0V1pURA$PmRmiM02Sj1m4eII5S36jZHFN4iullJnXW5ns/ysA6I', 'web_admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-01-14 18:30:28', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:30:28'),
(5, 'Kwame Asante', 'kwame.asante@constituency.gov.gh', '+233204567890', '$argon2id$v=19$m=65536,t=4,p=2$S016Q3YyVE1ZWkphRnBaSA$AZuKIMWrbG/tX8+bVCOsNbg7f1p04me68u0hENLVbIM', 'officer', 1, '2026-01-03 18:33:19', 'active', 0, '2026-01-13 14:57:01', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-13 14:57:01'),
(6, 'Efua Boateng', 'efua.boateng@constituency.gov.gh', '+233205678901', '$argon2id$v=19$m=65536,t=4,p=2$RGhVcjFWL1FzSHNTcEwxdg$xeMlwLAw5mmqu3dsJleuxS2fjT3DwqDL0e+ofbHCpEw', 'officer', 1, '2026-01-03 18:33:20', 'active', 0, '2026-01-14 18:32:26', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:32:26'),
(7, 'Kofi Adjei', 'kofi.adjei@constituency.gov.gh', '+233206789012', '$argon2id$v=19$m=65536,t=4,p=2$bHBJcFMxYi44NXNWM0pWVg$CT6nWaowAmCCrBkoLACgyOtIOOArpgrjxzqsTrsm1jk', 'admin', 1, '2026-01-03 18:33:20', 'active', 0, '2026-01-14 18:31:33', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:31:33'),
(8, 'Akosua Darko', 'akosua.darko@constituency.gov.gh', '+233207890123', '$argon2id$v=19$m=65536,t=4,p=2$M3JKLi40ZVdGbnRaRFJILw$T/PBF/Qjx+3XDgbL7t/KsohDrLvMQ1YVfjAH+9W9Q5M', 'officer', 1, '2026-01-03 18:33:20', 'active', 0, NULL, NULL, NULL, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(9, 'Yaw Frimpong', 'yaw.frimpong@constituency.gov.gh', '+233208901234', '$argon2id$v=19$m=65536,t=4,p=2$QXhLQ2F6WDRuNmpFRXpRaQ$9rM0Zb9auttQqC6gd5ioCnCCuzleb89WtX3ORzSXiJc', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, '2026-01-09 01:18:21', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-09 01:18:21'),
(10, 'Ama Serwaa', 'ama.serwaa@constituency.gov.gh', '+233209012345', '$argon2id$v=19$m=65536,t=4,p=2$NXR3RUc5cVRianFrZ0Y0RQ$UcxjArTufXRRnAHEsPxyuniSjuTVsq3DGEO/lmXJ35c', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, '2026-01-04 14:37:30', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-04 14:37:30'),
(11, 'Kwabena Owusu', 'kwabena.owusu@constituency.gov.gh', '+233200123456', '$argon2id$v=19$m=65536,t=4,p=2$Q2lwRDJ6WnFXTC4zRC85Uw$3lr7ZYbNR8nUcIqnrl8OjvUzl8BGQEka7pPJ7WghU30', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, NULL, NULL, NULL, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(12, 'Adwoa Mensah', 'adwoa.mensah@constituency.gov.gh', '+233201234599', '$argon2id$v=19$m=65536,t=4,p=2$dXlLT3VRWVZNazUwTTdmTw$UsuSxIx6jqr13sEpT/HaibfNTiC475qZQRQBo+yzIwQ', 'agent', 1, '2026-01-03 18:33:21', 'active', 0, '2026-01-12 17:19:06', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-12 17:19:06'),
(13, 'Emmanuel Tetteh', 'emmanuel.tetteh@constituency.gov.gh', '+233202345699', '$argon2id$v=19$m=65536,t=4,p=2$bEZOUUpYWFUvenhpNnNVRg$y+f0Cu+4dS7731u3iUKH7Hq3qvJ3HQZpccyMn6WD6Tk', 'officer', 1, '2026-01-03 18:33:21', 'active', 0, NULL, NULL, NULL, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(14, 'Grace Amoako', 'grace.amoako@constituency.gov.gh', '+233203456799', '$argon2id$v=19$m=65536,t=4,p=2$c0owSTdnLmF3L0Y2LmlxNw$qpR3mYaT20uwWSmwINEZ6Ob0q9xle+PQfPpg9a+pucQ', 'task_force', 1, '2026-01-03 18:33:21', 'active', 0, '2026-01-14 18:37:09', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-14 18:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `web_admins`
--

CREATE TABLE `web_admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL COMMENT 'Staff ID',
  `admin_level` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  `department` varchar(100) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Specific permissions override' CHECK (json_valid(`permissions`)),
  `profile_image` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `web_admins`
--

INSERT INTO `web_admins` (`id`, `user_id`, `employee_id`, `admin_level`, `department`, `permissions`, `profile_image`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'ADM-001', 'super_admin', 'Administration', '[\"all\"]', NULL, 'Primary super administrator', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(2, 2, 'ADM-002', 'admin', 'Content Management', '[\"content\",\"events\",\"blog\"]', NULL, 'Content administrator', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 3, 'ADM-003', 'moderator', 'Communications', '[\"blog\",\"events\"]', NULL, 'Communications moderator', '2026-01-03 18:33:21', '2026-01-03 18:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `youth_programs`
--

CREATE TABLE `youth_programs` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `slug` varchar(220) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` enum('education','employment','entrepreneurship','skills_training','sports','arts_culture','technology','health','other') DEFAULT 'other',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `status` enum('draft','upcoming','active','registration_closed','completed','cancelled') DEFAULT 'draft',
  `max_participants` int(11) UNSIGNED DEFAULT NULL,
  `current_enrollment` int(11) UNSIGNED DEFAULT 0,
  `location_id` int(11) UNSIGNED DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `youth_program_participants`
--

CREATE TABLE `youth_program_participants` (
  `id` int(11) UNSIGNED NOT NULL,
  `program_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected','withdrawn','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `youth_records`
--

CREATE TABLE `youth_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hometown` varchar(100) DEFAULT NULL,
  `community` varchar(100) DEFAULT NULL,
  `location_id` int(11) UNSIGNED DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `jhs_completed` tinyint(1) DEFAULT 0,
  `shs_qualification` varchar(200) DEFAULT NULL,
  `certificate_qualification` varchar(200) DEFAULT NULL,
  `diploma_qualification` varchar(200) DEFAULT NULL,
  `degree_qualification` varchar(200) DEFAULT NULL,
  `postgraduate_qualification` varchar(200) DEFAULT NULL,
  `professional_qualification` varchar(200) DEFAULT NULL,
  `employment_status` enum('employed','unemployed','student','self_employed') DEFAULT 'unemployed',
  `availability_status` enum('available','unavailable') DEFAULT 'available',
  `current_employment` varchar(300) DEFAULT NULL,
  `preferred_location` varchar(200) DEFAULT NULL,
  `salary_expectation` decimal(10,2) DEFAULT NULL,
  `employment_notes` text DEFAULT NULL,
  `work_experiences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`work_experiences`)),
  `skills` text DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `youth_records`
--

INSERT INTO `youth_records` (`id`, `full_name`, `date_of_birth`, `gender`, `national_id`, `phone`, `email`, `hometown`, `community`, `location_id`, `education_level`, `jhs_completed`, `shs_qualification`, `certificate_qualification`, `diploma_qualification`, `degree_qualification`, `postgraduate_qualification`, `professional_qualification`, `employment_status`, `availability_status`, `current_employment`, `preferred_location`, `salary_expectation`, `employment_notes`, `work_experiences`, `skills`, `interests`, `status`, `admin_notes`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Abena Ampofowaa Agyei1', '2000-01-18', 'male', '1245678916', '+233 55 080 7914', 'a@a.com', 'Kumasi', 'Tanoso', NULL, 'diploma', 1, 'W,BJKW', 'kogi', 'abena', 'ajeasi', 'phd', 'acca', 'unemployed', 'available', 'TEACHER', 'tsnoso', 789456.00, 'uj sdalhn', '[\"i am going to school\",\"i am a bous +\",\"dasb k,am H KNFBJKN DNJO ,\",\"EIUILHFJNWEH LHLKEWQION ;LSAD \",\";JA.SDNIJ K, DAS\"]', 'wqefwedsf', 'fgfds', 'pending', 'werrewrwer', NULL, '2026-01-10 17:22:41', '2026-01-13 15:20:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `agent_code` (`agent_code`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `assigned_location` (`assigned_location`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category` (`category`),
  ADD KEY `priority` (`priority`),
  ADD KEY `status` (`status`),
  ADD KEY `publish_date` (`publish_date`),
  ADD KEY `expiry_date` (`expiry_date`),
  ADD KEY `is_pinned` (`is_pinned`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `entity_type` (`entity_type`,`entity_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `published_at` (`published_at`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `community_ideas`
--
ALTER TABLE `community_ideas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `category` (`category`),
  ADD KEY `priority` (`priority`),
  ADD KEY `votes` (`votes`),
  ADD KEY `submitter_email` (`submitter_email`),
  ADD KEY `submitter_user_id` (`submitter_user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `community_idea_votes`
--
ALTER TABLE `community_idea_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_vote` (`idea_id`,`user_id`),
  ADD KEY `unique_ip_vote` (`idea_id`,`voter_ip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `community_stats`
--
ALTER TABLE `community_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `constituency_events`
--
ALTER TABLE `constituency_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `status` (`status`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `employment_jobs`
--
ALTER TABLE `employment_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `category` (`category`),
  ADD KEY `job_type` (`job_type`),
  ADD KEY `experience_level` (`experience_level`),
  ADD KEY `application_deadline` (`application_deadline`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `galleries_slug_unique` (`slug`),
  ADD KEY `galleries_category_index` (`category`),
  ADD KEY `galleries_status_index` (`status`),
  ADD KEY `galleries_date_index` (`date`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `issue_assessment_reports`
--
ALTER TABLE `issue_assessment_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_report_id` (`issue_report_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `status` (`status`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_id` (`case_id`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`),
  ADD KEY `category` (`category`),
  ADD KEY `location` (`location`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `submitted_by_agent_id` (`submitted_by_agent_id`),
  ADD KEY `assigned_officer_id` (`assigned_officer_id`),
  ADD KEY `assigned_agent_id` (`assigned_agent_id`),
  ADD KEY `acknowledged_by` (`acknowledged_by`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `fk_issue_reports_task_force` (`assigned_task_force_id`);

--
-- Indexes for table `issue_report_comments`
--
ALTER TABLE `issue_report_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_report_id` (`issue_report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_internal` (`is_internal`);

--
-- Indexes for table `issue_report_status_history`
--
ALTER TABLE `issue_report_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_report_id` (`issue_report_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `new_status` (`new_status`);

--
-- Indexes for table `issue_resolution_reports`
--
ALTER TABLE `issue_resolution_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_report_id` (`issue_report_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `status` (`status`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `job_applicants`
--
ALTER TABLE `job_applicants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`),
  ADD KEY `applied_at` (`applied_at`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `type` (`type`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `user_id_2` (`user_id`,`is_read`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `department` (`department`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_email_token` (`email`,`token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `status` (`status`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `location` (`location`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `managing_officer_id` (`managing_officer_id`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `revoked` (`revoked`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `task_force_members`
--
ALTER TABLE `task_force_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `specialization` (`specialization`),
  ADD KEY `id_verified` (`id_verified`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `phone` (`phone`),
  ADD KEY `role` (`role`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `web_admins`
--
ALTER TABLE `web_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `admin_level` (`admin_level`);

--
-- Indexes for table `youth_programs`
--
ALTER TABLE `youth_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `start_date` (`start_date`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `youth_program_participants`
--
ALTER TABLE `youth_program_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_id_2` (`program_id`,`email`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `youth_records`
--
ALTER TABLE `youth_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `employment_status` (`employment_status`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `full_name` (`full_name`,`phone`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `community_ideas`
--
ALTER TABLE `community_ideas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `community_idea_votes`
--
ALTER TABLE `community_idea_votes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `community_stats`
--
ALTER TABLE `community_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `constituency_events`
--
ALTER TABLE `constituency_events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employment_jobs`
--
ALTER TABLE `employment_jobs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `issue_assessment_reports`
--
ALTER TABLE `issue_assessment_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_reports`
--
ALTER TABLE `issue_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `issue_report_comments`
--
ALTER TABLE `issue_report_comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_report_status_history`
--
ALTER TABLE `issue_report_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_resolution_reports`
--
ALTER TABLE `issue_resolution_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applicants`
--
ALTER TABLE `job_applicants`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `task_force_members`
--
ALTER TABLE `task_force_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `web_admins`
--
ALTER TABLE `web_admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `youth_programs`
--
ALTER TABLE `youth_programs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `youth_program_participants`
--
ALTER TABLE `youth_program_participants`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `youth_records`
--
ALTER TABLE `youth_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `agents_ibfk_2` FOREIGN KEY (`supervisor_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_posts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `community_ideas`
--
ALTER TABLE `community_ideas`
  ADD CONSTRAINT `community_ideas_ibfk_1` FOREIGN KEY (`submitter_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `community_ideas_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `community_idea_votes`
--
ALTER TABLE `community_idea_votes`
  ADD CONSTRAINT `community_idea_votes_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `community_ideas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `community_idea_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `community_stats`
--
ALTER TABLE `community_stats`
  ADD CONSTRAINT `community_stats_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `community_stats_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `constituency_events`
--
ALTER TABLE `constituency_events`
  ADD CONSTRAINT `constituency_events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `constituency_events_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD CONSTRAINT `contact_info_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `contact_info_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `email_verification_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employment_jobs`
--
ALTER TABLE `employment_jobs`
  ADD CONSTRAINT `employment_jobs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `employment_jobs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `faqs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD CONSTRAINT `hero_slides_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `hero_slides_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `issue_assessment_reports`
--
ALTER TABLE `issue_assessment_reports`
  ADD CONSTRAINT `issue_assessment_reports_ibfk_1` FOREIGN KEY (`issue_report_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_assessment_reports_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `task_force_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_assessment_reports_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD CONSTRAINT `fk_issue_reports_task_force` FOREIGN KEY (`assigned_task_force_id`) REFERENCES `task_force_members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_1` FOREIGN KEY (`submitted_by_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_2` FOREIGN KEY (`assigned_officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_3` FOREIGN KEY (`assigned_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_4` FOREIGN KEY (`acknowledged_by`) REFERENCES `officers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_5` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `issue_report_comments`
--
ALTER TABLE `issue_report_comments`
  ADD CONSTRAINT `issue_report_comments_ibfk_1` FOREIGN KEY (`issue_report_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_report_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `issue_report_status_history`
--
ALTER TABLE `issue_report_status_history`
  ADD CONSTRAINT `issue_report_status_history_ibfk_1` FOREIGN KEY (`issue_report_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_report_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `issue_resolution_reports`
--
ALTER TABLE `issue_resolution_reports`
  ADD CONSTRAINT `issue_resolution_reports_ibfk_1` FOREIGN KEY (`issue_report_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_resolution_reports_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `task_force_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_resolution_reports_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `job_applicants`
--
ALTER TABLE `job_applicants`
  ADD CONSTRAINT `job_applicants_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `employment_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `officers`
--
ALTER TABLE `officers`
  ADD CONSTRAINT `officers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_ibfk_4` FOREIGN KEY (`managing_officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sectors`
--
ALTER TABLE `sectors`
  ADD CONSTRAINT `sectors_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sectors_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `task_force_members`
--
ALTER TABLE `task_force_members`
  ADD CONSTRAINT `task_force_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `web_admins`
--
ALTER TABLE `web_admins`
  ADD CONSTRAINT `web_admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `youth_programs`
--
ALTER TABLE `youth_programs`
  ADD CONSTRAINT `youth_programs_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `youth_programs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `youth_program_participants`
--
ALTER TABLE `youth_program_participants`
  ADD CONSTRAINT `youth_program_participants_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `youth_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `youth_program_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `youth_records`
--
ALTER TABLE `youth_records`
  ADD CONSTRAINT `youth_records_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `youth_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
