<?php
/**
 * Frontend Helper Functions
 * Shared functions for campus frontend rendering
 */

/**
 * Get current campus configuration for frontend
 */
function get_campus_config() {
    static $campus_config = null;
    
    if ($campus_config === null) {
        // Get campus from config or detect from path
        if (defined('CURRENT_CAMPUS_ID')) {
            $campus_id = CURRENT_CAMPUS_ID;
        } else {
            // Try to detect from current path
            $script_path = $_SERVER['SCRIPT_NAME'];
            $path_parts = explode('/', trim($script_path, '/'));
            
            // Look for campus slug in path (e.g., /campus_website2/andrews/public/...)
            $campus_slug = 'andrews'; // default
            foreach ($path_parts as $part) {
                if (in_array($part, ['andrews', 'carig', 'aparri', 'gonzaga', 'lallo', 'lasam', 'piat', 'sanchezmira', 'solana'])) {
                    $campus_slug = $part;
                    break;
                }
            }
            
            $db = Database::getInstance();
            $campus = $db->fetch("SELECT * FROM campuses WHERE slug = ?", [$campus_slug]);
            $campus_id = $campus ? $campus['id'] : 1; // Default to Andrews
        }
        
        $db = Database::getInstance();
        $campus_config = $db->fetch("SELECT * FROM campuses WHERE id = ?", [$campus_id]);
        
        if ($campus_config) {
            // Parse settings JSON
            $campus_config['settings'] = json_decode($campus_config['settings'] ?? '{}', true);
        }
    }
    
    return $campus_config;
}

/**
 * Get campus-specific setting
 */
function get_campus_setting($key, $default = null) {
    $campus = get_campus_config();
    
    if (!$campus) {
        return $default;
    }
    
    // Check direct campus field first
    if (isset($campus[$key])) {
        return $campus[$key];
    }
    
    // Check in settings JSON
    if (isset($campus['settings'][$key])) {
        return $campus['settings'][$key];
    }
    
    return $default;
}

/**
 * Get published posts for current campus
 */
function get_campus_posts($limit = 10, $offset = 0, $featured_only = false) {
    $campus = get_campus_config();
    if (!$campus) return [];
    
    $db = Database::getInstance();
    
    $where_conditions = [
        "p.campus_id = ?",
        "p.status = 'published'"
    ];
    $params = [$campus['id']];
    
    if ($featured_only) {
        $where_conditions[] = "p.is_featured = 1";
    }
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT p.*, u.username as author_name, u.first_name, u.last_name
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id
        $where_clause 
        ORDER BY p.published_at DESC, p.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    return $db->fetchAll($sql, $params);
}

/**
 * Get single post by ID or slug
 */
function get_campus_post($identifier, $by_slug = false) {
    $campus = get_campus_config();
    if (!$campus) return null;
    
    $db = Database::getInstance();
    
    $field = $by_slug ? 'p.slug' : 'p.id';
    $sql = "
        SELECT p.*, u.username as author_name, u.first_name, u.last_name
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id
        WHERE $field = ? AND p.campus_id = ? AND p.status = 'published'
    ";
    
    return $db->fetch($sql, [$identifier, $campus['id']]);
}

/**
 * Get campus menu items
 */
function get_campus_menu($menu_location = 'main') {
    $campus = get_campus_config();
    if (!$campus) return [];
    
    $db = Database::getInstance();
    
    // Get menu
    $menu = $db->fetch("
        SELECT * FROM menus 
        WHERE campus_id = ? AND location = ? AND is_active = 1
    ", [$campus['id'], $menu_location]);
    
    if (!$menu) return [];
    
    // Get menu items
    $items = $db->fetchAll("
        SELECT * FROM menu_items 
        WHERE menu_id = ? AND is_visible = 1 
        ORDER BY sort_order ASC
    ", [$menu['id']]);
    
    return build_menu_tree($items);
}

/**
 * Build hierarchical menu tree
 */
function build_menu_tree($items, $parent_id = null) {
    $tree = [];
    
    foreach ($items as $item) {
        if ($item['parent_id'] == $parent_id) {
            $item['children'] = build_menu_tree($items, $item['id']);
            $tree[] = $item;
        }
    }
    
    return $tree;
}

/**
 * Render menu HTML
 */
function render_menu($items, $class = 'navbar-nav', $depth = 0) {
    if (empty($items)) return '';
    
    $html = '';
    $ul_class = $depth === 0 ? $class : 'dropdown-menu';
    
    if ($depth === 0) {
        $html .= "<ul class=\"$ul_class\">";
    }
    
    foreach ($items as $item) {
        $has_children = !empty($item['children']);
        $li_class = $has_children ? 'nav-item dropdown' : 'nav-item';
        $a_class = $has_children ? 'nav-link dropdown-toggle' : 'nav-link';
        
        if ($depth > 0) {
            $li_class = $has_children ? 'dropdown-submenu' : '';
            $a_class = $has_children ? 'dropdown-item dropdown-toggle' : 'dropdown-item';
        }
        
        $attributes = '';
        if ($has_children) {
            $attributes = 'data-bs-toggle="dropdown" aria-expanded="false"';
        }
        
        $url = $item['url'] ?: '#';
        if (isset($item['type']) && $item['type'] === 'post' && isset($item['post_id']) && $item['post_id']) {
            $post = get_campus_post($item['post_id']);
            $url = $post ? "post.php?slug=" . $post['slug'] : '#';
        }
        
        $target = $item['target'] ? "target=\"{$item['target']}\"" : '';
        
        $html .= "<li class=\"$li_class\">";
        $html .= "<a href=\"$url\" class=\"$a_class\" $attributes $target>";
        $html .= htmlspecialchars($item['title']);
        $html .= "</a>";
        
        if ($has_children) {
            $html .= "<ul class=\"dropdown-menu\">";
            $html .= render_menu($item['children'], '', $depth + 1);
            $html .= "</ul>";
        }
        
        $html .= "</li>";
    }
    
    if ($depth === 0) {
        $html .= "</ul>";
    }
    
    return $html;
}

/**
 * Get campus widgets by area
 */
function get_campus_widgets($area = 'sidebar') {
    $campus = get_campus_config();
    if (!$campus) return [];
    
    $db = Database::getInstance();
    
    return $db->fetchAll("
        SELECT * FROM widgets 
        WHERE campus_id = ? AND area = ? AND is_active = 1 
        ORDER BY sort_order ASC
    ", [$campus['id'], $area]);
}

/**
 * Render widget HTML
 */
function render_widget($widget) {
    $content = '';
    
    switch ($widget['type']) {
        case 'html':
            $content = $widget['content'];
            break;
            
        case 'recent_posts':
            $posts = get_campus_posts(5);
            $content = '<ul class="list-unstyled">';
            foreach ($posts as $post) {
                $content .= '<li class="mb-2">';
                $content .= '<a href="post.php?slug=' . $post['slug'] . '" class="text-decoration-none">';
                $content .= htmlspecialchars($post['title']);
                $content .= '</a>';
                $content .= '<small class="text-muted d-block">' . date('M j, Y', strtotime($post['published_at'])) . '</small>';
                $content .= '</li>';
            }
            $content .= '</ul>';
            break;
            
        case 'contact_info':
            $campus = get_campus_config();
            $content = '<div class="contact-widget">';
            $content .= '<p><strong>' . htmlspecialchars($campus['full_name']) . '</strong></p>';
            $content .= '<p>' . htmlspecialchars($campus['address']) . '</p>';
            $content .= '<p>Email: <a href="mailto:' . $campus['contact_email'] . '">' . $campus['contact_email'] . '</a></p>';
            $content .= '</div>';
            break;
            
        default:
            $content = $widget['content'];
    }
    
    if (!$content) return '';
    
    $html = '<div class="widget widget-' . $widget['type'] . ' mb-4">';
    if ($widget['title']) {
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
    }
    $html .= '<div class="widget-content">' . $content . '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate SEO meta tags
 */
function get_seo_meta($title = null, $description = null, $image = null) {
    $campus = get_campus_config();
    if (!$campus) return '';
    
    $site_title = $title ? $title . ' - ' . $campus['seo_title'] : $campus['seo_title'];
    $site_description = $description ?: $campus['seo_description'];
    
    $meta = '';
    $meta .= '<title>' . htmlspecialchars($site_title) . '</title>' . "\n";
    $meta .= '<meta name="description" content="' . htmlspecialchars($site_description) . '">' . "\n";
    
    // Open Graph tags
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($site_title) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($site_description) . '">' . "\n";
    $meta .= '<meta property="og:type" content="website">' . "\n";
    $meta .= '<meta property="og:url" content="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">' . "\n";
    
    if ($image) {
        $meta .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
    }
    
    return $meta;
}

/**
 * Format excerpt
 */
function get_excerpt($content, $length = 150) {
    $text = strip_tags($content);
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $last_space = strrpos($text, ' ');
    if ($last_space !== false) {
        $text = substr($text, 0, $last_space);
    }
    
    return $text . '...';
}

/**
 * Generate breadcrumbs
 */
function get_breadcrumbs($items = []) {
    $campus = get_campus_config();
    if (!$campus) return '';
    
    $breadcrumbs = [
        ['title' => 'Home', 'url' => 'index.php']
    ];
    
    $breadcrumbs = array_merge($breadcrumbs, $items);
    
    $html = '<nav aria-label="breadcrumb">';
    $html .= '<ol class="breadcrumb">';
    
    $total = count($breadcrumbs);
    foreach ($breadcrumbs as $index => $item) {
        $is_last = ($index + 1) === $total;
        
        if ($is_last) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</li>';
        } else {
            $html .= '<li class="breadcrumb-item">';
            $html .= '<a href="' . $item['url'] . '">' . htmlspecialchars($item['title']) . '</a>';
            $html .= '</li>';
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Pagination helper
 */
function render_pagination($current_page, $total_pages, $url_pattern = '?page=%d') {
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_url = sprintf($url_pattern, $current_page - 1);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $prev_url . '">&laquo; Previous</a>';
        $html .= '</li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, 1) . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current_page ? ' active' : '';
        $page_url = sprintf($url_pattern, $i);
        $html .= '<li class="page-item' . $active . '">';
        $html .= '<a class="page-link" href="' . $page_url . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $total_pages) . '">' . $total_pages . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_url = sprintf($url_pattern, $current_page + 1);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $next_url . '">Next &raquo;</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}
