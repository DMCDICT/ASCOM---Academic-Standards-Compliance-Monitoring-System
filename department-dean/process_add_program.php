<?php
// process_add_program.php
// Handles AJAX requests to add new programs to the database.

// Suppress any output that might interfere with JSON response
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Set error handler to catch any PHP errors
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    require_once dirname(__FILE__) . '/../session_config.php';
    require_once dirname(__FILE__) . '/includes/db_connection.php';

    // Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    // Configure session before starting
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}



// Debug: log session information
file_put_contents('../login_debug.txt', 'process_add_program.php - session_id=' . session_id() . ' dean_logged_in=' . ($_SESSION['dean_logged_in'] ?? 'NOT_SET') . ' selected_role=' . json_encode($_SESSION['selected_role'] ?? 'NOT_SET') . PHP_EOL, FILE_APPEND);

// Check if user is logged in as dean - more flexible check
$isDean = false;

// Check multiple ways user could be authenticated as dean
if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    $isDean = true;
    file_put_contents('../login_debug.txt', 'process_add_program.php - dean_logged_in found' . PHP_EOL, FILE_APPEND);
} elseif (isset($_SESSION['selected_role']['role_name']) && $_SESSION['selected_role']['role_name'] === 'dean') {
    $isDean = true;
    file_put_contents('../login_debug.txt', 'process_add_program.php - selected_role dean found' . PHP_EOL, FILE_APPEND);
} elseif (isset($_SESSION['selected_role']['type']) && $_SESSION['selected_role']['type'] === 'dean') {
    $isDean = true;
    file_put_contents('../login_debug.txt', 'process_add_program.php - selected_role type dean found' . PHP_EOL, FILE_APPEND);
} elseif (isset($_SESSION['user_id'])) {
    // Check if user is assigned as dean in departments table
    try {
        $deptQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$_SESSION['user_id']]);
        if ($deptStmt->rowCount() > 0) {
            $isDean = true;
            file_put_contents('../login_debug.txt', 'process_add_program.php - dean found in departments table' . PHP_EOL, FILE_APPEND);
        }
    } catch (Exception $e) {
        // Continue with other checks
        file_put_contents('../login_debug.txt', 'process_add_program.php - error checking departments: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}

if (!$isDean) {
    file_put_contents('../login_debug.txt', 'process_add_program.php - AUTHORIZATION FAILED - session data: ' . json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Dean role required']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get the dean's department ID from session or selected_role
$deanDepartmentId = $_SESSION['dean_department_id'] ?? null;
$deanUserId = $_SESSION['user_id'] ?? null;

// If not in session, get it from selected_role
if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_id'])) {
    $deanDepartmentId = $_SESSION['selected_role']['department_id'];
}

// If still not found, get it from department_code in selected_role
if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_code'])) {
    try {
        $deptCode = $_SESSION['selected_role']['department_code'];
        $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$deptCode]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        if ($deptResult) {
            $deanDepartmentId = $deptResult['id'];
            file_put_contents('../login_debug.txt', 'process_add_program.php - Found department ID ' . $deanDepartmentId . ' for code ' . $deptCode . PHP_EOL, FILE_APPEND);
        }
    } catch (Exception $e) {
        // Log error but continue
        error_log("Error getting department ID from code: " . $e->getMessage());
    }
}

// If still not found, get it from departments table using dean's user_id
if (!$deanDepartmentId && isset($_SESSION['user_id'])) {
    try {
        $deptQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$_SESSION['user_id']]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        if ($deptResult) {
            $deanDepartmentId = $deptResult['id'];
        }
    } catch (Exception $e) {
        // Log error but continue
        error_log("Error getting department ID: " . $e->getMessage());
    }
}

// Debug logging for department ID
file_put_contents('../login_debug.txt', 'process_add_program.php - Final department ID: ' . ($deanDepartmentId ?? 'NOT_FOUND') . PHP_EOL, FILE_APPEND);

if (!$deanDepartmentId) {
    file_put_contents('../login_debug.txt', 'process_add_program.php - DEPARTMENT ID NOT FOUND - session data: ' . json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Department ID not found']);
    exit();
}

// Validate and sanitize input
$programCode = trim($_POST['program_code'] ?? '');
$programName = trim($_POST['program_name'] ?? '');
$major = trim($_POST['major'] ?? ''); // Optional field

// Validation
if (empty($programCode) || empty($programName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Program code and program name are required']);
    exit();
}

// Check if program code already exists in this department
$checkQuery = "SELECT id FROM programs WHERE program_code = ? AND department_id = ?";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$programCode, $deanDepartmentId]);

if ($checkStmt->rowCount() > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Program code already exists in this department']);
    exit();
}

// Get the department color code
$deptColorQuery = "SELECT color_code FROM departments WHERE id = ?";
$deptColorStmt = $pdo->prepare($deptColorQuery);
$deptColorStmt->execute([$deanDepartmentId]);
$deptColorResult = $deptColorStmt->fetch(PDO::FETCH_ASSOC);

if (!$deptColorResult) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit();
}

$departmentColorCode = $deptColorResult['color_code'];

// Insert new program
$insertQuery = "INSERT INTO programs (program_code, program_name, major, color_code, department_id, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$insertStmt = $pdo->prepare($insertQuery);

if ($insertStmt->execute([$programCode, $programName, $major, $departmentColorCode, $deanDepartmentId, $deanUserId])) {
    $newProgramId = $pdo->lastInsertId();
    
    // Log the activity (if activity_logs table exists)
    try {
        $username = $_SESSION['username'] ?? $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name'] ?? 'Unknown';
        $activityDescription = "Added new program: $programName ($programCode)";
        $logQuery = "INSERT INTO activity_logs (username, description, department_id, activity_timestamp) VALUES (?, ?, ?, NOW())";
        $logStmt = $pdo->prepare($logQuery);
        $logStmt->execute([$username, $activityDescription, $deanDepartmentId]);
    } catch (Exception $e) {
        // If activity_logs table doesn't exist, just continue
        error_log("Activity logging failed: " . $e->getMessage());
    }
    
    // Send notifications to Super Admin, Librarian, Quality Assurance, and Teachers/Faculty
    try {
        // Get dean name from database
        $deanName = 'Department Dean';
        try {
            $deanQuery = "SELECT first_name, last_name, employee_no FROM users WHERE id = ?";
            $deanStmt = $pdo->prepare($deanQuery);
            $deanStmt->execute([$deanUserId]);
            $deanData = $deanStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($deanData) {
                if (!empty($deanData['first_name']) && !empty($deanData['last_name'])) {
                    $deanName = trim($deanData['first_name'] . ' ' . $deanData['last_name']);
                } elseif (!empty($deanData['employee_no'])) {
                    $deanName = 'Dean (' . $deanData['employee_no'] . ')';
                }
            }
        } catch (Exception $e) {
            error_log("Failed to get dean name: " . $e->getMessage());
        }
        
        $departmentName = $_SESSION['selected_role']['department_name'] ?? 'Department';
        
        // Get department name for notification
        $deptNameQuery = "SELECT department_name FROM departments WHERE id = ?";
        $deptNameStmt = $pdo->prepare($deptNameQuery);
        $deptNameStmt->execute([$deanDepartmentId]);
        $deptResult = $deptNameStmt->fetch(PDO::FETCH_ASSOC);
        $departmentName = $deptResult['department_name'] ?? 'Department';
        
        // Create notification for Super Admin
        $superAdminNotification = [
            'title' => 'New Program Created',
            'message' => "$departmentName Dean has created a new program: $programName ($programCode)",
            'type' => 'info',
            'sender_id' => $deanUserId,
            'sender_name' => $deanName,
            'sender_role' => 'dean',
            'recipient_type' => 'super_admin',
            'recipient_id' => null
        ];
        
        // Create notification for Librarian
        $librarianNotification = [
            'title' => 'New Program Created',
            'message' => "$departmentName Dean has created a new program: $programName ($programCode). Please update library resources accordingly.",
            'type' => 'info',
            'sender_id' => $deanUserId,
            'sender_name' => $deanName,
            'sender_role' => 'dean',
            'recipient_type' => 'librarian',
            'recipient_id' => null
        ];
        
        // Create notification for Quality Assurance
        $qaNotification = [
            'title' => 'New Program Created',
            'message' => "$departmentName Dean has created a new program: $programName ($programCode). Please review for quality assurance.",
            'type' => 'info',
            'sender_id' => $deanUserId,
            'sender_name' => $deanName,
            'sender_role' => 'dean',
            'recipient_type' => 'quality_assurance',
            'recipient_id' => null
        ];
        
        // Insert notifications
        $notificationStmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, sender_id, sender_name, sender_role, recipient_type, recipient_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Insert Super Admin notification
        $notificationStmt->execute([
            $superAdminNotification['title'],
            $superAdminNotification['message'],
            $superAdminNotification['type'],
            $superAdminNotification['sender_id'],
            $superAdminNotification['sender_name'],
            $superAdminNotification['sender_role'],
            $superAdminNotification['recipient_type'],
            $superAdminNotification['recipient_id']
        ]);
        
        // Insert Librarian notification
        $notificationStmt->execute([
            $librarianNotification['title'],
            $librarianNotification['message'],
            $librarianNotification['type'],
            $librarianNotification['sender_id'],
            $librarianNotification['sender_name'],
            $librarianNotification['sender_role'],
            $librarianNotification['recipient_type'],
            $librarianNotification['recipient_id']
        ]);
        
        // Insert Quality Assurance notification
        $notificationStmt->execute([
            $qaNotification['title'],
            $qaNotification['message'],
            $qaNotification['type'],
            $qaNotification['sender_id'],
            $qaNotification['sender_name'],
            $qaNotification['sender_role'],
            $qaNotification['recipient_type'],
            $qaNotification['recipient_id']
        ]);
        
        // Get all teachers/faculty in the same department
        $teachersQuery = "
            SELECT u.id, u.first_name, u.last_name, u.employee_no
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            WHERE ur.role_name = 'teacher' 
            AND ur.is_active = 1
            AND u.department_id = ?
        ";
        $teachersStmt = $pdo->prepare($teachersQuery);
        $teachersStmt->execute([$deanDepartmentId]);
        $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Send notification to each teacher
        foreach ($teachers as $teacher) {
            $teacherNotification = [
                'title' => 'New Program Created',
                'message' => "$departmentName Dean has created a new program: $programName ($programCode). This program is now available for your department.",
                'type' => 'info',
                'sender_id' => $deanUserId,
                'sender_name' => $deanName,
                'sender_role' => 'dean',
                'recipient_type' => 'teacher',
                'recipient_id' => $teacher['id']
            ];
            
            $notificationStmt->execute([
                $teacherNotification['title'],
                $teacherNotification['message'],
                $teacherNotification['type'],
                $teacherNotification['sender_id'],
                $teacherNotification['sender_name'],
                $teacherNotification['sender_role'],
                $teacherNotification['recipient_type'],
                $teacherNotification['recipient_id']
            ]);
        }
        
        // Send confirmation notification to the Dean who created the program
        $deanNotification = [
            'title' => 'Program Created Successfully',
            'message' => "You have successfully created a new program: $programName ($programCode). Notifications have been sent to Super Admin, Librarian, Quality Assurance, and department teachers.",
            'type' => 'success',
            'sender_id' => null,
            'sender_name' => 'System',
            'sender_role' => 'system',
            'recipient_type' => 'dean',
            'recipient_id' => $deanUserId
        ];
        
        $notificationStmt->execute([
            $deanNotification['title'],
            $deanNotification['message'],
            $deanNotification['type'],
            $deanNotification['sender_id'],
            $deanNotification['sender_name'],
            $deanNotification['sender_role'],
            $deanNotification['recipient_type'],
            $deanNotification['recipient_id']
        ]);
        
        file_put_contents('../login_debug.txt', 'process_add_program.php - Notifications sent successfully for program: ' . $programName . ' to ' . count($teachers) . ' teachers' . PHP_EOL, FILE_APPEND);
        
    } catch (Exception $e) {
        // Log notification error but don't fail the program creation
        error_log("Notification creation failed: " . $e->getMessage());
        file_put_contents('../login_debug.txt', 'process_add_program.php - Notification error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        
        // Try to create a simple notification without sender details
        try {
            $simpleNotification = [
                'title' => 'New Program Created',
                'message' => "A new program has been created: $programName ($programCode)",
                'type' => 'info',
                'sender_id' => null,
                'sender_name' => 'Department Dean',
                'sender_role' => 'dean',
                'recipient_type' => 'super_admin',
                'recipient_id' => null
            ];
            
            $simpleStmt = $pdo->prepare("
                INSERT INTO notifications (title, message, type, sender_id, sender_name, sender_role, recipient_type, recipient_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $simpleStmt->execute([
                $simpleNotification['title'],
                $simpleNotification['message'],
                $simpleNotification['type'],
                $simpleNotification['sender_id'],
                $simpleNotification['sender_name'],
                $simpleNotification['sender_role'],
                $simpleNotification['recipient_type'],
                $simpleNotification['recipient_id']
            ]);
            
            file_put_contents('../login_debug.txt', 'process_add_program.php - Simple notification created successfully' . PHP_EOL, FILE_APPEND);
            
        } catch (Exception $e2) {
            file_put_contents('../login_debug.txt', 'process_add_program.php - Simple notification also failed: ' . $e2->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }
    
    // Return success response with program data
    $response = [
        'success' => true,
        'message' => "Program '$programName' has been successfully created!",
        'program' => [
            'id' => $newProgramId,
            'code' => $programCode,
            'name' => $programName,
            'major' => $major,
            'color' => $departmentColorCode,
            'courses' => 0,
            'faculty' => 0,
            'created_by' => $username ?? 'Unknown'
        ]
    ];
    
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create program. Please try again.']);
}

// Clean any unwanted output and send the JSON response
ob_end_flush();

} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 