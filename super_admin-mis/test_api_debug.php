<?php
// Test API debug file
echo "<h1>Testing API Endpoint</h1>";

// Test database connection
echo "<h2>1. Testing Database Connection</h2>";
require_once __DIR__ . '/includes/db_connection.php';

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test if users table exists
echo "<h2>2. Testing Users Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Users table exists</p>";
    
    // Test if there are any users
    $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    echo "<p>Total users in database: " . $userCount . "</p>";
    
    // Show first few users
    $users = $conn->query("SELECT employee_no, first_name, last_name FROM users LIMIT 5");
    echo "<h3>Sample Users:</h3>";
    echo "<ul>";
    while ($user = $users->fetch_assoc()) {
        echo "<li>" . $user['employee_no'] . " - " . $user['first_name'] . " " . $user['last_name'] . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ Users table does not exist</p>";
}

// Test API endpoint directly
echo "<h2>3. Testing API Endpoint</h2>";
if (isset($_GET['test_employee'])) {
    $employee_no = $_GET['test_employee'];
    echo "<p>Testing with employee number: " . htmlspecialchars($employee_no) . "</p>";
    
    // Simulate the API logic
    $query = "SELECT u.*, r.role as role_name, d.department_name, d.department_code 
              FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              LEFT JOIN departments d ON u.department_id = d.id 
              WHERE u.employee_no = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $employee_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo "<p style='color: red;'>❌ User not found</p>";
        } else {
            $userData = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ User found!</p>";
            echo "<pre>" . print_r($userData, true) . "</pre>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>❌ Failed to prepare statement</p>";
    }
} else {
    echo "<p>Add ?test_employee=EMPLOYEE_NUMBER to test the API</p>";
    echo "<p>Example: <a href='?test_employee=150644'>?test_employee=150644</a></p>";
}

$conn->close();
?>
