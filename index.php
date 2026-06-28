<?php
define('BASE_PATH', __DIR__);
ob_start(); // Start output buffering
/**
 * Main Entry Point
 * Palliative Care System
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}

// Check if service_requests table needs fixing
try {
    // Initialize database connection
    require_once 'classes/Database.php';
    $db = Database::getInstance();
    
    // Check for service_provider_id column in service_requests
    $stmt = $db->query("SHOW COLUMNS FROM service_requests LIKE 'service_provider_id'");
    if ($stmt->rowCount() == 0) {
        // Check if provider_id column exists
        $stmt = $db->query("SHOW COLUMNS FROM service_requests LIKE 'provider_id'");
        if ($stmt->rowCount() > 0) {
            // Rename the column
            $db->exec("ALTER TABLE service_requests CHANGE COLUMN provider_id service_provider_id INT NOT NULL");
            error_log("Renamed provider_id to service_provider_id in service_requests table");
        }
    }
    
    // Check for request_type column in service_requests
    $stmt = $db->query("SHOW COLUMNS FROM service_requests LIKE 'request_type'");
    if ($stmt->rowCount() == 0) {
        // Add the column and set default values
        $db->exec("ALTER TABLE service_requests ADD COLUMN request_type VARCHAR(50) NOT NULL DEFAULT 'medicine_delivery' AFTER service_provider_id");
        $db->exec("UPDATE service_requests SET request_type = 'medicine_delivery' WHERE service_type = 'medicine'");
        error_log("Added request_type column to service_requests table");
    }
    
} catch (Exception $e) {
    error_log("Error fixing service_requests table: " . $e->getMessage());
}

// Get module and action from URL
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'index';

// If no module or empty module is specified, show the landing page
if (empty($module)) {
    include 'landing_page.php';
    exit;
} else {
    // Validate module name for security
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $module)) {
        error_log("Invalid module name attempted: " . $module);
        http_response_code(400);
        die('Invalid module name');
    }

    // Define allowed modules and their controllers with required authentication
    $allowed_modules = [
        'auth' => ['controller' => 'AuthController', 'auth_required' => false],
        'admin' => ['controller' => 'AdminController', 'auth_required' => true, 'role' => 'admin'],
        'doctor' => ['controller' => 'DoctorController', 'auth_required' => true, 'role' => 'doctor'],
        'patient' => ['controller' => 'PatientController', 'auth_required' => true, 'role' => 'patient'],
        'service' => ['controller' => 'ServiceController', 'auth_required' => true, 'role' => 'service'],
        'contact' => ['controller' => 'ContactController', 'auth_required' => false],
        'generate_report' => ['controller' => 'AdminController', 'auth_required' => true, 'role' => 'admin']
    ];

    // Check if module is allowed
    if (!array_key_exists($module, $allowed_modules)) {
        http_response_code(404);
        die('Module not found');
    }

    // Special case for logout action
    if ($module === 'auth' && $action === 'logout') {
        error_log("Special case for logout action detected");
        try {
            require_once 'modules/base_controller.php';
            require_once 'modules/auth/controller.php';
            $controller = new AuthController();
            error_log("AuthController created for logout action");
            $controller->logout();
        } catch (Exception $e) {
            error_log("Error in logout action: " . $e->getMessage());
            // Fallback to direct logout
            header("Location: logout.php");
        }
        exit;
    }

    // Check authentication if required
    if ($allowed_modules[$module]['auth_required']) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            // Don't redirect to login if we're already on a login page
            if ($module !== 'auth' || $action !== 'login') {
                $_SESSION['error'] = "Please log in to access this page.";
                header('Location: index.php?module=auth&action=login&type=' . $allowed_modules[$module]['role']);
                exit();
            }
        } else {
            // Check role authorization
            if ($_SESSION['user_type'] !== $allowed_modules[$module]['role']) {
                // If wrong role but authenticated, redirect to appropriate dashboard
                $_SESSION['error'] = "You don't have permission to access this page.";
                header('Location: index.php?module=' . $_SESSION['user_type'] . '&action=dashboard');
                exit();
            }
        }
    } else if ($module === 'auth' && $action === 'login' && isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        // If already logged in and trying to access login page, redirect to appropriate dashboard
        header('Location: index.php?module=' . $_SESSION['user_type'] . '&action=dashboard');
        exit();
    }

    // Include the controller file
    $controller_file = "modules/{$module}/controller.php";
    if (!file_exists($controller_file)) {
        http_response_code(500);
        die('Controller not found');
    }

    // Include base controller for common functionality
    require_once 'modules/base_controller.php';
    require_once $controller_file;

    // Create controller instance
    $controller_class = $allowed_modules[$module]['controller'];
    $controller = new $controller_class();

    // Check if method exists and is callable
    if (!method_exists($controller, $action) || !is_callable([$controller, $action])) {
        error_log("Action not found: module={$module}, action={$action}, controller_class={$controller_class}");
        http_response_code(404);
        die('Action not found: ' . $action);
    }

    // Execute the action
    try {
        error_log("Executing action: module={$module}, action={$action}, controller_class={$controller_class}");
        $controller->$action();
    } catch (Exception $e) {
        error_log("Error in {$module}/{$action}: " . $e->getMessage());
        http_response_code(500);
        die('An error occurred. Please try again later. ' . $e->getMessage());
    }
}

// Get the current buffer contents and clean it
$content = ob_get_clean();

// If no content was generated, show the landing page
if (empty($content)) {
    ob_start();
    include 'landing_page.php';
    $content = ob_get_clean();
}

// Output the content
echo $content;