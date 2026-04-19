<?php
// department-dean/api/update_account.php
// API endpoint to update dean account information.

header('Content-Type: application/json');

// Include session and database connection
require_once __DIR__ . '/../../session_config.php';
require_once __DIR__ . '/../includes/db_connection.php';

// Authentication check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['is_authenticated']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        throw new Exception("Full name and email are required.");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }
    
    // Split full name into first and last name (simplified)
    $parts = explode(' ', $full_name);
    $first_name = $parts[0];
    $last_name = (count($parts) > 1) ? implode(' ', array_slice($parts, 1)) : '';
    
    $userId = $_SESSION['user_id'];
    
    // Update database
    $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, institutional_email = ? WHERE id = ?";
    $stmt = $pdo->prepare($updateQuery);
    
    if (!$stmt->execute([$first_name, $last_name, $email, $userId])) {
        throw new Exception("Database update failed.");
    }

    // Update session
    $_SESSION['user_first_name'] = $first_name;
    $_SESSION['user_last_name'] = $last_name;
    $_SESSION['username'] = $email;

    echo json_encode([
        'success' => true,
        'message' => 'Account information updated successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
