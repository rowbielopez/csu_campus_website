<?php
require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance()->getConnection();

// Add carousel item for Carig campus (campus_id = 2)
echo "Adding carousel item for Carig campus...\n";

try {
    $stmt = $db->prepare("
        INSERT INTO carousel_items (campus_id, title, description, image_path, image_alt, display_order, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        2, // Carig campus
        'MTLE March 2025 - Carig Campus',
        'Major Teacher Licensure Examination preparation program at Carig Campus',
        'public/img/MTLEMARCH2025 (1).jpg',
        'MTLE March 2025 Program',
        1,
        1
    ]);
    
    echo "✅ Successfully added carousel item for Carig campus\n";
    
    // List all carousel items
    echo "\nAll carousel items:\n";
    $items = $db->query("SELECT id, campus_id, title, image_path, is_active FROM carousel_items ORDER BY campus_id, display_order")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $campusName = $item['campus_id'] == 1 ? 'Andrews' : ($item['campus_id'] == 2 ? 'Carig' : 'Campus ' . $item['campus_id']);
        echo "- {$campusName}: {$item['title']} (" . ($item['is_active'] ? 'Active' : 'Inactive') . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
