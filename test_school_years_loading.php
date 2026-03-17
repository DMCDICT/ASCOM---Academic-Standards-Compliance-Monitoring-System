<?php
// test_school_years_loading.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test School Years Loading</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test 2: Check if school_years table exists
echo "<h3>2. School Years Table Test:</h3>";
$check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_sy->num_rows > 0) {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
} else {
    echo "<p style='color: red;'>❌ school_years table does not exist</p>";
    exit;
}

// Test 3: Check table structure
echo "<h3>3. Table Structure Test:</h3>";
$columns = $conn->query("DESCRIBE school_years");
$column_names = [];
while ($row = $columns->fetch_assoc()) {
    $column_names[] = $row['Field'];
}

echo "<p>Columns found: " . implode(', ', $column_names) . "</p>";

if (in_array('school_year_label', $column_names)) {
    echo "<p style='color: green;'>✅ school_year_label column exists</p>";
} else {
    echo "<p style='color: red;'>❌ school_year_label column missing</p>";
    exit;
}

// Test 4: Check if table has data
echo "<h3>4. Data Availability Test:</h3>";
$count = $conn->query("SELECT COUNT(*) as count FROM school_years");
$count_row = $count->fetch_assoc();
echo "<p>Total school years: " . $count_row['count'] . "</p>";

if ($count_row['count'] > 0) {
    echo "<p style='color: green;'>✅ School years data exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No school years found - inserting sample data</p>";
    
    // Insert sample data
    $insert_sample = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES 
        ('2023-2024', '2023-08-01', '2024-05-31', 'Active'),
        ('2024-2025', '2024-08-01', '2025-05-31', 'Inactive')";
    
    if ($conn->query($insert_sample)) {
        echo "<p style='color: green;'>✅ Sample data inserted successfully</p>";
        echo "<p><a href='test_school_years_loading.php'>Refresh to test again</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to insert sample data: " . $conn->error . "</p>";
    }
    exit;
}

// Test 5: Test the exact query from school calendar
echo "<h3>5. Query Test (School Calendar Logic):</h3>";

// Simulate the school calendar logic
$school_years_for_dropdown = [];

try {
    $columns_check = $conn->query("DESCRIBE school_years");
    if ($columns_check === false) {
        throw new Exception("Failed to describe school_years table");
    }
    
    $columns = [];
    while ($row = $columns_check->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $sql_sy = "";
    if (in_array('school_year_label', $columns)) {
        $sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
        echo "<p>Using query: " . $sql_sy . "</p>";
        
        $result_sy = $conn->query($sql_sy);
        if ($result_sy && $result_sy->num_rows > 0) {
            echo "<p style='color: green;'>✅ Query successful! Found " . $result_sy->num_rows . " records</p>";
            
            while($row = $result_sy->fetch_assoc()) {
                $school_years_for_dropdown[] = $row;
            }
            
            echo "<h4>Loaded School Years:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
            foreach ($school_years_for_dropdown as $sy) {
                echo "<tr>";
                echo "<td>" . $sy['id'] . "</td>";
                echo "<td>" . $sy['school_year_label'] . "</td>";
                echo "<td>" . $sy['start_date'] . "</td>";
                echo "<td>" . $sy['end_date'] . "</td>";
                echo "<td>" . $sy['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p style='color: red;'>❌ Query returned no results</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ school_year_label column not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 6: Test modal HTML generation
echo "<h3>6. Modal HTML Generation Test:</h3>";
if (!empty($school_years_for_dropdown)) {
    echo "<p style='color: green;'>✅ School years data available for modal</p>";
    
    echo "<h4>Generated Modal HTML:</h4>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
    echo "<select id='schoolYearId' name='schoolYearId' required>";
    echo "<option value='' disabled selected>-- Select a School Year --</option>";
    foreach ($school_years_for_dropdown as $sy) {
        echo "<option value='" . htmlspecialchars($sy['id']) . "' data-start='" . htmlspecialchars($sy['start_date']) . "' data-end='" . htmlspecialchars($sy['end_date']) . "'>";
        echo htmlspecialchars($sy['school_year_label']);
        echo "</option>";
    }
    echo "</select>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ No school years data available for modal</p>";
}

echo "<h3>7. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar'>Go to School Calendar</a></p>";
echo "<p><a href='debug_school_years_dropdown.php'>Run Detailed Debug</a></p>";

$conn->close();
?>
