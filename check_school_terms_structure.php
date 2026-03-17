<?php
// check_school_terms_structure.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>School Terms Table Structure Check</h2>";

// Check if table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($table_exists->num_rows === 0) {
    echo "❌ school_terms table does not exist<br>";
    exit;
}

echo "✅ school_terms table exists<br>";

// Get table structure
$structure = $conn->query("DESCRIBE school_terms");
if ($structure) {
    echo "<h3>Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    $has_status = false;
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'status') {
            $has_status = true;
        }
    }
    echo "</table>";
    
    if (!$has_status) {
        echo "<p style='color: red;'>❌ <strong>Missing 'status' column!</strong></p>";
    } else {
        echo "<p style='color: green;'>✅ <strong>'status' column exists</strong></p>";
    }
} else {
    echo "❌ Could not get table structure: " . $conn->error . "<br>";
}

$conn->close();
?>
