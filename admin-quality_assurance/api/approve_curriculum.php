<?php
// approve_curriculum.php
// API endpoint to approve a curriculum proposal

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_start();
}

try {
    // Check if user is authenticated as QA
    $isAuthenticated = false;
    
    if (isset($_SESSION['qa_logged_in']) && $_SESSION['qa_logged_in'] === true) {
        $isAuthenticated = true;
    }
    elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role']['type'] === 'quality_assurance') {
        $isAuthenticated = true;
        $_SESSION['qa_logged_in'] = true;
    }
    elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $isAuthenticated = true;
    }
    
    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in as Quality Assurance.'
        ]);
        exit;
    }
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['proposal_id']) || empty($data['proposal_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Proposal ID is required'
        ]);
        exit;
    }
    
    $proposalId = (int)$data['proposal_id'];
    $qaUserId = $_SESSION['user_id'] ?? null;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Check if proposal exists and is in pending status
        $checkStmt = $pdo->prepare("
            SELECT id, status, courses_data 
            FROM course_proposals 
            WHERE id = ?
        ");
        $checkStmt->execute([$proposalId]);
        $proposal = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$proposal) {
            throw new Exception('Proposal not found');
        }
        
        if ($proposal['status'] !== 'Pending QA Review' && $proposal['status'] !== 'PENDING' && $proposal['status'] !== 'Draft') {
            throw new Exception('Proposal cannot be approved. Current status: ' . $proposal['status']);
        }
        
        // Update proposal status to APPROVED
        $updateStmt = $pdo->prepare("
            UPDATE course_proposals 
            SET status = 'Approved',
                reviewed_at = NOW(),
                reviewed_by = ?,
                review_notes = 'Approved by Quality Assurance'
            WHERE id = ?
        ");
        $updateStmt->execute([$qaUserId, $proposalId]);
        
        // Parse courses data and create/update courses in the courses table
        $coursesData = json_decode($proposal['courses_data'] ?? '[]', true);
        
        foreach ($coursesData as $course) {
            // Check if course already exists
            $checkCourseStmt = $pdo->prepare("
                SELECT id FROM courses 
                WHERE course_code = ? AND term = ? AND academic_year = ?
            ");
            $checkCourseStmt->execute([
                $course['course_code'],
                $proposal['term'] ?? null,
                $proposal['academic_year'] ?? null
            ]);
            $existingCourse = $checkCourseStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingCourse) {
                // Update existing course
                $updateCourseStmt = $pdo->prepare("
                    UPDATE courses 
                    SET course_title = ?,
                        description = ?,
                        units = ?,
                        lecture_hours = ?,
                        laboratory_hours = ?,
                        prerequisites = ?,
                        year_level = ?,
                        program_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $updateCourseStmt->execute([
                    $course['course_title'] ?? $course['course_name'] ?? '',
                    $course['course_description'] ?? '',
                    $course['units'] ?? 0,
                    $course['lecture_hours'] ?? 0,
                    $course['laboratory_hours'] ?? 0,
                    $course['prerequisites'] ?? '',
                    $proposal['year_level'] ?? null,
                    $proposal['program_id'] ?? null,
                    $existingCourse['id']
                ]);
            } else {
                // Insert new course
                $insertCourseStmt = $pdo->prepare("
                    INSERT INTO courses (
                        course_code, course_title, description, term, academic_year,
                        units, lecture_hours, laboratory_hours, prerequisites,
                        year_level, program_id, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $insertCourseStmt->execute([
                    $course['course_code'] ?? '',
                    $course['course_title'] ?? $course['course_name'] ?? '',
                    $course['course_description'] ?? '',
                    $proposal['term'] ?? null,
                    $proposal['academic_year'] ?? null,
                    $course['units'] ?? 0,
                    $course['lecture_hours'] ?? 0,
                    $course['laboratory_hours'] ?? 0,
                    $course['prerequisites'] ?? '',
                    $proposal['year_level'] ?? null,
                    $proposal['program_id'] ?? null
                ]);
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Curriculum proposal approved successfully',
            'proposal_id' => $proposalId,
            'courses_affected' => count($coursesData)
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to approve curriculum proposal',
        'error' => $e->getMessage()
    ]);
}
?>