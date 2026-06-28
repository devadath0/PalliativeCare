-- SQL to update patient_issues table
-- Add patient_response and patient_response_at columns

-- Check if patient_response column exists and add it if not
SELECT COUNT(*) INTO @response_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'patient_issues' 
AND COLUMN_NAME = 'patient_response';

SET @add_response = IF(@response_exists = 0, 
    'ALTER TABLE patient_issues ADD COLUMN patient_response TEXT NULL AFTER admin_response',
    'SELECT "patient_response column already exists" AS message');

PREPARE stmt FROM @add_response;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if patient_response_at column exists and add it if not
SELECT COUNT(*) INTO @response_at_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'patient_issues' 
AND COLUMN_NAME = 'patient_response_at';

SET @add_response_at = IF(@response_at_exists = 0, 
    'ALTER TABLE patient_issues ADD COLUMN patient_response_at DATETIME NULL AFTER admin_response_at',
    'SELECT "patient_response_at column already exists" AS message');

PREPARE stmt FROM @add_response_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 