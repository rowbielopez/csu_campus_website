-- CSU CMS Platform - Database Seeding Script
-- This script populates the database with initial data for testing and development

-- =====================================================
-- PART 1: USER SEEDING
-- =====================================================

-- Insert Super Admin (Global Access)
INSERT INTO users (campus_id, username, email, password_hash, first_name, last_name, role, status, email_verified, created_at) VALUES
(NULL, 'superadmin', 'superadmin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Administrator', 'super_admin', 'active', TRUE, NOW());

-- Insert Campus Administrators (One per campus)
INSERT INTO users (campus_id, username, email, password_hash, first_name, last_name, role, status, email_verified, preferences, created_at) VALUES
(1, 'admin_andrews', 'admin@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria', 'Santos', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(2, 'admin_aparri', 'admin@aparri.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Dela Cruz', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(3, 'admin_carig', 'admin@carig.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'Reyes', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(4, 'admin_gonzaga', 'admin@gonzaga.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Garcia', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(5, 'admin_lallo', 'admin@lallo.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rosa', 'Mendoza', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(6, 'admin_lasam', 'admin@lasam.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro', 'Aquino', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(7, 'admin_piat', 'admin@piat.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Elena', 'Torres', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(8, 'admin_sanchezmira', 'admin@sanchezmira.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Miguel', 'Fernandez', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW()),
(9, 'admin_solana', 'admin@solana.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carmen', 'Villanueva', 'campus_admin', 'active', TRUE, '{"dashboard_layout": "default", "notifications": true}', NOW());

-- Insert Sample Editors and Authors per campus
INSERT INTO users (campus_id, username, email, password_hash, first_name, last_name, role, status, email_verified, bio, created_at) VALUES
-- Andrews Campus Staff
(1, 'editor_andrews', 'editor@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa', 'Cruz', 'editor', 'active', TRUE, 'Content editor specializing in academic publications and campus news.', NOW()),
(1, 'author_andrews1', 'author1@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark', 'Silva', 'author', 'active', TRUE, 'Agriculture faculty member and researcher.', NOW()),
(1, 'author_andrews2', 'author2@andrews.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Lopez', 'author', 'active', TRUE, 'Communications specialist and writer.', NOW()),

-- Aparri Campus Staff
(2, 'editor_aparri', 'editor@aparri.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Oceano', 'editor', 'active', TRUE, 'Marine biology content specialist.', NOW()),
(2, 'author_aparri1', 'author1@aparri.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marina', 'Santos', 'author', 'active', TRUE, 'Fisheries research coordinator.', NOW()),

-- Carig Campus Staff
(3, 'editor_carig', 'editor@carig.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Dela Rosa', 'editor', 'active', TRUE, 'Main campus communications director.', NOW()),
(3, 'author_carig1', 'author1@carig.csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sofia', 'Marquez', 'author', 'active', TRUE, 'Academic affairs coordinator.', NOW());

-- =====================================================
-- PART 2: CATEGORIES SEEDING
-- =====================================================

INSERT INTO categories (campus_id, name, slug, description, color, sort_order, status, created_at) VALUES
-- Andrews Campus Categories
(1, 'Campus News', 'campus-news', 'Latest news and updates from Andrews Campus', '#1e3a8a', 1, 'active', NOW()),
(1, 'Academic Programs', 'academic-programs', 'Information about academic offerings and curricula', '#059669', 2, 'active', NOW()),
(1, 'Research', 'research', 'Research projects and publications', '#7c3aed', 3, 'active', NOW()),
(1, 'Student Life', 'student-life', 'Student activities and campus life', '#dc2626', 4, 'active', NOW()),

-- Aparri Campus Categories
(2, 'Marine Research', 'marine-research', 'Marine biology and fisheries research', '#0891b2', 1, 'active', NOW()),
(2, 'Campus Updates', 'campus-updates', 'General campus news and announcements', '#059669', 2, 'active', NOW()),
(2, 'Community Outreach', 'community-outreach', 'Community engagement and extension programs', '#ea580c', 3, 'active', NOW()),

-- Carig Campus Categories
(3, 'University News', 'university-news', 'Main campus and university-wide news', '#7c3aed', 1, 'active', NOW()),
(3, 'Academic Excellence', 'academic-excellence', 'Academic achievements and recognition', '#1e3a8a', 2, 'active', NOW()),
(3, 'Innovation Hub', 'innovation-hub', 'Technology and innovation initiatives', '#059669', 3, 'active', NOW());

-- =====================================================
-- PART 3: SAMPLE POSTS/CONTENT SEEDING
-- =====================================================

INSERT INTO posts (campus_id, author_id, title, slug, body, excerpt, featured_image_url, category_id, status, post_type, is_featured, seo_title, seo_description, published_at, created_at) VALUES
-- Andrews Campus Posts
(1, 3, 'Welcome to Andrews Campus - Agricultural Excellence', 'welcome-andrews-campus', 
'<p>Welcome to the Cagayan State University Andrews Campus, where agricultural excellence meets innovation. Our campus has been at the forefront of agricultural education and research in the Cagayan Valley for over decades.</p><p>We offer comprehensive programs in agriculture, agricultural engineering, and rural development. Our state-of-the-art facilities include modern laboratories, experimental farms, and research centers dedicated to advancing sustainable farming practices.</p><p>Join us in our mission to develop skilled agricultural professionals who will lead the transformation of Philippine agriculture.</p>', 
'Discover agricultural excellence at CSU Andrews Campus with innovative programs and research facilities.', 
'/assets/img/posts/andrews-welcome.jpg', 1, 'published', 'post', TRUE,
'Welcome to CSU Andrews Campus - Agricultural Excellence', 
'Explore CSU Andrews Campus, the premier destination for agricultural education and research in Cagayan Valley.', 
NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY),

(1, 4, 'New Research Laboratory Opens for Sustainable Agriculture', 'new-research-lab-sustainable-agriculture',
'<p>CSU Andrews Campus proudly announces the opening of its new Research Laboratory for Sustainable Agriculture. This cutting-edge facility will serve as a hub for groundbreaking research in eco-friendly farming practices.</p><p>The laboratory is equipped with the latest technology for soil analysis, plant pathology studies, and crop optimization research. Faculty and students will collaborate on projects aimed at increasing crop yields while minimizing environmental impact.</p>',
'Andrews Campus opens new research laboratory dedicated to sustainable agriculture and eco-friendly farming practices.',
'/assets/img/posts/research-lab.jpg', 3, 'published', 'post', FALSE,
'New Sustainable Agriculture Research Lab Opens at Andrews Campus',
'CSU Andrews Campus launches new research facility for sustainable agriculture and eco-friendly farming innovation.',
NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),

-- Aparri Campus Posts
(2, 6, 'Marine Biology Program Receives National Recognition', 'marine-biology-national-recognition',
'<p>The Marine Biology program at CSU Aparri Campus has received national recognition for its outstanding contributions to marine research and conservation efforts in the Philippines.</p><p>Our faculty and students have been involved in critical research projects including coral reef restoration, fish population studies, and marine biodiversity conservation. This recognition validates our commitment to marine science education and research excellence.</p>',
'CSU Aparri Campus Marine Biology program receives national recognition for research excellence and conservation efforts.',
'/assets/img/posts/marine-recognition.jpg', 1, 'published', 'post', TRUE,
'CSU Aparri Marine Biology Program Receives National Recognition',
'Learn about the national recognition received by CSU Aparri Campus for excellence in marine biology research.',
NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY),

-- Carig Campus Posts  
(3, 8, 'CSU Main Campus Launches Innovation and Technology Hub', 'innovation-technology-hub-launch',
'<p>Cagayan State University Main Campus (Carig) has officially launched its Innovation and Technology Hub, marking a significant milestone in the university\'s commitment to technological advancement and innovation.</p><p>The hub will serve as a collaborative space for students, faculty, and industry partners to develop innovative solutions to real-world problems. It features modern maker spaces, 3D printing facilities, and software development laboratories.</p>',
'CSU Carig Campus launches new Innovation and Technology Hub to foster collaboration and technological advancement.',
'/assets/img/posts/innovation-hub.jpg', 3, 'published', 'post', TRUE,
'CSU Carig Campus Launches Innovation and Technology Hub',
'Discover the new Innovation and Technology Hub at CSU Carig Campus, fostering innovation and collaboration.',
NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY);

-- =====================================================
-- PART 4: MENU SYSTEM SEEDING
-- =====================================================

-- Create Main Menus for each campus
INSERT INTO menus (campus_id, name, slug, location, description, status, created_at) VALUES
(1, 'Main Navigation', 'main-nav', 'main', 'Primary navigation menu for Andrews Campus', 'active', NOW()),
(1, 'Footer Menu', 'footer-menu', 'footer', 'Footer navigation for Andrews Campus', 'active', NOW()),
(2, 'Main Navigation', 'main-nav', 'main', 'Primary navigation menu for Aparri Campus', 'active', NOW()),
(2, 'Footer Menu', 'footer-menu', 'footer', 'Footer navigation for Aparri Campus', 'active', NOW()),
(3, 'Main Navigation', 'main-nav', 'main', 'Primary navigation menu for Carig Campus', 'active', NOW()),
(3, 'Footer Menu', 'footer-menu', 'footer', 'Footer navigation for Carig Campus', 'active', NOW());

-- Create Menu Items for Andrews Campus Main Menu
INSERT INTO menu_items (menu_id, parent_id, title, url, target, sort_order, status, created_at) VALUES
(1, NULL, 'Home', '/', '_self', 1, 'active', NOW()),
(1, NULL, 'About', '/about', '_self', 2, 'active', NOW()),
(1, NULL, 'Academic Programs', '#', '_self', 3, 'active', NOW()),
(1, NULL, 'Research', '/research', '_self', 4, 'active', NOW()),
(1, NULL, 'News & Events', '/news', '_self', 5, 'active', NOW()),
(1, NULL, 'Contact', '/contact', '_self', 6, 'active', NOW());

-- Create Academic Programs submenu for Andrews
INSERT INTO menu_items (menu_id, parent_id, title, url, target, sort_order, status, created_at) VALUES
(1, 3, 'Bachelor of Science in Agriculture', '/programs/agriculture', '_self', 1, 'active', NOW()),
(1, 3, 'Agricultural Engineering', '/programs/agricultural-engineering', '_self', 2, 'active', NOW()),
(1, 3, 'Rural Development', '/programs/rural-development', '_self', 3, 'active', NOW());

-- Footer Menu Items for Andrews Campus
INSERT INTO menu_items (menu_id, parent_id, title, url, target, sort_order, status, created_at) VALUES
(2, NULL, 'Privacy Policy', '/privacy', '_self', 1, 'active', NOW()),
(2, NULL, 'Terms of Service', '/terms', '_self', 2, 'active', NOW()),
(2, NULL, 'Site Map', '/sitemap', '_self', 3, 'active', NOW());

-- =====================================================
-- PART 5: SETTINGS SEEDING
-- =====================================================

-- Default settings for each campus
INSERT INTO settings (campus_id, setting_key, setting_value, setting_type, description, is_public, created_at) VALUES
-- Andrews Campus Settings
(1, 'site_title', 'CSU Andrews Campus', 'string', 'Website title', 1, NOW()),
(1, 'site_tagline', 'Excellence in Agricultural Education', 'string', 'Website tagline', 1, NOW()),
(1, 'posts_per_page', '10', 'number', 'Number of posts per page', 0, NOW()),
(1, 'enable_comments', 'true', 'boolean', 'Enable comments on posts', 0, NOW()),
(1, 'contact_address', 'Andrews, Cagayan Valley, Philippines', 'string', 'Campus address', 1, NOW()),
(1, 'contact_phone', '+63 78 123 4567', 'string', 'Campus phone number', 1, NOW()),
(1, 'social_facebook', 'https://facebook.com/csu.andrews', 'string', 'Facebook page URL', 1, NOW()),

-- Aparri Campus Settings
(2, 'site_title', 'CSU Aparri Campus', 'string', 'Website title', 1, NOW()),
(2, 'site_tagline', 'Marine Excellence and Innovation', 'string', 'Website tagline', 1, NOW()),
(2, 'posts_per_page', '10', 'number', 'Number of posts per page', 0, NOW()),
(2, 'enable_comments', 'true', 'boolean', 'Enable comments on posts', 0, NOW()),
(2, 'contact_address', 'Aparri, Cagayan Valley, Philippines', 'string', 'Campus address', 1, NOW()),
(2, 'contact_phone', '+63 78 234 5678', 'string', 'Campus phone number', 1, NOW()),

-- Carig Campus Settings
(3, 'site_title', 'CSU Carig Campus', 'string', 'Website title', 1, NOW()),
(3, 'site_tagline', 'Leading University in Northern Luzon', 'string', 'Website tagline', 1, NOW()),
(3, 'posts_per_page', '12', 'number', 'Number of posts per page', 0, NOW()),
(3, 'enable_comments', 'true', 'boolean', 'Enable comments on posts', 0, NOW()),
(3, 'contact_address', 'Carig, Tuguegarao City, Cagayan', 'string', 'Campus address', 1, NOW()),
(3, 'contact_phone', '+63 78 345 6789', 'string', 'Campus phone number', 1, NOW());

-- =====================================================
-- PART 6: AUDIT LOGS SAMPLE DATA
-- =====================================================

INSERT INTO activity_logs (campus_id, user_id, action, table_name, record_id, ip_address, user_agent, created_at) VALUES
(1, 3, 'user_login', 'users', 3, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW() - INTERVAL 1 HOUR),
(1, 3, 'post_created', 'posts', 1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW() - INTERVAL 7 DAY),
(2, 6, 'user_login', 'users', 6, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', NOW() - INTERVAL 2 HOUR),
(2, 6, 'post_published', 'posts', 3, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', NOW() - INTERVAL 5 DAY),
(3, 8, 'user_login', 'users', 8, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', NOW() - INTERVAL 30 MINUTE),
(3, 8, 'post_created', 'posts', 4, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', NOW() - INTERVAL 2 DAY);

-- =====================================================
-- CONFIRMATION MESSAGE
-- =====================================================

SELECT 'Database seeding completed successfully!' as status,
       (SELECT COUNT(*) FROM campuses) as campuses_created,
       (SELECT COUNT(*) FROM users) as users_created,
       (SELECT COUNT(*) FROM categories) as categories_created,
       (SELECT COUNT(*) FROM posts) as posts_created,
       (SELECT COUNT(*) FROM menus) as menus_created,
       (SELECT COUNT(*) FROM menu_items) as menu_items_created,
       (SELECT COUNT(*) FROM settings) as settings_created;
