<?php
/**
 * User Authentication Class
 * Handles user login, logout, and session management
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/session.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        // Session is already started by session.php
    }

    /**
     * Authenticate user with email and password
     */
    public function login($email, $password, $campus_id = null) {
        try {
            // Get user from database
            $sql = "SELECT * FROM users WHERE email = ? AND status = 1";
            $user = $this->db->fetch($sql, [$email]);

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password.'];
            }

            // Check campus access (if not super admin)
            if ($user['role'] !== 'super_admin' && $campus_id !== null) {
                if ($user['campus_id'] != $campus_id) {
                    return ['success' => false, 'message' => 'Access denied. You do not have permission to access this campus.'];
                }
            }

            // Create session
            $this->createUserSession($user);

            // Update last login
            $this->updateLastLogin($user['id']);

            return ['success' => true, 'message' => 'Login successful.', 'user' => $user];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login. Please try again.'];
        }
    }

    /**
     * Create user session
     */
    private function createUserSession($user) {
        // Regenerate session ID for security - but don't destroy old session data
        // session_regenerate_id(true); // Commented out - this might be causing the issue
        
        // Instead, just regenerate without destroying
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(false);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'campus_id' => $user['campus_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'avatar_path' => $user['avatar_path'],
            'login_time' => time()
        ];

        // Set additional session data
        $_SESSION['is_super_admin'] = ($user['role'] === 'super_admin');
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * Update user's last login timestamp
     */
    private function updateLastLogin($user_id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$user_id]);
    }

    /**
     * Logout user
     */
    public function logout() {
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
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Get current user data
     */
    public function getCurrentUser() {
        return $this->isLoggedIn() ? $_SESSION['user'] : null;
    }

    /**
     * Check if current user has access to specific campus
     */
    public function canAccessCampus($campus_id) {
        if (!$this->isLoggedIn()) return false;
        
        // Super admins can access any campus
        if ($_SESSION['user']['role'] === 'super_admin') return true;
        
        // Other users can only access their assigned campus
        return $_SESSION['user']['campus_id'] == $campus_id;
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        $sql = "SELECT * FROM users WHERE id = ? AND status = 1";
        return $this->db->fetch($sql, [$user_id]);
    }

    /**
     * Get campus information by ID
     */
    public function getCampusById($campus_id) {
        $sql = "SELECT * FROM campuses WHERE id = ? AND status = 'active'";
        return $this->db->fetch($sql, [$campus_id]);
    }

    /**
     * Get campus by code
     */
    public function getCampusByCode($campus_code) {
        $sql = "SELECT * FROM campuses WHERE code = ? AND status = 'active'";
        return $this->db->fetch($sql, [$campus_code]);
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF token
     */
    public function getCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Check password strength
     */
    public function validatePassword($password) {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }

    /**
     * Hash password securely
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
