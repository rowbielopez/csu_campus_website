<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $campuses = $db->fetchAll('SELECT * FROM campuses ORDER BY id');
    
    echo "Campus Information:\n";
    echo "==================\n";
    
    foreach ($campuses as $campus) {
        echo "ID: " . $campus['id'] . "\n";
        echo "Name: " . $campus['name'] . "\n";
        echo "Code: " . ($campus['code'] ?? 'NULL') . "\n";
        echo "URL: " . ($campus['website_url'] ?? 'NULL') . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
