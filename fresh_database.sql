-- ============================================================
-- ASCOM Fresh Database Setup
-- Clean database with seeded superadmin
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- Disable foreign key checks for clean drop
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Users Table
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_no VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'faculty',
    department_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_activity TIMESTAMP NULL,
    INDEX idx_employee_no (employee_no),
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_department (department_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. Departments Table
-- ============================================================
DROP TABLE IF EXISTS departments;
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(255) NOT NULL,
    department_code VARCHAR(50) UNIQUE NOT NULL,
    color_code VARCHAR(20) DEFAULT '#1976d2',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_department_code (department_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Programs Table
-- ============================================================
DROP TABLE IF EXISTS programs;
CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_code VARCHAR(50) NOT NULL,
    program_name VARCHAR(255) NOT NULL,
    major VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    INDEX idx_program_code (program_code),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Courses Table
-- ============================================================
DROP TABLE IF EXISTS courses;
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(50) NOT NULL,
    course_title VARCHAR(255) NOT NULL,
    units INT DEFAULT 0,
    program_id INT DEFAULT NULL,
    department_id INT DEFAULT NULL,
    year_level VARCHAR(50) DEFAULT NULL,
    term VARCHAR(50) DEFAULT NULL,
    academic_year VARCHAR(50) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Active',
    faculty_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_course_code (course_code),
    INDEX idx_program (program_id),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Roles Table
-- ============================================================
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    role_description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. User Roles Table
-- ============================================================
DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    department_id INT DEFAULT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. Program Heads Table
-- ============================================================
DROP TABLE IF EXISTS program_heads;
CREATE TABLE program_heads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    teacher_id INT NOT NULL,
    assigned_by INT DEFAULT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_program (program_id),
    UNIQUE KEY unique_teacher (teacher_id),
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. Course Assignments Table
-- ============================================================
DROP TABLE IF EXISTS course_assignments;
CREATE TABLE course_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    assigned_by INT DEFAULT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_course_teacher (course_id, teacher_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. School Years Table
-- ============================================================
DROP TABLE IF EXISTS school_years;
CREATE TABLE school_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year_label VARCHAR(50) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. Terms Table
-- ============================================================
DROP TABLE IF EXISTS terms;
CREATE TABLE terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    school_year_id INT DEFAULT NULL,
    term_order INT DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE SET NULL,
    INDEX idx_school_year (school_year_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. Library Books Table
-- ============================================================
DROP TABLE IF EXISTS library_books;
CREATE TABLE library_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_title VARCHAR(255) NOT NULL,
    author VARCHAR(255) DEFAULT NULL,
    isbn VARCHAR(50) DEFAULT NULL,
    department_id INT DEFAULT NULL,
    classification_id INT DEFAULT NULL,
    no_of_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_department (department_id),
    INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. Book References Table
-- ============================================================
DROP TABLE IF EXISTS book_references;
CREATE TABLE book_references (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_title VARCHAR(255) NOT NULL,
    author VARCHAR(255) DEFAULT NULL,
    isbn VARCHAR(50) DEFAULT NULL,
    publisher VARCHAR(255) DEFAULT NULL,
    edition VARCHAR(50) DEFAULT NULL,
    course_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. Activity Logs Table
-- ============================================================
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    target_entity VARCHAR(100) DEFAULT NULL,
    target_entity_id INT DEFAULT NULL,
    activity_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_timestamp (activity_timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. Course Proposals Table
-- ============================================================
DROP TABLE IF EXISTS course_proposals;
CREATE TABLE course_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_code VARCHAR(50) NOT NULL,
    course_code VARCHAR(50) DEFAULT NULL,
    course_title VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Draft',
    courses_data TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. Book Classifications Table
-- ============================================================
DROP TABLE IF EXISTS book_classifications;
CREATE TABLE book_classifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classification_code VARCHAR(50) NOT NULL,
    classification_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- INSERT SEEDED DATA
-- ============================================================

-- Insert default roles
INSERT INTO roles (role_name, role_description) VALUES 
('super_admin', 'System Administrator'),
('dean', 'Department Dean'),
('teacher', 'Faculty/Teacher'),
('qa', 'Quality Assurance'),
('librarian', 'Librarian');

-- Insert Super Admin User (password: admin123)
-- Using bcrypt hash for 'admin123'
INSERT INTO users (employee_no, username, password, first_name, last_name, title, email, role, is_active) VALUES
('SUPER001', 'super_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'Admin', 'admin@ascom.edu', 'super_admin', 1);

-- Insert Super Admin Role
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

-- Insert a sample department
INSERT INTO departments (department_name, department_code, color_code) VALUES
('College of Computing Studies', 'CCS', '#1976d2');

-- Insert a sample program
INSERT INTO programs (program_code, program_name, major, description, department_id) VALUES
('BSCS', 'Bachelor of Science in Computer Science', 'Computing', '4-year Computer Science program', 1);

-- Insert a sample course
INSERT INTO courses (course_code, course_title, units, program_id, department_id, year_level, term, academic_year, status) VALUES
('CS101', 'Introduction to Computer Science', 3, 1, 1, '1st Year', '1st Semester', '2025-2026', 'Active'),
('CS102', 'Data Structures and Algorithms', 3, 1, 1, '2nd Year', '1st Semester', '2025-2026', 'Active');

-- Insert School Year
INSERT INTO school_years (school_year_label, start_date, end_date, is_active) VALUES
('2025-2026', '2025-08-01', '2026-05-31', 1);

-- Insert Terms
INSERT INTO terms (name, school_year_id, term_order, start_date, end_date, is_active) VALUES
('1st Semester', 1, 1, '2025-08-01', '2025-12-31', 1),
('2nd Semester', 1, 2, '2026-01-01', '2026-05-31', 0);

-- Insert Book Classifications
INSERT INTO book_classifications (classification_code, classification_name) VALUES
('QA', 'Quality Assurance'),
('IT', 'Information Technology'),
('CS', 'Computer Science');

-- ============================================================
-- Done!
-- ============================================================
SELECT 'Database initialized successfully!' AS status;
