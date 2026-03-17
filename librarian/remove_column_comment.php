<?php
// Script to remove comment from no_of_copies column
require_once dirname(__FILE__) . '/super_admin-mis/includes/db_connection.php';

echo "<h2>🔧 Removing comment from no_of_copies column</h2>";

try {
    // Check current table structure
    echo "<h3>📊 Current Table Structure:</h3>";
    $describeQuery = "DESCRIBE book_references";
    $result = $conn->query($describeQuery);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $hasNoOfCopies = false;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'no_of_copies') {
            $hasNoOfCopies = true;
        }
    }
    echo "</table>";
    
    // Modify column to remove comment
    if ($hasNoOfCopies) {
        echo "<h3>🔨 Removing Comment:</h3>";
        echo "<p>Found 'no_of_copies' column, removing comment...</p>";
        
        $alterQuery = "ALTER TABLE book_references MODIFY COLUMN `no_of_copies` INT(11) DEFAULT 1";
        
        if ($conn->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully removed comment from 'no_of_copies' column</p>";
        } else {
            echo "<p style='color: red;'>❌ Error removing comment: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Column 'no_of_copies' not found</p>";
    }
    
    // Show updated table structure
    echo "<h3>📊 Updated Table Structure:</h3>";
    $describeQuery = "DESCRIBE book_references";
    $result = $conn->query($describeQuery);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Comment Removal Complete!</h3>";
    echo "<p>The 'no_of_copies' column no longer has a description comment.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
