<?php
// super_admin-mis/settings-content/settings.php
?>
<div class="settings-page-container" style="display: flex; gap: 40px; padding: 0 0 40px 0; box-sizing: border-box;">
  <!-- Sidebar Navigation -->
  <aside class="settings-sidebar" style="width: 320px;">
    <h2 class="main-page-title">Settings</h2>
    <div id="accountCard" class="account-card" style="background: #fff; border-radius: 16px; padding: 24px 20px; margin-bottom: 32px; display: flex; align-items: center; gap: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); cursor: pointer; transition: box-shadow 0.2s;">
      <div style="width: 64px; height: 64px; background: #E0E0E0; border-radius: 50%;"></div>
      <div>
        <div style="font-weight: bold; font-size: 1.1rem;">User Name</div>
        <div style="color: #666; font-size: 1rem;">Super Admin Account</div>
      </div>
    </div>
    <nav class="settings-nav" style="display: flex; flex-direction: column; gap: 18px;">
      <button style="height: 56px; border-radius: 14px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: none; margin-bottom: 0; font-size: 1rem; font-family: 'TT Interphases', sans-serif; cursor: pointer;">More Settings Soon</button>
    </nav>
  </aside>

  <!-- Main Content -->
  <main id="settingsMainContent" class="settings-main-content" style="flex: 1; background: #fff; border-radius: 16px; height: calc(100vh - 70px); padding: 30px 20px 20px 20px; box-sizing: border-box; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: flex-start; overflow: auto;">
    <!-- Main content intentionally left empty -->
  </main>
</div>
<script>
  const accountCard = document.getElementById('accountCard');
  const mainContent = document.getElementById('settingsMainContent');

  function renderMyAccount() {
    mainContent.innerHTML = `
      <h2 style="font-size: 1.3rem; font-weight: bold; margin: 0 0 24px 0;">My Account</h2>
      <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 48px; margin-bottom: 24px;">
        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 18px; min-width: 160px;">
          <div style="position: relative; width: 160px; height: 160px;">
            <div id="profileBg" style="width: 160px; height: 160px; background: #E0E0E0; display: flex; align-items: center; justify-content: center; position: absolute; top: 0; left: 0; z-index: 1;">
              <img id="fileIconOverlay" src="../src/assets/icons/file-icon.png" alt="Upload Icon" style="width: 80px; height: 80px; opacity: 0.85; cursor: default; pointer-events: none;" />
            </div>
                          <img id="profileImage" src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Profile" style="width: 160px; height: 160px; object-fit: cover; border: 2px solid #E0E0E0; background: transparent; position: absolute; top: 0; left: 0; z-index: 2; display: none;" />
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px; align-items: flex-start; width: 160px;">
            <input type="file" id="profileInput" accept="image/*" style="display: none;" />
          </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: row; justify-content: flex-start; gap: 32px;">
          <div style="display: flex; flex-direction: column; justify-content: flex-start; gap: 18px; min-width: 220px; width: 100%;">
            <div style="display: flex; flex-direction: row; align-items: center; gap: 24px;">
              <div style="flex: 1;">
                <div style="font-size: 1.05rem; font-weight: bold;">User Name</div>
                <div id="userNameValue" style="font-size: 1.05rem;">Sample Name</div>
              </div>
              <div style="width: 160px; display: flex; align-items: center;"></div>
            </div>
            <div style="display: flex; flex-direction: row; align-items: center; gap: 24px;">
              <div style="flex: 1;">
                <div style="font-size: 1.05rem; font-weight: bold;">Email</div>
                <div id="userEmailValue" style="font-size: 1.05rem;">superadmin@email.com</div>
              </div>
              <div style="width: 160px; display: flex; align-items: center;">
                <button style="font-family: 'TT Interphases', sans-serif; padding: 6px 10px; border-radius: 8px; border: none; background: #1976d2; color: #fff; font-size: 1rem; cursor: pointer; font-weight: 600; width: 160px; white-space: nowrap;">Change Password</button>
              </div>
            </div>
            <div style="display: flex; flex-direction: row; align-items: center; gap: 24px;">
              <div style="flex: 1;">
                <div style="font-size: 1.05rem; font-weight: bold;">Account Role</div>
                <div id="userRoleValue" style="font-size: 1.05rem;">Super Admin</div>
              </div>
              <div style="width: 160px; display: flex; align-items: center;">
                <button style="font-family: 'TT Interphases', sans-serif; padding: 6px 28px; border-radius: 8px; border: none; background: #43a047; color: #fff; font-size: 1rem; cursor: pointer; font-weight: 600; width: 160px;">Edit Account</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div style="margin-top: -25px;">
        <h3 style="font-size: 1.1rem; font-weight: bold; margin-bottom: 16px;">My Activity Logs</h3>
        <div id="adminLogsSection" style="background: #f5f5f5; border-radius: 10px; padding: 20px; min-height: 80px; color: #444; font-size: 0.98rem;">
          <!-- Placeholder for logs -->
          <em>No recent activities.</em>
        </div>
      </div>
    `;
  }

  // Make renderMyAccount globally available
  window.renderMyAccount = renderMyAccount;

  // Function to initialize settings page (can be called from global.js)
  window.initializeSettingsPage = function() {
    
    const accountCard = document.getElementById('accountCard');
    const mainContent = document.getElementById('settingsMainContent');
    
    if (accountCard && mainContent) {
      // Render the default content
      renderMyAccount();
      
      // Add event listeners
      accountCard.addEventListener('click', renderMyAccount);
      accountCard.addEventListener('mouseover', function() {
        accountCard.style.boxShadow = '0 4px 16px rgba(0,0,0,0.08)';
      });
      accountCard.addEventListener('mouseout', function() {
        accountCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.03)';
      });
      
    } else {
    }
  };

  // Initialize on page load
  initializeSettingsPage();

  // Render My Account by default on page load
  renderMyAccount();

  accountCard.addEventListener('click', renderMyAccount);
  accountCard.addEventListener('mouseover', function() {
    accountCard.style.boxShadow = '0 4px 16px rgba(0,0,0,0.08)';
  });
  accountCard.addEventListener('mouseout', function() {
    accountCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.03)';
  });

  // Modal logic
  function showModal(id) {
    document.getElementById(id).style.display = 'flex';
    // Disable body scroll
    document.body.style.overflow = 'hidden';
  }
  function hideModal(id) {
    document.getElementById(id).style.display = 'none';
    // Re-enable body scroll
    document.body.style.overflow = '';
  }
  // Edit Account Modal
  document.addEventListener('click', function(e) {
    if (e.target && e.target.textContent === 'Edit Account') {
      showModal('editAccountModal');
    }
    if (e.target && e.target.id === 'cancelEditAccount') {
      hideModal('editAccountModal');
    }
    if (e.target && e.target.textContent === 'Change Password') {
      showModal('changePasswordModal');
    }
    if (e.target && e.target.id === 'cancelChangePassword') {
      hideModal('changePasswordModal');
    }
    // Close modal if clicking outside the modal content
    if (e.target && (e.target.id === 'editAccountModal' || e.target.id === 'changePasswordModal')) {
      hideModal(e.target.id);
    }
  });
  // Password requirements live feedback
  document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'newPassword') {
      const val = e.target.value;
      document.getElementById('pwLetter').style.color = /[a-zA-Z]/.test(val) ? '#43a047' : '#e53935';
      document.getElementById('pwLetter').children[0].textContent = /[a-zA-Z]/.test(val) ? 'Met' : 'Not met';
      document.getElementById('pwNumber').style.color = /[0-9#?!&]/.test(val) ? '#43a047' : '#e53935';
      document.getElementById('pwNumber').children[0].textContent = /[0-9#?!&]/.test(val) ? 'Met' : 'Not met';
      document.getElementById('pwLength').style.color = val.length >= 8 ? '#43a047' : '#e53935';
      document.getElementById('pwLength').children[0].textContent = val.length >= 8 ? 'Met' : 'Not met';
    }
  });
  // Show/hide password toggles
  document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'toggleNewPassword') {
      const input = document.getElementById('newPassword');
      const icon = e.target;
      if (input.type === 'password') {
        input.type = 'text';
        icon.src = '../src/assets/icons/hide_password.png';
        icon.alt = 'Hide';
      } else {
        input.type = 'password';
        icon.src = '../src/assets/icons/show_password.png';
        icon.alt = 'Show';
      }
    }
    if (e.target && e.target.id === 'toggleConfirmPassword') {
      const input = document.getElementById('confirmPassword');
      const icon = e.target;
      if (input.type === 'password') {
        input.type = 'text';
        icon.src = '../src/assets/icons/hide_password.png';
        icon.alt = 'Hide';
      } else {
        input.type = 'password';
        icon.src = '../src/assets/icons/show_password.png';
        icon.alt = 'Show';
      }
    }
  });

  // Save button enable/disable logic for Edit Account
  function checkEditAccountFields() {
    const emp = document.getElementById('editEmployeeNo');
    const name = document.getElementById('editUserName');
    const email = document.getElementById('editEmail');
    const saveBtn = document.querySelector('#editAccountModal .create-btn');
    if (emp && name && email && emp.value.trim() && name.value.trim() && email.value.trim()) {
      saveBtn.disabled = false;
    } else {
      saveBtn.disabled = true;
    }
  }
  document.addEventListener('input', function(e) {
    if (e.target && (e.target.id === 'editEmployeeNo' || e.target.id === 'editUserName' || e.target.id === 'editEmail')) {
      checkEditAccountFields();
    }
  });
  document.addEventListener('DOMContentLoaded', checkEditAccountFields);

  // Save button enable/disable logic for Change Password
  function checkChangePasswordFields() {
    const curr = document.getElementById('currentPassword');
    const newPw = document.getElementById('newPassword');
    const conf = document.getElementById('confirmPassword');
    const saveBtn = document.querySelector('#changePasswordModal .create-btn');
    const pwLetter = /[a-zA-Z]/.test(newPw.value);
    const pwNumber = /[0-9#?!&]/.test(newPw.value);
    const pwLength = newPw.value.length >= 8;
    const pwMatch = newPw.value === conf.value && newPw.value.length > 0;
    if (curr.value && newPw.value && conf.value && pwLetter && pwNumber && pwLength && pwMatch) {
      saveBtn.disabled = false;
    } else {
      saveBtn.disabled = true;
    }
  }
  document.addEventListener('input', function(e) {
    if (e.target && (e.target.id === 'currentPassword' || e.target.id === 'newPassword' || e.target.id === 'confirmPassword')) {
      checkChangePasswordFields();
    }
  });
  document.addEventListener('DOMContentLoaded', checkChangePasswordFields);
</script>

<!-- Edit Account Modal -->
<div id="editAccountModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width: 500px;">
    <div class="modal-header">
      <h3>Edit Account</h3>
      <button type="button" class="close-button" onclick="hideModal('editAccountModal')">&times;</button>
    </div>
    <form id="editAccountForm" autocomplete="off">
      <div class="form-group">
        <label>Employee No. :</label>
        <input type="text" id="editEmployeeNo" />
      </div>
      <div class="form-group">
        <label>User Name :</label>
        <input type="text" id="editUserName" />
      </div>
      <div class="form-group">
        <label>Institutional Email :</label>
        <input type="email" id="editEmail" />
      </div>
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="hideModal('editAccountModal')">Cancel</button>
        <button type="submit" class="create-btn" disabled>Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal-overlay" style="display:none;">
  <div class="modal-box" style="max-width: 500px;">
    <div class="modal-header">
      <h3>Change Password</h3>
      <button type="button" class="close-button" onclick="hideModal('changePasswordModal')">&times;</button>
    </div>
    <form id="changePasswordForm" autocomplete="off">
      <div class="form-group">
        <label>Current Password :</label>
        <input type="password" id="currentPassword" />
      </div>
      <div class="form-group password-group" style="position: relative;">
        <label>New Password :</label>
        <input type="password" id="newPassword" style="padding-right: 40px; padding-left: 12px; height: 50px; border-radius: 12px;" />
        <img id="toggleNewPassword" src="../src/assets/icons/show_password.png" class="toggle-password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; cursor: pointer; z-index: 10; filter: invert(50%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(50%) contrast(100%);" />
      </div>
      <div id="passwordRequirements" style="margin-bottom: 12px; font-size: 0.97rem; padding: 10px; background: #f8f8f8; border-radius: 8px;">
        <div id="pwLetter" style="color: #e53935; margin-bottom: 4px;">1 letter <span style="font-size:0.95em;">Not met</span></div>
        <div id="pwNumber" style="color: #e53935; margin-bottom: 4px;">1 number or special character (example: # ? ! &amp;) <span style="font-size:0.95em;">Not met</span></div>
        <div id="pwLength" style="color: #e53935;">8 characters <span style="font-size:0.95em;">Not met</span></div>
      </div>
      <div class="form-group password-group" style="position: relative;">
        <label>Confirm Password :</label>
        <input type="password" id="confirmPassword" style="padding-right: 40px; padding-left: 12px; height: 50px; border-radius: 12px;" />
        <img id="toggleConfirmPassword" src="../src/assets/icons/show_password.png" class="toggle-password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; cursor: pointer; z-index: 10; filter: invert(50%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(50%) contrast(100%);" />
      </div>
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="hideModal('changePasswordModal')">Cancel</button>
        <button type="submit" class="create-btn" disabled>Save</button>
      </div>
    </form>
  </div>
</div>
