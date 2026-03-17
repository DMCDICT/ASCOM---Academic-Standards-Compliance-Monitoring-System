<?php
// get_department_compliance.php
// API endpoint to fetch department compliance statistics based on academic term

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Get term parameter (can be "all", term ID, or term name)
    $termFilter = $_GET['term'] ?? 'all';
    
    // First, get all departments from the database
    $allDeptsQuery = "SELECT department_code, department_name, color_code FROM departments ORDER BY department_code ASC";
    $allDeptsStmt = $pdo->prepare($allDeptsQuery);
    $allDeptsStmt->execute();
    $allDepartments = $allDeptsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize department stats with all departments (0 values for now)
    $departmentStats = [];
    foreach ($allDepartments as $dept) {
        $deptCode = $dept['department_code'];
        $departmentStats[$deptCode] = [
            'department_code' => $deptCode,
            'department_name' => $dept['department_name'] ?? $deptCode,
            'color_code' => $dept['color_code'] ?? '#1976d2',
            'total_courses' => 0,
            'compliant_courses' => 0,
            'non_compliant_courses' => 0,
            'compliance_percentage' => 0,
            'courses_needing_attention' => 0
        ];
    }
    
    // Base query to get courses with their departments and compliance counts
    $baseQuery = "
        SELECT DISTINCT
            c.id as course_id,
            c.course_code,
            d.department_code,
            d.department_name,
            d.color_code,
            COALESCE(compliant_counts.compliant_count, 0) as compliant_count
        FROM courses c
        INNER JOIN programs p ON c.program_id = p.id
        INNER JOIN departments d ON p.department_id = d.id
        LEFT JOIN (
            SELECT 
                course_id, 
                COUNT(*) AS compliant_count
            FROM book_references
            WHERE publication_year > 0 
                AND (YEAR(CURDATE()) - CAST(publication_year AS UNSIGNED)) < 5
            GROUP BY course_id
        ) compliant_counts ON c.id = compliant_counts.course_id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Apply term filter
    if ($termFilter !== 'all' && !empty($termFilter)) {
        // Check if it's a term ID
        if (is_numeric($termFilter)) {
            // Get term name from terms table
            $termNameQuery = "SELECT name FROM terms WHERE id = ? LIMIT 1";
            $termNameStmt = $pdo->prepare($termNameQuery);
            $termNameStmt->execute([$termFilter]);
            $termData = $termNameStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($termData) {
                $baseQuery .= " AND UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL";
                $params[] = trim($termData['name']);
            }
        } else {
            // It's a term name directly
            $baseQuery .= " AND UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL";
            $params[] = trim($termFilter);
        }
    }
    
    // Execute the query to get all courses with department info
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update department stats with course data
    foreach ($courses as $course) {
        $deptCode = $course['department_code'];
        
        // Skip if department doesn't exist in our stats (shouldn't happen, but just in case)
        if (!isset($departmentStats[$deptCode])) {
            continue;
        }
        
        // Increment counters
        $departmentStats[$deptCode]['total_courses']++;
        
        $compCount = (int)($course['compliant_count'] ?? 0);
        if ($compCount >= 5) {
            $departmentStats[$deptCode]['compliant_courses']++;
        } else {
            $departmentStats[$deptCode]['non_compliant_courses']++;
            $departmentStats[$deptCode]['courses_needing_attention']++;
        }
    }
    
    // Calculate compliance percentage for each department
    foreach ($departmentStats as $deptCode => &$stats) {
        if ($stats['total_courses'] > 0) {
            $stats['compliance_percentage'] = round(($stats['compliant_courses'] / $stats['total_courses']) * 100);
        }
    }
    unset($stats); // Unset reference
    
    // Sort departments alphabetically by department code (ascending)
    usort($departmentStats, function($a, $b) {
        return strcmp($a['department_code'], $b['department_code']);
    });
    
    echo json_encode([
        'success' => true,
        'data' => array_values($departmentStats), // Reset array keys
        'debug' => [
            'term_filter' => $termFilter,
            'total_courses_found' => count($courses),
            'departments_count' => count($departmentStats)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
