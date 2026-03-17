<?php
// test_user_activity_status.php
// Test script to check user activity status and debug why users aren't showing as "Online"

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>User Activity Status Test</h2>";

// Check if last_activity column exists
$checkColumnQuery = "SHOW COLUMNS FROM users LIKE 'last_activity'";
$checkResult = $conn->query($checkColumnQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    echo "<p style='color: green;'>✅ last_activity column exists</p>";
} else {
    echo "<p style='color: red;'>❌ last_activity column does not exist</p>";
    echo "<p>You need to add the last_activity column to the users table.</p>";
    echo "<p>SQL: ALTER TABLE users ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL;</p>";
    exit;
}

// Fetch all users with their activity data
$fetchUsersQuery = "SELECT 
    employee_no, 
    first_name, 
    last_name, 
    is_active, 
    last_activity,
    CASE
        WHEN last_activity >= NOW() - INTERVAL 15 MINUTE THEN 'Online'
        WHEN last_activity >= NOW() - INTERVAL 30 DAY THEN 'Active'
        ELSE 'Inactive'
    END as calculated_status
    FROM users 
    ORDER BY last_activity DESC";

$fetchUsersResult = $conn->query($fetchUsersQuery);

if (!$fetchUsersResult) {
    echo "<p style='color: red;'>❌ Error fetching users: " . $conn->error . "</p>";
    exit;
}

echo "<h3>Current User Activity Status:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Employee No.</th>";
echo "<th>Name</th>";
echo "<th>Is Active</th>";
echo "<th>Last Activity</th>";
echo "<th>Calculated Status</th>";
echo "<th>Time Since Activity</th>";
echo "</tr>";

$now = new DateTime();
$onlineCount = 0;
$activeCount = 0;
$inactiveCount = 0;

while ($row = $fetchUsersResult->fetch_assoc()) {
    $lastActivity = $row['last_activity'] ? new DateTime($row['last_activity']) : null;
    $timeSince = $lastActivity ? $now->diff($lastActivity) : null;
    
    $statusClass = '';
    switch ($row['calculated_status']) {
        case 'Online':
            $statusClass = 'color: green; font-weight: bold;';
            $onlineCount++;
            break;
        case 'Active':
            $statusClass = 'color: orange;';
            $activeCount++;
            break;
        case 'Inactive':
            $statusClass = 'color: red;';
            $inactiveCount++;
            break;
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
    echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
    echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
    echo "<td>" . ($row['last_activity'] ? $row['last_activity'] : 'Never') . "</td>";
    echo "<td style='$statusClass'>" . $row['calculated_status'] . "</td>";
    echo "<td>";
    if ($timeSince) {
        if ($timeSince->days > 0) {
            echo $timeSince->days . " days, " . $timeSince->h . " hours ago";
        } elseif ($timeSince->h > 0) {
            echo $timeSince->h . " hours, " . $timeSince->i . " minutes ago";
        } else {
            echo $timeSince->i . " minutes ago";
        }
    } else {
        echo "No activity recorded";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Status Summary:</h3>";
echo "<p><strong>Online:</strong> <span style='color: green; font-weight: bold;'>$onlineCount</span></p>";
echo "<p><strong>Active:</strong> <span style='color: orange;'>$activeCount</span></p>";
echo "<p><strong>Inactive:</strong> <span style='color: red;'>$inactiveCount</span></p>";

// Test activity update
echo "<h3>Test Activity Update:</h3>";
echo "<form method='post'>";
echo "<select name='test_employee_no'>";
$conn->query("SELECT employee_no, first_name, last_name FROM users WHERE is_active = 1 LIMIT 5");
$result = $conn->query("SELECT employee_no, first_name, last_name FROM users WHERE is_active = 1 LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "<option value='" . $row['employee_no'] . "'>" . $row['first_name'] . ' ' . $row['last_name'] . " (" . $row['employee_no'] . ")</option>";
}
echo "</select>";
echo "<input type='submit' name='test_update' value='Update Activity' style='margin-left: 10px; padding: 5px 10px;'>";
echo "</form>";

if ($_POST['test_update']) {
    $testEmployeeNo = $_POST['test_employee_no'];
    
    // Update the user's activity
    $updateQuery = "UPDATE users SET last_activity = NOW() WHERE employee_no = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $testEmployeeNo);
    
    if ($updateStmt->execute()) {
        echo "<p style='color: green;'>✅ Activity updated for employee: $testEmployeeNo</p>";
        echo "<p>Refresh this page to see the updated status.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update activity: " . $conn->error . "</p>";
    }
    
    $updateStmt->close();
}

// Check current time for reference
echo "<h3>Current Server Time:</h3>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Online Threshold:</strong> Within 15 minutes</p>";
echo "<p><strong>Active Threshold:</strong> Within 30 days</p>";

echo "<br><p><strong>Debug complete!</strong></p>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";
?> 