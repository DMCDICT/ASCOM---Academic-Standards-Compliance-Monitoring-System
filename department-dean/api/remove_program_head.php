<?php
// remove_program_head.php
// Remove a teacher as program head from a program

require_once '../includes/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);
$program_id = $input['program_id'] ?? null;

header('Content-Type: application/json');

if (!$program_id) {
    echo json_encode(['success' => false, 'message' => 'Program ID is required']);
    exit;
}

try {
    $deactivate = $pdo->prepare("UPDATE program_heads SET is_active = FALSE WHERE program_id = ?");
    $deactivate->execute([$program_id]);
    
    echo json_encode(['success' => true, 'message' => 'Program head removed successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
