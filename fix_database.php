<?php
// fix_database.php - Fix database structure issues
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Database Structure Fixer</h2>";

// Fix school_years table
echo "<h3>Fixing school_years table...</h3>";
$check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_sy->num_rows === 0) {
    $create_sy = "CREATE TABLE school_years (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_year_label VARCHAR(50) UNIQUE NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($create_sy)) {
        echo "✅ Created school_years table<br>";
    } else {
        echo "❌ Failed to create school_years table: " . $conn->error . "<br>";
    }
} else {
    // Check if it has the new structure
    $columns = $conn->query("DESCRIBE school_years");
    $has_new_structure = false;
    while ($row = $columns->fetch_assoc()) {
        if ($row['Field'] === 'school_year_label') {
            $has_new_structure = true;
            break;
        }
    }
    
    if (!$has_new_structure) {
        // Convert old structure to new
        $alter_sy = "ALTER TABLE school_years 
            ADD COLUMN school_year_label VARCHAR(50) UNIQUE NOT NULL AFTER id,
            ADD COLUMN start_date DATE NOT NULL DEFAULT '2023-08-01' AFTER school_year_label,
            ADD COLUMN end_date DATE NOT NULL DEFAULT '2024-05-31' AFTER start_date,
            ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Inactive' AFTER end_date,
            ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status";
        
        if ($conn->query($alter_sy)) {
            echo "✅ Updated school_years table structure<br>";
            
            // Update existing data
            $update_sy = "UPDATE school_years SET 
                school_year_label = CONCAT(year_start, '-', year_end),
                start_date = CONCAT(year_start, '-08-01'),
                end_date = CONCAT(year_end, '-05-31'),
                status = CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END";
            
            if ($conn->query($update_sy)) {
                echo "✅ Updated school_years data<br>";
            }
        } else {
            echo "❌ Failed to update school_years table: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ school_years table already has correct structure<br>";
    }
}

// Fix school_terms table
echo "<h3>Fixing school_terms table...</h3>";
$check_st = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($check_st->num_rows === 0) {
    $create_st = "CREATE TABLE school_terms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        school_year_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
    )";
    if ($conn->query($create_st)) {
        echo "✅ Created school_terms table<br>";
    } else {
        echo "❌ Failed to create school_terms table: " . $conn->error . "<br>";
    }
} else {
    echo "✅ school_terms table exists<br>";
}

// Fix departments table
echo "<h3>Fixing departments table...</h3>";
$check_dept = $conn->query("SHOW TABLES LIKE 'departments'");
if ($check_dept->num_rows === 0) {
    $create_dept = "CREATE TABLE departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        department_code VARCHAR(10) UNIQUE NOT NULL,
        department_name VARCHAR(100) NOT NULL,
        color_code VARCHAR(7) DEFAULT '#4A7DFF',
        created_by VARCHAR(100) DEFAULT 'Super Admin MIS',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($create_dept)) {
        echo "✅ Created departments table<br>";
    } else {
        echo "❌ Failed to create departments table: " . $conn->error . "<br>";
    }
} else {
    // Check if it has the new structure
    $columns = $conn->query("DESCRIBE departments");
    $has_new_structure = false;
    while ($row = $columns->fetch_assoc()) {
        if ($row['Field'] === 'department_code') {
            $has_new_structure = true;
            break;
        }
    }
    
    if (!$has_new_structure) {
        // Convert old structure to new
        $alter_dept = "ALTER TABLE departments 
            ADD COLUMN department_code VARCHAR(10) UNIQUE NOT NULL AFTER id,
            ADD COLUMN color_code VARCHAR(7) DEFAULT '#4A7DFF' AFTER department_name,
            ADD COLUMN created_by VARCHAR(100) DEFAULT 'Super Admin MIS' AFTER color_code";
        
        if ($conn->query($alter_dept)) {
            echo "✅ Updated departments table structure<br>";
        } else {
            echo "❌ Failed to update departments table: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ departments table already has correct structure<br>";
    }
}

// Fix users table
echo "<h3>Fixing users table...</h3>";
$check_users = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_users->num_rows > 0) {
    $columns = $conn->query("DESCRIBE users");
    $has_new_structure = false;
    while ($row = $columns->fetch_assoc()) {
        if ($row['Field'] === 'employee_no') {
            $has_new_structure = true;
            break;
        }
    }
    
    if (!$has_new_structure) {
        // Add new columns to users table
        $alter_users = "ALTER TABLE users 
            ADD COLUMN employee_no VARCHAR(10) UNIQUE AFTER id,
            ADD COLUMN institutional_email VARCHAR(255) UNIQUE AFTER email,
            ADD COLUMN mobile_no VARCHAR(20) AFTER institutional_email,
            ADD COLUMN name_prefix VARCHAR(10) AFTER mobile_no,
            ADD COLUMN created_by VARCHAR(100) AFTER department_id";
        
        if ($conn->query($alter_users)) {
            echo "✅ Updated users table structure<br>";
        } else {
            echo "❌ Failed to update users table: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ users table already has correct structure<br>";
    }
}

echo "<h3>Database structure fix completed!</h3>";
$conn->close();
?> 