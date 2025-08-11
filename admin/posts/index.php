<?php
/**
 * Posts Management - Main Listing
 * Role-based post management with campus isolation
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['post_ids'])) {
    $action = $_POST['bulk_action'];
    $post_ids = array_map('intval', $_POST['post_ids']);
    
    if (!empty($post_ids)) {
        switch ($action) {
            case 'delete':
                if (is_campus_admin() || is_super_admin()) {
                    $placeholders = str_repeat('?,', count($post_ids) - 1) . '?';
                    $params = $post_ids;
                    
                    if (!is_super_admin()) {
                        $sql = "DELETE FROM posts WHERE id IN ($placeholders) AND campus_id = ?";
                        $params[] = current_campus_id();
                    } else {
                        $sql = "DELETE FROM posts WHERE id IN ($placeholders)";
                    }
                    
                    $db->query($sql, $params);
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => count($post_ids) . ' posts deleted successfully.'];
                }
                break;
                
            case 'publish':
                if (is_campus_admin() || is_super_admin()) {
                    $placeholders = str_repeat('?,', count($post_ids) - 1) . '?';
                    $params = array_merge($post_ids, [date('Y-m-d H:i:s')]);
                    
                    if (!is_super_admin()) {
                        $sql = "UPDATE posts SET status = 'published', published_at = ? WHERE id IN ($placeholders) AND campus_id = ?";
                        $params[] = current_campus_id();
                    } else {
                        $sql = "UPDATE posts SET status = 'published', published_at = ? WHERE id IN ($placeholders)";
                    }
                    
                    $db->query($sql, $params);
                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => count($post_ids) . ' posts published successfully.'];
                }
                break;
        }
    }
    
    header('Location: index.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$author_filter = $_GET['author'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query based on role and filters
$where_conditions = [];
$params = [];

// Campus isolation (except for super admin)
if (!is_super_admin()) {
    $where_conditions[] = "p.campus_id = ?";
    $params[] = current_campus_id();
}

// Author filter (non-admin users can only see their own posts unless they're editors+)
if (!is_campus_admin() && !is_super_admin() && !is_editor()) {
    $where_conditions[] = "p.author_id = ?";
    $params[] = $current_user['id'];
} elseif ($author_filter) {
    $where_conditions[] = "p.author_id = ?";
    $params[] = $author_filter;
}

// Status filter
if ($status_filter) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

// Search filter
if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get posts with author and campus info
$sql = "
    SELECT p.*, u.username as author_name" . (is_super_admin() ? ", c.name as campus_name" : "") . "
    FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id" . 
    (is_super_admin() ? " LEFT JOIN campuses c ON p.campus_id = c.id" : "") . "
    $where_clause 
    ORDER BY p.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$posts = $db->fetchAll($sql, $params);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM posts p $where_clause";
$total_posts = $db->fetch($count_sql, $params)['total'];
$total_pages = ceil($total_posts / $per_page);

// Get authors for filter dropdown (campus-scoped)
$authors_sql = "SELECT DISTINCT u.id, u.username FROM users u INNER JOIN posts p ON u.id = p.author_id";
if (!is_super_admin()) {
    $authors_sql .= " WHERE p.campus_id = ?";
    $authors = $db->fetchAll($authors_sql, [current_campus_id()]);
} else {
    $authors = $db->fetchAll($authors_sql);
}

$page_title = 'Posts Management';
$page_description = 'Manage posts and content for your campus';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="edit-3"></i></div>
                        Posts Management
                    </h1>
                    <div class="page-header-subtitle">Manage posts and content for your campus</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <div class="btn-group">
                        <a href="view-posts.php" class="btn btn-outline-light">
                            <i class="me-2" data-feather="eye"></i>
                            View Posts
                        </a>
                        <a href="create.php" class="btn btn-light">
                            <i class="me-2" data-feather="plus"></i>
                            Add New Post
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main page content-->
<div class="container-xl px-4 mt-n10">
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show auto-dismiss" role="alert">
            <div class="d-flex align-items-center">
                <i class="me-2" data-feather="<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'check-circle' : 'alert-circle'; ?>"></i>
                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="me-2" data-feather="filter"></i>
            Filter Posts
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <?php if (is_editor() || is_campus_admin() || is_super_admin()): ?>
                    <div class="col-md-3">
                        <label for="author" class="form-label">Author</label>
                        <select name="author" id="author" class="form-select">
                            <option value="">All Authors</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?php echo $author['id']; ?>" <?php echo $author_filter == $author['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($author['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group input-group-joined">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-text"><i data-feather="search"></i></div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="me-1" data-feather="search"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table Card -->
    <div class="card">
        <div class="card-header card-header-actions">
            <div class="card-header-title">
                <i class="me-2" data-feather="database"></i>
                Posts (<?php echo number_format($total_posts); ?> total)
            </div>
            
            <?php if ((is_campus_admin() || is_super_admin()) && !empty($posts)): ?>
                <div class="bulk-actions" style="display: none;">
                    <form method="POST" class="d-inline" id="bulkForm">
                        <div class="input-group input-group-sm">
                            <select name="bulk_action" class="form-select">
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary" onclick="return confirmBulkAction()">
                                Apply
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="feather-xl text-gray-400" data-feather="edit-3"></i>
                    </div>
                    <h4 class="text-gray-700">No posts found</h4>
                    <p class="text-gray-500 mb-4">
                        <?php if ($status_filter || $author_filter || $search): ?>
                            Try adjusting your filters or <a href="index.php" class="text-primary">view all posts</a>.
                        <?php else: ?>
                            Get started by creating your first post.
                        <?php endif; ?>
                    </p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="me-2" data-feather="plus"></i>
                        Create First Post
                    </a>
                </div>
            <?php else: ?>
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <?php if (is_campus_admin() || is_super_admin()): ?>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                            <?php endif; ?>
                            <th>Title</th>
                            <th>Author</th>
                            <?php if (is_super_admin()): ?>
                                <th>Campus</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <?php if (is_campus_admin() || is_super_admin()): ?>
                                <th>Select</th>
                            <?php endif; ?>
                            <th>Title</th>
                            <th>Author</th>
                            <?php if (is_super_admin()): ?>
                                <th>Campus</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <?php if (is_campus_admin() || is_super_admin()): ?>
                                    <td>
                                        <input type="checkbox" name="post_ids[]" value="<?php echo $post['id']; ?>" 
                                               class="form-check-input post-checkbox">
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                            <?php if (isset($post['is_featured']) && $post['is_featured']): ?>
                                                <span class="badge bg-warning-soft text-warning ms-1">Featured</span>
                                            <?php endif; ?>
                                            <?php if ($post['excerpt']): ?>
                                                <div class="small text-gray-500"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 80)); ?>...</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <?php if (is_super_admin()): ?>
                                    <td><?php echo htmlspecialchars($post['campus_name'] ?? 'N/A'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="badge bg-<?php 
                                        echo $post['status'] === 'published' ? 'success' : 
                                            ($post['status'] === 'pending' ? 'warning' : 
                                             ($post['status'] === 'archived' ? 'secondary' : 'info')); 
                                    ?>-soft text-<?php 
                                        echo $post['status'] === 'published' ? 'success' : 
                                            ($post['status'] === 'pending' ? 'warning' : 
                                             ($post['status'] === 'archived' ? 'secondary' : 'info')); 
                                    ?> rounded-pill">
                                        <?php echo ucfirst($post['status']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                        <?php if ($post['published_at']): ?>
                                            <div class="text-gray-500">Published: <?php echo date('M j, Y', strtotime($post['published_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="window.location.href='edit.php?id=<?php echo $post['id']; ?>'" title="Edit">
                                        <i data-feather="edit"></i>
                                    </button>
                                    
                                    <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="window.open('view-post.php?id=<?php echo $post['id']; ?>', '_blank')" title="View Post">
                                        <i data-feather="eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Posts pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="me-2" data-feather="chevron-left"></i>
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Next
                                        <i class="ms-2" data-feather="chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Handle bulk actions
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const postCheckboxes = document.querySelectorAll('.post-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const bulkForm = document.getElementById('bulkForm');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            postCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }
    
    postCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
        if (bulkActions) {
            bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
        
        // Add selected post IDs to bulk form
        if (bulkForm) {
            // Remove existing hidden inputs
            bulkForm.querySelectorAll('input[name="post_ids[]"]').forEach(input => input.remove());
            
            // Add selected IDs
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'post_ids[]';
                input.value = checkbox.value;
                bulkForm.appendChild(input);
            });
        }
    }
});

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checkedCount = document.querySelectorAll('.post-checkbox:checked').length;
    
    if (!action) {
        alert('Please select an action.');
        return false;
    }
    
    if (checkedCount === 0) {
        alert('Please select at least one post.');
        return false;
    }
    
    const actionText = action === 'delete' ? 'delete' : action;
    return confirm(`Are you sure you want to ${actionText} ${checkedCount} selected post(s)?`);
}
</script>

<script>
// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const autoAlerts = document.querySelectorAll('.auto-dismiss');
    autoAlerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Bulk actions functionality
<?php if (is_campus_admin() || is_super_admin()): ?>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllBox = document.getElementById('selectAll');
    const postCheckboxes = document.querySelectorAll('.post-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const bulkForm = document.getElementById('bulkForm');

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function() {
            postCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }

    postCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
            if (selectAllBox) {
                selectAllBox.checked = checkedBoxes.length === postCheckboxes.length;
                selectAllBox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < postCheckboxes.length;
            }
            toggleBulkActions();
        });
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
        if (bulkActions) {
            bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
    }

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
            checkedBoxes.forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'post_ids[]';
                hiddenInput.value = checkbox.value;
                this.appendChild(hiddenInput);
            });
        });
    }
});

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
    
    if (!action) {
        alert('Please select a bulk action.');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one post.');
        return false;
    }
    
    const actionText = action === 'delete' ? 'delete' : 'publish';
    return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} post(s)?`);
}
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
