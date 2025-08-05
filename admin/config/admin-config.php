<?php
/**
 * Admin Configuration
 * Centralized paths and settings for admin panel
 */

// Prevent direct access
if (!defined('ADMIN_ACCESS')) {
    die('Direct access not permitted');
}

// Define admin paths - these work from any nested directory
define('ADMIN_ROOT', '/campus_website2/admin');
define('ADMIN_DIST', '/campus_website2/dist');
define('ADMIN_CORE', '/campus_website2/core');
define('ADMIN_BASE_URL', 'http://localhost/campus_website2');

// Asset paths (absolute URLs for consistency)
define('ADMIN_CSS_PATH', ADMIN_DIST . '/css');
define('ADMIN_JS_PATH', ADMIN_DIST . '/js');
define('ADMIN_IMG_PATH', ADMIN_DIST . '/assets/img');
define('ADMIN_FONTS_PATH', ADMIN_DIST . '/assets/fonts');

// Admin layout paths
define('ADMIN_LAYOUTS_PATH', dirname(__FILE__) . '/../layouts');

// Common CSS files
$admin_css_files = [
    'https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css',
    'https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css',
    ADMIN_CSS_PATH . '/styles.css'
];

// Common JS files
$admin_js_files = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js',
    'https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js',
    'https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js',
    ADMIN_JS_PATH . '/scripts.js'
];

// Icon libraries
$admin_icon_files = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js'
];

/**
 * Get the correct depth for relative paths
 * This helps determine how many ../ we need based on current file location
 */
function get_admin_path_depth() {
    $script_path = $_SERVER['SCRIPT_NAME'];
    $admin_pos = strpos($script_path, '/admin/');
    
    if ($admin_pos === false) return 0;
    
    $after_admin = substr($script_path, $admin_pos + 7); // 7 = length of '/admin/'
    return substr_count($after_admin, '/');
}

/**
 * Generate relative path prefix based on current location
 */
function get_relative_path_prefix() {
    $depth = get_admin_path_depth();
    return str_repeat('../', $depth);
}

/**
 * Generate absolute URL for admin assets
 */
function admin_asset_url($path) {
    return ADMIN_BASE_URL . $path;
}

/**
 * Generate proper asset path that works from any admin directory
 */
function admin_asset_path($asset_path) {
    // If it's already an absolute URL, return as is
    if (strpos($asset_path, 'http') === 0 || strpos($asset_path, '//') === 0) {
        return $asset_path;
    }
    
    // If it starts with /campus_website2, make it absolute URL
    if (strpos($asset_path, '/campus_website2') === 0) {
        return ADMIN_BASE_URL . str_replace('/campus_website2', '', $asset_path);
    }
    
    return $asset_path;
}
?>
