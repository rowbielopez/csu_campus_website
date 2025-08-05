<?php
/**
 * Super Admin Only Middleware
 * Ensures only super admins can access system-wide admin pages
 */

require_once __DIR__ . '/auth.php'; // This will check if user is logged in first

// Check if user is super admin
if (!is_super_admin()) {
    http_response_code(403);
    die('Access denied. Super administrator privileges required.');
}
?>
