<?php
// debug_add_term_api.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Debug Add Term API</h2>";

// Get an active school year for testing
$school_years = $conn->query("SELECT id, school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1");
if (!$school_years || $school_years->num_rows === 0) {
    echo "❌ No active school year found<br>";
    exit;
}

$school_year = $school_years->fetch_assoc();
echo "✅ Using school year: " . $school_year['school_year_label'] . " (ID: " . $school_year['id'] . ")<br>";

// Test data
$test_data = [
    'title' => 'Test Term',
    'school_year_id' => $school_year['id'],
    'start_date' => '2025-08-15',
    'end_date' => '2025-12-15'
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>Direct API Test:</h3>";

// Test 1: Direct file inclusion test
echo "<h4>Test 1: Direct File Inclusion</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Capture output
ob_start();

// Simulate the API call
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['jsonData'] = json_encode($test_data);

// Include the API file
include 'super_admin-mis/api/add_term.php';

$output = ob_get_clean();
echo "Raw API Output:<br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode as JSON
$json_response = json_decode($output, true);
if ($json_response) {
    echo "✅ Valid JSON response:<br>";
    echo "<pre>" . json_encode($json_response, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "❌ Invalid JSON response. JSON error: " . json_last_error_msg() . "<br>";
}

echo "</div>";

// Test 2: CURL test
echo "<h4>Test 2: CURL Test</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/add_term.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "<br>";
if ($curl_error) {
    echo "CURL Error: " . $curl_error . "<br>";
}
echo "Response:<br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to decode as JSON
$json_response = json_decode($response, true);
if ($json_response) {
    echo "✅ Valid JSON response:<br>";
    echo "<pre>" . json_encode($json_response, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "❌ Invalid JSON response. JSON error: " . json_last_error_msg() . "<br>";
}

echo "</div>";

// Test 3: Check for PHP errors
echo "<h4>Test 3: PHP Error Check</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Check if there are any PHP errors in the error log
$error_log_path = ini_get('error_log');
if ($error_log_path && file_exists($error_log_path)) {
    echo "Error log path: " . $error_log_path . "<br>";
    $recent_errors = shell_exec('tail -20 "' . $error_log_path . '" 2>/dev/null');
    if ($recent_errors) {
        echo "Recent error log entries:<br>";
        echo "<pre>" . htmlspecialchars($recent_errors) . "</pre>";
    } else {
        echo "No recent errors found in log<br>";
    }
} else {
    echo "Could not access error log<br>";
}

echo "</div>";

// Test 4: Check database connection
echo "<h4>Test 4: Database Connection Test</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

if ($conn->ping()) {
    echo "✅ Database connection is working<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Test if we can query the school_terms table
$test_query = $conn->query("SELECT COUNT(*) as count FROM school_terms");
if ($test_query) {
    $count = $test_query->fetch_assoc()['count'];
    echo "✅ school_terms table is accessible. Current count: " . $count . "<br>";
} else {
    echo "❌ Cannot query school_terms table: " . $conn->error . "<br>";
}

echo "</div>";

$conn->close();
?>
