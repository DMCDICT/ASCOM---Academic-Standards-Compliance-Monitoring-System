<?php
// switch_role.php
// API endpoint to handle role switching with password verification (Librarian)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
require_once dirname(__FILE__) . '/../../session_config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Include database connection
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    // Trim password on input (matching login form behavior)
    $password = trim($input['password'] ?? '');
    $targetRole = $input['target_role'] ?? '';
    
    if (empty($password) || empty($targetRole)) {
        throw new Exception('Password and target role are required.');
    }
    
    // Verify user is authenticated
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated.');
    }
    
    $userId = $_SESSION['user_id'];
    
    // Verify password and check if user has access to the target role
    // Use the same approach as department-dean/api/switch_role.php
    $user = null;
    $roleInfo = null;
    
    if ($targetRole === 'teacher') {
        // Check for teacher role - teacher role comes from users.role_id = 4, not user_roles table
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.role_id, u.department_id, u.first_name, u.last_name, u.middle_name, u.title,
                   d.department_code, d.department_name, d.color_code
            FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            WHERE u.id = ? AND u.role_id = 4 AND u.department_id IS NOT NULL AND u.is_active = 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $roleInfo = [
                'type' => 'teacher',
                'department_code' => $user['department_code'],
                'department_name' => $user['department_name'],
                'department_color' => $user['color_code']
            ];
        }
    } elseif ($targetRole === 'dean') {
        // Check for dean role (from departments.dean_user_id)
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.first_name, u.last_name, u.middle_name, u.title,
                   d.department_code, d.department_name, d.color_code
            FROM users u
            LEFT JOIN departments d ON u.id = d.dean_user_id
            WHERE u.id = ? AND d.dean_user_id IS NOT NULL AND u.is_active = 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $roleInfo = [
                'type' => 'dean',
                'department_code' => $user['department_code'],
                'department_name' => $user['department_name'],
                'department_color' => $user['color_code']
            ];
        }
    } else {
        // Check for quality_assurance role (stored in user_roles table)
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.first_name, u.last_name, u.middle_name, u.title,
                   ur.role_name
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            WHERE u.id = ? AND ur.role_name = ? AND ur.is_active = 1 AND u.is_active = 1
        ");
        $stmt->execute([$userId, $targetRole]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $roleInfo = [
                'type' => $targetRole,
                'department_code' => 'QA',
                'department_name' => $targetRole === 'quality_assurance' ? 'Quality Assurance' : 'Librarian',
                'department_color' => '#1976d2'
            ];
        }
    }
    
    if (!$user) {
        $roleDisplay = ucfirst(str_replace('_', ' ', $targetRole));
        throw new Exception("You do not have access to the {$roleDisplay} role.");
    }
    
    // Verify password using same method as login system (direct comparison, no trimming of DB password)
    // Login form trims password on client side, so we only trim the input here
    if ($password !== $user['password']) {
        // Debug logging for password mismatch
        throw new Exception('Invalid password.');
    }
    
    // Set session based on target role
    $_SESSION['librarian_logged_in'] = false;
    
    switch ($targetRole) {
        case 'teacher':
            $_SESSION['selected_role'] = $roleInfo;
            $_SESSION['teacher_logged_in'] = true;
            $redirectUrl = '../teachers/content.php';
            break;
            
        case 'dean':
            $_SESSION['selected_role'] = $roleInfo;
            $_SESSION['dean_logged_in'] = true;
            $redirectUrl = '../department-dean/content.php';
            break;
            
        case 'quality_assurance':
            $_SESSION['selected_role'] = $roleInfo;
            $_SESSION['qa_logged_in'] = true;
            $redirectUrl = '../admin-quality_assurance/content.php';
            break;
            
        default:
            throw new Exception('Invalid role specified.');
    }
    
    // Store user info in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_middle_name'] = $user['middle_name'] ?? '';
    $_SESSION['user_title'] = $user['title'] ?? '';
    
    echo json_encode([
        'success' => true,
        'message' => 'Role switched successfully.',
        'redirect_url' => $redirectUrl,
        'new_role' => $targetRole
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
