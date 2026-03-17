<?php
// test_course_creation.php
// Simple test to check course creation process

header('Content-Type: text/plain');

echo "=== COURSE CREATION TEST ===\n\n";

try {
    // Test 1: Check session config
    echo "1. Testing session configuration...\n";
    $sessionConfigPath = __DIR__ . '/../session_config.php';
    if (file_exists($sessionConfigPath)) {
        echo "   ✅ Session config file exists\n";
        require_once $sessionConfigPath;
        echo "   ✅ Session config loaded\n";
    } else {
        echo "   ❌ Session config file not found at: $sessionConfigPath\n";
    }
    
    // Test 2: Check session
    echo "\n2. Testing session...\n";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "   ✅ Session started\n";
    } else {
        echo "   ✅ Session already active\n";
    }
    
    echo "   Session ID: " . session_id() . "\n";
    echo "   Session data: " . print_r($_SESSION, true) . "\n";
    
    // Test 3: Check database connection
    echo "\n3. Testing database connection...\n";
    require_once 'includes/db_connection.php';
    if (isset($pdo) && $pdo !== null) {
        echo "   ✅ Database connection OK\n";
        
        // Test a simple query
        $testQuery = "SELECT COUNT(*) as count FROM programs";
        $stmt = $pdo->prepare($testQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   ✅ Programs count: " . $result['count'] . "\n";
    } else {
        echo "   ❌ Database connection failed\n";
    }
    
    // Test 4: Check courses table
    echo "\n4. Testing courses table...\n";
    $testQuery = "DESCRIBE courses";
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✅ Courses table structure:\n";
    foreach ($columns as $column) {
        echo "      - {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
