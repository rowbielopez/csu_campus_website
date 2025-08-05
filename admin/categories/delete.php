<?php
/**
 * Delete Category
 * Handles category deletion with proper checks
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Check permissions - only campus admins and super admins can manage categories
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to delete categories.'
    ];
    header('Location: ../index.php');
    exit;
}

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Get category ID
$category_id = intval($_GET['id'] ?? 0);

if (!$category_id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Invalid category ID.'
    ];
    header('Location: index.php');
    exit;
}

// Get category details with permission check
$sql = "SELECT c.*, COUNT(DISTINCT pc.post_id) as post_count, COUNT(DISTINCT children.id) as child_count 
        FROM categories c 
        LEFT JOIN post_categories pc ON c.id = pc.category_id
        LEFT JOIN categories children ON c.id = children.parent_id
        WHERE c.id = ?";
$params = [$category_id];

// Add campus isolation for non-super admins
if (!is_super_admin()) {
    $sql .= " AND c.campus_id = ?";
    $params[] = current_campus_id();
}

$sql .= " GROUP BY c.id";

$category = $db->fetch($sql, $params);

if (!$category) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Category not found or you do not have permission to delete it.'
    ];
    header('Location: index.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check if category has child categories
        if ($category['child_count'] > 0) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Cannot delete category with subcategories. Please delete or move subcategories first.'
            ];
            header('Location: index.php');
            exit;
        }
        
        // Check if posts are using this category
        if ($category['post_count'] > 0) {
            // Option 1: Prevent deletion if posts exist
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Cannot delete category that is being used by ' . $category['post_count'] . ' post(s). Please reassign or delete the posts first.'
            ];
            header('Location: index.php');
            exit;
            
            // Option 2: Remove associations (uncomment if you prefer this approach)
            /*
            $db->query("DELETE FROM post_categories WHERE category_id = ?", [$category_id]);
            */
        }
        
        // Delete the category
        $db->query("DELETE FROM categories WHERE id = ?", [$category_id]);
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Category "' . htmlspecialchars($category['name']) . '" has been deleted successfully.'
        ];
        
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Error deleting category: ' . $e->getMessage()
        ];
    }
}

// Get child categories
$children = [];
if ($category['child_count'] > 0) {
    $children = $db->fetchAll("SELECT name FROM categories WHERE parent_id = ? ORDER BY name", [$category_id]);
}

// Get posts using this category
$posts = [];
if ($category['post_count'] > 0) {
    $posts = $db->fetchAll("
        SELECT p.id, p.title 
        FROM posts p 
        INNER JOIN post_categories pc ON p.id = pc.post_id 
        WHERE pc.category_id = ? 
        ORDER BY p.title 
        LIMIT 10", [$category_id]);
}

$page_title = 'Delete Category';
$page_description = 'Confirm category deletion';

include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4"><?php echo $page_title; ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Categories</a></li>
                <li class="breadcrumb-item active">Delete Category</li>
            </ol>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
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
                        You are about to permanently delete this category. This action cannot be undone.
                    </div>
                    
                    <div class="category-preview">
                        <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                        <div class="text-muted mb-3">
                            <small>
                                Created: <?php echo date('F j, Y \a\t g:i A', strtotime($category['created_at'])); ?>
                                <?php if ($category['updated_at']): ?>
                                    | Last Updated: <?php echo date('F j, Y \a\t g:i A', strtotime($category['updated_at'])); ?>
                                <?php endif; ?>
                                | Status: <span class="badge bg-<?php echo $category['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </small>
                        </div>
                        
                        <?php if ($category['description']): ?>
                            <div class="category-description mb-3">
                                <strong>Description:</strong>
                                <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Slug:</strong>
                                    <code><?php echo htmlspecialchars($category['slug']); ?></code>
                                </div>
                                <div class="mb-3">
                                    <strong>Sort Order:</strong>
                                    <?php echo $category['sort_order']; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Posts Using Category:</strong>
                                    <span class="badge bg-info"><?php echo $category['post_count']; ?></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Subcategories:</strong>
                                    <span class="badge bg-warning"><?php echo $category['child_count']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Show blocking conditions -->
                    <?php if ($category['child_count'] > 0): ?>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Cannot Delete - Has Subcategories</h6>
                            <p class="mb-2">This category has <?php echo $category['child_count']; ?> subcategory(ies):</p>
                            <ul class="mb-0">
                                <?php foreach ($children as $child): ?>
                                    <li><?php echo htmlspecialchars($child['name']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="mt-2 mb-0"><strong>Action Required:</strong> Delete or move subcategories to another parent first.</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($category['post_count'] > 0): ?>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Cannot Delete - Used by Posts</h6>
                            <p class="mb-2">This category is being used by <?php echo $category['post_count']; ?> post(s):</p>
                            <ul class="mb-0">
                                <?php foreach ($posts as $post): ?>
                                    <li>
                                        <a href="../posts/edit.php?id=<?php echo $post['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($category['post_count'] > 10): ?>
                                    <li class="text-muted">... and <?php echo $category['post_count'] - 10; ?> more</li>
                                <?php endif; ?>
                            </ul>
                            <p class="mt-2 mb-0"><strong>Action Required:</strong> Reassign posts to other categories or delete them first.</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            
                            <?php if ($category['child_count'] == 0 && $category['post_count'] == 0): ?>
                                <button type="submit" name="confirm_delete" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Yes, Delete This Category
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-danger" disabled>
                                    <i class="fas fa-ban"></i> Cannot Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($category['child_count'] > 0 || $category['post_count'] > 0): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-lightbulb me-1"></i>
                        What you can do
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($category['child_count'] > 0): ?>
                                <div class="col-md-6">
                                    <h6>Handle Subcategories:</h6>
                                    <ul class="small">
                                        <li>Edit each subcategory to change its parent</li>
                                        <li>Delete subcategories first (if they're not being used)</li>
                                        <li>Move content from subcategories to other categories</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($category['post_count'] > 0): ?>
                                <div class="col-md-6">
                                    <h6>Handle Posts:</h6>
                                    <ul class="small">
                                        <li>Edit posts to assign different categories</li>
                                        <li>Use bulk edit to reassign multiple posts</li>
                                        <li>Delete posts that are no longer needed</li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> Manage Categories
                            </a>
                            <?php if ($category['post_count'] > 0): ?>
                                <a href="../posts/index.php?category=<?php echo $category['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-edit"></i> View Posts in Category
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
