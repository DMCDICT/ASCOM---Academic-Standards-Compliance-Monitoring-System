<?php
// add_department.php - API to add new department

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name'] ?? '');
    $department_code = strtoupper(trim($_POST['department_code'] ?? ''));
    $color_code = trim($_POST['color_code'] ?? '#1976d2');
    
    // Validation
    if (empty($department_name)) {
        $response['message'] = 'Department name is required';
    } elseif (empty($department_code)) {
        $response['message'] = 'Department code is required';
    } elseif (strlen($department_code) > 10) {
        $response['message'] = 'Department code must be 10 characters or less';
    } else {
        // Check for duplicate
        $check = $conn->prepare("SELECT id FROM departments WHERE department_code = ?");
        $check->bind_param("s", $department_code);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $response['message'] = 'Department code already exists';
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (department_name, department_code, color_code, is_active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $department_name, $department_code, $color_code);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Department created successfully!';
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