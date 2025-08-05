<?php
require_once __DIR__ . '/../core/functions/auth.php';

echo "<h1>Session Consistency Test</h1>";

// Set a test session variable from admin root
$_SESSION['test_from_admin_root'] = 'Session data from admin root - ' . date('Y-m-d H:i:s');

echo "<h2>Current Session Status:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

echo "<h2>Session Data:</h2>";
if (!empty($_SESSION)) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p>No session data found</p>";
}

echo "<h2>User Status:</h2>";
if (is_logged_in()) {
    $user = get_logged_in_user();
    echo "<p>✅ User is logged in: " . htmlspecialchars($user['username']) . "</p>";
    echo "<p>Role: " . htmlspecialchars($user['role']) . "</p>";
} else {
    echo "<p>❌ User is not logged in</p>";
}

echo "<h2>Test Links:</h2>";
echo "<p><a href='posts/session-test.php'>Test Posts Session</a></p>";
echo "<p><a href='users/session-test.php'>Test Users Session</a></p>";
echo "<p><a href='media/session-test.php'>Test Media Session</a></p>";
?>
