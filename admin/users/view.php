<?php
/**
 * User Management - View/Edit User
 * View and edit user details with role-based access control
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

// Get user ID from URL
$user_id = (int)($_GET['id'] ?? 0);
if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Get user data with campus isolation
$sql = "SELECT u.* FROM users u WHERE u.id = ?";
$params = [$user_id];

if (!is_super_admin()) {
    $sql .= " AND u.campus_id = ?";
    $params[] = current_campus_id();
}

$user = $db->fetch($sql, $params);

if (!$user) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'User not found.'];
    header('Location: index.php');
    exit;
}

// Prevent non-super admins from editing super admins
if ($user['role'] === 'super_admin' && !is_super_admin()) {
    $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Access denied.'];
    header('Location: index.php');
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $status = (int)$_POST['status'];
        $campus_id = is_super_admin() ? (int)$_POST['campus_id'] : $user['campus_id'];
        
        $errors = [];
        
        // Validation
        if (empty($first_name)) {
            $errors[] = "First name is required.";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required.";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        // Role validation
        $allowed_roles = ['reader', 'author', 'editor'];
        if (is_super_admin()) {
            $allowed_roles[] = 'super_admin';
            $allowed_roles[] = 'campus_admin';
        } elseif (is_campus_admin()) {
            $allowed_roles[] = 'campus_admin';
        }
        
        if (!in_array($role, $allowed_roles)) {
            $errors[] = "Invalid role selected.";
        }
        
        // Prevent users from changing their own role or status
        if ($user_id === $current_user['id']) {
            $role = $user['role'];
            $status = $user['status'];
        }
        
        // Check if email already exists (excluding current user)
        if (empty($errors)) {
            $existing_user = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
            if ($existing_user) {
                $errors[] = "Email address already exists.";
            }
        }
        
        // Update user if no errors
        if (empty($errors)) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, status = ?, campus_id = ?, updated_at = NOW() WHERE id = ?";
            $params = [$first_name, $last_name, $email, $role, $status, $campus_id, $user_id];
            
            $result = $db->query($sql, $params);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success', 
                    'message' => "User updated successfully."
                ];
                header('Location: view.php?id=' . $user_id);
                exit;
            } else {
                $errors[] = "Error updating user. Please try again.";
            }
        }
        
        // Update user array with form data for display
        $user = array_merge($user, $_POST);
    } else {
        $errors[] = "Invalid form submission.";
    }
}

// Get campuses for super admin
$campuses = [];
if (is_super_admin()) {
    $campuses = $db->fetchAll("SELECT id, name FROM campuses WHERE status = 'active' ORDER BY name");
}

// Get user's recent activity
$recent_posts = $db->fetchAll(
    "SELECT id, title, status, created_at FROM posts WHERE author_id = ? ORDER BY created_at DESC LIMIT 5",
    [$user_id]
);

$page_title = 'User: ' . $user['username'];
$page_description = 'View and edit user details';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="user"></i></div>
                        User: <?php echo htmlspecialchars($user['username']); ?>
                    </h1>
                    <div class="page-header-subtitle">View and edit user details</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="me-2" data-feather="arrow-left"></i>
                        Back to Users
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

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="me-2" data-feather="alert-circle"></i>
                <div>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-xl-4">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">User Profile</div>
                <div class="card-body text-center">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl">
                        <?php if (!empty($user['avatar_url']) && file_exists(__DIR__ . '/../../' . $user['avatar_url'])): ?>
                            <img class="avatar-img rounded-3" 
                                 src="../../<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($user['username']); ?>" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="avatar-img rounded-3 bg-primary d-flex align-items-center justify-content-center text-white fw-bold" 
                                 style="width: 120px; height: 120px; font-size: 2rem;">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User Info -->
                    <div class="mt-4">
                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        <p class="text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
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
                        ?> rounded-pill mb-3">
                            <?php echo get_role_display_name($user['role']); ?>
                        </div>
                        <div class="badge bg-<?php echo $user['status'] ? 'success' : 'secondary'; ?>-soft text-<?php echo $user['status'] ? 'success' : 'secondary'; ?> rounded-pill">
                            <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <div class="fw-bold"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                            <div class="small text-gray-600">Joined</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="fw-bold">
                                <?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?>
                            </div>
                            <div class="small text-gray-600">Last Login</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Details Form -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="first_name">First Name</label>
                                <input class="form-control" id="first_name" name="first_name" type="text" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="last_name">Last Name</label>
                                <input class="form-control" id="last_name" name="last_name" type="text" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="username">Username</label>
                            <input class="form-control" id="username" type="text" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <div class="form-text">Username cannot be changed.</div>
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="email">Email Address</label>
                            <input class="form-control" id="email" name="email" type="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="role">Role</label>
                                <select class="form-select" id="role" name="role" 
                                        <?php echo $user_id === $current_user['id'] ? 'disabled' : ''; ?>>
                                    <option value="reader" <?php echo $user['role'] === 'reader' ? 'selected' : ''; ?>>Reader</option>
                                    <option value="author" <?php echo $user['role'] === 'author' ? 'selected' : ''; ?>>Author</option>
                                    <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                    <?php if (is_campus_admin() || is_super_admin()): ?>
                                        <option value="campus_admin" <?php echo $user['role'] === 'campus_admin' ? 'selected' : ''; ?>>Campus Admin</option>
                                    <?php endif; ?>
                                    <?php if (is_super_admin()): ?>
                                        <option value="super_admin" <?php echo $user['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                    <?php endif; ?>
                                </select>
                                <?php if ($user_id === $current_user['id']): ?>
                                    <div class="form-text">You cannot change your own role.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="status">Status</label>
                                <select class="form-select" id="status" name="status" 
                                        <?php echo $user_id === $current_user['id'] ? 'disabled' : ''; ?>>
                                    <option value="1" <?php echo $user['status'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo !$user['status'] ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <?php if ($user_id === $current_user['id']): ?>
                                    <div class="form-text">You cannot change your own status.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (is_super_admin()): ?>
                            <div class="mb-3">
                                <label class="small mb-1" for="campus_id">Campus</label>
                                <select class="form-select" id="campus_id" name="campus_id">
                                    <?php foreach ($campuses as $campus): ?>
                                        <option value="<?php echo $campus['id']; ?>" 
                                                <?php echo $user['campus_id'] == $campus['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($campus['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between">
                            <div>
                                <?php if ($user_id !== $current_user['id']): ?>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                        <i class="me-2" data-feather="key"></i>
                                        Reset Password
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="me-2" data-feather="save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <?php if (!empty($recent_posts)): ?>
        <div class="card">
            <div class="card-header">Recent Posts</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
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
                                    <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <a href="../posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm" method="POST" action="reset-password.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_email" name="send_email">
                            <label class="form-check-label" for="send_email">
                                Send new password to user via email
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="resetPasswordForm" class="btn btn-danger">Reset Password</button>
            </div>
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
    transform: scale(1.02);
}

/* Ensure consistent sizing and styling */
.avatar-img.rounded-3 {
    border-radius: 12px !important;
}

/* Default avatar background gradient */
.avatar-img.bg-primary {
    background: linear-gradient(135deg, #0061f2 0%, #0056d3 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Profile image styling */
.avatar-img img, .avatar-img[style*="background"] {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

/* Large avatar specific styling */
.avatar-xl .avatar-img.rounded-3 {
    border-radius: 16px !important;
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
