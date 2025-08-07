<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - CSU Campus Website</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .credentials { background: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
        .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Password Reset</h1>
        </div>
        
        <div class="content">
            <h2>Hello <?= htmlspecialchars($user_name) ?>,</h2>
            
            <p>Your password has been reset by an administrator.</p>
            
            <div class="credentials">
                <h3>Your New Password:</h3>
                <p><strong>New Password:</strong> <?= htmlspecialchars($new_password) ?></p>
            </div>
            
            <p>Please use this new password to log in to your account:</p>
            
            <p style="text-align: center;">
                <a href="<?= htmlspecialchars($login_url) ?>" class="button">Login to Your Account</a>
            </p>
            
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 5px;">
                <h4>🔒 Security Recommendation:</h4>
                <p>For security reasons, please change this password immediately after logging in to something only you know.</p>
            </div>
            
            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px;">
                <h4>⚠️ Didn't Request This?</h4>
                <p>If you didn't request a password reset, please contact your administrator immediately.</p>
            </div>
            
            <p>If you have any questions or need assistance, please contact our support team at 
               <a href="mailto:<?= htmlspecialchars($support_email) ?>"><?= htmlspecialchars($support_email) ?></a></p>
        </div>
        
        <div class="footer">
            <p>This email was sent by CSU Campus Website Management System</p>
            <p>If you received this email in error, please ignore it.</p>
        </div>
    </div>
</body>
</html>
