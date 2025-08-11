<?php
/**
 * About Page
 * Campus information and overview
 */

// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();
$page_title = 'About ' . $campus['name'];
$page_description = 'Learn more about ' . $campus['full_name'] . ' and our academic programs.';

include 'layouts/header.php';
?>

<!-- Breadcrumbs -->
<div class="container py-3">
    <?php 
    $breadcrumbs = [
        ['title' => 'About', 'url' => '']
    ];
    echo get_breadcrumbs($breadcrumbs); 
    ?>
</div>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">
                    About <?php echo htmlspecialchars($campus['name']); ?>
                </h1>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container my-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Campus Overview -->
            <section class="mb-5">
                <h2 class="text-primary mb-4">Campus Overview</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body text-center">
                                <svg width="48" height="48" fill="var(--campus-primary)" class="bi bi-geo-alt mb-3" viewBox="0 0 16 16">
                                    <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                                    <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                </svg>
                                <h5 class="card-title">Location</h5>
                                <p class="card-text"><?php echo htmlspecialchars($campus['address']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body text-center">
                                <svg width="48" height="48" fill="var(--campus-primary)" class="bi bi-envelope mb-3" viewBox="0 0 16 16">
                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                </svg>
                                <h5 class="card-title">Contact</h5>
                                <p class="card-text">
                                    <a href="mailto:<?php echo $campus['contact_email']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($campus['contact_email']); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mission & Vision -->
            <section class="mb-5">
                <h2 class="text-primary mb-4">Mission & Vision</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Our Mission</h5>
                                <p class="card-text">
                                    To provide quality education, conduct relevant research, and extend community services 
                                    that promote sustainable development and improve the quality of life in the region.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Our Vision</h5>
                                <p class="card-text">
                                    To be a premier state university in the Philippines, recognized for excellence in 
                                    education, research, and community engagement.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Academic Programs -->
            <section class="mb-5">
                <h2 class="text-primary mb-4">Academic Programs</h2>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-mortarboard mb-3" viewBox="0 0 16 16">
                                    <path d="M8.211 2.047a.5.5 0 0 0-.422 0L1.5 5.09v1.567a.5.5 0 0 0 .294.456l6.5 3a.5.5 0 0 0 .412 0l6.5-3a.5.5 0 0 0 .294-.456V5.09l-6.289-3.043ZM8 3.046 2.662 5.5 8 7.954 13.338 5.5 8 3.046ZM4.5 7.01l3.5 1.617 3.5-1.617v4.457l-3.5 1.617-3.5-1.617V7.01Z"/>
                                </svg>
                                <h6>Undergraduate</h6>
                                <p class="small">Bachelor's degree programs across various disciplines</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-journal-bookmark mb-3" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M6 8V1h1v6.117L8.743 6.07a.5.5 0 0 1 .514 0L11 7.117V1h1v7a.5.5 0 0 1-.757.429L9 7.083 6.757 8.43A.5.5 0 0 1 6 8z"/>
                                    <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                                    <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                                </svg>
                                <h6>Graduate</h6>
                                <p class="small">Master's and doctoral programs for advanced studies</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <svg width="32" height="32" fill="var(--campus-primary)" class="bi bi-search mb-3" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                                <h6>Research</h6>
                                <p class="small">Research programs and innovation initiatives</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Campus History -->
            <section class="mb-5">
                <h2 class="text-primary mb-4">Campus History</h2>
                <div class="card">
                    <div class="card-body">
                        <p>
                            <?php echo htmlspecialchars($campus['full_name']); ?> has been serving the educational needs 
                            of the community for many years. As part of the Cagayan State University system, we are 
                            committed to providing quality education that meets the highest academic standards.
                        </p>
                        <p>
                            Our campus has grown from a small educational institution to a comprehensive university 
                            offering diverse academic programs. We continue to evolve and adapt to meet the changing 
                            needs of our students and the community.
                        </p>
                        <p>
                            Through the years, we have maintained our commitment to excellence in teaching, research, 
                            and community service, making us a vital part of the educational landscape in the region.
                        </p>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <aside class="sidebar">
                <!-- Quick Facts -->
                <div class="widget mb-4">
                    <h5 class="widget-title">Quick Facts</h5>
                    <div class="widget-content">
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Campus:</strong> <?php echo htmlspecialchars($campus['name']); ?></li>
                            <li class="mb-2"><strong>Location:</strong> <?php echo htmlspecialchars($campus['address']); ?></li>
                            <li class="mb-2"><strong>Domain:</strong> <?php echo htmlspecialchars($campus['domain']); ?></li>
                            <li class="mb-2"><strong>Email:</strong> 
                                <a href="mailto:<?php echo $campus['contact_email']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($campus['contact_email']); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Latest News -->
                <div class="widget mb-4">
                    <h5 class="widget-title">Latest News</h5>
                    <div class="widget-content">
                        <?php
                        $recent_posts = get_campus_posts(3);
                        if (!empty($recent_posts)):
                        ?>
                            <ul class="list-unstyled">
                                <?php foreach ($recent_posts as $post): ?>
                                    <li class="mb-3 pb-3 border-bottom">
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                           class="text-decoration-none">
                                            <div class="fw-semibold text-dark mb-1">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </div>
                                        </a>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent news available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="widget mb-4">
                    <h5 class="widget-title">Contact Information</h5>
                    <div class="widget-content">
                        <p><strong><?php echo htmlspecialchars($campus['full_name']); ?></strong></p>
                        <p class="mb-2"><?php echo htmlspecialchars($campus['address']); ?></p>
                        <p class="mb-0">
                            Email: <a href="mailto:<?php echo $campus['contact_email']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($campus['contact_email']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
