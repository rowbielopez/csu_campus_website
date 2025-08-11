<?php
/**
 * Email Service Class
 * Handles all email sending functionality using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/email.php';

class EmailService {
    private $mailer;
    private $isConfigured = false;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    /**
     * Configure PHPMailer with SMTP settings
     */
    private function configure() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = SMTP_AUTH;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            
            // Only set credentials if they're provided
            if (defined('SMTP_USERNAME') && !empty(SMTP_USERNAME)) {
                $this->mailer->Username = SMTP_USERNAME;
                $this->mailer->Password = SMTP_PASSWORD;
                $this->isConfigured = true;
            }
            
            // Default sender
            if (defined('SMTP_FROM_EMAIL') && !empty(SMTP_FROM_EMAIL)) {
                $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            }
            
            // Content settings
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
            $this->isConfigured = false;
        }
    }
    
    /**
     * Check if email service is properly configured
     */
    public function isConfigured() {
        return $this->isConfigured;
    }
    
    /**
     * Send user invitation email
     */
    public function sendUserInvitation($userEmail, $userFullName, $username, $temporaryPassword, $loginUrl) {
        if (!$this->isConfigured) {
            return ['success' => false, 'message' => 'Email service not configured'];
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userFullName);
            
            $this->mailer->Subject = 'Welcome to CSU Campus Website - Account Created';
            
            // Load email template
            $emailBody = $this->loadTemplate('user-invitation', [
                'user_name' => $userFullName,
                'username' => $username,
                'temporary_password' => $temporaryPassword,
                'login_url' => $loginUrl,
                'support_email' => SMTP_FROM_EMAIL
            ]);
            
            $this->mailer->Body = $emailBody;
            
            // Plain text version
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));
            
            $this->mailer->send();
            
            return ['success' => true, 'message' => 'Invitation email sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($userEmail, $userFullName, $newPassword) {
        if (!$this->isConfigured) {
            return ['success' => false, 'message' => 'Email service not configured'];
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userFullName);
            
            $this->mailer->Subject = 'Password Reset - CSU Campus Website';
            
            // Load email template
            $emailBody = $this->loadTemplate('password-reset', [
                'user_name' => $userFullName,
                'new_password' => $newPassword,
                'login_url' => $this->getLoginUrl(),
                'support_email' => SMTP_FROM_EMAIL
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));
            
            $this->mailer->send();
            
            return ['success' => true, 'message' => 'Password reset email sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send password change notification
     */
    public function sendPasswordChangeNotification($userEmail, $userFullName) {
        if (!$this->isConfigured) {
            return ['success' => false, 'message' => 'Email service not configured'];
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userFullName);
            
            $this->mailer->Subject = 'Password Changed - CSU Campus Website';
            
            // Load email template
            $emailBody = $this->loadTemplate('password-change-notification', [
                'user_name' => $userFullName,
                'change_time' => date('F j, Y \a\t g:i A'),
                'login_url' => $this->getLoginUrl(),
                'support_email' => SMTP_FROM_EMAIL
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));
            
            $this->mailer->send();
            
            return ['success' => true, 'message' => 'Password change notification sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send test email
     */
    public function sendTestEmail($toEmail, $toName = '') {
        if (!$this->isConfigured) {
            return ['success' => false, 'message' => 'Email service not configured'];
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->Subject = 'Test Email - CSU Campus Website';
            $this->mailer->Body = '<h2>Test Email</h2><p>This is a test email from CSU Campus Website system.</p><p>If you received this, email configuration is working correctly.</p>';
            $this->mailer->AltBody = 'Test Email - This is a test email from CSU Campus Website system. If you received this, email configuration is working correctly.';
            
            $this->mailer->send();
            
            return ['success' => true, 'message' => 'Test email sent successfully'];
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Load email template
     */
    private function loadTemplate($templateName, $variables = []) {
        $templatePath = EMAIL_TEMPLATES_PATH . $templateName . '.php';
        
        if (file_exists($templatePath)) {
            // Extract variables for template
            extract($variables);
            
            // Start output buffering
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        // Fallback to basic template
        return $this->getBasicTemplate($templateName, $variables);
    }
    
    /**
     * Get basic email template if file doesn't exist
     */
    private function getBasicTemplate($templateName, $variables) {
        switch ($templateName) {
            case 'user-invitation':
                return "
                <h2>Welcome to CSU Campus Website</h2>
                <p>Hello {$variables['user_name']},</p>
                <p>Your account has been created successfully. Here are your login credentials:</p>
                <ul>
                    <li><strong>Username:</strong> {$variables['username']}</li>
                    <li><strong>Temporary Password:</strong> {$variables['temporary_password']}</li>
                </ul>
                <p><a href='{$variables['login_url']}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Now</a></p>
                <p>Please change your password after your first login.</p>
                <p>Best regards,<br>CSU IT Team</p>
                ";
            
            case 'password-reset':
                return "
                <h2>Password Reset</h2>
                <p>Hello {$variables['user_name']},</p>
                <p>Your password has been reset. Here is your new password:</p>
                <p><strong>New Password:</strong> {$variables['new_password']}</p>
                <p><a href='{$variables['login_url']}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Now</a></p>
                <p>Please change your password after logging in.</p>
                <p>Best regards,<br>CSU IT Team</p>
                ";
            
            default:
                return "<p>Email template not found.</p>";
        }
    }
    
    /**
     * Get login URL
     */
    private function getLoginUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . '/campus_website2/login.php';
    }
}
?>
