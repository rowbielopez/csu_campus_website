<?php
/**
 * Media Details Modal Content
 * Returns detailed information about a media file
 */

session_start();

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/media.php';

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

$media_id = (int)($_GET['id'] ?? 0);
if (!$media_id) {
    http_response_code(400);
    exit('Invalid media ID');
}

// Initialize MediaManager
$media_manager = new MediaManager();

// Get media file (with campus isolation)
$media = $media_manager->getMediaById($media_id, $current_campus['id']);

if (!$media) {
    http_response_code(404);
    exit('Media file not found');
}

// Check permissions for non-admin users
if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'super_admin' && $media['user_id'] != $current_user['id']) {
    http_response_code(403);
    exit('Access denied');
}

// Parse metadata
$metadata = json_decode($media['metadata'], true) ?: [];
?>

<div class="row">
    <!-- Media Preview -->
    <div class="col-md-6">
        <div class="media-preview-large mb-3">
            <?php if ($media['file_type'] === 'image'): ?>
                <img src="<?php echo htmlspecialchars($media['file_url']); ?>" 
                     alt="<?php echo htmlspecialchars($media['alt_text']); ?>" 
                     class="img-fluid rounded">
            <?php else: ?>
                <div class="file-icon-xl bg-secondary text-white rounded d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i data-feather="file" style="width: 64px; height: 64px;"></i><br>
                        <h4><?php echo strtoupper($media['file_extension']); ?></h4>
                        <p class="mb-0"><?php echo ucfirst($media['file_type']); ?> File</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary" onclick="copyUrl('<?php echo htmlspecialchars($media['file_url']); ?>')">
                <i data-feather="copy"></i>
                Copy URL
            </button>
            <a href="<?php echo htmlspecialchars($media['file_url']); ?>" target="_blank" class="btn btn-outline-secondary">
                <i data-feather="external-link"></i>
                Open in New Tab
            </a>
            <a href="<?php echo htmlspecialchars($media['file_url']); ?>" download class="btn btn-outline-secondary">
                <i data-feather="download"></i>
                Download
            </a>
        </div>
    </div>
    
    <!-- Media Information -->
    <div class="col-md-6">
        <h5><?php echo htmlspecialchars($media['original_filename']); ?></h5>
        
        <table class="table table-sm">
            <tbody>
                <tr>
                    <td><strong>File Type:</strong></td>
                    <td><?php echo ucfirst($media['file_type']); ?></td>
                </tr>
                <tr>
                    <td><strong>MIME Type:</strong></td>
                    <td><?php echo htmlspecialchars($media['mime_type']); ?></td>
                </tr>
                <tr>
                    <td><strong>File Size:</strong></td>
                    <td>
                        <?php 
                        $size_mb = $media['file_size'] / (1024 * 1024);
                        if ($size_mb >= 1) {
                            echo number_format($size_mb, 2) . ' MB';
                        } else {
                            echo number_format($media['file_size'] / 1024, 1) . ' KB';
                        }
                        ?>
                    </td>
                </tr>
                <?php if ($media['file_type'] === 'image' && !empty($metadata['width'])): ?>
                    <tr>
                        <td><strong>Dimensions:</strong></td>
                        <td><?php echo $metadata['width']; ?> × <?php echo $metadata['height']; ?> pixels</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Uploaded By:</strong></td>
                    <td>
                        <?php 
                        if ($media['first_name'] && $media['last_name']) {
                            echo htmlspecialchars($media['first_name'] . ' ' . $media['last_name']);
                        } else {
                            echo htmlspecialchars($media['username']);
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Upload Date:</strong></td>
                    <td><?php echo date('F j, Y g:i A', strtotime($media['created_at'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Downloads:</strong></td>
                    <td><?php echo number_format($media['download_count']); ?></td>
                </tr>
                <tr>
                    <td><strong>Public:</strong></td>
                    <td>
                        <span class="badge bg-<?php echo $media['is_public'] ? 'success' : 'warning'; ?>">
                            <?php echo $media['is_public'] ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                </tr>
                <?php if ($media['is_featured']): ?>
                    <tr>
                        <td><strong>Featured:</strong></td>
                        <td><span class="badge bg-primary">Yes</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Metadata -->
        <?php if ($media['alt_text'] || $media['caption'] || $media['description']): ?>
            <h6 class="mt-3">Content Information</h6>
            <?php if ($media['alt_text']): ?>
                <div class="mb-2">
                    <strong>Alt Text:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($media['alt_text']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($media['caption']): ?>
                <div class="mb-2">
                    <strong>Caption:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($media['caption']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($media['description']): ?>
                <div class="mb-2">
                    <strong>Description:</strong><br>
                    <span class="text-muted"><?php echo nl2br(htmlspecialchars($media['description'])); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Edit Form (for file owner or admin) -->
        <?php if ($current_user['role'] === 'admin' || $media['user_id'] == $current_user['id']): ?>
            <div class="mt-4">
                <h6>Edit Information</h6>
                <form method="POST" action="update-media.php" class="media-edit-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                    
                    <div class="mb-2">
                        <label class="form-label">Alt Text</label>
                        <input type="text" class="form-control form-control-sm" name="alt_text" 
                               value="<?php echo htmlspecialchars($media['alt_text']); ?>">
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Caption</label>
                        <input type="text" class="form-control form-control-sm" name="caption" 
                               value="<?php echo htmlspecialchars($media['caption']); ?>">
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" name="description" rows="2"><?php echo htmlspecialchars($media['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_public" 
                                   <?php echo $media['is_public'] ? 'checked' : ''; ?>>
                            <span class="form-check-label">Public file</span>
                        </label>
                        
                        <?php if ($current_user['role'] === 'admin'): ?>
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_featured" 
                                       <?php echo $media['is_featured'] ? 'checked' : ''; ?>>
                                <span class="form-check-label">Featured file</span>
                            </label>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-success">
                        <i data-feather="save"></i>
                        Update
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.media-preview-large {
    max-height: 300px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-icon-xl {
    height: 200px;
    width: 100%;
}

.media-edit-form .form-control-sm {
    font-size: 0.875rem;
}
</style>

<script>
function copyUrl(url) {
    const fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        // Show success feedback
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-feather="check"></i> Copied!';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            feather.replace();
        }, 2000);
        
        feather.replace();
    }).catch(err => {
        console.error('Failed to copy URL:', err);
        alert('Failed to copy URL to clipboard');
    });
}

// Initialize feather icons
feather.replace();
</script>
