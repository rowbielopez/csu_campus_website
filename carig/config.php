<?php
/**
 * Carig Campus Configuration
 * Campus-specific settings and initialization
 */

// Load base configuration
require_once __DIR__ . '/../config/config.php';

// Define current campus
define('CURRENT_CAMPUS_ID', 3);
define('CURRENT_CAMPUS_SLUG', 'carig');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Campus-specific settings
$campus_config = [
    'id' => 3,
    'slug' => 'carig',
    'name' => 'Carig Campus',
    'full_name' => 'Cagayan State University - Carig Campus',
    'domain' => 'carig.csu.edu.ph',
    'theme_color' => '#7c3aed',
    'secondary_color' => '#f59e0b',
    'contact_email' => 'info@carig.csu.edu.ph',
    'address' => 'Carig, Tuguegarao City, Cagayan',
    'seo_title' => 'CSU Carig Campus - Main Campus',
    'seo_description' => 'The main campus of Cagayan State University offering diverse academic programs and research opportunities.',
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
