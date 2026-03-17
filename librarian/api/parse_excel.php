<?php
// parse_excel.php
// API endpoint to process pre-parsed Excel data from client-side (SheetJS)
// Only handles program code matching - Excel parsing is done in browser using SheetJS

header('Content-Type: application/json');

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get all program codes from database for matching
$programCodes = [];
try {
    $programQuery = "SELECT DISTINCT program_code FROM programs ORDER BY program_code ASC";
    $programStmt = $pdo->prepare($programQuery);
    $programStmt->execute();
    $programCodes = $programStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Error fetching programs: " . $e->getMessage());
}

// Function to match program code (handles cases like "BSIT-CHED" -> "BSIT")
function matchProgramCode($excelProgramCode, $databaseProgramCodes) {
    $excelProgramCode = trim(strtoupper($excelProgramCode));
    
    // Exact match
    if (in_array($excelProgramCode, $databaseProgramCodes)) {
        return $excelProgramCode;
    }
    
    // Try to match by prefix (e.g., "BSIT-CHED" -> "BSIT")
    foreach ($databaseProgramCodes as $dbCode) {
        $dbCodeUpper = strtoupper($dbCode);
        // Check if excel code starts with database code
        if (strpos($excelProgramCode, $dbCodeUpper) === 0) {
            // Check if there's a separator (dash, space, etc.) after the match
            $nextChar = substr($excelProgramCode, strlen($dbCodeUpper), 1);
            if (empty($nextChar) || in_array($nextChar, ['-', '_', ' ', '.'])) {
                return $dbCode;
            }
        }
    }
    
    // If no match found, return the original code (will be used as-is)
    return $excelProgramCode;
}

// Check if we're receiving pre-parsed data from client-side (JavaScript)
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['parsed_data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No parsed data received. Please upload an Excel file.'
    ]);
    exit;
}

try {
    // Get pre-parsed books from client-side
    $booksByProgram = $input['parsed_data'];
    
    // Check detected courses
    $detectedCourses = $input['detected_courses'] ?? [];
    $checkedCourses = [];
    
    if (!empty($detectedCourses)) {
        foreach ($detectedCourses as $course) {
            $courseCode = $course['course_code'] ?? '';
            $programCode = $course['program_code'] ?? '';
            $years = $course['years'] ?? [];
            
            // Match program code
            $matchedProgramCode = matchProgramCode($programCode, $programCodes);
            
            // Check if course exists in database
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
                'course_id' => $courseId,
                'years' => $years // Include publication years
            ];
        }
    }
    
    if (!is_array($booksByProgram) || empty($booksByProgram)) {
        echo json_encode([
            'success' => false,
            'message' => 'No books found in the Excel file.'
        ]);
        exit;
    }
    
    // Match program codes and reorganize books
    $matchedBooksByProgram = [];
    
    foreach ($booksByProgram as $programCode => $books) {
        // Match program code with database
        $matchedProgramCode = matchProgramCode($programCode, $programCodes);
        
        // Update program_code in each book
        foreach ($books as &$book) {
            $book['program_code'] = $matchedProgramCode;
        }
        
        // Group by matched program code
        if (!isset($matchedBooksByProgram[$matchedProgramCode])) {
            $matchedBooksByProgram[$matchedProgramCode] = [];
        }
        
        $matchedBooksByProgram[$matchedProgramCode] = array_merge(
            $matchedBooksByProgram[$matchedProgramCode],
            $books
        );
    }
    
    echo json_encode([
        'success' => true,
        'books_by_program' => $matchedBooksByProgram,
        'total_books' => array_sum(array_map('count', $matchedBooksByProgram)),
        'programs_found' => array_keys($matchedBooksByProgram),
        'detected_courses' => $checkedCourses
    ]);
    
} catch (Exception $e) {
    error_log("Excel processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing Excel data: ' . $e->getMessage()
    ]);
}
?>
