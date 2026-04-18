<?php
// assign_dean.php - API to assign a dean to a department

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)$_POST['department_id'] ?? 0;
    $user_id = (int)$_POST['user_id'] ?? 0;
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } elseif ($user_id <= 0) {
        $response['message'] = 'Please select a teacher';
    } else {
        // First, check if user is a teacher/faculty in this department
        $checkUser = $conn->prepare("SELECT id, role_id FROM users WHERE id = ? AND department_id = ?");
        $checkUser->bind_param("ii", $user_id, $department_id);
        $checkUser->execute();
        $userResult = $checkUser->get_result();
        
        if ($userResult->num_rows === 0) {
            $response['message'] = 'User must be assigned to this department first';
        } else {
            // Update user's role to dean (role_id = 2) and set as department dean
            $updateUser = $conn->prepare("UPDATE users SET role_id = 2, role = 'dean' WHERE id = ?");
            $updateUser->bind_param("i", $user_id);
            
            // Update department's dean
            $updateDept = $conn->prepare("UPDATE departments SET dean_user_id = ? WHERE id = ?");
            $updateDept->bind_param("ii", $user_id, $department_id);
            
            if ($updateUser->execute() && $updateDept->execute()) {
                $response['success'] = true;
                $response['message'] = 'Dean assigned successfully!';
            } else {
                $response['message'] = 'Error: ' . $conn->error;
            }
            
            $updateUser->close();
            $updateDept->close();
        }
        $checkUser->close();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();