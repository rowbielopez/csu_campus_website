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
        'message' => 'You do not have permission to view widget previews.'
    ];
    header('Location: ../index.php');
    exit;
}

if (!isset($_GET['widget_id'])) {
    echo '<div class="alert alert-danger m-3">Widget ID not provided</div>';
    exit;
}

$widgetManager = new WidgetManagerFlexible();
$widget = $widgetManager->getWidget($_GET['widget_id']);

if (!$widget) {
    echo '<div class="alert alert-danger m-3">Widget not found</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --campus-primary: #069952;
            --campus-secondary: #28a745;
            --campus-accent: #ffc107;
        }
        
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .preview-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: var(--campus-primary);
            border-color: var(--campus-primary);
        }
        
        .btn-primary:hover {
            background-color: rgba(6, 153, 82, 0.8);
            border-color: rgba(6, 153, 82, 0.8);
        }
        
        .bg-gradient-primary-to-secondary {
            background: linear-gradient(135deg, var(--campus-primary) 0%, var(--campus-secondary) 100%);
        }
        
        .text-primary {
            color: var(--campus-primary) !important;
        }
        
        .badge.bg-primary {
            background-color: var(--campus-primary) !important;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .widget {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
        }
        
        .widget-title {
            color: var(--campus-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }
        
        .widget-content {
            line-height: 1.6;
        }
        
        .widget-content img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .image-caption {
            font-style: italic;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 8px;
        }
        
        .widget-meta {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.875rem;
            border-left: 4px solid var(--campus-primary);
        }
        
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .debug-info summary {
            cursor: pointer;
            font-weight: 600;
            color: var(--campus-primary);
        }
        
        .debug-info pre {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.875rem;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="preview-container">
    <div class="widget-meta">
        <div class="row">
            <div class="col-md-6">
                <strong>Widget ID:</strong> <?= htmlspecialchars($widget['id']) ?><br>
                <strong>Title:</strong> <?= htmlspecialchars($widget['title']) ?><br>
                <strong>Type:</strong> <?= htmlspecialchars($widget['type_name'] ?? 'Unknown') ?>
            </div>
            <div class="col-md-6">
                <strong>Position:</strong> <?= htmlspecialchars($widget['position']) ?><br>
                <strong>Status:</strong> 
                <?php if ($widget['is_active']): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-warning">Inactive</span>
                <?php endif; ?><br>
                <strong>CSS Class:</strong> <?= htmlspecialchars($widget['css_class'] ?: 'None') ?>
            </div>
        </div>
    </div>
    
    <h5 class="text-primary mb-3">
        <i class="fas fa-eye me-2"></i>Widget Preview
    </h5>
    
    <div class="border rounded p-3" style="background: #fdfdfd;">
        <?php
        try {
            echo $widgetManager->renderWidget($widget);
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">';
            echo '<h6><i class="fas fa-exclamation-triangle me-2"></i>Preview Error</h6>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="debug-info">
        <details>
            <summary><i class="fas fa-code me-2"></i>Debug Information</summary>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Widget Data:</h6>
                    <pre><?= htmlspecialchars(json_encode($widget, JSON_PRETTY_PRINT)) ?></pre>
                </div>
                <div class="col-md-6">
                    <h6>Configuration:</h6>
                    <pre><?php
                    $config = json_decode($widget['config'] ?? '{}', true);
                    echo htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT));
                    ?></pre>
                </div>
            </div>
        </details>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
