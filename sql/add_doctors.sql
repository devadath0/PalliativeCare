-- Add more doctors with various specializations
-- First, add users
INSERT INTO `users` (`email`, `password_hash`, `name`, `user_type`, `status`, `created_at`, `updated_at`) VALUES
('dr.smith@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Smith', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.johnson@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Michael Johnson', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.patel@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Anita Patel', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.wilson@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Wilson', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.chen@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Li Chen', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.garcia@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Maria Garcia', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.brown@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. James Brown', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('dr.taylor@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Emily Taylor', 'doctor', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Then, add doctors with specializations
INSERT INTO `doctors` (`user_id`, `name`, `email`, `phone`, `specialization`, `qualification`, `experience_years`, `license_number`, `availability_status`, `consultation_fee`, `created_at`, `updated_at`) 
VALUES
-- Get the user_id for each doctor (assuming the IDs are sequential starting from the last user_id + 1)
((SELECT id FROM users WHERE email = 'dr.smith@palliative.com'), 'Dr. Sarah Smith', 'dr.smith@palliative.com', '555-111-2222', 'General Practitioner', 'MD', 8, 'GP12345', 'available', 150.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.johnson@palliative.com'), 'Dr. Michael Johnson', 'dr.johnson@palliative.com', '555-222-3333', 'Cardiologist', 'MD, PhD', 12, 'CARD6789', 'available', 250.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.patel@palliative.com'), 'Dr. Anita Patel', 'dr.patel@palliative.com', '555-333-4444', 'Neurologist', 'MD', 10, 'NEUR7890', 'available', 225.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.wilson@palliative.com'), 'Dr. Robert Wilson', 'dr.wilson@palliative.com', '555-444-5555', 'Pulmonologist', 'MD', 15, 'PULM1234', 'available', 200.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.chen@palliative.com'), 'Dr. Li Chen', 'dr.chen@palliative.com', '555-555-6666', 'Infectious Disease', 'MD, MPH', 9, 'INFD5678', 'available', 175.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.garcia@palliative.com'), 'Dr. Maria Garcia', 'dr.garcia@palliative.com', '555-666-7777', 'ENT Specialist', 'MD', 7, 'ENT9012', 'available', 180.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.brown@palliative.com'), 'Dr. James Brown', 'dr.brown@palliative.com', '555-777-8888', 'Gastroenterologist', 'MD', 11, 'GAST3456', 'available', 210.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
((SELECT id FROM users WHERE email = 'dr.taylor@palliative.com'), 'Dr. Emily Taylor', 'dr.taylor@palliative.com', '555-888-9999', 'Family Medicine', 'MD', 6, 'FAM7890', 'available', 140.00, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Note: The password hash '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' corresponds to 'password' 