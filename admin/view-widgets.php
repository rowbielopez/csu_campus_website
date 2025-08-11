<?php
// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/utilities.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/classes/WidgetManagerFlexible.php';

// Check permissions - only campus admins and super admins can view widgets
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to view widgets.'
    ];
    header('Location: ../index.php');
    exit;
}

$pageTitle = "View Widgets";
$pageDescription = "Preview how widgets appear on the frontend";

// Initialize WidgetManager
$widgetManager = new WidgetManagerFlexible();

// Get all active widgets grouped by position
$commonPositions = ['sidebar', 'header', 'footer', 'content_top', 'content_bottom', 'homepage'];
$widgets = [];
$widgetsByPosition = [];

foreach ($commonPositions as $position) {
    $positionWidgets = $widgetManager->getWidgetsByLocation($position);
    if (!empty($positionWidgets)) {
        $widgetsByPosition[$position] = $positionWidgets;
        $widgets = array_merge($widgets, $positionWidgets);
    }
}

// Also check for any custom positions by querying the database directly
try {
    $db = Database::getInstance();
    $campus_id = current_campus_id();
    
    $sql = "SELECT DISTINCT position FROM campus_widgets WHERE is_active = 1";
    $params = [];
    
    if ($campus_id) {
        $sql .= " AND campus_id = ?";
        $params[] = $campus_id;
    }
    
    $customPositions = $db->fetchAll($sql, $params);
    
    foreach ($customPositions as $pos) {
        $position = $pos['position'];
        if (!in_array($position, $commonPositions)) {
            $positionWidgets = $widgetManager->getWidgetsByLocation($position);
            if (!empty($positionWidgets)) {
                $widgetsByPosition[$position] = $positionWidgets;
                $widgets = array_merge($widgets, $positionWidgets);
            }
        }
    }
} catch (Exception $e) {
    error_log("Error getting custom widget positions: " . $e->getMessage());
}

include __DIR__ . '/layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4">
                <div class="me-4 mb-3 mb-sm-0">
                    <h1 class="mb-0"><?php echo $pageTitle; ?></h1>
                    <div class="small">
                        <span class="fw-500 text-primary"><?php echo date('l'); ?></span>
                        · <?php echo date('F j, Y'); ?> · <?php echo date('g:i A'); ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo ADMIN_ROOT; ?>/widgets.php" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-1"></i>
                        Manage Widgets
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($widgetsByPosition)): ?>
        <!-- No Widgets Message -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-puzzle-piece fa-3x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No Active Widgets Found</h4>
                        <p class="text-muted mb-4">There are currently no active widgets to display. You can create and activate widgets from the Widget Management page.</p>
                        <a href="<?php echo ADMIN_ROOT; ?>/widgets.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Create Widget
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Widgets Preview -->
        <div class="row">
            <?php foreach ($widgetsByPosition as $position => $positionWidgets): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <h6 class="mb-0"><?php echo ucfirst(str_replace('_', ' ', $position)); ?> Position</h6>
                                <span class="badge bg-secondary ms-auto"><?php echo count($positionWidgets); ?> widget(s)</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php foreach ($positionWidgets as $widget): ?>
                                <div class="widget-preview mb-3 p-3 border rounded">
                                    <div class="widget-header d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($widget['title']); ?></h6>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($widget['type_name'] ?? 'Unknown'); ?></span>
                                    </div>
                                    <div class="widget-content">
                                        <?php
                                        // Render widget content based on type
                                        $config = json_decode($widget['config'] ?? '{}', true);
                                        
                                        switch ($widget['type_name']) {
                                            case 'HTML':
                                                echo $config['content'] ?? '<p class="text-muted">No content configured</p>';
                                                break;
                                                
                                            case 'Text':
                                                echo '<p>' . nl2br(htmlspecialchars($config['text'] ?? 'No text configured')) . '</p>';
                                                break;
                                                
                                            case 'Menu':
                                                if (!empty($config['menu_items'])) {
                                                    echo '<ul class="list-unstyled mb-0">';
                                                    foreach ($config['menu_items'] as $item) {
                                                        echo '<li><a href="' . htmlspecialchars($item['url'] ?? '#') . '">' . htmlspecialchars($item['title'] ?? 'Untitled') . '</a></li>';
                                                    }
                                                    echo '</ul>';
                                                } else {
                                                    echo '<p class="text-muted">No menu items configured</p>';
                                                }
                                                break;
                                                
                                            case 'Recent Posts':
                                                $limit = (int)($config['limit'] ?? 5);
                                                echo '<div class="text-muted small">Latest ' . $limit . ' posts would appear here</div>';
                                                echo '<ul class="list-unstyled mb-0 mt-2">';
                                                for ($i = 1; $i <= min($limit, 3); $i++) {
                                                    echo '<li class="mb-1"><a href="#" class="text-decoration-none">Sample Post Title ' . $i . '</a></li>';
                                                }
                                                echo '</ul>';
                                                break;
                                                
                                            case 'Social Links':
                                                if (!empty($config['social_links'])) {
                                                    echo '<div class="d-flex gap-2">';
                                                    foreach ($config['social_links'] as $link) {
                                                        $platform = $link['platform'] ?? 'link';
                                                        $iconClass = 'fab fa-' . strtolower($platform);
                                                        echo '<a href="' . htmlspecialchars($link['url'] ?? '#') . '" class="btn btn-sm btn-outline-primary" target="_blank">';
                                                        echo '<i class="' . $iconClass . '"></i>';
                                                        echo '</a>';
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<p class="text-muted">No social links configured</p>';
                                                }
                                                break;
                                                
                                            default:
                                                echo '<p class="text-muted">Widget preview not available for this type</p>';
                                                break;
                                        }
                                        ?>
                                    </div>
                                    <?php if (!empty($widget['css_class'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">CSS Class: <code><?php echo htmlspecialchars($widget['css_class']); ?></code></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Widget Statistics -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Widget Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="h4 text-primary"><?php echo array_sum(array_map('count', $widgetsByPosition)); ?></div>
                                <div class="small text-muted">Total Active Widgets</div>
                            </div>
                            <div class="col-md-3">
                                <div class="h4 text-success"><?php echo count($widgetsByPosition); ?></div>
                                <div class="small text-muted">Widget Positions</div>
                            </div>
                            <div class="col-md-3">
                                <div class="h4 text-info">
                                    <?php 
                                    $types = array_unique(array_column($widgets, 'type_name'));
                                    echo count($types);
                                    ?>
                                </div>
                                <div class="small text-muted">Widget Types Used</div>
                            </div>
                            <div class="col-md-3">
                                <div class="h4 text-warning">
                                    <?php echo get_current_campus()['name']; ?>
                                </div>
                                <div class="small text-muted">Campus Context</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.widget-preview {
    background-color: #f8f9fa;
    transition: all 0.2s ease-in-out;
}

.widget-preview:hover {
    background-color: #e9ecef;
    transform: translateY(-1px);
}

.widget-content {
    font-size: 0.9rem;
}

.widget-content ul {
    margin-bottom: 0;
}

.widget-content a {
    color: #6c757d;
    text-decoration: none;
}

.widget-content a:hover {
    color: #495057;
    text-decoration: underline;
}
</style>

<?php include __DIR__ . '/layouts/footer-new.php'; ?>
