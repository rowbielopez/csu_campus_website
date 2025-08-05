<?php
/**
 * Admin Only Middleware
 * Ensures only campus admins and super admins can access admin pages
 */

require_once __DIR__ . '/auth.php'; // This will check if user is logged in first

// Check if user has admin privileges
if (!can_manage_users()) {
    http_response_code(403);
    die('Access denied. Administrator privileges required.');
}

// Additional campus-specific check for campus admins
if (!is_super_admin()) {
    // Get current campus from config or URL
    $current_campus_code = get_current_campus_code();
    if ($current_campus_code) {
        // Load campus config to get campus ID
        $campus_config_path = __DIR__ . "/../../config/{$current_campus_code}.php";
        if (file_exists($campus_config_path)) {
            include $campus_config_path;
            if (defined('CAMPUS_ID') && !can_access_campus(CAMPUS_ID)) {
                http_response_code(403);
                die('Access denied. You do not have permission to manage this campus.');
            }
        }
    }
}
?>
