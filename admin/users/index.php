<?php
/**
 * User Management - Main Listing
 * Campus-scoped user management with role-based access
 */

// Load core authentication and require admin access

// Define admin access
define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../core/middleware/admin_only.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($user_id && $user_id !== $current_user['id']) {
        switch ($action) {
            case 'deactivate':
                $params = [$user_id];
                $sql = "UPDATE users SET status = 0 WHERE id = ?";
                
                // Campus admins can only manage users in their campus
                if (!is_super_admin()) {
                    $sql .= " AND campus_id = ?";
                    $params[] = current_campus_id();
                }
                
                $db->query($sql, $params);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User deactivated successfully.'];
                break;
                
            case 'activate':
                $params = [$user_id];
                $sql = "UPDATE users SET status = 1 WHERE id = ?";
                
                if (!is_super_admin()) {
                    $sql .= " AND campus_id = ?";
                    $params[] = current_campus_id();
                }
                
                $db->query($sql, $params);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User activated successfully.'];
                break;
        }
    }
    
    header('Location: index.php');
    exit;
}

// Get filter parameters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query based on role and filters
$where_conditions = [];
$params = [];

// Campus isolation (except for super admin)
if (!is_super_admin()) {
    $where_conditions[] = "u.campus_id = ?";
    $params[] = current_campus_id();
}

// Role filter
if ($role_filter) {
    $where_conditions[] = "u.role = ?";
    $params[] = $role_filter;
}

// Status filter
if ($status_filter !== '') {
    $where_conditions[] = "u.status = ?";
    $params[] = $status_filter;
}

// Search filter
if ($search) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get users with campus info
$sql = "
    SELECT u.*" . (is_super_admin() ? ", c.name as campus_name" : "") . "
    FROM users u" . 
    (is_super_admin() ? " LEFT JOIN campuses c ON u.campus_id = c.id" : "") . "
    $where_clause 
    ORDER BY u.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$users = $db->fetchAll($sql, $params);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users u $where_clause";
$total_users = $db->fetch($count_sql, $params)['total'];
$total_pages = ceil($total_users / $per_page);

$page_title = 'User Management';
$page_description = 'Manage users and their access permissions';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="users"></i></div>
                        User Management
                    </h1>
                    <div class="page-header-subtitle">Manage users and their access permissions</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a href="create.php" class="btn btn-light">
                        <i class="me-2" data-feather="user-plus"></i>
                        Add User
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
            Filter Users
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="">All Roles</option>
                        <?php if (is_super_admin()): ?>
                            <option value="super_admin" <?php echo $role_filter === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                        <?php endif; ?>
                        <option value="campus_admin" <?php echo $role_filter === 'campus_admin' ? 'selected' : ''; ?>>Campus Admin</option>
                        <option value="editor" <?php echo $role_filter === 'editor' ? 'selected' : ''; ?>>Editor</option>
                        <option value="author" <?php echo $role_filter === 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="reader" <?php echo $role_filter === 'reader' ? 'selected' : ''; ?>>Reader</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group input-group-joined">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
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

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header">
            <i class="me-2" data-feather="database"></i>
            Users (<?php echo number_format($total_users); ?> total)
        </div>
        
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="feather-xl text-gray-400" data-feather="users"></i>
                    </div>
                    <h4 class="text-gray-700">No users found</h4>
                    <p class="text-gray-500 mb-4">
                        <?php if ($role_filter || $status_filter || $search): ?>
                            Try adjusting your filters or <a href="index.php" class="text-primary">view all users</a>.
                        <?php else: ?>
                            Start by inviting your first user.
                        <?php endif; ?>
                    </p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="me-2" data-feather="user-plus"></i>
                        Add First User
                    </a>
                </div>
            <?php else: ?>
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <?php if (is_super_admin()): ?>
                                <th>Campus</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <?php if (is_super_admin()): ?>
                                <th>Campus</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar">
                                            <?php if (!empty($user['avatar_url']) && file_exists(__DIR__ . '/../../' . $user['avatar_url'])): ?>
                                                <img class="avatar-img rounded-3" 
                                                     src="../../<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($user['username']); ?>" 
                                                     style="width: 45px; height: 45px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar-img rounded-3 bg-primary d-flex align-items-center justify-content-center text-white fw-bold" 
                                                     style="width: 45px; height: 45px;">
                                                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="small text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                            <?php if ($user['first_name'] || $user['last_name']): ?>
                                                <div class="small text-gray-500">
                                                    <?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge bg-<?php 
                                        echo $user['role'] === 'super_admin' ? 'danger' : 
                                            ($user['role'] === 'campus_admin' ? 'warning' : 
                                             ($user['role'] === 'editor' ? 'info' : 
                                              ($user['role'] === 'author' ? 'success' : 'secondary'))); 
                                    ?>-soft text-<?php 
                                        echo $user['role'] === 'super_admin' ? 'danger' : 
                                            ($user['role'] === 'campus_admin' ? 'warning' : 
                                             ($user['role'] === 'editor' ? 'info' : 
                                              ($user['role'] === 'author' ? 'success' : 'secondary'))); 
                                    ?> rounded-pill">
                                        <?php echo get_role_display_name($user['role']); ?>
                                    </div>
                                </td>
                                <?php if (is_super_admin()): ?>
                                    <td><?php echo htmlspecialchars($user['campus_name'] ?? 'Global'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="badge bg-<?php echo $user['status'] ? 'success' : 'secondary'; ?>-soft text-<?php echo $user['status'] ? 'success' : 'secondary'; ?> rounded-pill">
                                        <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <div class="small">
                                            <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                            <div class="text-gray-500"><?php echo date('g:i A', strtotime($user['last_login'])); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-500 small">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                </td>
                                <td>
                                    <?php if ($user['id'] !== $current_user['id']): ?>
                                        <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="window.location.href='view.php?id=<?php echo $user['id']; ?>'" title="View">
                                            <i data-feather="eye"></i>
                                        </button>
                                        <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="window.location.href='edit.php?id=<?php echo $user['id']; ?>'" title="Edit">
                                            <i data-feather="edit"></i>
                                        </button>
                                        
                                        <!-- Status Toggle -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $user['status'] ? 'deactivate' : 'activate'; ?>">
                                            <button type="submit" class="btn btn-datatable btn-icon btn-transparent-dark" 
                                                    title="<?php echo $user['status'] ? 'Deactivate' : 'Activate'; ?>"
                                                    onclick="return confirm('Are you sure you want to <?php echo $user['status'] ? 'deactivate' : 'activate'; ?> this user?')">
                                                <i data-feather="<?php echo $user['status'] ? 'pause' : 'play'; ?>"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-primary-soft text-primary">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Users pagination" class="mt-4">
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

<style>
/* Custom avatar styling for rounded rectangle */
.avatar-img {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.avatar-img:hover {
    border-color: rgba(0, 97, 242, 0.3);
    transform: scale(1.05);
}

/* Ensure consistent sizing and styling */
.avatar-img.rounded-3 {
    border-radius: 8px !important;
}

/* Default avatar background gradient */
.avatar-img.bg-primary {
    background: linear-gradient(135deg, #0061f2 0%, #0056d3 100%);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Profile image styling */
.avatar-img img, .avatar-img[style*="background"] {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
</style>

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
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
