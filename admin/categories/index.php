<?php
/**
 * Categories Management - Main Listing
 * Manage content categories with hierarchy support
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
        'message' => 'You do not have permission to manage categories.'
    ];
    header('Location: ../index.php');
    exit;
}

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['category_ids'])) {
    $action = $_POST['bulk_action'];
    $category_ids = array_map('intval', $_POST['category_ids']);
    
    if (!empty($category_ids)) {
        switch ($action) {
            case 'delete':
                $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
                $params = $category_ids;
                
                if (!is_super_admin()) {
                    $sql = "DELETE FROM categories WHERE id IN ($placeholders) AND campus_id = ?";
                    $params[] = current_campus_id();
                } else {
                    $sql = "DELETE FROM categories WHERE id IN ($placeholders)";
                }
                
                $db->query($sql, $params);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => count($category_ids) . ' categories deleted successfully.'];
                break;
                
            case 'activate':
                $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
                $params = array_merge([1], $category_ids);
                
                if (!is_super_admin()) {
                    $sql = "UPDATE categories SET is_active = ? WHERE id IN ($placeholders) AND campus_id = ?";
                    $params[] = current_campus_id();
                } else {
                    $sql = "UPDATE categories SET is_active = ? WHERE id IN ($placeholders)";
                }
                
                $db->query($sql, $params);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => count($category_ids) . ' categories activated successfully.'];
                break;
                
            case 'deactivate':
                $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
                $params = array_merge([0], $category_ids);
                
                if (!is_super_admin()) {
                    $sql = "UPDATE categories SET is_active = ? WHERE id IN ($placeholders) AND campus_id = ?";
                    $params[] = current_campus_id();
                } else {
                    $sql = "UPDATE categories SET is_active = ? WHERE id IN ($placeholders)";
                }
                
                $db->query($sql, $params);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => count($category_ids) . ' categories deactivated successfully.'];
                break;
        }
    }
    
    header('Location: index.php');
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query based on role and filters
$where_conditions = [];
$params = [];

// Campus isolation (except for super admin)
if (!is_super_admin()) {
    $where_conditions[] = "c.campus_id = ?";
    $params[] = current_campus_id();
}

// Status filter
if ($status_filter !== '') {
    $where_conditions[] = "c.is_active = ?";
    $params[] = $status_filter;
}

// Search filter
if ($search) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get categories with parent info and post count
$sql = "
    SELECT c.*, 
           p.name as parent_name,
           campus.name as campus_name,
           COUNT(DISTINCT pc.post_id) as post_count
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id
    LEFT JOIN campuses campus ON c.campus_id = campus.id
    LEFT JOIN post_categories pc ON c.id = pc.category_id
    $where_clause 
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
";

$categories = $db->fetchAll($sql, $params);

// Build hierarchical structure
function buildCategoryTree($categories, $parent_id = null) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $category['children'] = buildCategoryTree($categories, $category['id']);
            $tree[] = $category;
        }
    }
    return $tree;
}

$category_tree = buildCategoryTree($categories);

$page_title = 'Categories Management';
$page_description = 'Manage content categories and taxonomy';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="folder"></i></div>
                        Categories Management
                    </h1>
                    <div class="page-header-subtitle">Organize content with categories and hierarchies</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a href="create.php" class="btn btn-light">
                        <i class="me-2" data-feather="plus"></i>
                        Add New Category
                    </a>
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
            Filter Categories
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-7">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group input-group-joined">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
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

    <!-- Categories Table Card -->
    <div class="card">
        <div class="card-header card-header-actions">
            <div class="card-header-title">
                <i class="me-2" data-feather="database"></i>
                Categories (<?php echo count($categories); ?> total)
            </div>
            
            <?php if (!empty($categories)): ?>
                <div class="bulk-actions" style="display: none;">
                    <form method="POST" class="d-inline" id="bulkForm">
                        <div class="input-group input-group-sm">
                            <select name="bulk_action" class="form-select">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
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
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="feather-xl text-gray-400" data-feather="folder"></i>
                    </div>
                    <h4 class="text-gray-700">No categories found</h4>
                    <p class="text-gray-500 mb-4">
                        <?php if ($status_filter !== '' || $search): ?>
                            Try adjusting your filters or <a href="index.php" class="text-primary">view all categories</a>.
                        <?php else: ?>
                            Get started by creating your first category.
                        <?php endif; ?>
                    </p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="me-2" data-feather="plus"></i>
                        Create First Category
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Category</th>
                                <th>Parent</th>
                                <?php if (is_super_admin()): ?>
                                    <th>Campus</th>
                                <?php endif; ?>
                                <th>Posts</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            function renderCategoryRows($categories, $level = 0) {
                                global $current_campus;
                                foreach ($categories as $category):
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level * 2);
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="category_ids[]" value="<?php echo $category['id']; ?>" 
                                               class="form-check-input category-checkbox">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($level > 0): ?>
                                                <span class="text-muted me-2"><?php echo $indent; ?>└─</span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </div>
                                                <?php if ($category['description']): ?>
                                                    <div class="small text-gray-500">
                                                        <?php echo htmlspecialchars(substr($category['description'], 0, 80)); ?>...
                                                    </div>
                                                <?php endif; ?>
                                                <div class="small text-muted">
                                                    <code><?php echo htmlspecialchars($category['slug']); ?></code>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '<span class="text-muted">—</span>'; ?>
                                    </td>
                                    <?php if (is_super_admin()): ?>
                                        <td><?php echo htmlspecialchars($category['campus_name'] ?? 'N/A'); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-info-soft text-info">
                                            <?php echo $category['post_count']; ?> posts
                                        </span>
                                    </td>
                                    <td><?php echo $category['sort_order']; ?></td>
                                    <td>
                                        <div class="badge bg-<?php echo $category['is_active'] ? 'success' : 'secondary'; ?>-soft text-<?php echo $category['is_active'] ? 'success' : 'secondary'; ?> rounded-pill">
                                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" 
                                                onclick="window.location.href='edit.php?id=<?php echo $category['id']; ?>'" 
                                                title="Edit">
                                            <i data-feather="edit"></i>
                                        </button>
                                        
                                        <button class="btn btn-datatable btn-icon btn-transparent-dark btn-delete" 
                                               onclick="confirmDelete(<?php echo $category['id']; ?>)" 
                                               title="Delete">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <?php
                                // Render children recursively
                                if (!empty($category['children'])) {
                                    renderCategoryRows($category['children'], $level + 1);
                                }
                                ?>
                            <?php 
                                endforeach;
                            }
                            
                            renderCategoryRows($category_tree);
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Handle bulk actions
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const bulkForm = document.getElementById('bulkForm');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }
    
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.category-checkbox:checked');
        if (bulkActions) {
            bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
        
        // Add selected category IDs to bulk form
        if (bulkForm) {
            // Remove existing hidden inputs
            bulkForm.querySelectorAll('input[name="category_ids[]"]').forEach(input => input.remove());
            
            // Add selected IDs
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'category_ids[]';
                input.value = checkbox.value;
                bulkForm.appendChild(input);
            });
        }
    }
});

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
    
    if (!action) {
        alert('Please select an action.');
        return false;
    }
    
    if (checkedCount === 0) {
        alert('Please select at least one category.');
        return false;
    }
    
    const actionText = action === 'delete' ? 'delete' : action;
    return confirm(`Are you sure you want to ${actionText} ${checkedCount} selected category(ies)?`);
}

function confirmDelete(categoryId) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone and will remove all associations with posts.')) {
        window.location.href = 'delete.php?id=' + categoryId;
    }
}

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
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
