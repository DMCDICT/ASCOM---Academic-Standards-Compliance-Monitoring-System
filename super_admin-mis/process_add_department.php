<?php
// process_add_department.php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'department' => null];

// Check if departments table exists
$table_check = $conn->query("SHOW TABLES LIKE 'departments'");
if ($table_check->num_rows === 0) {
    // Create departments table if it doesn't exist
    $create_table = "CREATE TABLE departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        department_code VARCHAR(20) UNIQUE NOT NULL,
        department_name VARCHAR(100) UNIQUE NOT NULL,
        color_code VARCHAR(7) NOT NULL DEFAULT '#4A7DFF',
        dean_user_id INT(11) NULL,
        created_by VARCHAR(100) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table)) {
        $response['message'] = 'Failed to create departments table: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_code = trim($_POST['department_code'] ?? '');
    $department_name = trim($_POST['department_name'] ?? '');
    $color_code = trim($_POST['color_code'] ?? '#4A7DFF');

    // Validation
    if (empty($department_code) || empty($department_name)) {
        $response['message'] = 'Department code and name are required.';
    } elseif (strlen($department_code) < 2 || strlen($department_code) > 20) {
        $response['message'] = 'Department code must be between 2 and 20 characters.';
    } elseif (strlen($department_name) < 3 || strlen($department_name) > 100) {
        $response['message'] = 'Department name must be between 3 and 100 characters.';
    } else {
        // Check for duplicate department code
        $stmt_check = $conn->prepare("SELECT id FROM departments WHERE department_code = ? OR department_name = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("ss", $department_code, $department_name);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $response['message'] = 'Department code or name already exists.';
            } else {
                $stmt_check->close();
                
                // Insert new department
                $stmt_insert = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
                if ($stmt_insert) {
                    $created_by = 'Super Admin MIS';
                    $stmt_insert->bind_param("ssss", $department_code, $department_name, $color_code, $created_by);
                    
                    if ($stmt_insert->execute()) {
                        $new_department_id = $conn->insert_id;
                        
                        $response['success'] = true;
                        $response['message'] = 'Department "' . htmlspecialchars($department_name) . '" created successfully!';
                        $response['department'] = [
                            'id' => $new_department_id,
                            'code' => $department_code,
                            'name' => $department_name,
                            'color' => $color_code,
                            'dean' => 'N/A',
                            'programs' => 0,
                            'created_by' => $created_by
                        ];
                        
                        // Log activity
                        $activity_type = 'Department Creation';
                        $description = 'Created new department: ' . $department_name . ' (' . $department_code . ')';
                        $target_entity = 'Department';
                        $target_entity_id = $new_department_id;
                        
                        $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt_log) {
                            $logged_in_user_id = NULL;
                            $logged_in_username = 'Super Admin MIS';
                            $stmt_log->bind_param("issssi", $logged_in_user_id, $logged_in_username, $activity_type, $description, $target_entity, $target_entity_id);
                            $stmt_log->execute();
                            $stmt_log->close();
                        }
                    } else {
                        // Check if it's a duplicate entry error
                        if ($stmt_insert->errno === 1062) {
                            $response['message'] = 'Department code or name already exists.';
                        } else {
                            $response['message'] = 'Error creating department: ' . $stmt_insert->error;
                        }
                    }
                    $stmt_insert->close();
                } else {
                    $response['message'] = 'Database error: Could not prepare insert statement.';
                }
            }
        } else {
            $response['message'] = 'Database error: Could not prepare duplicate check statement.';
        }
    }
} else {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
    header("HTTP/1.1 405 Method Not Allowed");
    header("Allow: POST");
}

$conn->close();
echo json_encode($response);
exit;
?>