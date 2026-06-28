-- Add fields to cab_bookings table
ALTER TABLE cab_bookings ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0;
ALTER TABLE cab_bookings ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN confirmed_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN completed_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN cancelled_at DATETIME DEFAULT NULL;

-- Add fields to appointments table
ALTER TABLE appointments ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0;
ALTER TABLE appointments ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL; 