<?php
/**
 * Sync featured data between columns
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Syncing featured data...\n";
    
    // Copy data from featured to is_featured
    $result = $db->query("UPDATE posts SET is_featured = featured WHERE featured = 1");
    
    echo "✅ Synced featured posts data\n";
    
    // Show current featured posts
    $featured_posts = $db->fetchAll("SELECT id, title, featured, is_featured FROM posts WHERE is_featured = 1 OR featured = 1");
    
    echo "\nFeatured posts:\n";
    echo "=============\n";
    foreach($featured_posts as $post) {
        echo "ID: {$post['id']} - Title: {$post['title']} - Old Featured: {$post['featured']} - New Featured: {$post['is_featured']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
