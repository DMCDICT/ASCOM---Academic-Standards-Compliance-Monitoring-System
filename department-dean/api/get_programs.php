<?php
// get_programs.php
// API endpoint to fetch programs for the current department

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

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

try {
    // Get current department code (same pattern as other files in the codebase)
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if (!$deanDepartmentCode) {
        // Try alternative ways to get department code
        $deanDepartmentCode = $_SESSION['dean_department_code'] ?? null;
        if (!$deanDepartmentCode && isset($_SESSION['user_id'])) {
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
    
    if ($deanDepartmentCode) {
        // Query programs by joining with departments table using department_code
        // This matches the pattern used in other files like program-management.php
        $stmt = $pdo->prepare("
            SELECT p.id, p.program_code, p.program_name 
            FROM programs p 
            JOIN departments d ON p.department_id = d.id 
            WHERE d.department_code = ? 
            ORDER BY p.program_name ASC
        ");
        $stmt->execute([$deanDepartmentCode]);
    } else {
        // If no department found, try to get from departments table using dean_user_id
        if (isset($_SESSION['user_id'])) {
            $deptStmt = $pdo->prepare("SELECT department_code FROM departments WHERE dean_user_id = ?");
            $deptStmt->execute([$_SESSION['user_id']]);
            $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
            if ($deptResult) {
                $deanDepartmentCode = $deptResult['department_code'];
                $stmt = $pdo->prepare("
                    SELECT p.id, p.program_code, p.program_name 
                    FROM programs p 
                    JOIN departments d ON p.department_id = d.id 
                    WHERE d.department_code = ? 
                    ORDER BY p.program_name ASC
                ");
                $stmt->execute([$deanDepartmentCode]);
            } else {
                // Still no department found
                error_log("get_programs.php: No department code found in session. Session data: " . json_encode([
                    'selected_role' => $_SESSION['selected_role'] ?? 'NOT_SET',
                    'user_id' => $_SESSION['user_id'] ?? 'NOT_SET'
                ]));
                echo json_encode([
                    'success' => false,
                    'message' => 'No department assigned',
                    'programs' => []
                ]);
                exit;
            }
        } else {
            error_log("get_programs.php: No user_id in session");
            echo json_encode([
                'success' => false,
                'message' => 'User not logged in',
                'programs' => []
            ]);
            exit;
        }
    }
    
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'programs' => $programs
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_programs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch programs',
        'error' => $e->getMessage()
    ]);
}
?>

