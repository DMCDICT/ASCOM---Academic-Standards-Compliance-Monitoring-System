<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Simulate the switch role process for user ID 49
$userId = 49;
$testPassword = "password123"; // Change this to the password you're using

echo "<h2>Switch Role Test for User ID 49</h2>";

// Get user information (same query as switch role API)
$stmt = $conn->prepare("
    SELECT u.id, u.password, u.first_name, u.last_name, u.middle_name, u.title,
           u.department_id, d.department_code, d.department_name, d.color_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    WHERE u.id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "<p><strong>User found:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p><strong>Department:</strong> " . $user['department_code'] . " - " . $user['department_name'] . "</p>";
    echo "<p><strong>DB Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
    echo "<p><strong>Test Password:</strong> '" . htmlspecialchars($testPassword) . "'</p>";
    
    // Test the exact comparison used in switch role API
    $passwordMatch = ($testPassword === $user['password']);
    echo "<p><strong>Password Match:</strong> " . ($passwordMatch ? 'YES' : 'NO') . "</p>";
    
    if ($passwordMatch) {
        echo "<p style='color: green;'><strong>✅ Switch role would succeed!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Switch role would fail with 'Incorrect password'</strong></p>";
        
        // Show detailed comparison
        echo "<h3>Detailed Comparison:</h3>";
        echo "<p><strong>Input length:</strong> " . strlen($testPassword) . "</p>";
        echo "<p><strong>DB length:</strong> " . strlen($user['password']) . "</p>";
        echo "<p><strong>Input hex:</strong> " . bin2hex($testPassword) . "</p>";
        echo "<p><strong>DB hex:</strong> " . bin2hex($user['password']) . "</p>";
        
        // Check for common issues
        echo "<h3>Common Issues Check:</h3>";
        echo "<p><strong>Trimmed input matches:</strong> " . (trim($testPassword) === $user['password'] ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Trimmed DB matches:</strong> " . ($testPassword === trim($user['password']) ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Both trimmed match:</strong> " . (trim($testPassword) === trim($user['password']) ? 'YES' : 'NO') . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>User not found!</strong></p>";
}

$stmt->close();
$conn->close();
?>
