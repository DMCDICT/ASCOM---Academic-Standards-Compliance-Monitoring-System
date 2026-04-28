-- ============================================================
-- Course Syllabi Table
-- Stores comprehensive course syllabus data with all required
-- sections: books, e-books, web resources, description,
-- PEO/outcomes, learning plan, exams, grading, policies, etc.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing table if exists
DROP TABLE IF EXISTS course_syllabi;

CREATE TABLE course_syllabi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    academic_year VARCHAR(50) DEFAULT NULL,
    term VARCHAR(50) DEFAULT NULL,
    
    -- Course Overview
    course_description TEXT DEFAULT NULL,
    course_objectives TEXT DEFAULT NULL,
    expected_course_outcomes TEXT DEFAULT NULL, -- PEO/Program Outcomes
    
    -- Books (JSON array: [{title, author, isbn, publisher, year, edition}])
    books TEXT DEFAULT NULL,
    
    -- E-books (JSON array: [{title, author, url, access_date, publisher, year}])
    ebooks TEXT DEFAULT NULL,
    
    -- Web Resources (JSON array: [{title, url, access_date, description}])
    web_resources TEXT DEFAULT NULL,
    
    -- Learning Plan (JSON: {week: {topic, activities, assessment}})
    learning_plan TEXT DEFAULT NULL,
    
    -- Exam Schedules (JSON: {prelim: {duration, weeks: []}, midterm: {}, prefinal: {}, final: {}})
    exam_schedules TEXT DEFAULT NULL,
    
    -- Grading System (JSON: {components: [{name, percentage, breakdown}]})
    grading_system TEXT DEFAULT NULL,
    
    -- Course Requirements (text)
    course_requirements TEXT DEFAULT NULL,
    
    -- Course Expectations (text)
    course_expectations TEXT DEFAULT NULL,
    
    -- Remote/Online Classroom Policies (text)
    remote_policies TEXT DEFAULT NULL,
    
    -- References (APA 7 format, JSON array)
    references TEXT DEFAULT NULL,
    
    -- Status tracking
    status ENUM('draft', 'submitted', 'ph_review', 'ph_approved', 'dean_approved', 'revision_requested') DEFAULT 'draft',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    submitted_at DATETIME DEFAULT NULL,
    ph_approved_at DATETIME DEFAULT NULL,
    dean_approved_at DATETIME DEFAULT NULL,
    
    -- Program Head Review
    ph_reviewer_id INT DEFAULT NULL,
    ph_review_comments TEXT DEFAULT NULL,
    
    -- Dean Review
    dean_reviewer_id INT DEFAULT NULL,
    dean_review_comments TEXT DEFAULT NULL,
    
    -- Revision tracking
    revision_count INT DEFAULT 0,
    last_revision_note TEXT DEFAULT NULL,
    
    -- Foreign keys
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ph_reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (dean_reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Ensure one syllabus per course per teacher per term
    UNIQUE KEY unique_course_teacher_term (course_id, teacher_id, academic_year, term),
    
    -- Indexes
    INDEX idx_course (course_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year),
    INDEX idx_term (term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'course_syllabi table created successfully!' AS status;