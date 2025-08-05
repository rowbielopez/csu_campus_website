<?php
/**
 * Session Configuration
 * Centralized session management for consistent behavior across the application
 */

// Only configure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_lifetime', 0); // Session expires when browser closes
    ini_set('session.cookie_path', '/'); // Set path for entire domain
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.use_only_cookies', 1); // Only use cookies for session ID
    
    // Set session name
    session_name('CSU_CMS_SESSION');
    
    // Start the session
    session_start();
}
?>
