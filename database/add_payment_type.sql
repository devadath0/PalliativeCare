 -- First drop the existing foreign key constraint
ALTER TABLE payments DROP FOREIGN KEY payments_ibfk_1;

-- Drop the order_id column since we'll replace it with reference_id
ALTER TABLE payments DROP COLUMN order_id;

-- Add the reference_id column
ALTER TABLE payments ADD COLUMN reference_id INT NOT NULL AFTER id;

-- Add indexes to improve query performance
CREATE INDEX idx_payment_reference ON payments(payment_type, reference_id);
