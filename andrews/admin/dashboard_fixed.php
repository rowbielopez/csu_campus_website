<?php
/**
 * Andrews Campus Admin Dashboard - Fixed Version
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load campus configuration first
require_once __DIR__ . '/../../config/andrews.php';

// Load session config (same as login page)
require_once __DIR__ . '/../../core/config/session.php';

// Load auth functions ONLY (no bootstrap to avoid conflicts)
require_once __DIR__ . '/../../core/functions/auth.php';

// Manual authentication check (instead of using middleware that might cause redirects)
if (!is_logged_in()) {
    // Instead of using redirect_to_login which might cause a loop, redirect directly
    header('Location: ../login.php?message=' . urlencode('Please log in to access the dashboard.'));
    exit;
}

// Check if user can access this campus
if (!can_access_campus(CAMPUS_ID) && !is_super_admin()) {
    http_response_code(403);
    die('<h1>Access Denied</h1><p>You do not have permission to access this campus.</p><p><a href="../login.php">Login with different account</a></p>');
}

// Check if user has admin privileges
if (!can_manage_users()) {
    http_response_code(403);
    die('<h1>Access Denied</h1><p>Administrator privileges required.</p><p><a href="../login.php">Login as administrator</a></p>');
}

// If we get here, user is properly authenticated and authorized
try {
    // Load required classes
    require_once __DIR__ . '/../../core/classes/Database.php';
    require_once __DIR__ . '/../../core/classes/Auth.php';
    
    $auth = new Auth();
    $db = Database::getInstance();
    $user = $auth->getCurrentUser();

    // Get campus information with fallback
    $campus_info = $auth->getCampusById(CAMPUS_ID);
    if (!$campus_info) {
        $campus_info = [
            'name' => 'Andrews Campus',
            'full_name' => 'Cagayan State University - Andrews Campus',
            'address' => 'Andrews, Cagayan Valley, Philippines',
            'contact_email' => 'info@andrews.csu.edu.ph',
            'theme_color' => '#1e3a8a',
            'secondary_color' => '#f59e0b'
        ];
    }

    // Get basic stats with error handling
    $stats = [
        'campus_users' => 0,
        'campus_posts' => 0,
        'campus_pages' => 0,
        'published_posts' => 0
    ];
    
    try {
        $stats['campus_users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE campus_id = ? AND status = 1", [CAMPUS_ID])['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error loading user count: " . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    die('<h1>Dashboard Error</h1><p>An error occurred loading the dashboard. Please try again.</p><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($campus_info['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --campus-primary: <?php echo $campus_info['theme_color']; ?>;
            --campus-secondary: <?php echo $campus_info['secondary_color']; ?>;
        }
        body { background: #f8f9fa; }
        .navbar { background: linear-gradient(45deg, var(--campus-primary), var(--campus-secondary)) !important; }
        .dashboard-header { 
            background: linear-gradient(45deg, var(--campus-primary), var(--campus-secondary)); 
            color: white; 
            padding: 2rem; 
            margin-bottom: 2rem; 
            border-radius: 10px;
        }
        .stat-card {
            border-left: 4px solid var(--campus-primary);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>
                <?php echo htmlspecialchars($campus_info['name']); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars(current_user_name()); ?>
                </span>
                <a class="nav-link" href="../login.php?action=logout">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Success Message -->
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Dashboard Loaded Successfully!</strong> No more redirect loops.
        </div>

        <!-- Campus Header -->
        <div class="dashboard-header text-center">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
            <h3><?php echo htmlspecialchars($campus_info['full_name']); ?></h3>
            <p class="mb-0 opacity-75"><?php echo htmlspecialchars($campus_info['address']); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['campus_users']; ?></h4>
                        <p class="card-text text-muted">Campus Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['campus_posts']; ?></h4>
                        <p class="card-text text-muted">Posts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-file fa-2x text-warning mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['campus_pages']; ?></h4>
                        <p class="card-text text-muted">Pages</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x text-success mb-2"></i>
                        <h4 class="card-title"><?php echo $stats['published_posts']; ?></h4>
                        <p class="card-text text-muted">Published</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="#" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Create Post
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="#" class="btn btn-outline-success w-100">
                                    <i class="fas fa-file me-2"></i>Create Page
                                </a>
                            </div>
                            <?php if (can_manage_users()): ?>
                            <div class="col-md-6 mb-2">
                                <a href="#" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add User
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6 mb-2">
                                <a href="../../admin/index.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-globe me-2"></i>Global Admin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars(current_user_name()); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars(current_user_email()); ?></p>
                        <p><strong>Role:</strong> <?php echo htmlspecialchars(get_role_display_name(current_user_role())); ?></p>
                        <p><strong>Campus:</strong> <?php echo htmlspecialchars($campus_info['name']); ?></p>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-secondary btn-sm">Edit Profile</a>
                            <a href="../login.php?action=logout" class="btn btn-outline-danger btn-sm">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
