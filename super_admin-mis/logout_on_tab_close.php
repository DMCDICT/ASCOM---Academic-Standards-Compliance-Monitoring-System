<?php
// logout_on_tab_close.php
// Handles automatic logout when users close their tab

session_start();
header('Content-Type: application/json');

// Debug logging
$log_file = '../tab_close_debug.txt';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($log_file, "[$timestamp] Tab close logout request received\n", FILE_APPEND);

// Include database connection
require_once './includes/db_connection.php';

// Get the employee number from the request
$raw_input = file_get_contents('php://input');
file_put_contents($log_file, "[$timestamp] Raw input: $raw_input\n", FILE_APPEND);

$data = json_decode($raw_input, true);
$employee_no = $data['employee_no'] ?? null;

// Check if this is a Super Admin session
$is_super_admin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;
file_put_contents($log_file, "[$timestamp] Is Super Admin: " . ($is_super_admin ? 'Yes' : 'No') . "\n", FILE_APPEND);

if (!$employee_no) {
    file_put_contents($log_file, "[$timestamp] ERROR: Employee number not provided\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Employee number not provided']);
    exit;
}

file_put_contents($log_file, "[$timestamp] Processing logout for employee: $employee_no\n", FILE_APPEND);

try {
    // Update user's online status to offline
    $update_status_sql = "UPDATE users SET online_status = 'offline', last_activity = NOW() WHERE employee_no = ?";
    $update_status_stmt = $conn->prepare($update_status_sql);
    $update_status_stmt->bind_param("s", $employee_no);
    
    if ($update_status_stmt->execute()) {
        // For Super Admin, don't destroy the session, just update status
        if (!$is_super_admin) {
            session_destroy();
        }
        
        file_put_contents($log_file, "[$timestamp] SUCCESS: User $employee_no status updated to offline\n", FILE_APPEND);
        
        echo json_encode([
            'success' => true, 
            'message' => $is_super_admin ? 'Super Admin status updated to offline' : 'User logged out successfully due to tab close',
            'employee_no' => $employee_no,
            'is_super_admin' => $is_super_admin
        ]);
    } else {
        file_put_contents($log_file, "[$timestamp] ERROR: Failed to update user status for $employee_no\n", FILE_APPEND);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update user status'
        ]);
    }
    
    $update_status_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 