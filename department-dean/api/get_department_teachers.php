<?php
// get_department_teachers.php
// Get teachers from a specific department for the Dean to assign

require_once '../includes/db_connection.php';

$department_id = $_GET['department_id'] ?? null;

header('Content-Type: application/json');

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

try {
    // Get all teachers from this department
    $stmt = $pdo->prepare("
        SELECT id, employee_no, first_name, last_name, title, email
        FROM users 
        WHERE department_id = ? AND is_active = 1
        ORDER BY first_name ASC
    ");
    $stmt->execute([$department_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Also check if any are already program heads
    $headStmt = $pdo->query("
        SELECT teacher_id, program_id 
        FROM program_heads 
        WHERE is_active = TRUE
    ");
    $heads = $headStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Mark teachers who are program heads
    foreach ($teachers as &$teacher) {
        if (isset($heads[$teacher['id']])) {
            $teacher['is_program_head'] = true;
            $teacher['head_program_id'] = $heads[$teacher['id']];
        } else {
            $teacher['is_program_head'] = false;
        }
    }
    
    echo json_encode([
        'success' => true, 
        'teachers' => $teachers
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
