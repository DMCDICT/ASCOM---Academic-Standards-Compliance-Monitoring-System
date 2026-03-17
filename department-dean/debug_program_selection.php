<?php
// Debug program selection issue
require_once '../config/db_connection.php';

echo "<h2>Program Selection Debug</h2>";

try {
    // Check what's in the database for CS102
    echo "<h3>1. CS102 Course Data:</h3>";
    $query = "
        SELECT 
            c.course_code,
            c.course_title,
            c.program_id,
            p.program_code,
            p.program_name,
            p.color_code
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE c.course_code = 'CS102'
        ORDER BY c.id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($courses);
    echo "</pre>";
    
    // Check what programs are available
    echo "<h3>2. Available Programs:</h3>";
    $query = "SELECT * FROM programs ORDER BY program_code";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($programs);
    echo "</pre>";
    
    // Simulate what the editCourse function would do
    echo "<h3>3. Simulated Program Data for CS102:</h3>";
    $programsData = [];
    
    foreach ($courses as $course) {
        if (!empty($course['program_code'])) {
            $programsData[] = [
                'id' => $course['program_id'],
                'program_code' => $course['program_code'],
                'program_name' => $course['program_name'],
                'program_color' => $course['color_code'] ?? '#1976d2'
            ];
        }
    }
    
    echo "<pre>";
    print_r($programsData);
    echo "</pre>";
    
    // Show what would be stored in the hidden input
    echo "<h3>4. What would be stored in editSelectedProgramsInput:</h3>";
    $jsonData = json_encode($programsData);
    echo "<p><strong>JSON:</strong> " . htmlspecialchars($jsonData) . "</p>";
    
    // Show what checkboxes would be created
    echo "<h3>5. What checkboxes would be created:</h3>";
    foreach ($programs as $program) {
        $programId = $program['id'];
        $programCode = htmlspecialchars($program['program_code']);
        $programName = htmlspecialchars($program['program_name']);
        
        echo "<p>Checkbox: <code>id='edit_modal_program_$programId' value='$programId'</code> - $programCode - $programName</p>";
    }
    
    // Show what should be pre-selected
    echo "<h3>6. What should be pre-selected:</h3>";
    foreach ($programsData as $program) {
        $programId = $program['id'];
        echo "<p>Should select: <code>edit_modal_program_$programId</code> (ID: $programId, Code: {$program['program_code']})</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
