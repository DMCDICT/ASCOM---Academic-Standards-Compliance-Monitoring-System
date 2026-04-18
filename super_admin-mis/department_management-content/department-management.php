<?php
// department-management.php
// College Department Management Page

global $conn;

$departments = [];
$stats = [
    'total' => 0,
    'active' => 0,
    'inactive' => 0,
    'total_programs' => 0,
    'total_courses' => 0,
    'total_teachers' => 0
];

if (isset($conn) && !$conn->connect_error) {
    // Get department stats
    $deptQuery = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN is_active = 0 OR is_active IS NULL THEN 1 ELSE 0 END) as inactive
        FROM departments";
    $deptResult = $conn->query($deptQuery);
    if ($deptResult && $row = $deptResult->fetch_assoc()) {
        $stats['total'] = $row['total'] ?? 0;
        $stats['active'] = $row['active'] ?? 0;
        $stats['inactive'] = $row['inactive'] ?? 0;
    }

    // Get program count
    $progQuery = "SELECT COUNT(*) as cnt FROM programs";
    $progResult = $conn->query($progQuery);
    if ($progResult && $row = $progResult->fetch_assoc()) {
        $stats['total_programs'] = $row['cnt'] ?? 0;
    }

    // Get course count
    $courseQuery = "SELECT COUNT(*) as cnt FROM courses";
    $courseResult = $conn->query($courseQuery);
    if ($courseResult && $row = $courseResult->fetch_assoc()) {
        $stats['total_courses'] = $row['cnt'] ?? 0;
    }

    // Get teacher count (role_id = 4 for teacher)
    $teacherQuery = "SELECT COUNT(*) as cnt FROM users WHERE role_id = 4";
    $teacherResult = $conn->query($teacherQuery);
    if ($teacherResult && $row = $teacherResult->fetch_assoc()) {
        $stats['total_teachers'] = $row['cnt'] ?? 0;
    }

    // Fetch all departments with their data
    $fetchDepts = "SELECT 
        d.*,
        (SELECT COUNT(*) FROM programs p WHERE p.department_id = d.id) as program_count,
        (SELECT COUNT(*) FROM courses c WHERE c.department_id = d.id) as course_count,
        (SELECT COUNT(*) FROM users u WHERE u.department_id = d.id AND u.role_id = 4) as teacher_count,
        u.employee_no as dean_employee_no,
        u.first_name as dean_first_name,
        u.last_name as dean_last_name,
        CONCAT_WS(' ', u.first_name, u.last_name) as dean_name
    FROM departments d 
    LEFT JOIN users u ON d.dean_user_id = u.id
    ORDER BY d.department_name ASC";
    
    $deptResult = $conn->query($fetchDepts);
    if ($deptResult) {
        while ($row = $deptResult->fetch_assoc()) {
            $departments[] = $row;
        }
    }
}
?>

<div class="department-management-page">
    <div class="header-row">
        <h2 class="main-page-title" style="padding-left: 0px;">College Department Management</h2>
        <div class="header-buttons">
            <button class="create-btn" onclick="openAddDepartmentModal()" style="min-width: 160px;">+ Add Department</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="label-icon">
                <span>Total Departments</span>
            </div>
            <div class="stat-amount"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-box">
            <div class="label-icon">
                <span>Active Departments</span>
            </div>
            <div class="stat-amount" style="color: #28a745;"><?php echo $stats['active']; ?></div>
        </div>
        <div class="stat-box">
            <div class="label-icon">
                <span>Programs</span>
            </div>
            <div class="stat-amount"><?php echo $stats['total_programs']; ?></div>
        </div>
        <div class="stat-box">
            <div class="label-icon">
                <span>Courses</span>
            </div>
            <div class="stat-amount"><?php echo $stats['total_courses']; ?></div>
        </div>
        <div class="stat-box">
            <div class="label-icon">
                <span>Teachers</span>
            </div>
            <div class="stat-amount"><?php echo $stats['total_teachers']; ?></div>
        </div>
    </div>

    <!-- Department List -->
    <div class="department-list">
        <?php if (empty($departments)): ?>
        <div class="empty-state">
            <p>No departments found. Click "Add Department" to create one.</p>
        </div>
        <?php else: ?>
        <?php foreach ($departments as $dept): ?>
        <div class="department-card" data-dept-id="<?php echo $dept['id']; ?>">
            <div class="dept-main">
                <div class="dept-row dept-row-top">
                    <div class="dept-color" style="background-color: <?php echo htmlspecialchars($dept['color_code'] ?? '#1976d2'); ?>;"></div>
                    <div class="dept-name"><?php echo htmlspecialchars($dept['department_name']); ?></div>
                    <div class="dept-code-badge"><?php echo htmlspecialchars($dept['department_code']); ?></div>
                    <button class="view-details-btn" onclick="openDepartmentDetailsModal(<?php echo $dept['id']; ?>); event.stopPropagation();">
                        View Details
                    </button>
                </div>
                <div class="dept-row dept-row-middle">
                    <span class="stat-pill"><strong><?php echo $dept['program_count']; ?></strong> Programs</span>
                    <span class="stat-pill"><strong><?php echo $dept['course_count']; ?></strong> Courses</span>
                    <span class="stat-pill"><strong><?php echo $dept['teacher_count']; ?></strong> Teachers</span>
                    <?php if ($dept['dean_user_id']): ?>
                    <span class="stat-pill dean-pill">Dean: <?php echo htmlspecialchars($dept['dean_name']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="dept-row dept-row-bottom">
                    <div>
                        <button class="dean-btn" onclick="openAssignDeanModal(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_code']); ?>'); event.stopPropagation();">
                            <?php echo $dept['dean_user_id'] ? 'Change Dean' : 'Assign Dean'; ?>
                        </button>
                        <button class="edit-btn" onclick="openEditDepartmentModal(<?php echo $dept['id']; ?>); event.stopPropagation();">Edit</button>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="dept-toggle" 
                            <?php echo ($dept['is_active'] ?? 1) ? 'checked' : ''; ?>
                            onchange="toggleDepartmentStatus(<?php echo $dept['id']; ?>, this.checked)"
                            onclick="event.stopPropagation()">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Dean Modal (Inline) -->
<div id="assignDeanModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; height: auto; max-height: 90vh; overflow-y: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Assign Dean</h2>
      <span onclick="window.closeAssignDeanModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    <p id="assignDeanDeptName" style="font-size: 16px; color: #666; margin-bottom: 20px;"></p>
    <form id="assignDeanForm" style="display: flex; flex-direction: column; gap: 15px;">
      <input type="hidden" name="department_id" id="assign_dept_id">
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Select Teacher/Faculty <span style="color: #dc3545;">*</span></label>
        <select name="user_id" id="assign_user_id" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
          <option value="">-- Select Teacher --</option>
        </select>
      </div>
      <div id="currentDeanSection" style="display: none; background: #fff3cd; padding: 15px; border-radius: 8px;">
        <strong>Current Dean:</strong> <span id="currentDeanName"></span>
        <button type="button" onclick="window.removeDean()" style="margin-left: 10px; color: #dc3545; background: none; border: none; cursor: pointer; text-decoration: underline;">Remove</button>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="window.closeAssignDeanModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" class="create-btn" style="width: 125px; height: 50px;">ASSIGN</button>
      </div>
    </form>
  </div>
</div>

<!-- Success Modal -->
<div id="deptSuccessModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
  <div style="max-width: 400px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="margin-bottom: 20px;"><span style="font-size: 60px;">✓</span></div>
    <h2 style="color: #28a745; margin-bottom: 15px;">Success!</h2>
    <p id="deptSuccessMessage" style="font-size: 16px; margin-bottom: 25px;">Operation completed successfully!</p>
    <button onclick="document.getElementById('deptSuccessModal').style.display='none'; location.reload();" style="min-width: 120px; height: 45px; background-color: #28a745; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px;">OK</button>
  </div>
</div>

<!-- Error Modal -->
<div id="deptErrorModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
  <div style="max-width: 400px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="margin-bottom: 20px;"><span style="font-size: 60px; color: #dc3545;">✕</span></div>
    <h2 style="color: #dc3545; margin-bottom: 15px;">Error!</h2>
    <p id="deptErrorMessage" style="font-size: 16px; margin-bottom: 25px;">An error occurred.</p>
    <button onclick="document.getElementById('deptErrorModal').style.display='none';" style="min-width: 120px; height: 45px; background-color: #dc3545; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px;">OK</button>
  </div>
</div>

<!-- Edit Department Modal -->
<div id="editDepartmentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; height: auto; max-height: 90vh; overflow-y: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Edit Department</h2>
      <span onclick="window.closeEditDepartmentModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    <form id="editDepartmentForm" style="display: flex; flex-direction: column; gap: 15px;">
      <input type="hidden" name="department_id" id="edit_dept_id">
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Name <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_name" id="edit_department_name" required placeholder="e.g., College of Computing Studies" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
      </div>
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Code <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_code" id="edit_department_code" required placeholder="e.g., CCS" maxlength="10" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF; text-transform: uppercase;">
      </div>
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Color Code</label>
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="color" id="edit_color_picker" value="#1976d2" style="width: 50px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
          <input type="text" id="edit_color_hex" value="#1976d2" maxlength="7" style="flex: 1; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="window.closeEditDepartmentModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" class="create-btn" style="width: 125px; height: 50px;">UPDATE</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Department Modal -->
<div id="addDepartmentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; height: auto; max-height: 90vh; overflow-y: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Add Department</h2>
      <span onclick="window.closeAddDepartmentModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    <form id="addDepartmentForm" style="display: flex; flex-direction: column; gap: 15px;">
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Name <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_name" id="add_department_name" required placeholder="e.g., College of Computing Studies" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
      </div>
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department Code <span style="color: #dc3545;">*</span></label>
        <input type="text" name="department_code" id="add_department_code" required placeholder="e.g., CCS" maxlength="10" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF; text-transform: uppercase;">
      </div>
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Color Code</label>
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="color" id="add_color_picker" value="#1976d2" style="width: 50px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
          <input type="text" id="add_color_hex" value="#1976d2" maxlength="7" style="flex: 1; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="window.closeAddDepartmentModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" class="create-btn" style="width: 125px; height: 50px;">ADD</button>
      </div>
    </form>
  </div>
</div>

<!-- Department Details Modal -->
<div id="departmentDetailsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; height: auto; max-height: 85vh; overflow-y: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 id="detailsModalTitle" style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Department Details</h2>
      <span onclick="window.closeDepartmentDetailsModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
    </div>
    <div id="departmentDetailsContent" style="display: flex; flex-direction: column; gap: 20px;">
      <p style="text-align: center; color: #666;">Loading...</p>
    </div>
  </div>
</div>

<script>
function toggleDepartmentDetails(deptId) {
    const detailsSection = document.getElementById('dept-details-' + deptId);
    const card = detailsSection.closest('.department-card');
    card.classList.toggle('expanded');
}

window.toggleDepartmentDetails = toggleDepartmentDetails;

function toggleDepartmentStatus(deptId, isActive) {
    fetch('./api/update_department_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'department_id=' + deptId + '&is_active=' + (isActive ? 1 : 0)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update status');
            location.reload();
        }
    });
}

window.toggleDepartmentStatus = toggleDepartmentStatus;

function openDepartmentDetailsModal(deptId) {
    const modal = document.getElementById('departmentDetailsModal');
    const content = document.getElementById('departmentDetailsContent');
    const title = document.getElementById('detailsModalTitle');
    
    if (!modal || !content) {
        console.error('Department Details Modal not found');
        alert('Error: Modal not loaded. Please refresh the page.');
        return;
    }
    
    content.innerHTML = '<p style="text-align: center; color: #666;">Loading...</p>';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    fetch('./api/get_department_data.php?id=' + deptId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const dept = data.data;
            title.textContent = dept.department_name + ' - Details';
            
            let html = '';
            
            // Dean Section
            html += '<div style="background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">';
            html += '<h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #333; border-bottom: 2px solid #6f42c1; padding-bottom: 8px;">Dean</h4>';
            if (dept.dean_name) {
                html += '<div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span style="color: #666;">Name:</span><span style="font-weight: 500;">' + dept.dean_name + '</span></div>';
                html += '<div style="display: flex; justify-content: space-between;"><span style="color: #666;">Employee No:</span><span>' + (dept.dean_employee_no || 'N/A') + '</span></div>';
            } else {
                html += '<p style="color: #999; font-style: italic;">No dean assigned</p>';
            }
            html += '</div>';
            
            // Stats Summary
            html += '<div style="display: flex; gap: 10px; flex-wrap: wrap;">';
            html += '<div style="flex: 1; min-width: 100px; background: #fff; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08);"><div style="font-size: 24px; font-weight: 700; color: #007bff;">' + dept.program_count + '</div><div style="font-size: 12px; color: #666;">Programs</div></div>';
            html += '<div style="flex: 1; min-width: 100px; background: #fff; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08);"><div style="font-size: 24px; font-weight: 700; color: #28a745;">' + dept.course_count + '</div><div style="font-size: 12px; color: #666;">Courses</div></div>';
            html += '<div style="flex: 1; min-width: 100px; background: #fff; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08);"><div style="font-size: 24px; font-weight: 700; color: #dc3545;">' + dept.teacher_count + '</div><div style="font-size: 12px; color: #666;">Teachers</div></div>';
            html += '</div>';
            
            // Programs (if any)
            if (dept.programs && dept.programs.length > 0) {
                html += '<div style="background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">';
                html += '<h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 8px;">Programs (' + dept.programs.length + ')</h4>';
                html += '<ul style="list-style: none; padding: 0; margin: 0;">';
                dept.programs.forEach(function(prog) {
                    html += '<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;"><strong>' + prog.program_code + '</strong> ' + prog.program_name + (prog.major ? ' - ' + prog.major : '') + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            // Courses (if any)
            if (dept.courses && dept.courses.length > 0) {
                html += '<div style="background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">';
                html += '<h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 8px;">Courses (' + dept.courses.length + ')</h4>';
                html += '<ul style="list-style: none; padding: 0; margin: 0;">';
                dept.courses.slice(0, 10).forEach(function(course) {
                    html += '<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;"><strong>' + course.course_code + '</strong> ' + course.course_title + ' <span style="color: #999; font-size: 12px;">(' + (course.units || 0) + ' units)</span></li>';
                });
                if (dept.courses.length > 10) {
                    html += '<li style="padding: 8px 0; color: #666; font-size: 12px;">...and ' + (dept.courses.length - 10) + ' more courses</li>';
                }
                html += '</ul>';
                html += '</div>';
            }
            
            // Teachers (if any)
            if (dept.teachers && dept.teachers.length > 0) {
                html += '<div style="background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">';
                html += '<h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 8px;">Teachers (' + dept.teachers.length + ')</h4>';
                html += '<ul style="list-style: none; padding: 0; margin: 0;">';
                dept.teachers.slice(0, 10).forEach(function(teacher) {
                    html += '<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;">' + teacher.first_name + ' ' + teacher.last_name + ' <span style="color: #999; font-size: 12px;">(' + teacher.employee_no + ')</span></li>';
                });
                if (dept.teachers.length > 10) {
                    html += '<li style="padding: 8px 0; color: #666; font-size: 12px;">...and ' + (dept.teachers.length - 10) + ' more teachers</li>';
                }
                html += '</ul>';
                html += '</div>';
            }
            
            content.innerHTML = html;
        } else {
            content.innerHTML = '<p style="text-align: center; color: #dc3545;">Error loading department details.</p>';
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        content.innerHTML = '<p style="text-align: center; color: #dc3545;">Network error. Please try again.</p>';
    });
}

window.openDepartmentDetailsModal = openDepartmentDetailsModal;

function closeDepartmentDetailsModal() {
    const modal = document.getElementById('departmentDetailsModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

window.closeDepartmentDetailsModal = closeDepartmentDetailsModal;

function openAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Add Department Modal not found');
        alert('Error: Modal not loaded. Please refresh the page.');
    }
}

window.openAddDepartmentModal = openAddDepartmentModal;

function openEditDepartmentModal(deptId) {
    const modal = document.getElementById('editDepartmentModal');
    if (!modal) {
        console.error('Edit Department Modal not found');
        alert('Error: Modal not loaded. Please refresh the page.');
        return;
    }
    
    // Fetch department data
    fetch('./api/get_department_data.php?id=' + deptId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_dept_id').value = data.data.id;
            document.getElementById('edit_department_name').value = data.data.department_name;
            document.getElementById('edit_department_code').value = data.data.department_code;
            document.getElementById('edit_color_picker').value = data.data.color_code || '#1976d2';
            document.getElementById('edit_color_hex').value = data.data.color_code || '#1976d2';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            alert('Error: ' + (data.message || 'Failed to load department data'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error loading department data');
    });
}

window.openEditDepartmentModal = openEditDepartmentModal;
</script>

<script>
// Dean assignment modal functions (defined globally)
window.openAssignDeanModal = function(deptId, deptCode) {
    // Check if modal exists, if not show error
    const assignDeptId = document.getElementById('assign_dept_id');
    const assignDeanModal = document.getElementById('assignDeanModal');
    
    if (!assignDeptId || !assignDeanModal) {
        console.error('Assign Dean Modal not found in DOM');
        alert('Error: Modal not loaded. Please refresh the page.');
        return;
    }
    
    assignDeptId.value = deptId;
    document.getElementById('assignDeanDeptName').textContent = 'Department: ' + deptCode;
    
    // Show current dean info if exists
    const currentDeanSection = document.getElementById('currentDeanSection');
    const deptCard = document.querySelector('.department-card[data-dept-id="' + deptId + '"]');
    if (deptCard) {
        const deanName = deptCard.querySelector('.dean-name');
        if (deanName && deanName.textContent.trim()) {
            document.getElementById('currentDeanName').textContent = deanName.textContent;
            currentDeanSection.style.display = 'block';
        } else {
            currentDeanSection.style.display = 'none';
        }
    }
    
    // Fetch teachers for this department
    fetch('./api/get_department_teachers.php?department_id=' + deptId)
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('assign_user_id');
        select.innerHTML = '<option value="">-- Select Teacher --</option>';
        
        if (data.success && data.teachers) {
            data.teachers.forEach(function(teacher) {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = teacher.first_name + ' ' + teacher.last_name + ' (' + teacher.employee_no + ')';
                select.appendChild(option);
            });
        }
        
        if (select.options.length <= 1) {
            select.innerHTML = '<option value="">No teachers available</option>';
        }
    });
    
    document.getElementById('assignDeanModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

window.closeAssignDeanModal = function() {
    document.getElementById('assignDeanModal').style.display = 'none';
    document.body.style.overflow = '';
}

window.removeDean = function() {
    const deptId = document.getElementById('assign_dept_id').value;
    if (confirm('Are you sure you want to remove this dean?')) {
        fetch('./api/remove_dean.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'department_id=' + deptId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('deptSuccessMessage').textContent = 'Dean removed successfully!';
                document.getElementById('deptSuccessModal').style.display = 'flex';
                window.closeAssignDeanModal();
            } else {
                document.getElementById('deptErrorMessage').textContent = data.message;
                document.getElementById('deptErrorModal').style.display = 'flex';
            }
        });
    }
}

// Handle assign dean form submission
document.addEventListener('DOMContentLoaded', function() {
    // Color picker sync for add modal
    const addColorPicker = document.getElementById('add_color_picker');
    const addColorHex = document.getElementById('add_color_hex');
    if (addColorPicker && addColorHex) {
        addColorPicker.addEventListener('input', function() {
            addColorHex.value = this.value.toUpperCase();
        });
        addColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                addColorPicker.value = this.value;
            }
        });
    }
    
    // Color picker sync for edit modal
    const editColorPicker = document.getElementById('edit_color_picker');
    const editColorHex = document.getElementById('edit_color_hex');
    if (editColorPicker && editColorHex) {
        editColorPicker.addEventListener('input', function() {
            editColorHex.value = this.value.toUpperCase();
        });
        editColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                editColorPicker.value = this.value;
            }
        });
    }
    
    // Handle add department form
    const addForm = document.getElementById('addDepartmentForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(addForm);
            formData.append('color_code', document.getElementById('add_color_hex').value);
            
            fetch('./api/add_department.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('deptSuccessMessage').textContent = data.message || 'Department added successfully!';
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    window.closeAddDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            });
        });
    }
    
    // Handle edit department form
    const editForm = document.getElementById('editDepartmentForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(editForm);
            formData.append('color_code', document.getElementById('edit_color_hex').value);
            
            fetch('./api/edit_department.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('deptSuccessMessage').textContent = data.message || 'Department updated successfully!';
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    window.closeEditDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            });
        });
    }
    
    // Handle assign dean form
    const assignDeanForm = document.getElementById('assignDeanForm');
    if (assignDeanForm) {
        assignDeanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(assignDeanForm);
            
            fetch('./api/assign_dean.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('deptSuccessMessage').textContent = data.message || 'Dean assigned successfully!';
                    document.getElementById('deptSuccessModal').style.display = 'flex';
                    window.closeAssignDeanModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
            });
        });
    }
});
</script>