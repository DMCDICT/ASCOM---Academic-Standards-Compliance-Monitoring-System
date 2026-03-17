<?php
// test_login_step_by_step.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Step-by-Step Login Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test with a known user
$testEmail = "philipcrisencarnacion@sccpag.edu.ph";
$testPassword = "132065TCHCCS";

echo "<h3>Testing Login Process:</h3>";
echo "<p><strong>Test User:</strong> " . $testEmail . "</p>";
echo "<p><strong>Test Password:</strong> " . $testPassword . "</p>";

// Step 1: Check if user exists
echo "<h4>Step 1: Check if user exists</h4>";
$userQuery = "SELECT id, first_name, last_name, institutional_email, password, role_id, is_active FROM users WHERE institutional_email = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("s", $testEmail);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 1) {
    $user = $userResult->fetch_assoc();
    echo "<p style='color: green;'>✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>Is Active: " . ($user['is_active'] ? 'Yes' : 'No') . "</p>";
    
    // Step 2: Check password
    echo "<h4>Step 2: Check password</h4>";
    if ($testPassword === $user['password']) {
        echo "<p style='color: green;'>✅ Password matches</p>";
        
        // Step 3: Check user roles
        echo "<h4>Step 3: Check user roles</h4>";
        $rolesQuery = "
            SELECT ur.role_name, ur.is_active, ur.assigned_at
            FROM user_roles ur
            WHERE ur.user_id = ? AND ur.is_active = 1
            ORDER BY ur.assigned_at DESC
        ";
        $rolesStmt = $conn->prepare($rolesQuery);
        $rolesStmt->bind_param("i", $user['id']);
        $rolesStmt->execute();
        $rolesResult = $rolesStmt->get_result();
        
        if ($rolesResult->num_rows > 0) {
            echo "<p style='color: green;'>✅ Found " . $rolesResult->num_rows . " role(s):</p>";
            echo "<ul>";
            while ($role = $rolesResult->fetch_assoc()) {
                echo "<li>" . $role['role_name'] . " (Assigned: " . $role['assigned_at'] . ")</li>";
            }
            echo "</ul>";
            
            // Step 4: Determine login flow
            echo "<h4>Step 4: Determine login flow</h4>";
            if ($rolesResult->num_rows > 1) {
                echo "<p style='color: blue;'>🔵 User has multiple roles - should see role selection</p>";
                echo "<p>Expected flow: Login → Role Selection → Welcome → Dashboard</p>";
            } else {
                echo "<p style='color: orange;'>🟠 User has single role - should go directly to dashboard</p>";
                echo "<p>Expected flow: Login → Welcome → Dashboard</p>";
            }
            
            // Step 5: Simulate session creation
            echo "<h4>Step 5: Simulate session creation</h4>";
            echo "<p>Session variables that should be set:</p>";
            echo "<ul>";
            echo "<li>user_id: " . $user['id'] . "</li>";
            echo "<li>employee_no: " . ($user['employee_no'] ?? 'Not set') . "</li>";
            echo "<li>username: " . $user['institutional_email'] . "</li>";
            echo "<li>user_first_name: " . $user['first_name'] . "</li>";
            echo "<li>user_last_name: " . $user['last_name'] . "</li>";
            echo "<li>is_authenticated: true</li>";
            echo "</ul>";
            
        } else {
            echo "<p style='color: red;'>❌ No roles found for user</p>";
            echo "<p>This user cannot login because they have no assigned roles.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Password does not match</p>";
        echo "<p>Expected: " . $testPassword . "</p>";
        echo "<p>Actual: " . $user['password'] . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ User not found with email: " . $testEmail . "</p>";
}

echo "<h3>Test Login Form:</h3>";
echo "<form method='POST' action='test_login_step_by_step.php'>";
echo "<input type='text' name='test_email' placeholder='Email' value='" . $testEmail . "' style='width: 300px; padding: 10px; margin: 5px;'><br>";
echo "<input type='password' name='test_password' placeholder='Password' value='" . $testPassword . "' style='width: 300px; padding: 10px; margin: 5px;'><br>";
echo "<button type='submit' style='background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin: 10px;'>Test This Login</button>";
echo "</form>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['test_email'] ?? '';
    $password = $_POST['test_password'] ?? '';
    
    if ($email && $password) {
        echo "<h3>Form Submission Test:</h3>";
        echo "<p>Email: " . htmlspecialchars($email) . "</p>";
        echo "<p>Password: " . str_repeat('*', strlen($password)) . "</p>";
        
        // Test the same process with form data
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows === 1) {
            $user = $userResult->fetch_assoc();
            echo "<p style='color: green;'>✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "</p>";
            
            if ($password === $user['password']) {
                echo "<p style='color: green;'>✅ Password matches</p>";
                
                // Check roles
                $rolesStmt = $conn->prepare($rolesQuery);
                $rolesStmt->bind_param("i", $user['id']);
                $rolesStmt->execute();
                $rolesResult = $rolesStmt->get_result();
                
                if ($rolesResult->num_rows > 0) {
                    echo "<p style='color: green;'>✅ User has " . $rolesResult->num_rows . " role(s)</p>";
                    
                    if ($rolesResult->num_rows > 1) {
                        echo "<p style='color: blue;'>🔵 Should see role selection screen</p>";
                        echo "<p><a href='user_login.php' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Try Actual Login</a></p>";
                    } else {
                        echo "<p style='color: orange;'>🟠 Should go directly to dashboard</p>";
                        echo "<p><a href='user_login.php' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Try Actual Login</a></p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ No roles assigned - cannot login</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Password incorrect</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found</p>";
        }
    }
}

echo "<h3>Quick Actions:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='check_current_status.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>📊 Check System Status</a>";
echo "<a href='assign_test_roles.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔧 Assign Roles</a>";
echo "<a href='user_login.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Go to Login</a>";
echo "</div>";

$conn->close();
?>
