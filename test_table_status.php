<?php
// test_table_status.php
// Test script to verify table status rendering

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Table Status Test</h2>";

// Test the same query as the user account management page
$fetchUsersQuery = "SELECT employee_no, first_name, middle_name, last_name, institutional_email, mobile_no, role_id, department_id, is_active, last_activity, online_status, last_login, last_logout FROM users ORDER BY id DESC LIMIT 5";
$fetchUsersResult = $conn->query($fetchUsersQuery);

if ($fetchUsersResult) {
    echo "<h3>First 5 Users with Status:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Employee No</th><th>Name</th><th>Online Status</th><th>Last Login</th><th>Last Logout</th><th>Last Activity</th><th>Calculated Status</th></tr>";
    
    while ($row = $fetchUsersResult->fetch_assoc()) {
        // Facebook-style status calculation (same as in the table)
        $statusText = 'Inactive';
        $statusClass = 'status-inactive';
        
        if ($row['is_active'] == 1) {
            // Check if user has online_status data
            if (isset($row['online_status']) && $row['online_status'] === 'online') {
                $statusText = 'Online';
                $statusClass = 'status-online';
            } else {
                // User is offline, check when they last logged out
                if (isset($row['last_logout']) && $row['last_logout']) {
                    $lastLogout = new DateTime($row['last_logout']);
                    $now = new DateTime();
                    $timeDiff = $now->diff($lastLogout);
                    $daysDiff = $timeDiff->days;
                    
                    if ($daysDiff <= 30) {
                        $statusText = 'Active';
                        $statusClass = 'status-active';
                    } else {
                        $statusText = 'Inactive';
                        $statusClass = 'status-inactive';
                    }
                } else {
                    // No logout record, check last_activity as fallback
                    if (isset($row['last_activity']) && $row['last_activity']) {
                        $lastActivity = new DateTime($row['last_activity']);
                        $now = new DateTime();
                        $timeDiff = $now->diff($lastActivity);
                        $daysDiff = $timeDiff->days;
                        
                        if ($daysDiff <= 30) {
                            $statusText = 'Active';
                            $statusClass = 'status-active';
                        } else {
                            $statusText = 'Inactive';
                            $statusClass = 'status-inactive';
                        }
                    } else {
                        $statusText = 'Active';
                        $statusClass = 'status-active';
                    }
                }
            }
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['online_status'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['last_login'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['last_logout'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['last_activity'] ?? 'N/A') . "</td>";
        echo "<td style='color: " . ($statusClass === 'status-online' ? 'green' : ($statusClass === 'status-active' ? 'blue' : 'red')) . "; font-weight: bold;'>" . $statusText . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    $fetchUsersResult->free();
} else {
    echo "<p style='color: red;'>Error fetching users: " . $conn->error . "</p>";
}

echo "<h3>Test Links:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management' target='_blank'>🔗 Open User Account Management</a></p>";
echo "<p><a href='force_refresh_users.php' target='_blank'>🔗 Force Refresh Users</a></p>";

$conn->close();
?> 