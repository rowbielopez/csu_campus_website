<?php
/**
 * Create New Post
 * WYSIWYG editor with role-based publishing workflow
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../../core/middleware/auth.php';
require_once __DIR__ . '/../../core/functions/auth.php';
require_once __DIR__ . '/../../core/functions/utilities.php';
require_once __DIR__ . '/../../config/config.php';

// Get current user and campus
$current_user = get_logged_in_user();
$current_campus = get_current_campus();
$db = Database::getInstance();

// Get available categories for dropdown
$categories = $db->fetchAll("SELECT id, name, parent_id FROM categories WHERE campus_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC", [current_campus_id()]);

// Build hierarchical options
function buildCategoryOptions($categories, $parent_id = null, $level = 0) {
    $options = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $options[] = [
                'id' => $category['id'],
                'name' => $indent . $category['name'],
                'level' => $level
            ];
            $options = array_merge($options, buildCategoryOptions($categories, $category['id'], $level + 1));
        }
    }
    return $options;
}

$category_options = buildCategoryOptions($categories);

// Get popular tags for suggestions
$popular_tags = $db->fetchAll("SELECT name FROM tags WHERE campus_id = ? AND is_active = 1 ORDER BY usage_count DESC LIMIT 20", [current_campus_id()]);

// Get available widgets for content assignment
$available_widgets = $db->fetchAll("
    SELECT cw.id, cw.title, cw.position, wt.name as type_name, wt.description as type_description
    FROM campus_widgets cw 
    LEFT JOIN widget_types wt ON cw.widget_type_id = wt.id 
    WHERE cw.campus_id = ? AND cw.is_active = 1 
    ORDER BY cw.position, cw.sort_order, cw.title
", [current_campus_id()]);

// Group widgets by position for better organization
$widgets_by_position = [];
foreach ($available_widgets as $widget) {
    $position = $widget['position'] ?: 'other';
    $widgets_by_position[$position][] = $widget;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $category_ids = $_POST['category_ids'] ?? [];
    $tag_names = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
    $widget_ids = $_POST['widget_ids'] ?? [];
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $featured_image_url = trim($_POST['featured_image_url'] ?? '');
    
    $errors = [];
    
    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/images/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = 'featured_' . time() . '_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                $featured_image_url = '/campus_website2/uploads/images/' . $filename;
            } else {
                $errors[] = 'Failed to upload featured image.';
            }
        } else {
            $errors[] = 'Invalid image format. Allowed: JPG, PNG, GIF, WebP.';
        }
    }
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if (empty($category_ids)) {
        $errors[] = 'At least one category must be selected.';
    }
    
    // Status validation based on role
    if ($status === 'published' && !is_campus_admin() && !is_super_admin()) {
        $status = 'pending'; // Authors can only submit for review
    }
    
    if (empty($errors)) {
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        // Check for duplicate slug in campus
        $existing = $db->fetch("SELECT id FROM posts WHERE slug = ? AND campus_id = ?", [$slug, current_campus_id()]);
        if ($existing) {
            $slug .= '-' . time();
        }
        
        // Auto-generate excerpt if not provided
        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 150) . '...';
        }
        
        try {
            $post_data = [
                'campus_id' => current_campus_id(),
                'author_id' => $current_user['id'],
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'status' => $status,
                'is_featured' => $featured,
                'featured_image_url' => $featured_image_url,
                'meta_title' => $meta_title ?: $title,
                'meta_description' => $meta_description ?: $excerpt
            ];
            
            // Set published_at if publishing immediately
            if ($status === 'published') {
                $post_data['published_at'] = date('Y-m-d H:i:s');
            }
            
            $fields = array_keys($post_data);
            $placeholders = ':' . implode(', :', $fields);
            
            $sql = "INSERT INTO posts (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            $db->query($sql, $post_data);
            
            $post_id = $db->lastInsertId();
            
            // Handle categories
            if (!empty($category_ids)) {
                foreach ($category_ids as $category_id) {
                    $category_id = intval($category_id);
                    if ($category_id > 0) {
                        $db->query("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)", [$post_id, $category_id]);
                    }
                }
            }
            
            // Handle tags
            if (!empty($tag_names)) {
                foreach ($tag_names as $tag_name) {
                    if (!empty($tag_name)) {
                        // Create slug for tag
                        $tag_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tag_name), '-'));
                        
                        // Check if tag exists in campus
                        $existing_tag = $db->fetch("SELECT id FROM tags WHERE slug = ? AND campus_id = ?", [$tag_slug, current_campus_id()]);
                        
                        if ($existing_tag) {
                            $tag_id = $existing_tag['id'];
                        } else {
                            // Create new tag
                            $db->query("INSERT INTO tags (campus_id, name, slug) VALUES (?, ?, ?)", [current_campus_id(), $tag_name, $tag_slug]);
                            $tag_id = $db->lastInsertId();
                        }
                        
                        // Associate tag with post
                        $db->query("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)", [$post_id, $tag_id]);
                    }
                }
            }
            
            // Handle widget assignments
            if (!empty($widget_ids)) {
                $widget_stmt = $conn->prepare("INSERT INTO post_widgets (post_id, widget_id) VALUES (?, ?)");
                foreach ($widget_ids as $widget_id) {
                    $widget_stmt->execute([$post_id, $widget_id]);
                }
            }
            
            $_SESSION['flash_message'] = [
                'type' => 'success', 
                'message' => "Post created successfully! " . 
                           ($status === 'published' ? 'It is now live.' : 
                            ($status === 'pending' ? 'It has been submitted for review.' : 'It has been saved as a draft.'))
            ];
            
            header('Location: edit.php?id=' . $post_id);
            exit;
            
        } catch (Exception $e) {
            $errors[] = 'Error creating post: ' . $e->getMessage();
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Create New Post';
$page_description = 'Create and publish new content';

include __DIR__ . '/../layouts/header-new.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4"><?php echo $page_title; ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Posts</a></li>
                <li class="breadcrumb-item active">Create New</li>
            </ol>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> &nbsp; Back to Posts
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

    <form method="POST" id="postForm" class="auto-save" enctype="multipart/form-data">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Title -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" name="title" id="title" class="form-control form-control-lg" 
                                   placeholder="Enter post title..." 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea name="excerpt" id="excerpt" class="form-control" rows="3"
                                      placeholder="Brief description or summary..."><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                            <div class="form-text">Leave blank to auto-generate from content.</div>
                        </div>
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-edit me-1"></i>
                        Content
                    </div>
                    <div class="card-body">
                        <textarea name="content" id="content" class="form-control"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
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
                                       placeholder="SEO title (leave blank to use post title)"
                                       value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
                                <div class="form-text">Recommended: 50-60 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                                          placeholder="SEO description (leave blank to use excerpt)"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                                <div class="form-text">Recommended: 150-160 characters</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publish Options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-paper-plane me-1"></i>
                        Publish
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                                    Save as Draft
                                </option>
                                <?php if (is_campus_admin() || is_super_admin()): ?>
                                    <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                        Publish Immediately
                                    </option>
                                <?php else: ?>
                                    <option value="pending" <?php echo ($_POST['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>
                                        Submit for Review
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" 
                                       value="1" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                <label for="is_featured" class="form-check-label">
                                    Featured Post
                                </label>
                            </div>
                            <div class="form-text">Featured posts appear prominently on the homepage.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php if (is_campus_admin() || is_super_admin()): ?>
                                   &nbsp; Create Post
                                <?php else: ?>
                                    Save Post
                                <?php endif; ?>
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>  &nbsp; Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Widget Assignment -->
                <?php if (!empty($available_widgets)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-puzzle-piece me-1"></i>
                        Widget Assignment
                        <small class="text-muted">(Select specific widgets for this post)</small>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            <strong>Instance-Based Filtering:</strong> Select the specific widget instances where this post should appear. 
                            Each widget can have unique content - two "Text Widgets" can show different posts.
                        </p>
                        
                        <?php foreach ($widgets_by_position as $position => $widgets): ?>
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo ucfirst($position); ?> Widgets
                                </h6>
                                <div class="row">
                                    <?php foreach ($widgets as $widget): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check widget-option">
                                                <input type="checkbox" name="widget_ids[]" value="<?php echo $widget['id']; ?>" 
                                                       id="widget_<?php echo $widget['id']; ?>" class="form-check-input"
                                                       <?php echo in_array($widget['id'], $_POST['widget_ids'] ?? []) ? 'checked' : ''; ?>>
                                                <label for="widget_<?php echo $widget['id']; ?>" class="form-check-label">
                                                    <strong><?php echo htmlspecialchars($widget['title']); ?></strong>
                                                    <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($widget['type_name']); ?></span>
                                                    <?php if ($widget['type_description']): ?>
                                                        <small class="text-muted d-block">
                                                            <?php echo htmlspecialchars($widget['type_description']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>How it works:</strong> 
                                <ul class="mb-1 mt-2">
                                    <li>Each widget instance can show unique content</li>
                                    <li>Two "Text Widgets" can display completely different posts</li>
                                    <li>Select specific widgets where you want this post to appear</li>
                                    <li>Unselected widgets will not show this post</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-puzzle-piece me-1"></i>
                        Widget Assignment
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No widgets available for assignment.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-folder me-1"></i>
                        Categories *
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php if (empty($category_options)): ?>
                                <div class="alert alert-warning">
                                    <p class="mb-2">No categories available. <a href="../categories/create.php" target="_blank">Create categories first</a>.</p>
                                </div>
                            <?php else: ?>
                                <div class="category-checkboxes" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($category_options as $option): ?>
                                        <div class="form-check">
                                            <input type="checkbox" name="category_ids[]" value="<?php echo $option['id']; ?>" 
                                                   class="form-check-input category-checkbox"
                                                   id="category_<?php echo $option['id']; ?>"
                                                   <?php echo in_array($option['id'], $_POST['category_ids'] ?? []) ? 'checked' : ''; ?>>
                                            <label for="category_<?php echo $option['id']; ?>" class="form-check-label">
                                                <?php echo $option['name']; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">Select at least one category for this post.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tags me-1"></i>
                        Tags
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" name="tags" id="tags" class="form-control" 
                                   placeholder="Enter tags separated by commas"
                                   value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>">
                            <div class="form-text">Example: news, events, campus life</div>
                        </div>
                        
                        <?php if (!empty($popular_tags)): ?>
                            <div class="popular-tags">
                                <small class="text-muted">Popular tags:</small><br>
                                <?php foreach ($popular_tags as $tag): ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1 tag-suggestion" 
                                            data-tag="<?php echo htmlspecialchars($tag['name']); ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-image me-1"></i>
                        Featured Image
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-8">
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="browseFeaturedImage()">
                                        <i class="fas fa-search me-1"></i>
                                        Browse Library
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">Upload new image or browse existing media library</div>
                            <input type="hidden" id="featured_image_url" name="featured_image_url">
                        </div>
                        <div id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" class="img-fluid rounded mb-2">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="removeImage()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editImageDetails()">
                                    <i class="fas fa-edit"></i> Edit Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-lightbulb me-1"></i>
                        Quick Tips
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Use clear, descriptive titles</li>
                            <li><i class="fas fa-check text-success me-2"></i>Add relevant tags for better discovery</li>
                            <li><i class="fas fa-check text-success me-2"></i>Include an excerpt for better SEO</li>
                            <?php if (!is_campus_admin() && !is_super_admin()): ?>
                                <li><i class="fas fa-info text-info me-2"></i>Posts need admin approval before publishing</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* CKEditor custom styling */
.ck-editor__editable_inline {
    min-height: 400px;
}

.ck.ck-editor {
    max-width: 100%;
}

.ck-content {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

.ck-content h1 {
    font-size: 2rem;
}

.ck-content h2 {
    font-size: 1.5rem;
}

.ck-content h3 {
    font-size: 1.25rem;
}

.ck-content blockquote {
    border-left: 4px solid #ccc;
    margin-left: 0;
    padding-left: 1rem;
    font-style: italic;
}

/* Widget Type Selection Styling */
.widget-type-option {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s ease;
    background-color: #f9fafb;
}

.widget-type-option:hover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.widget-type-option:has(input:checked) {
    border-color: #3b82f6;
    background-color: #dbeafe;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.widget-type-option .form-check-input:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.widget-type-option label {
    cursor: pointer;
    margin-bottom: 0;
    font-weight: 500;
}

.widget-type-option small {
    color: #6b7280 !important;
    font-size: 0.875rem;
}
</style>

<!-- Include CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
// Initialize CKEditor
let contentEditor;

ClassicEditor
    .create(document.querySelector('#content'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|',
                'link', 'insertImage', 'browseCampusMedia', 'insertTable', 'mediaEmbed', '|',
                'alignment', '|',
                'blockQuote', 'codeBlock', '|',
                'undo', 'redo', '|',
                'sourceEditing'
            ]
        },
        language: 'en',
        image: {
            toolbar: [
                'imageTextAlternative',
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:side'
            ]
        },
        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells'
            ]
        },
        licenseKey: '',
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
            ]
        }
    })
    .then(editor => {
        contentEditor = editor;
        window.editor = editor;
        
        // Add custom media browser button
        editor.ui.componentFactory.add('browseCampusMedia', locale => {
            const view = new editor.ui.buttonView(locale);
            
            view.set({
                label: 'Browse Media Library',
                icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M6.972 16.615a.997.997 0 0 1-.744-.292l-4.596-4.596a1 1 0 0 1 0-1.414l4.596-4.596a1 1 0 0 1 1.414 0l4.596 4.596a1 1 0 0 1 0 1.414l-4.596 4.596a.99.99 0 0 1-.67.292z" fill="#999"/><path d="M6.972 14.615l-3.596-3.596 3.596-3.596 3.596 3.596-3.596 3.596z" fill="#444"/></svg>',
                tooltip: true
            });
            
            view.on('execute', () => {
                openMediaBrowser();
            });
            
            return view;
        });
        
        // Set editor height
        editor.editing.view.change(writer => {
            writer.setStyle('height', '400px', editor.editing.view.document.getRoot());
        });
    })
    .catch(error => {
        console.error('CKEditor initialization failed:', error);
    });

// Media Browser Functions
function openMediaBrowser() {
    const browserWindow = window.open(
        '../media/media-browser.php?type=content', 
        'mediaBrowser', 
        'width=1200,height=800,scrollbars=yes,resizable=yes'
    );
    browserWindow.focus();
}

// Browse for featured image
function browseFeaturedImage() {
    const browserWindow = window.open(
        '../media/media-browser.php?type=featured&file_type=image', 
        'featuredImageBrowser', 
        'width=1200,height=800,scrollbars=yes,resizable=yes'
    );
    browserWindow.focus();
}

// Callback function for content media browser
function insertMediaCallback(media) {
    if (contentEditor) {
        let content = '';
        
        if (media.type === 'image') {
            content = `<figure class="image"><img src="${media.url}" alt="${media.alt || media.filename}"></figure>`;
        } else if (media.type === 'video') {
            content = `<video controls style="width: 100%; max-width: 640px;"><source src="${media.url}" type="video/mp4">Your browser does not support the video tag.</video>`;
        } else if (media.type === 'audio') {
            content = `<audio controls style="width: 100%;"><source src="${media.url}" type="audio/mpeg">Your browser does not support the audio tag.</audio>`;
        } else {
            content = `<a href="${media.url}" target="_blank" rel="noopener">${media.filename}</a>`;
        }
        
        const viewFragment = contentEditor.data.processor.toView(content);
        const modelFragment = contentEditor.data.toModel(viewFragment);
        contentEditor.model.insertContent(modelFragment);
    }
}

// Callback function for featured image browser
function insertFeaturedImageCallback(media) {
    document.getElementById('featured_image_url').value = media.url;
    document.getElementById('previewImg').src = media.url;
    document.getElementById('imagePreview').style.display = 'block';
    
    // Clear file input since we're using library image
    document.getElementById('featured_image').value = '';
}

// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    // Auto-save functionality can be added here
});

// Handle featured image upload
document.getElementById('featured_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function removeImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('featured_image_url').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

function editImageDetails() {
    // Open image editing modal (could be implemented later)
    alert('Image editing functionality can be implemented here');
}

// Character counters
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

// Form submission handling
document.getElementById('postForm').addEventListener('submit', function(e) {
    // Basic validation
    const title = document.getElementById('title').value.trim();
    const content = contentEditor ? contentEditor.getData() : '';
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox:checked');
    
    if (!title) {
        alert('Please enter a title for your post.');
        e.preventDefault();
        return false;
    }
    
    if (!content || content.trim() === '') {
        alert('Please enter some content for your post.');
        e.preventDefault();
        return false;
    }
    
    if (categoryCheckboxes.length === 0) {
        alert('Please select at least one category for your post.');
        e.preventDefault();
        return false;
    }
    
    // Update the textarea with CKEditor content before form submission
    if (contentEditor) {
        document.getElementById('content').value = contentEditor.getData();
    }
});

// Tag suggestion functionality
document.addEventListener('DOMContentLoaded', function() {
    const tagInput = document.getElementById('tags');
    const tagSuggestions = document.querySelectorAll('.tag-suggestion');
    
    tagSuggestions.forEach(button => {
        button.addEventListener('click', function() {
            const tagName = this.getAttribute('data-tag');
            const currentTags = tagInput.value.split(',').map(tag => tag.trim()).filter(tag => tag !== '');
            
            if (!currentTags.includes(tagName)) {
                currentTags.push(tagName);
                tagInput.value = currentTags.join(', ');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer-new.php'; ?>
