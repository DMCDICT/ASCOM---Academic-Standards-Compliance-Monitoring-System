<?php
// Simple user check
echo "<h1>Simple User Check</h1>";

// Connect to database
$conn = new mysqli("localhost", "root", "", "ascom_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<p style='color: green;'>Database connected successfully!</p>";

// Get user ID 49
$result = $conn->query("SELECT * FROM users WHERE id = 49");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    echo "<h2>User Found:</h2>";
    echo "<p><strong>ID:</strong> " . $user['id'] . "</p>";
    echo "<p><strong>Email:</strong> " . $user['institutional_email'] . "</p>";
    echo "<p><strong>Name:</strong> " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p><strong>Password:</strong> '" . htmlspecialchars($user['password']) . "'</p>";
    echo "<p><strong>Password Length:</strong> " . strlen($user['password']) . "</p>";
    
    // Show password in different formats
    echo "<h3>Password Analysis:</h3>";
    echo "<p><strong>Raw password:</strong> " . var_export($user['password'], true) . "</p>";
    echo "<p><strong>Password as hex:</strong> " . bin2hex($user['password']) . "</p>";
    echo "<p><strong>Password with quotes:</strong> '" . $user['password'] . "'</p>";
    
    // Test common passwords
    echo "<h3>Password Tests:</h3>";
    $testPasswords = [
        "password123",
        "123456",
        "password",
        "admin123",
        "test123",
        "user123",
        "qwerty",
        "abc123"
    ];
    
    foreach ($testPasswords as $testPass) {
        $match = ($testPass === $user['password']);
        $color = $match ? 'green' : 'red';
        echo "<p style='color: $color;'><strong>$testPass:</strong> " . ($match ? 'MATCH!' : 'No match') . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>User not found!</p>";
}

$conn->close();
?>