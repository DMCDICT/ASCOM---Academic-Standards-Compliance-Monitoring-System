<?php
require_once 'super_admin_session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    file_put_contents('super_admin_login_debug.txt', 'Login attempt: ' . $username . ' | Password: ' . $password . PHP_EOL, FILE_APPEND);

    // Prepare and execute query (case-insensitive email, plain text password)
    $stmt = $conn->prepare("SELECT id, email, password, is_active FROM super_admin WHERE LOWER(email) = LOWER(?) AND password = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        file_put_contents('super_admin_login_debug.txt', 'Query executed. Num rows: ' . $result->num_rows . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('super_admin_login_debug.txt', 'Query failed: ' . $conn->error . PHP_EOL, FILE_APPEND);
    }

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        file_put_contents('super_admin_login_debug.txt', 'Fetched row: ' . print_r($row, true) . PHP_EOL, FILE_APPEND);
        
        // Start session with Super Admin configuration
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set session variables
        $_SESSION['super_admin_logged_in'] = true;
        $_SESSION['user_role'] = 'super_admin';
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['email'];
        $_SESSION['employee_no'] = 'SUPER_ADMIN'; // Set a default employee number for super admin
        
        file_put_contents('super_admin_login_debug.txt', 'Session variables set: ' . print_r($_SESSION, true) . PHP_EOL, FILE_APPEND);
        file_put_contents('super_admin_login_debug.txt', 'Login success!\n', FILE_APPEND);
        
        header('Location: super_admin_successful_login.php');
        exit;
    } else {
        file_put_contents('super_admin_login_debug.txt', 'Login failed. Redirecting to index.php\n', FILE_APPEND);
        // Failed login: redirect back to index.php with error
        header('Location: index.php?error=invalid_credentials');
        exit;
    }
} else {
    file_put_contents('super_admin_login_debug.txt', 'Accessed directly, redirecting to index.php\n', FILE_APPEND);
    // If accessed directly, redirect to login
    header('Location: index.php');
    exit;
}
?> 