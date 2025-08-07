<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CSU Campus Website</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .credentials { background: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
        .button { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to CSU Campus Website</h1>
        </div>
        
        <div class="content">
            <h2>Hello <?= htmlspecialchars($user_name) ?>,</h2>
            
            <p>Your account has been successfully created for the CSU Campus Website management system.</p>
            
            <div class="credentials">
                <h3>Your Login Credentials:</h3>
                <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                <p><strong>Temporary Password:</strong> <?= htmlspecialchars($temporary_password) ?></p>
            </div>
            
            <p>Please use these credentials to log in to your account:</p>
            
            <p style="text-align: center;">
                <a href="<?= htmlspecialchars($login_url) ?>" class="button">Login to Your Account</a>
            </p>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;">
                <h4>🔒 Security Notice:</h4>
                <p>For security reasons, please change your password immediately after your first login.</p>
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
