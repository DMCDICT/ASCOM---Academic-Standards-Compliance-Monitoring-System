<?php
// submit_syllabus.php - Submit syllabus for approval
require_once dirname(__FILE__, 3) . '/session_config.php';
require_once dirname(__FILE__, 3) . '/bootstrap/auth.php';

ascom_require_role('teacher', dirname(__FILE__, 3) . '/../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__, 3) . '/../super_admin-mis/includes/db_connection.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $course_id = $input['course_id'] ?? null;
    $teacher_id = $input['teacher_id'] ?? $_SESSION['user_id'];
    $academic_year = $input['academic_year'] ?? null;
    $term = $input['term'] ?? null;
    
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        exit;
    }
    
    // Validate required fields
    $required = ['course_description', 'expected_course_outcomes', 'course_requirements', 'remote_policies'];
    $missing = [];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
        exit;
    }
    
    // Prepare syllabus data
    $syllabusData = [
        'course_description' => $input['course_description'] ?? '',
        'course_objectives' => $input['course_objectives'] ?? '',
        'expected_course_outcomes' => $input['expected_course_outcomes'] ?? '',
        'books' => $input['books'] ?? '[]',
        'ebooks' => $input['ebooks'] ?? '[]',
        'web_resources' => $input['web_resources'] ?? '[]',
        'learning_plan' => $input['learning_plan'] ?? '[]',
        'exam_schedules' => $input['exam_schedules'] ?? '[]',
        'grading_system' => $input['grading_system'] ?? '[]',
        'course_requirements' => $input['course_requirements'] ?? '',
        'course_expectations' => $input['course_expectations'] ?? '',
        'remote_policies' => $input['remote_policies'] ?? '',
        'references' => $input['references'] ?? '[]'
    ];
    
    // Check if syllabus exists
    $stmt = $pdo->prepare("
        SELECT id FROM course_syllabi 
        WHERE course_id = ? AND teacher_id = ? AND academic_year = ? AND term = ?
    ");
    $stmt->execute([$course_id, $teacher_id, $academic_year, $term]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing syllabus and set status to submitted
        $updateSql = "
            UPDATE course_syllabi SET
                course_description = ?,
                course_objectives = ?,
                expected_course_outcomes = ?,
                books = ?,
                ebooks = ?,
                web_resources = ?,
                learning_plan = ?,
                exam_schedules = ?,
                grading_system = ?,
                course_requirements = ?,
                course_expectations = ?,
                remote_policies = ?,
                references = ?,
                status = 'submitted',
                submitted_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ";
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            $syllabusData['course_description'],
            $syllabusData['course_objectives'],
            $syllabusData['expected_course_outcomes'],
            $syllabusData['books'],
            $syllabusData['ebooks'],
            $syllabusData['web_resources'],
            $syllabusData['learning_plan'],
            $syllabusData['exam_schedules'],
            $syllabusData['grading_system'],
            $syllabusData['course_requirements'],
            $syllabusData['course_expectations'],
            $syllabusData['remote_policies'],
            $syllabusData['references'],
            $existing['id']
        ]);
        
        $syllabusId = $existing['id'];
    } else {
        // Insert new syllabus as submitted
        $insertSql = "
            INSERT INTO course_syllabi (
                course_id, teacher_id, academic_year, term,
                course_description, course_objectives, expected_course_outcomes,
                books, ebooks, web_resources, learning_plan, exam_schedules,
                grading_system, course_requirements, course_expectations,
                remote_policies, references, status, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())
        ";
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $course_id, $teacher_id, $academic_year, $term,
            $syllabusData['course_description'],
            $syllabusData['course_objectives'],
            $syllabusData['expected_course_outcomes'],
            $syllabusData['books'],
            $syllabusData['ebooks'],
            $syllabusData['web_resources'],
            $syllabusData['learning_plan'],
            $syllabusData['exam_schedules'],
            $syllabusData['grading_system'],
            $syllabusData['course_requirements'],
            $syllabusData['course_expectations'],
            $syllabusData['remote_policies'],
            $syllabusData['references']
        ]);
        
        $syllabusId = $pdo->lastInsertId();
    }
    
    // Update course syllabus status
    $stmt = $pdo->prepare("UPDATE courses SET syllabus_status = 'submitted', last_syllabus_update = NOW() WHERE id = ?");
    $stmt->execute([$course_id]);
    
    // Update course_assignments
    $stmt = $pdo->prepare("
        UPDATE course_assignments 
        SET syllabus_status = 'submitted', syllabus_submitted_at = NOW() 
        WHERE course_id = ? AND teacher_id = ?
    ");
    $stmt->execute([$course_id, $teacher_id]);
    
    // Log activity
    logActivity($pdo, $teacher_id, 'syllabus_submit', 'Syllabus submitted for course ID: ' . $course_id, 'course_syllabi', $syllabusId);
    
    echo json_encode(['success' => true, 'message' => 'Syllabus submitted for approval', 'id' => $syllabusId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error submitting syllabus: ' . $e->getMessage()]);
}

function logActivity($pdo, $userId, $activityType, $description, $targetEntity, $targetId) {
    try {
        $username = $_SESSION['username'] ?? 'unknown';
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $username, $activityType, $description, $targetEntity, $targetId]);
    } catch (Exception $e) {
        // Silently fail
    }
}
?>