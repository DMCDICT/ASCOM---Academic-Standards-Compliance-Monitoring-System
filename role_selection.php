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

    $stmt = $conn->prepare('SELECT id, employee_no, institutional_email, email, role_id, is_active, first_name, last_name, title, department_id FROM users WHERE (institutional_email = ? OR email = ?) AND is_active = 1');
    $stmt->bind_param('ss', $captchaUsername, $captchaUsername);
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

    $userRoles = array();

    // Check if user is Dean (via departments.dean_user_id)
    $deptQuery = $conn->prepare('SELECT department_code, department_name FROM departments WHERE dean_user_id = ?');
    $deptQuery->bind_param('i', $user['id']);
    $deptQuery->execute();
    $deptRes = $deptQuery->get_result();
    while ($deptRes && ($deptRow = $deptRes->fetch_assoc())) {
        $userRoles[] = array(
            'type' => 'dean',
            'department_code' => $deptRow['department_code'],
            'department_name' => $deptRow['department_name'],
            'assigned_at' => null,
        );
    }
    $deptQuery->close();

    // Check if user is Teacher based on role_id (2 = Dean, 3 = Teacher in original system)
    // OR if user has a department assigned (faculty member)
    $isTeacherByRole = ((int)$user['role_id'] === 2 || (int)$user['role_id'] === 3);
    $hasDepartment = !empty($user['department_id']);
    
    if ($isTeacherByRole || $hasDepartment) {
        $deptCode = null;
        $deptName = null;
        if (!empty($user['department_id'])) {
            $facultyDeptQuery = $conn->prepare('SELECT department_code, department_name FROM departments WHERE id = ?');
            $facultyDeptQuery->bind_param('i', $user['department_id']);
            $facultyDeptQuery->execute();
            $facultyDeptRes = $facultyDeptQuery->get_result();
            if ($facultyDeptRes && $facultyDeptRes->num_rows > 0) {
                $facultyDept = $facultyDeptRes->fetch_assoc();
                $deptCode = $facultyDept['department_code'];
                $deptName = $facultyDept['department_name'];
            }
            $facultyDeptQuery->close();
        }
        $userRoles[] = array(
            'type' => 'teacher',
            'department_code' => $deptCode,
            'department_name' => $deptName,
            'assigned_at' => null,
        );
    }

    // Check other roles (librarian, QA) from user_roles table
    $roleQuery = $conn->prepare("SELECT r.role_name, ur.assigned_at FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.role_name IN ('librarian','quality_assurance','qa') AND ur.is_active = 1");
    $roleQuery->bind_param('i', $user['id']);
    $roleQuery->execute();
    $roleRes = $roleQuery->get_result();
    while ($roleRes && ($roleRow = $roleRes->fetch_assoc())) {
        $roleType = strtolower($roleRow['role_name']);
        if ($roleType === 'qa') {
            $roleType = 'quality_assurance';
        }
        $userRoles[] = array(
            'type' => $roleType,
            'department_code' => null,
            'department_name' => null,
            'assigned_at' => $roleRow['assigned_at'],
        );
    }
    $roleQuery->close();

    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['employee_no'] = $user['employee_no'];
    $_SESSION['username'] = $user['institutional_email'] ?? $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_title'] = $user['title'];
    $_SESSION['user_roles'] = $userRoles;
    $_SESSION['_last_session_regeneration'] = time();

    unset($_SESSION['captcha_verified'], $_SESSION['captcha_username'], $_SESSION['captcha_verification_time'], $_SESSION['captcha_answer']);

    // Check if user has both dean AND teacher roles
    $hasDean = false;
    $hasTeacher = false;
    
    foreach ($userRoles as $role) {
        if ($role['type'] === 'dean') {
            $hasDean = true;
        }
        if ($role['type'] === 'teacher') {
            $hasTeacher = true;
        }
    }
    
    // If user has both dean AND teacher, redirect to integrated Dean interface
    if ($hasDean && $hasTeacher) {
        foreach ($userRoles as $role) {
            if ($role['type'] === 'dean') {
                ascom_set_selected_role($role);
                break;
            }
        }
        header('Location: successful_login.php');
        exit();
    }
    
    // If only one role, auto-redirect
    if (count($userRoles) === 1) {
        ascom_set_selected_role($userRoles[0]);
        header('Location: successful_login.php');
        exit();
    }
    
    // Show role selection page below
}

// Handle role selection form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
    $selectedRole = $_POST['selected_role'];
    foreach ($_SESSION['user_roles'] as $role) {
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
        }
        body {
            background: #0C4B34;
            font-family: 'TT Interphases', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .role-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 {
            color: #0C4B34;
            margin: 0 0 10px 0;
        }
        p {
            color: #666;
            margin: 0 0 30px 0;
        }
        .role-btn {
            display: block;
            width: 100%;
            padding: 20px;
            margin: 10px 0;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            color: #333;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }
        .role-btn:hover {
            background: #0C4B34;
            border-color: #0C4B34;
            color: white;
        }
        .role-type {
            font-size: 20px;
            font-weight: 700;
        }
        .role-dept {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .role-btn:hover .role-dept {
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="role-card">
            <h1>Select Your Role</h1>
            <p>You have multiple roles. Please select the role you want to use:</p>
            <form method="POST">
                <?php foreach ($_SESSION['user_roles'] as $role): ?>
                    <button type="submit" name="selected_role" value="<?php echo htmlspecialchars($role['type']); ?>" class="role-btn">
                        <div class="role-type"><?php echo ucfirst(htmlspecialchars($role['type'])); ?></div>
                        <?php if (!empty($role['department_name'])): ?>
                            <div class="role-dept">- <?php echo htmlspecialchars($role['department_name']); ?></div>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</body>
</html>