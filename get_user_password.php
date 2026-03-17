<?php
require_once 'super_admin-mis/includes/db_connection.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$testPassword = $input['password'] ?? '';

// Get user ID 49 password
$userId = 49;

$stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $match = ($testPassword === $user['password']);
    
    echo json_encode([
        'match' => $match,
        'input' => $testPassword,
        'db_password' => $user['password'],
        'input_length' => strlen($testPassword),
        'db_length' => strlen($user['password'])
    ]);
} else {
    echo json_encode([
        'error' => 'User not found'
    ]);
}

$stmt->close();
$conn->close();
?>
