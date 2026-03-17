<?php
require_once 'session_config.php';
file_put_contents('login_debug.txt', 'user_auth.php hit at ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
session_start(); // Start session to store login state

// Include database connection
require_once 'super_admin-mis/includes/db_connection.php';

if ($conn->connect_error) {
    file_put_contents('login_debug.txt', 'DB connection error: ' . $conn->connect_error . PHP_EOL, FILE_APPEND);
    header("Location: user_login.php?error=invalid_credentials");
    exit();
}

// Get input values
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Check if this is a CAPTCHA-verified login
$captchaVerified = isset($_GET['captcha_verified']) && $_GET['captcha_verified'] === 'true';
$captchaUsername = $_GET['username'] ?? '';

// Validate required fields
if (empty($username) || empty($password)) {
    file_put_contents('login_debug.txt', 'Missing username or password' . PHP_EOL, FILE_APPEND);
    header("Location: user_login.php?error=invalid_credentials");
    exit();
}

try {
    // Query the users table to find user by institutional_email (any role except super_admin)
    $stmt = $conn->prepare("SELECT id, employee_no, institutional_email, password, role_id, is_active, last_activity, first_name, last_name, title, department_id FROM users WHERE institutional_email = ? AND is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    file_put_contents('login_debug.txt', 'Login attempt: ' . $username . ' | Password: ' . $password . PHP_EOL, FILE_APPEND);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        file_put_contents('login_debug.txt', 'Fetched user: ' . print_r($user, true) . PHP_EOL, FILE_APPEND);

        // Check if user is inactive (more than 30 days)
        $isInactive = false;
        if ($user['last_activity']) {
            $lastActivity = new DateTime($user['last_activity']);
            $now = new DateTime();
            $timeDiff = $now->diff($lastActivity);
            $daysDiff = $timeDiff->days;
            
            // User is inactive if last activity was more than 30 days ago
            if ($daysDiff > 30) {
                $isInactive = true;
                file_put_contents('login_debug.txt', 'User inactive for ' . $daysDiff . ' days' . PHP_EOL, FILE_APPEND);
            }
        }

        // If user is inactive and not CAPTCHA verified, redirect to CAPTCHA
        if ($isInactive && !$captchaVerified) {
            file_put_contents('login_debug.txt', 'Redirecting inactive user to CAPTCHA' . PHP_EOL, FILE_APPEND);
            header("Location: captcha_verification.php?username=" . urlencode($username));
            exit();
        }

        // If CAPTCHA verified, check if username matches
        if ($captchaVerified && $captchaUsername !== $username) {
            file_put_contents('login_debug.txt', 'CAPTCHA username mismatch' . PHP_EOL, FILE_APPEND);
            header("Location: user_login.php?error=invalid_credentials");
            exit();
        }

        // Build roles from authoritative sources
        $user_roles = [];
        
        // 1) Teacher role from users.role_id and user's department
        if (intval($user['role_id']) === 4) {
            $deptCode = null;
            $deptName = null;
            if (!empty($user['department_id'])) {
                $deptQuery = $conn->prepare("SELECT department_code, department_name FROM departments WHERE id = ?");
                $deptQuery->bind_param("i", $user['department_id']);
                $deptQuery->execute();
                $deptRes = $deptQuery->get_result();
                if ($deptRes && $deptRes->num_rows > 0) {
                    $deptRow = $deptRes->fetch_assoc();
                    $deptCode = $deptRow['department_code'];
                    $deptName = $deptRow['department_name'];
                }
                $deptQuery->close();
            }
            $user_roles[] = [
                'type' => 'teacher',
                'department_code' => $deptCode,
                'department_name' => $deptName,
                'assigned_at' => null
            ];
        }
        
        // 2) Dean roles strictly from departments.dean_user_id matches
        $deanQuery = $conn->prepare("SELECT department_code, department_name FROM departments WHERE dean_user_id = ?");
        $deanQuery->bind_param("i", $user['id']);
        $deanQuery->execute();
        $deanRes = $deanQuery->get_result();
        if ($deanRes && $deanRes->num_rows > 0) {
            while ($d = $deanRes->fetch_assoc()) {
                $user_roles[] = [
                    'type' => 'dean',
                    'department_code' => $d['department_code'],
                    'department_name' => $d['department_name'],
                    'assigned_at' => null
                ];
            }
        }
        $deanQuery->close();
        
        // 3) Librarian and QA from user_roles table
        $urStmt = $conn->prepare("SELECT role_name, assigned_at FROM user_roles WHERE user_id = ? AND is_active = 1 AND role_name IN ('librarian','quality_assurance')");
        $urStmt->bind_param("i", $user['id']);
        $urStmt->execute();
        $urRes = $urStmt->get_result();
        if ($urRes && $urRes->num_rows > 0) {
            while ($row = $urRes->fetch_assoc()) {
                $user_roles[] = [
                    'type' => strtolower($row['role_name']),
                    'department_code' => null,
                    'department_name' => null,
                    'assigned_at' => $row['assigned_at']
                ];
            }
        }
        $urStmt->close();
        
        // 4) Legacy fallback if nothing was found at all
        if (count($user_roles) === 0) {
            $role_stmt = $conn->prepare("SELECT role FROM roles WHERE id = ?");
            $role_stmt->bind_param("i", $user['role_id']);
            $role_stmt->execute();
            $role_result = $role_stmt->get_result();
            if ($role_result->num_rows === 1) {
                $role_row = $role_result->fetch_assoc();
                $user_roles[] = [
                    'type' => strtolower($role_row['role']),
                    'department_code' => null,
                    'department_name' => null,
                    'assigned_at' => null
                ];
            }
            $role_stmt->close();
        }
        
        file_put_contents('login_debug.txt', 'User roles: ' . print_r($user_roles, true) . PHP_EOL, FILE_APPEND);

        // Plain text password comparison
        if ($password === $user['password']) {
            file_put_contents('login_debug.txt', 'Password match!\n', FILE_APPEND);
            
            // Update last_activity, online_status, and last_login to current timestamp
            // Use a try-catch to handle cases where new columns don't exist yet
            try {
                $updateStmt = $conn->prepare("UPDATE users SET last_activity = NOW(), online_status = 'online', last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
                file_put_contents('login_debug.txt', 'Updated user activity with new columns' . PHP_EOL, FILE_APPEND);
            } catch (Exception $e) {
                // If new columns don't exist, just update last_activity
                file_put_contents('login_debug.txt', 'New columns not found, updating only last_activity: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
                $fallbackStmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
                $fallbackStmt->bind_param("i", $user['id']);
                $fallbackStmt->execute();
                $fallbackStmt->close();
            }
            
            // Store session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['employee_no'] = $user['employee_no'];
            $_SESSION['username'] = $user['institutional_email'];
            $_SESSION['user_roles'] = $user_roles;
            $_SESSION['is_authenticated'] = true;
            $_SESSION['user_first_name'] = $user['first_name'];
            $_SESSION['user_last_name'] = $user['last_name'];
            $_SESSION['user_title'] = $user['title'];
            
            // Clear CAPTCHA verification session
            unset($_SESSION['captcha_verified']);
            unset($_SESSION['captcha_username']);
            
            // Log session info before branching
            file_put_contents('login_debug.txt', 'user_auth: session_id=' . session_id() . ' is_authenticated=' . ($_SESSION['is_authenticated'] ? '1' : '0') . ' roles_count=' . count($user_roles) . PHP_EOL, FILE_APPEND);
            
            // Check if user has multiple roles
            if (count($user_roles) > 1) {
                // Multiple roles - redirect to role selection
                file_put_contents('login_debug.txt', 'User has multiple roles, redirecting to role selection' . PHP_EOL, FILE_APPEND);
                // Ensure session is persisted before redirect
                session_write_close();
                $sid = urlencode(session_id());
                header("Location: role_selection.php?ASCOM_SESSION={$sid}");
                // Client-side fallback
                echo '<script>window.location.href = "role_selection.php?ASCOM_SESSION=' . $sid . '";</script>';
                exit();
            } else if (count($user_roles) === 1) {
                // Single role - set it as selected and redirect directly
                $_SESSION['selected_role'] = $user_roles[0];
                $role_type = $user_roles[0]['type'];
                
                // Set role-specific session variables for backward compatibility
                switch ($role_type) {
                    case 'dean':
                        $_SESSION['dean_logged_in'] = true;
                        break;
                    case 'teacher':
                        $_SESSION['teacher_logged_in'] = true;
                        break;
                    case 'librarian':
                        $_SESSION['librarian_logged_in'] = true;
                        break;
                    case 'quality_assurance':
                        $_SESSION['admin_qa_logged_in'] = true;
                        break;
                }
                
                file_put_contents('login_debug.txt', 'User has single role: ' . $role_type . PHP_EOL, FILE_APPEND);
                // Ensure session is persisted before redirect
                session_write_close();
                $sid = urlencode(session_id());
                header("Location: successful_login.php?ASCOM_SESSION={$sid}");
                // Client-side fallback in case headers already sent
                echo '<script>window.location.href = "successful_login.php?ASCOM_SESSION=' . $sid . '";</script>';
                exit();
            } else {
                // No roles found
                file_put_contents('login_debug.txt', 'No roles found for user' . PHP_EOL, FILE_APPEND);
                header("Location: user_login.php?error=invalid_credentials");
                exit();
            }
        } else {
            file_put_contents('login_debug.txt', 'Password mismatch! Input: ' . $password . ' | DB: ' . $user['password'] . PHP_EOL, FILE_APPEND);
            // Invalid password
            header("Location: user_login.php?error=invalid_credentials");
            exit();
        }
    } else {
        file_put_contents('login_debug.txt', 'No user found or account not active for: ' . $username . PHP_EOL, FILE_APPEND);
        // User not found or account disabled
        $stmt_disabled = $conn->prepare("SELECT id FROM users WHERE institutional_email = ? AND is_active = 0");
        $stmt_disabled->bind_param("s", $username);
        $stmt_disabled->execute();
        $disabled_result = $stmt_disabled->get_result();
        if ($disabled_result->num_rows > 0) {
            file_put_contents('login_debug.txt', 'Account disabled for: ' . $username . PHP_EOL, FILE_APPEND);
            header("Location: user_login.php?error=account_disabled");
        } else {
            file_put_contents('login_debug.txt', 'Invalid credentials for: ' . $username . PHP_EOL, FILE_APPEND);
            header("Location: user_login.php?error=invalid_credentials");
        }
        exit();
    }

} catch (Exception $e) {
    // Log error (in production, log to file instead of showing)
    file_put_contents('login_debug.txt', 'Exception: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    error_log("Login error: " . $e->getMessage());
    header("Location: user_login.php?error=invalid_credentials");
    exit();
}

$stmt->close();
$conn->close();
?> 