<?php
ob_start();
header('Content-Type: application/json');
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Log the incoming POST data for debugging
    error_log("=== COURSE APPROVAL/REJECTION DEBUG START ===");
    error_log("File reached: approve_reject_course.php");
    error_log("POST data: " . print_r($_POST, true));
    
    // Include session configuration
    $sessionConfigPath = dirname(dirname(__FILE__)) . '/../session_config.php';
    if (!file_exists($sessionConfigPath)) {
        throw new Exception('Session configuration file not found');
    }
    require_once $sessionConfigPath;
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in as dean
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access - Login required');
    }
    
    // Check if selected role is dean
    if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role']['type'] !== 'dean') {
        error_log("ERROR: Selected role is not dean");
        throw new Exception('Unauthorized access - Dean role required');
    }
    
    // Include database connection
    require_once dirname(__FILE__) . '/../../includes/db_connection.php';
    
    if (!isset($pdo) || $pdo === null) {
        throw new Exception('Database connection failed');
    }
    
    // Get form data
    $courseId = intval($_POST['course_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    
    // Debug: Log form data
    error_log("Form data received:");
    error_log("- Course ID: $courseId");
    error_log("- Action: '$action'");
    
    // Validate inputs
    if ($courseId <= 0) {
        throw new Exception('Invalid course ID');
    }
    if ($action !== 'approve' && $action !== 'reject') {
        throw new Exception('Invalid action. Must be either "approve" or "reject"');
    }
    
    // Get dean's department ID for validation
    $deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;
    
    if (!$deanDepartmentId) {
        $deptCode = $_SESSION['selected_role']['department_code'] ?? null;
        if ($deptCode) {
            $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
            $deptStmt = $pdo->prepare($deptQuery);
            $deptStmt->execute([$deptCode]);
            $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
            if ($deptResult) {
                $deanDepartmentId = $deptResult['id'];
            }
        }
    }
    
    if (!$deanDepartmentId) {
        throw new Exception('Unable to determine department');
    }
    
    // Verify that the course belongs to the dean's department
    $verifyQuery = "
        SELECT c.id 
        FROM courses c
        JOIN programs p ON c.program_id = p.id
        WHERE c.id = ? AND p.department_id = ?
    ";
    $verifyStmt = $pdo->prepare($verifyQuery);
    $verifyStmt->execute([$courseId, $deanDepartmentId]);
    
    if ($verifyStmt->rowCount() === 0) {
        throw new Exception('Course not found or you do not have permission to modify it');
    }
    
    // Determine the new status
    $newStatus = ($action === 'approve') ? 'active' : 'rejected';
    
    // Update the course status
    $updateQuery = "UPDATE courses SET status = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([$newStatus, $courseId]);
    
    if ($updateStmt->rowCount() === 0) {
        throw new Exception('Failed to update course status');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Course $action successfully",
        'status' => $newStatus
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    ob_clean();
    
    // Log the error
    error_log("=== COURSE APPROVAL/REJECTION ERROR ===");
    error_log("Error message: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

ob_end_flush();
?>

