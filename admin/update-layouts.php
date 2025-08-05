<?php
/**
 * Batch Update Admin Layout Includes
 * Updates all admin files to use the new layout system
 */

// Files to update (relative to admin directory)
$admin_files = [
    'users/index.php',
    'users/create.php',
    'users/view.php',
    'media/index.php',
    'media/upload.php',
    'settings.php'
];

$admin_root = __DIR__;

foreach ($admin_files as $file) {
    $file_path = $admin_root . '/' . $file;
    
    if (file_exists($file_path)) {
        echo "Updating: $file\n";
        
        // Read file content
        $content = file_get_contents($file_path);
        
        // Add ADMIN_ACCESS define if not present
        if (strpos($content, "define('ADMIN_ACCESS'") === false) {
            // Look for the opening PHP tag and add the define after it
            $content = preg_replace(
                '/(<\?php\s*(?:\/\*.*?\*\/\s*)?(?:\/\/.*?\n\s*)?)/s',
                "$1\n// Define admin access\ndefine('ADMIN_ACCESS', true);\n",
                $content,
                1
            );
        }
        
        // Update header includes
        $content = str_replace(
            [
                "include __DIR__ . '/../layouts/header.php';",
                "include '../layouts/header.php';",
                "require '../layouts/header.php';",
                "require_once '../layouts/header.php';",
                "include_once '../layouts/header.php';"
            ],
            "include __DIR__ . '/../layouts/header-new.php';",
            $content
        );
        
        // Update footer includes
        $content = str_replace(
            [
                "include __DIR__ . '/../layouts/footer.php';",
                "include '../layouts/footer.php';",
                "require '../layouts/footer.php';",
                "require_once '../layouts/footer.php';",
                "include_once '../layouts/footer.php';"
            ],
            "include __DIR__ . '/../layouts/footer-new.php';",
            $content
        );
        
        // Handle root admin files
        $content = str_replace(
            [
                "include __DIR__ . '/layouts/header.php';",
                "include 'layouts/header.php';",
                "require 'layouts/header.php';",
                "require_once 'layouts/header.php';",
                "include_once 'layouts/header.php';"
            ],
            "include __DIR__ . '/layouts/header-new.php';",
            $content
        );
        
        $content = str_replace(
            [
                "include __DIR__ . '/layouts/footer.php';",
                "include 'layouts/footer.php';",
                "require 'layouts/footer.php';",
                "require_once 'layouts/footer.php';",
                "include_once 'layouts/footer.php';"
            ],
            "include __DIR__ . '/layouts/footer-new.php';",
            $content
        );
        
        // Write updated content back
        file_put_contents($file_path, $content);
        echo "✓ Updated: $file\n";
    } else {
        echo "✗ File not found: $file\n";
    }
}

echo "\nBatch update completed!\n";
?>
