<?php
/**
 * Pharmacy Controller for Service Module
 * Handles pharmacy-related functionality
 */
class PharmacyController extends BaseController {
    private $pharmacyId;
    private $serviceProviderId;
    
    public function __construct() {
        // Initialize database connection
        parent::__construct();
        
        // Check authentication
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service') {
            $this->setFlash('error', 'Please login to continue');
            $this->redirect('index.php?module=auth&action=login&type=service');
            return;
        }
        
        // Get pharmacy ID from the database based on user ID
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT id FROM pharmacies WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pharmacy) {
                $this->pharmacyId = $pharmacy['id'];
            } else {
                // Check if user is a pharmacy service provider
                $stmt = $this->db->prepare("
                    SELECT sp.*, u.email 
                    FROM service_providers sp
                    JOIN users u ON sp.user_id = u.id
                    WHERE sp.user_id = ? AND (sp.service_type = 'pharmacy' OR sp.service_type = 'medicine')
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $provider = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($provider) {
                    $this->serviceProviderId = $provider['id'];
                    
                    // Check if service_provider_id column exists in pharmacies table
                    try {
                        $columnExists = false;
                        $stmt = $this->db->query("SHOW COLUMNS FROM pharmacies LIKE 'service_provider_id'");
                        if ($stmt->rowCount() > 0) {
                            $columnExists = true;
                        }
                        
                        // Create a pharmacy record for this service provider
                        $this->db->beginTransaction();
                        
                        if ($columnExists) {
                            $stmt = $this->db->prepare("
                                INSERT INTO pharmacies (
                                    user_id, service_provider_id, name, address, phone, email, license_number, status
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, ?, 'active'
                                )
                            ");
                            
                            $licenseNumber = $provider['license_number'] ?? 'LIC-' . rand(10000, 99999);
                            
                            $stmt->execute([
                                $_SESSION['user_id'],
                                $provider['id'],
                                $provider['company_name'],
                                $provider['address'] ?? 'Address not provided',
                                $provider['phone'] ?? 'Phone not provided',
                                $provider['email'],
                                $licenseNumber
                            ]);
                        } else {
                            // Insert without service_provider_id
                            $stmt = $this->db->prepare("
                                INSERT INTO pharmacies (
                                    user_id, name, address, phone, email, license_number, status
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, 'active'
                                )
                            ");
                            
                            $licenseNumber = $provider['license_number'] ?? 'LIC-' . rand(10000, 99999);
                            
                            $stmt->execute([
                                $_SESSION['user_id'],
                                $provider['company_name'],
                                $provider['address'] ?? 'Address not provided',
                                $provider['phone'] ?? 'Phone not provided',
                                $provider['email'],
                                $licenseNumber
                            ]);
                        }
                        
                        $this->pharmacyId = $this->db->lastInsertId();
                        $this->db->commit();
                        
                        $this->setFlash('success', 'Your pharmacy profile has been created. Welcome!');
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        $this->logError("Error creating pharmacy record: " . $e->getMessage());
                    }
                }
            }
        }
        
        // Check if user is a pharmacy service provider
        if (!$this->pharmacyId) {
            // Instead of redirecting, try to create a new pharmacy entry for this service user
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM users WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Create a basic pharmacy record for this user
                    $this->db->beginTransaction();
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO pharmacies (
                            user_id, name, address, phone, email, license_number, status
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, 'active'
                        )
                    ");
                    
                    $licenseNumber = 'LIC-' . rand(10000, 99999);
                    
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $user['name'] ?? 'Pharmacy',
                        'Address not provided',
                        'Phone not provided',
                        $user['email'],
                        $licenseNumber
                    ]);
                    
                    $this->pharmacyId = $this->db->lastInsertId();
                    $this->db->commit();
                    
                    $this->setFlash('success', 'Your pharmacy profile has been created. Welcome!');
                } else {
                    // If we can't find the user, set a temporary pharmacy ID
                    $this->pharmacyId = 1; // Temporary ID
                    $this->logError("Using temporary pharmacy ID because user not found");
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->logError("Error creating basic pharmacy record: " . $e->getMessage());
                // Don't redirect, just continue with a temporary pharmacy ID
                $this->pharmacyId = 1; // Temporary ID
            }
        }
    }

    /**
     * Override the render method to use the service module's views
     */
    protected function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Explicitly set the view file path to use the service module
        $view_file = "modules/service/views/{$view}.php";
        
        if (!file_exists($view_file)) {
            $this->logError("View file not found: {$view_file}");
            
            // If the view is for a pharmacy page that doesn't exist, let's create a basic template
            if (strpos($view, 'pharmacy/') === 0) {
                // Extract the view name from the path
                $view_name = str_replace('pharmacy/', '', $view);
                
                // Create the directory if it doesn't exist
                $dir = dirname($view_file);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                // Create a basic template
                $title = ucfirst(str_replace('_', ' ', $view_name));
                $template = '<?php
// Pharmacy ' . $title . ' View
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "' . $title . '"; ?> - Pharmacy Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?module=service&action=pharmacy_dashboard">
                <i class="fas fa-prescription-bottle-alt me-2"></i> Pharmacy Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pharmacyNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="pharmacyNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_dashboard">
                           <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view_name == "inventory" ? "active" : ""; ?>" href="index.php?module=service&action=pharmacy_inventory">
                           <i class="fas fa-pills"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view_name == "orders" ? "active" : ""; ?>" href="index.php?module=service&action=pharmacy_orders">
                           <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view_name == "service_requests" ? "active" : ""; ?>" href="index.php?module=service&action=pharmacy_service_requests">
                           <i class="fas fa-truck-medical"></i> Delivery Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $view_name == "stock_history" ? "active" : ""; ?>" href="index.php?module=service&action=pharmacy_stock_history">
                           <i class="fas fa-history"></i> Stock History
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4"><?php echo isset($page_title) ? $page_title : "' . $title . '"; ?></h1>
        
        <div class="alert alert-info">
            <p>This feature is currently under development. Please check back later.</p>
            <a href="index.php?module=service&action=pharmacy_dashboard" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
            </a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
                
                // Save the template to the view file
                file_put_contents($view_file, $template);
                
                if (file_exists($view_file)) {
                    $this->logError("Created new view file: {$view_file}");
                } else {
                    // If we couldn't create the file, fall back to dashboard
                    $this->logError("Could not create the view file, falling back to dashboard view");
                    $view_file = "modules/service/views/dashboard.php";
                }
            } else {
                throw new Exception("View file not found: {$view_file}");
            }
        }
        
        // Include the view file
        include $view_file;
    }

    /**
     * Display pharmacy dashboard
     */
    public function dashboard() {
        try {
            // Verify pharmacyId is set
            if (empty($this->pharmacyId)) {
                $this->setFlash('error', 'Could not identify pharmacy');
                $this->redirect('index.php?module=auth&action=login&type=service');
                return;
            }
            
            // Get pharmacy information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email 
                FROM pharmacies p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->pharmacyId]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pharmacy) {
                $this->setFlash('error', 'Pharmacy not found');
                $this->redirect('index.php?module=auth&action=login&type=service');
                return;
            }
            
            // Get service provider information if available
            $service_provider = null;
            $service_requests = [];
            
            // Get service provider ID using the helper method that handles missing column
            $service_provider_id = $this->getServiceProviderId();
            
            if ($service_provider_id) {
                $stmt = $this->db->prepare("
                    SELECT * FROM service_providers
                    WHERE id = ?
                ");
                $stmt->execute([$service_provider_id]);
                $service_provider = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get recent service requests if service provider exists
                if ($service_provider) {
                    try {
                        $stmt = $this->db->prepare("
                            SELECT sr.*, p.name as patient_name, p.phone as patient_phone
                            FROM service_requests sr
                            JOIN patients p ON sr.patient_id = p.id
                            WHERE sr.service_provider_id = ? 
                            AND sr.request_type = 'medicine_delivery'
                            ORDER BY sr.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$service_provider_id]);
                        $service_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        // Fall back to alternative query in case of column mismatch
                        try {
                            $stmt = $this->db->prepare("
                                SELECT sr.*, p.name as patient_name, p.phone as patient_phone
                                FROM service_requests sr
                                JOIN patients p ON sr.patient_id = p.id
                                WHERE sr.provider_id = ? 
                                AND (sr.service_type = 'medicine' OR sr.service_type = 'equipment')
                                ORDER BY sr.created_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute([$service_provider_id]);
                            $service_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e2) {
                            // If both queries fail, just use empty array
                            $this->logError("Error getting service requests: " . $e2->getMessage());
                            $service_requests = [];
                        }
                    }
                }
            }
            
            // Get order statistics
            $stats = $this->getOrderStats();
            
            // Get recent orders
            $recent_orders = $this->getRecentOrders();
            
            // Get low stock medicines
            $low_stock = $this->getLowStockMedicines();

            $this->render('pharmacy/dashboard', [
                'page_title' => 'Pharmacy Dashboard',
                'pharmacy' => $pharmacy,
                'service_provider' => $service_provider,
                'service_requests' => $service_requests,
                'stats' => $stats,
                'recent_orders' => $recent_orders,
                'low_stock' => $low_stock
            ]);
        } catch (Exception $e) {
            $this->logError("Dashboard error: " . $e->getMessage());
            $this->setFlash('error', 'Error loading dashboard data');
            $this->render('pharmacy/dashboard', [
                'page_title' => 'Pharmacy Dashboard',
                'pharmacy' => [],
                'service_provider' => null,
                'service_requests' => [],
                'stats' => [],
                'recent_orders' => [],
                'low_stock' => []
            ]);
        }
    }

    /**
     * Get order statistics
     */
    private function getOrderStats() {
        try {
            $stats = [];
            
            // Check if pharmacyId is valid
            if (!$this->pharmacyId || $this->pharmacyId <= 0) {
                return [
                    'orders' => [
                        'total' => 0,
                        'pending' => 0,
                        'processing' => 0,
                        'shipped' => 0,
                        'delivered' => 0,
                        'cancelled' => 0
                    ],
                    'today' => [
                        'count' => 0,
                        'revenue' => 0
                    ]
                ];
            }
            
            // Total orders
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM medicine_orders 
                WHERE pharmacy_id = ?
            ");
            $stmt->execute([$this->pharmacyId]);
            $stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Today's orders
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, SUM(total_amount) as revenue
                FROM medicine_orders 
                WHERE pharmacy_id = ? 
                AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$this->pharmacyId]);
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            $this->logError("Error getting order stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders() {
        try {
            // Check if pharmacyId is valid
            if (!$this->pharmacyId || $this->pharmacyId <= 0) {
                return [];
            }
            
            $stmt = $this->db->prepare("
                SELECT mo.*, p.name as patient_name
                FROM medicine_orders mo
                JOIN patients p ON mo.patient_id = p.id
                WHERE mo.pharmacy_id = ?
                ORDER BY mo.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$this->pharmacyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError("Error getting recent orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get low stock medicines
     */
    private function getLowStockMedicines() {
        try {
            // Check if pharmacyId is valid
            if (!$this->pharmacyId || $this->pharmacyId <= 0) {
                return [];
            }
            
            $stmt = $this->db->prepare("
                SELECT *
                FROM medicines
                WHERE pharmacy_id = ?
                AND stock_quantity <= 10
                AND status != 'discontinued'
                ORDER BY stock_quantity ASC
                LIMIT 5
            ");
            $stmt->execute([$this->pharmacyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logError("Error getting low stock medicines: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Display medicine inventory
     */
    public function inventory() {
        try {
            // Check if pharmacy ID is valid
            if (!$this->pharmacyId || $this->pharmacyId <= 0) {
                $this->logError("Invalid pharmacy ID in inventory method");
                $this->setFlash('error', 'Invalid pharmacy configuration. Please contact support.');
                $this->render('pharmacy/inventory', [
                    'page_title' => 'Medicine Inventory',
                    'medicines' => [],
                    'pharmacy' => [
                        'name' => 'Pharmacy',
                        'email' => $_SESSION['email'] ?? 'Unknown',
                        'address' => 'Address not set',
                        'phone' => 'Phone not set',
                        'license_number' => 'License not set'
                    ]
                ]);
                return;
            }
            
            // Get medicines
            $stmt = $this->db->prepare("
                SELECT * FROM medicines 
                WHERE pharmacy_id = ?
                ORDER BY name ASC
            ");
            $stmt->execute([$this->pharmacyId]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get pharmacy information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email 
                FROM pharmacies p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->pharmacyId]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If pharmacy not found, create a default structure
            if (!$pharmacy) {
                $pharmacy = [
                    'id' => $this->pharmacyId,
                    'name' => 'Pharmacy',
                    'email' => $_SESSION['email'] ?? 'Unknown',
                    'address' => 'Address not set',
                    'phone' => 'Phone not set',
                    'license_number' => 'License not set'
                ];
            }
            
            // Render the inventory view
            $this->render('pharmacy/inventory', [
                'page_title' => 'Medicine Inventory',
                'medicines' => $medicines,
                'pharmacy' => $pharmacy
            ]);
        } catch (Exception $e) {
            $this->logError("Inventory error: " . $e->getMessage());
            $this->setFlash('error', 'Error loading inventory data: ' . $e->getMessage());
            
            // Still render the inventory view with empty data
            $this->render('pharmacy/inventory', [
                'page_title' => 'Medicine Inventory',
                'medicines' => [],
                'pharmacy' => [
                    'name' => 'Pharmacy',
                    'email' => $_SESSION['email'] ?? 'Unknown',
                    'address' => 'Error loading pharmacy data',
                    'phone' => 'Please try again later',
                    'license_number' => 'Error'
                ]
            ]);
        }
    }

    /**
     * Display and manage orders
     */
    public function orders() {
        try {
            $status = $_GET['status'] ?? 'all';
            
            $query = "
                SELECT mo.*, p.name as patient_name, pr.id as prescription_id
                FROM medicine_orders mo
                JOIN patients p ON mo.patient_id = p.id
                LEFT JOIN prescriptions pr ON mo.prescription_id = pr.id
                WHERE mo.pharmacy_id = ?
            ";
            
            if ($status !== 'all') {
                $query .= " AND mo.order_status = ?";
                $params = [$this->pharmacyId, $status];
            } else {
                $params = [$this->pharmacyId];
            }
            
            $query .= " ORDER BY mo.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('pharmacy/orders', [
                'page_title' => 'Manage Orders',
                'orders' => $orders,
                'current_status' => $status
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading orders: " . $e->getMessage());
            $this->setFlash('error', 'Error loading orders data');
            $this->render('pharmacy/orders', [
                'page_title' => 'Manage Orders',
                'orders' => [],
                'current_status' => $status ?? 'all'
            ]);
        }
    }

    /**
     * Get order details
     */
    public function order_details() {
        $order_id = $_GET['id'] ?? null;
        
        if (!$order_id) {
            $this->setFlash('error', 'Invalid order ID');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
            return;
        }

        try {
            // Get order details
            $stmt = $this->db->prepare("
                SELECT mo.*, p.name as patient_name, p.phone as patient_phone,
                       p.email as patient_email, p.id as patient_id
                FROM medicine_orders mo
                JOIN patients p ON mo.patient_id = p.id
                WHERE mo.id = ? AND mo.pharmacy_id = ?
            ");
            $stmt->execute([$order_id, $this->pharmacyId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->setFlash('error', 'Order not found');
                $this->redirect('index.php?module=service&action=pharmacy_orders');
                return;
            }
            
            // Get patient details
            $stmt = $this->db->prepare("
                SELECT * FROM patients WHERE id = ?
            ");
            $stmt->execute([$order['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get order items with stock check
            $stmt = $this->db->prepare("
                SELECT moi.*, 
                       m.name as medicine_name,
                       m.stock_quantity,
                       m.id as medicine_id
                FROM medicine_order_items moi
                LEFT JOIN medicines m ON moi.medicine_id = m.id AND m.pharmacy_id = ?
                WHERE moi.order_id = ?
            ");
            $stmt->execute([$this->pharmacyId, $order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Check stock availability for each item
            foreach ($order_items as &$item) {
                // If medicine_id is not null, check stock
                if (!empty($item['medicine_id'])) {
                    // Check if sufficient stock is available
                    $item['in_stock'] = ($item['stock_quantity'] >= $item['quantity']);
                } else {
                    // Handle manually entered medicines that don't exist in inventory
                    $item['stock_quantity'] = 'Not in inventory';
                    $item['in_stock'] = false;
                }
            }
            
            // Render the order details view
            $this->render('pharmacy/order_details', [
                'page_title' => 'Order Details',
                'order' => $order,
                'patient' => $patient,
                'order_items' => $order_items
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error getting order details: " . $e->getMessage());
            $this->setFlash('error', 'Error loading order details');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
        }
    }

    /**
     * Display stock history
     */
    public function stock_history() {
        try {
            $medicine_id = $_GET['medicine_id'] ?? null;
            
            $query = "
                SELECT sm.*, m.name as medicine_name, u.name as user_name
                FROM stock_movements sm
                JOIN medicines m ON sm.medicine_id = m.id
                JOIN users u ON sm.created_by = u.id
                WHERE m.pharmacy_id = ?
            ";
            $params = [$this->pharmacyId];
            
            if ($medicine_id) {
                $query .= " AND sm.medicine_id = ?";
                $params[] = $medicine_id;
            }
            
            $query .= " ORDER BY sm.created_at DESC LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('pharmacy/stock_history', [
                'page_title' => 'Stock Movement History',
                'movements' => $movements
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading stock history: " . $e->getMessage());
            $this->setFlash('error', 'Error loading stock history');
            $this->render('pharmacy/stock_history', [
                'page_title' => 'Stock Movement History',
                'movements' => []
            ]);
        }
    }

    /**
     * Update order status
     */
    public function update_order_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
            return;
        }

        $order_id = $_POST['order_id'] ?? null;
        $new_status = $_POST['status'] ?? null;

        if (!$order_id || !$new_status) {
            $this->setFlash('error', 'Missing required parameters');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
            return;
        }

        try {
            // Get the current order status
            $stmt = $this->db->prepare("
                SELECT order_status FROM medicine_orders 
                WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$order_id, $this->pharmacyId]);
            $current_status = $stmt->fetchColumn();
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // If moving from pending/processing to delivered, update inventory
            if (($current_status === 'pending' || $current_status === 'processing') && 
                ($new_status === 'delivered' || $new_status === 'shipped')) {
                // Get order items
                $stmt = $this->db->prepare("
                    SELECT oi.medicine_id, oi.quantity, oi.medicine_name
                    FROM medicine_order_items oi
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Update inventory for each item
                foreach ($order_items as $item) {
                    if (!empty($item['medicine_id'])) {
                        // Check current stock
                        $stmt = $this->db->prepare("
                            SELECT stock_quantity 
                            FROM medicines 
                            WHERE id = ? AND pharmacy_id = ?
                            FOR UPDATE
                        ");
                        $stmt->execute([$item['medicine_id'], $this->pharmacyId]);
                        $current_stock = $stmt->fetchColumn();
                        
                        if ($current_stock === false) {
                            // Medicine not found or doesn't belong to this pharmacy
                            continue;
                        }
                        
                        // Calculate new stock quantity
                        $new_stock = max(0, $current_stock - $item['quantity']);
                        
                        // Update medicine stock
                        $stmt = $this->db->prepare("
                            UPDATE medicines 
                            SET stock_quantity = ?,
                                status = CASE 
                                    WHEN ? = 0 THEN 'out_of_stock'
                                    ELSE status
                                END,
                                updated_at = NOW()
                            WHERE id = ? AND pharmacy_id = ?
                        ");
                        $stmt->execute([$new_stock, $new_stock, $item['medicine_id'], $this->pharmacyId]);
                        
                        // Log stock movement
                        $stmt = $this->db->prepare("
                            INSERT INTO stock_movements (
                                medicine_id, quantity, movement_type, 
                                reference_type, reference_id, notes, created_by
                            ) VALUES (?, ?, 'out', 'order', ?, ?, ?)
                        ");
                        $stmt->execute([
                            $item['medicine_id'],
                            $item['quantity'],
                            $order_id,
                            "Order #{$order_id} - {$new_status}",
                            $_SESSION['user_id']
                        ]);
                    }
                }
            }
            
            // Update order status
            $stmt = $this->db->prepare("
                UPDATE medicine_orders 
                SET order_status = ?, updated_at = NOW()
                WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$new_status, $order_id, $this->pharmacyId]);
            
            // Create patient alert for order status update
            try {
                // Get patient ID and order details
                $stmt = $this->db->prepare("
                    SELECT mo.patient_id, mo.order_number, p.name as pharmacy_name
                    FROM medicine_orders mo
                    LEFT JOIN pharmacies p ON mo.pharmacy_id = p.id
                    WHERE mo.id = ?
                ");
                $stmt->execute([$order_id]);
                $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($orderInfo && isset($orderInfo['patient_id'])) {
                    // Check if patient_alerts table exists
                    $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                    $patient_alerts_exists = ($stmt->rowCount() > 0);
                    
                    // Create table if it doesn't exist
                    if (!$patient_alerts_exists) {
                        $this->db->exec("
                            CREATE TABLE IF NOT EXISTS `patient_alerts` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `patient_id` int NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `message` text NOT NULL,
                              `alert_type` enum('appointment','medicine_order','cab_booking','prescription','payment','system') NOT NULL DEFAULT 'system',
                              `reference_id` int DEFAULT NULL,
                              `is_read` tinyint(1) DEFAULT '0',
                              `read_at` timestamp NULL DEFAULT NULL,
                              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (`id`),
                              KEY `patient_id` (`patient_id`),
                              KEY `idx_alert_type_reference` (`alert_type`,`reference_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                        ");
                    }
                    
                    // Set alert message based on status
                    $alertTitle = "Medicine Order #{$orderInfo['order_number']} Update";
                    $alertMessage = "";
                    
                    switch ($new_status) {
                        case 'processing':
                            $alertMessage = "Your medicine order #{$orderInfo['order_number']} at {$orderInfo['pharmacy_name']} is now being processed.";
                            break;
                        case 'shipped':
                            $alertMessage = "Your medicine order #{$orderInfo['order_number']} has been shipped from {$orderInfo['pharmacy_name']} and is on its way.";
                            break;
                        case 'delivered':
                            $alertMessage = "Your medicine order #{$orderInfo['order_number']} from {$orderInfo['pharmacy_name']} has been delivered successfully.";
                            break;
                        case 'cancelled':
                            $alertMessage = "Your medicine order #{$orderInfo['order_number']} at {$orderInfo['pharmacy_name']} has been cancelled.";
                            break;
                        default:
                            $alertMessage = "Your medicine order #{$orderInfo['order_number']} status has been updated to {$new_status}.";
                    }
                    
                    // Create the alert
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'medicine_order', ?, 0)
                    ");
                    $stmt->execute([
                        $orderInfo['patient_id'],
                        $alertTitle,
                        $alertMessage,
                        $order_id
                    ]);
                    
                    error_log("Created patient alert for medicine order ID {$order_id}, new status: {$new_status}");
                }
            } catch (PDOException $e) {
                error_log("Error creating patient alert for medicine order: " . $e->getMessage());
                // Continue execution even if alert creation fails
            }
            
            // If the order is marked as delivered, update payment status if not already completed
            if ($new_status === 'delivered') {
                $stmt = $this->db->prepare("
                    UPDATE medicine_orders 
                    SET payment_status = 'completed'
                    WHERE id = ? AND pharmacy_id = ? AND payment_status = 'pending'
                ");
                $stmt->execute([$order_id, $this->pharmacyId]);
            }
            
            $this->db->commit();
            $this->setFlash('success', 'Order status updated successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError("Error updating order status: " . $e->getMessage());
            $this->setFlash('error', 'Error updating order status: ' . $e->getMessage());
        }

        // Redirect back to the previous page
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?module=service&action=pharmacy_orders';
        $this->redirect($redirect);
    }

    /**
     * Get service provider ID associated with this pharmacy
     */
    private function getServiceProviderId() {
        try {
            // Check if the column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM pharmacies LIKE 'service_provider_id'");
            if ($stmt->rowCount() == 0) {
                // Column doesn't exist, use the user ID to get service provider ID
                $stmt = $this->db->prepare("
                    SELECT sp.id 
                    FROM service_providers sp
                    WHERE sp.user_id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['id'] : null;
            } else {
                // Column exists, use it
                $stmt = $this->db->prepare("
                    SELECT service_provider_id FROM pharmacies WHERE id = ?
                ");
                $stmt->execute([$this->pharmacyId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? $result['service_provider_id'] : null;
            }
        } catch (Exception $e) {
            $this->logError("Error getting service provider ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * View prescription for pharmacy
     */
    public function view_prescription() {
        $prescription_id = $_GET['id'] ?? null;
        
        if (!$prescription_id) {
            $this->setFlash('error', 'Invalid prescription ID');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
            return;
        }
        
        try {
            // Get prescription details
            $stmt = $this->db->prepare("
                SELECT p.*, pat.name as patient_name, d.name as doctor_name
                FROM prescriptions p
                JOIN patients pat ON p.patient_id = pat.id
                JOIN doctors d ON p.doctor_id = d.id
                WHERE p.id = ?
            ");
            $stmt->execute([$prescription_id]);
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prescription) {
                $this->setFlash('error', 'Prescription not found');
                $this->redirect('index.php?module=service&action=pharmacy_orders');
                return;
            }
            
            // Get patient details
            $stmt = $this->db->prepare("
                SELECT * FROM patients WHERE id = ?
            ");
            $stmt->execute([$prescription['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get prescription items if available
            $prescription_items = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM prescription_items
                    WHERE prescription_id = ?
                ");
                $stmt->execute([$prescription_id]);
                $prescription_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // If table doesn't exist or no items found, try an alternative approach
                $this->logError("Error getting prescription items, trying medicines: " . $e->getMessage());
                
                try {
                    $stmt = $this->db->prepare("
                        SELECT * FROM prescription_medicines
                        WHERE prescription_id = ?
                    ");
                    $stmt->execute([$prescription_id]);
                    $prescription_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e2) {
                    $this->logError("Error getting prescription medicines: " . $e2->getMessage());
                    // Continue with empty array
                }
            }
            
            // If no items found in either table, parse from diagnosis field
            if (empty($prescription_items) && !empty($prescription['diagnosis'])) {
                $prescription_items = $this->parsePrescriptionText($prescription['diagnosis']);
            }
            
            $this->render('pharmacy/view_prescription', [
                'page_title' => 'View Prescription',
                'prescription' => $prescription,
                'patient' => $patient,
                'prescription_items' => $prescription_items
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error viewing prescription: " . $e->getMessage());
            $this->setFlash('error', 'Error loading prescription details');
            $this->redirect('index.php?module=service&action=pharmacy_orders');
        }
    }
    
    /**
     * Parse prescription text into structured items
     */
    private function parsePrescriptionText($text) {
        $items = [];
        $lines = explode("\n", $text);
        
        $currentItem = [
            'medicine' => '',
            'dosage' => '',
            'frequency' => '',
            'duration' => '',
            'instructions' => ''
        ];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, 'Medication:') !== false) {
                // If we already have an item, save it before starting a new one
                if (!empty($currentItem['medicine'])) {
                    $items[] = $currentItem;
                }
                
                // Start a new item
                $currentItem = [
                    'medicine' => trim(str_replace('Medication:', '', $line)),
                    'dosage' => '',
                    'frequency' => '',
                    'duration' => '',
                    'instructions' => ''
                ];
            } elseif (strpos($line, 'Dosage:') !== false) {
                $currentItem['dosage'] = trim(str_replace('Dosage:', '', $line));
            } elseif (strpos($line, 'Frequency:') !== false) {
                $currentItem['frequency'] = trim(str_replace('Frequency:', '', $line));
            } elseif (strpos($line, 'Duration:') !== false) {
                $currentItem['duration'] = trim(str_replace('Duration:', '', $line));
            } else {
                // If it doesn't match any of the above, consider it additional instructions
                if (empty($currentItem['instructions'])) {
                    $currentItem['instructions'] = $line;
                } else {
                    $currentItem['instructions'] .= "\n" . $line;
                }
            }
        }
        
        // Add the last item if not empty
        if (!empty($currentItem['medicine'])) {
            $items[] = $currentItem;
        }
        
        return $items;
    }
    
    /**
     * Get order items for pharmacy
     */
    public function get_order_items() {
        $this->logError("Pharmacy get_order_items method called but not implemented");
        $this->setFlash('error', 'This feature is not yet implemented');
        $this->redirect('index.php?module=service&action=pharmacy_orders');
    }
    
    /**
     * Export orders for pharmacy
     */
    public function export_orders() {
        $this->logError("Pharmacy export_orders method called but not implemented");
        $this->setFlash('error', 'This feature is not yet implemented');
        $this->redirect('index.php?module=service&action=pharmacy_orders');
    }
    
    /**
     * Add medicine to inventory
     */
    public function add_medicine() {
        try {
            // If form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate form data
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $unit = $_POST['unit'] ?? '';
                $price = floatval($_POST['price'] ?? 0);
                $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
                $reorder_level = intval($_POST['reorder_level'] ?? 0);
                $manufacturer = $_POST['manufacturer'] ?? '';
                $storage_instructions = $_POST['storage_instructions'] ?? '';
                $batch_number = $_POST['batch_number'] ?? '';
                $expiry_date = $_POST['expiry_date'] ?? '';
                $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;
                
                // Validate required fields
                if (empty($name) || empty($category) || empty($unit) || $price <= 0) {
                    $this->setFlash('error', 'Please fill in all required fields');
                    $this->redirect('index.php?module=service&action=pharmacy_add_medicine');
                    return;
                }
                
                // Insert new medicine
                $stmt = $this->db->prepare("
                    INSERT INTO medicines (
                        pharmacy_id, name, description, category, unit, price,
                        stock_quantity, reorder_level, manufacturer, 
                        storage_instructions, batch_number, expiry_date,
                        requires_prescription, status, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?, ?,
                        ?, 'active', NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    $this->pharmacyId, $name, $description, $category, $unit, $price,
                    $stock_quantity, $reorder_level, $manufacturer,
                    $storage_instructions, $batch_number, $expiry_date,
                    $requires_prescription
                ]);
                
                $medicine_id = $this->db->lastInsertId();
                
                // Log stock movement if initial stock is added
                if ($stock_quantity > 0) {
                    $stmt = $this->db->prepare("
                        INSERT INTO stock_movements (
                            medicine_id, quantity, movement_type, 
                            reference_type, reference_id, notes, created_by
                        ) VALUES (?, ?, 'in', 'initial', NULL, ?, ?)
                    ");
                    $stmt->execute([
                        $medicine_id,
                        $stock_quantity,
                        "Initial stock for new medicine",
                        $_SESSION['user_id']
                    ]);
                }
                
                $this->setFlash('success', 'Medicine added successfully');
                $this->redirect('index.php?module=service&action=pharmacy_inventory');
                return;
            }
            
            // Display add form
            $this->render('pharmacy/add_medicine', [
                'page_title' => 'Add New Medicine'
            ]);
        } catch (Exception $e) {
            $this->logError("Error adding medicine: " . $e->getMessage());
            $this->setFlash('error', 'Error adding medicine: ' . $e->getMessage());
            $this->redirect('index.php?module=service&action=pharmacy_add_medicine');
        }
    }
    
    /**
     * Edit medicine in inventory
     */
    public function edit_medicine() {
        $medicine_id = $_GET['id'] ?? null;
        
        if (!$medicine_id) {
            $this->setFlash('error', 'Invalid medicine ID');
            $this->redirect('index.php?module=service&action=pharmacy_inventory');
            return;
        }
        
        try {
            // If form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate form data
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $unit = $_POST['unit'] ?? '';
                $price = floatval($_POST['price'] ?? 0);
                $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
                $reorder_level = intval($_POST['reorder_level'] ?? 0);
                $manufacturer = $_POST['manufacturer'] ?? '';
                $storage_instructions = $_POST['storage_instructions'] ?? '';
                $batch_number = $_POST['batch_number'] ?? '';
                $expiry_date = $_POST['expiry_date'] ?? '';
                $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;
                $status = $_POST['status'] ?? 'active';
                
                // Validate required fields
                if (empty($name) || empty($category) || empty($unit) || $price <= 0) {
                    $this->setFlash('error', 'Please fill in all required fields');
                    $this->redirect("index.php?module=service&action=pharmacy_edit_medicine&id={$medicine_id}");
                    return;
                }
                
                // Update medicine
                $stmt = $this->db->prepare("
                    UPDATE medicines
                    SET name = ?, description = ?, category = ?, unit = ?, price = ?,
                        stock_quantity = ?, reorder_level = ?, manufacturer = ?,
                        storage_instructions = ?, batch_number = ?, expiry_date = ?,
                        requires_prescription = ?, status = ?, updated_at = NOW()
                    WHERE id = ? AND pharmacy_id = ?
                ");
                
                $stmt->execute([
                    $name, $description, $category, $unit, $price,
                    $stock_quantity, $reorder_level, $manufacturer,
                    $storage_instructions, $batch_number, $expiry_date,
                    $requires_prescription, $status, $medicine_id, $this->pharmacyId
                ]);
                
                // Log stock movement if quantity changed
                $stmt = $this->db->prepare("
                    SELECT stock_quantity FROM medicines
                    WHERE id = ? AND pharmacy_id = ?
                ");
                $stmt->execute([$medicine_id, $this->pharmacyId]);
                $previous_quantity = $stmt->fetchColumn();
                
                if ($previous_quantity != $stock_quantity) {
                    $quantity_delta = $stock_quantity - $previous_quantity;
                    $movement_type = $quantity_delta > 0 ? 'in' : 'out';
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO stock_movements (
                            medicine_id, quantity, movement_type, 
                            reference_type, reference_id, notes, created_by
                        ) VALUES (?, ?, ?, 'adjustment', NULL, ?, ?)
                    ");
                    $stmt->execute([
                        $medicine_id,
                        abs($quantity_delta),
                        $movement_type,
                        "Inventory adjustment via edit form",
                        $_SESSION['user_id']
                    ]);
                }
                
                $this->setFlash('success', 'Medicine updated successfully');
                $this->redirect('index.php?module=service&action=pharmacy_inventory');
                return;
            }
            
            // Display edit form - get medicine details
            $stmt = $this->db->prepare("
                SELECT * FROM medicines WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$medicine_id, $this->pharmacyId]);
            $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$medicine) {
                $this->setFlash('error', 'Medicine not found');
                $this->redirect('index.php?module=service&action=pharmacy_inventory');
                return;
            }
            
            $this->render('pharmacy/edit_medicine', [
                'page_title' => 'Edit Medicine',
                'medicine' => $medicine
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error editing medicine: " . $e->getMessage());
            $this->setFlash('error', 'Error editing medicine: ' . $e->getMessage());
            $this->redirect('index.php?module=service&action=pharmacy_inventory');
        }
    }
    
    /**
     * Update medicine stock
     */
    public function update_stock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $medicine_id = $_POST['medicine_id'] ?? null;
            $quantity = intval($_POST['quantity'] ?? 0);
            $source = $_POST['source'] ?? 'adjustment';
            $notes = $_POST['notes'] ?? '';

            if (!$medicine_id || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                return;
            }

            // Start transaction
            $this->db->beginTransaction();

            // Get current stock
            $stmt = $this->db->prepare("
                SELECT stock_quantity, name 
                FROM medicines 
                WHERE id = ? AND pharmacy_id = ?
                FOR UPDATE
            ");
            $stmt->execute([$medicine_id, $this->pharmacyId]);
            $medicine = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$medicine) {
                throw new Exception('Medicine not found');
            }

            // Calculate new stock quantity
            $new_quantity = $medicine['stock_quantity'] + $quantity;

            // Update medicine stock
            $stmt = $this->db->prepare("
                UPDATE medicines 
                SET stock_quantity = ?,
                    status = CASE 
                        WHEN ? = 0 THEN 'out_of_stock'
                        ELSE 'active'
                    END,
                    updated_at = NOW()
                WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$new_quantity, $new_quantity, $medicine_id, $this->pharmacyId]);

            // Prepare note message
            $note_message = $notes;
            if (empty($note_message)) {
                $note_message = "Added {$quantity} units to {$medicine['name']} via {$source}";
            }

            // Log stock movement
            $stmt = $this->db->prepare("
                INSERT INTO stock_movements (
                    medicine_id, quantity, movement_type, 
                    reference_type, notes, created_by, created_at
                ) VALUES (?, ?, 'in', ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $medicine_id,
                $quantity,
                $source,
                $note_message,
                $_SESSION['user_id']
            ]);

            $this->db->commit();
            
            $this->setFlash('success', 'Stock updated successfully');
            // Check if request is AJAX or not
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
            } else {
                $this->redirect('index.php?module=service&action=pharmacy_inventory');
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError("Error updating stock: " . $e->getMessage());
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            } else {
                $this->setFlash('error', 'Error updating stock: ' . $e->getMessage());
                $this->redirect('index.php?module=service&action=pharmacy_inventory');
            }
        }
    }
    
    /**
     * Update service request
     */
    public function update_service_request() {
        $this->logError("Pharmacy update_service_request method called but not implemented");
        $this->setFlash('error', 'This feature is not yet implemented');
        $this->redirect('index.php?module=service&action=pharmacy_dashboard');
    }
    
    /**
     * Update medicine prices
     */
    public function update_medicine_prices() {
        $this->logError("Pharmacy update_medicine_prices method called but not implemented");
        $this->setFlash('error', 'This feature is not yet implemented');
        $this->redirect('index.php?module=service&action=pharmacy_inventory');
    }
    
    /**
     * Bulk process orders
     */
    public function bulk_process_orders() {
        $this->logError("Pharmacy bulk_process_orders method called but not implemented");
        $this->setFlash('error', 'This feature is not yet implemented');
        $this->redirect('index.php?module=service&action=pharmacy_orders');
    }

    /**
     * Alias methods for direct access
     */
    public function pharmacy_dashboard() {
        $this->dashboard();
    }
    
    public function pharmacy_inventory() {
        $this->inventory();
    }
    
    public function pharmacy_orders() {
        $this->orders();
    }
    
    public function pharmacy_stock_history() {
        $this->stock_history();
    }

    /**
     * Route pharmacy_edit_medicine to edit_medicine
     */
    public function pharmacy_edit_medicine() {
        $this->edit_medicine();
    }

    /**
     * Route pharmacy_view_prescription to view_prescription
     */
    public function pharmacy_view_prescription() {
        $this->view_prescription();
    }

    /**
     * Get the CSS class for order rows based on status
     */
    private function getOrderRowClass($status) {
        switch ($status) {
            case 'delivered':
                return 'table-success';
            case 'shipped':
                return 'table-info';
            case 'processing':
                return 'table-primary';
            case 'cancelled':
                return 'table-danger';
            case 'pending':
            default:
                return 'table-warning';
        }
    }
} 