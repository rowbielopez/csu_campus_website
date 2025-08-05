<?php
/**
 * Main Admin Dashboard - Simplified & Streamlined
 * Clean layout with essential elements only
 */

// Define admin access
define('ADMIN_ACCESS', true);

// Load core authentication
require_once __DIR__ . '/../core/middleware/auth.php';
require_once __DIR__ . '/../core/functions/auth.php';

// Get current user info
$current_user = get_logged_in_user();
$current_campus = get_current_campus();

$page_title = 'Dashboard';
$page_description = 'CSU CMS Admin Dashboard - Quick Overview & Actions';

include __DIR__ . '/layouts/header-new.php';
?>

<!-- Main Dashboard Content -->
<div class="container-xl px-4 mt-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4">
        <div class="me-4 mb-3 mb-sm-0">
            <h1 class="mb-0">Dashboard</h1>
            <div class="small">
                <span class="fw-500 text-primary"><?php echo date('l, F j, Y'); ?></span>
                · Welcome back, <?php echo htmlspecialchars($current_user['first_name'] ?? $current_user['username']); ?>
            </div>
        </div>
        <div class="ms-auto">
            <a class="btn btn-primary" href="posts/create.php">
                <i class="me-1" data-feather="plus"></i>
                New Post
            </a>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Total Posts</div>
                            <div class="text-lg fw-bold">247</div>
                        </div>
                        <i class="fas fa-edit fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="posts/">View All Posts</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Active Users</div>
                            <div class="text-lg fw-bold">32</div>
                        </div>
                        <i class="fas fa-users fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="users/">Manage Users</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Media Files</div>
                            <div class="text-lg fw-bold">1,489</div>
                        </div>
                        <i class="fas fa-images fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="media/">Media Library</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">Page Views</div>
                            <div class="text-lg fw-bold">45.2K</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="analytics.php">View Analytics</a>
                    <div class="text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="me-2" data-feather="zap"></i>
            Quick Actions
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="posts/create.php" class="btn btn-lg btn-outline-primary w-100 py-3">
                        <i class="fas fa-plus mb-2 d-block fa-2x"></i>
                        Create Post
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="media/upload.php" class="btn btn-lg btn-outline-success w-100 py-3">
                        <i class="fas fa-upload mb-2 d-block fa-2x"></i>
                        Upload Media
                    </a>
                </div>
                <?php if (is_campus_admin() || is_super_admin()): ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="users/create.php" class="btn btn-lg btn-outline-warning w-100 py-3">
                        <i class="fas fa-user-plus mb-2 d-block fa-2x"></i>
                        Add User
                    </a>
                </div>
                <?php endif; ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <?php 
                    $campus_info = get_current_campus();
                    $campus_code = $campus_info['code'] ?? null;
                    
                    if ($campus_code) {
                        // Check if campus public site exists
                        $campus_public_dir = dirname(__DIR__) . "/{$campus_code}/public/";
                        if (is_dir($campus_public_dir)) {
                            $site_url = "http://localhost/campus_website2/{$campus_code}/public/";
                            $btn_text = "View Campus Site";
                        } else {
                            $site_url = "http://localhost/campus_website2/";
                            $btn_text = "View Main Site";
                        }
                    } else {
                        $site_url = "http://localhost/campus_website2/";
                        $btn_text = "View Main Site";
                    }
                    ?>
                    <a href="<?php echo $site_url; ?>" target="_blank" class="btn btn-lg btn-outline-info w-100 py-3">
                        <i class="fas fa-external-link-alt mb-2 d-block fa-2x"></i>
                        <?php echo $btn_text; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Monthly Post Activity Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="me-2" data-feather="bar-chart-2"></i>
                    Monthly Post Activity
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyPostChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Role Distribution Chart -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="me-2" data-feather="pie-chart"></i>
                    User Role Distribution
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userRoleChart" width="100%" height="50"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Initialization Scripts -->
<script>
// Monthly Post Activity Bar Chart
const monthlyPostCtx = document.getElementById('monthlyPostChart').getContext('2d');
const monthlyPostChart = new Chart(monthlyPostCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Posts Published',
            data: [12, 19, 8, 15, 22, 18, 25, 20, 16, 24, 14, 18],
            backgroundColor: 'rgba(0, 97, 242, 0.1)',
            borderColor: 'rgba(0, 97, 242, 1)',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [2],
                    borderDashOffset: [2],
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            }
        }
    }
});

// User Role Distribution Pie Chart
const userRoleCtx = document.getElementById('userRoleChart').getContext('2d');
const userRoleChart = new Chart(userRoleCtx, {
    type: 'doughnut',
    data: {
        labels: ['Admins', 'Editors', 'Authors', 'Contributors'],
        datasets: [{
            data: [8, 12, 15, 7],
            backgroundColor: [
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(13, 110, 253, 0.8)'
            ],
            borderColor: [
                'rgba(220, 53, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(25, 135, 84, 1)',
                'rgba(13, 110, 253, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/layouts/footer-new.php'; ?>
