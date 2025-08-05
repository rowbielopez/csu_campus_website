<?php
/**
 * Andrews Campus Login Page
 */

// Use centralized session configuration
require_once __DIR__ . '/../core/config/session.php';

require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/functions/auth.php';

// Load campus configuration
require_once __DIR__ . '/../config/andrews.php';

$auth = new Auth();
$error_message = '';
$success_message = '';

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
    // Check if user can access this campus
    if ($auth->canAccessCampus(CAMPUS_ID)) {
        header('Location: admin/dashboard_fixed.php');
    } else {
        $auth->logout();
        $error_message = 'Access denied. You do not have permission to access this campus.';
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!$auth->verifyCsrfToken($csrf_token)) {
        $error_message = 'Invalid request. Please try again.';
    } else if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        // Pass campus ID for access control
        $result = $auth->login($email, $password, CAMPUS_ID);
        
        if ($result['success']) {
            header('Location: admin/dashboard_fixed.php');
            exit;
        } else {
            $error_message = $result['message'];
        }
    }
}

$csrf_token = $auth->getCsrfToken();

// Get campus information
$campus_info = $auth->getCampusById(CAMPUS_ID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($campus_info['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, <?php echo $campus_info['theme_color']; ?> 0%, <?php echo $campus_info['secondary_color']; ?> 100%);
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
            background: linear-gradient(135deg, <?php echo $campus_info['theme_color']; ?> 0%, <?php echo $campus_info['secondary_color']; ?> 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: <?php echo $campus_info['theme_color']; ?>;
            box-shadow: 0 0 0 0.2rem <?php echo $campus_info['theme_color']; ?>40;
        }
        .btn-login {
            background: linear-gradient(135deg, <?php echo $campus_info['theme_color']; ?> 0%, <?php echo $campus_info['secondary_color']; ?> 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
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
                    <h3 class="mb-1"><?php echo htmlspecialchars($campus_info['name']); ?></h3>
                    <p class="mb-0 opacity-75"><?php echo htmlspecialchars($campus_info['full_name']); ?></p>
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
                                Sign In to <?php echo htmlspecialchars($campus_info['name']); ?>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <a href="../login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Main Login
                            </a>
                        </small>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <strong>Test Credentials:</strong><br>
                            Campus Admin: andrews-admin@csu.edu.ph / admin123
                        </small>
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
