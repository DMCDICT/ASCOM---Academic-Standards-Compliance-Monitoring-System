<?php

require_once 'session_config.php';
require_once 'bootstrap/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!ascom_authenticated_for_regular_user()) {
    header('Location: user_login.php');
    exit();
}

$selectedRole = $_SESSION['selected_role'] ?? null;
if (!is_array($selectedRole) || empty($selectedRole['type'])) {
    header('Location: role_selection.php');
    exit();
}

ascom_set_selected_role($selectedRole);

$redirectMap = [
    'teacher' => ['path' => 'teachers/content.php', 'message' => 'Welcome, Teacher!'],
    'dean' => ['path' => 'department-dean/content.php', 'message' => 'Welcome, Department Dean!'],
    'librarian' => ['path' => 'librarian/content.php', 'message' => 'Welcome, Librarian!'],
    'quality_assurance' => ['path' => 'admin-quality_assurance/content.php', 'message' => 'Welcome, Quality Assurance!'],
];

$target = $redirectMap[$selectedRole['type']] ?? ['path' => 'user_login.php', 'message' => 'Login Successful!'];
$redirectUrl = $target['path'];
$roleMessage = $target['message'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
  </script>
</body>
</html>
