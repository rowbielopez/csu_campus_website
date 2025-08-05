<?php
/**
 * Simple Working Dashboard for Andrews Campus
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load campus configuration
require_once __DIR__ . '/../../config/andrews.php';

// Load authentication
require_once __DIR__ . '/../../core/functions/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andrews Campus Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .dashboard-header { background: linear-gradient(45deg, #1e3a8a, #f59e0b); color: white; padding: 2rem; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="dashboard-header text-center">
        <h1>✅ Andrews Campus Dashboard</h1>
        <p>Campus Admin Panel - Successfully Loaded!</p>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                        <p><strong>Campus ID:</strong> <?php echo htmlspecialchars($user['campus_id']); ?></p>
                        <p><strong>Login Time:</strong> <?php echo date('Y-m-d H:i:s', $user['login_time']); ?></p>
                        
                        <hr>
                        
                        <h6>Session Debug:</h6>
                        <pre><?php print_r($_SESSION); ?></pre>
                        
                        <hr>
                        
                        <div class="btn-group">
                            <a href="../login.php?action=logout" class="btn btn-danger">Logout</a>
                            <a href="../../admin/index.php" class="btn btn-primary">Main Admin</a>
                            <a href="../../login.php" class="btn btn-secondary">Main Login</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Quick Tests</div>
                    <div class="card-body">
                        <p><strong>Campus Access:</strong> 
                            <?php echo can_access_campus(CAMPUS_ID) ? '✅ Allowed' : '❌ Denied'; ?>
                        </p>
                        <p><strong>Manage Content:</strong> 
                            <?php echo can_manage_content() ? '✅ Yes' : '❌ No'; ?>
                        </p>
                        <p><strong>Manage Users:</strong> 
                            <?php echo can_manage_users() ? '✅ Yes' : '❌ No'; ?>
                        </p>
                        <p><strong>Super Admin:</strong> 
                            <?php echo is_super_admin() ? '✅ Yes' : '❌ No'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
