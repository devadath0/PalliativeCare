<?php
/**
 * Password Reset View
 * Palliative Care System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css" type="text/css">
    <!-- Add any additional CSS libraries here -->
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h2 class="auth-title">Reset Your Password</h2>
                
                <?php 
                // Display flash messages
                if (function_exists('displayFlashMessage')) {
                    displayFlashMessage();
                }
                ?>
                
                <form action="<?php echo SITE_URL; ?>modules/auth.php?action=reset_password" method="post" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                        <small class="form-text text-muted">Enter the email address associated with your account. We'll send you a link to reset your password.</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                    </div>
                    
                    <div class="auth-links">
                        <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" type="text/javascript"></script>
</body>
</html>
