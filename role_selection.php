<?php

require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/super_admin-mis/includes/db_connection.php';
require_once __DIR__ . '/bootstrap/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$captchaVerified = !empty($_SESSION['captcha_verified']);
$captchaUsername = $_SESSION['captcha_username'] ?? '';

if (!ascom_authenticated_for_regular_user() && !$captchaVerified) {
    header('Location: user_login.php');
    exit();
}

if ($captchaVerified && !ascom_authenticated_for_regular_user()) {
    if ($captchaUsername === '') {
        header('Location: user_login.php?error=invalid_credentials');
        exit();
    }

    $stmt = $conn->prepare('SELECT id, employee_no, institutional_email, role_id, is_active, first_name, last_name, title, department_id FROM users WHERE institutional_email = ? AND is_active = 1');
    $stmt->bind_param('s', $captchaUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user) {
        header('Location: user_login.php?error=invalid_credentials');
        exit();
    }

    $updateStmt = $conn->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?');
    $updateStmt->bind_param('i', $user['id']);
    $updateStmt->execute();
    $updateStmt->close();

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
                $dept = $deptRes->fetch_assoc();
                $deptCode = $dept['department_code'];
                $deptName = $dept['department_name'];
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
    while ($deanRes && ($row = $deanRes->fetch_assoc())) {
        $userRoles[] = [
            'type' => 'dean',
            'department_code' => $row['department_code'],
            'department_name' => $row['department_name'],
            'assigned_at' => null,
        ];
    }
    $deanQuery->close();

    $roleStmt = $conn->prepare("SELECT role_name, assigned_at FROM user_roles WHERE user_id = ? AND is_active = 1 AND role_name IN ('librarian','quality_assurance')");
    $roleStmt->bind_param('i', $user['id']);
    $roleStmt->execute();
    $roleRes = $roleStmt->get_result();
    while ($roleRes && ($row = $roleRes->fetch_assoc())) {
        $userRoles[] = [
            'type' => strtolower($row['role_name']),
            'department_code' => null,
            'department_name' => null,
            'assigned_at' => $row['assigned_at'],
        ];
    }
    $roleStmt->close();

    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['employee_no'] = $user['employee_no'];
    $_SESSION['username'] = $user['institutional_email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_title'] = $user['title'];
    $_SESSION['user_roles'] = $userRoles;
    $_SESSION['_last_session_regeneration'] = time();

    unset($_SESSION['captcha_verified'], $_SESSION['captcha_username'], $_SESSION['captcha_verification_time'], $_SESSION['captcha_answer']);

    if (count($userRoles) === 1) {
        ascom_set_selected_role($userRoles[0]);
        header('Location: successful_login.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
    $selectedRole = $_POST['selected_role'];
    foreach (($_SESSION['user_roles'] ?? []) as $role) {
        if (($role['type'] ?? null) === $selectedRole) {
            ascom_set_selected_role($role);
            header('Location: successful_login.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - ASCOM Monitoring System</title>
    <style>
        @font-face {
            font-family: 'TT Interphases';
            src: url('src/assets/fonts/tt-interphases/TT Interphases Pro Trial Bold.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            background: #0C4B34;
            font-family: 'TT Interphases', sans-serif;
            text-align: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            overflow: hidden;
        }

        .role-selection-container {
            background: rgba(217, 217, 217, 0.1);
            backdrop-filter: blur(35px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 560px;
            width: 100%;
        }

        .welcome-text {
            color: white;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .user-info {
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            margin-bottom: 40px;
        }

        .role-options {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .role-button {
            border: none;
            border-radius: 18px;
            padding: 18px 20px;
            font-size: 18px;
            cursor: pointer;
            background: white;
            color: #0C4B34;
            font-family: inherit;
            font-weight: bold;
        }

        .role-button:hover {
            background: #e8f5ef;
        }
    </style>
</head>
<body>
    <div class="role-selection-container">
        <div class="welcome-text">Select Your Role</div>
        <div class="user-info">
            <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
        </div>

        <form method="POST" class="role-options">
            <?php foreach (($_SESSION['user_roles'] ?? []) as $role): ?>
                <button class="role-button" type="submit" name="selected_role" value="<?php echo htmlspecialchars($role['type']); ?>">
                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role['type']))); ?>
                    <?php if (!empty($role['department_name'])): ?>
                        - <?php echo htmlspecialchars($role['department_name']); ?>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </form>
    </div>
</body>
</html>
