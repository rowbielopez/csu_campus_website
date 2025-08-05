<?php
/**
 * Test Login Debug Page for Andrews Campus
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Andrews Campus Login Debug</h1>";

try {
    echo "<h2>Step 1: Loading Andrews Config</h2>";
    require_once __DIR__ . '/../../config/andrews.php';
    echo "✓ Andrews config loaded<br>";
    echo "CAMPUS_ID: " . (defined('CAMPUS_ID') ? CAMPUS_ID : 'not defined') . "<br>";
    echo "CAMPUS_CODE: " . (defined('CAMPUS_CODE') ? CAMPUS_CODE : 'not defined') . "<br>";

    echo "<h2>Step 2: Loading Bootstrap</h2>";
    require_once __DIR__ . '/../../core/bootstrap.php';
    echo "✓ Bootstrap loaded<br>";

    echo "<h2>Step 3: Checking Session</h2>";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "✓ Session is active<br>";
        echo "Session ID: " . session_id() . "<br>";
        
        if (isset($_SESSION['user'])) {
            echo "✓ User session exists<br>";
            echo "User ID: " . $_SESSION['user']['id'] . "<br>";
            echo "User Role: " . $_SESSION['user']['role'] . "<br>";
            echo "User Campus ID: " . $_SESSION['user']['campus_id'] . "<br>";
        } else {
            echo "✗ No user session found<br>";
        }
    } else {
        echo "✗ Session not active<br>";
    }

    echo "<h2>Step 4: Loading Auth Functions</h2>";
    require_once __DIR__ . '/../../core/functions/auth.php';
    echo "✓ Auth functions loaded<br>";

    if (function_exists('is_logged_in')) {
        echo "is_logged_in(): " . (is_logged_in() ? 'true' : 'false') . "<br>";
    }
    
    if (function_exists('current_user_name')) {
        echo "current_user_name(): " . (current_user_name() ?: 'null') . "<br>";
    }

    echo "<h2>Step 5: Testing Database</h2>";
    $db = Database::getInstance();
    echo "✓ Database instance created<br>";
    
    // Test query
    $result = $db->fetch("SELECT COUNT(*) as count FROM users WHERE campus_id = ?", [CAMPUS_ID]);
    echo "Campus users count: " . $result['count'] . "<br>";

    echo "<h2>Step 6: Testing Auth Class</h2>";
    $auth = new Auth();
    echo "✓ Auth class instantiated<br>";
    
    $campus_info = $auth->getCampusById(CAMPUS_ID);
    if ($campus_info) {
        echo "✓ Campus info loaded: " . $campus_info['name'] . "<br>";
    } else {
        echo "✗ Failed to load campus info<br>";
    }

} catch (Exception $e) {
    echo "<div style='color: red;'><h2>Error:</h2>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h2>Debug Complete</h2>";
echo "<a href='dashboard.php'>Try Dashboard</a>";
?>
