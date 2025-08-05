<?php
/**
 * Campus-Specific Configuration
 * This file should be unique for each campus deployment
 */

// Campus Configuration
define('CAMPUS_ID', 1); // Change this for each campus (1-9)
define('CAMPUS_NAME', 'Andrews Campus');
define('CAMPUS_CODE', 'andrews');
define('CAMPUS_DOMAIN', 'andrews.csu.edu.ph');

// Campus Details
define('CAMPUS_FULL_NAME', 'Cagayan State University - Andrews Campus');
define('CAMPUS_ADDRESS', 'Andrews, Cagayan Valley, Philippines');
define('CAMPUS_PHONE', '+63 XXX XXX XXXX');
define('CAMPUS_EMAIL', 'info@andrews.csu.edu.ph');

// Campus-Specific Paths
define('CAMPUS_UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CAMPUS_CACHE_PATH', __DIR__ . '/../cache/');
define('CAMPUS_LOG_PATH', __DIR__ . '/../logs/');

// Campus Theme Settings
define('CAMPUS_PRIMARY_COLOR', '#1e3a8a'); // Blue
define('CAMPUS_SECONDARY_COLOR', '#f59e0b'); // Amber
define('CAMPUS_LOGO_PATH', '/assets/img/campuses/andrews-logo.png');
define('CAMPUS_FAVICON_PATH', '/assets/img/campuses/andrews-favicon.ico');

// Campus Feature Flags
define('CAMPUS_ENABLE_BLOG', true);
define('CAMPUS_ENABLE_EVENTS', true);
define('CAMPUS_ENABLE_GALLERY', true);
define('CAMPUS_ENABLE_ANNOUNCEMENTS', true);
define('CAMPUS_ENABLE_CONTACT_FORM', true);

// Campus Timezone
define('CAMPUS_TIMEZONE', 'Asia/Manila');

// Campus Language
define('CAMPUS_LANGUAGE', 'en');
define('CAMPUS_LOCALE', 'en_PH');

// Load common configuration
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';
?>
