<?php
// program-management.php for Department Dean
// This file is an HTML fragment, included by content.php.
// It will fetch program data from the database and display it.

global $conn; // $conn is provided globally by content.php

// Include the modal
include './modal_add_program.php';

// Initialize programs array
$programs = [];

// Try to fetch programs from database
try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if ($deanDepartmentCode) {
        // Fetch programs for the dean's department
        $query = "
            SELECT 
                p.id,
                p.program_code,
                p.program_name,
                p.major,
                d.color_code,
                p.description,
                COUNT(c.id) as course_count,
                COUNT(DISTINCT u.id) as faculty_count
            FROM 
                programs p
            LEFT JOIN 
                courses c ON p.id = c.program_id
            LEFT JOIN 
                users u ON u.department_id = p.department_id
            JOIN
                departments d ON p.department_id = d.id
            WHERE 
                d.department_code = ?
            GROUP BY 
                p.id, p.program_code, p.program_name, p.major, d.color_code, p.description
            ORDER BY 
                p.created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deanDepartmentCode]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
    }
} catch (Exception $e) {
    $programs = [];
}

// Show all programs without pagination

$totalCourses = 0;
$totalFaculty = 0;
$recentActivities = [];

/*
// --- ALL DATABASE CODE BELOW IS COMMENTED OUT FOR DUMMY DATA MODE ---
// Check if the connection was successful before proceeding
if (isset($conn) && !$conn->connect_error) { 
    // Get the current dean's department ID from session
    $deanDepartmentId = $_SESSION['dean_department_id'] ?? null;
    
    if ($deanDepartmentId) {
        // Fetch total number of programs for the overview box
        $countQuery = "SELECT COUNT(*) AS total_programs FROM programs WHERE department_id = ?";
        $stmt = $conn->prepare($countQuery);
        $stmt->bind_param("i", $deanDepartmentId);
        $stmt->execute();
        $countResult = $stmt->get_result();

        if ($countResult && $countResult->num_rows > 0) {
            $row = $countResult->fetch_assoc();
            $totalPrograms = $row['total_programs'];
            $countResult->free();
        }

        // Fetch total courses count
        $coursesQuery = "SELECT COUNT(*) AS total_courses FROM courses c 
                        JOIN programs p ON c.program_id = p.id 
                        WHERE p.department_id = ?";
        $stmt = $conn->prepare($coursesQuery);
        $stmt->bind_param("i", $deanDepartmentId);
        $stmt->execute();
        $coursesResult = $stmt->get_result();
        if ($coursesResult && $coursesResult->num_rows > 0) {
            $row = $coursesResult->fetch_assoc();
            $totalCourses = $row['total_courses'];
            $coursesResult->free();
        }

        // Fetch total faculty count
        $facultyQuery = "SELECT COUNT(*) AS total_faculty FROM users 
                        WHERE department_id = ? AND role = 'faculty' AND is_active = TRUE";
        $stmt = $conn->prepare($facultyQuery);
        $stmt->bind_param("i", $deanDepartmentId);
        $stmt->execute();
        $facultyResult = $stmt->get_result();
        if ($facultyResult && $facultyResult->num_rows > 0) {
            $row = $facultyResult->fetch_assoc();
            $totalFaculty = $row['total_faculty'];
            $facultyResult->free();
        }

        // Fetch all programs for the dean's department
        $query = "
            SELECT 
                p.id, 
                p.program_code, 
                p.program_name, 
                p.color_code,
                p.description,
                COUNT(c.id) AS course_count,
                COUNT(DISTINCT u.id) AS faculty_count
            FROM 
                programs p
            LEFT JOIN 
                courses c ON p.id = c.program_id
            LEFT JOIN 
                users u ON p.id = u.program_id AND u.role = 'faculty' AND u.is_active = TRUE
            WHERE 
                p.department_id = ?
            GROUP BY 
                p.id, p.program_code, p.program_name, p.color_code, p.description
            ORDER BY 
                p.program_name ASC;
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $deanDepartmentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $programs[] = $row; 
            }
            $result->free();
        }

        // Fetch Recent Activities for the department
        $activitiesQuery = "SELECT username, description, activity_timestamp 
                           FROM activity_logs 
                           WHERE department_id = ? 
                           ORDER BY activity_timestamp DESC LIMIT 10";
        $stmt = $conn->prepare($activitiesQuery);
        $stmt->bind_param("i", $deanDepartmentId);
        $stmt->execute();
        $activitiesResult = $stmt->get_result();

        if ($activitiesResult) {
            while ($row = $activitiesResult->fetch_assoc()) {
                $recentActivities[] = $row;
            }
            $activitiesResult->free();
        }
    }
} else {
}
*/

?>

<style>
.program-management-container {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.main-page-title {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.content-wrapper {
    margin-top: 102px !important;
    padding-top: 0 !important;
}

/* Floating Back to Top Button Styles */
.back-to-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    overflow: hidden;
    white-space: nowrap;
}

.back-to-top-btn:hover {
    background: #1565c0;
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
    width: 140px;
    border-radius: 25px;
}

.back-to-top-btn:active {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
}

.back-to-top-btn.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top-btn .arrow {
    width: 20px;
    height: 20px;
    transition: all 0.3s ease;
    position: absolute;
    left: 50%;
    transform: translateX(-50%) rotate(90deg);
    filter: brightness(0) invert(1);
}

.back-to-top-btn .text {
    position: absolute;
    left: 50%;
    transform: translateX(-50%) translateX(-10px);
    font-size: 14px;
    font-weight: 500;
    font-family: 'TT Interphases', sans-serif;
    opacity: 0;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.back-to-top-btn:hover .arrow {
    left: 20px;
    transform: translateX(0) rotate(90deg);
    margin-right: 8px;
}

.back-to-top-btn:hover .text {
    opacity: 1;
    left: 43px;
    transform: translateX(0);
}

/* Responsive floating back to top button */
@media (max-width: 768px) {
    .back-to-top-btn {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
        font-size: 18px;
    }
    
    .back-to-top-btn:hover {
        width: 120px;
    }
    
    .back-to-top-btn .arrow {
        width: 18px;
        height: 18px;
    }
    
    .back-to-top-btn .text {
        font-size: 13px;
    }
}
</style>

<h2 class="main-page-title" style="padding-left: 0px; margin-top: 20px;">Program Management</h2>

<div class="dashboard-container">
    <div class="box">
        <h2>Programs</h2>
        <div class="amount"><?php echo $totalPrograms; ?></div>
    </div>
    <div class="box">
        <h2>Total Courses</h2>
        <div class="amount"><?php echo $totalCourses; ?></div>
    </div>
    <div class="box">
        <h2>Faculty Members</h2>
        <div class="amount"><?php echo $totalFaculty; ?></div>
    </div>
    <div class="box">
        <h2>System Status</h2>
        <div class="amount" style="color: green;">Active</div>
    </div>
</div>

<div class="departments-section">
    <div class="departments-header">
        <div>
            <h3>Program Management</h3>
            <p>Manage academic programs and their configurations</p>
        </div>
        <button class="add-dept-btn" id="addProgramButton" style="display: <?php echo (count($programs) > 0) ? 'block' : 'none'; ?>">Add Program</button>
    </div>

    <div class="departments-container" id="programContainer">
        <?php
        if (!empty($programs)) {
                         foreach ($programs as $program) {
                 echo "<div class='department-card'>";
                 echo "<div class='dept-code' style='background-color: " . htmlspecialchars($program['color_code']) . "'>" . htmlspecialchars($program['program_code']) . "</div>";
                 echo "<h3>" . htmlspecialchars($program['program_name']) . "</h3>";
                 if (!empty($program['major'])) {
                     echo "<p style='margin: 4px 0; font-size: 12px; color: #666;'>Major in: <strong>" . htmlspecialchars($program['major']) . "</strong></p>";
                 }
                 echo "<p><strong>Description:</strong> " . htmlspecialchars($program['description']) . "</p>";
                 echo "<p><strong>Courses:</strong> " . htmlspecialchars($program['course_count']) . "</p>";
                 echo "<p><strong>Faculty:</strong> " . htmlspecialchars($program['faculty_count']) . "</p>";
                 echo "<button class='view-details-btn' onclick=\"window.location.href='content.php?page=program-courses&program=" . urlencode($program['program_code']) . "'\">View Details</button>";
                 echo "</div>";
             }
        } else {
            // No programs found - show empty state card
            echo "<div class='department-card empty-program-card'>";
            echo "<div style='display: flex; justify-content: space-between; align-items: center;'><div class='dept-code' style='background-color: #1976d2; color: white; font-weight: bold;'>NEW</div><span style='font-size: 1.5rem;'>📁</span></div>";
            echo "<h3>No Programs Yet</h3>";
            echo "<p style='font-weight: bold; color: #333;'>Start building your programs</p>";
            echo "<button class='view-details-btn' onclick='openAddProgramModal()'>Create First Program</button>";
            echo "</div>";
        }
        ?>
    </div>

    <!-- Floating Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back to Top" class="arrow">
        <span class="text">Back to Top</span>
    </button>
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
    <div class="active-users">
        <h3>Active Users</h3>
        <div class="user">No users currently active.</div> 
    </div>
</div>

<script>
    // This variable will be accessed by scripts/program-management.js
    const programs = <?php echo json_encode($programs); ?>;
    const recentActivities = <?php echo json_encode($recentActivities); ?>;
    
    // Back to top functionality
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        const backToTopBtn = document.getElementById('backToTopBtn');
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
</script>

<style>
/* Empty Program Card Styling */
.empty-program-card {
    background: #e3f2fd;
    border: 2px dashed #90caf9;
    cursor: default;
    transition: all 0.3s ease;
}

.empty-program-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(33, 150, 243, 0.2);
    border-color: #64b5f6;
}

.empty-program-card h3 {
    color: #1976d2;
}

.empty-program-card p {
    color: #64b5f6;
    margin-bottom: 15px;
}

/* View Details Button Styling */
.view-details-btn {
    background: #1976d2;
    color: white;
    border: none;
    padding: 6px 18px;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    margin-top: auto;
    align-self: flex-end;
    width: auto;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
}

.view-details-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
}
</style> 