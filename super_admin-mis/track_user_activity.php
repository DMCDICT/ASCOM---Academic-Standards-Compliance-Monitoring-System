<?php
// track_user_activity.php
// Script to track and update user activity

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/includes/db_connection.php';

function updateUserActivity($employee_no) {
    global $conn;
    
    try {
        // Update last_activity to current timestamp
        $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("s", $employee_no);
        
        if ($updateStmt->execute()) {
            return true;
        } else {
            return false;
        }
        
        $updateStmt->close();
    } catch (Exception $e) {
        return false;
    }
}

// Auto-update activity for logged-in users
if (isset($_SESSION['employee_no'])) {
    updateUserActivity($_SESSION['employee_no']);
}
?> 