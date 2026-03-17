<?php
// update_program_colors_to_department.php
// This script updates all programs to use their department's color code instead of their own color code

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h1>Update Program Colors to Department Colors</h1>";

try {
    // Update all programs to use their department's color code
    $updateQuery = "
        UPDATE programs p 
        JOIN departments d ON p.department_id = d.id 
        SET p.color_code = d.color_code
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $result = $stmt->execute();
    
    if ($result) {
        $affectedRows = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Successfully updated $affectedRows programs to use their department's color code!</p>";
        
        // Show which programs were updated
        $showQuery = "
            SELECT p.program_code, p.program_name, p.color_code, d.department_name, d.color_code as dept_color_code
            FROM programs p 
            JOIN departments d ON p.department_id = d.id 
            ORDER BY d.department_name, p.program_code
        ";
        
        $showStmt = $pdo->prepare($showQuery);
        $showStmt->execute();
        $programs = $showStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Updated Programs:</h2>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Department</th><th>Program Code</th><th>Program Name</th><th>Color Code</th></tr>";
        
        foreach ($programs as $program) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($program['department_name']) . "</td>";
            echo "<td>" . htmlspecialchars($program['program_code']) . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td style='background-color: " . htmlspecialchars($program['color_code']) . "; color: white; text-align: center;'>" . htmlspecialchars($program['color_code']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to update programs!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p>This update ensures that all programs now use their department's color code instead of having their own individual color codes.</p>";
echo "<p>From now on, when creating or editing programs, the system will automatically use the department's color code.</p>";
?>
