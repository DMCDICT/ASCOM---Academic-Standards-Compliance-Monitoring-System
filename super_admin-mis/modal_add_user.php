<?php
// modal_add_user.php
// This file is an HTML fragment, included by content.php.
// Activate PHP Data Fetching for departments and roles
if (!isset($conn)) {
    require_once __DIR__ . '/includes/db_connection.php';
}
if (!isset($conn) || $conn->connect_error) {
    $departments = [];
    $roles = [];
}
global $conn;
$departments = $departments ?? [];
$roles = [];
$roleLabels = [
    'super_admin' => 'Super Admin',
    'dean' => 'Department Dean',
    'teacher' => 'Teacher',
    'qa' => 'Quality Assurance',
    'quality_assurance' => 'Quality Assurance',
    'librarian' => 'Librarian',
];
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    // Fetch departments
    $departmentsQuery = "SELECT id, department_code FROM departments ORDER BY department_code ASC";
    $departmentsResult = $conn->query($departmentsQuery);
    if ($departmentsResult) {
        while ($row = $departmentsResult->fetch_assoc()) {
            $departments[$row['id']] = $row['department_code'];
        }
        $departmentsResult->free();
    } else {
    }
    // Fetch roles
    $rolesQuery = "SELECT id, role_name as role FROM roles WHERE LOWER(role_name) NOT IN ('program_head', 'program-head') ORDER BY FIELD(role_name, 'super_admin', 'dean', 'teacher', 'qa', 'quality_assurance', 'librarian'), role_name ASC";
    $rolesResult = $conn->query($rolesQuery);
    if ($rolesResult) {
        while ($row = $rolesResult->fetch_assoc()) {
            $roles[$row['id']] = $row['role'];
        }
        $rolesResult->free();
    } else {
    }
}
?>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-overlay" style="display: none;" data-modal-state="hidden">
  <div class="modal-box add-user-modal-shell">
    <!-- Modal Header -->
    <div class="add-user-modal-hero">
      <div class="add-user-modal-hero-left">
        <div class="add-user-modal-icon" aria-hidden="true">
          <i data-lucide="user-plus"></i>
        </div>
        <div>
          <h2 class="add-user-modal-title">Add New User</h2>
          <p class="add-user-modal-subtitle">Create an account and assign the correct access level.</p>
        </div>
      </div>
      <button type="button" class="add-user-close" onclick="closeAddUserModal()" aria-label="Close modal">
        <i data-lucide="x"></i>
      </button>
    </div>

    <!-- Info Note -->
    <div class="add-user-modal-note">
      <i data-lucide="info" aria-hidden="true"></i>
      Program head assignments are managed by department deans.
    </div>

    <!-- Form -->
    <form id="addUserForm" class="add-user-form">
      <!-- Section 1: Account Details -->
      <div class="section-header">
        <div class="label-bar"></div>
        <div>
          <h3>Account Details</h3>
          <p>Select role and enter employee information.</p>
        </div>
      </div>
      <div class="add-user-grid">
        <div class="form-group">
          <label for="add_role_id">Role <span class="required">*</span></label>
          <select name="role_id" id="add_role_id" required onchange="handleRoleChange()">
            <option value="">Select a role</option>
            <?php foreach ($roles as $id => $role): ?>
              <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role))); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="add_employee_no">Employee No. <span class="required">*</span></label>
          <input type="text" name="employee_no" id="add_employee_no" required maxlength="6" placeholder="6-digit number" autocomplete="off" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)">
        </div>
      </div>

      <!-- Section 2: Personal Information -->
      <div class="section-header">
        <div class="label-bar"></div>
        <div>
          <h3>Personal Information</h3>
          <p>Enter the user's name and department.</p>
        </div>
      </div>
      <div class="add-user-grid add-user-grid-wide">
        <div class="form-group">
          <label id="add_department_label" for="add_department_id">Department</label>
          <select name="department_id" id="add_department_id">
            <option value="">Select department</option>
            <?php foreach ($departments as $id => $code): ?>
              <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($code); ?></option>
            <?php endforeach; ?>
          </select>
          <div id="add_department_help" class="field-help">Required for dean and teacher roles.</div>
        </div>
        <div class="form-group">
          <label for="add_title">Title</label>
          <select name="title" id="add_title">
            <option value="">No title</option>
            <option>Mr.</option>
            <option>Mrs.</option>
            <option>Ms.</option>
            <option>Dr.</option>
            <option>Prof.</option>
          </select>
        </div>
        <div class="form-group">
          <label for="add_first_name">First Name <span class="required">*</span></label>
          <input type="text" name="first_name" id="add_first_name" required placeholder="e.g. Juan" autocomplete="off">
        </div>
        <div class="form-group">
          <label for="add_middle_name">Middle Name</label>
          <input type="text" name="middle_name" id="add_middle_name" placeholder="e.g. Santos" autocomplete="off">
        </div>
        <div class="form-group">
          <label for="add_last_name">Last Name <span class="required">*</span></label>
          <input type="text" name="last_name" id="add_last_name" required placeholder="e.g. Dela Cruz" autocomplete="off">
        </div>
      </div>

      <!-- Section 3: Contact Details -->
      <div class="section-header">
        <div class="label-bar"></div>
        <div>
          <h3>Contact Details</h3>
          <p>Institutional email and mobile number.</p>
        </div>
      </div>
      <div class="add-user-grid add-user-grid-wide">
        <div class="form-group add-user-email-group">
          <label for="add_institutional_email">Institutional Email <span class="required">*</span></label>
          <div class="input-with-clear">
            <input type="email" name="institutional_email" id="add_institutional_email" required placeholder="username@sccpag.edu.ph">
            <button type="button" id="clear_add_email_btn" class="clear-btn" title="Clear field" aria-label="Clear email">
              <i data-lucide="x"></i>
            </button>
          </div>
          <div class="field-help">@sccpag.edu.ph will be used for this account.</div>
        </div>
        <div class="form-group">
          <label for="add_mobile_no">Mobile Number</label>
          <input type="text" name="mobile_no" id="add_mobile_no" maxlength="11" placeholder="e.g. 09123456789">
        </div>
      </div>

      <!-- Section 4: Account Security -->
      <div class="section-header">
        <div class="label-bar"></div>
        <div>
          <h3>Account Security</h3>
          <p>Set password for this account.</p>
        </div>
      </div>
      <div class="add-user-grid password-row">
        <div class="form-group password-group">
          <label for="add_password">Password <span class="required">*</span></label>
          <div class="input-with-toggle">
            <input type="password" name="password" id="add_password" autocomplete="new-password" minlength="8" required placeholder="Minimum 8 characters">
            <button type="button" class="toggle-password" data-target="add_password" aria-label="Show or hide password">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
        <div class="form-group password-group">
          <label for="add_confirm_password">Confirm Password <span class="required">*</span></label>
          <div class="input-with-toggle">
            <input type="password" name="confirm_password" id="add_confirm_password" autocomplete="new-password" required placeholder="Re-enter password">
            <button type="button" class="toggle-password" data-target="add_confirm_password" aria-label="Show or hide password">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Validation Message -->
      <div id="addUserValidationMsg" class="validation-msg" style="display: none;"></div>

      <!-- Actions -->
      <div class="add-user-actions">
        <button type="button" class="cancel-btn" onclick="closeAddUserModal()">Cancel</button>
        <button type="submit" id="add_create_btn" class="create-btn" disabled>Create</button>
      </div>
    </form>
  </div>
</div>

<!-- Success Modal for Teacher Account Creation -->
<div id="addUserSuccessModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
  <div class="modal-box" style="max-width: 500px; text-align: center;">
    <div class="modal-header">
      <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block;">
      <h2 style="color: #28a745; margin-bottom: 10px;">Success!</h2>
    </div>
    <div class="modal-body">
      <p id="addUserSuccessMessage" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 20px;">
        Teacher account created successfully!
      </p>
      <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
          <strong>Next Steps:</strong><br>
          • The teacher can now log in using their institutional email<br>
          • Default password has been generated and sent to their email<br>
          • They should change their password on first login
        </p>
      </div>
    </div>
    <div class="modal-actions" style="text-align: center;">
      <button type="button" class="create-btn" onclick="closeAddUserSuccessModal()" style="min-width: 120px;">
        OK
      </button>
    </div>
  </div>
</div>

<!-- Error Modal for Teacher Account Creation -->
<div id="addUserErrorModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
  <div class="modal-box" style="max-width: 500px; text-align: center;">
    <div class="modal-header">
      <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block;">
      <h2 id="addUserErrorHeading" style="color: #dc3545; margin-bottom: 10px;">Error!</h2>
    </div>
    <div class="modal-body">
      <p id="addUserErrorMessage" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 20px;">
        An error occurred while creating the teacher account.
      </p>
    </div>
    <div class="modal-actions" style="text-align: center;">
      <button type="button" class="cancel-btn" onclick="closeAddUserErrorModal()" style="min-width: 120px;">
        OK
      </button>
    </div>
  </div>
</div>

<!-- Warning Modal for Teacher Account Creation -->
<div id="addUserWarningModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
  <div class="modal-box" style="max-width: 500px; text-align: center;">
    <div class="modal-header">
      <img src="../src/assets/animated_icons/warning-animated-icon.gif" alt="Warning" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block;">
      <h2 style="color: #ffc107; margin-bottom: 10px;">Warning!</h2>
    </div>
    <div class="modal-body">
      <p id="addUserWarningMessage" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 20px;">
        Please review the information before proceeding.
      </p>
    </div>
    <div class="modal-actions" style="text-align: center;">
      <button type="button" class="cancel-btn" onclick="closeAddUserWarningModal()" style="min-width: 120px;">
        OK
      </button>
    </div>
  </div>
</div>

<?php
// Force department IDs to strings to avoid JS array re-indexing bug
$departments = array_combine(array_map('strval', array_keys($departments)), array_values($departments));
?>
<script>
window.departments = <?php echo json_encode($departments); ?>;
window.roles = <?php echo json_encode($roles); ?>;

// Handle role change to dynamically update department requirement
function handleRoleChange() {
    const roleSelect = document.getElementById('add_role_id');
    const departmentSelect = document.getElementById('add_department_id');
    const departmentLabel = document.getElementById('add_department_label');
    const departmentHelp = document.getElementById('add_department_help');
    
    const selectedRole = roleSelect.value;
    
    // Role IDs from database: 1=super_admin, 2=dean, 3=teacher, 4=qa, 5=librarian
    // Department required for: 2 = dean, 3 = teacher
    // Department optional for: 4 = qa, 5 = librarian
    const departmentRequiredRoles = ['2', '3']; // Dean and Teacher
    const departmentOptionalRoles = ['4', '5']; // QA and Librarian
    
    if (departmentRequiredRoles.includes(selectedRole)) {
        // Department is required for Dean and Teacher
        departmentLabel.innerHTML = 'Department <span style="color: #dc3545;">*</span>';
        departmentSelect.setAttribute('required', 'required');
        if (departmentHelp) {
            departmentHelp.textContent = 'Required for dean and teacher roles.';
        }
    } else if (departmentOptionalRoles.includes(selectedRole)) {
        // Department is optional for Librarian and QA
        departmentLabel.innerHTML = 'Department <span style="color: #666; font-size: 12px;">(optional)</span>';
        departmentSelect.removeAttribute('required');
        if (departmentHelp) {
            departmentHelp.textContent = 'Optional for QA and librarian roles.';
        }
    } else {
        // No role selected yet
        departmentLabel.innerHTML = 'Department';
        departmentSelect.removeAttribute('required');
        if (departmentHelp) {
            departmentHelp.textContent = 'Required for dean and teacher roles.';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Reset form when modal opens
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                // Modal backdrop clicked - could add reset logic here
            }
        });
    }
    
    // Initial state check for role (in case form was pre-filled)
    const roleSelect = document.getElementById('add_role_id');
    if (roleSelect && roleSelect.value) {
        handleRoleChange();
    }
});
</script>
