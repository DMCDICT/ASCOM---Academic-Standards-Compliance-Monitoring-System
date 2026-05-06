<?php
// get_school_year_events.php
// API endpoint to fetch school year and term events for calendar

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    $events = [];
    
    // Check if year_start column exists
    $columnCheckStmt = $pdo->prepare("SHOW COLUMNS FROM school_years LIKE 'year_start'");
    $columnCheckStmt->execute();
    $yearStartExists = $columnCheckStmt->rowCount() > 0;
    
    // Get all school years
    if ($yearStartExists) {
        $schoolYearsQuery = "SELECT id, year_start, year_end, start_date, end_date, status FROM school_years ORDER BY year_start DESC";
    } else {
        $schoolYearsQuery = "SELECT id, start_date, end_date, status FROM school_years ORDER BY start_date DESC";
    }
    
    $schoolYearsStmt = $pdo->prepare($schoolYearsQuery);
    $schoolYearsStmt->execute();
    $schoolYears = $schoolYearsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($schoolYears as $row) {
        // Determine if this school year is currently active based on actual dates
        $current_date = date('Y-m-d');
        $is_currently_active = ($current_date >= $row['start_date'] && $current_date <= $row['end_date']);
        
        $yearLabel = $yearStartExists ? 
            'A.Y. ' . $row['year_start'] . '-' . $row['year_end'] : 
            'Academic Year';
        
        // Add start date event
        $events[] = [
            'id' => 'sy_start_' . $row['id'],
            'title' => $yearLabel . ' Starts',
            'date' => $row['start_date'],
            'type' => 'school_year_start',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
        
        // Add end date event
        $events[] = [
            'id' => 'sy_end_' . $row['id'],
            'title' => $yearLabel . ' Ends',
            'date' => $row['end_date'],
            'type' => 'school_year_end',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
    }
    
    // Get all terms
    $termsQuery = "
        SELECT t.id, t.name, t.start_date, t.end_date, t.status, 
               COALESCE(sy.school_year_label, 'Current Academic Year') as school_year_label
        FROM terms t
        LEFT JOIN school_years sy ON t.school_year_id = sy.id
        ORDER BY t.start_date
    ";
    $termsStmt = $pdo->prepare($termsQuery);
    $termsStmt->execute();
    $terms = $termsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($terms as $term) {
        // Determine if this term is currently active
        $current_date = date('Y-m-d');
        $term_is_active = ($current_date >= $term['start_date'] && $current_date <= $term['end_date']);
        
        // Add term start date event
        $events[] = [
            'id' => 'term_start_' . $term['id'],
            'title' => $term['name'] . ' Starts',
            'date' => $term['start_date'],
            'type' => 'term_start',
            'term_id' => $term['id'],
            'school_year_label' => $term['school_year_label'],
            'is_active' => $term_is_active ? 1 : 0,
            'status' => $term['status']
        ];
        
        // Add term end date event
        $events[] = [
            'id' => 'term_end_' . $term['id'],
            'title' => $term['name'] . ' Ends',
            'date' => $term['end_date'],
            'type' => 'term_end',
            'term_id' => $term['id'],
            'school_year_label' => $term['school_year_label'],
            'is_active' => $term_is_active ? 1 : 0,
            'status' => $term['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $events
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>