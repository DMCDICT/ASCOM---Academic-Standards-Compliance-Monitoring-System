<?php
// Command-line database setup for ASCOM Monitoring System
// Run this with: php setup_database_cli.php

echo "=== ASCOM Monitoring System Database Setup ===\n\n";

try {
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "ascom_db";
    
    // Connect to MySQL server
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✅ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Database '$database' created or already exists\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($database);
    echo "✅ Selected database '$database'\n";
    
    // Create super_admin table
    $sql = "CREATE TABLE IF NOT EXISTS super_admin (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Super admin table created or already exists\n";
    } else {
        throw new Exception("Error creating super_admin table: " . $conn->error);
    }
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('department_dean', 'teacher', 'librarian', 'admin_qa') NOT NULL,
        department_id INT NULL,
        first_name VARCHAR(100) NULL,
        last_name VARCHAR(100) NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role),
        INDEX idx_active (is_active)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Users table created or already exists\n";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Create departments table
    $sql = "CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department_code VARCHAR(20) NOT NULL UNIQUE,
        department_name VARCHAR(100) NOT NULL UNIQUE,
        color_code VARCHAR(7) NOT NULL,
        created_by VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Departments table created or already exists\n";
    } else {
        throw new Exception("Error creating departments table: " . $conn->error);
    }
    
    // Create school_years table
    $sql = "CREATE TABLE IF NOT EXISTS school_years (
        id INT PRIMARY KEY AUTO_INCREMENT,
        year_start INT NOT NULL,
        year_end INT NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_year (year_start, year_end)
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ School years table created or already exists\n";
    } else {
        throw new Exception("Error creating school_years table: " . $conn->error);
    }
    
    // Create terms table
    $sql = "CREATE TABLE IF NOT EXISTS terms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_year_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_active BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Terms table created or already exists\n";
    } else {
        throw new Exception("Error creating terms table: " . $conn->error);
    }
    
    // Create school_calendar table
    $sql = "CREATE TABLE IF NOT EXISTS school_calendar (
        id INT PRIMARY KEY AUTO_INCREMENT,
        term_id INT NOT NULL,
        event_type ENUM('class', 'holiday', 'exam', 'event') NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        start_date DATE NOT NULL,
        end_date DATE NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ School calendar table created or already exists\n";
    } else {
        throw new Exception("Error creating school_calendar table: " . $conn->error);
    }
    
    // Insert super admin if not exists
    $result = $conn->query("SELECT COUNT(*) as count FROM super_admin");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $email = "superadmin.mis@sccpag.edu.ph";
        $password = "password123";
        $stmt = $conn->prepare("INSERT INTO super_admin (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $password);
        if ($stmt->execute()) {
            echo "✅ Super admin account created\n";
        } else {
            echo "❌ Failed to create super admin account\n";
        }
        $stmt->close();
    } else {
        echo "✅ Super admin account already exists\n";
    }
    
    // Insert sample users if not exist
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $users = [
            ['dean.cse@sccpag.edu.ph', $password_hash, 'department_dean', 'John', 'Smith'],
            ['teacher1@sccpag.edu.ph', $password_hash, 'teacher', 'Jane', 'Doe'],
            ['teacher2@sccpag.edu.ph', $password_hash, 'teacher', 'Mike', 'Johnson'],
            ['librarian@sccpag.edu.ph', $password_hash, 'librarian', 'Sarah', 'Wilson'],
            ['qa.admin@sccpag.edu.ph', $password_hash, 'admin_qa', 'Robert', 'Brown']
        ];
        
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->bind_param("sssss", $user[0], $user[1], $user[2], $user[3], $user[4]);
            if ($stmt->execute()) {
                echo "✅ Added user: {$user[0]} ({$user[2]})\n";
            } else {
                echo "❌ Failed to add user: {$user[0]}\n";
            }
        }
        $stmt->close();
    } else {
        echo "✅ Users already exist\n";
    }
    
    // Show final status
    echo "\n=== Database Information ===\n";
    $tables = ['users', 'super_admin', 'departments', 'school_years', 'terms', 'school_calendar'];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "$table: {$row['count']} records\n";
        } else {
            echo "$table: Table not found\n";
        }
    }
    
    echo "\n=== Test Credentials ===\n";
    echo "Super Admin: superadmin.mis@sccpag.edu.ph / password123\n";
    echo "Department Dean: dean.cse@sccpag.edu.ph / password123\n";
    echo "Teacher: teacher1@sccpag.edu.ph / password123\n";
    echo "Librarian: librarian@sccpag.edu.ph / password123\n";
    echo "Admin QA: qa.admin@sccpag.edu.ph / password123\n";
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "You can now access the system at: http://localhost/DataDrift/ASCOM%20Monitoring%20System/\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your MySQL connection and try again.\n";
}
?> 