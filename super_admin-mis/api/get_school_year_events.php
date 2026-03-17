<?php
// Disable error output to prevent HTML from being sent before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header first
header('Content-Type: application/json');

// Enable error logging instead of display
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

require_once '../includes/db_connection.php';

function send_response($status, $data = null, $message = '') {
    $response = ['status' => $status];
    if ($data !== null) {
        $response['data'] = $data;
    }
    if ($message !== '') {
        $response['message'] = $message;
    }
    echo json_encode($response);
    exit;
}

try {
    // Check if school_years table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'school_years'");
    if ($table_check->num_rows === 0) {
        send_response('error', null, 'school_years table does not exist.');
    }

    // Get all school years with their start and end dates
    $sql = "SELECT id, year_start, year_end, start_date, end_date, status FROM school_years ORDER BY year_start DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        send_response('error', null, 'Database error: ' . $conn->error);
    }

    $events = [];
    error_log("Processing school year events...");
    while ($row = $result->fetch_assoc()) {
        error_log("Processing school year: " . print_r($row, true));
        
        // Determine if this school year is currently active based on actual dates
        $current_date = date('Y-m-d');
        $is_currently_active = ($current_date >= $row['start_date'] && $current_date <= $row['end_date']);
        
        // Add start date event
        $events[] = [
            'id' => 'sy_start_' . $row['id'],
            'title' => 'A.Y. ' . $row['year_start'] . '-' . $row['year_end'] . ' Starts',
            'date' => $row['start_date'],
            'type' => 'school_year_start',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
        
        // Add end date event
        $events[] = [
            'id' => 'sy_end_' . $row['id'],
            'title' => 'A.Y. ' . $row['year_start'] . '-' . $row['year_end'] . ' Ends',
            'date' => $row['end_date'],
            'type' => 'school_year_end',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
    }
    
    // Get all terms for all school years
    $terms_sql = "SELECT st.id, st.title, st.start_date, st.end_date, st.status, sy.school_year_label 
                  FROM school_terms st 
                  JOIN school_years sy ON st.school_year_id = sy.id 
                  ORDER BY st.start_date";
    $terms_result = $conn->query($terms_sql);
    
    if ($terms_result) {
        error_log("Processing term events...");
        while ($term = $terms_result->fetch_assoc()) {
            error_log("Processing term: " . print_r($term, true));
            
            // Determine if this term is currently active
            $current_date = date('Y-m-d');
            $term_is_active = ($current_date >= $term['start_date'] && $current_date <= $term['end_date']);
            
            // Add term start date event
            $events[] = [
                'id' => 'term_start_' . $term['id'],
                'title' => $term['title'] . ' Starts',
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
                'title' => $term['title'] . ' Ends',
                'date' => $term['end_date'],
                'type' => 'term_end',
                'term_id' => $term['id'],
                'school_year_label' => $term['school_year_label'],
                'is_active' => $term_is_active ? 1 : 0,
                'status' => $term['status']
            ];
        }
    }
    
    error_log("Total events created: " . count($events));
    error_log("Events data: " . print_r($events, true));

    send_response('success', $events);

} catch (Exception $e) {
    error_log("Unexpected error in get_school_year_events: " . $e->getMessage());
    send_response('error', null, 'An unexpected error occurred.');
}
?>
