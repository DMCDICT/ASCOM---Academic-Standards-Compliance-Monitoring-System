<?php
// get_program_head_data.php - Get data for program head dashboard
require_once dirname(__FILE__, 3) . '/session_config.php';
require_once dirname(__FILE__, 3) . '/bootstrap/auth.php';

ascom_require_role('teacher', dirname(__FILE__, 3) . '/../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__, 3) . '/../super_admin-mis/includes/db_connection.php';
    
    $teacher_id = $_SESSION['user_id'];
    $academic_year = $_SESSION['selected_role']['academic_year'] ?? null;
    $term = $_SESSION['selected_role']['term'] ?? null;
    
    // Get program head info
    $phStmt = $pdo->prepare("
        SELECT ph.id, ph.program_id, p.program_name, p.program_code
        FROM program_heads ph
        JOIN programs p ON ph.program_id = p.id
        WHERE ph.teacher_id = ? AND ph.is_active = TRUE
    ");
    $phStmt->execute([$teacher_id]);
    $programHead = $phStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$programHead) {
        echo json_encode(['success' => false, 'message' => 'Not a program head']);
        exit;
    }
    
    $program_id = $programHead['program_id'];
    
    // Get courses in this program
    $courseStmt = $pdo->prepare("
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.year_level,
            c.syllabus_status,
            cs.id as syllabus_id,
            cs.status as syllabus_full_status,
            u.first_name,
            u.last_name,
            u.title
        FROM courses c
        LEFT JOIN course_syllabi cs ON c.id = cs.course_id
        LEFT JOIN course_assignments ca ON c.id = ca.course_id AND ca.is_active = TRUE
        LEFT JOIN users u ON ca.teacher_id = u.id
        WHERE c.program_id = ?
    ");
    $courseStmt->execute([$program_id]);
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get teachers
    $teacherStmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.first_name, u.last_name, u.title, u.email
        FROM users u
        INNER JOIN course_assignments ca ON u.id = ca.teacher_id
        INNER JOIN courses c ON ca.course_id = c.id
        WHERE c.program_id = ? AND ca.is_active = TRUE
    ");
    $teacherStmt->execute([$program_id]);
    $teachers = $teacherStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get pending syllabi for review
    $pendingStmt = $pdo->prepare("
        SELECT 
            cs.id,
            cs.course_id,
            cs.teacher_id,
            cs.status,
            cs.submitted_at,
            cs.ph_review_comments,
            c.course_code,
            c.course_title,
            u.first_name,
            u.last_name,
            u.title
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        JOIN users u ON cs.teacher_id = u.id
        WHERE c.program_id = ? AND cs.status = 'submitted'
        ORDER BY cs.submitted_at ASC
    ");
    $pendingStmt->execute([$program_id]);
    $pendingSyllabi = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get approved syllabi
    $approvedStmt = $pdo->prepare("
        SELECT 
            cs.id,
            cs.course_id,
            c.course_code,
            c.course_title,
            u.first_name,
            u.last_name,
            u.title,
            cs.status,
            cs.ph_approved_at
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        JOIN users u ON cs.teacher_id = u.id
        WHERE c.program_id = ? AND cs.status IN ('ph_approved', 'dean_approved')
        ORDER BY cs.ph_approved_at DESC
    ");
    $approvedStmt->execute([$program_id]);
    $approvedSyllabi = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process courses to add teacher names
    foreach ($courses as &$course) {
        if ($course['first_name']) {
            $course['teacher_name'] = ($course['title'] ? $course['title'] . ' ' : '') . $course['first_name'] . ' ' . $course['last_name'];
        } else {
            $course['teacher_name'] = null;
        }
        // Map full status to simple status for display
        $course['syllabus_status'] = $course['syllabus_full_status'] ?? 'not_started';
    }
    
    // Process pending syllabi to add teacher names
    foreach ($pendingSyllabi as &$s) {
        $s['teacher_name'] = ($s['title'] ? $s['title'] . ' ' : '') . $s['first_name'] . ' ' . $s['last_name'];
    }
    
    // Process approved syllabi to add teacher names
    foreach ($approvedSyllabi as &$s) {
        $s['teacher_name'] = ($s['title'] ? $s['title'] . ' ' : '') . $s['first_name'] . ' ' . $s['last_name'];
    }
    
    echo json_encode([
        'success' => true,
        'program_head' => $programHead,
        'courses' => $courses,
        'teachers' => $teachers,
        'pendingSyllabi' => $pendingSyllabi,
        'approvedSyllabi' => $approvedSyllabi
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading data: ' . $e->getMessage()]);
}
?>