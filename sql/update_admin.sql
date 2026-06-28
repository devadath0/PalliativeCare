-- Update admin@palliative.care to super admin
UPDATE admins 
SET role = 'super_admin' 
WHERE email = 'admin@palliative.care';

-- Update user type in users table
UPDATE users 
SET user_type = 'admin',
    status = 'active'
WHERE email = 'admin@palliative.care';

-- Verify the updates
SELECT 
    u.id, u.email, u.name, u.user_type, u.status,
    a.role as admin_role, a.last_login
FROM users u
LEFT JOIN admins a ON u.id = a.user_id
WHERE u.email = 'admin@palliative.care'; 