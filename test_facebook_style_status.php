<?php
// test_facebook_style_status.php
// Test script to verify Facebook-style online status system

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Facebook-Style Online Status Test</h2>";

// Check if new columns exist
$columns = ['online_status', 'last_login', 'last_logout'];
foreach ($columns as $column) {
    $checkQuery = "SHOW COLUMNS FROM users LIKE '$column'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ $column column exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $column column does not exist</p>";
        echo "<p>You need to run the setup script first.</p>";
        echo "<p><a href='super_admin-mis/setup_activity_tracking.php'>Run Setup Script</a></p>";
        exit;
    }
}

// Fetch all users with their new status data
$fetchUsersQuery = "SELECT 
    employee_no, 
    first_name, 
    last_name, 
    is_active, 
    online_status,
    last_login,
    last_logout,
    last_activity,
    CASE
        WHEN online_status = 'online' THEN 'Online'
        WHEN last_logout IS NOT NULL AND last_logout >= NOW() - INTERVAL 30 DAY THEN 'Active'
        WHEN last_activity IS NOT NULL AND last_activity >= NOW() - INTERVAL 30 DAY THEN 'Active'
        ELSE 'Inactive'
    END as calculated_status
    FROM users 
    ORDER BY online_status DESC, last_login DESC";

$fetchUsersResult = $conn->query($fetchUsersQuery);

if (!$fetchUsersResult) {
    echo "<p style='color: red;'>❌ Error fetching users: " . $conn->error . "</p>";
    exit;
}

echo "<h3>Current User Status (Facebook-Style):</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Employee No.</th>";
echo "<th>Name</th>";
echo "<th>Is Active</th>";
echo "<th>Online Status</th>";
echo "<th>Last Login</th>";
echo "<th>Last Logout</th>";
echo "<th>Calculated Status</th>";
echo "<th>Status Logic</th>";
echo "</tr>";

$now = new DateTime();
$onlineCount = 0;
$activeCount = 0;
$inactiveCount = 0;

while ($row = $fetchUsersResult->fetch_assoc()) {
    $lastLogin = $row['last_login'] ? new DateTime($row['last_login']) : null;
    $lastLogout = $row['last_logout'] ? new DateTime($row['last_logout']) : null;
    
    $statusClass = '';
    $statusLogic = '';
    
    switch ($row['calculated_status']) {
        case 'Online':
            $statusClass = 'color: green; font-weight: bold;';
            $statusLogic = 'Currently logged in (online_status = online)';
            $onlineCount++;
            break;
        case 'Active':
            $statusClass = 'color: orange;';
            $statusLogic = 'Logged out within 30 days';
            $activeCount++;
            break;
        case 'Inactive':
            $statusClass = 'color: red;';
            $statusLogic = 'No activity for more than 30 days';
            $inactiveCount++;
            break;
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
    echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
    echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
    echo "<td>" . htmlspecialchars($row['online_status'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['last_login'] ? $row['last_login'] : 'Never') . "</td>";
    echo "<td>" . ($row['last_logout'] ? $row['last_logout'] : 'Never') . "</td>";
    echo "<td style='$statusClass'>" . $row['calculated_status'] . "</td>";
    echo "<td style='font-size: 12px;'>" . $statusLogic . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Status Summary:</h3>";
echo "<p><strong>Online:</strong> <span style='color: green; font-weight: bold;'>$onlineCount</span> (currently logged in)</p>";
echo "<p><strong>Active:</strong> <span style='color: orange;'>$activeCount</span> (logged out within 30 days)</p>";
echo "<p><strong>Inactive:</strong> <span style='color: red;'>$inactiveCount</span> (no activity for more than 30 days)</p>";

// Test login simulation
echo "<h3>Test Login Simulation:</h3>";
echo "<form method='post'>";
echo "<select name='test_employee_no'>";
$result = $conn->query("SELECT employee_no, first_name, last_name FROM users WHERE is_active = 1 LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "<option value='" . $row['employee_no'] . "'>" . $row['first_name'] . ' ' . $row['last_name'] . " (" . $row['employee_no'] . ")</option>";
}
echo "</select>";
echo "<input type='submit' name='test_login' value='Simulate Login' style='margin-left: 10px; padding: 5px 10px;'>";
echo "<input type='submit' name='test_logout' value='Simulate Logout' style='margin-left: 10px; padding: 5px 10px;'>";
echo "</form>";

if ($_POST['test_login']) {
    $testEmployeeNo = $_POST['test_employee_no'];
    
    // Simulate login
    $updateQuery = "UPDATE users SET online_status = 'online', last_login = NOW(), last_activity = NOW() WHERE employee_no = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $testEmployeeNo);
    
    if ($updateStmt->execute()) {
        echo "<p style='color: green;'>✅ Login simulation successful for employee: $testEmployeeNo</p>";
        echo "<p style='color: blue;'>🔄 User should now show as 'Online'</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to simulate login: " . $conn->error . "</p>";
    }
}

if ($_POST['test_logout']) {
    $testEmployeeNo = $_POST['test_employee_no'];
    
    // Simulate logout
    $updateQuery = "UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $testEmployeeNo);
    
    if ($updateStmt->execute()) {
        echo "<p style='color: green;'>✅ Logout simulation successful for employee: $testEmployeeNo</p>";
        echo "<p style='color: blue;'>🔄 User should now show as 'Active'</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to simulate logout: " . $conn->error . "</p>";
    }
}

// Check current time for reference
echo "<h3>Current Server Time:</h3>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Online Status:</strong> Based on actual login/logout (Facebook-style)</p>";
echo "<p><strong>Active Threshold:</strong> Within 30 days of last logout</p>";
echo "<p><strong>Inactive Threshold:</strong> More than 30 days since last activity</p>";

echo "<br><p><strong>Facebook-Style Status System:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>Online:</strong> User is currently logged in (online_status = 'online')</li>";
echo "<li>✅ <strong>Active:</strong> User logged out within 30 days</li>";
echo "<li>✅ <strong>Inactive:</strong> No activity for more than 30 days</li>";
echo "<li>✅ Real-time status updates based on actual login/logout events</li>";
echo "<li>✅ No more time-based activity thresholds for 'Online' status</li>";
echo "</ul>";

echo "<br><p><strong>Test complete!</strong></p>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";

$conn->close();
?> 