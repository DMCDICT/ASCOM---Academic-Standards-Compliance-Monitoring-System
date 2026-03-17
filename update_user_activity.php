<?php
// update_user_activity.php
// Update user's last activity timestamp and online status

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Super Admin should never be logged out automatically
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
    // Just update last activity for Super Admin, don't change online status
    try {
        require_once 'super_admin-mis/includes/db_connection.php';
        
        $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = 'SUPER_ADMIN'";
        $conn->query($updateQuery);
        $conn->close();
    } catch (Exception $e) {
        error_log("Error updating Super Admin activity: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true, 'message' => 'Super Admin activity updated']);
    exit;
}

// Get user info
$employee_no = $_SESSION['employee_no'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$employee_no || $employee_no === 'SUPER_ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {
    require_once 'super_admin-mis/includes/db_connection.php';
    
    // Update user's activity and online status
    try {
        $updateQuery = "UPDATE users SET last_activity = NOW(), online_status = 'online' WHERE employee_no = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $employee_no);
        $updateStmt->execute();
        $updateStmt->close();
        
        // More aggressive: check for users who haven't been active for more than 2 minutes and mark them as offline
        $offlineQuery = "UPDATE users SET online_status = 'offline' WHERE last_activity < DATE_SUB(NOW(), INTERVAL 2 MINUTE) AND online_status = 'online' AND employee_no != 'SUPER_ADMIN'";
        $conn->query($offlineQuery);
        
        // Also check for users with no activity in the last 1 minute and mark them as offline
        $veryInactiveQuery = "UPDATE users SET online_status = 'offline' WHERE last_activity < DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND online_status = 'online' AND employee_no != 'SUPER_ADMIN'";
        $conn->query($veryInactiveQuery);
        
        echo json_encode(['success' => true, 'message' => 'Activity updated']);
    } catch (Exception $e) {
        // If new columns don't exist, just update last_activity
        $fallbackQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
        $fallbackStmt = $conn->prepare($fallbackQuery);
        $fallbackStmt->bind_param("s", $employee_no);
        $fallbackStmt->execute();
        $fallbackStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Activity updated (fallback)']);
    }
    
    $conn->close();
} catch (Exception $e) {
    error_log("Error updating user activity: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
