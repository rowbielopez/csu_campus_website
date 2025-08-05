<?php
/**
 * Add missing is_featured column to posts table
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if column already exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM posts LIKE 'is_featured'");
    
    if (empty($columns)) {
        echo "Adding is_featured column to posts table...\n";
        
        $sql = "ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status";
        $db->query($sql);
        
        echo "✅ is_featured column added successfully!\n";
        
        // Set some random posts as featured for testing
        $db->query("UPDATE posts SET is_featured = 1 WHERE id IN (SELECT id FROM (SELECT id FROM posts ORDER BY RAND() LIMIT 3) as temp)");
        echo "✅ Set 3 random posts as featured for testing\n";
        
    } else {
        echo "is_featured column already exists.\n";
    }
    
    // Show updated table structure
    echo "\nUpdated posts table structure:\n";
    echo "=============================\n";
    $columns = $db->fetchAll('DESCRIBE posts');
    foreach($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . " - " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
