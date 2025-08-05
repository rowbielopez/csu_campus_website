<?php
/**
 * Application Bootstrap
 * Initialize the CSU CMS Platform
 */

// Start output buffering
ob_start();

// Error reporting (disable in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $error = [
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'severity' => $severity,
        'time' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if (defined('LOG_ERRORS') && LOG_ERRORS && defined('CAMPUS_LOG_PATH')) {
        error_log(json_encode($error), 3, CAMPUS_LOG_PATH . 'errors.log');
    }
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border:1px solid #f5c6cb;'>";
        echo "<strong>Error:</strong> {$message} in <strong>{$file}</strong> on line <strong>{$line}</strong>";
        echo "</div>";
    }
    
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    $error = [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'time' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if (defined('LOG_ERRORS') && LOG_ERRORS && defined('CAMPUS_LOG_PATH')) {
        error_log(json_encode($error), 3, CAMPUS_LOG_PATH . 'exceptions.log');
    }
    
    // Always show debug info for now
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;margin:10px;border:1px solid #f5c6cb;'>";
    echo "<h3>Uncaught Exception</h3>";
    echo "<strong>Message:</strong> " . $exception->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
});

// Load core classes
require_once CORE_PATH . 'classes/Database.php';
require_once CORE_PATH . 'classes/User.php';
require_once CORE_PATH . 'classes/Campus.php';
require_once CORE_PATH . 'classes/Content.php';

// Load utility functions
require_once CORE_PATH . 'functions/utilities.php';
require_once CORE_PATH . 'functions/helpers.php';

// Initialize database connection
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please contact support.");
    }
}

// Initialize core classes
$campus = new Campus();
$user = new User();
$content = new Content();

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// URL helpers
function site_url($path = '') {
    return defined('BASE_URL') ? BASE_URL . '/' . ltrim($path, '/') : '';
}

function admin_url($path = '') {
    return defined('ADMIN_URL') ? ADMIN_URL . '/' . ltrim($path, '/') : '';
}

function assets_url($path = '') {
    return defined('ASSETS_URL') ? ASSETS_URL . '/' . ltrim($path, '/') : '';
}

function campus_url($path = '') {
    return site_url($path);
}

// Content helpers
function get_campus_setting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        global $db;
        if (!$db || !method_exists($db, 'query')) {
            return $default;
        }
        
        try {
            $sql = "SELECT setting_key, setting_value FROM settings WHERE campus_id = ?";
            $result = $db->query($sql, [defined('CAMPUS_ID') ? CAMPUS_ID : 1]);
            $settings = [];
            
            if ($result) {
                while ($row = $result->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Exception $e) {
            error_log("Error loading campus settings: " . $e->getMessage());
            return $default;
        }
    }
    
    return $settings[$key] ?? $default;
}

function set_campus_setting($key, $value) {
    global $db;
    
    if (!$db || !method_exists($db, 'query')) {
        return false;
    }
    
    try {
        $sql = "INSERT INTO settings (campus_id, setting_key, setting_value) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        
        return $db->query($sql, [defined('CAMPUS_ID') ? CAMPUS_ID : 1, $key, $value]);
    } catch (Exception $e) {
        error_log("Error setting campus setting: " . $e->getMessage());
        return false;
    }
}

// Flash message helpers
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function has_flash_messages() {
    return !empty($_SESSION['flash_messages']);
}

// Menu helpers
function get_active_menu_item($url) {
    $current_url = $_SERVER['REQUEST_URI'];
    return strpos($current_url, $url) === 0 ? 'active' : '';
}

// Security helpers
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validate_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Date helpers
function format_date_local($date, $format = 'F j, Y') {
    if (!$date) return '';
    
    $timezone = defined('CAMPUS_TIMEZONE') ? CAMPUS_TIMEZONE : 'Asia/Manila';
    $dt = new DateTime($date);
    $dt->setTimezone(new DateTimeZone($timezone));
    
    return $dt->format($format);
}

// Logging helper
function log_activity($user_id, $action, $description, $campus_id = null) {
    global $db;
    
    if (!$db) return false;
    
    try {
        $sql = "INSERT INTO activity_logs (user_id, campus_id, action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $campus_id = $campus_id ?? (defined('CAMPUS_ID') ? CAMPUS_ID : 1);
        
        return $db->execute($sql, [
            $user_id,
            $campus_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

// Session security
function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// Redirect helper
function redirect($url, $status_code = 302) {
    if (!headers_sent()) {
        header("Location: {$url}", true, $status_code);
        exit;
    }
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $session_name = defined('SESSION_NAME') ? SESSION_NAME : 'CSU_CMS_SESSION';
    session_name($session_name);
    session_start();
}

// Generate CSRF token for the session
generateCSRFToken();

// Clean output buffer on shutdown
register_shutdown_function(function() {
    if (ob_get_level()) {
        ob_end_flush();
    }
});
?>
