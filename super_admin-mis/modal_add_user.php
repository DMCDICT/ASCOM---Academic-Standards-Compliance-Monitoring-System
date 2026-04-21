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
    $rolesQuery = "SELECT id, role_name as role FROM roles ORDER BY id ASC";
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
<div id="addUserModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;" data-modal-state="hidden">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 650px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; max-height: 98vh; overflow-y: auto; margin: 20px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Add New User</h2>
      <span onclick="closeAddUserModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer; transition: color 0.2s;">&times;</span>
    </div>
    
    <form id="addUserForm" style="display: flex; flex-direction: column; gap: 15px;">
      <!-- Row 1: Role (FIRST!) & Employee No. -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Role <span style="color: #dc3545;">*</span></label>
          <select name="role_id" id="add_role_id" required onchange="handleRoleChange()" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $id => $role): ?>
              <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars(ucfirst($role)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Employee No. <span style="color: #dc3545;">*</span></label>
          <input type="text" name="employee_no" id="add_employee_no" required maxlength="6" placeholder="6-digit number" autocomplete="off" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <!-- Row 2: Department & First Name -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label id="add_department_label" style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department</label>
          <select name="department_id" id="add_department_id" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $id => $code): ?>
              <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($code); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">First Name <span style="color: #dc3545;">*</span></label>
          <input type="text" name="first_name" id="add_first_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <!-- Row 3: Middle Name & Last Name -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1.2;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Middle Name</label>
          <input type="text" name="middle_name" id="add_middle_name" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Last Name <span style="color: #dc3545;">*</span></label>
          <input type="text" name="last_name" id="add_last_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <!-- Row 4: Title & Institutional Email -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1.2;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Title</label>
          <select name="title" id="add_title" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <option value="">--</option>
            <option>Mr.</option>
            <option>Mrs.</option>
            <option>Ms.</option>
            <option>Dr.</option>
            <option>Prof.</option>
          </select>
        </div>
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">
            Institutional Email <span style="color: #dc3545;">*</span>
            <span style="color: #666; font-size: 12px;">@sccpag.edu.ph</span>
          </label>
          <div style="position: relative;">
            <input type="email" name="institutional_email" id="add_institutional_email" required placeholder="username@sccpag.edu.ph" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <button type="button" id="clear_add_email_btn" title="Clear field" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999;">✕</button>
          </div>
        </div>
      </div>
      
      <!-- Row 5: Mobile Number & Password -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Mobile Number</label>
          <input type="text" name="mobile_no" id="add_mobile_no" maxlength="11" placeholder="e.g., 09123456789" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Password <span style="color: #dc3545;">*</span> <span style="color: #666; font-size: 11px;">(min 8 chars)</span></label>
          <div style="position: relative;">
            <input type="password" name="password" id="add_password" autocomplete="new-password" minlength="8" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="add_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
          </div>
        </div>
      </div>
      
      <!-- Row 6: Confirm Password -->
      <div>
        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Confirm Password <span style="color: #dc3545;">*</span></label>
        <div style="position: relative;">
          <input type="password" name="confirm_password" id="add_confirm_password" autocomplete="new-password" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
          <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="add_confirm_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
        </div>
      </div>
      
      <!-- Row 6: Password & Confirm Password -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Password <span style="color: #dc3545;">*</span> <span style="color: #666; font-size: 11px;">(min 8 chars)</span></label>
          <div style="position: relative;">
            <input type="password" name="password" id="add_password" autocomplete="new-password" minlength="8" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="add_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
          </div>
        </div>
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Confirm Password <span style="color: #dc3545;">*</span></label>
          <div style="position: relative;">
            <input type="password" name="confirm_password" id="add_confirm_password" autocomplete="new-password" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="add_confirm_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
          </div>
        </div>
      </div>
      
      <!-- Validation Messages -->
      <div id="addUserValidationMsg" style="display: none; padding: 10px; border-radius: 8px; font-size: 14px;"></div>
      
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="closeAddUserModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" id="add_create_btn" style="width: 125px; height: 50px; background-color: #28a745; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CREATE</button>
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
    } else if (departmentOptionalRoles.includes(selectedRole)) {
        // Department is optional for Librarian and QA
        departmentLabel.innerHTML = 'Department <span style="color: #666; font-size: 12px;">(optional)</span>';
        departmentSelect.removeAttribute('required');
    } else {
        // No role selected yet
        departmentLabel.innerHTML = 'Department';
        departmentSelect.removeAttribute('required');
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

