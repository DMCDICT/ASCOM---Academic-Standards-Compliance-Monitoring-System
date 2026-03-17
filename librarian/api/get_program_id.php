<?php
// get_program_id.php
// Get program ID from program code

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);
$programCode = $input['program_code'] ?? '';

if (empty($programCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Program code is required'
    ]);
    exit;
}

try {
    $query = "SELECT id FROM programs WHERE program_code = ? LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$programCode]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($program) {
        echo json_encode([
            'success' => true,
            'program_id' => $program['id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Program not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error getting program ID: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

