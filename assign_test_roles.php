<?php
// assign_test_roles.php
// Script to assign multiple roles to a user for testing

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Assign Test Roles for Multi-Role Login Testing</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_roles'])) {
    $userId = intval($_POST['user_id']);
    $selectedRoles = $_POST['roles'] ?? [];
    
    if ($userId && !empty($selectedRoles)) {
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($selectedRoles as $roleName) {
            // Check if role already exists for this user
            $checkQuery = "SELECT id FROM user_roles WHERE user_id = ? AND role_name = ? AND is_active = 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("is", $userId, $roleName);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows === 0) {
                // Role doesn't exist, add it
                $insertQuery = "INSERT INTO user_roles (user_id, role_name, assigned_by) VALUES (?, ?, 'Test Script')";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("is", $userId, $roleName);
                
                if ($insertStmt->execute()) {
                    $successCount++;
                    echo "<p style='color: green;'>✅ Successfully assigned role: " . $roleName . "</p>";
                } else {
                    $errorCount++;
                    echo "<p style='color: red;'>❌ Failed to assign role: " . $roleName . " - " . $insertStmt->error . "</p>";
                }
                $insertStmt->close();
            } else {
                echo "<p style='color: orange;'>⚠️ Role already exists: " . $roleName . "</p>";
            }
            $checkStmt->close();
        }
        
        echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Assignment Summary:</h3>";
        echo "<p>Successfully assigned: " . $successCount . " roles</p>";
        echo "<p>Errors: " . $errorCount . "</p>";
        echo "<p><a href='test_complete_login_flow.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Login Flow</a></p>";
        echo "</div>";
    }
}

// Get available users
echo "<h3>1. Select User to Assign Roles:</h3>";
$usersQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.institutional_email,
        COUNT(ur.id) as current_roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY u.first_name, u.last_name
";
$usersResult = $conn->query($usersQuery);

if ($usersResult && $usersResult->num_rows > 0) {
    echo "<form method='POST'>";
    echo "<select name='user_id' required style='padding: 10px; margin: 10px; width: 300px;'>";
    echo "<option value=''>-- Select a User --</option>";
    
    while ($user = $usersResult->fetch_assoc()) {
        $selected = (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '';
        echo "<option value='" . $user['id'] . "' " . $selected . ">";
        echo $user['first_name'] . " " . $user['last_name'] . " (" . $user['institutional_email'] . ") - " . $user['current_roles'] . " roles";
        echo "</option>";
    }
    echo "</select>";
    
    // Get available roles
    echo "<h3>2. Select Roles to Assign:</h3>";
    $availableRoles = ['teacher', 'dean', 'librarian', 'quality_assurance'];
    
    echo "<div style='margin: 10px 0;'>";
    foreach ($availableRoles as $role) {
        $checked = (isset($_POST['roles']) && in_array($role, $_POST['roles'])) ? 'checked' : '';
        echo "<label style='display: block; margin: 5px 0;'>";
        echo "<input type='checkbox' name='roles[]' value='" . $role . "' " . $checked . "> ";
        echo ucfirst($role);
        echo "</label>";
    }
    echo "</div>";
    
    echo "<button type='submit' name='assign_roles' style='background-color: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer;'>Assign Selected Roles</button>";
    echo "</form>";
} else {
    echo "<p>No users found.</p>";
}

// Show current role assignments
echo "<h3>3. Current Role Assignments:</h3>";
$currentRolesQuery = "
    SELECT 
        ur.id,
        ur.user_id,
        ur.role_name,
        ur.assigned_by,
        ur.assigned_at,
        u.first_name,
        u.last_name
    FROM user_roles ur
    JOIN users u ON ur.user_id = u.id
    WHERE ur.is_active = 1
    ORDER BY u.first_name, ur.role_name
";
$currentRolesResult = $conn->query($currentRolesQuery);

if ($currentRolesResult && $currentRolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Assignment ID</th><th>User</th><th>Role</th><th>Assigned By</th><th>Assigned At</th>";
    echo "</tr>";
    
    while ($row = $currentRolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . $row['assigned_by'] . "</td>";
        echo "<td>" . $row['assigned_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No role assignments found.</p>";
}

echo "<h3>4. Quick Actions:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='test_complete_login_flow.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🚀 Test Complete Login Flow</a>";
echo "<a href='user_login.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Go to Login</a>";
echo "<a href='debug_role_types.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>📊 Debug Role Types</a>";
echo "</div>";

$conn->close();
?>
