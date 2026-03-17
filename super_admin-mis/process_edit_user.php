<?php
// process_edit_user.php
// Handle edit user form submission

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['employee_no_original', 'employee_no', 'first_name', 'last_name', 'institutional_email', 'department_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Required field '$field' is missing or empty");
        }
    }

    $employee_no_original = trim($_POST['employee_no_original']);
    $employee_no = trim($_POST['employee_no']);
    $first_name = trim($_POST['first_name']);
    $middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $last_name = trim($_POST['last_name']);
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $institutional_email = trim($_POST['institutional_email']);
    $mobile_no = isset($_POST['mobile_no']) ? trim($_POST['mobile_no']) : '';
    $department_id = (int)$_POST['department_id'];
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate email format
    if (!filter_var($institutional_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check if email ends with @sccpag.edu.ph
    if (!str_ends_with($institutional_email, '@sccpag.edu.ph')) {
        throw new Exception("Email must end with @sccpag.edu.ph");
    }

    // Check if employee number already exists (excluding the current user)
    $checkEmployeeQuery = "SELECT id FROM users WHERE employee_no = ? AND employee_no != ?";
    $checkEmployeeStmt = $conn->prepare($checkEmployeeQuery);
    $checkEmployeeStmt->bind_param("ss", $employee_no, $employee_no_original);
    $checkEmployeeStmt->execute();
    $checkEmployeeResult = $checkEmployeeStmt->get_result();
    
    if ($checkEmployeeResult->num_rows > 0) {
        throw new Exception("Employee number already exists");
    }
    $checkEmployeeStmt->close();

    // Check if email already exists (excluding the current user)
    $checkEmailQuery = "SELECT id FROM users WHERE institutional_email = ? AND employee_no != ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("ss", $institutional_email, $employee_no_original);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();
    
    if ($checkEmailResult->num_rows > 0) {
        throw new Exception("Email address already exists");
    }
    $checkEmailStmt->close();

    // Start building the update query
    $updateFields = [
        "employee_no = ?",
        "first_name = ?",
        "middle_name = ?",
        "last_name = ?",
        "title = ?",
        "institutional_email = ?",
        "mobile_no = ?",
        "department_id = ?",
    ];
    
    $updateValues = [
        $employee_no,
        $first_name,
        $middle_name,
        $last_name,
        $title,
        $institutional_email,
        $mobile_no,
        $department_id,
    ];

    // Add password update if provided
    if (!empty($password)) {
        // For edit user, we want to save the password as-is (whether it's hashed or plain text)
        // This allows users to see their current password and update it if needed
        $updateFields[] = "password = ?";
        $updateValues[] = $password;
    }

    $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE employee_no = ?";
    $updateValues[] = $employee_no_original;

    // Debug: Log the query and values
    error_log('Update query: ' . $updateQuery);
    error_log('Update values: ' . json_encode($updateValues));
    error_log('Employee no original: ' . $employee_no_original);

    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param(str_repeat("s", count($updateValues)), ...$updateValues);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update user: " . $conn->error);
    }

    // Debug: Log affected rows and any errors
    error_log('Affected rows: ' . $updateStmt->affected_rows);
    if ($updateStmt->error) {
        error_log('MySQL error: ' . $updateStmt->error);
    }

    if ($updateStmt->affected_rows === 0) {
        // Check if the user actually exists
        $checkUserQuery = "SELECT id FROM users WHERE employee_no = ?";
        $checkUserStmt = $conn->prepare($checkUserQuery);
        $checkUserStmt->bind_param("s", $employee_no_original);
        $checkUserStmt->execute();
        $checkUserResult = $checkUserStmt->get_result();
        
        if ($checkUserResult->num_rows === 0) {
            throw new Exception("User with employee number '$employee_no_original' not found in database");
        } else {
            // User exists but no changes were made - this is actually fine!
            // It means the user submitted the form with the same values that are already in the database
            error_log('User exists but no changes detected. This is considered successful.');
            
            // Get current values to compare for logging purposes
            $currentQuery = "SELECT employee_no, first_name, middle_name, last_name, title, institutional_email, mobile_no, department_id FROM users WHERE employee_no = ?";
            $currentStmt = $conn->prepare($currentQuery);
            $currentStmt->bind_param("s", $employee_no_original);
            $currentStmt->execute();
            $currentResult = $currentStmt->get_result();
            $currentUser = $currentResult->fetch_assoc();
            
            error_log('Current user data: ' . json_encode($currentUser));
            error_log('Submitted data: ' . json_encode([
                'employee_no' => $employee_no,
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'title' => $title,
                'institutional_email' => $institutional_email,
                'mobile_no' => $mobile_no,
                'department_id' => $department_id
            ]));
            
            // Don't throw an error - this is actually successful
            // The user's data is already in the desired state
        }
        $checkUserStmt->close();
    }

    $updateStmt->close();

    // Return success response
    $response = [
        'success' => true,
        'message' => 'User account updated successfully'
    ];
    
    error_log('Edit user success response: ' . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    $error_response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    error_log('Edit user error response: ' . json_encode($error_response));
    http_response_code(400);
    echo json_encode($error_response);
}

$conn->close();
?> 