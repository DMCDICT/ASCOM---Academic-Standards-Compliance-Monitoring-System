<?php
// test_tab_close.php
// Test page for tab close detection

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user information
$employee_no = $_SESSION['employee_no'] ?? 'UNKNOWN';
$user_role = $_SESSION['user_role'] ?? 'UNKNOWN';
$username = $_SESSION['username'] ?? 'UNKNOWN';
$is_super_admin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;

// Check if user is logged in
$is_logged_in = false;
if ($is_super_admin) {
    $is_logged_in = true;
} elseif (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['teacher_logged_in']) && $_SESSION['teacher_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['librarian_logged_in']) && $_SESSION['librarian_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['admin_qa_logged_in']) && $_SESSION['admin_qa_logged_in'] === true) {
    $is_logged_in = true;
}

// Get user status from database
$user_status = 'Unknown';
if ($employee_no !== 'UNKNOWN' && $employee_no !== 'SUPER_ADMIN') {
    try {
        require_once 'super_admin-mis/includes/db_connection.php';
        $statusQuery = "SELECT online_status FROM users WHERE employee_no = ?";
        $statusStmt = $conn->prepare($statusQuery);
        $statusStmt->bind_param("s", $employee_no);
        $statusStmt->execute();
        $result = $statusStmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_status = $row['online_status'] ?? 'Unknown';
        }
        
        $statusStmt->close();
        $conn->close();
    } catch (Exception $e) {
        $user_status = 'Error: ' . $e->getMessage();
    }
} elseif ($is_super_admin) {
    $user_status = 'Super Admin (Protected)';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Tab Close Detection</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        .log { background: #f5f5f5; padding: 10px; margin: 10px 0; max-height: 200px; overflow-y: auto; }
        .user-info { background: #e8f5e8; padding: 10px; border-radius: 5px; }
        .warning { background: #fff3cd; padding: 10px; border-radius: 5px; color: #856404; }
    </style>
</head>
<body>
    <h1>Test Tab Close Detection</h1>
    
    <div class="test-section user-info">
        <h3>Current User Info</h3>
        <p><strong>Employee No:</strong> <span id="employeeNo"><?php echo htmlspecialchars($employee_no); ?></span></p>
        <p><strong>Username:</strong> <span id="username"><?php echo htmlspecialchars($username); ?></span></p>
        <p><strong>User Role:</strong> <span id="userRole"><?php echo htmlspecialchars($user_role); ?></span></p>
        <p><strong>Status:</strong> <span id="userStatus"><?php echo htmlspecialchars($user_status); ?></span></p>
        <p><strong>Logged In:</strong> <span id="isLoggedIn"><?php echo $is_logged_in ? 'Yes' : 'No'; ?></span></p>
        <p><strong>Super Admin:</strong> <span id="isSuperAdmin"><?php echo $is_super_admin ? 'Yes' : 'No'; ?></span></p>
    </div>
    
    <?php if (!$is_logged_in): ?>
    <div class="test-section warning">
        <h3>⚠️ Warning</h3>
        <p>You are not logged in as a regular user. To test tab close detection properly:</p>
        <ol>
            <li>Logout from Super Admin</li>
            <li>Login as a regular user (Teacher, Librarian, etc.)</li>
            <li>Access this test page again</li>
        </ol>
    </div>
    <?php endif; ?>
    
    <div class="test-section">
        <h3>Manual Actions</h3>
        <button onclick="testLogout()">Test Manual Logout</button>
        <button onclick="testForceLogout()">Test Force Logout</button>
        <button onclick="clearLog()">Clear Log</button>
    </div>
    
    <div class="test-section">
        <h3>Event Log</h3>
        <div id="eventLog" class="log"></div>
    </div>
    
    <div class="test-section">
        <h3>Instructions</h3>
        <ol>
            <li>Make sure you're logged in as a regular user (not Super Admin)</li>
            <li>Click "Test Manual Logout" to test the logout functionality</li>
            <li>Click "Test Force Logout" to force logout the current user</li>
            <li>Close this tab and check if the user status changes to "Active"</li>
            <li>Watch the event log for tab close events</li>
        </ol>
    </div>

    <script>
        let employeeNo = '<?php echo $employee_no; ?>';
        let isSuperAdmin = <?php echo $is_super_admin ? 'true' : 'false'; ?>;
        let isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        
        // Log function
        function log(message) {
            const logDiv = document.getElementById('eventLog');
            const timestamp = new Date().toLocaleTimeString();
            logDiv.innerHTML += `[${timestamp}] ${message}<br>`;
            logDiv.scrollTop = logDiv.scrollHeight;
            console.log(message);
        }
        
        function clearLog() {
            document.getElementById('eventLog').innerHTML = '';
        }
        
        // Test manual logout
        function testLogout() {
            log('Testing manual logout...');
            fetch('logout_on_close.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ logout_reason: 'test_manual' })
            })
            .then(response => response.text())
            .then(data => {
                log('Manual logout response: ' + data);
            })
            .catch(error => {
                log('Manual logout error: ' + error);
            });
        }
        
        // Test force logout
        function testForceLogout() {
            log('Testing force logout for employee: ' + employeeNo);
            fetch('super_admin-mis/force_logout_user.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    employee_no: employeeNo,
                    logout_reason: 'test_force_logout'
                })
            })
            .then(response => {
                log('Response status: ' + response.status);
                return response.json();
            })
            .then(data => {
                log('Force logout response: ' + JSON.stringify(data));
                if (data.success) {
                    log('✅ Force logout successful!');
                    // Update the status display
                    document.getElementById('userStatus').textContent = 'offline';
                } else {
                    log('❌ Force logout failed: ' + data.message);
                }
            })
            .catch(error => {
                log('Force logout error: ' + error);
            });
        }
        
        // Tab close detection
        window.addEventListener('beforeunload', (event) => {
            log('beforeunload event triggered');
            navigator.sendBeacon('logout_on_close.php');
        });
        
        window.addEventListener('unload', (event) => {
            log('unload event triggered');
            navigator.sendBeacon('logout_on_close.php');
        });
        
        window.addEventListener('pagehide', (event) => {
            log('pagehide event triggered');
            navigator.sendBeacon('logout_on_close.php');
        });
        
        // Page visibility
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                log('Page became hidden');
                navigator.sendBeacon('logout_on_close.php');
            } else {
                log('Page became visible');
            }
        });
        
        // Initial log
        log('Test page loaded. Employee No: ' + employeeNo);
        log('Super Admin: ' + (isSuperAdmin ? 'Yes' : 'No'));
        log('Logged In: ' + (isLoggedIn ? 'Yes' : 'No'));
        log('Tab close detection active. Close this tab to test.');
    </script>
</body>
</html> 