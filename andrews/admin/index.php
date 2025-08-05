<?php
/**
 * Andrews Campus Admin Index
 * Redirects to campus dashboard
 */

// Load campus configuration first
require_once __DIR__ . '/../../config/andrews.php';

// Load authentication middleware
require_once __DIR__ . '/../../core/middleware/auth.php';

// Redirect to dashboard
header('Location: dashboard.php');
exit;
?>
