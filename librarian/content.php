<?php
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/../bootstrap/auth.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

ascom_require_role('librarian', '../user_login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Librarian - Content</title>

<!-- Use shared styles from super_admin-mis -->
<link rel="stylesheet" href="../super_admin-mis/styles/global.css">
<link rel="stylesheet" href="../super_admin-mis/styles/modals.css">
<link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
<link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
<link rel="stylesheet" href="../super_admin-mis/styles/school-calendar.css">
<link rel="stylesheet" href="../super_admin-mis/styles/settings.css">
<link rel="stylesheet" href="./styles/modal-add-book.css">
<link rel="stylesheet" href="../super_admin-mis/styles/notifications.css">

</head>
<body>

<?php 
// Check if user has access to other roles (beyond librarian)
$hasOtherRoles = false;
if (isset($_SESSION['user_id']) && isset($pdo)) {
  $userId = $_SESSION['user_id'];
  
  // Debug: Log user ID
  error_log('Librarian interface - User ID: ' . $userId);
  
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
      
      // 2. Check for teacher role (from users.role_id = 4, NOT user_roles table)
      $teacherStmt = $pdo->prepare("
        SELECT COUNT(*) as teacher_count
        FROM users
        WHERE id = ? AND role_id = 4 AND department_id IS NOT NULL AND is_active = 1
      ");
      $teacherStmt->execute([$userId]);
      $teacherResult = $teacherStmt->fetch(PDO::FETCH_ASSOC);
      
      // 3. Check for quality_assurance role (from user_roles table)
      $qaStmt = $pdo->prepare("
        SELECT COUNT(*) as qa_count
        FROM user_roles
        WHERE user_id = ? AND role_name = 'quality_assurance' AND is_active = 1
      ");
      $qaStmt->execute([$userId]);
      $qaResult = $qaStmt->fetch(PDO::FETCH_ASSOC);
      
      $hasOtherRoles = ($deanResult['dean_count'] > 0) || ($teacherResult['teacher_count'] > 0) || ($qaResult['qa_count'] > 0);
    
    // Get the specific roles and set in session
    $availableRoles = [];
    
    // Check for dean role
    if ($deanResult['dean_count'] > 0) {
      $availableRoles[] = 'dean';
    }
    
    // Check for teacher role (from users.role_id = 4)
    if ($teacherResult['teacher_count'] > 0) {
      $availableRoles[] = 'teacher';
    }
    
    // Check for quality_assurance role (from user_roles table)
    if ($qaResult['qa_count'] > 0) {
      $availableRoles[] = 'quality_assurance';
    }
    
    // Set available roles in session
    $_SESSION['available_roles'] = $availableRoles;
    
    } catch (Exception $e) {
      error_log('Librarian role check error: ' . $e->getMessage());
    }
}

// Include the Add Book modal
include './modal_add_book.php';

// Include switch role modal if user has other roles
if ($hasOtherRoles) {
    include './modals/switch_role_modal.php';
}
?>

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
    <a href="#" class="nav-button new-account-button" id="newAccountBtn" onclick="lockPageScroll(); const m = document.getElementById('addBookModal'); if(m) { m.style.display='flex'; m.style.setProperty('overflow', 'hidden', 'important'); } setTimeout(function(){ if(typeof validateAddBookButton === 'function') validateAddBookButton(); }, 100);">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>Add Book</span>
    </a>

    <?php $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard' || $currentPage == 'material-processing' || $currentPage == 'classification-details') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
    </a>

    <a href="content.php?page=library-management" class="nav-button hoverable <?php if (
      $currentPage == 'library-management' || $currentPage == 'course-details') echo 'active'; ?>" style="height: 76px;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/library-icon.png" alt="Library Icon" class="nav-icon" />
      </span>
      <span style="line-height: 1.2;">
        Library<br />Management
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
    <?php if ($hasOtherRoles): ?>
    <a href="#" class="nav-button switch-role-button" onclick="openSwitchRoleModal()" style="background-color: #1976d2;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/switch.png" alt="Switch Role Icon" class="nav-icon" />
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
      case 'library-management':
        include './library-management-content/library-management.php';
        break;
      case 'course-details':
        include './library-management-content/course-details.php';
        break;
      case 'classification-details':
        include './library-management-content/classification-details.php';
        break;
      case 'material-processing':
        include './material-processing-content/material-processing.php';
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
<!-- <script src="./scripts/modal-add-book.js"></script> -->
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
console.log('🚨 LIBRARIAN: Tooltip system enabled');

function createEmergencyTooltips() {
    console.log('🚨 LIBRARIAN: Creating tooltips...');
    
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`🚨 LIBRARIAN: Found ${navButtons.length} nav buttons`);
    
    if (navButtons.length === 0) {
        console.log('🚨 LIBRARIAN: No nav buttons found');
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
        console.log(`🚨 LIBRARIAN: Setting up tooltip for button ${index + 1}: "${tooltipText}"`);
        
        button.addEventListener('mouseenter', function() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
            
            console.log(`🚨 LIBRARIAN: Hover on button ${index + 1}, sidebar collapsed: ${isCollapsed}`);
            
            if (isCollapsed) {
                if (emergencyTooltip) {
                    emergencyTooltip.remove();
                }
                
                const buttonRect = button.getBoundingClientRect();
                console.log(`🚨 LIBRARIAN: Button ${index + 1} position:`, buttonRect);
                
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
                console.log(`🚨 LIBRARIAN: Tooltip created for button ${index + 1}`);
            }
        });
        
        button.addEventListener('mouseleave', function() {
            console.log(`🚨 LIBRARIAN: Leave button ${index + 1}`);
            if (emergencyTooltip) {
                emergencyTooltip.remove();
                emergencyTooltip = null;
                console.log(`🚨 LIBRARIAN: Tooltip removed for button ${index + 1}`);
            }
        });
    });
    
    console.log('🚨 LIBRARIAN: Emergency tooltips created successfully');
}

// Create emergency tooltips once
setTimeout(createEmergencyTooltips, 1000);

// Make it available globally
window.createEmergencyTooltips = createEmergencyTooltips;

console.log('🚨 LIBRARIAN: Emergency tooltip system ready');

// CHAT AND NOTIFICATION FUNCTIONALITY
console.log('🚨 LIBRARIAN: Initializing chat and notification functionality');

// Initialize chats and notifications
const chatsIcon = document.querySelector('.chats-icon');
if (chatsIcon) {
    console.log('🚨 LIBRARIAN: Chats icon found, adding click handler');
    chatsIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🚨 LIBRARIAN: Chats icon clicked');
        const dropdown = document.getElementById('chatsDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
            console.log('🚨 LIBRARIAN: Chats dropdown toggled:', dropdown.style.display);
        }
    };
    chatsIcon.style.cursor = 'pointer';
    chatsIcon.style.pointerEvents = 'auto';
    console.log('🚨 LIBRARIAN: Chats initialized');
} else {
    console.log('🚨 LIBRARIAN: Chats icon not found');
}

const notificationIcon = document.querySelector('.notification-icon');
if (notificationIcon) {
    console.log('🚨 LIBRARIAN: Notification icon found, adding click handler');
    notificationIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🚨 LIBRARIAN: Notification icon clicked');
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
            console.log('🚨 LIBRARIAN: Notification dropdown toggled:', dropdown.style.display);
        }
    };
    notificationIcon.style.cursor = 'pointer';
    notificationIcon.style.pointerEvents = 'auto';
    console.log('🚨 LIBRARIAN: Notifications initialized');
} else {
    console.log('🚨 LIBRARIAN: Notification icon not found');
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

console.log('🚨 LIBRARIAN: Chat and notification functionality ready');
</script>
</body>
</html> 
