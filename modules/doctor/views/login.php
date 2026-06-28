<?php
/**
 * Doctor Login View
 * Palliative Care System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - Palliative Care System</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-md fa-3x text-success mb-3"></i>
                            <h2>Doctor Portal</h2>
                            <p class="text-muted">Access your patient records and appointments</p>
                        </div>

                        <?php 
                        if (function_exists('displayFlashMessage')) {
                            displayFlashMessage();
                        }
                        ?>

                        <form action="<?php echo SITE_URL; ?>index.php?module=auth&action=login" method="post" class="needs-validation">
                            <input type="hidden" name="user_type" value="doctor">
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           placeholder="Enter your email">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           placeholder="Enter your password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                    <label class="custom-control-label" for="remember">Remember me</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-block btn-lg">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </button>

                            <div class="text-center mt-4">
                                <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=forgot_password" class="text-muted">
                                    Forgot your password?
                                </a>
                            </div>

                            <hr class="my-4">

                            <div class="text-center">
                                <p class="text-muted mb-0">Don't have an account?</p>
                                <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=register&type=doctor" class="font-weight-bold">
                                    Register as Doctor
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>" class="text-muted">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
