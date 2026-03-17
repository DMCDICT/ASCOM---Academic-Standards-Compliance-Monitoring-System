<?php
ob_start();
header('Content-Type: application/json');
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Log the incoming POST data for debugging
    error_log("=== COURSE CREATION DEBUG START ===");
    error_log("File reached: process_add_course.php");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session status: " . session_status());
    
    // Include session configuration
    $sessionConfigPath = dirname(__FILE__) . '/../session_config.php';
    error_log("Looking for session config at: $sessionConfigPath");
    if (!file_exists($sessionConfigPath)) {
        error_log("Session config file not found at: $sessionConfigPath");
        throw new Exception('Session configuration file not found');
    }
    require_once $sessionConfigPath;
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Debug: Log session data
    error_log("Session data after start: " . print_r($_SESSION, true));
    
    // Check if user is logged in as dean
    if (!isset($_SESSION['dean_logged_in']) || !$_SESSION['dean_logged_in']) {
        error_log("ERROR: User not logged in as dean");
        error_log("dean_logged_in: " . ($_SESSION['dean_logged_in'] ?? 'not set'));
        throw new Exception('Unauthorized access - Dean role required');
    }
    
    // Check if selected role is dean
    if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role']['type'] !== 'dean') {
        error_log("ERROR: Selected role is not dean");
        error_log("selected_role: " . print_r($_SESSION['selected_role'] ?? 'not set', true));
        throw new Exception('Unauthorized access - Dean role required');
    }
    
    // Include database connection
    require_once 'includes/db_connection.php';
    
    // Debug: Check database connection
    if (!isset($pdo) || $pdo === null) {
        error_log("ERROR: Database connection failed - \$pdo is null");
        throw new Exception('Database connection failed');
    }
    error_log("Database connection: OK");
    
    // Get form data
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseName = trim($_POST['course_name'] ?? '');
    $units = intval($_POST['units'] ?? 0);
    $yearLevel = trim($_POST['year_level'] ?? '');
    $schoolTerm = trim($_POST['school_term'] ?? '');
    $schoolYear = trim($_POST['school_year'] ?? '');
    $selectedPrograms = trim($_POST['programs'] ?? '');
    
    // Debug: Log form data
    error_log("Form data received:");
    error_log("- Course Code: '$courseCode'");
    error_log("- Course Name: '$courseName'");
    error_log("- Units: $units");
    error_log("- Year Level: '$yearLevel'");
    error_log("- School Term: '$schoolTerm'");
    error_log("- School Year: '$schoolYear'");
    error_log("- Selected Programs: '$selectedPrograms'");
    
    // Validate required fields
    if (empty($courseCode)) {
        throw new Exception('Course code is required');
    }
    if (empty($courseName)) {
        throw new Exception('Course name is required');
    }
    if ($units <= 0) {
        throw new Exception('Units must be greater than 0');
    }
    if (empty($yearLevel)) {
        throw new Exception('Year level is required');
    }
    if (empty($schoolTerm)) {
        throw new Exception('School term is required');
    }
    if (empty($schoolYear)) {
        throw new Exception('School year is required');
    }
    if (empty($selectedPrograms)) {
        throw new Exception('At least one program must be selected');
    }
    
    // Get dean's department ID and user ID
    $deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;
    $deanUserId = $_SESSION['user_id'] ?? null;
    
    // If department_id is not available, get it from department_code
    if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_code'])) {
        $deptCode = $_SESSION['selected_role']['department_code'];
        error_log("Getting department ID for code: $deptCode");
        $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$deptCode]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        if ($deptResult) {
            $deanDepartmentId = $deptResult['id'];
            error_log("Found department ID: $deanDepartmentId");
        } else {
            error_log("No department found for code: $deptCode");
        }
    }
    
    if (!$deanDepartmentId) {
        throw new Exception('Unable to determine department');
    }
    
    // Parse selected programs
    $programIds = array_filter(array_map('trim', explode(',', $selectedPrograms)));
    if (empty($programIds)) {
        throw new Exception('No valid programs selected');
    }
    
    // Validate that all selected programs belong to the dean's department
    $placeholders = str_repeat('?,', count($programIds) - 1) . '?';
    $validateQuery = "SELECT id FROM programs WHERE id IN ($placeholders) AND department_id = ?";
    $validateStmt = $pdo->prepare($validateQuery);
    $validateStmt->execute(array_merge($programIds, [$deanDepartmentId]));
    $validPrograms = $validateStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($validPrograms) !== count($programIds)) {
        throw new Exception('One or more selected programs are not valid for your department');
    }
    
    // Check if course code already exists in any of the selected programs (excluding library courses)
    $checkQuery = "SELECT COUNT(*) FROM courses WHERE course_code = ? AND program_id IN ($placeholders) AND program_id IS NOT NULL";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(array_merge([$courseCode], $programIds));
    $existingCount = $checkStmt->fetchColumn();
    
    if ($existingCount > 0) {
        throw new Exception('Course code already exists in one or more selected programs');
    }
    
    // Check if there's a library course (program_id IS NULL) with this course code
    $libraryCourseQuery = "SELECT id FROM courses WHERE course_code = ? AND program_id IS NULL LIMIT 1";
    $libraryCourseStmt = $pdo->prepare($libraryCourseQuery);
    $libraryCourseStmt->execute([$courseCode]);
    $libraryCourse = $libraryCourseStmt->fetch(PDO::FETCH_ASSOC);
    $libraryCourseId = $libraryCourse ? $libraryCourse['id'] : null;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        $createdCourses = [];
        $isFirstProgram = true;
        
        // Create/update course for each selected program
        foreach ($programIds as $programId) {
            $courseId = null;
            
            // For the first program: if library course exists, UPDATE it instead of creating new
            if ($isFirstProgram && $libraryCourseId) {
                // Update the library course to assign it to the first program
                $updateQuery = "UPDATE courses SET
                    course_title = ?,
                    units = ?,
                    program_id = ?,
                    status = 'Active',
                    term = ?,
                    academic_year = ?,
                    year_level = ?,
                    created_by = ?,
                    updated_at = NOW()
                WHERE id = ?";
                
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([
                    $courseName,
                    $units,
                    $programId,
                    $schoolTerm,
                    $schoolYear,
                    $yearLevel,
                    $deanUserId,
                    $libraryCourseId
                ]);
                
                $courseId = $libraryCourseId;
                error_log("Updated library course ID $libraryCourseId with program_id $programId");
            } else {
                // For subsequent programs or if no library course exists, create new entry
                $insertQuery = "INSERT INTO courses (
                    course_code, 
                    course_title, 
                    units, 
                    program_id, 
                    faculty_id,
                    status, 
                    term, 
                    academic_year, 
                    year_level, 
                    created_by,
                    created_at
                ) VALUES (?, ?, ?, ?, NULL, 'Active', ?, ?, ?, ?, NOW())";
                
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([
                    $courseCode,
                    $courseName,
                    $units,
                    $programId,
                    $schoolTerm,
                    $schoolYear,
                    $yearLevel,
                    $deanUserId
                ]);
                
                $courseId = $pdo->lastInsertId();
                error_log("Created new course ID $courseId for program_id $programId");
            }
            
            $createdCourses[] = [
                'id' => $courseId,
                'course_code' => $courseCode,
                'course_title' => $courseName,
                'program_id' => $programId
            ];
            
            $isFirstProgram = false;
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Course created successfully',
            'courses' => $createdCourses,
            'count' => count($createdCourses)
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("EXCEPTION CAUGHT: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    
    // Return detailed error for debugging
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ];
    
    echo json_encode($errorResponse, JSON_PRETTY_PRINT);
}

ob_end_flush();
?>
