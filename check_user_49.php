<?php
// Simple check for user ID 49
echo "<h1>User ID 49 Check</h1>";

// Try different database connections
echo "<h2>Testing Database Connections</h2>";

// Test 1: MySQLi connection
echo "<h3>MySQLi Connection Test:</h3>";
try {
    $conn = new mysqli("localhost", "root", "", "ascom_db");
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>MySQLi connection successful!</p>";
        
        // Get user data
        $stmt = $conn->prepare("SELECT id, institutional_email, password, first_name, last_name, title FROM users WHERE id = 49");
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            echo "<p><strong>User found:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
            echo "<p><strong>Email:</strong> " . $user['institutional_email'] . "</p>";
            echo "<p><strong>Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
            echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
        } else {
            echo "<p style='color: red;'>User not found!</p>";
        }
        
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test 2: PDO connection
echo "<h3>PDO Connection Test:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ascom_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>PDO connection successful!</p>";
    
    // Get user data
    $stmt = $pdo->prepare("SELECT id, institutional_email, password, first_name, last_name, title FROM users WHERE id = 49");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p><strong>User found:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $user['institutional_email'] . "</p>";
        echo "<p><strong>Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
        echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test 3: Direct query
echo "<h3>Direct Query Test:</h3>";
try {
    $conn = new mysqli("localhost", "root", "", "ascom_db");
    $result = $conn->query("SELECT id, institutional_email, password, first_name, last_name, title FROM users WHERE id = 49");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p><strong>User found:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
        echo "<p><strong>Email:</strong> " . $user['institutional_email'] . "</p>";
        echo "<p><strong>Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
        echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
        
        // Test password comparison
        echo "<h3>Password Tests:</h3>";
        $testPasswords = ["password123", "123456", "password", "admin123", "test123"];
        foreach ($testPasswords as $testPass) {
            $match = ($testPass === $user['password']);
            $color = $match ? 'green' : 'red';
            echo "<p style='color: $color;'><strong>$testPass:</strong> " . ($match ? 'MATCH!' : 'No match') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
