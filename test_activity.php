<?php
// test_activity.php
// Test script to check user activity tracking

require_once 'session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';

session_start();

echo "<h2>User Activity Test</h2>";

// Check current session
echo "<h3>Current Session:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
if (isset($_SESSION['employee_no'])) {
    echo "<p style='color: green;'>✅ Employee number found: " . $_SESSION['employee_no'] . "</p>";
    
    // Check current activity in database
    if ($_SESSION['employee_no'] !== 'SUPER_ADMIN') {
        $query = "SELECT employee_no, last_activity FROM users WHERE employee_no = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_SESSION['employee_no']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p>Current last_activity: " . ($user['last_activity'] ? $user['last_activity'] : 'NULL') . "</p>";
            
            // Update activity
            $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $_SESSION['employee_no']);
            
            if ($updateStmt->execute()) {
                echo "<p style='color: green;'>✅ Activity updated successfully!</p>";
                
                // Check updated activity
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                echo "<p>Updated last_activity: " . $user['last_activity'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update activity: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found in database</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Super admin - no database update needed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No employee number in session</p>";
}

echo "<br><p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";
?> 