<?php
/**
 * View Single Post
 * Simple post viewer for admin area
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Get post ID
$post_id = $_GET['id'] ?? 0;

// Get post data
$sql = "SELECT p.*, u.first_name, u.last_name, u.email 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.id = ? AND p.campus_id = ?";
$post = $db->fetch($sql, [$post_id, current_campus_id()]);

if (!$post) {
    header('Location: index.php');
    exit;
}

$page_title = $post['title'];
include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">View Post</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Posts</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($post['title']); ?></li>
            </ol>
        </div>
        <div>
            <a href="view-posts.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Posts
            </a>
            <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Post
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Post Content -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-<?php 
                                echo $post['status'] === 'published' ? 'success' : 
                                     ($post['status'] === 'pending' ? 'warning' : 'secondary'); 
                            ?>">
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                            <?php if ($post['featured']): ?>
                                <span class="badge bg-primary">Featured</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            Created: <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <?php if ($post['excerpt']): ?>
                        <div class="lead mb-3">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($post['featured_image_url']): ?>
                        <div class="mb-3">
                            <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <?php if ($post['tags']): ?>
                        <div class="mt-4">
                            <h6>Tags:</h6>
                            <?php 
                            $tags = array_map('trim', explode(',', $post['tags']));
                            foreach ($tags as $tag): 
                            ?>
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Post Meta -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Post Information
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Author:</strong></td>
                            <td><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $post['status'] === 'published' ? 'success' : 
                                         ($post['status'] === 'pending' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></td>
                        </tr>
                        <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($post['updated_at'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($post['published_at']): ?>
                        <tr>
                            <td><strong>Published:</strong></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($post['published_at'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Slug:</strong></td>
                            <td><code><?php echo htmlspecialchars($post['slug']); ?></code></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- SEO Information -->
            <?php if ($post['meta_title'] || $post['meta_description']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-search me-1"></i>
                    SEO Information
                </div>
                <div class="card-body">
                    <?php if ($post['meta_title']): ?>
                        <div class="mb-2">
                            <strong>Meta Title:</strong><br>
                            <small><?php echo htmlspecialchars($post['meta_title']); ?></small>
                        </div>
                    <?php endif; ?>
                    <?php if ($post['meta_description']): ?>
                        <div class="mb-2">
                            <strong>Meta Description:</strong><br>
                            <small><?php echo htmlspecialchars($post['meta_description']); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs me-1"></i>
                    Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Post
                        </a>
                        
                        <?php if ($post['status'] === 'published'): ?>
                            <a href="../../../<?php echo $current_campus['code']; ?>/public/post.php?slug=<?php echo $post['slug']; ?>" 
                               target="_blank" class="btn btn-success">
                                <i class="fas fa-external-link-alt"></i> View Live Post
                            </a>
                        <?php endif; ?>
                        
                        <a href="view-posts.php" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> All Posts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.post-content {
    line-height: 1.6;
}

.post-content h1, .post-content h2, .post-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.post-content p {
    margin-bottom: 1rem;
}

.post-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
    border-radius: 0.375rem;
}

.post-content blockquote {
    border-left: 4px solid #dee2e6;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}
</style>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
