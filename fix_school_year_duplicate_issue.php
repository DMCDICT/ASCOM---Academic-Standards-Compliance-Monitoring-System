<?php
// fix_school_year_duplicate_issue.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Fix School Year Duplicate Issue</h2>";

// Step 1: Check current data
echo "<h3>1. Current School Years Data:</h3>";
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

// Step 2: Check for duplicate school_year_labels
echo "<h3>2. Check for Duplicate School Year Labels:</h3>";
$duplicates = $conn->query("
    SELECT school_year_label, COUNT(*) as count 
    FROM school_years 
    WHERE school_year_label IS NOT NULL 
    GROUP BY school_year_label 
    HAVING COUNT(*) > 1
");

if ($duplicates && $duplicates->num_rows > 0) {
    echo "<p style='color: red;'>⚠️ Found duplicate school year labels:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>School Year Label</th><th>Count</th></tr>";
    while ($row = $duplicates->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Clean up duplicates (keep the newest one)
    echo "<h3>3. Cleaning Up Duplicates:</h3>";
    $duplicate_labels = $conn->query("
        SELECT school_year_label, COUNT(*) as count 
        FROM school_years 
        WHERE school_year_label IS NOT NULL 
        GROUP BY school_year_label 
        HAVING COUNT(*) > 1
    ");
    
    while ($dup = $duplicate_labels->fetch_assoc()) {
        $label = $dup['school_year_label'];
        echo "<p>Cleaning up duplicates for: <strong>$label</strong></p>";
        
        // Get all records with this label, ordered by ID (newest first)
        $records = $conn->query("SELECT id FROM school_years WHERE school_year_label = '$label' ORDER BY id DESC");
        $first = true;
        
        while ($record = $records->fetch_assoc()) {
            if ($first) {
                echo "<p style='color: green;'>✅ Keeping record ID: " . $record['id'] . "</p>";
                $first = false;
            } else {
                echo "<p style='color: red;'>🗑️ Deleting duplicate record ID: " . $record['id'] . "</p>";
                $conn->query("DELETE FROM school_years WHERE id = " . $record['id']);
            }
        }
    }
} else {
    echo "<p style='color: green;'>✅ No duplicate school year labels found.</p>";
}

// Step 3: Check for NULL school_year_labels
echo "<h3>4. Check for NULL School Year Labels:</h3>";
$null_labels = $conn->query("SELECT * FROM school_years WHERE school_year_label IS NULL OR school_year_label = ''");
if ($null_labels && $null_labels->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Found records with NULL or empty school_year_label:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    while ($row = $null_labels->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['school_year_label'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['status'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Fix NULL labels by generating them from start_date and end_date
    echo "<h3>5. Fixing NULL School Year Labels:</h3>";
    $null_records = $conn->query("SELECT * FROM school_years WHERE school_year_label IS NULL OR school_year_label = ''");
    while ($record = $null_records->fetch_assoc()) {
        $id = $record['id'];
        $start_date = $record['start_date'];
        $end_date = $record['end_date'];
        
        if ($start_date && $end_date) {
            $start_year = date('Y', strtotime($start_date));
            $end_year = date('Y', strtotime($end_date));
            $new_label = "$start_year-$end_year";
            
            echo "<p>Fixing record ID $id: <strong>$new_label</strong></p>";
            $conn->query("UPDATE school_years SET school_year_label = '$new_label' WHERE id = $id");
        } else {
            echo "<p style='color: red;'>Cannot fix record ID $id - missing start_date or end_date</p>";
        }
    }
} else {
    echo "<p style='color: green;'>✅ No NULL school year labels found.</p>";
}

// Step 4: Test insertion
echo "<h3>6. Testing School Year Creation:</h3>";
$test_label = '2027-2028';
$test_start = '2027-08-01';
$test_end = '2028-06-30';
$test_status = 'Inactive';

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
    }
    $stmt->close();
}

// Step 5: Final data check
echo "<h3>7. Final School Years Data:</h3>";
$final_data = $conn->query("SELECT * FROM school_years ORDER BY id DESC");
if ($final_data && $final_data->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Created At</th></tr>";
    while ($row = $final_data->fetch_assoc()) {
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

echo "<h3>8. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
