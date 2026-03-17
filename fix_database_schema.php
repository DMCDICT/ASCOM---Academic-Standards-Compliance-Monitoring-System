<?php
// Web-accessible script to fix database schema
header('Content-Type: text/plain');

try {
    require_once 'department-dean/includes/db_connection.php';
    
    echo "=== Database Schema Fix ===\n\n";
    
    // Check if major column exists
    $checkQuery = "SHOW COLUMNS FROM programs LIKE 'major'";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "❌ 'major' column is missing from programs table\n";
        echo "Adding 'major' column to programs table...\n";
        
        // Add the major column
        $alterQuery = "ALTER TABLE programs ADD COLUMN major VARCHAR(100) NULL AFTER program_name";
        $pdo->exec($alterQuery);
        
        echo "✅ 'major' column added successfully!\n\n";
    } else {
        echo "✅ 'major' column already exists!\n\n";
    }
    
    // Show current table structure
    echo "Current programs table structure:\n";
    echo "================================\n";
    $describeQuery = "DESCRIBE programs";
    $stmt = $pdo->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['Default'] ? "DEFAULT '{$column['Default']}'" : '';
        echo "- {$column['Field']} ({$column['Type']}) {$null} {$default}\n";
    }
    
    echo "\n=== Test Program Creation ===\n";
    
    // Test if we can now insert a program with major field
    try {
        $testQuery = "INSERT INTO programs (program_code, program_name, major, color_code, department_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $testStmt = $pdo->prepare($testQuery);
        
        // Get a test department ID
        $deptQuery = "SELECT id FROM departments LIMIT 1";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute();
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($deptResult) {
            $testStmt->execute(['TEST', 'Test Program', 'Test Major', '#FF0000', $deptResult['id']]);
            echo "✅ Test program creation successful!\n";
            
            // Clean up test data
            $deleteQuery = "DELETE FROM programs WHERE program_code = 'TEST'";
            $pdo->exec($deleteQuery);
            echo "✅ Test data cleaned up\n";
        } else {
            echo "❌ No departments found - cannot test program creation\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Test program creation failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Database Schema Fix Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
