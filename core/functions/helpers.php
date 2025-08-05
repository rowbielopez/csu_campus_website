<?php
/**
 * Template Helper Functions
 * Functions to help with rendering templates and UI components
 */

/**
 * Render template with data
 */
function render_template($template_file, $data = []) {
    // Extract data to variables
    extract($data);
    
    // Start output buffering
    ob_start();
    
    // Include template file
    if (file_exists($template_file)) {
        include $template_file;
    } else {
        echo "Template not found: {$template_file}";
    }
    
    // Return rendered content
    return ob_get_clean();
}

/**
 * Include partial template
 */
function include_partial($partial_name, $data = []) {
    $partial_file = PUBLIC_PATH . 'partials/' . $partial_name . '.php';
    echo render_template($partial_file, $data);
}

/**
 * Get page template
 */
function get_page_template($template_name = 'default') {
    $template_file = PUBLIC_PATH . 'templates/page-' . $template_name . '.php';
    
    if (!file_exists($template_file)) {
        $template_file = PUBLIC_PATH . 'templates/page-default.php';
    }
    
    return $template_file;
}

/**
 * Get post template
 */
function get_post_template($template_name = 'default') {
    $template_file = PUBLIC_PATH . 'templates/post-' . $template_name . '.php';
    
    if (!file_exists($template_file)) {
        $template_file = PUBLIC_PATH . 'templates/post-default.php';
    }
    
    return $template_file;
}

/**
 * Render navigation menu
 */
function render_menu($location = 'primary') {
    global $db;
    
    $sql = "SELECT items FROM menus WHERE campus_id = :campus_id AND location = :location AND status = 1";
    $result = $db->query($sql, ['campus_id' => CAMPUS_ID, 'location' => $location]);
    $menu = $result->fetch();
    
    if (!$menu || !$menu['items']) {
        return '';
    }
    
    $items = json_decode($menu['items'], true);
    
    if (!$items) {
        return '';
    }
    
    return render_menu_items($items);
}

/**
 * Render menu items recursively
 */
function render_menu_items($items, $depth = 0) {
    $html = '';
    $css_class = $depth === 0 ? 'navbar-nav' : 'dropdown-menu';
    
    $html .= "<ul class=\"{$css_class}\">";
    
    foreach ($items as $item) {
        $has_children = !empty($item['children']);
        $item_class = 'nav-item' . ($has_children ? ' dropdown' : '');
        $link_class = 'nav-link' . ($has_children ? ' dropdown-toggle' : '');
        
        $html .= "<li class=\"{$item_class}\">";
        
        if ($has_children) {
            $html .= "<a class=\"{$link_class}\" href=\"#\" role=\"button\" data-bs-toggle=\"dropdown\">";
            $html .= htmlspecialchars($item['title']);
            $html .= "</a>";
            $html .= render_menu_items($item['children'], $depth + 1);
        } else {
            $html .= "<a class=\"{$link_class}\" href=\"" . htmlspecialchars($item['url']) . "\">";
            $html .= htmlspecialchars($item['title']);
            $html .= "</a>";
        }
        
        $html .= "</li>";
    }
    
    $html .= "</ul>";
    
    return $html;
}

/**
 * Render widget
 */
function render_widget($widget_data) {
    $type = $widget_data['type'];
    $settings = json_decode($widget_data['settings'] ?? '{}', true);
    
    switch ($type) {
        case WIDGET_TEXT:
            return render_text_widget($widget_data, $settings);
        case WIDGET_HTML:
            return render_html_widget($widget_data, $settings);
        case WIDGET_IMAGE:
            return render_image_widget($widget_data, $settings);
        case WIDGET_RECENT_POSTS:
            return render_recent_posts_widget($widget_data, $settings);
        default:
            return '';
    }
}

/**
 * Render text widget
 */
function render_text_widget($widget, $settings) {
    $html = '<div class="widget widget-text">';
    
    if ($widget['title']) {
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
    }
    
    $html .= '<div class="widget-content">';
    $html .= nl2br(htmlspecialchars($widget['content']));
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Render HTML widget
 */
function render_html_widget($widget, $settings) {
    $html = '<div class="widget widget-html">';
    
    if ($widget['title']) {
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
    }
    
    $html .= '<div class="widget-content">';
    $html .= $widget['content']; // Allow HTML
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Render image widget
 */
function render_image_widget($widget, $settings) {
    $image_url = $settings['image_url'] ?? '';
    $alt_text = $settings['alt_text'] ?? $widget['title'];
    $link_url = $settings['link_url'] ?? '';
    
    if (!$image_url) {
        return '';
    }
    
    $html = '<div class="widget widget-image">';
    
    if ($widget['title']) {
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
    }
    
    $html .= '<div class="widget-content">';
    
    if ($link_url) {
        $html .= '<a href="' . htmlspecialchars($link_url) . '">';
    }
    
    $html .= '<img src="' . htmlspecialchars($image_url) . '" alt="' . htmlspecialchars($alt_text) . '" class="img-fluid">';
    
    if ($link_url) {
        $html .= '</a>';
    }
    
    if ($widget['content']) {
        $html .= '<p class="mt-2">' . htmlspecialchars($widget['content']) . '</p>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Render recent posts widget
 */
function render_recent_posts_widget($widget, $settings) {
    global $content;
    
    $limit = $settings['limit'] ?? 5;
    $posts = $content->getRecentPosts($limit);
    
    if (empty($posts)) {
        return '';
    }
    
    $html = '<div class="widget widget-recent-posts">';
    
    if ($widget['title']) {
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
    }
    
    $html .= '<div class="widget-content">';
    $html .= '<ul class="list-unstyled">';
    
    foreach ($posts as $post) {
        $html .= '<li class="mb-3">';
        $html .= '<a href="' . campus_url('post/' . $post['slug']) . '" class="text-decoration-none">';
        $html .= '<h6 class="mb-1">' . htmlspecialchars($post['title']) . '</h6>';
        $html .= '</a>';
        $html .= '<small class="text-muted">' . format_date($post['published_at']) . '</small>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Render sidebar widgets
 */
function render_sidebar($location = 'sidebar') {
    global $db;
    
    $sql = "SELECT * FROM widgets WHERE campus_id = :campus_id AND location = :location AND status = 1 ORDER BY sort_order";
    $result = $db->query($sql, ['campus_id' => CAMPUS_ID, 'location' => $location]);
    $widgets = $result->fetchAll();
    
    $html = '';
    
    foreach ($widgets as $widget) {
        $html .= render_widget($widget);
    }
    
    return $html;
}

/**
 * Render flash messages
 */
function render_flash_messages() {
    $messages = get_flash_messages();
    
    if (empty($messages)) {
        return '';
    }
    
    $html = '';
    
    foreach ($messages as $message) {
        $type = $message['type'];
        $text = $message['message'];
        
        // Map message types to Bootstrap alert classes
        $alert_class = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-info'
        };
        
        $html .= '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        $html .= htmlspecialchars($text);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Render pagination
 */
function render_pagination($pagination) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['has_previous']) {
        $prev_url = $pagination['pages'][0]['url'] ?? '#';
        $prev_url = str_replace('page=' . $pagination['current_page'], 'page=' . $pagination['previous_page'], $prev_url);
        $html .= '<li class="page-item"><a class="page-link" href="' . $prev_url . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    foreach ($pagination['pages'] as $page) {
        if ($page['page'] === '...') {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } elseif ($page['is_current']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $page['page'] . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $page['url'] . '">' . $page['page'] . '</a></li>';
        }
    }
    
    // Next button
    if ($pagination['has_next']) {
        $next_url = $pagination['pages'][0]['url'] ?? '#';
        $next_url = str_replace('page=' . $pagination['current_page'], 'page=' . $pagination['next_page'], $next_url);
        $html .= '<li class="page-item"><a class="page-link" href="' . $next_url . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Get current page title
 */
function get_page_title($custom_title = null) {
    $site_title = get_campus_setting('site_title', CAMPUS_FULL_NAME);
    $separator = ' - ';
    
    if ($custom_title) {
        return $custom_title . $separator . $site_title;
    }
    
    return $site_title;
}

/**
 * Get page meta description
 */
function get_page_description($custom_description = null) {
    if ($custom_description) {
        return $custom_description;
    }
    
    return get_campus_setting('site_tagline', 'Excellence in Education');
}
?>
