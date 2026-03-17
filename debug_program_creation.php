<?php
// Debug script for program creation
session_start();
require_once 'department-dean/includes/db_connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Program Creation</h2>";

// Check session
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check POST data
echo "<h3>POST Data:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Check database connection
echo "<h3>Database Connection:</h3>";
if (isset($pdo)) {
    echo "✅ PDO connection exists<br>";
    
    // Check if programs table exists
    try {
        $query = "DESCRIBE programs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Programs table structure:</h4>";
        echo "<pre>";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        echo "</pre>";
        
        // Check if major column exists
        $hasMajor = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'major') {
                $hasMajor = true;
                break;
            }
        }
        
        if (!$hasMajor) {
            echo "<p style='color: red;'>❌ Major column is missing!</p>";
            echo "<p>Adding major column...</p>";
            $alterQuery = "ALTER TABLE programs ADD COLUMN major VARCHAR(100) NULL AFTER program_name";
            $pdo->exec($alterQuery);
            echo "<p style='color: green;'>✅ Major column added successfully!</p>";
        } else {
            echo "<p style='color: green;'>✅ Major column exists!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error checking table: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "❌ PDO connection not found<br>";
}

// Test the actual program creation logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    echo "<h3>Testing Program Creation:</h3>";
    
    try {
        // Get the dean's department ID
        $deanDepartmentId = $_SESSION['dean_department_id'] ?? null;
        
        if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_id'])) {
            $deanDepartmentId = $_SESSION['selected_role']['department_id'];
        }
        
        if (!$deanDepartmentId && isset($_SESSION['user_id'])) {
            $deptQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
            $deptStmt = $pdo->prepare($deptQuery);
            $deptStmt->execute([$_SESSION['user_id']]);
            $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
            if ($deptResult) {
                $deanDepartmentId = $deptResult['id'];
            }
        }
        
        echo "<p>Dean Department ID: " . ($deanDepartmentId ?? 'NOT FOUND') . "</p>";
        
        if ($deanDepartmentId) {
            // Validate input
            $programCode = trim($_POST['program_code'] ?? '');
            $programName = trim($_POST['program_name'] ?? '');
            $colorCode = trim($_POST['color_code'] ?? '');
            $major = trim($_POST['major'] ?? '');
            
            echo "<p>Program Code: '$programCode'</p>";
            echo "<p>Program Name: '$programName'</p>";
            echo "<p>Color Code: '$colorCode'</p>";
            echo "<p>Major: '$major'</p>";
            
            // Test insert
            $insertQuery = "INSERT INTO programs (program_code, program_name, major, color_code, department_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $insertStmt = $pdo->prepare($insertQuery);
            
            if ($insertStmt->execute([$programCode, $programName, $major, $colorCode, $deanDepartmentId])) {
                echo "<p style='color: green;'>✅ Program created successfully!</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to create program</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Department ID not found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST" style="margin-top: 20px; padding: 20px; border: 1px solid #ccc;">
    <h3>Test Program Creation:</h3>
    <p>
        <label>Program Code:</label><br>
        <input type="text" name="program_code" value="TEST" required>
    </p>
    <p>
        <label>Program Name:</label><br>
        <input type="text" name="program_name" value="Test Program" required>
    </p>
    <p>
        <label>Color Code:</label><br>
        <input type="text" name="color_code" value="#FF0000" required>
    </p>
    <p>
        <label>Major (Optional):</label><br>
        <input type="text" name="major" value="Test Major">
    </p>
    <p>
        <button type="submit">Test Create Program</button>
    </p>
</form>
