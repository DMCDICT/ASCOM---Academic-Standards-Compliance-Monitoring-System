<?php
// test_program_creation_simple.php
// Simple test to check program creation API

session_start();
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h1>Test Program Creation API</h1>";

// Simulate POST data
$_POST['program_code'] = 'TEST123';
$_POST['program_name'] = 'Test Program 123';
$_POST['color_code'] = '#FF0000';

// Include the process file
ob_start();
include 'department-dean/process_add_program.php';
$output = ob_get_clean();

echo "<h3>API Response:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Check if programs table has the new entry
try {
    $query = "SELECT * FROM programs WHERE program_code = 'TEST123'";
    $result = $pdo->query($query);
    
    echo "<h3>Database Check:</h3>";
    if ($result->rowCount() > 0) {
        $program = $result->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Program found in database:</p>";
        echo "<pre>" . print_r($program, true) . "</pre>";
    } else {
        echo "<p>❌ Program not found in database</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking database: " . $e->getMessage() . "</p>";
}

echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
?>
