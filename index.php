<?php
// Clear any existing sessions to prevent conflicts
if (session_status() == PHP_SESSION_ACTIVE) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Super Admin Login - ASCOM Monitoring System</title>
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
      position: relative;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
      flex-direction: column;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 37, 24, 0.6);
      z-index: 1;
    }

    .logo-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      margin-bottom: 40px;
      z-index: 3;
      position: relative;
    }

    .logo-image {
      width: 400px;
      height: auto;
      margin-bottom: 20px;
      z-index: 3;
      position: relative;
    }

    .portal-label {
      color: white;
      font-size: 28px;
      font-weight: bold;
      letter-spacing: 2px;
      margin-top: 10px;
      text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
      position: relative;
      z-index: 3;
    }

    .login-container {
      position: relative;
      z-index: 3;
      background: rgba(217, 217, 217, 0.1);
      backdrop-filter: blur(35px);
      padding: 40px;
      border-radius: 30px;
      box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 540px;
    }

    input {
      font-size: 20px;
      margin: 10px auto;
      padding: 20px;
      width: 500px;
      height: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      outline: none;
      transition: border 0.3s ease-in-out;
      display: block;
    }

    input::placeholder {
      color: rgba(217, 217, 217, 0.6);
    }

    input:focus {
      border: 2px solid rgb(146, 255, 213);
    }

    .password-container {
      position: relative;
      display: block;
      margin: 10px auto;
      width: fit-content;
    }

    .password-container input {
      padding-right: 20px;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 20px;
      transform: translateY(-50%);
      width: 35px;
      height: 35px;
      cursor: pointer;
    }

    button {
      font-size: 30px;
      padding: 15px;
      margin-top: 20px;
      margin-bottom: 10px;
      width: 540px;
      background: rgba(0, 119, 255, 0.5);
      color: white;
      border: 2px solid #739AFF;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease-in-out;
    }

    button:hover {
      background: rgba(115, 154, 255, 0.8);
    }

    button:disabled {
      background: rgba(92, 92, 92, 0.5);
      border: 2px solid #D9D9D9;
      cursor: not-allowed;
      color: rgba(255, 255, 255, 0.5);
    }

    input::-ms-reveal,
    input::-ms-clear {
      display: none;
    }

    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-textfield-decoration-container,
    input[type="password"]::-webkit-clear-button {
      display: none !important;
    }

    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .popup-message {
      background: #FFFFFF;
      color: black;
      padding: 30px 40px;
      border-radius: 20px;
      max-width: 90vw;
      text-align: center;
      position: relative;
    }

    .popup-message button {
      margin-top: 10px;
      width: 160px;
      padding: 10px 20px;
      background: #739AFF;
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 18px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .popup-message button:hover {
      background: #0bf;
    }

    .hidden-behind-popup {
      visibility: hidden;
    }

    .error-message {
      color: #ff6b6b;
      margin: 10px 0;
      font-size: 16px;
    }
  </style>
</head>
<body>

  <div class="overlay" id="mainOverlay"></div>

  <div class="logo-container">
    <img src="src/assets/images/ASCOM_Monitoring_System.png" alt="Logo" class="logo-image" id="logoImage">
    <div class="portal-label">Super Admin Portal</div>
  </div>

  <div class="login-container" id="loginContainer">
    <form id="loginForm" action="super_admin_auth.php" method="POST">
      <input type="text" id="username" name="username" placeholder="Enter Email" required>
      <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Enter Password" required>
        <img src="src/assets/icons/hide_password.png" alt="Toggle Password" id="togglePassword" class="toggle-password">
      </div>
      
      <?php if (isset($_GET['error'])): ?>
        <!-- Removed redundant error message label below password field -->
      <?php endif; ?>
      
      <button type="submit" id="loginButton" disabled>Login</button>
    </form>
    
    <!-- Test Account Information -->
    <!-- REMOVED: Test account label and credentials section -->
  </div>

  <!-- Error Popup -->
  <div id="errorPopup" class="popup-overlay">
    <div class="popup-message">
      <img src="src/assets/animated_icons/warning-animated-icon.gif" alt="Error GIF" style="width: 120px; height: auto;">
      <h2 id="errorPopupText">Incorrect password. Please try again.</h2>
      <button id="closePopupBtn">CLOSE (5)</button>
    </div>
  </div>

  <script>
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const loginButton = document.getElementById("loginButton");
    const togglePassword = document.getElementById("togglePassword");

    const errorPopup = document.getElementById("errorPopup");
    const closePopupBtn = document.getElementById("closePopupBtn");
    const errorPopupText = document.getElementById("errorPopupText");

    const logoImage = document.getElementById("logoImage");
    const loginContainer = document.getElementById("loginContainer");
    const mainOverlay = document.getElementById("mainOverlay");

    function validateInputs() {
      const username = usernameInput.value.trim();
      const password = passwordInput.value.trim();
      loginButton.disabled = !(username && password.length >= 6);
    }

    usernameInput.addEventListener("input", validateInputs);
    passwordInput.addEventListener("input", validateInputs);

    togglePassword.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";
      passwordInput.type = isPassword ? "text" : "password";
      togglePassword.src = isPassword ? "src/assets/icons/show_password.png" : "src/assets/icons/hide_password.png";
    });

    closePopupBtn.addEventListener("click", () => {
      errorPopup.style.display = "none";
    });

    function showErrorPopupWithTimer() {
      errorPopup.style.display = "flex";
      let remaining = 5;
      closePopupBtn.textContent = `CLOSE (${remaining})`;

      const countdown = setInterval(() => {
        remaining--;
        closePopupBtn.textContent = `CLOSE (${remaining})`;
        if (remaining === 0) {
          clearInterval(countdown);
          errorPopup.style.display = "none";
        }
      }, 1000);
    }

    window.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const error = urlParams.get('error');

      if (error === 'invalid_credentials') {
        errorPopupText.textContent = "Incorrect username or password. Please try again.";
        showErrorPopupWithTimer();
      }

      // Remove query parameters from the URL
      if (error) {
        history.replaceState(null, "", window.location.pathname);
      }
    });
  </script>
</body>
</html> 