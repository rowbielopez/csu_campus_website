<?php
/**
 * Run Carousel Table Migration
 */

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../core/classes/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting carousel table migration...\n";
    
    // Read the SQL migration file
    $sql = file_get_contents(__DIR__ . '/create_carousel_table.sql');
    
    // Split into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "⚠ Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
                } else {
                    echo "✗ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\nMigration completed successfully!\n";
    
    // Verify the table was created
    $result = $db->query("SHOW TABLES LIKE 'carousel_items'");
    if ($result->rowCount() > 0) {
        echo "✓ carousel_items table exists\n";
        
        // Check if we have data
        $count = $db->query("SELECT COUNT(*) FROM carousel_items")->fetchColumn();
        echo "✓ Table contains {$count} records\n";
    } else {
        echo "✗ carousel_items table was not created\n";
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
