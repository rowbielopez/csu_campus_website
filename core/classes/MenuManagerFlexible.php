<?php
/**
 * Menu Manager Class - Flexible Version
 * Handles menu operations with backward compatibility
 */

class MenuManagerFlexible {
    private $db;
    private $campus_id;
    private $hasCampusId = false;
    
    public function __construct($campus_id = null) {
        $this->db = Database::getInstance();
        $this->campus_id = $campus_id ?? current_campus_id();
        
        // Check if menu_items table has campus_id column
        try {
            $columns = $this->db->fetchAll("DESCRIBE menu_items");
            foreach ($columns as $col) {
                if ($col['Field'] === 'campus_id') {
                    $this->hasCampusId = true;
                    break;
                }
            }
        } catch (Exception $e) {
            // Table might not exist, we'll handle this gracefully
        }
    }
    
    /**
     * Get menu items for a specific location
     */
    public function getMenuItems($location = 'main', $parent_id = null) {
        if (!$this->tableExists('menu_items')) {
            return []; // Return empty array if table doesn't exist
        }
        
        $sql = "SELECT * FROM menu_items WHERE menu_location = ? AND is_active = 1";
        $params = [$location];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        if ($parent_id === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = ?";
            $params[] = $parent_id;
        }
        
        $sql .= " ORDER BY sort_order ASC";
        
        try {
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get complete menu tree for a location
     */
    public function getMenuTree($location = 'main') {
        if (!$this->tableExists('menu_items')) {
            return []; // Return empty array if table doesn't exist
        }
        
        $sql = "SELECT * FROM menu_items WHERE menu_location = ? AND is_active = 1";
        $params = [$location];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        $sql .= " ORDER BY parent_id, sort_order ASC";
        
        try {
            $items = $this->db->fetchAll($sql, $params);
            return $this->buildMenuTree($items);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Check if table exists
     */
    private function tableExists($tableName) {
        try {
            $result = $this->db->fetchAll("SHOW TABLES LIKE ?", [$tableName]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
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
        if (!$this->tableExists('menu_items')) {
            $this->createMenuTables();
        }
        
        $user_id = get_logged_in_user()['id'];
        
        $data['created_by'] = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->hasCampusId && $this->campus_id) {
            $data['campus_id'] = $this->campus_id;
        }
        
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO menu_items (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        
        try {
            $this->db->query($sql, $data);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("MenuManager createMenuItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create menu tables if they don't exist
     */
    private function createMenuTables() {
        $sql = "CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            url VARCHAR(500),
            menu_location VARCHAR(50) DEFAULT 'main',
            parent_id INT NULL,
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            link_type ENUM('url', 'page', 'post', 'category') DEFAULT 'url',
            target_id INT NULL,
            css_class VARCHAR(100),
            icon_class VARCHAR(100),
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
        )";
        
        try {
            $this->db->query($sql);
            
            // Add campus_id column if we're in a multi-campus setup
            if ($this->campus_id) {
                $this->db->query("ALTER TABLE menu_items ADD COLUMN campus_id INT AFTER id");
                $this->hasCampusId = true;
            }
        } catch (Exception $e) {
            error_log("MenuManager createMenuTables error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all menu locations
     */
    public function getMenuLocations() {
        return [
            'main' => 'Main Navigation',
            'footer' => 'Footer Menu', 
            'sidebar' => 'Sidebar Menu',
            'mobile' => 'Mobile Menu'
        ];
    }
    
    /**
     * Update menu item
     */
    public function updateMenuItem($id, $data) {
        if (!$this->tableExists('menu_items')) {
            return false;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $setClause = [];
        foreach (array_keys($data) as $field) {
            $setClause[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE menu_items SET " . implode(', ', $setClause) . " WHERE id = :id";
        $data['id'] = $id;
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = :campus_id";
            $data['campus_id'] = $this->campus_id;
        }
        
        try {
            $result = $this->db->query($sql, $data);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            error_log("MenuManager updateMenuItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete menu item
     */
    public function deleteMenuItem($id) {
        if (!$this->tableExists('menu_items')) {
            return false;
        }
        
        $sql = "DELETE FROM menu_items WHERE id = ?";
        $params = [$id];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            error_log("MenuManager deleteMenuItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single menu item
     */
    public function getMenuItem($id) {
        if (!$this->tableExists('menu_items')) {
            return null;
        }
        
        $sql = "SELECT * FROM menu_items WHERE id = ?";
        $params = [$id];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        try {
            return $this->db->fetch($sql, $params);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update menu item order
     */
    public function updateMenuOrder($itemOrders) {
        if (!$this->tableExists('menu_items') || empty($itemOrders)) {
            return false;
        }
        
        try {
            foreach ($itemOrders as $itemData) {
                $sql = "UPDATE menu_items SET sort_order = ?, parent_id = ? WHERE id = ?";
                $params = [
                    $itemData['order'],
                    $itemData['parent_id'] ?? null,
                    $itemData['id']
                ];
                
                if ($this->hasCampusId && $this->campus_id) {
                    $sql .= " AND campus_id = ?";
                    $params[] = $this->campus_id;
                }
                
                $this->db->query($sql, $params);
            }
            return true;
        } catch (Exception $e) {
            error_log("MenuManager updateMenuOrder error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available pages for menu linking
     */
    public function getAvailablePages() {
        try {
            if ($this->tableExists('pages')) {
                $sql = "SELECT id, title, slug FROM pages WHERE is_published = 1";
                $params = [];
                
                if ($this->hasCampusId && $this->campus_id) {
                    $sql .= " AND campus_id = ?";
                    $params[] = $this->campus_id;
                }
                
                return $this->db->fetchAll($sql, $params);
            }
        } catch (Exception $e) {
            // Pages table might not exist
        }
        
        return [];
    }
    
    /**
     * Get available posts for menu linking
     */
    public function getAvailablePosts() {
        try {
            if ($this->tableExists('posts')) {
                $sql = "SELECT id, title, slug FROM posts WHERE status = 'published'";
                $params = [];
                
                if ($this->hasCampusId && $this->campus_id) {
                    $sql .= " AND campus_id = ?";
                    $params[] = $this->campus_id;
                }
                
                return $this->db->fetchAll($sql, $params);
            }
        } catch (Exception $e) {
            // Posts table might not exist
        }
        
        return [];
    }
    
    /**
     * Render menu HTML
     */
    public function renderMenu($location = 'main', $containerClass = 'nav') {
        $items = $this->getMenuTree($location);
        
        if (empty($items)) {
            return "<div class='alert alert-info'>No menu items found for '{$location}' location.</div>";
        }
        
        return $this->renderMenuItems($items, $containerClass);
    }
    
    /**
     * Render menu items HTML
     */
    private function renderMenuItems($items, $containerClass = 'nav', $isSubmenu = false) {
        if (empty($items)) {
            return '';
        }
        
        $html = $isSubmenu ? '<ul class="submenu">' : "<ul class='{$containerClass}'>";
        
        foreach ($items as $item) {
            $hasChildren = !empty($item['children']);
            $cssClass = $item['css_class'] ?? '';
            
            if ($hasChildren) {
                $cssClass .= ' has-dropdown';
            }
            
            $html .= "<li class='{$cssClass}'>";
            
            // Build the link
            $url = $this->buildMenuUrl($item);
            $target = $item['link_type'] === 'url' && strpos($item['url'], 'http') === 0 ? ' target="_blank"' : '';
            $icon = $item['icon_class'] ? "<i class='{$item['icon_class']}'></i> " : '';
            
            $html .= "<a href='{$url}'{$target}>{$icon}" . htmlspecialchars($item['title']) . "</a>";
            
            // Add children if any
            if ($hasChildren) {
                $html .= $this->renderMenuItems($item['children'], 'submenu', true);
            }
            
            $html .= "</li>";
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * Build URL for menu item based on type
     */
    private function buildMenuUrl($item) {
        switch ($item['link_type']) {
            case 'page':
                // Assuming you have a page system
                return "/page/{$item['target_id']}";
                
            case 'post':
                // Assuming you have a post system
                return "/post/{$item['target_id']}";
                
            case 'category':
                // Assuming you have categories
                return "/category/{$item['target_id']}";
                
            case 'url':
            default:
                return $item['url'] ?: '#';
        }
    }
}
?>
