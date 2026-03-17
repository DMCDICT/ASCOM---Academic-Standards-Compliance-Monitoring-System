<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Simulate the add_term.php logic
$test_data = array (
  'title' => '1st Semester',
  'school_year_id' => '35',
  'start_date' => '2025-08-15',
  'end_date' => '2025-12-15',
);

// Validation
if (empty($test_data['title']) || empty($test_data['school_year_id']) || empty($test_data['start_date']) || empty($test_data['end_date'])) {
    echo '❌ Validation failed: Missing required fields<br>';
    exit;
}

// Date validation
if (strtotime($test_data['start_date']) > strtotime($test_data['end_date'])) {
    echo '❌ Validation failed: Start date after end date<br>';
    exit;
}

// Insert into database
$sql = 'INSERT INTO school_terms (title, school_year_id, start_date, end_date, status) VALUES (?, ?, ?, ?, "Inactive")';
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('siss', $test_data['title'], $test_data['school_year_id'], $test_data['start_date'], $test_data['end_date']);
    
    if ($stmt->execute()) {
        $new_term_id = $conn->insert_id;
        echo '✅ Term added successfully!<br>';
        echo '   - New Term ID: ' . $new_term_id . '<br>';
        echo '   - Title: ' . $test_data['title'] . '<br>';
        echo '   - School Year ID: ' . $test_data['school_year_id'] . '<br>';
        echo '   - Start Date: ' . $test_data['start_date'] . '<br>';
        echo '   - End Date: ' . $test_data['end_date'] . '<br>';
        
        // Clean up - delete the test term
        $delete_sql = 'DELETE FROM school_terms WHERE id = ?';
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $new_term_id);
        $delete_stmt->execute();
        echo '🧹 Test term cleaned up<br>';
        
    } else {
        echo '❌ Database error: ' . $stmt->error . '<br>';
    }
    $stmt->close();
} else {
    echo '❌ Prepare statement failed: ' . $conn->error . '<br>';
}
?>