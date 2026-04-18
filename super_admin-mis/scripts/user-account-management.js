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
function renderTable(users = filteredUsers) {
    const tableBody = document.getElementById('userTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.employee_no || 'N/A'}</td>
            <td>${user.first_name} ${user.last_name}</td>
            <td>${getRoleDisplayName(user.role) || 'N/A'}</td>
            <td>${user.email || 'N/A'}</td>
            <td>
                <button class="edit-btn" data-employee="${user.employee_no}">Edit</button>
                <button class="delete-btn" data-employee="${user.employee_no}">Delete</button>
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
function openDeleteUserModal(employeeNo) {
    console.log('Opening delete for:', employeeNo);
}

// Close delete modal
function closeDeleteUserModal() {
    const modal = document.getElementById('deleteUserModal');
    if (modal) modal.style.display = 'none';
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
    console.log('Confirming delete');
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
