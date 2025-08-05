<?php
/**
 * Main Public Site Homepage
 * Fallback public site for CMS
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cagayan State University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 100px 0;
        }
        .campus-card {
            transition: transform 0.3s ease;
        }
        .campus-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-university me-2"></i>
                Cagayan State University
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin/">Admin Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Welcome to Cagayan State University</h1>
            <p class="lead mb-5">Choose your campus to explore our programs and services</p>
        </div>
    </section>

    <!-- Campus Selection -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Select Your Campus</h2>
            <div class="row">
                <?php
                $campuses = [
                    ['code' => 'andrews', 'name' => 'Andrews Campus'],
                    ['code' => 'aparri', 'name' => 'Aparri Campus'],
                    ['code' => 'carig', 'name' => 'Carig Campus'],
                    ['code' => 'gonzaga', 'name' => 'Gonzaga Campus'],
                    ['code' => 'lallo', 'name' => 'Lallo Campus'],
                    ['code' => 'lasam', 'name' => 'Lasam Campus'],
                    ['code' => 'piat', 'name' => 'Piat Campus'],
                    ['code' => 'sanchezmira', 'name' => 'Sanchez Mira Campus'],
                    ['code' => 'solana', 'name' => 'Solana Campus']
                ];

                foreach ($campuses as $campus):
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card campus-card h-100 text-center">
                        <div class="card-body d-flex flex-column">
                            <div class="campus-logo mb-3">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white" 
                                     style="width: 80px; height: 80px; font-size: 1.5rem; font-weight: bold;">
                                    <?php echo strtoupper(substr($campus['code'], 0, 3)); ?>
                                </div>
                            </div>
                            <h5 class="card-title"><?php echo $campus['name']; ?></h5>
                            <div class="mt-auto">
                                <?php 
                                $site_url = "{$campus['code']}/public/";
                                if (is_dir(__DIR__ . "/{$campus['code']}/public/")) {
                                    echo "<a href='$site_url' class='btn btn-primary'>Visit Campus Site</a>";
                                } else {
                                    echo "<span class='text-muted'>Coming Soon</span>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 Cagayan State University. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
