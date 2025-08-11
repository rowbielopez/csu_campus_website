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
        SELECT cw.*, wt.name as type_name 
        FROM campus_widgets cw
        LEFT JOIN widget_types wt ON cw.widget_type_id = wt.id
        WHERE cw.campus_id = ? AND cw.position = ? AND cw.is_active = 1 
        ORDER BY cw.sort_order ASC
    ", [$campus['id'], $area]);
}

/**
 * Render widget HTML
 */
function render_widget($widget) {
    // Try to use template file first
    $template_path = $widget['template_path'] ?? '';
    $widget_dir = __DIR__ . '/../../templates/widgets/';
    
    // Check if template file exists
    if ($template_path && file_exists($widget_dir . basename($template_path))) {
        ob_start();
        try {
            // Make widget data available in template
            $config = json_decode($widget['config'] ?? '{}', true);
            
            // Include required dependencies
            if (!class_exists('Database')) {
                require_once __DIR__ . '/../../config/database.php';
            }
            if (!function_exists('current_campus_id')) {
                require_once __DIR__ . '/auth.php';
            }
            
            include $widget_dir . basename($template_path);
            return ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            error_log("Widget template error: " . $e->getMessage());
            // Fall through to legacy rendering
        }
    }
    
    // Legacy rendering for backward compatibility
    $content = '';
    $config = json_decode($widget['config'] ?? '{}', true);
    $type_name = $widget['type_name'] ?? 'Unknown';
    
    switch ($type_name) {
        case 'Text Widget':
            // Check if content contains HTML tags, if so render as HTML, otherwise escape
            $raw_content = $config['content'] ?? '';
            if (empty($raw_content)) {
                // Debug info for administrators
                $debug_info = '';
                if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                    $debug_info = '<div class="alert alert-info mt-2"><small><strong>Debug:</strong> Config JSON: ' . htmlspecialchars(json_encode($config)) . '</small></div>';
                }
                $content = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>No content configured for this text widget. <a href="/campus_website2/admin/widgets.php">Configure widget content</a></div>' . $debug_info;
            } else if (strip_tags($raw_content) !== $raw_content) {
                // Content contains HTML tags, render as HTML (keep existing formatting)
                $content = $raw_content;
            } else {
                // Plain text content, apply news title styling: bold, centered, black
                $escaped_content = nl2br(htmlspecialchars($raw_content));
                $content = "<div class='text-center' style='font-family: \"Roboto Black\", sans-serif; font-weight: 900; color: black;'>{$escaped_content}</div>";
            }
            break;
            
        case 'Image':
        case 'Image Widget':
            if (!empty($config['image_url'])) {
                $alt = htmlspecialchars($config['alt_text'] ?? $config['image_alt'] ?? 'Widget Image');
                $caption = htmlspecialchars($config['caption'] ?? '');
                $link = $config['link_url'] ?? $config['image_redirect_url'] ?? '';
                
                $content = "<img src='{$config['image_url']}' alt='{$alt}' class='img-fluid rounded'>";
                
                if ($link) {
                    // Always open external links in new tab, and internal links in same tab
                    $isExternal = (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0 || !empty($config['external_image']));
                    $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $content = "<a href='{$link}'{$target}>{$content}</a>";
                }
                
                if ($caption) {
                    $content .= "<p class='image-caption mt-2'>{$caption}</p>";
                }
            } else {
                $content = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>No image configured for this widget. <a href="/campus_website2/admin/widgets.php">Configure widget image</a></div>';
            }
            break;
            
        case 'Image & Hyperlink Text Widget':
            // Use the template file for this widget type
            $template_path = __DIR__ . '/../../templates/widgets/image_hyperlink.php';
            if (file_exists($template_path)) {
                ob_start();
                include $template_path;
                $content = ob_get_clean();
            } else {
                $content = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>Widget template not found. <a href="/campus_website2/admin/widgets.php">Configure widget</a></div>';
            }
            break;
            
        case 'Featured Post Widget':
            // Enhanced featured post with col-4/col-8 layout
            $post_id = $config['post_id'] ?? '';
            $show_excerpt = $config['show_excerpt'] ?? true;
            $show_author = $config['show_author'] ?? true;
            $show_image = $config['show_image'] ?? true;
            $show_date = $config['show_date'] ?? true;
            
            // Get specific post or latest featured post
            $posts = [];
            if (!empty($post_id)) {
                // Get specific post by ID
                $db = Database::getInstance();
                $post = $db->fetch("
                    SELECT p.*, 
                           u.first_name, u.last_name,
                           CONCAT(u.first_name, ' ', u.last_name) as author_name,
                           c.name as category_name
                    FROM posts p 
                    LEFT JOIN users u ON p.author_id = u.id 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.id = ? AND p.status = 'published' AND p.campus_id = ?
                ", [$post_id, current_campus_id()]);
                if ($post) $posts = [$post];
            } else {
                $posts = get_campus_posts(1, 0, true); // Get latest featured post
                if (empty($posts)) {
                    $posts = get_campus_posts(1); // Fallback to latest post
                }
            }
            
            if (!empty($posts)) {
                $post = $posts[0];
                $content = '<article class="featured-post-widget card">';
                $content .= '<div class="card-body">';
                $content .= '<div class="row">';
                
                // Col-md-6 for image (LEFT SIDE)
                if ($show_image && !empty($post['featured_image_url'])) {
                    $content .= '<div class="col-md-6">';
                    $content .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" ';
                    $content .= 'alt="' . htmlspecialchars($post['title']) . '" ';
                    $content .= 'class="img-fluid rounded" loading="lazy">';
                    $content .= '</div>';
                }
                
                // Col-md-6 for content (RIGHT SIDE)
                $content .= '<div class="col-md-6">';
                
                // Title
                $content .= '<h5 class="card-title mb-2" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">';
                $content .= '<a href="post.php?slug=' . htmlspecialchars($post['slug']) . '" class="text-decoration-none text-dark">';
                $content .= htmlspecialchars($post['title']);
                $content .= '</a></h5>';
                
                // Meta information
                if ($show_author || $show_date) {
                    $content .= '<div class="text-muted small mb-2">';
                    if ($show_author && !empty($post['author_name'])) {
                        $content .= '<i class="fas fa-user me-1"></i>' . htmlspecialchars($post['author_name']);
                    }
                    if ($show_author && $show_date) {
                        $content .= ' • ';
                    }
                    if ($show_date) {
                        $post_date = $post['published_at'] ? $post['published_at'] : $post['created_at'];
                        $content .= '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($post_date));
                    }
                    $content .= '</div>';
                }
                
                // Excerpt
                if ($show_excerpt) {
                    $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : get_excerpt($post['content'], 100);
                    $content .= '<p class="card-text small mb-2">' . htmlspecialchars($excerpt) . '</p>';
                }
                
                // Read More button
                $content .= '<a href="post.php?slug=' . htmlspecialchars($post['slug']) . '" class="btn btn-primary btn-sm">';
                $content .= 'Read More <i class="fas fa-arrow-right ms-1"></i></a>';
                
                $content .= '</div>'; // Close content col
                $content .= '</div>'; // Close row
                $content .= '</div>'; // Close card-body
                $content .= '</article>'; // Close article
            } else {
                $content = '<div class="alert alert-info">No featured posts available</div>';
            }
            break;
            
        case 'Recent Posts Widget':
            // Enhanced recent posts with col-md-6/col-md-6 layout (consistent with other widgets)
            $count = $config['count'] ?? 5;
            $show_excerpt = $config['show_excerpt'] ?? true;
            $show_author = $config['show_author'] ?? true;
            $show_image = $config['show_image'] ?? true;
            $show_date = $config['show_date'] ?? true;
            
            // Get posts specifically assigned to THIS widget instance
            $posts = get_widget_posts($widget['id'], $config);
            
            if (!empty($posts)) {
                $content = '<div class="recent-posts-list">';
                foreach ($posts as $index => $post) {
                    $content .= '<article class="recent-post-item card mb-3">';
                    $content .= '<div class="card-body">';
                    $content .= '<div class="row">';
                    
                    // Col-md-6 for image (LEFT SIDE)
                    if ($show_image && !empty($post['featured_image_url'])) {
                        $content .= '<div class="col-md-4">';
                        $content .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" ';
                        $content .= 'alt="' . htmlspecialchars($post['title']) . '" ';
                        $content .= 'class="img-fluid rounded">';
                        $content .= '</div>';
                    }
                    
                    // Col-md-6 for content (RIGHT SIDE)
                    $content .= '<div class="col-md-8">';
                    
                    // Title
                    $content .= '<h6 class="card-title mb-1" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">';
                    $content .= '<a href="post.php?slug=' . htmlspecialchars($post['slug']) . '" class="text-decoration-none text-dark">';
                    $content .= htmlspecialchars($post['title']);
                    $content .= '</a></h6>';
                    
                    // Meta information
                    if ($show_author || $show_date) {
                        $content .= '<div class="text-muted small mb-2">';
                        if ($show_author && !empty($post['author_name'])) {
                            $content .= '<i class="fas fa-user me-1"></i>' . htmlspecialchars($post['author_name']);
                        }
                        if ($show_author && $show_date) {
                            $content .= ' • ';
                        }
                        if ($show_date) {
                            $post_date = $post['published_at'] ? $post['published_at'] : $post['created_at'];
                            $content .= '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($post_date));
                        }
                        $content .= '</div>';
                    }
                    
                    // Excerpt
                    if ($show_excerpt) {
                        $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : get_excerpt($post['content'], 100);
                        $content .= '<p class="card-text small">' . htmlspecialchars($excerpt) . '</p>';
                    }
                    
                    $content .= '</div>'; // Close content col
                    
                    $content .= '</div>'; // Close row
                    $content .= '</div>'; // Close card-body
                    $content .= '</article>'; // Close article
                }
                $content .= '</div>';
            } else {
                $content = '<div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>No posts are assigned to this specific widget instance. <a href="/campus_website2/admin/posts/create.php">Create posts</a> and assign them to this widget, or <a href="/campus_website2/admin/widgets.php">configure this widget</a> to show recent posts automatically.</div>';
            }
            break;
            
        case 'Contact Info':
            $campus = get_campus_config();
            $content = '<div class="contact-widget">';
            $content .= '<p><strong>' . htmlspecialchars($campus['full_name']) . '</strong></p>';
            $content .= '<p>' . htmlspecialchars($campus['address']) . '</p>';
            $content .= '<p>Email: <a href="mailto:' . $campus['contact_email'] . '">' . $campus['contact_email'] . '</a></p>';
            $content .= '</div>';
            break;
            
        case 'HTML':
            $content = $config['content'] ?? 'No content specified.';
            break;
            
        case 'Content Widget':
        case 'Media Widget':
        case 'Link Widget':
        case 'News Ticker':
            // Handle both image and text content for Media Widget
            if (!empty($config['image_url'])) {
                // Render image content
                $alt = htmlspecialchars($config['alt_text'] ?? $config['image_alt'] ?? 'Widget Image');
                $caption = htmlspecialchars($config['caption'] ?? '');
                $link = $config['link_url'] ?? $config['image_redirect_url'] ?? '';
                
                $content = "<img src='{$config['image_url']}' alt='{$alt}' class='img-fluid rounded'>";
                
                if ($link) {
                    // Always open external links in new tab, and internal links in same tab
                    $isExternal = (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0 || !empty($config['external_image']));
                    $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $content = "<a href='{$link}'{$target}>{$content}</a>";
                }
                
                if ($caption) {
                    $content .= "<p class='image-caption mt-2'>{$caption}</p>";
                }
            } elseif (!empty($config['text_content'])) {
                // Render text content with styling
                $text_content = $config['text_content'];
                $text_link = $config['text_redirect_url'] ?? '';
                
                // Check if content contains HTML tags, if so render as HTML, otherwise escape
                if (strip_tags($text_content) !== $text_content) {
                    // Content contains HTML tags, render as HTML
                    $text_html = $text_content;
                } else {
                    // Plain text content, escape and add line breaks
                    $text_html = nl2br(htmlspecialchars($text_content));
                }
                
                // Apply styling: bold, centered, black color, news title font
                $styled_text = "<div class='text-center' style='font-family: \"Roboto Black\", sans-serif; font-weight: 900; color: black;'>{$text_html}</div>";
                
                // Add link to entire div if provided
                if ($text_link) {
                    $isExternal = (strpos($text_link, 'http://') === 0 || strpos($text_link, 'https://') === 0 || !empty($config['external_text']));
                    $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                    $content = "<a href='{$text_link}'{$target} style='text-decoration: none; color: inherit; display: block;'>{$styled_text}</a>";
                } else {
                    $content = $styled_text;
                }
            } elseif (!empty($config['content'])) {
                // Fallback for widgets that might use 'content' instead of 'text_content'
                $raw_content = $config['content'];
                if (strip_tags($raw_content) !== $raw_content) {
                    $content = $raw_content;
                } else {
                    $content = nl2br(htmlspecialchars($raw_content));
                }
            } else {
                // No image or text content, try posts as last resort
                $assigned_posts = get_widget_posts($widget['id'], $config);
                if (!empty($assigned_posts)) {
                    $content = render_widget_posts($assigned_posts, $widget['type_name'], $config);
                } else {
                    $content = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>No content configured for this widget. <a href="/campus_website2/admin/widgets.php">Configure widget content</a></div>';
                }
            }
            break;
            
        case 'Featured Posts':
            // Enhanced featured posts display with col-md-6/col-md-6 layout (consistent with other widgets)
            $count = $config['count'] ?? 3;
            $show_excerpt = $config['show_excerpt'] ?? true;
            $show_author = $config['show_author'] ?? true;
            $show_image = $config['show_image'] ?? true;
            $show_date = $config['show_date'] ?? true;
            
            // Get posts specifically assigned to THIS widget instance
            $assigned_posts = get_widget_posts($widget['id'], $config);
            
            if (!empty($assigned_posts)) {
                $content = '<div class="featured-posts-list">';
                foreach (array_slice($assigned_posts, 0, $count) as $index => $post) {
                    $content .= '<article class="featured-post-item card mb-3">';
                    $content .= '<div class="card-body">';
                    $content .= '<div class="row">';
                    
                    // Col-md-6 for image (LEFT SIDE)
                    if ($show_image && !empty($post['featured_image_url'])) {
                        $content .= '<div class="col-md-6">';
                        $content .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" ';
                        $content .= 'alt="' . htmlspecialchars($post['title']) . '" ';
                        $content .= 'class="img-fluid rounded">';
                        $content .= '</div>';
                    }
                    
                    // Col-md-6 for content (RIGHT SIDE)
                    $content .= '<div class="col-md-6">';
                    
                    // Title
                    $content .= '<h6 class="card-title mb-1" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">';
                    $content .= '<a href="post.php?slug=' . htmlspecialchars($post['slug']) . '" class="text-decoration-none text-dark">';
                    $content .= htmlspecialchars($post['title']);
                    $content .= '</a></h6>';
                    
                    // Meta information
                    if ($show_author || $show_date) {
                        $content .= '<div class="text-muted small mb-2">';
                        if ($show_author && !empty($post['author_name'])) {
                            $content .= '<i class="fas fa-user me-1"></i>' . htmlspecialchars($post['author_name']);
                        }
                        if ($show_author && $show_date) {
                            $content .= ' • ';
                        }
                        if ($show_date) {
                            $post_date = $post['published_at'] ? $post['published_at'] : $post['created_at'];
                            $content .= '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($post_date));
                        }
                        $content .= '</div>';
                    }
                    
                    // Excerpt
                    if ($show_excerpt) {
                        $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : get_excerpt($post['content'], 100);
                        $content .= '<p class="card-text small">' . htmlspecialchars($excerpt) . '</p>';
                    }
                    
                    $content .= '</div>'; // Close content col
                    
                    $content .= '</div>'; // Close row
                    $content .= '</div>'; // Close card-body
                    $content .= '</article>'; // Close article
                }
                $content .= '</div>';
            } else {
                $content = '<div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>No posts are assigned to this specific Featured Posts widget instance. <a href="/campus_website2/admin/posts/create.php">Create posts</a> and assign them to this specific widget for curated display.</div>';
            }
            break;
            
        default:
            // For unknown widget types, try to get content from config
            $debug_info = '';
            if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                $debug_info = '<div class="alert alert-info mt-2"><small><strong>Debug:</strong> Widget Type: "' . htmlspecialchars($type_name) . '", Config: ' . htmlspecialchars(json_encode($config)) . '</small></div>';
            }
            $content = ($config['content'] ?? $config['text'] ?? 'Widget content not available') . $debug_info;
    }
    
    if (!$content) return '';
    
    $css_class = !empty($widget['css_class']) ? ' ' . $widget['css_class'] : '';
    
    // Widget types that should never show titles and use special styling
    $titlelessWidgetTypes = [
        'Image Widget', 
        'Image', 
        'Image & Hyperlink Text Widget',
        'Text Widget',
        'Media Widget'
    ];
    
    $isShowingTitle = false;
    
    // For titleless widget types, never show title regardless of configuration
    if (in_array($type_name, $titlelessWidgetTypes)) {
        // Use special rounded box styling for these widgets
        $specialClass = ' widget-rounded-box';
        $html = '<div class="widget widget-' . str_replace(' ', '-', strtolower($type_name)) . $css_class . $specialClass . ' mb-4">';
        // Never add title for these widget types
    } else {
        // For other widget types, use normal logic
        $showTitle = $config['show_title'] ?? true;
        $html = '<div class="widget widget-' . str_replace(' ', '-', strtolower($type_name)) . $css_class . ' mb-4">';
        
        if ($widget['title'] && $showTitle) {
            $html .= '<h5 class="widget-title" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($widget['title']) . '</h5>';
            $isShowingTitle = true;
        }
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

/**
 * Get posts assigned to a specific widget instance
 */
function get_widget_posts($widget_id, $config = []) {
    $db = Database::getInstance();
    $limit = intval($config['limit'] ?? 5);
    
    // Get posts that are specifically assigned to this widget instance
    $posts = $db->fetchAll("
        SELECT p.*, u.first_name, u.last_name, 
               CONCAT(u.first_name, ' ', u.last_name) as author_name,
               pw.sort_order as widget_sort_order
        FROM posts p
        INNER JOIN post_widgets pw ON p.id = pw.post_id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE pw.widget_id = ? AND pw.is_active = 1 AND p.status = 'published'
        ORDER BY pw.sort_order ASC, p.published_at DESC
        LIMIT ?
    ", [$widget_id, $limit]);
    
    return $posts;
}

/**
 * Render posts for different widget types
 */
function render_widget_posts($posts, $widget_type, $config = []) {
    if (empty($posts)) {
        return '<p class="text-muted">No posts available.</p>';
    }
    
    $layout = $config['layout'] ?? 'list';
    $show_excerpt = $config['show_excerpt'] ?? true;
    $show_image = $config['show_image'] ?? true;
    $show_date = $config['show_date'] ?? true;
    
    $html = '';
    
    switch ($widget_type) {
        case 'Content Widget':
            $html .= '<div class="content-widget-posts">';
            foreach ($posts as $post) {
                $html .= '<article class="post-item mb-3 pb-3 border-bottom">';
                
                if ($show_image && !empty($post['featured_image_url'])) {
                    $html .= '<div class="post-image mb-2">';
                    $html .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" alt="' . htmlspecialchars($post['title']) . '" class="img-fluid rounded">';
                    $html .= '</div>';
                }
                
                $html .= '<h6><a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a></h6>';
                
                if ($show_date) {
                    $html .= '<small class="text-muted d-block mb-2">By ' . htmlspecialchars($post['author_name']) . ' • ' . date('M j, Y', strtotime($post['published_at'])) . '</small>';
                }
                
                if ($show_excerpt && !empty($post['excerpt'])) {
                    $html .= '<p class="text-muted small">' . htmlspecialchars(substr($post['excerpt'], 0, 120)) . '...</p>';
                }
                
                $html .= '</article>';
            }
            $html .= '</div>';
            break;
            
        case 'Media Widget':
            $html .= '<div class="media-widget-posts row g-2">';
            foreach ($posts as $post) {
                $html .= '<div class="col-6">';
                $html .= '<div class="media-item">';
                
                if (!empty($post['featured_image_url'])) {
                    $html .= '<a href="post.php?slug=' . urlencode($post['slug']) . '">';
                    $html .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" alt="' . htmlspecialchars($post['title']) . '" class="img-fluid rounded">';
                    $html .= '</a>';
                } else {
                    $html .= '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">';
                    $html .= '<span class="text-muted">No Image</span>';
                    $html .= '</div>';
                }
                
                $html .= '<small class="d-block mt-1"><a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a></small>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            break;
            
        case 'Link Widget':
            $html .= '<ul class="list-unstyled link-widget-posts">';
            foreach ($posts as $post) {
                $html .= '<li class="mb-2">';
                $html .= '<a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a>';
                if ($show_date) {
                    $html .= '<small class="text-muted d-block">' . date('M j, Y', strtotime($post['published_at'])) . '</small>';
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
            break;
            
        case 'News Ticker':
            $speed = $config['speed'] ?? 'medium';
            $html .= '<div class="news-ticker" data-speed="' . htmlspecialchars($speed) . '">';
            $html .= '<div class="ticker-content">';
            foreach ($posts as $post) {
                $html .= '<span class="ticker-item">';
                $html .= '<a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a>';
                if ($show_date) {
                    $html .= ' <small class="text-muted">(' . date('M j', strtotime($post['published_at'])) . ')</small>';
                }
                $html .= '</span>';
            }
            $html .= '</div>';
            $html .= '</div>';
            break;
            
        case 'Featured Posts':
            $html .= '<div class="featured-posts-widget">';
            foreach ($posts as $index => $post) {
                $html .= '<div class="featured-item mb-3' . ($index === 0 ? ' featured-main' : '') . '">';
                
                if ($show_image && !empty($post['featured_image_url'])) {
                    $html .= '<div class="featured-image mb-2">';
                    $html .= '<img src="' . htmlspecialchars($post['featured_image_url']) . '" alt="' . htmlspecialchars($post['title']) . '" class="img-fluid rounded">';
                    $html .= '</div>';
                }
                
                $html .= '<h6><a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a></h6>';
                
                if ($show_date) {
                    $html .= '<small class="text-muted d-block mb-1">' . date('M j, Y', strtotime($post['published_at'])) . '</small>';
                }
                
                if ($show_excerpt && !empty($post['excerpt']) && $index === 0) {
                    $html .= '<p class="text-muted small">' . htmlspecialchars(substr($post['excerpt'], 0, 100)) . '...</p>';
                }
                
                $html .= '</div>';
            }
            $html .= '</div>';
            break;
            
        default:
            $html .= '<ul class="list-unstyled">';
            foreach ($posts as $post) {
                $html .= '<li class="mb-2">';
                $html .= '<a href="post.php?slug=' . urlencode($post['slug']) . '" class="text-decoration-none" style="font-family: \'Roboto Black\', sans-serif; font-weight: 900;">' . htmlspecialchars($post['title']) . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
    }
    
    return $html;
}

/**
 * Get carousel items for frontend display
 */
function get_carousel_items($campus_id = null) {
    if ($campus_id === null) {
        $campus = get_campus_config();
        $campus_id = $campus['id'];
    }
    
    $db = Database::getInstance();
    $items = $db->fetchAll("
        SELECT * FROM carousel_items 
        WHERE campus_id = ? AND is_active = 1 
        ORDER BY display_order ASC, created_at ASC
    ", [$campus_id]);
    
    return $items ?: [];
}

/**
 * Convert image path to frontend-accessible URL
 */
function get_frontend_image_path($imagePath) {
    // If it's already a full URL, return as is
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // If it starts with public/, we need to go up two levels from campus/public to reach the root public/
    if (strpos($imagePath, 'public/') === 0) {
        return '../../' . $imagePath; // From andrews/public/ go up to campus_website2/public/
    }
    
    // If it starts with uploads/, it's relative to site root
    if (strpos($imagePath, 'uploads/') === 0) {
        return '../../' . $imagePath;
    }
    
    // For any other relative path, assume it's relative to site root
    return '../../' . ltrim($imagePath, '/');
}

/**
 * Render carousel HTML for frontend
 */
function render_carousel($items = null, $carousel_id = 'campusCarousel') {
    if ($items === null) {
        $items = get_carousel_items();
    }
    
    if (empty($items)) {
        return '';
    }
    
    $html = '<div id="' . htmlspecialchars($carousel_id) . '" class="carousel slide rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="5000">';
    $html .= '<div class="carousel-inner">';
    
    foreach ($items as $index => $item) {
        $isActive = $index === 0 ? ' active' : '';
        $html .= '<div class="carousel-item' . $isActive . '">';
        $html .= '<div class="carousel-image-wrapper">';
        
        if (!empty($item['link_url'])) {
            $target = $item['link_target'] === '_blank' ? ' target="_blank" rel="noopener"' : '';
            $html .= '<a href="' . htmlspecialchars($item['link_url']) . '"' . $target . '>';
        }
        
        // Convert database image path to frontend-accessible path
        $frontendImagePath = get_frontend_image_path($item['image_path']);
        $html .= '<img src="' . htmlspecialchars($frontendImagePath) . '" class="d-block w-100" alt="' . htmlspecialchars($item['image_alt'] ?: $item['title'] ?: '') . '" loading="lazy">';
        
        if (!empty($item['link_url'])) {
            $html .= '</a>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // Add navigation controls
    $html .= '<button class="carousel-control-prev" type="button" data-bs-target="#' . htmlspecialchars($carousel_id) . '" data-bs-slide="prev">';
    $html .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
    $html .= '<span class="visually-hidden">Previous</span>';
    $html .= '</button>';
    $html .= '<button class="carousel-control-next" type="button" data-bs-target="#' . htmlspecialchars($carousel_id) . '" data-bs-slide="next">';
    $html .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
    $html .= '<span class="visually-hidden">Next</span>';
    $html .= '</button>';
    
    $html .= '</div>';
    
    return $html;
}
