<?php
/**
 * Simple Andrews Campus Admin Test Dashboard
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Andrews Admin Dashboard Test</h1>";

try {
    echo "<p>Step 1: Loading configuration...</p>";
    require_once __DIR__ . '/../../config/andrews.php';
    echo "<p>✓ Andrews config loaded - Campus ID: " . CAMPUS_ID . "</p>";

    echo "<p>Step 2: Loading bootstrap...</p>";
    require_once __DIR__ . '/../../core/bootstrap.php';
    echo "<p>✓ Bootstrap loaded</p>";

    echo "<p>Step 3: Loading auth functions...</p>";
    require_once __DIR__ . '/../../core/functions/auth.php';
    echo "<p>✓ Auth functions loaded</p>";

    echo "<p>Step 4: Checking authentication...</p>";
    if (is_logged_in()) {
        echo "<p>✓ User is logged in: " . current_user_name() . "</p>";
        echo "<p>User role: " . current_user_role() . "</p>";
        echo "<p>Campus ID: " . current_campus_id() . "</p>";
        
        if (can_access_campus(CAMPUS_ID)) {
            echo "<p>✓ User can access this campus</p>";
        } else {
            echo "<p>✗ User cannot access this campus</p>";
        }
    } else {
        echo "<p>✗ User is not logged in</p>";
        echo "<p><a href='../login.php'>Go to Login</a></p>";
        exit;
    }

    echo "<p>Step 5: Loading Auth class...</p>";
    $auth = new Auth();
    echo "<p>✓ Auth class loaded</p>";

    echo "<p>Step 6: Testing database...</p>";
    $db = Database::getInstance();
    echo "<p>✓ Database connected</p>";

    echo "<p>Step 7: Getting campus info...</p>";
    $campus_info = $auth->getCampusById(CAMPUS_ID);
    if ($campus_info) {
        echo "<p>✓ Campus info loaded: " . htmlspecialchars($campus_info['name']) . "</p>";
    } else {
        echo "<p>✗ Campus info not found</p>";
    }

    echo "<h2>Success! All components loaded correctly.</h2>";
    echo "<p><a href='dashboard.php'>Try Full Dashboard</a></p>";

} catch (Exception $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Error:</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>
