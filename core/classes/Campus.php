<?php
/**
 * Campus Model - Core model for campus-specific data handling
 */

class Campus {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get current campus information
     */
    public function getCurrentCampus() {
        $sql = "SELECT * FROM campuses WHERE id = :campus_id";
        $result = $this->db->query($sql, ['campus_id' => CAMPUS_ID]);
        return $result->fetch();
    }
    
    /**
     * Get all campuses
     */
    public function getAllCampuses() {
        $sql = "SELECT * FROM campuses WHERE status = 1 ORDER BY name";
        $result = $this->db->query($sql);
        return $result->fetchAll();
    }
    
    /**
     * Update campus settings
     */
    public function updateCampusSettings($data) {
        $allowed_fields = [
            'name', 'full_name', 'address', 'phone', 'email',
            'logo_path', 'favicon_path', 'primary_color', 'secondary_color',
            'timezone', 'language'
        ];
        
        $update_data = [];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }
        
        if (!empty($update_data)) {
            return $this->db->updateCampusRecord('campuses', $update_data, CAMPUS_ID);
        }
        
        return false;
    }
    
    /**
     * Get campus statistics
     */
    public function getCampusStats() {
        $stats = [];
        
        // Count users
        $sql = "SELECT COUNT(*) as count FROM users WHERE campus_id = :campus_id AND status = 1";
        $result = $this->db->query($sql, ['campus_id' => CAMPUS_ID]);
        $stats['users'] = $result->fetch()['count'];
        
        // Count published pages
        $sql = "SELECT COUNT(*) as count FROM pages WHERE campus_id = :campus_id AND status = 1";
        $result = $this->db->query($sql, ['campus_id' => CAMPUS_ID]);
        $stats['pages'] = $result->fetch()['count'];
        
        // Count published posts
        $sql = "SELECT COUNT(*) as count FROM posts WHERE campus_id = :campus_id AND status = 1";
        $result = $this->db->query($sql, ['campus_id' => CAMPUS_ID]);
        $stats['posts'] = $result->fetch()['count'];
        
        // Count media files
        $sql = "SELECT COUNT(*) as count FROM media WHERE campus_id = :campus_id";
        $result = $this->db->query($sql, ['campus_id' => CAMPUS_ID]);
        $stats['media'] = $result->fetch()['count'];
        
        return $stats;
    }
}
?>
