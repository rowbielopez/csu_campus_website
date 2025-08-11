<?php
/**
 * Image & Hyperlink Text Widget Template
 * 
 * This widget displays an image with URL redirect and hyperlink text below it.
 * Both image and text can have separate redirect URLs.
 * Widget title is hidden when image or text content is present to avoid redundancy.
 */

// Ensure widget data is available
if (!isset($widget) || !isset($config)) {
    return;
}

// Parse widget configuration
$widgetConfig = is_string($config) ? json_decode($config, true) : $config;
if (!$widgetConfig) {
    $widgetConfig = [];
}

// Extract configuration values with defaults
$imageUrl = $widgetConfig['image_url'] ?? '';
$imageAlt = $widgetConfig['image_alt'] ?? 'Widget Image';
$imageRedirectUrl = $widgetConfig['image_redirect_url'] ?? '';
$textContent = $widgetConfig['text_content'] ?? '';
$textRedirectUrl = $widgetConfig['text_redirect_url'] ?? '';
$externalImage = $widgetConfig['external_image'] ?? false;
$externalText = $widgetConfig['external_text'] ?? false;
$showTitle = $widgetConfig['show_title'] ?? true;

// Hide title if image or text content is present to avoid redundancy
$hasContent = !empty($imageUrl) || !empty($textContent);
$displayTitle = $showTitle && !$hasContent && !empty($widget['title']);

// Helper function to determine if URL is external (avoid redeclaration)
if (!function_exists('isExternalUrl')) {
    function isExternalUrl($url) {
        if (empty($url)) return false;
        return (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '//') === 0);
    }
}

// Determine target attributes
$imageTarget = ($externalImage || isExternalUrl($imageRedirectUrl)) ? ' target="_blank" rel="noopener noreferrer"' : '';
$textTarget = ($externalText || isExternalUrl($textRedirectUrl)) ? ' target="_blank" rel="noopener noreferrer"' : '';

// Generate widget CSS class
$cssClass = 'image-hyperlink-widget';
if (!empty($widget['css_class'])) {
    $cssClass .= ' ' . htmlspecialchars($widget['css_class']);
}
?>

<style>
.image-hyperlink-widget {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 20px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.image-hyperlink-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.image-hyperlink-widget .widget-title-header {
    width: 100%;
    margin-bottom: 20px;
    text-align: center;
}

.image-hyperlink-widget .widget-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    margin: 0;
    padding: 0 0 10px 0;
    border-bottom: 2px solid #e9ecef;
}

.image-hyperlink-widget .widget-image-container {
    width: 100%;
    margin-bottom: 15px;
    overflow: hidden;
    border-radius: 8px;
}

.image-hyperlink-widget .widget-image {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: cover;
    border-radius: 8px;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.image-hyperlink-widget .widget-image:hover {
    transform: scale(1.05);
}

.image-hyperlink-widget .widget-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    padding: 8px 16px;
    border-radius: 6px;
    display: inline-block;
    background: transparent;
}

.image-hyperlink-widget .widget-text:hover {
    color: #0066cc;
    text-decoration: underline;
    background: rgba(0, 102, 204, 0.05);
    transform: translateY(-1px);
}

.image-hyperlink-widget .widget-text:focus {
    outline: 2px solid #0066cc;
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .image-hyperlink-widget {
        padding: 15px;
    }
    
    .image-hyperlink-widget .widget-image {
        max-height: 200px;
    }
    
    .image-hyperlink-widget .widget-text {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .image-hyperlink-widget {
        padding: 12px;
        border-radius: 8px;
    }
    
    .image-hyperlink-widget .widget-image-container {
        margin-bottom: 12px;
    }
    
    .image-hyperlink-widget .widget-image {
        max-height: 150px;
        border-radius: 6px;
    }
    
    .image-hyperlink-widget .widget-text {
        font-size: 0.95rem;
        padding: 6px 12px;
    }
}

/* Loading state */
.image-hyperlink-widget .widget-image.loading {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-hyperlink-widget .widget-image.loading::before {
    content: "Loading image...";
    color: #6c757d;
    font-size: 0.9rem;
}

/* Error state */
.image-hyperlink-widget .error-message {
    color: #dc3545;
    font-size: 0.9rem;
    margin: 10px 0;
    padding: 8px 12px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
}

/* Empty state */
.image-hyperlink-widget .empty-state {
    color: #6c757d;
    font-style: italic;
    padding: 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}
</style>

<div class="<?= htmlspecialchars($cssClass) ?>">
    <?php if ($displayTitle): ?>
        <div class="widget-title-header">
            <h3 class="widget-title"><?= htmlspecialchars($widget['title']) ?></h3>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($imageUrl)): ?>
        <div class="widget-image-container">
            <?php if (!empty($imageRedirectUrl)): ?>
                <a href="<?= htmlspecialchars($imageRedirectUrl) ?>"<?= $imageTarget ?> aria-label="<?= htmlspecialchars($imageAlt) ?>">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="<?= htmlspecialchars($imageAlt) ?>" 
                         class="widget-image"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="error-message" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Failed to load image
                    </div>
                </a>
            <?php else: ?>
                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                     alt="<?= htmlspecialchars($imageAlt) ?>" 
                     class="widget-image"
                     loading="lazy"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="error-message" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Failed to load image
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($textContent)): ?>
        <?php if (!empty($textRedirectUrl)): ?>
            <a href="<?= htmlspecialchars($textRedirectUrl) ?>"<?= $textTarget ?> class="widget-text">
                <?= htmlspecialchars($textContent) ?>
            </a>
        <?php else: ?>
            <div class="widget-text" style="cursor: default;">
                <?= htmlspecialchars($textContent) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($imageUrl) && empty($textContent)): ?>
        <div class="empty-state">
            <i class="fas fa-image me-2"></i>
            No content configured for this widget. Please edit the widget to add an image and text.
        </div>
    <?php endif; ?>
</div>

<?php
// Widget debug information (only shown to admins in debug mode)
if (defined('WIDGET_DEBUG') && WIDGET_DEBUG && function_exists('is_super_admin') && function_exists('is_campus_admin') && (is_super_admin() || is_campus_admin())):
?>
<div class="widget-debug mt-2 p-2 bg-light border rounded" style="font-size: 0.8rem;">
    <strong>Debug Info:</strong>
    <ul class="mb-0 mt-1">
        <li>Widget ID: <?= $widget['id'] ?? 'N/A' ?></li>
        <li>Image URL: <?= $imageUrl ?: 'Not set' ?></li>
        <li>Image Redirect: <?= $imageRedirectUrl ?: 'None' ?></li>
        <li>Text: <?= $textContent ?: 'Not set' ?></li>
        <li>Text Redirect: <?= $textRedirectUrl ?: 'None' ?></li>
        <li>External Image: <?= $externalImage ? 'Yes' : 'No' ?></li>
        <li>External Text: <?= $externalText ? 'Yes' : 'No' ?></li>
    </ul>
</div>
<?php endif; ?>
