<?php
/**
 * Media Browser for WYSIWYG Editor
 * Popup window for selecting media files in posts
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

// Initialize MediaManager
$media_manager = new MediaManager();

// Get parameters
$browser_type = $_GET['type'] ?? 'content'; // 'content' or 'featured'
$file_type = $_GET['file_type'] ?? '';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 12;

// Build filter options
$filter_options = [
    'page' => $page,
    'per_page' => $per_page,
    'is_public' => 1 // Only show public files in browser
];

if ($file_type) {
    $filter_options['file_type'] = $file_type;
}

if ($search) {
    $filter_options['search'] = $search;
}

// Get media files
$media_data = $media_manager->getMediaFiles($current_campus['id'], $filter_options);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Browser</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.css" rel="stylesheet">
    
    <style>
        body {
            padding: 20px;
            background: #f8f9fa;
        }
        
        .media-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .media-item.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        
        .media-preview {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
            border-radius: 6px;
        }
        
        .media-preview img {
            max-height: 100%;
            max-width: 100%;
            object-fit: cover;
        }
        
        .file-icon {
            width: 60px;
            height: 60px;
            background: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.875rem;
        }
        
        .media-info {
            padding: 8px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        
        .filter-bar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .action-bar {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 15px;
            margin: 0 -20px -20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <?php 
                echo $browser_type === 'featured' ? 'Select Featured Image' : 'Select Media';
                if ($file_type) {
                    echo ' (' . ucfirst($file_type) . 's only)';
                }
                ?>
            </h4>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                <i data-feather="x"></i>
                Close
            </button>
        </div>
        
        <!-- Filters -->
        <div class="filter-bar p-3 mb-4">
            <form method="GET" class="row g-3">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($browser_type); ?>">
                <?php if (isset($_GET['file_type']) && $_GET['file_type']): ?>
                    <input type="hidden" name="file_type" value="<?php echo htmlspecialchars($_GET['file_type']); ?>">
                <?php endif; ?>
                
                <?php if (!isset($_GET['file_type']) || !$_GET['file_type']): ?>
                <div class="col-md-4">
                    <label class="form-label">File Type</label>
                    <select name="file_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="image" <?php echo $file_type === 'image' ? 'selected' : ''; ?>>Images</option>
                        <option value="document" <?php echo $file_type === 'document' ? 'selected' : ''; ?>>Documents</option>
                        <option value="video" <?php echo $file_type === 'video' ? 'selected' : ''; ?>>Videos</option>
                        <option value="audio" <?php echo $file_type === 'audio' ? 'selected' : ''; ?>>Audio</option>
                    </select>
                </div>
                <div class="col-md-6">
                <?php else: ?>
                <div class="col-md-8">
                <?php endif; ?>
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control form-control-sm" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search files...">
                </div>
                <div class="col-md-<?php echo (!isset($_GET['file_type']) || !$_GET['file_type']) ? '2' : '4'; ?>">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="upload.php" target="_blank" class="btn btn-sm btn-outline-success">
                            <i data-feather="upload"></i> Upload
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Media Grid -->
        <?php if (empty($media_data['files'])): ?>
            <div class="text-center py-5">
                <i data-feather="image" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                <h5 class="text-muted">No media files found</h5>
                <p class="text-muted">
                    <?php if ($search || $file_type): ?>
                        Try adjusting your search criteria.
                    <?php else: ?>
                        Upload some files to get started.
                    <?php endif; ?>
                </p>
                <a href="upload.php" target="_blank" class="btn btn-primary">
                    <i data-feather="upload"></i>
                    Upload Media
                </a>
            </div>
        <?php else: ?>
            <div class="row g-3" id="mediaGrid">
                <?php foreach ($media_data['files'] as $media): ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="card media-item" data-media-id="<?php echo $media['id']; ?>" 
                             data-url="<?php echo htmlspecialchars($media['file_url']); ?>"
                             data-filename="<?php echo htmlspecialchars($media['original_filename']); ?>"
                             data-alt="<?php echo htmlspecialchars($media['alt_text']); ?>"
                             data-caption="<?php echo htmlspecialchars($media['caption']); ?>"
                             data-type="<?php echo $media['file_type']; ?>"
                             onclick="selectMedia(this)">
                            
                            <div class="media-preview">
                                <?php if ($media['file_type'] === 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($media['file_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($media['alt_text']); ?>">
                                <?php else: ?>
                                    <div class="file-icon">
                                        <?php echo strtoupper($media['file_extension']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="media-info">
                                <div class="fw-semibold small text-truncate" title="<?php echo htmlspecialchars($media['original_filename']); ?>">
                                    <?php echo htmlspecialchars($media['original_filename']); ?>
                                </div>
                                <div class="text-muted small">
                                    <?php echo ucfirst($media['file_type']); ?> • 
                                    <?php echo number_format($media['file_size'] / 1024, 1); ?>KB
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($media_data['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $media_data['total_pages']; $i++): ?>
                            <?php
                            $params = $_GET;
                            $params['page'] = $i;
                            $url = 'media-browser.php?' . http_build_query($params);
                            ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Action Bar -->
    <div class="action-bar">
        <div class="d-flex justify-content-between align-items-center">
            <div id="selectedInfo" class="text-muted">
                Select a media file to insert
            </div>
            <div>
                <button type="button" class="btn btn-secondary me-2" onclick="window.close()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="insertBtn" onclick="insertMedia()" disabled>
                    Insert Media
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
    
    <script>
        let selectedMedia = null;
        const browserType = '<?php echo $browser_type; ?>';
        
        function selectMedia(element) {
            // Remove previous selection
            document.querySelectorAll('.media-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Select current item
            element.classList.add('selected');
            
            // Store selected media data
            selectedMedia = {
                id: element.dataset.mediaId,
                url: element.dataset.url,
                filename: element.dataset.filename,
                alt: element.dataset.alt,
                caption: element.dataset.caption,
                type: element.dataset.type
            };
            
            // Update UI
            document.getElementById('selectedInfo').textContent = `Selected: ${selectedMedia.filename}`;
            document.getElementById('insertBtn').disabled = false;
        }
        
        function insertMedia() {
            if (!selectedMedia) {
                alert('Please select a media file');
                return;
            }
            
            // Determine callback function based on browser type
            const callbackName = browserType === 'featured' ? 'insertFeaturedImageCallback' : 'insertMediaCallback';
            
            // Check if parent window has the appropriate callback function
            if (window.opener && window.opener[callbackName]) {
                window.opener[callbackName](selectedMedia);
                window.close();
            } else if (window.parent && window.parent[callbackName]) {
                window.parent[callbackName](selectedMedia);
                // For iframe, don't close the window, just hide the modal
                if (window.parent.hideMediaBrowser) {
                    window.parent.hideMediaBrowser();
                }
            } else {
                // Fallback: copy URL to clipboard
                const fullUrl = window.location.origin + selectedMedia.url;
                navigator.clipboard.writeText(fullUrl).then(() => {
                    alert('Media URL copied to clipboard: ' + fullUrl);
                    window.close();
                }).catch(() => {
                    alert('Media URL: ' + fullUrl);
                    window.close();
                });
            }
        }
        
        // Initialize feather icons
        feather.replace();
        
        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            } else if (e.key === 'Enter' && selectedMedia) {
                insertMedia();
            }
        });
        
        // Auto-focus search on load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>
