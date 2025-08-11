<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating carousel image paths...\n";
    
    // Update with correct image paths
    $updates = [
        [1, 'IZN 2025 Innovation Summit', 'Leading the future of technology and innovation at Andrews Campus.', 'public/img/izn2025 (1).jpg', 'IZN 2025 Event'],
        [2, 'Level 3 AA Cup Championship', 'Excellence in sports and athletic achievement representing our campus pride.', 'public/img/level3aacup (1).jpg', 'Level 3 AA Cup Championship'],
        [3, 'MTLE March 2025', 'Major Teacher Learning Enhancement program at the main campus - advancing educational excellence.', 'public/img/MTLEMARCH2025 (1).jpg', 'MTLE March 2025']
    ];
    
    $stmt = $db->prepare("UPDATE carousel_items SET title = ?, description = ?, image_path = ?, image_alt = ? WHERE id = ?");
    
    foreach ($updates as $update) {
        $stmt->execute([$update[1], $update[2], $update[3], $update[4], $update[0]]);
        echo "✓ Updated item {$update[0]}: {$update[1]}\n";
        
        // Check if file exists
        $fullPath = __DIR__ . '/../../' . $update[3];
        echo "  Path: {$update[3]}\n";
        echo "  File exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
    }
    
    echo "\nDatabase updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
