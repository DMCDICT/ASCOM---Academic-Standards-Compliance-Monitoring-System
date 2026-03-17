<?php
// Script to fix book_references table structure
require_once dirname(__FILE__) . '/../super_admin-mis/includes/db_connection.php';

echo "<h2>🔧 Fixing Book References Table Structure</h2>";

try {
    // Check current table structure
    echo "<h3>📊 Current Table Structure:</h3>";
    $describeQuery = "DESCRIBE book_references";
    $result = $conn->query($describeQuery);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $existingColumns = [];
    while ($row = $result->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
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
    
    // Add missing columns
    $columnsToAdd = [
        'no_of_copies' => "INT(11) DEFAULT 1 COMMENT 'Number of copies available'",
        'book_title' => "VARCHAR(255) COMMENT 'Book title (alternative to title)'",
        'copyright' => "VARCHAR(10) COMMENT 'Copyright year (alternative to copyright_year)'",
        'authors' => "VARCHAR(255) COMMENT 'Book authors'"
    ];
    
    echo "<h3>🔨 Adding Missing Columns:</h3>";
    
    foreach ($columnsToAdd as $columnName => $columnDef) {
        if (!in_array($columnName, $existingColumns)) {
            $alterQuery = "ALTER TABLE book_references ADD COLUMN `$columnName` $columnDef";
            echo "<p>Adding column: <code>$columnName</code></p>";
            
            if ($conn->query($alterQuery)) {
                echo "<p style='color: green;'>✅ Successfully added column: $columnName</p>";
            } else {
                echo "<p style='color: red;'>❌ Error adding column $columnName: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Column $columnName already exists</p>";
        }
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
    
    echo "<h3>✅ Table Fix Complete!</h3>";
    echo "<p>The book_references table now has all the required columns for the Add Book functionality.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
