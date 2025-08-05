<?php
require_once 'core/classes/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Checking campuses table...\n";
    
    // Check if campuses table exists
    $result = $db->query("SHOW TABLES LIKE 'campuses'");
    if ($result->rowCount() == 0) {
        echo "Campuses table doesn't exist. Creating...\n";
        
        $sql = "CREATE TABLE campuses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            code VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            full_name VARCHAR(200) NOT NULL,
            address TEXT,
            contact_email VARCHAR(100),
            contact_phone VARCHAR(20),
            theme_color VARCHAR(7) DEFAULT '#1e3a8a',
            secondary_color VARCHAR(7) DEFAULT '#f59e0b',
            logo_path VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->query($sql);
        echo "Campuses table created.\n";
    }
    
    // Check if Andrews campus exists
    $andrews = $db->fetch("SELECT * FROM campuses WHERE code = 'andrews'");
    if (!$andrews) {
        echo "Andrews campus not found. Creating...\n";
        
        $sql = "INSERT INTO campuses (code, name, full_name, address, contact_email, theme_color, secondary_color, status) VALUES 
                ('andrews', 'Andrews Campus', 'Cagayan State University - Andrews Campus', 'Andrews, Cagayan Valley, Philippines', 'info@andrews.csu.edu.ph', '#1e3a8a', '#f59e0b', 'active')";
        $db->query($sql);
        echo "Andrews campus created.\n";
    } else {
        echo "Andrews campus exists: " . $andrews['name'] . "\n";
    }
    
    // List all campuses
    echo "\nAll campuses:\n";
    $campuses = $db->fetchAll("SELECT * FROM campuses");
    foreach ($campuses as $campus) {
        echo "ID: {$campus['id']}, Code: {$campus['code']}, Name: {$campus['name']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
