<?php
/**
 * Main Login Page for CSU CMS Platform
 */

require_once __DIR__ . '/core/config/session.php';
require_once __DIR__ . '/core/classes/Auth.php';
require_once __DIR__ . '/core/functions/auth.php';

$auth = new Auth();
$error_message = '';
$success_message = '';

// Handle campus selection from URL
$selected_campus = $_GET['campus'] ?? '';
$campus_info = null;
$campus_id = null;

// Get campus information if campus is selected
if ($selected_campus) {
    $campus_map = [
        'andrews' => 1,
        'aparri' => 2, 
        'carig' => 3,
        'gonzaga' => 4,
        'lallo' => 5,
        'lasam' => 6,
        'piat' => 7,
        'sanchezmira' => 8,
        'solana' => 9
    ];
    
    if (isset($campus_map[$selected_campus])) {
        $campus_id = $campus_map[$selected_campus];
        $campus_info = $auth->getCampusById($campus_id);
    }
}

// Handle GET messages
if (isset($_GET['message'])) {
    $success_message = htmlspecialchars($_GET['message']);
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
    $success_message = 'You have been logged out successfully.';
}

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    $campus_form = $_POST['campus'] ?? '';

    // Verify CSRF token
    if (!$auth->verifyCsrfToken($csrf_token)) {
        $error_message = 'Invalid request. Please try again.';
    } else if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        // Determine campus ID for login
        $login_campus_id = null;
        if ($campus_form && isset($campus_map[$campus_form])) {
            $login_campus_id = $campus_map[$campus_form];
        }
        
        $result = $auth->login($email, $password, $login_campus_id);
        
        if ($result['success']) {
            // Determine redirect based on campus selection
            if ($campus_form) {
                // Redirect to campus-specific dashboard
                header('Location: admin/index.php?campus=' . $campus_form);
            } else {
                // Redirect to main admin dashboard
                header('Location: admin/index.php');
            }
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
    <title><?php echo $selected_campus ? "Login - " . htmlspecialchars($selected_campus['name']) : "Login - CSU CMS Platform"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        }
        .input-group-text {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-university fa-3x mb-3"></i>
                    <h2 class="mb-0"><?php echo $selected_campus ? htmlspecialchars($selected_campus['name']) : "CSU CMS Platform"; ?></h2>
                    <p class="mb-0 opacity-75"><?php echo $selected_campus ? "Cagayan State University" : "Cagayan State University"; ?></p>
                </div>
                
                <div class="login-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <?php if ($selected_campus): ?>
                            <input type="hidden" name="campus" value="<?php echo htmlspecialchars($selected_campus); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="forgot-password.php" class="text-decoration-none">
                            <small class="text-muted">
                                <i class="fas fa-key me-1"></i>
                                Forgot your password?
                            </small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Auto-focus on email field
        document.getElementById('email').focus();
    </script>
</body>
</html>
