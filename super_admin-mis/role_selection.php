<?php
require_once dirname(__FILE__) . '/../session_config.php';
if (isset($_GET[session_name()]) && is_string($_GET[session_name()])) {
    session_id($_GET[session_name()]);
}
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Role - ASCOM Monitoring System</title>
    <style>
        @font-face {
            font-family: 'TT Interphases';
            src: url('../src/assets/fonts/tt-interphases/TT Interphases Pro Trial Regular.ttf') format('truetype');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'TT Interphases', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .role-selection-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .welcome-header {
            margin-bottom: 30px;
        }
        
        .welcome-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .welcome-header p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #739AFF;
        }
        
        .user-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .user-details {
            color: #666;
            font-size: 14px;
        }
        
        .roles-container {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .role-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            position: relative;
            overflow: hidden;
        }
        
        .role-card:hover {
            border-color: #739AFF;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(115, 154, 255, 0.15);
        }
        
        .role-card.selected {
            border-color: #739AFF;
            background: linear-gradient(135deg, #739AFF 0%, #5a7cfa 100%);
            color: white;
        }
        
        .role-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .role-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .role-card:not(.selected) .role-icon {
            background: #739AFF;
            color: white;
        }
        
        .role-card.selected .role-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .role-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .role-card:not(.selected) .role-title {
            color: #333;
        }
        
        .role-card.selected .role-title {
            color: white;
        }
        
        .role-description {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        
        .role-card:not(.selected) .role-description {
            color: #666;
        }
        
        .role-card.selected .role-description {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .role-department {
            font-size: 12px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        
        .role-card:not(.selected) .role-department {
            background: #e9ecef;
            color: #666;
        }
        
        .role-card.selected .role-department {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .continue-btn {
            background: #739AFF;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        
        .continue-btn:hover {
            background: #5a7cfa;
            transform: translateY(-1px);
        }
        
        .continue-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #739AFF;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="role-selection-container">
        <div class="welcome-header">
            <h1>Welcome Back!</h1>
            <p>You have multiple roles available. Please select which role you'd like to access:</p>
        </div>
        
        <div class="error-message" id="errorMessage"></div>
        
        <div class="user-info" id="userInfo">
            <div class="user-name" id="userName">Loading...</div>
            <div class="user-details" id="userDetails">Employee No: Loading...</div>
        </div>
        
        <div class="roles-container" id="rolesContainer">
            <!-- Roles will be loaded here -->
        </div>
        
        <button class="continue-btn" id="continueBtn" disabled>
            Continue
        </button>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Switching to selected role...</p>
        </div>
    </div>

    <script>
        let selectedRole = null;
        let userData = null;
        
        // Load user roles on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUserRoles();
        });
        
        function loadUserRoles() {
            const userId = <?php echo $userId; ?>;
            
            fetch(`api/get_user_roles.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userData = data;
                        displayUserInfo(data.user);
                        displayRoles(data.roles);
                    } else {
                        showError('Failed to load user roles: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to load user roles. Please try again.');
                });
        }
        
        function displayUserInfo(user) {
            document.getElementById('userName').textContent = user.display_name;
            document.getElementById('userDetails').textContent = `Employee No: ${user.employee_no}`;
        }
        
        function displayRoles(roles) {
            const container = document.getElementById('rolesContainer');
            container.innerHTML = '';
            
            roles.forEach(role => {
                const roleCard = document.createElement('div');
                roleCard.className = 'role-card';
                roleCard.onclick = (e) => selectRole(e.currentTarget, role);
                
                const icon = role.type === 'dean' ? '👨‍💼' : '👨‍🏫';
                const title = role.type === 'dean' ? 'Department Dean' : 'Teacher';
                const description = role.type === 'dean' 
                    ? 'Access department management tools, view faculty information, and manage academic programs.'
                    : 'Access teaching resources, view schedules, and manage your courses.';
                
                roleCard.innerHTML = `
                    <div class="role-header">
                        <div class="role-icon">${icon}</div>
                        <div>
                            <div class="role-title">${title}</div>
                            <div class="role-department">${role.department_name}</div>
                        </div>
                    </div>
                    <div class="role-description">${description}</div>
                `;
                
                container.appendChild(roleCard);
            });
        }
        
        function selectRole(roleElement, role) {
            // Remove previous selection
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            roleElement.classList.add('selected');
            
            selectedRole = role;
            document.getElementById('continueBtn').disabled = false;
        }
        
        function continueToRole() {
            if (!selectedRole) return;
            
            const loading = document.getElementById('loading');
            const continueBtn = document.getElementById('continueBtn');
            
            loading.style.display = 'block';
            continueBtn.disabled = true;
            
            // Store selected role in session
            fetch('set_selected_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    role_type: selectedRole.type,
                    department_code: selectedRole.department_code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Always route through the central success page to set flags and show message
                    window.location.href = '../successful_login.php';
                } else {
                    showError('Failed to set role: ' + data.message);
                    loading.style.display = 'none';
                    continueBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to set role. Please try again.');
                loading.style.display = 'none';
                continueBtn.disabled = false;
            });
        }
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
        
        // Event listeners
        document.getElementById('continueBtn').addEventListener('click', continueToRole);
    </script>
</body>
</html>
