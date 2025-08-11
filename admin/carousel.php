<?php
/**
 * Carousel Management Module
 * Admin interface for managing carousel items
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/utilities.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/classes/Database.php';

// Check permissions - only campus admins and super admins can manage carousel
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to manage carousel.'
    ];
    header('Location: ../index.php');
    exit;
}

$page_title = "Carousel Management";
$page_description = "Manage carousel for " . get_current_campus()['name'];

// Set breadcrumbs for the header
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'index.php'],
    ['title' => 'Carousel Management', 'url' => '']
];

// Get current campus info
$campus_info = get_current_campus();
$campus_id = $campus_info['id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_carousel_items':
            echo json_encode(getCarouselItems($campus_id));
            exit;
            
        case 'update_order':
            $items = json_decode($_POST['items'], true);
            echo json_encode(updateCarouselOrder($items));
            exit;
            
        case 'toggle_status':
            $id = (int)$_POST['id'];
            $status = (bool)$_POST['status'];
            echo json_encode(toggleCarouselStatus($id, $status));
            exit;
            
        case 'delete_item':
            $id = (int)$_POST['id'];
            echo json_encode(deleteCarouselItem($id));
            exit;
            
        case 'save_item':
            $result = saveCarouselItem($_POST, $_FILES);
            echo json_encode($result);
            exit;
    }
}

// Helper functions
function getCarouselItems($campus_id) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM carousel_items 
            WHERE campus_id = ? 
            ORDER BY display_order ASC, created_at ASC
        ");
        $stmt->execute([$campus_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert image paths to web-accessible URLs
        foreach ($items as &$item) {
            $item['web_image_path'] = getWebImagePath($item['image_path']);
        }
        
        return ['success' => true, 'items' => $items];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to load carousel items: ' . $e->getMessage()];
    }
}

function getWebImagePath($imagePath) {
    // If it's already a full URL, return as is
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // If it starts with public/, we need to go up one level from admin
    if (strpos($imagePath, 'public/') === 0) {
        return '../' . $imagePath;
    }
    
    // If it starts with uploads/, we need to go up one level from admin
    if (strpos($imagePath, 'uploads/') === 0) {
        return '../' . $imagePath;
    }
    
    // For any other relative path, assume it needs to go up one level
    return '../' . ltrim($imagePath, '/');
}

function updateCarouselOrder($items) {
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE carousel_items SET display_order = ? WHERE id = ?");
        
        foreach ($items as $index => $item) {
            $stmt->execute([$index + 1, $item['id']]);
        }
        
        $db->commit();
        return ['success' => true, 'message' => 'Order updated successfully'];
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()];
    }
}

function toggleCarouselStatus($id, $status) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE carousel_items SET is_active = ? WHERE id = ?");
        $stmt->execute([$status ? 1 : 0, $id]);
        return ['success' => true, 'message' => 'Status updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()];
    }
}

function deleteCarouselItem($id) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get image path before deleting
        $stmt = $db->prepare("SELECT image_path FROM carousel_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Delete the database record
            $stmt = $db->prepare("DELETE FROM carousel_items WHERE id = ?");
            $stmt->execute([$id]);
            
            // Delete the image file if it exists and is not a default image
            if ($item['image_path'] && strpos($item['image_path'], 'public/img/') === false) {
                $imagePath = __DIR__ . '/../../' . ltrim($item['image_path'], '/');
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        
        return ['success' => true, 'message' => 'Item deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to delete item: ' . $e->getMessage()];
    }
}

function saveCarouselItem($data, $files) {
    global $campus_id;
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Validate required fields
        if (empty($files['image']['name']) && empty($data['id'])) {
            return ['success' => false, 'message' => 'Image is required for new carousel items'];
        }
        
        // Handle image upload
        $imagePath = '';
        if (!empty($files['image']['name'])) {
            $uploadResult = handleImageUpload($files['image'], $campus_id);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagePath = $uploadResult['path'];
        }
        
        if (!empty($data['id'])) {
            // Update existing item
            $sql = "UPDATE carousel_items SET title = ?, description = ?, image_alt = ?, link_url = ?, link_target = ?, updated_at = CURRENT_TIMESTAMP";
            $params = [$data['title'], $data['description'], $data['image_alt'], $data['link_url'], $data['link_target']];
            
            if ($imagePath) {
                $sql .= ", image_path = ?";
                $params[] = $imagePath;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $data['id'];
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Carousel item updated successfully'];
        } else {
            // Create new item
            $stmt = $db->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM carousel_items WHERE campus_id = ?");
            $stmt->execute([$campus_id]);
            $nextOrder = $stmt->fetch(PDO::FETCH_ASSOC)['next_order'];
            
            $stmt = $db->prepare("
                INSERT INTO carousel_items (campus_id, title, description, image_path, image_alt, link_url, link_target, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $campus_id,
                $data['title'],
                $data['description'],
                $imagePath,
                $data['image_alt'],
                $data['link_url'],
                $data['link_target'],
                $nextOrder
            ]);
            
            return ['success' => true, 'message' => 'Carousel item created successfully'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to save item: ' . $e->getMessage()];
    }
}

function handleImageUpload($file, $campus_id) {
    require_once __DIR__ . '/../core/classes/ImageProcessor.php';
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum size is 10MB.'];
    }
    
    // Create upload directories
    $uploadDir = __DIR__ . '/../uploads/carousel/' . $campus_id . '/';
    $thumbDir = __DIR__ . '/../uploads/carousel/' . $campus_id . '/thumbs/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'carousel_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    $thumbPath = $thumbDir . $filename;
    $relativePath = 'uploads/carousel/' . $campus_id . '/' . $filename;
    
    // Process and optimize the image
    $processResult = ImageProcessor::processCarouselImage($file['tmp_name'], $uploadPath);
    
    if ($processResult['success']) {
        // Create thumbnail for admin preview
        ImageProcessor::createThumbnail($uploadPath, $thumbPath);
        
        return [
            'success' => true, 
            'path' => $relativePath,
            'dimensions' => $processResult['dimensions'],
            'original_dimensions' => $processResult['original_dimensions']
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to process image: ' . $processResult['error']];
    }
}

// Load carousel items for initial display
$carouselItems = getCarouselItems($campus_id);

include 'layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>
    
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <li class="breadcrumb-item<?php echo empty($crumb['url']) ? ' active' : ''; ?>">
                    <?php if (!empty($crumb['url'])): ?>
                        <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['title']; ?></a>
                    <?php else: ?>
                        <?php echo $crumb['title']; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <!-- Toasts will be dynamically added here -->
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-6">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#carouselModal">
                <i class="fas fa-plus"></i>&nbsp; Add New Carousel Item
            </button>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-info" id="previewCarousel">
                <i class="fas fa-eye"></i> &nbsp; Preview Carousel
            </button>
            <a href="../<?php echo $campus_info['code']; ?>/public/index.php" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt"></i> &nbsp; View Frontend
            </a>
        </div>
    </div>

    <!-- Carousel Items Management -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Manage Carousel Items</h5>
            <small class="text-muted">Drag and drop to reorder items. Click edit to modify content.</small>
        </div>
        <div class="card-body">
            <div id="carouselItemsList" class="row">
                <!-- Items will be loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Carousel Item Modal -->
<div class="modal fade" id="carouselModal" tabindex="-1" aria-labelledby="carouselModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carouselModalLabel">Add Carousel Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="carouselForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="id">
                    <input type="hidden" name="action" value="save_item">
                    
                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="carouselImage" class="form-label">Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="carouselImage" name="image" accept="image/*">
                        <div class="form-text">
                            Recommended size: 1904x534 pixels. Max file size: 5MB. Formats: JPG, PNG, GIF, WebP
                        </div>
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                        <div id="cropperContainer" class="mt-3" style="display: none;">
                            <label class="form-label">Crop Image (Optional)</label>
                            <div id="cropperWrapper" style="max-height: 400px; overflow: hidden;">
                                <img id="cropperImg" src="" alt="Crop" style="max-width: 100%;">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="resetCrop">Reset</button>
                                <button type="button" class="btn btn-sm btn-primary" id="applyCrop">Apply Crop</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="carouselTitle" class="form-label">Title (Optional)</label>
                        <input type="text" class="form-control" id="carouselTitle" name="title" maxlength="255">
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="carouselDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="carouselDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <!-- Image Alt Text -->
                    <div class="mb-3">
                        <label for="carouselAlt" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="carouselAlt" name="image_alt" maxlength="255">
                        <div class="form-text">Describe the image for accessibility</div>
                    </div>
                    
                    <!-- Link URL -->
                    <div class="mb-3">
                        <label for="carouselLink" class="form-label">Link URL (Optional)</label>
                        <input type="url" class="form-control" id="carouselLink" name="link_url">
                        <div class="form-text">URL to redirect when carousel item is clicked</div>
                    </div>
                    
                    <!-- Link Target -->
                    <div class="mb-3">
                        <label for="carouselTarget" class="form-label">Link Target</label>
                        <select class="form-select" id="carouselTarget" name="link_target">
                            <option value="_self">Same window</option>
                            <option value="_blank">New window</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Carousel Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Carousel Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Carousel Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="carouselPreview">
                    <!-- Preview will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
    let carouselItems = [];
    let cropper = null;
    let croppedBlob = null;
    
    // Load carousel items
    loadCarouselItems();
    
    // Initialize sortable
    const carouselList = document.getElementById('carouselItemsList');
    const sortable = Sortable.create(carouselList, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            updateCarouselOrder();
        }
    });
    
    // Form submission
    document.getElementById('carouselForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCarouselItem();
    });
    
    // Image preview
    document.getElementById('carouselImage').addEventListener('change', function(e) {
        previewImage(e.target);
    });
    
    // Preview carousel
    document.getElementById('previewCarousel').addEventListener('click', function() {
        showCarouselPreview();
    });
    
    // Modal reset on close
    document.getElementById('carouselModal').addEventListener('hidden.bs.modal', function() {
        resetForm();
        document.getElementById('carouselModalLabel').textContent = 'Add Carousel Item';
    });
    
    function loadCarouselItems() {
        fetch('carousel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=get_carousel_items'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                carouselItems = data.items;
                renderCarouselItems();
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to load carousel items');
        });
    }
    
    function renderCarouselItems() {
        const container = document.getElementById('carouselItemsList');
        container.innerHTML = '';
        
        carouselItems.forEach((item, index) => {
            const itemHtml = `
                <div class="col-md-6 col-lg-4 mb-3" data-id="${item.id}">
                    <div class="card carousel-item-card ${item.is_active ? '' : 'opacity-50'}">
                        <div class="card-img-top position-relative">
                            <img src="${item.web_image_path}" alt="${item.image_alt || ''}" 
                                 class="img-fluid" style="height: 150px; object-fit: cover; width: 100%;">
                            <div class="position-absolute top-0 end-0 p-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           ${item.is_active ? 'checked' : ''} 
                                           onchange="toggleItemStatus(${item.id}, this.checked)">
                                    <label class="form-check-label text-white bg-dark px-1 rounded">
                                        ${item.is_active ? 'Active' : 'Inactive'}
                                    </label>
                                </div>
                            </div>
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge bg-primary">#${index + 1}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">${item.title || 'No Title'}</h6>
                            <p class="card-text small text-muted">${item.description ? item.description.substring(0, 80) + '...' : 'No description'}</p>
                            <div class="btn-group w-100">
                                <button class="btn btn-sm btn-outline-primary" onclick="editItem(${item.id})">
                                    <i class="fas fa-edit"></i>&nbsp; Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(${item.id})">
                                    <i class="fas fa-trash"></i>&nbsp; Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += itemHtml;
        });
        
        if (carouselItems.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No carousel items found. <a href="#" data-bs-toggle="modal" data-bs-target="#carouselModal">Add your first item</a>.</p></div>';
        }
    }
    
    function updateCarouselOrder() {
        const items = Array.from(document.querySelectorAll('#carouselItemsList > div')).map(el => ({
            id: el.dataset.id
        }));
        
        fetch('carousel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=update_order&items=' + encodeURIComponent(JSON.stringify(items))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                loadCarouselItems();
            } else {
                showToast('error', data.message);
            }
        });
    }
    
    function toggleItemStatus(id, status) {
        fetch('carousel.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=toggle_status&id=${id}&status=${status ? 1 : 0}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                loadCarouselItems();
            } else {
                showToast('error', data.message);
            }
        });
    }
    
    function deleteItem(id) {
        if (confirm('Are you sure you want to delete this carousel item? This action cannot be undone.')) {
            fetch('carousel.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_item&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    loadCarouselItems();
                } else {
                    showToast('error', data.message);
                }
            });
        }
    }
    
    function editItem(id) {
        const item = carouselItems.find(i => i.id == id);
        if (item) {
            resetForm(); // Reset any previous state
            
            document.getElementById('itemId').value = item.id;
            document.getElementById('carouselTitle').value = item.title || '';
            document.getElementById('carouselDescription').value = item.description || '';
            document.getElementById('carouselAlt').value = item.image_alt || '';
            document.getElementById('carouselLink').value = item.link_url || '';
            document.getElementById('carouselTarget').value = item.link_target || '_self';
            
            // Show current image
            if (item.image_path) {
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('previewImg').src = item.web_image_path;
            }
            
            document.getElementById('carouselModalLabel').textContent = 'Edit Carousel Item';
            new bootstrap.Modal(document.getElementById('carouselModal')).show();
        }
    }
    
    function saveCarouselItem() {
        const form = document.getElementById('carouselForm');
        const formData = new FormData(form);
        
        // If we have a cropped image, replace the original file
        if (croppedBlob) {
            formData.delete('image'); // Remove original
            formData.append('image', croppedBlob, 'cropped-image.jpg');
        }
        
        fetch('carousel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('carouselModal')).hide();
                resetForm();
                loadCarouselItems();
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to save carousel item');
        });
    }
    
    function resetForm() {
        const form = document.getElementById('carouselForm');
        form.reset();
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('cropperContainer').style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        croppedBlob = null;
    }
    
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('previewImg').src = e.target.result;
                
                // Initialize cropper
                const cropperImg = document.getElementById('cropperImg');
                cropperImg.src = e.target.result;
                document.getElementById('cropperContainer').style.display = 'block';
                
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(cropperImg, {
                    aspectRatio: 1904 / 534, // Carousel aspect ratio
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                    guides: true,
                    center: true,
                    highlight: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });
            };
            
            reader.readAsDataURL(file);
        }
    }
    
    // Crop controls
    document.getElementById('resetCrop').addEventListener('click', function() {
        if (cropper) {
            cropper.reset();
            croppedBlob = null;
        }
    });
    
    document.getElementById('applyCrop').addEventListener('click', function() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 1904,
                height: 534,
                imageSmoothingQuality: 'high'
            });
            
            canvas.toBlob(function(blob) {
                croppedBlob = blob;
                const url = URL.createObjectURL(blob);
                document.getElementById('previewImg').src = url;
                showToast('success', 'Crop applied! The cropped image will be used when saving.');
            }, 'image/jpeg', 0.9);
        }
    });
    
    function showCarouselPreview() {
        const activeItems = carouselItems.filter(item => item.is_active);
        
        if (activeItems.length === 0) {
            showToast('warning', 'No active carousel items to preview');
            return;
        }
        
        // Generate unique carousel ID to avoid conflicts
        const carouselId = 'adminPreviewCarousel' + Date.now();
        
        let previewHtml = `
            <div id="${carouselId}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-indicators">
        `;
        
        // Add indicators
        activeItems.forEach((item, index) => {
            previewHtml += `
                <button type="button" data-bs-target="#${carouselId}" data-bs-slide-to="${index}" 
                        class="${index === 0 ? 'active' : ''}" aria-current="${index === 0 ? 'true' : 'false'}" 
                        aria-label="Slide ${index + 1}"></button>
            `;
        });
        
        previewHtml += `
                </div>
                <div class="carousel-inner">
        `;
        
        // Add carousel items
        activeItems.forEach((item, index) => {
            previewHtml += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${item.web_image_path}" class="d-block w-100" alt="${item.image_alt || ''}" style="height: 400px; object-fit: cover;">
                    ${item.title || item.description ? `
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                            ${item.title ? `<h5 class="text-white mb-2">${item.title}</h5>` : ''}
                            ${item.description ? `<p class="text-white mb-0">${item.description}</p>` : ''}
                        </div>
                    ` : ''}
                </div>
            `;
        });
        
        previewHtml += `
                </div>
        `;
        
        // Add controls only if there are multiple items
        if (activeItems.length > 1) {
            previewHtml += `
                <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            `;
        }
        
        previewHtml += `</div>`;
        
        // Add preview info
        previewHtml += `
            <div class="mt-3 p-3 bg-light rounded">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Preview showing ${activeItems.length} active carousel item${activeItems.length !== 1 ? 's' : ''}. 
                    ${activeItems.length > 1 ? 'Use arrow keys or click indicators to navigate.' : ''}
                </small>
            </div>
        `;
        
        // Insert HTML and show modal
        document.getElementById('carouselPreview').innerHTML = previewHtml;
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
        
        // Initialize carousel after modal is shown
        document.getElementById('previewModal').addEventListener('shown.bs.modal', function initCarousel() {
            const carouselElement = document.getElementById(carouselId);
            if (carouselElement && activeItems.length > 1) {
                // Initialize Bootstrap carousel
                const carouselInstance = new bootstrap.Carousel(carouselElement, {
                    interval: 3000,
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true,
                    keyboard: true,
                    touch: true
                });
                
                // Add keyboard support
                document.addEventListener('keydown', function handleKeydown(e) {
                    if (document.querySelector('#previewModal.show')) {
                        if (e.key === 'ArrowLeft') {
                            e.preventDefault();
                            carouselInstance.prev();
                        } else if (e.key === 'ArrowRight') {
                            e.preventDefault();
                            carouselInstance.next();
                        }
                    }
                });
                
                // Clean up event listener when modal is hidden
                document.getElementById('previewModal').addEventListener('hidden.bs.modal', function cleanup() {
                    document.removeEventListener('keydown', handleKeydown);
                    this.removeEventListener('hidden.bs.modal', cleanup);
                });
            }
            
            // Remove this event listener after first use
            this.removeEventListener('shown.bs.modal', initCarousel);
        });
    }
    
    function showToast(type, message) {
        const toastContainer = document.querySelector('.toast-container');
        const toastId = 'toast-' + Date.now();
        
        // Define toast types and their corresponding Bootstrap classes and icons
        const toastTypes = {
            'success': { bgClass: 'bg-success', icon: 'fas fa-check-circle', title: 'Success' },
            'error': { bgClass: 'bg-danger', icon: 'fas fa-exclamation-circle', title: 'Error' },
            'warning': { bgClass: 'bg-warning', icon: 'fas fa-exclamation-triangle', title: 'Warning' },
            'info': { bgClass: 'bg-info', icon: 'fas fa-info-circle', title: 'Info' }
        };
        
        const toastConfig = toastTypes[type] || toastTypes['info'];
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${toastConfig.bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${toastConfig.icon} me-2"></i>
                        <strong>${toastConfig.title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        
        toast.show();
        
        // Remove toast element from DOM after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
    
    // Global functions for inline event handlers
    window.toggleItemStatus = toggleItemStatus;
    window.deleteItem = deleteItem;
    window.editItem = editItem;
});
</script>

<style>
.carousel-item-card {
    transition: all 0.3s ease;
    cursor: move;
}

.carousel-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.05);
}

.sortable-drag {
    transform: rotate(5deg);
}

#carouselItemsList .col-md-6 {
    transition: all 0.3s ease;
}

/* Toast Enhancements */
.toast-container {
    z-index: 1055 !important;
}

.toast {
    min-width: 300px;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.toast .toast-body {
    font-weight: 500;
}

.toast .fas {
    font-size: 1.1em;
}

/* Carousel Preview Enhancements */
#previewModal .modal-dialog {
    max-width: 90vw;
}

#previewModal .carousel-item img {
    border-radius: 8px;
}

#previewModal .carousel-control-prev,
#previewModal .carousel-control-next {
    width: 5%;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 50%;
    height: 50px;
    top: 50%;
    transform: translateY(-50%);
}

#previewModal .carousel-control-prev {
    left: 15px;
}

#previewModal .carousel-control-next {
    right: 15px;
}

#previewModal .carousel-control-prev:hover,
#previewModal .carousel-control-next:hover {
    background: rgba(0, 0, 0, 0.5);
}

#previewModal .carousel-indicators {
    margin-bottom: -30px;
    z-index: 10;
}

#previewModal .carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 4px;
    background-color: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.8);
}

#previewModal .carousel-indicators button.active {
    background-color: #fff;
    transform: scale(1.2);
}

#previewModal .carousel-caption {
    bottom: 20px;
}
</style>

<?php include 'layouts/footer.php'; ?>
