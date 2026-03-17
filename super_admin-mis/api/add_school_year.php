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

function send_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Check if school_years table exists and get its structure
try {
    $table_check = $conn->query("SHOW TABLES LIKE 'school_years'");
    if (!$table_check) {
        send_response('error', 'Database connection error.');
    }
    
    error_log("Table check result: " . $table_check->num_rows);
    if ($table_check->num_rows === 0) {
        error_log("school_years table does not exist");
        send_response('error', 'school_years table does not exist in the database.');
    } else {
        error_log("school_years table exists");
        
        // Check table structure
        $structure = $conn->query("DESCRIBE school_years");
        if ($structure) {
            error_log("Table structure:");
            while ($row = $structure->fetch_assoc()) {
                error_log("Field: " . $row['Field'] . " Type: " . $row['Type']);
            }
        }
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    send_response('error', 'Database connection error.');
}

try {
    $jsonData = file_get_contents('php://input');
    error_log("Received JSON data: " . $jsonData);
    $data = json_decode($jsonData);
    error_log("Decoded data: " . print_r($data, true));

    if (!$data || !isset($data->school_year_label) || !isset($data->start_date) || !isset($data->end_date) || !isset($data->status)) {
        send_response('error', 'Invalid input data. Please fill out all fields.');
    }

$label = trim($data->school_year_label);
$start_date = trim($data->start_date);
$end_date = trim($data->end_date);
$status = trim($data->status);

// Convert status to is_active (1 for Active, 0 for Inactive)
$is_active = ($status === 'Active') ? 1 : 0;

// Extract year from start_date for year_start field
$year_start = date('Y', strtotime($start_date));
$year_end = date('Y', strtotime($end_date));

if (strtotime($start_date) > strtotime($end_date)) {
    send_response('error', 'Start date cannot be after the end date.');
}

// Check for overlapping date ranges with existing school years
$overlap_check_sql = "SELECT school_year_label, start_date, end_date FROM school_years WHERE 
    (start_date <= ? AND end_date >= ?) OR 
    (start_date <= ? AND end_date >= ?) OR 
    (start_date >= ? AND end_date <= ?)";
$overlap_stmt = $conn->prepare($overlap_check_sql);
if ($overlap_stmt) {
    $overlap_stmt->bind_param('ssssss', $start_date, $start_date, $end_date, $end_date, $start_date, $end_date);
    $overlap_stmt->execute();
    $overlap_result = $overlap_stmt->get_result();
    
    if ($overlap_result && $overlap_result->num_rows > 0) {
        $conflicting_years = [];
        while ($row = $overlap_result->fetch_assoc()) {
            $conflicting_years[] = $row['school_year_label'] . ' (' . $row['start_date'] . ' - ' . $row['end_date'] . ')';
        }
        $overlap_stmt->close();
        send_response('error', 'Date range conflicts with existing school year(s): ' . implode(', ', $conflicting_years) . '. Please choose a different date range.');
    }
    $overlap_stmt->close();
}

// Check if start_date and end_date fields exist in the table
$field_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'start_date'");
if (!$field_check) {
    error_log("Field check error: " . $conn->error);
    send_response('error', 'Database error: Could not check table structure.');
}
$has_start_date = $field_check->num_rows > 0;

$field_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'end_date'");
if (!$field_check) {
    error_log("Field check error: " . $conn->error);
    send_response('error', 'Database error: Could not check table structure.');
}
$has_end_date = $field_check->num_rows > 0;

// Check if school_year_label and status columns exist (new structure)
$field_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'school_year_label'");
$has_school_year_label = $field_check && $field_check->num_rows > 0;

$field_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'status'");
$has_status = $field_check && $field_check->num_rows > 0;

// Handle setting other school years to inactive when setting one as active
if ($status === 'Active') {
    if ($has_status) {
        // New structure - update status column
        $update_result = $conn->query("UPDATE school_years SET status = 'Inactive'");
    } else {
        // Old structure - update is_active column
        $update_result = $conn->query("UPDATE school_years SET is_active = 0");
    }
    
    if (!$update_result) {
        error_log("Update error: " . $conn->error);
        send_response('error', 'Database error: Could not update existing school years.');
    }
}

if ($has_school_year_label && $has_status) {
    // Use new structure with school_year_label and status
    error_log("Using new structure - school_year_label: $label, status: $status, start_date: $start_date, end_date: $end_date");
    
    // Convert status to proper format
    $status_value = ($status === 'Active') ? 'Active' : 'Inactive';
    
    // Check if old columns (year_start, year_end) still exist and need to be populated
    $old_columns_check = $conn->query("SHOW COLUMNS FROM school_years LIKE 'year_start'");
    $has_old_columns = $old_columns_check && $old_columns_check->num_rows > 0;
    
    if ($has_old_columns) {
        // Include old columns to satisfy the unique constraint
        $sql = "INSERT INTO school_years (school_year_label, year_start, year_end, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) { 
            error_log("Prepare error: " . $conn->error);
            send_response('error', 'Server error: Could not prepare the statement. ' . $conn->error); 
        }
        $bind_result = $stmt->bind_param('siisss', $label, $year_start, $year_end, $start_date, $end_date, $status_value);
    } else {
        // Only new columns exist
        $sql = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) { 
            error_log("Prepare error: " . $conn->error);
            send_response('error', 'Server error: Could not prepare the statement. ' . $conn->error); 
        }
        $bind_result = $stmt->bind_param('ssss', $label, $start_date, $end_date, $status_value);
    }
    
    if (!$bind_result) {
        error_log("Bind error: " . $stmt->error);
        send_response('error', 'Server error: Could not bind parameters.');
    }
} elseif ($has_start_date && $has_end_date) {
    // Use old structure with year_start, year_end, start_date, end_date, is_active
    error_log("Using old structure with date fields - year_start: $year_start, year_end: $year_end, start_date: $start_date, end_date: $end_date");
    $sql = "INSERT INTO school_years (year_start, year_end, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { 
        error_log("Prepare error: " . $conn->error);
        send_response('error', 'Server error: Could not prepare the statement. ' . $conn->error); 
    }
    $bind_result = $stmt->bind_param('iissi', $year_start, $year_end, $start_date, $end_date, $is_active);
    if (!$bind_result) {
        error_log("Bind error: " . $stmt->error);
        send_response('error', 'Server error: Could not bind parameters.');
    }
} else {
    // Use only year fields if date fields don't exist
    error_log("Using year fields only - year_start: $year_start, year_end: $year_end");
    $sql = "INSERT INTO school_years (year_start, year_end, is_active) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { 
        error_log("Prepare error: " . $conn->error);
        send_response('error', 'Server error: Could not prepare the statement. ' . $conn->error); 
    }
    $bind_result = $stmt->bind_param('iii', $year_start, $year_end, $is_active);
    if (!$bind_result) {
        error_log("Bind error: " . $stmt->error);
        send_response('error', 'Server error: Could not bind parameters.');
    }
}

$execute_result = $stmt->execute();
if ($execute_result) {
    error_log("School year inserted successfully");
    send_response('success', 'New school year has been added successfully!');
} else {
    error_log("Execute error: " . $stmt->error . ", MySQL errno: " . $conn->errno);
    // Check for duplicate entry error (MySQL error 1062)
    if ($conn->errno == 1062 || strpos($stmt->error, 'Duplicate entry') !== false) {
        send_response('error', 'This school year already exists.');
    } else {
        send_response('error', 'Database error: Could not add the school year. ' . $stmt->error);
    }
}
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    // Check if this is a duplicate entry error
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        send_response('error', 'This school year already exists.');
    } else {
        send_response('error', 'An unexpected error occurred. Please try again.');
    }
}
?>