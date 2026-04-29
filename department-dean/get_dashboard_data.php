<?php
// get_dashboard_data.php
// AJAX endpoint to get filtered dashboard data based on selected academic term

require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $termId = $_POST['term_id'] ?? null;
    $showAllTerms = ($termId === 'all' || $termId === null || $termId === '');

    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    if (!$deanDepartmentCode) {
        echo json_encode(['success' => false, 'message' => 'No department assigned']);
        exit;
    }

    $response = [
        'success' => true,
        'stats' => [
            'totalPrograms' => 0,
            'totalCourses' => 0,
            'totalFaculty' => 0,
        ],
        'programs' => [],
        'requests' => [],
        'selectedTerm' => null,
    ];

    $termName = null;
    if ($showAllTerms) {
        $response['selectedTerm'] = [
            'id' => 'all',
            'term_name' => 'All Terms',
        ];
    } elseif (is_numeric($termId)) {
        $termStmt = $pdo->prepare("SELECT id, name AS term_name FROM terms WHERE id = ? LIMIT 1");
        $termStmt->execute([$termId]);
        $termRow = $termStmt->fetch(PDO::FETCH_ASSOC);
        if ($termRow) {
            $termName = $termRow['term_name'];
            $response['selectedTerm'] = $termRow;
        } else {
            // Invalid term id -> treat as "all"
            $showAllTerms = true;
            $response['selectedTerm'] = [
                'id' => 'all',
                'term_name' => 'All Terms',
            ];
        }
    }

    // Always show all programs, but calculate course counts based on selected term
    if ($showAllTerms) {
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
        $programsStmt->execute([$termName, $deanDepartmentCode]);
    }

    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    $response['programs'] = $programs;
    $response['stats']['totalPrograms'] = count($programs);

    // Count unique courses for this department
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
        $response['stats']['totalCourses'] = (int) $uniqueCoursesStmt->fetchColumn();
    } elseif ($termName) {
        $uniqueCoursesQuery = "
            SELECT COUNT(DISTINCT c.course_code) as unique_course_count
            FROM courses c
            INNER JOIN programs p ON c.program_id = p.id
            INNER JOIN departments d ON p.department_id = d.id
            WHERE d.department_code = ? AND c.term = ?
        ";
        $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
        $uniqueCoursesStmt->execute([$deanDepartmentCode, $termName]);
        $response['stats']['totalCourses'] = (int) $uniqueCoursesStmt->fetchColumn();
    }

    // Fetch total faculty count for this department (not filtered by academic term)
    try {
        $facultyQuery = "
            SELECT COUNT(DISTINCT u.id) AS total_faculty
            FROM users u
            JOIN departments d ON u.department_id = d.id
            WHERE EXISTS (
                SELECT 1
                FROM user_roles ur
                WHERE ur.user_id = u.id
                  AND " . ascom_user_roles_role_predicate($pdo, 'ur', 'teacher') . "
                  AND " . ascom_user_roles_active_predicate($pdo, 'ur') . "
            )
            AND d.department_code = ?
            AND u.is_active = 1
        ";
        $facultyStmt = $pdo->prepare($facultyQuery);
        $facultyStmt->execute([$deanDepartmentCode]);
        $response['stats']['totalFaculty'] = (int) $facultyStmt->fetchColumn();
    } catch (Exception $e) {
        $response['stats']['totalFaculty'] = 0;
    }

    echo json_encode($response);
} catch (Exception $e) {
    error_log("get_dashboard_data.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage(),
    ]);
}
