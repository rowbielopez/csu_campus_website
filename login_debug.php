<?php
/**
 * Login Process Debug
 * Test the exact login flow to see where user session is lost
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Process Debug</h1>";

// Step 1: Load session config (same as login page)
echo "<h2>Step 1: Load Session Config</h2>";
require_once __DIR__ . '/core/config/session.php';
echo "<p>✅ Session loaded</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

// Step 2: Load Andrews config
echo "<h2>Step 2: Load Campus Config</h2>";
require_once __DIR__ . '/config/andrews.php';
echo "<p>✅ Andrews config loaded - Campus ID: " . CAMPUS_ID . "</p>";

// Step 3: Load Auth class
echo "<h2>Step 3: Load Auth Class</h2>";
require_once __DIR__ . '/core/classes/Auth.php';
$auth = new Auth();
echo "<p>✅ Auth class instantiated</p>";

// Check session before login
echo "<h2>Session Before Login:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Step 4: Attempt login
echo "<h2>Step 4: Attempt Login</h2>";
echo "<form method='post'>";
echo "<p>Email: <input type='email' name='email' value='andrews-admin@csu.edu.ph' required></p>";
echo "<p>Password: <input type='password' name='password' value='admin123' required></p>";
echo "<p><input type='submit' name='test_login' value='Test Login Process'></p>";
echo "</form>";

if (isset($_POST['test_login'])) {
    echo "<h3>Login Attempt...</h3>";
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    echo "<p>Campus ID: " . CAMPUS_ID . "</p>";
    
    // Call login method
    $result = $auth->login($email, $password, CAMPUS_ID);
    
    echo "<h3>Login Result:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h3>✅ Login Method Returned Success!</h3>";
        echo "</div>";
        
        // Check session after login
        echo "<h3>Session After Login:</h3>";
        echo "<p>Session ID: " . session_id() . " (may have changed due to regeneration)</p>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        // Test authentication functions
        echo "<h3>Authentication Function Tests:</h3>";
        echo "<p>isLoggedIn(): " . ($auth->isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";
        
        if ($auth->isLoggedIn()) {
            $current_user = $auth->getCurrentUser();
            echo "<p>getCurrentUser():</p>";
            echo "<pre>";
            print_r($current_user);
            echo "</pre>";
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<h4>🎉 SUCCESS! Login is working correctly!</h4>";
            echo "<p>The issue might be in the redirect or session persistence between pages.</p>";
            echo "<p><a href='andrews/admin/debug_loop.php'>Test Dashboard Access</a></p>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h4>❌ Problem: Login succeeded but isLoggedIn() returns false!</h4>";
            echo "<p>This indicates a session issue in the Auth class.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>❌ Login Failed</h3>";
        echo "<p>Message: " . htmlspecialchars($result['message']) . "</p>";
        echo "</div>";
    }
}

echo "<h2>Navigation:</h2>";
echo "<p><a href='andrews/login.php'>Andrews Login Page</a></p>";
echo "<p><a href='session_debug.php'>Session Debug</a></p>";
?>
