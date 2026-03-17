<?php
// update_program_colors_web.php
// This script updates all programs to use their department's color code instead of their own color code
// Can be run through web browser

require_once 'department-dean/includes/db_connection.php';

echo "<h1>Update Program Colors to Department Colors</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
    .color-sample { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
</style>";

try {
    // First, let's see what we have before the update
    echo "<h2>Before Update:</h2>";
    $beforeQuery = "
        SELECT p.program_code, p.program_name, p.color_code as program_color, d.department_name, d.color_code as dept_color_code
        FROM programs p 
        JOIN departments d ON p.department_id = d.id 
        ORDER BY d.department_name, p.program_code
    ";
    
    $beforeStmt = $pdo->prepare($beforeQuery);
    $beforeStmt->execute();
    $beforePrograms = $beforeStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Department</th><th>Program Code</th><th>Program Name</th><th>Program Color</th><th>Department Color</th><th>Match?</th></tr>";
    
    foreach ($beforePrograms as $program) {
        $match = $program['program_color'] === $program['dept_color_code'] ? '✅ Yes' : '❌ No';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($program['department_name']) . "</td>";
        echo "<td>" . htmlspecialchars($program['program_code']) . "</td>";
        echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
        echo "<td><span class='color-sample' style='background-color: " . htmlspecialchars($program['program_color']) . ";'>" . htmlspecialchars($program['program_color']) . "</span></td>";
        echo "<td><span class='color-sample' style='background-color: " . htmlspecialchars($program['dept_color_code']) . ";'>" . htmlspecialchars($program['dept_color_code']) . "</span></td>";
        echo "<td>" . $match . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Now perform the update
    echo "<h2>Performing Update...</h2>";
    $updateQuery = "
        UPDATE programs p 
        JOIN departments d ON p.department_id = d.id 
        SET p.color_code = d.color_code
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $result = $stmt->execute();
    
    if ($result) {
        $affectedRows = $stmt->rowCount();
        echo "<p class='success'>✅ Successfully updated $affectedRows programs to use their department's color code!</p>";
        
        // Show the results after update
        echo "<h2>After Update:</h2>";
        $afterStmt = $pdo->prepare($beforeQuery);
        $afterStmt->execute();
        $afterPrograms = $afterStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Department</th><th>Program Code</th><th>Program Name</th><th>Program Color</th><th>Department Color</th><th>Match?</th></tr>";
        
        foreach ($afterPrograms as $program) {
            $match = $program['program_color'] === $program['dept_color_code'] ? '✅ Yes' : '❌ No';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($program['department_name']) . "</td>";
            echo "<td>" . htmlspecialchars($program['program_code']) . "</td>";
            echo "<td>" . htmlspecialchars($program['program_name']) . "</td>";
            echo "<td><span class='color-sample' style='background-color: " . htmlspecialchars($program['program_color']) . ";'>" . htmlspecialchars($program['program_color']) . "</span></td>";
            echo "<td><span class='color-sample' style='background-color: " . htmlspecialchars($program['dept_color_code']) . ";'>" . htmlspecialchars($program['dept_color_code']) . "</span></td>";
            echo "<td>" . $match . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>❌ Failed to update programs!</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p>This update ensures that all programs now use their department's color code instead of having their own individual color codes.</p>";
echo "<p>From now on, when creating or editing programs, the system will automatically use the department's color code.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Test creating a new program - it should not show a color field</li>";
echo "<li>Test editing an existing program - it should not show a color field</li>";
echo "<li>Verify that programs display with their department's color</li>";
echo "</ul>";
?>
