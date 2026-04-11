<?php
// process_course.php - Handle course proposal submission to QA
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    require_once dirname(__FILE__) . '/../session_config.php';
    session_start();
}

try {
    // Check if user is authenticated as dean
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['dean_logged_in']) || $_SESSION['dean_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in as department dean.'
        ]);
        exit;
    }
    
    // Get form data
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseName = trim($_POST['course_name'] ?? '');
    $units = intval($_POST['units'] ?? 0);
    $lectureHours = intval($_POST['lecture_hours'] ?? 0);
    $laboratoryHours = intval($_POST['laboratory_hours'] ?? 0);
    $prerequisites = trim($_POST['prerequisites'] ?? 'None');
    $courseDescription = trim($_POST['course_description'] ?? '');
    $justification = trim($_POST['justification'] ?? '');
    
    // Get academic information
    $academicTerm = trim($_POST['academic_term'] ?? $_POST['term'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');
    $programId = !empty($_POST['selectedPrograms']) ? intval(explode(',', $_POST['selectedPrograms'])[0]) : null;
    
    // Get course type
    $courseType = trim($_POST['course_type'] ?? 'New Course Proposal');
    
    // Validate required fields
    if (empty($courseCode)) {
        throw new Exception('Course code is required');
    }
    if (empty($courseName)) {
        throw new Exception('Course name is required');
    }
    if ($units <= 0) {
        throw new Exception('Units must be greater than 0');
    }
    
    // Collect learning outcomes
    $learningOutcomes = [];
    if (isset($_POST['learning_outcomes']) && is_array($_POST['learning_outcomes'])) {
        $learningOutcomes = array_filter(array_map('trim', $_POST['learning_outcomes']));
    }
    
    // Collect course outline
    $courseOutline = [];
    if (isset($_POST['course_outline']) && is_array($_POST['course_outline'])) {
        foreach ($_POST['course_outline'] as $topic) {
            if (isset($topic['topic']) && !empty(trim($topic['topic']))) {
                $courseOutline[] = [
                    'topic' => trim($topic['topic']),
                    'description' => trim($topic['description'] ?? ''),
                    'hours' => floatval($topic['hours'] ?? 0)
                ];
            }
        }
    }
    
    // Collect assessment methods
    $assessmentMethods = [];
    if (isset($_POST['assessment_methods']) && is_array($_POST['assessment_methods'])) {
        foreach ($_POST['assessment_methods'] as $method) {
            if (isset($method['type']) && !empty(trim($method['type']))) {
                $assessmentMethods[] = [
                    'type' => trim($method['type']),
                    'weight' => floatval($method['weight'] ?? 0)
                ];
            }
        }
    }
    
    // Collect learning materials
    $learningMaterials = [];
    if (isset($_POST['learning_materials']) && is_array($_POST['learning_materials'])) {
        foreach ($_POST['learning_materials'] as $material) {
            if (isset($material['material_title']) && !empty(trim($material['material_title']))) {
                $learningMaterials[] = [
                    'material_type' => trim($material['material_type'] ?? ''),
                    'material_title' => trim($material['material_title']),
                    'author' => trim($material['author'] ?? ''),
                    'publication_year' => trim($material['publication_year'] ?? ''),
                    'edition' => trim($material['edition'] ?? ''),
                    'publisher' => trim($material['publisher'] ?? ''),
                    'isbn' => trim($material['isbn'] ?? '')
                ];
            }
        }
    }
    
    // Prepare course data for storage
    $courseData = [
        'course_code' => $courseCode,
        'course_name' => $courseName,
        'course_title' => $courseName,
        'units' => $units,
        'lecture_hours' => $lectureHours,
        'laboratory_hours' => $laboratoryHours,
        'prerequisites' => $prerequisites,
        'course_description' => $courseDescription,
        'learning_outcomes' => $learningOutcomes,
        'course_outline' => $courseOutline,
        'assessment_methods' => $assessmentMethods,
        'learning_materials' => $learningMaterials,
        'justification' => $justification
    ];
    
    // Handle file attachments if any
    $attachments = [];
    if (isset($_FILES['course_attachments']) && is_array($_FILES['course_attachments']['name'])) {
        $fileCount = count($_FILES['course_attachments']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['course_attachments']['error'][$i] === UPLOAD_ERR_OK) {
                $attachments[] = [
                    'name' => $_FILES['course_attachments']['name'][$i],
                    'size' => $_FILES['course_attachments']['size'][$i],
                    'type' => $_FILES['course_attachments']['type'][$i]
                ];
            }
        }
    }
    $courseData['attachments'] = $attachments;
    
    // Get user ID
    $userId = $_SESSION['user_id'];
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Save to course_proposals table
        $insertProposalStmt = $pdo->prepare("
            INSERT INTO course_proposals (
                user_id, program_id, term, academic_year, year_level, 
                course_type, status, courses_data, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending QA Review', ?, NOW())
        ");
        
        $coursesJson = json_encode([$courseData]);
        
        $insertProposalStmt->execute([
            $userId,
            $programId,
            $academicTerm,
            $academicYear,
            $yearLevel,
            $courseType,
            $coursesJson
        ]);
        
        $proposalId = $pdo->lastInsertId();
        
        // Optionally, delete the draft if it exists
        if (isset($_POST['draft_id']) && !empty($_POST['draft_id'])) {
            $draftId = intval($_POST['draft_id']);
            $deleteDraftStmt = $pdo->prepare("DELETE FROM course_drafts WHERE id = ? AND user_id = ?");
            $deleteDraftStmt->execute([$draftId, $userId]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Course proposal submitted successfully to Quality Assurance for review.',
            'proposal_id' => $proposalId,
            'course_code' => $courseCode,
            'course_name' => $courseName
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.',
        'error_details' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Validation or other error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => null
    ]);
}
?>
