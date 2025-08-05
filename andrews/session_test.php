<?php
/**
 * Andrews-specific session test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Andrews Session Test</h1>";

echo "<h2>Before Loading Config:</h2>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'Not active') . "</p>";

// Load Andrews config
require_once __DIR__ . '/../config/andrews.php';
echo "<p>✓ Andrews config loaded</p>";

echo "<h2>After Loading Config:</h2>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'Not active') . "</p>";

// Load auth functions
require_once __DIR__ . '/../core/functions/auth.php';
echo "<p>✓ Auth functions loaded</p>";

echo "<h2>Authentication Check:</h2>";
echo "<p>is_logged_in(): " . (is_logged_in() ? 'TRUE' : 'FALSE') . "</p>";

if (is_logged_in()) {
    echo "<p>User ID: " . current_user_id() . "</p>";
    echo "<p>User Email: " . current_user_email() . "</p>";
    echo "<p>Campus ID: " . current_campus_id() . "</p>";
} else {
    echo "<p style='color: red;'>User not logged in!</p>";
}

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Test Authentication:</h2>";
echo "<form method='post'>";
echo "<p>Email: <input type='email' name='email' value='andrews-admin@csu.edu.ph'></p>";
echo "<p>Password: <input type='password' name='password' value='admin123'></p>";
echo "<p><input type='submit' value='Test Login' name='test_login'></p>";
echo "</form>";

if (isset($_POST['test_login'])) {
    require_once __DIR__ . '/../core/classes/Auth.php';
    $auth = new Auth();
    $result = $auth->login($_POST['email'], $_POST['password'], CAMPUS_ID);
    
    echo "<h3>Login Result:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<p style='color: green;'>Login successful! Check session data above.</p>";
        echo "<p><a href='admin/dashboard_fixed.php'>Try Dashboard</a></p>";
    }
}

echo "<h2>Links:</h2>";
echo "<p><a href='../session_debug.php'>Back to Main Session Debug</a></p>";
echo "<p><a href='login.php'>Andrews Login Page</a></p>";
echo "<p><a href='admin/dashboard_fixed.php'>Dashboard</a></p>";
?>
