/*
 * user-account-management.js
 * Contains JavaScript specific to the user-account-management.php content fragment.
 * Path to assets: From super_admin-mis/scripts/ to src/assets/
 */

// Global variables
let usersData = [];
let filteredUsers = [];
let userManagementCurrentPage = 1;
let rowsPerPage = 10;
let isPageReady = false; // Add flag to prevent premature modal opening

// Function to map role names to display names
function getRoleDisplayName(roleName) {
    const roleMap = {
        'department_dean': 'Department Dean',
        'teacher': 'Teacher',
        'librarian': 'Librarian',
        'quality_assurance': 'Quality Assurance',
        'admin_qa': 'Quality Assurance',
        'super_admin': 'Super Administrator'
    };
    return roleMap[roleName] || roleName;
}

// Global function declarations - these need to be available immediately
window.manualRefreshUserList = function() {
    
    // Show refresh status icon
    const refreshStatusIcon = document.getElementById('refreshStatusIcon');
    if (refreshStatusIcon) {
        refreshStatusIcon.style.display = 'inline';
        refreshStatusIcon.classList.add('show');
    }
    
    // Show auto-refresh indicator during manual refresh
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    const countdownTimer = document.getElementById('countdownTimer');
    
    if (autoRefreshIndicator) {
        autoRefreshIndicator.style.display = 'inline';
        autoRefreshIndicator.classList.add('active');
    }
    
    // Hide countdown timer during manual refresh
    if (countdownTimer) {
        countdownTimer.style.display = 'none';
        countdownTimer.classList.remove('show');
    }
    
    // Call the refresh function
    window.refreshUserList();
    
    // Don't stop auto-refresh, just let it continue
};

window.refreshUserList = function() {
            
            // Add cache-busting parameter
            const timestamp = new Date().getTime();
            const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        usersData = data.users;
                        filteredUsers = usersData.slice();
                window.renderTable();
                
                // Hide refresh status icon after a short delay
                const refreshStatusIcon = document.getElementById('refreshStatusIcon');
                if (refreshStatusIcon) {
                    setTimeout(() => {
                        refreshStatusIcon.classList.remove('show');
                        setTimeout(() => {
                            refreshStatusIcon.style.display = 'none';
                        }, 300); // Wait for fade out animation
                    }, 1000);
                }
                
                // Hide auto-refresh indicator after refresh completes
                const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
                if (autoRefreshIndicator) {
                    autoRefreshIndicator.style.display = 'none';
                    autoRefreshIndicator.classList.remove('active');
                }
                
                // Update tooltip to show countdown
                updateRefreshButtonTooltip();
            } else {
                // Silent fail - just update UI
                const refreshStatusIcon = document.getElementById('refreshStatusIcon');
                if (refreshStatusIcon) {
                    refreshStatusIcon.classList.remove('show');
                            setTimeout(() => {
                        refreshStatusIcon.style.display = 'none';
                    }, 300);
                }
                
                const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
                if (autoRefreshIndicator) {
                    autoRefreshIndicator.style.display = 'none';
                    autoRefreshIndicator.classList.remove('active');
                }
                
                updateRefreshButtonTooltip();
                    }
                })
                .catch(error => {
            // Silent fail
            const refreshStatusIcon = document.getElementById('refreshStatusIcon');
            if (refreshStatusIcon) {
                refreshStatusIcon.classList.remove('show');
                setTimeout(() => {
                    refreshStatusIcon.style.display = 'none';
                }, 300);
            }
            
            const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
                autoRefreshIndicator.classList.remove('active');
            }
            
            updateRefreshButtonTooltip();
        });
};

window.renderTable = function(users = filteredUsers) {
    const userTableBody = document.getElementById("userTableBody");
    const paginationControls = document.getElementById("paginationControls");
    
    if (!userTableBody) {
        return;
    }
    
    
            userTableBody.innerHTML = '';
            const start = (userManagementCurrentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedUsers = users.slice(start, end);

            if (paginatedUsers.length === 0 && users.length > 0 && userManagementCurrentPage > 1) {
                userManagementCurrentPage = Math.max(1, Math.ceil(users.length / rowsPerPage));
        window.renderTable(users);
                return;
            }
    
            if (paginatedUsers.length === 0 && users.length === 0) {
                const noUsersRow = document.createElement("tr");
                noUsersRow.innerHTML = `<td colspan="8" style="text-align: center;">No users found.</td>`;
                userTableBody.appendChild(noUsersRow);
            } else {
                paginatedUsers.forEach(user => {
                    const row = document.createElement("tr");
                    row.className = "clickable";
                    row.setAttribute("data-employee-no", user.employee_no);
                    
                    // Get Discord-style status for table
                    const statusInfo = getDiscordStyleStatus(user);
                    
                    // Map role names to display names for multiple roles
                    const roleDisplayNames = user.roles ? user.roles.map(role => getRoleDisplayName(role)) : [getRoleDisplayName(user.role_name)];
                    const rolesDisplay = roleDisplayNames.join(', ');
                    
                    row.innerHTML = `
                        <td>${user.employee_no}</td>
                        <td>${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}</td>
                        <td>${user.institutional_email}</td>
                        <td>${user.mobile_no}</td>
                        <td>${rolesDisplay}</td>
                        <td>${user.department_name}</td>
                        <td>
                            <div class="discord-status">
                                <span class="status-dot ${statusInfo.class}"></span>
                                <span class="status-text">${statusInfo.text}</span>
                            </div>
                        </td> 
                        <td class="action-cell">
                            <button class="action-btn edit-btn" data-employee-no="${user.employee_no}">Edit</button>
                            <button class="action-btn delete-btn" data-employee-no="${user.employee_no}">Delete</button>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                    
                    // Add row click handler
                    row.addEventListener('click', function(event) {
                        // Only open modal if clicking on the row itself, not action buttons
                        if (!event.target.closest('.action-cell')) {
                            const employeeNo = this.dataset.employeeNo;
                            
                            // Only open modal if we have a valid employee number
                            if (employeeNo && employeeNo !== 'undefined' && employeeNo !== 'null') {
                                window.openUserDetailsModal(employeeNo);
                            } else {
                                // Invalid - ignore silently
                            }
                        }
                    });
                    
                    // Add click handlers for the action buttons
                    row.querySelector('.edit-btn').addEventListener('click', function(event) {
                        event.stopPropagation();
                        // Edit User Modal functions are now handled by modal-edit-user.js
                        if (typeof window.openEditUserModal === 'function') {
                            window.openEditUserModal(this.dataset.employeeNo);
                        } else {
                            // Function not available - fallback handles it
                        }
                    });
                    row.querySelector('.delete-btn').addEventListener('click', function(event) {
                        event.stopPropagation();
                window.openDeleteUserModal(this.dataset.employeeNo);
                    });
                });
            }
    
    // Render pagination controls
            renderPaginationControls(users);
};

window.filterUsers = function(query) {
    
    if (!query.trim()) {
        filteredUsers = usersData.slice();
    } else {
        const searchTerm = query.toLowerCase();
        
        filteredUsers = usersData.filter(user => {
            // Debug logging for status search
            if (searchTerm === 'active' || searchTerm === 'inactive') {
            }
            
            // Special handling for active/inactive search
            if (searchTerm === 'active' || searchTerm === 'inactive') {
                // Convert to number and check if it's 1 (active) or 0 (inactive)
                // Handle both string and number types
                let isActive = false;
                
                // More robust checking for is_active
                if (user.is_active === null || user.is_active === undefined) {
                    isActive = false;
                } else if (typeof user.is_active === 'string') {
                    isActive = user.is_active === '1' || user.is_active === 'true' || user.is_active === 'TRUE';
                } else if (typeof user.is_active === 'number') {
                    isActive = user.is_active === 1;
                } else if (typeof user.is_active === 'boolean') {
                    isActive = user.is_active === true;
                } else {
                    // Fallback: try to parse as integer
                    const parsed = parseInt(user.is_active);
                    isActive = !isNaN(parsed) && parsed === 1;
                }
                
                
                if (searchTerm === 'active') {
                    // Only return true if user is active
                    const result = isActive;
                    return result;
                } else if (searchTerm === 'inactive') {
                    // Only return true if user is inactive
                    const result = !isActive;
                    return result;
                }
            }
            
            // Check if user matches search term for other searches
            const roleDisplayNames = user.roles ? user.roles.map(role => getRoleDisplayName(role)) : [getRoleDisplayName(user.role_name)];
            const rolesDisplay = roleDisplayNames.join(', ');
            const statusInfo = getDiscordStyleStatus(user);
            const statusText = statusInfo.text.toLowerCase();
            
            const matches = user.employee_no.toLowerCase().includes(searchTerm) ||
                user.first_name.toLowerCase().includes(searchTerm) ||
                user.last_name.toLowerCase().includes(searchTerm) ||
                user.institutional_email.toLowerCase().includes(searchTerm) ||
                user.role_name.toLowerCase().includes(searchTerm) ||
                rolesDisplay.toLowerCase().includes(searchTerm) ||
                (user.department_name && user.department_name.toLowerCase().includes(searchTerm)) ||
                (user.department_code && user.department_code.toLowerCase().includes(searchTerm)) ||
                statusText.includes(searchTerm);
            
            if (matches) {
            }
            
            return matches;
        });
    }
    userManagementCurrentPage = 1;
    window.renderTable();
};

// Modal functions
window.openUserDetailsModal = function(employeeNo) {
    
    // STRICT CHECK - Only allow if employee number is valid
    if (!employeeNo || employeeNo === 'undefined' || employeeNo === 'null' || employeeNo === '') {
        return;
    }
    
    // Prevent opening if page isn't ready
    if (!isPageReady) {
        return;
    }
    
    // Validate input
    if (!employeeNo) {
        return;
    }
    
    // Check if usersData is loaded
    if (!usersData || usersData.length === 0) {
        alert('User data not loaded. Please refresh the page.');
        return;
    }
    
    // Find the user data
    const user = usersData.find(u => u.employee_no === employeeNo);
    if (!user) {
        alert('User not found. Please refresh the page.');
        return;
    }
    
    
    // Populate the modal content
    const userDetailsContent = document.getElementById('userDetailsContent');
    if (userDetailsContent) {
        const statusInfo = getDiscordStyleStatus(user);
        const lastActivity = user.last_activity ? new Date(user.last_activity).toLocaleString() : 'Never';
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleString() : 'Never';
        const lastLogout = user.last_logout ? new Date(user.last_logout).toLocaleString() : 'Never';
        
        userDetailsContent.innerHTML = `
            <div style="display: flex; flex-direction: column; gap: 15px; max-width: 400px; margin: 0 auto;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 16px; font-weight: 600;">Personal Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Employee No.</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${user.employee_no}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Full Name</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Email</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${user.institutional_email}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Mobile</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${user.mobile_no || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 16px; font-weight: 600;">Account Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Role</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${user.roles ? user.roles.map(role => getRoleDisplayName(role)).join(', ') : getRoleDisplayName(user.role_name)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Department</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px; line-height: 1.4; text-align: right; max-width: 60%; word-wrap: break-word;">${user.department_name || 'N/A'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Status</span>
                            <span style="display: flex; align-items: center; gap: 5px; font-size: 14px;">
                                <span class="status-dot ${statusInfo.class}" style="width: 6px; height: 6px; border-radius: 50%; display: inline-block;"></span>
                                ${statusInfo.text}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 16px; font-weight: 600;">Activity Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Last Activity</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${lastActivity}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Last Login</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${lastLogin}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 500; color: #666; font-size: 14px;">Last Logout</span>
                            <span style="font-weight: 600; color: #333; font-size: 14px;">${lastLogout}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        return;
    }
    
    // Show the modal
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        document.body.style.overflow = 'hidden';
        
        document.getElementById('editFromDetailsBtn').setAttribute('data-employee-no', employeeNo);
        document.getElementById('deleteFromDetailsBtn').setAttribute('data-employee-no', employeeNo);
    }
};

window.closeUserDetailsModal = function() {
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        // Use class-based modal hide
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        document.body.style.overflow = 'auto'; // Re-enable body scroll
    }
};

// Edit User Modal functions are now handled by modal-edit-user.js
// This prevents conflicts and ensures consistent behavior

// Edit User Modal functions are now handled by modal-edit-user.js
// This prevents conflicts and ensures consistent behavior

window.openDeleteUserModal = function(employeeNo) {
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Find the user data
    const user = usersData.find(u => u.employee_no === employeeNo);
    if (!user) {
        return;
    }
    
    // Populate the modal content
    document.getElementById('deleteUserName').textContent = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
    document.getElementById('deleteUserEmail').textContent = user.institutional_email;
    document.getElementById('deleteUserRole').textContent = user.role_name;
    
    // Store the employee number for confirmation
    document.getElementById('confirmDeleteBtn').setAttribute('data-employee-no', employeeNo);
    
    // Show the modal
    const modal = document.getElementById('deleteUserModal');
    if (modal) {
        modal.style.display = 'flex';
    }
};

window.closeDeleteUserModal = function() {
    const modal = document.getElementById('deleteUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
    // Re-enable body scroll
    document.body.style.overflow = '';
};

// Additional modal functions
window.editFromDetails = function() {
    const employeeNo = document.getElementById('editFromDetailsBtn').getAttribute('data-employee-no');
    window.closeUserDetailsModal();
    window.openEditUserModal(employeeNo);
};

window.deleteFromDetails = function() {
    const employeeNo = document.getElementById('deleteFromDetailsBtn').getAttribute('data-employee-no');
    window.closeUserDetailsModal();
    window.openDeleteUserModal(employeeNo);
};

window.confirmDeleteUser = function() {
    const employeeNo = document.getElementById('confirmDeleteBtn').getAttribute('data-employee-no');
    
    // Send delete request
    fetch('./process_delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employee_no: employeeNo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            document.getElementById('deleteUserSuccessMessage').textContent = data.message;
            document.getElementById('deleteUserModal').style.display = 'none';
            document.getElementById('deleteUserSuccessModal').style.display = 'flex';
            
            // Disable body scroll for success modal
            document.body.style.overflow = 'hidden';
            
            // Refresh the user list
            window.refreshUserList();
        } else {
            // Show error modal
            document.getElementById('deleteUserErrorHeading').textContent = 'Delete Failed';
            document.getElementById('deleteUserErrorMessage').textContent = data.message;
            document.getElementById('deleteUserModal').style.display = 'none';
            document.getElementById('deleteUserErrorModal').style.display = 'flex';
            
            // Disable body scroll for error modal
            document.body.style.overflow = 'hidden';
        }
    })
    .catch(error => {
        document.getElementById('deleteUserErrorHeading').textContent = 'Network Error';
        document.getElementById('deleteUserErrorMessage').textContent = 'Failed to delete user. Please try again.';
        document.getElementById('deleteUserModal').style.display = 'none';
        document.getElementById('deleteUserErrorModal').style.display = 'flex';
        
        // Disable body scroll for error modal
        document.body.style.overflow = 'hidden';
    });
};

// Success/Error modal functions
window.closeDeleteUserSuccessModal = function() {
    document.getElementById('deleteUserSuccessModal').style.display = 'none';
    
    // Re-enable body scroll
    document.body.style.overflow = '';
};

window.closeDeleteUserErrorModal = function() {
    document.getElementById('deleteUserErrorModal').style.display = 'none';
    
    // Re-enable body scroll
    document.body.style.overflow = '';
};

window.closeEditUserSuccessModal = function() {
    document.getElementById('editUserSuccessModal').style.display = 'none';
    
    // Re-enable body scroll
    document.body.style.overflow = '';
};

window.closeEditUserErrorModal = function() {
    document.getElementById('editUserErrorModal').style.display = 'none';
    
    // Re-enable body scroll
    document.body.style.overflow = '';
};

// Edit user form submission handler
window.addEventListener('DOMContentLoaded', function() {
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Send update request
            fetch('./process_edit_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    document.getElementById('editUserSuccessMessage').textContent = data.message;
                    document.getElementById('editUserModal').style.display = 'none';
                    document.getElementById('editUserSuccessModal').style.display = 'flex';
                    
                    // Disable body scroll for success modal
                    document.body.style.overflow = 'hidden';
                    
                    // Refresh the user list
                    window.refreshUserList();
                } else {
                    // Show error modal
                    document.getElementById('editUserErrorHeading').textContent = 'Update Failed';
                    document.getElementById('editUserErrorMessage').textContent = data.message;
                    document.getElementById('editUserModal').style.display = 'none';
                    document.getElementById('editUserErrorModal').style.display = 'flex';
                    
                    // Disable body scroll for error modal
                    document.body.style.overflow = 'hidden';
                }
            })
            .catch(error => {
                document.getElementById('editUserErrorHeading').textContent = 'Network Error';
                document.getElementById('editUserErrorMessage').textContent = 'Failed to update user. Please try again.';
                document.getElementById('editUserModal').style.display = 'none';
                document.getElementById('editUserErrorModal').style.display = 'flex';
                
                // Disable body scroll for error modal
                document.body.style.overflow = 'hidden';
            });
        });
    }
    
    // Handle role change to show/hide department field
    const editRoleSelect = document.getElementById('edit_role_id');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function() {
            const departmentField = document.getElementById('edit_department_field_group');
            if (departmentField) {
                if (this.value == 2 || this.value == 3) { // Dean or Teacher
                    departmentField.style.display = 'block';
                } else {
                    departmentField.style.display = 'none';
                }
            }
        });
    }
});

// Helper functions
function getDiscordStyleStatus(userData) {
    const now = new Date();
    let lastActivity = null;
    
    if (userData.last_activity) {
        lastActivity = new Date(userData.last_activity);
    }
    
    let statusClass = 'status-inactive';
    let statusText = 'Inactive';
    
    // Check if user has been active within 30 days
    if (lastActivity) {
        const daysSinceActivity = (now - lastActivity) / (1000 * 60 * 60 * 24);
        
        if (daysSinceActivity <= 30) {
            statusClass = 'status-active';
            statusText = 'Active';
        } else {
            statusClass = 'status-inactive';
            statusText = 'Inactive';
        }
    } else {
        // If no last_activity recorded, check if user is currently online
        if (userData.online_status === 'online') {
            statusClass = 'status-active';
            statusText = 'Active';
        } else {
            statusClass = 'status-inactive';
            statusText = 'Inactive';
        }
    }
    
    return {
        class: statusClass,
        text: statusText
    };
}

                function renderPaginationControls(users = filteredUsers) {
    const paginationControls = document.getElementById("paginationControls");   
    if (!paginationControls) return;

            const totalPages = Math.ceil(users.length / rowsPerPage);

    if (totalPages <= 1) {
        paginationControls.innerHTML = '';
        return;
    }

    let paginationHTML = '';

    // Previous buttons
    if (userManagementCurrentPage > 1) {
        paginationHTML += `<button onclick="goToPage(${userManagementCurrentPage - 1})">Previous</button>`;
    }

    // Page numbers
    const startPage = Math.max(1, userManagementCurrentPage - 2);
    const endPage = Math.min(totalPages, userManagementCurrentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === userManagementCurrentPage) {
            paginationHTML += `<button class="active">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="goToPage(${i})">${i}</button>`; 
        }
    }

    // Next buttons
    if (userManagementCurrentPage < totalPages) {
        paginationHTML += `<button onclick="goToPage(${userManagementCurrentPage + 1})">Next</button>`;
    }

    paginationControls.innerHTML = paginationHTML;
}

window.goToPage = function(page) {
    userManagementCurrentPage = page;
    window.renderTable();
};

// Initialize when DOM is loaded
window.addEventListener('DOMContentLoaded', () => {
    // This script will only run its DOMContentLoaded block if user-account-management.php is the active page.
    if (window.location.search.includes('page=user-account-management')) {
        
        // Immediately hide the modal on page load
        const userDetailsModal = document.getElementById('userDetailsModal');
        if (userDetailsModal) {
            userDetailsModal.style.display = 'none';
            userDetailsModal.style.visibility = 'hidden';
            userDetailsModal.classList.remove('show');
        }
        
        const userTableBody = document.getElementById("userTableBody");
        const paginationControls = document.getElementById("paginationControls");
        const searchInput = document.getElementById("userSearchInput");
    const suggestionsPanel = document.getElementById("searchSuggestions");

        // Store current user information for tab close logout
        storeCurrentUserInfo();

        // Load initial data from API
        loadInitialData();

        function loadInitialData() {
    
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
    
    
    fetch(apiUrl)
                .then(response => {
                    return response.json();
                })
        .then(data => {
            if (data.success) {
                usersData = data.users;
                        filteredUsers = usersData.slice();
                
                // Log the first user to see what data we're getting
                if (usersData.length > 0) {
                }
                
                        // Render the table with the loaded data
                        window.renderTable();
                        
                        
                        // Start auto-refresh after initial load
                        startAutoRefresh();
                        
                                // Mark page as ready for modal interactions
        isPageReady = true;
        
        // Ensure user details modal is hidden on page load
        const userDetailsModal = document.getElementById('userDetailsModal');
        if (userDetailsModal) {
            userDetailsModal.classList.remove('show');
            userDetailsModal.style.display = 'none';
            userDetailsModal.style.visibility = 'hidden';
        }
        
        // Set up event listeners
        if (searchInput) {
            // Remove real-time search - only search on button click or Enter key
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const query = this.value.trim();
                    if (query) {
                        window.filterUsers(query);
                    } else {
                        window.filterUsers('');
                    }
                    
                    // Focus the search button for visual feedback
                    const searchButton = document.querySelector('.search-button');
                    if (searchButton) {
                        searchButton.focus();
                    }
                }
            });
            
            // Add input event to show/hide clear button
            searchInput.addEventListener('input', function() {
                const clearBtn = document.getElementById('clearSearchBtn');
                if (clearBtn) {
                    if (this.value.trim() !== '') {
                        clearBtn.style.display = 'flex';
                    } else {
                        clearBtn.style.display = 'none';
                    }
                }
            });
            
            // Add focus event for better UX
            searchInput.addEventListener('focus', function() {
                this.placeholder = 'Type to search...';
            });
            
            // Add blur event to restore placeholder
            searchInput.addEventListener('blur', function() {
                this.placeholder = 'Search Account Here';
            });
        }
        
        // Set up clear button functionality
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        if (clearSearchBtn) {
            // Ensure clear button is hidden initially
            clearSearchBtn.style.display = 'none';
            
            clearSearchBtn.addEventListener('click', function() {
                const searchInput = document.getElementById('userSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                    this.style.display = 'none';
                    window.filterUsers('');
                }
            });
        }
        
        // Set up search button click event
        const searchButton = document.querySelector('.search-button');
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                const query = searchInput ? searchInput.value.trim() : '';
                if (query) {
                    window.filterUsers(query);
                } else {
                    window.filterUsers('');
                }
            });
        }
        
        // Set up auto-refresh indicator click handler
        const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
        if (autoRefreshIndicator) {
            autoRefreshIndicator.addEventListener('click', function() {
                const isActive = window.toggleAutoRefresh();
                if (isActive) {
                    this.classList.remove('inactive');
                    this.classList.add('active');
                    this.title = 'Auto-refresh active (every 30s) - Click to toggle';
            } else {
                    this.classList.add('inactive');
                    this.classList.remove('active');
                    this.title = 'Auto-refresh inactive - Click to enable';
                }
            });
        }
        
    }
});

// Function to store current user information
function storeCurrentUserInfo() {
    
    // Try to get current user from various sources
    const currentUser = getCurrentUserEmployeeNo();
    
    if (currentUser) {
        // Store in both localStorage and sessionStorage for reliability
        localStorage.setItem('current_user_employee_no', currentUser);
        sessionStorage.setItem('current_user_employee_no', currentUser);
        
        // Also store in a data attribute on the body for easy access
        document.body.setAttribute('data-current-user', currentUser);
        
    } else {
    }
}

// Auto-refresh functionality
let autoRefreshInterval = null;
let nextAutoRefreshTime = null;
let tooltipUpdateInterval = null;

function startAutoRefresh() {
    // Clear any existing interval
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    // Set up auto-refresh every 30 seconds
    autoRefreshInterval = setInterval(() => {
        showAutoRefreshIndicator();
        autoRefreshUserList();
    }, 30000); // 30 seconds
    
    // Set next refresh time
    nextAutoRefreshTime = new Date(Date.now() + 30000);
    
    
    // Start updating the tooltip
    updateRefreshButtonTooltip();
    
    // Start tooltip update interval (update every 500ms for smoother real-time counting)
    if (tooltipUpdateInterval) {
        clearInterval(tooltipUpdateInterval);
    }
    tooltipUpdateInterval = setInterval(updateRefreshButtonTooltip, 500);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        nextAutoRefreshTime = null;
        hideAutoRefreshIndicator();
        updateRefreshButtonTooltip();
        
        // Stop tooltip update interval
        if (tooltipUpdateInterval) {
            clearInterval(tooltipUpdateInterval);
            tooltipUpdateInterval = null;
        }
        
    }
}

function autoRefreshUserList() {
    
    // Store current search query before refreshing
    const currentSearchQuery = document.getElementById('userSearchInput') ? document.getElementById('userSearchInput').value.trim() : '';
    
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
    
    fetch(apiUrl)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            usersData = data.users;
            
            // Re-apply the current search filter if there was one
            if (currentSearchQuery) {
                window.filterUsers(currentSearchQuery);
            } else {
                filteredUsers = usersData.slice();
                window.renderTable();
            }
            
            
            // Set next refresh time
            nextAutoRefreshTime = new Date(Date.now() + 30000);
            
            // Hide auto-refresh indicator and update tooltip
            const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
                autoRefreshIndicator.classList.remove('active');
            }
            
            updateRefreshButtonTooltip();
        } else {
            const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
                autoRefreshIndicator.classList.remove('active');
            }
        }
    })
    .catch(error => {
        const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
        if (autoRefreshIndicator) {
            autoRefreshIndicator.style.display = 'none';
            autoRefreshIndicator.classList.remove('active');
        }
    });
}

// Global function to manually control auto-refresh
window.toggleAutoRefresh = function() {
    if (autoRefreshInterval) {
        stopAutoRefresh();
        return false; // Auto-refresh is now off
            } else {
        startAutoRefresh();
        return true; // Auto-refresh is now on
    }
};

// Tab close and visibility handling
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Handle tab close and logout with multiple event listeners
function handleTabClose() {
    
    // Get current user's employee number from session or page data
    const currentUser = getCurrentUserEmployeeNo();
    
    if (currentUser && currentUser !== 'SUPER_ADMIN') {
        // Send logout request
        const logoutData = {
            employee_no: currentUser
        };
        
        
        // Use sendBeacon for reliable delivery during page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(logoutData)], {type: 'application/json'});
            const success = navigator.sendBeacon('/super_admin-mis/logout_on_tab_close.php', blob);
            } else {
            // Fallback for older browsers
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/super_admin-mis/logout_on_tab_close.php', false); // Synchronous for beforeunload
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(logoutData));
        }
        
            } else {
    }
}

// Multiple event listeners for better tab close detection
window.addEventListener('beforeunload', handleTabClose);
window.addEventListener('unload', handleTabClose);
window.addEventListener('pagehide', handleTabClose);

// Also detect when page becomes hidden (tab switch or close)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        // Use a small delay to ensure the request is sent
        setTimeout(handleTabClose, 100);
    }
});

// Additional approach: Use a heartbeat to detect when user is no longer active
let heartbeatInterval = null;
let lastActivity = Date.now();

function startHeartbeat() {
    // Update last activity on any user interaction
    const updateActivity = () => {
        lastActivity = Date.now();
    };
    
    // Track user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
        document.addEventListener(event, updateActivity, true);
    });
    
    // Check every 5 seconds if user is still active
    heartbeatInterval = setInterval(() => {
        const now = Date.now();
        const timeSinceActivity = now - lastActivity;
        
        // If no activity for 10 seconds, consider user inactive
        if (timeSinceActivity > 10000) {
            handleTabClose();
            clearInterval(heartbeatInterval);
        }
    }, 5000);
}

// Start heartbeat when page loads
if (typeof window !== 'undefined') {
    startHeartbeat();
}

// Function to get current user's employee number
function getCurrentUserEmployeeNo() {
    
    // First try to get from body data attribute (most reliable)
    const bodyUser = document.body.getAttribute('data-current-user');
    if (bodyUser) {
        return bodyUser;
    }
    
    // Try to get from session data or page elements
    const userInfoElement = document.querySelector('[data-employee-no]');
    if (userInfoElement) {
        return userInfoElement.dataset.employeeNo;
    }
    
    // Try to get from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const employeeNo = urlParams.get('employee_no');
    if (employeeNo) {
        return employeeNo;
    }
    
    // Try to get from localStorage or sessionStorage
    const storedEmployeeNo = localStorage.getItem('current_user_employee_no') || 
                             sessionStorage.getItem('current_user_employee_no');
    if (storedEmployeeNo) {
        return storedEmployeeNo;
    }
    
    // Try to get from PHP session (if available)
    const sessionUser = document.querySelector('meta[name="current-user"]');
    if (sessionUser) {
        const content = sessionUser.getAttribute('content');
        return content;
    }
    
    // If we can't determine the user, return null
    return null;
}

// Helper functions for auto-refresh indicator
function showAutoRefreshIndicator() {
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
        autoRefreshIndicator.classList.remove('inactive');
        autoRefreshIndicator.classList.add('active');
        autoRefreshIndicator.title = 'Auto-refresh active (every 30s) - Click to toggle';
    }
}

function hideAutoRefreshIndicator() {
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
        autoRefreshIndicator.classList.add('inactive');
        autoRefreshIndicator.classList.remove('active');
        autoRefreshIndicator.title = 'Auto-refresh inactive - Click to enable';
    }
}

function updateRefreshButtonTooltip() {
    const refreshButton = document.querySelector('.refresh-button');
    const countdownTimer = document.getElementById('countdownTimer');
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (!refreshButton) return;
    
    if (autoRefreshInterval && nextAutoRefreshTime) {
        // Auto-refresh is active, show countdown
                    const now = new Date();
        const timeLeft = Math.max(0, Math.floor((nextAutoRefreshTime - now) / 1000));
        
        if (timeLeft > 0) {
            // Show countdown timer in button
            if (countdownTimer) {
                countdownTimer.textContent = `(${timeLeft}s)`;
                countdownTimer.style.display = 'inline';
                countdownTimer.classList.add('show');
            }
            
            // Hide auto-refresh indicator (not updating)
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
            }
            
            refreshButton.title = 'Refresh user status in real-time\nClick to manually refresh\nAuto-refresh every 30s';
        } else {
            // Timer is 0, show auto-refresh indicator
            if (countdownTimer) {
                countdownTimer.style.display = 'none';
                countdownTimer.classList.remove('show');
            }
            
            // Show auto-refresh indicator (updating)
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'inline';
                autoRefreshIndicator.classList.add('active');
            }
            
            refreshButton.title = 'Refresh user status in real-time\nAuto-refresh in progress';
        }
    } else {
        // Auto-refresh is inactive
        if (countdownTimer) {
            countdownTimer.style.display = 'none';
            countdownTimer.classList.remove('show');
        }
        
        if (autoRefreshIndicator) {
            autoRefreshIndicator.style.display = 'none';
        }
        
        refreshButton.title = 'Refresh user status in real-time\nClick to manually refresh\nAuto-refresh every 30s';
    }
}

// Test function to check auto-refresh status
window.checkAutoRefreshStatus = function() {
    
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
    }
    
    return !!autoRefreshInterval;
};

// Global function to trigger user account management initialization
window.initializeUserAccountManagement = function() {
    // This will be called from global.js when navigating to user-account-management
    // The actual initialization will happen in the DOMContentLoaded event
};