-- Create school_years table
CREATE TABLE IF NOT EXISTS `school_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year_label` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_year_label` (`school_year_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create school_terms table
CREATE TABLE IF NOT EXISTS `school_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_year_id` (`school_year_id`),
  CONSTRAINT `school_terms_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample school year
INSERT INTO `school_years` (`school_year_label`, `start_date`, `end_date`, `status`) VALUES
('2024-2025', '2024-08-01', '2025-05-31', 'Active');

-- Insert sample terms
INSERT INTO `school_terms` (`school_year_id`, `title`, `start_date`, `end_date`, `status`) VALUES
(1, 'First Semester', '2024-08-01', '2024-12-15', 'Active'),
(1, 'Second Semester', '2025-01-15', '2025-05-31', 'Inactive'); 