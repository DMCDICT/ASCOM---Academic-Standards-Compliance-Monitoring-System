<?php
// switch_role_modal.php
// Modal for switching between user roles (Department Dean)
?>

<div id="switchRoleModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 500px;">
    <div class="modal-header">
      <h2>Switch Role</h2>
      <span class="close-button" onclick="closeSwitchRoleModal()">&times;</span>
    </div>
    
    <div class="modal-content">
      <p style="margin-bottom: 20px; color: #666;">Please enter your password to confirm role switch.</p>
      
      <form id="switchRoleForm" class="form-grid">
        <div class="form-group">
          <label for="switch_role_password">Password</label>
          <div class="password-input-wrapper" style="position: relative;">
            <input type="password" name="password" id="switch_role_password" required style="padding-right: 45px;" oninput="validatePassword()">
            <style>
              #switch_role_password::-webkit-credentials-auto-fill-button,
              #switch_role_password::-webkit-contacts-auto-fill-button,
              #switch_role_password::-webkit-strong-password-auto-fill-button {
                display: none !important;
              }
              #switch_role_password::-ms-reveal,
              #switch_role_password::-ms-clear {
                display: none !important;
              }
            </style>
            <button type="button" class="password-toggle" onclick="togglePassword('switch_role_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 5px;">
              <img src="../src/assets/icons/hide_password.png" alt="Toggle Password" class="password-icon" style="width: 24px; height: 24px; object-fit: contain; filter: brightness(0.3) contrast(1.5); opacity: 0.8;">
            </button>
          </div>
        </div>
        
        <div class="form-group">
          <label for="switch_info">Switch Information</label>
          <div style="background-color: #f5f5f5; padding: 15px; border-radius: 8px; border-left: 4px solid #1976d2;">
            <p style="margin: 0; color: #333; font-weight: 500;" id="switchInfoText">
              You will be switching to: <span id="targetRoleDisplay" style="color: #1976d2; font-weight: bold;">Loading...</span>
            </p>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="button" class="cancel-btn" onclick="closeSwitchRoleModal()">CANCEL</button>
          <button type="submit" class="create-btn" id="confirmSwitchRoleBtn" disabled>CONFIRM</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Incorrect Password Modal -->
<div id="incorrectPasswordModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 400px;">
    <div class="modal-content">
      <div style="text-align: center; padding: 20px;">
        <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 96px; height: 96px; margin-bottom: 20px;">
        <h2 style="color: #333; margin-bottom: 15px;">Incorrect Password</h2>
        <p style="color: #666; margin-bottom: 25px;">The password you entered is incorrect. Please try again.</p>
                 <button type="button" style="background-color: #dc3545; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 700;" onclick="closeIncorrectPasswordModal()">OK</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Fix duplicate modal issue - override global modal-content positioning */
#switchRoleModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
    padding: 25px !important;
    background-color: #EFEFEF !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    width: auto !important;
    max-width: none !important;
}

#switchRoleModal .modal-box {
    position: relative !important;
    background-color: #EFEFEF !important;
    padding: 0 !important;
    border-radius: 15px !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    border: 1px solid #888 !important;
    overflow: hidden !important;
}

#switchRoleModal .modal-header {
    position: relative !important;
    background-color: #EFEFEF !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-bottom: 1px solid #e5e5e5 !important;
    padding: 25px 25px 15px 25px !important;
    margin-bottom: 0 !important;
}

#switchRoleModal .modal-header h2 {
    margin: 0 !important;
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #333 !important;
}

#switchRoleModal .close-button {
    color: #aaa !important;
    font-size: 28px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    line-height: 1 !important;
    padding: 0 !important;
    background: none !important;
    border: none !important;
    width: 30px !important;
    height: 30px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#switchRoleModal .close-button:hover {
    color: #000 !important;
}

#incorrectPasswordModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
    padding: 0 !important;
    background-color: #EFEFEF !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    width: auto !important;
    max-width: none !important;
}

#incorrectPasswordModal .modal-box {
    position: relative !important;
    background-color: #EFEFEF !important;
    padding: 0 !important;
    border-radius: 15px !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    border: 1px solid #888 !important;
    overflow: hidden !important;
}

/* Ensure password toggle button is visible and positioned correctly */
#switchRoleModal .password-input-wrapper {
    position: relative !important;
}

#switchRoleModal .password-input-wrapper .password-toggle {
    position: absolute !important;
    right: 10px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#switchRoleModal .password-input-wrapper .password-toggle img {
    display: block !important;
    visibility: visible !important;
}

</style>

<script>
function openSwitchRoleModal() {
  document.getElementById('switchRoleModal').style.display = 'flex';
  document.getElementById('switchRoleForm').reset();
  document.getElementById('switch_role_password').focus();
  
  // Automatically detect and set the target role
  detectAndSetTargetRole();
  
  // Disable confirm button initially with CSS
  const confirmBtn = document.getElementById('confirmSwitchRoleBtn');
  confirmBtn.disabled = true;
  confirmBtn.setAttribute('disabled', 'disabled');
  confirmBtn.style.opacity = '0.5';
  confirmBtn.style.cursor = 'not-allowed';
  confirmBtn.style.pointerEvents = 'none';
  
  // Force validation after a short delay to ensure button is disabled
  setTimeout(() => {
    validatePassword();
  }, 100);
}

function detectAndSetTargetRole() {
  // Since this is the Department Dean interface, the available role is Teacher
  const targetRole = 'teacher';
  const targetRoleDisplay = 'Teacher';
  
  document.getElementById('targetRoleDisplay').textContent = targetRoleDisplay;
  
  // Store the target role in a hidden input or data attribute
  document.getElementById('switchRoleForm').setAttribute('data-target-role', targetRole);
}

function validatePassword() {
  const password = document.getElementById('switch_role_password').value;
  const confirmBtn = document.getElementById('confirmSwitchRoleBtn');
  
  
  // Enable button only if password has 8 or more characters
  if (password.length >= 8) {
    confirmBtn.disabled = false;
    confirmBtn.removeAttribute('disabled');
    confirmBtn.style.opacity = '1';
    confirmBtn.style.cursor = 'pointer';
    confirmBtn.style.pointerEvents = 'auto';
  } else {
    confirmBtn.disabled = true;
    confirmBtn.setAttribute('disabled', 'disabled');
    confirmBtn.style.opacity = '0.5';
    confirmBtn.style.cursor = 'not-allowed';
    confirmBtn.style.pointerEvents = 'none';
  }
}

function closeSwitchRoleModal() {
  document.getElementById('switchRoleModal').style.display = 'none';
}

function closeIncorrectPasswordModal() {
  document.getElementById('incorrectPasswordModal').style.display = 'none';
}

function showIncorrectPasswordModal() {
  document.getElementById('incorrectPasswordModal').style.display = 'flex';
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const button = input.parentElement.querySelector('.password-toggle');
  const icon = button.querySelector('.password-icon');
  
  if (input.type === 'password') {
    input.type = 'text';
    icon.src = '../src/assets/icons/show_password.png';
    icon.style.filter = 'brightness(0.3) contrast(1.5)';
    icon.style.opacity = '0.8';
  } else {
    input.type = 'password';
    icon.src = '../src/assets/icons/hide_password.png';
    icon.style.filter = 'brightness(0.3) contrast(1.5)';
    icon.style.opacity = '0.8';
  }
}

// Initialize event listeners only once to prevent duplicates
if (!window.switchRoleModalInitialized) {
  window.switchRoleModalInitialized = true;
  
  // Handle form submission
  const switchRoleForm = document.getElementById('switchRoleForm');
  if (switchRoleForm) {
    switchRoleForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const password = document.getElementById('switch_role_password').value;
      const targetRole = document.getElementById('switchRoleForm').getAttribute('data-target-role');
      const confirmBtn = document.getElementById('confirmSwitchRoleBtn');
      
      if (!password) {
        alert('Please enter your password.');
        return;
      }
      
      // Disable button and show loading
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'SWITCHING...';
      
      try {
        const response = await fetch('api/switch_role.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            password: password,
            target_role: targetRole
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Redirect to the appropriate interface
          window.location.href = data.redirect_url;
        } else {
          showIncorrectPasswordModal();
        }
      } catch (error) {
        console.error('Error switching role:', error);
        alert('An error occurred while switching roles. Please try again.');
      } finally {
        // Re-enable button
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'CONFIRM';
      }
    });
  }
  
  // Close modal when clicking outside
  const switchRoleModal = document.getElementById('switchRoleModal');
  if (switchRoleModal) {
    switchRoleModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeSwitchRoleModal();
      }
    });
  }
  
  const incorrectPasswordModal = document.getElementById('incorrectPasswordModal');
  if (incorrectPasswordModal) {
    incorrectPasswordModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeIncorrectPasswordModal();
      }
    });
  }
}
</script>
