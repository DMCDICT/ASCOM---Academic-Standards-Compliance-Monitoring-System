<?php
// get_academic_terms.php
// API endpoint to fetch academic terms for dropdown

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    $terms = [];
    
    // First, get the current academic year - check if school_year_label column exists
    // Try with school_year_label first, fallback to year_start/year_end if needed
    $currentYearQuery = "
        SELECT id, start_date, end_date, 
               COALESCE(school_year_label, CONCAT('SY ', year_start, '-', year_end)) as school_year_label
        FROM school_years 
        WHERE status = 'Active' 
        ORDER BY start_date DESC 
        LIMIT 1
    ";
    $currentYearStmt = $pdo->prepare($currentYearQuery);
    $currentYearStmt->execute();
    $currentAcademicYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($currentAcademicYear) {
        // Get only the terms for the current academic year from the 'terms' table
        // Match the exact query structure from department dean dashboard
        $query = "
            SELECT 
                t.id,
                t.name as term_name,
                t.school_year_id,
                COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end)) as school_year_label,
                t.start_date,
                t.end_date,
                t.is_active as status,
                CONCAT(t.name, ' ', COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end))) as display_name
            FROM terms t
            INNER JOIN school_years sy ON t.school_year_id = sy.id
            WHERE sy.id = ?
            ORDER BY 
                CASE t.name 
                    WHEN '1st Semester' THEN 1
                    WHEN '2nd Semester' THEN 2
                    WHEN 'Summer Semester' THEN 3
                    ELSE 4
                END
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$currentAcademicYear['id']]);
        $dbTerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($dbTerms as $term) {
            $terms[] = [
                'id' => $term['id'],
                'value' => (string)$term['id'],
                'label' => $term['display_name'],
                'term_name' => $term['term_name'],
                'school_year_label' => $term['school_year_label'],
                'status' => $term['status'],
                'start_date' => $term['start_date'],
                'end_date' => $term['end_date']
            ];
        }
    } else {
        // No active academic year found
    }
    
    // Get initial date range for "All Terms" option
    $allTermsDateRange = '';
    if ($currentAcademicYear && $currentAcademicYear['start_date'] && $currentAcademicYear['end_date']) {
        $startFormatted = date('F Y', strtotime($currentAcademicYear['start_date']));
        $endFormatted = date('F Y', strtotime($currentAcademicYear['end_date']));
        $allTermsDateRange = $startFormatted . ' - ' . $endFormatted;
    }
    
    echo json_encode([
        'success' => true,
        'terms' => $terms,
        'current_academic_year' => $currentAcademicYear ? [
            'id' => $currentAcademicYear['id'],
            'label' => $currentAcademicYear['school_year_label'],
            'start_date' => $currentAcademicYear['start_date'],
            'end_date' => $currentAcademicYear['end_date']
        ] : null,
        'all_terms_date_range' => $allTermsDateRange,
        'debug' => [
            'terms_count' => count($terms),
            'has_academic_year' => !empty($currentAcademicYear)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString(),
        'terms' => []
    ]);
}
?>
