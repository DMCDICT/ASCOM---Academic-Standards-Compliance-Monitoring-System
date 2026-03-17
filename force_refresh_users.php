<?php
// force_refresh_users.php
// Force refresh user data and clear caching

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Force Refresh User Data</h2>";

try {
    // Force update all users with recent activity to online status
    $updateQuery = "UPDATE users SET online_status = 'online' WHERE last_activity >= NOW() - INTERVAL 10 MINUTE AND is_active = 1";
    
    if ($conn->query($updateQuery)) {
        $affectedRows = $conn->affected_rows;
        echo "<p style='color: green;'>✅ Updated $affectedRows users to online status</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update users: " . $conn->error . "</p>";
    }
    
    // Show current status
    echo "<h3>Current User Status:</h3>";
    $statusQuery = "SELECT employee_no, first_name, last_name, online_status, last_activity FROM users ORDER BY last_activity DESC";
    $statusResult = $conn->query($statusQuery);
    
    if ($statusResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Employee No</th><th>Name</th><th>Online Status</th><th>Last Activity</th></tr>";
        
        while ($row = $statusResult->fetch_assoc()) {
            $statusColor = $row['online_status'] === 'online' ? 'green' : 'gray';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>" . htmlspecialchars($row['online_status'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['last_activity'] ?? 'Never') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h3>Next Steps:</h3>";
    echo "<p>1. <strong>Clear your browser cache</strong> (Ctrl+F5 or Ctrl+Shift+R)</p>";
    echo "<p>2. <strong>Go to User Account Management</strong> and check the status</p>";
    echo "<p>3. <strong>Open browser developer tools</strong> (F12) and check the Console tab for any errors</p>";
    
    echo "<br><p><strong>Links:</strong></p>";
    echo "<p><a href='super_admin-mis/content.php?page=user-account-management' target='_blank'>Open User Account Management</a></p>";
    echo "<p><a href='test_api_response.php' target='_blank'>Test API Response</a></p>";
    
    // JavaScript to force refresh
    echo "<script>";
    echo "console.log('Force refresh script loaded');";
    echo "setTimeout(function() {";
    echo "  console.log('Forcing page refresh in 3 seconds...');";
    echo "  window.location.reload(true);";
    echo "}, 3000);";
    echo "</script>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 