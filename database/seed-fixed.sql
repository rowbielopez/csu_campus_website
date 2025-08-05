-- CSU CMS Platform Database Seed Data
-- Populates the database with initial data for testing and development

-- =====================================================
-- PART 1: SUPER ADMIN AND CAMPUS ADMINISTRATORS
-- =====================================================

-- Super Admin (Global Access)
INSERT INTO users (campus_id, username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
(NULL, 'superadmin', 'superadmin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Administrator', 'super_admin', 1, NOW());

-- Campus Administrators for each campus
INSERT INTO users (campus_id, username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
-- Andrews Campus Admin
(1, 'andrews-admin', 'andrews-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andrews', 'Administrator', 'campus_admin', 1, NOW()),

-- Aparri Campus Admin  
(2, 'aparri-admin', 'aparri-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aparri', 'Administrator', 'campus_admin', 1, NOW()),

-- Carig Campus Admin
(3, 'carig-admin', 'carig-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carig', 'Administrator', 'campus_admin', 1, NOW()),

-- Gonzaga Campus Admin
(4, 'gonzaga-admin', 'gonzaga-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gonzaga', 'Administrator', 'campus_admin', 1, NOW()),

-- Lallo Campus Admin
(5, 'lallo-admin', 'lallo-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lallo', 'Administrator', 'campus_admin', 1, NOW()),

-- Lasam Campus Admin
(6, 'lasam-admin', 'lasam-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lasam', 'Administrator', 'campus_admin', 1, NOW()),

-- Piat Campus Admin
(7, 'piat-admin', 'piat-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Piat', 'Administrator', 'campus_admin', 1, NOW()),

-- Sanchez Mira Campus Admin
(8, 'sanchezmira-admin', 'sanchezmira-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sanchez Mira', 'Administrator', 'campus_admin', 1, NOW()),

-- Solana Campus Admin
(9, 'solana-admin', 'solana-admin@csu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Solana', 'Administrator', 'campus_admin', 1, NOW());

-- =====================================================
-- PART 2: CATEGORIES SEEDING
-- =====================================================

INSERT INTO categories (campus_id, name, slug, description, color, sort_order, created_at) VALUES
-- Andrews Campus Categories
(1, 'Campus News', 'campus-news', 'Latest news and updates from Andrews Campus', '#1e3a8a', 1, NOW()),
(1, 'Academic Programs', 'academic-programs', 'Information about academic offerings and curricula', '#059669', 2, NOW()),
(1, 'Research', 'research', 'Research projects and publications', '#7c3aed', 3, NOW()),
(1, 'Student Life', 'student-life', 'Student activities and campus life', '#dc2626', 4, NOW()),

-- Aparri Campus Categories
(2, 'Marine Biology News', 'marine-biology-news', 'Marine and fisheries education updates', '#059669', 1, NOW()),
(2, 'Fisheries Programs', 'fisheries-programs', 'Specialized fisheries and aquaculture programs', '#0891b2', 2, NOW()),
(2, 'Coastal Research', 'coastal-research', 'Coastal resource management research', '#7c3aed', 3, NOW()),

-- Carig Campus Categories  
(3, 'Main Campus News', 'main-campus-news', 'News from the main campus', '#7c3aed', 1, NOW()),
(3, 'University Updates', 'university-updates', 'University-wide announcements and updates', '#dc2626', 2, NOW()),
(3, 'Academic Excellence', 'academic-excellence', 'Academic achievements and programs', '#059669', 3, NOW()),

-- Add categories for remaining campuses (shortened for brevity)
(4, 'Agricultural News', 'agricultural-news', 'Latest in agricultural education', '#dc2626', 1, NOW()),
(5, 'Technology Updates', 'technology-updates', 'Technology and innovation news', '#0891b2', 1, NOW()),
(6, 'Community Development', 'community-development', 'Community outreach and development', '#ea580c', 1, NOW()),
(7, 'Arts and Sciences', 'arts-sciences', 'Liberal arts and sciences news', '#be185d', 1, NOW()),
(8, 'Rural Development', 'rural-development', 'Rural development initiatives', '#059669', 1, NOW()),
(9, 'Environmental Studies', 'environmental-studies', 'Environmental science and forestry', '#7c2d12', 1, NOW());

-- =====================================================
-- PART 3: SAMPLE POSTS FOR EACH CAMPUS
-- =====================================================

INSERT INTO posts (campus_id, author_id, title, slug, body, excerpt, category_id, status, post_type, is_featured, published_at, created_at) VALUES

-- Andrews Campus Posts
(1, 2, 'Welcome to Andrews Campus', 'welcome-andrews-campus', 
'<p>Welcome to Cagayan State University - Andrews Campus! We are excited to share our commitment to excellence in education, particularly in agriculture, engineering, and liberal arts.</p>
<p>Our campus provides a nurturing environment for students to grow academically and personally. With state-of-the-art facilities and dedicated faculty, we ensure that every student receives quality education.</p>
<p>Join us in our journey toward academic excellence and community development.</p>', 
'Welcome message from Andrews Campus highlighting our commitment to excellence in education.', 
1, 'published', 'post', TRUE, NOW(), NOW()),

(1, 2, 'New Agricultural Research Center Opens', 'agricultural-research-center-opens',
'<p>Andrews Campus is proud to announce the opening of our new Agricultural Research Center. This state-of-the-art facility will enhance our research capabilities in sustainable farming and crop development.</p>
<p>The center features modern laboratories, greenhouse facilities, and field testing areas. Students and faculty will work together on innovative agricultural solutions for the region.</p>', 
'Andrews Campus opens new Agricultural Research Center to advance farming innovation.', 
3, 'published', 'post', FALSE, NOW(), NOW()),

-- Aparri Campus Posts
(2, 3, 'Marine Science Program Expansion', 'marine-science-program-expansion',
'<p>Aparri Campus announces the expansion of our Marine Science program with new courses in marine conservation and sustainable fisheries management.</p>
<p>Our coastal location provides unique opportunities for hands-on learning in marine environments. Students will have access to research vessels and marine laboratories.</p>', 
'Aparri Campus expands Marine Science program with new conservation courses.', 
1, 'published', 'post', TRUE, NOW(), NOW()),

-- Carig Campus Posts
(3, 4, 'University-wide Collaboration Initiative', 'university-collaboration-initiative',
'<p>Carig Campus, as the main campus, is launching a new collaboration initiative to strengthen partnerships between all CSU campuses.</p>
<p>This initiative will facilitate resource sharing, joint research projects, and student exchange programs across all nine campuses.</p>', 
'Main campus launches initiative to strengthen inter-campus collaboration.', 
2, 'published', 'post', TRUE, NOW(), NOW()),

-- Additional sample posts for other campuses
(4, 5, 'Sustainable Agriculture Practices Workshop', 'sustainable-agriculture-workshop',
'<p>Gonzaga Campus will host a workshop on sustainable agriculture practices for local farmers and students.</p>', 
'Workshop on sustainable agriculture at Gonzaga Campus.', 
4, 'published', 'post', FALSE, NOW(), NOW()),

(5, 6, 'Technology Innovation Hub Launch', 'technology-innovation-hub',
'<p>Lallo Campus introduces a new Technology Innovation Hub to support student entrepreneurs and tech startups.</p>', 
'New Technology Innovation Hub at Lallo Campus.', 
5, 'published', 'post', FALSE, NOW(), NOW()),

(6, 7, 'Community Outreach Program Success', 'community-outreach-success',
'<p>Lasam Campus celebrates the success of its community development outreach programs in surrounding municipalities.</p>', 
'Successful community outreach programs at Lasam Campus.', 
6, 'published', 'post', FALSE, NOW(), NOW()),

(7, 8, 'Teacher Education Excellence Award', 'teacher-education-excellence',
'<p>Piat Campus receives recognition for excellence in teacher education programs.</p>', 
'Piat Campus recognized for teacher education excellence.', 
7, 'published', 'post', FALSE, NOW(), NOW()),

(8, 9, 'Rural Development Research Grant', 'rural-development-grant',
'<p>Sanchez Mira Campus receives major research grant for rural development initiatives.</p>', 
'Research grant awarded to Sanchez Mira Campus.', 
8, 'published', 'post', FALSE, NOW(), NOW()),

(9, 10, 'Environmental Conservation Project', 'environmental-conservation-project',
'<p>Solana Campus launches major environmental conservation project in partnership with local communities.</p>', 
'Environmental conservation project at Solana Campus.', 
9, 'published', 'post', FALSE, NOW(), NOW());

-- =====================================================
-- PART 4: BASIC SETTINGS FOR EACH CAMPUS
-- =====================================================

INSERT INTO settings (campus_id, setting_key, setting_value, setting_type, is_public, created_at) VALUES
-- Global settings for all campuses
(1, 'site_title', 'CSU Andrews Campus', 'string', TRUE, NOW()),
(1, 'site_description', 'Excellence in Agriculture, Engineering, and Liberal Arts', 'string', TRUE, NOW()),
(1, 'contact_email', 'info@andrews.csu.edu.ph', 'string', TRUE, NOW()),
(1, 'posts_per_page', '10', 'number', FALSE, NOW()),
(1, 'allow_comments', 'true', 'boolean', FALSE, NOW()),

(2, 'site_title', 'CSU Aparri Campus', 'string', TRUE, NOW()),
(2, 'site_description', 'Marine and Fisheries Education Excellence', 'string', TRUE, NOW()),
(2, 'contact_email', 'info@aparri.csu.edu.ph', 'string', TRUE, NOW()),
(2, 'posts_per_page', '10', 'number', FALSE, NOW()),
(2, 'allow_comments', 'true', 'boolean', FALSE, NOW()),

(3, 'site_title', 'CSU Carig Campus', 'string', TRUE, NOW()),
(3, 'site_description', 'Main Campus - Diverse Academic Excellence', 'string', TRUE, NOW()),
(3, 'contact_email', 'info@carig.csu.edu.ph', 'string', TRUE, NOW()),
(3, 'posts_per_page', '10', 'number', FALSE, NOW()),
(3, 'allow_comments', 'true', 'boolean', FALSE, NOW()),

-- Additional settings for remaining campuses
(4, 'site_title', 'CSU Gonzaga Campus', 'string', TRUE, NOW()),
(4, 'contact_email', 'info@gonzaga.csu.edu.ph', 'string', TRUE, NOW()),
(5, 'site_title', 'CSU Lallo Campus', 'string', TRUE, NOW()),
(5, 'contact_email', 'info@lallo.csu.edu.ph', 'string', TRUE, NOW()),
(6, 'site_title', 'CSU Lasam Campus', 'string', TRUE, NOW()),
(6, 'contact_email', 'info@lasam.csu.edu.ph', 'string', TRUE, NOW()),
(7, 'site_title', 'CSU Piat Campus', 'string', TRUE, NOW()),
(7, 'contact_email', 'info@piat.csu.edu.ph', 'string', TRUE, NOW()),
(8, 'site_title', 'CSU Sanchez Mira Campus', 'string', TRUE, NOW()),
(8, 'contact_email', 'info@sanchezmira.csu.edu.ph', 'string', TRUE, NOW()),
(9, 'site_title', 'CSU Solana Campus', 'string', TRUE, NOW()),
(9, 'contact_email', 'info@solana.csu.edu.ph', 'string', TRUE, NOW());

-- =====================================================
-- PART 5: SAMPLE PAGES FOR EACH CAMPUS
-- =====================================================

INSERT INTO pages (campus_id, author_id, title, slug, content, excerpt, status, is_homepage, created_at) VALUES
-- Andrews Campus Pages
(1, 2, 'About Andrews Campus', 'about', 
'<h2>About CSU Andrews Campus</h2>
<p>Cagayan State University - Andrews Campus has been serving the educational needs of the Cagayan Valley region for decades. We specialize in agriculture, engineering, and liberal arts programs.</p>
<h3>Our Mission</h3>
<p>To provide quality education that prepares students for successful careers and meaningful contributions to society.</p>
<h3>Academic Programs</h3>
<ul>
<li>Bachelor of Science in Agriculture</li>
<li>Bachelor of Science in Engineering</li>
<li>Bachelor of Arts in Liberal Arts</li>
<li>Graduate Programs</li>
</ul>', 
'Learn about Andrews Campus mission, programs, and commitment to excellence.', 
'published', FALSE, NOW()),

-- Homepage for Andrews Campus
(1, 2, 'Welcome to Andrews Campus', 'home', 
'<h1>Welcome to CSU Andrews Campus</h1>
<p>Excellence in Agriculture, Engineering, and Liberal Arts</p>
<p>Discover opportunities for academic growth and personal development at our beautiful campus.</p>', 
'Welcome to Andrews Campus homepage.', 
'published', TRUE, NOW()),

-- Sample pages for other campuses (abbreviated)
(2, 3, 'About Aparri Campus', 'about', '<h2>About CSU Aparri Campus</h2><p>Specializing in marine and fisheries education.</p>', 'About Aparri Campus', 'published', FALSE, NOW()),
(3, 4, 'About Carig Campus', 'about', '<h2>About CSU Carig Campus</h2><p>The main campus offering diverse academic programs.</p>', 'About Carig Campus', 'published', FALSE, NOW()),
(4, 5, 'About Gonzaga Campus', 'about', '<h2>About CSU Gonzaga Campus</h2><p>Agricultural excellence and research.</p>', 'About Gonzaga Campus', 'published', FALSE, NOW()),
(5, 6, 'About Lallo Campus', 'about', '<h2>About CSU Lallo Campus</h2><p>Technology and innovation focus.</p>', 'About Lallo Campus', 'published', FALSE, NOW()),
(6, 7, 'About Lasam Campus', 'about', '<h2>About CSU Lasam Campus</h2><p>Community development and social work.</p>', 'About Lasam Campus', 'published', FALSE, NOW()),
(7, 8, 'About Piat Campus', 'about', '<h2>About CSU Piat Campus</h2><p>Arts, sciences, and teacher education.</p>', 'About Piat Campus', 'published', FALSE, NOW()),
(8, 9, 'About Sanchez Mira Campus', 'about', '<h2>About CSU Sanchez Mira Campus</h2><p>Rural development and agriculture extension.</p>', 'About Sanchez Mira Campus', 'published', FALSE, NOW()),
(9, 10, 'About Solana Campus', 'about', '<h2>About CSU Solana Campus</h2><p>Environmental science and forestry programs.</p>', 'About Solana Campus', 'published', FALSE, NOW());
