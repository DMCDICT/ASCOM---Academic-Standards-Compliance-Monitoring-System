<?php
// debug_school_years_dropdown.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Debug School Years Dropdown Issue</h2>";

// Check if school_years table exists
$check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_sy->num_rows > 0) {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
    
    // Check table structure
    $columns = $conn->query("DESCRIBE school_years");
    echo "<h3>Table Structure:</h3>";
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
    
    // Check if table has data
    $count = $conn->query("SELECT COUNT(*) as count FROM school_years");
    $count_row = $count->fetch_assoc();
    echo "<p>Total school years in database: " . $count_row['count'] . "</p>";
    
    if ($count_row['count'] > 0) {
        // Show all school years
        $data = $conn->query("SELECT * FROM school_years ORDER BY id DESC");
        echo "<h3>All School Years:</h3>";
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
        
        // Test the exact query from school calendar
        echo "<h3>Testing School Calendar Query:</h3>";
        try {
            $columns_check = $conn->query("DESCRIBE school_years");
            if ($columns_check === false) {
                throw new Exception("Failed to describe school_years table");
            }
            
            $columns = [];
            while ($row = $columns_check->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            echo "<p>Columns found: " . implode(', ', $columns) . "</p>";
            
            $sql_sy = "";
            if (in_array('school_year_label', $columns)) {
                $sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
                echo "<p>Using query: " . $sql_sy . "</p>";
                
                $result_sy = $conn->query($sql_sy);
                if ($result_sy && $result_sy->num_rows > 0) {
                    echo "<p style='color: green;'>✅ Query successful! Found " . $result_sy->num_rows . " records</p>";
                    
                    $school_years_for_dropdown = [];
                    while($row = $result_sy->fetch_assoc()) {
                        $school_years_for_dropdown[] = $row;
                    }
                    
                    echo "<h3>Processed Data for Dropdown:</h3>";
                    echo "<pre>" . print_r($school_years_for_dropdown, true) . "</pre>";
                    
                    // Test the modal HTML generation
                    echo "<h3>Generated Modal HTML:</h3>";
                    echo "<select id='schoolYearId' name='schoolYearId' required>";
                    echo "<option value='' disabled selected>-- Select a School Year --</option>";
                    foreach ($school_years_for_dropdown as $sy) {
                        echo "<option value='" . htmlspecialchars($sy['id']) . "' data-start='" . htmlspecialchars($sy['start_date']) . "' data-end='" . htmlspecialchars($sy['end_date']) . "'>";
                        echo htmlspecialchars($sy['school_year_label']);
                        echo "</option>";
                    }
                    echo "</select>";
                    
                } else {
                    echo "<p style='color: red;'>❌ Query returned no results</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ school_year_label column not found</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No school years found in database</p>";
        
        // Insert sample data for testing
        echo "<h3>Inserting Sample Data:</h3>";
        $insert_sample = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES 
            ('2023-2024', '2023-08-01', '2024-05-31', 'Active'),
            ('2024-2025', '2024-08-01', '2025-05-31', 'Inactive')";
        
        if ($conn->query($insert_sample)) {
            echo "<p style='color: green;'>✅ Sample data inserted successfully</p>";
            echo "<p><a href='debug_school_years_dropdown.php'>Refresh to test again</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to insert sample data: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ school_years table does not exist</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar'>Go to School Calendar</a></p>";
echo "<p><a href='fix_school_calendar_database.php'>Run Database Fixer</a></p>";

$conn->close();
?>
