<?php
/**
 * Recent Posts Widget Template
 * Displays a list of recent posts with excerpts, images, authors and dates
 */

// Get widget configuration
$config = json_decode($widget['config'] ?? '{}', true);
$count = $config['count'] ?? 5;
$show_excerpt = $config['show_excerpt'] ?? true;
$show_date = $config['show_date'] ?? true;
$show_author = $config['show_author'] ?? true;
$show_image = $config['show_image'] ?? true;

// Get database instance
$db = Database::getInstance();

// Get recent posts
$posts = $db->fetchAll("
    SELECT p.*, u.first_name, u.last_name, u.email 
    FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id 
    WHERE p.status = 'published' AND p.campus_id = ? 
    ORDER BY p.published_at DESC 
    LIMIT ?
", [current_campus_id(), $count]);

if (empty($posts)) {
    echo '<div class="alert alert-info">No recent posts available.</div>';
    return;
}
?>

<div class="recent-posts-widget">
    <?php foreach ($posts as $index => $post): ?>
        <?php 
        $post_date = $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : date('M j, Y', strtotime($post['created_at']));
        $author_name = trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? '')) ?: 'Anonymous';
        ?>
        
        <article class="recent-post-item <?php echo $index === 0 ? 'featured' : ''; ?> mb-4">
            <?php if ($show_image && !empty($post['featured_image_url'])): ?>
                <div class="post-image <?php echo $index === 0 ? 'mb-3' : 'mb-2'; ?>">
                    <a href="/campus_website2/post/<?php echo htmlspecialchars($post['slug']); ?>">
                        <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="img-fluid rounded <?php echo $index === 0 ? 'featured-img' : 'thumb-img'; ?>">
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="post-content">
                <h3 class="post-title <?php echo $index === 0 ? 'h4' : 'h6'; ?> mb-2">
                    <a href="/campus_website2/post/<?php echo htmlspecialchars($post['slug']); ?>" 
                       class="text-decoration-none">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a>
                </h3>
                
                <?php if ($show_date || $show_author): ?>
                    <div class="post-meta mb-2 text-muted small">
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
                
                <?php if ($show_excerpt && ($index === 0 || $count <= 3)): ?>
                    <p class="post-excerpt small text-muted mb-2">
                        <?php echo htmlspecialchars($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 100) . '...'); ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($index === 0): ?>
                    <a href="/campus_website2/post/<?php echo htmlspecialchars($post['slug']); ?>" 
                       class="btn btn-primary btn-sm">
                        Read More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        
        <?php if ($index < count($posts) - 1): ?>
            <hr class="my-3">
        <?php endif; ?>
    <?php endforeach; ?>
    
    <div class="text-center mt-3">
        <a href="/campus_website2/posts" class="btn btn-outline-primary btn-sm">
            View All Posts <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
</div>

<style>
.recent-posts-widget {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.recent-posts-widget .recent-post-item.featured {
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
    margin-bottom: 1.5rem !important;
}

.recent-posts-widget .post-title a {
    color: #2c3e50;
    transition: color 0.3s ease;
}

.recent-posts-widget .post-title a:hover {
    color: #3498db;
}

.recent-posts-widget .featured-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.recent-posts-widget .thumb-img {
    width: 80px;
    height: 60px;
    object-fit: cover;
    float: left;
    margin-right: 1rem;
}

.recent-posts-widget .post-meta {
    font-size: 0.85rem;
}

.recent-posts-widget .post-excerpt {
    line-height: 1.4;
}

.recent-posts-widget hr {
    border-color: #eee;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .recent-posts-widget .thumb-img {
        width: 60px;
        height: 45px;
        margin-right: 0.75rem;
    }
}
</style>
