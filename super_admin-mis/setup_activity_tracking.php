<?php
// setup_activity_tracking.php
// Run this script once to add the last_activity column to your database

require_once __DIR__ . '/includes/db_connection.php';

echo "<h2>Setting up Activity Tracking</h2>";

try {
    // Check if last_activity column already exists
    $checkQuery = "SHOW COLUMNS FROM users LIKE 'last_activity'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ last_activity column already exists!</p>";
    } else {
        // Add last_activity column
        $alterQuery = "ALTER TABLE users ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL";
        if ($conn->query($alterQuery)) {
            echo "<p style='color: green;'>✅ Successfully added last_activity column!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add last_activity column: " . $conn->error . "</p>";
        }
    }
    
    // Check if online_status column exists
    $checkOnlineQuery = "SHOW COLUMNS FROM users LIKE 'online_status'";
    $checkOnlineResult = $conn->query($checkOnlineQuery);
    
    if ($checkOnlineResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ online_status column already exists!</p>";
    } else {
        // Add online_status column
        $alterOnlineQuery = "ALTER TABLE users ADD COLUMN online_status ENUM('online', 'offline') DEFAULT 'offline'";
        if ($conn->query($alterOnlineQuery)) {
            echo "<p style='color: green;'>✅ Successfully added online_status column!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add online_status column: " . $conn->error . "</p>";
        }
    }
    
    // Check if last_login column exists
    $checkLoginQuery = "SHOW COLUMNS FROM users LIKE 'last_login'";
    $checkLoginResult = $conn->query($checkLoginQuery);
    
    if ($checkLoginResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ last_login column already exists!</p>";
    } else {
        // Add last_login column
        $alterLoginQuery = "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL";
        if ($conn->query($alterLoginQuery)) {
            echo "<p style='color: green;'>✅ Successfully added last_login column!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add last_login column: " . $conn->error . "</p>";
        }
    }
    
    // Check if last_logout column exists
    $checkLogoutQuery = "SHOW COLUMNS FROM users LIKE 'last_logout'";
    $checkLogoutResult = $conn->query($checkLogoutQuery);
    
    if ($checkLogoutResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ last_logout column already exists!</p>";
    } else {
        // Add last_logout column
        $alterLogoutQuery = "ALTER TABLE users ADD COLUMN last_logout TIMESTAMP NULL DEFAULT NULL";
        if ($conn->query($alterLogoutQuery)) {
            echo "<p style='color: green;'>✅ Successfully added last_logout column!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add last_logout column: " . $conn->error . "</p>";
        }
    }
    
    // Update existing users with current timestamp for last_activity
    $updateQuery = "UPDATE users SET last_activity = NOW() WHERE last_activity IS NULL";
    if ($conn->query($updateQuery)) {
        echo "<p style='color: green;'>✅ Updated existing users with current activity timestamp!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Failed to set initial timestamps: " . $conn->error . "</p>";
    }
    
    // Add indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_last_activity ON users(last_activity)",
        "CREATE INDEX IF NOT EXISTS idx_users_online_status ON users(online_status)",
        "CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login)",
        "CREATE INDEX IF NOT EXISTS idx_users_last_logout ON users(last_logout)"
    ];
    
    foreach ($indexes as $indexQuery) {
        if ($conn->query($indexQuery)) {
            echo "<p style='color: green;'>✅ Added performance index!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Index creation failed (may already exist): " . $conn->error . "</p>";
        }
    }
    
    // Test the column
    $testQuery = "SELECT employee_no, last_activity FROM users LIMIT 3";
    $testResult = $conn->query($testQuery);
    
    if ($testResult) {
        echo "<h3>Sample Data:</h3>";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<br><p><strong>Setup complete!</strong> You can now close this page and test the status system.</p>";
echo "<p><a href='content.php?page=user-account-management'>Go to User Account Management</a></p>";

$conn->close();
?> 