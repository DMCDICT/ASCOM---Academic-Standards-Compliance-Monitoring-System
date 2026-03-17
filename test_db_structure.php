<?php
// test_db_structure.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Database Structure Test</h2>";

// Test school_years table structure
echo "<h3>School Years Table Structure:</h3>";
$check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_sy->num_rows > 0) {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
    
    $columns = $conn->query("DESCRIBE school_years");
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test data
    echo "<h4>Sample Data:</h4>";
    $data = $conn->query("SELECT * FROM school_years LIMIT 3");
    if ($data && $data->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
        while ($row = $data->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . ($row['school_year_label'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['status'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No data in school_years table</p>";
    }
} else {
    echo "<p style='color: red;'>❌ school_years table does not exist</p>";
}

// Test school_terms table structure
echo "<h3>School Terms Table Structure:</h3>";
$check_st = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($check_st->num_rows > 0) {
    echo "<p style='color: green;'>✅ school_terms table exists</p>";
    
    $columns = $conn->query("DESCRIBE school_terms");
    echo "<table border='1'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ school_terms table does not exist</p>";
}

echo "<h3>Test Completed!</h3>";
echo "<p><a href='fix_school_calendar_database.php'>Run Database Fixer</a></p>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar'>Go to School Calendar</a></p>";

$conn->close();
?> 