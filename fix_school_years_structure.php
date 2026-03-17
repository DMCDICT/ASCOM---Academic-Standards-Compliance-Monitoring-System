<?php
// fix_school_years_structure.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Fix School Years Table Structure</h2>";

// Step 1: Check current table structure
echo "<h3>1. Current Table Structure:</h3>";
$columns = $conn->query("DESCRIBE school_years");
$column_names = [];
while ($row = $columns->fetch_assoc()) {
    $column_names[] = $row['Field'];
}

echo "<p>Current columns: " . implode(', ', $column_names) . "</p>";

// Step 2: Check if we need to update the structure
$needs_update = false;
if (!in_array('school_year_label', $column_names)) {
    $needs_update = true;
    echo "<p style='color: orange;'>⚠️ Missing school_year_label column - will add it</p>";
}

if (!in_array('status', $column_names)) {
    $needs_update = true;
    echo "<p style='color: orange;'>⚠️ Missing status column - will add it</p>";
}

// Step 3: Update table structure if needed
if ($needs_update) {
    echo "<h3>2. Updating Table Structure:</h3>";
    
    // Add school_year_label column
    if (!in_array('school_year_label', $column_names)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN school_year_label VARCHAR(50) UNIQUE AFTER id";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added school_year_label column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add school_year_label column: " . $conn->error . "</p>";
        }
    }
    
    // Add status column
    if (!in_array('status', $column_names)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Inactive' AFTER end_date";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added status column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add status column: " . $conn->error . "</p>";
        }
    }
    
    // Update existing data to populate school_year_label
    echo "<h3>3. Updating Existing Data:</h3>";
    $update_sql = "UPDATE school_years SET 
        school_year_label = CONCAT(year_start, '-', year_end),
        status = CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END";
    
    if ($conn->query($update_sql)) {
        echo "<p style='color: green;'>✅ Updated existing data</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update data: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Table structure is already correct</p>";
}

// Step 4: Check if table has data
echo "<h3>4. Checking Data:</h3>";
$count = $conn->query("SELECT COUNT(*) as count FROM school_years");
$count_row = $count->fetch_assoc();

if ($count_row['count'] == 0) {
    echo "<p style='color: orange;'>⚠️ No school years found - inserting sample data</p>";
    
    // Insert sample school years
    $insert_sample = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES 
        ('2023-2024', '2023-08-01', '2024-05-31', 'Active'),
        ('2024-2025', '2024-08-01', '2025-05-31', 'Inactive'),
        ('2025-2026', '2025-08-01', '2026-05-31', 'Inactive')";
    
    if ($conn->query($insert_sample)) {
        echo "<p style='color: green;'>✅ Sample school years inserted successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to insert sample data: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ School years table has " . $count_row['count'] . " records</p>";
}

// Step 5: Show current data
echo "<h3>5. Current School Years Data:</h3>";
$data = $conn->query("SELECT * FROM school_years ORDER BY id DESC");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
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

// Step 6: Test the dropdown generation
echo "<h3>6. Testing Dropdown Generation:</h3>";
$school_years_for_dropdown = [];
$result = $conn->query("SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC");

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $school_years_for_dropdown[] = $row;
    }
    
    echo "<p style='color: green;'>✅ Successfully loaded " . count($school_years_for_dropdown) . " school years</p>";
    
    echo "<h4>Generated Dropdown HTML:</h4>";
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
    echo "<p style='color: red;'>❌ Failed to load school years data</p>";
}

echo "<h3>7. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to School Calendar</a></p>";
echo "<p><a href='test_school_years_loading.php' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Run Test Again</a></p>";

$conn->close();
?>
