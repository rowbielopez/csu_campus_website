<?php
/**
 * Simple Session Test Dashboard
 * Tests exact same flow as login redirect
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard Session Test</h1>";

// Step 1: Load session config (same as login)
echo "<h2>Step 1: Session Config</h2>";
require_once __DIR__ . '/../../core/config/session.php';
echo "<p>✓ Session config loaded</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// Step 2: Load campus config (same as login)
echo "<h2>Step 2: Campus Config</h2>";
require_once __DIR__ . '/../../config/andrews.php';
echo "<p>✓ Andrews config loaded</p>";

// Step 3: Load auth functions (same as dashboard)
echo "<h2>Step 3: Auth Functions</h2>";
require_once __DIR__ . '/../../core/functions/auth.php';
echo "<p>✓ Auth functions loaded</p>";

// Step 4: Check authentication
echo "<h2>Step 4: Authentication Check</h2>";
echo "<p>is_logged_in(): " . (is_logged_in() ? 'TRUE' : 'FALSE') . "</p>";

if (is_logged_in()) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>✅ SUCCESS! User is logged in</h3>";
    echo "<p><strong>Name:</strong> " . current_user_name() . "</p>";
    echo "<p><strong>Email:</strong> " . current_user_email() . "</p>";
    echo "<p><strong>Role:</strong> " . current_user_role() . "</p>";
    echo "<p><strong>Campus ID:</strong> " . current_campus_id() . "</p>";
    echo "<p><strong>Can access campus:</strong> " . (can_access_campus(CAMPUS_ID) ? 'YES' : 'NO') . "</p>";
    echo "<p><strong>Can manage users:</strong> " . (can_manage_users() ? 'YES' : 'NO') . "</p>";
    echo "</div>";
    
    echo "<h3>This would normally load the dashboard content...</h3>";
    echo "<p><a href='dashboard_fixed.php'>Try Full Dashboard</a></p>";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ PROBLEM! User is not logged in</h3>";
    echo "<p>This explains why you're getting redirected back to login.</p>";
    echo "<p>The session is not being maintained between login and dashboard.</p>";
    echo "</div>";
    
    echo "<h3>Debugging Info:</h3>";
    echo "<p>This page should show the user as logged in if the session is working properly.</p>";
    echo "<p><a href='../login.php'>Back to Login</a></p>";
}

// Show session data
echo "<h2>Current Session Data:</h2>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>No session data!</p>";
} else {
    echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd;'>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<h2>Navigation:</h2>";
echo "<p><a href='../login.php'>Andrews Login</a></p>";
echo "<p><a href='../../session_flow_test.php'>Session Flow Test</a></p>";
echo "<p><a href='../../login.php'>Main Login</a></p>";
?>
