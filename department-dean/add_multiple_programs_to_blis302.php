<?php
// Add multiple programs to BLIS302 for testing
require_once '../config/db_connection.php';

echo "<h2>Adding Multiple Programs to BLIS302</h2>";

try {
    // First, check if course_programs table exists, if not create it
    $query = "SHOW TABLES LIKE 'course_programs'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p>Creating course_programs table...</p>";
        $createTable = "
            CREATE TABLE course_programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_code VARCHAR(50) NOT NULL,
                program_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (program_id) REFERENCES programs(id),
                UNIQUE KEY unique_course_program (course_code, program_id)
            )
        ";
        $pdo->exec($createTable);
        echo "<p style='color: green;'>course_programs table created!</p>";
    } else {
        echo "<p style='color: green;'>course_programs table already exists</p>";
    }
    
    // Get program IDs
    $programs = [
        'BLIS' => null,
        'BSCS' => null,
        'BSIT' => null
    ];
    
    foreach ($programs as $programCode => &$programId) {
        $query = "SELECT id FROM programs WHERE program_code = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$programCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $programId = $result['id'];
            echo "<p>Found {$programCode} with ID: {$programId}</p>";
        } else {
            echo "<p style='color: red;'>Program {$programCode} not found!</p>";
        }
    }
    
    // Clear existing associations for BLIS302
    $query = "DELETE FROM course_programs WHERE course_code = 'BLIS302'";
    $pdo->exec($query);
    echo "<p>Cleared existing associations for BLIS302</p>";
    
    // Add multiple program associations for BLIS302
    $insertedCount = 0;
    foreach ($programs as $programCode => $programId) {
        if ($programId) {
            try {
                $query = "INSERT INTO course_programs (course_code, program_id) VALUES ('BLIS302', ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$programId]);
                $insertedCount++;
                echo "<p style='color: green;'>Added BLIS302 -> {$programCode} (ID: {$programId})</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>Could not add {$programCode}: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<p style='color: blue;'><strong>Total programs added: {$insertedCount}</strong></p>";
    
    // Verify the associations
    echo "<h3>Verification:</h3>";
    $query = "
        SELECT cp.course_code, p.program_code, p.program_name, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        WHERE cp.course_code = 'BLIS302'
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($associations) {
        echo "<pre>";
        print_r($associations);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No associations found!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
