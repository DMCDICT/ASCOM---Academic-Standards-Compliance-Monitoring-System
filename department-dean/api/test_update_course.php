<?php
// Test script to debug update_course.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing update_course.php API...\n";

// Test database connection
try {
    require_once '../includes/db_connection.php';
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test if courses table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'courses'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Courses table exists\n";
    } else {
        echo "❌ Courses table does not exist\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking courses table: " . $e->getMessage() . "\n";
}

// Test if course_programs table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_programs'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Course_programs table exists\n";
    } else {
        echo "❌ Course_programs table does not exist\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking course_programs table: " . $e->getMessage() . "\n";
}

// Test sample course update
try {
    $test_course_code = 'BLIS103';
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_code = ?");
    $stmt->execute([$test_course_code]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course) {
        echo "✅ Test course found: " . $course['course_title'] . "\n";
    } else {
        echo "❌ Test course not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking test course: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
