<?php
// debug_login_issue.php
// This script will help debug the login issue

echo "<h2>Login Debug Information</h2>";

// Check if this is a POST request (simulating login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Detected:</h3>";
    echo "<p>Username: " . ($_POST['username'] ?? 'NOT SET') . "</p>";
    echo "<p>Password: " . (isset($_POST['password']) ? 'SET (hidden)' : 'NOT SET') . "</p>";
    
    // Simulate the login process
    require_once 'super_admin-mis/includes/db_connection.php';
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
        exit;
    }
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Database Check:</h3>";
    
    // Check if user exists
    $userQuery = "SELECT id, first_name, last_name, institutional_email, password, role_id FROM users WHERE institutional_email = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("s", $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 1) {
        $user = $userResult->fetch_assoc();
        echo "<p style='color: green;'>✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        echo "<p>Password match: " . ($password === $user['password'] ? 'YES' : 'NO') . "</p>";
        
        if ($password === $user['password']) {
            // Check user roles
            $rolesQuery = "
                SELECT ur.role_name, ur.is_active
                FROM user_roles ur
                WHERE ur.user_id = ? AND ur.is_active = 1
                ORDER BY ur.assigned_at DESC
            ";
            $rolesStmt = $conn->prepare($rolesQuery);
            $rolesStmt->bind_param("i", $user['id']);
            $rolesStmt->execute();
            $rolesResult = $rolesStmt->get_result();
            
            echo "<h3>User Roles:</h3>";
            if ($rolesResult->num_rows > 0) {
                echo "<ul>";
                while ($role = $rolesResult->fetch_assoc()) {
                    echo "<li>" . $role['role_name'] . " (Active: " . ($role['is_active'] ? 'Yes' : 'No') . ")</li>";
                }
                echo "</ul>";
                
                if ($rolesResult->num_rows > 1) {
                    echo "<p style='color: blue;'>🔵 User has multiple roles - should see role selection</p>";
                } else {
                    echo "<p style='color: orange;'>🟠 User has single role - should go directly to dashboard</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ No roles found for user</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Password mismatch</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User not found with email: " . htmlspecialchars($username) . "</p>";
    }
    
    $conn->close();
} else {
    echo "<h3>No POST Request</h3>";
    echo "<p>This script should be called via POST from the login form.</p>";
    
    // Show available users for testing
    require_once 'super_admin-mis/includes/db_connection.php';
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
        exit;
    }
    
    echo "<h3>Available Users for Testing:</h3>";
    $usersQuery = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.institutional_email,
            u.password,
            COUNT(ur.id) as role_count
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
        WHERE u.is_active = 1
        GROUP BY u.id
        ORDER BY u.first_name
        LIMIT 5
    ";
    $usersResult = $conn->query($usersQuery);
    
    if ($usersResult && $usersResult->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Name</th><th>Email</th><th>Password</th><th>Role Count</th>";
        echo "</tr>";
        
        while ($row = $usersResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['institutional_email'] . "</td>";
            echo "<td>" . $row['password'] . "</td>";
            echo "<td>" . $row['role_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found.</p>";
    }
    
    $conn->close();
}

echo "<h3>Test Login Form:</h3>";
echo "<form method='POST' action='debug_login_issue.php'>";
echo "<input type='text' name='username' placeholder='Email' required><br><br>";
echo "<input type='password' name='password' placeholder='Password' required><br><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";

echo "<h3>Quick Actions:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Go to Login</a>";
echo "<a href='quick_check_roles.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>📊 Check Roles</a>";
echo "<a href='assign_test_roles.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔧 Assign Roles</a>";
echo "</div>";
?>
