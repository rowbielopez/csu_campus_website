-- Media Management System Database Migration
-- Creates media table for file uploads and management

CREATE TABLE IF NOT EXISTS `media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `campus_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_url` varchar(500) NOT NULL,
    `file_type` varchar(100) NOT NULL,
    `mime_type` varchar(100) NOT NULL,
    `file_size` bigint(20) NOT NULL,
    `file_extension` varchar(10) NOT NULL,
    `alt_text` text DEFAULT NULL,
    `caption` text DEFAULT NULL,
    `description` text DEFAULT NULL,
    `is_public` tinyint(1) DEFAULT 1,
    `is_featured` tinyint(1) DEFAULT 0,
    `sort_order` int(11) DEFAULT 0,
    `download_count` int(11) DEFAULT 0,
    `metadata` longtext DEFAULT NULL COMMENT 'JSON metadata for images (dimensions, etc.)',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campus_id` (`campus_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_file_type` (`file_type`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_media_campus_type` (`campus_id`, `file_type`),
    KEY `idx_media_public_featured` (`is_public`, `is_featured`),
    KEY `idx_media_filename` (`filename`),
    CONSTRAINT `fk_media_campus` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample media files for testing
INSERT INTO `media` (`campus_id`, `user_id`, `filename`, `original_filename`, `file_path`, `file_url`, `file_type`, `mime_type`, `file_size`, `file_extension`, `alt_text`, `caption`, `is_public`, `is_featured`) VALUES
(1, 1, 'andrews_banner_20250801_001.jpg', 'campus-banner.jpg', 'uploads/andrews/andrews_banner_20250801_001.jpg', '/campus_website2/uploads/andrews/andrews_banner_20250801_001.jpg', 'image', 'image/jpeg', 245760, 'jpg', 'Andrews Campus Banner', 'Beautiful view of Andrews Campus main building', 1, 1),
(1, 1, 'research_document_20250801_002.pdf', 'research-paper.pdf', 'uploads/andrews/research_document_20250801_002.pdf', '/campus_website2/uploads/andrews/research_document_20250801_002.pdf', 'document', 'application/pdf', 1048576, 'pdf', 'Research Document', 'Latest agricultural research findings', 1, 0),
(3, 3, 'carig_welcome_20250801_003.jpg', 'welcome-image.jpg', 'uploads/carig/carig_welcome_20250801_003.jpg', '/campus_website2/uploads/carig/carig_welcome_20250801_003.jpg', 'image', 'image/jpeg', 189440, 'jpg', 'Carig Campus Welcome', 'Welcome to Carig Campus entrance', 1, 1);
