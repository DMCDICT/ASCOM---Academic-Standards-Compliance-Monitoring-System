<?php
echo "Starting title column fix...\n";

try {
    require_once 'includes/db_connection.php';
    echo "✅ Database connection successful\n";
    
    // Check if title column exists
    $checkQuery = "SHOW COLUMNS FROM users LIKE 'title'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows === 0) {
        echo "❌ Title column does not exist. Adding it...\n";
        
        $addQuery = "ALTER TABLE users ADD COLUMN title VARCHAR(50) DEFAULT NULL AFTER last_name";
        if ($conn->query($addQuery)) {
            echo "✅ Title column added successfully!\n";
        } else {
            echo "❌ Error adding title column: " . $conn->error . "\n";
        }
    } else {
        echo "✅ Title column already exists\n";
    }
    
    // Show the table structure
    echo "\nCurrent users table structure:\n";
    $structureQuery = "DESCRIBE users";
    $structureResult = $conn->query($structureQuery);
    
    while ($row = $structureResult->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>
