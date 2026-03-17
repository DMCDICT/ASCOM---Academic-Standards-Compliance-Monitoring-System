<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Direct test of switch role API
$userId = 49;
$testPassword = "password123"; // Change this to test different passwords

echo "<h2>Direct Switch Role Test</h2>";

// Simulate the exact API call
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
    echo "<p><strong>User:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p><strong>Department:</strong> " . $user['department_code'] . "</p>";
    
    // Test password
    $passwordMatch = ($testPassword === $user['password']);
    
    echo "<h3>Password Test:</h3>";
    echo "<p><strong>Input Password:</strong> '$testPassword'</p>";
    echo "<p><strong>Database Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
    echo "<p><strong>Match:</strong> " . ($passwordMatch ? 'YES' : 'NO') . "</p>";
    
    if ($passwordMatch) {
        echo "<p style='color: green;'><strong>✅ Switch role would succeed!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Switch role would fail</strong></p>";
        
        // Show detailed comparison
        echo "<h3>Detailed Analysis:</h3>";
        echo "<p><strong>Input length:</strong> " . strlen($testPassword) . "</p>";
        echo "<p><strong>DB length:</strong> " . strlen($user['password']) . "</p>";
        echo "<p><strong>Input hex:</strong> " . bin2hex($testPassword) . "</p>";
        echo "<p><strong>DB hex:</strong> " . bin2hex($user['password']) . "</p>";
        
        // Check for spaces
        echo "<p><strong>Input trimmed:</strong> '" . trim($testPassword) . "'</p>";
        echo "<p><strong>DB trimmed:</strong> '" . trim($user['password']) . "'</p>";
        echo "<p><strong>Trimmed match:</strong> " . (trim($testPassword) === trim($user['password']) ? 'YES' : 'NO') . "</p>";
    }
} else {
    echo "<p style='color: red;'>User not found!</p>";
}

$stmt->close();
$conn->close();
?>
