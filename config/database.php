<?php
/**
 * Database Connection
 * Creates and returns a PDO database connection
 */

// Include configuration if not already included
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $db;
} catch (PDOException $e) {
    // Log the error but don't expose details to users
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please try again later or contact support.");
} 