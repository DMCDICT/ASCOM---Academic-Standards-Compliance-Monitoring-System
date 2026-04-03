<?php
header('Content-Type: application/json');

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isUserActive()) {
        throw new Exception('User not authenticated');
    }
    
    if (!empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin') {
        require_once 'super_admin_session_config.php';
        extendSuperAdminSession();
        
        echo json_encode([
            'success' => true,
            'message' => 'Super Admin session extended (unlimited)',
            'timestamp' => date('Y-m-d H:i:s'),
            'user_type' => 'super_admin',
            'session_type' => 'unlimited'
        ]);
        exit;
    }
    
    extendSession();
    
    if (isset($_SESSION['employee_no']) && $_SESSION['employee_no'] !== 'SUPER_ADMIN') {
        $conn = ascom_get_mysqli();
        $updateStmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE employee_no = ?");
        $updateStmt->bind_param("s", $_SESSION['employee_no']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Session extended successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'user_type' => 'regular_user',
        'session_type' => 'timed'
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
