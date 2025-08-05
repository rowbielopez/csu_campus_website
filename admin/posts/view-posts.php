<?php
/**
 * Simple Posts Viewer
 * Quick way to view posts you've created
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

// Get posts for current campus
$sql = "SELECT p.*, u.first_name, u.last_name 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE p.campus_id = ? 
        ORDER BY p.created_at DESC";
$posts = $db->fetchAll($sql, [current_campus_id()]);

$page_title = 'View Posts';
include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4"><?php echo $page_title; ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Posts</a></li>
                <li class="breadcrumb-item active">View Posts</li>
            </ol>
        </div>
        <div>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Post
            </a>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            <h5>No posts found</h5>
            <p>You haven't created any posts yet. <a href="create.php">Create your first post</a> to get started!</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <?php if ($post['featured_image_url']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
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
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            
                            <?php if ($post['excerpt']): ?>
                                <p class="card-text"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                                    on <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </small>
                                
                                <div class="mt-2">
                                    <a href="view-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Full Post
                                    </a>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($post['status'] === 'published'): ?>
                                        <a href="../../../<?php echo $current_campus['code']; ?>/public/post.php?slug=<?php echo $post['slug']; ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-external-link-alt"></i> View Live
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
