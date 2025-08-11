<?php
/**
 * Widget Renderer Helper
 * Functions for rendering widgets in frontend templates
 */

/**
 * Render widgets for a specific location
 */
function render_widgets($location, $css_class = '') {
    static $widgetManager = null;
    
    if ($widgetManager === null) {
        $widgetManager = new WidgetManagerFlexible();
    }
    
    $widgets = $widgetManager->getWidgetsByLocation($location);
    
    if (empty($widgets)) {
        return '';
    }
    
    $html = '<div class="widget-area widget-area-' . $location . ($css_class ? ' ' . $css_class : '') . '">';
    
    foreach ($widgets as $widget) {
        $widget_html = $widgetManager->renderWidget($widget);
        if ($widget_html) {
            $widget_css = $widget['css_class'] ? ' ' . $widget['css_class'] : '';
            $html .= '<div class="widget widget-' . $widget['widget_type_id'] . $widget_css . '" data-widget-id="' . $widget['id'] . '">';
            $html .= $widget_html;
            $html .= '</div>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render navigation menu for a specific location
 */
function render_menu($location, $css_class = 'nav') {
    static $menuManager = null;
    
    if ($menuManager === null) {
        $menuManager = new MenuManager();
    }
    
    return $menuManager->renderMenu($location, $css_class);
}

/**
 * Check if a location has widgets
 */
function has_widgets($location) {
    static $widgetManager = null;
    
    if ($widgetManager === null) {
        $widgetManager = new WidgetManagerFlexible();
    }
    
    $widgets = $widgetManager->getWidgetsByLocation($location);
    return !empty($widgets);
}

/**
 * Check if a location has menu items
 */
function has_menu($location) {
    static $menuManager = null;
    
    if ($menuManager === null) {
        $menuManager = new MenuManagerFlexible();
    }
    
    $menu_items = $menuManager->getMenuItems($location);
    return !empty($menu_items);
}

/**
 * Get widget count for a location
 */
function widget_count($location) {
    static $widgetManager = null;
    
    if ($widgetManager === null) {
        $widgetManager = new WidgetManagerFlexible();
    }
    
    $widgets = $widgetManager->getWidgetsByLocation($location);
    return count($widgets);
}
?>
