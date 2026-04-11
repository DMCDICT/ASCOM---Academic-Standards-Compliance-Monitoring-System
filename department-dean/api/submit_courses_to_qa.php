<?php
// submit_courses_to_qa.php
// API endpoint to submit multiple courses to Quality Assurance

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    require_once dirname(__FILE__) . '/../session_config.php';
    // Configure session before starting
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

try {
    // Check if user is authenticated - use same logic as content.php
    $isAuthenticated = false;
    
    // Primary check: dean_logged_in flag
    if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
        $isAuthenticated = true;
    }
    // Secondary check: selected_role
    elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role']['type'] === 'dean') {
        $isAuthenticated = true;
        $_SESSION['dean_logged_in'] = true; // Set the flag for future requests
    }
    // Tertiary check: user_id and username exist (basic session validation)
    elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $isAuthenticated = true;
        $_SESSION['dean_logged_in'] = true; // Assume dean if we have basic session
    }
    
    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in as department dean.'
        ]);
        exit;
    }
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['courses']) || !is_array($data['courses']) || empty($data['courses'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No courses provided'
        ]);
        exit;
    }
    
    $courses = $data['courses'];
    $userId = $_SESSION['user_id'];
    $programId = $data['program_id'] ?? null;
    $term = $data['term'] ?? null;
    $academicYear = $data['academic_year'] ?? null;
    $yearLevel = $data['year_level'] ?? null;
    $courseType = $data['course_type'] ?? 'New Course Proposal';
    
    $submittedCount = 0;
    $errors = [];
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Validate that we have courses to submit
        if (empty($courses)) {
            throw new Exception('No courses provided');
        }
        
        // Prepare courses data for storage
        $coursesData = [];
        foreach ($courses as $course) {
            // Validate required fields
            if (empty($course['course_code']) || empty($course['course_title'] ?? $course['course_name'])) {
                $errors[] = "Course code and title are required for all courses";
                continue;
            }
            
            $coursesData[] = $course;
        }
        
        if (empty($coursesData)) {
            throw new Exception('No valid courses to submit');
        }
        
        // Save to course_proposals table
        $insertProposalStmt = $pdo->prepare("
            INSERT INTO course_proposals (
                user_id, program_id, term, academic_year, year_level, 
                course_type, status, courses_data, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending QA Review', ?, NOW())
        ");
        
        $coursesJson = json_encode($coursesData);
        
        $insertProposalStmt->execute([
            $userId,
            $programId,
            $term,
            $academicYear,
            $yearLevel,
            $courseType,
            $coursesJson
        ]);
        
        $proposalId = $pdo->lastInsertId();
        $submittedCount = count($coursesData);
        
        // Optionally, delete the draft if it exists
        if (isset($data['draft_id']) || isset($data['_draftId'])) {
            $draftId = $data['draft_id'] ?? $data['_draftId'] ?? null;
            if ($draftId) {
                $deleteDraftStmt = $pdo->prepare("DELETE FROM course_drafts WHERE id = ? AND user_id = ?");
                $deleteDraftStmt->execute([$draftId, $userId]);
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully submitted $submittedCount course(s) to Quality Assurance",
            'submitted_count' => $submittedCount,
            'total_count' => count($courses),
            'proposal_id' => $proposalId,
            'errors' => $errors
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit courses to Quality Assurance',
        'error' => $e->getMessage()
    ]);
}
?>

