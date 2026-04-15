<?php
// assign_program_head.php
// Assign a teacher as program head for a program

require_once '../includes/db_connection.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$program_id = $input['program_id'] ?? null;
$teacher_id = $input['teacher_id'] ?? null;

header('Content-Type: application/json');

// Validate input
if (!$program_id || !$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Program and teacher are required']);
    exit;
}

try {
    // Check if teacher is already a head of another program
    $checkExisting = $pdo->prepare("
        SELECT id, program_id FROM program_heads 
        WHERE teacher_id = ? AND is_active = TRUE
    ");
    $checkExisting->execute([$teacher_id]);
    $existingHead = $checkExisting->fetch();
    
    if ($existingHead) {
        echo json_encode([
            'success' => false, 
            'message' => 'This teacher is already assigned as program head for another program. Please remove them first.'
        ]);
        exit;
    }
    
    // Check if program already has a head
    $checkProgram = $pdo->prepare("
        SELECT id FROM program_heads 
        WHERE program_id = ? AND is_active = TRUE
    ");
    $checkProgram->execute([$program_id]);
    $currentHead = $checkProgram->fetch();
    
    if ($currentHead) {
        // Deactivate existing head first
        $deactivate = $pdo->prepare("UPDATE program_heads SET is_active = FALSE WHERE program_id = ?");
        $deactivate->execute([$program_id]);
    }
    
    // Get the dean's user ID from session (we'll use teacher_id as assigned_by for now)
    $assigned_by = $teacher_id; // In real implementation, get from session
    
    // Insert new program head
    $insert = $pdo->prepare("
        INSERT INTO program_heads (program_id, teacher_id, assigned_by) 
        VALUES (?, ?, ?)
    ");
    $insert->execute([$program_id, $teacher_id, $assigned_by]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Program head assigned successfully'
    ]);
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false, 
            'message' => 'This teacher is already assigned as a program head for another program.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
