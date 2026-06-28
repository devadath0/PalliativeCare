<?php
/**
 * Login View
 * Palliative Care System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css" type="text/css">
    <!-- Add any additional CSS libraries here -->
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h2 class="auth-title">Login to <?php echo SITE_NAME; ?></h2>
                
                <?php 
                // Display flash messages
                if (function_exists('displayFlashMessage')) {
                    displayFlashMessage();
                }
                ?>
                
                <form action="<?php echo SITE_URL; ?>modules/auth.php?action=login" method="post" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                    
                    <div class="auth-links">
                        <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=reset_password">Forgot Password?</a>
                        <span class="separator">|</span>
                        <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=register">Create an Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" type="text/javascript"></script>
</body>
</html>
