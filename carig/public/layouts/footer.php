<?php
/**
 * Frontend Footer Layout
 * Dynamic footer with campus-specific information and widgets
 */

$campus = get_campus_config();
$footer_widgets = get_campus_widgets('footer');
?>

    <!-- Footer -->
    <footer class="footer py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Campus Information -->
                <div class="col-lg-4 mb-4">
                    <h5 class="text-primary mb-3"><?php echo htmlspecialchars($campus['name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($campus['full_name']); ?></p>
                    <p class="text-muted">
                        <strong>Address:</strong><br>
                        <?php echo htmlspecialchars($campus['address']); ?>
                    </p>
                    <p class="text-muted">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo $campus['contact_email']; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($campus['contact_email']); ?>
                        </a>
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 mb-4">
                    <h6 class="text-primary mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="posts.php" class="text-muted text-decoration-none">News & Updates</a></li>
                        <li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="../login.php" class="text-muted text-decoration-none">Admin Login</a></li>
                    </ul>
                </div>
                
                <!-- Academic Programs -->
                <div class="col-lg-3 mb-4">
                    <h6 class="text-primary mb-3">Academic Programs</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Undergraduate Programs</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Graduate Programs</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Research Programs</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Extension Services</a></li>
                    </ul>
                </div>
                
                <!-- Footer Widgets -->
                <?php if (!empty($footer_widgets)): ?>
                    <div class="col-lg-3 mb-4">
                        <?php foreach ($footer_widgets as $widget): ?>
                            <?php echo render_widget($widget); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Default Recent Posts Widget -->
                    <div class="col-lg-3 mb-4">
                        <h6 class="text-primary mb-3">Recent Updates</h6>
                        <?php
                        $recent_posts = get_campus_posts(3);
                        if (!empty($recent_posts)):
                        ?>
                            <ul class="list-unstyled">
                                <?php foreach ($recent_posts as $post): ?>
                                    <li class="mb-3">
                                        <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                           class="text-decoration-none">
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($post['title']); ?></div>
                                        </a>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent updates available.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <hr class="my-4">
            
            <!-- Footer Bottom -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($campus['full_name']); ?>. 
                        All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        Powered by <a href="#" class="text-primary text-decoration-none">Campus CMS</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Image lazy loading fallback for older browsers
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const script = document.createElement('script');
            script.src = 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver';
            document.head.appendChild(script);
        }
    </script>
    
    <!-- Additional JS from page -->
    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
</body>
</html>
