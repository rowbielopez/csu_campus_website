<?php
/**
 * Admin Layout Header
 * Universal header that works from any admin directory
 */

// Define admin access
if (!defined('ADMIN_ACCESS')) {
    define('ADMIN_ACCESS', true);
}

// Load admin configuration
require_once dirname(__FILE__) . '/../config/admin-config.php';

// Ensure we have user data
if (!function_exists('get_logged_in_user')) {
    require_once dirname(__FILE__) . '/../../core/functions/auth.php';
}

$current_user = get_logged_in_user();
$current_campus = get_current_campus();

// Set defaults if not defined
$page_title = $page_title ?? 'Admin Dashboard';
$page_description = $page_description ?? 'CSU CMS Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta name="author" content="CSU CMS Platform" />
    <title><?php echo htmlspecialchars($page_title); ?> - CSU CMS</title>
    
    <!-- CSS Files with Absolute URLs -->
    <?php foreach ($admin_css_files as $css_file): ?>
        <link href="<?php echo admin_asset_path($css_file); ?>" rel="stylesheet" />
    <?php endforeach; ?>
    
    <!-- Cropper.js CSS for image cropping -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo admin_asset_url(ADMIN_IMG_PATH . '/favicon.png'); ?>" />
    
    <!-- Icon Libraries -->
    <?php foreach ($admin_icon_files as $icon_file): ?>
        <script src="<?php echo $icon_file; ?>" <?php echo strpos($icon_file, 'font-awesome') ? 'data-search-pseudo-elements defer' : ''; ?> crossorigin="anonymous"></script>
    <?php endforeach; ?>
    
    <!-- Custom Campus Styles -->
    <style>
        :root {
            --campus-primary: <?php echo $current_campus['theme_color'] ?? '#069952'; ?>;
            --campus-secondary: <?php echo $current_campus['secondary_color'] ?? '#f59e0b'; ?>;
        }
        
        .navbar-brand {
            color: white !important;
        }
        
        /* Admin navbar styling */
        .navbar-dark {
            background-color: var(--campus-primary) !important;
        }
        
        .btn-primary {
            background-color: var(--campus-primary);
            border-color: var(--campus-primary);
        }
        
        /* Custom avatar styling for navigation */
        .dropdown-user-img, .btn-transparent-dark .img-fluid, .btn-transparent-dark > div {
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .dropdown-user-img:hover, .btn-transparent-dark:hover .img-fluid, .btn-transparent-dark:hover > div {
            transform: scale(1.05);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Default avatar background */
        .bg-primary {
            background: linear-gradient(135deg, #0061f2 0%, #0056d3 100%) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
            border-color: var(--campus-primary);
        }
        
        .btn-primary:hover {
            background-color: rgba(6, 153, 82, 0.8);
            border-color: rgba(6, 153, 82, 0.8);
        }
        
        .bg-gradient-primary-to-secondary {
            background: linear-gradient(135deg, var(--campus-primary) 0%, var(--campus-secondary) 100%);
        }
        
        .text-primary {
            color: var(--campus-primary) !important;
        }
        
        .badge.bg-primary {
            background-color: var(--campus-primary) !important;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body class="nav-fixed">
    <!-- Top Navigation -->
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
        <!-- Sidenav Toggle Button-->
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>
        
        <!-- Navbar Brand-->
        <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="<?php echo ADMIN_ROOT; ?>/index.php">
            <img src="<?php echo ADMIN_ROOT; ?>/../public/img/Cagayan State University - Logo.png" alt="CSU Logo" height="28" class="d-inline-block align-text-top me-2">
            <?php echo htmlspecialchars($current_campus['name'] ?? 'CSU CMS'); ?>
        </a>
        
        <!-- Navbar Search Input-->
        <form class="form-inline me-auto d-none d-lg-block me-3">
            <div class="input-group input-group-joined input-group-solid">
                <input class="form-control pe-0" type="search" placeholder="Search..." aria-label="Search" />
                <div class="input-group-text"><i data-feather="search"></i></div>
            </div>
        </form>
        
        <!-- Navbar Items-->
        <ul class="navbar-nav align-items-center ms-auto">
            <!-- Alerts Dropdown-->
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                    <h6 class="dropdown-header">
                        <i class="me-2" data-feather="bell"></i>
                        Alerts Center
                    </h6>
                    <a class="dropdown-item" href="#!">
                        <div class="dropdown-item-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="dropdown-item-content">
                            <div class="dropdown-item-content-details">December 29, 2021</div>
                            <div class="dropdown-item-content-text">This is an alert message. It's nothing serious, but it requires your attention.</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-footer" href="#!">View All Alerts</a>
                </div>
            </li>
            
            <!-- Messages Dropdown-->
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownMessages" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="mail"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownMessages">
                    <h6 class="dropdown-header">
                        <i class="me-2" data-feather="mail"></i>
                        Message Center
                    </h6>
                    <a class="dropdown-item" href="#!">
                        <img class="dropdown-item-img" src="<?php echo admin_asset_url(ADMIN_IMG_PATH . '/illustrations/profiles/profile-2.png'); ?>" />
                        <div class="dropdown-item-content">
                            <div class="dropdown-item-content-details">Thomas Wilcox · 58m</div>
                            <div class="dropdown-item-content-text">Hey! There are some new files available to download. I'll send over the link.</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-footer" href="#!">Read All Messages</a>
                </div>
            </li>
            
            <!-- User Dropdown-->
            <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php if (!empty($current_user['avatar_url']) && file_exists(__DIR__ . '/../../' . $current_user['avatar_url'])): ?>
                        <img class="img-fluid rounded-2" 
                             src="<?php echo admin_asset_url('../../' . $current_user['avatar_url']); ?>" 
                             alt="Profile" 
                             style="width: 36px; height: 36px; object-fit: cover;" />
                    <?php else: ?>
                        <div class="img-fluid rounded-2 bg-primary d-flex align-items-center justify-content-center text-white fw-bold" 
                             style="width: 36px; height: 36px; font-size: 14px;">
                            <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                    <h6 class="dropdown-header d-flex align-items-center">
                        <?php if (!empty($current_user['avatar_url']) && file_exists(__DIR__ . '/../../' . $current_user['avatar_url'])): ?>
                            <img class="dropdown-user-img rounded-2" 
                                 src="<?php echo admin_asset_url('../../' . $current_user['avatar_url']); ?>" 
                                 alt="Profile" 
                                 style="width: 48px; height: 48px; object-fit: cover;" />
                        <?php else: ?>
                            <div class="dropdown-user-img rounded-2 bg-primary d-flex align-items-center justify-content-center text-white fw-bold" 
                                 style="width: 48px; height: 48px; font-size: 16px;">
                                <?php echo strtoupper(substr($current_user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="dropdown-user-details">
                            <div class="dropdown-user-details-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                            <div class="dropdown-user-details-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                        </div>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo ADMIN_ROOT; ?>/account/profile.php">
                        <div class="dropdown-item-icon"><i data-feather="user"></i></div>
                        Profile
                    </a>
                    <a class="dropdown-item" href="<?php echo ADMIN_ROOT; ?>/account/change-password.php">
                        <div class="dropdown-item-icon"><i data-feather="lock"></i></div>
                        Change Password
                    </a>
                    <a class="dropdown-item" href="<?php echo admin_asset_url('/logout.php'); ?>">
                        <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    
    <!-- Side Navigation -->
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sidenav shadow-right sidenav-light">
                <div class="sidenav-menu">
                    <div class="nav accordion" id="accordionSidenav">
                        <!-- Core Section -->
                        <div class="sidenav-menu-heading">Core</div>
                        
                        <!-- Dashboard Link -->
                        <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/index.php">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            Dashboard
                        </a>
                        
                        <!-- Content Management -->
                        <div class="sidenav-menu-heading">Content</div>
                        
                        <!-- Posts Management -->
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapsePostsManagement" aria-expanded="false" aria-controls="collapsePostsManagement">
                            <div class="nav-link-icon"><i data-feather="edit-3"></i></div>
                            Posts Management
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePostsManagement" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/posts/">All Posts</a>
                                <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/posts/create.php">Create Post</a>
                                <?php if (is_campus_admin() || is_super_admin()): ?>
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/posts/?status=pending">Pending Review</a>
                                <?php endif; ?>
                                <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/posts/?author=<?php echo $current_user['id']; ?>">My Posts</a>
                            </nav>
                        </div>
                        
                        <!-- Categories Management -->
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseCategoriesManagement" aria-expanded="false" aria-controls="collapseCategoriesManagement">
                                <div class="nav-link-icon"><i data-feather="folder"></i></div>
                                Categories
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseCategoriesManagement" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/categories/">All Categories</a>
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/categories/create.php">Create Category</a>
                                </nav>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Media Management -->
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseMediaManagement" aria-expanded="false" aria-controls="collapseMediaManagement">
                            <div class="nav-link-icon"><i data-feather="image"></i></div>
                            Media Library
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseMediaManagement" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/media/">Media Library</a>
                                <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/media/upload.php">Upload Media</a>
                            </nav>
                        </div>
                        
                        <!-- Widget Management -->
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseWidgetManagement" aria-expanded="false" aria-controls="collapseWidgetManagement">
                                <div class="nav-link-icon"><i data-feather="grid"></i></div>
                                Widgets
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseWidgetManagement" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/widgets.php">Manage Widgets</a>
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/carousel.php">Carousel Manager</a>
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/view-widgets.php">View Widgets</a>
                                </nav>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Menu Management -->
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseMenuManagement" aria-expanded="false" aria-controls="collapseMenuManagement">
                                <div class="nav-link-icon"><i data-feather="menu"></i></div>
                                Menus
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseMenuManagement" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/menus.php">Manage Menus</a>
                                </nav>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                            <!-- Administration -->
                            <div class="sidenav-menu-heading">Administration</div>
                            
                            <!-- User Management -->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseUserManagement" aria-expanded="false" aria-controls="collapseUserManagement">
                                <div class="nav-link-icon"><i data-feather="users"></i></div>
                                User Management
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseUserManagement" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/users/">Users List</a>
                                    <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/users/create.php">Add User</a>
                                </nav>
                            </div>
                            
                            <!-- Settings -->
                            <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/settings.php">
                                <div class="nav-link-icon"><i data-feather="settings"></i></div>
                                Settings
                            </a>
                        <?php endif; ?>
                        
                        <?php if (is_super_admin()): ?>
                            <!-- Super Admin -->
                            <div class="sidenav-menu-heading">Super Admin</div>
                            <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/campuses/">
                                <div class="nav-link-icon"><i data-feather="home"></i></div>
                                Campuses
                            </a>
                            <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/system/">
                                <div class="nav-link-icon"><i data-feather="server"></i></div>
                                System
                            </a>
                        <?php endif; ?>
                        
                        <!-- Tools -->
                        <div class="sidenav-menu-heading">Tools</div>
                        <?php 
                        $campus_info = get_current_campus();
                        $campus_code = $campus_info['code'] ?? null;
                        
                        if ($campus_code) {
                            // Check if campus public site exists
                            $campus_public_dir = dirname(dirname(__DIR__)) . "/{$campus_code}/public/";
                            if (is_dir($campus_public_dir)) {
                                $site_url = ADMIN_BASE_URL . "/{$campus_code}/public/";
                            } else {
                                $site_url = ADMIN_BASE_URL . "/";
                            }
                        } else {
                            $site_url = ADMIN_BASE_URL . "/";
                        }
                        ?>
                        <a class="nav-link" href="<?php echo $site_url; ?>" target="_blank">
                            <div class="nav-link-icon"><i data-feather="external-link"></i></div>
                            View Site
                        </a>
                        <a class="nav-link" href="<?php echo ADMIN_ROOT; ?>/help.php">
                            <div class="nav-link-icon"><i data-feather="help-circle"></i></div>
                            Help & Support
                        </a>
                    </div>
                </div>
                
                <!-- Sidenav Footer -->
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">Logged in as:</div>
                        <div class="sidenav-footer-title"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                    </div>
                </div>
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div id="layoutSidenav_content">
            <main>
