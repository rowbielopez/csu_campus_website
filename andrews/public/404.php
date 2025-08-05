<?php
/**
 * 404 Error Page
 * Campus-specific 404 page with branding
 */

// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();
$page_title = 'Page Not Found - ' . $campus['name'];
$page_description = 'The page you are looking for could not be found.';

// Set 404 header if not already set
if (!headers_sent()) {
    http_response_code(404);
}

include 'layouts/header.php';
?>

<!-- 404 Content -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <!-- 404 Icon -->
            <div class="mb-4">
                <svg width="120" height="120" fill="var(--campus-primary)" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                </svg>
            </div>
            
            <!-- 404 Title -->
            <h1 class="display-1 fw-bold text-primary">404</h1>
            <h2 class="h3 text-dark mb-4">Page Not Found</h2>
            
            <!-- Description -->
            <p class="lead text-muted mb-4">
                Sorry, the page you are looking for could not be found. It may have been moved, deleted, or you entered the wrong URL.
            </p>
            
            <!-- Campus-specific message -->
            <div class="alert alert-info" role="alert">
                <strong>Looking for <?php echo htmlspecialchars($campus['name']); ?> content?</strong><br>
                You can find all our latest updates and information using the links below.
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mb-5">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" fill="currentColor" class="bi bi-house me-2" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                        <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                    </svg>
                    Go to Homepage
                </a>
                <a href="posts.php" class="btn btn-outline-primary btn-lg">
                    <svg width="20" height="20" fill="currentColor" class="bi bi-file-text me-2" viewBox="0 0 16 16">
                        <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                    </svg>
                    Browse Posts
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Search Our Content</h5>
                            <form action="posts.php" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search for posts, news, or information..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <svg width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                        </svg>
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links Section -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <h3 class="text-center text-primary mb-4">Quick Links</h3>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 bg-light text-center">
                        <div class="card-body">
                            <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-mortarboard mb-3" viewBox="0 0 16 16">
                                <path d="M8.211 2.047a.5.5 0 0 0-.422 0L1.5 5.09v1.567a.5.5 0 0 0 .294.456l6.5 3a.5.5 0 0 0 .412 0l6.5-3a.5.5 0 0 0 .294-.456V5.09l-6.289-3.043ZM8 3.046 2.662 5.5 8 7.954 13.338 5.5 8 3.046ZM4.5 7.01l3.5 1.617 3.5-1.617v4.457l-3.5 1.617-3.5-1.617V7.01Z"/>
                            </svg>
                            <h6>Academic Programs</h6>
                            <p class="small text-muted">Explore our degree programs and courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 bg-light text-center">
                        <div class="card-body">
                            <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-calendar-event mb-3" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5 0zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            <h6>Events & News</h6>
                            <p class="small text-muted">Stay updated with campus events</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 bg-light text-center">
                        <div class="card-body">
                            <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-people mb-3" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                            </svg>
                            <h6>Student Services</h6>
                            <p class="small text-muted">Support and resources for students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 bg-light text-center">
                        <div class="card-body">
                            <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-envelope mb-3" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                            </svg>
                            <h6>Contact Us</h6>
                            <p class="small text-muted">Get in touch with our campus</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Posts -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <h3 class="text-center text-primary mb-4">Recent Posts</h3>
            <div class="row">
                <?php
                $recent_posts = get_campus_posts(4);
                if (!empty($recent_posts)):
                    foreach ($recent_posts as $post):
                ?>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h6>
                                <p class="card-text small text-muted">
                                    <?php echo get_excerpt($post['content'], 80); ?>
                                </p>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No recent posts available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
