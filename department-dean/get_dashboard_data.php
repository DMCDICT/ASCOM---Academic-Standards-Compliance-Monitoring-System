<?php
// get_dashboard_data.php
// AJAX endpoint to get filtered dashboard data based on selected academic term

require_once dirname(__FILE__) . '/../session_config.php';
require_once 'includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
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
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    // Get the term ID from POST data
    $termId = isset($_POST['term_id']) ? $_POST['term_id'] : null;
    
    // Handle "all" option
    $showAllTerms = ($termId === 'all');
    
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if (!$deanDepartmentCode) {
        echo json_encode(['success' => false, 'message' => 'No department assigned']);
        exit;
    }
    
    // Initialize response data
    $response = [
        'success' => true,
        'stats' => [
            'totalPrograms' => 0,
            'totalCourses' => 0,
            'totalFaculty' => 0
        ],
        'requests' => [],
        'selectedTerm' => null
    ];
    
    // Get current academic year safely
    $currentAcademicYear = ['school_year_label' => 'Current Year'];
    try {
        $currentYearStmt = $pdo->prepare("SELECT school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1");
        $currentYearStmt->execute();
        $currentAcademicYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC) ?: $currentAcademicYear;
    } catch (Exception $e) {
        error_log("Get academic year error: " . $e->getMessage());
    }
    
    // Get selected term information
    $response['selectedTerm'] = null;
    try {
        if ($showAllTerms) {
            $response['selectedTerm'] = [
                'id' => 'all',
                'term_name' => 'All Terms',
                'school_year_id' => null,
                'school_year_label' => $currentAcademicYear['school_year_label'] ?? 'Current Year',
                'display_name' => 'All Terms (Current Academic Year)'
            ];
        } elseif ($termId && is_numeric($termId)) {
            $termStmt = $pdo->prepare("SELECT id, name as term_name, school_year_id, school_year_label, CONCAT(name, ' ', school_year_label) as display_name FROM terms WHERE id = ?");
            $termStmt->execute([$termId]);
            $response['selectedTerm'] = $termStmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Term select error: " . $e->getMessage());
    }
    
    // Fetch programs - safely wrapped
    try {
        $programsQuery = "SELECT p.id, p.program_code, p.program_name, p.department_id, COUNT(DISTINCT c.id) as course_count
                         FROM programs p
                         LEFT JOIN courses c ON c.program_id = p.id
                         WHERE p.department_id = ?
                         GROUP BY p.id
                         ORDER BY p.program_name";
        
        $programsStmt = $pdo->prepare($programsQuery);
        $programsStmt->execute([$deanDepartmentCode]);
        $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['stats']['totalPrograms'] = count($programs);
        $response['programs'] = $programs;
    } catch (Exception $e) {
        error_log("Programs fetch error: " . $e->getMessage());
    }
    
    // Return success
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("get_dashboard_data.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
    // Always show all programs, but calculate course counts based on selected term
    if ($showAllTerms) {
        // Show all courses for the current academic year (no term filtering)
        $programsQuery = "
            SELECT p.id, p.program_code, p.program_name, p.major, p.color_code, 
                   COUNT(c.id) as course_count
            FROM programs p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN courses c ON p.id = c.program_id
            WHERE d.department_code = ?
            GROUP BY p.id, p.program_code, p.program_name, p.major, p.color_code
            ORDER BY p.created_at DESC
        ";
        $programsStmt = $pdo->prepare($programsQuery);
        $programsStmt->execute([$deanDepartmentCode]);
    } else {
        // Show all programs, but count courses only for the selected term
        $programsQuery = "
            SELECT p.id, p.program_code, p.program_name, p.major, p.color_code, 
                   COUNT(CASE WHEN c.term = ? THEN c.id ELSE NULL END) as course_count
            FROM programs p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN courses c ON p.id = c.program_id
            WHERE d.department_code = ?
            GROUP BY p.id, p.program_code, p.program_name, p.major, p.color_code
            ORDER BY p.created_at DESC
        ";
        
        $programsStmt = $pdo->prepare($programsQuery);
        if ($termName) {
            $programsStmt->execute([$termName, $deanDepartmentCode]);
        } else {
            // Fallback if no term name
            $programsQuery = "
                SELECT p.id, p.program_code, p.program_name, p.major, p.color_code, 
                       0 as course_count
                FROM programs p
                LEFT JOIN departments d ON p.department_id = d.id
                WHERE d.department_code = ?
                ORDER BY p.created_at DESC
            ";
            $programsStmt = $pdo->prepare($programsQuery);
            $programsStmt->execute([$deanDepartmentCode]);
        }
    }
    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update statistics
    $response['stats']['totalPrograms'] = count($programs);
    
    // Count unique courses for this department (not just sum of program counts)
    if ($showAllTerms) {
        $uniqueCoursesQuery = "
            SELECT COUNT(DISTINCT c.course_code) as unique_course_count
            FROM courses c
            INNER JOIN programs p ON c.program_id = p.id
            INNER JOIN departments d ON p.department_id = d.id
            WHERE d.department_code = ?
        ";
        $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
        $uniqueCoursesStmt->execute([$deanDepartmentCode]);
        $response['stats']['totalCourses'] = $uniqueCoursesStmt->fetchColumn();
    } else {
        $uniqueCoursesQuery = "
            SELECT COUNT(DISTINCT c.course_code) as unique_course_count
            FROM courses c
            INNER JOIN programs p ON c.program_id = p.id
            INNER JOIN departments d ON p.department_id = d.id
            WHERE d.department_code = ? AND c.term = ?
        ";
        $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
        if ($termName) {
            $uniqueCoursesStmt->execute([$deanDepartmentCode, $termName]);
            $response['stats']['totalCourses'] = $uniqueCoursesStmt->fetchColumn();
        } else {
            $response['stats']['totalCourses'] = 0;
        }
    }
    
    // Add programs data to response for updating the Program & Courses Management section
    $response['programs'] = $programs;
    
    // Fetch total faculty count for this department (not filtered by academic term)
    // Faculty members remain the same across all academic terms
    try {
        $facultyQuery = "
            SELECT COUNT(DISTINCT u.id) AS total_faculty 
            FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id 
            JOIN departments d ON u.department_id = d.id 
            WHERE ur.role_name = 'teacher' 
            AND d.department_code = ? 
            AND ur.is_active = 1 
            AND u.is_active = 1
        ";
        $facultyStmt = $pdo->prepare($facultyQuery);
        $facultyStmt->execute([$deanDepartmentCode]);
        $facultyResult = $facultyStmt->fetch(PDO::FETCH_ASSOC);
        $response['stats']['totalFaculty'] = $facultyResult['total_faculty'];
    } catch (Exception $e) {
        $response['stats']['totalFaculty'] = 0;
    }
    
    // Note: reference_requests table doesn't exist yet
    // For now, return empty array for requests
    $response['requests'] = [];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the actual error for debugging
    error_log("get_dashboard_data.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>
