<?php
// API endpoint to fetch academic terms for teachers
header('Content-Type: application/json');

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Check if school_terms table exists, if not try 'terms' table
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'school_terms'");
    $tableExists = $tableCheck->rowCount() > 0;
    
    if (!$tableExists) {
        // Try checking for 'terms' table
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'terms'");
        $tableExists = $tableCheck->rowCount() > 0;
        $tableName = 'terms';
        $titleColumn = 'name';
    } else {
        $tableName = 'school_terms';
        $titleColumn = 'title';
    }
    
    if (!$tableExists) {
        echo json_encode([
            'status' => 'success',
            'terms' => [],
            'current_term' => null,
            'message' => 'No terms table found'
        ]);
        exit;
    }
    
    // Fetch all terms with their school year information
    // Handle both table structures
    if ($tableName === 'school_terms') {
        $query = "
            SELECT 
                st.id,
                st.title as term_name,
                st.school_year_id,
                sy.school_year_label,
                st.start_date,
                st.end_date,
                st.status,
                CONCAT(st.title, ' ', sy.school_year_label) as display_name
            FROM school_terms st
            INNER JOIN school_years sy ON st.school_year_id = sy.id
            ORDER BY sy.start_date DESC, st.start_date DESC
        ";
    } else {
        // Handle 'terms' table structure
        $query = "
            SELECT 
                t.id,
                t.name as term_name,
                t.school_year_id,
                COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end)) as school_year_label,
                t.start_date,
                t.end_date,
                CASE WHEN t.is_active = 1 THEN 'Active' ELSE 'Inactive' END as status,
                CONCAT(t.name, ' ', COALESCE(sy.school_year_label, CONCAT('SY ', sy.year_start, '-', sy.year_end))) as display_name
            FROM terms t
            INNER JOIN school_years sy ON t.school_year_id = sy.id
            ORDER BY sy.start_date DESC, t.start_date DESC
        ";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find the current active term
    $currentTerm = null;
    foreach ($terms as $term) {
        if ($term['status'] === 'Active') {
            $currentTerm = $term;
            break;
        }
    }
    
    // If no active term, find the one with today's date in range
    if (!$currentTerm) {
        $today = date('Y-m-d');
        foreach ($terms as $term) {
            if (isset($term['start_date']) && isset($term['end_date'])) {
                if ($term['start_date'] <= $today && $term['end_date'] >= $today) {
                    $currentTerm = $term;
                    break;
                }
            }
        }
    }
    
    // If still no current term, use the most recent one
    if (!$currentTerm && count($terms) > 0) {
        $currentTerm = $terms[0];
    }
    
    echo json_encode([
        'status' => 'success',
        'terms' => $terms,
        'current_term' => $currentTerm
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_academic_terms.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch academic terms: ' . $e->getMessage()
    ]);
}

