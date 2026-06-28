-- Create reports table
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `generated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(50) NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `description` text,
  `parameters` text,
  PRIMARY KEY (`id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reports` (`name`, `type`, `generated_by`, `file_path`, `description`, `parameters`) VALUES
('Monthly Patient Report', 'patient', 1, 'reports/patient_report_2023_10.pdf', 'Monthly summary of patient activities', '{"month": "October", "year": "2023"}'),
('Quarterly Service Usage', 'service', 1, 'reports/service_usage_q3_2023.pdf', 'Quarterly analysis of service provider usage', '{"quarter": "Q3", "year": "2023"}'),
('Annual Staff Performance', 'staff', 1, 'reports/staff_performance_2023.pdf', 'Annual staff performance metrics', '{"year": "2023"}'); 