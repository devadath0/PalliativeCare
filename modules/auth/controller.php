<?php
/**
 * Authentication Controller
 * Handles user authentication, registration, and password management
 */
class AuthController extends BaseController {
    private $userType;
    private $tableName;

    public function __construct() {
        parent::__construct();
        
        // Use empty string as default if type is not set to prevent null
        $this->userType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
        error_log("User type from URL: " . $this->userType);
        
        // Only try to get table name if user type is set
        if (!empty($this->userType)) {
            try {
                $this->tableName = $this->getTableName();
                error_log("AuthController initialized with userType: " . $this->userType);
            } catch (Exception $e) {
                error_log("Error getting table name: " . $e->getMessage());
                $_SESSION['error'] = "Invalid user type";
                header("Location: " . SITE_URL . "index.php");
                exit;
            }
        }
    }

    /**
     * Handle login action
     */
    public function login() {
        error_log("Login method called");
        
        // If user is already logged in, redirect to appropriate dashboard
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
            error_log("User already logged in, redirecting to dashboard");
            $this->redirectToDashboard($_SESSION['user_type']);
            return;
        }

        // If this is not a POST request, show the login form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Showing login form (GET request)");
            require_once 'modules/auth/views/login.php';
            return;
        }

        error_log("Processing login POST request");
        error_log("POST data: " . print_r($_POST, true));
        error_log("User type from URL: " . $this->userType);

        // Validate user type
        $validTypes = ['patient', 'doctor', 'service', 'admin'];
        if (!in_array($this->userType, $validTypes)) {
            error_log("Invalid user type: " . $this->userType);
            $_SESSION['error'] = "Invalid user type";
            header("Location: " . SITE_URL . "index.php");
            exit;
        }

        // Validate required fields
        if (empty($_POST['email']) || empty($_POST['password'])) {
            error_log("Missing required fields");
            $_SESSION['error'] = "Please fill in all required fields";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
            exit;
        }

        // Sanitize input
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email format: " . $email);
            $_SESSION['error'] = "Invalid email format";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
            exit;
        }

        try {
            error_log("Attempting database queries");
            
            // First check the users table
            $stmt = $this->db->prepare("SELECT id, password_hash, name FROM users WHERE email = ? AND user_type = ?");
            error_log("Checking users table for email: " . $email . " and type: " . $this->userType);
            $stmt->execute([$email, $this->userType]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("No user found with email: " . $email);
                $_SESSION['error'] = "Invalid email or password";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
                exit;
            }
            
            error_log("User found, verifying password");
            if (!password_verify($_POST['password'], $user['password_hash'])) {
                error_log("Password verification failed for user: " . $email);
                $_SESSION['error'] = "Invalid email or password";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
                exit;
            }

            // Now get the user details from their specific table
            if ($this->userType === 'admin') {
                $query = "SELECT * FROM admins WHERE user_id = ?";
            } else {
                $query = "SELECT * FROM {$this->tableName} WHERE user_id = ?";
            }
            error_log("Checking {$this->tableName} table with query: " . $query);
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user['id']]);
            $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userDetails) {
                error_log("No user details found in {$this->tableName} for user_id: " . $user['id']);
                $_SESSION['error'] = "Account not found";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
                exit;
            }

            error_log("User details found, setting session variables");
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $this->userType;
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $email;
            
            // Store profile image in session if available
            if (isset($userDetails['profile_image']) && !empty($userDetails['profile_image'])) {
                $_SESSION['profile_image'] = $userDetails['profile_image'];
            }

            // Set admin role if user is an admin
            if ($this->userType === 'admin') {
                $_SESSION['admin_level'] = $userDetails['role'] === 'super_admin' ? 'super' : 'standard';
            }

            error_log("Session variables set: " . print_r($_SESSION, true));

            // Update last login timestamp
            if ($this->userType === 'admin') {
                $stmt = $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE user_id = ?");
            } else {
                $stmt = $this->db->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            }
            $stmt->execute([$user['id']]);
            error_log("Updated last login timestamp");

            // Set success message
            $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";

            // Redirect to appropriate dashboard
            error_log("Redirecting to dashboard for user type: " . $this->userType);
            $this->redirectToDashboard($this->userType);

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "An error occurred during login. Please try again.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . urlencode($this->userType));
            exit;
        }
    }

    /**
     * Handle logout action
     */
    public function logout() {
        error_log("Logout method called");
        
        // Log session data before logout
        error_log("Session data before logout: " . print_r($_SESSION, true));
        
        // Clear all session variables
        $_SESSION = array();
        error_log("Session variables cleared");

        // Destroy the session
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
        
        $result = session_destroy();
        error_log("Session destroy result: " . ($result ? 'true' : 'false'));
        
        // Start a new session to set the success message
        session_start();
        error_log("New session started");
        
        $_SESSION['success'] = "You have been successfully logged out.";
        error_log("Success message set in new session");
        
        // Log session data after logout
        error_log("Session data after logout: " . print_r($_SESSION, true));

        // Redirect to home page using direct header redirect
        error_log("Redirecting to home page");
        header("Location: " . SITE_URL . "index.php");
        exit;
    }

    /**
     * Get the appropriate table name based on user type
     */
    private function getTableName() {
        // Validate user type before matching
        if (empty($this->userType)) {
            throw new Exception("User type cannot be empty");
        }

        return match($this->userType) {
            'patient' => 'patients',
            'doctor' => 'doctors',
            'service' => 'service_providers',
            'admin' => 'admins',
            default => throw new Exception("Invalid user type: " . $this->userType)
        };
    }

    /**
     * Redirect to the appropriate dashboard based on user type
     */
    private function redirectToDashboard($userType) {
        // For service providers, check if they're a pharmacy
        if ($userType === 'service') {
            // Check if service provider is pharmacy type
            $stmt = $this->db->prepare("
                SELECT service_type FROM service_providers WHERE user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($provider && ($provider['service_type'] === 'pharmacy' || $provider['service_type'] === 'medicine')) {
                $url = SITE_URL . "index.php?module=service&action=pharmacy_dashboard";
                error_log("Redirecting pharmacy service to pharmacy dashboard: " . $url);
                header("Location: " . $url);
                exit;
            }
        }
        
        $url = SITE_URL . "index.php?module=" . $userType . "&action=dashboard";
        error_log("Redirecting to dashboard: " . $url);
        header("Location: " . $url);
        exit;
    }

    public function process_admin_register() {
        // Enable error reporting
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        try {
            // Ensure database connection is initialized
            if (!isset($this->db)) {
                error_log("Database connection not initialized");
                require_once __DIR__ . '/../classes/Database.php';
                $this->db = Database::getInstance();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Invalid request method';
                header('Location: index.php?module=auth&action=register&type=admin');
                exit;
            }

            error_log("Processing admin registration - POST data: " . print_r($_POST, true));

            $required_fields = ['name', 'email', 'password', 'confirm_password', 'token'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    error_log("Missing required field: " . $field);
                    $_SESSION['error'] = 'All fields are required';
                    header('Location: index.php?module=auth&action=register&type=admin');
                    exit;
                }
            }

            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $token = trim($_POST['token']);

            error_log("Validating password");
            // Validate password
            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long';
                header('Location: index.php?module=auth&action=register&type=admin');
                exit;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Passwords do not match';
                header('Location: index.php?module=auth&action=register&type=admin');
                exit;
            }

            error_log("Validating token");
            // Validate token
            $token_query = "SELECT * FROM admin_tokens 
                           WHERE token = ? 
                           AND is_used = 0 
                           AND expires_at > NOW()";
            
            $stmt = $this->db->prepare($token_query);
            $stmt->execute([$token]);
            $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$token_data) {
                error_log("Invalid or expired token: " . $token);
                $_SESSION['error'] = 'Invalid or expired registration token';
                header('Location: index.php?module=auth&action=register&type=admin');
                exit;
            }

            error_log("Starting transaction");
            $this->db->beginTransaction();

            // Check if email already exists in users table
            error_log("Checking if email exists in users table");
            $check_email = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($check_email);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered');
            }

            // Check if email already exists in admins table
            error_log("Checking if email exists in admins table");
            $check_admin_email = "SELECT id FROM admins WHERE email = ?";
            $stmt = $this->db->prepare($check_admin_email);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered as admin');
            }

            error_log("Inserting into users table");
            // Insert into users table
            $insert_user = "INSERT INTO users (email, password_hash, name, user_type, status, created_at, updated_at) 
                           VALUES (?, ?, ?, 'admin', 'active', NOW(), NOW())";
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare($insert_user);
            $stmt->execute([
                $email,
                $hashed_password,
                $name
            ]);
            
            $user_id = $this->db->lastInsertId();
            error_log("User created with ID: " . $user_id);

            // Insert into admins table
            error_log("Inserting into admins table");
            $admin_role = $token_data['admin_level'] === 'super' ? 'super_admin' : 'admin';
            $insert_admin = "INSERT INTO admins (user_id, name, email, role, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt = $this->db->prepare($insert_admin);
            $stmt->execute([
                $user_id,
                $name,
                $email,
                $admin_role
            ]);

            // Mark token as used
            error_log("Marking token as used");
            $update_token = "UPDATE admin_tokens 
                           SET is_used = 1, 
                               used_by = ?, 
                               used_at = NOW() 
                           WHERE id = ?";
            $stmt = $this->db->prepare($update_token);
            $stmt->execute([$user_id, $token_data['id']]);

            $this->db->commit();
            error_log("Transaction committed successfully");

            $_SESSION['success'] = 'Registration successful! Please login to continue.';
            header('Location: index.php?module=auth&action=login&type=admin');
            exit;

        } catch (Exception $e) {
            error_log("Error in process_admin_register: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if (isset($this->db) && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
            header('Location: index.php?module=auth&action=register&type=admin');
            exit;
        }
    }

    /**
     * Display admin registration form
     */
    public function admin_register() {
        // Load the admin registration view
        require_once 'modules/auth/views/admin_register.php';
    }

    /**
     * Display registration form based on user type
     */
    public function register() {
        // Allow patient, doctor and service registration
        if (!in_array($this->userType, ['patient', 'doctor', 'service'])) {
            $_SESSION['error'] = "Invalid registration type";
            header("Location: " . SITE_URL . "index.php");
            exit;
        }

        // Load the registration view
        require_once 'modules/auth/views/register.php';
    }

    public function process_register() {
        try {
            error_log("Starting registration process");
            error_log("POST data received: " . print_r($_POST, true));
            
            // Validate user type
            $userType = $_POST['user_type'] ?? '';
            if (!in_array($userType, ['patient', 'doctor', 'service_provider'])) {
                throw new Exception("Invalid user type");
            }

            // Validate common required fields
            $required_fields = ['username', 'email', 'password', 'confirm_password', 'user_type'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    error_log("Missing required field: {$field}");
                    $_SESSION['error'] = "Please fill in all required fields. Missing: {$field}";
                    header("Location: " . SITE_URL . "index.php?module=auth&action=register");
                    exit;
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email format: {$_POST['email']}");
                $_SESSION['error'] = "Invalid email format";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register");
                exit;
            }

            // Check if passwords match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                error_log("Passwords do not match");
                $_SESSION['error'] = "Passwords do not match";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register");
                exit;
            }

            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                error_log("Email already exists: {$_POST['email']}");
                $_SESSION['error'] = "Email already registered";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register");
                exit;
            }

            error_log("Starting database transaction for registration");
            $this->db->beginTransaction();

            try {
                // Insert into users table
                error_log("Inserting into users table");
                $stmt = $this->db->prepare("
                    INSERT INTO users (email, password_hash, name, user_type, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['username'],
                    $userType === 'service_provider' ? 'service' : $userType
                ]);
                $user_id = $this->db->lastInsertId();
                error_log("User created with ID: {$user_id}");

                // Insert into specific table based on user type
                if ($userType === 'patient') {
                    error_log("Inserting into patients table");
                    $stmt = $this->db->prepare("
                        INSERT INTO patients (name, email, phone, dob, gender, emergency_contact, address, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
                    ");
                    $stmt->execute([
                        $_POST['first_name'] . ' ' . $_POST['last_name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['date_of_birth'],
                        $_POST['gender'],
                        $_POST['emergency_contact'],
                        $_POST['address']
                    ]);
                } elseif ($userType === 'doctor') {
                    error_log("Inserting into doctors table");
                    $stmt = $this->db->prepare("
                        INSERT INTO doctors (user_id, name, email, phone, specialization, qualification, 
                                            license_number, experience_years, consultation_fee, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user_id,
                        $_POST['first_name'] . ' ' . $_POST['last_name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['specialization'],
                        $_POST['qualification'],
                        $_POST['license_number'] ?? null,
                        $_POST['experience'] ?? 0,
                        $_POST['consultation_fee'] ?? 0
                    ]);
                } elseif ($userType === 'service_provider') {
                    error_log("Inserting into service_providers table");
                    $stmt = $this->db->prepare("
                        INSERT INTO service_providers (
                            user_id, company_name, email, phone, service_type, 
                            address, operating_hours, service_area, license_number,
                            status, created_at, updated_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?,
                            'pending', NOW(), NOW()
                        )
                    ");
                    $stmt->execute([
                        $user_id,
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['service_type'],
                        $_POST['address'],
                        $_POST['operating_hours'] ?? null,
                        $_POST['service_area'] ?? null,
                        $_POST['license_number'] ?? null
                    ]);
                }

                // Commit transaction
                error_log("Committing transaction");
                $this->db->commit();

                // Set success message
                $_SESSION['success'] = "Registration successful! Please login to continue.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=" . $userType);
                exit;

            } catch (PDOException $e) {
                error_log("Database error during registration: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                error_log("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }

        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                error_log("Rolling back transaction");
                $this->db->rollBack();
            }
            error_log("Registration error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "Database error during registration: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                error_log("Rolling back transaction");
                $this->db->rollBack();
            }
            error_log("General error during registration: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "An error occurred during registration: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register");
            exit;
        }
    }

    public function process_service_register() {
        try {
            error_log("Starting service provider registration process");
            error_log("POST data received: " . print_r($_POST, true));
            
            // Validate required fields
            $required_fields = ['company_name', 'email', 'phone', 'service_type', 'address', 'operating_hours', 'service_area', 'license_number', 'password', 'confirm_password'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    error_log("Missing required field: {$field}");
                    $_SESSION['error'] = "Please fill in all required fields. Missing: {$field}";
                    header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
                    exit;
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email format: {$_POST['email']}");
                $_SESSION['error'] = "Invalid email format";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
                exit;
            }

            // Check if passwords match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                error_log("Passwords do not match");
                $_SESSION['error'] = "Passwords do not match";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
                exit;
            }

            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                error_log("Email already exists: {$_POST['email']}");
                $_SESSION['error'] = "Email already registered";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
                exit;
            }

            error_log("Starting database transaction for service registration");
            $this->db->beginTransaction();

            try {
                // Insert into users table
                error_log("Inserting into users table");
                $stmt = $this->db->prepare("
                    INSERT INTO users (email, password_hash, name, user_type, created_at, updated_at)
                    VALUES (?, ?, ?, 'service', NOW(), NOW())
                ");
                $stmt->execute([
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['company_name']
                ]);
                $user_id = $this->db->lastInsertId();
                error_log("User created with ID: {$user_id}");

                // Insert into service_providers table
                error_log("Inserting into service_providers table");
                $stmt = $this->db->prepare("
                    INSERT INTO service_providers (
                        user_id, company_name, email, phone, service_type, 
                        address, operating_hours, service_area, license_number,
                        status, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?,
                        'pending', NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    $user_id,
                    $_POST['company_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['service_type'],
                    $_POST['address'],
                    $_POST['operating_hours'],
                    $_POST['service_area'],
                    $_POST['license_number']
                ]);

                // Commit transaction
                error_log("Committing transaction");
                $this->db->commit();

                // Set success message
                $_SESSION['success'] = "Registration successful! Please login to continue.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=service");
                exit;

            } catch (PDOException $e) {
                error_log("Database error during service registration: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                error_log("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }

        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                error_log("Rolling back transaction");
                $this->db->rollBack();
            }
            error_log("Service registration error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "Database error during registration: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                error_log("Rolling back transaction");
                $this->db->rollBack();
            }
            error_log("General error during service registration: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "An error occurred during registration: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=service");
            exit;
        }
    }

    public function service_register() {
        // Load the service registration view
        require_once 'modules/auth/views/service_register.php';
    }

    /**
     * Process doctor registration
     */
    public function process_doctor_register() {
        try {
            error_log("Starting doctor registration process");
            
            // Validate required fields
            $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password', 
                               'specialization', 'qualification', 'license_number', 'experience_years', 'consultation_fee'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['error'] = "Please fill in all required fields. Missing: " . str_replace('_', ' ', $field);
                    header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=doctor");
                    exit;
                }
            }
            
            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=doctor");
                exit;
            }
            
            // Check if passwords match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = "Passwords do not match";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=doctor");
                exit;
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already registered";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=doctor");
                exit;
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Insert into users table
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, user_type, created_at) 
                VALUES (?, ?, 'doctor', NOW())
            ");
            
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([$_POST['email'], $hashedPassword]);
            $userId = $this->db->lastInsertId();
            
            // Insert into doctors table
            $stmt = $this->db->prepare("
                INSERT INTO doctors (user_id, name, email, phone, specialization, qualification, 
                                    license_number, experience_years, consultation_fee, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $_POST['first_name'] . ' ' . $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['specialization'],
                $_POST['qualification'],
                $_POST['license_number'],
                $_POST['experience_years'],
                $_POST['consultation_fee']
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            // Set success message
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=doctor");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            error_log("Error in doctor registration: " . $e->getMessage());
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=doctor");
            exit;
        }
    }

    /**
     * Process patient registration
     */
    public function process_patient_register() {
        try {
            error_log("Starting patient registration process");
            
            // Validate required fields
            $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password', 'date_of_birth', 'gender'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $_SESSION['error'] = "Please fill in all required fields. Missing: " . str_replace('_', ' ', $field);
                    header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
                    exit;
                }
            }
            
            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
                exit;
            }
            
            // Check if passwords match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = "Passwords do not match";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
                exit;
            }
            
            // Check password strength
            if (strlen($_POST['password']) < 8 || !preg_match('/[A-Z]/', $_POST['password']) || 
                !preg_match('/[a-z]/', $_POST['password']) || !preg_match('/[0-9]/', $_POST['password'])) {
                $_SESSION['error'] = "Password must be at least 8 characters and include uppercase, lowercase, and numbers";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
                exit;
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already registered";
                header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
                exit;
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Insert into users table
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, user_type, created_at) 
                VALUES (?, ?, 'patient', NOW())
            ");
            
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([$_POST['email'], $hashedPassword]);
            $userId = $this->db->lastInsertId();
            
            // Insert into patients table
            $stmt = $this->db->prepare("
                INSERT INTO patients (user_id, name, email, phone, dob, gender, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $_POST['first_name'] . ' ' . $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['date_of_birth'],
                $_POST['gender']
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            // Set success message
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=patient");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            error_log("Error in patient registration: " . $e->getMessage());
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
            header("Location: " . SITE_URL . "index.php?module=auth&action=register&type=patient");
            exit;
        }
    }

    /**
     * Handle password reset requests
     */
    public function reset_password() {
        // If this is a GET request, show the reset password form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once 'modules/auth/views/reset_password.php';
            return;
        }
        
        // Process POST request for password reset
        try {
            // Validate email
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $_SESSION['error'] = "Please enter a valid email address";
                header("Location: " . SITE_URL . "index.php?module=auth&action=reset_password");
                exit;
            }
            
            // Check if the email exists in the users table
            $stmt = $this->db->prepare("SELECT id, name, user_type FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Still show success message for security (don't reveal if email exists)
                $_SESSION['success'] = "If your email address exists in our database, you will receive a password recovery link shortly.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login");
                exit;
            }
            
            // Generate a unique token and expiry time (24 hours)
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Store the reset token in the database
            // First, check if a password_resets table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'password_resets'");
            $tableExists = ($stmt->rowCount() > 0);
            
            if (!$tableExists) {
                // Create the password_resets table if it doesn't exist
                $this->db->exec("CREATE TABLE password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(100) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Delete any existing tokens for this email
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            // Insert the new token
            $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expiry]);
            
            // Send an email with the reset link
            require_once __DIR__ . '/../../classes/EmailService.php';
            $emailService = new EmailService();
            
            // Create reset URL
            $resetUrl = SITE_URL . "index.php?module=auth&action=complete_reset&token=" . $token;
            
            // Prepare email content
            $subject = "Password Reset Request - " . SITE_NAME;
            $name = $user['name'];
            
            $message = "
            <html>
            <head>
                <title>Password Reset</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4a6ea9; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #4a6ea9; color: white; text-decoration: none; border-radius: 5px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Password Reset</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$name},</p>
                        <p>We received a request to reset your password. If you didn't make this request, you can ignore this email.</p>
                        <p>To reset your password, please click the button below:</p>
                        <p style='text-align: center;'>
                            <a href='{$resetUrl}' class='button'>Reset Password</a>
                        </p>
                        <p>Or copy and paste this URL into your browser:</p>
                        <p>{$resetUrl}</p>
                        <p>This link is valid for 24 hours.</p>
                        <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this message.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Send the email
            $emailSent = $emailService->send($email, $name, $subject, $message);
            
            if (!$emailSent) {
                error_log("Failed to send password reset email to: " . $email);
            }
            
            // Show success message (don't reveal if email was actually sent for security)
            $_SESSION['success'] = "If your email address exists in our database, you will receive a password recovery link shortly.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login");
            exit;
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during the password reset process. Please try again.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=reset_password");
            exit;
        }
    }

    /**
     * Handle password reset completion
     */
    public function complete_reset() {
        try {
            // Get token from URL
            $token = $_GET['token'] ?? '';
            if (empty($token)) {
                $_SESSION['error'] = "Invalid or missing token.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login");
                exit;
            }
            
            // Verify token exists and is not expired
            $stmt = $this->db->prepare("
                SELECT email, expires_at 
                FROM password_resets 
                WHERE token = ? AND expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reset) {
                $_SESSION['error'] = "Invalid or expired token. Please request a new password reset.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=reset_password");
                exit;
            }
            
            // If GET request, show reset form
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                require_once 'modules/auth/views/complete_reset.php';
                return;
            }
            
            // Process POST request to complete password reset
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate password
            if (strlen($password) < 8) {
                $_SESSION['error'] = "Password must be at least 8 characters.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=complete_reset&token=" . $token);
                exit;
            }
            
            // Validate password confirmation
            if ($password !== $confirm_password) {
                $_SESSION['error'] = "Passwords do not match.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=complete_reset&token=" . $token);
                exit;
            }
            
            // Get user from email
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$reset['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $_SESSION['error'] = "User not found.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=reset_password");
                exit;
            }
            
            // Update password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user['id']]);
            
            // Remove used token
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            // Set success message
            $_SESSION['success'] = "Your password has been updated successfully. You can now log in with your new password.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login");
            exit;
            
        } catch (Exception $e) {
            error_log("Password reset completion error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during the password reset process. Please try again.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=reset_password");
            exit;
        }
    }
}
