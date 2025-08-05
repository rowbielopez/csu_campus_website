<?php
// Simple login page without any fancy features
require_once __DIR__ . '/core/config/session.php';
require_once __DIR__ . '/core/classes/Auth.php';

$auth = new Auth();
$message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            header('Location: admin/index.php');
            exit;
        } else {
            $message = $result['message'];
        }
    } else {
        $message = 'Please enter both email and password.';
    }
}

// Check if already logged in
if ($auth->isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { padding: 10px; margin: 10px 0; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <h2>CSU CMS Login</h2>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" value="andrews-admin@csu.edu.ph" required>
        
        <label>Password:</label>
        <input type="password" name="password" value="admin123" required>
        
        <button type="submit">Login</button>
    </form>
    
    <p><small>Default credentials: andrews-admin@csu.edu.ph / admin123</small></p>
    <p><a href="test-login.php">Debug Login</a> | <a href="login.php">Full Login Page</a></p>
</body>
</html>
