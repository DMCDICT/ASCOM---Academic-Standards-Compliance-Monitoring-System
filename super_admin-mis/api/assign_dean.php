<?php
// assign_dean.php - API to assign a dean to a department
// Only users with the Dean role (role_id = 2) can be assigned as dean

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)($_POST['department_id'] ?? 0);
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } elseif ($user_id <= 0) {
        $response['message'] = 'Please select a user';
    } else {
        // First, check if user exists and belongs to this department
        $checkUser = $conn->prepare("SELECT id, employee_no, first_name, last_name FROM users WHERE id = ? AND department_id = ?");
        $checkUser->bind_param("ii", $user_id, $department_id);
        $checkUser->execute();
        $userResult = $checkUser->get_result();
        
        if ($userResult->num_rows === 0) {
            $response['message'] = 'User must belong to this department first';
            $checkUser->close();
        } else {
            $userData = $userResult->fetch_assoc();
            $checkUser->close();
            
            // Check if user has the Dean role - check BOTH user_roles table AND users.role_id
            $checkRole = $conn->prepare("SELECT role_id FROM users WHERE id = ? AND (role_id = 2 OR id IN (SELECT user_id FROM user_roles WHERE role_id = 2))");
            $checkRole->bind_param("i", $user_id);
            $checkRole->execute();
            $roleResult = $checkRole->get_result();
            
            if ($roleResult->num_rows === 0) {
                $response['message'] = 'User must have Dean role to be assigned as department dean. Please assign the Dean role to this user first.';
                $checkRole->close();
            } else {
                $checkRole->close();
                
                // Update department's dean
                $updateDept = $conn->prepare("UPDATE departments SET dean_user_id = ? WHERE id = ?");
                $updateDept->bind_param("ii", $user_id, $department_id);
                
                if ($updateDept->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Dean assigned successfully! ' . $userData['first_name'] . ' ' . $userData['last_name'] . ' is now the dean of this department.';
                } else {
                    $response['message'] = 'Error: ' . $updateDept->error;
                }
                
                $updateDept->close();
            }
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();