<?php
require_once 'session_config.php';
// If session ID is passed via URL (cookie blocked or first-party disabled), adopt it
if (isset($_GET[session_name()]) && is_string($_GET[session_name()])) {
    session_id($_GET[session_name()]);
}
session_start();

// Debug: track arrival at success page and the selected role
file_put_contents('login_debug.txt', 'successful_login.php hit. selected_role=' . print_r($_SESSION['selected_role'] ?? null, true) . PHP_EOL, FILE_APPEND);

// Check if user is authenticated; be tolerant and recover if user_id exists
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['is_authenticated'] = true;
        file_put_contents('login_debug.txt', "successful_login: recovered auth using user_id\n", FILE_APPEND);
    } else {
        header("Location: user_login.php");
        exit();
    }
}

// Get the selected role from session
$selectedRole = $_SESSION['selected_role'] ?? null;
$redirectUrl = 'user_login.php';
$roleMessage = 'Login Successful!';

if ($selectedRole) {
    $roleType = $selectedRole['type'];
    
    // Set role-specific session variables for backward compatibility
    switch ($roleType) {
        case 'teacher':
            $_SESSION['teacher_logged_in'] = true;
            $redirectUrl = 'teachers/content.php';
            $roleMessage = 'Welcome, Teacher!';
            break;
        case 'dean':
            $_SESSION['dean_logged_in'] = true;
            $redirectUrl = 'department-dean/content.php';
            $roleMessage = 'Welcome, Department Dean!';
            break;
        case 'librarian':
            $_SESSION['librarian_logged_in'] = true;
            $redirectUrl = 'librarian/content.php';
            $roleMessage = 'Welcome, Librarian!';
            break;
        case 'quality_assurance':
            $_SESSION['admin_qa_logged_in'] = true;
            $redirectUrl = 'admin-quality_assurance/content.php';
            $roleMessage = 'Welcome, Quality Assurance!';
            break;
        case 'super_admin':
            $_SESSION['super_admin_logged_in'] = true;
            $redirectUrl = 'super_admin-mis/content.php';
            $roleMessage = 'Welcome, Super Admin!';
            break;
        default:
            $roleMessage = 'Login Successful!';
            $redirectUrl = 'user_login.php';
    }
} else {
    // Fallback for legacy users
    $user_role = $_SESSION['user_role'] ?? '';
    switch ($user_role) {
        case 'super_admin':
            $redirectUrl = 'super_admin-mis/content.php';
            $roleMessage = 'Welcome, Super Admin!';
            break;
        case 'Department Dean':
            $redirectUrl = 'department-dean/content.php';
            $roleMessage = 'Welcome, Department Dean!';
            break;
        case 'Teacher':
            $redirectUrl = 'teachers/content.php';
            $roleMessage = 'Welcome, Teacher!';
            break;
        case 'Librarian':
            $redirectUrl = 'librarian/content.php';
            $roleMessage = 'Welcome, Librarian!';
            break;
        case 'Admin - Quality Assurance':
            $redirectUrl = 'admin-quality_assurance/content.php';
            $roleMessage = 'Welcome, Admin Quality Assurance!';
            break;
        default:
            $roleMessage = 'Login Successful!';
            $redirectUrl = 'user_login.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login Successful</title>
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
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: white;
      text-align: center;
      flex-direction: column;
    }
    .popup-message {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 40px;
      color: #000000;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      max-width: 600px;
      margin: auto;
    }
    img {
      width: 120px;
      height: auto;
    }
    h2 {
      margin: 20px 0 10px 0;
      color: #0C4B34;
    }
    p {
      color: #0C4B34;
      margin-bottom: 0;
    }
    .okay-btn {
      background: #739AFF;
      border: none;
      border-radius: 12px;
      padding: 12px 24px;
      font-size: 18px;
      cursor: pointer;
      margin-top: 20px;
      color: white;
      transition: background 0.3s ease;
    }
    .okay-btn:hover {
      background: #0bf;
    }
  </style>
</head>
<body>
  <div class="popup-message">
    <img src="src/assets/animated_icons/check-animated-icon.gif" alt="Success" />
    <h2><?php echo htmlspecialchars($roleMessage); ?></h2>
    <p>Redirecting to your dashboard...</p>
    <button class="okay-btn" id="okayBtn">OKAY (<span id="countdown">3</span>)</button>
  </div>
  <script>
    // Safety: if headers-based redirect didn't happen server-side, enforce it on client too
    // Gives us resilience if output started earlier unexpectedly
    let seconds = 3;
    const countdownSpan = document.getElementById('countdown');
    const okayBtn = document.getElementById('okayBtn');
    const redirectUrl = <?php echo json_encode($redirectUrl); ?>;

    const interval = setInterval(() => {
      seconds--;
      countdownSpan.textContent = seconds;
      if (seconds === 0) {
        clearInterval(interval);
        window.location.href = redirectUrl;
      }
    }, 1000);

    okayBtn.addEventListener('click', function() {
      window.location.href = redirectUrl;
    });

    // Absolute fallback in 5 seconds regardless of countdown state
    setTimeout(() => {
      if (window.location.href.indexOf(redirectUrl) === -1) {
        window.location.href = redirectUrl;
      }
    }, 5000);
  </script>
</body>
</html>
