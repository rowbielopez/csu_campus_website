<?php
/**
 * Media Library - Main Interface
 * Campus-specific media management with role-based access and SB Admin Pro 2 UI
 */

// Load core authentication

// Define admin access
define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/media.php';

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

// Initialize MediaManager
$media_manager = new MediaManager();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $media_id = (int)$_POST['media_id'];
        
        // Check permissions - only admins or file owners can delete
        $media = $media_manager->getMediaById($media_id, $current_campus['id']);
        if ($media && (is_campus_admin() || is_super_admin() || $media['uploader_id'] == $current_user['id'])) {
            $result = $media_manager->deleteMedia($media_id, $current_campus['id']);
            if ($result['success']) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'File deleted successfully.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error deleting file: ' . $result['error']];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Permission denied.'];
        }
    }
    header('Location: index.php');
    exit;
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && isset($_POST['media_ids'])) {
    if (hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $action = $_POST['bulk_action'];
        $media_ids = array_map('intval', $_POST['media_ids']);
        
        if ($action === 'delete' && (is_campus_admin() || is_super_admin())) {
            $deleted_count = 0;
            foreach ($media_ids as $media_id) {
                $result = $media_manager->deleteMedia($media_id, $current_campus['id']);
                if ($result['success']) {
                    $deleted_count++;
                }
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "$deleted_count files deleted successfully."];
        }
    }
    header('Location: index.php');
    exit;
}

// Get filter parameters
$file_type = $_GET['file_type'] ?? '';
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'desc';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$uploader = $_GET['uploader'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$view_mode = $_GET['view'] ?? 'grid'; // grid or list
$per_page = $view_mode === 'grid' ? 16 : 20;

// Get media files
$options = [
    'page' => $page,
    'per_page' => $per_page,
    'file_type' => $file_type,
    'search' => $search,
    'sort_by' => $sort_by,
    'sort_order' => $sort_order,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'uploader' => $uploader,
    'user_id' => !is_campus_admin() && !is_super_admin() ? $current_user['id'] : null
];

$media_result = $media_manager->getMediaFiles($current_campus['id'], $options);
$media_files = $media_result['files'];
$total_files = $media_result['total'];
$total_pages = $media_result['total_pages'];

// Get file type statistics
$file_types = $media_manager->getFileTypes();

$page_title = 'Media Library';
$page_description = 'Manage and organize your media files';

include __DIR__ . '/../layouts/header-new.php';
?>

<!-- Page Header -->
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="image"></i></div>
                        Media Library
                    </h1>
                    <div class="page-header-subtitle">Manage and organize your media files</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a href="upload.php" class="btn btn-light">
                        <i class="me-2" data-feather="upload"></i>
                        Upload Media
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

    <!-- Filters and View Toggle Card -->
    <div class="card mb-4">
        <div class="card-header card-header-actions">
            <div class="card-header-title">
                <i class="me-2" data-feather="filter"></i>
                Filter & Search Media
            </div>
            <div class="btn-group" role="group" aria-label="View toggle">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'grid'])); ?>" 
                   class="btn btn-outline-primary <?php echo $view_mode === 'grid' ? 'active' : ''; ?>">
                    <i data-feather="grid"></i>
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'list'])); ?>" 
                   class="btn btn-outline-primary <?php echo $view_mode === 'list' ? 'active' : ''; ?>">
                    <i data-feather="list"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view_mode); ?>">
                
                <div class="col-lg-2 col-md-3">
                    <label for="file_type" class="form-label">File Type</label>
                    <select name="file_type" id="file_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($file_types as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $file_type === $type ? 'selected' : ''; ?>>
                                <?php echo ucfirst($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group input-group-joined">
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search filename, caption..." value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-text"><i data-feather="search"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Upload Date</option>
                        <option value="original_filename" <?php echo $sort_by === 'original_filename' ? 'selected' : ''; ?>>Filename</option>
                        <option value="file_size" <?php echo $sort_by === 'file_size' ? 'selected' : ''; ?>>File Size</option>
                        <option value="downloads" <?php echo $sort_by === 'downloads' ? 'selected' : ''; ?>>Downloads</option>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-2">
                    <label for="sort_order" class="form-label">Order</label>
                    <select name="sort_order" id="sort_order" class="form-select">
                        <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                
                <div class="col-lg-3 col-md-12">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="me-1" data-feather="search"></i>
                            Filter
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i data-feather="refresh-cw"></i>
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="toggleAdvancedFilters()">
                            <i data-feather="settings"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Filters (Initially Hidden) -->
                <div id="advancedFilters" class="col-12" style="display: none;">
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <?php if (is_campus_admin() || is_super_admin()): ?>
                        <div class="col-md-3">
                            <label for="uploader" class="form-label">Uploader</label>
                            <input type="text" name="uploader" id="uploader" class="form-control" 
                                   placeholder="Search by uploader name..." value="<?php echo htmlspecialchars($uploader); ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Media Content Card -->
    <div class="card">
        <div class="card-header card-header-actions">
            <div class="card-header-title">
                <i class="me-2" data-feather="database"></i>
                Media Files (<?php echo number_format($total_files); ?> total)
            </div>
            
            <?php if ((is_campus_admin() || is_super_admin()) && !empty($media_files)): ?>
                <div class="bulk-actions" style="display: none;">
                    <form method="POST" class="d-inline" id="bulkForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="input-group input-group-sm">
                            <select name="bulk_action" class="form-select">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirmBulkAction()">
                                Apply
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if (empty($media_files)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="feather-xl text-gray-400" data-feather="image"></i>
                    </div>
                    <h4 class="text-gray-700">No media files found</h4>
                    <p class="text-gray-500 mb-4">
                        <?php if ($file_type || $search): ?>
                            Try adjusting your filters or <a href="index.php" class="text-primary">view all files</a>.
                        <?php else: ?>
                            Start building your media library by uploading your first file.
                        <?php endif; ?>
                    </p>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="me-2" data-feather="upload"></i>
                        Upload First File
                    </a>
                </div>
            <?php else: ?>
                <?php if ($view_mode === 'grid'): ?>
                    <!-- Grid View -->
                    <div class="row g-3">
                        <?php foreach ($media_files as $file): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card media-card h-100" data-media-id="<?php echo $file['id']; ?>">
                                    <?php if ((is_campus_admin() || is_super_admin())): ?>
                                        <div class="card-header-checkbox">
                                            <input type="checkbox" name="media_ids[]" value="<?php echo $file['id']; ?>" 
                                                   class="form-check-input media-checkbox">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-img-container">
                                        <?php if ($file['file_type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($file['thumbnail_url'] ?? $file['file_url']); ?>" 
                                                 class="card-img-top" 
                                                 alt="<?php echo htmlspecialchars($file['alt_text'] ?: $file['original_filename']); ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                                <i class="feather-xl text-gray-400" data-feather="<?php echo $file['file_type'] === 'video' ? 'video' : ($file['file_type'] === 'audio' ? 'music' : 'file'); ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-img-overlay-actions">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-light" onclick="viewMedia(<?php echo $file['id']; ?>)" title="View Details">
                                                    <i data-feather="eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light" onclick="copyUrl('<?php echo htmlspecialchars($file['file_url']); ?>')" title="Copy URL">
                                                    <i data-feather="link"></i>
                                                </button>
                                                <?php if (is_campus_admin() || is_super_admin() || $file['uploader_id'] == $current_user['id']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteMedia(<?php echo $file['id']; ?>)" title="Delete">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h6 class="card-title text-truncate" title="<?php echo htmlspecialchars($file['original_filename']); ?>">
                                            <?php echo htmlspecialchars($file['original_filename']); ?>
                                        </h6>
                                        <div class="small text-gray-500 mb-2">
                                            <div><?php echo strtoupper($file['file_extension']); ?> • <?php echo format_file_size($file['file_size']); ?></div>
                                            <div><?php echo date('M j, Y', strtotime($file['created_at'])); ?></div>
                                        </div>
                                        
                                        <?php if ($file['caption']): ?>
                                            <p class="card-text small text-truncate"><?php echo htmlspecialchars($file['caption']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="badge bg-<?php echo $file['is_public'] ? 'success' : 'warning'; ?>-soft text-<?php echo $file['is_public'] ? 'success' : 'warning'; ?>">
                                                <?php echo $file['is_public'] ? 'Public' : 'Private'; ?>
                                            </div>
                                            <small class="text-gray-500"><?php echo $file['downloads'] ?? 0; ?> downloads</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- List View -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <?php if (is_campus_admin() || is_super_admin()): ?>
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                    <?php endif; ?>
                                    <th style="width: 60px;">Preview</th>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($media_files as $file): ?>
                                    <tr>
                                        <?php if (is_campus_admin() || is_super_admin()): ?>
                                            <td>
                                                <input type="checkbox" name="media_ids[]" value="<?php echo $file['id']; ?>" 
                                                       class="form-check-input media-checkbox">
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if ($file['file_type'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($file['thumbnail_url'] ?? $file['file_url']); ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     alt="<?php echo htmlspecialchars($file['alt_text'] ?: $file['original_filename']); ?>">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 50px; height: 50px;">
                                                    <i data-feather="<?php echo $file['file_type'] === 'video' ? 'video' : ($file['file_type'] === 'audio' ? 'music' : 'file'); ?>"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($file['original_filename']); ?></div>
                                            <?php if ($file['caption']): ?>
                                                <div class="small text-gray-500"><?php echo htmlspecialchars($file['caption']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-soft text-secondary"><?php echo strtoupper($file['file_extension']); ?></span>
                                        </td>
                                        <td><?php echo format_file_size($file['file_size']); ?></td>
                                        <td>
                                            <div class="small">
                                                <?php echo date('M j, Y', strtotime($file['created_at'])); ?>
                                                <div class="text-gray-500"><?php echo date('g:i A', strtotime($file['created_at'])); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge bg-<?php echo $file['is_public'] ? 'success' : 'warning'; ?>-soft text-<?php echo $file['is_public'] ? 'success' : 'warning'; ?>">
                                                <?php echo $file['is_public'] ? 'Public' : 'Private'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="viewMedia(<?php echo $file['id']; ?>)" title="View Details">
                                                <i data-feather="eye"></i>
                                            </button>
                                            <button class="btn btn-datatable btn-icon btn-transparent-dark me-2" onclick="copyUrl('<?php echo htmlspecialchars($file['file_url']); ?>')" title="Copy URL">
                                                <i data-feather="link"></i>
                                            </button>
                                            <?php if (is_campus_admin() || is_super_admin() || $file['uploader_id'] == $current_user['id']): ?>
                                                <button class="btn btn-datatable btn-icon btn-transparent-dark" onclick="deleteMedia(<?php echo $file['id']; ?>)" title="Delete">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Media pagination" class="mt-4">
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

<!-- Custom Styles -->
<style>
.media-card {
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
}

.media-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-img-container {
    position: relative;
    overflow: hidden;
}

.card-img-overlay-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.2s;
}

.media-card:hover .card-img-overlay-actions {
    opacity: 1;
}

.card-header-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
}

.bulk-actions {
    transition: all 0.3s ease;
}
</style>

<!-- JavaScript -->
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
    const mediaCheckboxes = document.querySelectorAll('.media-checkbox');
    const bulkActions = document.querySelector('.bulk-actions');
    const bulkForm = document.getElementById('bulkForm');

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function() {
            mediaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }

    mediaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.media-checkbox:checked');
            if (selectAllBox) {
                selectAllBox.checked = checkedBoxes.length === mediaCheckboxes.length;
                selectAllBox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < mediaCheckboxes.length;
            }
            toggleBulkActions();
        });
    });

    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.media-checkbox:checked');
        if (bulkActions) {
            bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
    }

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.media-checkbox:checked');
            checkedBoxes.forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'media_ids[]';
                hiddenInput.value = checkbox.value;
                this.appendChild(hiddenInput);
            });
        });
    }
});

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checkedBoxes = document.querySelectorAll('.media-checkbox:checked');
    
    if (!action) {
        alert('Please select a bulk action.');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one file.');
        return false;
    }
    
    return confirm(`Are you sure you want to delete ${checkedBoxes.length} file(s)? This action cannot be undone.`);
}
<?php endif; ?>

// Media actions
function viewMedia(mediaId) {
    // Open media details modal or redirect to details page
    const modal = new bootstrap.Modal(document.getElementById('mediaDetailsModal'));
    // Load media details via AJAX and show modal
    fetch(`media-details.php?id=${mediaId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('mediaDetailsContent').innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error loading media details:', error);
            alert('Error loading media details. Please try again.');
        });
}

// Toggle advanced filters
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const isVisible = advancedFilters.style.display !== 'none';
    
    if (isVisible) {
        advancedFilters.style.display = 'none';
    } else {
        advancedFilters.style.display = 'block';
    }
}

// Auto-show advanced filters if any advanced filter values are set
document.addEventListener('DOMContentLoaded', function() {
    const hasAdvancedFilters = <?php echo ($date_from || $date_to || $uploader) ? 'true' : 'false'; ?>;
    if (hasAdvancedFilters) {
        document.getElementById('advancedFilters').style.display = 'block';
    }
});

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        // Show success toast
        const toast = new bootstrap.Toast(document.getElementById('copySuccessToast'));
        toast.show();
    });
}

function deleteMedia(mediaId) {
    if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="media_id" value="${mediaId}">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Media Details Modal -->
<div class="modal fade" id="mediaDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="mediaDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Copy Success Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="copySuccessToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="me-2 text-success" data-feather="check"></i>
            <strong class="me-auto">Success</strong>
        </div>
        <div class="toast-body">
            URL copied to clipboard!
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
