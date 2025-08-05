<?php
// Carig Campus Configuration
define('CAMPUS_ID', 3);
define('CAMPUS_NAME', 'Carig Campus');
define('CAMPUS_CODE', 'carig');
define('CAMPUS_DOMAIN', 'carig.csu.edu.ph');
define('CAMPUS_FULL_NAME', 'Cagayan State University - Carig Campus');
define('CAMPUS_ADDRESS', 'Carig, Tuguegarao City, Cagayan');
define('CAMPUS_PHONE', '+63 XXX XXX XXXX');
define('CAMPUS_EMAIL', 'info@carig.csu.edu.ph');
define('CAMPUS_UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CAMPUS_CACHE_PATH', __DIR__ . '/../cache/');
define('CAMPUS_LOG_PATH', __DIR__ . '/../logs/');
define('CAMPUS_PRIMARY_COLOR', '#7c3aed');
define('CAMPUS_SECONDARY_COLOR', '#f59e0b');
define('CAMPUS_LOGO_PATH', '/assets/img/campuses/carig-logo.png');
define('CAMPUS_FAVICON_PATH', '/assets/img/campuses/carig-favicon.ico');
define('CAMPUS_ENABLE_BLOG', true);
define('CAMPUS_ENABLE_EVENTS', true);
define('CAMPUS_ENABLE_GALLERY', true);
define('CAMPUS_ENABLE_ANNOUNCEMENTS', true);
define('CAMPUS_ENABLE_CONTACT_FORM', true);
define('CAMPUS_TIMEZONE', 'Asia/Manila');
define('CAMPUS_LANGUAGE', 'en');
define('CAMPUS_LOCALE', 'en_PH');
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';
?>
