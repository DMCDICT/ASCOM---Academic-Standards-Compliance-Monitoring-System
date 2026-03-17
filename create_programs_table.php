<?php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h1>Create Programs Table</h1>";

try {
    $checkTable = "SHOW TABLES LIKE 'programs'";
    $result = $pdo->query($checkTable);
    
    if ($result->rowCount() > 0) {
        echo "<p>✅ Programs table already exists!</p>";
    } else {
        echo "<p>Creating programs table...</p>";
        
        $createTableSQL = "
        CREATE TABLE programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            program_code VARCHAR(20) NOT NULL,
            program_name VARCHAR(255) NOT NULL,
            color_code VARCHAR(7) NOT NULL,
            department_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
            UNIQUE KEY unique_program_department (program_code, department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($pdo->exec($createTableSQL) !== false) {
            echo "<p>✅ Programs table created successfully!</p>";
            
            // Insert sample data
            $samplePrograms = [
                ['BSIT', 'Bachelor of Science in Information Technology', '#4A7DFF', 1],
                ['BSCS', 'Bachelor of Science in Computer Science', '#FF6B6B', 1]
            ];
            
            $insertStmt = $pdo->prepare("INSERT INTO programs (program_code, program_name, color_code, department_id) VALUES (?, ?, ?, ?)");
            
            foreach ($samplePrograms as $program) {
                try {
                    $insertStmt->execute($program);
                } catch (PDOException $e) {
                    // Skip if already exists
                }
            }
            
            echo "<p>✅ Sample programs inserted!</p>";
        } else {
            echo "<p>❌ Failed to create programs table!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
?>

