<?php
// Suppress error reporting for clean display
error_reporting(0);
ini_set('display_errors', 0);

require_once 'super_admin_session_config.php';
session_start();

$user_role = $_SESSION['user_role'] ?? '';
$redirectUrl = 'super_admin-mis/content.php';
$roleMessage = 'Welcome, Super Admin!';

// Super Admin should always redirect to their dashboard
if ($user_role === 'super_admin' || isset($_SESSION['super_admin_logged_in'])) {
    $redirectUrl = 'super_admin-mis/content.php';
    $roleMessage = 'Welcome, Super Admin!';
} else {
    // Fallback to regular user login
    $redirectUrl = 'user_login.php';
    $roleMessage = 'Login Successful!';
}
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
    /* Fallback font in case TT Interphases fails to load */
    body {
      font-family: 'TT Interphases', Arial, sans-serif;
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
    <?php 
    $iconPath = 'src/assets/animated_icons/check-animated-icon.gif';
    if (file_exists($iconPath)) {
        echo '<img src="' . $iconPath . '" alt="Success" />';
    } else {
        echo '<div style="width: 120px; height: 120px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">✓</div>';
    }
    ?>
    <h2><?php echo htmlspecialchars($roleMessage); ?></h2>
    <p>Redirecting to your dashboard...</p>
    <button class="okay-btn" onclick="window.location.href='<?php echo htmlspecialchars($redirectUrl); ?>'">Continue</button>
  </div>

  <script>
    // Auto-redirect after 2 seconds
    setTimeout(function() {
      try {
        window.location.href = '<?php echo htmlspecialchars($redirectUrl); ?>';
      } catch (error) {
        console.error('Redirect error:', error);
        // Fallback redirect
        window.location.href = 'super_admin-mis/content.php';
      }
    }, 2000);
  </script>
</body>
</html> 