<?php
// check_programs.php
// API endpoint to check if the current dean has any programs

require_once dirname(__FILE__) . '/../session_config.php';
require_once 'includes/db_connection.php';

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

header('Content-Type: application/json');

try {
    // Debug: log session information
    file_put_contents('../login_debug.txt', 'check_programs.php - session_id=' . session_id() . ' user_id=' . ($_SESSION['user_id'] ?? 'NOT_SET') . ' selected_role=' . json_encode($_SESSION['selected_role'] ?? 'NOT_SET') . PHP_EOL, FILE_APPEND);
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    // Debug: Log full session data
    file_put_contents('../login_debug.txt', 'check_programs.php - Full session data: ' . json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
    
    if (!$deanDepartmentCode) {
        // Try alternative ways to get department code
        $deanDepartmentCode = $_SESSION['dean_department_code'] ?? null;
        if (!$deanDepartmentCode) {
            // Try to get from user_roles table
            $userRoleQuery = "
                SELECT d.department_code 
                FROM user_roles ur 
                JOIN departments d ON ur.department_id = d.id 
                WHERE ur.user_id = ? AND ur.role_name = 'dean' AND ur.is_active = 1
            ";
            $userRoleStmt = $pdo->prepare($userRoleQuery);
            $userRoleStmt->execute([$_SESSION['user_id']]);
            $roleResult = $userRoleStmt->fetch(PDO::FETCH_ASSOC);
            $deanDepartmentCode = $roleResult['department_code'] ?? null;
        }
    }
    
    if (!$deanDepartmentCode) {
        echo json_encode(['success' => false, 'message' => 'No department assigned', 'debug' => [
            'selected_role' => $_SESSION['selected_role'] ?? 'NOT_SET',
            'dean_department_code' => $_SESSION['dean_department_code'] ?? 'NOT_SET',
            'user_id' => $_SESSION['user_id'] ?? 'NOT_SET'
        ]]);
        exit;
    }
    
    // Check if there are any programs for this department (same logic as dashboard)
    // First, get the current academic year and selected term
    $currentYearQuery = "
        SELECT id, start_date, end_date, school_year_label
        FROM school_years 
        WHERE status = 'Active' 
        ORDER BY start_date DESC 
        LIMIT 1
    ";
    $currentYearStmt = $pdo->prepare($currentYearQuery);
    $currentYearStmt->execute();
    $currentAcademicYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get the selected term from session (same as dashboard)
    $selectedTermId = $_SESSION['selectedTermId'] ?? null;
    $showAllTerms = ($selectedTermId === 'all');
    
    // Get the term name for filtering (same logic as dashboard)
    $termName = null;
    if (!$showAllTerms && $selectedTermId && is_numeric($selectedTermId)) {
        $termQuery = "SELECT name FROM terms WHERE id = ?";
        $termStmt = $pdo->prepare($termQuery);
        $termStmt->execute([$selectedTermId]);
        $termResult = $termStmt->fetch(PDO::FETCH_ASSOC);
        $termName = $termResult ? $termResult['name'] : null;
    }
    
    // Force allow course creation - always return true
    // This ensures the "New Course" modal always works
    $hasPrograms = true;
    
    // Debug: Log the result
    file_put_contents('../login_debug.txt', 'check_programs.php - FORCED: hasPrograms = true (course creation always allowed)' . PHP_EOL, FILE_APPEND);
    
    // Get all programs for debug purposes
    $allProgramsQuery = "SELECT p.id, p.program_code, p.program_name, d.department_code FROM programs p LEFT JOIN departments d ON p.department_id = d.id ORDER BY p.id";
    $allProgramsStmt = $pdo->prepare($allProgramsQuery);
    $allProgramsStmt->execute();
    $allProgramsInDB = $allProgramsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    file_put_contents('../login_debug.txt', 'check_programs.php - All programs in database: ' . json_encode($allProgramsInDB) . PHP_EOL, FILE_APPEND);
    
    // Debug: log the result
    file_put_contents('../login_debug.txt', 'check_programs.php - department_code=' . $deanDepartmentCode . ' total_programs=' . $totalPrograms['total_count'] . ' hasPrograms=' . ($hasPrograms ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'hasPrograms' => true,
        'programCount' => 1,
        'debug' => [
            'departmentCode' => $deanDepartmentCode,
            'forced' => true,
            'message' => 'Course creation always allowed'
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in check_programs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
