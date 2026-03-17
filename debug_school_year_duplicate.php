<?php
// debug_school_year_duplicate.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Debug School Year Duplicate Issue</h2>";

// Check current database structure
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

// Check for unique constraints
echo "<h3>2. Unique Constraints:</h3>";
$constraints = $conn->query("SHOW INDEX FROM school_years WHERE Non_unique = 0");
if ($constraints && $constraints->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Key Name</th><th>Column Name</th><th>Non Unique</th></tr>";
    while ($row = $constraints->fetch_assoc()) {
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

// Check current data
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

// Check for any hidden data or soft deletes
echo "<h3>4. Check for Hidden/Soft Deleted Records:</h3>";
$hidden_check = $conn->query("SELECT COUNT(*) as total FROM school_years");
if ($hidden_check) {
    $total = $hidden_check->fetch_assoc()['total'];
    echo "<p><strong>Total records in school_years table:</strong> $total</p>";
}

// Test direct insertion to see the exact error
echo "<h3>5. Testing Direct Database Insertion:</h3>";

// Test data
$test_label = '2027-2028';
$test_start = '2027-08-01';
$test_end = '2028-06-30';
$test_status = 'Inactive';

echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
echo "<li>School Year Label: $test_label</li>";
echo "<li>Start Date: $test_start</li>";
echo "<li>End Date: $test_end</li>";
echo "<li>Status: $test_status</li>";
echo "</ul>";

// Try direct insertion
$sql = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo "<p style='color: red;'>❌ Prepare failed: " . $conn->error . "</p>";
} else {
    $stmt->bind_param('ssss', $test_label, $test_start, $test_end, $test_status);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Direct insertion successful!</p>";
        
        // Get the inserted ID
        $inserted_id = $conn->insert_id;
        echo "<p><strong>Inserted ID:</strong> $inserted_id</p>";
        
        // Clean up
        $conn->query("DELETE FROM school_years WHERE id = $inserted_id");
        echo "<p style='color: blue;'>🗑️ Test record cleaned up.</p>";
    } else {
        echo "<p style='color: red;'>❌ Direct insertion failed: " . $stmt->error . "</p>";
        echo "<p><strong>MySQL Error Code:</strong> " . $conn->errno . "</p>";
        
        if ($conn->errno == 1062) {
            echo "<p style='color: orange;'>⚠️ This is a duplicate entry error (1062)</p>";
            
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

// Check if there are any triggers
echo "<h3>6. Check for Triggers:</h3>";
$triggers = $conn->query("SHOW TRIGGERS LIKE 'school_years'");
if ($triggers && $triggers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Trigger</th><th>Event</th><th>Table</th><th>Statement</th></tr>";
    while ($row = $triggers->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Trigger'] . "</td>";
        echo "<td>" . $row['Event'] . "</td>";
        echo "<td>" . $row['Table'] . "</td>";
        echo "<td>" . substr($row['Statement'], 0, 100) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No triggers found on school_years table.</p>";
}

echo "<h3>7. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
