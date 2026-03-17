<?php
// content_fixed.php - Clean version with JavaScript errors fixed

// Session validation
$isAuthenticated = false;

// Primary check: dean_logged_in session variable
if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    $isAuthenticated = true;
    file_put_contents('../login_debug.txt', 'department-dean/content.php - dean_logged_in session found' . PHP_EOL, FILE_APPEND);
}
// Secondary check: selected_role session variable
elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role'] === 'department-dean') {
    $isAuthenticated = true;
    $_SESSION['dean_logged_in'] = true; // Set the flag for future requests
    file_put_contents('../login_debug.txt', 'department-dean/content.php - recovered from selected_role' . PHP_EOL, FILE_APPEND);
}
// Tertiary check: user_id and username exist (basic session validation)
elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // For now, just check if basic session data exists
    $isAuthenticated = true;
    $_SESSION['dean_logged_in'] = true; // Assume dean if we have basic session
    file_put_contents('../login_debug.txt', 'department-dean/content.php - recovered from basic session data' . PHP_EOL, FILE_APPEND);
}

if (!$isAuthenticated) {
    file_put_contents('../login_debug.txt', 'department-dean/content.php - REDIRECTING TO LOGIN - no valid session found' . PHP_EOL, FILE_APPEND);
    header("Location: ../user_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Department Dean - Content</title>

<!-- Use shared styles from super_admin-mis -->
<link rel="stylesheet" href="../super_admin-mis/styles/global.css">
<link rel="stylesheet" href="../super_admin-mis/styles/modals.css">
<link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
<link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
<link rel="stylesheet" href="../super_admin-mis/styles/school-calendar.css">
<link rel="stylesheet" href="../super_admin-mis/styles/settings.css">
<link rel="stylesheet" href="./styles/program-management.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="./styles/course-modal.css?v=<?php echo time(); ?>&r=<?php echo rand(1000, 9999); ?>">

</head>
<body>

<?php include './modals/add_faculty_modal.php'; ?>
<?php include './modals/add_course_modal.php'; ?>
<?php include './modals/add_program_modal.php'; ?>
<?php include './modals/add_book_reference_modal.php'; ?>
<?php include './modals/switch_role_modal.php'; ?>

<div class="top-navbar">
  <div class="top-navbar-content">
    <div class="hamburger" onclick="toggleSidebar()" role="button" tabindex="0" aria-label="Toggle sidebar">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div class="navbar-title">Department Dean</div>
    <div class="navbar-actions">
      <div class="notification-icon" onclick="toggleNotificationDropdown()">
        <img src="../src/assets/icons/notification-icon.png" alt="Notifications">
        <span class="notification-badge">3</span>
      </div>
      <div class="chats-icon" onclick="toggleChatsDropdown()">
        <img src="../src/assets/icons/chat-icon.png" alt="Chats">
        <span class="chat-badge">2</span>
      </div>
      <div class="user-profile">
        <img src="../src/assets/icons/user-icon.png" alt="User">
        <span><?php echo $_SESSION['username'] ?? 'User'; ?></span>
      </div>
    </div>
  </div>
</div>

<nav class="side-navbar" id="sidebar" aria-label="Sidebar navigation">
  <div class="nav-buttons">
    <a href="#" class="nav-button new-account-button" id="newCourseBtn" onclick="checkProgramsAndOpenCourseModal(); return false;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>New Course</span>
    </a>

    <?php $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard' || $currentPage == 'all-courses' || $currentPage == 'course-details' || $currentPage == 'program-courses' || $currentPage == 'reference-requests') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
    </a>

    <a href="content.php?page=faculty-management" class="nav-button hoverable <?php if ($currentPage == 'academic-management' || $currentPage == 'faculty-management' || $currentPage == 'faculty-details') echo 'active'; ?>" style="height: 76px;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/faculty-icon.png" alt="Faculty Icon" class="nav-icon" />
      </span>
      <span>Faculty Management</span>
    </a>

    <a href="content.php?page=program-management" class="nav-button hoverable <?php if ($currentPage == 'program-management') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/program-icon.png" alt="Program Icon" class="nav-icon" />
      </span>
      <span>Program Management</span>
    </a>

    <a href="content.php?page=reference-requests" class="nav-button hoverable <?php if ($currentPage == 'reference-requests') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/reference-icon.png" alt="Reference Icon" class="nav-icon" />
      </span>
      <span>Reference Requests</span>
    </a>

    <a href="content.php?page=switch-role" class="nav-button hoverable <?php if ($currentPage == 'switch-role') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/switch-icon.png" alt="Switch Icon" class="nav-icon" />
      </span>
      <span>Switch Role</span>
    </a>
  </div>
</nav>

<div class="content-wrapper">
  <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    switch ($page) {
      case 'faculty-management':
        include './faculty-management-content/faculty-management.php';
        break;
      case 'program-management':
        include './program-management-content/program-management.php';
        break;
      case 'reference-requests':
        include './reference-requests-content/reference-requests.php';
        break;
      case 'switch-role':
        include './switch-role-content/switch-role.php';
        break;
      case 'dashboard':
      default:
        include './dashboard-content/dashboard.php';
        break;
    }
  ?>
</div>

<!-- Use shared scripts -->
<script src="../session_manager.js"></script>
<script src="./fix_js_errors.js"></script>
<script src="../scripts/global.js"></script>
<script src="../super_admin-mis/scripts/dashboard.js"></script>
<script src="../super_admin-mis/scripts/user-account-management.js"></script>
<script src="../super_admin-mis/scripts/school-calendar.js"></script>
<script src="./scripts/program-management.js"></script>

<script>
// All functions are now defined in fix_js_errors.js to avoid conflicts

// Simple diagnostic check
console.log('🔍 DIAGNOSTIC: Checking critical functions...');
const criticalFunctions = ['openAddCourseModal', 'toggleSidebar', 'checkProgramsAndOpenCourseModal'];
criticalFunctions.forEach(funcName => {
    if (typeof window[funcName] === 'function') {
        console.log('✅ Function available:', funcName);
    } else {
        console.error('❌ Function missing:', funcName);
    }
});

console.log('🔍 DIAGNOSTIC: All checks complete');
</script>
</body>
</html>
