<?php
// Simple API test without cURL
require_once 'includes/db_connection.php';

echo "<h2>Simple API Test</h2>";

// Simulate the API call directly
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'department_code' => 'CBE',
    'teacher_id' => 28
];

// Set the input for the API
file_put_contents('php://input', json_encode($input));

// Include and test the API directly
ob_start();
include 'api/assign_department_dean.php';
$response = ob_get_clean();

echo "<h3>API Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to decode the response
$responseData = json_decode($response, true);
if ($responseData) {
    if ($responseData['success']) {
        echo "<p style='color: green;'>✅ API test successful!</p>";
        echo "<p>Message: " . $responseData['message'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ API test failed: " . $responseData['message'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Invalid JSON response</p>";
}
?>
