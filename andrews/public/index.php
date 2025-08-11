<?php
/**
 * Andrews Campus Homepage
 * Dynamic homepage with campus-specific content
 */

// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();
$page_title = $campus['seo_title'];
$page_description = $campus['seo_description'];

// Get featured posts
$featured_posts = get_campus_posts(6, 0, true);
if (empty($featured_posts)) {
    // If no featured posts, get recent posts
    $featured_posts = get_campus_posts(6);
}

// Get recent news/updates
$recent_posts = get_campus_posts(4);

// Get homepage-specific widgets
$home_main_widgets = get_campus_widgets('home_main');
$home_sidebar_widgets = get_campus_widgets('home_sidebar');

// Fallback to regular sidebar widgets if no homepage sidebar widgets
$sidebar_widgets = !empty($home_sidebar_widgets) ? $home_sidebar_widgets : get_campus_widgets('sidebar');

include 'layouts/header.php';

// Get carousel items for this campus
$carousel_items = get_carousel_items();
?>

<!-- Modern Image Carousel -->
<?php if (!empty($carousel_items)): ?>
<section class="carousel-section py-5">
    <div class="container">
        <?php echo render_carousel($carousel_items); ?>
    </div>
</section>
<?php endif; ?>

<!-- Main Content -->
<div class="container my-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Homepage Main Widgets -->
            <?php if (!empty($home_main_widgets)): ?>
                <section class="mb-5">
                    <div class="row">
                        <?php foreach ($home_main_widgets as $widget): ?>
                            <div class="col-12 mb-4">
                                <?php echo render_widget($widget); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <?php if (!empty($featured_posts)): ?>
                <!-- Featured Posts Section -->
                <section class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-primary">Featured Posts</h2>
                        <a href="posts.php" class="btn btn-outline-primary">View All Posts</a>
                    </div>
                    
                    <div class="row">
                        <?php foreach (array_slice($featured_posts, 0, 3) as $index => $post): ?>
                            <div class="col-md-<?php echo $index === 0 ? '12' : '6'; ?> mb-4">
                                <article class="post-card card h-100">
                                    <div class="card-body">
                                        <?php if ($index === 0): ?>
                                            <!-- Main featured post (larger) -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h3 class="card-title">
                                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                                           class="text-decoration-none text-dark">
                                                            <?php echo htmlspecialchars($post['title']); ?>
                                                        </a>
                                                    </h3>
                                                    <p class="text-muted mb-2">
                                                        By <?php echo htmlspecialchars($post['author_name']); ?> • 
                                                        <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                                    </p>
                                                    <p class="card-text">
                                                        <?php echo get_excerpt($post['content'], 200); ?>
                                                    </p>
                                                    <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                                       class="btn btn-primary">
                                                        Read More
                                                    </a>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if (isset($post['featured_image_url']) && $post['featured_image_url']): ?>
                                                        <img src="<?php echo htmlspecialchars($post['featured_image_url']); ?>" 
                                                             class="img-fluid rounded" 
                                                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                             loading="lazy">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                             style="height: 200px;">
                                                            <span class="text-muted">No Image</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Secondary featured posts -->
                                            <h5 class="card-title">
                                                <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <?php echo get_excerpt($post['content'], 100); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <?php if (!empty($recent_posts)): ?>
                <!-- Recent Posts Section -->
                <section class="mb-5">
                    <h2 class="text-primary mb-4">Latest Updates</h2>
                    <div class="row">
                        <?php foreach ($recent_posts as $post): ?>
                            <div class="col-md-6 mb-4">
                                <article class="post-card card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            By <?php echo htmlspecialchars($post['author_name']); ?> • 
                                            <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                        </p>
                                        <p class="card-text">
                                            <?php echo get_excerpt($post['content'], 120); ?>
                                        </p>
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Read More
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Quick Info Section -->
            <section class="mb-5">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100 border-0 bg-light">
                            <div class="card-body">
                                <div class="text-primary mb-3">
                                    <svg width="48" height="48" fill="currentColor" class="bi bi-mortarboard" viewBox="0 0 16 16">
                                        <path d="M8.211 2.047a.5.5 0 0 0-.422 0L1.5 5.09v1.567a.5.5 0 0 0 .294.456l6.5 3a.5.5 0 0 0 .412 0l6.5-3a.5.5 0 0 0 .294-.456V5.09l-6.289-3.043ZM8 3.046 2.662 5.5 8 7.954 13.338 5.5 8 3.046ZM4.5 7.01l3.5 1.617 3.5-1.617v4.457l-3.5 1.617-3.5-1.617V7.01Z"/>
                                    </svg>
                                </div>
                                <h5 class="card-title">Academic Excellence</h5>
                                <p class="card-text">Quality education programs designed to prepare students for future challenges.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100 border-0 bg-light">
                            <div class="card-body">
                                <div class="text-primary mb-3">
                                    <svg width="48" height="48" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                    </svg>
                                </div>
                                <h5 class="card-title">Research & Innovation</h5>
                                <p class="card-text">Cutting-edge research programs contributing to knowledge and community development.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100 border-0 bg-light">
                            <div class="card-body">
                                <div class="text-primary mb-3">
                                    <svg width="48" height="48" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                    </svg>
                                </div>
                                <h5 class="card-title">Community Engagement</h5>
                                <p class="card-text">Strong partnerships with local communities for mutual growth and development.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <aside class="sidebar">
                <!-- Custom Widgets from Database -->
                <?php if (!empty($sidebar_widgets)): ?>
                    <?php foreach ($sidebar_widgets as $widget): ?>
                        <?php echo render_widget($widget); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback message if no widgets -->
                    <div class="widget mb-4">
                        <h5 class="widget-title">No Widgets Available</h5>
                        <div class="widget-content">
                            <p class="text-muted">Please add widgets through the admin panel.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
