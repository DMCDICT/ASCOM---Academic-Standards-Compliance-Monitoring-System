<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Cache-Control: no-cache');

require_once __DIR__ . '/../includes/db_connection.php';

try {
    if (!isset($conn) || !$conn) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }
    
    $events = [];
    $current_date = date('Y-m-d');
    
    // Get school years
    $sy_result = $conn->query("SELECT id, school_year_label, start_date, end_date, status FROM school_years ORDER BY start_date DESC");
    if ($sy_result) {
        while ($sy = $sy_result->fetch_assoc()) {
            $events[] = [
                'id' => 'sy_start_' . $sy['id'],
                'title' => $sy['school_year_label'] . ' Starts',
                'date' => $sy['start_date'],
                'type' => 'school_year',
                'school_year_id' => (int)$sy['id'],
                'school_year_label' => $sy['school_year_label'],
                'status' => $sy['status']
            ];
            $events[] = [
                'id' => 'sy_end_' . $sy['id'],
                'title' => $sy['school_year_label'] . ' Ends',
                'date' => $sy['end_date'],
                'type' => 'school_year',
                'school_year_id' => (int)$sy['id'],
                'school_year_label' => $sy['school_year_label'],
                'status' => $sy['status']
            ];
        }
    }
    
    // Get terms
    $term_result = $conn->query("SELECT st.id, st.title, st.start_date, st.end_date, st.status, sy.school_year_label 
                FROM school_terms st 
                LEFT JOIN school_years sy ON st.school_year_id = sy.id 
                ORDER BY st.start_date DESC");
    if ($term_result) {
        while ($term = $term_result->fetch_assoc()) {
            $events[] = [
                'id' => 'term_start_' . $term['id'],
                'title' => $term['title'] . ' Starts',
                'date' => $term['start_date'],
                'type' => 'term_start',
                'term_id' => (int)$term['id'],
                'school_year_label' => $term['school_year_label'] ?? 'Unknown',
                'status' => $term['status']
            ];
            $events[] = [
                'id' => 'term_end_' . $term['id'],
                'title' => $term['title'] . ' Ends',
                'date' => $term['end_date'],
                'type' => 'term_end',
                'term_id' => (int)$term['id'],
                'school_year_label' => $term['school_year_label'] ?? 'Unknown',
                'status' => $term['status']
            ];
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $events]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}

$conn->close();