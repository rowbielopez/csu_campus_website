<?php
/**
 * Media Browser API
 * Returns JSON list of available media files
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core dependencies
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../config/config.php';

// Check permissions
if (!is_campus_admin() && !is_super_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

try {
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 12; // Images per page
    
    // Define media directories to scan
    $mediaDirs = [
        'uploads/images/',
        'uploads/avatars/',
        'uploads/thumbs/',
        'public/img/'
    ];
    
    $images = [];
    $baseDir = __DIR__ . '/../';
    
    foreach ($mediaDirs as $dir) {
        $fullPath = $baseDir . $dir;
        
        if (is_dir($fullPath)) {
            $files = scandir($fullPath);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filePath = $fullPath . $file;
                $relativePath = $dir . $file;
                
                // Check if it's an image file
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if (in_array($extension, $imageExtensions) && is_file($filePath)) {
                    // Apply search filter if provided
                    if (empty($search) || stripos($file, $search) !== false) {
                        $fileSize = filesize($filePath);
                        $fileSizeFormatted = formatFileSize($fileSize);
                        
                        // Create absolute URL path with correct base
                        $webPath = '/campus_website2/' . str_replace('\\', '/', $relativePath);
                        
                        $images[] = [
                            'path' => $webPath,
                            'name' => pathinfo($file, PATHINFO_FILENAME),
                            'filename' => $file,
                            'size' => $fileSizeFormatted,
                            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                            'directory' => rtrim($dir, '/')
                        ];
                    }
                }
            }
        }
    }
    
    // Sort by modification date (newest first)
    usort($images, function($a, $b) {
        return strtotime($b['modified']) - strtotime($a['modified']);
    });
    
    // Pagination
    $total = count($images);
    $totalPages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;
    $pagedImages = array_slice($images, $offset, $limit);
    
    echo json_encode([
        'success' => true,
        'images' => $pagedImages,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_images' => $total,
            'per_page' => $limit
        ],
        'search' => $search
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Format file size in human readable format
 */
function formatFileSize($bytes, $precision = 1) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . $units[$i];
}
?>
