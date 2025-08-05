<?php
/**
 * Add missing columns to menu tables
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Adding missing columns to menu tables...\n";
    
    // Check and add is_active to menus table
    $columns = $db->fetchAll("SHOW COLUMNS FROM menus LIKE 'is_active'");
    if (empty($columns)) {
        echo "Adding is_active column to menus table...\n";
        $db->query("ALTER TABLE menus ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER location");
        echo "✅ is_active column added to menus table\n";
    } else {
        echo "is_active column already exists in menus table\n";
    }
    
    // Check and add is_visible to menu_items table
    $columns = $db->fetchAll("SHOW COLUMNS FROM menu_items LIKE 'is_visible'");
    if (empty($columns)) {
        echo "Adding is_visible column to menu_items table...\n";
        $db->query("ALTER TABLE menu_items ADD COLUMN is_visible TINYINT(1) DEFAULT 1 AFTER target");
        echo "✅ is_visible column added to menu_items table\n";
    } else {
        echo "is_visible column already exists in menu_items table\n";
    }
    
    // Create a default main menu for each campus if none exists
    echo "\nCreating default menus for campuses...\n";
    
    $campuses = $db->fetchAll("SELECT id, name, code FROM campuses");
    
    foreach ($campuses as $campus) {
        // Check if campus already has a main menu
        $existing_menu = $db->fetch("SELECT id FROM menus WHERE campus_id = ? AND location = 'main'", [$campus['id']]);
        
        if (!$existing_menu) {
            // Create main menu
            $db->query("INSERT INTO menus (campus_id, name, location, is_active, created_at, updated_at) VALUES (?, ?, 'main', 1, NOW(), NOW())", 
                      [$campus['id'], 'Main Navigation']);
            $menu_id = $db->lastInsertId();
            
            // Add default menu items
            $default_items = [
                ['title' => 'Home', 'url' => '/', 'sort_order' => 1],
                ['title' => 'About', 'url' => '/about.php', 'sort_order' => 2],
                ['title' => 'News', 'url' => '/posts.php', 'sort_order' => 3],
                ['title' => 'Contact', 'url' => '/contact.php', 'sort_order' => 4]
            ];
            
            foreach ($default_items as $item) {
                $db->query("INSERT INTO menu_items (menu_id, title, url, sort_order, is_visible, target, created_at, updated_at) VALUES (?, ?, ?, ?, 1, '_self', NOW(), NOW())",
                          [$menu_id, $item['title'], $item['url'], $item['sort_order']]);
            }
            
            echo "✅ Created main menu for {$campus['name']}\n";
        } else {
            echo "Main menu already exists for {$campus['name']}\n";
        }
    }
    
    echo "\n✅ Menu setup complete!\n";
    
    // Show updated table structures
    echo "\nUpdated table structures:\n";
    echo "========================\n";
    
    echo "menus table:\n";
    $columns = $db->fetchAll('DESCRIBE menus');
    foreach($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\nmenu_items table:\n";
    $columns = $db->fetchAll('DESCRIBE menu_items');
    foreach($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
