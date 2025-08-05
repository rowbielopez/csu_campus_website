<?php
require_once __DIR__ . '/../../core/functions/auth.php';

echo "<h1>Users Session Test</h1>";

// Set a test session variable from users directory
$_SESSION['test_from_users'] = 'Session data from users - ' . date('Y-m-d H:i:s');

echo "<h2>Current Session Status:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

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

echo "<p><a href='../session-test.php'>← Back to Admin Root Test</a></p>";
echo "<p><a href='index.php'>Go to Users Management</a></p>";
?>
