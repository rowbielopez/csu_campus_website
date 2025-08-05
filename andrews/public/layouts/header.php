<?php
/**
 * Frontend Header Layout
 * Dynamic header with campus-specific branding
 */

// Load core functions
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../core/functions/frontend.php';

// Get campus configuration
$campus = get_campus_config();
$page_title = $page_title ?? $campus['seo_title'];
$page_description = $page_description ?? $campus['seo_description'];

// Cache campus config in session for performance
if (!isset($_SESSION['campus_config'])) {
    $_SESSION['campus_config'] = $campus;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <?php echo get_seo_meta($page_title, $page_description); ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../admin/assets/img/favicon.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --campus-primary: <?php echo $campus['theme_color']; ?>;
            --campus-secondary: <?php echo $campus['secondary_color']; ?>;
            --campus-primary-rgb: <?php 
                $hex = str_replace('#', '', $campus['theme_color']);
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                echo "$r, $g, $b";
            ?>;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--campus-primary) !important;
        }
        
        .btn-primary {
            background-color: var(--campus-primary);
            border-color: var(--campus-primary);
        }
        
        .btn-primary:hover {
            background-color: rgba(var(--campus-primary-rgb), 0.8);
            border-color: rgba(var(--campus-primary-rgb), 0.8);
        }
        
        .text-primary {
            color: var(--campus-primary) !important;
        }
        
        .bg-primary {
            background-color: var(--campus-primary) !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--campus-primary), var(--campus-secondary));
            color: white;
            padding: 4rem 0;
        }
        
        .post-card {
            transition: transform 0.2s ease-in-out;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .footer {
            background-color: #f8f9fa;
            border-top: 3px solid var(--campus-primary);
        }
        
        .widget-title {
            color: var(--campus-primary);
            border-bottom: 2px solid var(--campus-primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--campus-primary) !important;
        }
        
        .dropdown-menu {
            border-top: 3px solid var(--campus-primary);
        }
        
        .breadcrumb-item.active {
            color: var(--campus-primary);
        }
        
        .pagination .page-link {
            color: var(--campus-primary);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--campus-primary);
            border-color: var(--campus-primary);
        }
    </style>
    
    <!-- Additional CSS from page -->
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="me-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px; background-color: var(--campus-primary); color: white; font-weight: bold;">
                        <?php echo strtoupper(substr($campus['code'], 0, 2)); ?>
                    </div>
                </div>
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($campus['name']); ?></div>
                    <small class="text-muted">Cagayan State University</small>
                </div>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <?php
                    $menu_items = get_campus_menu('main');
                    if (empty($menu_items)) {
                        // Default menu if no custom menu is set
                        $default_menu = [
                            ['title' => 'Home', 'url' => 'index.php', 'children' => []],
                            ['title' => 'Posts', 'url' => 'posts.php', 'children' => []],
                            ['title' => 'About', 'url' => 'about.php', 'children' => []],
                            ['title' => 'Contact', 'url' => 'contact.php', 'children' => []]
                        ];
                        echo render_menu($default_menu);
                    } else {
                        echo render_menu($menu_items);
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
