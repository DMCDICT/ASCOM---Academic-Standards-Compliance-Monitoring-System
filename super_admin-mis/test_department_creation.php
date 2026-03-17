<?php
// test_department_creation.php
// Simple test to check department creation

require_once __DIR__ . '/includes/db_connection.php';

echo "<h2>Department Creation Test</h2>";

// Check if departments table exists
$table_check = $conn->query("SHOW TABLES LIKE 'departments'");
if ($table_check->num_rows === 0) {
    echo "<p>❌ Departments table does not exist</p>";
    
    // Create departments table
    $create_table = "CREATE TABLE departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        department_code VARCHAR(10) UNIQUE NOT NULL,
        department_name VARCHAR(100) NOT NULL,
        color_code VARCHAR(7) DEFAULT '#4A7DFF',
        created_by VARCHAR(100) DEFAULT 'Super Admin MIS',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p>✅ Departments table created successfully</p>";
    } else {
        echo "<p>❌ Failed to create departments table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p>✅ Departments table exists</p>";
}

// Check current departments
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC LIMIT 5");
echo "<h3>Current Departments (Last 5):</h3>";
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Color</th><th>Created By</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . $row['color_code'] . "</td>";
        echo "<td>" . $row['created_by'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No departments found</p>";
}

// Test inserting a department
echo "<h3>Testing Department Insertion:</h3>";
$test_code = "TEST" . rand(100, 999);
$test_name = "Test Department " . rand(1, 100);
$test_color = "#FF0000";

$stmt = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $created_by = 'Test Script';
    $stmt->bind_param("ssss", $test_code, $test_name, $test_color, $created_by);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        echo "<p>✅ Test department created successfully! ID: " . $new_id . "</p>";
        
        // Clean up - delete the test department
        $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $new_id);
            $delete_stmt->execute();
            echo "<p>✅ Test department cleaned up</p>";
        }
    } else {
        echo "<p>❌ Failed to create test department: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>❌ Could not prepare insert statement: " . $conn->error . "</p>";
}

$conn->close();
echo "<p><strong>Test completed!</strong></p>";
?> 