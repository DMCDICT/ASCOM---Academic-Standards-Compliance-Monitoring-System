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

function ascom_normalize_hex_color($color, $fallback = '#1976d2') {
    if (!is_string($color)) {
        return $fallback;
    }
    $trimmed = trim($color);
    if ($trimmed === '') {
        return $fallback;
    }
    if ($trimmed[0] !== '#') {
        $trimmed = '#' . $trimmed;
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $trimmed)) {
        return $fallback;
    }
    return strtoupper($trimmed);
}

function ascom_is_light_hex_color($hex) {
    $normalized = ascom_normalize_hex_color($hex, '#FFFFFF');
    $r = hexdec(substr($normalized, 1, 2)) / 255;
    $g = hexdec(substr($normalized, 3, 2)) / 255;
    $b = hexdec(substr($normalized, 5, 2)) / 255;
    // Perceived luminance (good enough for UI contrast decisions)
    $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    return $luminance > 0.62;
}

function ascom_on_color_for_hex($hex) {
    return ascom_is_light_hex_color($hex) ? '#111827' : '#FFFFFF';
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
        <?php
            $deptColor = ascom_normalize_hex_color($dept['color_code'] ?? '', '#1976d2');
            $deptOnColor = ascom_on_color_for_hex($deptColor);
            $isLightHeader = ascom_is_light_hex_color($deptColor);
            $deptChipBg = $isLightHeader ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.18)';
            $deptChipBorder = $isLightHeader ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.26)';
            $deptActionBorder = $isLightHeader ? 'rgba(17,24,39,0.45)' : 'rgba(255,255,255,0.55)';
            $deptFocusRing = $isLightHeader ? 'rgba(17,24,39,0.55)' : 'rgba(255,255,255,0.80)';
            $deptHeaderDivider = $isLightHeader ? 'rgba(0,0,0,0.10)' : 'rgba(255,255,255,0.22)';
            $deptActionHover = $isLightHeader ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.16)';
            $deptActionActive = $isLightHeader ? 'rgba(0,0,0,0.14)' : 'rgba(255,255,255,0.22)';
        ?>
        <div class="department-card" data-dept-id="<?php echo $dept['id']; ?>">
            <div class="dept-main">
                <div
                    class="dept-row dept-row-top"
                    style="
                        --dept-color: <?php echo htmlspecialchars($deptColor); ?>;
                        --dept-on-color: <?php echo htmlspecialchars($deptOnColor); ?>;
                        --dept-chip-bg: <?php echo htmlspecialchars($deptChipBg); ?>;
                        --dept-chip-border: <?php echo htmlspecialchars($deptChipBorder); ?>;
                        --dept-action-border: <?php echo htmlspecialchars($deptActionBorder); ?>;
                        --dept-focus-ring: <?php echo htmlspecialchars($deptFocusRing); ?>;
                        --dept-header-divider: <?php echo htmlspecialchars($deptHeaderDivider); ?>;
                        --dept-action-hover: <?php echo htmlspecialchars($deptActionHover); ?>;
                        --dept-action-active: <?php echo htmlspecialchars($deptActionActive); ?>;
                    "
                >
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

<!-- Assign Dean Modal -->
<div id="assignDeanModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal dept-form-modal" role="dialog" aria-modal="true" aria-labelledby="assignDeanTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span class="dept-details-modal__badge">DEAN</span>
        <div class="dept-details-modal__titles">
          <h2 id="assignDeanTitle" class="dept-details-modal__title">Assign Dean</h2>
          <div id="assignDeanDeptName" class="dept-details-modal__subtitle"></div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeAssignDeanModal()" aria-label="Close modal">&times;</button>
    </div>
    <div class="dept-details-modal__content">
      <form id="assignDeanForm" class="dept-form">
        <input type="hidden" name="department_id" id="assign_dept_id">
        <div class="dept-form__group">
          <label class="dept-form__label" for="assign_user_id">Select User with Dean Role <span class="dept-form__req">*</span></label>
          <select name="user_id" id="assign_user_id" class="dept-form__control" required>
            <option value="">-- Select Dean --</option>
          </select>
        </div>
        <div id="currentDeanSection" class="dept-form__notice" style="display: none;">
          <div><strong>Current Dean:</strong> <span id="currentDeanName"></span></div>
          <button type="button" class="dept-link-danger" onclick="window.removeDean()">Remove</button>
        </div>
        <div class="dept-form__actions">
          <button type="button" class="dept-btn dept-btn--secondary" onclick="window.closeAssignDeanModal()">Cancel</button>
          <button type="submit" class="dept-btn dept-btn--primary">Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div id="deptSuccessModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal dept-feedback-modal" role="dialog" aria-modal="true" aria-labelledby="deptSuccessTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span class="dept-details-modal__badge">OK</span>
        <div class="dept-details-modal__titles">
          <h2 id="deptSuccessTitle" class="dept-details-modal__title">Success</h2>
          <div class="dept-details-modal__subtitle">Action completed.</div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeDeptSuccessModal()" aria-label="Close modal">&times;</button>
    </div>
    <div class="dept-details-modal__content">
      <div class="dept-feedback-modal__icon dept-feedback-modal__icon--success">✓</div>
      <div id="deptSuccessMessage" class="dept-feedback-modal__message">Operation completed successfully!</div>
      <div class="dept-form__actions" style="justify-content: center;">
        <button type="button" class="dept-btn dept-btn--primary" onclick="window.closeDeptSuccessModal(true)">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Error Modal -->
<div id="deptErrorModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal dept-feedback-modal" role="dialog" aria-modal="true" aria-labelledby="deptErrorTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span class="dept-details-modal__badge">ERR</span>
        <div class="dept-details-modal__titles">
          <h2 id="deptErrorTitle" class="dept-details-modal__title">Error</h2>
          <div class="dept-details-modal__subtitle">Something went wrong.</div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeDeptErrorModal()" aria-label="Close modal">&times;</button>
    </div>
    <div class="dept-details-modal__content">
      <div class="dept-feedback-modal__icon dept-feedback-modal__icon--error">✕</div>
      <div id="deptErrorMessage" class="dept-feedback-modal__message">An error occurred.</div>
      <div class="dept-form__actions" style="justify-content: center;">
        <button type="button" class="dept-btn dept-btn--primary" onclick="window.closeDeptErrorModal()">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Department Modal -->
<div id="editDepartmentModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal dept-form-modal" role="dialog" aria-modal="true" aria-labelledby="editDepartmentTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span class="dept-details-modal__badge">EDIT</span>
        <div class="dept-details-modal__titles">
          <h2 id="editDepartmentTitle" class="dept-details-modal__title">Edit Department</h2>
          <div class="dept-details-modal__subtitle">Update department information.</div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeEditDepartmentModal()" aria-label="Close modal">&times;</button>
    </div>
    <div class="dept-details-modal__content">
      <form id="editDepartmentForm" class="dept-form">
        <input type="hidden" name="department_id" id="edit_dept_id">
        <div class="dept-form__group">
          <label class="dept-form__label" for="edit_department_name">Department Name <span class="dept-form__req">*</span></label>
          <input type="text" name="department_name" id="edit_department_name" required placeholder="e.g., College of Computing Studies" class="dept-form__control">
        </div>
        <div class="dept-form__group">
          <label class="dept-form__label" for="edit_department_code">Department Code <span class="dept-form__req">*</span></label>
          <input type="text" name="department_code" id="edit_department_code" required placeholder="e.g., CCS" maxlength="10" class="dept-form__control" style="text-transform: uppercase;">
        </div>
        <div class="dept-form__group">
          <label class="dept-form__label" for="edit_color_hex">Color Code</label>
          <div class="dept-form__row">
            <input type="color" id="edit_color_picker" value="#1976d2" class="dept-form__color">
            <input type="text" id="edit_color_hex" value="#1976d2" maxlength="7" class="dept-form__control" style="flex: 1;">
          </div>
        </div>
        <div class="dept-form__actions">
          <button type="button" class="dept-btn dept-btn--secondary" onclick="window.closeEditDepartmentModal()">Cancel</button>
          <button type="submit" class="dept-btn dept-btn--primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Department Modal -->
<div id="addDepartmentModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal dept-form-modal" role="dialog" aria-modal="true" aria-labelledby="addDepartmentTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span class="dept-details-modal__badge">ADD</span>
        <div class="dept-details-modal__titles">
          <h2 id="addDepartmentTitle" class="dept-details-modal__title">Add Department</h2>
          <div class="dept-details-modal__subtitle">Create a new department.</div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeAddDepartmentModal()" aria-label="Close modal">&times;</button>
    </div>
    <div class="dept-details-modal__content">
      <form id="addDepartmentForm" class="dept-form">
        <div class="dept-form__group">
          <label class="dept-form__label" for="add_department_name">Department Name <span class="dept-form__req">*</span></label>
          <input type="text" name="department_name" id="add_department_name" required placeholder="e.g., College of Computing Studies" class="dept-form__control">
        </div>
        <div class="dept-form__group">
          <label class="dept-form__label" for="add_department_code">Department Code <span class="dept-form__req">*</span></label>
          <input type="text" name="department_code" id="add_department_code" required placeholder="e.g., CCS" maxlength="10" class="dept-form__control" style="text-transform: uppercase;">
        </div>
        <div class="dept-form__group">
          <label class="dept-form__label" for="add_color_hex">Color Code</label>
          <div class="dept-form__row">
            <input type="color" id="add_color_picker" value="#1976d2" class="dept-form__color">
            <input type="text" id="add_color_hex" value="#1976d2" maxlength="7" class="dept-form__control" style="flex: 1;">
          </div>
        </div>
        <div class="dept-form__actions">
          <button type="button" class="dept-btn dept-btn--secondary" onclick="window.closeAddDepartmentModal()">Cancel</button>
          <button type="submit" class="dept-btn dept-btn--primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Department Details Modal -->
<div id="departmentDetailsModal" class="modal-overlay" style="display: none;">
  <div class="modal-box dept-details-modal" role="dialog" aria-modal="true" aria-labelledby="detailsModalTitle">
    <div class="dept-details-modal__header">
      <div class="dept-details-modal__titlewrap">
        <span id="deptDetailsBadge" class="dept-details-modal__badge">DEPT</span>
        <div class="dept-details-modal__titles">
          <h2 id="detailsModalTitle" class="dept-details-modal__title">Department Details</h2>
          <div id="deptDetailsSubtitle" class="dept-details-modal__subtitle"></div>
        </div>
      </div>
      <button type="button" class="dept-details-modal__close" onclick="window.closeDepartmentDetailsModal()" aria-label="Close modal">&times;</button>
    </div>
    <div id="departmentDetailsContent" class="dept-details-modal__content" aria-live="polite">
      <div class="dept-details-modal__loading">Loading...</div>
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
    const subtitle = document.getElementById('deptDetailsSubtitle');
    const badge = document.getElementById('deptDetailsBadge');
    const modalBox = modal ? modal.querySelector('.modal-box') : null;
    
    if (!modal || !content) {
        console.error('Department Details Modal not found');
        alert('Error: Modal not loaded. Please refresh the page.');
        return;
    }
    
    content.innerHTML = '<div class="dept-details-modal__loading">Loading...</div>';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    fetch('./api/get_department_data.php?id=' + deptId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const dept = data.data;
            title.textContent = dept.department_name || 'Department Details';
            if (subtitle) {
                subtitle.textContent = dept.dean_name ? `Dean: ${dept.dean_name}` : 'No dean assigned';
            }
            if (badge) {
                badge.textContent = (dept.department_code || 'DEPT').toString().toUpperCase();
            }
            if (modalBox) {
                modalBox.style.setProperty('--dept-color', (dept.color_code || '#0C4B34'));
            }
            
            let html = '';
            
            // Dean Section
            html += '<section class="dept-details-modal__section">';
            html += '<h3 class="dept-details-modal__section-title">Dean</h3>';
            if (dept.dean_name) {
                html += '<div class="dept-details-modal__kv">';
                html += '<div class="dept-details-modal__k">Name</div><div class="dept-details-modal__v">' + dept.dean_name + '</div>';
                html += '<div class="dept-details-modal__k">Employee No</div><div class="dept-details-modal__v">' + (dept.dean_employee_no || 'N/A') + '</div>';
                html += '</div>';
            } else {
                html += '<div class="dept-details-modal__empty">No dean assigned</div>';
            }
            html += '</section>';
            
            // Stats Summary
            html += '<section class="dept-details-modal__stats">';
            html += '<div class="dept-details-modal__stat"><div class="dept-details-modal__stat-value">' + (dept.program_count ?? 0) + '</div><div class="dept-details-modal__stat-label">Programs</div></div>';
            html += '<div class="dept-details-modal__stat"><div class="dept-details-modal__stat-value">' + (dept.course_count ?? 0) + '</div><div class="dept-details-modal__stat-label">Courses</div></div>';
            html += '<div class="dept-details-modal__stat"><div class="dept-details-modal__stat-value">' + (dept.teacher_count ?? 0) + '</div><div class="dept-details-modal__stat-label">Teachers</div></div>';
            html += '</section>';
            
            // Programs (if any)
            if (dept.programs && dept.programs.length > 0) {
                html += '<section class="dept-details-modal__section">';
                html += '<h3 class="dept-details-modal__section-title">Programs <span class="dept-details-modal__count">(' + dept.programs.length + ')</span></h3>';
                html += '<ul class="dept-details-modal__list">';
                dept.programs.forEach(function(prog) {
                    html += '<li class="dept-details-modal__list-item"><span class="dept-details-modal__list-code">' + prog.program_code + '</span><span class="dept-details-modal__list-text">' + prog.program_name + (prog.major ? ' — ' + prog.major : '') + '</span></li>';
                });
                html += '</ul></section>';
            }
            
            // Courses (if any)
            if (dept.courses && dept.courses.length > 0) {
                html += '<section class="dept-details-modal__section">';
                html += '<h3 class="dept-details-modal__section-title">Courses <span class="dept-details-modal__count">(' + dept.courses.length + ')</span></h3>';
                html += '<ul class="dept-details-modal__list">';
                dept.courses.slice(0, 10).forEach(function(course) {
                    html += '<li class="dept-details-modal__list-item"><span class="dept-details-modal__list-code">' + course.course_code + '</span><span class="dept-details-modal__list-text">' + course.course_title + '</span><span class="dept-details-modal__list-meta">' + (course.units || 0) + ' units</span></li>';
                });
                if (dept.courses.length > 10) {
                    html += '<li class="dept-details-modal__list-more">…and ' + (dept.courses.length - 10) + ' more courses</li>';
                }
                html += '</ul></section>';
            }
            
            // Teachers (if any)
            if (dept.teachers && dept.teachers.length > 0) {
                html += '<section class="dept-details-modal__section">';
                html += '<h3 class="dept-details-modal__section-title">Teachers <span class="dept-details-modal__count">(' + dept.teachers.length + ')</span></h3>';
                html += '<ul class="dept-details-modal__list">';
                dept.teachers.slice(0, 10).forEach(function(teacher) {
                    html += '<li class="dept-details-modal__list-item"><span class="dept-details-modal__list-text">' + teacher.first_name + ' ' + teacher.last_name + '</span><span class="dept-details-modal__list-meta">' + teacher.employee_no + '</span></li>';
                });
                if (dept.teachers.length > 10) {
                    html += '<li class="dept-details-modal__list-more">…and ' + (dept.teachers.length - 10) + ' more teachers</li>';
                }
                html += '</ul></section>';
            }
            
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div class="dept-details-modal__error">Error loading department details.</div>';
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        content.innerHTML = '<div class="dept-details-modal__error">Network error. Please try again.</div>';
    });

    // Close on overlay click + Esc
    modal.onclick = function(e) {
        if (e.target === modal) {
            window.closeDepartmentDetailsModal();
        }
    };
    window.__deptDetailsEscHandler = function(e) {
        if (e.key === 'Escape') {
            window.closeDepartmentDetailsModal();
        }
    };
    document.addEventListener('keydown', window.__deptDetailsEscHandler);
}

window.openDepartmentDetailsModal = openDepartmentDetailsModal;

function closeDepartmentDetailsModal() {
    const modal = document.getElementById('departmentDetailsModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    const subtitle = document.getElementById('deptDetailsSubtitle');
    if (subtitle) subtitle.textContent = '';
    if (modal) modal.onclick = null;
    if (window.__deptDetailsEscHandler) {
        document.removeEventListener('keydown', window.__deptDetailsEscHandler);
        window.__deptDetailsEscHandler = null;
    }
}

window.closeDepartmentDetailsModal = closeDepartmentDetailsModal;

function setupModalDismiss(modalId, onClose) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.onclick = function(e) {
        if (e.target === modal) {
            onClose();
        }
    };

    const handlerKey = `__${modalId}EscHandler`;
    window[handlerKey] = function(e) {
        if (e.key === 'Escape') {
            onClose();
        }
    };
    document.addEventListener('keydown', window[handlerKey]);
}

function teardownModalDismiss(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.onclick = null;

    const handlerKey = `__${modalId}EscHandler`;
    if (window[handlerKey]) {
        document.removeEventListener('keydown', window[handlerKey]);
        window[handlerKey] = null;
    }
}

window.closeDeptSuccessModal = function(shouldReload) {
    const modal = document.getElementById('deptSuccessModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    teardownModalDismiss('deptSuccessModal');
    if (shouldReload) {
        location.reload();
    }
};

window.closeDeptErrorModal = function() {
    const modal = document.getElementById('deptErrorModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    teardownModalDismiss('deptErrorModal');
};

window.closeAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    teardownModalDismiss('addDepartmentModal');
};

window.closeEditDepartmentModal = function() {
    const modal = document.getElementById('editDepartmentModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    teardownModalDismiss('editDepartmentModal');
};

function openAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setupModalDismiss('addDepartmentModal', window.closeAddDepartmentModal);
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
            setupModalDismiss('editDepartmentModal', window.closeEditDepartmentModal);
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
    
    // Fetch potential deans for this department (users with Dean role)
    fetch('./api/get_department_teachers.php?department_id=' + deptId)
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('assign_user_id');
        select.innerHTML = '<option value="">-- Select Dean --</option>';
        
        if (data.success && data.teachers && data.teachers.length > 0) {
            data.teachers.forEach(function(teacher) {
                const option = document.createElement('option');
                option.value = teacher.id;
                // Show name, employee no, and their roles
                const rolesText = teacher.roles ? ' [' + teacher.roles + ']' : '';
                option.textContent = teacher.display_name + ' (' + teacher.employee_no + ')' + rolesText;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">No users with Dean role found in this department</option>';
        }
        
        // Add helper message if no users available
        const helperMsg = document.getElementById('assignDeanDeptName');
        if (data.teachers && data.teachers.length === 0 && helperMsg) {
            helperMsg.innerHTML = 'Department: ' + deptCode + '<br><small style="color: #dc3545;">No users with Dean role found. Please assign Dean role to a user first.</small>';
        }
    });
    
    document.getElementById('assignDeanModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setupModalDismiss('assignDeanModal', window.closeAssignDeanModal);
}

window.closeAssignDeanModal = function() {
    document.getElementById('assignDeanModal').style.display = 'none';
    document.body.style.overflow = '';
    teardownModalDismiss('assignDeanModal');
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
                document.body.style.overflow = 'hidden';
                setupModalDismiss('deptSuccessModal', () => window.closeDeptSuccessModal(false));
                window.closeAssignDeanModal();
            } else {
                document.getElementById('deptErrorMessage').textContent = data.message;
                document.getElementById('deptErrorModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
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
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptSuccessModal', () => window.closeDeptSuccessModal(false));
                    window.closeAddDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
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
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptSuccessModal', () => window.closeDeptSuccessModal(false));
                    window.closeEditDepartmentModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
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
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptSuccessModal', () => window.closeDeptSuccessModal(false));
                    window.closeAssignDeanModal();
                } else {
                    document.getElementById('deptErrorMessage').textContent = data.message;
                    document.getElementById('deptErrorModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
                }
            })
            .catch(function(error) {
                document.getElementById('deptErrorMessage').textContent = 'Network error. Please try again.';
                document.getElementById('deptErrorModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setupModalDismiss('deptErrorModal', window.closeDeptErrorModal);
            });
        });
    }
});
</script>
