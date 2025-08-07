<?php
/**
 * Email Test Utility
 * Test email configuration and sending functionality
 */

require_once __DIR__ . '/core/classes/EmailService.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = trim($_POST['test_email'] ?? '');
    $testName = trim($_POST['test_name'] ?? '');
    
    if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $emailService = new EmailService();
        
        if (!$emailService->isConfigured()) {
            $message = 'Email service is not configured. Please check your email settings.';
            $messageType = 'error';
        } else {
            $result = $emailService->sendTestEmail($testEmail, $testName);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - CSU Campus Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>📧 Email Configuration Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="test_email" class="form-label">Test Email Address</label>
                                <input type="email" class="form-control" id="test_email" name="test_email" 
                                       placeholder="Enter email to send test to" 
                                       value="<?= htmlspecialchars($_POST['test_email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="test_name" class="form-label">Name (Optional)</label>
                                <input type="text" class="form-control" id="test_name" name="test_name" 
                                       placeholder="Enter recipient name" 
                                       value="<?= htmlspecialchars($_POST['test_name'] ?? '') ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Test Email</button>
                            <a href="admin/index.php" class="btn btn-secondary">Back to Admin</a>
                        </form>
                        
                        <hr class="mt-4">
                        
                        <h5>📋 Configuration Status</h5>
                        <?php
                        $emailService = new EmailService();
                        if ($emailService->isConfigured()): ?>
                            <div class="alert alert-success">
                                ✅ Email service is configured and ready to use.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                ⚠️ Email service is not configured. 
                                <br><br>
                                <strong>Setup Instructions:</strong>
                                <ol>
                                    <li>Copy <code>config/email-credentials-sample.php</code> to <code>config/email-credentials.php</code></li>
                                    <li>Update the email settings with your SMTP credentials</li>
                                    <li>For Gmail: Enable 2-factor authentication and create an App Password</li>
                                </ol>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <h6>Current Settings:</h6>
                            <ul class="list-unstyled">
                                <li><strong>SMTP Host:</strong> <?= defined('SMTP_HOST') ? SMTP_HOST : 'Not set' ?></li>
                                <li><strong>SMTP Port:</strong> <?= defined('SMTP_PORT') ? SMTP_PORT : 'Not set' ?></li>
                                <li><strong>SMTP Security:</strong> <?= defined('SMTP_SECURE') ? SMTP_SECURE : 'Not set' ?></li>
                                <li><strong>From Email:</strong> <?= defined('SMTP_FROM_EMAIL') && !empty(SMTP_FROM_EMAIL) ? SMTP_FROM_EMAIL : 'Not configured' ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
