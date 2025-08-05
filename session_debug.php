<?php
/**
 * Session Debug Tool
 * Check session status across different pages
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Debug Tool</h1>";

// Check if session is started
echo "<h2>Session Status:</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . " (0=disabled, 1=none, 2=active)</p>";

if (session_status() === PHP_SESSION_NONE) {
    echo "<p style='color: orange;'>Session not started. Starting now...</p>";
    session_start();
} else {
    echo "<p style='color: green;'>Session already active.</p>";
}

echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";

// Check session data
echo "<h2>Session Data:</h2>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>No session data found!</p>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// Check cookies
echo "<h2>Cookies:</h2>";
if (empty($_COOKIE)) {
    echo "<p>No cookies found.</p>";
} else {
    echo "<pre>";
    print_r($_COOKIE);
    echo "</pre>";
}

// Test session persistence
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
    echo "<p style='color: blue;'>Set test counter to 1. Refresh to see if it persists.</p>";
} else {
    $_SESSION['test_counter']++;
    echo "<p style='color: green;'>Session persisting! Counter: " . $_SESSION['test_counter'] . "</p>";
}

// Check current directory and includes
echo "<h2>File Info:</h2>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

// Links for testing
echo "<h2>Test Links:</h2>";
echo "<p><a href='andrews/session_test.php'>Test Andrews Session</a></p>";
echo "<p><a href='login.php'>Main Login</a></p>";
echo "<p><a href='andrews/login.php'>Andrews Login</a></p>";
?>
