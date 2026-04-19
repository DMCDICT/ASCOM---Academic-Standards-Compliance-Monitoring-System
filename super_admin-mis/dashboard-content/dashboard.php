<?php
// dashboard.php
// This file is an HTML fragment, included by content.php.
// It will fetch dashboard data from the database and display it.

global $conn; // $conn is provided globally by content.php

// --- ACTIVATE REAL DATABASE CODE ---
// Initialize default values
$totalDepartments = 0;
$totalUsersCount = 0;
$activeUsersCount = 0;
$departments = [];
$recentActivities = [];

// Check if the connection was successful before proceeding
if (isset($conn) && !$conn->connect_error) {
    // Set a timeout for database operations to prevent hanging
    $conn->query("SET SESSION wait_timeout = 10");
    $conn->query("SET SESSION interactive_timeout = 10"); 
    // Fetch total number of departments for the overview box
    $countQuery = "SELECT COUNT(*) AS total_depts FROM departments";
    $countResult = $conn->query($countQuery);

    if ($countResult && $countResult->num_rows > 0) {
        $row = $countResult->fetch_assoc();
        $totalDepartments = $row['total_depts'];
        $countResult->free();
    } elseif ($countResult === false) {
        $totalDepartments = 0; // Ensure we have a fallback value
    }

    // --- NEW: Fetch Total Users Count ---
    $totalUsersQuery = "SELECT COUNT(id) AS total_users FROM users";
    $totalUsersResult = $conn->query($totalUsersQuery);
    if ($totalUsersResult && $totalUsersResult->num_rows > 0) {
        $row = $totalUsersResult->fetch_assoc();
        $totalUsersCount = $row['total_users'];
        $totalUsersResult->free();
    } elseif ($totalUsersResult === false) {
        $totalUsersCount = 0; // Ensure we have a fallback value
    }

    // --- NEW: Fetch Active Users Count ---
    // Only use is_active if it exists in the table
    $activeUsersCount = 0;
    $activeUsersQuery = "SHOW COLUMNS FROM users LIKE 'is_active'";
    $activeUsersResult = $conn->query($activeUsersQuery);
    if ($activeUsersResult && $activeUsersResult->num_rows > 0) {
        // is_active exists, so count only active users
        $activeUsersQuery2 = "SELECT COUNT(id) AS active_users FROM users WHERE is_active = 1";
        $activeUsersResult2 = $conn->query($activeUsersQuery2);
        if ($activeUsersResult2 && $activeUsersResult2->num_rows > 0) {
            $row = $activeUsersResult2->fetch_assoc();
            $activeUsersCount = $row['active_users'];
            $activeUsersResult2->free();
        }
    } else {
        // is_active does not exist, count all users
        $activeUsersCount = $totalUsersCount;
    }

    // Fetch all departments along with their staff count and assigned Dean for the cards section
    $departments = [];
    $query = "
        SELECT 
            d.id, 
            d.department_code, 
            d.department_name, 
            d.color_code,
            COUNT(u.id) AS staff_count,
            COALESCE(
                CASE 
                    WHEN p.title IS NOT NULL AND p.title != '' 
                    THEN CONCAT(p.title, ' ', p.first_name, ' ', p.last_name)
                    ELSE CONCAT(p.first_name, ' ', p.last_name)
                END, 
                'N/A'
            ) AS dean_name 
        FROM 
            departments d
        LEFT JOIN 
            users u ON d.id = u.department_id 
        LEFT JOIN
            users p ON d.dean_user_id = p.id  
        GROUP BY 
            d.id, d.department_code, d.department_name, d.color_code, p.first_name, p.last_name, p.title
        ORDER BY 
            d.department_name ASC
        LIMIT 20;
    ";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row; 
        }
        $result->free();
    } elseif ($result === false) {
    }

    // Fetch Recent Activities from activity_logs table
    $recentActivities = [];
    $activitiesQuery = "SELECT username, description, activity_timestamp FROM activity_logs ORDER BY activity_timestamp DESC LIMIT 5";
    $activitiesResult = $conn->query($activitiesQuery);

    if ($activitiesResult) {
        while ($row = $activitiesResult->fetch_assoc()) {
            $recentActivities[] = $row;
        }
        $activitiesResult->free();
    } elseif ($activitiesResult === false) {
    }

} else {
    // Default values are already set above, so no need to set them again
}

// Greeting logic
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 17) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}
$todayDate = date('l, F j, Y');
?>

<!-- Greeting Banner -->
<div class="dashboard-greeting">
    <div class="greeting-text">
        <h2><?php echo $greeting; ?>, Admin</h2>
        <p>Here's what's happening across your institution today.</p>
    </div>
    <div class="greeting-date">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        <?php echo $todayDate; ?>
    </div>
</div>

<!-- Overview Section -->
<div class="section-label">
    <div class="label-bar"></div>
    <h3>Overview</h3>
</div>

<div class="dashboard-container">
    <!-- Departments Card -->
    <div class="box" id="overview-departments">
        <div class="box-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </div>
        <div class="box-content">
            <span class="box-label">Departments</span>
            <div class="amount"><?php echo $totalDepartments; ?></div>
            <span class="box-sub">Active departments</span>
        </div>
    </div>

    <!-- Total Users Card -->
    <div class="box" id="overview-users">
        <div class="box-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="box-content">
            <span class="box-label">Total Users</span>
            <div class="amount"><?php echo $totalUsersCount; ?></div>
            <span class="box-sub">Registered accounts</span>
        </div>
    </div>

    <!-- System Status Card -->
    <div class="box" id="overview-status">
        <div class="box-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
        </div>
        <div class="box-content">
            <span class="box-label">System Status</span>
            <div class="amount">
                <span class="status-indicator">
                    <span class="status-dot"></span>
                    Development
                </span>
            </div>
            <span class="box-sub">System health</span>
        </div>
    </div>
</div>

<!-- Account Access Management Section -->
<div class="account-access-section">
    <div class="account-access-header">
        <div class="label-bar"></div>
        <div>
            <h3>Account Access Management</h3>
            <p>Assign special access roles to users</p>
        </div>
    </div>
    
    <div class="account-access-cards">
        <!-- Librarian Access Card -->
        <div class="access-card">
            <div class="access-card-header">
                <div class="access-card-icon librarian-icon">
                    <img src="../src/assets/icons/library-icon.png" alt="Library">
                </div>
                <div>
                    <h4>Librarian Access</h4>
                    <p>Manage library resources and book records</p>
                </div>
            </div>
            <p class="access-card-description">
                Assign librarian privileges to users for managing library books, catalogs, and resource tracking.
            </p>
            <button onclick="openLibrarianAccessModal()" class="access-card-button librarian-button">
                Assign Librarian Access
            </button>
        </div>
        
        <!-- Quality Assurance Access Card -->
        <div class="access-card">
            <div class="access-card-header">
                <div class="access-card-icon qa-icon">
                    <img src="../src/assets/icons/maintenance-icon.png" alt="QA">
                </div>
                <div>
                    <h4>Quality Assurance Access</h4>
                    <p>Monitor academic quality and compliance</p>
                </div>
            </div>
            <p class="access-card-description">
                Assign QA privileges to users for monitoring academic standards, compliance, and quality assurance processes.
            </p>
            <button onclick="openQualityAssuranceAccessModal()" class="access-card-button qa-button">
                Assign QA Access
            </button>
        </div>
    </div>
</div>

<!-- Department Management Section -->
<div class="departments-section">
    <div class="departments-header">
        <div class="departments-header-left">
            <div class="label-bar"></div>
            <div>
                <h3>Department Management</h3>
                <p>Manage college departments and their color codes</p>
            </div>
        </div>
        <button class="add-dept-btn" id="addDepartmentButton" onclick="if(typeof openAddDepartmentModal === 'function') { openAddDepartmentModal(); } else { console.error('openAddDepartmentModal not available'); }">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Department
        </button>
    </div>

    <div class="departments-container" id="departmentContainer">
        <?php
        if (!empty($departments)) {
            $index = 0;
            foreach ($departments as $dept) {
                $hidden = ($index >= 8) ? "hidden" : "";
                echo "<div class='department-card compact-dept-card " . htmlspecialchars($hidden) . "' onclick='openDepartmentDetailsModal($index);' tabindex='0' aria-label='View " . htmlspecialchars($dept['department_name']) . " Details'>";
                echo "  <div class='dept-card-header'>";
                echo "    <div class='dept-card-title-wrap'>";
                echo "      <h3 class='dept-name' title='" . htmlspecialchars($dept['department_name']) . "'>" . htmlspecialchars($dept['department_name']) . "</h3>";
                echo "      <p class='dean-indicator'>Dean: <strong>" . htmlspecialchars($dept['dean_name']) . "</strong></p>";
                echo "    </div>";
                echo "    <span class='dept-code' style='background-color: " . htmlspecialchars($dept['color_code']) . "; box-shadow: 0 4px 12px " . htmlspecialchars($dept['color_code']) . "40;'>" . htmlspecialchars($dept['department_code']) . "</span>";
                echo "  </div>";
                echo "  <div class='dept-card-stats'>";
                echo "    <div class='stat-pill'>Programs: <strong>0</strong></div>";
                echo "    <div class='stat-pill'>Staff: <strong>" . htmlspecialchars($dept['staff_count']) . "</strong></div>";
                echo "  </div>";
                echo "</div>";
                $index++;
            }
        } else {
            echo "<p id='noDepartmentsMessage' style='width: 100%; text-align: center; color: #777;'>No departments found. Click 'Add Department' to create one.</p>";
        }
        ?>
    </div>

    <button class="view-all-btn" id="viewAllDepartmentsButton" 
            style="display: <?php echo (count($departments) > 6) ? 'block' : 'none'; ?>">View All →</button>
</div>

<!-- Recent Activities Section -->
<div class="dashboard-bottom">
    <div class="recent-activities">
        <div class="recent-activities-header">
            <div class="label-bar"></div>
            <h3>Recent Activities</h3>
        </div>
        <table>
            <thead><tr><th>User</th><th>Activity</th><th>Date & Time</th></tr></thead> 
            <tbody id="recentActivitiesTableBody">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['activity_timestamp']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">
                        <div class="empty-state">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            No recent activities found.
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>



<?php include 'modal_add_department.php'; ?>

<!-- Department Details Modal -->
<div id="departmentDetailsModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 80vw; max-width: 1000px; height: 85vh; overflow: hidden;">
        <div class="modal-header">
            <h2 id="deptDetailsTitle">Department Details</h2>
            <span class="close-button" onclick="closeDepartmentDetailsModal()">&times;</span>
        </div>
        
        <div id="deptDetailsContent" style="display: flex; gap: 25px; height: calc(100% - 80px); overflow: hidden;">
            <!-- Left Column: Department Info & Dean Assignment -->
            <div style="flex: 0 0 300px;">
                <!-- Department Info Section -->
                <div class="dept-info-section" style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                    <h3 style="margin-top: 0; color: #333; font-size: 15px; font-weight: 600; margin-bottom: 12px;">Department Information</h3>
                    <div class="dept-info-grid" style="display: grid; gap: 8px;">
                        <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: white; border-radius: 5px; border: 1px solid #e0e0e0;">
                            <label style="font-weight: 600; color: #555; font-size: 12px;">Code:</label>
                            <span id="deptCode" style="font-weight: 500; color: #333; font-size: 12px;"></span>
                        </div>
                        <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: white; border-radius: 5px; border: 1px solid #e0e0e0;">
                            <label style="font-weight: 600; color: #555; font-size: 12px;">Name:</label>
                            <span id="deptName" style="font-weight: 500; color: #333; font-size: 12px; text-align: right; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                        </div>
                        <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: white; border-radius: 5px; border: 1px solid #e0e0e0;">
                            <label style="font-weight: 600; color: #555; font-size: 12px;">Color:</label>
                            <span id="deptColor" style="font-weight: 500; color: #333; font-size: 12px;"></span>
                        </div>
                        <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: white; border-radius: 5px; border: 1px solid #e0e0e0;">
                            <label style="font-weight: 600; color: #555; font-size: 12px;">Staff:</label>
                            <span id="deptStaffCount" style="font-weight: 500; color: #333; font-size: 12px;"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Dean Assignment Section -->
                <div class="dean-assignment-section" style="background: #f8f9fa; padding: 15px; border-radius: 10px;">
                    <h3 style="margin-top: 0; color: #333; font-size: 15px; font-weight: 600; margin-bottom: 12px;">Dean Assignment</h3>
                    <div class="current-dean" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; color: #555; display: block; margin-bottom: 6px; font-size: 12px;">Current Dean:</label>
                        <span id="currentDean" style="font-weight: 500; color: #333; padding: 6px 10px; background: white; border-radius: 5px; border: 1px solid #e0e0e0; display: inline-block; min-width: 160px; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">No dean assigned</span>
                    </div>
                    
                    <div class="dean-assignment-form">
                        <button type="button" id="assignNewDeanBtn" onclick="toggleDeanAssignmentMode()" style="background-color: #739AFF; color: white; border: none; padding: 8px 14px; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 12px; width: 100%;">Assign a New Dean</button>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Department Teachers -->
            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
                <div class="dept-teachers-section" style="background: #f8f9fa; padding: 18px; border-radius: 10px; height: 100%; display: flex; flex-direction: column;">
                    <h3 style="margin-top: 0; color: #333; font-size: 16px; font-weight: 600; margin-bottom: 15px;">Department Teachers</h3>
                    
                    <!-- Search Bar -->
                    <div style="margin-bottom: 15px;">
                        <input type="text" id="teacherSearchInput" placeholder="Search teachers..." 
                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background-color: white; box-sizing: border-box; transition: all 0.3s ease;">
                    </div>
                    
                    <div id="deptTeachersList" style="flex: 1; overflow-y: auto; padding-right: 5px;">
                        <!-- Teachers will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Dean Confirmation Modal -->
<div id="assignDeanConfirmationModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 550px; text-align: center; padding: 0; margin: auto;">
        <div style="padding: 30px;">
            <img src="../src/assets/animated_icons/info-animated-icon.gif" alt="Info" style="width: 60px; height: 60px; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #333; font-size: 24px; font-weight: 600; margin-bottom: 20px;">Assign Dean</h2>
            <p id="assignDeanConfirmationMessage" style="font-size: 16px; margin-bottom: 25px; color: #333; line-height: 1.5;">
                Are you sure you want to assign <strong id="selectedTeacherName"></strong> as the dean of <strong id="selectedDepartmentName"></strong>?
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="closeAssignDeanConfirmationModal()" style="background-color: #C9C9C9; color: black; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Cancel</button>
                <button type="button" onclick="confirmAssignDean()" style="background-color: #739AFF; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">Confirm Assignment</button>
            </div>
        </div>
    </div>
</div>

<!-- Dean Assignment Success Modal -->
<div id="deanSuccessModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s;">
        <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 60px; height: 60px; margin-bottom: 15px;">
        <h2 style="color: green; margin-bottom: 10px;">Dean Assigned!</h2>
        <p id="deanSuccessMessage" style="font-size: 16px; margin-bottom: 20px;"></p>
        <button type="button" class="create-btn" onclick="closeDeanSuccessModal()">OK</button>
    </div>
</div>

<!-- BACK TO TOP FUNCTIONALITY -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.className = 'back-to-top';
    backToTopButton.setAttribute('aria-label', 'Back to top');
    
    const icon = document.createElement('img');
    icon.src = '../src/assets/icons/go-back-icon.png';
    icon.alt = 'Back to Top';
    icon.className = 'arrow';
    
    const text = document.createElement('span');
    text.className = 'text';
    text.textContent = 'Back to Top';
    
    backToTopButton.appendChild(icon);
    backToTopButton.appendChild(text);
    document.body.appendChild(backToTopButton);

    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });

    // Scroll to top when clicked
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>