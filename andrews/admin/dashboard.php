<?php
/**
 * Andrews Campus Admin Dashboard
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load campus configuration first
require_once __DIR__ . '/../../config/andrews.php';

// Load core bootstrap
require_once __DIR__ . '/../../core/bootstrap.php';

// Load authentication middleware
require_once __DIR__ . '/../../core/middleware/admin_only.php';
require_once __DIR__ . '/../../core/classes/Auth.php';
require_once __DIR__ . '/../../core/classes/Database.php';

$auth = new Auth();
$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Get campus information
try {
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
} catch (Exception $e) {
    error_log("Error loading campus info: " . $e->getMessage());
    $campus_info = [
        'name' => 'Andrews Campus',
        'full_name' => 'Cagayan State University - Andrews Campus', 
        'address' => 'Andrews, Cagayan Valley, Philippines',
        'contact_email' => 'info@andrews.csu.edu.ph',
        'theme_color' => '#1e3a8a',
        'secondary_color' => '#f59e0b'
    ];
}

// Get campus-specific statistics with error handling
$stats = [];
try {
    $stats['campus_users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE campus_id = ? AND status = 1", [CAMPUS_ID])['count'] ?? 0;
    $stats['campus_posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE campus_id = ?", [CAMPUS_ID])['count'] ?? 0;
    $stats['campus_pages'] = $db->fetch("SELECT COUNT(*) as count FROM pages WHERE campus_id = ?", [CAMPUS_ID])['count'] ?? 0;
    $stats['published_posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE campus_id = ? AND status = 'published'", [CAMPUS_ID])['count'] ?? 0;
    $stats['draft_posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE campus_id = ? AND status = 'draft'", [CAMPUS_ID])['count'] ?? 0;
    $stats['categories'] = $db->fetch("SELECT COUNT(*) as count FROM categories WHERE campus_id = ?", [CAMPUS_ID])['count'] ?? 0;
} catch (Exception $e) {
    error_log("Error loading stats: " . $e->getMessage());
    $stats = [
        'campus_users' => 0,
        'campus_posts' => 0, 
        'campus_pages' => 0,
        'published_posts' => 0,
        'draft_posts' => 0,
        'categories' => 0
    ];
}

// Get recent campus activity with error handling
try {
    $recent_content = $db->fetchAll("
        SELECT p.title, p.created_at, p.status, u.first_name, u.last_name, 'post' as type
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.campus_id = ?
        ORDER BY p.created_at DESC 
        LIMIT 5
    ", [CAMPUS_ID]);
} catch (Exception $e) {
    error_log("Error loading recent content: " . $e->getMessage());
    $recent_content = [];
}

// Get campus users by role with error handling
try {
    $user_roles = $db->fetchAll("
        SELECT role, COUNT(*) as count 
        FROM users 
        WHERE campus_id = ? AND status = 1 
        GROUP BY role
    ", [CAMPUS_ID]);
} catch (Exception $e) {
    error_log("Error loading user roles: " . $e->getMessage());
    $user_roles = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($campus_info['name']); ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --campus-primary: <?php echo $campus_info['theme_color']; ?>;
            --campus-secondary: <?php echo $campus_info['secondary_color']; ?>;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--campus-primary) 0%, var(--campus-secondary) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 5px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .stat-card {
            border-left: 4px solid var(--campus-primary);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        .navbar-brand {
            font-weight: 600;
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .campus-header {
            background: linear-gradient(45deg, var(--campus-primary), var(--campus-secondary));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-university text-white fa-2x"></i>
                        <h6 class="text-white mt-2"><?php echo htmlspecialchars($campus_info['name']); ?></h6>
                        <small class="text-white-50">CMS Admin</small>
                    </div>

                    <div class="user-info text-white">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo current_user_name(); ?></div>
                                <small class="opacity-75"><?php echo get_role_display_name(current_user_role()); ?></small>
                            </div>
                        </div>
                    </div>

                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        
                        <?php if (can_create_content()): ?>
                            <a class="nav-link" href="posts.php">
                                <i class="fas fa-file-alt me-2"></i> Posts
                            </a>
                            <a class="nav-link" href="pages.php">
                                <i class="fas fa-file me-2"></i> Pages
                            </a>
                        <?php endif; ?>

                        <?php if (can_manage_content()): ?>
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i> Categories
                            </a>
                            <a class="nav-link" href="media.php">
                                <i class="fas fa-images me-2"></i> Media
                            </a>
                        <?php endif; ?>

                        <?php if (can_manage_users()): ?>
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        <?php endif; ?>

                        <?php if (is_super_admin()): ?>
                            <hr class="text-white-50">
                            <a class="nav-link" href="../../admin/dashboard.php">
                                <i class="fas fa-globe me-2"></i> Global Admin
                            </a>
                        <?php endif; ?>

                        <hr class="text-white-50">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a class="nav-link" href="../login.php?action=logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <span class="navbar-brand">
                            <i class="fas fa-tachometer-alt me-2" style="color: var(--campus-primary);"></i>
                            Dashboard - <?php echo htmlspecialchars($campus_info['name']); ?>
                        </span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link">
                                Welcome, <?php echo current_user_name(); ?>
                            </span>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid p-4">
                    <!-- Campus Header -->
                    <div class="campus-header text-center">
                        <h2><?php echo htmlspecialchars($campus_info['full_name']); ?></h2>
                        <p class="mb-0 opacity-75"><?php echo htmlspecialchars($campus_info['address']); ?></p>
                        <small class="opacity-75">Contact: <?php echo htmlspecialchars($campus_info['contact_email']); ?></small>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
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
                                    <p class="card-text text-muted">Total Posts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-eye fa-2x text-success mb-2"></i>
                                    <h4 class="card-title"><?php echo $stats['published_posts']; ?></h4>
                                    <p class="card-text text-muted">Published Posts</p>
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
                    </div>

                    <div class="row">
                        <!-- Recent Activity -->
                        <div class="col-md-8 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Recent Campus Activity
                                    </h5>
                                </div>
                                <div class="card-body recent-activity">
                                    <?php if (empty($recent_content)): ?>
                                        <p class="text-muted text-center py-4">No recent activity found.</p>
                                    <?php else: ?>
                                        <?php foreach ($recent_content as $content): ?>
                                            <div class="d-flex align-items-center border-bottom py-2">
                                                <div class="me-3">
                                                    <?php if ($content['type'] === 'post'): ?>
                                                        <i class="fas fa-file-alt text-info"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-file text-warning"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($content['title']); ?></div>
                                                    <small class="text-muted">
                                                        by <?php echo htmlspecialchars($content['first_name'] . ' ' . $content['last_name']); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?php echo $content['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($content['status']); ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($content['created_at'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions & Stats -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bolt me-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (can_create_content()): ?>
                                        <a href="posts.php?action=new" class="btn btn-primary btn-sm w-100 mb-2">
                                            <i class="fas fa-plus me-2"></i>New Post
                                        </a>
                                        <a href="pages.php?action=new" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                            <i class="fas fa-plus me-2"></i>New Page
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (can_manage_users()): ?>
                                        <a href="users.php?action=new" class="btn btn-outline-success btn-sm w-100 mb-2">
                                            <i class="fas fa-user-plus me-2"></i>Add User
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="profile.php" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-user me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>

                            <!-- User Role Distribution -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        User Roles
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($user_roles)): ?>
                                        <p class="text-muted text-center">No users found.</p>
                                    <?php else: ?>
                                        <?php foreach ($user_roles as $role): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><?php echo get_role_display_name($role['role']); ?></span>
                                                <span class="badge bg-primary"><?php echo $role['count']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
