<?php
/**
 * Simple Admin Dashboard - Working Version
 */

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';

// Get current user info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

$page_title = 'Admin Dashboard';
$page_description = 'Welcome to the CSU CMS Admin Dashboard';

include __DIR__ . '/layouts/header.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="activity"></i></div>
                        Welcome, <?php echo htmlspecialchars($current_user['first_name'] ?? $current_user['username']); ?>!
                    </h1>
                    <div class="page-header-subtitle">
                        <?php echo htmlspecialchars($current_campus['name'] ?? 'CSU CMS'); ?> Admin Dashboard
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main page content-->
<div class="container-xl px-4 mt-n10">
    
    <!-- Quick Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Posts Management</div>
                            <div class="h4 mb-0">Manage Content</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="edit-3"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="posts/">View Posts</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Media Library</div>
                            <div class="h4 mb-0">Manage Files</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="image"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="media/">View Media</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php if (is_campus_admin() || is_super_admin()): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">User Management</div>
                            <div class="h4 mb-0">Manage Users</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="users"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="users/">View Users</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Settings</div>
                            <div class="h4 mb-0">Configure</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="settings"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="settings.php">View Settings</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Welcome Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="me-2" data-feather="info"></i>
                    Getting Started
                </div>
                <div class="card-body">
                    <h5>Welcome to the CSU CMS Admin Dashboard!</h5>
                    <p class="mb-4">
                        You are logged in as <strong><?php echo htmlspecialchars($current_user['username']); ?></strong> 
                        with <strong><?php echo htmlspecialchars($current_user['role']); ?></strong> privileges.
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="me-2" data-feather="edit-3"></i>Content Management</h6>
                            <ul class="list-unstyled">
                                <li><a href="posts/" class="text-decoration-none">• Create and manage posts</a></li>
                                <li><a href="posts/create.php" class="text-decoration-none">• Write new articles</a></li>
                                <li><a href="media/" class="text-decoration-none">• Upload and organize media</a></li>
                            </ul>
                        </div>
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                        <div class="col-md-6">
                            <h6><i class="me-2" data-feather="users"></i>Administration</h6>
                            <ul class="list-unstyled">
                                <li><a href="users/" class="text-decoration-none">• Manage user accounts</a></li>
                                <li><a href="users/create.php" class="text-decoration-none">• Add new users</a></li>
                                <li><a href="settings.php" class="text-decoration-none">• Configure campus settings</a></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="me-2" data-feather="user"></i>
                    Your Profile
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img class="img-account-profile rounded-circle me-3" 
                             src="../dist/assets/img/illustrations/profiles/profile-1.png" 
                             alt="Profile Picture" style="width: 60px; height: 60px;">
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h6>
                            <div class="small text-gray-500"><?php echo htmlspecialchars($current_user['email']); ?></div>
                            <div class="small">
                                <span class="badge bg-primary-soft text-primary">
                                    <?php echo ucwords(str_replace('_', ' ', $current_user['role'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="small text-gray-500">
                        <i class="me-1" data-feather="calendar"></i>
                        Last login: <?php echo $current_user['last_login'] ? date('M j, Y g:i A', strtotime($current_user['last_login'])) : 'First time'; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="me-2" data-feather="help-circle"></i>
                    Quick Help
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Need help?</strong> Here are some quick tips:</p>
                        <ul class="small mb-0">
                            <li>Use the navigation menu to access different sections</li>
                            <li>Posts can be saved as drafts before publishing</li>
                            <li>Media files are automatically organized by upload date</li>
                            <?php if (is_campus_admin() || is_super_admin()): ?>
                            <li>User permissions can be managed in the Users section</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
