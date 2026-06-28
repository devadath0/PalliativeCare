-- Admin Tokens table
CREATE TABLE IF NOT EXISTS `admin_tokens` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `admin_level` ENUM('standard', 'super') NOT NULL DEFAULT 'standard',
    `generated_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `is_used` BOOLEAN DEFAULT FALSE,
    `used_by` INT DEFAULT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`used_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 