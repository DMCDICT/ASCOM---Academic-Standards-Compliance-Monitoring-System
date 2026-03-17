<?php
echo "Testing database connection...\n";

try {
    require_once 'includes/db_connection.php';
    echo "✅ Database connection successful!\n";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Users table accessible. Total users: " . $row['count'] . "\n";
    } else {
        echo "❌ Error accessing users table\n";
    }
    
    // Check if title column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'title'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Title column exists in users table\n";
    } else {
        echo "❌ Title column does not exist in users table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?> 