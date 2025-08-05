-- Migration 007: Content Taxonomy and Categorization System
-- Creates categories, tags, and pivot tables for content organization

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_categories_campus (campus_id),
    INDEX idx_categories_slug (slug),
    INDEX idx_categories_parent (parent_id),
    INDEX idx_categories_active (is_active),
    INDEX idx_categories_sort (sort_order),
    
    -- Unique constraint
    UNIQUE KEY unique_category_slug_campus (slug, campus_id),
    
    -- Foreign keys
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#6c757d', -- Hex color for tag display
    usage_count INT DEFAULT 0, -- Track how many times this tag is used
    is_active BOOLEAN DEFAULT TRUE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_tags_campus (campus_id),
    INDEX idx_tags_slug (slug),
    INDEX idx_tags_name (name),
    INDEX idx_tags_active (is_active),
    INDEX idx_tags_usage (usage_count),
    
    -- Unique constraint
    UNIQUE KEY unique_tag_slug_campus (slug, campus_id),
    
    -- Foreign keys
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE CASCADE
);

-- Post-Category pivot table (many-to-many)
CREATE TABLE IF NOT EXISTS post_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_post_categories_post (post_id),
    INDEX idx_post_categories_category (category_id),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY unique_post_category (post_id, category_id),
    
    -- Foreign keys
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Post-Tag pivot table (many-to-many)
CREATE TABLE IF NOT EXISTS post_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_post_tags_post (post_id),
    INDEX idx_post_tags_tag (tag_id),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY unique_post_tag (post_id, tag_id),
    
    -- Foreign keys
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Insert some default categories for each campus
INSERT IGNORE INTO categories (campus_id, name, slug, description, sort_order) 
SELECT 
    c.id as campus_id,
    'News' as name,
    'news' as slug,
    'Campus news and announcements' as description,
    1 as sort_order
FROM campuses c;

INSERT IGNORE INTO categories (campus_id, name, slug, description, sort_order) 
SELECT 
    c.id as campus_id,
    'Events' as name,
    'events' as slug,
    'Campus events and activities' as description,
    2 as sort_order
FROM campuses c;

INSERT IGNORE INTO categories (campus_id, name, slug, description, sort_order) 
SELECT 
    c.id as campus_id,
    'Academics' as name,
    'academics' as slug,
    'Academic programs and achievements' as description,
    3 as sort_order
FROM campuses c;

INSERT IGNORE INTO categories (campus_id, name, slug, description, sort_order) 
SELECT 
    c.id as campus_id,
    'Student Life' as name,
    'student-life' as slug,
    'Student activities and campus life' as description,
    4 as sort_order
FROM campuses c;

-- Insert some default tags for each campus
INSERT IGNORE INTO tags (campus_id, name, slug, color) 
SELECT 
    c.id as campus_id,
    'Announcement' as name,
    'announcement' as slug,
    '#007bff' as color
FROM campuses c;

INSERT IGNORE INTO tags (campus_id, name, slug, color) 
SELECT 
    c.id as campus_id,
    'Important' as name,
    'important' as slug,
    '#dc3545' as color
FROM campuses c;

INSERT IGNORE INTO tags (campus_id, name, slug, color) 
SELECT 
    c.id as campus_id,
    'Featured' as name,
    'featured' as slug,
    '#28a745' as color
FROM campuses c;

-- Create stored procedure to update tag usage counts
DELIMITER //
CREATE PROCEDURE UpdateTagUsageCount(IN tag_id_param INT)
BEGIN
    UPDATE tags 
    SET usage_count = (
        SELECT COUNT(*) 
        FROM post_tags 
        WHERE tag_id = tag_id_param
    )
    WHERE id = tag_id_param;
END //
DELIMITER ;

-- Create trigger to update tag usage count when post_tags changes
DELIMITER //
CREATE TRIGGER update_tag_usage_after_insert
AFTER INSERT ON post_tags
FOR EACH ROW
BEGIN
    CALL UpdateTagUsageCount(NEW.tag_id);
END //

CREATE TRIGGER update_tag_usage_after_delete
AFTER DELETE ON post_tags
FOR EACH ROW
BEGIN
    CALL UpdateTagUsageCount(OLD.tag_id);
END //
DELIMITER ;
