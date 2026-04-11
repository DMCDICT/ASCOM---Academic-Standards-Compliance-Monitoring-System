<?php
// get_current_dean.php
// API endpoint to fetch the current dean of a department

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
    // Get current dean info
    $deanQuery = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.title,
            u.employee_no
        FROM 
            departments d
        LEFT JOIN 
            users u ON d.dean_user_id = u.id
        WHERE 
            d.department_code = ?
    ";
    
    $deanStmt = $conn->prepare($deanQuery);
    $deanStmt->bind_param("s", $deptCode);
    $deanStmt->execute();
    $deanResult = $deanStmt->get_result();
    
    if ($deanResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Department not found'
        ]);
        exit;
    }
    
    $deanRow = $deanResult->fetch_assoc();
    
    if (!$deanRow['id']) {
        // No dean assigned
        echo json_encode([
            'success' => true,
            'has_dean' => false,
            'dean_name' => 'No dean assigned'
        ]);
    } else {
        // Format the name with title
        $displayName = $deanRow['title'] ? $deanRow['title'] . ' ' . $deanRow['first_name'] . ' ' . $deanRow['last_name'] : $deanRow['first_name'] . ' ' . $deanRow['last_name'];
        
        echo json_encode([
            'success' => true,
            'has_dean' => true,
            'dean_name' => $displayName,
            'dean_id' => $deanRow['id'],
            'employee_no' => $deanRow['employee_no']
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
