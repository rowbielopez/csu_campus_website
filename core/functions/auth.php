<?php
/**
 * Authentication Helper Functions
 * Core utilities for user authentication and authorization
 */

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// Include session configuration
require_once __DIR__ . '/../config/session.php';

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Check if the current user is a super admin
 */
function is_super_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'super_admin';
}

/**
 * Check if the current user is a campus admin
 */
function is_campus_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'campus_admin';
}

/**
 * Check if the current user is an editor
 */
function is_editor() {
    return is_logged_in() && $_SESSION['user']['role'] === 'editor';
}

/**
 * Check if the current user is an author
 */
function is_author() {
    return is_logged_in() && $_SESSION['user']['role'] === 'author';
}

/**
 * Get the current user's campus ID
 */
function current_campus_id() {
    return is_logged_in() ? $_SESSION['user']['campus_id'] : null;
}

/**
 * Get the current user's ID
 */
function current_user_id() {
    return is_logged_in() ? $_SESSION['user']['id'] : null;
}

/**
 * Get the current user's role
 */
function current_user_role() {
    return is_logged_in() ? $_SESSION['user']['role'] : null;
}

/**
 * Get the current user's full name
 */
function current_user_name() {
    if (!is_logged_in()) return null;
    return $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
}

/**
 * Get the current user's email
 */
function current_user_email() {
    return is_logged_in() ? $_SESSION['user']['email'] : null;
}

/**
 * Check if user has permission to access a specific campus
 */
function can_access_campus($campus_id) {
    if (!is_logged_in()) return false;
    
    // Super admins can access any campus
    if (is_super_admin()) return true;
    
    // Other users can only access their assigned campus
    return current_campus_id() == $campus_id;
}

/**
 * Check if user can manage content (admin or editor)
 */
function can_manage_content() {
    return is_logged_in() && in_array(current_user_role(), ['super_admin', 'campus_admin', 'editor']);
}

/**
 * Check if user can create content (admin, editor, or author)
 */
function can_create_content() {
    return is_logged_in() && in_array(current_user_role(), ['super_admin', 'campus_admin', 'editor', 'author']);
}

/**
 * Check if user can manage users
 */
function can_manage_users() {
    return is_logged_in() && in_array(current_user_role(), ['super_admin', 'campus_admin']);
}

/**
 * Redirect to login page
 */
function redirect_to_login($message = '') {
    $login_url = get_login_url();
    if ($message) {
        $login_url .= '?message=' . urlencode($message);
    }
    header("Location: $login_url");
    exit;
}

/**
 * Get the login URL for the current campus
 */
function get_login_url() {
    // Check if we're in a campus-specific directory
    $campus_code = get_current_campus_code();
    if ($campus_code) {
        return "/campus_website2/$campus_code/login.php";
    }
    return '/campus_website2/login.php';
}

/**
 * Get current campus code from URL or config
 */
function get_current_campus_code() {
    // Try to get from config if it exists
    if (defined('CAMPUS_CODE')) {
        return CAMPUS_CODE;
    }
    
    // Try to extract from current path
    $path = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($path, '/'));
    
    // Look for campus codes in the path
    $campus_codes = ['andrews', 'aparri', 'carig', 'gonzaga', 'lallo', 'lasam', 'piat', 'sanchezmira', 'solana'];
    foreach ($parts as $part) {
        if (in_array($part, $campus_codes)) {
            return $part;
        }
    }
    
    return null;
}

/**
 * Logout user and destroy session
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Generate a secure CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user display role name
 */
function get_role_display_name($role) {
    $roles = [
        'super_admin' => 'Super Administrator',
        'campus_admin' => 'Campus Administrator',
        'editor' => 'Editor',
        'author' => 'Author',
        'reader' => 'Reader'
    ];
    
    return isset($roles[$role]) ? $roles[$role] : ucfirst($role);
}

/**
 * Check if user has minimum role level
 */
function has_minimum_role($required_role) {
    if (!is_logged_in()) return false;
    
    $role_hierarchy = [
        'reader' => 1,
        'author' => 2,
        'editor' => 3,
        'campus_admin' => 4,
        'super_admin' => 5
    ];
    
    $user_level = $role_hierarchy[current_user_role()] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Get current logged in user information
 */
function get_logged_in_user() {
    if (!is_logged_in()) return null;
    return $_SESSION['user'];
}

/**
 * Get current campus information
 */
function get_current_campus() {
    if (!is_logged_in()) return null;
    
    $campus_id = current_campus_id();
    if (!$campus_id) return null;
    
    $db = Database::getInstance();
    return $db->fetch("SELECT * FROM campuses WHERE id = ?", [$campus_id]);
}

/**
 * Get campus by ID
 */
function get_campus_by_id($campus_id) {
    $db = Database::getInstance();
    return $db->fetch("SELECT * FROM campuses WHERE id = ?", [$campus_id]);
}
?>
