<?php
/**
 * Session Test 2 - Check if session persists between files
 */

session_start();

echo "<h1>Session Test 2</h1>";
echo "<p>Testing if session data persists between files in andrews directory</p>";

if (isset($_SESSION['minimal_counter'])) {
    echo "<p style='color: green;'>✅ Session data found! Counter: " . $_SESSION['minimal_counter'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ No session data found!</p>";
}

if (isset($_SESSION['user'])) {
    echo "<p style='color: green;'>✅ User session data found!</p>";
    echo "<pre>";
    print_r($_SESSION['user']);
    echo "</pre>";
    
    echo "<p><a href='admin/debug_loop.php'>Test Dashboard Debug</a></p>";
} else {
    echo "<p style='color: orange;'>No user session data</p>";
}

echo "<h2>All Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='minimal_session_test.php'>Back to Session Test 1</a></p>";
?>
