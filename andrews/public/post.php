<?php
/**
 * Single Post Detail Page
 * Display individual post with full content
 */

// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();

// Get post by slug or ID
$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? '';

if (!$slug && !$id) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Get the post
if ($slug) {
    $post = get_campus_post($slug, true);
} else {
    $post = get_campus_post($id, false);
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Page meta
$page_title = $post['title'] . ' - ' . $campus['name'];
$page_description = $post['excerpt'] ?: get_excerpt($post['content'], 160);

// Get related posts
$db = Database::getInstance();
$related_posts = $db->fetchAll("
    SELECT p.*, u.username as author_name 
    FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.campus_id = ? AND p.status = 'published' AND p.id != ?
    ORDER BY p.published_at DESC 
    LIMIT 3
", [$campus['id'], $post['id']]);

include 'layouts/header.php';
?>

<!-- Breadcrumbs -->
<div class="container py-3">
    <?php 
    $breadcrumbs = [
        ['title' => 'News & Updates', 'url' => 'posts.php'],
        ['title' => $post['title'], 'url' => '']
    ];
    echo get_breadcrumbs($breadcrumbs); 
    ?>
</div>

<!-- Main Content -->
<div class="container py-4">
    <div class="row">
        <!-- Post Content -->
        <div class="col-lg-8">
            <article class="post-detail">
                <!-- Post Header -->
                <header class="mb-4">
                    <h1 class="display-5 fw-bold text-primary mb-3">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h1>
                    
                    <?php if (isset($post['is_featured']) && $post['is_featured']): ?>
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark fs-6">Featured Post</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-meta text-muted mb-4">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="me-4 mb-2">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-person me-2" viewBox="0 0 16 16">
                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                </svg>
                                <strong>By:</strong> <?php echo htmlspecialchars($post['author_name']); ?>
                            </div>
                            <div class="me-4 mb-2">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-calendar me-2" viewBox="0 0 16 16">
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5 0zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                </svg>
                                <strong>Published:</strong> <?php echo date('F j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                            </div>
                            <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                                <div class="me-4 mb-2">
                                    <svg width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise me-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                    </svg>
                                    <strong>Updated:</strong> <?php echo date('F j, Y', strtotime($post['updated_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
                
                <!-- Featured Image -->
                <?php if (isset($post['featured_image_url']) && $post['featured_image_url']): ?>
                    <div class="mb-4">
                        <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                             class="img-fluid rounded shadow-sm" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             style="width: 100%; max-height: 400px; object-fit: cover;">
                    </div>
                <?php endif; ?>
                
                <!-- Post Excerpt -->
                <?php if ($post['excerpt']): ?>
                    <div class="post-excerpt bg-light p-4 rounded mb-4">
                        <p class="lead mb-0"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Post Content -->
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Post Footer -->
                <footer class="post-footer mt-5 pt-4 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="post-author">
                                <h6 class="text-primary mb-2">About the Author</h6>
                                <div class="d-flex align-items-center">
                                    <div class="author-avatar me-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold" 
                                             style="width: 50px; height: 50px;">
                                            <?php echo strtoupper(substr($post['author_name'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($post['author_name']); ?></div>
                                        <small class="text-muted">Content Author</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="post-share">
                                <h6 class="text-primary mb-2">Share this post</h6>
                                <div class="btn-group" role="group">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <svg width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                            <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                        </svg>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <svg width="16" height="16" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16">
                                            <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
                                        </svg>
                                    </a>
                                    <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <svg width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </article>
            
            <!-- Navigation to Previous/Next Posts -->
            <nav class="post-navigation mt-5">
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        $prev_post = $db->fetch("
                            SELECT slug, title FROM posts 
                            WHERE campus_id = ? AND status = 'published' AND id < ? 
                            ORDER BY id DESC LIMIT 1
                        ", [$campus['id'], $post['id']]);
                        
                        if ($prev_post):
                        ?>
                            <a href="post.php?slug=<?php echo urlencode($prev_post['slug']); ?>" 
                               class="btn btn-outline-primary">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                </svg>
                                Previous Post
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <?php
                        $next_post = $db->fetch("
                            SELECT slug, title FROM posts 
                            WHERE campus_id = ? AND status = 'published' AND id > ? 
                            ORDER BY id ASC LIMIT 1
                        ", [$campus['id'], $post['id']]);
                        
                        if ($next_post):
                        ?>
                            <a href="post.php?slug=<?php echo urlencode($next_post['slug']); ?>" 
                               class="btn btn-outline-primary">
                                Next Post
                                <svg width="16" height="16" fill="currentColor" class="bi bi-arrow-right ms-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <aside class="sidebar">
                <!-- Related Posts -->
                <?php if (!empty($related_posts)): ?>
                    <div class="widget mb-4">
                        <h5 class="widget-title">Related Posts</h5>
                        <div class="widget-content">
                            <?php foreach ($related_posts as $related_post): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <a href="post.php?slug=<?php echo urlencode($related_post['slug']); ?>" 
                                       class="text-decoration-none">
                                        <div class="fw-semibold text-dark mb-1">
                                            <?php echo htmlspecialchars($related_post['title']); ?>
                                        </div>
                                    </a>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($related_post['published_at'] ?: $related_post['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Back to Posts -->
                <div class="widget mb-4">
                    <div class="widget-content">
                        <a href="posts.php" class="btn btn-primary w-100">
                            <svg width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                            Back to All Posts
                        </a>
                    </div>
                </div>
                
                <!-- Campus Info Widget -->
                <?php echo render_widget([
                    'type' => 'contact_info',
                    'title' => 'Campus Information',
                    'content' => ''
                ]); ?>
            </aside>
        </div>
    </div>
</div>

<!-- Additional CSS for post content -->
<style>
.post-content {
    line-height: 1.8;
    font-size: 1.1rem;
}

.post-content h1,
.post-content h2,
.post-content h3,
.post-content h4,
.post-content h5,
.post-content h6 {
    color: var(--campus-primary);
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    margin: 1rem 0;
}

.post-content blockquote {
    border-left: 4px solid var(--campus-primary);
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}

.post-content ul,
.post-content ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.post-content p {
    margin-bottom: 1.2rem;
}
</style>

<?php include 'layouts/footer.php'; ?>
