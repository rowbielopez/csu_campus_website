<?php
/**
 * Secure Media Download Handler
 * Handles protected downloads with access control and download tracking
 */

session_start();

// Load core functions
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/media.php';

$media_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

if (!$media_id) {
    http_response_code(400);
    exit('Invalid media ID');
}

// Initialize MediaManager
$media_manager = new MediaManager();

// Get media file
$media = $media_manager->getMediaById($media_id);

if (!$media) {
    http_response_code(404);
    exit('File not found');
}

// Check if file is public or if user has access
$has_access = false;

if ($media['is_public']) {
    $has_access = true;
} else {
    // Check if user is logged in and has access
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../../core/functions/auth.php';
        $current_user = get_logged_in_user();
        $current_campus = get_current_campus();
        
        // User has access if they're the owner, admin, or from same campus
        if ($current_user && (
            $media['user_id'] == $current_user['id'] ||
            $current_user['role'] === 'admin' ||
            $current_user['role'] === 'super_admin' ||
            ($current_campus && $media['campus_id'] == $current_campus['id'])
        )) {
            $has_access = true;
        }
    }
    
    // Check token-based access (for sharing protected files)
    if (!$has_access && $token) {
        $expected_token = hash('sha256', $media['id'] . $media['filename'] . 'secure_download_salt');
        if (hash_equals($expected_token, $token)) {
            $has_access = true;
        }
    }
}

if (!$has_access) {
    http_response_code(403);
    exit('Access denied');
}

// Check if file exists
if (!file_exists($media['file_path'])) {
    http_response_code(404);
    exit('File not found on disk');
}

// Update download count
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("UPDATE media SET download_count = download_count + 1 WHERE id = ?");
$stmt->execute([$media_id]);

// Determine content type
$content_type = $media['mime_type'];
$file_extension = strtolower($media['file_extension']);

// Set appropriate headers
header('Content-Type: ' . $content_type);
header('Content-Length: ' . $media['file_size']);
header('Content-Disposition: inline; filename="' . $media['original_filename'] . '"');
header('Cache-Control: private, max-age=3600');
header('Pragma: private');

// For certain file types, force download
$force_download_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
if (in_array($file_extension, $force_download_types)) {
    header('Content-Disposition: attachment; filename="' . $media['original_filename'] . '"');
}

// Output file
readfile($media['file_path']);
exit;
?>
