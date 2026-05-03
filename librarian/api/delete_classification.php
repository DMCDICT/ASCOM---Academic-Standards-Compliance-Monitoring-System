<?php
// delete_classification.php
// API endpoint to delete a classification

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';
require_once dirname(dirname(__FILE__)) . '/../session_config.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($input['id'] ?? 0);
    
    if (empty($id)) {
        throw new Exception('Classification ID is required');
    }
    
    // Check if classification exists
    $checkStmt = $pdo->prepare("SELECT id, name FROM classifications WHERE id = ?");
    $checkStmt->execute([$id]);
    $classification = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$classification) {
        throw new Exception('Classification not found');
    }
    
    // Delete the classification
    $deleteStmt = $pdo->prepare("DELETE FROM classifications WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    $response['success'] = true;
    $response['message'] = 'Classification deleted successfully';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;