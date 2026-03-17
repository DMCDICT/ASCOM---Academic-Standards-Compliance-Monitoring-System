<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
try {
    require_once '../includes/db_connection.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data - support both JSON and form data
    $input = null;
    
    // Try JSON first
    $json_input = json_decode(file_get_contents('php://input'), true);
    if ($json_input) {
        $input = $json_input;
        error_log("API received JSON data: " . print_r($input, true));
    } else {
        // Fallback to form data
        $input = $_POST;
        error_log("API received form data: " . print_r($input, true));
    }
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Debug: Log all received fields
    error_log("All received fields: " . print_r(array_keys($input), true));
    error_log("Input data: " . print_r($input, true));
    
    // Map form field names to expected field names
    $course_code = trim($input['course_code']);
    $course_title = trim($input['course_title']);
    $units = trim($input['units']);
    $term = trim($input['school_term'] ?? '');  // Form uses 'school_term'
    $academic_year = trim($input['school_year'] ?? '');  // Form uses 'school_year'
    $year_level = trim($input['year_level']);
    $programs = trim($input['programs']);
    
    // Validate required fields
    $required_fields = [
        'course_code' => $course_code,
        'course_title' => $course_title,
        'units' => $units,
        'term' => $term,
        'academic_year' => $academic_year,
        'year_level' => $year_level,
        'programs' => $programs
    ];
    
    foreach ($required_fields as $field_name => $field_value) {
        if (empty($field_value)) {
            throw new Exception("Missing required field: $field_name. Available fields: " . implode(', ', array_keys($input)));
        }
    }
    
    // Parse programs JSON
    $programs_array = json_decode($programs, true);
    if (!$programs_array) {
        throw new Exception('Invalid programs data');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update course basic information (ALL duplicate entries for this course)
    $update_course_sql = "UPDATE courses SET 
        course_title = :course_title,
        units = :units,
        term = :term,
        academic_year = :academic_year,
        year_level = :year_level,
        updated_at = NOW()
        WHERE course_code = :course_code";
    
    $stmt = $pdo->prepare($update_course_sql);
    $result = $stmt->execute([
        ':course_title' => $course_title,
        ':units' => $units,
        ':term' => $term,
        ':academic_year' => $academic_year,
        ':year_level' => $year_level,
        ':course_code' => $course_code
    ]);
    
    $rows_affected = $stmt->rowCount();
    error_log("Course update result: success=" . ($result ? 'true' : 'false') . ", rows_affected=" . $rows_affected);
    error_log("Update parameters: course_code=$course_code, course_title=$course_title, term=$term, academic_year=$academic_year");
    
    if ($rows_affected === 0) {
        throw new Exception('Course not found or no changes made');
    }
    
    // Update course programs (handle multiple program entries)
    try {
        // Get the first course entry as template (before deletion)
        $template_sql = "SELECT * FROM courses WHERE course_code = :course_code LIMIT 1";
        $stmt = $pdo->prepare($template_sql);
        $stmt->execute([':course_code' => $course_code]);
        $template_course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template_course) {
            // If no template found, use the updated data
            $template_course = [
                'course_title' => $course_title,
                'units' => $units,
                'term' => $term,
                'academic_year' => $academic_year,
                'year_level' => $year_level,
                'faculty_id' => null,
                'status' => 'Active',
                'created_by' => null
            ];
        } else {
            // Update template with new data
            $template_course['course_title'] = $course_title;
            $template_course['units'] = $units;
            $template_course['term'] = $term;
            $template_course['academic_year'] = $academic_year;
            $template_course['year_level'] = $year_level;
        }
        
        // Now delete all existing entries for this course
        $delete_sql = "DELETE FROM courses WHERE course_code = :course_code";
        $stmt = $pdo->prepare($delete_sql);
        $delete_result = $stmt->execute([':course_code' => $course_code]);
        $deleted_rows = $stmt->rowCount();
        error_log("Deleted $deleted_rows existing course entries for: $course_code");
        
        // Create new entries for each selected program
        $insert_sql = "INSERT INTO courses (
            course_code, course_title, units, program_id, faculty_id, 
            status, term, academic_year, year_level, created_by, 
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $pdo->prepare($insert_sql);
        $created_entries = 0;
        
        foreach ($programs_array as $program) {
            if (isset($program['id'])) {
                $insert_result = $stmt->execute([
                    $course_code,
                    $template_course['course_title'],
                    $template_course['units'],
                    $program['id'],
                    $template_course['faculty_id'],
                    $template_course['status'],
                    $template_course['term'],
                    $template_course['academic_year'],
                    $template_course['year_level'],
                    $template_course['created_by']
                ]);
                
                if ($insert_result) {
                    $created_entries++;
                    error_log("Created course entry: $course_code -> Program ID {$program['id']}");
                }
            }
        }
        
        error_log("Created $created_entries new course entries for: $course_code");
        
    } catch (Exception $e) {
        // If there's an error with program update, log it but don't fail the entire update
        error_log("Error updating course programs: " . $e->getMessage());
    }
    
    // Commit transaction
    $commit_result = $pdo->commit();
    error_log("Transaction commit result: " . ($commit_result ? 'SUCCESS' : 'FAILED'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Course updated successfully',
        'data' => [
            'course_code' => $course_code,
            'course_title' => $course_title,
            'units' => $units,
            'term' => $term,
            'academic_year' => $academic_year,
            'year_level' => $year_level,
            'programs' => $programs_array
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error for debugging
    error_log("Update course error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'received_data' => $input ?? 'No input data',
            'post_data' => $_POST,
            'json_input' => $json_input ?? 'No JSON input'
        ]
    ]);
}
?>
