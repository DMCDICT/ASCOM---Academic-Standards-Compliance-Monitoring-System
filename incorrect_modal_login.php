<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Error - ASCOM Monitoring System</title>
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
    }
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4);
      display: flex;
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
  </style>
</head>
<body>
  <div class="popup-overlay">
    <div class="popup-message">
      <img src="src/assets/animated_icons/warning-animated-icon.gif" alt="Error GIF" style="width: 120px; height: auto;">
      <h2>Incorrect username or password.<br>Please try again.</h2>
      <button onclick="window.location.href='index.php'">Back to Login</button>
    </div>
  </div>
</body>
</html> 