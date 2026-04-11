<?php
// Suppress any warnings or notices that might output HTML
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../includes/db_connection.php';

function send_response($status, $message) {
    // Ensure no output has been sent before
    if (headers_sent()) {
    }
    
    $response = ['status' => $status, 'message' => $message];
    echo json_encode($response);
    exit;
}

// Check if school_terms table exists and has correct structure
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'school_terms'");
    if ($table_check && $table_check->num_rows === 0) {
        // Create school_terms table if it doesn't exist
        $create_table = "CREATE TABLE IF NOT EXISTS school_terms (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(100) NOT NULL,
            school_year_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($create_table)) {
            send_response('error', 'Failed to create school_terms table: ' . $conn->error);
        }
    }
} catch (Exception $e) {
    send_response('error', 'Database error: ' . $e->getMessage());
}

$jsonData = file_get_contents('php://input');

$data = json_decode($jsonData);
if (json_last_error() !== JSON_ERROR_NONE) {
    send_response('error', 'Invalid JSON data received');
}

// Server-Side Validation
if (!$data || !isset($data->title) || !isset($data->school_year_id) || !isset($data->start_date) || !isset($data->end_date)) {
    send_response('error', 'Invalid input data. Please fill out all fields.');
}

// Sanitize the input
$title = trim($data->title);
$school_year_id = filter_var($data->school_year_id, FILTER_VALIDATE_INT);
$start_date = trim($data->start_date);
$end_date = trim($data->end_date);

if ($school_year_id === false) {
    send_response('error', 'Invalid School Year selected.');
}
if (strtotime($start_date) > strtotime($end_date)) {
    send_response('error', 'Start date cannot be after the end date.');
}

// Check for overlapping date ranges with existing terms in the same school year
$overlap_check_sql = "SELECT st.title, st.start_date, st.end_date, sy.school_year_label 
                      FROM school_terms st 
                      JOIN school_years sy ON st.school_year_id = sy.id 
                      WHERE st.school_year_id = ? AND 
                      ((st.start_date <= ? AND st.end_date >= ?) OR 
                       (st.start_date <= ? AND st.end_date >= ?) OR 
                       (st.start_date >= ? AND st.end_date <= ?))";
$overlap_stmt = $conn->prepare($overlap_check_sql);
if ($overlap_stmt) {
    $overlap_stmt->bind_param('issssss', $school_year_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date);
    $overlap_stmt->execute();
    $overlap_result = $overlap_stmt->get_result();
    
    if ($overlap_result && $overlap_result->num_rows > 0) {
        $conflicting_terms = [];
        while ($row = $overlap_result->fetch_assoc()) {
            $conflicting_terms[] = $row['title'] . ' (' . $row['start_date'] . ' - ' . $row['end_date'] . ')';
        }
        $overlap_stmt->close();
        send_response('error', 'Date range conflicts with existing term(s): ' . implode(', ', $conflicting_terms) . '. Please choose a different date range.');
    }
    $overlap_stmt->close();
}

try {
    // Check if status column exists
    $status_exists = $conn->query("SHOW COLUMNS FROM school_terms LIKE 'status'");
    $has_status = $status_exists && $status_exists->num_rows > 0;
    
    // Determine if the term should be active based on current date
    $current_date = date('Y-m-d');
    $term_is_active = ($current_date >= $start_date && $current_date <= $end_date) ? 'Active' : 'Inactive';
    
    // Prepare the SQL statement based on available columns
    if ($has_status) {
        $sql = "INSERT INTO school_terms (title, school_year_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)";
    } else {
        $sql = "INSERT INTO school_terms (title, school_year_id, start_date, end_date) VALUES (?, ?, ?, ?)";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        send_response('error', 'Server error: Could not prepare the statement. ' . $conn->error);
    }

    // Bind parameters based on available columns
    if ($has_status) {
        $bind_result = $stmt->bind_param('sisss', $title, $school_year_id, $start_date, $end_date, $term_is_active);
    } else {
        $bind_result = $stmt->bind_param('siss', $title, $school_year_id, $start_date, $end_date);
    }
    
    if (!$bind_result) {
        send_response('error', 'Server error: Could not bind parameters. ' . $stmt->error);
    }

    if ($stmt->execute()) {
        $new_term_id = $conn->insert_id;
        
        $status_message = $has_status ? " (Status: " . $term_is_active . ")" : "";
        send_response('success', 'New term has been added successfully!' . $status_message);
    } else {
        if ($conn->errno == 1062) {
            send_response('error', 'This term already exists for the selected academic year.');
        } else {
            send_response('error', 'Database error: Could not add the term. ' . $stmt->error);
        }
    }

    $stmt->close();
} catch (Exception $e) {
    send_response('error', 'Server error: ' . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>