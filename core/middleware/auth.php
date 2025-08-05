<?php
/**
 * Authentication Middleware
 * Ensures user is logged in before accessing protected pages
 */

require_once __DIR__ . '/../functions/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_to_login('Please log in to access this page.');
}

// Optional: Check session timeout (24 hours)
if (isset($_SESSION['user']['login_time'])) {
    $session_timeout = 24 * 60 * 60; // 24 hours in seconds
    if (time() - $_SESSION['user']['login_time'] > $session_timeout) {
        logout_user();
        redirect_to_login('Your session has expired. Please log in again.');
    }
}
?>
