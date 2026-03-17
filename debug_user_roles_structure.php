<?php
// debug_user_roles_structure.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>User Roles Table Structure Debug</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if user_roles table exists
echo "<h3>1. User Roles Table Status:</h3>";
$tableCheck = $conn->query("SHOW TABLES LIKE 'user_roles'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists</p>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE user_roles");
    if ($structure) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
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
} else {
    echo "<p style='color: red;'>❌ user_roles table does not exist</p>";
    exit;
}

// Check if department_id column exists
echo "<h3>2. Department ID Column Check:</h3>";
$columnCheck = $conn->query("SHOW COLUMNS FROM user_roles LIKE 'department_id'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ department_id column exists</p>";
} else {
    echo "<p style='color: red;'>❌ department_id column does not exist</p>";
    echo "<p>This is causing the login error!</p>";
}

// Show sample data
echo "<h3>3. Sample User Roles Data:</h3>";
$sampleData = $conn->query("SELECT * FROM user_roles LIMIT 5");
if ($sampleData && $sampleData->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr>";
    while ($row = $sampleData->fetch_assoc()) {
        foreach ($row as $key => $value) {
            echo "<th>" . $key . "</th>";
        }
        break;
    }
    echo "</tr>";
    
    $sampleData->data_seek(0);
    while ($row = $sampleData->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . ($value ?: 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in user_roles table.</p>";
}

$conn->close();
?>
