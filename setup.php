<?php
/**
 * Database Setup Script
 * Palliative Care System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';  // No password required
$DB_NAME = 'palliative';

function executeQuery($conn, $query, $description) {
    if (!$conn->query($query)) {
        die("<div style='color: red; margin: 10px 0;'>Error {$description}: " . $conn->error . "</div>");
    }
    echo "<div style='color: green; margin: 5px 0;'>✓ {$description} successful</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palliative Care System - Database Setup</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="fontawsom-all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .credentials {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .step-header {
            color: #007bff;
            margin-top: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Palliative Care System - Database Setup</h1>
        
        <?php
        try {
            // First create the database if it doesn't exist
            echo "<h2 class='step-header'><i class='fas fa-database'></i> Step 1: Connecting to MySQL Server</h2>";
            $root_conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
            if ($root_conn->connect_error) {
                throw new Exception("Connection failed: " . $root_conn->connect_error);
            }
            echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Connected to MySQL server successfully</div>";

            // Drop and recreate database
            echo "<h2 class='step-header'><i class='fas fa-plus-circle'></i> Step 2: Creating Database</h2>";
            executeQuery($root_conn, "DROP DATABASE IF EXISTS {$DB_NAME}", "Drop existing database");
            executeQuery($root_conn, "CREATE DATABASE {$DB_NAME}", "Create new database");
            $root_conn->close();

            // Connect to the new database
            echo "<h2 class='step-header'><i class='fas fa-table'></i> Step 3: Creating Tables</h2>";
            $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Create Users table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                user_type ENUM('patient', 'doctor', 'service', 'admin') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Users table");

            // Create Patients table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS patients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(20),
                dob DATE,
                gender ENUM('male', 'female', 'other'),
                blood_group VARCHAR(5),
                emergency_contact VARCHAR(255),
                address TEXT,
                medical_history TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Patients table");

            // Create Doctors table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS doctors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(20),
                specialization VARCHAR(255),
                qualification VARCHAR(255),
                experience_years INT,
                license_number VARCHAR(50) UNIQUE,
                availability_status ENUM('available', 'unavailable') DEFAULT 'available',
                consultation_fee DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Doctors table");

            // Create Service Providers table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS service_providers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(20),
                service_type ENUM('cab', 'medicine', 'equipment') NOT NULL,
                address TEXT,
                operating_hours VARCHAR(255),
                service_area TEXT,
                license_number VARCHAR(50),
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Service Providers table");

            // Create Admins table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                role ENUM('super_admin', 'admin') DEFAULT 'admin',
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Admins table");

            // Create Appointments table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS appointments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                appointment_date DATETIME NOT NULL,
                reason TEXT,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Appointments table");

            // Create Prescriptions table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS prescriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                diagnosis TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Prescriptions table");

            // Create Prescription Items table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS prescription_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prescription_id INT NOT NULL,
                medicine VARCHAR(255) NOT NULL,
                dosage VARCHAR(255),
                frequency VARCHAR(255),
                duration VARCHAR(255),
                instructions TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Prescription Items table");

            // Create Service Requests table
            executeQuery($conn, "CREATE TABLE IF NOT EXISTS service_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                provider_id INT NOT NULL,
                service_type ENUM('cab', 'medicine', 'equipment') NOT NULL,
                requested_date DATETIME NOT NULL,
                status ENUM('pending', 'approved', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
                notes TEXT,
                provider_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", "Create Service Requests table");

            echo "<h2 class='step-header'><i class='fas fa-search'></i> Step 4: Creating Indexes</h2>";
            // Create indexes for better performance
            executeQuery($conn, "CREATE INDEX idx_patient_email ON patients(email)", "Create patient email index");
            executeQuery($conn, "CREATE INDEX idx_doctor_email ON doctors(email)", "Create doctor email index");
            executeQuery($conn, "CREATE INDEX idx_service_provider_email ON service_providers(email)", "Create service provider email index");
            executeQuery($conn, "CREATE INDEX idx_admin_email ON admins(email)", "Create admin email index");
            executeQuery($conn, "CREATE INDEX idx_appointment_date ON appointments(appointment_date)", "Create appointment date index");
            executeQuery($conn, "CREATE INDEX idx_service_request_date ON service_requests(requested_date)", "Create service request date index");

            echo "<h2 class='step-header'><i class='fas fa-user-shield'></i> Step 5: Creating Default Admin User</h2>";
            // Insert default admin user
            executeQuery($conn, "INSERT INTO users (email, password, user_type) 
            VALUES (
                'admin@palliative.care',
                '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'admin'
            )", "Create default admin user");

            executeQuery($conn, "INSERT INTO admins (name, email, role) 
            VALUES (
                'System Admin',
                'admin@palliative.care',
                'super_admin'
            )", "Create admin profile");

            echo "<div class='alert alert-success' style='margin-top: 20px;'>
                <i class='fas fa-check-circle'></i> ✨ Database setup completed successfully! ✨
            </div>";
            
            echo "<div class='credentials'>
                <h3><i class='fas fa-key'></i> Default Admin Credentials</h3>
                <p><strong>Email:</strong> admin@palliative.care</p>
                <p><strong>Password:</strong> password</p>
                <p class='text-muted'><i class='fas fa-exclamation-triangle'></i> Please change the admin password after first login.</p>
            </div>";

            $conn->close();

        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>
                <i class='fas fa-exclamation-circle'></i> Setup failed: " . $e->getMessage() . "
            </div>";
        }
        ?>
    </div>
</body>
</html>
