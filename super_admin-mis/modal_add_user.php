<?php
// modal_add_user.php
// This file is an HTML fragment, included by content.php.
// Activate PHP Data Fetching for departments and roles
if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
    if (!isset($conn)) {
        require_once __DIR__ . '/includes/db_connection.php';
    }
    if (!isset($conn) || $conn->connect_error) {
        $departments = [];
        $roles = [];
    }
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
    $rolesQuery = "SELECT id, role FROM roles ORDER BY id ASC";
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

<!-- Teacher Account Creation Modal -->
<!-- This modal is now handled by content.php - using createCompleteModal() function -->
<!-- The modal is created dynamically with JavaScript instead of static HTML -->

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
</script>

