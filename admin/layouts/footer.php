            </main>
            
            <!-- Footer -->
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">
                            Copyright &copy; <?php echo date('Y'); ?> CSU CMS Platform
                            <?php if ($current_campus): ?>
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
    
    <!-- Core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../dist/js/scripts.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js" crossorigin="anonymous"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    
    <!-- Litepicker -->
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-info-circle me-2 toast-icon"></i>
                <strong class="me-auto toast-title">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body toast-message">
                Default toast message
            </div>
        </div>
    </div>
    
    <!-- Initialize Feather Icons -->
    <script>
        feather.replace();
    </script>
    
    <!-- Custom Admin Scripts -->
    <script>
        // Toast Notification System
        function showToast(message, type = 'info', duration = 5000) {
            const toast = document.getElementById('liveToast');
            const toastIcon = toast.querySelector('.toast-icon');
            const toastTitle = toast.querySelector('.toast-title');
            const toastMessage = toast.querySelector('.toast-message');
            const toastHeader = toast.querySelector('.toast-header');
            
            // Configure toast based on type
            switch(type) {
                case 'success':
                    toastIcon.className = 'fas fa-check-circle me-2 toast-icon text-success';
                    toastTitle.textContent = 'Success';
                    toastHeader.className = 'toast-header bg-success-subtle border-success';
                    break;
                case 'error':
                case 'danger':
                    toastIcon.className = 'fas fa-exclamation-circle me-2 toast-icon text-danger';
                    toastTitle.textContent = 'Error';
                    toastHeader.className = 'toast-header bg-danger-subtle border-danger';
                    break;
                case 'warning':
                    toastIcon.className = 'fas fa-exclamation-triangle me-2 toast-icon text-warning';
                    toastTitle.textContent = 'Warning';
                    toastHeader.className = 'toast-header bg-warning-subtle border-warning';
                    break;
                case 'info':
                default:
                    toastIcon.className = 'fas fa-info-circle me-2 toast-icon text-info';
                    toastTitle.textContent = 'Info';
                    toastHeader.className = 'toast-header bg-info-subtle border-info';
                    break;
            }
            
            toastMessage.textContent = message;
            
            // Create and show toast
            const bsToast = new bootstrap.Toast(toast, {
                delay: duration
            });
            bsToast.show();
        }
        
        // Initialize DataTables
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
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
    </script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($page_scripts) && is_array($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?php echo $inline_scripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>
