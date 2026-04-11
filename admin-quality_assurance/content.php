<?php
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/../bootstrap/auth.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

ascom_require_role('quality_assurance', '../user_login.php');

include './modals/switch_role_modal.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Quality Assurance - Content</title>

<!-- Use shared styles from super_admin-mis -->
<link rel="stylesheet" href="../super_admin-mis/styles/global.css">
<link rel="stylesheet" href="../super_admin-mis/styles/modals.css">
<link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
<link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
<link rel="stylesheet" href="../super_admin-mis/styles/school-calendar.css">
<link rel="stylesheet" href="../super_admin-mis/styles/settings.css">
<link rel="stylesheet" href="../super_admin-mis/styles/notifications.css">

</head>
<body>

<?php /* include modals if needed */ ?>

<div class="top-navbar">
  <div class="top-navbar-content">
    <div class="hamburger" onclick="toggleSidebar()" role="button" tabindex="0" aria-label="Toggle sidebar">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <img src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Logo" class="logo-img" />
    <div class="search-bar">
      <img src="../src/assets/icons/search-icon.png" alt="Search Icon" />
      <input type="text" placeholder="Search Here..." />
    </div>
    <div class="chats-icon">
      <img src="../src/assets/icons/chats-icon.png" alt="Chats" />
      <div class="chat-count">0</div>
      <div class="chat-dropdown" id="chatsDropdown">
        <h3>Chats</h3>
        <div class="chats-empty">No new messages</div>
      </div>
    </div>
    <div class="notification-icon">
      <img src="../src/assets/icons/notifications-icon.png" alt="Notifications" />
      <div class="notification-count">0</div>
      <div class="notification-dropdown" id="notificationDropdown">
        <h3>Notifications</h3>
        <div class="notification-empty">No new notifications</div>
      </div>
    </div>
  </div>
</div>

<nav class="side-navbar" id="sidebar" aria-label="Sidebar navigation">
  <div class="nav-buttons">
    <!-- New Account button hidden for Admin QA role -->
    <!-- <a href="#" class="nav-button new-account-button" id="newAccountBtn">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>New Account</span>
    </a> -->

    <?php $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard' || $currentPage == 'curriculum-review') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
    </a>

    <a href="content.php?page=academic-management" class="nav-button hoverable <?php if ($currentPage == 'academic-management') echo 'active'; ?>" style="height: 76px;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/academic-icon.png" alt="Users Icon" class="nav-icon" />
      </span>
      <span style="line-height: 1.2;">
        Academic<br />Management
      </span>
    </a>



    <a href="content.php?page=school-calendar" class="nav-button hoverable <?php if ($currentPage == 'school-calendar') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/calendar-icon.png" alt="Calendar Icon" class="nav-icon" />
      </span>
      <span>School Calendar</span>
    </a>

    <a href="content.php?page=settings" class="nav-button hoverable <?php if ($currentPage == 'settings') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/settings-icon.png" alt="Settings Icon" class="nav-icon" />
      </span>
      <span>Settings</span>
    </a>
  </div>

  <div class="bottom-nav-buttons">
    <?php 
    // Check if user has access to teacher role
    $hasTeacherAccess = false;
    if (isset($_SESSION['user_id']) && isset($pdo)) {
      try {
        // Check if user is a teacher (has teacher role OR has a department_id)
        $stmt = $pdo->prepare("
          SELECT COUNT(*) as teacher_count
          FROM users u
          WHERE u.id = ? AND (u.role = 'teacher' OR u.department_id IS NOT NULL)
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $hasTeacherAccess = $result['teacher_count'] > 0;
        
            } catch (Exception $e) {
      $hasTeacherAccess = false;
    }
    }
    ?>
    
    <a href="#" class="nav-button switch-role-button" onclick="openSwitchRoleModal(); return false;" style="background-color: #1976d2;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/switch.png" class="nav-icon" />
      </span>
      <span>Switch Role</span>
    </a>

    <a href="./logout.php" class="nav-button logout-button">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/logout-icon.png" class="nav-icon" />
      </span>
      <span>Log Out</span>
    </a>
  </div>
</nav>

<div class="content-wrapper">
  <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    switch ($page) {
      case 'academic-management':
        include './content/academic-management.php';
        break;
      case 'curriculum-review':
        include './content/curriculum-review.php';
        break;
      case 'course-details':
        include './content/course-details.php';
        break;
      case 'school-calendar':
        $_GET['page'] = 'school-calendar';
        include './content_coming_soon.php';
        break;
      case 'settings':
        $_GET['page'] = 'settings';
        include './content_coming_soon.php';
        break;
      case 'dashboard':
      default:
        include './content/dashboard.php';
        break;
    }
  ?>
</div>

<!-- Use shared scripts -->
<script src="../session_manager.js"></script>
<script src="../scripts/global.js"></script>
<script src="../super_admin-mis/scripts/dashboard.js"></script>
<script src="../super_admin-mis/scripts/user-account-management.js"></script>
<script src="../super_admin-mis/scripts/school-calendar.js"></script>
<script src="./js/notifications.js"></script>

<script>
// Back to Top functionality is handled by dashboard.js

// SIDEBAR TOGGLE FUNCTION
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '298px';
        }
        localStorage.setItem('sidebarCollapsed', 'false');
    } else {
        sidebar.classList.add('collapsed');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '115px';
        }
        localStorage.setItem('sidebarCollapsed', 'true');
    }
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '115px';
        }
    } else {
        sidebar.classList.remove('collapsed');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '298px';
        }
    }
});

// TOOLTIP SYSTEM - Fixed and working

function createEmergencyTooltips() {
    
    const navButtons = document.querySelectorAll('.nav-button');
    
    if (navButtons.length === 0) {
        return;
    }
    
    let emergencyTooltip = null;
    
    navButtons.forEach(function(button, index) {
        const spanElement = button.querySelector('span:not(.nav-icon-wrapper)');
        let tooltipText = 'Unknown';
        if (spanElement) {
            // Get innerHTML and replace <br> tags with spaces
            tooltipText = spanElement.innerHTML.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
        }
        
        button.addEventListener('mouseenter', function() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
            
            
            if (isCollapsed) {
                if (emergencyTooltip) {
                    emergencyTooltip.remove();
                }
                
                const buttonRect = button.getBoundingClientRect();
                
                emergencyTooltip = document.createElement('div');
                emergencyTooltip.innerHTML = `
                    <div style="
                        position: absolute;
                        left: -8px;
                        top: 50%;
                        transform: translateY(-50%);
                        width: 0;
                        height: 0;
                        border-top: 8px solid transparent;
                        border-bottom: 8px solid transparent;
                        border-right: 8px solid #f8f9fa;
                        filter: drop-shadow(-2px 0 4px rgba(0,0,0,0.2));
                    "></div>
                    ${tooltipText}
                `;
                
                emergencyTooltip.style.cssText = `
                    position: fixed !important;
                    left: ${buttonRect.right + 20}px !important;
                    top: ${buttonRect.top + buttonRect.height / 2}px !important;
                    transform: translateY(-50%) !important;
                    background: #f8f9fa !important;
                    color: #000000 !important;
                    padding: 12px 20px !important;
                    border-radius: 12px !important;
                    font-size: 14px !important;
                    font-weight: 600 !important;
                    white-space: nowrap !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    display: block !important;
                    pointer-events: none !important;
                    z-index: 999999 !important;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
                    border: 1px solid #e0e0e0 !important;
                    font-family: 'TT Interphases', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                    letter-spacing: 0.5px !important;
                    text-shadow: none !important;
                `;
                
                document.body.appendChild(emergencyTooltip);
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (emergencyTooltip) {
                emergencyTooltip.remove();
                emergencyTooltip = null;
            }
        });
    });
    
}

// Create emergency tooltips multiple times
setTimeout(createEmergencyTooltips, 1000);
setTimeout(createEmergencyTooltips, 3000);
setTimeout(createEmergencyTooltips, 5000);

// Make it available globally
window.createEmergencyTooltips = createEmergencyTooltips;


// CHAT AND NOTIFICATION FUNCTIONALITY

// Initialize chats and notifications
const chatsIcon = document.querySelector('.chats-icon');
if (chatsIcon) {
    chatsIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropdown = document.getElementById('chatsDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
        }
    };
    chatsIcon.style.cursor = 'pointer';
    chatsIcon.style.pointerEvents = 'auto';
} else {
}

const notificationIcon = document.querySelector('.notification-icon');
if (notificationIcon) {
    notificationIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
        }
    };
    notificationIcon.style.cursor = 'pointer';
    notificationIcon.style.pointerEvents = 'auto';
} else {
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.chats-icon')) {
        const chatsDropdown = document.getElementById('chatsDropdown');
        if (chatsDropdown) {
            chatsDropdown.style.display = 'none';
        }
    }
    
    if (!e.target.closest('.notification-icon')) {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.style.display = 'none';
        }
    }
});

</script>
</body>
</html> 
