<?php
/**
 * Comprehensive Session Flow Test
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Flow Test</h1>";
echo "<p>Testing the complete login flow to identify session issues.</p>";

// Step 1: Load session config
echo "<h2>Step 1: Load Session Config</h2>";
require_once __DIR__ . '/core/config/session.php';
echo "<p>✓ Session config loaded</p>";
echo "<p>Session Status: " . session_status() . " (2 = active)</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

// Step 2: Load Andrews config
echo "<h2>Step 2: Load Campus Config</h2>";
require_once __DIR__ . '/config/andrews.php';
echo "<p>✓ Andrews config loaded</p>";
echo "<p>Campus ID: " . CAMPUS_ID . "</p>";

// Step 3: Load Auth class
echo "<h2>Step 3: Load Auth Class</h2>";
require_once __DIR__ . '/core/classes/Auth.php';
$auth = new Auth();
echo "<p>✓ Auth class instantiated</p>";

// Step 4: Check current authentication
echo "<h2>Step 4: Check Current Authentication</h2>";
echo "<p>isLoggedIn(): " . ($auth->isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";

if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo "<p>Current User: " . $user['email'] . "</p>";
    echo "<p>Campus ID: " . $user['campus_id'] . "</p>";
    echo "<p>Can access Andrews: " . ($auth->canAccessCampus(CAMPUS_ID) ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p style='color: red;'>Not logged in - this might be the issue!</p>";
}

// Step 5: Test login if not logged in
if (!$auth->isLoggedIn()) {
    echo "<h2>Step 5: Test Login Process</h2>";
    
    // Simulate login
    $login_result = $auth->login('andrews-admin@csu.edu.ph', 'admin123', CAMPUS_ID);
    
    echo "<p>Login Result:</p>";
    echo "<pre>";
    print_r($login_result);
    echo "</pre>";
    
    if ($login_result['success']) {
        echo "<p style='color: green;'>✓ Login successful!</p>";
        
        // Check session after login
        echo "<h3>Session After Login:</h3>";
        echo "<p>Session ID: " . session_id() . "</p>";
        echo "<p>isLoggedIn(): " . ($auth->isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";
        
        echo "<h3>Session Data:</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<p><a href='andrews/admin/dashboard_fixed.php'>Test Dashboard Access</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Login failed: " . $login_result['message'] . "</p>";
    }
}

echo "<h2>Current Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Navigation Links:</h2>";
echo "<p><a href='andrews/login.php'>Andrews Login</a></p>";
echo "<p><a href='andrews/session_test.php'>Andrews Session Test</a></p>";
echo "<p><a href='session_debug.php'>Basic Session Debug</a></p>";

// Test session persistence
if (!isset($_SESSION['flow_test_counter'])) {
    $_SESSION['flow_test_counter'] = 1;
    echo "<p style='color: blue;'>Set flow test counter. Refresh to test persistence.</p>";
} else {
    $_SESSION['flow_test_counter']++;
    echo "<p style='color: green;'>Session persisting! Flow counter: " . $_SESSION['flow_test_counter'] . "</p>";
}
?>
