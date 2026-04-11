<?php
// index.php - Portal Gateway for ASCOM Academic Standards Compliance Monitoring System
if (session_status() == PHP_SESSION_ACTIVE) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Entrance Portal - ASCOM Monitoring System</title>
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
      margin: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 37, 24, 0.4);
      z-index: 1;
    }

    .container {
      position: relative;
      z-index: 3;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 40px;
      width: 100%;
      max-width: 900px;
    }

    .logo-container {
      margin-bottom: 20px;
    }

    .logo-image {
      width: 320px;
      height: auto;
    }

    .portal-selection {
      display: flex;
      gap: 30px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .portal-card {
      background: rgba(217, 217, 217, 0.1);
      backdrop-filter: blur(25px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 25px;
      padding: 40px 30px;
      width: 300px;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      text-decoration: none;
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .portal-card:hover {
      transform: translateY(-15px);
      background: rgba(255, 255, 255, 0.15);
      border-color: rgba(146, 255, 213, 0.5);
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }

    .portal-icon {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
      filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
    }

    .portal-title {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 15px;
      letter-spacing: 1px;
    }

    .portal-desc {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.5;
    }

    .footer-text {
      position: absolute;
      bottom: 30px;
      color: rgba(255, 255, 255, 0.4);
      font-size: 12px;
      letter-spacing: 1px;
      z-index: 3;
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .container > * {
      animation: fadeIn 0.8s ease forwards;
    }

    .portal-card:nth-child(1) { animation-delay: 0.2s; }
    .portal-card:nth-child(2) { animation-delay: 0.4s; }

  </style>
</head>
<body>
  <div class="overlay"></div>

  <div class="container">
    <div class="logo-container">
      <img src="src/assets/images/ASCOM_Monitoring_System.png" alt="ASCOM Logo" class="logo-image">
    </div>

    <div class="portal-selection">
      <a href="user_login.php" class="portal-card">
        <img src="src/assets/icons/users-icon.png" alt="User Portal" class="portal-icon">
        <div class="portal-title">User Portal</div>
        <div class="portal-desc">Access for Faculty, Deans, Librarians, and Quality Assurance personnel.</div>
      </a>

      <a href="super_admin_login.php" class="portal-card">
        <img src="src/assets/icons/academic-icon.png" alt="Super Admin Portal" class="portal-icon">
        <div class="portal-title">Super Admin</div>
        <div class="portal-desc">Administrative management for MIS and system-wide configurations.</div>
      </a>
    </div>
  </div>

  <div class="footer-text">© 2026 ASCOM Monitoring System. All Rights Reserved.</div>
</body>
</html>
