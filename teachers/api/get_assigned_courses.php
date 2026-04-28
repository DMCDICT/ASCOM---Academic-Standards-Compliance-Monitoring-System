<?php
// get_assigned_courses.php - Get courses assigned to the current teacher
require_once dirname(__FILE__, 3) . '/session_config.php';
require_once dirname(__FILE__, 3) . '/bootstrap/auth.php';

ascom_require_role('teacher', dirname(__FILE__, 3) . '/../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__, 3) . '/../super_admin-mis/includes/db_connection.php';
    
    $teacher_id = $_SESSION['user_id'];
    $department_id = $_SESSION['selected_role']['department_id'] ?? null;
    
    // Get current academic year and term
    $academic_year = $_SESSION['selected_role']['academic_year'] ?? null;
    $term = $_SESSION['selected_role']['term'] ?? null;
    
    // Build query
    $sql = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.year_level,
            c.term as course_term,
            c.academic_year,
            p.program_code,
            p.program_name,
            cs.status as syllabus_status,
            cs.id as syllabus_id,
            ca.syllabus_status as assignment_syllabus_status
        FROM courses c
        INNER JOIN course_assignments ca ON c.id = ca.course_id
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN course_syllabi cs ON c.id = cs.course_id AND cs.teacher_id = ?
        WHERE ca.teacher_id = ?
    ";
    
    $params = [$teacher_id, $teacher_id];
    
    if ($department_id) {
        $sql .= " AND c.department_id = ?";
        $params[] = $department_id;
    }
    
    if ($academic_year) {
        $sql .= " AND c.academic_year = ?";
        $params[] = $academic_year;
    }
    
    if ($term) {
        $sql .= " AND (c.term = ? OR c.term LIKE ?)";
        $params[] = $term;
        $params[] = '%' . $term . '%';
    }
    
    $sql .= " ORDER BY c.course_code ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enhance courses with syllabus status
    foreach ($courses as &$course) {
        // Determine syllabus status
        if (!$course['syllabus_id']) {
            $course['syllabus_status_text'] = 'Not Started';
            $course['syllabus_status_class'] = 'status-not-started';
        } else {
            switch ($course['syllabus_status']) {
                case 'draft':
                    $course['syllabus_status_text'] = 'Draft';
                    $course['syllabus_status_class'] = 'status-draft';
                    break;
                case 'submitted':
                    $course['syllabus_status_text'] = 'Submitted';
                    $course['syllabus_status_class'] = 'status-submitted';
                    break;
                case 'ph_review':
                    $course['syllabus_status_text'] = 'Under PH Review';
                    $course['syllabus_status_class'] = 'status-ph-review';
                    break;
                case 'ph_approved':
                    $course['syllabus_status_text'] = 'PH Approved';
                    $course['syllabus_status_class'] = 'status-ph-approved';
                    break;
                case 'dean_approved':
                    $course['syllabus_status_text'] = 'Approved';
                    $course['syllabus_status_class'] = 'status-approved';
                    break;
                case 'revision_requested':
                    $course['syllabus_status_text'] = 'Needs Revision';
                    $course['syllabus_status_class'] = 'status-revision';
                    break;
                default:
                    $course['syllabus_status_text'] = 'Not Started';
                    $course['syllabus_status_class'] = 'status-not-started';
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'courses' => $courses,
        'academic_year' => $academic_year,
        'term' => $term
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading courses: ' . $e->getMessage()]);
}
?>