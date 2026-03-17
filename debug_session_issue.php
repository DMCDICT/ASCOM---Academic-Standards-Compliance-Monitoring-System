<?php
session_start();

echo "<h1>Session Debug</h1>";

echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session ID:</h2>";
echo "<p>" . session_id() . "</p>";

// Test database connection and user lookup
echo "<h2>Database Test:</h2>";
try {
    $conn = new mysqli("localhost", "root", "", "ascom_db");
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>Database connected successfully!</p>";
        
        // Test user ID 49
        $stmt = $conn->prepare("
            SELECT u.id, u.first_name, u.last_name, u.title,
                   d.department_code, d.department_name, d.color_code
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.department_id
            WHERE u.id = ?
        ");
        
        $stmt->bind_param("i", 49);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            echo "<p><strong>User 49:</strong> " . $user['first_name'] . " " . $user['last_name'] . "</p>";
            echo "<p><strong>Department:</strong> " . $user['department_code'] . " - " . $user['department_name'] . "</p>";
            echo "<p><strong>Color:</strong> " . $user['color_code'] . "</p>";
        } else {
            echo "<p style='color: red;'>User 49 not found!</p>";
        }
        
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
