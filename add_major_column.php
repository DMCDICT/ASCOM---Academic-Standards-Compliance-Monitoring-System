<?php
// Script to add the missing 'major' column to the programs table
require_once 'department-dean/includes/db_connection.php';

try {
    // Check if major column exists
    $checkQuery = "SHOW COLUMNS FROM programs LIKE 'major'";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding 'major' column to programs table...\n";
        
        // Add the major column
        $alterQuery = "ALTER TABLE programs ADD COLUMN major VARCHAR(100) NULL AFTER program_name";
        $pdo->exec($alterQuery);
        
        echo "✅ 'major' column added successfully!\n";
    } else {
        echo "✅ 'major' column already exists!\n";
    }
    
    // Show current table structure
    echo "\nCurrent programs table structure:\n";
    $describeQuery = "DESCRIBE programs";
    $stmt = $pdo->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
