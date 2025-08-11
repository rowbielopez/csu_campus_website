<?php
/**
 * System Constants
 * Shared across all campuses
 */

// Application Information
define('APP_NAME', 'CSU CMS Platform');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'CSU IT Department');

// System Paths
define('ROOT_PATH', dirname(__DIR__));
define('CORE_PATH', ROOT_PATH . '/core/');
define('ADMIN_PATH', ROOT_PATH . '/admin/');
define('PUBLIC_PATH', ROOT_PATH . '/public/');
define('ASSETS_PATH', ROOT_PATH . '/assets/');
define('VENDOR_PATH', ROOT_PATH . '/vendor/');

// URL Configuration
define('BASE_URL', 'https://' . CAMPUS_DOMAIN);
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');

// Security Settings
define('SESSION_NAME', 'CSU_CMS_' . CAMPUS_CODE);
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// File Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

// Pagination Settings
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Email Settings (only define if not already defined by email.php)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_ENCRYPTION')) define('SMTP_ENCRYPTION', 'tls');
if (!defined('FROM_EMAIL')) define('FROM_EMAIL', CAMPUS_EMAIL);
if (!defined('FROM_NAME')) define('FROM_NAME', CAMPUS_FULL_NAME);

// User Roles (Updated to match ENUM values)
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_CAMPUS_ADMIN', 'campus_admin');
define('ROLE_EDITOR', 'editor');
define('ROLE_AUTHOR', 'author');
define('ROLE_READER', 'reader');

// Content Status (Updated to match ENUM values)
define('STATUS_DRAFT', 'draft');
define('STATUS_PENDING', 'pending');
define('STATUS_PUBLISHED', 'published');
define('STATUS_ARCHIVED', 'archived');

// Post Types
define('POST_TYPE_POST', 'post');
define('POST_TYPE_PAGE', 'page');
define('POST_TYPE_ANNOUNCEMENT', 'announcement');

// Widget Types
define('WIDGET_TEXT', 'text');
define('WIDGET_HTML', 'html');
define('WIDGET_IMAGE', 'image');
define('WIDGET_GALLERY', 'gallery');
define('WIDGET_RECENT_POSTS', 'recent_posts');
define('WIDGET_MENU', 'menu');

// Menu Locations (Updated to match ENUM values)
define('MENU_MAIN', 'main');
define('MENU_FOOTER', 'footer');
define('MENU_SIDEBAR', 'sidebar');
define('MENU_MOBILE', 'mobile');

// Error Logging
define('LOG_ERRORS', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Debug Settings
define('DEBUG_MODE', false);
define('SHOW_ERRORS', DEBUG_MODE);
define('WIDGET_DEBUG', DEBUG_MODE); // Widget debugging mode

// Campus IDs Mapping
$CAMPUS_MAPPING = [
    1 => 'andrews',
    2 => 'aparri',
    3 => 'carig',
    4 => 'gonzaga',
    5 => 'lallo',
    6 => 'lasam',
    7 => 'piat',
    8 => 'sanchezmira',
    9 => 'solana'
];

define('CAMPUS_MAPPING', $CAMPUS_MAPPING);

// Utility Functions
function getCampusName($campus_id) {
    $names = [
        1 => 'Andrews Campus',
        2 => 'Aparri Campus',
        3 => 'Carig Campus',
        4 => 'Gonzaga Campus',
        5 => 'Lallo Campus',
        6 => 'Lasam Campus',
        7 => 'Piat Campus',
        8 => 'Sanchez Mira Campus',
        9 => 'Solana Campus'
    ];
    return $names[$campus_id] ?? 'Unknown Campus';
}

function getCampusDomain($campus_id) {
    $code = CAMPUS_MAPPING[$campus_id] ?? null;
    return $code ? "{$code}.csu.edu.ph" : null;
}

function getRoleName($role) {
    $roles = [
        ROLE_SUPER_ADMIN => 'Super Administrator',
        ROLE_CAMPUS_ADMIN => 'Campus Administrator',
        ROLE_EDITOR => 'Editor',
        ROLE_AUTHOR => 'Author',
        ROLE_READER => 'Reader'
    ];
    return $roles[$role] ?? 'Unknown Role';
}

function getStatusName($status) {
    $statuses = [
        STATUS_DRAFT => 'Draft',
        STATUS_PENDING => 'Pending Review',
        STATUS_PUBLISHED => 'Published',
        STATUS_ARCHIVED => 'Archived'
    ];
    return $statuses[$status] ?? 'Unknown Status';
}

function getStatusBadgeClass($status) {
    $classes = [
        STATUS_DRAFT => 'secondary',
        STATUS_PENDING => 'warning',
        STATUS_PUBLISHED => 'success',
        STATUS_ARCHIVED => 'info'
    ];
    return $classes[$status] ?? 'secondary';
}

// Set timezone
date_default_timezone_set(CAMPUS_TIMEZONE);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?>
