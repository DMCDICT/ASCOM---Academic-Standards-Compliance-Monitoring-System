<?php
// assign_department_dean.php
// API endpoint to assign a teacher as department dean

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

if (!isset($input['department_code']) || !isset($input['teacher_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Department code and teacher ID are required'
    ]);
    exit;
}

$deptCode = $input['department_code'];
$teacherId = $input['teacher_id'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get department ID
    $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("s", $deptCode);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        throw new Exception('Department not found');
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $deptId = $deptRow['id'];
    
    // Verify the teacher exists and belongs to this department
    $teacherQuery = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.employee_no,
            u.department_id,
            u.role_id
        FROM 
            users u
        WHERE 
            u.id = ? 
            AND u.department_id = ?
            AND u.role_id = 4
            AND u.is_active = 1
    ";
    
    $teacherStmt = $conn->prepare($teacherQuery);
    $teacherStmt->bind_param("ii", $teacherId, $deptId);
    $teacherStmt->execute();
    $teacherResult = $teacherStmt->get_result();
    
    if ($teacherResult->num_rows === 0) {
        throw new Exception('Teacher not found or does not belong to this department');
    }
    
    $teacherRow = $teacherResult->fetch_assoc();
    
    // Check if this teacher is already a dean of another department
    $existingDeanQuery = "SELECT id FROM departments WHERE dean_user_id = ? AND id != ?";
    $existingDeanStmt = $conn->prepare($existingDeanQuery);
    $existingDeanStmt->bind_param("ii", $teacherId, $deptId);
    $existingDeanStmt->execute();
    $existingDeanResult = $existingDeanStmt->get_result();
    
    if ($existingDeanResult->num_rows > 0) {
        throw new Exception('This teacher is already assigned as dean of another department');
    }
    
    // First, get the current dean's ID before we update it
    $currentDeanQuery = "SELECT dean_user_id FROM departments WHERE id = ?";
    $currentDeanStmt = $conn->prepare($currentDeanQuery);
    $currentDeanStmt->bind_param("i", $deptId);
    $currentDeanStmt->execute();
    $currentDeanResult = $currentDeanStmt->get_result();
    $currentDeanRow = $currentDeanResult->fetch_assoc();
    $currentDeanId = $currentDeanRow['dean_user_id'];
    
    // Update the department to assign this teacher as dean
    $updateQuery = "UPDATE departments SET dean_user_id = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $teacherId, $deptId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to assign dean to department');
    }
    
    // Remove "Dr." title from the old dean (if there was one)
    if ($currentDeanId) {
        try {
            $removeOldDeanTitleQuery = "UPDATE users SET title = NULL WHERE id = ?";
            $removeOldDeanStmt = $conn->prepare($removeOldDeanTitleQuery);
            $removeOldDeanStmt->bind_param("i", $currentDeanId);
            $removeOldDeanStmt->execute();
        } catch (Exception $e) {
            // If this fails, continue anyway
        }
    }
    
    // Update the new dean's title to "Dr."
    try {
        $updateTitleQuery = "UPDATE users SET title = 'Dr.' WHERE id = ?";
        $updateTitleStmt = $conn->prepare($updateTitleQuery);
        $updateTitleStmt->bind_param("i", $teacherId);
        
        if (!$updateTitleStmt->execute()) {
            throw new Exception('Failed to update user title');
        }
    } catch (Exception $e) {
        // If title column doesn't exist, continue without updating title
    }
    
    // Log the activity (if activity_logs table exists)
    try {
        $activityQuery = "
            INSERT INTO activity_logs (username, description, activity_timestamp) 
            VALUES (?, ?, NOW())
        ";
        $activityStmt = $conn->prepare($activityQuery);
        $activityDescription = "Assigned " . $teacherRow['first_name'] . " " . $teacherRow['last_name'] . " as dean of department " . $deptCode;
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'super_admin';
        $activityStmt->bind_param("ss", $username, $activityDescription);
        $activityStmt->execute();
    } catch (Exception $e) {
        // Activity logging failed, but don't fail the entire operation
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Dean assigned successfully',
        'dean_name' => $teacherRow['first_name'] . ' ' . $teacherRow['last_name'],
        'employee_no' => $teacherRow['employee_no']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 