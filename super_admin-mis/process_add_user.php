<?php
// process_add_user.php
ob_start(); // Only one ob_start() needed, right after <?php
require_once __DIR__ . '/includes/db_connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Debug: Log the request
error_log("process_add_user.php called with method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_no = trim($_POST['employee_no'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? ''); 
    $last_name = trim($_POST['last_name'] ?? '');
    $name_prefix = trim($_POST['title'] ?? ''); 
    $institutional_email = trim($_POST['institutional_email'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? ''); 
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role_id = $_POST['role_id'] ?? '4'; // Default to Teacher (4) if not set
    $department_id = $_POST['department_id'] ?? NULL;

    if ($department_id === '') {
        $department_id = NULL;
    } else {
        $department_id = (int)$department_id;
    }

    // --- Input Validation ---
    if (empty($employee_no) || empty($first_name) || empty($last_name) || empty($institutional_email) || empty($password) || empty($role_id)) {
        $response['message'] = 'All required fields must be filled.';
    } elseif (!empty($confirm_password) && $password !== $confirm_password) {
        $response['message'] = 'Password and Confirm Password do not match.';
    } elseif (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long.';
    } elseif (!filter_var($institutional_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid institutional email format.';
    } elseif (!empty($mobile_no) && !preg_match('/^[0-9\-\(\)\s]+$/', $mobile_no)) {
        $response['message'] = 'Invalid mobile number format. Only numbers, hyphens, parentheses, and spaces are allowed.';
    }

    // --- Check for Duplicates (Employee No. and Institutional Email) ---
    if ($response['message'] == '') {
        // Check if users table has employee_no column
        $check_employee_no = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_no'");
        if ($check_employee_no->num_rows > 0) {
            $stmt_check_duplicate = $conn->prepare("SELECT id FROM users WHERE employee_no = ? OR institutional_email = ?");
        } else {
            $stmt_check_duplicate = $conn->prepare("SELECT id FROM users WHERE email = ?");
        }
        
        if ($stmt_check_duplicate) {
            if ($check_employee_no->num_rows > 0) {
                $stmt_check_duplicate->bind_param("ss", $employee_no, $institutional_email);
            } else {
                $stmt_check_duplicate->bind_param("s", $institutional_email);
            }
            $stmt_check_duplicate->execute();
            $check_result = $stmt_check_duplicate->get_result();

            if ($check_result->num_rows > 0) {
                if ($check_employee_no->num_rows > 0) {
                    $stmt_check_emp_no = $conn->prepare("SELECT id FROM users WHERE employee_no = ?");
                    if ($stmt_check_emp_no) { 
                        $stmt_check_emp_no->bind_param("s", $employee_no);
                        $stmt_check_emp_no->execute();
                        if ($stmt_check_emp_no->get_result()->num_rows > 0) {
                            $response['message'] = 'Employee No. already exists.';
                        } else {
                            $response['message'] = 'Institutional Email already exists.';
                        }
                        $stmt_check_emp_no->close();
                    }
                } else {
                    $response['message'] = 'Email already exists.';
                }
            }
            $stmt_check_duplicate->close();
        } else {
            $response['message'] = 'Database error during duplicate check preparation: ' . $conn->error;
        }
    }

        // --- If no validation errors, proceed with insert ---
    if ($response['message'] == '') {
        $created_by = 'Super Admin MIS'; 

        // Debug: Check database structure
        error_log("Checking database structure...");
        $check_employee_no = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_no'");
        error_log("employee_no column exists: " . ($check_employee_no->num_rows > 0 ? "YES" : "NO"));
        
        if ($check_employee_no->num_rows > 0) {
            // New structure with employee_no and institutional_email
            error_log("Using new structure for insert");
            $stmt_insert = $conn->prepare("
                INSERT INTO users 
                (employee_no, first_name, middle_name, last_name, name_prefix, institutional_email, mobile_no, password, role_id, department_id, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt_insert) {
                $stmt_insert->bind_param("ssssssssiss",
                    $employee_no, $first_name, $middle_name, $last_name, $name_prefix, 
                    $institutional_email, $mobile_no, $password, $role_id, $department_id, $created_by
                );
            } else {
                error_log("Failed to prepare insert statement: " . $conn->error);
            }
        } else {
            // Old structure - use email field
            error_log("Using old structure for insert");
            $stmt_insert = $conn->prepare("
                INSERT INTO users 
                (email, password, role, department_id, first_name, last_name, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");

            if ($stmt_insert) {
                $role_name = 'teacher'; // Default role name
                $stmt_insert->bind_param("ssssss", 
                    $institutional_email, $password, $role_name, $department_id, $first_name, $last_name
                );
            } else {
                error_log("Failed to prepare insert statement: " . $conn->error);
            }
        }

        if ($stmt_insert) {
            error_log("Attempting to execute insert statement...");
            if ($stmt_insert->execute()) {
                error_log("Insert successful! New user ID: " . $conn->insert_id);
                $response['success'] = true;
                $response['message'] = 'User account for ' . htmlspecialchars($first_name . ' ' . $last_name) . ' (' . htmlspecialchars($employee_no) . ') created successfully!';
            } else {
                error_log("Insert failed: " . $stmt_insert->error);
                $response['message'] = 'Error creating user account: ' . $stmt_insert->error;
            }

            // Log User Creation Activity
            $newly_created_user_id = $conn->insert_id;
            $logged_in_user_id = NULL; 
            $logged_in_username = 'Super Admin MIS'; 
            $activity_type = 'User Creation';
            $description = 'Created new user: ' . $name_prefix . ' ' . $first_name . ' ' . $last_name . ' (EMP: ' . $employee_no . ') with role: Teacher';
            
            if ($department_id !== NULL) {
                $dept_name_query = $conn->prepare("SELECT department_name FROM departments WHERE id = ?");
                if ($dept_name_query) {
                    $dept_name_query->bind_param("i", $department_id);
                    $dept_name_query->execute();
                    $dept_result = $dept_name_query->get_result();
                    $dept_name = $dept_result->fetch_assoc()['department_name'] ?? 'N/A';
                    $dept_name_query->close();
                    $description .= ' for Department: ' . $dept_name;
                }
            }
            
            $target_entity = 'User';
            $target_entity_id = $newly_created_user_id; 

            $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt_log) {
                $stmt_log->bind_param("issssi", $logged_in_user_id, $logged_in_username, $activity_type, $description, $target_entity, $target_entity_id);
                $stmt_log->execute();
                $stmt_log->close();
            }
        } else {
            $response['message'] = 'Error creating user account: ' . ($stmt_insert ? $stmt_insert->error : 'Statement preparation failed');
        }
        if ($stmt_insert) {
            $stmt_insert->close();
        }
    }
} else {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
    header("HTTP/1.1 405 Method Not Allowed");
    header("Allow: POST");
}

$conn->close();

// Capture any output and include in response
$buffered_output = ob_get_clean();
if (!empty($buffered_output)) {
    $response['debug_output'] = $buffered_output;
    $response['message'] = (empty($response['message']) ? 'Unexpected PHP output detected. ' : $response['message'] . ' ') . 'Check "debug_output" field in Network tab.';
    $response['success'] = false;
}

error_log("Final response: " . json_encode($response));
echo json_encode($response); 
exit;
?>