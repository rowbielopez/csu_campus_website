<?php
// Set JSON header first to ensure proper response
header('Content-Type: application/json');

try {
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
        echo json_encode(['success' => false, 'error' => 'You do not have permission to manage widgets.']);
        exit;
    }

    if (!isset($_GET['widget_id']) || empty($_GET['widget_id'])) {
        echo json_encode(['success' => false, 'error' => 'Widget ID not provided']);
        exit;
    }

    $widgetManager = new WidgetManagerFlexible();
    $widget = $widgetManager->getWidget($_GET['widget_id']);

    if (!$widget) {
        echo json_encode(['success' => false, 'error' => 'Widget not found']);
        exit;
    }

    // Generate widget HTML
    $widgetHtml = $widgetManager->renderWidget($widget);
    
    // Create a clean preview with metadata
    $config = json_decode($widget['config'] ?? '{}', true);
    
    $previewHtml = '
    <div class="widget-preview-container p-3">
        <div class="widget-meta mb-3 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>ID:</strong> ' . htmlspecialchars($widget['id']) . '<br>
                        <strong>Type:</strong> ' . htmlspecialchars($widget['type_name'] ?? 'Unknown') . '<br>
                        <strong>Position:</strong> ' . htmlspecialchars($widget['position']) . '
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>Status:</strong> ' . ($widget['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-warning">Inactive</span>') . '<br>
                        <strong>CSS Class:</strong> ' . htmlspecialchars($widget['css_class'] ?: 'None') . '<br>
                        <strong>Sort Order:</strong> ' . htmlspecialchars($widget['sort_order'] ?: '1') . '
                    </small>
                </div>
            </div>
        </div>
        
        <div class="widget-preview-content p-3 border rounded" style="background: #fdfdfd; min-height: 200px;">
            ' . $widgetHtml . '
        </div>
        
        <div class="widget-config mt-3">
            <details>
                <summary class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-code me-1"></i> View Configuration
                </summary>
                <pre class="bg-light p-2 rounded mt-2 small">' . htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT)) . '</pre>
            </details>
        </div>
    </div>';

    echo json_encode([
        'success' => true, 
        'html' => $previewHtml,
        'widget' => $widget
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'html' => '<div class="alert alert-danger p-3"><i class="fas fa-exclamation-triangle me-2"></i><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>'
    ]);
}
?>
