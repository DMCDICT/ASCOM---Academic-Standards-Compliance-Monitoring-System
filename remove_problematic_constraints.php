<?php
// remove_problematic_constraints.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Remove Problematic Constraints</h2>";

// Step 1: Check current constraints
echo "<h3>1. Current Constraints:</h3>";
$constraints = $conn->query("SHOW INDEX FROM school_years WHERE Non_unique = 0");
if ($constraints && $constraints->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Key Name</th><th>Column Name</th><th>Non Unique</th><th>Action</th></tr>";
    while ($row = $constraints->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Key_name'] . "</td>";
        echo "<td>" . $row['Column_name'] . "</td>";
        echo "<td>" . $row['Non_unique'] . "</td>";
        echo "<td>";
        if ($row['Key_name'] === 'unique_year' || $row['Key_name'] === 'school_year_label') {
            echo "<span style='color: red; font-weight: bold;'>Will Remove</span>";
        } else {
            echo "Keep (Primary Key)";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No unique constraints found.</p>";
}

// Step 2: Remove the problematic unique_year constraint
echo "<h3>2. Removing unique_year Constraint:</h3>";
$unique_year_check = $conn->query("SHOW INDEX FROM school_years WHERE Key_name = 'unique_year'");
if ($unique_year_check && $unique_year_check->num_rows > 0) {
    echo "<p>Found unique_year constraint - removing it...</p>";
    
    $drop_sql = "ALTER TABLE school_years DROP INDEX unique_year";
    if ($conn->query($drop_sql)) {
        echo "<p style='color: green;'>✅ Successfully removed unique_year constraint</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to remove unique_year constraint: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ unique_year constraint not found</p>";
}

// Step 3: Remove the school_year_label unique constraint (optional)
echo "<h3>3. Removing school_year_label Unique Constraint:</h3>";
$label_check = $conn->query("SHOW INDEX FROM school_years WHERE Key_name = 'school_year_label'");
if ($label_check && $label_check->num_rows > 0) {
    echo "<p>Found school_year_label unique constraint - removing it...</p>";
    
    $drop_sql = "ALTER TABLE school_years DROP INDEX school_year_label";
    if ($conn->query($drop_sql)) {
        echo "<p style='color: green;'>✅ Successfully removed school_year_label unique constraint</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to remove school_year_label constraint: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ school_year_label unique constraint not found</p>";
}

// Step 4: Check final constraints
echo "<h3>4. Final Constraints:</h3>";
$final_constraints = $conn->query("SHOW INDEX FROM school_years WHERE Non_unique = 0");
if ($final_constraints && $final_constraints->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Key Name</th><th>Column Name</th><th>Non Unique</th></tr>";
    while ($row = $final_constraints->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Key_name'] . "</td>";
        echo "<td>" . $row['Column_name'] . "</td>";
        echo "<td>" . $row['Non_unique'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No unique constraints found.</p>";
}

// Step 5: Test school year creation
echo "<h3>5. Testing School Year Creation:</h3>";
$test_label = 'A.Y. 2025-2026';
$test_start = '2025-08-01';
$test_end = '2026-06-30';
$test_status = 'Active';

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
    $year_start = 2025;
    $year_end = 2026;
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

echo "<h3>6. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
