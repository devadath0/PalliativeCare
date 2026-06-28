<?php
/**
 * Pharmacy Controller
 * Handles pharmacy-related functionality
 */
class PharmacyController extends BaseController {
    private $pharmacyId;
    
    public function __construct() {
        // Initialize database connection
        parent::__construct();
        
        // Get pharmacy ID from the database based on user ID
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT id FROM pharmacies WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pharmacy) {
                $this->pharmacyId = $pharmacy['id'];
            } else {
                // Check if user is a pharmacy service provider
                if ($this->hasRole('service')) {
                    // Check if the service provider has type 'pharmacy'
                    $stmt = $this->db->prepare("
                        SELECT sp.*, u.email 
                        FROM service_providers sp
                        JOIN users u ON sp.user_id = u.id
                        WHERE sp.user_id = ? AND sp.service_type = 'pharmacy'
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $provider = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($provider) {
                        // Create a pharmacy record for this service provider
                        try {
                            $this->db->beginTransaction();
                            
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
        }
        
        // Check if user is a pharmacy service provider
        if (!$this->hasRole('service') || !$this->pharmacyId) {
            $this->setFlash('error', 'You do not have permission to access this page.');
            $this->redirect('index.php?module=auth&action=login&type=service');
        }
    }

    /**
     * Default method - redirects to dashboard
     */
    public function index() {
        $this->dashboard();
    }

    /**
     * Display pharmacy dashboard
     */
    public function dashboard() {
        try {
            // Get pharmacy information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email 
                FROM pharmacies p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->pharmacyId]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get order statistics
            $stats = $this->getOrderStats();
            
            // Get recent orders
            $recent_orders = $this->getRecentOrders();
            
            // Get low stock medicines
            $low_stock = $this->getLowStockMedicines();

            $this->render('dashboard', [
                'page_title' => 'Pharmacy Dashboard',
                'pharmacy' => $pharmacy,
                'stats' => $stats,
                'recent_orders' => $recent_orders,
                'low_stock' => $low_stock
            ]);
        } catch (Exception $e) {
            $this->logError("Dashboard error: " . $e->getMessage());
            $this->setFlash('error', 'Error loading dashboard data');
            $this->render('dashboard', [
                'page_title' => 'Pharmacy Dashboard',
                'pharmacy' => [],
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
            $stmt = $this->db->prepare("
                SELECT * FROM medicines 
                WHERE pharmacy_id = ?
                ORDER BY name ASC
            ");
            $stmt->execute([$this->pharmacyId]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('inventory', [
                'page_title' => 'Medicine Inventory',
                'medicines' => $medicines
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading inventory: " . $e->getMessage());
            $this->setFlash('error', 'Error loading inventory data');
            $this->render('inventory', [
                'page_title' => 'Medicine Inventory',
                'medicines' => []
            ]);
        }
    }

    /**
     * Add new medicine
     */
    public function add_medicine() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $category = $_POST['category'];
                $unit = $_POST['unit'];
                $price = floatval($_POST['price']);
                $stock_quantity = intval($_POST['stock_quantity']);
                $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;

                $stmt = $this->db->prepare("
                    INSERT INTO medicines (
                        pharmacy_id, name, description, category, unit, 
                        price, stock_quantity, requires_prescription
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $this->pharmacyId, $name, $description, $category, $unit,
                    $price, $stock_quantity, $requires_prescription
                ]);

                $this->setFlash('success', 'Medicine added successfully');
                $this->redirect('index.php?module=pharmacy&action=inventory');
            } catch (Exception $e) {
                $this->logError("Error adding medicine: " . $e->getMessage());
                $this->setFlash('error', 'Error adding medicine');
                $this->redirect('index.php?module=pharmacy&action=add_medicine');
            }
        } else {
            $this->render('add_medicine', [
                'page_title' => 'Add New Medicine'
            ]);
        }
    }

    /**
     * Update medicine
     */
    public function edit_medicine() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'Invalid medicine ID');
            $this->redirect('index.php?module=pharmacy&action=inventory');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $category = $_POST['category'];
                $unit = $_POST['unit'];
                $price = floatval($_POST['price']);
                $stock_quantity = intval($_POST['stock_quantity']);
                $requires_prescription = isset($_POST['requires_prescription']) ? 1 : 0;
                $status = $_POST['status'];

                $stmt = $this->db->prepare("
                    UPDATE medicines 
                    SET name = ?, description = ?, category = ?, unit = ?,
                        price = ?, stock_quantity = ?, requires_prescription = ?,
                        status = ?
                    WHERE id = ? AND pharmacy_id = ?
                ");
                $stmt->execute([
                    $name, $description, $category, $unit,
                    $price, $stock_quantity, $requires_prescription, $status,
                    $id, $this->pharmacyId
                ]);

                $this->setFlash('success', 'Medicine updated successfully');
                $this->redirect('index.php?module=pharmacy&action=inventory');
            } catch (Exception $e) {
                $this->logError("Error updating medicine: " . $e->getMessage());
                $this->setFlash('error', 'Error updating medicine');
                $this->redirect("index.php?module=pharmacy&action=edit_medicine&id=$id");
            }
        } else {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM medicines 
                    WHERE id = ? AND pharmacy_id = ?
                ");
                $stmt->execute([$id, $this->pharmacyId]);
                $medicine = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$medicine) {
                    $this->setFlash('error', 'Medicine not found');
                    $this->redirect('index.php?module=pharmacy&action=inventory');
                }

                $this->render('edit_medicine', [
                    'page_title' => 'Edit Medicine',
                    'medicine' => $medicine
                ]);
            } catch (Exception $e) {
                $this->logError("Error loading medicine: " . $e->getMessage());
                $this->setFlash('error', 'Error loading medicine data');
                $this->redirect('index.php?module=pharmacy&action=inventory');
            }
        }
    }

    /**
     * View and manage orders
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

            $this->render('orders', [
                'page_title' => 'Manage Orders',
                'orders' => $orders,
                'current_status' => $status
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading orders: " . $e->getMessage());
            $this->setFlash('error', 'Error loading orders data');
            $this->render('orders', [
                'page_title' => 'Manage Orders',
                'orders' => [],
                'current_status' => $status
            ]);
        }
    }

    /**
     * Update order status
     */
    public function update_order_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('index.php?module=pharmacy&action=orders');
            return;
        }

        $order_id = $_POST['order_id'] ?? null;
        $new_status = $_POST['status'] ?? null;

        if (!$order_id || !$new_status) {
            $this->setFlash('error', 'Missing required parameters');
            $this->redirect('index.php?module=pharmacy&action=orders');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE medicine_orders 
                SET order_status = ?
                WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$new_status, $order_id, $this->pharmacyId]);

            $this->setFlash('success', 'Order status updated successfully');
        } catch (Exception $e) {
            $this->logError("Error updating order status: " . $e->getMessage());
            $this->setFlash('error', 'Error updating order status');
        }

        $this->redirect('index.php?module=pharmacy&action=orders');
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
            $quantity = intval($_POST['quantity']);
            $notes = $_POST['notes'] ?? '';

            if (!$medicine_id || $quantity === 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
                return;
            }

            // Start transaction
            $this->db->beginTransaction();

            // Get current stock
            $stmt = $this->db->prepare("
                SELECT stock_quantity 
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
            if ($new_quantity < 0) {
                throw new Exception('Insufficient stock');
            }

            // Update medicine stock
            $stmt = $this->db->prepare("
                UPDATE medicines 
                SET stock_quantity = ?,
                    status = CASE 
                        WHEN ? = 0 THEN 'out_of_stock'
                        ELSE 'active'
                    END
                WHERE id = ? AND pharmacy_id = ?
            ");
            $stmt->execute([$new_quantity, $new_quantity, $medicine_id, $this->pharmacyId]);

            // Log stock movement
            $stmt = $this->db->prepare("
                INSERT INTO stock_movements (
                    medicine_id, quantity, movement_type, 
                    reference_type, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $medicine_id,
                abs($quantity),
                $quantity > 0 ? 'in' : 'out',
                'adjustment',
                $notes,
                $_SESSION['user_id']
            ]);

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logError("Error updating stock: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get order items
     */
    public function get_order_items() {
        $order_id = $_GET['id'] ?? null;
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT oi.*, m.name as medicine_name
                FROM medicine_order_items oi
                JOIN medicines m ON oi.medicine_id = m.id
                JOIN medicine_orders o ON oi.order_id = o.id
                WHERE o.id = ? AND o.pharmacy_id = ?
            ");
            $stmt->execute([$order_id, $this->pharmacyId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'items' => $items]);
        } catch (Exception $e) {
            $this->logError("Error getting order items: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading order items']);
        }
    }

    /**
     * Get order details
     */
    public function get_order_details() {
        $order_id = $_GET['id'] ?? null;
        
        if (!$order_id) {
            $this->setFlash('error', 'Invalid order ID');
            $this->redirect('index.php?module=pharmacy&action=orders');
            return;
        }

        try {
            // Get order details
            $stmt = $this->db->prepare("
                SELECT mo.*, p.name as patient_name, p.phone as patient_phone,
                       p.email as patient_email
                FROM medicine_orders mo
                JOIN patients p ON mo.patient_id = p.id
                WHERE mo.id = ? AND mo.pharmacy_id = ?
            ");
            $stmt->execute([$order_id, $this->pharmacyId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->setFlash('error', 'Order not found');
                $this->redirect('index.php?module=pharmacy&action=orders');
                return;
            }
            
            // Get patient details
            $stmt = $this->db->prepare("
                SELECT * FROM patients WHERE id = ?
            ");
            $stmt->execute([$order['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get order items
            $stmt = $this->db->prepare("
                SELECT * FROM medicine_order_items WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Render the order details view
            $this->render('order_details', [
                'page_title' => 'Order Details',
                'order' => $order,
                'patient' => $patient,
                'order_items' => $order_items
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error getting order details: " . $e->getMessage());
            $this->setFlash('error', 'Error loading order details');
            $this->redirect('index.php?module=pharmacy&action=orders');
        }
    }

    /**
     * Get stock movement history
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

            $this->render('stock_history', [
                'page_title' => 'Stock Movement History',
                'movements' => $movements
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading stock history: " . $e->getMessage());
            $this->setFlash('error', 'Error loading stock history');
            $this->render('stock_history', [
                'page_title' => 'Stock Movement History',
                'movements' => []
            ]);
        }
    }

    /**
     * Update medicine prices for orders
     */
    public function update_medicine_prices() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $order_id = intval($_POST['order_id'] ?? 0);
                
                if ($order_id <= 0) {
                    $this->setFlash('error', 'Invalid order ID');
                    $this->redirect('index.php?module=pharmacy&action=orders');
                    return;
                }
                
                // Verify the order belongs to this pharmacy
                $stmt = $this->db->prepare("
                    SELECT * FROM medicine_orders 
                    WHERE id = ? AND pharmacy_id = ?
                ");
                $stmt->execute([$order_id, $this->pharmacyId]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$order) {
                    $this->setFlash('error', 'Order not found or does not belong to your pharmacy');
                    $this->redirect('index.php?module=pharmacy&action=orders');
                    return;
                }
                
                // Update prices for each medicine item
                if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
                    $item_ids = $_POST['item_id'];
                    $prices = $_POST['price'] ?? [];
                    
                    $this->db->beginTransaction();
                    $total_order_amount = 0;
                    
                    foreach ($item_ids as $i => $item_id) {
                        if (isset($prices[$i])) {
                            $item_id = intval($item_id);
                            $price = floatval($prices[$i]);
                            
                            // Get the current quantity
                            $stmt = $this->db->prepare("
                                SELECT quantity FROM medicine_order_items 
                                WHERE id = ? AND order_id = ?
                            ");
                            $stmt->execute([$item_id, $order_id]);
                            $item = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($item) {
                                $quantity = $item['quantity'];
                                $total_price = $quantity * $price;
                                
                                // Update the price
                                $stmt = $this->db->prepare("
                                    UPDATE medicine_order_items 
                                    SET unit_price = ?, total_price = ? 
                                    WHERE id = ? AND order_id = ?
                                ");
                                $stmt->execute([$price, $total_price, $item_id, $order_id]);
                                
                                $total_order_amount += $total_price;
                            }
                        }
                    }
                    
                    // Update the order total
                    $stmt = $this->db->prepare("
                        UPDATE medicine_orders 
                        SET total_amount = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$total_order_amount, $order_id]);
                    
                    $this->db->commit();
                    $this->setFlash('success', 'Medicine prices updated successfully');
                } else {
                    $this->setFlash('error', 'No items to update');
                }
                
                $this->redirect('index.php?module=pharmacy&action=get_order_details&id=' . $order_id);
                
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->logError("Error updating medicine prices: " . $e->getMessage());
                $this->setFlash('error', 'Error updating medicine prices: ' . $e->getMessage());
                $this->redirect('index.php?module=pharmacy&action=orders');
            }
        } else {
            $this->redirect('index.php?module=pharmacy&action=orders');
        }
    }
} 