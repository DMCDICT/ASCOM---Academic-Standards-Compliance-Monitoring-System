<?php
// test_captcha_system.php
// Test script to verify CAPTCHA system for inactive users

require_once 'session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';

session_start();

echo "<h2>CAPTCHA System Test</h2>";

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
            
            // Test the inactivity calculation
            $lastActivity = new DateTime($user['last_activity']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastActivity);
            $daysDiff = $timeDiff->days;
            
            echo "<h3>Inactivity Test:</h3>";
            echo "<p>Days since last activity: " . $daysDiff . "</p>";
            
            if ($daysDiff > 30) {
                echo "<p style='color: red; font-weight: bold;'>Status: Inactive (requires CAPTCHA)</p>";
                echo "<p style='color: blue;'>🔄 This user would be redirected to CAPTCHA verification</p>";
            } else {
                echo "<p style='color: green; font-weight: bold;'>Status: Active (no CAPTCHA required)</p>";
                echo "<p style='color: blue;'>🔄 This user can login normally</p>";
            }
            
            // Test making user inactive for CAPTCHA testing
            echo "<h3>CAPTCHA Testing:</h3>";
            echo "<p>To test the CAPTCHA system, you can manually set a user's last_activity to more than 30 days ago:</p>";
            echo "<p><strong>SQL Command:</strong></p>";
            echo "<code>UPDATE users SET last_activity = DATE_SUB(NOW(), INTERVAL 31 DAY) WHERE employee_no = '" . htmlspecialchars($_SESSION['employee_no']) . "';</code>";
            
            echo "<br><br><p><strong>Or test with a specific user:</strong></p>";
            echo "<form method='post'>";
            echo "<input type='text' name='test_username' placeholder='Enter username to test' style='padding: 8px; margin: 5px;'>";
            echo "<input type='submit' value='Test CAPTCHA' style='padding: 8px; margin: 5px;'>";
            echo "</form>";
            
            if ($_POST['test_username']) {
                $testUsername = $_POST['test_username'];
                $testQuery = "SELECT employee_no, last_activity FROM users WHERE institutional_email = ?";
                $testStmt = $conn->prepare($testQuery);
                $testStmt->bind_param("s", $testUsername);
                $testStmt->execute();
                $testResult = $testStmt->get_result();
                
                if ($testResult->num_rows > 0) {
                    $testUser = $testResult->fetch_assoc();
                    $testLastActivity = new DateTime($testUser['last_activity']);
                    $testTimeDiff = $now->diff($testLastActivity);
                    $testDaysDiff = $testTimeDiff->days;
                    
                    echo "<p><strong>Test User:</strong> " . htmlspecialchars($testUsername) . "</p>";
                    echo "<p><strong>Last Activity:</strong> " . $testUser['last_activity'] . "</p>";
                    echo "<p><strong>Days Inactive:</strong> " . $testDaysDiff . "</p>";
                    
                    if ($testDaysDiff > 30) {
                        echo "<p style='color: red;'>This user would require CAPTCHA verification</p>";
                        echo "<p><a href='captcha_verification.php?username=" . urlencode($testUsername) . "' target='_blank'>Test CAPTCHA Page</a></p>";
                    } else {
                        echo "<p style='color: green;'>This user would NOT require CAPTCHA verification</p>";
                    }
                } else {
                    echo "<p style='color: red;'>User not found</p>";
                }
            }
            
        } else {
            echo "<p style='color: red;'>❌ User not found in database</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Super admin - CAPTCHA testing not applicable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No employee number found in session</p>";
}

echo "<br><h3>CAPTCHA System Summary:</h3>";
echo "<ul>";
echo "<li>✅ Users inactive for more than 30 days are redirected to CAPTCHA</li>";
echo "<li>✅ CAPTCHA uses simple math problems (addition, subtraction, multiplication)</li>";
echo "<li>✅ CAPTCHA verification is session-based</li>";
echo "<li>✅ After CAPTCHA success, user is redirected back to login</li>";
echo "<li>✅ Username is pre-filled after CAPTCHA verification</li>";
echo "<li>✅ Only applies to regular users (not super admin)</li>";
echo "</ul>";

echo "<br><h3>Test the CAPTCHA System:</h3>";
echo "<ol>";
echo "<li>Create or find a user account that hasn't been used for 31+ days</li>";
echo "<li>Try to login with that account</li>";
echo "<li>You should be redirected to the CAPTCHA verification page</li>";
echo "<li>Solve the math problem and submit</li>";
echo "<li>You should be redirected back to login with username pre-filled</li>";
echo "<li>Complete the login process</li>";
echo "</ol>";

echo "<br><p><strong>Test complete!</strong></p>";
echo "<p><a href='user_login.php'>Go to User Login</a></p>";

$conn->close();
?> 