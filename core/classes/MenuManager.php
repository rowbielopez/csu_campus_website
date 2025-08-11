<?php
/**
 * Menu Manager Class
 * Handles menu operations for multi-campus CMS
 */

class MenuManager {
    private $db;
    private $campus_id;
    
    public function __construct($campus_id = null) {
        $this->db = Database::getInstance();
        $this->campus_id = $campus_id ?? current_campus_id();
    }
    
    /**
     * Get menu items for a specific location
     */
    public function getMenuItems($location = 'main', $parent_id = null) {
        $sql = "SELECT * FROM menu_items 
                WHERE campus_id = ? AND menu_location = ? AND is_active = 1";
        $params = [$this->campus_id, $location];
        
        if ($parent_id === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = ?";
            $params[] = $parent_id;
        }
        
        $sql .= " ORDER BY sort_order ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get complete menu tree for a location
     */
    public function getMenuTree($location = 'main') {
        $items = $this->db->fetchAll(
            "SELECT * FROM menu_items 
             WHERE campus_id = ? AND menu_location = ? AND is_active = 1 
             ORDER BY parent_id, sort_order ASC",
            [$this->campus_id, $location]
        );
        
        return $this->buildMenuTree($items);
    }
    
    /**
     * Build hierarchical menu tree from flat array
     */
    private function buildMenuTree($items, $parent_id = null) {
        $tree = [];
        
        foreach ($items as $item) {
            if ($item['parent_id'] == $parent_id) {
                $item['children'] = $this->buildMenuTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        
        return $tree;
    }
    
    /**
     * Create menu item
     */
    public function createMenuItem($data) {
        $user_id = get_logged_in_user()['id'];
        
        // Get next sort order
        $next_order = $this->getNextSortOrder($data['menu_location'], $data['parent_id'] ?? null);
        
        return $this->db->query(
            "INSERT INTO menu_items (campus_id, parent_id, title, url, url_type, target_id, target, icon, css_class, sort_order, menu_location, visibility_rules, created_by, updated_by) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->campus_id,
                $data['parent_id'] ?? null,
                $data['title'],
                $data['url'] ?? '',
                $data['url_type'] ?? 'internal',
                $data['target_id'] ?? null,
                $data['target'] ?? '_self',
                $data['icon'] ?? '',
                $data['css_class'] ?? '',
                $next_order,
                $data['menu_location'] ?? 'main',
                json_encode($data['visibility_rules'] ?? []),
                $user_id,
                $user_id
            ]
        );
    }
    
    /**
     * Update menu item
     */
    public function updateMenuItem($item_id, $data) {
        $user_id = get_logged_in_user()['id'];
        
        return $this->db->query(
            "UPDATE menu_items 
             SET title = ?, url = ?, url_type = ?, target_id = ?, target = ?, icon = ?, css_class = ?, visibility_rules = ?, is_active = ?, updated_by = ?, updated_at = NOW() 
             WHERE id = ? AND campus_id = ?",
            [
                $data['title'],
                $data['url'] ?? '',
                $data['url_type'] ?? 'internal',
                $data['target_id'] ?? null,
                $data['target'] ?? '_self',
                $data['icon'] ?? '',
                $data['css_class'] ?? '',
                json_encode($data['visibility_rules'] ?? []),
                $data['is_active'] ?? 1,
                $user_id,
                $item_id,
                $this->campus_id
            ]
        );
    }
    
    /**
     * Delete menu item (and its children)
     */
    public function deleteMenuItem($item_id) {
        // First delete children
        $children = $this->db->fetchAll(
            "SELECT id FROM menu_items WHERE parent_id = ? AND campus_id = ?",
            [$item_id, $this->campus_id]
        );
        
        foreach ($children as $child) {
            $this->deleteMenuItem($child['id']);
        }
        
        // Then delete the item itself
        return $this->db->query(
            "DELETE FROM menu_items WHERE id = ? AND campus_id = ?",
            [$item_id, $this->campus_id]
        );
    }
    
    /**
     * Update menu item order
     */
    public function updateMenuOrder($item_orders) {
        foreach ($item_orders as $item_id => $order_data) {
            $this->db->query(
                "UPDATE menu_items SET sort_order = ?, parent_id = ? WHERE id = ? AND campus_id = ?",
                [$order_data['order'], $order_data['parent_id'], $item_id, $this->campus_id]
            );
        }
        return true;
    }
    
    /**
     * Get menu item by ID
     */
    public function getMenuItem($item_id) {
        return $this->db->fetch(
            "SELECT * FROM menu_items WHERE id = ? AND campus_id = ?",
            [$item_id, $this->campus_id]
        );
    }
    
    /**
     * Get next sort order
     */
    private function getNextSortOrder($location, $parent_id = null) {
        $sql = "SELECT MAX(sort_order) as max_order FROM menu_items WHERE campus_id = ? AND menu_location = ?";
        $params = [$this->campus_id, $location];
        
        if ($parent_id === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = ?";
            $params[] = $parent_id;
        }
        
        $result = $this->db->fetch($sql, $params);
        return ($result['max_order'] ?? 0) + 1;
    }
    
    /**
     * Render menu HTML
     */
    public function renderMenu($location = 'main', $css_class = 'navbar-nav') {
        $menu_tree = $this->getMenuTree($location);
        
        if (empty($menu_tree)) {
            return '';
        }
        
        $html = '<ul class="' . $css_class . '">';
        foreach ($menu_tree as $item) {
            $html .= $this->renderMenuItem($item);
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Render individual menu item
     */
    private function renderMenuItem($item, $level = 0) {
        // Check visibility rules
        if (!$this->checkMenuVisibility($item)) {
            return '';
        }
        
        $has_children = !empty($item['children']);
        $css_classes = ['nav-item'];
        
        if ($has_children) {
            $css_classes[] = 'dropdown';
        }
        
        if ($item['css_class']) {
            $css_classes[] = $item['css_class'];
        }
        
        $html = '<li class="' . implode(' ', $css_classes) . '">';
        
        // Generate URL
        $url = $this->generateMenuUrl($item);
        
        $link_classes = ['nav-link'];
        if ($has_children) {
            $link_classes[] = 'dropdown-toggle';
        }
        
        $html .= '<a class="' . implode(' ', $link_classes) . '" href="' . htmlspecialchars($url) . '"';
        
        if ($item['target'] === '_blank') {
            $html .= ' target="_blank"';
        }
        
        if ($has_children) {
            $html .= ' data-bs-toggle="dropdown"';
        }
        
        $html .= '>';
        
        if ($item['icon']) {
            $html .= '<i class="' . htmlspecialchars($item['icon']) . ' me-2"></i>';
        }
        
        $html .= htmlspecialchars($item['title']) . '</a>';
        
        // Render children
        if ($has_children) {
            $html .= '<ul class="dropdown-menu">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderMenuItem($child, $level + 1);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        
        return $html;
    }
    
    /**
     * Generate URL for menu item
     */
    private function generateMenuUrl($item) {
        switch ($item['url_type']) {
            case 'external':
                return $item['url'];
            case 'internal':
                return $item['url'];
            case 'page':
                // Assuming pages have slugs
                return '/page/' . $item['target_id'];
            case 'post':
                return '/post/' . $item['target_id'];
            default:
                return $item['url'] ?? '#';
        }
    }
    
    /**
     * Check menu item visibility
     */
    private function checkMenuVisibility($item) {
        $visibility_rules = json_decode($item['visibility_rules'] ?? '[]', true);
        
        // Add visibility logic here
        // For now, return true
        return true;
    }
    
    /**
     * Get available pages for menu linking
     */
    public function getAvailablePages() {
        return $this->db->fetchAll(
            "SELECT id, title, slug FROM posts WHERE campus_id = ? AND post_type = 'page' AND status = 'published' ORDER BY title",
            [$this->campus_id]
        );
    }
    
    /**
     * Get available posts for menu linking
     */
    public function getAvailablePosts() {
        return $this->db->fetchAll(
            "SELECT id, title, slug FROM posts WHERE campus_id = ? AND post_type = 'post' AND status = 'published' ORDER BY title",
            [$this->campus_id]
        );
    }
}
?>
