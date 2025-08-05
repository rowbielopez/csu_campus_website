<?php
/**
 * Delete Post
 * Handles post deletion with proper permissions
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
$post_id = intval($_GET['id'] ?? 0);

if (!$post_id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Invalid post ID.'
    ];
    header('Location: index.php');
    exit;
}

// Get post details with permission check
$sql = "SELECT * FROM posts WHERE id = ?";
$params = [$post_id];

// Add campus isolation for non-super admins
if (!is_super_admin()) {
    $sql .= " AND campus_id = ?";
    $params[] = current_campus_id();
}

$post = $db->fetch($sql, $params);

if (!$post) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Post not found or you do not have permission to delete it.'
    ];
    header('Location: index.php');
    exit;
}

// Check permissions
$can_delete = false;

// Super admin can delete any post
if (is_super_admin()) {
    $can_delete = true;
}
// Campus admin can delete any post in their campus
elseif (is_campus_admin()) {
    $can_delete = true;
}
// Authors can delete their own posts
elseif ($post['author_id'] == $current_user['id']) {
    $can_delete = true;
}

if (!$can_delete) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to delete this post.'
    ];
    header('Location: index.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Delete the post
        $db->query("DELETE FROM posts WHERE id = ?", [$post_id]);
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Post "' . htmlspecialchars($post['title']) . '" has been deleted successfully.'
        ];
        
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Error deleting post: ' . $e->getMessage()
        ];
    }
}

$page_title = 'Delete Post';
$page_description = 'Confirm post deletion';

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
                <li class="breadcrumb-item active">Delete Post</li>
            </ol>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Posts
            </a>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm Deletion
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Warning!</h5>
                        You are about to permanently delete this post. This action cannot be undone.
                    </div>
                    
                    <div class="post-preview">
                        <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                        <div class="text-muted mb-3">
                            <small>
                                Created: <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                                <?php if ($post['published_at']): ?>
                                    | Published: <?php echo date('F j, Y \a\t g:i A', strtotime($post['published_at'])); ?>
                                <?php endif; ?>
                                | Status: <span class="badge bg-<?php 
                                    echo $post['status'] === 'published' ? 'success' : 
                                        ($post['status'] === 'pending' ? 'warning' : 
                                         ($post['status'] === 'archived' ? 'secondary' : 'info')); 
                                ?>"><?php echo ucfirst($post['status']); ?></span>
                            </small>
                        </div>
                        
                        <?php if ($post['excerpt']): ?>
                            <div class="post-excerpt">
                                <strong>Excerpt:</strong>
                                <p class="text-muted"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            
                            <button type="submit" name="confirm_delete" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Yes, Delete This Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
