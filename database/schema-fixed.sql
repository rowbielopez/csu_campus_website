SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS widgets;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS campuses;

SET FOREIGN_KEY_CHECKS = 1;

-- Campus master table - Foundation for all tenant-specific data
CREATE TABLE campuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    subdomain VARCHAR(50) NOT NULL UNIQUE,
    domain VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    contact_email VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    favicon_url VARCHAR(255),
    theme_color VARCHAR(7) DEFAULT '#1e3a8a',
    secondary_color VARCHAR(7) DEFAULT '#f59e0b',
    timezone VARCHAR(50) DEFAULT 'Asia/Manila',
    language VARCHAR(5) DEFAULT 'en',
    locale VARCHAR(10) DEFAULT 'en_PH',
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    settings JSON,
    seo_title VARCHAR(200),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status)
);

-- Insert campus data with comprehensive information
INSERT INTO campuses (id, name, slug, code, subdomain, domain, full_name, address, contact_email, theme_color, secondary_color, seo_title, seo_description, settings) VALUES
(1, 'Andrews Campus', 'andrews', 'andrews', 'andrews', 'andrews.csu.edu.ph', 'Cagayan State University - Andrews Campus', 'Andrews, Cagayan Valley, Philippines', 'info@andrews.csu.edu.ph', '#1e3a8a', '#f59e0b', 'CSU Andrews Campus - Excellence in Education', 'Cagayan State University Andrews Campus offers quality education in agriculture, engineering, and liberal arts.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(2, 'Aparri Campus', 'aparri', 'aparri', 'aparri', 'aparri.csu.edu.ph', 'Cagayan State University - Aparri Campus', 'Aparri, Cagayan Valley, Philippines', 'info@aparri.csu.edu.ph', '#059669', '#dc2626', 'CSU Aparri Campus - Marine and Fisheries Education', 'Specializing in marine biology, fisheries, and coastal resource management programs.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(3, 'Carig Campus', 'carig', 'carig', 'carig', 'carig.csu.edu.ph', 'Cagayan State University - Carig Campus', 'Carig, Tuguegarao City, Cagayan', 'info@carig.csu.edu.ph', '#7c3aed', '#f59e0b', 'CSU Carig Campus - Main Campus', 'The main campus of Cagayan State University offering diverse academic programs and research opportunities.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(4, 'Gonzaga Campus', 'gonzaga', 'gonzaga', 'gonzaga', 'gonzaga.csu.edu.ph', 'Cagayan State University - Gonzaga Campus', 'Gonzaga, Cagayan Valley, Philippines', 'info@gonzaga.csu.edu.ph', '#dc2626', '#059669', 'CSU Gonzaga Campus - Agricultural Excellence', 'Leading agricultural education and research in crop science and sustainable farming practices.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(5, 'Lallo Campus', 'lallo', 'lallo', 'lallo', 'lallo.csu.edu.ph', 'Cagayan State University - Lallo Campus', 'Lallo, Cagayan Valley, Philippines', 'info@lallo.csu.edu.ph', '#0891b2', '#f59e0b', 'CSU Lallo Campus - Technology and Innovation', 'Focused on technology education, engineering, and innovation for rural development.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(6, 'Lasam Campus', 'lasam', 'lasam', 'lasam', 'lasam.csu.edu.ph', 'Cagayan State University - Lasam Campus', 'Lasam, Cagayan Valley, Philippines', 'info@lasam.csu.edu.ph', '#ea580c', '#059669', 'CSU Lasam Campus - Community Development', 'Dedicated to community development, social work, and public administration programs.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(7, 'Piat Campus', 'piat', 'piat', 'piat', 'piat.csu.edu.ph', 'Cagayan State University - Piat Campus', 'Piat, Cagayan Valley, Philippines', 'info@piat.csu.edu.ph', '#be185d', '#f59e0b', 'CSU Piat Campus - Arts and Sciences', 'Excellence in liberal arts, sciences, and teacher education programs.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(8, 'Sanchez Mira Campus', 'sanchezmira', 'sanchezmira', 'sanchezmira', 'sanchezmira.csu.edu.ph', 'Cagayan State University - Sanchez Mira Campus', 'Sanchez Mira, Cagayan Valley, Philippines', 'info@sanchezmira.csu.edu.ph', '#059669', '#ea580c', 'CSU Sanchez Mira Campus - Rural Development', 'Specializing in rural development, agriculture extension, and community outreach programs.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}'),
(9, 'Solana Campus', 'solana', 'solana', 'solana', 'solana.csu.edu.ph', 'Cagayan State University - Solana Campus', 'Solana, Cagayan Valley, Philippines', 'info@solana.csu.edu.ph', '#7c2d12', '#f59e0b', 'CSU Solana Campus - Environmental Studies', 'Leading environmental science, forestry, and natural resource management education.', '{"enable_blog": true, "enable_events": true, "enable_gallery": true}');

-- Users table with enhanced role system
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NULL, -- NULL for super_admin (global access)
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('super_admin', 'campus_admin', 'editor', 'author', 'reader') NOT NULL DEFAULT 'reader',
    avatar_path VARCHAR(255),
    bio TEXT,
    phone VARCHAR(20),
    status TINYINT(1) DEFAULT 1,
    preferences JSON,
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    INDEX idx_campus_id (campus_id),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    UNIQUE KEY unique_campus_email (campus_id, email)
);

-- Categories table - must be created before posts
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    color VARCHAR(7) DEFAULT '#1e3a8a',
    sort_order INT DEFAULT 0,
    post_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_campus_id (campus_id),
    INDEX idx_parent_id (parent_id),
    UNIQUE KEY unique_campus_slug (campus_id, slug)
);

-- Pages table
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    parent_id INT NULL,
    template VARCHAR(50) DEFAULT 'default',
    status ENUM('draft', 'published', 'private') DEFAULT 'draft',
    is_homepage BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    seo_title VARCHAR(200),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES pages(id),
    INDEX idx_campus_id (campus_id),
    INDEX idx_author_id (author_id),
    INDEX idx_status (status),
    INDEX idx_campus_slug (campus_id, slug),
    UNIQUE KEY unique_campus_slug (campus_id, slug)
);

-- Posts table with enhanced content management
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    body LONGTEXT,
    excerpt TEXT,
    featured_image_url VARCHAR(255),
    category_id INT,
    status ENUM('draft', 'pending', 'published', 'archived') DEFAULT 'draft',
    post_type ENUM('post', 'page', 'announcement') DEFAULT 'post',
    is_featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    seo_title VARCHAR(200),
    seo_description TEXT,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_campus_id (campus_id),
    INDEX idx_author_id (author_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_post_type (post_type),
    INDEX idx_published_at (published_at),
    UNIQUE KEY unique_campus_slug (campus_id, slug)
);

-- Menus table
CREATE TABLE menus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    INDEX idx_campus_id (campus_id),
    UNIQUE KEY unique_campus_location (campus_id, location)
);

-- Menu items table
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    menu_id INT NOT NULL,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    page_id INT NULL,
    icon VARCHAR(50),
    css_class VARCHAR(100),
    sort_order INT DEFAULT 0,
    target VARCHAR(10) DEFAULT '_self',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE SET NULL,
    INDEX idx_menu_id (menu_id),
    INDEX idx_parent_id (parent_id)
);

-- Continue with remaining tables...
CREATE TABLE widgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    widget_type VARCHAR(50) NOT NULL,
    area VARCHAR(50) NOT NULL,
    content LONGTEXT,
    settings JSON,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_id (campus_id),
    INDEX idx_area (area)
);

CREATE TABLE media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    uploader_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    width INT NULL,
    height INT NULL,
    alt_text VARCHAR(255),
    caption TEXT,
    description TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (uploader_id) REFERENCES users(id),
    INDEX idx_campus_id (campus_id),
    INDEX idx_uploader_id (uploader_id),
    INDEX idx_mime_type (mime_type)
);

CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_id (campus_id),
    UNIQUE KEY unique_campus_key (campus_id, setting_key)
);

CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_campus_id (campus_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    campus_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_campus_id (campus_id),
    INDEX idx_last_activity (last_activity)
);

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    post_id INT NOT NULL,
    parent_id INT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    author_website VARCHAR(255),
    author_ip VARCHAR(45),
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_campus_id (campus_id),
    INDEX idx_post_id (post_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_status (status)
);

CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    organizer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description LONGTEXT,
    short_description TEXT,
    location VARCHAR(255),
    venue_details TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    is_all_day BOOLEAN DEFAULT FALSE,
    registration_required BOOLEAN DEFAULT FALSE,
    max_attendees INT NULL,
    current_attendees INT DEFAULT 0,
    registration_deadline DATETIME NULL,
    featured_image_url VARCHAR(255),
    status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (organizer_id) REFERENCES users(id),
    INDEX idx_campus_id (campus_id),
    INDEX idx_organizer_id (organizer_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date),
    UNIQUE KEY unique_campus_slug (campus_id, slug)
);
