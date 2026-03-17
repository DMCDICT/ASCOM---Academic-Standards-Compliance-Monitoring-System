<?php
// test_drafts.php
// Quick test script to check if drafts exist in the database

header('Content-Type: text/plain');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "=== Testing Course Drafts ===\n\n";

if (!isset($_SESSION['user_id'])) {
    echo "❌ User not authenticated. Session ID: " . session_id() . "\n";
    echo "Session data: " . print_r($_SESSION, true) . "\n";
    exit;
}

$userId = $_SESSION['user_id'];
echo "✅ User ID: $userId\n\n";

// Check if table exists
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'course_drafts'");
    if ($checkTable->rowCount() > 0) {
        echo "✅ course_drafts table exists\n\n";
        
        // Count total drafts
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM course_drafts WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Total drafts for user $userId: " . $count['count'] . "\n\n";
        
        // Get all drafts
        $draftsStmt = $pdo->prepare("
            SELECT 
                cd.id,
                cd.user_id,
                cd.program_id,
                cd.term,
                cd.academic_year,
                cd.year_level,
                cd.created_at,
                cd.updated_at,
                LENGTH(cd.courses_data) as data_length,
                LEFT(cd.courses_data, 100) as data_preview
            FROM course_drafts cd
            WHERE cd.user_id = ?
            ORDER BY cd.updated_at DESC
        ");
        $draftsStmt->execute([$userId]);
        $drafts = $draftsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($drafts) > 0) {
            echo "📝 Drafts found:\n";
            echo str_repeat("=", 80) . "\n";
            foreach ($drafts as $draft) {
                echo "ID: " . $draft['id'] . "\n";
                echo "Program ID: " . ($draft['program_id'] ?? 'NULL') . "\n";
                echo "Term: " . ($draft['term'] ?? 'NULL') . "\n";
                echo "Academic Year: " . ($draft['academic_year'] ?? 'NULL') . "\n";
                echo "Year Level: " . ($draft['year_level'] ?? 'NULL') . "\n";
                echo "Data Length: " . $draft['data_length'] . " bytes\n";
                echo "Data Preview: " . $draft['data_preview'] . "...\n";
                echo "Created: " . $draft['created_at'] . "\n";
                echo "Updated: " . $draft['updated_at'] . "\n";
                
                // Try to decode JSON
                $fullDataStmt = $pdo->prepare("SELECT courses_data FROM course_drafts WHERE id = ?");
                $fullDataStmt->execute([$draft['id']]);
                $fullData = $fullDataStmt->fetch(PDO::FETCH_ASSOC);
                $coursesData = json_decode($fullData['courses_data'], true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "✅ JSON is valid\n";
                    echo "Courses count: " . (is_array($coursesData) ? count($coursesData) : 0) . "\n";
                    if (is_array($coursesData) && count($coursesData) > 0) {
                        $firstCourse = $coursesData[0];
                        echo "First course code: " . ($firstCourse['course_code'] ?? 'N/A') . "\n";
                        echo "First course name: " . ($firstCourse['course_name'] ?? 'N/A') . "\n";
                    }
                } else {
                    echo "❌ JSON decode error: " . json_last_error_msg() . "\n";
                }
                echo str_repeat("-", 80) . "\n";
            }
        } else {
            echo "⚠️ No drafts found for user $userId\n";
        }
    } else {
        echo "❌ course_drafts table does NOT exist\n";
        echo "Please run: department-dean/api/create_course_drafts_table.php\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>

