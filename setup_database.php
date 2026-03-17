<?php
// Automatic Database Setup for ASCOM Monitoring System
// This script will create the database, table, and sample users

echo "<h2>Automatic Database Setup</h2>";

// Database connection parameters
if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
    $servername = "db";
} else {
    $servername = "localhost";
}
$username = "root";
$password = ""; // Your MySQL root password, likely empty for XAMPP
$database = "ascom_db";

try {
    // Connect without specifying database first
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ <strong>Connected to MySQL server</strong><br>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) === TRUE) {
        echo "✅ <strong>Database '$database' created or already exists</strong><br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($database);
    echo "✅ <strong>Selected database '$database'</strong><br>";
    
    echo "<br>DEBUG: About to create roles table...<br>";
    // Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        role VARCHAR(50) UNIQUE NOT NULL
    )";
    if ($conn->query($sql) === TRUE) {
        echo "✅ <strong>Roles table created or already exists</strong><br>";
    } else {
        echo "❌ <strong>Error creating roles table: " . $conn->error . "</strong><br>";
    }
    echo "<br>DEBUG: Finished roles table creation...<br>";

    // Insert default roles if not present
    echo "<br>DEBUG: About to check and insert default roles...<br>";
    $result = $conn->query("SELECT COUNT(*) as count FROM roles");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $roles = ['department_dean', 'librarian', 'teacher', 'admin_qa'];
        $stmt = $conn->prepare("INSERT INTO roles (role) VALUES (?)");
        foreach ($roles as $role) {
            $stmt->bind_param("s", $role);
            if ($stmt->execute()) {
                echo "✅ Added role: {$role}<br>";
            } else {
                echo "❌ Failed to add role: {$role}<br>";
            }
        }
        $stmt->close();
        echo "<br>✅ <strong>Default roles inserted successfully!</strong><br>";
    } else {
        echo "✅ <strong>Roles table already has data ({$row['count']} roles)</strong><br>";
    }
    echo "<br>DEBUG: Finished inserting/checking roles...<br>";
    
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
        echo "✅ <strong>Users table created or already exists</strong><br>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Check if table has data
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "📝 <strong>Inserting sample users...</strong><br>";
        
        // Sample password hash for 'password123'
        $password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        
        // Insert sample users
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
                echo "✅ Added user: {$user[0]} ({$user[2]})<br>";
            } else {
                echo "❌ Failed to add user: {$user[0]}<br>";
            }
        }
        
        $stmt->close();
        echo "<br>✅ <strong>Sample users inserted successfully!</strong><br>";
    } else {
        echo "✅ <strong>Users table already has data ({$row['count']} users)</strong><br>";
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
        echo "✅ <strong>Departments table created or already exists</strong><br>";
    } else {
        echo "❌ <strong>Error creating departments table: " . $conn->error . "</strong><br>";
    }
    // Removed automatic insertion of sample departments
    
    // Show final status
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<br>📊 <strong>Total users in database: {$row['count']}</strong><br>";
    
    // Show sample users
    $result = $conn->query("SELECT email, role, is_active FROM users ORDER BY role");
    echo "<br><strong>Current users:</strong><br>";
    while ($user = $result->fetch_assoc()) {
        $status = $user['is_active'] ? "✅ Active" : "❌ Disabled";
        echo "- {$user['email']} ({$user['role']}) - {$status}<br>";
    }
    
    echo "<br>🎉 <strong>Database setup completed successfully!</strong><br>";
    echo "<br><strong>Test Login Credentials:</strong><br>";
    echo "All accounts use password: <strong>password123</strong><br>";
    echo "<br><a href='user_login.php'>Go to User Login</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<br>Please check your MySQL connection and try again.";
}

$conn->close();
?> 