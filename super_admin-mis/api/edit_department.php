<?php
// edit_department.php - API to edit department

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)$_POST['department_id'] ?? 0;
    $department_name = trim($_POST['department_name'] ?? '');
    $department_code = strtoupper(trim($_POST['department_code'] ?? ''));
    $color_code = trim($_POST['color_code'] ?? '#1976d2');
    
    // Validation
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } elseif (empty($department_name)) {
        $response['message'] = 'Department name is required';
    } elseif (empty($department_code)) {
        $response['message'] = 'Department code is required';
    } else {
        // Check for duplicate (excluding current)
        $check = $conn->prepare("SELECT id FROM departments WHERE department_code = ? AND id != ?");
        $check->bind_param("si", $department_code, $department_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $response['message'] = 'Department code already exists';
        } else {
            $stmt = $conn->prepare("UPDATE departments SET department_name = ?, department_code = ?, color_code = ? WHERE id = ?");
            $stmt->bind_param("sssi", $department_name, $department_code, $color_code, $department_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Department updated successfully!';
            } else {
                $response['message'] = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();