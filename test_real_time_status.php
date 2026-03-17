<?php
// test_real_time_status.php
// Test script to verify real-time status updates

require_once 'session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';

session_start();

echo "<h2>Real-Time Status Test</h2>";

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
            
            // Test the new status calculation
            $lastActivity = new DateTime($user['last_activity']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastActivity);
            $minutesDiff = $timeDiff->i + ($timeDiff->h * 60) + ($timeDiff->days * 24 * 60);
            $hoursDiff = $timeDiff->h + ($timeDiff->days * 24);
            
            echo "<h3>Status Calculation Test:</h3>";
            echo "<p>Minutes since last activity: " . $minutesDiff . "</p>";
            echo "<p>Hours since last activity: " . $hoursDiff . "</p>";
            
            if ($minutesDiff <= 15) {
                echo "<p style='color: green; font-weight: bold;'>Status: Online (within 15 minutes)</p>";
            } elseif ($hoursDiff <= 720) { // 30 days * 24 hours = 720 hours
                echo "<p style='color: orange; font-weight: bold;'>Status: Active (within 30 days)</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Status: Inactive (more than 30 days)</p>";
            }
            
            // Update activity
            $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $_SESSION['employee_no']);
            
            if ($updateStmt->execute()) {
                echo "<p style='color: green;'>✅ Activity updated successfully!</p>";
                echo "<p style='color: blue;'>🔄 Your status should now show as 'Online' for the next 15 minutes</p>";
                echo "<p style='color: blue;'>🔄 The user list will refresh every 30 seconds to show real-time updates</p>";
                
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
        echo "<p style='color: blue;'>ℹ️ Super admin - activity tracking skipped</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No employee number found in session</p>";
}

echo "<br><h3>Real-Time Updates Summary:</h3>";
echo "<ul>";
echo "<li>✅ Activity updates every 30 seconds (reduced from 1 minute)</li>";
echo "<li>✅ User list refreshes every 30 seconds for real-time status</li>";
echo "<li>✅ 'Online' status threshold: 15 minutes</li>";
echo "<li>✅ 'Active' status threshold: 30 days (1 month)</li>";
echo "<li>✅ 'Inactive' status threshold: More than 30 days</li>";
echo "<li>✅ Visual refresh indicator shows when updates are happening</li>";
echo "<li>✅ Session extension every 30 seconds (reduced from 1 minute)</li>";
echo "</ul>";

echo "<br><p><strong>Test complete!</strong> Now go to the User Account Management page to see the real-time updates in action.</p>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";

$conn->close();
?> 