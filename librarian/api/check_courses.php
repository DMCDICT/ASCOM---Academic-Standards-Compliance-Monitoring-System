<?php
// check_courses.php
// Check which courses exist in the database

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);
$courses = $input['courses'] ?? [];

if (empty($courses)) {
    echo json_encode([
        'success' => false,
        'message' => 'No courses provided'
    ]);
    exit;
}

try {
    // Get all program codes
    $programQuery = "SELECT DISTINCT program_code FROM programs ORDER BY program_code ASC";
    $programStmt = $pdo->prepare($programQuery);
    $programStmt->execute();
    $programCodes = $programStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Function to match program code
    function matchProgramCode($excelProgramCode, $databaseProgramCodes) {
        $excelProgramCode = trim(strtoupper($excelProgramCode));
        
        if (in_array($excelProgramCode, $databaseProgramCodes)) {
            return $excelProgramCode;
        }
        
        foreach ($databaseProgramCodes as $dbCode) {
            $dbCodeUpper = strtoupper($dbCode);
            if (strpos($excelProgramCode, $dbCodeUpper) === 0) {
                $nextChar = substr($excelProgramCode, strlen($dbCodeUpper), 1);
                if (empty($nextChar) || in_array($nextChar, ['-', '_', ' ', '.'])) {
                    return $dbCode;
                }
            }
        }
        
        return $excelProgramCode;
    }
    
    $checkedCourses = [];
    
    foreach ($courses as $course) {
        $courseCode = $course['course_code'] ?? '';
        $programCode = $course['program_code'] ?? '';
        
        // Match program code
        $matchedProgramCode = matchProgramCode($programCode, $programCodes);
        
        // Check if course exists
        $exists = false;
        $courseId = null;
        
        if ($courseCode && $matchedProgramCode) {
            // Get program ID
            $programQuery = "SELECT id FROM programs WHERE program_code = ? LIMIT 1";
            $programStmt = $pdo->prepare($programQuery);
            $programStmt->execute([$matchedProgramCode]);
            $program = $programStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($program) {
                $programId = $program['id'];
                
                // Check if course exists
                $courseCheckQuery = "SELECT id FROM courses WHERE course_code = ? AND program_id = ? LIMIT 1";
                $courseCheckStmt = $pdo->prepare($courseCheckQuery);
                $courseCheckStmt->execute([$courseCode, $programId]);
                $existingCourse = $courseCheckStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingCourse) {
                    $exists = true;
                    $courseId = $existingCourse['id'];
                }
            }
        }
        
        $checkedCourses[] = [
            'course_code' => $courseCode,
            'course_title' => $course['course_title'] ?? '',
            'program_code' => $matchedProgramCode,
            'exists_in_db' => $exists,
            'course_id' => $courseId
        ];
    }
    
    echo json_encode([
        'success' => true,
        'checked_courses' => $checkedCourses
    ]);
    
} catch (Exception $e) {
    error_log("Error checking courses: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

