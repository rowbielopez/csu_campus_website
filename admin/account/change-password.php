<?php
/**
 * Change Password
 * Allow users to change their password
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/classes/EmailService.php';

$current_user = get_logged_in_user();
$db = Database::getInstance();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $send_email = isset($_POST['send_email']);
    
    $response = ['success' => false, 'message' => ''];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
        $response['message'] = $error_message;
    } elseif (!password_verify($current_password, $current_user['password_hash'])) {
        $error_message = 'Current password is incorrect.';
        $response['message'] = $error_message;
    } elseif (strlen($new_password) < 8) {
        $error_message = 'New password must be at least 8 characters long.';
        $response['message'] = $error_message;
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New password and confirmation do not match.';
        $response['message'] = $error_message;
    } elseif ($current_password === $new_password) {
        $error_message = 'New password must be different from current password.';
        $response['message'] = $error_message;
    } else {
        // Hash new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in database
        $updated = $db->query(
            "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
            [$password_hash, $current_user['id']]
        );
        
        if ($updated) {
            $success_message = 'Password changed successfully!';
            $response['success'] = true;
            $response['message'] = $success_message;
            $response['email_requested'] = $send_email;
            $response['email_sent'] = false;
            
            // Send email notification if requested
            if ($send_email) {
                $emailService = new EmailService();
                if ($emailService->isConfigured()) {
                    $user_full_name = trim($current_user['first_name'] . ' ' . $current_user['last_name']);
                    $result = $emailService->sendPasswordChangeNotification(
                        $current_user['email'], 
                        $user_full_name
                    );
                    
                    if ($result['success']) {
                        $success_message .= ' Email notification sent.';
                        $response['message'] = $success_message;
                        $response['email_sent'] = true;
                    } else {
                        $success_message .= ' (Email notification failed to send)';
                        $response['message'] = $success_message;
                        $response['email_error'] = $result['error'] ?? 'Unknown email error';
                    }
                } else {
                    $success_message .= ' (Email service not configured)';
                    $response['message'] = $success_message;
                    $response['email_error'] = 'Email service not configured';
                }
            }
            
            // Update session password hash
            $_SESSION['user']['password_hash'] = $password_hash;
        } else {
            $error_message = 'Error changing password. Please try again.';
            $response['message'] = $error_message;
        }
    }
    
    // Check if this is an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$page_title = 'Change Password';
$page_description = 'Update your account password';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="lock"></i></div>
                        Change Password
                    </h1>
                    <div class="page-header-subtitle">Update your account security</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a href="profile.php" class="btn btn-outline-light">
                        <i class="me-2" data-feather="arrow-left"></i>
                        Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main page content-->
<div class="container-xl px-4 mt-n10">
    <!-- Flash Messages -->
    <?php if ($error_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php echo addslashes(htmlspecialchars($error_message)); ?>', 'danger');
            });
        </script>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php echo addslashes(htmlspecialchars($success_message)); ?>', 'success');
            });
        </script>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Change password card-->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="me-2" data-feather="shield"></i>
                    Password Security
                </div>
                <div class="card-body">
                    <form method="POST" id="changePasswordForm">
                        <!-- Current Password -->
                        <div class="mb-3">
                            <label class="small mb-1" for="currentPassword">Current Password</label>
                            <div class="input-group">
                                <input class="form-control" id="currentPassword" name="current_password" 
                                       type="password" placeholder="Enter your current password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword', this)">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="small mb-1" for="newPassword">New Password</label>
                            <div class="input-group">
                                <input class="form-control" id="newPassword" name="new_password" 
                                       type="password" placeholder="Enter your new password" required
                                       onkeyup="checkPasswordStrength(this.value)">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword', this)">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            <div class="small text-muted mt-1">
                                Password must be at least 8 characters long
                            </div>
                            <!-- Password strength indicator -->
                            <div class="password-strength mt-2" id="passwordStrength" style="display: none;">
                                <div class="small mb-1">Password Strength:</div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" id="strengthBar"></div>
                                </div>
                                <div class="small mt-1" id="strengthText"></div>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label class="small mb-1" for="confirmPassword">Confirm New Password</label>
                            <div class="input-group">
                                <input class="form-control" id="confirmPassword" name="confirm_password" 
                                       type="password" placeholder="Confirm your new password" required
                                       onkeyup="checkPasswordMatch()">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword', this)">
                                    <i data-feather="eye"></i>
                                </button>
                            </div>
                            <div class="small mt-1" id="passwordMatch"></div>
                        </div>
                        
                        <!-- Email notification option -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" id="sendEmail" name="send_email" type="checkbox" value="1" checked>
                                <label class="form-check-label" for="sendEmail">
                                    Send email notification about password change
                                </label>
                            </div>
                        </div>
                        
                        <!-- Security Tips -->
                        <div class="alert alert-info">
                            <h6><i class="me-2" data-feather="info"></i>Security Tips:</h6>
                            <ul class="mb-0 small">
                                <li>Use a mix of uppercase and lowercase letters, numbers, and symbols</li>
                                <li>Avoid using personal information like names or birthdates</li>
                                <li>Don't reuse passwords from other accounts</li>
                                <li>Consider using a password manager</li>
                            </ul>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit" id="submitBtn">
                                <i class="me-2" data-feather="lock"></i>
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Password Requirements Card -->
            <div class="card">
                <div class="card-header">
                    <i class="me-2" data-feather="check-circle"></i>
                    Password Requirements
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    At least 8 characters long
                                </li>
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    Contains uppercase letters
                                </li>
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    Contains lowercase letters
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    Contains numbers
                                </li>
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    Contains special characters
                                </li>
                                <li class="mb-2">
                                    <i class="text-success me-2" data-feather="check"></i>
                                    Different from current password
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Email sending animation */
.email-sending-toast {
    min-width: 280px;
}

.email-sending-animation {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.email-envelope {
    position: relative;
    width: 100%;
    height: 100%;
}

.email-icon {
    font-size: 20px;
    animation: emailPulse 2s infinite ease-in-out;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.email-plane {
    font-size: 12px;
    position: absolute;
    top: 5px;
    right: 5px;
    animation: planeFly 3s infinite linear;
    opacity: 0;
}

@keyframes emailPulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.1);
        opacity: 0.8;
    }
}

@keyframes planeFly {
    0% {
        transform: translate(0, 0) rotate(0deg);
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    100% {
        transform: translate(20px, -20px) rotate(45deg);
        opacity: 0;
    }
}

/* Email sending progress indicator */
.email-sending-animation::after {
    content: '';
    position: absolute;
    width: 45px;
    height: 45px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: emailSpin 1s linear infinite;
}

@keyframes emailSpin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Glow effect for email sending */
.email-sending-toast .toast-body {
    position: relative;
    overflow: visible;
}

.email-sending-toast .toast-body::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 20px;
    width: 40px;
    height: 40px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    border-radius: 50%;
    transform: translateY(-50%);
    animation: emailGlow 2s infinite ease-in-out;
    pointer-events: none;
}

@keyframes emailGlow {
    0%, 100% {
        transform: translateY(-50%) scale(0.8);
        opacity: 0.3;
    }
    50% {
        transform: translateY(-50%) scale(1.3);
        opacity: 0.7;
    }
}
</style>

<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-feather', 'eye-off');
        feather.replace();
    } else {
        input.type = 'password';
        icon.setAttribute('data-feather', 'eye');
        feather.replace();
    }
}

// Check password strength
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    let score = 0;
    let feedback = [];
    
    // Length check
    if (password.length >= 8) score += 20;
    else feedback.push('at least 8 characters');
    
    // Uppercase check
    if (/[A-Z]/.test(password)) score += 20;
    else feedback.push('uppercase letters');
    
    // Lowercase check
    if (/[a-z]/.test(password)) score += 20;
    else feedback.push('lowercase letters');
    
    // Number check
    if (/\d/.test(password)) score += 20;
    else feedback.push('numbers');
    
    // Special character check
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score += 20;
    else feedback.push('special characters');
    
    // Update progress bar
    strengthBar.style.width = score + '%';
    
    if (score < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak - Add: ' + feedback.join(', ');
        strengthText.className = 'small mt-1 text-danger';
    } else if (score < 80) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Medium - Add: ' + feedback.join(', ');
        strengthText.className = 'small mt-1 text-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong';
        strengthText.className = 'small mt-1 text-success';
    }
}

// Check password match
function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirmPassword.length === 0) {
        matchDiv.textContent = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchDiv.textContent = '✓ Passwords match';
        matchDiv.className = 'small mt-1 text-success';
    } else {
        matchDiv.textContent = '✗ Passwords do not match';
        matchDiv.className = 'small mt-1 text-danger';
    }
}

// Form validation and AJAX submission
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Client-side validation
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match!', 'danger');
        return false;
    }
    
    if (newPassword.length < 8) {
        showToast('Password must be at least 8 characters long!', 'danger');
        return false;
    }
    
    // Get form data
    const formData = new FormData(this);
    
    // Find submit button and show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    const sendEmailCheckbox = document.getElementById('sendEmail');
    
    if (sendEmailCheckbox.checked) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changing Password & Sending Email...';
    } else {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changing Password...';
    }
    submitBtn.disabled = true;
    
    // Submit the form with AJAX header
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show initial success message (password changed)
            showToast('🔐 Password changed successfully!', 'success');
            
            // Check if email notification was requested
            if (data.email_requested) {
                // Show email sending animation
                showEmailSendingToast();
                
                // Simulate email sending delay for better UX
                setTimeout(() => {
                    hideEmailSendingToast();
                    
                    // Show email result based on actual backend response
                    if (data.email_sent) {
                        showToast('📧 Email notification sent successfully!', 'success');
                    } else if (data.email_error) {
                        showToast('⚠️ Email notification failed: ' + data.email_error, 'warning');
                    } else {
                        showToast('⚠️ Email notification failed to send', 'warning');
                    }
                }, 1500); // 1.5 second delay for email animation
            }
            
            // Clear form on success
            setTimeout(() => {
                document.getElementById('changePasswordForm').reset();
                
                // Hide password strength indicator
                document.getElementById('passwordStrength').style.display = 'none';
                document.getElementById('passwordMatch').textContent = '';
            }, data.email_requested ? 2000 : 500); // Delay form reset if email is being sent
            
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error changing password. Please try again.', 'danger');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Helper function to show toasts
function showToast(message, type) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'check-circle' : 'alert-circle';
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="me-2" data-feather="${iconClass}"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Email sending animation functions
let emailToastId = null;

function showEmailSendingToast() {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create email sending toast
    emailToastId = 'email-toast-' + Date.now();
    
    const toastHTML = `
        <div id="${emailToastId}" class="toast align-items-center text-white bg-info border-0 email-sending-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <div class="email-sending-animation me-3">
                        <div class="email-envelope">
                            <div class="email-icon">✉️</div>
                            <div class="email-plane">✈️</div>
                        </div>
                    </div>
                    <div>
                        <div class="fw-bold">Sending Email Notification</div>
                        <div class="small opacity-75">Please wait while we send the notification...</div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(emailToastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false // Don't auto-hide this one
    });
    
    toast.show();
}

function hideEmailSendingToast() {
    if (emailToastId) {
        const toastElement = document.getElementById(emailToastId);
        if (toastElement) {
            const toast = bootstrap.Toast.getInstance(toastElement);
            if (toast) {
                toast.hide();
            }
            // Remove element after hiding
            setTimeout(() => {
                if (toastElement.parentNode) {
                    toastElement.remove();
                }
            }, 300);
        }
        emailToastId = null;
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
