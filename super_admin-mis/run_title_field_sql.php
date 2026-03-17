<?php
require_once 'includes/db_connection.php';

echo "<h2>Adding Title Field to Users Table</h2>";

try {
    // Check if title field already exists
    $checkQuery = "SHOW COLUMNS FROM users LIKE 'title'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ Title field already exists in users table</p>";
    } else {
        // Add title field
        $addTitleQuery = "ALTER TABLE users ADD COLUMN title VARCHAR(50) DEFAULT NULL AFTER last_name";
        if ($conn->query($addTitleQuery)) {
            echo "<p style='color: green;'>✅ Title field added successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding title field: " . $conn->error . "</p>";
        }
    }
    
    // Show current users table structure
    echo "<h3>Current Users Table Structure:</h3>";
    $structureQuery = "DESCRIBE users";
    $structureResult = $conn->query($structureQuery);
    
    if ($structureResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $structureResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
