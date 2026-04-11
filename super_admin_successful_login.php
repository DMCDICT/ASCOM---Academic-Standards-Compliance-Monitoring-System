<?php

require_once 'super_admin_session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isSuperAdminAuthenticated()) {
    header('Location: super_admin_login.php');
    exit();
}

secureSuperAdminSession();
$redirectUrl = 'super_admin-mis/content.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Super Admin Login Successful</title>
  <style>
    @font-face {
      font-family: 'TT Interphases';
      src: url('src/assets/fonts/tt-interphases/TT Interphases Pro Trial Bold.ttf') format('truetype');
      font-weight: normal;
      font-style: normal;
    }
    body {
      background: #0C4B34;
      font-family: 'TT Interphases', Arial, sans-serif;
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
  </style>
</head>
<body>
  <div class="popup-message">
    <img src="src/assets/animated_icons/check-animated-icon.gif" alt="Success" />
    <h2>Welcome, Super Admin!</h2>
    <p>Redirecting to your dashboard...</p>
    <button class="okay-btn" onclick="window.location.href='<?php echo htmlspecialchars($redirectUrl); ?>'">Continue</button>
  </div>

  <script>
    setTimeout(function() {
      window.location.href = <?php echo json_encode($redirectUrl); ?>;
    }, 2000);
  </script>
</body>
</html>
