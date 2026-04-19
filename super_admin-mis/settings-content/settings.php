<?php
// super_admin-mis/settings-content/settings.php
// Redesigned for ASCOM premium standards.

global $conn;

// Fetch current user info if possible
$admin_name = $_SESSION['full_name'] ?? 'Super Admin';
$admin_email = $_SESSION['email'] ?? 'admin@ascom.edu.ph';
$admin_role = $_SESSION['role_display'] ?? 'System Administrator';
$admin_emp = $_SESSION['employee_no'] ?? 'EMP-000';
?>

<div class="settings-page-container">
    <!-- Sidebar Navigation -->
    <aside class="settings-sidebar">
        <h2 class="settings-sidebar-title">Settings</h2>
        
        <div id="accountCard" class="account-preview-card active">
            <img src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Profile" class="preview-avatar">
            <div class="preview-info">
                <span class="preview-name"><?php echo htmlspecialchars($admin_name); ?></span>
                <span class="preview-role"><?php echo htmlspecialchars($admin_role); ?></span>
            </div>
        </div>

        <nav class="settings-nav">
            <div class="nav-item active" onclick="renderMyAccount()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                My Account
            </div>
            <div class="nav-item placeholder">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                System Settings
            </div>
            <div class="nav-item placeholder">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Privacy & Security
            </div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main id="settingsMainContent" class="settings-main-content">
        <!-- Dynamically filled by JS -->
    </main>
</div>

<script>
    const adminData = {
        name: '<?php echo addslashes($admin_name); ?>',
        email: '<?php echo addslashes($admin_email); ?>',
        role: '<?php echo addslashes($admin_role); ?>',
        emp: '<?php echo addslashes($admin_emp); ?>'
    };

    function renderMyAccount() {
        const mainContent = document.getElementById('settingsMainContent');
        mainContent.innerHTML = `
            <h3 class="content-section-title">My Account</h3>
            
            <div class="profile-overview">
                <div class="profile-avatar-container">
                    <img src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Profile" class="profile-avatar-large">
                    <div class="avatar-edit-overlay" onclick="document.getElementById('profileInput').click()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                    <input type="file" id="profileInput" accept="image/*" style="display: none;">
                </div>

                <div class="profile-details-grid">
                    <div class="detail-row">
                        <div class="detail-info">
                            <span class="detail-label">Full Name</span>
                            <span class="detail-value">${adminData.name}</span>
                        </div>
                        <div class="detail-action">
                            <button onclick="showModal('editAccountModal')">Edit Info</button>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-info">
                            <span class="detail-label">Institutional Email</span>
                            <span class="detail-value">${adminData.email}</span>
                        </div>
                        <div class="detail-action">
                            <button class="secondary" onclick="showModal('changePasswordModal')">Change Password</button>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-info">
                            <span class="detail-label">Designation / Role</span>
                            <span class="detail-value">${adminData.role}</span>
                        </div>
                        <div class="detail-info" style="text-align: right;">
                             <span class="detail-label">Emp ID</span>
                             <span class="detail-value">${adminData.emp}</span>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="content-section-title">Activity Logs</h3>
            <div class="logs-container">
                <div style="display: flex; align-items: center; justify-content: center; height: 100px; color: rgba(17, 24, 39, 0.4); font-size: 13px; font-weight: 600; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    No recent activities found in your session.
                </div>
            </div>
        `;
    }

    // Modal helpers
    window.showModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // Animation trigger if needed
        }
    }

    window.hideModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Password requirements feedback
    function updatePasswordFeedback(val) {
        const letter = /[a-zA-Z]/.test(val);
        const number = /[0-9#?!&]/.test(val);
        const length = val.length >= 8;

        const letterEl = document.getElementById('pwLetter');
        const numberEl = document.getElementById('pwNumber');
        const lengthEl = document.getElementById('pwLength');

        if (letterEl) letterEl.className = 'pw-requirement' + (letter ? ' met' : '');
        if (numberEl) numberEl.className = 'pw-requirement' + (number ? ' met' : '');
        if (lengthEl) lengthEl.className = 'pw-requirement' + (length ? ' met' : '');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        renderMyAccount();

        // Listen for password input
        document.addEventListener('input', (e) => {
            if (e.target.id === 'newPassword') {
                updatePasswordFeedback(e.target.value);
            }
        });
    });

    // Toggle password visibility logic
    window.togglePassword = function(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.src = '../src/assets/icons/hide_password.png';
        } else {
            input.type = 'password';
            icon.src = '../src/assets/icons/show_password.png';
        }
    }
</script>

<!-- Edit Account Modal -->
<div id="editAccountModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width: 500px; padding: 32px;">
        <div class="modal-header">
            <h3 style="font-family: 'TT Interphases', sans-serif; font-weight: 800; color: #0C4B34;">Edit Account</h3>
            <button type="button" class="close-button" onclick="hideModal('editAccountModal')">&times;</button>
        </div>
        <form id="editAccountForm" autocomplete="off" style="margin-top: 20px;">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: rgba(17,24,39,0.5); margin-bottom: 8px; display: block;">Full Name</label>
                <input type="text" id="editUserName" value="<?php echo htmlspecialchars($admin_name); ?>" 
                       style="width: 100%; height: 50px; border-radius: 12px; border: 1px solid rgba(12,75,52,0.15); padding: 0 16px; font-size: 14px; outline: none; transition: border-color 0.2s;">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 700; color: rgba(17,24,39,0.5); margin-bottom: 8px; display: block;">Institutional Email</label>
                <input type="email" id="editEmail" value="<?php echo htmlspecialchars($admin_email); ?>"
                       style="width: 100%; height: 50px; border-radius: 12px; border: 1px solid rgba(12,75,52,0.15); padding: 0 16px; font-size: 14px; outline: none;">
            </div>
            <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid rgba(12,75,52,0.08); padding-top: 24px;">
                <button type="button" class="cancel-btn" onclick="hideModal('editAccountModal')" 
                        style="background: rgba(17,24,39,0.05); color: #333; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancel</button>
                <button type="submit" class="create-btn" 
                        style="background: #0C4B34; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width: 500px; padding: 32px;">
        <div class="modal-header">
            <h3 style="font-family: 'TT Interphases', sans-serif; font-weight: 800; color: #0C4B34;">Change Password</h3>
            <button type="button" class="close-button" onclick="hideModal('changePasswordModal')">&times;</button>
        </div>
        <form id="changePasswordForm" autocomplete="off" style="margin-top: 20px;">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 700; color: rgba(17,24,39,0.5); margin-bottom: 8px; display: block;">Current Password</label>
                <input type="password" id="currentPassword" placeholder="Enter current password"
                       style="width: 100%; height: 50px; border-radius: 12px; border: 1px solid rgba(12,75,52,0.15); padding: 0 16px; font-size: 14px; outline: none;">
            </div>
            
            <div class="form-group" style="margin-bottom: 12px; position: relative;">
                <label style="font-size: 13px; font-weight: 700; color: rgba(17,24,39,0.5); margin-bottom: 8px; display: block;">New Password</label>
                <input type="password" id="newPassword" placeholder="Create strong password"
                       style="width: 100%; height: 50px; border-radius: 12px; border: 1px solid rgba(12,75,52,0.15); padding: 0 44px 0 16px; font-size: 14px; outline: none;">
                <img id="toggleNewIcon" src="../src/assets/icons/show_password.png" onclick="togglePassword('newPassword', 'toggleNewIcon')" 
                     style="position: absolute; right: 14px; top: 38px; width: 20px; cursor: pointer; opacity: 0.5;">
            </div>

            <div id="passwordRequirements" style="margin-bottom: 24px; padding: 12px; background: rgba(12,75,52,0.03); border-radius: 12px;">
                <div id="pwLetter" class="pw-requirement"><span class="pw-requirement-dot"></span> 1 letter (At least one uppercase or lowercase)</div>
                <div id="pwNumber" class="pw-requirement"><span class="pw-requirement-dot"></span> 1 number or special character (#?!&)</div>
                <div id="pwLength" class="pw-requirement"><span class="pw-requirement-dot"></span> Minimum 8 characters</div>
            </div>

            <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid rgba(12,75,52,0.08); padding-top: 24px;">
                <button type="button" class="cancel-btn" onclick="hideModal('changePasswordModal')"
                        style="background: rgba(17,24,39,0.05); color: #333; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">Cancel</button>
                <button type="submit" class="create-btn"
                        style="background: #0C4B34; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">Update Password</button>
            </div>
        </form>
    </div>
</div>
