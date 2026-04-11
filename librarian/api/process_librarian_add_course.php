<?php
ob_start();
header('Content-Type: application/json');
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Log the incoming POST data for debugging
    
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
    
    // Debug: Log session data
    
    // Check if user is logged in as librarian
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access - Login required');
    }
    
    // Check if selected role is librarian
    if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role']['type'] !== 'librarian') {
        throw new Exception('Unauthorized access - Librarian role required');
    }
    
    // Include database connection
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    // Debug: Check database connection
    if (!isset($pdo) || $pdo === null) {
        throw new Exception('Database connection failed');
    }
    
    // Get form data
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseName = trim($_POST['course_name'] ?? '');
    $units = intval($_POST['units'] ?? 0);
    $yearLevel = trim($_POST['year_level'] ?? '');
    $schoolTerm = trim($_POST['school_term'] ?? '');
    $schoolYear = trim($_POST['school_year'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $selectedPrograms = trim($_POST['programs'] ?? '');
    $status = trim($_POST['status'] ?? 'approved'); // Librarians can create approved courses directly
    $createdByRole = trim($_POST['created_by_role'] ?? 'librarian');
    
    // Get librarian user ID
    $librarianUserId = $_SESSION['user_id'] ?? null;
    
    // Debug: Log form data
    
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
    if (empty($location)) {
        throw new Exception('Location is required');
    }
    if (empty($selectedPrograms)) {
        throw new Exception('At least one program must be selected');
    }
    if (empty($librarianUserId)) {
        throw new Exception('Unable to determine librarian user ID');
    }
    
    // Parse selected programs
    $programIds = array_filter(array_map('trim', explode(',', $selectedPrograms)));
    if (empty($programIds)) {
        throw new Exception('No valid programs selected');
    }
    
    // Validate that all selected programs exist
    $placeholders = str_repeat('?,', count($programIds) - 1) . '?';
    $validateQuery = "SELECT id, program_code, program_name FROM programs WHERE id IN ($placeholders)";
    $validateStmt = $pdo->prepare($validateQuery);
    $validateStmt->execute($programIds);
    $validPrograms = $validateStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($validPrograms) !== count($programIds)) {
        throw new Exception('One or more selected programs are invalid');
    }
    
    // Check if course code already exists in any of the selected programs
    $checkQuery = "SELECT COUNT(*) FROM courses WHERE course_code = ? AND program_id IN ($placeholders)";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(array_merge([$courseCode], $programIds));
    $existingCount = $checkStmt->fetchColumn();
    
    if ($existingCount > 0) {
        throw new Exception('Course code already exists in one or more selected programs');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        $createdCourses = [];
        
        // Create course for each selected program
        foreach ($programIds as $programId) {
            // Find the program info
            $programInfo = null;
            foreach ($validPrograms as $prog) {
                if ($prog['id'] == $programId) {
                    $programInfo = $prog;
                    break;
                }
            }
            
            // First check if created_by_user_id and created_by_role columns exist
            $columnsQuery = "SHOW COLUMNS FROM courses LIKE 'created_by_user_id'";
            $columnsStmt = $pdo->prepare($columnsQuery);
            $columnsStmt->execute();
            $hasCreatedByUserId = $columnsStmt->rowCount() > 0;
            
            if ($hasCreatedByUserId) {
                // Use the new columns
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
                    created_by_user_id,
                    created_by_role,
                    created_at,
                    location
                ) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([
                    $courseCode,
                    $courseName,
                    $units,
                    $programId,
                    $status,
                    $schoolTerm,
                    $schoolYear,
                    $yearLevel,
                    $librarianUserId, // created_by
                    $librarianUserId, // created_by_user_id
                    $createdByRole,   // created_by_role
                    $location
                ]);
            } else {
                // Fallback for older schema
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
                ) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, NOW())";
                
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([
                    $courseCode,
                    $courseName,
                    $units,
                    $programId,
                    $status,
                    $schoolTerm,
                    $schoolYear,
                    $yearLevel,
                    $librarianUserId
                ]);
            }
            
            $courseId = $pdo->lastInsertId();
            $createdCourses[] = [
                'id' => $courseId,
                'course_code' => $courseCode,
                'course_title' => $courseName,
                'program_id' => $programId,
                'program_code' => $programInfo['program_code'] ?? '',
                'program_name' => $programInfo['program_name'] ?? ''
            ];
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Get first program name for success message
        $programNames = array_column($createdCourses, 'program_name');
        $firstProgramName = !empty($programNames) ? $programNames[0] : 'Selected Program';
        if (count($programNames) > 1) {
            $firstProgramName .= ' (' . (count($programNames) - 1) . ' more)';
        }
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Course created successfully',
            'courses' => $createdCourses,
            'program_name' => $firstProgramName
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    ob_clean();
    
    // Log the error
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

ob_end_flush();
?>

