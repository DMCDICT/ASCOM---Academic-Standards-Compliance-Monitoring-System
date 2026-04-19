// user-account-management.js - User Account Management Script

// Make functions globally available
window.getRoleDisplayName = getRoleDisplayName;
window.manualRefreshUserList = manualRefreshUserList;
window.refreshUserList = refreshUserList;
window.renderTable = renderTable;
window.filterUsers = filterUsers;
window.openUserDetailsModal = openUserDetailsModal;
window.closeUserDetailsModal = closeUserDetailsModal;
window.openDeleteUserModal = openDeleteUserModal;
window.closeDeleteUserModal = closeDeleteUserModal;
window.editFromDetails = editFromDetails;
window.deleteFromDetails = deleteFromDetails;
window.confirmDeleteUser = confirmDeleteUser;
window.closeDeleteUserSuccessModal = closeDeleteUserSuccessModal;
window.closeDeleteUserErrorModal = closeDeleteUserErrorModal;
window.closeEditUserSuccessModal = closeEditUserSuccessModal;
window.closeEditUserErrorModal = closeEditUserErrorModal;
window.goToPage = goToPage;
window.toggleAutoRefresh = toggleAutoRefresh;
window.checkAutoRefreshStatus = checkAutoRefreshStatus;
window.initializeUserAccountManagement = initializeUserAccountManagement;

// Auto-refresh variables
let autoRefreshInterval = null;
let nextAutoRefreshTime = null;
let isPageVisible = true;
let currentDeleteEmployeeNo = null; // Store employee number for delete

// Role display name helper
function getRoleDisplayName(roleName) {
    const roleMap = {
        '1': 'Super Admin',
        '2': 'Dean',
        '3': 'Teacher',
        '4': 'QA'
    };
    return roleMap[roleName] || roleName;
}

// Manual refresh function
function manualRefreshUserList() {
    // Reset auto-refresh if active
    if (autoRefreshInterval) {
        stopAutoRefresh();
    }
    refreshUserList();
}

// Refresh user list from server
function refreshUserList() {
    // This function triggers a reload of the user data
    if (typeof window.loadInitialData === 'function') {
        window.loadInitialData();
    }
    console.log('Refreshing user list...');
}

// Render table with user data
function renderTable(users) {
    const tableBody = document.getElementById('userTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No users found.</td></tr>';
        return;
    }
    
    users.forEach(user => {
        // Ensure properties exist
        const fullName = user.full_name || (user.first_name + ' ' + (user.last_name || ''));
        const role = user.role_display || getRoleDisplayName(user.role) || 'User';
        const dept = user.department_code || user.dept || '-';
        const status = (user.is_active == 1 || user.status === 'Active') ? 'Active' : 'Inactive';
        const statusClass = status.toLowerCase();
        const email = user.display_email || user.email || 'N/A';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.employee_no || 'N/A'}</td>
            <td>${fullName}</td>
            <td>${email}</td>
            <td>${role}</td>
            <td>${dept}</td>
            <td>
                <div class="status-pill">
                    <span class="status-dot ${statusClass}"></span>
                    ${status}
                </div>
            </td>
            <td>
                <div class="action-btn-group">
                    <button class="table-edit-btn" onclick="window.openEditUserModal('${user.employee_no}')">Edit</button>
                    <button class="table-delete-btn" onclick="window.openDeleteUserModal('${user.employee_no}', '${fullName.replace(/'/g, "\\'")}', '${email.replace(/'/g, "\\'")}', '${role}')">Delete</button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Filter users by search query
function filterUsers(query) {
    // Implementation depends on your needs
    console.log('Filtering users:', query);
}

// Open user details modal
function openUserDetailsModal(employeeNo) {
    console.log('Opening details for:', employeeNo);
}

// Close user details modal
function closeUserDetailsModal() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) modal.style.display = 'none';
}

// Open delete user modal
function openDeleteUserModal(employeeNo, userName = '', userEmail = '', userRole = '') {
    console.log('>>> openDeleteUserModal called with:', employeeNo, userName);
    console.log('deleteUserModal exists:', !!document.getElementById('deleteUserModal'));
    
    // Store employee number for confirmDeleteUser
    currentDeleteEmployeeNo = employeeNo;
    
    // Get user details from the table or data
    const modal = document.getElementById('deleteUserModal');
    if (!modal) {
        console.error('Delete modal not found in DOM');
        alert('Delete modal not found. Please refresh the page.');
        return;
    }
    
    console.log('Modal found, showing...');
    
    // Set fallback display texts if empty
    if (!userName || userName === 'N/A') userName = employeeNo;
    if (!userEmail) userEmail = 'N/A';
    if (!userRole) userRole = 'N/A';
    
    // Update modal content
    const nameEl = document.getElementById('deleteUserName');
    const emailEl = document.getElementById('deleteUserEmail');
    const roleEl = document.getElementById('deleteUserRole');
    const messageEl = document.getElementById('deleteUserMessage');
    
    if (nameEl) nameEl.textContent = 'Name: ' + userName;
    if (emailEl) emailEl.textContent = 'Email: ' + userEmail;
    if (roleEl) roleEl.textContent = 'Role: ' + userRole;
    if (messageEl) messageEl.textContent = 'Are you sure you want to delete user "' + userName + '"? This action cannot be undone.';

    
    // Show modal
    console.log('Setting modal display to flex');
    modal.style.display = 'flex';
    console.log('Modal display is now:', modal.style.display);
    document.body.style.overflow = 'hidden';
    console.log('Done - modal should be visible');
}

// Close delete modal
function closeDeleteUserModal() {
    const modal = document.getElementById('deleteUserModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    currentDeleteEmployeeNo = null;
}

// Edit from details
function editFromDetails() {
    console.log('Edit from details');
}

// Delete from details
function deleteFromDetails() {
    console.log('Delete from details');
}

// Confirm delete user
function confirmDeleteUser() {
    console.log('>>> confirmDeleteUser called');
    console.log('currentDeleteEmployeeNo:', currentDeleteEmployeeNo);
    
    if (!currentDeleteEmployeeNo) {
        console.error('No employee selected for deletion');
        alert('No employee selected for deletion');
        return;
    }
    
    console.log('Confirming delete for:', currentDeleteEmployeeNo);
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';
    }
    
    // Call delete API with JSON
    fetch('./process_delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ employee_no: currentDeleteEmployeeNo })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Close delete modal
            closeDeleteUserModal();
            
            // Show success modal
            const successModal = document.getElementById('deleteUserSuccessModal');
            const successMessage = document.getElementById('deleteUserSuccessMessage');
            if (successMessage) {
                successMessage.textContent = data.message || 'User deleted successfully!';
            }
            if (successModal) {
                successModal.style.display = 'flex';
            }
            
            // Refresh user list
            refreshUserList();
        } else {
            // Show error modal
            const errorModal = document.getElementById('deleteUserErrorModal');
            const errorMessage = document.getElementById('deleteUserErrorMessage');
            if (errorMessage) {
                errorMessage.textContent = data.message || 'Failed to delete user';
            }
            if (errorModal) {
                errorModal.style.display = 'flex';
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        // Show error modal
        const errorModal = document.getElementById('deleteUserErrorModal');
        const errorMessage = document.getElementById('deleteUserErrorMessage');
        if (errorMessage) {
            errorMessage.textContent = 'Network error. Please try again.';
        }
        if (errorModal) {
            errorModal.style.display = 'flex';
        }
    })
    .finally(function() {
        // Reset button state
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'DELETE';
        }
    });
}

// Close delete success modal
function closeDeleteUserSuccessModal() {
    const modal = document.getElementById('deleteUserSuccessModal');
    if (modal) modal.style.display = 'none';
}

// Close delete error modal
function closeDeleteUserErrorModal() {
    const modal = document.getElementById('deleteUserErrorModal');
    if (modal) modal.style.display = 'none';
}

// Close edit success modal
function closeEditUserSuccessModal() {
    const modal = document.getElementById('editUserSuccessModal');
    if (modal) modal.style.display = 'none';
}

// Close edit error modal
function closeEditUserErrorModal() {
    const modal = document.getElementById('editUserErrorModal');
    if (modal) modal.style.display = 'none';
}

// Go to page
function goToPage(page) {
    console.log('Going to page:', page);
}

// Toggle auto-refresh
function toggleAutoRefresh() {
    if (autoRefreshInterval) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
}

// Start auto-refresh
function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        refreshUserList();
    }, 30000);
    nextAutoRefreshTime = new Date(Date.now() + 30000);
}

// Stop auto-refresh
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        nextAutoRefreshTime = null;
    }
}

// Check auto-refresh status
function checkAutoRefreshStatus() {
    return !!autoRefreshInterval;
}

// Initialize user account management
function initializeUserAccountManagement() {
    console.log('Initializing user account management');
}
