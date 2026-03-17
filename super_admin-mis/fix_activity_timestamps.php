<?php
// fix_activity_timestamps.php
// This script will update all users with NULL last_activity to have current timestamps

require_once __DIR__ . '/includes/db_connection.php';

echo "<h2>Fixing Activity Timestamps</h2>";

try {
    // Count users with NULL last_activity
    $countQuery = "SELECT COUNT(*) as null_count FROM users WHERE last_activity IS NULL";
    $countResult = $conn->query($countQuery);
    $nullCount = $countResult->fetch_assoc()['null_count'];
    
    echo "<p>Found <strong>$nullCount</strong> users with NULL last_activity</p>";
    
    if ($nullCount > 0) {
        // Update all users with NULL last_activity to current timestamp
        $updateQuery = "UPDATE users SET last_activity = NOW() WHERE last_activity IS NULL";
        if ($conn->query($updateQuery)) {
            $affectedRows = $conn->affected_rows;
            echo "<p style='color: green;'>✅ Successfully updated $affectedRows users with current timestamp!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update timestamps: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ All users already have activity timestamps!</p>";
    }
    
    // Show updated data
    $testQuery = "SELECT employee_no, last_activity FROM users ORDER BY last_activity DESC LIMIT 5";
    $testResult = $conn->query($testQuery);
    
    if ($testResult) {
        echo "<h3>Updated Data (Most Recent First):</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Employee No</th><th>Last Activity</th></tr>";
        while ($row = $testResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
            echo "<td>" . ($row['last_activity'] ? htmlspecialchars($row['last_activity']) : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test the status logic
    echo "<h3>Status Test:</h3>";
    $statusQuery = "SELECT employee_no, last_activity, 
                    CASE 
                        WHEN last_activity >= NOW() - INTERVAL 3 MINUTE THEN 'Online'
                        WHEN last_activity >= NOW() - INTERVAL 30 DAY THEN 'Active'
                        ELSE 'Inactive'
                    END as status
                    FROM users ORDER BY last_activity DESC LIMIT 3";
    $statusResult = $conn->query($statusQuery);
    
    if ($statusResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Employee No</th><th>Last Activity</th><th>Status</th></tr>";
        while ($row = $statusResult->fetch_assoc()) {
            $statusColor = $row['status'] === 'Online' ? 'green' : ($row['status'] === 'Active' ? 'orange' : 'gray');
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_activity']) . "</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><p><strong>Fix complete!</strong> Now test the status system:</p>";
echo "<p><a href='content.php?page=user-account-management'>Go to User Account Management</a></p>";
echo "<p>Then click the info button next to any status and use the 'TEST ACTIVITY' button.</p>";

$conn->close();
?> 