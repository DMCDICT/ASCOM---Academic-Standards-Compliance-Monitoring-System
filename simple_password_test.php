<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Simple password test for user ID 49
$userId = 49;

// Get the password from database
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "<h2>Simple Password Test</h2>";
    echo "<p><strong>Database Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
    echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
    
    // Test with common passwords
    $testPasswords = [
        "password123",
        "123456",
        "password",
        "admin123",
        "test123",
        "user123"
    ];
    
    echo "<h3>Testing Common Passwords:</h3>";
    foreach ($testPasswords as $testPass) {
        $match = ($testPass === $user['password']);
        $color = $match ? 'green' : 'red';
        echo "<p style='color: $color;'><strong>$testPass:</strong> " . ($match ? 'MATCH!' : 'No match') . "</p>";
    }
    
    // Show password in different formats
    echo "<h3>Password Analysis:</h3>";
    echo "<p><strong>Raw:</strong> " . var_export($user['password'], true) . "</p>";
    echo "<p><strong>Hex:</strong> " . bin2hex($user['password']) . "</p>";
    echo "<p><strong>With quotes:</strong> '" . $user['password'] . "'</p>";
    
} else {
    echo "<p>User not found</p>";
}

$stmt->close();
$conn->close();
?>
