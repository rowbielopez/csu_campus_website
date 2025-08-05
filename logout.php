<?php
/**
 * User Logout Handler
 * Destroys user session and redirects to login page
 */

require_once __DIR__ . '/core/config/session.php';
require_once __DIR__ . '/core/classes/Auth.php';
require_once __DIR__ . '/core/functions/auth.php';

// Initialize Auth class
$auth = new Auth();

// Check if user is logged in
if ($auth->isLoggedIn()) {
    // Get current user info for logging purposes
    $current_user = $auth->getCurrentUser();
    $username = $current_user['username'] ?? 'Unknown';
    
    // Perform logout
    $auth->logout();
    
    // Set success message for login page
    $message = "You have been logged out successfully. Thank you, {$username}!";
} else {
    // User wasn't logged in
    $message = "You are already logged out.";
}

// Redirect to login page with message
header('Location: login.php?message=' . urlencode($message));
exit;
?>
