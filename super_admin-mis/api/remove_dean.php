<?php
// remove_dean.php - API to remove a dean from a department

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_id = (int)$_POST['department_id'] ?? 0;
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } else {
        // Get current dean user ID
        $getDean = $conn->prepare("SELECT dean_user_id FROM departments WHERE id = ?");
        $getDean->bind_param("i", $department_id);
        $getDean->execute();
        $deanResult = $getDean->get_result();
        
        if ($deanResult->num_rows > 0) {
            $deanRow = $deanResult->fetch_assoc();
            $oldDeanId = $deanRow['dean_user_id'];
            
            // Remove dean from department
            $updateDept = $conn->prepare("UPDATE departments SET dean_user_id = NULL WHERE id = ?");
            $updateDept->bind_param("i", $department_id);
            
            // Change user's role back to teacher (role_id = 4)
            if ($oldDeanId) {
                $updateUser = $conn->prepare("UPDATE users SET role_id = 4, role = 'teacher' WHERE id = ?");
                $updateUser->bind_param("i", $oldDeanId);
                $updateUser->execute();
                $updateUser->close();
            }
            
            if ($updateDept->execute()) {
                $response['success'] = true;
                $response['message'] = 'Dean removed successfully!';
            } else {
                $response['message'] = 'Error: ' . $conn->error;
            }
            
            $updateDept->close();
        } else {
            $response['message'] = 'Department not found';
        }
        $getDean->close();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
$conn->close();