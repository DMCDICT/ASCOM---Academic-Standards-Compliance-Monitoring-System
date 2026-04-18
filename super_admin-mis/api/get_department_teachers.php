<?php
// get_department_teachers.php
// API endpoint to fetch teachers from a specific department

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'teachers' => []];

// Support both department_id and dept_code for backward compatibility
if (isset($_GET['department_id'])) {
    $department_id = (int)$_GET['department_id'];
    
    if ($department_id > 0) {
        $stmt = $conn->prepare("SELECT id, employee_no, first_name, last_name, email FROM users WHERE department_id = ? AND (role_id = 4 OR role = 'teacher') AND is_active = 1 ORDER BY first_name ASC");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $response['teachers'][] = $row;
        }
        
        $response['success'] = true;
        $stmt->close();
    }
} elseif (isset($_GET['dept_code'])) {
    // Original implementation
    $deptCode = $_GET['dept_code'];
    
    $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("s", $deptCode);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows > 0) {
        $deptRow = $deptResult->fetch_assoc();
        $deptId = $deptRow['id'];
        
        $teachersQuery = "SELECT id, employee_no, first_name, last_name, title, institutional_email FROM users WHERE department_id = ? AND role_id = 4 AND is_active = 1 ORDER BY first_name ASC";
        $teachersStmt = $conn->prepare($teachersQuery);
        $teachersStmt->bind_param("i", $deptId);
        $teachersStmt->execute();
        $teachersResult = $teachersStmt->get_result();
        
        while ($row = $teachersResult->fetch_assoc()) {
            $response['teachers'][] = $row;
        }
        
        $response['success'] = true;
        $teachersStmt->close();
    }
    $deptStmt->close();
}

echo json_encode($response);
$conn->close(); 