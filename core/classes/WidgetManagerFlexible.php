<?php
/**
 * Widget Manager Class - Flexible Version
 * Handles widget operations with automatic table creation
 */

class WidgetManagerFlexible {
    private $db;
    private $campus_id;
    private $hasCampusId = false;
    
    public function __construct($campus_id = null) {
        $this->db = Database::getInstance();
        
        // Handle campus_id - use provided value, CAMPUS_ID constant, or default to 1
        if ($campus_id !== null) {
            $this->campus_id = $campus_id;
        } elseif (defined('CAMPUS_ID')) {
            $this->campus_id = CAMPUS_ID;
        } elseif (function_exists('current_campus_id')) {
            $this->campus_id = current_campus_id();
        } else {
            $this->campus_id = 1; // Default campus
        }
        
        // Check if tables exist and create them if needed
        $this->ensureTablesExist();
        
        // Check if campus_widgets table has campus_id column
        if ($this->tableExists('campus_widgets')) {
            try {
                $columns = $this->db->fetchAll("DESCRIBE campus_widgets");
                foreach ($columns as $col) {
                    if ($col['Field'] === 'campus_id') {
                        $this->hasCampusId = true;
                        break;
                    }
                }
            } catch (Exception $e) {
                // Handle gracefully
            }
        }
    }
    
    /**
     * Ensure widget tables exist
     */
    private function ensureTablesExist() {
        try {
            // Create widget_types table
            if (!$this->tableExists('widget_types')) {
                $this->createWidgetTypesTable();
            }
            
            // Create campus_widgets table
            if (!$this->tableExists('campus_widgets')) {
                $this->createCampusWidgetsTable();
            }
            
            // Populate default widget types if empty
            $this->populateDefaultWidgetTypes();
            
        } catch (Exception $e) {
            error_log("WidgetManager ensureTablesExist error: " . $e->getMessage());
        }
    }
    
    /**
     * Create widget_types table
     */
    private function createWidgetTypesTable() {
        $sql = "CREATE TABLE widget_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            template_path VARCHAR(255),
            default_template TEXT,
            config_schema JSON,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->db->query($sql);
    }
    
    /**
     * Create campus_widgets table
     */
    private function createCampusWidgetsTable() {
        $sql = "CREATE TABLE campus_widgets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            campus_id INT,
            widget_type_id INT,
            title VARCHAR(255) NOT NULL,
            position VARCHAR(50) DEFAULT 'sidebar',
            sort_order INT DEFAULT 0,
            config JSON,
            css_class VARCHAR(100),
            is_active TINYINT(1) DEFAULT 1,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (widget_type_id) REFERENCES widget_types(id) ON DELETE CASCADE
        )";
        
        $this->db->query($sql);
        $this->hasCampusId = true;
    }
    
    /**
     * Populate default widget types
     */
    private function populateDefaultWidgetTypes() {
        try {
            $count = $this->db->fetch("SELECT COUNT(*) as count FROM widget_types")['count'];
            
            if ($count == 0) {
                $defaultTypes = [
                    [
                        'name' => 'Text Widget',
                        'description' => 'Display custom text content',
                        'template_path' => 'widgets/text.php',
                        'default_template' => '{"content": "", "show_title": true}'
                    ],
                    [
                        'name' => 'Image Widget',
                        'description' => 'Display an image with optional caption',
                        'template_path' => 'widgets/image.php',
                        'default_template' => '{"image_url": "", "alt_text": "", "caption": "", "link_url": ""}'
                    ],
                    [
                        'name' => 'Recent Posts',
                        'description' => 'Show recent blog posts',
                        'template_path' => 'widgets/recent-posts.php',
                        'default_template' => '{"count": 5, "show_excerpt": true, "show_date": true}'
                    ],
                    [
                        'name' => 'Navigation Menu',
                        'description' => 'Display a navigation menu',
                        'template_path' => 'widgets/menu.php',
                        'default_template' => '{"menu_location": "main", "show_icons": false}'
                    ],
                    [
                        'name' => 'Contact Info',
                        'description' => 'Display contact information',
                        'template_path' => 'widgets/contact.php',
                        'default_template' => '{"phone": "", "email": "", "address": "", "show_map": false}'
                    ]
                ];
                
                foreach ($defaultTypes as $type) {
                    $this->db->query(
                        "INSERT INTO widget_types (name, description, template_path, default_template) VALUES (?, ?, ?, ?)",
                        [$type['name'], $type['description'], $type['template_path'], $type['default_template']]
                    );
                }
            }
        } catch (Exception $e) {
            error_log("WidgetManager populateDefaultWidgetTypes error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if table exists
     */
    private function tableExists($tableName) {
        try {
            $result = $this->db->fetchAll("SHOW TABLES LIKE '{$tableName}'");
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get widgets by location
     */
    public function getWidgetsByLocation($location) {
        if (!$this->tableExists('campus_widgets')) {
            return [];
        }
        
        $sql = "SELECT w.*, wt.name as type_name, wt.template_path, wt.default_template 
                FROM campus_widgets w 
                LEFT JOIN widget_types wt ON w.widget_type_id = wt.id 
                WHERE w.position = ? AND w.is_active = 1";
        $params = [$location];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND w.campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        $sql .= " ORDER BY w.sort_order ASC, w.id ASC";
        
        try {
            $result = $this->db->fetchAll($sql, $params);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("WidgetManager getWidgetsByLocation error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            return [];
        }
    }
    
    /**
     * Get widget types
     */
    public function getWidgetTypes() {
        // Ensure tables exist before trying to get widget types
        $this->ensureTablesExist();
        
        if (!$this->tableExists('widget_types')) {
            return [];
        }
        
        try {
            return $this->db->fetchAll("SELECT * FROM widget_types WHERE is_active = 1 ORDER BY name");
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Create widget
     */
    public function createWidget($data) {
        if (!$this->tableExists('campus_widgets')) {
            return false;
        }
        
        $user_id = get_logged_in_user()['id'] ?? null;
        
        $data['created_by'] = $user_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->hasCampusId && $this->campus_id) {
            $data['campus_id'] = $this->campus_id;
        }
        
        // Convert config array to JSON if it's an array
        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }
        
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO campus_widgets (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        
        try {
            $this->db->query($sql, $data);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("WidgetManager createWidget error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update widget
     */
    public function updateWidget($id, $data) {
        if (!$this->tableExists('campus_widgets')) {
            return false;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Convert config array to JSON if it's an array
        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }
        
        $setClause = [];
        foreach (array_keys($data) as $field) {
            $setClause[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE campus_widgets SET " . implode(', ', $setClause) . " WHERE id = :id";
        $data['id'] = $id;
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = :campus_id";
            $data['campus_id'] = $this->campus_id;
        }
        
        try {
            $result = $this->db->query($sql, $data);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            error_log("WidgetManager updateWidget error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete widget
     */
    public function deleteWidget($id) {
        if (!$this->tableExists('campus_widgets')) {
            return false;
        }
        
        $sql = "DELETE FROM campus_widgets WHERE id = ?";
        $params = [$id];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            error_log("WidgetManager deleteWidget error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single widget
     */
    public function getWidget($id) {
        if (!$this->tableExists('campus_widgets')) {
            return null;
        }
        
        $sql = "SELECT w.*, wt.name as type_name, wt.template_path, wt.default_template 
                FROM campus_widgets w 
                LEFT JOIN widget_types wt ON w.widget_type_id = wt.id 
                WHERE w.id = ?";
        $params = [$id];
        
        if ($this->hasCampusId && $this->campus_id) {
            $sql .= " AND w.campus_id = ?";
            $params[] = $this->campus_id;
        }
        
        try {
            $widget = $this->db->fetch($sql, $params);
            if ($widget && $widget['config']) {
                $widget['configuration'] = $widget['config']; // For form compatibility
            }
            return $widget;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update widget order
     */
    public function updateWidgetOrder($widgetOrders) {
        if (!$this->tableExists('campus_widgets') || empty($widgetOrders)) {
            error_log("WidgetManager updateWidgetOrder: Invalid input - table exists: " . ($this->tableExists('campus_widgets') ? 'yes' : 'no') . ", orders empty: " . (empty($widgetOrders) ? 'yes' : 'no'));
            return false;
        }
        
        try {
            $this->db->query("BEGIN");
            
            foreach ($widgetOrders as $widgetId => $orderData) {
                // Validate widget exists and belongs to current campus
                $checkSql = "SELECT id FROM campus_widgets WHERE id = ?";
                $checkParams = [$widgetId];
                
                if ($this->hasCampusId && $this->campus_id) {
                    $checkSql .= " AND campus_id = ?";
                    $checkParams[] = $this->campus_id;
                }
                
                $existingWidget = $this->db->fetch($checkSql, $checkParams);
                if (!$existingWidget) {
                    error_log("WidgetManager updateWidgetOrder: Widget $widgetId not found or not accessible");
                    continue;
                }
                
                // Update widget order and position
                $sql = "UPDATE campus_widgets SET sort_order = ?, position = ?, updated_at = NOW() WHERE id = ?";
                $params = [
                    $orderData['order'],
                    $orderData['location'],
                    $widgetId
                ];
                
                if ($this->hasCampusId && $this->campus_id) {
                    $sql .= " AND campus_id = ?";
                    $params[] = $this->campus_id;
                }
                
                $result = $this->db->query($sql, $params);
                error_log("WidgetManager updateWidgetOrder: Updated widget $widgetId to order {$orderData['order']} in location {$orderData['location']} - affected rows: " . $result->rowCount());
            }
            
            $this->db->query("COMMIT");
            return true;
            
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("WidgetManager updateWidgetOrder error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }
    
    /**
     * Render widget HTML
     */
    public function renderWidget($widget) {
        if (!$widget || !$widget['is_active']) {
            return '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Widget is inactive or not found.</div>';
        }

        // Try to use template file first, fallback to hardcoded rendering
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
                    require_once __DIR__ . '/../../core/functions/auth.php';
                }
                
                include $widget_dir . basename($template_path);
                return ob_get_clean();
            } catch (Exception $e) {
                ob_end_clean();
                error_log("Widget template error: " . $e->getMessage());
                // Fall through to hardcoded rendering
            }
        }

        // Fallback to hardcoded rendering for backward compatibility
        $config = json_decode($widget['config'] ?? '{}', true);
        $cssClass = $widget['css_class'] ? " {$widget['css_class']}" : '';
        
        $html = "<div class='widget widget-{$widget['id']}{$cssClass}' data-widget-id='{$widget['id']}'>";
        
        // Widget types that should never show titles
        $titlelessWidgetTypes = [
            'Image Widget', 
            'Image', 
            'Image & Hyperlink Text Widget',
            'Text Widget',
            'Media Widget'
        ];
        
        // For titleless widget types, never show title regardless of configuration
        if (!in_array($widget['type_name'], $titlelessWidgetTypes)) {
            $showTitle = $config['show_title'] ?? true;
            if (!empty($widget['title']) && $showTitle) {
                $html .= "<h3 class='widget-title'><i class='fas fa-puzzle-piece me-2'></i>" . htmlspecialchars($widget['title']) . "</h3>";
            }
        }
        // Note: titleless widgets never get a title, regardless of configuration
        
        $html .= "<div class='widget-content'>";
        
        // Render based on widget type
        switch ($widget['type_name']) {
            case 'Text Widget':
                $content = $config['content'] ?? 'No content specified.';
                $html .= "<div class='text-widget'>" . nl2br(htmlspecialchars($content)) . "</div>";
                break;
                
            case 'Image Widget':
                if (!empty($config['image_url'])) {
                    $alt = htmlspecialchars($config['alt_text'] ?? 'Widget Image');
                    $caption = htmlspecialchars($config['caption'] ?? '');
                    $link = $config['link_url'] ?? '';
                    
                    $imgHtml = "<img src='{$config['image_url']}' alt='{$alt}' class='img-fluid rounded shadow-sm'>";
                    
                    if ($link) {
                        $imgHtml = "<a href='{$link}' target='_blank'>{$imgHtml}</a>";
                    }
                    
                    $html .= "<div class='image-widget text-center'>{$imgHtml}";
                    
                    if ($caption) {
                        $html .= "<p class='image-caption mt-2'><i class='fas fa-image me-1'></i>{$caption}</p>";
                    }
                    $html .= "</div>";
                } else {
                    $html .= "<div class='alert alert-info'><i class='fas fa-image me-2'></i>No image specified. Please configure the image URL in widget settings.</div>";
                }
                break;
                
            case 'Featured Post Widget':
                // This will be handled by template file, but keep fallback
                $html .= "<div class='alert alert-info'><i class='fas fa-star me-2'></i>Featured Post Widget - Template not loaded</div>";
                break;
                
            case 'Recent Posts Widget':
            case 'Recent Posts':
                // This will be handled by template file, but keep fallback
                $count = (int)($config['count'] ?? 5);
                $html .= "<div class='recent-posts-widget'>";
                $html .= "<div class='alert alert-info'><i class='fas fa-newspaper me-2'></i>Recent Posts Widget - Please use admin to update widget type</div>";
                $html .= "</div>";
                break;
                
            case 'Navigation Menu':
                $menuLocation = htmlspecialchars($config['menu_location'] ?? 'main');
                $showIcons = $config['show_icons'] ?? false;
                
                $html .= "<div class='navigation-widget'>";
                $html .= "<div class='alert alert-info'><i class='fas fa-bars me-2'></i>Navigation Menu Widget Preview</div>";
                $html .= "<p class='text-muted'>Menu Location: {$menuLocation}</p>";
                
                // Mock navigation menu
                $html .= "<nav class='navbar navbar-expand-lg navbar-light bg-light rounded'>";
                $html .= "<div class='navbar-nav'>";
                $menuItems = ['Home', 'About', 'Services', 'Contact'];
                foreach ($menuItems as $item) {
                    $icon = $showIcons ? "<i class='fas fa-chevron-right me-1'></i>" : "";
                    $html .= "<a class='nav-link' href='#'>{$icon}{$item}</a>";
                }
                $html .= "</div></nav>";
                $html .= "</div>";
                break;
                
            case 'Contact Info':
                $phone = htmlspecialchars($config['phone'] ?? '');
                $email = htmlspecialchars($config['email'] ?? '');
                $address = htmlspecialchars($config['address'] ?? '');
                $showMap = $config['show_map'] ?? false;
                
                $html .= "<div class='contact-widget'>";
                
                if ($phone || $email || $address) {
                    if ($phone) $html .= "<p class='mb-2'><i class='fas fa-phone text-primary me-2'></i><strong>Phone:</strong> {$phone}</p>";
                    if ($email) $html .= "<p class='mb-2'><i class='fas fa-envelope text-primary me-2'></i><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>";
                    if ($address) $html .= "<p class='mb-2'><i class='fas fa-map-marker-alt text-primary me-2'></i><strong>Address:</strong> {$address}</p>";
                    
                    if ($showMap && $address) {
                        $html .= "<div class='alert alert-secondary mt-3'><i class='fas fa-map me-2'></i>Map integration would be displayed here</div>";
                    }
                } else {
                    $html .= "<div class='alert alert-info'><i class='fas fa-address-card me-2'></i>No contact information configured. Please add phone, email, or address in widget settings.</div>";
                }
                
                $html .= "</div>";
                break;
                
            default:
                $html .= "<div class='alert alert-secondary'>";
                $html .= "<h6><i class='fas fa-puzzle-piece me-2'></i>Widget Type: " . htmlspecialchars($widget['type_name']) . "</h6>";
                $html .= "<p class='mb-0'>This widget type is not yet implemented for preview.</p>";
                if (!empty($config)) {
                    $html .= "<details class='mt-2'>";
                    $html .= "<summary>Configuration</summary>";
                    $html .= "<pre class='mt-2 small'>" . htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT)) . "</pre>";
                    $html .= "</details>";
                }
                $html .= "</div>";
        }
        
        $html .= "</div></div>";
        
        return $html;
    }
}
?>
