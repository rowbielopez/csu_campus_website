<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Checking menu-related tables:\n";
    echo "===========================\n";
    
    // Check if menus table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'menus'");
    if (!empty($tables)) {
        echo "✅ menus table exists\n";
        $columns = $db->fetchAll('DESCRIBE menus');
        foreach($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ menus table does not exist\n";
    }
    
    echo "\n";
    
    // Check if menu_items table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'menu_items'");
    if (!empty($tables)) {
        echo "✅ menu_items table exists\n";
        $columns = $db->fetchAll('DESCRIBE menu_items');
        foreach($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ menu_items table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
