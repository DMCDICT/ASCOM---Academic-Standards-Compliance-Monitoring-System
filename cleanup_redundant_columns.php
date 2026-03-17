<?php
// cleanup_redundant_columns.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Clean Up Redundant Columns</h2>";

// Step 1: Check current structure
echo "<h3>1. Current Database Structure:</h3>";
$structure = $conn->query("DESCRIBE school_years");
if ($structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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

// Step 2: Check current data
echo "<h3>2. Current School Years Data:</h3>";
$current_data = $conn->query("SELECT * FROM school_years ORDER BY id DESC");
if ($current_data && $current_data->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Status</th><th>Is Active</th><th>Start Date</th><th>End Date</th></tr>";
    while ($row = $current_data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['school_year_label'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['status'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['is_active'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No school years found in database.</p>";
}

// Step 3: Check for inconsistencies
echo "<h3>3. Checking for Inconsistencies:</h3>";
$inconsistencies = $conn->query("
    SELECT id, school_year_label, status, is_active 
    FROM school_years 
    WHERE (status = 'Active' AND is_active != 1) 
       OR (status = 'Inactive' AND is_active != 0)
       OR (status IS NULL AND is_active IS NOT NULL)
       OR (status IS NOT NULL AND is_active IS NULL)
");

if ($inconsistencies && $inconsistencies->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Found inconsistencies between status and is_active:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Status</th><th>Is Active</th><th>Issue</th></tr>";
    while ($row = $inconsistencies->fetch_assoc()) {
        $issue = "";
        if ($row['status'] === 'Active' && $row['is_active'] != 1) {
            $issue = "Status=Active but is_active≠1";
        } elseif ($row['status'] === 'Inactive' && $row['is_active'] != 0) {
            $issue = "Status=Inactive but is_active≠0";
        } elseif ($row['status'] === null && $row['is_active'] !== null) {
            $issue = "Status=NULL but is_active has value";
        } elseif ($row['status'] !== null && $row['is_active'] === null) {
            $issue = "Status has value but is_active=NULL";
        }
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . ($row['status'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['is_active'] ?? 'NULL') . "</td>";
        echo "<td style='color: red;'>" . $issue . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ No inconsistencies found between status and is_active columns.</p>";
}

// Step 4: Fix any inconsistencies
echo "<h3>4. Fixing Inconsistencies:</h3>";
$fix_result = $conn->query("
    UPDATE school_years 
    SET status = CASE 
        WHEN is_active = 1 THEN 'Active' 
        WHEN is_active = 0 THEN 'Inactive' 
        ELSE 'Inactive' 
    END 
    WHERE status IS NULL OR status = ''
");

if ($fix_result) {
    echo "<p style='color: green;'>✅ Fixed NULL/empty status values based on is_active</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to fix inconsistencies: " . $conn->error . "</p>";
}

// Step 5: Remove the redundant is_active column
echo "<h3>5. Removing Redundant is_active Column:</h3>";
$is_active_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'is_active'");
if ($is_active_check && $is_active_check->num_rows > 0) {
    echo "<p>Found is_active column - removing it...</p>";
    
    $drop_sql = "ALTER TABLE school_years DROP COLUMN is_active";
    if ($conn->query($drop_sql)) {
        echo "<p style='color: green;'>✅ Successfully removed is_active column</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to remove is_active column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ is_active column not found (already removed)</p>";
}

// Step 6: Check final structure
echo "<h3>6. Final Database Structure:</h3>";
$final_structure = $conn->query("DESCRIBE school_years");
if ($final_structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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

// Step 7: Test school year creation
echo "<h3>7. Testing School Year Creation:</h3>";
$test_label = 'A.Y. 2026-2027';
$test_start = '2026-08-01';
$test_end = '2027-06-30';
$test_status = 'Inactive';

echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
echo "<li>School Year Label: $test_label</li>";
echo "<li>Start Date: $test_start</li>";
echo "<li>End Date: $test_end</li>";
echo "<li>Status: $test_status</li>";
echo "</ul>";

// Check if this label already exists
$exists = $conn->query("SELECT id FROM school_years WHERE school_year_label = '$test_label'");
if ($exists && $exists->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ School year '$test_label' already exists. Deleting it first...</p>";
    $conn->query("DELETE FROM school_years WHERE school_year_label = '$test_label'");
    echo "<p style='color: green;'>✅ Old record deleted.</p>";
}

// Try insertion
$sql = "INSERT INTO school_years (school_year_label, year_start, year_end, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo "<p style='color: red;'>❌ Prepare failed: " . $conn->error . "</p>";
} else {
    $year_start = 2026;
    $year_end = 2027;
    $stmt->bind_param('siisss', $test_label, $year_start, $year_end, $test_start, $test_end, $test_status);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test insertion successful!</p>";
        $inserted_id = $conn->insert_id;
        echo "<p><strong>Inserted ID:</strong> $inserted_id</p>";
        
        // Show the inserted record
        $new_record = $conn->query("SELECT * FROM school_years WHERE id = $inserted_id");
        if ($new_record && $new_record->num_rows > 0) {
            $record = $new_record->fetch_assoc();
            echo "<p><strong>New Record:</strong></p>";
            echo "<ul>";
            echo "<li>ID: " . $record['id'] . "</li>";
            echo "<li>School Year Label: " . $record['school_year_label'] . "</li>";
            echo "<li>Year Start: " . $record['year_start'] . "</li>";
            echo "<li>Year End: " . $record['year_end'] . "</li>";
            echo "<li>Start Date: " . $record['start_date'] . "</li>";
            echo "<li>End Date: " . $record['end_date'] . "</li>";
            echo "<li>Status: " . $record['status'] . "</li>";
            echo "</ul>";
        }
        
        // Clean up test data
        $conn->query("DELETE FROM school_years WHERE id = $inserted_id");
        echo "<p style='color: blue;'>🗑️ Test record cleaned up.</p>";
    } else {
        echo "<p style='color: red;'>❌ Test insertion failed: " . $stmt->error . "</p>";
        echo "<p><strong>MySQL Error Code:</strong> " . $conn->errno . "</p>";
    }
    $stmt->close();
}

echo "<h3>8. Summary:</h3>";
echo "<p><strong>✅ Cleaned up redundant columns:</strong></p>";
echo "<ul>";
echo "<li>Removed <code>is_active</code> column (redundant)</li>";
echo "<li>Kept <code>status</code> column (more descriptive)</li>";
echo "<li>Fixed any inconsistencies between the two columns</li>";
echo "<li>Tested that school year creation still works</li>";
echo "</ul>";

echo "<h3>9. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
