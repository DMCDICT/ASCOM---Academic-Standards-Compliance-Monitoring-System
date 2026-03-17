<?php
// test_login_as_dean.php
// Test script to help with dean login

session_start();

echo "<h1>Dean Login Test</h1>";

// Check if already logged in
if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    echo "<p>✅ Already logged in as dean!</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT_SET') . "</p>";
    echo "<p>Dean Department ID: " . ($_SESSION['dean_department_id'] ?? 'NOT_SET') . "</p>";
    echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
    exit();
}

// Check if there's any session data
if (!empty($_SESSION)) {
    echo "<h2>Current Session Data:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<h2>Login Options:</h2>";

// Check available dean accounts
require_once 'super_admin-mis/includes/db_connection.php';

try {
    // Get dean accounts
    $deanQuery = "SELECT u.id, u.institutional_email, u.password, d.department_name, d.department_code 
                  FROM users u 
                  JOIN departments d ON d.dean_user_id = u.id 
                  WHERE u.role_id = 2 AND u.is_active = 1";
    $deanResult = $pdo->query($deanQuery);
    
    if ($deanResult->rowCount() > 0) {
        echo "<h3>Available Dean Accounts:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Password</th><th>Department</th><th>Department Code</th></tr>";
        
        while ($dean = $deanResult->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $dean['id'] . "</td>";
            echo "<td>" . $dean['institutional_email'] . "</td>";
            echo "<td>" . $dean['password'] . "</td>";
            echo "<td>" . $dean['department_name'] . "</td>";
            echo "<td>" . $dean['department_code'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Quick Login Links:</h3>";
        $deanResult = $pdo->query($deanQuery);
        while ($dean = $deanResult->fetch(PDO::FETCH_ASSOC)) {
            echo "<p><a href='user_login.php?email=" . urlencode($dean['institutional_email']) . "'>Login as " . $dean['institutional_email'] . " (" . $dean['department_name'] . ")</a></p>";
        }
        
    } else {
        echo "<p>❌ No dean accounts found in database!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error checking dean accounts: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Manual Login:</h2>";
echo "<p><a href='user_login.php'>Go to Login Page</a></p>";
echo "<p><a href='check_session.php'>Check Session State</a></p>";

// Test direct session setting (for debugging)
if (isset($_GET['test_session'])) {
    echo "<h2>Testing Session Setting:</h2>";
    
    // Simulate dean login
    $_SESSION['user_id'] = 18; // Assuming this is a dean user ID
    $_SESSION['dean_logged_in'] = true;
    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_first_name'] = 'Philipcris';
    $_SESSION['user_last_name'] = 'Encarnacion';
    $_SESSION['selected_role'] = [
        'role_name' => 'dean',
        'department_id' => 1,
        'department_name' => 'College of Computing Studies',
        'department_code' => 'CCS'
    ];
    
    echo "<p>✅ Session set for testing!</p>";
    echo "<p><a href='check_session.php'>Check Updated Session</a></p>";
    echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
}
?>
