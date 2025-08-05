<?php
/**
 * Media Table Migration Script
 * Creates the media management database structure
 */

require_once 'config/config.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=csu_cms_platform;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('database/migrations/006_create_media_table.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "..." . PHP_EOL;
            } catch (Exception $e) {
                echo "✗ Error: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
    
    echo PHP_EOL . "✓ Media table migration completed successfully!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . PHP_EOL;
}
?>
