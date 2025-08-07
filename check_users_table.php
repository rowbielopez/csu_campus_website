<?php
require_once 'core/classes/Database.php';
$db = Database::getInstance();
try {
    $result = $db->query('DESCRIBE users');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "Users table structure:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . ' - ' . $column['Type'] . ' - ' . ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . ' - ' . ($column['Default'] ?? 'No default') . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
