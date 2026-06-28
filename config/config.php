<?php
/**
 * Application Configuration
 * Define all necessary constants and settings
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'palliative');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application paths
define('SITE_URL', 'http://localhost/palliative/');
define('SITE_PATH', dirname(__DIR__) . '/');
define('SITE_NAME', 'Palliative Care System');

// Email Configuration
define('MAIL_ENABLED', true); // Set to true when SMTP credentials are configured
define('MAIL_HOST', 'smtp.gmail.com'); // SMTP server
define('MAIL_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('MAIL_USERNAME', 'smtppalliativecare@gmail.com'); // Your SMTP email
define('MAIL_PASSWORD', 'ajlf iyny yijb xidr'); // 16-character App Password from Google
define('MAIL_FROM', 'smtppalliativecare@gmail.com'); // From email
define('MAIL_FROM_NAME', 'Palliative Care System'); // Display name
define('MAIL_REPLY_TO', 'smtppalliativecare@gmail.com'); // Reply-to email

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour
session_start();

// Timezone
date_default_timezone_set('UTC');

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}); 