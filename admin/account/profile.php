<?php
/**
 * Account Profile Management
 * Edit user profile information
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

$current_user = get_logged_in_user();
$db = Database::getInstance();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    $response = ['success' => false, 'message' => ''];
    
    // Validation
    if (empty($first_name) || empty($last_name)) {
        $error_message = 'First name and last name are required.';
        $response['message'] = $error_message;
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
        $response['message'] = $error_message;
    } else {
        // Check if email is already taken by another user
        $existing_user = $db->fetch(
            "SELECT id FROM users WHERE email = ? AND id != ?", 
            [$email, $current_user['id']]
        );
        
        if ($existing_user) {
            $error_message = 'This email address is already in use by another account.';
            $response['message'] = $error_message;
        } else {
            // Handle avatar upload
            $avatar_url = $current_user['avatar_url'] ?? null;
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/avatars/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_info = pathinfo($_FILES['avatar']['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array(strtolower($file_info['extension']), $allowed_extensions)) {
                    $filename = 'avatar_' . $current_user['id'] . '_' . time() . '.' . $file_info['extension'];
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                        // Delete old avatar if exists
                        if (!empty($current_user['avatar_url']) && file_exists(__DIR__ . '/../../' . $current_user['avatar_url'])) {
                            unlink(__DIR__ . '/../../' . $current_user['avatar_url']);
                        }
                        
                        $avatar_url = 'uploads/avatars/' . $filename;
                    }
                }
            }
            
            // Update user profile
            $updated = $db->query(
                "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, bio = ?, avatar_url = ?, updated_at = NOW() WHERE id = ?",
                [$first_name, $last_name, $email, $phone, $bio, $avatar_url, $current_user['id']]
            );
            
            if ($updated) {
                $success_message = 'Profile updated successfully!';
                $response['success'] = true;
                $response['message'] = $success_message;
                
                // Refresh current user data from session
                $_SESSION['user'] = $db->fetch("SELECT * FROM users WHERE id = ?", [$current_user['id']]);
                $current_user = $_SESSION['user'];
            } else {
                $error_message = 'Error updating profile. Please try again.';
                $response['message'] = $error_message;
            }
        }
    }
    
    // Check if this is an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$page_title = 'Account Profile';
$page_description = 'Manage your account profile information';

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
                        Account Profile
                    </h1>
                    <div class="page-header-subtitle">Manage your personal information and preferences</div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main page content-->
<div class="container-xl px-4 mt-n10">
    <!-- Flash Messages -->
    <?php if ($error_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php echo addslashes(htmlspecialchars($error_message)); ?>', 'danger');
            });
        </script>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php echo addslashes(htmlspecialchars($success_message)); ?>', 'success');
            });
        </script>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Picture Card -->
        <div class="col-xl-4">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">Profile Picture</div>
                <div class="card-body text-center">
                    <!-- Profile picture image-->
                    <img class="img-account-profile rounded-circle mb-2" 
                         src="<?php 
                             if (!empty($current_user['avatar_url'])) {
                                 echo '../../' . htmlspecialchars($current_user['avatar_url']);
                             } else {
                                 echo '../../dist/assets/img/illustrations/profiles/profile-1.png';
                             }
                         ?>" 
                         alt="Profile Picture" id="profileImage">
                    <!-- Profile picture help block-->
                    <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                    <!-- Profile picture upload form-->
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                        <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>">
                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                        <input type="hidden" name="bio" value="<?php echo htmlspecialchars($current_user['bio'] ?? ''); ?>">
                        <input type="file" name="avatar" id="avatarInput" accept="image/*">
                        <input type="hidden" name="cropped_image" id="croppedImageData">
                    </form>
                    <input type="file" id="imageSelector" accept="image/*" style="display: none;">
                    <button class="btn btn-primary" type="button" onclick="document.getElementById('imageSelector').click()">
                        Upload new image
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Account details card-->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <!-- Form Group (first name and last name)-->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputFirstName">First name</label>
                                <input class="form-control" id="inputFirstName" name="first_name" type="text" 
                                       placeholder="Enter your first name" 
                                       value="<?php echo htmlspecialchars($current_user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputLastName">Last name</label>
                                <input class="form-control" id="inputLastName" name="last_name" type="text" 
                                       placeholder="Enter your last name" 
                                       value="<?php echo htmlspecialchars($current_user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Form Group (email address)-->
                        <div class="mb-3">
                            <label class="small mb-1" for="inputEmailAddress">Email address</label>
                            <input class="form-control" id="inputEmailAddress" name="email" type="email" 
                                   placeholder="Enter your email address" 
                                   value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                        </div>
                        
                        <!-- Form Group (phone number)-->
                        <div class="mb-3">
                            <label class="small mb-1" for="inputPhone">Phone number</label>
                            <input class="form-control" id="inputPhone" name="phone" type="tel" 
                                   placeholder="Enter your phone number" 
                                   value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                        </div>
                        
                        <!-- Form Group (bio)-->
                        <div class="mb-3">
                            <label class="small mb-1" for="inputBio">Bio</label>
                            <textarea class="form-control" id="inputBio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars($current_user['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Account info (read-only)-->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Username</label>
                                <input class="form-control" type="text" 
                                       value="<?php echo htmlspecialchars($current_user['username']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Role</label>
                                <input class="form-control" type="text" 
                                       value="<?php echo ucwords(str_replace('_', ' ', $current_user['role'])); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Campus</label>
                                <input class="form-control" type="text" 
                                       value="<?php echo htmlspecialchars(get_current_campus()['name'] ?? 'All Campuses'); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Account Status</label>
                                <input class="form-control" type="text" 
                                       value="<?php echo ucfirst($current_user['status'] ?? 'active'); ?>" disabled>
                            </div>
                        </div>
                        
                        <!-- Save changes button-->
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-primary" type="submit">
                                <i class="me-2" data-feather="save"></i>
                                Save changes
                            </button>
                            <a href="change-password.php" class="btn btn-outline-warning">
                                <i class="me-2" data-feather="lock"></i>
                                Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Crop Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropModalLabel">Crop Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="crop-container" style="max-height: 400px; overflow: hidden;">
                        <img id="cropImage" style="max-width: 100%;">
                    </div>
                </div>
                <div class="text-muted small text-center">
                    Drag to move the image and use the corners to resize the crop area
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropAndUpload">Crop & Upload</button>
            </div>
        </div>
    </div>
</div>

<style>
.img-account-profile {
    height: 10rem;
    width: 10rem;
    object-fit: cover;
}

.crop-container {
    position: relative;
    max-height: 400px;
    overflow: hidden;
}

.cropper-container {
    font-size: 0;
    line-height: 0;
    position: relative;
    user-select: none;
    direction: ltr;
    touch-action: none;
}

.cropper-canvas {
    position: absolute;
    left: 0;
    top: 0;
    overflow: hidden;
}

.cropper-crop-box {
    position: absolute;
    left: 20%;
    top: 20%;
    width: 60%;
    height: 60%;
    overflow: hidden;
    outline: 1px solid #39f;
    outline-color: rgba(51, 153, 255, 0.75);
}

.cropper-view-box {
    border-radius: 50%;
    outline: 1px solid #fff;
    outline-color: rgba(255, 255, 255, 0.75);
    overflow: hidden;
}

.cropper-dashed {
    border: 0 dashed #eee;
    display: block;
    opacity: 0.5;
    position: absolute;
}

.cropper-dashed.dashed-h {
    border-bottom-width: 1px;
    border-top-width: 1px;
    height: calc(100% / 3);
    left: 0;
    top: calc(100% / 3);
    width: 100%;
}

.cropper-dashed.dashed-v {
    border-left-width: 1px;
    border-right-width: 1px;
    height: 100%;
    left: calc(100% / 3);
    top: 0;
    width: calc(100% / 3);
}

.cropper-center {
    display: block;
    height: 0;
    left: 50%;
    opacity: 0.75;
    position: absolute;
    top: 50%;
    width: 0;
}

.cropper-center::before,
.cropper-center::after {
    background-color: #eee;
    content: ' ';
    display: block;
    position: absolute;
}

.cropper-center::before {
    height: 1px;
    left: -3px;
    top: 0;
    width: 7px;
}

.cropper-center::after {
    height: 7px;
    left: 0;
    top: -3px;
    width: 1px;
}

.cropper-point {
    background-color: #39f;
    display: block;
    height: 5px;
    opacity: 0.75;
    outline: 0;
    position: absolute;
    width: 5px;
}

.cropper-point.point-e {
    cursor: ew-resize;
    margin-top: -3px;
    right: -3px;
    top: 50%;
}

.cropper-point.point-n {
    cursor: ns-resize;
    left: 50%;
    margin-left: -3px;
    top: -3px;
}

.cropper-point.point-w {
    cursor: ew-resize;
    left: -3px;
    margin-top: -3px;
    top: 50%;
}

.cropper-point.point-s {
    bottom: -3px;
    cursor: ns-resize;
    left: 50%;
    margin-left: -3px;
}

.cropper-point.point-ne {
    cursor: nesw-resize;
    right: -3px;
    top: -3px;
}

.cropper-point.point-nw {
    cursor: nwse-resize;
    left: -3px;
    top: -3px;
}

.cropper-point.point-sw {
    bottom: -3px;
    cursor: nesw-resize;
    left: -3px;
}

.cropper-point.point-se {
    bottom: -3px;
    cursor: nwse-resize;
    right: -3px;
}

.cropper-line {
    background-color: #39f;
    display: block;
    opacity: 0.1;
    position: absolute;
}

.cropper-line.line-e {
    cursor: ew-resize;
    right: 0;
    top: 0;
    width: 1px;
    height: 100%;
}

.cropper-line.line-n {
    cursor: ns-resize;
    height: 1px;
    left: 0;
    top: 0;
    width: 100%;
}

.cropper-line.line-w {
    cursor: ew-resize;
    left: 0;
    top: 0;
    width: 1px;
    height: 100%;
}

.cropper-line.line-s {
    bottom: 0;
    cursor: ns-resize;
    height: 1px;
    left: 0;
    width: 100%;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper;
let selectedFile;

// Handle image selection
document.getElementById('imageSelector').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = function(e) {
            const cropImage = document.getElementById('cropImage');
            cropImage.src = e.target.result;
            
            // Show the crop modal
            const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
            cropModal.show();
            
            // Initialize cropper when modal is shown
            document.getElementById('cropModal').addEventListener('shown.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1, // Square crop for profile pictures
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    modal: true,
                    background: true,
                    rotatable: false,
                    scalable: false,
                    zoomable: true,
                    wheelZoomRatio: 0.1,
                    ready: function() {
                        // Cropper is ready
                    }
                });
            }, { once: true });
        };
        reader.readAsDataURL(file);
    }
});

// Handle crop and upload
document.getElementById('cropAndUpload').addEventListener('click', function() {
    if (cropper) {
        // Get cropped canvas
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        // Convert canvas to blob
        canvas.toBlob(function(blob) {
            // Create a new file from the blob
            const croppedFile = new File([blob], selectedFile.name, {
                type: selectedFile.type,
                lastModified: Date.now()
            });
            
            // Create FormData and append the cropped image
            const formData = new FormData();
            formData.append('avatar', croppedFile);
            formData.append('first_name', document.querySelector('input[name="first_name"]').value);
            formData.append('last_name', document.querySelector('input[name="last_name"]').value);
            formData.append('email', document.querySelector('input[name="email"]').value);
            formData.append('phone', document.querySelector('input[name="phone"]').value);
            formData.append('bio', document.querySelector('textarea[name="bio"]').value);
            
            // Show loading state
            const uploadBtn = document.getElementById('cropAndUpload');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            uploadBtn.disabled = true;
            
            // Submit the form
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Parse the response to check for success/error messages
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Check for success message
                const successAlert = doc.querySelector('.alert-success');
                const errorAlert = doc.querySelector('.alert-danger');
                
                if (successAlert) {
                    // Update profile image preview
                    document.getElementById('profileImage').src = canvas.toDataURL();
                    
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('cropModal')).hide();
                    
                    // Show success toast
                    showToast('Profile picture updated successfully!', 'success');
                } else if (errorAlert) {
                    showToast('Error uploading image. Please try again.', 'danger');
                } else {
                    // Reload page to show updated state
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error uploading image. Please try again.', 'danger');
            })
            .finally(() => {
                // Reset button state
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
            });
        }, selectedFile.type, 0.9);
    }
});

// Clean up cropper when modal is hidden
document.getElementById('cropModal').addEventListener('hidden.bs.modal', function() {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
});

// Helper function to show toasts
function showToast(message, type) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'check-circle' : 'alert-circle';
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="me-2" data-feather="${iconClass}"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Legacy code for direct upload (keeping for fallback)
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImage').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Handle main profile form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // Find submit button and show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    submitBtn.disabled = true;
    
    // Submit the form with AJAX header
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating profile. Please try again.', 'danger');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
