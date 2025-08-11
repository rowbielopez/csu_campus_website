<?php
/**
 * Admin Dashboard - Main administrative interface
 */

require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/functions/helpers.php';
require_once __DIR__ . '/../core/functions/utilities.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/bootstrap.php';

// Create asset_url function if it doesn't exist
if (!function_exists('asset_url')) {
    function asset_url($path = '') {
        return '/campus_website2/src/assets/' . ltrim($path, '/');
    }
}

$auth = new Auth();
$db = Database::getInstance();
$user = $auth->getCurrentUser();
$current_user = get_logged_in_user(); // For template compatibility
$page_title = 'Dashboard';

// Define campus constants for template compatibility
if (!defined('CAMPUS_NAME')) {
    $campus_id = current_campus_id();
    $campus_info = $auth->getCampusById($campus_id);
    
    if ($campus_info) {
        define('CAMPUS_NAME', $campus_info['name'] ?? 'Campus');
        define('CAMPUS_FULL_NAME', $campus_info['full_name'] ?? 'Campus Website');
        define('CAMPUS_PRIMARY_COLOR', $campus_info['primary_color'] ?? '#1e3a8a');
        define('CAMPUS_SECONDARY_COLOR', $campus_info['secondary_color'] ?? '#3b82f6');
        define('CAMPUS_FAVICON_PATH', '/campus_website2/src/assets/img/favicon.png');
        define('CAMPUS_ENABLE_EVENTS', true);
    } else {
        define('CAMPUS_NAME', 'Admin');
        define('CAMPUS_FULL_NAME', 'CSU Campus CMS');
        define('CAMPUS_PRIMARY_COLOR', '#1e3a8a');
        define('CAMPUS_SECONDARY_COLOR', '#3b82f6');
        define('CAMPUS_FAVICON_PATH', '/campus_website2/src/assets/img/favicon.png');
        define('CAMPUS_ENABLE_EVENTS', true);
    }
}

// Get dashboard statistics
$stats = [];

if (is_super_admin()) {
    // Super admin sees global statistics
    $stats['campuses'] = $db->fetch("SELECT COUNT(*) as count FROM campuses WHERE status = 'active'")['count'];
    $stats['users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 1")['count'];
    $stats['posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts")['count'];
    $stats['pages'] = $db->fetch("SELECT COUNT(*) as count FROM pages")['count'];
    $stats['media'] = $db->fetch("SELECT COUNT(*) as count FROM media WHERE 1")['count'] ?? 0;
    
    // Get campus-wise user counts
    $campus_stats = $db->fetchAll("
        SELECT c.name, COUNT(u.id) as user_count 
        FROM campuses c 
        LEFT JOIN users u ON c.id = u.campus_id 
        WHERE c.status = 'active'
        GROUP BY c.id 
        ORDER BY c.name
    ");
    
} else {
    // Campus admin sees campus-specific statistics
    $campus_id = current_campus_id();
    $stats['users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE campus_id = ? AND status = 1", [$campus_id])['count'];
    $stats['posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE campus_id = ?", [$campus_id])['count'];
    $stats['pages'] = $db->fetch("SELECT COUNT(*) as count FROM pages WHERE campus_id = ?", [$campus_id])['count'];
    $stats['media'] = $db->fetch("SELECT COUNT(*) as count FROM media WHERE campus_id = ?", [$campus_id])['count'] ?? 0;
    $stats['published_posts'] = $db->fetch("SELECT COUNT(*) as count FROM posts WHERE campus_id = ? AND status = 'published'", [$campus_id])['count'];
    
    // Get campus information
    $campus_info = $auth->getCampusById($campus_id);
}

// Get recent activity (last 10 posts/pages)
if (is_super_admin()) {
    $recent_activity = $db->fetchAll("
        SELECT p.title, p.created_at, p.status, u.first_name, u.last_name, c.name as campus_name, 'post' as type
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        JOIN campuses c ON p.campus_id = c.id
        UNION ALL
        SELECT pg.title, pg.created_at, pg.status, u.first_name, u.last_name, c.name as campus_name, 'page' as type
        FROM pages pg
        JOIN users u ON pg.author_id = u.id 
        JOIN campuses c ON pg.campus_id = c.id
        ORDER BY created_at DESC 
        LIMIT 10
    ");
} else {
    $campus_id = current_campus_id();
    $recent_activity = $db->fetchAll("
        SELECT p.title, p.created_at, p.status, u.first_name, u.last_name, 'post' as type
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.campus_id = ?
        UNION ALL
        SELECT pg.title, pg.created_at, pg.status, u.first_name, u.last_name, 'page' as type
        FROM pages pg
        JOIN users u ON pg.author_id = u.id 
        WHERE pg.campus_id = ?
        ORDER BY created_at DESC 
        LIMIT 10
    ", [$campus_id, $campus_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CSU CMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%);
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
            border-left: 4px solid #1e3a8a;
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
            background: rgba(30, 58, 138, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="<?= htmlspecialchars(CAMPUS_FULL_NAME) ?> - Admin Dashboard" />
    <meta name="author" content="CSU IT Department" />
    <title><?= get_page_title($page_title) ?></title>
    
    <!-- SB Admin Pro 2 CSS -->
    <link href="<?= asset_url('css/styles.css') ?>" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="<?= CAMPUS_FAVICON_PATH ?>" />
    
    <!-- Font Awesome -->
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js"></script>
    
    <!-- Campus Theme Colors -->
    <style>
        :root {
            --campus-primary: <?= CAMPUS_PRIMARY_COLOR ?>;
            --campus-secondary: <?= CAMPUS_SECONDARY_COLOR ?>;
        }
        .bg-campus-primary { background-color: var(--campus-primary) !important; }
        .text-campus-primary { color: var(--campus-primary) !important; }
        .btn-campus-primary { 
            background-color: var(--campus-primary); 
            border-color: var(--campus-primary);
            color: <?= get_contrast_color(CAMPUS_PRIMARY_COLOR) ?>;
        }
    </style>
</head>

<body class="nav-fixed">
    <!-- Top Navigation -->
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
        <!-- Sidenav Toggle Button -->
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>
        
        <!-- Navbar Brand -->
        <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="<?= admin_url() ?>">
            <?= htmlspecialchars(CAMPUS_NAME) ?> Admin
        </a>
        
        <!-- Navbar Search -->
        <form class="form-inline me-auto d-none d-lg-block me-3">
            <div class="input-group input-group-joined input-group-solid">
                <input class="form-control pe-0" type="search" placeholder="Search for..." aria-label="Search" />
                <div class="input-group-text">
                    <i data-feather="search"></i>
                </div>
            </div>
        </form>
        
        <!-- Navbar Items -->
        <ul class="navbar-nav align-items-center ms-auto">
            <!-- User Dropdown -->
            <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="img-fluid" src="<?= asset_url('img/illustrations/profiles/profile-1.png') ?>" />
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                    <h6 class="dropdown-header d-flex align-items-center">
                        <img class="dropdown-user-img" src="<?= asset_url('img/illustrations/profiles/profile-1.png') ?>" />
                        <div class="dropdown-user-details">
                            <div class="dropdown-user-details-name"><?= htmlspecialchars($current_user['full_name']) ?></div>
                            <div class="dropdown-user-details-email"><?= htmlspecialchars($current_user['email']) ?></div>
                        </div>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= admin_url('profile.php') ?>">
                        <div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                        Account
                    </a>
                    <a class="dropdown-item" href="<?= admin_url('logout.php') ?>">
                        <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <!-- Side Navigation -->
        <div id="layoutSidenav_nav">
            <nav class="sidenav shadow-right sidenav-light">
                <div class="sidenav-menu">
                    <div class="nav accordion" id="accordionSidenav">
                        <!-- Core -->
                        <div class="sidenav-menu-heading">Core</div>
                        <a class="nav-link active" href="<?= admin_url('dashboard.php') ?>">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            Dashboard
                        </a>
                        
                        <!-- Content Management -->
                        <div class="sidenav-menu-heading">Content</div>
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                            <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                            Pages
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePages" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesMenu">
                                <a class="nav-link" href="<?= admin_url('pages/index.php') ?>">All Pages</a>
                                <a class="nav-link" href="<?= admin_url('pages/create.php') ?>">Add New</a>
                            </nav>
                        </div>
                        
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapsePosts" aria-expanded="false" aria-controls="collapsePosts">
                            <div class="nav-link-icon"><i data-feather="edit-3"></i></div>
                            Posts
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePosts" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= admin_url('posts/index.php') ?>">All Posts</a>
                                <a class="nav-link" href="<?= admin_url('posts/create.php') ?>">Add New</a>
                                <a class="nav-link" href="<?= admin_url('categories/index.php') ?>">Categories</a>
                            </nav>
                        </div>
                        
                        <?php if (CAMPUS_ENABLE_EVENTS): ?>
                        <a class="nav-link" href="<?= admin_url('events/index.php') ?>">
                            <div class="nav-link-icon"><i data-feather="calendar"></i></div>
                            Events
                        </a>
                        <?php endif; ?>
                        
                        <a class="nav-link" href="<?= admin_url('media/index.php') ?>">
                            <div class="nav-link-icon"><i data-feather="image"></i></div>
                            Media Library
                        </a>
                        
                        <!-- Appearance -->
                        <div class="sidenav-menu-heading">Appearance</div>
                        <a class="nav-link" href="<?= admin_url('menus/index.php') ?>">
                            <div class="nav-link-icon"><i data-feather="menu"></i></div>
                            Menus
                        </a>
                        <a class="nav-link" href="<?= admin_url('widgets/index.php') ?>">
                            <div class="nav-link-icon"><i data-feather="grid"></i></div>
                            Widgets
                        </a>
                        <a class="nav-link" href="<?= admin_url('carousel.php') ?>">
                            <div class="nav-link-icon"><i data-feather="image"></i></div>
                            Carousel
                        </a>
                        
                        <!-- Users -->
                        <?php if ($user->hasPermission('manage_users')): ?>
                        <div class="sidenav-menu-heading">Users</div>
                        <a class="nav-link" href="<?= admin_url('users/index.php') ?>">
                            <div class="nav-link-icon"><i data-feather="users"></i></div>
                            All Users
                        </a>
                        <a class="nav-link" href="<?= admin_url('users/create.php') ?>">
                            <div class="nav-link-icon"><i data-feather="user-plus"></i></div>
                            Add New
                        </a>
                        <?php endif; ?>
                        
                        <!-- Settings -->
                        <?php if ($user->hasPermission('manage_settings')): ?>
                        <div class="sidenav-menu-heading">Settings</div>
                        <a class="nav-link" href="<?= admin_url('settings/general.php') ?>">
                            <div class="nav-link-icon"><i data-feather="settings"></i></div>
                            General
                        </a>
                        <a class="nav-link" href="<?= admin_url('settings/campus.php') ?>">
                            <div class="nav-link-icon"><i data-feather="home"></i></div>
                            Campus Settings
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidenav Footer -->
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">Logged in as:</div>
                        <div class="sidenav-footer-title"><?= htmlspecialchars($current_user['full_name']) ?></div>
                    </div>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div id="layoutSidenav_content">
            <main>
                <!-- Header -->
                <header class="page-header page-header-dark bg-campus-primary pb-10">
                    <div class="container-xl px-4">
                        <div class="page-header-content pt-4">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-4">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon"><i data-feather="activity"></i></div>
                                        <?= htmlspecialchars(CAMPUS_NAME) ?> Dashboard
                                    </h1>
                                    <div class="page-header-subtitle">Welcome back, <?= htmlspecialchars($current_user['first_name']) ?>!</div>
                                </div>
                                <div class="col-12 col-xl-auto mt-4">
                                    <div class="input-group input-group-joined border-0" style="width: 16.5rem">
                                        <span class="input-group-text"><i class="text-primary" data-feather="calendar"></i></span>
                                        <input class="form-control ps-0 pointer" id="litepickerRangePlugin" placeholder="Select date range..." />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Main page content -->
                <div class="container-xl px-4 mt-n10">
                    <!-- Flash Messages -->
                    <?= render_flash_messages() ?>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Total Users</div>
                                            <div class="text-lg fw-bold"><?= number_format($stats['users']) ?></div>
                                        </div>
                                        <i class="feather-xl text-white-50" data-feather="users"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between small">
                                    <a class="text-white stretched-link" href="<?= admin_url('users/index.php') ?>">View Details</a>
                                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Total Pages</div>
                                            <div class="text-lg fw-bold"><?= number_format($stats['pages']) ?></div>
                                        </div>
                                        <i class="feather-xl text-white-50" data-feather="file-text"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between small">
                                    <a class="text-white stretched-link" href="<?= admin_url('pages/index.php') ?>">View Details</a>
                                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Total Posts</div>
                                            <div class="text-lg fw-bold"><?= number_format($stats['posts']) ?></div>
                                        </div>
                                        <i class="feather-xl text-white-50" data-feather="edit-3"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between small">
                                    <a class="text-white stretched-link" href="<?= admin_url('posts/index.php') ?>">View Details</a>
                                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Media Files</div>
                                            <div class="text-lg fw-bold"><?= number_format($stats['media']) ?></div>
                                        </div>
                                        <i class="feather-xl text-white-50" data-feather="image"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between small">
                                    <a class="text-white stretched-link" href="<?= admin_url('media/index.php') ?>">View Details</a>
                                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row">
                        <!-- Recent Activity -->
                        <div class="col-xl-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">Recent Activity</div>
                                <div class="card-body">
                                    <?php if (empty($recent_activity)): ?>
                                        <p class="text-muted">No recent activity.</p>
                                    <?php else: ?>
                                        <div class="timeline">
                                            <?php foreach ($recent_activity as $activity): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-marker"></div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-title"><?= htmlspecialchars($activity['action']) ?></h6>
                                                        <p class="timeline-text">
                                                            <?php if ($activity['table_name']): ?>
                                                                on <?= htmlspecialchars($activity['table_name']) ?>
                                                                <?php if ($activity['record_id']): ?>
                                                                    (ID: <?= $activity['record_id'] ?>)
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </p>
                                                        <span class="timeline-date"><?= time_ago($activity['created_at']) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campus Information -->
                        <div class="col-xl-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header">Campus Information</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Campus Name:</strong><br>
                                            <?= htmlspecialchars($campus_info['full_name']) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Domain:</strong><br>
                                            <a href="https://<?= htmlspecialchars($campus_info['domain']) ?>" target="_blank">
                                                <?= htmlspecialchars($campus_info['domain']) ?>
                                            </a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Address:</strong><br>
                                            <?= htmlspecialchars($campus_info['address']) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Contact:</strong><br>
                                            <?= htmlspecialchars($campus_info['email']) ?><br>
                                            <?= htmlspecialchars($campus_info['phone']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">
                            Copyright &copy; <?= date('Y') ?> <?= htmlspecialchars(CAMPUS_FULL_NAME) ?>
                        </div>
                        <div class="col-md-6 text-md-end small">
                            <a href="#!">Privacy Policy</a> &middot; <a href="#!">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset_url('js/scripts.js') ?>"></script>
    <script src="<?= asset_url('js/litepicker.js') ?>"></script>
    
    <script>
        // Initialize date range picker
        window.addEventListener('DOMContentLoaded', event => {
            const litepickerRangePlugin = document.getElementById('litepickerRangePlugin');
            if (litepickerRangePlugin) {
                new Litepicker({
                    element: litepickerRangePlugin,
                    startDate: new Date(),
                    endDate: new Date(),
                    singleMode: false,
                    numberOfColumns: 2,
                    numberOfMonths: 2,
                    format: 'MMM DD, YYYY',
                    plugins: ['ranges'],
                });
            }
        });
    </script>
</body>
</html>
