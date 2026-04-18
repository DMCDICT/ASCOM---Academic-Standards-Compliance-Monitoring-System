<?php
// get_department_data.php - API to get department data with related info

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $department_id = (int)$_GET['id'];
    
    if ($department_id <= 0) {
        $response['message'] = 'Invalid department';
    } else {
        // Get department basic info with dean
        $stmt = $conn->prepare("SELECT 
            d.*,
            u.first_name as dean_first_name,
            u.last_name as dean_last_name,
            u.employee_no as dean_employee_no,
            (SELECT COUNT(*) FROM programs p WHERE p.department_id = d.id) as program_count,
            (SELECT COUNT(*) FROM courses c WHERE c.department_id = d.id) as course_count,
            (SELECT COUNT(*) FROM users u WHERE u.department_id = d.id AND u.role_id = 4) as teacher_count
        FROM departments d 
        LEFT JOIN users u ON d.dean_user_id = u.id
        WHERE d.id = ?");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $dept = $result->fetch_assoc();
            
            // Build dean name
            $dept['dean_name'] = null;
            if ($dept['dean_first_name'] && $dept['dean_last_name']) {
                $dept['dean_name'] = $dept['dean_first_name'] . ' ' . $dept['dean_last_name'];
            }
            
            // Get programs
            $progStmt = $conn->prepare("SELECT * FROM programs WHERE department_id = ?");
            $progStmt->bind_param("i", $department_id);
            $progStmt->execute();
            $progResult = $progStmt->get_result();
            $programs = [];
            while ($prog = $progResult->fetch_assoc()) {
                $programs[] = $prog;
            }
            $dept['programs'] = $programs;
            $progStmt->close();
            
            // Get courses
            $courseStmt = $conn->prepare("SELECT * FROM courses WHERE department_id = ?");
            $courseStmt->bind_param("i", $department_id);
            $courseStmt->execute();
            $courseResult = $courseStmt->get_result();
            $courses = [];
            while ($course = $courseResult->fetch_assoc()) {
                $courses[] = $course;
            }
            $dept['courses'] = $courses;
            $courseStmt->close();
            
            // Get teachers
            $teacherStmt = $conn->prepare("SELECT id, employee_no, first_name, last_name, email FROM users WHERE department_id = ? AND role_id = 4");
            $teacherStmt->bind_param("i", $department_id);
            $teacherStmt->execute();
            $teacherResult = $teacherStmt->get_result();
            $teachers = [];
            while ($teacher = $teacherResult->fetch_assoc()) {
                $teachers[] = $teacher;
            }
            $dept['teachers'] = $teachers;
            $teacherStmt->close();
            
            $response['success'] = true;
            $response['data'] = $dept;
        } else {
            $response['message'] = 'Department not found';
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
$conn->close();