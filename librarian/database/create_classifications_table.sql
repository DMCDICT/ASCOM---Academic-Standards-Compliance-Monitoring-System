-- Create classifications table for library classification management
CREATE TABLE IF NOT EXISTS `classifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'DDC' COMMENT 'Classification type (DDC, LCC, etc.)',
  `call_number_range` VARCHAR(20) NOT NULL COMMENT 'Range like 000-099',
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_by` INT(11) NULL COMMENT 'User ID who created this classification',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_call_number_range` (`call_number_range`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_classifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library classification systems (Dewey Decimal, etc.)';

