<?php
// update_current_users_online.php
// Script to update all users who have recent activity to show as online

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Update Current Users to Online Status</h2>";

try {
    // Update users who have activity in the last 5 minutes to show as online
    $updateQuery = "UPDATE users SET online_status = 'online' WHERE last_activity >= NOW() - INTERVAL 5 MINUTE AND is_active = 1";
    
    if ($conn->query($updateQuery)) {
        $affectedRows = $conn->affected_rows;
        echo "<p style='color: green;'>✅ Updated $affectedRows users to online status</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update users: " . $conn->error . "</p>";
    }
    
    // Show current online users
    echo "<h3>Current Online Users:</h3>";
    $onlineQuery = "SELECT employee_no, first_name, last_name, online_status, last_activity FROM users WHERE online_status = 'online' ORDER BY last_activity DESC";
    $onlineResult = $conn->query($onlineQuery);
    
    if ($onlineResult && $onlineResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Employee No</th><th>Name</th><th>Online Status</th><th>Last Activity</th></tr>";
        while ($row = $onlineResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            echo "<td style='color: green; font-weight: bold;'>" . htmlspecialchars($row['online_status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_activity']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No users currently showing as online</p>";
    }
    
    // Show all users with their current status
    echo "<h3>All Users Status:</h3>";
    $allUsersQuery = "SELECT employee_no, first_name, last_name, online_status, last_activity, last_login, last_logout FROM users ORDER BY last_activity DESC";
    $allUsersResult = $conn->query($allUsersQuery);
    
    if ($allUsersResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Employee No</th><th>Name</th><th>Online Status</th><th>Last Activity</th><th>Last Login</th><th>Last Logout</th></tr>";
        while ($row = $allUsersResult->fetch_assoc()) {
            $statusColor = $row['online_status'] === 'online' ? 'green' : 'gray';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>" . htmlspecialchars($row['online_status'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['last_activity'] ?? 'Never') . "</td>";
            echo "<td>" . htmlspecialchars($row['last_login'] ?? 'Never') . "</td>";
            echo "<td>" . htmlspecialchars($row['last_logout'] ?? 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><p><strong>Next Steps:</strong></p>";
    echo "<p>1. Go to User Account Management to see the updated status</p>";
    echo "<p>2. Try logging in with a user account to test the online status</p>";
    echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 