<?php
// debug_current_status.php
// Debug script to check current user status

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Current User Status Debug</h2>";

try {
    // Get current user data
    $query = "SELECT 
        employee_no, 
        first_name, 
        last_name, 
        is_active, 
        online_status,
        last_login,
        last_logout,
        last_activity
        FROM users 
        ORDER BY last_activity DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
        exit;
    }
    
    echo "<h3>Current User Data:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Employee No</th>";
    echo "<th>Name</th>";
    echo "<th>Is Active</th>";
    echo "<th>Online Status</th>";
    echo "<th>Last Login</th>";
    echo "<th>Last Logout</th>";
    echo "<th>Last Activity</th>";
    echo "<th>Calculated Status</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        // Calculate status based on our logic
        $status = 'Unknown';
        $statusClass = '';
        
        if ($row['is_active'] != 1) {
            $status = 'Inactive';
            $statusClass = 'color: red;';
        } elseif ($row['online_status'] === 'online') {
            $status = 'Online';
            $statusClass = 'color: green; font-weight: bold;';
        } elseif ($row['last_logout']) {
            $lastLogout = new DateTime($row['last_logout']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastLogout);
            $daysDiff = $timeDiff->days;
            
            if ($daysDiff <= 30) {
                $status = 'Active';
                $statusClass = 'color: orange;';
            } else {
                $status = 'Inactive';
                $statusClass = 'color: red;';
            }
        } elseif ($row['last_activity']) {
            $lastActivity = new DateTime($row['last_activity']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastActivity);
            $daysDiff = $timeDiff->days;
            
            if ($daysDiff <= 30) {
                $status = 'Active';
                $statusClass = 'color: orange;';
            } else {
                $status = 'Inactive';
                $statusClass = 'color: red;';
            }
        } else {
            $status = 'Active';
            $statusClass = 'color: orange;';
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employee_no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . htmlspecialchars($row['online_status'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['last_login'] ? $row['last_login'] : 'Never') . "</td>";
        echo "<td>" . ($row['last_logout'] ? $row['last_logout'] : 'Never') . "</td>";
        echo "<td>" . ($row['last_activity'] ? $row['last_activity'] : 'Never') . "</td>";
        echo "<td style='$statusClass'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check current session
    session_start();
    echo "<h3>Current Session:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    // Test login simulation
    echo "<h3>Test Login Simulation:</h3>";
    echo "<form method='post'>";
    echo "<select name='test_employee_no'>";
    $userResult = $conn->query("SELECT employee_no, first_name, last_name FROM users WHERE is_active = 1 LIMIT 5");
    while ($user = $userResult->fetch_assoc()) {
        echo "<option value='" . $user['employee_no'] . "'>" . $user['first_name'] . ' ' . $user['last_name'] . " (" . $user['employee_no'] . ")</option>";
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
            echo "<p><a href='debug_current_status.php'>Refresh to see changes</a></p>";
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
            echo "<p><a href='debug_current_status.php'>Refresh to see changes</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to simulate logout: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?> 