<?php
// fix_unique_constraint_issue.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Fix Unique Constraint Issue</h2>";

// Step 1: Check current database structure
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

// Step 2: Check for unique constraints
echo "<h3>2. Unique Constraints:</h3>";
$constraints = $conn->query("SHOW INDEX FROM school_years WHERE Non_unique = 0");
if ($constraints && $constraints->num_rows > 0) {
    echo "<p style='color: red;'>⚠️ Found unique constraints that might be causing the issue:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Key Name</th><th>Column Name</th><th>Non Unique</th><th>Action</th></tr>";
    while ($row = $constraints->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Key_name'] . "</td>";
        echo "<td>" . $row['Column_name'] . "</td>";
        echo "<td>" . $row['Non_unique'] . "</td>";
        echo "<td>";
        if ($row['Column_name'] === 'school_year_label') {
            echo "<button onclick='removeConstraint(\"" . $row['Key_name'] . "\")' style='background: red; color: white; border: none; padding: 5px; cursor: pointer;'>Remove</button>";
        } else {
            echo "N/A";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✅ No unique constraints found.</p>";
}

// Step 3: Check current data
echo "<h3>3. Current School Years Data:</h3>";
$current_data = $conn->query("SELECT * FROM school_years ORDER BY id DESC");
if ($current_data && $current_data->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Created At</th></tr>";
    while ($row = $current_data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['school_year_label'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['status'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['created_at'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No school years found in database.</p>";
}

// Step 4: Check for any hidden records with the same label
echo "<h3>4. Check for Hidden Records:</h3>";
$test_label = 'A.Y. 2025-2026';
$hidden_check = $conn->query("SELECT * FROM school_years WHERE school_year_label = '$test_label' OR school_year_label LIKE '%2025%' OR school_year_label LIKE '%2026%'");
if ($hidden_check && $hidden_check->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Found records that might conflict:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    while ($row = $hidden_check->fetch_assoc()) {
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
    echo "<p style='color: green;'>✅ No conflicting records found.</p>";
}

// Step 5: Remove unique constraint if it exists
echo "<h3>5. Removing Unique Constraint (if exists):</h3>";
$unique_check = $conn->query("SHOW INDEX FROM school_years WHERE Non_unique = 0 AND Column_name = 'school_year_label'");
if ($unique_check && $unique_check->num_rows > 0) {
    $constraint = $unique_check->fetch_assoc();
    $key_name = $constraint['Key_name'];
    
    echo "<p>Found unique constraint: <strong>$key_name</strong> on school_year_label</p>";
    
    // Remove the unique constraint
    $drop_sql = "ALTER TABLE school_years DROP INDEX `$key_name`";
    if ($conn->query($drop_sql)) {
        echo "<p style='color: green;'>✅ Successfully removed unique constraint: $key_name</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to remove constraint: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ No unique constraint found on school_year_label</p>";
}

// Step 6: Test insertion
echo "<h3>6. Testing School Year Creation:</h3>";
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
$sql = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo "<p style='color: red;'>❌ Prepare failed: " . $conn->error . "</p>";
} else {
    $stmt->bind_param('ssss', $test_label, $test_start, $test_end, $test_status);
    
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
        
        if ($conn->errno == 1062) {
            echo "<p style='color: orange;'>⚠️ This is still a duplicate entry error (1062)</p>";
            
            // Check what's causing the duplicate
            $duplicate_check = $conn->query("SELECT * FROM school_years WHERE school_year_label = '$test_label'");
            if ($duplicate_check && $duplicate_check->num_rows > 0) {
                echo "<p style='color: red;'>Found existing record with same school_year_label:</p>";
                $existing = $duplicate_check->fetch_assoc();
                echo "<pre>" . print_r($existing, true) . "</pre>";
            }
        }
    }
    $stmt->close();
}

// Step 7: Final structure check
echo "<h3>7. Final Database Structure:</h3>";
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

echo "<h3>8. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>

<script>
function removeConstraint(keyName) {
    if (confirm('Are you sure you want to remove the unique constraint: ' + keyName + '?')) {
        // This would need to be implemented with AJAX or form submission
        alert('Constraint removal would be handled here. Please run the PHP script to see the results.');
    }
}
</script>
