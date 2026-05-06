<?php
// get_academic_terms.php
// API endpoint to fetch academic terms for dropdown

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    $terms = [];
    
    // First, check if year_start column exists in school_years table
    $columnCheckStmt = $pdo->prepare("SHOW COLUMNS FROM school_years LIKE 'year_start'");
    $columnCheckStmt->execute();
    $yearStartExists = $columnCheckStmt->rowCount() > 0;
    
    // Build the query based on whether year_start exists
    if ($yearStartExists) {
        $currentYearQuery = "
            SELECT id, start_date, end_date, 
                   COALESCE(school_year_label, CONCAT('SY ', year_start, '-', year_end)) as school_year_label
            FROM school_years 
            WHERE is_active = 1 
            ORDER BY start_date DESC 
            LIMIT 1
        ";
    } else {
        // Fallback: just use school_year_label or generate from start_date/end_date
        $currentYearQuery = "
            SELECT id, start_date, end_date, 
                   COALESCE(school_year_label, CONCAT('SY ', DATE_FORMAT(start_date, '%Y'), '-', DATE_FORMAT(end_date, '%Y'))) as school_year_label
            FROM school_years 
            WHERE is_active = 1 
            ORDER BY start_date DESC 
            LIMIT 1
        ";
    }
    
    $currentYearStmt = $pdo->prepare($currentYearQuery);
    $currentYearStmt->execute();
    $currentAcademicYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($currentAcademicYear) {
        // Build the terms query based on column existence
        if ($yearStartExists) {
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
        } else {
            $query = "
                SELECT 
                    t.id,
                    t.name as term_name,
                    t.school_year_id,
                    COALESCE(sy.school_year_label, CONCAT('SY ', DATE_FORMAT(sy.start_date, '%Y'), '-', DATE_FORMAT(sy.end_date, '%Y'))) as school_year_label,
                    t.start_date,
                    t.end_date,
                    t.is_active as status,
                    CONCAT(t.name, ' ', COALESCE(sy.school_year_label, CONCAT('SY ', DATE_FORMAT(sy.start_date, '%Y'), '-', DATE_FORMAT(sy.end_date, '%Y')))) as display_name
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
        }
        
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
        // No active academic year found - try to get any terms without filtering by school year
        $fallbackQuery = "
            SELECT 
                t.id,
                t.name as term_name,
                t.school_year_id,
                COALESCE(sy.school_year_label, 'Current Academic Year') as school_year_label,
                t.start_date,
                t.end_date,
                t.is_active as status,
                CONCAT(t.name, ' ', COALESCE(sy.school_year_label, 'Current Academic Year')) as display_name
            FROM terms t
            LEFT JOIN school_years sy ON t.school_year_id = sy.id
            ORDER BY 
                CASE t.name 
                    WHEN '1st Semester' THEN 1
                    WHEN '2nd Semester' THEN 2
                    WHEN 'Summer Semester' THEN 3
                    ELSE 4
                END
            LIMIT 10
        ";
        $fallbackStmt = $pdo->prepare($fallbackQuery);
        $fallbackStmt->execute();
        $dbTerms = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
        
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
            'has_academic_year' => !empty($currentAcademicYear),
            'year_start_exists' => $yearStartExists
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
