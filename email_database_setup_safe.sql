-- Add fields to cab_bookings table
SET @cabBookingsExists = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings');

SET @reminderSentExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings' AND COLUMN_NAME = 'reminder_sent');
SET @reminderSentAtExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings' AND COLUMN_NAME = 'reminder_sent_at');
SET @confirmedAtExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings' AND COLUMN_NAME = 'confirmed_at');
SET @completedAtExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings' AND COLUMN_NAME = 'completed_at');
SET @cancelledAtExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'cab_bookings' AND COLUMN_NAME = 'cancelled_at');

SET @sql1 = IF(@cabBookingsExists > 0 AND @reminderSentExists = 0, 'ALTER TABLE cab_bookings ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0', 'SELECT 1');
SET @sql2 = IF(@cabBookingsExists > 0 AND @reminderSentAtExists = 0, 'ALTER TABLE cab_bookings ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL', 'SELECT 1');
SET @sql3 = IF(@cabBookingsExists > 0 AND @confirmedAtExists = 0, 'ALTER TABLE cab_bookings ADD COLUMN confirmed_at DATETIME DEFAULT NULL', 'SELECT 1');
SET @sql4 = IF(@cabBookingsExists > 0 AND @completedAtExists = 0, 'ALTER TABLE cab_bookings ADD COLUMN completed_at DATETIME DEFAULT NULL', 'SELECT 1');
SET @sql5 = IF(@cabBookingsExists > 0 AND @cancelledAtExists = 0, 'ALTER TABLE cab_bookings ADD COLUMN cancelled_at DATETIME DEFAULT NULL', 'SELECT 1');

PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;
PREPARE stmt5 FROM @sql5; EXECUTE stmt5; DEALLOCATE PREPARE stmt5;

-- Add fields to appointments table
SET @appointmentsExists = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'appointments');

SET @appReminderSentExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'reminder_sent');
SET @appReminderSentAtExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'palliative' AND TABLE_NAME = 'appointments' AND COLUMN_NAME = 'reminder_sent_at');

SET @sql6 = IF(@appointmentsExists > 0 AND @appReminderSentExists = 0, 'ALTER TABLE appointments ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0', 'SELECT 1');
SET @sql7 = IF(@appointmentsExists > 0 AND @appReminderSentAtExists = 0, 'ALTER TABLE appointments ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL', 'SELECT 1');

PREPARE stmt6 FROM @sql6; EXECUTE stmt6; DEALLOCATE PREPARE stmt6;
PREPARE stmt7 FROM @sql7; EXECUTE stmt7; DEALLOCATE PREPARE stmt7; 