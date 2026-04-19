<?php
header('Content-Type: application/json');
error_reporting(0);

require_once '../includes/db_connection.php';

try {
    // Check if school_terms table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'school_terms'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        echo json_encode(['status' => 'success', 'terms' => []]);
        exit;
    }
    
    // Get column names
    $cols = [];
    $colResult = $conn->query("DESCRIBE school_terms");
    if ($colResult) {
        while ($row = $colResult->fetch_assoc()) {
            $cols[] = $row['Field'];
        }
    }
    
    $hasSchoolYearId = in_array('school_year_id', $cols);
    $hasStatus = in_array('status', $cols);
    
    if ($hasSchoolYearId) {
        $sql = "SELECT st.*, sy.school_year_label 
               FROM school_terms st 
               LEFT JOIN school_years sy ON st.school_year_id = sy.id 
               ORDER BY st.start_date DESC";
    } else {
        $sql = "SELECT * FROM school_terms ORDER BY start_date DESC";
    }
    
    $result = $conn->query($sql);
    $terms = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $terms[] = $row;
        }
    }
    
    echo json_encode(['status' => 'success', 'terms' => $terms]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();