<?php
// program-management.php for Department Dean
// This file is an HTML fragment, included by content.php.
// It will fetch program data from the database and display it.

global $conn; // $conn is provided globally by content.php

// Include the modal
include './modal_add_program.php';

// Initialize programs array
$programs = [];
$programHeads = [];

// Try to fetch programs from database
try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    $deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;
    
    if ($deanDepartmentCode) {
        // Fetch programs for the dean's department
        $query = "
            SELECT 
                p.id,
                p.program_code,
                p.program_name,
                p.major,
                d.color_code,
                d.id as department_id,
                p.description,
                COUNT(DISTINCT c.id) as course_count,
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
                p.id, p.program_code, p.program_name, p.major, d.color_code, d.id, p.description
            ORDER BY 
                p.created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deanDepartmentCode]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch program heads for these programs
        $programIds = array_column($programs, 'id');
        $programHeads = [];
        
        if (!empty($programIds)) {
            $placeholders = implode(',', array_fill(0, count($programIds), '?'));
            $headQuery = "
                SELECT ph.program_id, ph.teacher_id, u.first_name, u.last_name, u.title
                FROM program_heads ph
                JOIN users u ON ph.teacher_id = u.id
                WHERE ph.program_id IN ($placeholders) AND ph.is_active = TRUE
            ";
            $headStmt = $pdo->prepare($headQuery);
            $headStmt->execute($programIds);
            while ($head = $headStmt->fetch(PDO::FETCH_ASSOC)) {
                $programHeads[$head['program_id']] = $head;
            }
        }
        
    } else {
    }
} catch (Exception $e) {
    $programs = [];
    $programHeads = [];
}

// Store department ID for JavaScript
$deanDepartmentId = $_SESSION['selected_role']['department_id'] ?? null;

// Show all programs without pagination

$totalPrograms = count($programs);
$totalCourses = 0;
$totalFaculty = 0;
$recentActivities = [];

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

/* Program Head Assignment Styles */
.program-head-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}

.assign-program-head-btn {
    background: #0C4B34;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s ease;
}

.assign-program-head-btn:hover {
    background: #0a3420;
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
                 $programId = $program['id'];
                 $head = $programHeads[$programId] ?? null;
                 $headName = $head ? trim(($head['title'] ?? '') . ' ' . $head['first_name'] . ' ' . $head['last_name']) : null;
                 
                 echo "<div class='department-card' data-program-id='" . htmlspecialchars($programId) . "'>";
                 echo "<div class='dept-code' style='background-color: " . htmlspecialchars($program['color_code']) . "'>" . htmlspecialchars($program['program_code']) . "</div>";
                 echo "<h3>" . htmlspecialchars($program['program_name']) . "</h3>";
                 if (!empty($program['major'])) {
                     echo "<p style='margin: 4px 0; font-size: 12px; color: #666;'>Major in: <strong>" . htmlspecialchars($program['major']) . "</strong></p>";
                 }
                 echo "<p><strong>Description:</strong> " . htmlspecialchars($program['description']) . "</p>";
                 echo "<p><strong>Courses:</strong> " . htmlspecialchars($program['course_count']) . "</p>";
                 echo "<p><strong>Faculty:</strong> " . htmlspecialchars($program['faculty_count']) . "</p>";
                 
                 // Program Head Section
                 echo "<div class='program-head-section'>";
                 echo "<p style='margin: 0 0 8px 0; font-size: 12px; color: #666;'><strong>Program Head:</strong> ";
                 if ($headName) {
                     echo "<span style='color: #0C4B34; font-weight: 600;'>" . htmlspecialchars($headName) . "</span>";
                     echo " <button onclick='removeProgramHead(" . $programId . ")' style='background: none; border: none; color: #dc3545; cursor: pointer; font-size: 11px; margin-left: 5px; text-decoration: underline;'>Remove</button>";
                 } else {
                     echo "<span style='color: #999; font-style: italic;'>Not assigned</span>";
                 }
                 echo "</p>";
                 echo "<button class='assign-program-head-btn' onclick='openAssignProgramHeadModal(" . $programId . ", " . json_encode(htmlspecialchars($program['program_name'])) . ")'>" . ($headName ? 'Change Program Head' : 'Assign Program Head') . "</button>";
                 echo "</div>";
                 
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
    const programs = <?php echo json_encode($programs); ?>;
    const recentActivities = <?php echo json_encode($recentActivities); ?>;
    const deanDepartmentId = <?php echo json_encode($deanDepartmentId); ?>;
    
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
    
    // Program Head Assignment Functions
    function openAssignProgramHeadModal(programId, programName) {
        // Load teachers from dean's department and show modal
        const modalHtml = `
            <div id="assignProgramHeadModal" class="modal-overlay" style="display: flex; position: fixed; z-index: 10003; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                <div class="modal-box" style="background: white; border-radius: 12px; padding: 24px; width: 90%; max-width: 450px; max-height: 80vh; overflow-y: auto;">
                    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                        <h2 style="margin: 0; font-size: 18px; color: #0C4B34;">Assign Program Head</h2>
                        <span class="close-button" onclick="closeAssignProgramHeadModal()" style="font-size: 24px; cursor: pointer; color: #666;">&times;</span>
                    </div>
                    <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Select a teacher from your department to assign as program head for <strong>${programName}</strong></p>
                    <div id="programHeadTeacherList" style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
                        <div style="padding: 20px; text-align: center; color: #999;">Loading teachers...</div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('assignProgramHeadModal');
        if (existingModal) existingModal.remove();
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Load teachers
        loadTeachersForProgramHead(programId);
    }
    
    function closeAssignProgramHeadModal() {
        const modal = document.getElementById('assignProgramHeadModal');
        if (modal) modal.remove();
    }
    
    function loadTeachersForProgramHead(programId) {
        if (!deanDepartmentId) {
            document.getElementById('programHeadTeacherList').innerHTML = 
                '<div style="padding: 20px; text-align: center; color: #dc3545;">Department not found</div>';
            return;
        }
        
        fetch(`api/get_department_teachers.php?department_id=${deanDepartmentId}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('programHeadTeacherList');
                
                if (!data.success || !data.teachers || data.teachers.length === 0) {
                    container.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No teachers found in your department</div>';
                    return;
                }
                
                container.innerHTML = data.teachers.map(teacher => {
                    const fullName = (teacher.title ? teacher.title + ' ' : '') + teacher.first_name + ' ' + teacher.last_name;
                    const isHead = teacher.is_program_head ? '<span style="color: #0C4B34; font-size: 11px; display: block; margin-top: 4px;">Currently Program Head</span>' : '';
                    
                    return `
                        <div class="teacher-option" onclick="assignProgramHead(${programId}, ${teacher.id}, '${fullName.replace(/'/g, "\\'")}')" 
                             style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s;">
                            <div style="font-weight: 600; color: #333; font-size: 14px;">${fullName}</div>
                            <div style="color: #666; font-size: 12px;">${teacher.employee_no || 'No employee number'}</div>
                            ${isHead}
                        </div>
                    `;
                }).join('');
                
                container.innerHTML += `
                    <div style="padding: 12px 16px; border-top: 1px solid #eee; background: #f9f9f9;">
                        <button onclick="closeAssignProgramHeadModal()" style="width: 100%; padding: 10px; background: #eee; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Cancel</button>
                    </div>
                `;
            })
            .catch(err => {
                document.getElementById('programHeadTeacherList').innerHTML = 
                    '<div style="padding: 20px; text-align: center; color: #dc3545;">Error loading teachers</div>';
            });
    }
    
    function assignProgramHead(programId, teacherId, teacherName) {
        if (!confirm(`Are you sure you want to assign ${teacherName} as the program head?`)) {
            return;
        }
        
        fetch('api/assign_program_head.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ program_id: programId, teacher_id: teacherId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Program head assigned successfully!');
                closeAssignProgramHeadModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to assign program head');
            }
        })
        .catch(err => {
            alert('Error assigning program head');
        });
    }
    
    function removeProgramHead(programId) {
        if (!confirm('Are you sure you want to remove this program head?')) {
            return;
        }
        
        fetch('api/remove_program_head.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ program_id: programId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Program head removed successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to remove program head');
            }
        })
        .catch(err => {
            alert('Error removing program head');
        });
    }
</script>
