<?php
/**
 * Forgot Password Page
 * Allow users to request password reset
 */

require_once __DIR__ . '/core/config/session.php';
require_once __DIR__ . '/core/classes/Auth.php';
require_once __DIR__ . '/core/functions/auth.php';
require_once __DIR__ . '/core/classes/EmailService.php';

$auth = new Auth();
$error_message = '';
$success_message = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!$auth->verifyCsrfToken($csrf_token)) {
        $error_message = 'Invalid request. Please try again.';
    } else if (empty($email)) {
        $error_message = 'Please enter your email address.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if user exists
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user) {
            // Generate temporary password
            $temp_password = bin2hex(random_bytes(8));
            $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // Update user's password
            $updated = $db->query(
                "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
                [$password_hash, $user['id']]
            );
            
            if ($updated) {
                // Send password reset email
                $emailService = new EmailService();
                if ($emailService->isConfigured()) {
                    $user_full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                    $result = $emailService->sendPasswordReset($email, $user_full_name, $temp_password);
                    
                    if ($result['success']) {
                        $success_message = 'A new password has been sent to your email address.';
                    } else {
                        $error_message = 'Password reset was processed, but email sending failed. Please contact administrator.';
                    }
                } else {
                    $error_message = 'Email service is not configured. Please contact administrator.';
                }
            } else {
                $error_message = 'Error processing password reset. Please try again.';
            }
        } else {
            // Don't reveal if email exists - security best practice
            $success_message = 'If an account with that email exists, a new password has been sent.';
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
    <title>Forgot Password - CSU CMS Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .forgot-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .btn-reset {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #c82333 0%, #e85d04 100%);
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
        <div class="forgot-container">
            <div class="forgot-card">
                <div class="forgot-header">
                    <i class="fas fa-key fa-3x mb-3"></i>
                    <h2 class="mb-0">Forgot Password</h2>
                    <p class="mb-0 opacity-75">CSU CMS Platform</p>
                </div>
                
                <div class="forgot-body">
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
                        <div class="text-center">
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <p class="text-muted">
                                Enter your email address and we'll send you a new password.
                            </p>
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-4">
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

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-danger btn-reset">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send New Password
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">
                                <small class="text-muted">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Back to Login
                                </small>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.focus();
            }
        });
    </script>
</body>
</html>
