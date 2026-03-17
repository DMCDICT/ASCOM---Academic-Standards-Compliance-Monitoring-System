-- Add created_by column to courses table
ALTER TABLE courses ADD COLUMN created_by INT NULL AFTER year_level;

-- Add created_by column to programs table  
ALTER TABLE programs ADD COLUMN created_by INT NULL AFTER major;

-- Add foreign key constraints to link created_by to users table
ALTER TABLE courses ADD CONSTRAINT fk_courses_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE programs ADD CONSTRAINT fk_programs_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add indexes for better performance
CREATE INDEX idx_courses_created_by ON courses(created_by);
CREATE INDEX idx_programs_created_by ON programs(created_by);
