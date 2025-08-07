<?php
/**
 * User Management - Create/Invite User
 * Add new users with role-based access control
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

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $role = $_POST['role'];
        $campus_id = is_super_admin() ? (int)$_POST['campus_id'] : current_campus_id();
        $password = $_POST['password'];
        $send_invite = isset($_POST['send_invite']);
        
        $errors = [];
        
        // Validation
        if (empty($username) || strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long.";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($first_name)) {
            $errors[] = "First name is required.";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required.";
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        
        // Role validation - campus admins can't create super admins
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
        
        // Check if username or email already exists
        if (empty($errors)) {
            $existing_user = $db->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existing_user) {
                $errors[] = "Username or email already exists.";
            }
        }
        
        // Create user if no errors
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, campus_id, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $result = $db->query($sql, [
                $username, $email, $password_hash, $first_name, $last_name, $role, $campus_id
            ]);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success', 
                    'message' => "User '{$username}' created successfully."
                ];
                
                // TODO: Send invitation email if $send_invite is true
                
                header('Location: index.php');
                exit;
            } else {
                $errors[] = "Error creating user. Please try again.";
            }
        }
    } else {
        $errors[] = "Invalid form submission.";
    }
}

// Get campuses for super admin
$campuses = [];
if (is_super_admin()) {
    $campuses = $db->fetchAll("SELECT id, name FROM campuses WHERE status = 'active' ORDER BY name");
}

$page_title = 'Create User';
$page_description = 'Add a new user to the system';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                        Create User
                    </h1>
                    <div class="page-header-subtitle">Add a new user to the system</div>
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

    <!-- Create User Form -->
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-xl-10">
            <div class="card">
                <div class="card-header">
                    <i class="me-2" data-feather="user-plus"></i>
                    User Information
                </div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="first_name">First Name <span class="text-danger">*</span></label>
                                <input class="form-control" id="first_name" name="first_name" type="text" 
                                       placeholder="Enter first name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input class="form-control" id="last_name" name="last_name" type="text" 
                                       placeholder="Enter last name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="username">Username <span class="text-danger">*</span></label>
                                <input class="form-control" id="username" name="username" type="text" 
                                       placeholder="Enter username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                <div class="form-text">Username must be at least 3 characters long and unique.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="email">Email Address <span class="text-danger">*</span></label>
                                <input class="form-control" id="email" name="email" type="email" 
                                       placeholder="Enter email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input class="form-control" id="password" name="password" type="password" 
                                           placeholder="Enter password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i data-feather="eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="role">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select a role...</option>
                                    <option value="reader" <?php echo ($_POST['role'] ?? '') === 'reader' ? 'selected' : ''; ?>>Reader</option>
                                    <option value="author" <?php echo ($_POST['role'] ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                                    <option value="editor" <?php echo ($_POST['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                    <?php if (is_campus_admin() || is_super_admin()): ?>
                                        <option value="campus_admin" <?php echo ($_POST['role'] ?? '') === 'campus_admin' ? 'selected' : ''; ?>>Campus Admin</option>
                                    <?php endif; ?>
                                    <?php if (is_super_admin()): ?>
                                        <option value="super_admin" <?php echo ($_POST['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (is_super_admin()): ?>
                            <div class="mb-3">
                                <label class="small mb-1" for="campus_id">Campus <span class="text-danger">*</span></label>
                                <select class="form-select" id="campus_id" name="campus_id" required>
                                    <option value="">Select a campus...</option>
                                    <?php foreach ($campuses as $campus): ?>
                                        <option value="<?php echo $campus['id']; ?>" 
                                                <?php echo ($_POST['campus_id'] ?? '') == $campus['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($campus['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" id="send_invite" name="send_invite" type="checkbox" 
                                       <?php echo isset($_POST['send_invite']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="send_invite">
                                    Send invitation email to user
                                </label>
                                <div class="form-text">If checked, an email with login credentials will be sent to the user.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php" class="btn btn-light me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="me-2" data-feather="user-plus"></i>
                                        Create User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.setAttribute('data-feather', 'eye-off');
    } else {
        passwordInput.type = 'password';
        icon.setAttribute('data-feather', 'eye');
    }
    
    // Re-initialize Feather icons
    feather.replace();
});

// Role description tooltips
document.getElementById('role').addEventListener('change', function() {
    const roleDescriptions = {
        'reader': 'Can view published content only',
        'author': 'Can create and edit their own posts',
        'editor': 'Can edit any posts within their campus',
        'campus_admin': 'Full access to campus management',
        'super_admin': 'Full system access across all campuses'
    };
    
    const description = roleDescriptions[this.value];
    if (description) {
        // Show tooltip or update help text
        console.log(description);
    }
});

// Username availability check (debounced)
let usernameTimeout;
document.getElementById('username').addEventListener('input', function() {
    clearTimeout(usernameTimeout);
    const username = this.value.trim();
    
    if (username.length >= 3) {
        usernameTimeout = setTimeout(() => {
            // Check username availability via AJAX
            fetch(`check-username.php?username=${encodeURIComponent(username)}`)
                .then(response => response.json())
                .then(data => {
                    const input = document.getElementById('username');
                    if (data.available) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                })
                .catch(() => {
                    // Handle error silently
                });
        }, 500);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
