<?php
echo "PHP is working!\n";

try {
    require_once 'includes/db_connection.php';
    echo "Database connection successful!\n";
    
    // Test a simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Users count: " . $row['count'] . "\n";
    } else {
        echo "Error querying users table\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed!\n";
?>
