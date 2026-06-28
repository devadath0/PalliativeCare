<?php
/**
 * Email Notification System Setup Script
 * This script will set up the required dependencies for the email notification system
 */

// Check if Composer is installed
echo "Checking for Composer...\n";

$composerInstalled = false;
exec('composer --version', $output, $returnCode);
if ($returnCode === 0) {
    echo "Composer is installed. Version: " . $output[0] . "\n";
    $composerInstalled = true;
} else {
    echo "Composer is not installed. Please install Composer first.\n";
    echo "Visit https://getcomposer.org/download/ for installation instructions.\n";
    exit(1);
}

// Create composer.json if it doesn't exist
if (!file_exists('composer.json')) {
    echo "Creating composer.json file...\n";
    $composerJson = [
        "name" => "palliative/care-system",
        "description" => "Palliative Care System",
        "type" => "project",
        "require" => [
            "phpmailer/phpmailer" => "^6.8"
        ],
        "autoload" => [
            "classmap" => ["classes/"]
        ]
    ];
    
    file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "composer.json file created.\n";
} else {
    echo "composer.json file already exists. Updating...\n";
    $composerJson = json_decode(file_get_contents('composer.json'), true);
    if (!isset($composerJson['require'])) {
        $composerJson['require'] = [];
    }
    $composerJson['require']['phpmailer/phpmailer'] = "^6.8";
    file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "composer.json file updated.\n";
}

// Run composer install to install PHPMailer
echo "Installing dependencies with Composer...\n";
exec('composer install', $output, $returnCode);
if ($returnCode === 0) {
    echo "Dependencies installed successfully.\n";
} else {
    echo "Failed to install dependencies. Error output:\n";
    echo implode("\n", $output);
    exit(1);
}

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    echo "Creating logs directory...\n";
    mkdir('logs', 0755);
    echo "Logs directory created.\n";
} else {
    echo "Logs directory already exists.\n";
}

// Check and update the config file
echo "Checking config file...\n";
$configFile = 'config/config.php';
if (!file_exists($configFile)) {
    echo "Error: Config file not found at {$configFile}.\n";
    exit(1);
}

$configContents = file_get_contents($configFile);
if (strpos($configContents, 'MAIL_ENABLED') === false) {
    echo "Email configuration not found in config file. Please run the application setup first.\n";
    exit(1);
}

echo "\nEmail notification system setup completed successfully!\n";
echo "To enable the email notification system, you need to:\n";
echo "1. Edit the 'config/config.php' file\n";
echo "2. Set MAIL_ENABLED to true\n";
echo "3. Update the SMTP settings with your email server details\n";
echo "\nExample configuration:\n";
echo "define('MAIL_ENABLED', true);\n";
echo "define('MAIL_HOST', 'smtp.gmail.com');\n";
echo "define('MAIL_PORT', 587);\n";
echo "define('MAIL_ENCRYPTION', 'tls');\n";
echo "define('MAIL_USERNAME', 'your.email@gmail.com');\n";
echo "define('MAIL_PASSWORD', 'your-app-password');\n";
echo "define('MAIL_FROM', 'your.email@gmail.com');\n";
echo "define('MAIL_FROM_NAME', 'Palliative Care System');\n";
echo "define('MAIL_REPLY_TO', 'support@yourcompany.com');\n";
?> 