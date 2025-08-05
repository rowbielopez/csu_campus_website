<?php
/**
 * Create New Category
 * Create categories with hierarchy support
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Check permissions - only campus admins and super admins can manage categories
if (!is_campus_admin() && !is_super_admin()) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'You do not have permission to create categories.'
    ];
    header('Location: ../index.php');
    exit;
}

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $description = trim($_POST['description'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Category name is required.';
    }
    
    if (strlen($name) > 255) {
        $errors[] = 'Category name must be 255 characters or less.';
    }
    
    // Validate parent category if specified
    if ($parent_id) {
        $parent_sql = "SELECT id FROM categories WHERE id = ?";
        $parent_params = [$parent_id];
        
        if (!is_super_admin()) {
            $parent_sql .= " AND campus_id = ?";
            $parent_params[] = current_campus_id();
        }
        
        $parent_exists = $db->fetch($parent_sql, $parent_params);
        if (!$parent_exists) {
            $errors[] = 'Invalid parent category selected.';
        }
    }
    
    if (empty($errors)) {
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        
        // Check for duplicate slug in campus
        $existing = $db->fetch("SELECT id FROM categories WHERE slug = ? AND campus_id = ?", [$slug, current_campus_id()]);
        if ($existing) {
            $slug .= '-' . time();
        }
        
        try {
            $category_data = [
                'campus_id' => current_campus_id(),
                'parent_id' => $parent_id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'sort_order' => $sort_order,
                'is_active' => $is_active,
                'meta_title' => $meta_title ?: $name,
                'meta_description' => $meta_description ?: $description
            ];
            
            $fields = array_keys($category_data);
            $placeholders = ':' . implode(', :', $fields);
            
            $sql = "INSERT INTO categories (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            $db->query($sql, $category_data);
            
            $category_id = $db->lastInsertId();
            
            $_SESSION['flash_message'] = [
                'type' => 'success', 
                'message' => "Category \"$name\" created successfully!"
            ];
            
            header('Location: edit.php?id=' . $category_id);
            exit;
            
        } catch (Exception $e) {
            $errors[] = 'Error creating category: ' . $e->getMessage();
        }
    }
}

// Get available parent categories for dropdown
$parent_categories_sql = "SELECT id, name, parent_id FROM categories WHERE campus_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC";
$parent_categories = $db->fetchAll($parent_categories_sql, [current_campus_id()]);

// Build hierarchical options
function buildCategoryOptions($categories, $parent_id = null, $level = 0, $exclude_id = null) {
    $options = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id && $category['id'] != $exclude_id) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $options[] = [
                'id' => $category['id'],
                'name' => $indent . $category['name'],
                'level' => $level
            ];
            $options = array_merge($options, buildCategoryOptions($categories, $category['id'], $level + 1, $exclude_id));
        }
    }
    return $options;
}

$parent_options = buildCategoryOptions($parent_categories);

$page_title = 'Create New Category';
$page_description = 'Create a new content category';

include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4"><?php echo $page_title; ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Categories</a></li>
                <li class="breadcrumb-item active">Create New</li>
            </ol>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="categoryForm">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-1"></i>
                        Basic Information
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   placeholder="Enter category name..." 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            <div class="form-text">This will be displayed to users and used for navigation.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="Describe what this category is for..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-text">Optional description to help users understand the category's purpose.</div>
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="card mb-4">
                    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#seoSettings" style="cursor: pointer;">
                        <i class="fas fa-search me-1"></i>
                        SEO Settings
                        <i class="fas fa-chevron-down float-end"></i>
                    </div>
                    <div class="collapse" id="seoSettings">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" 
                                       placeholder="SEO title (leave blank to use category name)"
                                       value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
                                <div class="form-text">Recommended: 50-60 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                                          placeholder="SEO description (leave blank to use category description)"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                                <div class="form-text">Recommended: 150-160 characters</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Category Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-cog me-1"></i>
                        Category Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                <?php foreach ($parent_options as $option): ?>
                                    <option value="<?php echo $option['id']; ?>" 
                                            <?php echo ($_POST['parent_id'] ?? '') == $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo $option['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Choose a parent to create a subcategory.</div>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" 
                                   min="0" value="<?php echo intval($_POST['sort_order'] ?? 0); ?>">
                            <div class="form-text">Lower numbers appear first in listings.</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                                       value="1" <?php echo isset($_POST['is_active']) || !$_POST ? 'checked' : ''; ?>>
                                <label for="is_active" class="form-check-label">
                                    Active
                                </label>
                            </div>
                            <div class="form-text">Only active categories are shown to users.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                Create Category
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-lightbulb me-1"></i>
                        Category Tips
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Use clear, descriptive names</li>
                            <li><i class="fas fa-check text-success me-2"></i>Create hierarchies for better organization</li>
                            <li><i class="fas fa-check text-success me-2"></i>Keep the structure simple and logical</li>
                            <li><i class="fas fa-check text-success me-2"></i>Consider SEO when naming categories</li>
                            <li><i class="fas fa-info text-info me-2"></i>Categories help users find related content</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-generate slug preview
document.getElementById('name').addEventListener('input', function() {
    // Could add slug preview functionality here
});

// Character counters for SEO fields
document.addEventListener('DOMContentLoaded', function() {
    const metaTitle = document.getElementById('meta_title');
    const metaDescription = document.getElementById('meta_description');
    
    function addCharCounter(element, maxLength) {
        const counter = document.createElement('div');
        counter.className = 'form-text';
        element.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = element.value.length;
            counter.textContent = `${length}/${maxLength} characters`;
            counter.className = length > maxLength ? 'form-text text-danger' : 'form-text text-muted';
        }
        
        element.addEventListener('input', updateCounter);
        updateCounter();
    }
    
    if (metaTitle) addCharCounter(metaTitle, 60);
    if (metaDescription) addCharCounter(metaDescription, 160);
});

// Form validation
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    
    if (!name) {
        alert('Please enter a category name.');
        e.preventDefault();
        return false;
    }
    
    if (name.length > 255) {
        alert('Category name must be 255 characters or less.');
        e.preventDefault();
        return false;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
