<?php
// Database Test Script for ASCOM Monitoring System
// This will help identify why login isn't working

echo "<h2>Database Connection Test</h2>";

// Test database connection
try {
    require_once 'super_admin-mis/includes/db_connection.php';
    echo "✅ <strong>Database connection successful!</strong><br>";
    echo "Connected to database: <strong>{$database}</strong><br><br>";
} catch (Exception $e) {
    echo "❌ <strong>Database connection failed:</strong> " . $e->getMessage() . "<br>";
    exit();
}

// Test if users table exists
try {
    $stmt = $conn->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ <strong>Users table exists!</strong><br><br>";
    } else {
        echo "❌ <strong>Users table does not exist!</strong><br>";
        echo "You need to run the database_setup.sql script first.<br><br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error checking table:</strong> " . $e->getMessage() . "<br><br>";
}

// Test if users table has data
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "📊 <strong>Users in database:</strong> {$row['count']}<br><br>";
    
    if ($row['count'] > 0) {
        // Show sample users
        $stmt = $conn->prepare("SELECT email, role, is_active FROM users LIMIT 5");
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<strong>Sample users:</strong><br>";
        while ($user = $result->fetch_assoc()) {
            $status = $user['is_active'] ? "✅ Active" : "❌ Disabled";
            echo "- {$user['email']} ({$user['role']}) - {$status}<br>";
        }
        echo "<br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error checking users:</strong> " . $e->getMessage() . "<br><br>";
}

// Test password verification
echo "<h3>Password Test</h3>";
$test_password = "password123";
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);

echo "Test password: <strong>{$test_password}</strong><br>";
echo "Generated hash: <strong>{$test_hash}</strong><br>";

if (password_verify($test_password, $test_hash)) {
    echo "✅ <strong>Password verification works!</strong><br><br>";
} else {
    echo "❌ <strong>Password verification failed!</strong><br><br>";
}

// Test specific user login
echo "<h3>Test User Login</h3>";
$test_email = "dean.cse@sccpag.edu.ph";

try {
    $stmt = $conn->prepare("SELECT id, email, password_hash, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $test_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ <strong>User found:</strong> {$user['email']}<br>";
        echo "Role: <strong>{$user['role']}</strong><br>";
        echo "Status: <strong>" . ($user['is_active'] ? "Active" : "Disabled") . "</strong><br>";
        
        // Test password verification
        if (password_verify($test_password, $user['password_hash'])) {
            echo "✅ <strong>Password verification successful!</strong><br>";
        } else {
            echo "❌ <strong>Password verification failed!</strong><br>";
            echo "The password hash in database might be different.<br>";
        }
    } else {
        echo "❌ <strong>User not found:</strong> {$test_email}<br>";
        echo "Make sure you've run the database_setup.sql script.<br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error testing user:</strong> " . $e->getMessage() . "<br>";
}

$stmt->close();
$conn->close();
?>

<hr>

<h3>Quick Fix Steps:</h3>
<ol>
    <li><strong>Run database setup:</strong> Execute the database_setup.sql script in your MySQL database</li>
    <li><strong>Check database name:</strong> Make sure your database is named "ascom_db"</li>
    <li><strong>Verify table creation:</strong> The users table should be created with sample data</li>
    <li><strong>Test login:</strong> Try logging in with dean.cse@sccpag.edu.ph / password123</li>
</ol>

<h3>Database Setup Commands:</h3>
<pre>
-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ascom_db;
USE ascom_db;

-- Then run the database_setup.sql script
</pre> 