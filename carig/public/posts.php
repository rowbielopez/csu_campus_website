<?php
/**
 * Posts Listing Page
 * Display published posts with pagination and search
 */

// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();

// Pagination settings
$page = max(1, intval($_GET['page'] ?? 1));
$posts_per_page = 12;
$offset = ($page - 1) * $posts_per_page;

// Search functionality
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Get posts with filters
$db = Database::getInstance();
$where_conditions = [
    "p.campus_id = ?",
    "p.status = 'published'"
];
$params = [$campus['id']];

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get posts
$sql = "
    SELECT p.*, u.username as author_name, u.first_name, u.last_name
    FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id
    $where_clause 
    ORDER BY p.published_at DESC, p.created_at DESC
    LIMIT $posts_per_page OFFSET $offset
";

$posts = $db->fetchAll($sql, $params);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM posts p $where_clause";
$total_posts = $db->fetch($count_sql, $params)['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Page meta
$page_title = 'News & Updates - ' . $campus['name'];
$page_description = 'Latest news, updates, and announcements from ' . $campus['full_name'];

include 'layouts/header.php';
?>

<!-- Page Header -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold text-primary">News & Updates</h1>
                <p class="lead text-muted">
                    Stay informed with the latest news, announcements, and updates from <?php echo htmlspecialchars($campus['name']); ?>.
                </p>
            </div>
            <div class="col-lg-4">
                <!-- Search Form -->
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Search posts..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Breadcrumbs -->
<div class="container py-3">
    <?php 
    $breadcrumbs = [
        ['title' => 'News & Updates', 'url' => '']
    ];
    echo get_breadcrumbs($breadcrumbs); 
    ?>
</div>

<!-- Main Content -->
<div class="container py-4">
    <div class="row">
        <!-- Posts Grid -->
        <div class="col-lg-8">
            <?php if ($search): ?>
                <div class="alert alert-info">
                    <strong>Search Results:</strong> 
                    Found <?php echo $total_posts; ?> post(s) for "<?php echo htmlspecialchars($search); ?>"
                    <a href="posts.php" class="ms-2">Clear Search</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($posts)): ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <article class="post-card card h-100">
                                <?php if (isset($post['featured_image_url']) && $post['featured_image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                                         style="height: 200px; object-fit: cover;"
                                         loading="lazy">
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <div class="text-muted small mb-3">
                                        <span>
                                            <svg width="14" height="14" fill="currentColor" class="bi bi-person me-1" viewBox="0 0 16 16">
                                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                            </svg>
                                            By <?php echo htmlspecialchars($post['author_name']); ?>
                                        </span>
                                        <span class="ms-3">
                                            <svg width="14" height="14" fill="currentColor" class="bi bi-calendar me-1" viewBox="0 0 16 16">
                                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5 0zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                            </svg>
                                            <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                        </span>
                                        <?php if (isset($post['is_featured']) && $post['is_featured']): ?>
                                            <span class="badge bg-warning text-dark ms-2">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="card-text flex-grow-1">
                                        <?php 
                                        if ($post['excerpt']) {
                                            echo htmlspecialchars($post['excerpt']);
                                        } else {
                                            echo get_excerpt($post['content'], 120);
                                        }
                                        ?>
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Read More
                                            <svg width="14" height="14" fill="currentColor" class="bi bi-arrow-right ms-1" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-5">
                        <?php 
                        $url_pattern = 'posts.php?page=%d';
                        if ($search) {
                            $url_pattern .= '&search=' . urlencode($search);
                        }
                        echo render_pagination($page, $total_pages, $url_pattern);
                        ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Posts Message -->
                <div class="text-center py-5">
                    <svg width="64" height="64" fill="currentColor" class="bi bi-file-text text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                    </svg>
                    <h4 class="text-muted">No Posts Found</h4>
                    <p class="text-muted">
                        <?php if ($search): ?>
                            No posts match your search criteria. Try adjusting your search terms.
                        <?php else: ?>
                            There are currently no published posts available.
                        <?php endif; ?>
                    </p>
                    <?php if ($search): ?>
                        <a href="posts.php" class="btn btn-primary">View All Posts</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <aside class="sidebar">
                <!-- Recent Posts Widget -->
                <div class="widget mb-4">
                    <h5 class="widget-title">Recent Posts</h5>
                    <div class="widget-content">
                        <?php
                        $recent_posts = get_campus_posts(5);
                        if (!empty($recent_posts)):
                        ?>
                            <ul class="list-unstyled">
                                <?php foreach ($recent_posts as $recent_post): ?>
                                    <li class="mb-3 pb-3 border-bottom">
                                        <a href="post.php?slug=<?php echo urlencode($recent_post['slug']); ?>" 
                                           class="text-decoration-none">
                                            <div class="fw-semibold text-dark mb-1">
                                                <?php echo htmlspecialchars($recent_post['title']); ?>
                                            </div>
                                        </a>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($recent_post['published_at'] ?: $recent_post['created_at'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent posts available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Search Widget -->
                <div class="widget mb-4">
                    <h5 class="widget-title">Search Posts</h5>
                    <div class="widget-content">
                        <form method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <svg width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Campus Info Widget -->
                <?php echo render_widget([
                    'type' => 'contact_info',
                    'title' => 'Campus Information',
                    'content' => ''
                ]); ?>
            </aside>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
