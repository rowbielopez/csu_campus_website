<?php
/**
 * Reset User Password
 */

require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/../../core/classes/EmailService.php';

// Check if user has permission to manage users
if (!is_logged_in() || (!is_super_admin() && !is_campus_admin())) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Access denied. You do not have permission to reset user passwords.'
    ];
    header('Location: ../index.php');
    exit;
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    $send_email = isset($_POST['send_email']);
    
    $errors = [];
    
    // Validate inputs
    if (!$user_id) {
        $errors[] = 'Invalid user ID.';
    }
    
    if (empty($new_password)) {
        $errors[] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    // Get user details
    $sql = "SELECT id, username, email, first_name, last_name, campus_id FROM users WHERE id = ?";
    $params = [$user_id];
    
    // Add campus isolation for non-super admins
    if (!is_super_admin()) {
        $sql .= " AND campus_id = ?";
        $params[] = current_campus_id();
    }
    
    $user = $db->fetch($sql, $params);
    
    if (!$user) {
        $errors[] = 'User not found or you do not have permission to modify this user.';
    }
    
    // Reset password if no errors
    if (empty($errors)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $result = $db->query(
            "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
            [$password_hash, $user_id]
        );
        
        if ($result) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Password reset successfully for user '{$user['username']}'."
            ];
            
            // Send email notification if requested
            if ($send_email) {
                $emailService = new EmailService();
                
                if ($emailService->isConfigured()) {
                    $emailResult = $emailService->sendPasswordReset(
                        $user['email'],
                        $user['first_name'] . ' ' . $user['last_name'],
                        $new_password
                    );
                    
                    if ($emailResult['success']) {
                        $_SESSION['flash_message']['message'] .= ' Email notification sent.';
                    } else {
                        $_SESSION['flash_message']['message'] .= ' Warning: Could not send email notification - ' . $emailResult['message'];
                    }
                } else {
                    $_SESSION['flash_message']['message'] .= ' Warning: Email service not configured - notification email not sent.';
                }
            }
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Error resetting password. Please try again.'
            ];
        }
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => implode(' ', $errors)
        ];
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Invalid request method.'
    ];
}

// Redirect back to user view
$user_id = intval($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
if ($user_id) {
    header("Location: view.php?id={$user_id}");
} else {
    header('Location: index.php');
}
exit;
?>
