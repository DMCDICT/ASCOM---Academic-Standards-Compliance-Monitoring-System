<?php
// Script to rename availability column to no_of_copies
require_once dirname(__FILE__) . '/super_admin-mis/includes/db_connection.php';

echo "<h2>🔧 Renaming availability column to no_of_copies</h2>";

try {
    // Check current table structure
    echo "<h3>📊 Current Table Structure:</h3>";
    $describeQuery = "DESCRIBE book_references";
    $result = $conn->query($describeQuery);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $hasAvailability = false;
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
        
        if ($row['Field'] === 'availability') {
            $hasAvailability = true;
        }
        if ($row['Field'] === 'no_of_copies') {
            $hasNoOfCopies = true;
        }
    }
    echo "</table>";
    
    // Rename column if it exists
    if ($hasAvailability && !$hasNoOfCopies) {
        echo "<h3>🔨 Renaming Column:</h3>";
        echo "<p>Found 'availability' column, renaming to 'no_of_copies'...</p>";
        
        $alterQuery = "ALTER TABLE book_references CHANGE COLUMN `availability` `no_of_copies` INT(11) DEFAULT 1 COMMENT ''";
        
        if ($conn->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully renamed 'availability' to 'no_of_copies'</p>";
        } else {
            echo "<p style='color: red;'>❌ Error renaming column: " . $conn->error . "</p>";
        }
    } elseif ($hasNoOfCopies) {
        echo "<p style='color: blue;'>ℹ️ Column 'no_of_copies' already exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Neither 'availability' nor 'no_of_copies' column found</p>";
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
    
    echo "<h3>✅ Column Rename Complete!</h3>";
    echo "<p>The book_references table now uses 'no_of_copies' instead of 'availability'.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
