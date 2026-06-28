<?php
/**
 * Simple Logout Script
 */

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log session data before logout
error_log("Simple logout script called");
error_log("Session data before logout: " . print_r($_SESSION, true));

// Clear all session variables
$_SESSION = array();
error_log("Session variables cleared");

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
    error_log("Session cookie removed");
}

// Destroy the session
$result = session_destroy();
error_log("Session destroy result: " . ($result ? 'true' : 'false'));

// Start a new session to set the success message
session_start();
error_log("New session started");

$_SESSION['success'] = "You have been successfully logged out.";
error_log("Success message set in new session");

// Redirect to home page
header("Location: index.php");
exit; 