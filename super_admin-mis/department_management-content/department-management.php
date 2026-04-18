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
            <div class="dept-header" onclick="toggleDepartmentDetails(<?php echo $dept['id']; ?>)">
                <div class="dept-info">
                    <div class="dept-color" style="background-color: <?php echo htmlspecialchars($dept['color_code'] ?? '#1976d2'); ?>;"></div>
                    <div class="dept-details">
                        <h3><?php echo htmlspecialchars($dept['department_name']); ?></h3>
                        <span class="dept-code"><?php echo htmlspecialchars($dept['department_code']); ?></span>
                    </div>
                </div>
                <div class="dept-stats">
                    <span class="stat-item">
                        <strong><?php echo $dept['program_count']; ?></strong> Programs
                    </span>
                    <span class="stat-item">
                        <strong><?php echo $dept['course_count']; ?></strong> Courses
                    </span>
                    <span class="stat-item">
                        <strong><?php echo $dept['teacher_count']; ?></strong> Teachers
                    </span>
                </div>
                <div class="dept-actions">
                    <label class="toggle-switch">
                        <input type="checkbox" class="dept-toggle" 
                            <?php echo ($dept['is_active'] ?? 1) ? 'checked' : ''; ?>
                            onchange="toggleDepartmentStatus(<?php echo $dept['id']; ?>, this.checked)"
                            onclick="event.stopPropagation()">
                        <span class="slider"></span>
                    </label>
                    <button class="edit-btn" onclick="openEditDepartmentModal(<?php echo $dept['id']; ?>)" onclick="event.stopPropagation()">
                        Edit
                    </button>
                    <span class="expand-icon">▼</span>
                </div>
            </div>
            
            <div class="dept-details-section" id="dept-details-<?php echo $dept['id']; ?>">
                <div class="details-grid">
                    <!-- Dean Section -->
                    <div class="detail-card">
                        <h4>Dean</h4>
                        <?php if ($dept['dean_user_id']): ?>
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span><?php echo htmlspecialchars($dept['dean_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Employee No:</span>
                            <span><?php echo htmlspecialchars($dept['dean_employee_no'] ?? 'N/A'); ?></span>
                        </div>
                        <?php else: ?>
                        <p class="no-data">No dean assigned</p>
                        <?php endif; ?>
                    </div>

                    <!-- Programs Section -->
                    <div class="detail-card">
                        <h4>Programs (<?php echo $dept['program_count']; ?>)</h4>
                        <?php 
                        $progQuery = "SELECT * FROM programs WHERE department_id = " . (int)$dept['id'];
                        $progResult = $conn->query($progQuery);
                        if ($progResult && $progResult->num_rows > 0):
                        ?>
                        <ul class="detail-list">
                            <?php while ($prog = $progResult->fetch_assoc()): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($prog['program_code']); ?></strong>
                                <?php echo htmlspecialchars($prog['program_name']); ?>
                                <?php if ($prog['major']): ?>
                                <span class="minor"> - <?php echo htmlspecialchars($prog['major']); ?></span>
                                <?php endif; ?>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php else: ?>
                        <p class="no-data">No programs</p>
                        <?php endif; ?>
                    </div>

                    <!-- Courses Section -->
                    <div class="detail-card">
                        <h4>Courses (<?php echo $dept['course_count']; ?>)</h4>
                        <?php 
                        $courseQuery = "SELECT * FROM courses WHERE department_id = " . (int)$dept['id'] . " LIMIT 10";
                        $courseResult = $conn->query($courseQuery);
                        if ($courseResult && $courseResult->num_rows > 0):
                        ?>
                        <ul class="detail-list">
                            <?php while ($course = $courseResult->fetch_assoc()): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($course['course_code']); ?></strong>
                                <?php echo htmlspecialchars($course['course_title']); ?>
                                <span class="units">(<?php echo $course['units'] ?? 0; ?> units)</span>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php if ($dept['course_count'] > 10): ?>
                        <p class="more">...and <?php echo $dept['course_count'] - 10; ?> more courses</p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p class="no-data">No courses</p>
                        <?php endif; ?>
                    </div>

                    <!-- Teachers Section -->
                    <div class="detail-card">
                        <h4>Teachers (<?php echo $dept['teacher_count']; ?>)</h4>
                        <?php 
                        $teacherQuery = "SELECT employee_no, first_name, last_name, email FROM users WHERE department_id = " . (int)$dept['id'] . " AND role_id = 4 LIMIT 10";
                        $teacherResult = $conn->query($teacherQuery);
                        if ($teacherResult && $teacherResult->num_rows > 0):
                        ?>
                        <ul class="detail-list">
                            <?php while ($teacher = $teacherResult->fetch_assoc()): ?>
                            <li>
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                <span class="minor">(<?php echo htmlspecialchars($teacher['employee_no']); ?>)</span>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php if ($dept['teacher_count'] > 10): ?>
                        <p class="more">...and <?php echo $dept['teacher_count'] - 10; ?> more teachers</p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p class="no-data">No teachers</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDepartmentDetails(deptId) {
    const detailsSection = document.getElementById('dept-details-' + deptId);
    const card = detailsSection.closest('.department-card');
    card.classList.toggle('expanded');
}

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

function openAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function openEditDepartmentModal(deptId) {
    const modal = document.getElementById('editDepartmentModal');
    if (modal) {
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
            }
        });
    }
}
</script>