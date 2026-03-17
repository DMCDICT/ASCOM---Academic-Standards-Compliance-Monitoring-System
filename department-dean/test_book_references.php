<?php
// Test script to check book references for a course
require_once dirname(__FILE__) . '/includes/db_connection.php';

$courseCode = 'ITE 111'; // From the URL
$courseId = 77; // From the URL

echo "<h2>Testing Book References Query</h2>";

// Test 1: Check if course exists
echo "<h3>Test 1: Find course with course_code = '$courseCode'</h3>";
$query1 = "SELECT id, course_code FROM courses WHERE course_code = ?";
$stmt1 = $pdo->prepare($query1);
$stmt1->execute([$courseCode]);
$courses = $stmt1->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($courses);
echo "</pre>";

if (!empty($courses)) {
    $courseIds = array_column($courses, 'id');
    echo "<h3>Test 2: Find book references for course_ids: " . implode(', ', $courseIds) . "</h3>";
    
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $query2 = "SELECT br.* FROM book_references br WHERE br.course_id IN ($placeholders)";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute($courseIds);
    $refs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($refs) . " book references</p>";
    echo "<pre>";
    print_r($refs);
    echo "</pre>";
    
    // Test 3: Check table structure
    echo "<h3>Test 3: Book References Table Structure</h3>";
    $query3 = "DESCRIBE book_references";
    $stmt3 = $pdo->prepare($query3);
    $stmt3->execute();
    $structure = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($structure);
    echo "</pre>";
    
    // Test 4: Direct query with course_id 77
    echo "<h3>Test 4: Direct query for course_id = $courseId</h3>";
    $query4 = "SELECT br.* FROM book_references br WHERE br.course_id = ?";
    $stmt4 = $pdo->prepare($query4);
    $stmt4->execute([$courseId]);
    $refs4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($refs4) . " book references for course_id $courseId</p>";
    echo "<pre>";
    print_r($refs4);
    echo "</pre>";
}

// Test 5: Check if program-courses query matches
echo "<h3>Test 5: How program-courses counts references</h3>";
$query5 = "
    SELECT 
        c.id,
        c.course_code,
        COUNT(br.id) as book_references_count
    FROM courses c
    LEFT JOIN book_references br ON c.id = br.course_id
    WHERE c.course_code = ?
    GROUP BY c.id, c.course_code
";
$stmt5 = $pdo->prepare($query5);
$stmt5->execute([$courseCode]);
$counts = $stmt5->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($counts);
echo "</pre>";
?>

