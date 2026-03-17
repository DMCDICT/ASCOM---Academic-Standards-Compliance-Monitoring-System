<?php
// Test program modal directly
session_start();
require_once 'includes/db_connection.php';

echo "<h2>Program Modal Test</h2>";

// Test database connection
try {
    echo "<p>✅ Database connection: OK</p>";
    
    // Test programs table
    $programs = $pdo->query("SELECT * FROM programs")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Programs in database: " . count($programs) . "</p>";
    echo "<pre>" . print_r($programs, true) . "</pre>";
    
    // Test the exact query used in the modal
    $query = "SELECT p.id, p.program_code, p.program_name, p.color_code FROM programs p ORDER BY p.program_code ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $modalPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Modal query result: " . count($modalPrograms) . " programs</p>";
    echo "<pre>" . print_r($modalPrograms, true) . "</pre>";
    
    if (count($modalPrograms) > 0) {
        echo "<h3>Program Checkboxes (as they would appear in modal):</h3>";
        foreach ($modalPrograms as $program) {
            $programId = $program['id'];
            $programCode = htmlspecialchars($program['program_code']);
            $programName = htmlspecialchars($program['program_name']);
            echo "<label><input type='checkbox' name='programs[]' value='$programId' id='edit_modal_program_$programId'> $programCode - $programName</label><br>";
        }
    } else {
        echo "<p style='color: red;'>No programs found - this is why the modal fails</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Show fallback data
    echo "<h3>Fallback Data (what should be used):</h3>";
    $fallbackPrograms = [
        ['id' => '1', 'program_code' => 'BLIS', 'program_name' => 'Bachelor of Library and Information Science', 'color_code' => '#FF9800'],
        ['id' => '2', 'program_code' => 'BSCS', 'program_name' => 'Bachelor of Science in Computer Science', 'color_code' => '#1976d2'],
        ['id' => '3', 'program_code' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology', 'color_code' => '#4CAF50'],
        ['id' => '4', 'program_code' => 'BSCE', 'program_name' => 'Bachelor of Science in Civil Engineering', 'color_code' => '#9C27B0']
    ];
    
    foreach ($fallbackPrograms as $program) {
        $programId = $program['id'];
        $programCode = htmlspecialchars($program['program_code']);
        $programName = htmlspecialchars($program['program_name']);
        echo "<label><input type='checkbox' name='programs[]' value='$programId' id='edit_modal_program_$programId'> $programCode - $programName</label><br>";
    }
}
?>
