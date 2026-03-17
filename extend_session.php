<?php
// extend_session.php
// Endpoint to extend user session

header('Content-Type: application/json');

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is authenticated
    if (!isUserActive()) {
        throw new Exception('User not authenticated');
    }
    
    // Super Admin has unlimited sessions - no timeout restrictions
    if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
        // Use dedicated Super Admin session extension
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
    
    // For regular users, extend session normally
    extendSession();
    
    // Update user activity if employee_no is available and not Super Admin
    if (isset($_SESSION['employee_no']) && $_SESSION['employee_no'] !== 'SUPER_ADMIN') {
        require_once 'super_admin-mis/includes/db_connection.php';
        
        $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
        $updateStmt = $conn->prepare($updateQuery);
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