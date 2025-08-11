-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2025 at 11:35 AM
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
-- Database: `csu_cms_platform`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateTagUsageCount` (IN `tag_id_param` INT)   BEGIN
            UPDATE tags 
            SET usage_count = (
                SELECT COUNT(*) 
                FROM post_tags 
                WHERE tag_id = tag_id_param
            )
            WHERE id = tag_id_param;
        END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `subdomain` varchar(50) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `favicon_url` varchar(255) DEFAULT NULL,
  `theme_color` varchar(7) DEFAULT '#1e3a8a',
  `secondary_color` varchar(7) DEFAULT '#f59e0b',
  `timezone` varchar(50) DEFAULT 'Asia/Manila',
  `language` varchar(5) DEFAULT 'en',
  `locale` varchar(10) DEFAULT 'en_PH',
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `seo_title` varchar(200) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`id`, `name`, `slug`, `code`, `subdomain`, `domain`, `full_name`, `address`, `phone`, `contact_email`, `logo_url`, `favicon_url`, `theme_color`, `secondary_color`, `timezone`, `language`, `locale`, `status`, `settings`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES
(1, 'Andrews Campus', 'andrews', 'andrews', 'andrews', 'andrews.csu.edu.ph', 'Cagayan State University - Andrews Campus', 'Andrews, Cagayan Valley, Philippines', '', 'info@andrews.csu.edu.ph', NULL, NULL, '#003e80', '#0a1fbd', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Andrews Campus - Excellence in Education', 'Cagayan State University Andrews Campus offers quality education in agriculture, engineering, and liberal arts.', '2025-08-01 03:34:17', '2025-08-08 07:35:51'),
(2, 'Aparri Campus', 'aparri', 'aparri', 'aparri', 'aparri.csu.edu.ph', 'Cagayan State University - Aparri Campus', 'Aparri, Cagayan Valley, Philippines', NULL, 'info@aparri.csu.edu.ph', NULL, NULL, '#059669', '#dc2626', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Aparri Campus - Marine and Fisheries Education', 'Specializing in marine biology, fisheries, and coastal resource management programs.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(3, 'Carig Campus', 'carig', 'carig', 'carig', 'carig.csu.edu.ph', 'Cagayan State University - Carig Campus', 'Carig, Tuguegarao City, Cagayan', NULL, 'info@carig.csu.edu.ph', NULL, NULL, '#7c3aed', '#f59e0b', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Carig Campus - Main Campus', 'The main campus of Cagayan State University offering diverse academic programs and research opportunities.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(4, 'Gonzaga Campus', 'gonzaga', 'gonzaga', 'gonzaga', 'gonzaga.csu.edu.ph', 'Cagayan State University - Gonzaga Campus', 'Gonzaga, Cagayan Valley, Philippines', NULL, 'info@gonzaga.csu.edu.ph', NULL, NULL, '#dc2626', '#059669', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Gonzaga Campus - Agricultural Excellence', 'Leading agricultural education and research in crop science and sustainable farming practices.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(5, 'Lallo Campus', 'lallo', 'lallo', 'lallo', 'lallo.csu.edu.ph', 'Cagayan State University - Lallo Campus', 'Lallo, Cagayan Valley, Philippines', NULL, 'info@lallo.csu.edu.ph', NULL, NULL, '#0891b2', '#f59e0b', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Lallo Campus - Technology and Innovation', 'Focused on technology education, engineering, and innovation for rural development.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(6, 'Lasam Campus', 'lasam', 'lasam', 'lasam', 'lasam.csu.edu.ph', 'Cagayan State University - Lasam Campus', 'Lasam, Cagayan Valley, Philippines', NULL, 'info@lasam.csu.edu.ph', NULL, NULL, '#ea580c', '#059669', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Lasam Campus - Community Development', 'Dedicated to community development, social work, and public administration programs.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(7, 'Piat Campus', 'piat', 'piat', 'piat', 'piat.csu.edu.ph', 'Cagayan State University - Piat Campus', 'Piat, Cagayan Valley, Philippines', NULL, 'info@piat.csu.edu.ph', NULL, NULL, '#be185d', '#f59e0b', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Piat Campus - Arts and Sciences', 'Excellence in liberal arts, sciences, and teacher education programs.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(8, 'Sanchez Mira Campus', 'sanchezmira', 'sanchezmira', 'sanchezmira', 'sanchezmira.csu.edu.ph', 'Cagayan State University - Sanchez Mira Campus', 'Sanchez Mira, Cagayan Valley, Philippines', NULL, 'info@sanchezmira.csu.edu.ph', NULL, NULL, '#059669', '#ea580c', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Sanchez Mira Campus - Rural Development', 'Specializing in rural development, agriculture extension, and community outreach programs.', '2025-08-01 03:34:17', '2025-08-01 03:34:17'),
(9, 'Solana Campus', 'solana', 'solana', 'solana', 'solana.csu.edu.ph', 'Cagayan State University - Solana Campus', 'Solana, Cagayan Valley, Philippines', NULL, 'info@solana.csu.edu.ph', NULL, NULL, '#7c2d12', '#f59e0b', 'Asia/Manila', 'en', 'en_PH', 'active', '{\"enable_blog\": true, \"enable_events\": true, \"enable_gallery\": true}', 'CSU Solana Campus - Environmental Studies', 'Leading environmental science, forestry, and natural resource management education.', '2025-08-01 03:34:17', '2025-08-01 03:34:17');

-- --------------------------------------------------------

--
-- Table structure for table `campus_widgets`
--

CREATE TABLE `campus_widgets` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `widget_type_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `position` varchar(50) DEFAULT 'sidebar',
  `sort_order` int(11) DEFAULT 0,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `css_class` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campus_widgets`
--

INSERT INTO `campus_widgets` (`id`, `campus_id`, `widget_type_id`, `title`, `position`, `sort_order`, `config`, `css_class`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 1, 1, 'Announcement', 'sidebar', 2, '{\"content\": \"wew\", \"show_title\": true}', 'test-widget', 1, 1, '2025-08-08 01:54:43', '2025-08-08 03:07:41'),
(4, 1, 1, 'Footer_new', 'footer', 1, '{\"content\":\"<div class=\\\"text-center\\\"><h6 class=\\\"text-primary mb-2\\\">Connect With Us<\\/h6><div class=\\\"d-flex justify-content-center gap-3\\\"><a href=\\\"#\\\" class=\\\"text-muted\\\"><i class=\\\"fab fa-facebook\\\"><\\/i> Facebook<\\/a><a href=\\\"#\\\" class=\\\"text-muted\\\"><i class=\\\"fab fa-twitter\\\"><\\/i> Twitter<\\/a><a href=\\\"#\\\" class=\\\"text-muted\\\"><i class=\\\"fab fa-linkedin\\\"><\\/i> LinkedIn<\\/a><\\/div><\\/div>\",\"show_title\":false}', '', 1, 1, '2025-08-08 01:55:03', '2025-08-08 03:07:41'),
(5, 1, 3, 'Announcements', 'home_sidebar', 1, '{\"count\": 5, \"show_excerpt\": true, \"show_date\": true, \"show_author\": true, \"show_image\": true}', '', 1, 1, '2025-08-08 02:32:31', '2025-08-08 08:18:56'),
(6, 1, 1, 'Quick Announcements', 'sidebar', 1, '{\"content\":\"<div class=\\\"alert alert-info\\\">\\r\\n            <strong>New Semester Registration<\\/strong><br>\\r\\n            Registration for the upcoming semester is now open. Visit the registrar\'s office for more details.\\r\\n        <\\/div>\\r\\n        <div class=\\\"alert alert-warning\\\">\\r\\n            <strong>Library Notice<\\/strong><br>\\r\\n            The library will be closed for maintenance on weekends this month.\\r\\n        <\\/div>\",\"show_title\":true}', NULL, 1, NULL, '2025-08-08 03:04:14', '2025-08-08 03:04:14'),
(7, 1, 1, 'Contact Information', 'sidebar', 3, '{\"content\":\"<p><strong>Andrews Campus - Cagayan State University<\\/strong><\\/p>\\r\\n        <p class=\\\"mb-2\\\">\\r\\n            <svg width=\\\"16\\\" height=\\\"16\\\" fill=\\\"currentColor\\\" class=\\\"bi bi-geo-alt me-2\\\" viewBox=\\\"0 0 16 16\\\">\\r\\n                <path d=\\\"M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z\\\"\\/>\\r\\n                <path d=\\\"M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z\\\"\\/>\\r\\n            <\\/svg>\\r\\n            Andrews, Cagayan Province, Philippines\\r\\n        <\\/p>\\r\\n        <p class=\\\"mb-0\\\">\\r\\n            <svg width=\\\"16\\\" height=\\\"16\\\" fill=\\\"currentColor\\\" class=\\\"bi bi-envelope me-2\\\" viewBox=\\\"0 0 16 16\\\">\\r\\n                <path d=\\\"M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z\\\"\\/>\\r\\n            <\\/svg>\\r\\n            <a href=\\\"mailto:andrews@csu.edu.ph\\\" class=\\\"text-decoration-none\\\">andrews@csu.edu.ph<\\/a>\\r\\n        <\\/p>\",\"show_title\":true}', NULL, 1, NULL, '2025-08-08 03:04:14', '2025-08-08 03:04:14'),
(8, 1, 1, 'Quick Links', 'sidebar', 4, '{\"content\":\"<ul class=\\\"list-unstyled\\\">\\r\\n            <li class=\\\"mb-2\\\"><a href=\\\"#\\\" class=\\\"text-decoration-none\\\">Student Portal<\\/a><\\/li>\\r\\n            <li class=\\\"mb-2\\\"><a href=\\\"#\\\" class=\\\"text-decoration-none\\\">Faculty Directory<\\/a><\\/li>\\r\\n            <li class=\\\"mb-2\\\"><a href=\\\"#\\\" class=\\\"text-decoration-none\\\">Academic Calendar<\\/a><\\/li>\\r\\n            <li class=\\\"mb-2\\\"><a href=\\\"#\\\" class=\\\"text-decoration-none\\\">Library Catalog<\\/a><\\/li>\\r\\n            <li class=\\\"mb-2\\\"><a href=\\\"..\\/login.php\\\" class=\\\"text-decoration-none\\\">Admin Login<\\/a><\\/li>\\r\\n        <\\/ul>\",\"show_title\":true}', NULL, 1, NULL, '2025-08-08 03:04:14', '2025-08-08 03:04:14'),
(9, 1, 3, 'Campus News', 'home_main', 1, '{\"post_id\": \"\", \"show_image\": true, \"show_author\": true, \"show_date\": true, \"show_excerpt\": true, \"show_full_content\": false}', '', 1, NULL, '2025-08-08 03:26:13', '2025-08-08 07:10:19'),
(11, 1, 3, 'Featured Media Gallery', 'home_sidebar', 1, '{\"post_id\": \"\", \"show_image\": true, \"show_author\": true, \"show_date\": true, \"show_excerpt\": true, \"show_full_content\": false}', '', 1, NULL, '2025-08-08 03:26:13', '2025-08-08 08:28:06'),
(13, 1, 10, 'Campus Highlights', 'footer', 1, '{\"count\": 3, \"show_excerpt\": true, \"show_date\": true, \"show_author\": true, \"show_image\": true}', NULL, 1, NULL, '2025-08-08 03:26:13', '2025-08-08 06:54:40');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#1e3a8a',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `post_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `campus_id`, `name`, `slug`, `description`, `parent_id`, `color`, `sort_order`, `is_active`, `meta_title`, `meta_description`, `post_count`, `created_at`, `updated_at`) VALUES
(1, 1, 'Campus News', 'campus-news', 'Latest news and updates from Andrews Campus', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(2, 1, 'Academic Programs', 'academic-programs', 'Information about academic offerings and curricula', NULL, '#059669', 2, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(3, 1, 'Research', 'research', 'Research projects and publications', NULL, '#7c3aed', 3, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(4, 1, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#dc2626', 4, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(5, 2, 'Marine Biology News', 'marine-biology-news', 'Marine and fisheries education updates', NULL, '#059669', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(6, 2, 'Fisheries Programs', 'fisheries-programs', 'Specialized fisheries and aquaculture programs', NULL, '#0891b2', 2, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(7, 2, 'Coastal Research', 'coastal-research', 'Coastal resource management research', NULL, '#7c3aed', 3, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(8, 3, 'Main Campus News', 'main-campus-news', 'News from the main campus', NULL, '#7c3aed', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(9, 3, 'University Updates', 'university-updates', 'University-wide announcements and updates', NULL, '#dc2626', 2, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(10, 3, 'Academic Excellence', 'academic-excellence', 'Academic achievements and programs', NULL, '#059669', 3, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(11, 4, 'Agricultural News', 'agricultural-news', 'Latest in agricultural education', NULL, '#dc2626', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(12, 5, 'Technology Updates', 'technology-updates', 'Technology and innovation news', NULL, '#0891b2', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(13, 6, 'Community Development', 'community-development', 'Community outreach and development', NULL, '#ea580c', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(14, 7, 'Arts and Sciences', 'arts-sciences', 'Liberal arts and sciences news', NULL, '#be185d', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(15, 8, 'Rural Development', 'rural-development', 'Rural development initiatives', NULL, '#059669', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(16, 9, 'Environmental Studies', 'environmental-studies', 'Environmental science and forestry', NULL, '#7c2d12', 1, 1, NULL, NULL, 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(17, 1, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(18, 2, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(19, 3, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(20, 4, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(21, 5, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(22, 6, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(23, 7, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(24, 8, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(25, 9, 'News', 'news', 'Campus news and announcements', NULL, '#1e3a8a', 1, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(32, 1, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(33, 2, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(34, 3, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(35, 4, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(36, 5, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(37, 6, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(38, 7, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(39, 8, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(40, 9, 'Events', 'events', 'Campus events and activities', NULL, '#1e3a8a', 2, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(47, 1, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(48, 2, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(49, 3, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(50, 4, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(51, 5, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(52, 6, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(53, 7, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(54, 8, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(55, 9, 'Academics', 'academics', 'Academic programs and achievements', NULL, '#1e3a8a', 3, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(62, 2, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(63, 3, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(64, 4, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(65, 5, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(66, 6, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(67, 7, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(68, 8, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(69, 9, 'Student Life', 'student-life', 'Student activities and campus life', NULL, '#1e3a8a', 4, 1, NULL, NULL, 0, '2025-08-04 07:19:03', '2025-08-04 07:19:03');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(100) NOT NULL,
  `author_website` varchar(255) DEFAULT NULL,
  `author_ip` varchar(45) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','spam','trash') DEFAULT 'pending',
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` longtext DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `venue_details` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT 0,
  `registration_required` tinyint(1) DEFAULT 0,
  `max_attendees` int(11) DEFAULT NULL,
  `current_attendees` int(11) DEFAULT 0,
  `registration_deadline` datetime DEFAULT NULL,
  `featured_image_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','cancelled','completed') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_extension` varchar(10) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_public` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `campus_id`, `uploader_id`, `filename`, `original_filename`, `file_path`, `file_url`, `file_size`, `mime_type`, `file_type`, `file_extension`, `width`, `height`, `alt_text`, `caption`, `description`, `metadata`, `is_public`, `is_featured`, `created_at`, `updated_at`) VALUES
(2, 1, 19, 'pavvurulun-afi-festival_20250804_144557_40473b.jpg', 'pavvurulun-afi-festival.jpg', 'C:/xampp/htdocs/campus_website2/uploads/andrews/pavvurulun-afi-festival_20250804_144557_40473b.jpg', '/campus_website2/uploads/andrews/pavvurulun-afi-festival_20250804_144557_40473b.jpg', 41462, 'image/jpeg', 'image', 'jpg', NULL, NULL, '', '', '', '{\"width\":526,\"height\":526,\"aspect_ratio\":1}', 1, 0, '2025-08-04 06:45:57', '2025-08-04 06:45:57');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `campus_id`, `name`, `location`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(2, 2, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(3, 3, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(4, 4, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(5, 5, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(6, 6, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(7, 7, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(8, 8, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(9, 9, 'Main Navigation', 'main', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `css_class` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `target` varchar(10) DEFAULT '_self',
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `title`, `url`, `page_id`, `icon`, `css_class`, `sort_order`, `target`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(2, 1, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(3, 1, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(4, 1, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(5, 2, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(6, 2, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(7, 2, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(8, 2, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(9, 3, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(10, 3, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(11, 3, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(12, 3, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(13, 4, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(14, 4, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(15, 4, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(16, 4, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(17, 5, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(18, 5, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(19, 5, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(20, 5, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(21, 6, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(22, 6, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(23, 6, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(24, 6, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(25, 7, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(26, 7, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(27, 7, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(28, 7, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(29, 8, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(30, 8, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(31, 8, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(32, 8, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(33, 9, NULL, 'Home', '/', NULL, NULL, NULL, 1, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(34, 9, NULL, 'About', '/about.php', NULL, NULL, NULL, 2, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(35, 9, NULL, 'News', '/posts.php', NULL, NULL, NULL, 3, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11'),
(36, 9, NULL, 'Contact', '/contact.php', NULL, NULL, NULL, 4, '_self', 1, '2025-08-05 02:26:11', '2025-08-05 02:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `template` varchar(50) DEFAULT 'default',
  `status` enum('draft','published','private') DEFAULT 'draft',
  `is_homepage` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `seo_title` varchar(200) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `campus_id`, `author_id`, `title`, `slug`, `content`, `excerpt`, `parent_id`, `template`, `status`, `is_homepage`, `view_count`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'About Andrews Campus', 'about', '<h2>About CSU Andrews Campus</h2>\n<p>Cagayan State University - Andrews Campus has been serving the educational needs of the Cagayan Valley region for decades. We specialize in agriculture, engineering, and liberal arts programs.</p>\n<h3>Our Mission</h3>\n<p>To provide quality education that prepares students for successful careers and meaningful contributions to society.</p>\n<h3>Academic Programs</h3>\n<ul>\n<li>Bachelor of Science in Agriculture</li>\n<li>Bachelor of Science in Engineering</li>\n<li>Bachelor of Arts in Liberal Arts</li>\n<li>Graduate Programs</li>\n</ul>', 'Learn about Andrews Campus mission, programs, and commitment to excellence.', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(2, 1, 2, 'Welcome to Andrews Campus', 'home', '<h1>Welcome to CSU Andrews Campus</h1>\n<p>Excellence in Agriculture, Engineering, and Liberal Arts</p>\n<p>Discover opportunities for academic growth and personal development at our beautiful campus.</p>', 'Welcome to Andrews Campus homepage.', NULL, 'default', 'published', 1, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(3, 2, 3, 'About Aparri Campus', 'about', '<h2>About CSU Aparri Campus</h2><p>Specializing in marine and fisheries education.</p>', 'About Aparri Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(4, 3, 4, 'About Carig Campus', 'about', '<h2>About CSU Carig Campus</h2><p>The main campus offering diverse academic programs.</p>', 'About Carig Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(5, 4, 5, 'About Gonzaga Campus', 'about', '<h2>About CSU Gonzaga Campus</h2><p>Agricultural excellence and research.</p>', 'About Gonzaga Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(6, 5, 6, 'About Lallo Campus', 'about', '<h2>About CSU Lallo Campus</h2><p>Technology and innovation focus.</p>', 'About Lallo Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(7, 6, 7, 'About Lasam Campus', 'about', '<h2>About CSU Lasam Campus</h2><p>Community development and social work.</p>', 'About Lasam Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(8, 7, 8, 'About Piat Campus', 'about', '<h2>About CSU Piat Campus</h2><p>Arts, sciences, and teacher education.</p>', 'About Piat Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(9, 8, 9, 'About Sanchez Mira Campus', 'about', '<h2>About CSU Sanchez Mira Campus</h2><p>Rural development and agriculture extension.</p>', 'About Sanchez Mira Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(10, 9, 10, 'About Solana Campus', 'about', '<h2>About CSU Solana Campus</h2><p>Environmental science and forestry programs.</p>', 'About Solana Campus', NULL, 'default', 'published', 0, 0, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `featured_image_url` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','published','archived') DEFAULT 'draft',
  `is_featured` tinyint(1) DEFAULT 0,
  `post_type` enum('post','page','announcement') DEFAULT 'post',
  `featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `meta_title_old` varchar(200) DEFAULT NULL,
  `meta_description_old` text DEFAULT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `campus_id`, `author_id`, `title`, `slug`, `content`, `excerpt`, `tags`, `featured_image_url`, `category_id`, `status`, `is_featured`, `post_type`, `featured`, `view_count`, `comment_count`, `meta_title_old`, `meta_description_old`, `meta_title`, `meta_description`, `published_at`, `created_at`, `updated_at`) VALUES
(2, 1, 2, 'EXECOM-MANCOM MEETING HIGHLIGHTS CSU’S MIDYEAR PROGRESS', 'execom-mancom-meeting-highlights-csu-s-midyear-progress', '<p>CSU OIC President <strong>Dr. Arthur G. Ibañez</strong> leads the monthly Executive and Management Committee (Execom-Mancom) meeting at CSU Sanchez Mira Campus, as members present and discuss midyear accomplishments in instruction, finance and administration, research, development, and extension, and internationalization, partnership, and resource mobilization.&nbsp;</p>', 'CSU OIC President Dr. Arthur G. Ibañez leads the monthly Executive and Management Committee (Execom-Mancom) meeting at CSU Sanchez Mira Campus, as me...', NULL, '/campus_website2/uploads/images/featured_1754633900_689596aceaf24.jpg', 3, 'published', 0, 'post', 0, 0, 0, NULL, NULL, 'EXECOM-MANCOM MEETING HIGHLIGHTS CSU’S MIDYEAR PROGRESS', 'CSU OIC President Dr. Arthur G. Ibañez leads the monthly Executive and Management Committee (Execom-Mancom) meeting at CSU Sanchez Mira Campus, as me...', '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-08 06:18:20'),
(3, 2, 3, 'Marine Science Program Expansion', 'marine-science-program-expansion', '<p>Aparri Campus announces the expansion of our Marine Science program with new courses in marine conservation and sustainable fisheries management.</p>\n<p>Our coastal location provides unique opportunities for hands-on learning in marine environments. Students will have access to research vessels and marine laboratories.</p>', 'Aparri Campus expands Marine Science program with new conservation courses.', NULL, NULL, 1, 'published', 1, 'post', 1, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-05 01:56:50'),
(4, 3, 4, 'University-wide Collaboration Initiative', 'university-collaboration-initiative', '<p>Carig Campus, as the main campus, is launching a new collaboration initiative to strengthen partnerships between all CSU campuses.</p>\n<p>This initiative will facilitate resource sharing, joint research projects, and student exchange programs across all nine campuses.</p>', 'Main campus launches initiative to strengthen inter-campus collaboration.', NULL, NULL, 2, 'published', 1, 'post', 1, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-05 01:56:50'),
(5, 4, 5, 'Sustainable Agriculture Practices Workshop', 'sustainable-agriculture-workshop', '<p>Gonzaga Campus will host a workshop on sustainable agriculture practices for local farmers and students.</p>', 'Workshop on sustainable agriculture at Gonzaga Campus.', NULL, NULL, 4, 'published', 0, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(6, 5, 6, 'Technology Innovation Hub Launch', 'technology-innovation-hub', '<p>Lallo Campus introduces a new Technology Innovation Hub to support student entrepreneurs and tech startups.</p>', 'New Technology Innovation Hub at Lallo Campus.', NULL, NULL, 5, 'published', 1, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-05 01:56:50'),
(7, 6, 7, 'Community Outreach Program Success', 'community-outreach-success', '<p>Lasam Campus celebrates the success of its community development outreach programs in surrounding municipalities.</p>', 'Successful community outreach programs at Lasam Campus.', NULL, NULL, 6, 'published', 0, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(8, 7, 8, 'Teacher Education Excellence Award', 'teacher-education-excellence', '<p>Piat Campus receives recognition for excellence in teacher education programs.</p>', 'Piat Campus recognized for teacher education excellence.', NULL, NULL, 7, 'published', 0, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(9, 8, 9, 'Rural Development Research Grant', 'rural-development-grant', '<p>Sanchez Mira Campus receives major research grant for rural development initiatives.</p>', 'Research grant awarded to Sanchez Mira Campus.', NULL, NULL, 8, 'published', 0, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(10, 9, 10, 'Environmental Conservation Project', 'environmental-conservation-project', '<p>Solana Campus launches major environmental conservation project in partnership with local communities.</p>', 'Environmental conservation project at Solana Campus.', NULL, NULL, 9, 'published', 0, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(12, 1, 2, 'Welcome to Andrews Campus', 'welcome-to-andrews-campus', '<p>Welcome to our <strong>campus</strong>! We are excited to share our latest updates and news with you.</p><p>This is a sample post with featured content to showcase our campus website capabilities.</p>', 'Welcome to our campus! We are excited to share our latest updates and news with you.', NULL, '/campus_website2/uploads/images/featured_1754362502_68917286258b8.jpg', NULL, 'published', 1, 'post', 0, 0, 0, NULL, NULL, 'Welcome to Andrews Campus', 'Welcome to our campus! We are excited to share our latest updates and news with you.', '2025-08-04 20:31:08', '2025-08-05 02:31:08', '2025-08-05 02:55:02'),
(13, 2, 3, 'Welcome to Aparri Campus', 'welcome-to-aparri-campus', '<p>Welcome to our campus! We are excited to share our latest updates and news with you.</p><p>This is a sample post with featured content to showcase our campus website capabilities.</p>', 'Welcome to our campus! We are excited to share our latest updates and news with you.', NULL, 'https://via.placeholder.com/800x400/0066cc/ffffff?text=Aparri+Campus', NULL, 'published', 1, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-04 20:31:08', '2025-08-05 02:31:08', '2025-08-05 02:31:08'),
(14, 3, 4, 'Welcome to Carig Campus', 'welcome-to-carig-campus', '<p>Welcome to our campus! We are excited to share our latest updates and news with you.</p><p>This is a sample post with featured content to showcase our campus website capabilities.</p>', 'Welcome to our campus! We are excited to share our latest updates and news with you.', NULL, 'https://via.placeholder.com/800x400/0066cc/ffffff?text=Carig+Campus', NULL, 'published', 1, 'post', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-08-04 20:31:08', '2025-08-05 02:31:08', '2025-08-05 02:31:08'),
(15, 1, 30, 'Kantori Summit', 'kantori-summit', '<p>The Local Government of Tuguegarao City and the Cagayan State University Andrews Campus, College of Teacher Education – Bachelor of Culture and Arts Education invite you to the Kantori Summit, one of the highlights of the 𝗣𝗮𝘃𝘃𝘂𝗿𝘂𝗹𝘂𝗻 𝗔𝗳𝗶 𝗙𝗲𝘀𝘁𝗶𝘃𝗮𝗹 𝟮𝟬𝟮𝟱.</p><p><img src=\"https://static.xx.fbcdn.net/images/emoji.php/v9/t2d/1/16/1f4cd.png\" alt=\"📍\"> August 13, 2025 | Tuguegarao City</p><p>𝗠𝗼𝗿𝗻𝗶𝗻𝗴 𝗦𝗲𝘀𝘀𝗶𝗼𝗻 | 8:00 AM to 12:00</p><p>Tuguegarao City Science High School</p><p>Focus Group Discussion and Conversations with Local Chanters</p><p>𝗔𝗳𝘁𝗲𝗿𝗻𝗼𝗼𝗻 𝗦𝗲𝘀𝘀𝗶𝗼𝗻 | 2:00 PM to 4:00 PM</p><p>Tuguegarao People’s Gymnasium</p><p>Live Performance of the Ibanag Kantori</p><p>Come and watch the Kantori chant these rare and disappearing musical marvels of the Ibanag--Salomon, Pasyon, Vinunga, Mangurug, Versu and Gozos!</p><p><a href=\"https://www.facebook.com/hashtag/kantorysummit2025?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#KantorySummit2025</strong></a> <a href=\"https://www.facebook.com/hashtag/voicesofancestry?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#VoicesOfAncestry</strong></a> <a href=\"https://www.facebook.com/hashtag/pavvurulunafifestival?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#PavvurulunAfiFestival</strong></a> <a href=\"https://www.facebook.com/hashtag/ibanagculture?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#IbanagCulture</strong></a> <a href=\"https://www.facebook.com/hashtag/tuguegaraocity?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#TuguegaraoCity</strong></a> <a href=\"https://www.facebook.com/hashtag/csubcae?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#CSUBCAE</strong></a> <a href=\"https://www.facebook.com/hashtag/culturalheritage?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#CulturalHeritage</strong></a> <a href=\"https://www.facebook.com/hashtag/ibanagpride?__eep__=6&amp;__cft__[0]=AZWKQbebJRDpoiD2pArgZajRM_48wry6DvssHVkloZN3Mh0qEBVVi8QLv0m0pb0MxBoOlTWCg5wHibftvn0AA7t0D-YgvtbJ_gzfy23u_Y1fT6WHJgO8MkywbsCMZD6YXwrVYBwLuO8kwMPKdVqVaPmOVS84GUg-hM43QN5trRO0TqwTBpTxBkkSF0nWqi-ZpQx239b5EcckrfGS3E7FEXOk&amp;__tn__=*NK-R\"><strong>#IbanagPride</strong></a></p>', 'The Local Government of Tuguegarao City and the Cagayan State University Andrews Campus, College of Teacher Education – Bachelor of Culture and Arts...', NULL, '/campus_website2/uploads/images/featured_1754625527_689575f7384ba.jpg', NULL, 'published', 0, 'post', 0, 0, 0, NULL, NULL, 'WELCOME', 'WELCOME', '2025-08-08 03:58:47', '2025-08-08 03:58:47', '2025-08-08 05:43:11'),
(16, 1, 30, 'EW USCF OFFICERS INDUCTED; STUDENT LEADERS URGED TO EMBRACE RESPONSIBILITY AND UNITY', 'ew-uscf-officers-inducted-student-leaders-urged-to-embrace-responsibility-and-unity', '<p>The newly elected officers of the University Student Council Federation (USCF), the highest student governing body of Cagayan State University (CSU), officially took their oath of office today, August 1, in a ceremony held at the CSU Central Administration Conference Hall.</p><p>CSU Officer-in-Charge President Dr. Arthur G. Ibañez administered the oath of office, formally inducting the new set of officers led by Mr. Kyle Aron T. Tan, the incoming USCF Chairperson, who succeeded former Student Regent John Lester S. Jaime, the outgoing chairperson.</p><p>In his message, Dr. Ibañez emphasized the weight of responsibility that comes with student leadership, reminding the officers of their duty to represent over 43,000 students across the university\'s nine campuses.</p><p>“You play an important role. You represent 43,000 students—this is a big task, a big challenge,” Dr. Ibañez said. “Take care of your constituents. Provide interventions, provide solutions. Respect the authority. You cannot be a good leader if you don’t respect the hierarchy. This is how we build a vibrant, healthy, and dynamic organization.”</p><p>He also encouraged the student leaders to remain engaged with the university administration. “My office, and that of the VPAA, are always open to hear your concerns. Congratulations,” he concluded.</p><p>Vice President for Academic Affairs Dr. Mariden V. Cauilan likewise addressed the student leaders, highlighting their significant role within the institution.</p><p>“You are the heart of the operation of the university. You are the voice of your constituents. You are our allies,” she said. “Service, excellence, and teamwork, when combined, make one strong Cagayan State University.”</p><p>Also present during the event were the student council and USCF advisers, along with Dr. Lorraine S. Tattao, University Director of the Office of Student Development and Welfare (OSDW).</p><p>Following the induction, the USCF conducted its operational planning and held its first quarter meeting for the School Year 2025–2026 to align its priorities with student needs and university goals.</p>', 'The newly elected officers of the University Student Council Federation (USCF), the highest student governing body of Cagayan State University (CSU), ...', NULL, '/campus_website2/uploads/images/featured_1754626132_689578548ecd4.jpg', NULL, 'published', 0, 'post', 0, 0, 0, NULL, NULL, 'WELCOME', 'WELCOME', '2025-08-08 04:01:46', '2025-08-08 04:01:46', '2025-08-08 04:08:52'),
(17, 1, 30, 'GS Enrolment Period', 'gs-enrolment-period', '<p>The enrolment period for Graduate School programs is from August 19 to 30, 2025. The start of classes for all programs is on August 31, 2024.</p>', 'The enrolment period for Graduate School programs is from August 19 to 30, 2025. The start of classes for all programs is on August 31, 2024....', NULL, '/campus_website2/uploads/images/featured_1754638695_6895a9673fa84.png', NULL, 'published', 0, 'post', 0, 0, 0, NULL, NULL, 'GS Enrolment Period', 'The enrolment period for Graduate School programs is from August 19 to 30, 2025. The start of classes for all programs is on August 31, 2024....', '2025-08-08 07:38:15', '2025-08-08 07:38:15', '2025-08-08 08:15:49'),
(18, 1, 30, 'OWLmazing Birthday| Happy Birthday to our incredible Campus Executive Officer, Ma\'am Carla Marie L. Sumigad!', 'owlmazing-birthday-happy-birthday-to-our-incredible-campus-executive-officer-ma-am-carla-marie-l-sumigad', '<p>OWLmazing Birthday| Happy Birthday to our incredible Campus Executive Officer, <strong>Ma\'am Carla Marie L. Sumigad!</strong> Your unwavering dedication, inspiring leadership, and genuine care for the CSU Andrews community truly light up our campus.<br>We are so fortunate to have you guiding us with such grace and strength.<br>May your day be filled with as much joy and warmth as you bring to others. Sending you the warmest wishes on your special day!</p>', 'OWLmazing Birthday| Happy Birthday to our incredible Campus Executive Officer, Ma\'am Carla Marie L. Sumigad! Your unwavering dedication, inspiring lea...', NULL, '/campus_website2/uploads/images/featured_1754641628_6895b4dcd4655.jpg', NULL, 'published', 0, 'post', 0, 0, 0, NULL, NULL, 'OWLmazing Birthday| Happy Birthday to our incredible Campus Executive Officer, Ma\'am Carla Marie L. Sumigad!', 'OWLmazing Birthday| Happy Birthday to our incredible Campus Executive Officer, Ma\'am Carla Marie L. Sumigad! Your unwavering dedication, inspiring lea...', '2025-08-08 08:27:08', '2025-08-08 08:27:08', '2025-08-08 09:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `post_categories`
--

CREATE TABLE `post_categories` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_categories`
--

INSERT INTO `post_categories` (`id`, `post_id`, `category_id`, `created_at`) VALUES
(6, 12, 1, '2025-08-05 02:55:02'),
(13, 16, 1, '2025-08-08 04:08:52'),
(15, 15, 1, '2025-08-08 05:43:11'),
(16, 2, 1, '2025-08-08 06:18:20'),
(18, 17, 1, '2025-08-08 08:15:49'),
(20, 18, 32, '2025-08-08 09:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `post_tags`
--

CREATE TABLE `post_tags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_tags`
--

INSERT INTO `post_tags` (`id`, `post_id`, `tag_id`, `created_at`) VALUES
(12, 16, 31, '2025-08-08 04:08:52'),
(13, 16, 40, '2025-08-08 04:08:52'),
(15, 17, 1, '2025-08-08 08:15:49'),
(17, 18, 1, '2025-08-08 09:06:08');

--
-- Triggers `post_tags`
--
DELIMITER $$
CREATE TRIGGER `update_tag_usage_after_delete` AFTER DELETE ON `post_tags` FOR EACH ROW BEGIN
    CALL UpdateTagUsageCount(OLD.tag_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_tag_usage_after_insert` AFTER INSERT ON `post_tags` FOR EACH ROW BEGIN
    CALL UpdateTagUsageCount(NEW.tag_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `post_widgets`
--

CREATE TABLE `post_widgets` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `widget_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_widgets`
--

INSERT INTO `post_widgets` (`id`, `post_id`, `widget_id`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 16, 9, 1, 1, '2025-08-08 04:01:46'),
(2, 2, 9, 1, 1, '2025-08-08 06:18:20'),
(4, 17, 5, 1, 1, '2025-08-08 08:15:49');

-- --------------------------------------------------------

--
-- Table structure for table `post_widget_types`
--

CREATE TABLE `post_widget_types` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `widget_type_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_widget_types`
--

INSERT INTO `post_widget_types` (`id`, `post_id`, `widget_type_id`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 16, 3, 1, 1, '2025-08-08 08:48:59'),
(2, 2, 10, 1, 1, '2025-08-08 08:48:59'),
(3, 17, 9, 1, 1, '2025-08-08 08:48:59'),
(4, 15, 6, 1, 1, '2025-08-08 08:48:59'),
(5, 18, 3, 2, 1, '2025-08-08 08:48:59'),
(6, 18, 10, 2, 1, '2025-08-08 08:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `campus_id`, `setting_key`, `setting_value`, `setting_type`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 1, 'site_title', 'CSU Andrews Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-08 07:35:51'),
(2, 1, 'site_description', 'Excellence in Agriculture, Engineering, and Liberal Arts', 'string', 1, '2025-08-01 03:37:29', '2025-08-08 07:35:51'),
(3, 1, 'contact_email', 'info@andrews.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(4, 1, 'posts_per_page', '10', 'number', 0, '2025-08-01 03:37:29', '2025-08-08 07:35:51'),
(5, 1, 'allow_comments', 'true', 'boolean', 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(6, 2, 'site_title', 'CSU Aparri Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(7, 2, 'site_description', 'Marine and Fisheries Education Excellence', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(8, 2, 'contact_email', 'info@aparri.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(9, 2, 'posts_per_page', '10', 'number', 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(10, 2, 'allow_comments', 'true', 'boolean', 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(11, 3, 'site_title', 'CSU Carig Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(12, 3, 'site_description', 'Main Campus - Diverse Academic Excellence', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(13, 3, 'contact_email', 'info@carig.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(14, 3, 'posts_per_page', '10', 'number', 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(15, 3, 'allow_comments', 'true', 'boolean', 0, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(16, 4, 'site_title', 'CSU Gonzaga Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(17, 4, 'contact_email', 'info@gonzaga.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(18, 5, 'site_title', 'CSU Lallo Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(19, 5, 'contact_email', 'info@lallo.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(20, 6, 'site_title', 'CSU Lasam Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(21, 6, 'contact_email', 'info@lasam.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(22, 7, 'site_title', 'CSU Piat Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(23, 7, 'contact_email', 'info@piat.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(24, 8, 'site_title', 'CSU Sanchez Mira Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(25, 8, 'contact_email', 'info@sanchezmira.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(26, 9, 'site_title', 'CSU Solana Campus', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(27, 9, 'contact_email', 'info@solana.csu.edu.ph', 'string', 1, '2025-08-01 03:37:29', '2025-08-01 03:37:29'),
(28, 1, 'timezone', 'Asia/Manila', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(29, 1, 'facebook_url', '', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(30, 1, 'twitter_url', '', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(31, 1, 'youtube_url', '', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(32, 1, 'instagram_url', '', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(33, 1, 'enable_comments', '1', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51'),
(34, 1, 'enable_search', '1', 'string', 0, '2025-08-04 06:33:06', '2025-08-08 07:35:51');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `usage_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `campus_id`, `name`, `slug`, `description`, `color`, `usage_count`, `is_active`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Announcement', 'announcement', NULL, '#007bff', 2, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-08 09:06:08'),
(2, 2, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(3, 3, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(4, 4, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(5, 5, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(6, 6, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(7, 7, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(8, 8, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(9, 9, 'Announcement', 'announcement', NULL, '#007bff', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(16, 1, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(17, 2, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(18, 3, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(19, 4, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(20, 5, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(21, 6, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(22, 7, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(23, 8, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(24, 9, 'Important', 'important', NULL, '#dc3545', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(31, 1, 'Featured', 'featured', NULL, '#28a745', 1, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-08 04:08:52'),
(32, 2, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(33, 3, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(34, 4, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(35, 5, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(36, 6, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(37, 7, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(38, 8, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(39, 9, 'Featured', 'featured', NULL, '#28a745', 0, 1, NULL, NULL, '2025-08-04 07:19:03', '2025-08-04 07:19:03'),
(40, 1, 'WELCOME', 'welcome', NULL, '#6c757d', 1, 1, NULL, NULL, '2025-08-08 03:58:47', '2025-08-08 04:08:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('super_admin','campus_admin','editor','author','reader') NOT NULL DEFAULT 'reader',
  `avatar_url` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `campus_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `avatar_url`, `avatar_path`, `bio`, `phone`, `status`, `preferences`, `last_login`, `email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `created_at`, `updated_at`) VALUES
(1, NULL, 'superadmin', 'superadmin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Super', 'Administrator', 'super_admin', NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(2, 1, 'admin_andrews', 'admin@andrews.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Maria', 'Santos', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(3, 2, 'admin_aparri', 'admin@aparri.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Juan', 'Dela Cruz', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(4, 3, 'admin_carig', 'admin@carig.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Ana', 'Reyes', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(5, 4, 'admin_gonzaga', 'admin@gonzaga.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Carlos', 'Garcia', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(6, 5, 'admin_lallo', 'admin@lallo.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Rosa', 'Mendoza', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(7, 6, 'admin_lasam', 'admin@lasam.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Pedro', 'Aquino', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(8, 7, 'admin_piat', 'admin@piat.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Elena', 'Torres', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(9, 8, 'admin_sanchezmira', 'admin@sanchezmira.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Miguel', 'Fernandez', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(10, 9, 'admin_solana', 'admin@solana.csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Carmen', 'Villanueva', 'campus_admin', NULL, NULL, NULL, NULL, 0, '{\"dashboard_layout\": \"default\", \"notifications\": true}', NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 04:00:01'),
(11, 1, 'editor_andrews', 'editor@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa', 'Cruz', 'editor', NULL, NULL, 'Content editor specializing in academic publications and campus news.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(12, 1, 'author_andrews1', 'author1@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark', 'Silva', 'author', NULL, NULL, 'Agriculture faculty member and researcher.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(13, 1, 'author_andrews2', 'author2@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Lopez', 'author', NULL, NULL, 'Communications specialist and writer.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(14, 2, 'editor_aparri', 'editor@aparri.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Oceano', 'editor', NULL, NULL, 'Marine biology content specialist.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(15, 2, 'author_aparri1', 'author1@aparri.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marina', 'Santos', 'author', NULL, NULL, 'Fisheries research coordinator.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(16, 3, 'editor_carig', 'editor@carig.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Dela Rosa', 'editor', NULL, NULL, 'Main campus communications director.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(17, 3, 'author_carig1', 'author1@carig.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sofia', 'Marquez', 'author', NULL, NULL, 'Academic affairs coordinator.', NULL, 0, NULL, NULL, 1, NULL, NULL, NULL, '2025-08-01 03:35:16', '2025-08-01 03:35:16'),
(18, NULL, 'superadmin', 'superadmin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Super', 'Administrator', 'super_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(19, 1, 'andrews-admin', 'lopezrowbie@gmail.com', '$2y$10$TmIU2XAu8SbA7ky9SS66U.GzcTA5A820zMXZQnD9oWo5K1i8voHR.', 'Andrews', 'Administrator', 'campus_admin', 'uploads/avatars/avatar_19_1754550508.JPG', NULL, '', '', 1, NULL, '2025-08-07 05:26:49', 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-07 07:23:44'),
(20, 2, 'aparri-admin', 'aparri-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Aparri', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(21, 3, 'carig-admin', 'carig-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Carig', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(22, 4, 'gonzaga-admin', 'gonzaga-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Gonzaga', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(23, 5, 'lallo-admin', 'lallo-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Lallo', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(24, 6, 'lasam-admin', 'lasam-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Lasam', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(25, 7, 'piat-admin', 'piat-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Piat', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(26, 8, 'sanchezmira-admin', 'sanchezmira-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Sanchez Mira', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(27, 9, 'solana-admin', 'solana-admin@csu.edu.ph', '.Kqe28p.VCZ.QDhGC7.hieAYeCcCRQ4QbFq', 'Solana', 'Administrator', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, NULL, NULL, NULL, '2025-08-01 03:37:29', '2025-08-01 04:00:01'),
(30, 1, 'rowbiepogi', 'rowbielopez@csu.edu.ph', '$2y$10$WGQ1sz9TxL.eZ4O30NIwxOAE1Y4Bxg15GiSUl.UFShDDHBzGUJs4K', 'ROWBIE', 'LOPEZ', 'campus_admin', NULL, NULL, NULL, NULL, 1, NULL, '2025-08-08 01:09:39', 0, NULL, NULL, NULL, '2025-08-07 06:41:43', '2025-08-08 01:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `widgets`
--

CREATE TABLE `widgets` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `widget_type` varchar(50) NOT NULL,
  `area` varchar(50) NOT NULL,
  `content` longtext DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `widget_types`
--

CREATE TABLE `widget_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `default_template` text DEFAULT NULL,
  `config_schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config_schema`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `widget_types`
--

INSERT INTO `widget_types` (`id`, `name`, `description`, `template_path`, `default_template`, `config_schema`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Text Widget', 'Display custom text content', 'widgets/text.php', '{\"content\": \"\", \"show_title\": true}', NULL, 1, '2025-08-07 08:31:29', '2025-08-07 08:31:29'),
(2, 'Image Widget', 'Display an image with optional caption', 'widgets/image.php', '{\"image_url\": \"\", \"alt_text\": \"\", \"caption\": \"\", \"link_url\": \"\"}', NULL, 1, '2025-08-07 08:31:29', '2025-08-07 08:31:29'),
(3, 'Recent Posts Widget', 'Show recent blog posts', 'widgets/recent_posts.php', '{\"count\": 5, \"show_excerpt\": true, \"show_date\": true, \"show_author\": true, \"show_image\": true}', NULL, 1, '2025-08-07 08:31:29', '2025-08-08 08:24:26'),
(4, 'Navigation Menu', 'Display a navigation menu', 'widgets/menu.php', '{\"menu_location\": \"main\", \"show_icons\": false}', NULL, 1, '2025-08-07 08:31:29', '2025-08-07 08:31:29'),
(5, 'Contact Info', 'Display contact information', 'widgets/contact.php', '{\"phone\": \"\", \"email\": \"\", \"address\": \"\", \"show_map\": false}', NULL, 1, '2025-08-07 08:31:29', '2025-08-07 08:31:29'),
(6, 'Content Widget', 'Display assigned posts with full content', 'widgets/content.php', '{\"layout\": \"list\", \"show_excerpt\": true, \"show_image\": true, \"limit\": 5}', NULL, 1, '2025-08-08 03:13:37', '2025-08-08 03:13:37'),
(7, 'Media Widget', 'Display posts with focus on media content', 'widgets/media.php', '{\"layout\": \"grid\", \"show_excerpt\": false, \"show_image\": true, \"limit\": 6}', NULL, 1, '2025-08-08 03:13:37', '2025-08-08 03:13:37'),
(8, 'Link Widget', 'Display posts as navigation links', 'widgets/links.php', '{\"layout\": \"vertical\", \"show_excerpt\": false, \"show_image\": false, \"limit\": 10}', NULL, 1, '2025-08-08 03:13:37', '2025-08-08 03:13:37'),
(9, 'News Ticker', 'Display posts as scrolling news ticker', 'widgets/ticker.php', '{\"speed\": \"slow\", \"show_date\": true, \"limit\": 20}', NULL, 1, '2025-08-08 03:13:37', '2025-08-08 03:13:37'),
(10, 'Featured Posts', 'Display featured posts with special layout', 'widgets/featured.php', '{\"layout\": \"carousel\", \"show_excerpt\": true, \"show_image\": true, \"limit\": 3}', NULL, 1, '2025-08-08 03:13:37', '2025-08-08 03:13:37'),
(11, 'Featured Post Widget', 'Display a single post with full content, image, author and date', 'widgets/featured_post.php', '{\"show_excerpt\": true, \"show_date\": true, \"show_author\": true, \"show_image\": true, \"show_full_content\": false}', NULL, 1, '2025-08-08 06:31:37', '2025-08-08 06:57:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `subdomain` (`subdomain`),
  ADD UNIQUE KEY `domain` (`domain`),
  ADD KEY `idx_subdomain` (`subdomain`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `campus_widgets`
--
ALTER TABLE `campus_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `widget_type_id` (`widget_type_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_slug` (`campus_id`,`slug`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_categories_active` (`is_active`),
  ADD KEY `idx_categories_sort` (`sort_order`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_slug` (`campus_id`,`slug`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_organizer_id` (`organizer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_uploader_id` (`uploader_id`),
  ADD KEY `idx_mime_type` (`mime_type`),
  ADD KEY `idx_media_filename` (`filename`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_location` (`campus_id`,`location`),
  ADD KEY `idx_campus_id` (`campus_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `idx_menu_id` (`menu_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_slug` (`campus_id`,`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_author_id` (`author_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_campus_slug` (`campus_id`,`slug`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_slug` (`campus_id`,`slug`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_author_id` (`author_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_post_type` (`post_type`),
  ADD KEY `idx_published_at` (`published_at`);

--
-- Indexes for table `post_categories`
--
ALTER TABLE `post_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_category` (`post_id`,`category_id`),
  ADD KEY `idx_post_categories_post` (`post_id`),
  ADD KEY `idx_post_categories_category` (`category_id`);

--
-- Indexes for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_tag` (`post_id`,`tag_id`),
  ADD KEY `idx_post_tags_post` (`post_id`),
  ADD KEY `idx_post_tags_tag` (`tag_id`);

--
-- Indexes for table `post_widgets`
--
ALTER TABLE `post_widgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_widget` (`post_id`,`widget_id`),
  ADD KEY `widget_id` (`widget_id`);

--
-- Indexes for table `post_widget_types`
--
ALTER TABLE `post_widget_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_widget_type` (`post_id`,`widget_type_id`),
  ADD KEY `idx_post_widget_types` (`post_id`),
  ADD KEY `idx_widget_type_posts` (`widget_type_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_key` (`campus_id`,`setting_key`),
  ADD KEY `idx_campus_id` (`campus_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag_slug_campus` (`slug`,`campus_id`),
  ADD KEY `idx_tags_campus` (`campus_id`),
  ADD KEY `idx_tags_slug` (`slug`),
  ADD KEY `idx_tags_name` (`name`),
  ADD KEY `idx_tags_active` (`is_active`),
  ADD KEY `idx_tags_usage` (`usage_count`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_email` (`campus_id`,`email`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campus_id` (`campus_id`),
  ADD KEY `idx_area` (`area`);

--
-- Indexes for table `widget_types`
--
ALTER TABLE `widget_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `campus_widgets`
--
ALTER TABLE `campus_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `post_categories`
--
ALTER TABLE `post_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `post_tags`
--
ALTER TABLE `post_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `post_widgets`
--
ALTER TABLE `post_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `post_widget_types`
--
ALTER TABLE `post_widget_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `widget_types`
--
ALTER TABLE `widget_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `campus_widgets`
--
ALTER TABLE `campus_widgets`
  ADD CONSTRAINT `campus_widgets_ibfk_1` FOREIGN KEY (`widget_type_id`) REFERENCES `widget_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `media_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_3` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`),
  ADD CONSTRAINT `pages_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pages_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_categories`
--
ALTER TABLE `post_categories`
  ADD CONSTRAINT `post_categories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_widgets`
--
ALTER TABLE `post_widgets`
  ADD CONSTRAINT `post_widgets_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_widgets_ibfk_2` FOREIGN KEY (`widget_id`) REFERENCES `campus_widgets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_widget_types`
--
ALTER TABLE `post_widget_types`
  ADD CONSTRAINT `post_widget_types_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_widget_types_ibfk_2` FOREIGN KEY (`widget_type_id`) REFERENCES `widget_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`);

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `widgets`
--
ALTER TABLE `widgets`
  ADD CONSTRAINT `widgets_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
