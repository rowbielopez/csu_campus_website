<?php
/**
 * User Model - Handle user authentication and management
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user login
     */
    public function authenticate($username, $password) {
        $sql = "SELECT id, username, email, password_hash, first_name, last_name, role_id, status 
                FROM users 
                WHERE (username = :username OR email = :username) 
                AND campus_id = :campus_id 
                AND status = 1";
        
        $result = $this->db->query($sql, [
            'username' => $username,
            'campus_id' => CAMPUS_ID
        ]);
        
        $user = $result->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Set session
            $this->setUserSession($user);
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Set user session
     */
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['campus_id'] = CAMPUS_ID;
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && 
               $_SESSION['logged_in'] === true && 
               isset($_SESSION['campus_id']) && 
               $_SESSION['campus_id'] == CAMPUS_ID;
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'role_id' => $_SESSION['role_id'],
            'full_name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
        ];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        // Validate required fields
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        // Check if username or email already exists for this campus
        if ($this->usernameExists($data['username'])) {
            throw new Exception("Username already exists");
        }
        
        if ($this->emailExists($data['email'])) {
            throw new Exception("Email already exists");
        }
        
        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Set default role if not provided
        if (!isset($data['role_id'])) {
            $data['role_id'] = ROLE_READER;
        }
        
        return $this->db->insertCampusRecord('users', $data);
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $data) {
        // Remove password from data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        return $this->db->updateCampusRecord('users', $data, $id);
    }
    
    /**
     * Check if username exists
     */
    private function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = :username AND campus_id = :campus_id";
        $result = $this->db->query($sql, [
            'username' => $username,
            'campus_id' => CAMPUS_ID
        ]);
        return $result->fetch() !== false;
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email AND campus_id = :campus_id";
        $result = $this->db->query($sql, [
            'email' => $email,
            'campus_id' => CAMPUS_ID
        ]);
        return $result->fetch() !== false;
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($user_id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id AND campus_id = :campus_id";
        $this->db->query($sql, [
            'id' => $user_id,
            'campus_id' => CAMPUS_ID
        ]);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role_id, $limit = null) {
        $sql = "SELECT id, username, email, first_name, last_name, role_id, status, created_at 
                FROM users 
                WHERE campus_id = :campus_id AND role_id = :role_id 
                ORDER BY first_name, last_name";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $result = $this->db->query($sql, [
            'campus_id' => CAMPUS_ID,
            'role_id' => $role_id
        ]);
        
        return $result->fetchAll();
    }
    
    /**
     * Check user permissions
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $role_permissions = [
            ROLE_SUPER_ADMIN => ['*'], // All permissions
            ROLE_CAMPUS_ADMIN => [
                'manage_users', 'manage_content', 'manage_settings',
                'manage_menus', 'manage_widgets', 'view_analytics'
            ],
            ROLE_EDITOR => [
                'create_content', 'edit_content', 'publish_content',
                'manage_media', 'moderate_comments'
            ],
            ROLE_AUTHOR => [
                'create_content', 'edit_own_content', 'manage_own_media'
            ],
            ROLE_READER => [
                'view_content'
            ]
        ];
        
        $user_permissions = $role_permissions[$user['role_id']] ?? [];
        
        return in_array('*', $user_permissions) || in_array($permission, $user_permissions);
    }
}
?>
