<?php
// fix_school_terms_table.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Fix School Terms Table Structure</h2>";

// Check if table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($table_exists->num_rows === 0) {
    echo "❌ school_terms table does not exist<br>";
    exit;
}

echo "✅ school_terms table exists<br>";

// Check if status column exists
$status_exists = $conn->query("SHOW COLUMNS FROM school_terms LIKE 'status'");
if ($status_exists->num_rows === 0) {
    echo "❌ 'status' column is missing<br>";
    
    // Add the status column
    $add_status = "ALTER TABLE school_terms ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Inactive' AFTER end_date";
    
    if ($conn->query($add_status)) {
        echo "✅ Successfully added 'status' column<br>";
    } else {
        echo "❌ Failed to add 'status' column: " . $conn->error . "<br>";
        exit;
    }
} else {
    echo "✅ 'status' column already exists<br>";
}

// Check if created_at and updated_at columns exist
$created_at_exists = $conn->query("SHOW COLUMNS FROM school_terms LIKE 'created_at'");
$updated_at_exists = $conn->query("SHOW COLUMNS FROM school_terms LIKE 'updated_at'");

if ($created_at_exists->num_rows === 0) {
    echo "❌ 'created_at' column is missing<br>";
    
    $add_created_at = "ALTER TABLE school_terms ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status";
    
    if ($conn->query($add_created_at)) {
        echo "✅ Successfully added 'created_at' column<br>";
    } else {
        echo "❌ Failed to add 'created_at' column: " . $conn->error . "<br>";
    }
} else {
    echo "✅ 'created_at' column already exists<br>";
}

if ($updated_at_exists->num_rows === 0) {
    echo "❌ 'updated_at' column is missing<br>";
    
    $add_updated_at = "ALTER TABLE school_terms ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
    
    if ($conn->query($add_updated_at)) {
        echo "✅ Successfully added 'updated_at' column<br>";
    } else {
        echo "❌ Failed to add 'updated_at' column: " . $conn->error . "<br>";
    }
} else {
    echo "✅ 'updated_at' column already exists<br>";
}

// Show final table structure
echo "<h3>Final Table Structure:</h3>";
$final_structure = $conn->query("DESCRIBE school_terms");
if ($final_structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    while ($row = $final_structure->fetch_assoc()) {
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

echo "<h3>Next Steps:</h3>";
echo "<p><a href='test_api_fix.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test API Again</a></p>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=7' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal</a></p>";

$conn->close();
?>
