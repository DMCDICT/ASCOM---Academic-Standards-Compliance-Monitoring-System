<?php
// Check book_references table structure
require_once dirname(__FILE__) . '/super_admin-mis/includes/db_connection.php';

echo "<h2>🔍 Checking Book References Table Structure</h2>";

try {
    // Check if table exists
    $checkTable = "SHOW TABLES LIKE 'book_references'";
    $result = $conn->query($checkTable);
    
    if ($result->num_rows == 0) {
        echo "<p style='color: red;'>❌ Table 'book_references' does not exist!</p>";
        echo "<p>You need to create the table first.</p>";
    } else {
        echo "<p style='color: green;'>✅ Table 'book_references' exists!</p>";
        
        // Show current structure
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
        
        // Check for missing columns
        $requiredColumns = ['no_of_copies', 'book_title', 'copyright', 'authors'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $existingColumns)) {
                $missingColumns[] = $column;
            }
        }
        
        if (empty($missingColumns)) {
            echo "<p style='color: green;'>✅ All required columns are present!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Missing columns: " . implode(', ', $missingColumns) . "</p>";
            echo "<p>You need to add these columns to the table.</p>";
        }
        
        // Show record count
        $countQuery = "SELECT COUNT(*) as total FROM book_references";
        $countResult = $conn->query($countQuery);
        $count = $countResult->fetch_assoc();
        echo "<p><strong>Total records:</strong> " . $count['total'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
