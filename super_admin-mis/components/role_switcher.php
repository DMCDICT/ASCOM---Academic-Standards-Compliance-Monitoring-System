<?php
// role_switcher.php
// Component for switching between dean and teacher roles

// Check if user has multiple roles
$hasMultipleRoles = false;
$availableRoles = [];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Check if user is a teacher
    $teacherQuery = "
        SELECT u.id, d.department_code, d.department_name 
        FROM users u 
        JOIN departments d ON u.department_id = d.id 
        WHERE u.id = ? AND u.role_id = 4
    ";
    $teacherStmt = $conn->prepare($teacherQuery);
    $teacherStmt->bind_param("i", $userId);
    $teacherStmt->execute();
    $teacherResult = $teacherStmt->get_result();
    
    if ($teacherResult->num_rows > 0) {
        $availableRoles[] = [
            'type' => 'teacher',
            'department_code' => $teacherResult->fetch_assoc()['department_code'],
            'department_name' => $teacherResult->fetch_assoc()['department_name']
        ];
    }
    
    // Check if user is a dean
    $deanQuery = "
        SELECT d.department_code, d.department_name 
        FROM departments d 
        WHERE d.dean_user_id = ?
    ";
    $deanStmt = $conn->prepare($deanQuery);
    $deanStmt->bind_param("i", $userId);
    $deanStmt->execute();
    $deanResult = $deanStmt->get_result();
    
    if ($deanResult->num_rows > 0) {
        while ($deanRow = $deanResult->fetch_assoc()) {
            $availableRoles[] = [
                'type' => 'dean',
                'department_code' => $deanRow['department_code'],
                'department_name' => $deanRow['department_name']
            ];
        }
    }
    
    $hasMultipleRoles = count($availableRoles) > 1;
}

if ($hasMultipleRoles):
?>
<div class="role-switcher">
    <div class="role-switcher-header">
        <span class="role-switcher-title">Switch Role</span>
        <button class="role-switcher-toggle" onclick="toggleRoleSwitcher()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>
    </div>
    
    <div class="role-switcher-content" id="roleSwitcherContent">
        <?php foreach ($availableRoles as $role): ?>
            <?php 
            $isCurrentRole = ($_SESSION['selected_role'] ?? '') === $role['type'] && 
                           ($_SESSION['selected_department_code'] ?? '') === $role['department_code'];
            ?>
            <div class="role-option <?php echo $isCurrentRole ? 'current' : ''; ?>">
                <div class="role-option-info">
                    <div class="role-option-icon">
                        <?php echo $role['type'] === 'dean' ? '👨‍💼' : '👨‍🏫'; ?>
                    </div>
                    <div class="role-option-details">
                        <div class="role-option-title">
                            <?php echo $role['type'] === 'dean' ? 'Department Dean' : 'Teacher'; ?>
                        </div>
                        <div class="role-option-department">
                            <?php echo $role['department_name']; ?>
                        </div>
                    </div>
                </div>
                <?php if (!$isCurrentRole): ?>
                    <button class="role-switch-btn" onclick="switchRole('<?php echo $role['type']; ?>', '<?php echo $role['department_code']; ?>')">
                        Switch
                    </button>
                <?php else: ?>
                    <div class="current-role-badge">Current</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.role-switcher {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.role-switcher-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.role-switcher-title {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.role-switcher-toggle {
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    transition: transform 0.3s ease;
}

.role-switcher-toggle:hover {
    color: #333;
}

.role-switcher-toggle.rotated {
    transform: rotate(180deg);
}

.role-switcher-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.role-switcher-content.expanded {
    max-height: 300px;
}

.role-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #f1f3f4;
    transition: background-color 0.2s ease;
}

.role-option:last-child {
    border-bottom: none;
}

.role-option:hover {
    background-color: #f8f9fa;
}

.role-option.current {
    background-color: #e3f2fd;
    border-left: 4px solid #739AFF;
}

.role-option-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.role-option-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #739AFF;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 16px;
}

.role-option-details {
    flex: 1;
}

.role-option-title {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
}

.role-option-department {
    color: #666;
    font-size: 12px;
}

.role-switch-btn {
    background: #739AFF;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.role-switch-btn:hover {
    background: #5a7cfa;
}

.current-role-badge {
    background: #4CAF50;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}
</style>

<script>
function toggleRoleSwitcher() {
    const content = document.getElementById('roleSwitcherContent');
    const toggle = document.querySelector('.role-switcher-toggle');
    
    content.classList.toggle('expanded');
    toggle.classList.toggle('rotated');
}

function switchRole(roleType, departmentCode) {
    // Show loading state
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = 'Switching...';
    btn.disabled = true;
    
    // Make API call to switch role
    fetch('set_selected_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            role_type: roleType,
            department_code: departmentCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to appropriate interface
            if (roleType === 'dean') {
                window.location.href = '../department-dean/';
            } else {
                window.location.href = '../teachers/';
            }
        } else {
            alert('Failed to switch role: ' + data.message);
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to switch role. Please try again.');
        btn.textContent = originalText;
        btn.disabled = false;
    });
}
</script>
<?php endif; ?>
