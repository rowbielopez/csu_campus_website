<?php
/**
 * Content Model - Handle pages and posts
 */

class Content {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get pages for current campus
     */
    public function getPages($conditions = [], $limit = null) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                FROM pages p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.campus_id = :campus_id";
        
        $params = ['campus_id' => CAMPUS_ID];
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND p.{$field} = :{$field}";
            $params[$field] = $value;
        }
        
        $sql .= " ORDER BY p.sort_order, p.title";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $result = $this->db->query($sql, $params);
        return $result->fetchAll();
    }
    
    /**
     * Get single page by slug
     */
    public function getPageBySlug($slug) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username 
                FROM pages p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.campus_id = :campus_id AND p.slug = :slug AND p.status = 1";
        
        $result = $this->db->query($sql, [
            'campus_id' => CAMPUS_ID,
            'slug' => $slug
        ]);
        
        return $result->fetch();
    }
    
    /**
     * Get posts for current campus
     */
    public function getPosts($conditions = [], $limit = null, $offset = 0) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username, c.name as category_name 
                FROM posts p 
                LEFT JOIN users u ON p.author_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.campus_id = :campus_id";
        
        $params = ['campus_id' => CAMPUS_ID];
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND p.{$field} = :{$field}";
            $params[$field] = $value;
        }
        
        $sql .= " ORDER BY p.published_at DESC, p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $result = $this->db->query($sql, $params);
        return $result->fetchAll();
    }
    
    /**
     * Get single post by slug
     */
    public function getPostBySlug($slug) {
        $sql = "SELECT p.*, u.first_name, u.last_name, u.username, c.name as category_name 
                FROM posts p 
                LEFT JOIN users u ON p.author_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.campus_id = :campus_id AND p.slug = :slug AND p.status = 1";
        
        $result = $this->db->query($sql, [
            'campus_id' => CAMPUS_ID,
            'slug' => $slug
        ]);
        
        $post = $result->fetch();
        
        if ($post) {
            // Increment view count
            $this->incrementPostViews($post['id']);
        }
        
        return $post;
    }
    
    /**
     * Create or update page
     */
    public function savePage($data, $id = null) {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title'], 'pages', $id);
        }
        
        if ($id) {
            return $this->db->updateCampusRecord('pages', $data, $id);
        } else {
            return $this->db->insertCampusRecord('pages', $data);
        }
    }
    
    /**
     * Create or update post
     */
    public function savePost($data, $id = null) {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title'], 'posts', $id);
        }
        
        // Set published_at if status is published and not set
        if ($data['status'] == STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        if ($id) {
            return $this->db->updateCampusRecord('posts', $data, $id);
        } else {
            return $this->db->insertCampusRecord('posts', $data);
        }
    }
    
    /**
     * Generate unique slug
     */
    private function generateSlug($title, $table, $exclude_id = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $table, $exclude_id)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $table, $exclude_id = null) {
        $sql = "SELECT id FROM {$table} WHERE campus_id = :campus_id AND slug = :slug";
        $params = ['campus_id' => CAMPUS_ID, 'slug' => $slug];
        
        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }
        
        $result = $this->db->query($sql, $params);
        return $result->fetch() !== false;
    }
    
    /**
     * Increment post view count
     */
    private function incrementPostViews($post_id) {
        $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = :id AND campus_id = :campus_id";
        $this->db->query($sql, [
            'id' => $post_id,
            'campus_id' => CAMPUS_ID
        ]);
    }
    
    /**
     * Get featured posts
     */
    public function getFeaturedPosts($limit = 5) {
        return $this->getPosts(['status' => STATUS_PUBLISHED, 'is_featured' => 1], $limit);
    }
    
    /**
     * Get recent posts
     */
    public function getRecentPosts($limit = 10) {
        return $this->getPosts(['status' => STATUS_PUBLISHED], $limit);
    }
    
    /**
     * Search content
     */
    public function searchContent($query, $type = 'all', $limit = 20) {
        $results = [];
        
        if ($type === 'all' || $type === 'pages') {
            $sql = "SELECT 'page' as type, id, title, slug, excerpt, created_at 
                    FROM pages 
                    WHERE campus_id = :campus_id AND status = 1 
                    AND (title LIKE :query OR content LIKE :query)
                    ORDER BY title";
            
            $result = $this->db->query($sql, [
                'campus_id' => CAMPUS_ID,
                'query' => "%{$query}%"
            ]);
            
            $results = array_merge($results, $result->fetchAll());
        }
        
        if ($type === 'all' || $type === 'posts') {
            $sql = "SELECT 'post' as type, id, title, slug, excerpt, published_at as created_at 
                    FROM posts 
                    WHERE campus_id = :campus_id AND status = 1 
                    AND (title LIKE :query OR content LIKE :query)
                    ORDER BY published_at DESC";
            
            $result = $this->db->query($sql, [
                'campus_id' => CAMPUS_ID,
                'query' => "%{$query}%"
            ]);
            
            $results = array_merge($results, $result->fetchAll());
        }
        
        // Sort by relevance (title matches first, then by date)
        usort($results, function($a, $b) use ($query) {
            $a_title_match = stripos($a['title'], $query) !== false;
            $b_title_match = stripos($b['title'], $query) !== false;
            
            if ($a_title_match && !$b_title_match) return -1;
            if (!$a_title_match && $b_title_match) return 1;
            
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($results, 0, $limit);
    }
}
?>
