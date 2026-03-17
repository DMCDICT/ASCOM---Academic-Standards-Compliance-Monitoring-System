<?php
// remove_department_dean.php
// API endpoint to remove a department dean

// Suppress error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['department_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Department code is required'
    ]);
    exit;
}

$deptCode = $input['department_code'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get department ID and current dean info
    $deptQuery = "
        SELECT 
            d.id,
            d.department_name,
            d.dean_user_id,
            u.first_name,
            u.last_name
        FROM 
            departments d
        LEFT JOIN 
            users u ON d.dean_user_id = u.id
        WHERE 
            d.department_code = ?
    ";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("s", $deptCode);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        throw new Exception('Department not found');
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $deptId = $deptRow['id'];
    $currentDeanId = $deptRow['dean_user_id'];
    
    if (!$currentDeanId) {
        throw new Exception('No dean is currently assigned to this department');
    }
    
    $deanName = $deptRow['first_name'] . ' ' . $deptRow['last_name'];
    
    // Remove the dean assignment
    $updateQuery = "UPDATE departments SET dean_user_id = NULL WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $deptId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to remove dean from department');
    }
    
    // Remove the user's title when dean is removed
    try {
        $updateTitleQuery = "UPDATE users SET title = NULL WHERE id = ?";
        $updateTitleStmt = $conn->prepare($updateTitleQuery);
        $updateTitleStmt->bind_param("i", $currentDeanId);
        
        if (!$updateTitleStmt->execute()) {
            throw new Exception('Failed to update user title');
        }
    } catch (Exception $e) {
        // If title column doesn't exist, continue without updating title
        error_log("Title update failed (column may not exist): " . $e->getMessage());
    }
    
    // Log the activity (if activity_logs table exists)
    try {
        $activityQuery = "
            INSERT INTO activity_logs (username, description, activity_timestamp) 
            VALUES (?, ?, NOW())
        ";
        $activityStmt = $conn->prepare($activityQuery);
        $activityDescription = "Removed " . $deanName . " as dean of department " . $deptCode;
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'super_admin';
        $activityStmt->bind_param("ss", $username, $activityDescription);
        $activityStmt->execute();
    } catch (Exception $e) {
        // Activity logging failed, but don't fail the entire operation
        error_log("Activity logging failed: " . $e->getMessage());
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Dean removed successfully',
        'dean_name' => $deanName,
        'department_name' => $deptRow['department_name']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error in remove_department_dean.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 