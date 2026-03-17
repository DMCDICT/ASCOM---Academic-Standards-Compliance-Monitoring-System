<?php
// Test script to check programs table structure
require_once 'department-dean/includes/db_connection.php';

try {
    // Check if programs table exists and get its structure
    $query = "DESCRIBE programs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Programs table structure:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Check if major column exists
    $hasMajor = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'major') {
            $hasMajor = true;
            break;
        }
    }
    
    if (!$hasMajor) {
        echo "\n❌ Major column is missing! Adding it...\n";
        $alterQuery = "ALTER TABLE programs ADD COLUMN major VARCHAR(100) NULL AFTER program_name";
        $pdo->exec($alterQuery);
        echo "✅ Major column added successfully!\n";
    } else {
        echo "\n✅ Major column exists!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
