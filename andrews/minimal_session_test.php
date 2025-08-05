<?php
/**
 * Minimal Session Test
 * Test if sessions work at all in the andrews directory
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session manually first
session_start();

echo "<h1>Minimal Session Test (Andrews Directory)</h1>";
echo "<p>Location: " . __FILE__ . "</p>";

// Test session persistence
if (!isset($_SESSION['minimal_counter'])) {
    $_SESSION['minimal_counter'] = 1;
    echo "<p style='color: blue;'>✓ Set session counter to 1</p>";
    echo "<p><strong>Refresh this page to test if session persists</strong></p>";
} else {
    $_SESSION['minimal_counter']++;
    echo "<p style='color: green;'>✅ Session is working! Counter: " . $_SESSION['minimal_counter'] . "</p>";
}

echo "<h2>Session Info:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Test Links:</h2>";
echo "<p><a href='session_test_2.php'>Test Session in Another File</a></p>";
echo "<p><a href='../session_debug.php'>Back to Main Session Debug</a></p>";

// Simulate setting user session data like the Auth class does
if (isset($_GET['simulate_login'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'campus_id' => 1,
        'username' => 'test-user',
        'email' => 'test@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 'campus_admin',
        'login_time' => time()
    ];
    
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<p>✅ Simulated login - user session data set!</p>";
    echo "<p><a href='debug_loop.php'>Test Dashboard Debug</a></p>";
    echo "</div>";
}

if (!isset($_GET['simulate_login'])) {
    echo "<p><a href='?simulate_login=1'>Simulate Login Session</a></p>";
}
?>
