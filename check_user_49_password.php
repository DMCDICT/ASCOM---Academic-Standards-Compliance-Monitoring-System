<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Check user ID 49 password
$userId = 49;

$stmt = $conn->prepare("SELECT id, institutional_email, password, first_name, last_name, title FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "User ID: " . $user['id'] . "\n";
    echo "Email: " . $user['institutional_email'] . "\n";
    echo "Password: '" . $user['password'] . "'\n";
    echo "Password Length: " . strlen($user['password']) . "\n";
    echo "Name: " . $user['title'] . " " . $user['first_name'] . " " . $user['last_name'] . "\n";
} else {
    echo "User not found\n";
}

$stmt->close();
$conn->close();
?>
