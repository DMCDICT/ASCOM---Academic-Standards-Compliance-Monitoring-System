<?php
// process_add_user.php - Direct database connection to debug

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

require_once dirname(__DIR__) . '/bootstrap/database.php';

$conn = ascom_get_mysqli();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_no = trim($_POST['employee_no'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name_prefix = trim($_POST['title'] ?? ''); 
    $institutional_email = trim($_POST['institutional_email'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? ''); 
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = $_POST['role_id'] ?? '4';
    $department_id = $_POST['department_id'] ?? null;

    if ($department_id === '' || $department_id === null) {
        $department_id = null;
    } else {
        $department_id = (int)$department_id;
    }

    // Validation
    // Role-based department requirement validation
    // Role IDs: 1=super_admin, 2=dean, 3=teacher, 4=qa, 5=librarian
    // Department required for: 2 (dean), 3 (teacher)
    // Department optional for: 4 (qa), 5 (librarian)
    $departmentRequiredRoles = [2, 3];
    $departmentOptionalRoles = [4, 5];
    $roleIdInt = (int)$role_id;
    
    if (empty($employee_no)) {
        $response['message'] = 'Employee No. is required.';
    } elseif (empty($first_name)) {
        $response['message'] = 'First Name is required.';
    } elseif (empty($last_name)) {
        $response['message'] = 'Last Name is required.';
    } elseif (empty($institutional_email)) {
        $response['message'] = 'Institutional Email is required.';
    } elseif (!filter_var($institutional_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
    } elseif (empty($password)) {
        $response['message'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
    } elseif (empty($role_id)) {
        $response['message'] = 'Role is required.';
    } elseif (in_array($roleIdInt, $departmentRequiredRoles) && (empty($department_id) || $department_id === null)) {
        $response['message'] = 'Department is required for Dean and Teacher roles.';
    }

    // Check duplicates
    if ($response['message'] == '') {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE employee_no = ? OR email = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("ss", $employee_no, $institutional_email);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $response['message'] = 'Employee No. or Email already exists.';
            }
            $stmt_check->close();
        } else {
            $response['message'] = 'Database error: ' . $conn->error;
        }
    }

    // Insert user
    if ($response['message'] == '') {
        $username = explode('@', $institutional_email)[0];
        
        $role_name = 'teacher';
        if ($role_id == '1') $role_name = 'super_admin';
        elseif ($role_id == '2') $role_name = 'dean';
        elseif ($role_id == '3') $role_name = 'teacher';
        elseif ($role_id == '4') $role_name = 'qa';
        elseif ($role_id == '5') $role_name = 'librarian';

        $stmt_insert = $conn->prepare("
            INSERT INTO users 
            (employee_no, username, password, first_name, last_name, title, email, mobile_no, role, role_id, department_id, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");

        if ($stmt_insert) {
            $stmt_insert->bind_param("sssssssssii",
                $employee_no, $username, $password, $first_name, $last_name, 
                $name_prefix, $institutional_email, $mobile_no, $role_name, $role_id, $department_id
            );
            
            if ($stmt_insert->execute()) {
                $response['success'] = true;
                $response['message'] = 'User account created successfully!';
            } else {
                $response['message'] = 'Error: ' . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $response['message'] = 'Prepare error: ' . $conn->error;
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();

echo json_encode($response);
