<?php
// get_program_head.php
// Get the program head for a specific program

require_once '../includes/db_connection.php';

$program_id = $_GET['program_id'] ?? null;

header('Content-Type: application/json');

if (!$program_id) {
    echo json_encode(['success' => false, 'message' => 'Program ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            ph.id,
            ph.program_id,
            ph.teacher_id,
            ph.assigned_at,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.title,
            u.email
        FROM program_heads ph
        JOIN users u ON ph.teacher_id = u.id
        WHERE ph.program_id = ? AND ph.is_active = TRUE
    ");
    $stmt->execute([$program_id]);
    $head = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'head' => $head ?: null
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
