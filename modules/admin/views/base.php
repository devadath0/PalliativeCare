<?php
/**
 * Base Admin View Template
 * This file should be included at the start of all admin views
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Function to get the current module name
function getCurrentModule() {
    return isset($_GET['module']) ? htmlspecialchars($_GET['module']) : 'admin';
}

// Function to get the current action
function getCurrentAction() {
    return isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'dashboard';
}

// Function to check if a nav item is active
function isActiveNav($action) {
    return getCurrentAction() === $action ? 'active' : '';
}
?> 