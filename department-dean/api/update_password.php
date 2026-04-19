<?php
// department-dean/api/update_password.php
// API endpoint to update dean password.

header('Content-Type: application/json');

// Include session and database connection
require_once __DIR__ . '/../../session_config.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../../bootstrap/auth.php'; // For password helpers

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
    
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        throw new Exception("Both current and new passwords are required.");
    }
    
    if (strlen($newPassword) < 8) {
        throw new Exception("New password must be at least 8 characters long.");
    }
    
    $userId = $_SESSION['user_id'];
    
    // Fetch current stored password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found.");
    }
    
    // Verify current password
    // We can use a simple check or the helper from bootstrap/auth.php
    $verified = ascom_verify_password_with_migration(
        $currentPassword,
        $user['password'],
        function() {} // No-op persist for verification only
    );
    
    if (!$verified) {
        throw new Exception("Incorrect current password.");
    }
    
    // Hash new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update database
    $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($updateQuery);
    
    if (!$stmt->execute([$newHash, $userId])) {
        throw new Exception("Database update failed.");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
