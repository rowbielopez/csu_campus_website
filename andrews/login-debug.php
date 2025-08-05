<?php
/**
 * Andrews Campus Login Page - Debug Version
 */

session_start();

require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/functions/auth.php';

// Load campus configuration
require_once __DIR__ . '/../config/andrews.php';

$auth = new Auth();
$error_message = '';
$success_message = '';
$debug_info = [];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_info[] = "Form submitted";
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    $debug_info[] = "Email: $email";
    $debug_info[] = "Password length: " . strlen($password);
    $debug_info[] = "CSRF token: " . substr($csrf_token, 0, 20) . "...";

    // Verify CSRF token
    if (!$auth->verifyCsrfToken($csrf_token)) {
        $error_message = 'Invalid request. Please try again.';
        $debug_info[] = "CSRF verification failed";
    } else if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
        $debug_info[] = "Empty email or password";
    } else {
        $debug_info[] = "Calling login with campus_id: " . CAMPUS_ID;
        
        // Pass campus ID for access control
        $result = $auth->login($email, $password, CAMPUS_ID);
        
        $debug_info[] = "Login result: " . json_encode($result);
        
        if ($result['success']) {
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error_message = $result['message'];
        }
    }
}

$csrf_token = $auth->getCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Andrews Campus (Debug)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3a8a 0%, #f59e0b 100%); min-height: 100vh; }
        .debug-panel { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; margin: 20px 0; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Andrews Campus Login</h2>
                            <p class="text-muted">Debug Version</p>
                        </div>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($debug_info)): ?>
                            <div class="debug-panel">
                                <h6>Debug Information:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($debug_info as $info): ?>
                                        <li><small><?php echo htmlspecialchars($info); ?></small></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="andrews-admin@csu.edu.ph" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="admin123" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>

                        <div class="debug-panel mt-3">
                            <h6>Test Credentials:</h6>
                            <p class="mb-1"><strong>Email:</strong> andrews-admin@csu.edu.ph</p>
                            <p class="mb-1"><strong>Password:</strong> admin123</p>
                            <p class="mb-0"><strong>Campus ID:</strong> <?php echo CAMPUS_ID; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
