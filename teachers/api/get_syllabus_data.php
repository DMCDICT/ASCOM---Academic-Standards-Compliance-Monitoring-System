<?php
// get_syllabus_data.php - Get syllabus data for a course
require_once dirname(__FILE__, 3) . '/session_config.php';
require_once dirname(__FILE__, 3) . '/bootstrap/auth.php';

ascom_require_role('teacher', dirname(__FILE__, 3) . '/../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__, 3) . '/../super_admin-mis/includes/db_connection.php';
    
    $course_id = $_GET['course_id'] ?? null;
    $teacher_id = $_SESSION['user_id'];
    
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Course ID is required']);
        exit;
    }
    
    // Get current academic year and term from session or default
    $academic_year = $_SESSION['selected_role']['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
    $term = $_SESSION['selected_role']['term'] ?? '1st Semester';
    
    // Get syllabus
    $stmt = $pdo->prepare("
        SELECT * FROM course_syllabi 
        WHERE course_id = ? AND teacher_id = ? AND academic_year = ? AND term = ?
    ");
    $stmt->execute([$course_id, $teacher_id, $academic_year, $term]);
    $syllabus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($syllabus) {
        echo json_encode([
            'success' => true,
            'syllabus' => $syllabus
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No syllabus found for this course'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading syllabus: ' . $e->getMessage()]);
}
?>