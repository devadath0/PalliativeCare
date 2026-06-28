<?php
/**
 * Shared Header
 * Palliative Care System
 */

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php?module=auth&action=login");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['name'] ?? 'User';

error_log("Header session data: " . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Palliative Care System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <?php if (isset($_GET['module']) && $_GET['module'] === 'patient'): ?>
    <!-- Patient Module styles -->
    <link href="assets/css/patient.css" rel="stylesheet">
    
    <!-- Issue reporting styles for issue-related pages -->
    <?php if (isset($_GET['action']) && in_array($_GET['action'], ['report_issue', 'my_issues', 'view_issue'])): ?>
    <link href="assets/css/issue-reporting.css" rel="stylesheet">
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .nav-link {
            color: #333;
        }
        .nav-link:hover {
            color: #007bff;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Palliative Care</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?module=<?php echo $_SESSION['user_type']; ?>&action=dashboard">
                                Dashboard
                            </a>
                        </li>
                        <?php if ($_SESSION['user_type'] === 'patient'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=appointments">
                                    Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=prescriptions">
                                    Prescriptions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=order_medicine">
                                    <i class="fas fa-pills"></i> Order Medicine
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=book_cab">
                                    <i class="fas fa-taxi"></i> Book Cab
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=cab_bookings">
                                    <i class="fas fa-list"></i> My Transport Bookings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=alerts">
                                    <i class="fas fa-bell"></i> Alerts
                                    <?php
                                    // Get unread alert count if available
                                    $unread_count = 0;
                                    if ($_SESSION['user_type'] === 'patient') {
                                        try {
                                            // Only run if the patient_alerts table exists
                                            $db_file = __DIR__ . '/../../config/database.php';
                                            if (file_exists($db_file)) {
                                                $db = require_once $db_file;
                                                $stmt = $db->query("SHOW TABLES LIKE 'patient_alerts'");
                                                if ($stmt->rowCount() > 0) {
                                                    $stmt = $db->prepare("
                                                        SELECT COUNT(*) FROM patient_alerts
                                                        WHERE patient_id = (
                                                            SELECT id FROM patients WHERE user_id = ?
                                                        ) AND is_read = 0
                                                    ");
                                                    $stmt->execute([$_SESSION['user_id']]);
                                                    $unread_count = $stmt->fetchColumn();
                                                }
                                            }
                                        } catch (Exception $e) {
                                            // Do nothing on error, just log it
                                            error_log("Error fetching alert count: " . $e->getMessage());
                                        }
                                    }
                                    
                                    if ($unread_count > 0):
                                    ?>
                                    <span class="badge rounded-pill bg-danger"><?= $unread_count ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=report_issue">
                                    <i class="fas fa-exclamation-circle"></i> Report Issue
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars(SITE_URL . $_SESSION['profile_image']) ?>" 
                                         class="rounded-circle me-1" alt="Profile" 
                                         style="width: 30px; height: 30px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="index.php?module=<?php echo $_SESSION['user_type']; ?>&action=profile">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type'] === 'error' ? 'danger' : $_SESSION['flash']['type']; ?> alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['flash']['message']);
            unset($_SESSION['flash']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="container mt-4">
