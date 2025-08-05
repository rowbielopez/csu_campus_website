-- CSU CMS Platform Database Schema
-- Multi-tenant architecture with campus_id scoping

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
    avatar_url VARCHAR(255),
    bio TEXT,
    phone VARCHAR(20),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    INDEX idx_campus_users (campus_id, username),
    INDEX idx_campus_email (campus_id, email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    UNIQUE KEY unique_global_username (username),
    UNIQUE KEY unique_global_email (email)
);

-- Pages table
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    template VARCHAR(50) DEFAULT 'default',
    meta_title VARCHAR(200),
    meta_description TEXT,
    meta_keywords TEXT,
    status TINYINT DEFAULT 0,
    sort_order INT DEFAULT 0,
    parent_id INT NULL,
    author_id INT NOT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES pages(id),
    INDEX idx_campus_pages (campus_id, status),
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
    seo_keywords TEXT,
    template VARCHAR(50) DEFAULT 'default',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_campus_posts (campus_id, status),
    INDEX idx_campus_slug (campus_id, slug),
    INDEX idx_published (campus_id, published_at),
    INDEX idx_featured (campus_id, is_featured),
    INDEX idx_type (campus_id, post_type),
    UNIQUE KEY unique_campus_slug (campus_id, slug)
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6B7280',
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_categories (campus_id, status),
    UNIQUE KEY unique_campus_category_slug (campus_id, slug)
);

-- Menus table for navigation management
CREATE TABLE menus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    location ENUM('main', 'footer', 'sidebar', 'mobile') NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    INDEX idx_campus_menus (campus_id, location),
    UNIQUE KEY unique_campus_menu_location (campus_id, location)
);

-- Menu items table for hierarchical menu structure
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    menu_id INT NOT NULL,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    target ENUM('_self', '_blank') DEFAULT '_self',
    css_class VARCHAR(100),
    icon VARCHAR(50),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_menu_items (menu_id, parent_id, sort_order),
    INDEX idx_status (status)
);

-- Widgets table
CREATE TABLE widgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    location VARCHAR(50) NOT NULL,
    title VARCHAR(200),
    content LONGTEXT,
    settings JSON,
    sort_order INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_widgets (campus_id, location, status)
);

-- Media library table
CREATE TABLE media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('image', 'document', 'video', 'audio', 'other') NOT NULL,
    alt_text VARCHAR(255),
    caption TEXT,
    description TEXT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_campus_media (campus_id, file_type),
    INDEX idx_upload_date (campus_id, created_at)
);

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_settings (campus_id, setting_key),
    UNIQUE KEY unique_campus_setting (campus_id, setting_key)
);

-- Activity log table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_campus_activity (campus_id, created_at),
    INDEX idx_user_activity (user_id, created_at)
);

-- Sessions table for better session management
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    campus_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_campus_sessions (campus_id, last_activity),
    INDEX idx_user_sessions (user_id, last_activity)
);

-- Comments table
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    post_id INT NOT NULL,
    parent_id INT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    author_website VARCHAR(200),
    content TEXT NOT NULL,
    ip_address VARCHAR(45),
    status TINYINT DEFAULT 0, -- 0: pending, 1: approved, 2: spam, 3: deleted
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (parent_id) REFERENCES comments(id),
    INDEX idx_campus_comments (campus_id, post_id, status),
    INDEX idx_comment_tree (post_id, parent_id)
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description LONGTEXT,
    short_description TEXT,
    featured_image VARCHAR(255),
    location VARCHAR(200),
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    is_all_day TINYINT DEFAULT 0,
    registration_required TINYINT DEFAULT 0,
    max_attendees INT NULL,
    current_attendees INT DEFAULT 0,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    status TINYINT DEFAULT 0,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    FOREIGN KEY (author_id) REFERENCES users(id),
    INDEX idx_campus_events (campus_id, start_date),
    INDEX idx_campus_event_slug (campus_id, slug),
    UNIQUE KEY unique_campus_event_slug (campus_id, slug)
);

-- Insert default settings for each campus
INSERT INTO settings (campus_id, setting_key, setting_value, setting_type, description, is_public) 
SELECT 
    id as campus_id,
    'site_title' as setting_key,
    full_name as setting_value,
    'string' as setting_type,
    'Website title' as description,
    1 as is_public
FROM campuses;

INSERT INTO settings (campus_id, setting_key, setting_value, setting_type, description, is_public)
SELECT 
    id as campus_id,
    'site_tagline' as setting_key,
    'Excellence in Education' as setting_value,
    'string' as setting_type,
    'Website tagline' as description,
    1 as is_public
FROM campuses;

INSERT INTO settings (campus_id, setting_key, setting_value, setting_type, description, is_public)
SELECT 
    id as campus_id,
    'posts_per_page' as setting_key,
    '10' as setting_value,
    'number' as setting_type,
    'Number of posts per page' as description,
    0 as is_public
FROM campuses;
