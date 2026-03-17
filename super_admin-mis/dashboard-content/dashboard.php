<?php
// dashboard.php
// This file is an HTML fragment, included by content.php.
// It will fetch dashboard data from the database and display it.

global $conn; // $conn is provided globally by content.php

// --- REMOVE DUMMY DATA ---
// Dummy values for overview boxes
// $totalDepartments = 1;
// $totalUsersCount = 3;
// $activeUsersCount = 1;
// Dummy departments array
// $departments = [ ... ];
// Dummy recent activities
// $recentActivities = [ ... ];

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
        error_log("Error executing total departments count query in dashboard.php: " . $conn->error);
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
        error_log("Error executing total users count query in dashboard.php: " . $conn->error);
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
        error_log("Error executing main data query in dashboard.php: " . $conn->error);
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
        error_log("Error fetching recent activities in dashboard.php: " . $conn->error);
    }

} else {
    error_log("Database connection failed or not available in dashboard.php: " . ($conn->connect_error ?? "Unknown error"));
    // Default values are already set above, so no need to set them again
}
?>

<h2 class="main-page-title" style="padding-left: 0px;">Overview</h2> <div class="dashboard-container">
    <div class="box">
        <h2>Departments</h2>
        <div class="amount"><?php echo $totalDepartments; ?></div>
    </div>
    <div class="box"><h2>Total Users</h2><div class="amount"><?php echo $totalUsersCount; ?></div></div> 
    <div class="box"><h2>System Status</h2><div class="amount" style="color: green;">Development</div></div>
</div>

<!-- Account Access Management Section -->
<div class="account-access-section">
    <div class="account-access-header">
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

<div class="departments-section">
    <div class="departments-header">
        <div>
            <h3>Department Management</h3>
            <p>Manage college departments and their color codes</p>
        </div>
        <button class="add-dept-btn" id="addDepartmentButton" onclick="if(typeof openAddDepartmentModal === 'function') { openAddDepartmentModal(); } else { console.error('openAddDepartmentModal not available'); }">Add Department</button>
    </div>

    <div class="departments-container" id="departmentContainer">
        <?php
        if (!empty($departments)) {
            $index = 0;
            foreach ($departments as $dept) {
                $hidden = ($index >= 6) ? "hidden" : "";
                echo "<div class='department-card " . htmlspecialchars($hidden) . "'>";
                echo "<span class='dept-code' style='background-color: " . htmlspecialchars($dept['color_code']) . "'>" . htmlspecialchars($dept['department_code']) . "</span>";
                echo "<h3>" . htmlspecialchars($dept['department_name']) . "</h3>";
                echo "<p class='dean-indicator'><strong>Dean:</strong> " . htmlspecialchars($dept['dean_name']) . "</p>";
                echo "<div class='dept-indicator-row'><span><strong>Programs:</strong> 0</span></div>";
                echo "<div class='dept-indicator-row staff-row-only'><span><strong>Staff:</strong> " . htmlspecialchars($dept['staff_count']) . "</span></div>";
                // Place button at the bottom

                echo "<button class='view-details-btn' onclick='event.stopPropagation(); openDepartmentDetailsModal($index);'>View Details</button>";
                echo "</div>";
                $index++;
            }
        } else {
            echo "<p id='noDepartmentsMessage' style='width: 100%; text-align: center; color: #777;'>No departments found. Click 'Add Department' to create one.</p>";
        }
        ?>
    </div>

    <button class="view-all-btn" id="viewAllDepartmentsButton" 
            style="display: <?php echo (count($departments) > 6) ? 'block' : 'none'; ?>">View All</button>
</div>

<div class="dashboard-bottom">
    <div class="recent-activities">
        <h3>Recent Activities</h3>
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
                    <tr><td colspan="3" style="text-align: center;">No recent activities found.</td></tr>
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

<!-- BACK TO TOP FUNCTIONALITY - Direct Implementation -->
<script>
console.log('🎯 DASHBOARD.PHP: Starting back-to-top functionality');

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 DASHBOARD.PHP: DOM ready, creating back-to-top button');
    
    try {
        // Create back to top button with icon and text
        const backToTopButton = document.createElement('button');
        backToTopButton.className = 'back-to-top';
        backToTopButton.setAttribute('aria-label', 'Back to top');
        
        // Create icon element
        const icon = document.createElement('img');
        icon.src = '../src/assets/icons/go-back-icon.png';
        icon.alt = 'Back to Top';
        icon.className = 'arrow';
        
        // Create text element
        const text = document.createElement('span');
        text.className = 'text';
        text.textContent = 'Back to Top';
        
        // Append icon and text to button
        backToTopButton.appendChild(icon);
        backToTopButton.appendChild(text);
        
        // Append button to body
        document.body.appendChild(backToTopButton);
        console.log('🎯 DASHBOARD.PHP: Back-to-top button created and appended to body');

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
            console.log('🎯 DASHBOARD.PHP: Back-to-top button clicked');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Test button removed - back-to-top functionality is working
        
        // Don't force show button - let it show naturally when scrolling
        console.log('🎯 DASHBOARD.PHP: Button ready - will show when scrolling down');
        
        console.log('🎯 DASHBOARD.PHP: Back-to-top functionality completed successfully');
    } catch (error) {
        console.error('❌ DASHBOARD.PHP: Error in back-to-top functionality:', error);
        // Fallback: Create a simple back-to-top button
        const fallbackButton = document.createElement('button');
        fallbackButton.textContent = 'BACK TO TOP (DASHBOARD.PHP FALLBACK)';
        fallbackButton.style.position = 'fixed';
        fallbackButton.style.bottom = '30px';
        fallbackButton.style.right = '30px';
        fallbackButton.style.background = 'orange';
        fallbackButton.style.color = 'white';
        fallbackButton.style.padding = '10px';
        fallbackButton.style.zIndex = '9999';
        fallbackButton.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
        document.body.appendChild(fallbackButton);
        console.log('🎯 DASHBOARD.PHP: Fallback button created');
    }
});

// TOOLTIP SYSTEM REMOVED - No tooltips needed
console.log('🚨 DASHBOARD.PHP: Tooltip system removed');

function createEmergencyTooltips() {
    console.log('🚨 DASHBOARD.PHP: Tooltip system removed');
    return; // No tooltips
    
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`🚨 DASHBOARD.PHP: Found ${navButtons.length} nav buttons`);
    
    if (navButtons.length === 0) {
        console.log('🚨 DASHBOARD.PHP: No nav buttons found');
        return;
    }
    
    let emergencyTooltip = null;
    
    navButtons.forEach(function(button, index) {
        const tooltipText = button.querySelector('span:not(.nav-icon-wrapper)')?.textContent?.trim() || 'Unknown';
        console.log(`🚨 DASHBOARD.PHP: Setting up tooltip for button ${index + 1}: "${tooltipText}"`);
        
        button.addEventListener('mouseenter', function() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
            
            console.log(`🚨 DASHBOARD.PHP: Hover on button ${index + 1}, sidebar collapsed: ${isCollapsed}`);
            
            if (isCollapsed) {
                if (emergencyTooltip) {
                    emergencyTooltip.remove();
                }
                
                const buttonRect = button.getBoundingClientRect();
                console.log(`🚨 DASHBOARD.PHP: Button ${index + 1} position:`, buttonRect);
                
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
                console.log(`🚨 DASHBOARD.PHP: Tooltip created for button ${index + 1}`);
            }
        });
        
        button.addEventListener('mouseleave', function() {
            console.log(`🚨 DASHBOARD.PHP: Leave button ${index + 1}`);
            if (emergencyTooltip) {
                emergencyTooltip.remove();
                emergencyTooltip = null;
                console.log(`🚨 DASHBOARD.PHP: Tooltip removed for button ${index + 1}`);
            }
        });
    });
    
    console.log('🚨 DASHBOARD.PHP: Emergency tooltips created successfully');
}

// Create emergency tooltips multiple times
setTimeout(createEmergencyTooltips, 1000);
setTimeout(createEmergencyTooltips, 3000);
setTimeout(createEmergencyTooltips, 5000);

// Make it available globally
window.createEmergencyTooltips = createEmergencyTooltips;

console.log('🚨 DASHBOARD.PHP: Emergency tooltip system ready');
</script>