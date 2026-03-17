<?php
// force_update_inactive_users.php
// Force update all inactive users to offline status

require_once '../session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only Super Admin can run this script
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    require_once 'includes/db_connection.php';
    
    // Force update all users who haven't been active in the last 1 minute to offline
    $updateQuery = "UPDATE users SET online_status = 'offline' WHERE last_activity < DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND online_status = 'online' AND employee_no != 'SUPER_ADMIN'";
    $result = $conn->query($updateQuery);
    
    if ($result) {
        $affectedRows = $conn->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "Updated $affectedRows users to offline status",
            'affected_rows' => $affectedRows
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    
    $conn->close();
} catch (Exception $e) {
    error_log("Error in force_update_inactive_users: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 