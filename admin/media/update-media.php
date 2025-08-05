<?php
/**
 * Update Media Metadata
 * Handles updates to media file information
 */

session_start();

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/functions/media.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get current user and campus info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

// CSRF Protection
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$media_id = (int)($_POST['media_id'] ?? 0);
if (!$media_id) {
    http_response_code(400);
    exit('Invalid media ID');
}

// Initialize MediaManager
$media_manager = new MediaManager();

// Get media file (with campus isolation)
$media = $media_manager->getMediaById($media_id, $current_campus['id']);

if (!$media) {
    $_SESSION['error_message'] = 'Media file not found.';
    header('Location: index.php');
    exit;
}

// Check permissions
if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'super_admin' && $media['user_id'] != $current_user['id']) {
    $_SESSION['error_message'] = 'Permission denied.';
    header('Location: index.php');
    exit;
}

// Prepare update data
$update_data = [
    'alt_text' => trim($_POST['alt_text'] ?? ''),
    'caption' => trim($_POST['caption'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'is_public' => isset($_POST['is_public']) ? 1 : 0
];

// Only admins can set featured status
if ($current_user['role'] === 'admin' || $current_user['role'] === 'super_admin') {
    $update_data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
}

// Update media
$result = $media_manager->updateMedia($media_id, $update_data, $current_campus['id']);

if ($result['success']) {
    $_SESSION['success_message'] = 'Media information updated successfully.';
} else {
    $_SESSION['error_message'] = 'Error updating media: ' . $result['error'];
}

header('Location: index.php');
exit;
?>
