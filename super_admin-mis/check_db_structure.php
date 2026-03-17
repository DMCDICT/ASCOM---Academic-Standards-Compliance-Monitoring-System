<?php
require_once 'includes/db_connection.php';

echo "=== Checking Users Table Structure ===\n";

$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "Users table columns:\n";
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n=== Checking if employee_no column exists ===\n";
$check_employee_no = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_no'");
echo "employee_no column exists: " . ($check_employee_no->num_rows > 0 ? "YES" : "NO") . "\n";

echo "\n=== Checking if institutional_email column exists ===\n";
$check_institutional_email = $conn->query("SHOW COLUMNS FROM users LIKE 'institutional_email'");
echo "institutional_email column exists: " . ($check_institutional_email->num_rows > 0 ? "YES" : "NO") . "\n";

echo "\n=== Sample data in users table ===\n";
$sample_result = $conn->query("SELECT * FROM users LIMIT 3");
if ($sample_result) {
    while($row = $sample_result->fetch_assoc()) {
        echo "User: " . json_encode($row) . "\n";
    }
} else {
    echo "Error getting sample data: " . $conn->error . "\n";
}

$conn->close();
?> 