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

// Get header widgets
$header_widgets = get_campus_widgets('header');

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
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Oswald:wght@200..700&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">    <!-- Custom CSS -->
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
        
        /* Global Font Styles */
        body {
            font-family: 'Nunito Sans', sans-serif;
        }
        
        /* Post Title Styles */
        .post-title, 
        .hero-title,
        h1.post-title,
        .card-title a,
        .post-card .card-title a {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
        }
        
        /* Post Content Styles */
        .post-content,
        .post-excerpt,
        .card-text,
        .post-body,
        article .content {
            font-family: 'Roboto', sans-serif;
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
        
        /* Rounded box styling for image and text widgets */
        .widget-rounded-box {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .widget-rounded-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .widget-rounded-box .widget-content {
            margin: 0 !important;
            padding: 0;
        }
        
        .widget-rounded-box img {
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        
        .widget-rounded-box img:hover {
            transform: scale(1.02);
        }
        
        /* Hide titles for image and text widgets - backup CSS rule */
        .widget-image-widget .widget-title,
        .widget-image .widget-title,
        .widget-text-widget .widget-title,
        .widget[class*="image"] .widget-title,
        .widget-rounded-box .widget-title {
            display: none !important;
        }
        
        /* Remove extra spacing when title is hidden */
        .widget-image-widget .widget-content,
        .widget-image .widget-content,
        .widget-text-widget .widget-content,
        .widget[class*="image"] .widget-content {
            margin-top: 0 !important;
        }
        .widget[class*="image"] .widget-content {
            margin-top: 0 !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--campus-primary) !important;
        }
        
        /* Custom navbar styling */
        .navbar {
            background-color: var(--campus-primary) !important;
            border-bottom: 3px solid var(--campus-secondary);
        }
        
        .navbar-brand, .navbar-brand:hover {
            color: white !important;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
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
        
        /* Modern Carousel Styles */
        .carousel-section {
            position: relative;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .carousel.slide {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .carousel-image-wrapper {
            position: relative;
            height: 534px;
            max-width: 1904px;
            margin: 0 auto;
            overflow: hidden;
        }
        
        .carousel-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 8s ease-in-out;
        }
        
        .carousel-item.active .carousel-image-wrapper img {
            transform: scale(1.05);
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                45deg,
                rgba(0, 0, 0, 0.7) 0%,
                rgba(0, 0, 0, 0.3) 50%,
                rgba(0, 0, 0, 0.7) 100%
            );
        }
        
        .carousel-caption {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            z-index: 3;
            text-align: center;
            padding: 2rem 0;
        }
        
        .carousel-caption h2 {
            font-family: 'Oswald', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            margin-bottom: 1rem;
        }
        
        .carousel-caption p {
            font-family: 'Roboto', sans-serif;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
            font-size: 1.1rem;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.5);
            opacity: 0.9;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .carousel-control-prev {
            left: 20px;
        }
        
        .carousel-control-next {
            right: 20px;
        }
        
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background-color: rgba(255, 255, 255, 0.7);
            border-color: rgba(255, 255, 255, 0.6);
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }
        
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 20px;
            height: 20px;
        }
        
        /* Smooth slide transitions */
        .carousel-item {
            transition: transform 0.8s ease-in-out;
        }
        
        @media (max-width: 768px) {
            .carousel-section {
                padding: 2rem 0;
            }
            
            .carousel-image-wrapper {
                height: 300px; /* Scaled down for mobile */
                max-width: 100%;
            }
            
            .carousel-caption h2 {
                font-size: 1.8rem;
            }
            
            .carousel-caption p {
                font-size: 0.95rem;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 40px;
                height: 40px;
            }
            
            .carousel-control-prev {
                left: 10px;
            }
            
            .carousel-control-next {
                right: 10px;
            }
        }
        
        @media (max-width: 1920px) {
            .carousel-image-wrapper {
                height: auto;
                aspect-ratio: 1904 / 534; /* Maintain exact aspect ratio */
            }
        }
    </style>
    
    <!-- Additional CSS from page -->
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="../../public/img/Cagayan State University - Logo.png" alt="CSU Logo" height="40" class="d-inline-block align-text-top me-2">
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($campus['name']); ?></div>
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
    
    <!-- Header Widgets -->
    <?php if (!empty($header_widgets)): ?>
        <section class="header-widgets bg-light py-3">
            <div class="container">
                <div class="row">
                    <?php foreach ($header_widgets as $widget): ?>
                        <div class="col-md-6 col-lg-4 mb-2">
                            <?php echo render_widget($widget); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
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
