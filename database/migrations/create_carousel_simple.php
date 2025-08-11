<?php
/**
 * Simple Carousel Table Creation
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating carousel_items table...\n";
    
    // Create the table
    $createTable = "
    CREATE TABLE IF NOT EXISTS carousel_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campus_id INT NOT NULL,
        title VARCHAR(255) NULL,
        description TEXT NULL,
        image_path VARCHAR(500) NOT NULL,
        image_alt VARCHAR(255) NULL,
        link_url VARCHAR(500) NULL,
        link_target ENUM('_self', '_blank') DEFAULT '_self',
        is_active BOOLEAN DEFAULT TRUE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_campus_id (campus_id),
        INDEX idx_active_order (campus_id, is_active, display_order)
    )";
    
    $db->exec($createTable);
    echo "✓ carousel_items table created\n";
    
    // Insert sample data
    $sampleData = "
    INSERT IGNORE INTO carousel_items (campus_id, title, description, image_path, image_alt, display_order, is_active) VALUES
    (1, 'Welcome to Andrews Campus', 'Experience excellence in education and innovation at our beautiful campus.', 'uploads/carousel/sample1.jpg', 'Andrews Campus Welcome', 1, TRUE),
    (1, 'Student Life at Andrews', 'Discover vibrant student life, clubs, and activities that shape your college experience.', 'uploads/carousel/sample2.jpg', 'Student Life', 2, TRUE),
    (2, 'Welcome to Carig Campus', 'Join our academic community dedicated to learning and growth.', 'uploads/carousel/sample3.jpg', 'Carig Campus Welcome', 1, TRUE)
    ";
    
    $db->exec($sampleData);
    echo "✓ Sample data inserted\n";
    
    // Verify the table was created
    $result = $db->query("SHOW TABLES LIKE 'carousel_items'");
    if ($result->rowCount() > 0) {
        echo "✓ carousel_items table exists\n";
        
        // Check if we have data
        $count = $db->query("SELECT COUNT(*) FROM carousel_items")->fetchColumn();
        echo "✓ Table contains {$count} records\n";
    } else {
        echo "✗ carousel_items table was not created\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
