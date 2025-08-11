<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Current carousel items in database:\n";
    echo "==================================\n\n";
    
    $stmt = $db->query("SELECT id, campus_id, title, image_path, is_active FROM carousel_items ORDER BY campus_id, display_order");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        echo "ID: {$item['id']}\n";
        echo "Campus ID: {$item['campus_id']}\n";
        echo "Title: {$item['title']}\n";
        echo "Image Path: {$item['image_path']}\n";
        echo "Active: " . ($item['is_active'] ? 'Yes' : 'No') . "\n";
        
        // Check if file exists
        $fullPath = __DIR__ . '/../../' . $item['image_path'];
        echo "Full Path: {$fullPath}\n";
        echo "File Exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
