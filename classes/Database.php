<?php
/**
 * Database Configuration
 * Handles database connection and provides singleton instance
 */
class Database {
    private static $instance = null;
    private $connection = null;

    private function __construct() {
        try {
            // Include config file if constants are not defined
            if (!defined('DB_HOST')) {
                require_once dirname(__DIR__) . '/config/config.php';
            }

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            error_log("Database connection established successfully");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Could not connect to the database. Please try again later.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    // Prevent cloning of the instance
    public function __clone() {
        throw new Exception("Cloning of Database instance is not allowed");
    }

    // Prevent unserializing of the instance
    public function __wakeup() {
        throw new Exception("Unserializing of Database instance is not allowed");
    }
} 