-- Update Pharmacies table
ALTER TABLE pharmacies
    MODIFY status ENUM('active', 'inactive', 'suspended') DEFAULT 'active';

-- Add operating_hours column if it doesn't exist
SELECT COUNT(*) INTO @pharmacies_operating_hours FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'pharmacies' AND COLUMN_NAME = 'operating_hours';

SET @sql = CONCAT('SELECT "Column operating_hours already exists in pharmacies table"');
SET @sql = IF(@pharmacies_operating_hours = 0, 'ALTER TABLE pharmacies ADD COLUMN operating_hours VARCHAR(255) DEFAULT NULL AFTER license_number', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add delivery_available column if it doesn't exist
SELECT COUNT(*) INTO @pharmacies_delivery FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'pharmacies' AND COLUMN_NAME = 'delivery_available';

SET @sql = CONCAT('SELECT "Column delivery_available already exists in pharmacies table"');
SET @sql = IF(@pharmacies_delivery = 0, 'ALTER TABLE pharmacies ADD COLUMN delivery_available TINYINT(1) DEFAULT 1 AFTER operating_hours', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update medicines table structure
ALTER TABLE medicines
    MODIFY category ENUM('tablets', 'capsules', 'syrups', 'injections', 'topical', 'other') NOT NULL,
    MODIFY unit VARCHAR(50) NOT NULL,
    MODIFY stock_quantity INT NOT NULL DEFAULT 0,
    MODIFY status ENUM('active', 'out_of_stock', 'discontinued') DEFAULT 'active';

-- Add reorder_level column if it doesn't exist
SELECT COUNT(*) INTO @medicines_reorder FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicines' AND COLUMN_NAME = 'reorder_level';

SET @sql = CONCAT('SELECT "Column reorder_level already exists in medicines table"');
SET @sql = IF(@medicines_reorder = 0, 'ALTER TABLE medicines ADD COLUMN reorder_level INT AFTER stock_quantity', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add manufacturer column if it doesn't exist
SELECT COUNT(*) INTO @medicines_manufacturer FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicines' AND COLUMN_NAME = 'manufacturer';

SET @sql = CONCAT('SELECT "Column manufacturer already exists in medicines table"');
SET @sql = IF(@medicines_manufacturer = 0, 'ALTER TABLE medicines ADD COLUMN manufacturer VARCHAR(255) AFTER reorder_level', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add storage_instructions column if it doesn't exist
SELECT COUNT(*) INTO @medicines_storage FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicines' AND COLUMN_NAME = 'storage_instructions';

SET @sql = CONCAT('SELECT "Column storage_instructions already exists in medicines table"');
SET @sql = IF(@medicines_storage = 0, 'ALTER TABLE medicines ADD COLUMN storage_instructions TEXT AFTER manufacturer', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add batch_number column if it doesn't exist
SELECT COUNT(*) INTO @medicines_batch FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicines' AND COLUMN_NAME = 'batch_number';

SET @sql = CONCAT('SELECT "Column batch_number already exists in medicines table"');
SET @sql = IF(@medicines_batch = 0, 'ALTER TABLE medicines ADD COLUMN batch_number VARCHAR(50) AFTER storage_instructions', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add expiry_date column if it doesn't exist
SELECT COUNT(*) INTO @medicines_expiry FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicines' AND COLUMN_NAME = 'expiry_date';

SET @sql = CONCAT('SELECT "Column expiry_date already exists in medicines table"');
SET @sql = IF(@medicines_expiry = 0, 'ALTER TABLE medicines ADD COLUMN expiry_date DATE AFTER batch_number', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update medicine_orders table
ALTER TABLE medicine_orders
    MODIFY order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending';

-- Add notes column if it doesn't exist
SELECT COUNT(*) INTO @orders_notes FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'medicine_orders' AND COLUMN_NAME = 'notes';

SET @sql = CONCAT('SELECT "Column notes already exists in medicine_orders table"');
SET @sql = IF(@orders_notes = 0, 'ALTER TABLE medicine_orders ADD COLUMN notes TEXT AFTER delivery_address', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add idx_pharmacy_status index if it doesn't exist
SELECT COUNT(*) INTO @idx_pharmacy_status FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_NAME = 'medicines' AND INDEX_NAME = 'idx_pharmacy_status';

SET @sql = CONCAT('SELECT "Index idx_pharmacy_status already exists in medicines table"');
SET @sql = IF(@idx_pharmacy_status = 0, 'ALTER TABLE medicines ADD INDEX idx_pharmacy_status (pharmacy_id, status)', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add idx_medicine_name index if it doesn't exist
SELECT COUNT(*) INTO @idx_medicine_name FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_NAME = 'medicines' AND INDEX_NAME = 'idx_medicine_name';

SET @sql = CONCAT('SELECT "Index idx_medicine_name already exists in medicines table"');
SET @sql = IF(@idx_medicine_name = 0, 'ALTER TABLE medicines ADD INDEX idx_medicine_name (name)', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add idx_pharmacy_order_status index if it doesn't exist
SELECT COUNT(*) INTO @idx_pharmacy_order_status FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_NAME = 'medicine_orders' AND INDEX_NAME = 'idx_pharmacy_order_status';

SET @sql = CONCAT('SELECT "Index idx_pharmacy_order_status already exists in medicine_orders table"');
SET @sql = IF(@idx_pharmacy_order_status = 0, 'ALTER TABLE medicine_orders ADD INDEX idx_pharmacy_order_status (pharmacy_id, order_status)', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add idx_order_items index if it doesn't exist
SELECT COUNT(*) INTO @idx_order_items FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_NAME = 'medicine_order_items' AND INDEX_NAME = 'idx_order_items';

SET @sql = CONCAT('SELECT "Index idx_order_items already exists in medicine_order_items table"');
SET @sql = IF(@idx_order_items = 0, 'ALTER TABLE medicine_order_items ADD INDEX idx_order_items (order_id, medicine_id)', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if stock_movements table exists
SELECT COUNT(*) INTO @stock_movements_exists FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_NAME = 'stock_movements';

SET @sql = CONCAT('SELECT "Table stock_movements already exists"');
SET @sql = IF(@stock_movements_exists = 0, 'CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    movement_type ENUM("in", "out") NOT NULL,
    reference_type ENUM("purchase", "sale", "adjustment", "return") NOT NULL,
    reference_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    INDEX idx_medicine (medicine_id),
    INDEX idx_reference (reference_type, reference_id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
)', @sql);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create test pharmacy user
INSERT INTO users (email, password_hash, name, user_type, status) 
VALUES (
    'pharmacy@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Test Pharmacy',
    'service',
    'active'
) ON DUPLICATE KEY UPDATE id=id;

-- Get the ID of the pharmacy user
SELECT id INTO @pharmacy_user_id FROM users WHERE email = 'pharmacy@test.com';

-- Create test pharmacy record
INSERT INTO pharmacies (
    user_id, 
    name, 
    email, 
    phone, 
    address, 
    license_number,
    operating_hours,
    delivery_available,
    status
) VALUES (
    @pharmacy_user_id,
    'Test Pharmacy',
    'pharmacy@test.com',
    '555-0123',
    '123 Test Street, Test City, 12345',
    'PHR123456',
    'Mon-Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM',
    1,
    'active'
) ON DUPLICATE KEY UPDATE id=id;