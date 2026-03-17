<?php
// check_database_structure.php
// Check current database structure

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Database Structure Check</h2>";

try {
    // Check if new columns exist
    $columns = ['online_status', 'last_login', 'last_logout', 'last_activity'];
    echo "<h3>Checking Required Columns:</h3>";
    
    foreach ($columns as $column) {
        $checkQuery = "SHOW COLUMNS FROM users LIKE '$column'";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            echo "<p style='color: green;'>✅ $column column exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $column column does NOT exist</p>";
        }
    }
    
    // Show current table structure
    echo "<h3>Current Table Structure:</h3>";
    $describeQuery = "DESCRIBE users";
    $describeResult = $conn->query($describeQuery);
    
    if ($describeResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $describeResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test a sample user
    echo "<h3>Sample User Data:</h3>";
    $sampleQuery = "SELECT employee_no, first_name, last_name, is_active, last_activity, online_status, last_login, last_logout FROM users LIMIT 1";
    $sampleResult = $conn->query($sampleQuery);
    
    if ($sampleResult && $sampleResult->num_rows > 0) {
        $user = $sampleResult->fetch_assoc();
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ No users found or query failed</p>";
    }
    
    echo "<br><p><strong>Next Steps:</strong></p>";
    echo "<p>If any columns are missing, run the database fix script:</p>";
    echo "<p><a href='fix_database.php'>Run Database Fix</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 