<?php
// check_user_44_dean_access.php
// Check User ID 44's dean access and session state

session_start();
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h1>User ID 44 Dean Access Check</h1>";

// Check current session
echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check User ID 44's roles and access
try {
    echo "<h2>User ID 44 Information:</h2>";
    
    // Get user details
    $userQuery = "SELECT u.*, ur.role_name, ur.is_active 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  WHERE u.id = 44";
    $userResult = $pdo->query($userQuery);
    
    if ($userResult->rowCount() > 0) {
        echo "<h3>User Details:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        
        while ($user = $userResult->fetch(PDO::FETCH_ASSOC)) {
            foreach ($user as $field => $value) {
                echo "<tr>";
                echo "<td>" . $field . "</td>";
                echo "<td>" . ($value ?? 'NULL') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p>❌ User ID 44 not found!</p>";
    }
    
    // Check if user is assigned as dean of any department
    $deanQuery = "SELECT d.* FROM departments d WHERE d.dean_user_id = 44";
    $deanResult = $pdo->query($deanQuery);
    
    echo "<h3>Dean Assignment Check:</h3>";
    if ($deanResult->rowCount() > 0) {
        $deanDept = $deanResult->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ User ID 44 is assigned as dean of: <strong>" . $deanDept['department_name'] . "</strong></p>";
        echo "<p>Department ID: " . $deanDept['id'] . "</p>";
        echo "<p>Department Code: " . $deanDept['department_code'] . "</p>";
    } else {
        echo "<p>❌ User ID 44 is NOT assigned as dean of any department!</p>";
    }
    
    // Check all user roles
    $rolesQuery = "SELECT ur.* FROM user_roles ur WHERE ur.user_id = 44";
    $rolesResult = $pdo->query($rolesQuery);
    
    echo "<h3>All User Roles:</h3>";
    if ($rolesResult->rowCount() > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Role Name</th><th>Is Active</th><th>Created At</th></tr>";
        
        while ($role = $rolesResult->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $role['role_name'] . "</td>";
            echo "<td>" . ($role['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $role['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No roles found for User ID 44!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Test Session for User 44:</h2>";
echo "<p><a href='?set_session=1'>Set Test Session for User 44 as Dean</a></p>";

// Set test session if requested
if (isset($_GET['set_session'])) {
    $_SESSION['user_id'] = 44;
    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_first_name'] = 'Test';
    $_SESSION['user_last_name'] = 'User';
    $_SESSION['institutional_email'] = 'test@example.com';
    
    // Set dean role
    $_SESSION['dean_logged_in'] = true;
    $_SESSION['selected_role'] = [
        'role_name' => 'dean',
        'department_id' => 1, // Assuming department ID 1
        'department_name' => 'College of Computing Studies',
        'department_code' => 'CCS'
    ];
    
    echo "<p>✅ Test session set for User 44 as dean!</p>";
    echo "<p><a href='check_session.php'>Check Session State</a></p>";
    echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
}

echo "<hr>";
echo "<p><a href='user_login.php'>Go to Login Page</a></p>";
echo "<p><a href='check_session.php'>Check Session State</a></p>";
?>
