<?php
require_once 'core/classes/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Checking users for Andrews campus (ID: 1)...\n";
    
    $users = $db->fetchAll("SELECT id, username, email, first_name, last_name, role, campus_id, status FROM users WHERE campus_id = 1");
    
    if (empty($users)) {
        echo "No users found for Andrews campus.\n";
        
        // Check if andrews-admin@csu.edu.ph exists
        $admin_user = $db->fetch("SELECT * FROM users WHERE email = 'andrews-admin@csu.edu.ph'");
        if (!$admin_user) {
            echo "Creating Andrews admin user...\n";
            
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, campus_id, status, created_at) 
                    VALUES ('andrews-admin', 'andrews-admin@csu.edu.ph', ?, 'Andrews', 'Administrator', 'campus_admin', 1, 1, NOW())";
            $db->query($sql, [$password_hash]);
            echo "Andrews admin user created.\n";
        } else {
            echo "Andrews admin user exists but wrong campus. Updating...\n";
            $db->query("UPDATE users SET campus_id = 1 WHERE email = 'andrews-admin@csu.edu.ph'");
        }
    } else {
        echo "Found " . count($users) . " users for Andrews campus:\n";
        foreach ($users as $user) {
            echo "- {$user['email']} ({$user['role']}) - Status: {$user['status']}\n";
        }
    }
    
    // Also check super admin
    echo "\nChecking super admin...\n";
    $super_admin = $db->fetch("SELECT * FROM users WHERE role = 'super_admin' AND status = 1");
    if ($super_admin) {
        echo "Super admin exists: " . $super_admin['email'] . "\n";
    } else {
        echo "No super admin found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
