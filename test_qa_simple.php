<?php
// Simple test for QA switch role
session_start();

echo "<h1>QA Switch Role Simple Test</h1>";

// Check session
echo "<h2>Session Check:</h2>";
echo "<p><strong>admin_qa_logged_in:</strong> " . (isset($_SESSION['admin_qa_logged_in']) ? ($_SESSION['admin_qa_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
echo "<p><strong>user_id:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "</p>";

// Check database connection
echo "<h2>Database Check:</h2>";
try {
    $conn = new mysqli("localhost", "root", "", "ascom_db");
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>Database connection successful!</p>";
        
        // Check if user exists
        $userId = $_SESSION['user_id'] ?? 49;
        $stmt = $conn->prepare("SELECT id, first_name, last_name, title FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            echo "<p style='color: green;'>User found: " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
        } else {
            echo "<p style='color: red;'>User not found!</p>";
        }
        
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

// Test the API directly
echo "<h2>API Test:</h2>";
$url = 'admin-quality_assurance/api/switch_role.php';
$data = json_encode([
    'password' => 'test123',
    'target_role' => 'teacher'
]);

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

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
if ($error) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> $error</p>";
}
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

$responseData = json_decode($response, true);
if ($responseData && isset($responseData['debug'])) {
    echo "<h3>Debug Information:</h3>";
    echo "<pre>";
    print_r($responseData['debug']);
    echo "</pre>";
}
?>
