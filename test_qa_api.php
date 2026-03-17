<?php
// Test the QA switch role API directly
echo "<h1>QA API Test</h1>";

// Test the API endpoint
$url = 'http://localhost/DataDrift/ASCOM Monitoring System/admin-quality_assurance/api/switch_role.php';

echo "<h2>Testing API Endpoint</h2>";
echo "<p><strong>URL:</strong> $url</p>";

// Test with a simple request
$data = json_encode([
    'password' => 'test123',
    'target_role' => 'teacher'
]);

echo "<p><strong>Test Data:</strong> $data</p>";

// Make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($error) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $error</p>";
}
echo "<p><strong>Response Body:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to decode JSON
$responseData = json_decode($response, true);
if ($responseData) {
    echo "<h3>Decoded Response:</h3>";
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
}
?>
