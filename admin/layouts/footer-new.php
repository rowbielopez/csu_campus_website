            </main>
            
            <!-- Footer -->
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">
                            Copyright &copy; <?php echo date('Y'); ?> CSU CMS Platform
                            <?php if (isset($current_campus) && $current_campus): ?>
                                - <?php echo htmlspecialchars($current_campus['name']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-md-end small">
                            <a href="#" class="text-muted">Privacy Policy</a>
                            &middot;
                            <a href="#" class="text-muted">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- JavaScript Files with Absolute URLs -->
    <?php foreach ($admin_js_files as $js_file): ?>
        <script src="<?php echo admin_asset_path($js_file); ?>" crossorigin="anonymous"></script>
    <?php endforeach; ?>
    
    <!-- Initialize Feather Icons -->
    <script>
        // Wait for DOM and Feather to be ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    
    <!-- Custom Admin Scripts -->
    <script>
        // Initialize DataTables
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple && typeof simpleDatatables !== 'undefined') {
                new simpleDatatables.DataTable(datatablesSimple);
            }
        });
        
        // Flash message auto-hide
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert.auto-dismiss');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.classList.remove('show');
                    setTimeout(function() {
                        alert.remove();
                    }, 150);
                }, 5000);
            });
        });
        
        // Confirm delete actions
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const confirmMessage = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
                    if (confirm(confirmMessage)) {
                        window.location.href = this.href;
                    }
                });
            });
        });
        
        // Auto-save functionality (for forms with .auto-save class)
        document.addEventListener('DOMContentLoaded', function() {
            const autoSaveForms = document.querySelectorAll('form.auto-save');
            autoSaveForms.forEach(function(form) {
                let saveTimeout;
                const inputs = form.querySelectorAll('input, textarea, select');
                
                inputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        clearTimeout(saveTimeout);
                        saveTimeout = setTimeout(function() {
                            // Auto-save logic here
                            console.log('Auto-saving form...');
                        }, 2000);
                    });
                });
            });
        });
        
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidenav-toggled');
                    localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sidenav-toggled'));
                });
            }
        });
    </script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($page_scripts) && is_array($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo admin_asset_path($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?php echo $inline_scripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>
