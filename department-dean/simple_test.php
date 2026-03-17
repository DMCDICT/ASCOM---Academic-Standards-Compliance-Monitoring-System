<?php
// Simple test to check what's working
echo "<h2>Simple Database Test</h2>";

try {
    require_once 'includes/db_connection.php';
    echo "<p>✅ Database connection successful</p>";
    
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Existing tables: " . implode(', ', $tables) . "</p>";
    
    // Try to create programs table with minimal structure
    $pdo->exec("CREATE TABLE IF NOT EXISTS programs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        program_code VARCHAR(20) UNIQUE NOT NULL,
        program_name VARCHAR(255) NOT NULL,
        color_code VARCHAR(7) DEFAULT '#1976d2'
    )");
    echo "<p>✅ Programs table created/verified</p>";
    
    // Insert one test program
    $pdo->exec("INSERT IGNORE INTO programs (program_code, program_name, color_code) VALUES 
        ('BLIS', 'Bachelor of Library and Information Science', '#FF9800')");
    echo "<p>✅ Test program inserted</p>";
    
    // Check if it worked
    $programs = $pdo->query("SELECT * FROM programs")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Programs in database: " . json_encode($programs) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
