<?php
// test_add_school_year.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test Add School Year Functionality</h2>";

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

// Check current school years
echo "<h3>2. Current School Years in Database:</h3>";
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

// Test the API
echo "<h3>3. Testing Add School Year API:</h3>";

// Test data
$test_data = [
    'school_year_label' => '2027-2028',
    'start_date' => '2027-08-01',
    'end_date' => '2028-06-30',
    'status' => 'Inactive'
];

echo "<p><strong>Test Data:</strong></p>";
echo "<ul>";
foreach ($test_data as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";

// Make API call
$url = 'super_admin-mis/api/add_school_year.php';
$json_data = json_encode($test_data);

echo "<p><strong>Making API call to:</strong> $url</p>";
echo "<p><strong>JSON Data:</strong> $json_data</p>";

// Use cURL to test the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Response Code:</strong> $http_code</p>";
echo "<p><strong>Response:</strong> $response</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $curl_error</p>";
}

// Check if the test school year was added
echo "<h3>4. Checking if Test School Year was Added:</h3>";
$check_data = $conn->query("SELECT * FROM school_years WHERE school_year_label = '2027-2028' ORDER BY id DESC");
if ($check_data && $check_data->num_rows > 0) {
    echo "<p style='color: green;'>✅ Test school year was successfully added!</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Created At</th></tr>";
    while ($row = $check_data->fetch_assoc()) {
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
    echo "<p style='color: red;'>❌ Test school year was not found in database.</p>";
}

// Clean up test data
echo "<h3>5. Cleaning Up Test Data:</h3>";
$delete_result = $conn->query("DELETE FROM school_years WHERE school_year_label = '2027-2028'");
if ($delete_result) {
    echo "<p style='color: green;'>✅ Test data cleaned up successfully.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Could not clean up test data: " . $conn->error . "</p>";
}

echo "<h3>6. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
