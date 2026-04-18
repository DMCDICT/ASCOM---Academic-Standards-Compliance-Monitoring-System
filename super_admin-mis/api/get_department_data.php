<?php
// get_department_data.php - API to get department data

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $department_id = (int)$_GET['id'];
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } else {
        $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['success'] = true;
            $response['data'] = $result->fetch_assoc();
        } else {
            $response['message'] = 'Department not found';
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
$conn->close();