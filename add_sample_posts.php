<?php
/**
 * Add sample posts with featured images for testing
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Adding sample posts with featured images...\n";
    
    // Get campus IDs
    $campuses = $db->fetchAll("SELECT id, name FROM campuses LIMIT 3");
    
    foreach ($campuses as $campus) {
        // Get an admin user for this campus
        $admin = $db->fetch("SELECT id FROM users WHERE campus_id = ? AND role IN ('campus_admin', 'super_admin') LIMIT 1", [$campus['id']]);
        
        if (!$admin) {
            echo "No admin found for {$campus['name']}, skipping...\n";
            continue;
        }
        
        // Add a sample post with featured image
        $post_data = [
            'campus_id' => $campus['id'],
            'author_id' => $admin['id'],
            'title' => 'Welcome to ' . $campus['name'],
            'slug' => 'welcome-to-' . strtolower(str_replace(' ', '-', $campus['name'])),
            'content' => '<p>Welcome to our campus! We are excited to share our latest updates and news with you.</p><p>This is a sample post with featured content to showcase our campus website capabilities.</p>',
            'excerpt' => 'Welcome to our campus! We are excited to share our latest updates and news with you.',
            'status' => 'published',
            'is_featured' => 1,
            'featured_image_url' => 'https://via.placeholder.com/800x400/0066cc/ffffff?text=' . urlencode($campus['name']),
            'published_at' => date('Y-m-d H:i:s')
        ];
        
        $db->query("INSERT INTO posts (campus_id, author_id, title, slug, content, excerpt, status, is_featured, featured_image_url, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                  [$post_data['campus_id'], $post_data['author_id'], $post_data['title'], $post_data['slug'], $post_data['content'], $post_data['excerpt'], $post_data['status'], $post_data['is_featured'], $post_data['featured_image_url'], $post_data['published_at']]);
        
        echo "✅ Added sample post for {$campus['name']}\n";
    }
    
    echo "\n✅ Sample posts with featured images added successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
