<?php
/**
 * Base Controller
 * Provides common functionality for all controllers
 */
class BaseController {
    protected $db;
    protected $user;
    
    public function __construct() {
        // Initialize database connection
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../config/config.php';
        }
        require_once __DIR__ . '/../classes/Database.php';
        $this->db = Database::getInstance();
        
        // Initialize user data if logged in
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * Render a view with data
     */
    protected function render($view, $data = []) {
        // Extract data to make variables available in view
        error_log("Rendering view: {$view} with data keys: " . implode(', ', array_keys($data)));
        extract($data);
        
        // Get the view file path
        $view_file = "modules/" . strtolower(str_replace("Controller", "", get_class($this))) . "/views/{$view}.php";
        error_log("View file path: {$view_file}");
        
        if (!file_exists($view_file)) {
            error_log("View file not found: {$view_file}");
            throw new Exception("View file not found: {$view_file}");
        }
        
        // Include the view file
        include $view_file;
    }
    
    /**
     * Redirect to another page
     */
    protected function redirect($url) {
        if (ob_get_length()) ob_clean();
        header("Location: {$url}");
        exit();
    }
    
    /**
     * Send JSON response
     */
    protected function json($data, $status = 200) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
    
    /**
     * Get POST data with validation
     */
    protected function getPostData($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }
    
    /**
     * Get GET data with validation
     */
    protected function getQueryData($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole($role) {
        return $this->isAuthenticated() && $_SESSION['user_type'] === $role;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get flash message and clear it
     */
    protected function getFlash() {
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Database transaction helpers
     */
    protected function beginTransaction() {
        $this->db->beginTransaction();
    }
    
    protected function commit() {
        $this->db->commit();
    }
    
    protected function rollback() {
        $this->db->rollBack();
    }
    
    /**
     * Log error
     */
    protected function logError($message, $context = []) {
        $log_message = date('Y-m-d H:i:s') . " - " . $message;
        if (!empty($context)) {
            $log_message .= " - Context: " . json_encode($context);
        }
        error_log($log_message);
    }
}
