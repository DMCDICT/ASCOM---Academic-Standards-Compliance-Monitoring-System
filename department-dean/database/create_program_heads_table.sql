-- program_heads table
-- Assigns a teacher as program head for a specific program
-- Constraints: one head per program, one program per head

CREATE TABLE IF NOT EXISTS program_heads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    teacher_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Ensure only one head per program
    UNIQUE KEY unique_program (program_id),
    
    -- Ensure a teacher can only be head of one program
    UNIQUE KEY unique_teacher (teacher_id),
    
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
