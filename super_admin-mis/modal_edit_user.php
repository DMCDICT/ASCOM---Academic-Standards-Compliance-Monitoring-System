<?php
// modal_edit_user.php
// This file is an HTML fragment, included by content.php.
// Activate PHP Data Fetching for departments
if (!isset($conn)) {
    require_once __DIR__ . '/includes/db_connection.php';
}
if (!isset($conn) || $conn->connect_error) {
    $departments = [];
}
global $conn;
$departments = $departments ?? [];
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
}
?>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;" data-modal-state="hidden">
  <div class="modal-box" style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; max-height: 98vh; overflow-y: visible; margin: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
      <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Edit User Account</h2>
      <span onclick="closeEditUserModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer; transition: color 0.2s;">&times;</span>
    </div>
    
    <form id="editUserForm" style="display: flex; flex-direction: column; gap: 15px;">
      <input type="hidden" name="employee_no_original" id="employee_no_original">
      
      <!-- Row 1: Employee No. & Department -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Employee No.</label>
          <input type="text" name="employee_no" id="edit_employee_no" required maxlength="6" placeholder="6-digit number" autocomplete="off" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department</label>
          <select name="department_id" id="edit_department_id" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $id => $code): ?>
              <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($code); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      
      <!-- Row 2: First Name & Middle Name -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">First Name</label>
          <input type="text" name="first_name" id="edit_first_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
        <div style="flex: 1.2;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Middle Name (Optional)</label>
          <input type="text" name="middle_name" id="edit_middle_name" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <!-- Row 3: Last Name & Name Prefix/Title -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Last Name</label>
          <input type="text" name="last_name" id="edit_last_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
        <div style="flex: 1.2;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Name Prefix / Title</label>
          <select name="title" id="edit_title" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <option value="">--</option>
            <option>Mr.</option>
            <option>Mrs.</option>
            <option>Ms.</option>
            <option>Dr.</option>
            <option>Prof.</option>
          </select>
        </div>
      </div>
      
      <!-- Row 4: Institutional Email & Mobile Number -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 2.5;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">
            Institutional Email 
            <span style="color: #666; font-size: 12px;">@sccpag.edu.ph</span>
            <span style="color: #007bff; cursor: help;" title="This domain is automatically added and cannot be changed">ℹ️</span>
          </label>
          <div style="position: relative;">
            <input type="email" name="institutional_email" id="edit_institutional_email" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <button type="button" id="clear_edit_email_btn" title="Clear field" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999;">✕</button>
          </div>
        </div>
        <div style="flex: 1.2;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">
            Mobile Number 
            <span style="color: #666; font-size: 12px;">(11 digits)</span>
            <span style="color: #007bff; cursor: help;" title="Only numbers allowed, maximum 11 digits">ℹ️</span>
          </label>
          <input type="text" name="mobile_no" id="edit_mobile_no" maxlength="11" placeholder="e.g., 09123456789" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
        </div>
      </div>
      
      <!-- Row 5: Password & Reset to Default Password -->
      <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Password</label>
          <div style="position: relative;">
            <input type="password" name="password" id="edit_password" autocomplete="off" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="edit_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
          </div>
        </div>
        <div style="flex: 1;">
          <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">&nbsp;</label>
          <button type="button" id="reset_default_password_btn" style="height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase; width: 100%; padding: 10px 20px; box-sizing: border-box;">Reset to Default</button>
        </div>
      </div>
      
      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="closeEditUserModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
        <button type="submit" id="edit_update_btn" style="width: 125px; height: 50px; background-color: #007bff; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">UPDATE</button>
      </div>
    </form>
  </div>
</div>

                            <!-- Success Modal for User Edit -->
            <div id="editUserSuccessModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
              <div class="modal-box" style="max-width: 400px; min-height: 280px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <div class="modal-header" style="text-align: center; margin-bottom: 20px;">
                      <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 80px; height: 80px; margin: 0 auto 0; display: block;">
                      <div style="height: 40px;"></div>
                      <h2 style="color: #28a745; margin-bottom: 20px; display: block; width: 100%;">Success!</h2>
                    </div>
                    <div class="modal-body" style="text-align: center; margin-bottom: 20px;">
                      <p id="editUserSuccessMessage" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 30px;">
                        User account updated successfully!
                      </p>
                    </div>
                    <div class="modal-actions" style="text-align: center;">
                      <button type="button" class="create-btn" onclick="closeEditUserSuccessModal()" style="min-width: 120px;">
                        OK
                      </button>
                    </div>
                  </div>
                </div>

                            <!-- Error Modal for User Edit -->
            <div id="editUserErrorModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.8); z-index: 9999;">
              <div class="modal-box" style="max-width: 400px; min-height: 280px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    <div class="modal-header" style="text-align: center; margin-bottom: 20px;">
                      <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 80px; height: 80px; margin: 0 auto 0; display: block;">
                      <div style="height: 40px;"></div>
                      <h2 id="editUserErrorHeading" style="color: #dc3545; margin-bottom: 20px; display: block; width: 100%;">Error!</h2>
                    </div>
                    <div class="modal-body" style="text-align: center; margin-bottom: 20px;">
                      <p id="editUserErrorMessage" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 30px;">
                        An error occurred while updating the user account.
                      </p>
                    </div>
                    <div class="modal-actions" style="text-align: center;">
                      <button type="button" class="cancel-btn" onclick="closeEditUserErrorModal()" style="min-width: 120px;">
                        TRY AGAIN
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
</script> 