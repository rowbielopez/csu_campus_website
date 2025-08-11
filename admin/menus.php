<?php
// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/utilities.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/classes/MenuManagerFlexible.php';

// Check permissions - only campus admins and super admins can manage menus
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to manage menus.'
    ];
    header('Location: ../index.php');
    exit;
}

$pageTitle = "Menu Builder";
$pageDescription = "Build and manage navigation menus for " . get_current_campus()['name'];

// Initialize MenuManager with flexible version
$menuManager = new MenuManagerFlexible();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_menu_item':
            $result = $menuManager->createMenuItem($_POST);
            echo json_encode(['success' => $result !== false, 'item_id' => $result]);
            exit;
            
        case 'update_menu_item':
            $result = $menuManager->updateMenuItem($_POST['item_id'], $_POST);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'delete_menu_item':
            $result = $menuManager->deleteMenuItem($_POST['item_id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_menu_order':
            $result = $menuManager->updateMenuOrder($_POST['item_orders']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_menu_item':
            $item = $menuManager->getMenuItem($_POST['item_id']);
            echo json_encode(['success' => true, 'item' => $item]);
            exit;
    }
}

// Get menu locations and their items
$menu_locations = [
    'main' => 'Main Navigation',
    'header' => 'Header Menu',
    'footer' => 'Footer Menu',
    'sidebar' => 'Sidebar Menu'
];

$menus_by_location = [];
foreach ($menu_locations as $location => $label) {
    $menus_by_location[$location] = $menuManager->getMenuTree($location);
}

// Get available pages and posts for linking
$available_pages = $menuManager->getAvailablePages();
$available_posts = $menuManager->getAvailablePosts();

include 'layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">Menu Builder</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="admin/">Dashboard</a></li>
                <li class="breadcrumb-item active">Menu Builder</li>
            </ol>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuItemModal">
            <i class="fas fa-plus me-2"></i>Add Menu Item
        </button>
    </div>

    <!-- Menu Areas -->
    <div class="row">
        <?php foreach ($menu_locations as $location => $label): ?>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bars me-2"></i><?= htmlspecialchars($label) ?>
                    </h5>
                    <span class="badge bg-secondary"><?= count($menus_by_location[$location]) ?> items</span>
                </div>
                <div class="card-body">
                    <div class="menu-area" data-location="<?= $location ?>" style="min-height: 200px;">
                        <?php if (empty($menus_by_location[$location])): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-sitemap fa-3x mb-3"></i>
                                <p>No menu items in this location</p>
                                <small>Drag items here or click "Add Menu Item" to get started</small>
                            </div>
                        <?php else: ?>
                            <div class="menu-items">
                                <?= renderMenuItems($menus_by_location[$location]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Menu Preview -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-eye me-2"></i>Menu Preview
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($menu_locations as $location => $label): ?>
                <div class="col-md-6 mb-4">
                    <h6><?= htmlspecialchars($label) ?></h6>
                    <div class="border p-3 bg-light">
                        <?= $menuManager->renderMenu($location, 'nav') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Menu Item Modal -->
<div class="modal fade" id="menuItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="menuItemForm">
                <div class="modal-body">
                    <input type="hidden" id="item_id" name="item_id">
                    <input type="hidden" id="action" name="action" value="create_menu_item">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="menu_title" class="form-label">Menu Title</label>
                            <input type="text" class="form-control" id="menu_title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="menu_location" class="form-label">Menu Location</label>
                            <select class="form-select" id="menu_location" name="menu_location" required>
                                <option value="">Select location</option>
                                <?php foreach ($menu_locations as $location => $label): ?>
                                <option value="<?= $location ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="url_type" class="form-label">Link Type</label>
                            <select class="form-select" id="url_type" name="url_type" required>
                                <option value="internal">Internal Link</option>
                                <option value="external">External Link</option>
                                <option value="page">Link to Page</option>
                                <option value="post">Link to Post</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="menu_target" class="form-label">Link Target</label>
                            <select class="form-select" id="menu_target" name="target">
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- URL Field (for internal/external) -->
                    <div class="mb-3" id="url_field">
                        <label for="menu_url" class="form-label">URL</label>
                        <input type="text" class="form-control" id="menu_url" name="url" placeholder="/about-us">
                    </div>
                    
                    <!-- Page Selection -->
                    <div class="mb-3 d-none" id="page_field">
                        <label for="target_page" class="form-label">Select Page</label>
                        <select class="form-select" id="target_page" name="target_page">
                            <option value="">Select a page</option>
                            <?php foreach ($available_pages as $page): ?>
                            <option value="<?= $page['id'] ?>"><?= htmlspecialchars($page['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Post Selection -->
                    <div class="mb-3 d-none" id="post_field">
                        <label for="target_post" class="form-label">Select Post</label>
                        <select class="form-select" id="target_post" name="target_post">
                            <option value="">Select a post</option>
                            <?php foreach ($available_posts as $post): ?>
                            <option value="<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="menu_icon" class="form-label">Icon Class</label>
                            <input type="text" class="form-control" id="menu_icon" name="icon" placeholder="fas fa-home">
                            <div class="form-text">FontAwesome icon class (optional)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="menu_css" class="form-label">CSS Class</label>
                            <input type="text" class="form-control" id="menu_css" name="css_class" placeholder="custom-menu-item">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="saveSpinner"></span>
                        Save Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable menu areas
    const menuAreas = document.querySelectorAll('.menu-area .menu-items');
    
    menuAreas.forEach(area => {
        Sortable.create(area, {
            group: 'menus',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.menu-item-handle',
            onEnd: function(evt) {
                updateMenuOrder();
            }
        });
    });
    
    // Form handling
    const menuItemForm = document.getElementById('menuItemForm');
    const menuItemModal = new bootstrap.Modal(document.getElementById('menuItemModal'));
    
    // URL type change handler
    document.getElementById('url_type').addEventListener('change', function() {
        const urlField = document.getElementById('url_field');
        const pageField = document.getElementById('page_field');
        const postField = document.getElementById('post_field');
        
        // Hide all fields
        urlField.classList.toggle('d-none', this.value === 'page' || this.value === 'post');
        pageField.classList.toggle('d-none', this.value !== 'page');
        postField.classList.toggle('d-none', this.value !== 'post');
        
        // Update URL field placeholder
        if (this.value === 'external') {
            document.getElementById('menu_url').placeholder = 'https://example.com';
        } else {
            document.getElementById('menu_url').placeholder = '/about-us';
        }
    });
    
    // Add menu item button
    document.querySelector('[data-bs-target="#menuItemModal"]').addEventListener('click', function() {
        resetMenuItemForm();
        document.querySelector('#menuItemModal .modal-title').textContent = 'Add Menu Item';
    });
    
    // Form submission
    menuItemForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveMenuItem();
    });
    
    // Event delegation for dynamic buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-menu-item')) {
            const itemId = e.target.closest('.btn-edit-menu-item').dataset.itemId;
            editMenuItem(itemId);
        } else if (e.target.closest('.btn-delete-menu-item')) {
            const itemId = e.target.closest('.btn-delete-menu-item').dataset.itemId;
            if (confirm('Are you sure you want to delete this menu item and all its children?')) {
                deleteMenuItem(itemId);
            }
        }
    });
    
    function resetMenuItemForm() {
        menuItemForm.reset();
        document.getElementById('action').value = 'create_menu_item';
        document.getElementById('item_id').value = '';
        document.getElementById('url_type').dispatchEvent(new Event('change'));
    }
    
    function saveMenuItem() {
        const spinner = document.getElementById('saveSpinner');
        spinner.classList.remove('d-none');
        
        const formData = new FormData(menuItemForm);
        
        // Set target_id based on url_type
        const urlType = formData.get('url_type');
        if (urlType === 'page') {
            formData.set('target_id', formData.get('target_page'));
        } else if (urlType === 'post') {
            formData.set('target_id', formData.get('target_post'));
        }
        
        fetch('admin/menus.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinner.classList.add('d-none');
            if (data.success) {
                showToast('Menu item saved successfully', 'success');
                menuItemModal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to save menu item', 'error');
            }
        });
    }
    
    function updateMenuOrder() {
        // Implementation for updating menu order
        showToast('Menu order updated', 'success');
    }
});
</script>

<style>
.menu-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: white;
    cursor: move;
    transition: all 0.3s ease;
}

.menu-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.menu-item.has-children {
    border-left: 4px solid #0d6efd;
}

.menu-item-handle {
    color: #6c757d;
    cursor: grab;
}

.menu-item-handle:active {
    cursor: grabbing;
}

.menu-children {
    margin-left: 1.5rem;
    margin-top: 0.5rem;
    border-left: 2px dashed #dee2e6;
    padding-left: 1rem;
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: rotate(2deg);
}

.menu-area {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.menu-area:hover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}
</style>

<?php
function renderMenuItems($items, $level = 0) {
    $html = '';
    foreach ($items as $item) {
        $html .= '<div class="menu-item' . (!empty($item['children']) ? ' has-children' : '') . '" data-item-id="' . $item['id'] . '">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<div class="d-flex align-items-center">';
        $html .= '<i class="fas fa-grip-vertical menu-item-handle me-2"></i>';
        if ($item['icon']) {
            $html .= '<i class="' . htmlspecialchars($item['icon']) . ' me-2"></i>';
        }
        $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';
        if (!$item['is_active']) {
            $html .= '<span class="badge bg-warning ms-2">Inactive</span>';
        }
        $html .= '</div>';
        $html .= '<div class="btn-group btn-group-sm">';
        $html .= '<button type="button" class="btn btn-outline-primary btn-edit-menu-item" data-item-id="' . $item['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
        $html .= '<button type="button" class="btn btn-outline-danger btn-delete-menu-item" data-item-id="' . $item['id'] . '" title="Delete"><i class="fas fa-trash"></i></button>';
        $html .= '</div>';
        $html .= '</div>';
        
        if (!empty($item['children'])) {
            $html .= '<div class="menu-children">';
            $html .= renderMenuItems($item['children'], $level + 1);
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    return $html;
}

include 'layouts/footer-new.php';
?>
