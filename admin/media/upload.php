<?php
/**
 * Media Upload Interface
 * Handles file uploads with drag-and-drop support
 */

// Load core authentication

// Define admin access
define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/media.php';

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

// Initialize MediaManager
$media_manager = new MediaManager();

$message = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_files'])) {
    // CSRF Protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $files = $_FILES['media_files'];
        $uploaded_files = [];
        $errors = [];
        
        // Handle multiple files
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    $options = [
                        'alt_text' => $_POST['alt_text'][$i] ?? '',
                        'caption' => $_POST['caption'][$i] ?? '',
                        'description' => $_POST['description'][$i] ?? '',
                        'is_public' => isset($_POST['is_public'][$i]) ? 1 : 0
                    ];
                    
                    $result = $media_manager->uploadFile($file, $current_campus['id'], $current_user['id'], $options);
                    
                    if ($result['success']) {
                        $uploaded_files[] = $result;
                    } else {
                        $errors[] = $files['name'][$i] . ': ' . $result['error'];
                    }
                }
            }
        } else {
            // Single file upload
            if ($files['error'] === UPLOAD_ERR_OK) {
                $options = [
                    'alt_text' => $_POST['alt_text'] ?? '',
                    'caption' => $_POST['caption'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'is_public' => isset($_POST['is_public']) ? 1 : 0
                ];
                
                $result = $media_manager->uploadFile($files, $current_campus['id'], $current_user['id'], $options);
                
                if ($result['success']) {
                    $uploaded_files[] = $result;
                } else {
                    $errors[] = $result['error'];
                }
            }
        }
        
        if (!empty($uploaded_files)) {
            $message = count($uploaded_files) . ' file(s) uploaded successfully!';
        }
        
        if (!empty($errors)) {
            $error = 'Upload errors: ' . implode(', ', $errors);
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Upload Media';
include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-xl">
    <!-- Page Header -->
    <div class="row g-2 align-items-center mb-4">
        <div class="col">
            <h2 class="page-title">Upload Media</h2>
            <div class="text-muted">Upload images, documents, and other media files</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="index.php" class="btn btn-primary">
                    <i data-feather="arrow-left"></i>
                    Back to Media Library
                </a>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Upload Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Select Files</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <!-- Drag & Drop Upload Area -->
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-area-content">
                                <i data-feather="upload-cloud" class="mb-3" style="width: 48px; height: 48px;"></i>
                                <h4>Drag & Drop Files Here</h4>
                                <p class="text-muted">or click to browse files</p>
                                <input type="file" name="media_files[]" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.webm,.mp3,.wav">
                            </div>
                        </div>

                        <!-- File List -->
                        <div id="fileList" class="mt-4" style="display: none;">
                            <h5>Selected Files</h5>
                            <div id="filesContainer"></div>
                        </div>

                        <!-- Global Options -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label">Default Alt Text</label>
                                <input type="text" class="form-control" name="default_alt_text" id="defaultAltText" placeholder="Default alt text for images">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Caption</label>
                                <input type="text" class="form-control" name="default_caption" id="defaultCaption" placeholder="Default caption">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" name="default_is_public" id="defaultIsPublic" checked>
                                    <span class="form-check-label">Make files public by default</span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                <i data-feather="upload"></i>
                                Upload Files
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearFiles()">
                                <i data-feather="x"></i>
                                Clear All
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Upload Guidelines -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Guidelines</h3>
                </div>
                <div class="card-body">
                    <h6>Allowed File Types:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Images:</strong> JPG, PNG, GIF, WebP</li>
                        <li><strong>Documents:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX</li>
                        <li><strong>Videos:</strong> MP4, WebM, MOV</li>
                        <li><strong>Audio:</strong> MP3, WAV, OGG</li>
                    </ul>
                    
                    <h6 class="mt-3">File Size Limits:</h6>
                    <ul class="list-unstyled">
                        <li>Maximum file size: <strong>10MB</strong></li>
                        <li>Multiple files supported</li>
                    </ul>
                    
                    <h6 class="mt-3">Tips:</h6>
                    <ul class="list-unstyled text-muted small">
                        <li>• Use descriptive filenames</li>
                        <li>• Add alt text for accessibility</li>
                        <li>• Optimize images before upload</li>
                        <li>• Use captions for context</li>
                    </ul>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Recent Uploads</h3>
                </div>
                <div class="card-body">
                    <?php
                    $recent_media = $media_manager->getMediaFiles($current_campus['id'], ['per_page' => 5]);
                    if (!empty($recent_media['files'])):
                    ?>
                        <?php foreach ($recent_media['files'] as $media): ?>
                            <div class="d-flex align-items-center mb-2">
                                <?php if ($media['file_type'] === 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($media['file_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($media['alt_text']); ?>" 
                                         class="avatar avatar-sm me-2">
                                <?php else: ?>
                                    <div class="avatar avatar-sm bg-secondary text-white me-2">
                                        <?php echo strtoupper(substr($media['file_extension'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-fill">
                                    <div class="text-truncate"><?php echo htmlspecialchars($media['original_filename']); ?></div>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($media['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent uploads</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 3rem 2rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.upload-area:hover {
    border-color: #0d6efd;
    background: #e7f1ff;
}

.upload-area.dragover {
    border-color: #0d6efd;
    background: #e7f1ff;
    transform: scale(1.02);
}

.upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-item {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #fff;
}

.file-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.file-icon {
    width: 60px;
    height: 60px;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const filesContainer = document.getElementById('filesContainer');
    const uploadBtn = document.getElementById('uploadBtn');
    let selectedFiles = [];

    // Click to browse
    uploadArea.addEventListener('click', function(e) {
        // Only trigger file input if clicking on the upload area itself, not child elements
        if (e.target === uploadArea || e.target.closest('.upload-area-content')) {
            fileInput.click();
        }
    });

    // Prevent file input from triggering when already open
    fileInput.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Drag and drop events
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            handleFiles(files);
            // Reset the file input to allow selecting the same files again if needed
            fileInput.value = '';
        }
    });

    function handleFiles(files) {
        // Filter out duplicate files based on name and size
        const newFiles = files.filter(file => {
            return !selectedFiles.some(existingFile => 
                existingFile.name === file.name && existingFile.size === file.size
            );
        });
        
        selectedFiles = [...selectedFiles, ...newFiles];
        updateFileList();
        updateUploadButton();
    }

    function updateFileList() {
        if (selectedFiles.length === 0) {
            fileList.style.display = 'none';
            // Clean up any existing object URLs
            document.querySelectorAll('.file-preview').forEach(img => {
                if (img.src.startsWith('blob:')) {
                    URL.revokeObjectURL(img.src);
                }
            });
            return;
        }

        fileList.style.display = 'block';
        
        // Clean up existing object URLs before clearing container
        document.querySelectorAll('.file-preview').forEach(img => {
            if (img.src.startsWith('blob:')) {
                URL.revokeObjectURL(img.src);
            }
        });
        
        filesContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const isImage = file.type.startsWith('image/');
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            let previewUrl = '';
            if (isImage) {
                previewUrl = URL.createObjectURL(file);
            }
            
            fileItem.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-auto">
                        ${isImage ? 
                            `<img src="${previewUrl}" alt="Preview" class="file-preview">` :
                            `<div class="file-icon">${file.name.split('.').pop().toUpperCase()}</div>`
                        }
                    </div>
                    <div class="col">
                        <div class="fw-semibold">${file.name}</div>
                        <div class="text-muted small">${fileSize} MB • ${file.type}</div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" 
                                       name="alt_text[${index}]" placeholder="Alt text" 
                                       value="${document.getElementById('defaultAltText').value}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" 
                                       name="caption[${index}]" placeholder="Caption"
                                       value="${document.getElementById('defaultCaption').value}">
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <label class="form-check form-check-sm">
                                <input type="checkbox" class="form-check-input" 
                                       name="is_public[${index}]" ${document.getElementById('defaultIsPublic').checked ? 'checked' : ''}>
                                <span class="form-check-label">Public</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
            
            filesContainer.appendChild(fileItem);
        });

        // Re-initialize Feather icons
        feather.replace();
    }

    function updateUploadButton() {
        uploadBtn.disabled = selectedFiles.length === 0;
    }

    // Global functions
    window.removeFile = function(index) {
        // Clean up object URL for the removed file if it's an image
        const fileItem = document.querySelectorAll('.file-item')[index];
        if (fileItem) {
            const preview = fileItem.querySelector('.file-preview');
            if (preview && preview.src.startsWith('blob:')) {
                URL.revokeObjectURL(preview.src);
            }
        }
        
        selectedFiles.splice(index, 1);
        updateFileList();
        updateUploadButton();
    };

    window.clearFiles = function() {
        selectedFiles = [];
        fileInput.value = '';
        updateFileList();
        updateUploadButton();
        
        // Clear any object URLs to prevent memory leaks
        document.querySelectorAll('.file-preview').forEach(img => {
            if (img.src.startsWith('blob:')) {
                URL.revokeObjectURL(img.src);
            }
        });
    };

    // Apply defaults when they change
    document.getElementById('defaultAltText').addEventListener('input', function() {
        document.querySelectorAll('input[name^="alt_text"]').forEach(input => {
            if (!input.value) input.value = this.value;
        });
    });

    document.getElementById('defaultCaption').addEventListener('input', function() {
        document.querySelectorAll('input[name^="caption"]').forEach(input => {
            if (!input.value) input.value = this.value;
        });
    });

    document.getElementById('defaultIsPublic').addEventListener('change', function() {
        document.querySelectorAll('input[name^="is_public"]').forEach(input => {
            input.checked = this.checked;
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
