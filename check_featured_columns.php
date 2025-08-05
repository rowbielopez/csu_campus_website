<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Checking featured columns in posts table:\n";
    
    $columns = $db->fetchAll("SHOW COLUMNS FROM posts WHERE Field LIKE '%featured%'");
    
    foreach($columns as $col) {
        echo "Column: " . $col['Field'] . " - Type: " . $col['Type'] . " - Default: " . ($col['Default'] ?? 'NULL') . "\n";
    }
    
    // Check if we have both columns
    $featured_count = $db->fetch("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = 'posts' AND column_name = 'featured'");
    $is_featured_count = $db->fetch("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = 'posts' AND column_name = 'is_featured'");
    
    echo "\nColumn existence:\n";
    echo "featured: " . ($featured_count['count'] > 0 ? 'EXISTS' : 'NOT EXISTS') . "\n";
    echo "is_featured: " . ($is_featured_count['count'] > 0 ? 'EXISTS' : 'NOT EXISTS') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
