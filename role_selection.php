<?php
// role_selection.php
// This page appears after successful login for users with multiple roles

// Ensure consistent session settings and name across the app
require_once __DIR__ . '/session_config.php';
if (session_status() == PHP_SESSION_NONE) {
    // Accept explicit session id via URL if provided
    if (isset($_GET[session_name()]) && is_string($_GET[session_name()])) {
        session_id($_GET[session_name()]);
    }
    session_start();
}

// Check if user is authenticated or captcha verified
$captchaVerified = (isset($_GET['captcha_verified']) && $_GET['captcha_verified'] === 'true') || 
                   (isset($_SESSION['captcha_verified']) && $_SESSION['captcha_verified'] === true);
$captchaUsername = $_GET['username'] ?? $_SESSION['captcha_username'] ?? '';

file_put_contents('login_debug.txt', 'Role selection - captcha verified: ' . ($captchaVerified ? 'YES' : 'NO') . ' | username: ' . $captchaUsername . ' | session auth: ' . (isset($_SESSION['is_authenticated']) ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);

if ((!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) && !$captchaVerified) {
    file_put_contents('login_debug.txt', 'Role selection - redirecting to login (no auth, no captcha)' . PHP_EOL, FILE_APPEND);
    header("Location: user_login.php");
    exit();
}

// If captcha verified, we need to authenticate the user and set up their session
if ($captchaVerified && !empty($captchaUsername)) {
    // Check if captcha verification is not too old (within 10 minutes)
    $captchaTime = $_SESSION['captcha_verification_time'] ?? 0;
    if ($captchaTime > 0 && (time() - $captchaTime) > 600) { // 10 minutes timeout
        file_put_contents('login_debug.txt', 'CAPTCHA verification expired, redirecting to login' . PHP_EOL, FILE_APPEND);
        header("Location: user_login.php?error=captcha_expired");
        exit();
    }
    // Include database connection
    require_once 'super_admin-mis/includes/db_connection.php';
    
    if ($conn->connect_error) {
        header("Location: user_login.php?error=invalid_credentials");
        exit();
    }
    
    try {
        // Query the users table to find user by institutional_email
        $stmt = $conn->prepare("SELECT id, employee_no, institutional_email, password, role_id, is_active, last_activity, first_name, last_name, title, department_id FROM users WHERE institutional_email = ? AND is_active = 1");
        $stmt->bind_param("s", $captchaUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Update last_activity to current time since they just logged in
            $updateStmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // Set up session data (similar to user_auth.php logic)
            $_SESSION['is_authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['institutional_email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['title'] = $user['title'];
            $_SESSION['department_id'] = $user['department_id'];
            
            // Build user roles (same logic as user_auth.php)
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
                }
                
                if ($deptCode && $deptName) {
                    $user_roles[] = [
                        'type' => 'teacher',
                        'department_code' => $deptCode,
                        'department_name' => $deptName,
                        'assigned_at' => null
                    ];
                }
            }
            
            // 2) Dean role from departments table (where user is the dean)
            $deanQuery = $conn->prepare("
                SELECT department_code, department_name 
                FROM departments 
                WHERE dean_user_id = ?
            ");
            $deanQuery->bind_param("i", $user['id']);
            $deanQuery->execute();
            $deanRes = $deanQuery->get_result();
            
            while ($deanRow = $deanRes->fetch_assoc()) {
                $user_roles[] = [
                    'type' => 'dean',
                    'department_code' => $deanRow['department_code'],
                    'department_name' => $deanRow['department_name'],
                    'assigned_at' => null
                ];
            }
            
            // 3) Librarian role from user_roles table (no department info available)
            $librarianQuery = $conn->prepare("
                SELECT assigned_at 
                FROM user_roles 
                WHERE user_id = ? AND role_name = 'librarian' AND is_active = 1
            ");
            $librarianQuery->bind_param("i", $user['id']);
            $librarianQuery->execute();
            $librarianRes = $librarianQuery->get_result();
            
            while ($librarianRow = $librarianRes->fetch_assoc()) {
                $user_roles[] = [
                    'type' => 'librarian',
                    'department_code' => null,
                    'department_name' => null,
                    'assigned_at' => $librarianRow['assigned_at']
                ];
            }
            
            $_SESSION['user_roles'] = $user_roles;
            
            // Clear captcha verification data after successful authentication
            unset($_SESSION['captcha_verified']);
            unset($_SESSION['captcha_username']);
            unset($_SESSION['captcha_verification_time']);
            
            file_put_contents('login_debug.txt', 'CAPTCHA verified user authenticated: ' . $captchaUsername . ' | Roles: ' . print_r($user_roles, true) . PHP_EOL, FILE_APPEND);
            
        } else {
            header("Location: user_login.php?error=invalid_credentials");
            exit();
        }
    } catch (Exception $e) {
        file_put_contents('login_debug.txt', 'CAPTCHA verification error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        header("Location: user_login.php?error=invalid_credentials");
        exit();
    }
}

// Debug: classic role selection page hit
file_put_contents('login_debug.txt', 'classic role_selection.php hit. user_roles=' . print_r($_SESSION['user_roles'] ?? null, true) . PHP_EOL, FILE_APPEND);

// Check if user has multiple roles
if (!isset($_SESSION['user_roles']) || count($_SESSION['user_roles']) <= 1) {
    // If only one role, redirect to successful login page
    if (isset($_SESSION['user_roles'][0])) {
        $role = $_SESSION['user_roles'][0];
        $_SESSION['selected_role'] = $role;
        header("Location: successful_login.php");
        exit();
    }
}

// Handle role selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
    $selectedRole = $_POST['selected_role'];
    
    // Validate that the user has this role
    $hasRole = false;
    foreach ($_SESSION['user_roles'] as $role) {
        if ($role['type'] === $selectedRole) {
            $hasRole = true;
            $_SESSION['selected_role'] = $role;
            break;
        }
    }
    
    if ($hasRole) {
        // Redirect to successful login page instead of directly to dashboard
        header("Location: successful_login.php");
        // Client-side fallback if headers already sent
        echo '<script>window.location.href = "successful_login.php";</script>';
        exit();
    }
}

function getRoleDashboard($roleType) {
    switch ($roleType) {
        case 'teacher':
            return 'teacher/dashboard.php';
        case 'dean':
            return 'dean/dashboard.php';
        case 'librarian':
            return 'librarian/dashboard.php';
        case 'quality_assurance':
            return 'qa/dashboard.php';
        default:
            return 'user_login.php';
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
            gap: 20px;
            margin-bottom: 30px;
        }

        .role-option {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            text-align: left;
        }

        .role-option:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(146, 255, 213, 0.8);
            transform: translateY(-2px);
        }

        .role-option.selected {
            background: rgba(146, 255, 213, 0.2);
            border-color: rgb(146, 255, 213);
        }

        .role-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .role-description {
            font-size: 14px;
            opacity: 0.8;
        }

        .role-department {
            font-size: 12px;
            opacity: 0.6;
            margin-top: 5px;
        }

        .continue-button {
            font-size: 20px;
            padding: 15px 30px;
            background: rgba(0, 119, 255, 0.5);
            color: white;
            border: 2px solid #739AFF;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .continue-button:hover:not(:disabled) {
            background: rgba(115, 154, 255, 0.8);
        }

        .continue-button:disabled {
            background: rgba(92, 92, 92, 0.5);
            border-color: #D9D9D9;
            cursor: not-allowed;
            color: rgba(255, 255, 255, 0.5);
        }

        .logout-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            display: inline-block;
        }

        .logout-link:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="role-selection-container">
        <div class="welcome-text">Welcome to ASCOM Monitoring System</div>
        <div class="user-info">
            <?php echo htmlspecialchars($_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']); ?>
        </div>

        <form id="roleForm" method="POST">
            <div class="role-options">
                <?php foreach ($_SESSION['user_roles'] as $role): ?>
                    <div class="role-option" data-role="<?php echo htmlspecialchars($role['type']); ?>">
                        <div class="role-title">
                            <?php echo getRoleDisplayName($role['type']); ?>
                        </div>
                        <div class="role-description">
                            <?php echo getRoleDescription($role['type']); ?>
                        </div>
                        <?php if (isset($role['department_name']) && $role['department_name']): ?>
                            <div class="role-department">
                                Department: <?php echo htmlspecialchars($role['department_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="continue-button" id="continueBtn" disabled>
                Continue
            </button>
        </form>

        <a href="user_login.php" class="logout-link">Logout</a>
    </div>

    <script>
        const roleOptions = document.querySelectorAll('.role-option');
        const continueBtn = document.getElementById('continueBtn');
        const roleForm = document.getElementById('roleForm');
        let selectedRole = null;

        roleOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                roleOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Get the role type
                selectedRole = this.dataset.role;
                
                // Enable continue button
                continueBtn.disabled = false;
            });
        });

        roleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (selectedRole) {
                // Create a hidden input for the selected role
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_role';
                hiddenInput.value = selectedRole;
                roleForm.appendChild(hiddenInput);
                
                // Submit the form
                roleForm.submit();
            }
        });
    </script>
</body>
</html>

<?php
function getRoleDisplayName($roleType) {
    switch ($roleType) {
        case 'teacher':
            return '👨‍🏫 Teacher';
        case 'dean':
            return '👨‍💼 Department Dean';
        case 'librarian':
            return '📚 Librarian';
        case 'quality_assurance':
            return '🔍 Quality Assurance';
        default:
            return ucfirst($roleType);
    }
}

function getRoleDescription($roleType) {
    switch ($roleType) {
        case 'teacher':
            return 'Access to class management, grading, and student records';
        case 'dean':
            return 'Department oversight, teacher management, and academic planning';
        case 'librarian':
            return 'Library management, resource tracking, and book circulation';
        case 'quality_assurance':
            return 'Quality monitoring, compliance checking, and system oversight';
        default:
            return 'System access and management';
    }
}
?>
