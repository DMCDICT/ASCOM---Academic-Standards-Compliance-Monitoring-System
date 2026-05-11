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
	    $filterTermId = (!$showAllTerms && is_numeric($termId)) ? (int) $termId : null;

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
	            'compliantCourses' => 0,
	            'nonCompliantCourses' => 0,
	        ],
	        'programs' => [],
	        'requests' => [],
	        'selectedTerm' => null,
	    ];

	    if ($showAllTerms) {
	        $response['selectedTerm'] = [
	            'id' => 'all',
	            'term_name' => 'All Terms',
	        ];
	    } elseif ($filterTermId !== null) {
	        $termStmt = $pdo->prepare("SELECT id, name AS term_name FROM terms WHERE id = ? LIMIT 1");
	        $termStmt->execute([$filterTermId]);
	        $termRow = $termStmt->fetch(PDO::FETCH_ASSOC);
	        if ($termRow) {
	            $response['selectedTerm'] = $termRow;
	        } else {
	            // Invalid term id -> treat as "all"
	            $showAllTerms = true;
	            $filterTermId = null;
	            $response['selectedTerm'] = [
	                'id' => 'all',
	                'term_name' => 'All Terms',
	            ];
	        }
	    }

	    // Get term name for filtering (courses.term is VARCHAR)
	    $filterTermName = null;
	    if (!$showAllTerms && $filterTermId !== null) {
	        $termNameStmt = $pdo->prepare("SELECT name FROM terms WHERE id = ? LIMIT 1");
	        $termNameStmt->execute([$filterTermId]);
	        $termNameRow = $termNameStmt->fetch(PDO::FETCH_ASSOC);
	        $filterTermName = $termNameRow['name'] ?? null;
	    }

	    // Always show all programs; compute course_count based on selected term (if any)
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
	        $programsStmt->execute([$filterTermName, $deanDepartmentCode]);
	    }

    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    $response['programs'] = $programs;
    $response['stats']['totalPrograms'] = count($programs);

	    // Count unique courses for this department
	    if ($showAllTerms) {
	        $uniqueCoursesQuery = "
	            SELECT COUNT(DISTINCT c.id) as unique_course_count
	            FROM courses c
	            INNER JOIN programs p ON c.program_id = p.id
	            INNER JOIN departments d ON p.department_id = d.id
	            WHERE d.department_code = ?
	        ";
	        $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
	        $uniqueCoursesStmt->execute([$deanDepartmentCode]);
	        $response['stats']['totalCourses'] = (int) $uniqueCoursesStmt->fetchColumn();
	    } elseif ($filterTermName) {
	        $uniqueCoursesQuery = "
	            SELECT COUNT(DISTINCT c.id) as unique_course_count
	            FROM courses c
	            INNER JOIN programs p ON c.program_id = p.id
	            INNER JOIN departments d ON p.department_id = d.id
	            WHERE d.department_code = ? AND c.term = ?
	        ";
	        $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
	        $uniqueCoursesStmt->execute([$deanDepartmentCode, $filterTermName]);
	        $response['stats']['totalCourses'] = (int) $uniqueCoursesStmt->fetchColumn();
	    }

	    // Fetch total faculty count for this department (not filtered by academic term)
    try {
        $facultyQuery = "
            SELECT COUNT(DISTINCT u.id) AS total_faculty
            FROM users u
            JOIN departments d ON u.department_id = d.id
            WHERE (
                u.role_id = 4
                OR EXISTS (
                    SELECT 1
                    FROM user_roles ur
                    WHERE ur.user_id = u.id
                      AND " . ascom_user_roles_role_predicate($pdo, 'ur', 'teacher') . "
                      AND " . ascom_user_roles_active_predicate($pdo, 'ur') . "
                )
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

	    // Compliance stats (5+ compliant books within last 5 years), filtered by term when a term is selected
	    try {
	        $currentYear = date('Y');
	        $fiveYearsAgo = $currentYear - 4;

	        $deptIdStmt = $pdo->prepare("SELECT id FROM departments WHERE department_code = ? LIMIT 1");
	        $deptIdStmt->execute([$deanDepartmentCode]);
	        $deptRow = $deptIdStmt->fetch(PDO::FETCH_ASSOC);
	        $deptId = $deptRow['id'] ?? null;

	        if ($deptId) {
	            if ($showAllTerms) {
	                $complianceQuery = "
	                    SELECT
	                        c.id,
	                        c.course_code,
	                        c.course_title,
	                        COUNT(br.id) as total_references,
	                        SUM(CASE 
	                            WHEN br.publication_year IS NOT NULL 
	                            AND br.publication_year >= :fiveYearsAgo 
	                            AND br.publication_year <= :currentYear 
	                            THEN 1 
	                            ELSE 0 
	                        END) as recent_references
	                    FROM courses c
	                    INNER JOIN programs p ON c.program_id = p.id
	                    LEFT JOIN book_references br ON c.id = br.course_id
	                    WHERE p.department_id = :deptId
	                    GROUP BY c.id, c.course_code, c.course_title
	                ";
	                $complianceStmt = $pdo->prepare($complianceQuery);
	                $complianceStmt->execute([
	                    'deptId' => $deptId,
	                    'fiveYearsAgo' => $fiveYearsAgo,
	                    'currentYear' => $currentYear
	                ]);
	            } else {
	                $complianceQuery = "
	                    SELECT
	                        c.id,
	                        c.course_code,
	                        c.course_title,
	                        COUNT(br.id) as total_references,
	                        SUM(CASE 
	                            WHEN br.publication_year IS NOT NULL 
	                            AND br.publication_year >= :fiveYearsAgo 
	                            AND br.publication_year <= :currentYear 
	                            THEN 1 
	                            ELSE 0 
	                        END) as recent_references
	                    FROM courses c
	                    INNER JOIN programs p ON c.program_id = p.id
	                    LEFT JOIN book_references br ON c.id = br.course_id
	                    WHERE p.department_id = :deptId AND c.term = :termFilter
	                    GROUP BY c.id, c.course_code, c.course_title
	                ";
	                $complianceStmt = $pdo->prepare($complianceQuery);
	                $complianceStmt->execute([
	                    'deptId' => $deptId,
	                    'termFilter' => $filterTermName,
	                    'fiveYearsAgo' => $fiveYearsAgo,
	                    'currentYear' => $currentYear
	                ]);
	            }
	            $results = $complianceStmt->fetchAll(PDO::FETCH_ASSOC);

	            $compliant = 0;
	            $nonCompliant = 0;
	            foreach ($results as $course) {
	                $recentRefs = (int) ($course['recent_references'] ?? 0);
	                if ($recentRefs >= 5) {
	                    $compliant++;
	                } else {
	                    $nonCompliant++;
	                }
	            }
	            $response['stats']['compliantCourses'] = $compliant;
	            $response['stats']['nonCompliantCourses'] = $nonCompliant;
	        }
	    } catch (Exception $e) {
	        $response['stats']['compliantCourses'] = 0;
	        $response['stats']['nonCompliantCourses'] = 0;
	    }

	    echo json_encode($response);
	} catch (Exception $e) {
    error_log("get_dashboard_data.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage(),
    ]);
}
