<?php
require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance()->getConnection();
$items = $db->query('SELECT id, campus_id, image_path, title FROM carousel_items LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

echo "Current carousel items in database:\n";
foreach ($items as $item) {
    echo "ID: {$item['id']}, Campus: {$item['campus_id']}, Image: {$item['image_path']}, Title: {$item['title']}\n";
}

// Also check what files exist in public/img
echo "\nFiles in public/img directory:\n";
$imgDir = __DIR__ . '/../../public/img/';
if (is_dir($imgDir)) {
    $files = scandir($imgDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($imgDir . $file)) {
            echo "- {$file}\n";
        }
    }
} else {
    echo "Directory public/img does not exist\n";
}
?>
