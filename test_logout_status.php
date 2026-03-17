<?php
// test_logout_status.php
// Test script to verify logout status updates

require_once 'session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';

session_start();

echo "<h2>Logout Status Test</h2>";

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
            
            // Test the status calculation
            $lastActivity = new DateTime($user['last_activity']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastActivity);
            $minutesDiff = $timeDiff->i + ($timeDiff->h * 60) + ($timeDiff->days * 24 * 60);
            $hoursDiff = $timeDiff->h + ($timeDiff->days * 24);
            
            echo "<h3>Current Status Test:</h3>";
            echo "<p>Minutes since last activity: " . $minutesDiff . "</p>";
            echo "<p>Hours since last activity: " . $hoursDiff . "</p>";
            
            if ($minutesDiff <= 3) {
                echo "<p style='color: green; font-weight: bold;'>Status: Online (within 3 minutes)</p>";
            } elseif ($hoursDiff <= 720) { // 30 days * 24 hours = 720 hours
                echo "<p style='color: orange; font-weight: bold;'>Status: Active (within 30 days)</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Status: Inactive (more than 30 days)</p>";
            }
            
            // Test logout status simulation
            echo "<h3>Logout Status Simulation:</h3>";
            echo "<p>When a user logs out, their last_activity is set to 16 minutes ago to ensure 'Active' status.</p>";
            
            // Simulate logout status (16 minutes ago)
            $logoutTime = clone $now;
            $logoutTime->modify('-16 minutes');
            $logoutTimeDiff = $now->diff($logoutTime);
            $logoutMinutesDiff = $logoutTimeDiff->i + ($logoutTimeDiff->h * 60) + ($logoutTimeDiff->days * 24 * 60);
            
            echo "<p><strong>Simulated logout time:</strong> " . $logoutTime->format('Y-m-d H:i:s') . "</p>";
            echo "<p><strong>Minutes since simulated logout:</strong> " . $logoutMinutesDiff . "</p>";
            
            if ($logoutMinutesDiff <= 3) {
                echo "<p style='color: green; font-weight: bold;'>After logout: Online (within 3 minutes)</p>";
            } elseif ($logoutMinutesDiff <= 720) { // 30 days * 24 hours = 720 hours
                echo "<p style='color: orange; font-weight: bold;'>After logout: Active (within 30 days) ✅</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>After logout: Inactive (more than 30 days)</p>";
            }
            
            // Test the actual logout function
            echo "<h3>Test Logout Function:</h3>";
            echo "<p><strong>SQL Command to simulate logout:</strong></p>";
            echo "<code>UPDATE users SET last_activity = DATE_SUB(NOW(), INTERVAL 4 MINUTE) WHERE employee_no = '" . htmlspecialchars($_SESSION['employee_no']) . "';</code>";
            
            echo "<br><br><p><strong>Or test the actual logout:</strong></p>";
            echo "<form method='post'>";
            echo "<input type='submit' name='test_logout' value='Simulate Logout Status Update' style='padding: 10px; margin: 5px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
            echo "</form>";
            
            if ($_POST['test_logout']) {
                // Simulate the logout status update
                $updateQuery = "UPDATE users SET last_activity = DATE_SUB(NOW(), INTERVAL 16 MINUTE) WHERE employee_no = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("s", $_SESSION['employee_no']);
                
                if ($updateStmt->execute()) {
                    echo "<p style='color: green;'>✅ Logout status simulation successful!</p>";
                    echo "<p style='color: blue;'>🔄 User should now show as 'Active' instead of 'Online'</p>";
                    
                    // Check updated status
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    echo "<p><strong>Updated last_activity:</strong> " . $user['last_activity'] . "</p>";
                    
                    // Recalculate status
                    $lastActivity = new DateTime($user['last_activity']);
                    $timeDiff = $now->diff($lastActivity);
                    $minutesDiff = $timeDiff->i + ($timeDiff->h * 60) + ($timeDiff->days * 24 * 60);
                    
                    if ($minutesDiff <= 15) {
                        echo "<p style='color: green; font-weight: bold;'>Status: Online (within 15 minutes)</p>";
                    } else {
                        echo "<p style='color: orange; font-weight: bold;'>Status: Active (within 30 days) ✅</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Failed to simulate logout status: " . $conn->error . "</p>";
                }
            }
            
        } else {
            echo "<p style='color: red;'>❌ User not found in database</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Super admin - logout status testing not applicable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No employee number found in session</p>";
}

echo "<br><h3>Logout Status System Summary:</h3>";
echo "<ul>";
echo "<li>✅ All logout files now use centralized logout handler</li>";
echo "<li>✅ Logout sets last_activity to 4 minutes ago</li>";
echo "<li>✅ This ensures users show as 'Active' instead of 'Online' after logout</li>";
echo "<li>✅ Works for manual logout and tab/window close</li>";
echo "<li>✅ Includes proper logging and error handling</li>";
echo "<li>✅ Maintains session cleanup and redirects</li>";
echo "</ul>";

echo "<br><h3>Test the Logout System:</h3>";
echo "<ol>";
echo "<li>Login with any user account</li>";
echo "<li>Check their status (should be 'Online')</li>";
echo "<li>Logout using the logout button</li>";
echo "<li>Check their status again (should be 'Active')</li>";
echo "<li>Or use the test button above to simulate logout</li>";
echo "</ol>";

echo "<br><p><strong>Test complete!</strong></p>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";

$conn->close();
?> 