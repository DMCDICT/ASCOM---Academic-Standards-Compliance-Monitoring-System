<?php
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated as teacher
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated as teacher']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit();
}

$password = $input['password'] ?? '';
$targetRole = $input['target_role'] ?? '';

// Validate input
if (empty($password) || empty($targetRole)) {
    echo json_encode(['success' => false, 'error' => 'Password and target role are required']);
    exit();
}

// Validate target role (teacher can switch to dean, quality_assurance, or librarian)
if (!in_array($targetRole, ['dean', 'quality_assurance', 'librarian'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid target role']);
    exit();
}

try {
    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'User ID not found in session']);
        exit();
    }
    
    // Verify password and check if user has access to the target role
    $user = null;
    $roleInfo = null;
    
    if ($targetRole === 'dean') {
        // Check for dean role
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.first_name, u.last_name, u.middle_name, u.title,
                   d.department_code, d.department_name, d.color_code
            FROM users u
            LEFT JOIN departments d ON u.id = d.dean_user_id
            WHERE u.id = ? AND d.dean_user_id IS NOT NULL
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $roleInfo = [
                'role' => 'dean',
                'department_code' => $user['department_code'],
                'department_name' => $user['department_name'],
                'department_color' => $user['color_code']
            ];
        }
    } else {
        // Check for quality_assurance or librarian role
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.first_name, u.last_name, u.middle_name, u.title,
                   ur.role_name
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            WHERE u.id = ? AND ur.role_name = ? AND ur.is_active = 1
        ");
        $stmt->execute([$userId, $targetRole]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $roleInfo = [
                'role' => $targetRole,
                'department_code' => 'QA', // Default for QA
                'department_name' => $targetRole === 'quality_assurance' ? 'Quality Assurance' : 'Librarian',
                'department_color' => '#1976d2' // Default blue
            ];
        }
    }
    
    if (!$user) {
        $roleDisplay = ucfirst(str_replace('_', ' ', $targetRole));
        echo json_encode(['success' => false, 'error' => "You do not have access to the {$roleDisplay} role"]);
        exit();
    }
    
    // Verify password (using same method as login system)
    if ($password !== $user['password']) {
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        exit();
    }
    
    // Update session based on target role
    $_SESSION['teacher_logged_in'] = false;
    
    if ($targetRole === 'dean') {
        $_SESSION['dean_logged_in'] = true;
        $redirectUrl = '../department-dean/content.php';
        $message = 'Successfully switched to Department Dean role';
    } elseif ($targetRole === 'quality_assurance') {
        $_SESSION['admin_qa_logged_in'] = true;
        $redirectUrl = '../admin-quality_assurance/content.php';
        $message = 'Successfully switched to Quality Assurance role';
    } elseif ($targetRole === 'librarian') {
        $_SESSION['librarian_logged_in'] = true;
        $redirectUrl = '../librarian/content.php';
        $message = 'Successfully switched to Librarian role';
    }
    
    $_SESSION['selected_role'] = $roleInfo;
    
    // Store user info in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_middle_name'] = $user['middle_name'];
    $_SESSION['user_title'] = $user['title'];
    
    // Debug: Log session data
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'redirect_url' => $redirectUrl
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'An error occurred while switching roles']);
}
?>
