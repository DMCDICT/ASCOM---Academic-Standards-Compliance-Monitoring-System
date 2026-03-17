<?php
// process_edit_program.php
// Handles AJAX requests to update existing programs in the database.

// Suppress any output that might interfere with JSON response
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Set error handler to catch any PHP errors
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    require_once dirname(__FILE__) . '/../session_config.php';
    require_once dirname(__FILE__) . '/includes/db_connection.php';

    // Ensure session configuration is applied before starting session
    if (session_status() == PHP_SESSION_NONE) {
        // Configure session before starting
        session_name('ASCOM_SESSION');
        session_set_cookie_params([
            'lifetime' => 30 * 24 * 60 * 60, // 30 days
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

    // Debug: log session information
    file_put_contents('../login_debug.txt', 'process_edit_program.php - session_id=' . session_id() . ' dean_logged_in=' . ($_SESSION['dean_logged_in'] ?? 'NOT_SET') . ' selected_role=' . json_encode($_SESSION['selected_role'] ?? 'NOT_SET') . PHP_EOL, FILE_APPEND);

    // Check if user is logged in as dean - more flexible check
    $isDean = false;

    // Check multiple ways user could be authenticated as dean
    if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
        $isDean = true;
        file_put_contents('../login_debug.txt', 'process_edit_program.php - dean_logged_in found' . PHP_EOL, FILE_APPEND);
    } elseif (isset($_SESSION['selected_role']['role_name']) && $_SESSION['selected_role']['role_name'] === 'dean') {
        $isDean = true;
        file_put_contents('../login_debug.txt', 'process_edit_program.php - selected_role dean found' . PHP_EOL, FILE_APPEND);
    } elseif (isset($_SESSION['selected_role']['type']) && $_SESSION['selected_role']['type'] === 'dean') {
        $isDean = true;
        file_put_contents('../login_debug.txt', 'process_edit_program.php - selected_role type dean found' . PHP_EOL, FILE_APPEND);
    } elseif (isset($_SESSION['user_id'])) {
        // Check if user is assigned as dean in departments table
        try {
            $deptQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
            $deptStmt = $pdo->prepare($deptQuery);
            $deptStmt->execute([$_SESSION['user_id']]);
            if ($deptStmt->rowCount() > 0) {
                $isDean = true;
                file_put_contents('../login_debug.txt', 'process_edit_program.php - dean found in departments table' . PHP_EOL, FILE_APPEND);
            }
        } catch (Exception $e) {
            // Continue with other checks
            file_put_contents('../login_debug.txt', 'process_edit_program.php - error checking departments: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    if (!$isDean) {
        file_put_contents('../login_debug.txt', 'process_edit_program.php - AUTHORIZATION FAILED - session data: ' . json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access - Dean role required']);
        exit();
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit();
    }

    // Get the dean's department ID from session or selected_role
    $deanDepartmentId = $_SESSION['dean_department_id'] ?? null;
    $deanUserId = $_SESSION['user_id'] ?? null;

    // If not in session, get it from selected_role
    if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_id'])) {
        $deanDepartmentId = $_SESSION['selected_role']['department_id'];
    }

    // If still not found, get it from department_code in selected_role
    if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_code'])) {
        try {
            $deptCode = $_SESSION['selected_role']['department_code'];
            $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
            $deptStmt = $pdo->prepare($deptQuery);
            $deptStmt->execute([$deptCode]);
            $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
            if ($deptResult) {
                $deanDepartmentId = $deptResult['id'];
            }
        } catch (Exception $e) {
            file_put_contents('../login_debug.txt', 'process_edit_program.php - error getting department: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    if (!$deanDepartmentId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Department not found']);
        exit();
    }

    // Get form data
    $programCode = trim($_POST['program_code'] ?? '');
    $programName = trim($_POST['program_name'] ?? '');
    $major = trim($_POST['major'] ?? '');

    // Validate required fields
    if (empty($programCode)) {
        echo json_encode(['success' => false, 'message' => 'Program code is required']);
        exit();
    }

    if (empty($programName)) {
        echo json_encode(['success' => false, 'message' => 'Program name is required']);
        exit();
    }

    // Check if program exists and belongs to the dean's department
    $checkProgramQuery = "
        SELECT p.id, p.program_name, p.color_code, p.major 
        FROM programs p 
        JOIN departments d ON p.department_id = d.id 
        WHERE p.program_code = ? AND d.id = ?
    ";
    $checkStmt = $pdo->prepare($checkProgramQuery);
    $checkStmt->execute([$programCode, $deanDepartmentId]);
    $existingProgram = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingProgram) {
        echo json_encode(['success' => false, 'message' => 'Program not found or you do not have permission to edit it']);
        exit();
    }

    // Check if program name already exists (excluding current program)
    $checkNameQuery = "
        SELECT p.id 
        FROM programs p 
        JOIN departments d ON p.department_id = d.id 
        WHERE p.program_name = ? AND p.program_code != ? AND d.id = ?
    ";
    $checkNameStmt = $pdo->prepare($checkNameQuery);
    $checkNameStmt->execute([$programName, $programCode, $deanDepartmentId]);
    
    if ($checkNameStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Program name already exists in your department']);
        exit();
    }

    // Update the program
    $updateQuery = "
        UPDATE programs 
        SET program_name = ?, major = ?, updated_at = NOW() 
        WHERE program_code = ? AND department_id = ?
    ";
    
    $updateStmt = $pdo->prepare($updateQuery);
    $updateResult = $updateStmt->execute([
        $programName,
        $major,
        $programCode,
        $deanDepartmentId
    ]);

    if ($updateResult) {
        // Log successful update
        file_put_contents('../login_debug.txt', 'process_edit_program.php - Program updated successfully: ' . $programCode . PHP_EOL, FILE_APPEND);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Program updated successfully!',
            'program' => [
                'code' => $programCode,
                'name' => $programName,
                'major' => $major
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update program. Please try again.']);
    }

} catch (Exception $e) {
    // Log the error
    file_put_contents('../login_debug.txt', 'process_edit_program.php - ERROR: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the program. Please try again.']);
} finally {
    // Clean any output buffer
    ob_end_clean();
}
?>
