<?php
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/../bootstrap/auth.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

ascom_require_role('teacher', '../user_login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Teacher - Content</title>

<!-- Use shared styles from super_admin-mis -->
<link rel="stylesheet" href="../super_admin-mis/styles/global.css">
<link rel="stylesheet" href="../super_admin-mis/styles/modals.css">
<link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
<link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
<link rel="stylesheet" href="../super_admin-mis/styles/school-calendar.css">
<link rel="stylesheet" href="../super_admin-mis/styles/settings.css">

</head>
<body>

<?php 
// Check if user has access to other roles (beyond teacher)
$hasOtherRoles = false;
if (isset($_SESSION['user_id']) && isset($pdo)) {
  $userId = $_SESSION['user_id'];
  
  // Debug: Log user ID
  
      try {
      // Check for roles using the same logic as login system
      $hasOtherRoles = false;
      
      // 1. Check if user is actually a dean (from departments.dean_user_id)
      $deanStmt = $pdo->prepare("
        SELECT COUNT(*) as dean_count
        FROM departments
        WHERE dean_user_id = ?
      ");
      $deanStmt->execute([$userId]);
      $deanResult = $deanStmt->fetch(PDO::FETCH_ASSOC);
      
      // 2. Check for librarian/QA roles (from user_roles with is_active = 1)
      $otherStmt = $pdo->prepare("
        SELECT COUNT(*) as other_count
        FROM user_roles
        WHERE user_id = ? AND role_name IN ('librarian', 'quality_assurance') AND is_active = 1
      ");
      $otherStmt->execute([$userId]);
      $otherResult = $otherStmt->fetch(PDO::FETCH_ASSOC);
      
      $hasOtherRoles = ($deanResult['dean_count'] > 0) || ($otherResult['other_count'] > 0);
    
    // Get the specific roles and set in session
    $availableRoles = [];
    
    // Check for dean role
    if ($deanResult['dean_count'] > 0) {
      $availableRoles[] = 'dean';
    }
    
    // Check for other roles (librarian, quality_assurance)
    $roleStmt = $pdo->prepare("
      SELECT ur.role_name
      FROM user_roles ur
      WHERE ur.user_id = ? AND ur.role_name IN ('librarian', 'quality_assurance') AND ur.is_active = 1
    ");
    $roleStmt->execute([$userId]);
    $otherRoles = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($otherRoles as $role) {
      $availableRoles[] = $role['role_name'];
    }
    
    // Set available roles in session
    $_SESSION['available_roles'] = $availableRoles;
    

  } catch (Exception $e) {
    // Debug output for database errors
    $hasOtherRoles = false;
  }
} else {
}

include './modals/switch_role_modal.php';
include './modals/request_book_modal.php'; 
?>

<div class="top-navbar">
  <div class="top-navbar-content">
    <div class="top-nav-left">
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
    <!-- Request Book button for Teachers -->
    <a href="#" class="nav-button new-account-button" id="requestBookBtn" onclick="openRequestBookModal(); return false;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>Request Book</span>
    </a>

    <?php $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard' || $currentPage == 'book-requests') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
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
    <?php if ($hasOtherRoles): ?>
    <a href="#" class="nav-button switch-role-button" onclick="openSwitchRoleModal(); return false;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/switch.png" class="nav-icon" />
      </span>
      <span>Switch Role</span>
    </a>
    <?php endif; ?>

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
      case 'book-requests':
        include './content/book-requests.php';
        break;
      case 'grade-management':
        $_GET['page'] = 'grade-management';
        include './content_coming_soon.php';
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

<script>
// Request Book Button Function - now opens the modal
// Function is defined in request_book_modal.php

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
