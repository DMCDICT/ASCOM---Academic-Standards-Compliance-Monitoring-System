<?php
// Script to update existing term values to use full semester names
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/db_connection.php';
    echo "✅ Database connection successful\n\n";
    
    // Update existing term values to use full semester names
    echo "=== UPDATING TERM FORMATS ===\n";
    
    // Update 1st to 1st Semester
    $stmt = $pdo->prepare("UPDATE courses SET term = '1st Semester' WHERE term = '1st'");
    $result1 = $stmt->execute();
    $rows1 = $stmt->rowCount();
    echo "Updated '1st' to '1st Semester': $rows1 rows affected\n";
    
    // Update 2nd to 2nd Semester
    $stmt = $pdo->prepare("UPDATE courses SET term = '2nd Semester' WHERE term = '2nd'");
    $result2 = $stmt->execute();
    $rows2 = $stmt->rowCount();
    echo "Updated '2nd' to '2nd Semester': $rows2 rows affected\n";
    
    // Update summer to Summer Semester
    $stmt = $pdo->prepare("UPDATE courses SET term = 'Summer Semester' WHERE term = 'summer'");
    $result3 = $stmt->execute();
    $rows3 = $stmt->rowCount();
    echo "Updated 'summer' to 'Summer Semester': $rows3 rows affected\n";
    
    // Show current term values
    echo "\n=== CURRENT TERM VALUES ===\n";
    $stmt = $pdo->query("SELECT DISTINCT term FROM courses ORDER BY term");
    $terms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($terms as $term) {
        echo "- '$term'\n";
    }
    
    // Show sample courses with their terms
    echo "\n=== SAMPLE COURSES WITH TERMS ===\n";
    $stmt = $pdo->query("SELECT course_code, course_title, term FROM courses LIMIT 5");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($courses as $course) {
        echo "- {$course['course_code']}: {$course['course_title']} - {$course['term']}\n";
    }
    
    echo "\n✅ Term format update completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
