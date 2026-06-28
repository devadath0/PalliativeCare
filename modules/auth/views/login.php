<?php
/**
 * Login View
 * Palliative Care System
 */

// Debug information
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get user type from URL parameter or passed variable
$userType = isset($userType) ? $userType : (isset($_GET['type']) ? $_GET['type'] : '');

// Validate user type
$validTypes = ['patient', 'doctor', 'service', 'admin'];
if (!in_array($userType, $validTypes)) {
    $_SESSION['error'] = "Invalid user type";
    header("Location: index.php");
    exit;
}

// Get page title based on user type
$pageTitle = match($userType) {
    'patient' => 'Patient Login',
    'doctor' => 'Doctor Login',
    'service' => 'Service Provider Login',
    'admin' => 'Administrator Login',
    default => 'Login'
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <?php
                            $icon = match($userType) {
                                'patient' => 'user-injured',
                                'doctor' => 'user-md',
                                'service' => 'building',
                                'admin' => 'user-shield',
                                default => 'user-circle'
                            };
                            ?>
                            <i class="fas fa-<?php echo $icon; ?> fa-3x text-primary"></i>
                            <h2 class="mt-3"><?php echo $pageTitle; ?></h2>
                            <p class="text-muted">Welcome back! Please login to your account.</p>
                        </div>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=<?php echo htmlspecialchars($userType); ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           autocomplete="email" placeholder="Enter your email">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           autocomplete="current-password" placeholder="Enter your password">
                                    <div class="invalid-feedback">Please enter your password.</div>
                                </div>
                            </div>
                            
                            <div class="row justify-content-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                            <br>
                            <div class="text-center">
                                <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=reset_password" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i> Forgot Password?
                                </a>
                                <span class="mx-2">|</span>
                                <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=<?php echo $userType === 'admin' ? 'admin_register' : 'register'; ?>&type=<?php echo htmlspecialchars($userType); ?>" class="text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i> Create Account
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>index.php" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    </script>
</body>
</html>
