<?php

require_once 'session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';
require_once 'bootstrap/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = $_POST['password'] ?? '';
$captchaVerified = !empty($_SESSION['captcha_verified']);
$captchaUsername = $_SESSION['captcha_username'] ?? '';

if ($username === '' || $password === '') {
    header('Location: user_login.php?error=invalid_credentials');
    exit();
}

try {
    $stmt = $conn->prepare('SELECT id, employee_no, institutional_email, password, role_id, is_active, last_activity, first_name, last_name, title, department_id FROM users WHERE institutional_email = ? AND is_active = 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmtDisabled = $conn->prepare('SELECT id FROM users WHERE institutional_email = ? AND is_active = 0');
        $stmtDisabled->bind_param('s', $username);
        $stmtDisabled->execute();
        $disabledResult = $stmtDisabled->get_result();

        $redirect = $disabledResult->num_rows > 0
            ? 'user_login.php?error=account_disabled'
            : 'user_login.php?error=invalid_credentials';

        $stmtDisabled->close();
        header("Location: {$redirect}");
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    $isInactive = false;
    if (!empty($user['last_activity'])) {
        $lastActivity = new DateTime($user['last_activity']);
        $isInactive = (new DateTime())->diff($lastActivity)->days > 30;
    }

    if ($isInactive && !$captchaVerified) {
        header('Location: captcha_verification.php?username=' . urlencode($username));
        exit();
    }

    if ($captchaVerified && $captchaUsername !== $username) {
        header('Location: user_login.php?error=invalid_credentials');
        exit();
    }

    $userRoles = [];

    if ((int) $user['role_id'] === 4) {
        $deptCode = null;
        $deptName = null;
        if (!empty($user['department_id'])) {
            $deptQuery = $conn->prepare('SELECT department_code, department_name FROM departments WHERE id = ?');
            $deptQuery->bind_param('i', $user['department_id']);
            $deptQuery->execute();
            $deptRes = $deptQuery->get_result();
            if ($deptRes && $deptRes->num_rows > 0) {
                $deptRow = $deptRes->fetch_assoc();
                $deptCode = $deptRow['department_code'];
                $deptName = $deptRow['department_name'];
            }
            $deptQuery->close();
        }

        $userRoles[] = [
            'type' => 'teacher',
            'department_code' => $deptCode,
            'department_name' => $deptName,
            'assigned_at' => null,
        ];
    }

    $deanQuery = $conn->prepare('SELECT department_code, department_name FROM departments WHERE dean_user_id = ?');
    $deanQuery->bind_param('i', $user['id']);
    $deanQuery->execute();
    $deanRes = $deanQuery->get_result();
    if ($deanRes && $deanRes->num_rows > 0) {
        while ($row = $deanRes->fetch_assoc()) {
            $userRoles[] = [
                'type' => 'dean',
                'department_code' => $row['department_code'],
                'department_name' => $row['department_name'],
                'assigned_at' => null,
            ];
        }
    }
    $deanQuery->close();

    $roleStmt = $conn->prepare("SELECT role_name, assigned_at FROM user_roles WHERE user_id = ? AND is_active = 1 AND role_name IN ('librarian','quality_assurance')");
    $roleStmt->bind_param('i', $user['id']);
    $roleStmt->execute();
    $roleRes = $roleStmt->get_result();
    if ($roleRes && $roleRes->num_rows > 0) {
        while ($row = $roleRes->fetch_assoc()) {
            $userRoles[] = [
                'type' => strtolower($row['role_name']),
                'department_code' => null,
                'department_name' => null,
                'assigned_at' => $row['assigned_at'],
            ];
        }
    }
    $roleStmt->close();

    if (count($userRoles) === 0) {
        $legacyRoleStmt = $conn->prepare('SELECT role FROM roles WHERE id = ?');
        $legacyRoleStmt->bind_param('i', $user['role_id']);
        $legacyRoleStmt->execute();
        $legacyRoleRes = $legacyRoleStmt->get_result();
        if ($legacyRoleRes && $legacyRoleRes->num_rows === 1) {
            $legacyRole = $legacyRoleRes->fetch_assoc();
            $userRoles[] = [
                'type' => strtolower($legacyRole['role']),
                'department_code' => null,
                'department_name' => null,
                'assigned_at' => null,
            ];
        }
        $legacyRoleStmt->close();
    }

    $verified = ascom_verify_password_with_migration(
        $password,
        $user['password'] ?? null,
        function (string $newHash) use ($conn, $user): void {
            $updatePassword = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
            $updatePassword->bind_param('si', $newHash, $user['id']);
            $updatePassword->execute();
            $updatePassword->close();
        }
    );

    if (!$verified) {
        header('Location: user_login.php?error=invalid_credentials');
        exit();
    }

    try {
        $updateStmt = $conn->prepare("UPDATE users SET last_activity = NOW(), online_status = 'online', last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param('i', $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
    } catch (Throwable $e) {
        $fallbackStmt = $conn->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?');
        $fallbackStmt->bind_param('i', $user['id']);
        $fallbackStmt->execute();
        $fallbackStmt->close();
    }

    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['employee_no'] = $user['employee_no'];
    $_SESSION['username'] = $user['institutional_email'];
    $_SESSION['user_roles'] = $userRoles;
    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_title'] = $user['title'];
    $_SESSION['_last_session_regeneration'] = time();

    unset($_SESSION['captcha_verified'], $_SESSION['captcha_username'], $_SESSION['captcha_verification_time'], $_SESSION['captcha_answer']);

    if (count($userRoles) > 1) {
        header('Location: role_selection.php');
        exit();
    }

    if (count($userRoles) === 1) {
        ascom_set_selected_role($userRoles[0]);
        header('Location: successful_login.php');
        exit();
    }

    header('Location: user_login.php?error=invalid_credentials');
    exit();
} catch (Throwable $e) {
    header('Location: user_login.php?error=invalid_credentials');
    exit();
}
