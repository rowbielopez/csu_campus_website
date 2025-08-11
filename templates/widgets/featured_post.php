<?php
/**
 * Featured Post Widget Template
 * Displays a single post with full content, image, author and date
 */

// Get widget configuration
$config = json_decode($widget['config'] ?? '{}', true);
$post_id = $config['post_id'] ?? '';
$show_image = $config['show_image'] ?? true;
$show_author = $config['show_author'] ?? true;
$show_date = $config['show_date'] ?? true;
$show_excerpt = $config['show_excerpt'] ?? true;
$show_full_content = $config['show_full_content'] ?? false;

// Get database instance
$db = Database::getInstance();

// If no specific post ID is set, get the latest post
if (empty($post_id)) {
    $post = $db->fetch("
        SELECT p.*, u.first_name, u.last_name, u.email 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.status = 'published' AND p.campus_id = ? 
        ORDER BY p.published_at DESC 
        LIMIT 1
    ", [current_campus_id()]);
} else {
    $post = $db->fetch("
        SELECT p.*, u.first_name, u.last_name, u.email 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.id = ? AND p.status = 'published' AND p.campus_id = ?
    ", [$post_id, current_campus_id()]);
}

if (!$post) {
    echo '<div class="alert alert-info">No featured post available.</div>';
    return;
}

// Format date
$post_date = $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : date('M j, Y', strtotime($post['created_at']));
$author_name = trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? '')) ?: 'Anonymous';
?>

<article class="featured-post-widget">
    <?php if ($show_image && !empty($post['featured_image_url'])): ?>
        <div class="featured-post-image mb-3">
            <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                 class="img-fluid rounded">
        </div>
    <?php endif; ?>
    
    <div class="featured-post-content">
        <h2 class="featured-post-title h4 mb-2">
            <a href="/campus_website2/post/<?php echo htmlspecialchars($post['slug']); ?>" 
               class="text-decoration-none">
                <?php echo htmlspecialchars($post['title']); ?>
            </a>
        </h2>
        
        <?php if ($show_date || $show_author): ?>
            <div class="featured-post-meta mb-3 text-muted small">
                <?php if ($show_author): ?>
                    <span class="post-author">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($author_name); ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($show_date): ?>
                    <?php if ($show_author): ?>
                        <span class="mx-2">•</span>
                    <?php endif; ?>
                    <span class="post-date">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo $post_date; ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="featured-post-text">
            <?php if ($show_full_content): ?>
                <?php echo $post['content']; ?>
            <?php elseif ($show_excerpt): ?>
                <p><?php echo htmlspecialchars($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 150) . '...'); ?></p>
                <a href="/campus_website2/post/<?php echo htmlspecialchars($post['slug']); ?>" 
                   class="btn btn-primary btn-sm">
                    Read More <i class="fas fa-arrow-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>

<style>
.featured-post-widget {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.featured-post-widget .featured-post-title a {
    color: #2c3e50;
    transition: color 0.3s ease;
}

.featured-post-widget .featured-post-title a:hover {
    color: #3498db;
}

.featured-post-widget .featured-post-meta {
    font-size: 0.9rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}

.featured-post-widget .featured-post-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
</style>
