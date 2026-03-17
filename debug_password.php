<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Check user ID 49 password
$userId = 49;

$stmt = $conn->prepare("SELECT id, institutional_email, password, first_name, last_name, title FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>User ID 49 Password Debug</h2>";

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
    echo "<p><strong>Email:</strong> " . $user['institutional_email'] . "</p>";
    echo "<p><strong>Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
    echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
    echo "<p><strong>Name:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    
    // Test password comparison
    $testPassword = "password123"; // Common test password
    echo "<p><strong>Test with 'password123':</strong> " . ($testPassword === $user['password'] ? 'MATCH' : 'NO MATCH') . "</p>";
    
    $testPassword2 = "123456"; // Another common test password
    echo "<p><strong>Test with '123456':</strong> " . ($testPassword2 === $user['password'] ? 'MATCH' : 'NO MATCH') . "</p>";
    
    // Show password in different formats
    echo "<h3>Password Analysis:</h3>";
    echo "<p><strong>Raw password:</strong> " . var_export($user['password'], true) . "</p>";
    echo "<p><strong>Password as hex:</strong> " . bin2hex($user['password']) . "</p>";
    echo "<p><strong>Password with quotes:</strong> '" . $user['password'] . "'</p>";
    
} else {
    echo "<p>User not found</p>";
}

$stmt->close();
$conn->close();
?>
