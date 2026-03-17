<?php
// get_compliance_stats.php
// API endpoint to fetch compliance statistics based on academic term

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Get term parameter (can be "all", term ID, or term name)
    $termFilter = $_GET['term'] ?? 'all';
    
    // First, get all courses that match the filter (for debugging)
    $baseQuery = "
        SELECT DISTINCT
            c.id,
            c.course_code,
            c.term,
            COALESCE(compliant_counts.compliant_count, 0) as compliant_count
        FROM courses c
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
    
    // Build query to get compliance statistics
    // Use a simpler approach - count from the base query results
    $query = "
        SELECT 
            COUNT(*) as total_courses,
            SUM(CASE 
                WHEN COALESCE(compliant_count, 0) >= 5 THEN 1
                ELSE 0
            END) as compliant_courses,
            SUM(CASE 
                WHEN COALESCE(compliant_count, 0) < 5 THEN 1
                ELSE 0
            END) as non_compliant_courses
        FROM (
            $baseQuery
        ) course_compliance
    ";
    
    $params = [];
    
    // Apply term filter to both base query and main query
    if ($termFilter !== 'all' && !empty($termFilter)) {
        // Check if it's a term ID
        if (is_numeric($termFilter)) {
            // Get term name from terms table
            $termNameQuery = "SELECT name FROM terms WHERE id = ? LIMIT 1";
            $termNameStmt = $pdo->prepare($termNameQuery);
            $termNameStmt->execute([$termFilter]);
            $termData = $termNameStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($termData) {
                // Add term filter to base query (only match the specific term, excluding NULL)
                // Match exact term name, case-insensitive
                $baseQuery .= " AND UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL";
                // Update main query by replacing base query part
                $query = "
                    SELECT 
                        COUNT(*) as total_courses,
                        SUM(CASE 
                            WHEN COALESCE(compliant_count, 0) >= 5 THEN 1
                            ELSE 0
                        END) as compliant_courses,
                        SUM(CASE 
                            WHEN COALESCE(compliant_count, 0) < 5 THEN 1
                            ELSE 0
                        END) as non_compliant_courses
                    FROM (
                        $baseQuery
                    ) course_compliance
                ";
                $params[] = trim($termData['name']);
            }
        } else {
            // It's a term name directly (only match the specific term, excluding NULL)
            // Match exact term name, case-insensitive
            $baseQuery .= " AND UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL";
            $query = "
                SELECT 
                    COUNT(*) as total_courses,
                    SUM(CASE 
                        WHEN COALESCE(compliant_count, 0) >= 5 THEN 1
                        ELSE 0
                    END) as compliant_courses,
                    SUM(CASE 
                        WHEN COALESCE(compliant_count, 0) < 5 THEN 1
                        ELSE 0
                    END) as non_compliant_courses
                FROM (
                    $baseQuery
                ) course_compliance
            ";
            $params[] = trim($termFilter);
        }
    }
    
    // Debug: Log the query and params
    error_log("Compliance Stats Query: " . $query);
    error_log("Compliance Stats Params: " . json_encode($params));
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: Log the raw result
    error_log("Compliance Stats Raw Result: " . json_encode($result));
    
    $totalCourses = (int)($result['total_courses'] ?? 0);
    $compliantCourses = (int)($result['compliant_courses'] ?? 0);
    // Directly count non-compliant courses (< 5 compliant references)
    // This includes courses with 0 book references (NULL -> 0 via COALESCE)
    $nonCompliantCourses = (int)($result['non_compliant_courses'] ?? 0);
    
    // Debug: First check how many courses exist in total
    $allCoursesQuery = "SELECT id, course_code, term FROM courses";
    $allCoursesStmt = $pdo->prepare($allCoursesQuery);
    $allCoursesStmt->execute();
    $allCourses = $allCoursesStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("DEBUG: Total courses in database: " . count($allCourses));
    error_log("DEBUG: All courses: " . json_encode($allCourses));
    
    // Get all courses first (this is the source of truth)
    $debugStmt = $pdo->prepare($baseQuery);
    $debugStmt->execute($params);
    $debugCourses = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("DEBUG: Courses found by our query: " . count($debugCourses));
    error_log("All courses being counted: " . json_encode($debugCourses));
    
    // Calculate counts directly from the courses array (more reliable than SQL aggregation)
    // This ensures we're counting exactly what we see
    $totalCourses = count($debugCourses);
    $compliantCourses = 0;
    $nonCompliantCourses = 0;
    
    foreach ($debugCourses as $course) {
        $compCount = (int)($course['compliant_count'] ?? 0);
        if ($compCount >= 5) {
            $compliantCourses++;
        } else {
            $nonCompliantCourses++;
        }
    }
    
    // Log for debugging
    error_log("Calculated from courses array: Total=$totalCourses, Compliant=$compliantCourses, Non-Compliant=$nonCompliantCourses");
    
    // Verify the SQL query result matches (for debugging)
    $sqlTotal = (int)($result['total_courses'] ?? 0);
    $sqlCompliant = (int)($result['compliant_courses'] ?? 0);
    $sqlNonCompliant = (int)($result['non_compliant_courses'] ?? 0);
    
    if ($sqlTotal != $totalCourses || $sqlCompliant != $compliantCourses || $sqlNonCompliant != $nonCompliantCourses) {
        error_log("SQL query mismatch - SQL: Total=$sqlTotal, Compliant=$sqlCompliant, Non-Compliant=$sqlNonCompliant | Array: Total=$totalCourses, Compliant=$compliantCourses, Non-Compliant=$nonCompliantCourses");
    }
    
    // Calculate compliance percentage
    $compliancePercentage = 0;
    if ($totalCourses > 0) {
        $compliancePercentage = round(($compliantCourses / $totalCourses) * 100);
    }
    
    // Calculate improvement by comparing with previous period
    $improvement = 0;
    $currentSchoolYearId = null;
    $previousSchoolYearId = null;
    $termNameForComparison = null;
    
    if ($termFilter === 'all') {
        // Get current active school year
        $currentYearQuery = "SELECT id, year_start, year_end FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
        $currentYearStmt = $pdo->prepare($currentYearQuery);
        $currentYearStmt->execute();
        $currentYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentYear) {
            $currentSchoolYearId = $currentYear['id'];
            // Find previous school year (year_start - 1, year_end - 1)
            $prevYearStart = $currentYear['year_start'] - 1;
            $prevYearEnd = $currentYear['year_end'] - 1;
            $previousYearQuery = "SELECT id FROM school_years WHERE year_start = ? AND year_end = ? LIMIT 1";
            $previousYearStmt = $pdo->prepare($previousYearQuery);
            $previousYearStmt->execute([$prevYearStart, $prevYearEnd]);
            $previousYear = $previousYearStmt->fetch(PDO::FETCH_ASSOC);
            
                         if ($previousYear) {
                 $previousSchoolYearId = $previousYear['id'];
                 // Calculate compliance for previous school year (all terms)
                 // academic_year can be either an ID or a VARCHAR, so we check both
                 $prevBaseQuery = "
                     SELECT DISTINCT
                         c.id,
                         COALESCE(compliant_counts.compliant_count, 0) as compliant_count
                     FROM courses c
                     LEFT JOIN (
                         SELECT 
                             course_id, 
                             COUNT(*) AS compliant_count
                         FROM book_references
                         WHERE publication_year > 0 
                             AND (YEAR(CURDATE()) - CAST(publication_year AS UNSIGNED)) < 5
                         GROUP BY course_id
                     ) compliant_counts ON c.id = compliant_counts.course_id
                     LEFT JOIN school_years sy ON c.academic_year = sy.id
                     WHERE (c.academic_year = ? OR sy.id = ?)
                 ";
                                 $prevStmt = $pdo->prepare($prevBaseQuery);
                 $prevStmt->execute([$previousSchoolYearId, $previousSchoolYearId]);
                $prevCourses = $prevStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($prevCourses)) {
                    $prevTotalCourses = count($prevCourses);
                    $prevCompliantCourses = 0;
                    foreach ($prevCourses as $prevCourse) {
                        $compCount = (int)($prevCourse['compliant_count'] ?? 0);
                        if ($compCount >= 5) {
                            $prevCompliantCourses++;
                        }
                    }
                    $prevCompliancePercentage = $prevTotalCourses > 0 ? round(($prevCompliantCourses / $prevTotalCourses) * 100) : 0;
                    $improvement = $compliancePercentage - $prevCompliancePercentage;
                }
            }
        }
    } else {
        // Specific term selected - get the term's school year and term name
        if (is_numeric($termFilter)) {
            $termInfoQuery = "SELECT t.name, t.school_year_id, sy.year_start, sy.year_end FROM terms t LEFT JOIN school_years sy ON t.school_year_id = sy.id WHERE t.id = ? LIMIT 1";
            $termInfoStmt = $pdo->prepare($termInfoQuery);
            $termInfoStmt->execute([$termFilter]);
            $termInfo = $termInfoStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($termInfo) {
                $termNameForComparison = $termInfo['name'];
                $currentSchoolYearId = $termInfo['school_year_id'];
                $yearStart = $termInfo['year_start'];
                $yearEnd = $termInfo['year_end'];
                
                // Find previous school year
                $prevYearStart = $yearStart - 1;
                $prevYearEnd = $yearEnd - 1;
                $previousYearQuery = "SELECT id FROM school_years WHERE year_start = ? AND year_end = ? LIMIT 1";
                $previousYearStmt = $pdo->prepare($previousYearQuery);
                $previousYearStmt->execute([$prevYearStart, $prevYearEnd]);
                $previousYear = $previousYearStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($previousYear && $termNameForComparison) {
                    $previousSchoolYearId = $previousYear['id'];
                    // Find the previous term with the same name in the previous school year
                    $prevTermQuery = "SELECT id FROM terms WHERE name = ? AND school_year_id = ? LIMIT 1";
                    $prevTermStmt = $pdo->prepare($prevTermQuery);
                    $prevTermStmt->execute([$termNameForComparison, $previousSchoolYearId]);
                    $prevTerm = $prevTermStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($prevTerm) {
                        // Calculate compliance for previous term
                        $prevBaseQuery = "
                            SELECT DISTINCT
                                c.id,
                                COALESCE(compliant_counts.compliant_count, 0) as compliant_count
                            FROM courses c
                            LEFT JOIN (
                                SELECT 
                                    course_id, 
                                    COUNT(*) AS compliant_count
                                FROM book_references
                                WHERE publication_year > 0 
                                    AND (YEAR(CURDATE()) - CAST(publication_year AS UNSIGNED)) < 5
                                GROUP BY course_id
                            ) compliant_counts ON c.id = compliant_counts.course_id
                            WHERE UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL
                                AND c.academic_year = ?
                        ";
                        $prevStmt = $pdo->prepare($prevBaseQuery);
                        $prevStmt->execute([$termNameForComparison, $previousSchoolYearId]);
                        $prevCourses = $prevStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($prevCourses)) {
                            $prevTotalCourses = count($prevCourses);
                            $prevCompliantCourses = 0;
                            foreach ($prevCourses as $prevCourse) {
                                $compCount = (int)($prevCourse['compliant_count'] ?? 0);
                                if ($compCount >= 5) {
                                    $prevCompliantCourses++;
                                }
                            }
                            $prevCompliancePercentage = $prevTotalCourses > 0 ? round(($prevCompliantCourses / $prevTotalCourses) * 100) : 0;
                            $improvement = $compliancePercentage - $prevCompliancePercentage;
                        }
                    }
                }
            }
        } else {
            // Term name directly provided - need to get current school year and term info
            // Get current active school year first
            $currentYearQuery = "SELECT id, year_start, year_end FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
            $currentYearStmt = $pdo->prepare($currentYearQuery);
            $currentYearStmt->execute();
            $currentYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($currentYear) {
                $termNameForComparison = trim($termFilter);
                $currentSchoolYearId = $currentYear['id'];
                $yearStart = $currentYear['year_start'];
                $yearEnd = $currentYear['year_end'];
                
                // Find previous school year
                $prevYearStart = $yearStart - 1;
                $prevYearEnd = $yearEnd - 1;
                $previousYearQuery = "SELECT id FROM school_years WHERE year_start = ? AND year_end = ? LIMIT 1";
                $previousYearStmt = $pdo->prepare($previousYearQuery);
                $previousYearStmt->execute([$prevYearStart, $prevYearEnd]);
                $previousYear = $previousYearStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($previousYear && $termNameForComparison) {
                    $previousSchoolYearId = $previousYear['id'];
                    // Calculate compliance for previous term (same term name, previous school year)
                    $prevBaseQuery = "
                        SELECT DISTINCT
                            c.id,
                            COALESCE(compliant_counts.compliant_count, 0) as compliant_count
                        FROM courses c
                        LEFT JOIN (
                            SELECT 
                                course_id, 
                                COUNT(*) AS compliant_count
                            FROM book_references
                            WHERE publication_year > 0 
                                AND (YEAR(CURDATE()) - CAST(publication_year AS UNSIGNED)) < 5
                            GROUP BY course_id
                        ) compliant_counts ON c.id = compliant_counts.course_id
                        WHERE UPPER(TRIM(c.term)) = UPPER(TRIM(?)) AND c.term IS NOT NULL
                            AND c.academic_year = ?
                    ";
                    $prevStmt = $pdo->prepare($prevBaseQuery);
                    $prevStmt->execute([$termNameForComparison, $previousSchoolYearId]);
                    $prevCourses = $prevStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($prevCourses)) {
                        $prevTotalCourses = count($prevCourses);
                        $prevCompliantCourses = 0;
                        foreach ($prevCourses as $prevCourse) {
                            $compCount = (int)($prevCourse['compliant_count'] ?? 0);
                            if ($compCount >= 5) {
                                $prevCompliantCourses++;
                            }
                        }
                        $prevCompliancePercentage = $prevTotalCourses > 0 ? round(($prevCompliantCourses / $prevTotalCourses) * 100) : 0;
                        $improvement = $compliancePercentage - $prevCompliancePercentage;
                    }
                }
            }
        }
    }
    
    // Get term display name and date range
    $termDisplayName = 'All Terms (Current Academic Year)';
    $dateRange = '';
    $startDate = null;
    $endDate = null;
    
    if ($termFilter !== 'all' && !empty($termFilter)) {
        if (is_numeric($termFilter)) {
            // Get term name and date range from terms table
            $termQuery = "
                SELECT 
                    t.name as term_title,
                    t.start_date,
                    t.end_date,
                    COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end)) as academic_year,
                    CONCAT(t.name, ' ', COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end))) as display_name
                FROM terms t
                LEFT JOIN school_years sy ON t.school_year_id = sy.id
                WHERE t.id = ?
                LIMIT 1
            ";
            $termStmt = $pdo->prepare($termQuery);
            $termStmt->execute([$termFilter]);
            $termData = $termStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($termData) {
                $termDisplayName = $termData['display_name'];
                $startDate = $termData['start_date'];
                $endDate = $termData['end_date'];
                
                // Format dates: "Month YYYY - Month YYYY"
                if ($startDate && $endDate) {
                    $startFormatted = date('F Y', strtotime($startDate));
                    $endFormatted = date('F Y', strtotime($endDate));
                    $dateRange = $startFormatted . ' - ' . $endFormatted;
                }
            }
        } else {
            // Use the term name directly
            $termDisplayName = $termFilter;
        }
    } else {
        // Get date range from school_years table for "All Terms"
        $yearQuery = "
            SELECT start_date, end_date
            FROM school_years 
            WHERE status = 'Active' 
            ORDER BY start_date DESC 
            LIMIT 1
        ";
        $yearStmt = $pdo->prepare($yearQuery);
        $yearStmt->execute();
        $yearData = $yearStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($yearData && $yearData['start_date'] && $yearData['end_date']) {
            $startDate = $yearData['start_date'];
            $endDate = $yearData['end_date'];
            
            // Format dates: "Month YYYY - Month YYYY"
            $startFormatted = date('F Y', strtotime($startDate));
            $endFormatted = date('F Y', strtotime($endDate));
            $dateRange = $startFormatted . ' - ' . $endFormatted;
        }
    }
    
    // Include debug courses in response for troubleshooting
    $response = [
        'success' => true,
        'data' => [
            'total_courses' => $totalCourses,
            'compliant_courses' => $compliantCourses,
            'non_compliant_courses' => $nonCompliantCourses,
            'compliance_percentage' => $compliancePercentage,
            'improvement' => round($improvement, 1), // Round to 1 decimal place
            'term_display_name' => $termDisplayName,
            'date_range' => $dateRange,
            'start_date' => $startDate,
            'end_date' => $endDate
        ],
        'debug' => [
            'calculated_total' => $compliantCourses + $nonCompliantCourses,
            'actual_total' => $totalCourses,
            'courses_found' => $debugCourses ?? [],
            'term_filter' => $termFilter,
            'query_used' => $query,
            'params_used' => $params,
            'total_courses_in_db' => count($allCourses ?? []),
            'all_courses_details' => $allCourses ?? []
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
