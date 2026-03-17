<?php
// Suppress error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../includes/db_connection.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    // First, check if user exists and is active
    $checkUserQuery = "SELECT id, first_name, last_name FROM users WHERE id = ? AND is_active = 1";
    $checkStmt = $conn->prepare($checkUserQuery);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $userResult = $checkStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
        exit;
    }
    
    $user = $userResult->fetch_assoc();
    
    // Check if user is already a department dean
    $checkDeanQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
    $checkDeanStmt = $conn->prepare($checkDeanQuery);
    $checkDeanStmt->bind_param("i", $userId);
    $checkDeanStmt->execute();
    $deanResult = $checkDeanStmt->get_result();
    
    if ($deanResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'User is already a department dean']);
        exit;
    }
    
    // Check if user already has active QA role
    $checkQAQuery = "SELECT id FROM user_roles WHERE user_id = ? AND role_name = 'quality_assurance' AND is_active = 1";
    $checkQAStmt = $conn->prepare($checkQAQuery);
    $checkQAStmt->bind_param("i", $userId);
    $checkQAStmt->execute();
    $qaResult = $checkQAStmt->get_result();
    
    if ($qaResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'User already has Quality Assurance access']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'super_admin';
        
        // Get the previous QA user's info for logging
        $previousQAQuery = "SELECT u.first_name, u.last_name FROM user_roles ur JOIN users u ON ur.user_id = u.id WHERE ur.role_name = 'quality_assurance' AND ur.is_active = 1";
        $previousResult = $conn->query($previousQAQuery);
        $previousQA = null;
        if ($previousResult && $previousResult->num_rows > 0) {
            $previousQA = $previousResult->fetch_assoc();
        }
        
        // First, deactivate any existing QA role
        $deactivateQuery = "UPDATE user_roles SET is_active = 0 WHERE role_name = 'quality_assurance' AND is_active = 1";
        $deactivateStmt = $conn->prepare($deactivateQuery);
        $deactivateStmt->execute();
        
        // Check if this user already has a deactivated QA role
        $checkExistingQuery = "SELECT id FROM user_roles WHERE user_id = ? AND role_name = 'quality_assurance'";
        $checkExistingStmt = $conn->prepare($checkExistingQuery);
        $checkExistingStmt->bind_param("i", $userId);
        $checkExistingStmt->execute();
        $existingResult = $checkExistingStmt->get_result();
        
        if ($existingResult->num_rows > 0) {
            // Update existing record
            $updateQuery = "UPDATE user_roles SET is_active = 1, assigned_by = ?, assigned_at = NOW() WHERE user_id = ? AND role_name = 'quality_assurance'";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $username, $userId);
            $updateStmt->execute();
        } else {
            // Insert new record
            $assignQuery = "INSERT INTO user_roles (user_id, role_name, assigned_by) VALUES (?, 'quality_assurance', ?)";
            $assignStmt = $conn->prepare($assignQuery);
            $assignStmt->bind_param("is", $userId, $username);
            $assignStmt->execute();
        }
        
        // Log the activity
        $description = "Assigned Quality Assurance role to user: " . $user['first_name'] . " " . $user['last_name'];
        if ($previousQA) {
            $description .= " (replaced " . $previousQA['first_name'] . " " . $previousQA['last_name'] . ")";
        }
        
        $logQuery = "INSERT INTO activity_logs (username, description, activity_timestamp) VALUES (?, ?, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("ss", $username, $description);
        $logStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $message = 'Quality Assurance role assigned successfully to ' . $user['first_name'] . ' ' . $user['last_name'];
        if ($previousQA) {
            $message .= ' (replaced previous QA user)';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'replaced_previous' => $previousQA ? true : false
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in assign_qa_role.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to assign Quality Assurance role: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
