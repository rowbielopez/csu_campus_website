<?php
/**
 * Widget Manager Class
 * Handles widget operations for multi-campus CMS
 */

class WidgetManager {
    private $db;
    private $campus_id;
    
    public function __construct($campus_id = null) {
        $this->db = Database::getInstance();
        $this->campus_id = $campus_id ?? current_campus_id();
    }
    
    /**
     * Get all available widget types
     */
    public function getWidgetTypes() {
        return $this->db->fetchAll(
            "SELECT * FROM widget_types WHERE is_active = 1 ORDER BY display_name"
        );
    }
    
    /**
     * Get widgets for a specific position
     */
    public function getWidgetsByPosition($position = 'sidebar') {
        return $this->db->fetchAll(
            "SELECT cw.*, wt.name as widget_type, wt.display_name, wt.icon 
             FROM campus_widgets cw 
             JOIN widget_types wt ON cw.widget_type_id = wt.id 
             WHERE cw.campus_id = ? AND cw.position = ? AND cw.is_active = 1 
             ORDER BY cw.sort_order ASC",
            [$this->campus_id, $position]
        );
    }
    
    /**
     * Get all widgets for current campus
     */
    public function getAllCampusWidgets() {
        return $this->db->fetchAll(
            "SELECT cw.*, wt.name as widget_type, wt.display_name, wt.icon 
             FROM campus_widgets cw 
             JOIN widget_types wt ON cw.widget_type_id = wt.id 
             WHERE cw.campus_id = ? 
             ORDER BY cw.position, cw.sort_order ASC",
            [$this->campus_id]
        );
    }
    
    /**
     * Create a new widget
     */
    public function createWidget($data) {
        $user_id = get_logged_in_user()['id'];
        
        // Get next sort order for position
        $next_order = $this->getNextSortOrder($data['position']);
        
        return $this->db->query(
            "INSERT INTO campus_widgets (campus_id, widget_type_id, title, config, position, sort_order, visibility_rules, created_by, updated_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->campus_id,
                $data['widget_type_id'],
                $data['title'],
                json_encode($data['config'] ?? []),
                $data['position'] ?? 'sidebar',
                $next_order,
                json_encode($data['visibility_rules'] ?? []),
                $user_id,
                $user_id
            ]
        );
    }
    
    /**
     * Update widget
     */
    public function updateWidget($widget_id, $data) {
        $user_id = get_logged_in_user()['id'];
        
        return $this->db->query(
            "UPDATE campus_widgets 
             SET title = ?, config = ?, position = ?, visibility_rules = ?, is_active = ?, updated_by = ?, updated_at = NOW() 
             WHERE id = ? AND campus_id = ?",
            [
                $data['title'],
                json_encode($data['config'] ?? []),
                $data['position'] ?? 'sidebar',
                json_encode($data['visibility_rules'] ?? []),
                $data['is_active'] ?? 1,
                $user_id,
                $widget_id,
                $this->campus_id
            ]
        );
    }
    
    /**
     * Delete widget
     */
    public function deleteWidget($widget_id) {
        return $this->db->query(
            "DELETE FROM campus_widgets WHERE id = ? AND campus_id = ?",
            [$widget_id, $this->campus_id]
        );
    }
    
    /**
     * Update widget sort order
     */
    public function updateWidgetOrder($widget_orders) {
        foreach ($widget_orders as $widget_id => $order) {
            $this->db->query(
                "UPDATE campus_widgets SET sort_order = ? WHERE id = ? AND campus_id = ?",
                [$order, $widget_id, $this->campus_id]
            );
        }
        return true;
    }
    
    /**
     * Get widget by ID
     */
    public function getWidget($widget_id) {
        return $this->db->fetch(
            "SELECT cw.*, wt.name as widget_type, wt.display_name, wt.icon, wt.config_schema 
             FROM campus_widgets cw 
             JOIN widget_types wt ON cw.widget_type_id = wt.id 
             WHERE cw.id = ? AND cw.campus_id = ?",
            [$widget_id, $this->campus_id]
        );
    }
    
    /**
     * Get next sort order for position
     */
    private function getNextSortOrder($position) {
        $result = $this->db->fetch(
            "SELECT MAX(sort_order) as max_order FROM campus_widgets WHERE campus_id = ? AND position = ?",
            [$this->campus_id, $position]
        );
        return ($result['max_order'] ?? 0) + 1;
    }
    
    /**
     * Render widget HTML
     */
    public function renderWidget($widget) {
        $config = json_decode($widget['config'] ?? '[]', true);
        $widget_type = $widget['widget_type'];
        
        // Check visibility rules
        if (!$this->checkWidgetVisibility($widget)) {
            return '';
        }
        
        $html = '<div class="widget widget-' . $widget_type . '" data-widget-id="' . $widget['id'] . '">';
        $html .= '<h5 class="widget-title">' . htmlspecialchars($widget['title']) . '</h5>';
        $html .= '<div class="widget-content">';
        
        switch ($widget_type) {
            case 'news_feed':
                $html .= $this->renderNewsFeed($config);
                break;
            case 'events_list':
                $html .= $this->renderEventsList($config);
                break;
            case 'contact_info':
                $html .= $this->renderContactInfo($config);
                break;
            case 'announcements':
                $html .= $this->renderAnnouncements($config);
                break;
            case 'featured_posts':
                $html .= $this->renderFeaturedPosts($config);
                break;
            case 'custom_html':
                $html .= $this->renderCustomHTML($config);
                break;
            default:
                $html .= '<p>Widget type not implemented yet.</p>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    /**
     * Check widget visibility rules
     */
    private function checkWidgetVisibility($widget) {
        $visibility_rules = json_decode($widget['visibility_rules'] ?? '[]', true);
        
        // Add visibility logic here
        // For now, return true
        return true;
    }
    
    /**
     * Render news feed widget
     */
    private function renderNewsFeed($config) {
        $count = $config['count'] ?? 5;
        $show_excerpt = $config['show_excerpt'] ?? true;
        $show_date = $config['show_date'] ?? true;
        
        // Get news posts (assuming posts table exists)
        $news = $this->db->fetchAll(
            "SELECT title, excerpt, content, created_at, slug 
             FROM posts 
             WHERE campus_id = ? AND status = 'published' AND post_type = 'post' 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$this->campus_id, $count]
        );
        
        $html = '<div class="news-feed">';
        if (empty($news)) {
            $html .= '<p class="text-muted">No news available.</p>';
        } else {
            foreach ($news as $item) {
                $html .= '<div class="news-item mb-3">';
                $html .= '<h6><a href="/post/' . $item['slug'] . '">' . htmlspecialchars($item['title']) . '</a></h6>';
                if ($show_date) {
                    $html .= '<small class="text-muted">' . date('M j, Y', strtotime($item['created_at'])) . '</small>';
                }
                if ($show_excerpt && $item['excerpt']) {
                    $html .= '<p class="small">' . htmlspecialchars($item['excerpt']) . '</p>';
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render events list widget
     */
    private function renderEventsList($config) {
        $count = $config['count'] ?? 5;
        $show_date = $config['show_date'] ?? true;
        $show_location = $config['show_location'] ?? true;
        
        // For now, return placeholder
        return '<div class="events-list"><p class="text-muted">Events module coming soon.</p></div>';
    }
    
    /**
     * Render contact info widget
     */
    private function renderContactInfo($config) {
        $campus = get_current_campus();
        
        $html = '<div class="contact-info">';
        
        if ($config['show_phone'] ?? true) {
            $html .= '<div class="contact-item mb-2">';
            $html .= '<i class="fas fa-phone me-2"></i>';
            $html .= '<span>' . htmlspecialchars($campus['phone'] ?? 'N/A') . '</span>';
            $html .= '</div>';
        }
        
        if ($config['show_email'] ?? true) {
            $html .= '<div class="contact-item mb-2">';
            $html .= '<i class="fas fa-envelope me-2"></i>';
            $html .= '<span>' . htmlspecialchars($campus['email'] ?? 'N/A') . '</span>';
            $html .= '</div>';
        }
        
        if ($config['show_address'] ?? true) {
            $html .= '<div class="contact-item mb-2">';
            $html .= '<i class="fas fa-map-marker-alt me-2"></i>';
            $html .= '<span>' . htmlspecialchars($campus['address'] ?? 'N/A') . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render announcements widget
     */
    private function renderAnnouncements($config) {
        $count = $config['count'] ?? 3;
        
        return '<div class="announcements"><p class="text-muted">Announcements module coming soon.</p></div>';
    }
    
    /**
     * Render featured posts widget
     */
    private function renderFeaturedPosts($config) {
        $count = $config['count'] ?? 4;
        $show_thumbnail = $config['show_thumbnail'] ?? true;
        $show_excerpt = $config['show_excerpt'] ?? true;
        
        return '<div class="featured-posts"><p class="text-muted">Featured posts module coming soon.</p></div>';
    }
    
    /**
     * Render custom HTML widget
     */
    private function renderCustomHTML($config) {
        $html_content = $config['html_content'] ?? '';
        $css_styles = $config['css_styles'] ?? '';
        
        $output = '';
        if ($css_styles) {
            $output .= '<style>' . $css_styles . '</style>';
        }
        $output .= $html_content;
        
        return $output;
    }
    
    /**
     * Get widgets by location (all widgets, not just active)
     */
    public function getWidgetsByLocation($location) {
        $sql = "SELECT w.*, wt.name as type_name, wt.description as type_description 
                FROM campus_widgets w 
                JOIN widget_types wt ON w.widget_type_id = wt.id 
                WHERE w.campus_id = ? AND w.position = ?
                ORDER BY w.sort_order ASC";
        
        return $this->db->fetchAll($sql, [$this->campus_id, $location]);
    }
    
    /**
     * Get active widgets by location
     */
    public function getActiveWidgets($location = null) {
        $sql = "SELECT w.*, wt.name as type_name, wt.description as type_description 
                FROM campus_widgets w 
                JOIN widget_types wt ON w.widget_type_id = wt.id 
                WHERE w.campus_id = ? AND w.is_active = 1";
        $params = [$this->campus_id];
        
        if ($location) {
            $sql .= " AND w.position = ?";
            $params[] = $location;
        }
        
        $sql .= " ORDER BY w.sort_order ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
}
?>
