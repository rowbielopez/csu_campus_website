<?php
/**
 * Utility Functions
 * Common utility functions used throughout the application
 */

/**
 * Format date according to campus locale
 */
function format_date($date, $format = 'F j, Y') {
    if (!$date) return '';
    
    $timestamp = is_string($date) ? strtotime($date) : $date;
    return date($format, $timestamp);
}

/**
 * Format date and time
 */
function format_datetime($datetime, $format = 'F j, Y g:i A') {
    if (!$datetime) return '';
    
    $timestamp = is_string($datetime) ? strtotime($datetime) : $datetime;
    return date($format, $timestamp);
}

/**
 * Get time ago format
 */
function time_ago($datetime) {
    if (!$datetime) return '';
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

/**
 * Truncate text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Strip HTML and truncate
 */
function excerpt($text, $length = 150) {
    $text = strip_tags($text);
    return truncate_text($text, $length);
}

/**
 * Generate random string
 */
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Clean filename
 */
function clean_filename($filename) {
    // Remove any path info
    $filename = basename($filename);
    
    // Replace spaces and special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Remove multiple underscores
    $filename = preg_replace('/_+/', '_', $filename);
    
    return trim($filename, '_');
}

/**
 * Get file extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size
 */
function format_file_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . ' ' . $units[$i];
}

/**
 * Check if image file
 */
function is_image($filename) {
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    return in_array(get_file_extension($filename), $image_extensions);
}

/**
 * Pagination helper
 */
function paginate($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    $pagination = [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page > 1 ? $current_page - 1 : null,
        'next_page' => $current_page < $total_pages ? $current_page + 1 : null,
        'offset' => ($current_page - 1) * $items_per_page,
        'pages' => []
    ];
    
    // Generate page links
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $pagination['pages'][] = [
            'page' => $i,
            'url' => $base_url . (strpos($base_url, '?') !== false ? '&' : '?') . 'page=' . $i,
            'is_current' => $i == $current_page
        ];
    }
    
    // Add first and last if needed
    if ($start > 1) {
        array_unshift($pagination['pages'], [
            'page' => 1,
            'url' => $base_url . (strpos($base_url, '?') !== false ? '&' : '?') . 'page=1',
            'is_current' => false
        ]);
        
        if ($start > 2) {
            array_splice($pagination['pages'], 1, 0, [['page' => '...', 'url' => '#', 'is_current' => false]]);
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $pagination['pages'][] = ['page' => '...', 'url' => '#', 'is_current' => false];
        }
        
        $pagination['pages'][] = [
            'page' => $total_pages,
            'url' => $base_url . (strpos($base_url, '?') !== false ? '&' : '?') . 'page=' . $total_pages,
            'is_current' => false
        ];
    }
    
    return $pagination;
}

/**
 * Breadcrumb helper
 */
function generate_breadcrumbs($items) {
    $breadcrumbs = [];
    
    foreach ($items as $item) {
        $breadcrumbs[] = [
            'title' => $item['title'],
            'url' => $item['url'] ?? null,
            'is_current' => $item['is_current'] ?? false
        ];
    }
    
    return $breadcrumbs;
}

/**
 * Status badge helper
 */
function get_status_badge($status) {
    $badges = [
        STATUS_DRAFT => ['class' => 'secondary', 'text' => 'Draft'],
        STATUS_PUBLISHED => ['class' => 'success', 'text' => 'Published'],
        STATUS_ARCHIVED => ['class' => 'warning', 'text' => 'Archived']
    ];
    
    return $badges[$status] ?? ['class' => 'secondary', 'text' => 'Unknown'];
}

/**
 * Role name helper
 */
function get_role_name($role_id) {
    $roles = [
        ROLE_SUPER_ADMIN => 'Super Admin',
        ROLE_CAMPUS_ADMIN => 'Campus Admin',
        ROLE_EDITOR => 'Editor',
        ROLE_AUTHOR => 'Author',
        ROLE_READER => 'Reader'
    ];
    
    return $roles[$role_id] ?? 'Unknown';
}

/**
 * Color helper
 */
function get_contrast_color($hex_color) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex_color, 0, 2));
    $g = hexdec(substr($hex_color, 2, 2));
    $b = hexdec(substr($hex_color, 4, 2));
    
    // Calculate luminance
    $luminance = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255;
    
    return $luminance > 0.5 ? '#000000' : '#ffffff';
}

/**
 * Social media helper
 */
function get_social_share_urls($title, $url) {
    $encoded_title = urlencode($title);
    $encoded_url = urlencode($url);
    
    return [
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
        'twitter' => "https://twitter.com/intent/tweet?text={$encoded_title}&url={$encoded_url}",
        'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}",
        'email' => "mailto:?subject={$encoded_title}&body={$encoded_url}"
    ];
}

/**
 * SEO meta helper
 */
function generate_meta_tags($page_data) {
    $meta = [
        'title' => $page_data['meta_title'] ?: $page_data['title'],
        'description' => $page_data['meta_description'] ?: excerpt($page_data['content'] ?? '', 160),
        'keywords' => $page_data['meta_keywords'] ?: '',
        'image' => $page_data['featured_image'] ? campus_url($page_data['featured_image']) : null,
        'url' => campus_url($_SERVER['REQUEST_URI'])
    ];
    
    return $meta;
}
?>
