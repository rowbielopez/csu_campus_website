<?php
/**
 * Andrews Campus Configuration
 * Campus-specific settings and initialization
 */

// Load base configuration
require_once __DIR__ . '/../config/config.php';

// Define current campus
define('CURRENT_CAMPUS_ID', 1);
define('CURRENT_CAMPUS_SLUG', 'andrews');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Campus-specific settings
$campus_config = [
    'id' => 1,
    'slug' => 'andrews',
    'name' => 'Andrews Campus',
    'full_name' => 'Cagayan State University - Andrews Campus',
    'domain' => 'andrews.csu.edu.ph',
    'theme_color' => '#1e3a8a',
    'secondary_color' => '#f59e0b',
    'contact_email' => 'info@andrews.csu.edu.ph',
    'address' => 'Andrews, Cagayan Valley, Philippines',
    'seo_title' => 'CSU Andrews Campus - Excellence in Education',
    'seo_description' => 'Cagayan State University Andrews Campus offers quality education in agriculture, engineering, and liberal arts.',
    'features' => [
        'blog' => true,
        'events' => true,
        'gallery' => true,
        'news' => true,
        'announcements' => true
    ]
];

// Cache configuration
$_SESSION['campus_config'] = $campus_config;
