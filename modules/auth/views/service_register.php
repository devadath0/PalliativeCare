<?php 
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(dirname(__FILE__) . '/../../../'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/auth.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet" type="text/css">
    <style>
        /* Fallback styles in case external CSS fails to load */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            margin-bottom: 20px;
            border: none;
        }
        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #004494;
            border-color: #003d82;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 8px 0 0 8px;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 86, 179, 0.25);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .text-primary {
            color: #0056b3 !important;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .password-strength-meter {
            height: 5px;
            background-color: #eee;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-meter div {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
        .password-strength-meter.weak div {
            width: 25%;
            background-color: #dc3545;
        }
        .password-strength-meter.medium div {
            width: 50%;
            background-color: #ffc107;
        }
        .password-strength-meter.strong div {
            width: 75%;
            background-color: #28a745;
        }
        .password-strength-meter.very-strong div {
            width: 100%;
            background-color: #20c997;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card">
                <div class="card-header text-center">
                    <i class="fas fa-hospital-user fa-3x text-primary mb-3"></i>
                    <h2 class="mb-1">Service Provider Registration</h2>
                    <p class="text-muted">Join our network of healthcare service providers</p>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo SITE_URL; ?>index.php?module=auth&action=process_service_register" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name" class="form-label">Company Name*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_type" class="form-label">Service Type*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-briefcase-medical"></i></span>
                                        <select class="form-control" id="service_type" name="service_type" required>
                                            <option value="">Select Service Type</option>
                                            <option value="pharmacy">Pharmacy</option>
                                            <option value="equipment">Medical Equipment</option>
                                            <option value="transportation">Transportation</option>
                                            <option value="homecare">Home Care</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="form-label">Business Address*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="operating_hours" class="form-label">Operating Hours*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <input type="text" class="form-control" id="operating_hours" name="operating_hours" 
                                               placeholder="e.g. Mon-Fri: 9AM-5PM" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_area" class="form-label">Service Area*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="text" class="form-control" id="service_area" name="service_area" 
                                               placeholder="e.g. Greater Toronto Area" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="license_number" class="form-label">License Number*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="license_number" name="license_number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">Password* (minimum 8 characters)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" minlength="8" required autocomplete="new-password">
                                    </div>
                                    <div class="password-strength-meter mt-2">
                                        <div></div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Password must be at least 8 characters long
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm Password*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-2"></i> Register as Service Provider
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=service" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i> Already have an account? Login
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?php echo SITE_URL; ?>assets/js/main.js" type="text/javascript"></script>

<!-- Fallback inline JavaScript in case external JS fails to load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Service registration page loaded');
    
    // Add autocomplete attributes to password fields
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(function(field) {
        field.setAttribute('autocomplete', 'new-password');
    });
    
    // Password confirmation validation
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm_password');
    
    if (password && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
        
        password.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            // Update password strength meter
            updatePasswordStrength(password.value);
        });
    }
    
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Password strength meter
    function updatePasswordStrength(password) {
        const meter = document.querySelector('.password-strength-meter');
        if (!meter) return;
        
        // Remove all classes
        meter.classList.remove('weak', 'medium', 'strong', 'very-strong');
        
        // Calculate strength
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;
        
        // Add appropriate class
        if (strength === 0) {
            // No class
        } else if (strength === 1) {
            meter.classList.add('weak');
        } else if (strength === 2) {
            meter.classList.add('medium');
        } else if (strength === 3) {
            meter.classList.add('strong');
        } else {
            meter.classList.add('very-strong');
        }
    }
});
</script>
</body>
</html>
