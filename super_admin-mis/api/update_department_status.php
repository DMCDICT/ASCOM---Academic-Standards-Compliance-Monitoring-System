<?php
// update_department_status.php - API to toggle department status

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)$_POST['department_id'] ?? 0;
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } else {
        $stmt = $conn->prepare("UPDATE departments SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $department_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Department status updated';
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();