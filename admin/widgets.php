<?php
// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/utilities.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/classes/WidgetManagerFlexible.php';

// Check permissions - only campus admins and super admins can manage widgets
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to manage widgets.'
    ];
    header('Location: ../index.php');
    exit;
}

$pageTitle = "Widget Management";
$pageDescription = "Manage widgets for " . get_current_campus()['name'];

// Initialize WidgetManager with flexible version
$widgetManager = new WidgetManagerFlexible();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_widget':
            try {
                $db = Database::getInstance();
                
                // First, ensure the table exists
                $tableCheck = $db->fetchAll("SHOW TABLES LIKE 'campus_widgets'");
                if (empty($tableCheck)) {
                    echo json_encode(['success' => false, 'error' => 'campus_widgets table does not exist']);
                    exit;
                }
                
                // Get table structure to know what columns exist
                $columns = $db->fetchAll("DESCRIBE campus_widgets");
                $columnNames = array_column($columns, 'Field');
                
                // Prepare data based on existing columns
                $insertData = [];
                
                // Required fields
                $insertData['title'] = $_POST['title'];
                $insertData['position'] = $_POST['location'];
                $insertData['widget_type_id'] = (int)$_POST['widget_type_id'];
                
                // Optional fields (only if columns exist)
                if (in_array('campus_id', $columnNames)) {
                    $insertData['campus_id'] = defined('CAMPUS_ID') ? CAMPUS_ID : 1;
                }
                if (in_array('config', $columnNames)) {
                    $insertData['config'] = $_POST['configuration'] ?? '{}';
                }
                if (in_array('css_class', $columnNames)) {
                    $insertData['css_class'] = $_POST['css_class'] ?? '';
                }
                if (in_array('is_active', $columnNames)) {
                    $insertData['is_active'] = (int)($_POST['is_active'] ?? 1);
                }
                if (in_array('sort_order', $columnNames)) {
                    $insertData['sort_order'] = 1;
                }
                if (in_array('created_by', $columnNames)) {
                    $insertData['created_by'] = 1; // Default user for testing
                }
                if (in_array('created_at', $columnNames)) {
                    $insertData['created_at'] = date('Y-m-d H:i:s');
                }
                if (in_array('updated_at', $columnNames)) {
                    $insertData['updated_at'] = date('Y-m-d H:i:s');
                }
                
                // Build and execute insert query
                $fields = implode(', ', array_keys($insertData));
                $placeholders = ':' . implode(', :', array_keys($insertData));
                $sql = "INSERT INTO campus_widgets ({$fields}) VALUES ({$placeholders})";
                
                $result = $db->query($sql, $insertData);
                
                if ($result) {
                    $insertId = $db->lastInsertId();
                    
                    // Verify the widget was actually inserted
                    $verifyWidget = $db->fetch("SELECT * FROM campus_widgets WHERE id = ?", [$insertId]);
                    
                    if ($verifyWidget) {
                        echo json_encode([
                            'success' => true, 
                            'widget_id' => $insertId, 
                            'message' => 'Widget created and verified in database',
                            'widget' => $verifyWidget
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Widget inserted but verification failed']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Database insert query failed']);
                }
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false, 
                    'error' => $e->getMessage(), 
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
            }
            exit;
            
        case 'update_widget':
            // Map form fields to database fields
            $data = [
                'widget_type_id' => $_POST['widget_type_id'],
                'title' => $_POST['title'],
                'position' => $_POST['location'], // Form uses 'location', DB uses 'position'
                'config' => $_POST['configuration'] ?? '{}', // Keep as string for now
                'css_class' => $_POST['css_class'] ?? '',
                'is_active' => $_POST['is_active'] ?? 1
            ];
            $result = $widgetManager->updateWidget($_POST['widget_id'], $data);
            echo json_encode(['success' => $result, 'debug' => $data]);
            exit;
            
        case 'delete_widget':
            $result = $widgetManager->deleteWidget($_POST['widget_id']);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'update_widget_order':
            try {
                $widget_orders = json_decode($_POST['widget_orders'], true);
                error_log("Widget order update received: " . print_r($widget_orders, true));
                
                if (!$widget_orders) {
                    echo json_encode(['success' => false, 'error' => 'Invalid widget order data']);
                    exit;
                }
                
                $result = $widgetManager->updateWidgetOrder($widget_orders);
                error_log("Widget order update result: " . ($result ? 'SUCCESS' : 'FAILED'));
                
                echo json_encode(['success' => $result, 'debug' => $widget_orders]);
            } catch (Exception $e) {
                error_log("Widget order update exception: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'get_widget':
            $widget = $widgetManager->getWidget($_POST['widget_id']);
            echo json_encode(['success' => true, 'widget' => $widget]);
            exit;
    }
}

// Get available widget types
$widget_types = $widgetManager->getWidgetTypes();

// If no widget types exist, force creation of default types
if (empty($widget_types)) {
    try {
        $db = Database::getInstance();
        
        // Check if table exists
        $tables = $db->fetchAll("SHOW TABLES LIKE 'widget_types'");
        
        if (empty($tables)) {
            // Create widget_types table
            $sql = "CREATE TABLE widget_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                template_path VARCHAR(255),
                default_template TEXT,
                config_schema JSON,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $db->query($sql);
        }
        
        // Check if table is empty and populate with default types
        $count = $db->fetch("SELECT COUNT(*) as count FROM widget_types")['count'];
        
        if ($count == 0) {
            $defaultTypes = [
                [
                    'name' => 'Text Widget',
                    'description' => 'Display custom text content',
                    'template_path' => 'widgets/text.php',
                    'default_template' => '{"content": "", "show_title": true}'
                ],
                [
                    'name' => 'Image Widget',
                    'description' => 'Display an image with optional caption',
                    'template_path' => 'widgets/image.php',
                    'default_template' => '{"image_url": "", "alt_text": "", "caption": "", "link_url": ""}'
                ],
                [
                    'name' => 'Featured Post Widget',
                    'description' => 'Display a single post with full content, image, author and date',
                    'template_path' => 'widgets/featured_post.php',
                    'default_template' => '{"post_id": "", "show_image": true, "show_author": true, "show_date": true, "show_excerpt": true, "show_full_content": false}'
                ],
                [
                    'name' => 'Recent Posts Widget',
                    'description' => 'Display a list of recent posts with excerpts',
                    'template_path' => 'widgets/recent_posts.php',
                    'default_template' => '{"count": 5, "show_excerpt": true, "show_date": true, "show_author": true, "show_image": true}'
                ],
                [
                    'name' => 'Navigation Menu',
                    'description' => 'Display a navigation menu',
                    'template_path' => 'widgets/menu.php',
                    'default_template' => '{"menu_location": "main", "show_icons": false}'
                ],
                [
                    'name' => 'Contact Info',
                    'description' => 'Display contact information',
                    'template_path' => 'widgets/contact.php',
                    'default_template' => '{"phone": "", "email": "", "address": "", "show_map": false}'
                ],
                [
                    'name' => 'Image & Hyperlink Text Widget',
                    'description' => 'Display an image with URL redirect and hyperlink text below it',
                    'template_path' => 'widgets/image_hyperlink.php',
                    'default_template' => '{"image_url": "", "image_alt": "", "image_redirect_url": "", "text_content": "", "text_redirect_url": "", "external_image": false, "external_text": false, "show_title": true}'
                ]
            ];
            
            foreach ($defaultTypes as $type) {
                $db->query(
                    "INSERT INTO widget_types (name, description, template_path, default_template) VALUES (?, ?, ?, ?)",
                    [$type['name'], $type['description'], $type['template_path'], $type['default_template']]
                );
            }
            
            // Refresh widget types after insertion
            $widget_types = $widgetManager->getWidgetTypes();
        }
    } catch (Exception $e) {
        error_log("Error initializing widget types: " . $e->getMessage());
    }
}

// Get widgets grouped by location
$widget_locations = [
    'home_main' => 'Homepage Main',
    'home_sidebar' => 'Homepage Sidebar',
    'header' => 'Header Area',
    'sidebar' => 'Sidebar',
    'footer' => 'Footer'
];

$widgets_by_location = [];
foreach ($widget_locations as $location => $label) {
    $widgets_by_location[$location] = $widgetManager->getWidgetsByLocation($location);
}

// Get available widget types
$widget_types = $widgetManager->getWidgetTypes();

include 'layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">Widget Management</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Widget Management</li>
            </ol>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#widgetModal">
            <i class="fas fa-plus me-2"></i>Add Widget
        </button>
    </div>

    <!-- Widget Areas -->
    <div class="row">
        <?php foreach ($widget_locations as $location => $label): ?>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-th-large me-2"></i><?= htmlspecialchars($label) ?>
                    </h5>
                    <span class="badge bg-secondary"><?= count($widgets_by_location[$location]) ?> widgets</span>
                </div>
                <div class="card-body">
                    <div class="widget-area" data-location="<?= $location ?>" style="min-height: 200px;">
                        <?php if (empty($widgets_by_location[$location])): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-puzzle-piece fa-3x mb-3"></i>
                                <p>No widgets in this area</p>
                                <small>Drag widgets here or click "Add Widget" to get started</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($widgets_by_location[$location] as $widget): ?>
                                <div class="widget-item card mb-2" data-widget-id="<?= $widget['id'] ?>">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1">
                                                    <i class="fas fa-grip-vertical text-muted me-2"></i>
                                                    <?= htmlspecialchars($widget['title']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    Type: <?= htmlspecialchars($widget['type_name']) ?>
                                                    <?php if (!$widget['is_active']): ?>
                                                        <span class="badge bg-warning ms-2">Inactive</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-edit-widget" 
                                                        data-widget-id="<?= $widget['id'] ?>" 
                                                        data-bs-toggle="tooltip" title="Edit Widget">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-preview-widget" 
                                                        data-widget-id="<?= $widget['id'] ?>" 
                                                        data-bs-toggle="tooltip" title="Preview Widget">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-widget" 
                                                        data-widget-id="<?= $widget['id'] ?>" 
                                                        data-bs-toggle="tooltip" title="Delete Widget">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Widget Modal -->
<div class="modal fade" id="widgetModal" tabindex="-1" aria-labelledby="widgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetModalLabel">Add Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="widgetForm" onsubmit="return false;">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="modalMessage" class="alert d-none" role="alert"></div>
                    
                    <input type="hidden" id="widget_id" name="widget_id">
                    <input type="hidden" id="form_action" name="action" value="create_widget">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="widget_title" class="form-label">Widget Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="widget_title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="widget_type" class="form-label">Widget Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="widget_type" name="widget_type_id" required>
                                <option value="">Select a widget type</option>
                                <!-- Static widget types for testing -->
                                <option value="1" data-template='{"content": "", "show_title": true}'>Text Widget - Display custom text content</option>
                                <option value="2" data-template='{"image_url": "", "alt_text": "", "caption": "", "link_url": ""}'>Image Widget - Display an image with optional caption</option>
                                <option value="3" data-template='{"post_id": "", "show_image": true, "show_author": true, "show_date": true, "show_excerpt": true, "show_full_content": false}'>Featured Post Widget - Display a single post with full content, image, author and date</option>
                                <option value="4" data-template='{"count": 5, "show_excerpt": true, "show_date": true, "show_author": true, "show_image": true}'>Recent Posts Widget - Display a list of recent posts with excerpts</option>
                                <option value="5" data-template='{"menu_location": "main", "show_icons": false}'>Navigation Menu - Display a navigation menu</option>
                                <option value="6" data-template='{"phone": "", "email": "", "address": "", "show_map": false}'>Contact Info - Display contact information</option>
                                <option value="7" data-template='{"image_url": "", "image_alt": "", "image_redirect_url": "", "text_content": "", "text_redirect_url": "", "external_image": false, "external_text": false, "show_title": true}'>Media Widget - Display image or text content with optional URL redirect</option>
                                <option value="11" data-template='{"image_url": "", "image_alt": "", "image_redirect_url": "", "text_content": "", "text_redirect_url": "", "external_image": false, "external_text": false, "show_title": true}'>Image & Hyperlink Text Widget - Display an image with URL redirect and hyperlink text below it</option>
                            </select>
                            <div class="form-text">
                                <small class="text-info">Using static widget types for testing purposes</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="widget_location" class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" id="widget_location" name="location" required>
                                <option value="">Select location</option>
                                <?php foreach ($widget_locations as $location => $label): ?>
                                <option value="<?= $location ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="widget_status" class="form-label">Status</label>
                            <select class="form-select" id="widget_status" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="widget_config" class="form-label">
                            Widget Configuration 
                            <small class="text-muted">(JSON format)</small>
                        </label>
                        <textarea class="form-control" id="widget_config" name="configuration" rows="4" placeholder="{}"><?= htmlspecialchars('{}') ?></textarea>
                        <div class="form-text">
                            Enter widget-specific configuration in JSON format. Leave empty for default settings.
                        </div>
                    </div>
                    
                    <!-- Dynamic configuration fields for Image & Hyperlink Text Widget -->
                    <div id="imageHyperlinkConfig" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Media & Content Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="config_image_url" class="form-label">Image URL</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="config_image_url" placeholder="Enter image URL or path" style="color: #000 !important; background-color: #fff !important;">
                                            <button type="button" class="btn btn-outline-secondary" id="browseMediaBtn">
                                                <i class="fas fa-folder-open me-1"></i>Browse
                                            </button>
                                        </div>
                                        <div class="form-text">Enter the full URL or relative path to the image</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="config_image_alt" class="form-label">Alt Text</label>
                                        <input type="text" class="form-control" id="config_image_alt" placeholder="Image description" style="color: #000 !important; background-color: #fff !important;">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="config_show_title">
                                            <label class="form-check-label" for="config_show_title">
                                                Show Title
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <small class="text-muted">Auto-hidden when content exists</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="config_image_redirect" class="form-label">Image Redirect URL</label>
                                        <input type="text" class="form-control" id="config_image_redirect" placeholder="Where should the image link to?" style="color: #000 !important; background-color: #fff !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="config_external_image">
                                            <label class="form-check-label" for="config_external_image">
                                                External Link
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="config_text_content" class="form-label">Hyperlink Text</label>
                                        <input type="text" class="form-control" id="config_text_content" placeholder="Enter the text to display" style="color: #000 !important; background-color: #fff !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="config_text_redirect" class="form-label">Text Redirect URL</label>
                                        <input type="text" class="form-control" id="config_text_redirect" placeholder="Where should the text link to?" style="color: #000 !important; background-color: #fff !important;">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="config_external_text">
                                            <label class="form-check-label" for="config_external_text">
                                                External Link
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="uploadImageBtn">
                                            <i class="fas fa-upload me-1"></i>Upload New Image
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" id="generateConfigBtn">
                                            <i class="fas fa-code me-1"></i>Generate JSON Config
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dynamic configuration fields for Text Widget -->
                    <div id="textWidgetConfig" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-font me-2"></i>Text Widget Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="config_text_content_simple" class="form-label">Text Content</label>
                                    <textarea class="form-control" id="config_text_content_simple" rows="4" placeholder="Enter your text content here..." style="color: #000 !important; background-color: #fff !important;"></textarea>
                                    <div class="form-text">
                                        <small class="text-muted">HTML tags are supported. Plain text will be styled with bold, centered formatting.</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="config_show_title_text">
                                            <label class="form-check-label" for="config_show_title_text">
                                                Show Title (Override)
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <small class="text-muted">Text widgets are title-free by default</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-sm btn-outline-info" id="generateTextConfigBtn">
                                            <i class="fas fa-code me-1"></i>Generate JSON Config
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="widget_css" class="form-label">Custom CSS Class</label>
                        <input type="text" class="form-control" id="widget_css" name="css_class" placeholder="custom-widget-class">
                        <div class="form-text">Optional CSS class for custom styling</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveWidgetBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="saveSpinner"></span>
                        <span id="saveButtonText">Save Widget</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye me-2"></i>Widget Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="previewContent" style="min-height: 300px;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="openNewTabBtn">
                    <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Media Browser Modal -->
<div class="modal fade" id="mediaBrowserModal" tabindex="-1" aria-labelledby="mediaBrowserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="mediaBrowserModalLabel">
                    <i class="fas fa-images me-2"></i>Media Library Browser
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" id="mediaSearchInput" placeholder="Search images...">
                            <button type="button" class="btn btn-outline-secondary" id="searchMediaBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-success" id="uploadNewImageBtn">
                            <i class="fas fa-upload me-2"></i>Upload New Image
                        </button>
                    </div>
                </div>
                
                <!-- Upload Section (initially hidden) -->
                <div id="uploadSection" class="mb-4 p-3 border rounded bg-light" style="display: none;">
                    <h6><i class="fas fa-upload me-2"></i>Upload New Image</h6>
                    <form id="imageUploadForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" class="form-control" id="imageFileInput" accept="image/*">
                                <div class="form-text">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary" id="startUploadBtn">
                                    <i class="fas fa-cloud-upload-alt me-1"></i>Upload
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancelUploadBtn">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        <div id="uploadProgress" class="mt-2" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Media Grid -->
                <div id="mediaGrid" class="row g-3">
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading media...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading images from media library...</p>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="mediaPagination" class="d-flex justify-content-center mt-4">
                    <!-- Pagination will be inserted here -->
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="me-auto">
                    <small class="text-muted">
                        <span id="selectedImageInfo">No image selected</span>
                    </small>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="selectImageBtn" disabled>
                    <i class="fas fa-check me-2"></i>Select Image
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable widget areas
    const widgetAreas = document.querySelectorAll('.widget-area');
    
    widgetAreas.forEach(area => {
        Sortable.create(area, {
            group: 'widgets',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateWidgetOrder();
            }
        });
    });
    
    // Modal and form elements
    const widgetModal = new bootstrap.Modal(document.getElementById('widgetModal'));
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const mediaBrowserModal = new bootstrap.Modal(document.getElementById('mediaBrowserModal'));
    const widgetForm = document.getElementById('widgetForm');
    const saveBtn = document.getElementById('saveWidgetBtn');
    const saveSpinner = document.getElementById('saveSpinner');
    const saveButtonText = document.getElementById('saveButtonText');
    
    // Track current preview widget for "Open in New Tab" functionality
    let currentPreviewWidgetId = null;
    let selectedImageData = null;
    
    // Show success/error messages in modal
    function showModalMessage(message, type) {
        const messageDiv = document.getElementById('modalMessage');
        messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
        messageDiv.textContent = message;
        messageDiv.classList.remove('d-none');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            messageDiv.classList.add('d-none');
        }, 3000);
    }
    
    // Toast notification system
    function showToast(message, type = 'info') {
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
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
    
    // Reset form and modal
    function resetWidgetForm() {
        widgetForm.reset();
        document.getElementById('form_action').value = 'create_widget';
        document.getElementById('widget_id').value = '';
        document.getElementById('widget_config').value = '{}';
        document.getElementById('widgetModalLabel').textContent = 'Add Widget';
        document.getElementById('saveButtonText').textContent = 'Save Widget';
        document.getElementById('modalMessage').classList.add('d-none');
        
        // Hide dynamic config sections
        document.getElementById('imageHyperlinkConfig').style.display = 'none';
        document.getElementById('textWidgetConfig').style.display = 'none';
        clearImageHyperlinkForm();
        clearTextWidgetForm();
    }
    
    // Clear Image & Hyperlink form fields
    function clearImageHyperlinkForm() {
        document.getElementById('config_image_url').value = '';
        document.getElementById('config_image_alt').value = '';
        document.getElementById('config_image_redirect').value = '';
        document.getElementById('config_text_content').value = '';
        document.getElementById('config_text_redirect').value = '';
        document.getElementById('config_external_image').checked = false;
        document.getElementById('config_external_text').checked = false;
        document.getElementById('config_show_title').checked = true;
    }
    
    // Populate Image & Hyperlink form from template/config
    function populateImageHyperlinkForm(template) {
        if (!template) return;
        
        try {
            const config = typeof template === 'string' ? JSON.parse(template) : template;
            
            document.getElementById('config_image_url').value = config.image_url || '';
            document.getElementById('config_image_alt').value = config.image_alt || '';
            document.getElementById('config_image_redirect').value = config.image_redirect_url || '';
            document.getElementById('config_text_content').value = config.text_content || '';
            document.getElementById('config_text_redirect').value = config.text_redirect_url || '';
            document.getElementById('config_external_image').checked = config.external_image || false;
            document.getElementById('config_external_text').checked = config.external_text || false;
            document.getElementById('config_show_title').checked = config.show_title !== undefined ? config.show_title : true;
        } catch (e) {
            console.error('Error parsing widget template:', e);
        }
    }
    
    // Generate JSON config from Image & Hyperlink form
    function generateImageHyperlinkConfig() {
        const config = {
            image_url: document.getElementById('config_image_url').value.trim(),
            image_alt: document.getElementById('config_image_alt').value.trim(),
            image_redirect_url: document.getElementById('config_image_redirect').value.trim(),
            text_content: document.getElementById('config_text_content').value.trim(),
            text_redirect_url: document.getElementById('config_text_redirect').value.trim(),
            external_image: document.getElementById('config_external_image').checked,
            external_text: document.getElementById('config_external_text').checked,
            show_title: document.getElementById('config_show_title').checked
        };
        
        return JSON.stringify(config, null, 2);
    }
    
    // Populate Text Widget form from template/config
    function populateTextWidgetForm(template) {
        console.log('populateTextWidgetForm called with:', template);
        if (!template) return;
        
        try {
            const config = typeof template === 'string' ? JSON.parse(template) : template;
            console.log('Parsed config:', config);
            
            const contentElement = document.getElementById('config_text_content_simple');
            const titleElement = document.getElementById('config_show_title_text');
            
            if (contentElement) {
                contentElement.value = config.content || '';
                console.log('Set content to:', config.content || '');
            } else {
                console.error('config_text_content_simple element not found');
            }
            
            if (titleElement) {
                titleElement.checked = config.show_title !== undefined ? config.show_title : false;
                console.log('Set show_title to:', config.show_title !== undefined ? config.show_title : false);
            } else {
                console.error('config_show_title_text element not found');
            }
        } catch (e) {
            console.error('Error parsing widget template:', e);
        }
    }
    
    // Generate JSON config from Text Widget form
    function generateTextWidgetConfig() {
        const config = {
            content: document.getElementById('config_text_content_simple').value.trim(),
            show_title: document.getElementById('config_show_title_text').checked
        };
        
        return JSON.stringify(config, null, 2);
    }
    
    // Clear Text Widget form fields
    function clearTextWidgetForm() {
        document.getElementById('config_text_content_simple').value = '';
        document.getElementById('config_show_title_text').checked = false;
    }
    
    // Add widget button
    document.querySelector('[data-bs-target="#widgetModal"]').addEventListener('click', function() {
        resetWidgetForm();
    });
    
    // Widget type change handler
    document.getElementById('widget_type').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const widgetTypeId = selectedOption.value;
        
        // Hide all dynamic config sections
        document.getElementById('imageHyperlinkConfig').style.display = 'none';
        document.getElementById('textWidgetConfig').style.display = 'none';
        
        if (selectedOption.value) {
            const template = selectedOption.dataset.template;
            const isEditMode = document.getElementById('form_action').value === 'update_widget';
            
            // Only set template if we're not in edit mode (to preserve existing data)
            if (!isEditMode) {
                document.getElementById('widget_config').value = template || '{}';
            }
            
            // Show specific configuration UI based on widget type
            if (widgetTypeId === '7' || widgetTypeId === '11') { // Media Widget or Image & Hyperlink Text Widget
                document.getElementById('imageHyperlinkConfig').style.display = 'block';
                // Only populate with template if not in edit mode (edit mode will populate separately)
                if (!isEditMode) {
                    populateImageHyperlinkForm(template);
                }
            } else if (widgetTypeId === '1') { // Text Widget
                document.getElementById('textWidgetConfig').style.display = 'block';
                // Only populate with template if not in edit mode (edit mode will populate separately)
                if (!isEditMode) {
                    populateTextWidgetForm(template);
                }
            }
        }
    });
    
    // Save widget button
    saveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        saveWidget();
    });
    
    // Edit widget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-widget')) {
            const widgetId = e.target.closest('.btn-edit-widget').dataset.widgetId;
            editWidget(widgetId);
        }
    });
    
    // Delete widget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-widget')) {
            const widgetId = e.target.closest('.btn-delete-widget').dataset.widgetId;
            if (confirm('Are you sure you want to delete this widget?')) {
                deleteWidget(widgetId);
            }
        }
    });
    
    // Preview widget buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-preview-widget')) {
            const widgetId = e.target.closest('.btn-preview-widget').dataset.widgetId;
            previewWidget(widgetId);
        }
    });
    
    // Open in New Tab button
    document.getElementById('openNewTabBtn').addEventListener('click', function() {
        if (currentPreviewWidgetId) {
            window.open('widget-preview.php?widget_id=' + currentPreviewWidgetId, '_blank');
        } else {
            showToast('No widget selected for preview', 'error');
        }
    });
    
    // Generate Config button for Image & Hyperlink widget
    document.getElementById('generateConfigBtn').addEventListener('click', function() {
        const widgetType = document.getElementById('widget_type').value;
        if (widgetType === '7') { // Image & Hyperlink Text Widget
            const config = generateImageHyperlinkConfig();
            document.getElementById('widget_config').value = config;
            showToast('Configuration generated successfully!', 'success');
        }
    });
    
    // Browse Media Library button
    document.getElementById('browseMediaBtn').addEventListener('click', function() {
        loadMediaLibrary();
        mediaBrowserModal.show();
    });
    
    // Upload New Image button  
    document.getElementById('uploadImageBtn').addEventListener('click', function() {
        showToast('Upload functionality coming soon!', 'info');
        // TODO: Implement direct upload functionality
    });
    
    // Generate Text Config button
    document.getElementById('generateTextConfigBtn').addEventListener('click', function() {
        const generatedConfig = generateTextWidgetConfig();
        document.getElementById('widget_config').value = generatedConfig;
        showToast('JSON configuration generated!', 'success');
    });
    
    // Generate Config button for Image & Hyperlink
    document.getElementById('generateConfigBtn').addEventListener('click', function() {
        const generatedConfig = generateImageHyperlinkConfig();
        document.getElementById('widget_config').value = generatedConfig;
        showToast('JSON configuration generated!', 'success');
    });
    
    // Save widget function
    function saveWidget() {
        // Validate form
        if (!widgetForm.checkValidity()) {
            widgetForm.reportValidity();
            return;
        }
        
        // Generate config from dynamic forms if visible
        const widgetType = document.getElementById('widget_type').value;
        if ((widgetType === '7' || widgetType === '11') && document.getElementById('imageHyperlinkConfig').style.display !== 'none') {
            // Media Widget or Image & Hyperlink widget
            const generatedConfig = generateImageHyperlinkConfig();
            document.getElementById('widget_config').value = generatedConfig;
        } else if (widgetType === '1' && document.getElementById('textWidgetConfig').style.display !== 'none') {
            // Text Widget
            const generatedConfig = generateTextWidgetConfig();
            document.getElementById('widget_config').value = generatedConfig;
        }
        
        // Show loading state
        saveSpinner.classList.remove('d-none');
        saveBtn.disabled = true;
        saveButtonText.textContent = 'Saving...';
        
        // Prepare form data
        const formData = new FormData(widgetForm);
        
        // Debug: Log form data
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        
        // Submit form
        fetch('widgets.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            return response.text(); // Get as text first to see raw response
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed response:', data);
                
                if (data.success) {
                    showToast('Widget saved successfully!', 'success');
                    setTimeout(() => {
                        widgetModal.hide();
                        location.reload();
                    }, 1500);
                } else {
                    const errorMsg = data.error || 'Unknown error occurred';
                    const debugInfo = data.columns ? ` Available columns: ${data.columns.join(', ')}` : '';
                    showToast(`Failed to save widget: ${errorMsg}${debugInfo}`, 'error');
                    console.error('Save failed:', data);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                showToast('Server returned invalid response: ' + text.substring(0, 100), 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showToast('Network error: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset loading state
            saveSpinner.classList.add('d-none');
            saveBtn.disabled = false;
            saveButtonText.textContent = document.getElementById('form_action').value === 'create_widget' ? 'Save Widget' : 'Update Widget';
        });
    }
    
    // Edit widget function
    function editWidget(widgetId) {
        fetch('widgets.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=get_widget&widget_id=' + widgetId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.widget) {
                const widget = data.widget;
                document.getElementById('widget_id').value = widget.id;
                document.getElementById('form_action').value = 'update_widget';
                document.getElementById('widget_title').value = widget.title || '';
                document.getElementById('widget_type').value = widget.widget_type_id || '';
                document.getElementById('widget_location').value = widget.position || ''; // Use 'position' from DB
                document.getElementById('widget_status').value = widget.is_active || '1';
                document.getElementById('widget_config').value = widget.configuration || widget.config || '{}';
                document.getElementById('widget_css').value = widget.css_class || '';
                
                // Trigger the widget type change event to show correct configuration UI
                const widgetTypeSelect = document.getElementById('widget_type');
                const changeEvent = new Event('change');
                widgetTypeSelect.dispatchEvent(changeEvent);
                
                // Wait a moment for the UI to update, then populate with existing data
                setTimeout(() => {
                    const config = widget.configuration || widget.config || '{}';
                    console.log('Widget type ID:', widget.widget_type_id, 'Config:', config);
                    
                    if (widget.widget_type_id == '7' || widget.widget_type_id == '11') {
                        // Media Widget and Image & Hyperlink widget
                        console.log('Populating Media/Image & Hyperlink config');
                        populateImageHyperlinkForm(config);
                    } else if (widget.widget_type_id == '1') {
                        // Text Widget
                        console.log('Populating Text Widget config');
                        populateTextWidgetForm(config);
                    }
                }, 100);
                
                document.getElementById('widgetModalLabel').textContent = 'Edit Widget';
                document.getElementById('saveButtonText').textContent = 'Update Widget';
                widgetModal.show();
            } else {
                showToast('Failed to load widget data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading widget data', 'error');
        });
    }
    
    // Delete widget function
    function deleteWidget(widgetId) {
        fetch('widgets.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete_widget&widget_id=' + widgetId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Widget deleted successfully', 'success');
                location.reload();
            } else {
                showToast('Failed to delete widget', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting widget', 'error');
        });
    }
    
    // Update widget order function
    function updateWidgetOrder() {
        const widgetOrders = {};
        
        widgetAreas.forEach(area => {
            const location = area.dataset.location;
            const widgets = area.querySelectorAll('.widget-item');
            
            widgets.forEach((widget, index) => {
                const widgetId = widget.dataset.widgetId;
                widgetOrders[widgetId] = {
                    order: index + 1,
                    location: location
                };
            });
        });
        
        console.log('Updating widget order:', widgetOrders);
        
        fetch('widgets.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=update_widget_order&widget_orders=' + encodeURIComponent(JSON.stringify(widgetOrders))
        })
        .then(response => response.json())
        .then(data => {
            console.log('Widget order update response:', data);
            if (data.success) {
                console.log('Widget order updated successfully');
                showToast('Widget order updated successfully', 'success');
            } else {
                console.error('Widget order update failed:', data.error);
                showToast('Failed to update widget order: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error updating widget order:', error);
            showToast('Error updating widget order: ' + error.message, 'error');
        });
    }
    
    // Preview widget function
    function previewWidget(widgetId) {
        currentPreviewWidgetId = widgetId;
        
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading widget preview...</span>
                </div>
                <p class="mt-2 text-muted">Loading widget preview...</p>
            </div>
        `;
        
        previewModal.show();
        
        // Load widget content via AJAX
        fetch('widget-content.php?widget_id=' + widgetId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                // Update modal title with widget title
                document.querySelector('#previewModal .modal-title').innerHTML = 
                    '<i class="fas fa-eye me-2"></i>Preview: ' + (data.widget.title || 'Untitled Widget');
            } else {
                document.getElementById('previewContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Preview Error:</strong> ${data.error || 'Unknown error occurred'}
                    </div>`;
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            document.getElementById('previewContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Network Error:</strong> Failed to load widget preview. ${error.message}
                </div>`;
        });
    }
    
    // Media browser functionality
    function loadMediaLibrary(search = '') {
        const mediaGrid = document.getElementById('mediaGrid');
        
        // Show loading state
        mediaGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading media...</span>
                </div>
                <p class="mt-2 text-muted">Loading images from media library...</p>
            </div>
        `;
        
        // Load actual media files from server
        fetch('media-browser.php?search=' + encodeURIComponent(search))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.images && data.images.length > 0) {
                let gridHTML = '';
                
                data.images.forEach((image, index) => {
                    gridHTML += `
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card media-item" data-image-path="${image.path}" data-image-name="${image.name}">
                                <div class="card-img-top-container" style="height: 200px; overflow: hidden; position: relative;">
                                    <img src="${image.path}" class="card-img-top" alt="${image.name}" 
                                         style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2Yzc1N2QiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+'">
                                    <div class="selection-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(13, 110, 253, 0.8); display: none; align-items: center; justify-content: center;">
                                        <i class="fas fa-check-circle text-white fa-3x"></i>
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1 text-truncate" title="${image.name}">${image.name}</h6>
                                    <small class="text-muted">${image.size}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                mediaGrid.innerHTML = gridHTML;
            } else {
                // Fallback to sample images if no media found or media-browser.php doesn't exist
                loadSampleMedia();
            }
            
            // Add click handlers to media items
            document.querySelectorAll('.media-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectMediaItem(this);
                });
            });
        })
        .catch(error => {
            console.error('Error loading media library:', error);
            // Fallback to sample images
            loadSampleMedia();
        });
    }
    
    function loadSampleMedia() {
        const mediaGrid = document.getElementById('mediaGrid');
        
        // Sample images for demonstration
        const sampleImages = [
            { path: '/campus_website2/public/img/Cagayan State University - Logo.png', name: 'CSU Logo', size: '45KB' },
            { path: '/campus_website2/public/img/izn2025 (1).jpg', name: 'IZN 2025', size: '234KB' },
            { path: '/campus_website2/public/img/level3aacup (1).jpg', name: 'Level 3 AACUP', size: '156KB' },
            { path: '/campus_website2/public/img/MTLEMARCH2025 (1).jpg', name: 'MTLE March 2025', size: '198KB' }
        ];
        
        let gridHTML = '';
        
        sampleImages.forEach((image, index) => {
            gridHTML += `
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card media-item" data-image-path="${image.path}" data-image-name="${image.name}">
                        <div class="card-img-top-container" style="height: 200px; overflow: hidden; position: relative;">
                            <img src="${image.path}" class="card-img-top" alt="${image.name}" 
                                 style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2Yzc1N2QiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+'">
                            <div class="selection-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(13, 110, 253, 0.8); display: none; align-items: center; justify-content: center;">
                                <i class="fas fa-check-circle text-white fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1 text-truncate" title="${image.name}">${image.name}</h6>
                            <small class="text-muted">${image.size}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (gridHTML === '') {
            gridHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No images found in the media library</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadNewImageBtn').click()">
                        <i class="fas fa-upload me-2"></i>Upload Your First Image
                    </button>
                </div>
            `;
        }
        
        mediaGrid.innerHTML = gridHTML;
        
        // Add click handlers to media items
        document.querySelectorAll('.media-item').forEach(item => {
            item.addEventListener('click', function() {
                selectMediaItem(this);
            });
        });
    }
    
    function selectMediaItem(element) {
        // Remove previous selection
        document.querySelectorAll('.media-item').forEach(item => {
            item.classList.remove('border-primary', 'bg-light');
            const overlay = item.querySelector('.selection-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        });
        
        // Mark current selection
        element.classList.add('border-primary', 'bg-light');
        const overlay = element.querySelector('.selection-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
        
        // Store selected image data
        selectedImageData = {
            path: element.dataset.imagePath,
            name: element.dataset.imageName
        };
        
        // Update UI
        document.getElementById('selectedImageInfo').textContent = `Selected: ${selectedImageData.name}`;
        document.getElementById('selectImageBtn').disabled = false;
        
        // Visual feedback
        showToast(`Image "${selectedImageData.name}" selected. Click "Select Image" to use it.`, 'info');
    }
    
    // Media browser modal event handlers
    document.getElementById('uploadNewImageBtn').addEventListener('click', function() {
        const uploadSection = document.getElementById('uploadSection');
        uploadSection.style.display = uploadSection.style.display === 'none' ? 'block' : 'none';
    });
    
    document.getElementById('cancelUploadBtn').addEventListener('click', function() {
        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('imageFileInput').value = '';
        document.getElementById('uploadProgress').style.display = 'none';
    });
    
    // Start upload button handler
    document.getElementById('startUploadBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('imageFileInput');
        const file = fileInput.files[0];
        
        if (!file) {
            showToast('Please select an image file first', 'error');
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const fileType = file.type.toLowerCase();
        if (!allowedTypes.includes(fileType)) {
            showToast('Invalid file type. Please select JPG, PNG, GIF, or WebP images only.', 'error');
            return;
        }
        
        // Validate file size (5MB max)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            showToast('File size too large. Maximum size is 5MB.', 'error');
            return;
        }
        
        // Show upload progress
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = uploadProgress.querySelector('.progress-bar');
        uploadProgress.style.display = 'block';
        progressBar.style.width = '0%';
        
        // Disable upload button during upload
        const startUploadBtn = document.getElementById('startUploadBtn');
        startUploadBtn.disabled = true;
        startUploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';
        
        // Create form data
        const formData = new FormData();
        formData.append('image', file);
        
        // Upload the file
        fetch('upload-image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Upload failed with status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            progressBar.style.width = '100%';
            
            if (data.success) {
                // Success - add the uploaded image to the selected image
                selectedImageData = {
                    path: data.file_path,
                    name: data.file_name
                };
                
                // Update UI to show the uploaded image is selected
                document.getElementById('selectedImageInfo').textContent = `Selected: ${selectedImageData.name} (newly uploaded)`;
                document.getElementById('selectImageBtn').disabled = false;
                
                // Hide upload section
                document.getElementById('uploadSection').style.display = 'none';
                
                // Refresh media library to show the new image
                loadMediaLibrary();
                
                showToast('Image uploaded successfully! Click "Select Image" to use it.', 'success');
            } else {
                throw new Error(data.error || 'Upload failed');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showToast('Upload failed: ' + error.message, 'error');
            progressBar.style.width = '0%';
        })
        .finally(() => {
            // Reset upload button
            startUploadBtn.disabled = false;
            startUploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Upload';
            
            // Hide progress after a delay
            setTimeout(() => {
                uploadProgress.style.display = 'none';
            }, 2000);
        });
    });
    
    document.getElementById('selectImageBtn').addEventListener('click', function() {
        if (selectedImageData) {
            document.getElementById('config_image_url').value = selectedImageData.path;
            document.getElementById('config_image_alt').value = selectedImageData.name;
            mediaBrowserModal.hide();
            showToast('Image selected successfully!', 'success');
        }
    });
    
    document.getElementById('searchMediaBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('mediaSearchInput').value;
        loadMediaLibrary(searchTerm);
    });
    
    // Reset media browser when modal is hidden
    document.getElementById('mediaBrowserModal').addEventListener('hidden.bs.modal', function() {
        selectedImageData = null;
        document.getElementById('selectedImageInfo').textContent = 'No image selected';
        document.getElementById('selectImageBtn').disabled = true;
        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('imageFileInput').value = '';
        document.getElementById('uploadProgress').style.display = 'none';
        
        // Reset all media item selections
        document.querySelectorAll('.media-item').forEach(item => {
            item.classList.remove('border-primary', 'bg-light');
            const overlay = item.querySelector('.selection-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        });
        
        // Reset upload button if it was in uploading state
        const startUploadBtn = document.getElementById('startUploadBtn');
        if (startUploadBtn) {
            startUploadBtn.disabled = false;
            startUploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Upload';
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.widget-item {
    cursor: move;
    transition: all 0.3s ease;
}

.widget-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: rotate(5deg);
}

.widget-area {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.widget-area:hover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.widget-area.sortable-over {
    border-color: #198754;
    background-color: rgba(25, 135, 84, 0.1);
}

/* Preview Modal Styles */
#previewModal .modal-content {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

#previewModal .modal-header {
    background: linear-gradient(135deg, #069952 0%, #28a745 100%);
    border-bottom: none;
}

#previewModal .modal-title {
    color: white !important;
}

.widget-preview-container .widget-meta {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.widget-preview-content {
    background: #fdfdfd !important;
    border: 2px solid #e3f2fd !important;
    position: relative;
}

.widget-preview-content::before {
    content: "PREVIEW";
    position: absolute;
    top: 5px;
    right: 10px;
    background: #069952;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 0.7rem;
    font-weight: bold;
    z-index: 10;
}

.widget-preview-container .widget {
    margin: 0;
    border: none;
    background: transparent;
    padding: 15px;
}

.widget-preview-container .widget-title {
    color: #069952 !important;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

/* Dynamic Configuration Styles */
#imageHyperlinkConfig .card {
    border: 1px solid #e3f2fd;
    background: #fafbff;
}

#imageHyperlinkConfig .card-header {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-bottom: 1px solid #90caf9;
}

#imageHyperlinkConfig .card-header h6 {
    color: #1565c0;
    font-weight: 600;
}

#imageHyperlinkConfig .form-control:focus {
    border-color: #1976d2;
    box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
}

#imageHyperlinkConfig .form-check-input:checked {
    background-color: #1976d2;
    border-color: #1976d2;
}

#imageHyperlinkConfig .btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

#imageHyperlinkConfig .btn-outline-info:hover {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

#imageHyperlinkConfig hr {
    border-color: #90caf9;
    opacity: 0.5;
}

/* Enhanced form styling */
.form-label {
    font-weight: 500;
    color: #495057;
}

.form-text {
    font-size: 0.8rem;
}

.card-body .row.mb-3:last-child {
    margin-bottom: 0 !important;
}

/* Media Browser Styles */
#mediaBrowserModal .modal-content {
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

#mediaBrowserModal .modal-header {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    border-bottom: none;
}

.media-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.media-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #dee2e6;
}

.media-item.border-primary {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.media-item img {
    transition: transform 0.3s ease;
}

.media-item:hover img {
    transform: scale(1.05);
}

.selection-overlay {
    transition: all 0.3s ease;
}

.media-item:hover .selection-overlay {
    display: flex !important;
    opacity: 0.3;
}

.media-item.border-primary .selection-overlay {
    display: flex !important;
    opacity: 1;
}

#uploadSection {
    border: 2px dashed #28a745;
    background: rgba(40, 167, 69, 0.05);
}

#uploadProgress .progress {
    height: 8px;
}

.media-item .card-title {
    font-size: 0.9rem;
    font-weight: 500;
}

/* Enhanced input group styling */
.input-group .btn {
    border-left: none;
}

.input-group .form-control:focus + .btn {
    border-color: #86b7fe;
}

/* Modal footer enhancements */
#mediaBrowserModal .modal-footer {
    border-top: 1px solid #dee2e6;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

#mediaBrowserModal .modal-footer .btn {
    min-width: 120px;
}

#selectedImageInfo {
    color: #495057;
    font-weight: 500;
}

/* Upload section styling */
#uploadSection.show {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php include 'layouts/footer-new.php'; ?>
