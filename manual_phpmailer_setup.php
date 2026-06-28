<?php
/**
 * Manual PHPMailer Setup Script
 * Use this if Composer installation fails
 */

// Create directories
$directories = [
    'vendor',
    'vendor/phpmailer',
    'vendor/phpmailer/phpmailer',
    'vendor/phpmailer/phpmailer/src'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir<br>";
    }
}

// GitHub URLs for PHPMailer files
$files = [
    'src/PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'src/SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'src/Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

// Download files
foreach ($files as $path => $url) {
    $fullPath = 'vendor/phpmailer/phpmailer/' . $path;
    $content = file_get_contents($url);
    
    if ($content === false) {
        echo "Failed to download: $url<br>";
        continue;
    }
    
    file_put_contents($fullPath, $content);
    echo "Downloaded: $path<br>";
}

// Create autoload file
$autoloadContent = '<?php
// PHPMailer autoloader
spl_autoload_register(function ($class) {
    // PHPMailer uses the namespace prefix
    $prefix = \'PHPMailer\\\\PHPMailer\\\\\';
    $base_dir = __DIR__ . \'/phpmailer/phpmailer/src/\';
    
    // Only handle PHPMailer namespaced classes
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Remove the prefix from the class name
    $class = substr($class, $len);
    
    // Base directory for the namespace prefix
    $file = $base_dir . str_replace(\'\\\\\', \'/\', $class) . \'.php\';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});';

file_put_contents('vendor/autoload.php', $autoloadContent);
echo "Created autoload.php<br>";

echo "<br>Manual PHPMailer setup complete! Try running your email test now.";
?> 