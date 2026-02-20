-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 07:04 PM
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
(1, 8, 'AGT-001', 1, '[\"Adum Central\",\"Adum North\"]', '5', 1, 1, 1, NULL, 'ghana_card', 'GHA-123456789-0', 1, '2026-01-03 18:33:21', '123 Main St, Adum', 'Kofi Frimpong', '+233201111111', 25, '2026-01-03 12:33:21', '2026-01-03 18:33:21', '2026-01-22 10:08:23'),
(2, 9, 'AGT-002', 1, '[\"Bantama East\",\"Bantama West\"]', 'Bantama', 1, 1, 0, NULL, 'voter_id', 'VID-987654321', 1, '2026-01-03 18:33:21', '45 Station Road, Bantama', 'Ama Kyere', '+233202222222', 18, '2026-01-02 04:33:21', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(3, 10, 'AGT-003', 2, '[\"Asafo Market\",\"Asafo Residential\"]', 'Asafo', 1, 1, 1, NULL, 'ghana_card', 'GHA-567890123-4', 1, '2026-01-03 18:33:21', '78 Commerce Street, Asafo', 'Yaw Mensah', '+233203333333', 42, '2026-01-01 23:33:21', '2026-01-03 18:33:21', '2026-02-14 15:30:11'),
(4, 11, 'AGT-004', 3, '[\"Subin Central\"]', 'Subin', 1, 1, 0, NULL, 'ghana_card', 'GHA-234567890-1', 0, NULL, '12 Unity Lane, Subin', 'Akua Boateng', '+233204444444', 14, '2026-01-02 05:33:21', '2026-01-03 18:33:21', '2026-02-13 18:31:26'),
(6, 19, 'AGT-2026-0005', NULL, NULL, '6', 1, 1, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, '2026-01-22 10:09:05', '2026-01-22 10:09:05'),
(7, 24, 'AGT-2026-0006', NULL, '[\"Tano\"]', 'Kwadaso', 1, 1, 0, NULL, 'ghana_card', '', 0, NULL, 'P.O.Box 1277', '', '', 0, NULL, '2026-02-06 14:28:34', '2026-02-06 15:01:36');

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

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `display_order` int(11) UNSIGNED DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `downvotes` int(11) DEFAULT 0,
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

-- --------------------------------------------------------

--
-- Table structure for table `community_idea_votes`
--

CREATE TABLE `community_idea_votes` (
  `id` int(11) UNSIGNED NOT NULL,
  `idea_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `type` enum('up','down') DEFAULT 'up',
  `voter_ip` varchar(45) DEFAULT NULL,
  `voter_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `category` varchar(255) NOT NULL DEFAULT 'Other' COMMENT 'Job category (e.g. Health Services)',
  `sector` varchar(255) DEFAULT NULL COMMENT 'Specific sector (e.g. Nursing)',
  `experience_level` enum('entry','mid','senior','executive') NOT NULL DEFAULT 'entry',
  `applicants_count` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `views` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `status` enum('submitted','under_officer_review','forwarded_to_admin','assigned_to_task_force','assessment_in_progress','assessment_submitted','resources_allocated','resolution_in_progress','resolution_submitted','resolved','closed','rejected') NOT NULL DEFAULT 'submitted',
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sub_sector_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(4, 7, 'OFF-004', 'Community Liaison Officer', 'Community Relations', '[5,6]', '[\"Tafo\",\"Suame\"]', 0, 1, 1, 0, NULL, 'Community engagement and social development specialist', 'Block C, Office 3', '+233302456789', '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(7, 18, 'OFF-2026-0005', 'Officer', 'Education', '[\"1\",\"5\",\"2\",\"6\"]', '[\"Dominase\",\"Kwadaso\",\"Tanoso\",\"kofi\",\"Opoku1\",\"Koo1\"]', 0, 0, 0, 0, NULL, 'Kofi akwasi the good boy', '', NULL, '2026-01-22 10:00:47', '2026-01-22 10:07:05'),
(8, 8, 'OFF-2026-0006', 'Officer', 'Operations', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL, '2026-02-17 17:03:21', '2026-02-17 17:03:21');

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

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `sub_sectors`
--

CREATE TABLE `sub_sectors` (
  `id` int(11) UNSIGNED NOT NULL,
  `sector_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `display_order` int(11) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, 'Super Admin1', 'superadmin@constituency.gov.gh', '+233201234567', '$argon2id$v=19$m=65536,t=4,p=2$SzhLVTAybS4vcXRRbnpCSw$RPPEQ0g2UPO0MLjdvI/5e3oPmGGcybsk/xTMUyG2AvQ', 'admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-02-15 18:50:40', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 18:50:40'),
(3, 'John Mensah', 'john.mensah@constituency.gov.gh', '+233202345678', '$argon2id$v=19$m=65536,t=4,p=2$UUV1alVJMndiY0R0cjhlMg$qZ+uoNNVFb2I8xZZyvWOIjituIbq6r1gbGLbpTxrBk4', 'web_admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-02-15 03:45:28', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 03:45:28'),
(4, 'Abena Osei1', 'abena.osei@constituency.gov.gh', '+233203456789', '$argon2id$v=19$m=65536,t=4,p=2$R1d1UTJVTUhyTHY0V1pURA$PmRmiM02Sj1m4eII5S36jZHFN4iullJnXW5ns/ysA6I', 'web_admin', 1, '2026-01-03 18:33:19', 'active', 0, '2026-02-15 03:17:50', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 03:17:50'),
(5, 'Kwame Asante3', 'kwame.asante@constituency.gov.gh', '+233204567890', '$argon2id$v=19$m=65536,t=4,p=2$NG1rcENNQXEyZnUySE9Zeg$IYmfeismZyaLRFkhqaXPNkzWLOXB8nWZC23mvX3Jb5E', 'officer', 1, '2026-01-03 18:33:19', 'active', 0, '2026-02-15 18:03:52', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 18:03:52'),
(6, 'Efua Boateng1', 'efua.boateng@constituency.gov.gh', '+233205678901', '$argon2id$v=19$m=65536,t=4,p=2$RGhVcjFWL1FzSHNTcEwxdg$xeMlwLAw5mmqu3dsJleuxS2fjT3DwqDL0e+ofbHCpEw', 'officer', 1, '2026-01-03 18:33:20', 'active', 0, '2026-02-15 03:19:02', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 03:19:02'),
(7, 'Kofi Adjei', 'kofi.adjei@constituency.gov.gh', '+233206789012', '$argon2id$v=19$m=65536,t=4,p=2$bHBJcFMxYi44NXNWM0pWVg$CT6nWaowAmCCrBkoLACgyOtIOOArpgrjxzqsTrsm1jk', 'admin', 1, '2026-01-03 18:33:20', 'active', 0, '2026-02-15 04:02:36', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 04:02:36'),
(8, 'Akosua Darko2', 'akosua.darko@constituency.gov.gh', '+233207890123', '$argon2id$v=19$m=65536,t=4,p=2$M3JKLi40ZVdGbnRaRFJILw$T/PBF/Qjx+3XDgbL7t/KsohDrLvMQ1YVfjAH+9W9Q5M', 'officer', 1, '2026-01-03 18:33:20', 'active', 0, '2026-02-17 16:40:26', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-17 16:40:26'),
(9, 'Yaw Frimpong', 'yaw.frimpong@constituency.gov.gh', '+233208901234', '$argon2id$v=19$m=65536,t=4,p=2$QXhLQ2F6WDRuNmpFRXpRaQ$9rM0Zb9auttQqC6gd5ioCnCCuzleb89WtX3ORzSXiJc', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, '2026-01-09 01:18:21', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-01-09 01:18:21'),
(10, 'Ama Serwaa1', 'ama.serwaa@constituency.gov.gh', '+233209012345', '$2y$10$pyWCyO1mfWJfGiEKdEXbR.NbKNU42996tqWFgqApLTF400oa/jUEK', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, '2026-02-15 03:46:48', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 03:46:48'),
(11, 'Kwabena Owusu', 'kwabena.owusu@constituency.gov.gh', '+233200123456', '$argon2id$v=19$m=65536,t=4,p=2$Q2lwRDJ6WnFXTC4zRC85Uw$3lr7ZYbNR8nUcIqnrl8OjvUzl8BGQEka7pPJ7WghU30', 'agent', 1, '2026-01-03 18:33:20', 'active', 0, '2026-02-13 17:52:09', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-13 17:52:09'),
(12, 'Adwoa Mensah', 'adwoa.mensah@constituency.gov.gh', '+233201234599', '$argon2id$v=19$m=65536,t=4,p=2$dXlLT3VRWVZNazUwTTdmTw$UsuSxIx6jqr13sEpT/HaibfNTiC475qZQRQBo+yzIwQ', 'agent', 1, '2026-01-03 18:33:21', 'active', 0, '2026-02-01 23:42:38', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-01 23:42:38'),
(13, 'Emmanuel Tetteh', 'emmanuel.tetteh@constituency.gov.gh', '+233202345699', '$argon2id$v=19$m=65536,t=4,p=2$bEZOUUpYWFUvenhpNnNVRg$y+f0Cu+4dS7731u3iUKH7Hq3qvJ3HQZpccyMn6WD6Tk', 'officer', 1, '2026-01-03 18:33:21', 'active', 0, NULL, NULL, NULL, '2026-01-03 18:33:21', '2026-01-03 18:33:21'),
(14, 'Grace Amoako', 'grace.amoako@constituency.gov.gh', '', '$argon2id$v=19$m=65536,t=4,p=2$c0owSTdnLmF3L0Y2LmlxNw$qpR3mYaT20uwWSmwINEZ6Ob0q9xle+PQfPpg9a+pucQ', 'task_force', 1, '2026-01-03 18:33:21', 'active', 0, '2026-02-15 18:45:09', '127.0.0.1', NULL, '2026-01-03 18:33:21', '2026-02-15 18:45:09'),
(18, 'Omega1', 'omega@gmail.com', '+233 24 123 4567', '$argon2id$v=19$m=65536,t=4,p=2$eURvWkpmajZTZy5WM2JTQg$6Qaxu22XSOXJrrHy827IwK0xrhUOKTXCzFekd5jsX9Q', 'officer', 1, NULL, 'active', 1, NULL, NULL, NULL, '2026-01-22 10:00:47', '2026-01-22 10:07:05'),
(19, 'Abena Ampofowaa Agyei', 'a@a.com', '111-222-3333', '$argon2id$v=19$m=65536,t=4,p=2$Llduemhya1pWUTdlUDFJVA$FDUa0gWNCYxokERFBLWWNkc4yAwk/Xb868YeUx2TbBg', 'agent', 0, NULL, 'pending', 1, NULL, NULL, NULL, '2026-01-22 10:09:05', '2026-01-22 10:09:05'),
(20, 'Joshua Asemani', 'an@gmail.com', '+233 24 123 4567', '$argon2id$v=19$m=65536,t=4,p=2$eTlyc3pZWi51NHFBMVZ6Rg$VyEUR3XvKEYJzmwtmke8KgDPIGQUlgpfJ4xqg8t3HBc', 'task_force', 0, '2026-01-30 21:06:20', 'active', 1, NULL, NULL, NULL, '2026-01-30 21:06:20', '2026-01-30 21:06:20'),
(21, 'Anthony Afriyie', 'anthony@gmail.com', '+233 24 123 4567', '$argon2id$v=19$m=65536,t=4,p=2$MVguajdPU0U1WWlqY2EzRg$UqBhCCtEe4m0JGy8xtHE8MEvPPDe9mubEBtnyz9CXDA', 'agent', 0, NULL, 'pending', 1, NULL, NULL, NULL, '2026-02-06 13:58:06', '2026-02-06 13:58:06'),
(22, 'Anthony Afriyie', 'anthony25@gmail.com', '+233 24 123 4567', '$argon2id$v=19$m=65536,t=4,p=2$czJlTzNWZ1VTdmdML3hCUg$8+smzjaOitxlXmz2ZpCkG7zro/c24AWpsUULn7vsqtc', 'agent', 0, NULL, 'pending', 1, NULL, NULL, NULL, '2026-02-06 14:05:16', '2026-02-06 14:05:16'),
(23, 'Kwadwo Mensah', 'kwadwo@gmil.com', '0551274173', '$argon2id$v=19$m=65536,t=4,p=2$SEZjNXRsYTV3eXBUdS9ZaA$kKNj6mScxpc8cVLdscKs6d0zN0pF6xxphlil4pcOf9A', 'agent', 0, NULL, 'pending', 1, NULL, NULL, NULL, '2026-02-06 14:09:19', '2026-02-06 14:09:19'),
(24, 'Ronaldo Sheep5', 'ronaldosheep@gmail.com', '0551274173', '$argon2id$v=19$m=65536,t=4,p=2$YVFVUS9nNDlOWmxlOXMybw$1nDPv25I9GV8QWvA8SaELuB94T0sJI2Wl6sMRG4TXa4', 'agent', 0, NULL, 'suspended', 1, NULL, NULL, NULL, '2026-02-06 14:28:34', '2026-02-06 15:10:46');

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `display_order` (`display_order`);

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
  ADD KEY `fk_issue_reports_task_force` (`assigned_task_force_id`),
  ADD KEY `sub_sector_id` (`sub_sector_id`);

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
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `sub_sectors`
--
ALTER TABLE `sub_sectors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `status` (`status`);

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_ideas`
--
ALTER TABLE `community_ideas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_idea_votes`
--
ALTER TABLE `community_idea_votes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_stats`
--
ALTER TABLE `community_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `constituency_events`
--
ALTER TABLE `constituency_events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employment_jobs`
--
ALTER TABLE `employment_jobs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_assessment_reports`
--
ALTER TABLE `issue_assessment_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_reports`
--
ALTER TABLE `issue_reports`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_sectors`
--
ALTER TABLE `sub_sectors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_force_members`
--
ALTER TABLE `task_force_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `issue_reports_ibfk_5` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `issue_reports_ibfk_6` FOREIGN KEY (`sub_sector_id`) REFERENCES `sub_sectors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `sectors_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `web_admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sectors_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sub_sectors`
--
ALTER TABLE `sub_sectors`
  ADD CONSTRAINT `sub_sectors_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
