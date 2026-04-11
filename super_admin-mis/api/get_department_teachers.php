<?php
// get_department_teachers.php
// API endpoint to fetch teachers from a specific department

require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if department code is provided
if (!isset($_GET['dept_code']) || empty($_GET['dept_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Department code is required'
    ]);
    exit;
}

$deptCode = $_GET['dept_code'];

try {
    // Get department ID first
    $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("s", $deptCode);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Department not found'
        ]);
        exit;
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $deptId = $deptRow['id'];
    
    // Get teachers from this department (role_id = 4 for Teacher), including the dean
    $teachersQuery = "
        SELECT 
            u.id,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.title,
            u.institutional_email,
            u.mobile_no,
            u.created_at,
            0 as total_units
        FROM 
            users u
        WHERE 
            u.department_id = ? 
            AND u.role_id = 4
            AND u.is_active = 1
        ORDER BY 
            u.last_name ASC, u.first_name ASC
    ";
    
    $teachersStmt = $conn->prepare($teachersQuery);
    $teachersStmt->bind_param("i", $deptId);
    $teachersStmt->execute();
    $teachersResult = $teachersStmt->get_result();
    
    $teachers = [];
    while ($row = $teachersResult->fetch_assoc()) {
        $teachers[] = [
            'id' => $row['id'],
            'employee_no' => $row['employee_no'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'title' => $row['title'],
            'institutional_email' => $row['institutional_email'],
            'mobile_no' => $row['mobile_no'],
            'created_at' => $row['created_at'],
            'total_units' => $row['total_units']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'teachers' => $teachers,
        'count' => count($teachers)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 