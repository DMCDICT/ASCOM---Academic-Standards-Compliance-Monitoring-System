<?php
// test_api_fix.php
echo "<h2>Test API Fix</h2>";

// Test data
$test_data = [
    'title' => 'Test Term',
    'school_year_id' => 35, // Use the active school year ID
    'start_date' => '2025-08-15',
    'end_date' => '2025-12-15'
];

echo "<h3>Testing API with data:</h3>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

// Test the API
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

echo "<h3>API Response:</h3>";
echo "<p><strong>HTTP Code:</strong> " . $http_code . "</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>CURL Error:</strong> " . $curl_error . "</p>";
}

echo "<p><strong>Raw Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to parse as JSON
$json_response = json_decode($response, true);
if ($json_response) {
    echo "<p style='color: green;'><strong>✅ Valid JSON Response:</strong></p>";
    echo "<pre>" . json_encode($json_response, JSON_PRETTY_PRINT) . "</pre>";
    
    if ($json_response['status'] === 'success') {
        echo "<p style='color: green;'>🎉 <strong>SUCCESS!</strong> The API is working correctly!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ API returned an error: " . $json_response['message'] . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ Invalid JSON Response</strong></p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    echo "<p>This means the API is still outputting HTML instead of JSON.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=7' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal</a></p>";
?>
