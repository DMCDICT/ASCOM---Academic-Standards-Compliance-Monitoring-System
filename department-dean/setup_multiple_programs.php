<?php
// Set up multiple programs for courses
require_once '../config/db_connection.php';

echo "<h2>Setting up Multiple Programs for Courses</h2>";

try {
    // Create course_programs table if it doesn't exist
    echo "<h3>1. Creating course_programs table:</h3>";
    $createTable = "
        CREATE TABLE IF NOT EXISTS course_programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_code VARCHAR(50) NOT NULL,
            program_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(id),
            UNIQUE KEY unique_course_program (course_code, program_id)
        )
    ";
    $pdo->exec($createTable);
    echo "<p style='color: green;'>✅ course_programs table created/verified</p>";
    
    // Clear existing data
    $pdo->exec("DELETE FROM course_programs");
    echo "<p>🗑️ Cleared existing course_programs data</p>";
    
    // Get program IDs
    $programs = [];
    $query = "SELECT id, program_code FROM programs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($programData as $program) {
        $programs[$program['program_code']] = $program['id'];
    }
    
    echo "<h3>2. Available programs:</h3>";
    echo "<pre>";
    print_r($programs);
    echo "</pre>";
    
    // Add multiple programs for some courses
    $coursePrograms = [
        'CS102' => ['BSCS', 'BSIT'],  // CS102 belongs to both BSCS and BSIT
        'BLIS302' => ['BLIS', 'BSCS'], // BLIS302 belongs to both BLIS and BSCS
        'MATH101' => ['BSCS', 'BSIT', 'BLIS'], // MATH101 belongs to all three
    ];
    
    echo "<h3>3. Adding multiple programs for courses:</h3>";
    
    foreach ($coursePrograms as $courseCode => $programCodes) {
        echo "<p>Adding programs for <strong>$courseCode</strong>:</p>";
        
        foreach ($programCodes as $programCode) {
            if (isset($programs[$programCode])) {
                $programId = $programs[$programCode];
                
                try {
                    $query = "INSERT INTO course_programs (course_code, program_id) VALUES (?, ?)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$courseCode, $programId]);
                    echo "<p style='color: green;'>✅ Added $courseCode → $programCode (ID: $programId)</p>";
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ Could not add $courseCode → $programCode: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Program $programCode not found</p>";
            }
        }
    }
    
    // Verify the data
    echo "<h3>4. Verification - All course_programs data:</h3>";
    $query = "
        SELECT cp.course_code, p.program_code, p.program_name, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        ORDER BY cp.course_code, p.program_code
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Setup complete! Now courses should show multiple program badges.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
