<?php
if (!defined('ADMIN_LAYOUT')) {
    define('ADMIN_LAYOUT', true);
}

$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$page_title = $page_title ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="<?php echo htmlspecialchars($page_description ?? 'CSU CMS Admin Dashboard'); ?>" />
    <meta name="author" content="CSU CMS Platform" />
    <title><?php echo htmlspecialchars($page_title); ?> - CSU CMS</title>
    
    <!-- SB Admin Pro CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
    <link href="../dist/css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../dist/assets/img/favicon.png" />
    
    <!-- Font Awesome and Feather Icons -->
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
    
    <!-- Custom Campus Styles -->
    <style>
        :root {
            --campus-primary: <?php echo $current_campus['theme_color'] ?? '#069952'; ?>;
            --campus-secondary: <?php echo $current_campus['secondary_color'] ?? '#f59e0b'; ?>;
        }
        
        .navbar-brand {
            color: var(--campus-primary) !important;
        }
        
        .btn-primary {
            background-color: var(--campus-primary);
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
        <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="index.php">
            <i class="fas fa-university me-2"></i>
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
            <!-- Navbar Search Dropdown (Mobile) -->
            <li class="nav-item dropdown no-caret me-3 d-lg-none">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="searchDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--fade-in-up" aria-labelledby="searchDropdown">
                    <form class="form-inline me-auto w-100">
                        <div class="input-group input-group-joined input-group-solid">
                            <input class="form-control pe-0" type="text" placeholder="Search for..." aria-label="Search" />
                            <div class="input-group-text"><i data-feather="search"></i></div>
                        </div>
                    </form>
                </div>
            </li>
            
            <!-- Alerts Dropdown-->
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="bell"></i>
                        Alerts Center
                    </h6>
                    <a class="dropdown-item dropdown-notifications-item" href="#!">
                        <div class="dropdown-notifications-item-icon bg-warning"><i data-feather="activity"></i></div>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-details">Just now</div>
                            <div class="dropdown-notifications-item-content-text">Welcome to the CSU CMS Admin Dashboard!</div>
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
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="mail"></i>
                        Message Center
                    </h6>
                    <a class="dropdown-item dropdown-notifications-item" href="#!">
                        <img class="dropdown-notifications-item-img" src="../dist/assets/img/illustrations/profiles/profile-1.png" />
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">No new messages</div>
                            <div class="dropdown-notifications-item-content-details">System · Now</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-footer" href="#!">Read All Messages</a>
                </div>
            </li>
            
            <!-- User Dropdown-->
            <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="img-fluid" src="../dist/assets/img/illustrations/profiles/profile-1.png" />
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                    <h6 class="dropdown-header d-flex align-items-center">
                        <img class="dropdown-user-img" src="../dist/assets/img/illustrations/profiles/profile-1.png" />
                        <div class="dropdown-user-details">
                            <div class="dropdown-user-details-name"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                            <div class="dropdown-user-details-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                        </div>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="profile.php">
                        <div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                        Account
                    </a>
                    <a class="dropdown-item" href="../logout.php">
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
                        <!-- Sidenav Menu Heading (Account - Mobile Only) -->
                        <div class="sidenav-menu-heading d-sm-none">Account</div>
                        
                        <!-- Mobile Alerts Link -->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="bell"></i></div>
                            Alerts
                            <span class="badge bg-warning-soft text-warning ms-auto">New!</span>
                        </a>
                        
                        <!-- Mobile Messages Link -->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="mail"></i></div>
                            Messages
                        </a>
                        
                        <!-- Core Section -->
                        <div class="sidenav-menu-heading">Core</div>
                        
                        <!-- Dashboard Link -->
                        <a class="nav-link" href="index.php">
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
                                <a class="nav-link" href="posts/">All Posts</a>
                                <a class="nav-link" href="posts/create.php">Create Post</a>
                                <?php if (is_campus_admin() || is_super_admin()): ?>
                                    <a class="nav-link" href="posts/?status=pending">Pending Review</a>
                                <?php endif; ?>
                                <a class="nav-link" href="posts/?author=<?php echo $current_user['id']; ?>">My Posts</a>
                            </nav>
                        </div>
                        
                        <!-- Media Management -->
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseMediaManagement" aria-expanded="false" aria-controls="collapseMediaManagement">
                            <div class="nav-link-icon"><i data-feather="image"></i></div>
                            Media Library
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseMediaManagement" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link" href="media/">Media Library</a>
                                <a class="nav-link" href="media/upload.php">Upload Media</a>
                            </nav>
                        </div>
                        
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
                                    <a class="nav-link" href="users/">Users List</a>
                                    <a class="nav-link" href="users/create.php">Add User</a>
                                </nav>
                            </div>
                            
                            <!-- Settings -->
                            <a class="nav-link" href="settings.php">
                                <div class="nav-link-icon"><i data-feather="settings"></i></div>
                                Settings
                            </a>
                        <?php endif; ?>
                        
                        <?php if (is_super_admin()): ?>
                            <!-- Super Admin -->
                            <div class="sidenav-menu-heading">Super Admin</div>
                            <a class="nav-link" href="campuses/">
                                <div class="nav-link-icon"><i data-feather="home"></i></div>
                                Campuses
                            </a>
                            <a class="nav-link" href="system/">
                                <div class="nav-link-icon"><i data-feather="server"></i></div>
                                System
                            </a>
                        <?php endif; ?>
                        
                        <!-- Tools -->
                        <div class="sidenav-menu-heading">Tools</div>
                        <a class="nav-link" href="../" target="_blank">
                            <div class="nav-link-icon"><i data-feather="external-link"></i></div>
                            View Site
                        </a>
                        <a class="nav-link" href="help.php">
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
