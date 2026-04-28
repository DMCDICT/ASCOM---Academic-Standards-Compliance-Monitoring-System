<?php
// get_syllabus_details.php - Get detailed syllabus data for review
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../../bootstrap/auth.php';

ascom_require_role('dean', dirname(__FILE__) . '/../../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';
    
    $syllabus_id = $_GET['syllabus_id'] ?? null;
    
    if (!$syllabus_id) {
        echo json_encode(['success' => false, 'message' => 'Syllabus ID is required']);
        exit;
    }
    
    // Get syllabus with full details
    $syllabusStmt = $pdo->prepare("
        SELECT 
            cs.*,
            c.course_code,
            c.course_title,
            c.units,
            c.year_level,
            c.term,
            c.academic_year,
            p.program_name,
            p.program_code,
            u.first_name,
            u.last_name,
            u.title,
            u.email,
            ph_u.first_name as ph_first_name,
            ph_u.last_name as ph_last_name,
            ph_u.title as ph_title,
            dean_u.first_name as dean_first_name,
            dean_u.last_name as dean_last_name
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        JOIN users u ON cs.teacher_id = u.id
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN program_heads ph ON ph.program_id = c.program_id AND ph.is_active = TRUE
        LEFT JOIN users ph_u ON ph.teacher_id = ph_u.id
        LEFT JOIN users dean_u ON cs.dean_reviewer_id = dean_u.id
        WHERE cs.id = ?
    ");
    $syllabusStmt->execute([$syllabus_id]);
    $syllabus = $syllabusStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$syllabus) {
        echo json_encode(['success' => false, 'message' => 'Syllabus not found']);
        exit;
    }
    
    // Format names
    $syllabus['teacher_name'] = ($syllabus['title'] ? $syllabus['title'] . ' ' : '') . $syllabus['first_name'] . ' ' . $syllabus['last_name'];
    $syllabus['ph_name'] = ($syllabus['ph_title'] ? $syllabus['ph_title'] . ' ' : '') . ($syllabus['ph_first_name'] ?? '') . ' ' . ($syllabus['ph_last_name'] ?? '');
    
    echo json_encode([
        'success' => true,
        'syllabus' => $syllabus
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading syllabus: ' . $e->getMessage()]);
}
?>