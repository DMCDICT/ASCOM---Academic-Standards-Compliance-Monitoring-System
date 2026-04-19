<?php
header('Content-Type: application/json');
error_reporting(0);

require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$termId = intval($data['term_id'] ?? 0);

if ($termId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid term ID']);
    exit;
}

try {
    $result = $conn->query("DELETE FROM school_terms WHERE id = $termId");
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Term deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete term']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();