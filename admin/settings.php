<?php
/**
 * Campus Settings Management
 * Campus-specific configuration and branding settings
 */

// Load core authentication and require admin access

// Define admin access
define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../core/middleware/admin_only.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../config/config.php';

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST['settings'] ?? [];
    $campus_id = current_campus_id();
    
    try {
        $db->beginTransaction();
        
        // Update or insert settings
        foreach ($settings as $key => $value) {
            $existing = $db->fetch("SELECT id FROM settings WHERE campus_id = ? AND setting_key = ?", [$campus_id, $key]);
            
            if ($existing) {
                $db->query("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE campus_id = ? AND setting_key = ?", 
                          [$value, $campus_id, $key]);
            } else {
                $db->query("INSERT INTO settings (campus_id, setting_key, setting_value, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", 
                          [$campus_id, $key, $value]);
            }
        }
        
        // Update campus table with basic info
        if (isset($_POST['campus'])) {
            $campus_data = $_POST['campus'];
            $db->query("UPDATE campuses SET name = ?, full_name = ?, contact_email = ?, phone = ?, address = ?, theme_color = ?, secondary_color = ?, updated_at = NOW() WHERE id = ?", 
                      [$campus_data['name'], $campus_data['full_name'], $campus_data['contact_email'], 
                       $campus_data['phone'], $campus_data['address'], $campus_data['theme_color'], 
                       $campus_data['secondary_color'], $campus_id]);
        }
        
        $db->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Settings saved successfully!'];
        
    } catch (Exception $e) {
        // Handle rollback safely - check if connection is still active
        try {
            if ($db && method_exists($db, 'rollback')) {
                $db->rollback();
            }
        } catch (Exception $rollback_error) {
            // Log the rollback error but don't throw it
            error_log('Rollback failed: ' . $rollback_error->getMessage());
        }
        
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error saving settings: ' . $e->getMessage()];
    }
    
    header('Location: settings.php');
    exit;
}

// Create settings table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campus_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_campus_setting (campus_id, setting_key),
    FOREIGN KEY (campus_id) REFERENCES campuses(id)
)");

// Get current settings
$campus_id = current_campus_id();
$settings_result = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE campus_id = ?", [$campus_id]);
$settings = [];
foreach ($settings_result as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Get campus info
$campus = $db->fetch("SELECT * FROM campuses WHERE id = ?", [$campus_id]);

$page_title = 'Campus Settings';
$page_description = 'Manage campus configuration and branding';

include __DIR__ . '/layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4"><?php echo $page_title; ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show auto-dismiss" role="alert">
            <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form method="POST" id="settingsForm">
        <div class="row">
            <!-- Campus Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-university me-1"></i>
                        Campus Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="campus_name" class="form-label">Campus Name</label>
                                    <input type="text" name="campus[name]" id="campus_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($campus['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="campus_full_name" class="form-label">Full Name</label>
                                    <input type="text" name="campus[full_name]" id="campus_full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($campus['full_name']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="campus_email" class="form-label">Contact Email</label>
                                    <input type="email" name="campus[contact_email]" id="campus_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($campus['contact_email']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="campus_phone" class="form-label">Phone Number</label>
                                    <input type="text" name="campus[phone]" id="campus_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($campus['phone']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="campus_address" class="form-label">Address</label>
                            <textarea name="campus[address]" id="campus_address" class="form-control" rows="3"><?php echo htmlspecialchars($campus['address']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Website Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-globe me-1"></i>
                        Website Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="site_title" class="form-label">Site Title</label>
                            <input type="text" name="settings[site_title]" id="site_title" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_title'] ?? $campus['name']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="site_description" class="form-label">Site Description</label>
                            <textarea name="settings[site_description]" id="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="posts_per_page" class="form-label">Posts per Page</label>
                                    <input type="number" name="settings[posts_per_page]" id="posts_per_page" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['posts_per_page'] ?? '10'); ?>" min="1" max="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select name="settings[timezone]" id="timezone" class="form-select">
                                        <option value="Asia/Manila" <?php echo ($settings['timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila</option>
                                        <option value="UTC" <?php echo ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-share-alt me-1"></i>
                        Social Media Links
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook</label>
                                    <input type="url" name="settings[facebook_url]" id="facebook_url" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" 
                                           placeholder="https://facebook.com/yourpage">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter</label>
                                    <input type="url" name="settings[twitter_url]" id="twitter_url" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" 
                                           placeholder="https://twitter.com/yourhandle">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="youtube_url" class="form-label">YouTube</label>
                                    <input type="url" name="settings[youtube_url]" id="youtube_url" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" 
                                           placeholder="https://youtube.com/yourchannel">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram</label>
                                    <input type="url" name="settings[instagram_url]" id="instagram_url" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" 
                                           placeholder="https://instagram.com/yourhandle">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Branding -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-palette me-1"></i>
                        Branding
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="theme_color" class="form-label">Primary Color</label>
                            <div class="input-group">
                                <input type="color" name="campus[theme_color]" id="theme_color" class="form-control form-control-color" 
                                       value="<?php echo htmlspecialchars($campus['theme_color']); ?>">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($campus['theme_color']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="campus[secondary_color]" id="secondary_color" class="form-control form-control-color" 
                                       value="<?php echo htmlspecialchars($campus['secondary_color']); ?>">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($campus['secondary_color']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="color-preview p-3 rounded" style="background: linear-gradient(135deg, <?php echo $campus['theme_color']; ?> 0%, <?php echo $campus['secondary_color']; ?> 100%);">
                            <div class="text-white text-center">
                                <strong>Color Preview</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-toggle-on me-1"></i>
                        Features
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" name="settings[enable_comments]" id="enable_comments" class="form-check-input" 
                                   value="1" <?php echo ($settings['enable_comments'] ?? '1') ? 'checked' : ''; ?>>
                            <label for="enable_comments" class="form-check-label">
                                Enable Comments
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="settings[enable_registration]" id="enable_registration" class="form-check-input" 
                                   value="1" <?php echo ($settings['enable_registration'] ?? '0') ? 'checked' : ''; ?>>
                            <label for="enable_registration" class="form-check-label">
                                Allow User Registration
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="settings[enable_search]" id="enable_search" class="form-check-input" 
                                   value="1" <?php echo ($settings['enable_search'] ?? '1') ? 'checked' : ''; ?>>
                            <label for="enable_search" class="form-check-label">
                                Enable Search
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="settings[maintenance_mode]" id="maintenance_mode" class="form-check-input" 
                                   value="1" <?php echo ($settings['maintenance_mode'] ?? '0') ? 'checked' : ''; ?>>
                            <label for="maintenance_mode" class="form-check-label">
                                Maintenance Mode
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Save Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="small text-muted">
                            <i class="fas fa-info-circle"></i>
                            Changes will be applied immediately after saving.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Color picker synchronization
document.addEventListener('DOMContentLoaded', function() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    
    colorInputs.forEach(function(colorInput) {
        const textInput = colorInput.parentNode.querySelector('input[type="text"]');
        
        colorInput.addEventListener('change', function() {
            textInput.value = this.value;
            updateColorPreview();
        });
    });
    
    function updateColorPreview() {
        const primary = document.getElementById('theme_color').value;
        const secondary = document.getElementById('secondary_color').value;
        const preview = document.querySelector('.color-preview');
        
        preview.style.background = `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`;
    }
});

// Form auto-save (optional)
let saveTimeout;
document.getElementById('settingsForm').addEventListener('input', function() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(function() {
        // Could implement auto-save here
        console.log('Auto-save triggered...');
    }, 2000);
});
</script>

<?php include __DIR__ . '/layouts/footer-new.php'; ?>
