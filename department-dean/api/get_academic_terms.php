<?php
// API endpoint to fetch academic terms
header('Content-Type: application/json');

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Fetch all terms with their school year information
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
        ORDER BY sy.start_date DESC, st.start_date ASC
    ";
    
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
            if ($term['start_date'] <= $today && $term['end_date'] >= $today) {
                $currentTerm = $term;
                break;
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'terms' => $terms,
        'current_term' => $currentTerm
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch academic terms: ' . $e->getMessage()
    ]);
}

