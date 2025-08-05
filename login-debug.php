<?php
require_once __DIR__ . '/core/config/session.php';
require_once __DIR__ . '/core/classes/Auth.php';

echo "<h1>Login Debug Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Login Attempt Debug:</h2>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Password Length:</strong> " . strlen($password) . "</p>";
    
    $auth = new Auth();
    
    echo "<h3>Step 1: Database Connection</h3>";
    try {
        $db = Database::getInstance();
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Step 2: User Lookup</h3>";
    try {
        $user = $db->fetch("SELECT * FROM users WHERE email = ? AND status = 1", [$email]);
        if ($user) {
            echo "<p style='color: green;'>✅ User found in database</p>";
            echo "<p>User ID: " . $user['id'] . "</p>";
            echo "<p>Role: " . $user['role'] . "</p>";
            echo "<p>Status: " . $user['status'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ User not found or inactive</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ User lookup failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Step 3: Password Verification</h3>";
    if (isset($user) && $user) {
        if (password_verify($password, $user['password_hash'])) {
            echo "<p style='color: green;'>✅ Password verification successful</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification failed</p>";
            echo "<p>Stored hash: " . substr($user['password_hash'], 0, 30) . "...</p>";
            
            // Check if it's an old-style hash
            if (md5($password) === $user['password_hash']) {
                echo "<p style='color: orange;'>⚠️ Password is stored as MD5</p>";
            } elseif (sha1($password) === $user['password_hash']) {
                echo "<p style='color: orange;'>⚠️ Password is stored as SHA1</p>";
            }
        }
    }
    
    echo "<h3>Step 4: Full Login Attempt</h3>";
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Login successful!</p>";
        echo "<p>Redirect URL: " . ($result['redirect_url'] ?? 'Not specified') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Login failed</p>";
        echo "<p>Error message: " . htmlspecialchars($result['message']) . "</p>";
    }
    
    echo "<h3>Step 5: Session Check</h3>";
    if ($auth->isLoggedIn()) {
        echo "<p style='color: green;'>✅ User is now logged in according to Auth class</p>";
        $current_user = $auth->getCurrentUser();
        echo "<p>Current user: " . htmlspecialchars($current_user['username']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ User is NOT logged in according to Auth class</p>";
    }
    
    echo "<h3>Session Data:</h3>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
} else {
    ?>
    <form method="POST">
        <div style="margin-bottom: 10px;">
            <label>Email:</label><br>
            <input type="email" name="email" value="andrews-admin@csu.edu.ph" required style="width: 300px; padding: 5px;">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Password:</label><br>
            <input type="password" name="password" value="admin123" required style="width: 300px; padding: 5px;">
        </div>
        <button type="submit" style="padding: 10px 20px;">Test Login</button>
    </form>
    
    <h2>Current Session Status:</h2>
    <p>Session ID: <?php echo session_id(); ?></p>
    <p>Session Name: <?php echo session_name(); ?></p>
    
    <?php if (!empty($_SESSION)): ?>
        <h3>Current Session Data:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    <?php endif; ?>
    <?php
}
?>
