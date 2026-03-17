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
    console.log('🔄 Manual refresh triggered at:', new Date().toLocaleTimeString());
    
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
    console.log('📊 Auto-refresh status after manual refresh:', window.checkAutoRefreshStatus());
};

window.refreshUserList = function() {
    console.log('🔄 Refreshing user list...');
            
            // Add cache-busting parameter
            const timestamp = new Date().getTime();
            const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
            console.log('Refresh API Response:', data);
                    if (data.success) {
                        usersData = data.users;
                        filteredUsers = usersData.slice();
                window.renderTable();
                console.log('✅ User list refreshed successfully');
                
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
                console.error('❌ Failed to refresh user list:', data.message);
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
            console.error('❌ Error refreshing user list:', error);
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
        console.log('User table body not found');
        return;
    }
    
    console.log('Rendering table with users:', users.length);
    
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
                            console.log('Row clicked, employee number:', employeeNo);
                            
                            // Only open modal if we have a valid employee number
                            if (employeeNo && employeeNo !== 'undefined' && employeeNo !== 'null') {
                                window.openUserDetailsModal(employeeNo);
                            } else {
                                console.warn('Invalid employee number:', employeeNo);
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
                            console.warn('openEditUserModal function not available yet');
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
    console.log('🔍 Filtering users with query:', query);
    console.log('📊 Total users in data:', usersData.length);
    
    if (!query.trim()) {
        filteredUsers = usersData.slice();
        console.log('✅ No query provided, showing all users');
    } else {
        const searchTerm = query.toLowerCase();
        console.log(`🔍 Searching for: "${searchTerm}"`);
        
        filteredUsers = usersData.filter(user => {
            // Debug logging for status search
            if (searchTerm === 'active' || searchTerm === 'inactive') {
                console.log(`👤 User ${user.employee_no}: is_active="${user.is_active}" (type: ${typeof user.is_active}), searchTerm="${searchTerm}"`);
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
                
                console.log(`👤 User ${user.employee_no}: isActive=${isActive}, searchTerm=${searchTerm}, will return: ${searchTerm === 'active' ? isActive : !isActive}`);
                
                if (searchTerm === 'active') {
                    // Only return true if user is active
                    const result = isActive;
                    console.log(`👤 User ${user.employee_no}: ACTIVE search - returning ${result}`);
                    return result;
                } else if (searchTerm === 'inactive') {
                    // Only return true if user is inactive
                    const result = !isActive;
                    console.log(`👤 User ${user.employee_no}: INACTIVE search - returning ${result}`);
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
                console.log(`👤 User ${user.employee_no}: matches search term "${searchTerm}"`);
            }
            
            return matches;
        });
    }
    console.log(`✅ Filtered users count: ${filteredUsers.length}`);
    userManagementCurrentPage = 1;
    window.renderTable();
};

// Modal functions
window.openUserDetailsModal = function(employeeNo) {
    console.log('🔍 Opening user details modal for:', employeeNo);
    console.log('📊 Available users data:', usersData.length, 'users');
    console.log('📊 Page ready status:', isPageReady);
    
    // STRICT CHECK - Only allow if employee number is valid
    if (!employeeNo || employeeNo === 'undefined' || employeeNo === 'null' || employeeNo === '') {
        console.warn('🚫 Modal opening blocked - invalid employee number:', employeeNo);
        return;
    }
    
    // Prevent opening if page isn't ready
    if (!isPageReady) {
        console.warn('Page not ready, preventing modal opening');
        return;
    }
    
    // Validate input
    if (!employeeNo) {
        console.error('No employee number provided');
        return;
    }
    
    // Check if usersData is loaded
    if (!usersData || usersData.length === 0) {
        console.error('No users data available');
        alert('User data not loaded. Please refresh the page.');
        return;
    }
    
    // Find the user data
    const user = usersData.find(u => u.employee_no === employeeNo);
    if (!user) {
        console.error('User not found:', employeeNo);
        alert('User not found. Please refresh the page.');
        return;
    }
    
    console.log('✅ Found user:', user);
    
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
        console.error('User details content element not found');
        return;
    }
    
    // Show the modal
    const modal = document.getElementById('userDetailsModal');
    if (modal) {
        // Use class-based modal display
        modal.classList.add('show');
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        document.body.style.overflow = 'hidden'; // Disable body scroll
        
        // Store the employee number for edit/delete actions
        document.getElementById('editFromDetailsBtn').setAttribute('data-employee-no', employeeNo);
        document.getElementById('deleteFromDetailsBtn').setAttribute('data-employee-no', employeeNo);
        
        console.log('✅ Modal displayed successfully');
    } else {
        console.error('User details modal not found');
    }
};

window.closeUserDetailsModal = function() {
    console.log('Closing user details modal');
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
    console.log('🗑️ Opening delete user modal for:', employeeNo);
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Find the user data
    const user = usersData.find(u => u.employee_no === employeeNo);
    if (!user) {
        console.error('User not found:', employeeNo);
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
    console.log('Closing delete user modal');
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
        console.error('Error deleting user:', error);
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
                console.error('Error updating user:', error);
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
        console.log('🔄 Initializing user account management...');
        
        // Immediately hide the modal on page load
        const userDetailsModal = document.getElementById('userDetailsModal');
        if (userDetailsModal) {
            userDetailsModal.style.display = 'none';
            userDetailsModal.style.visibility = 'hidden';
            userDetailsModal.classList.remove('show');
            console.log('✅ User details modal immediately hidden');
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
            console.log('🔄 Loading initial user data from API...');
    
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
    
    console.log('📡 API URL:', apiUrl);
    
    fetch(apiUrl)
                .then(response => {
                    console.log('📡 Response status:', response.status);
                    console.log('📡 Response ok:', response.ok);
                    return response.json();
                })
        .then(data => {
                    console.log('Initial API Response:', data);
            if (data.success) {
                usersData = data.users;
                        filteredUsers = usersData.slice();
                
                // Log the first user to see what data we're getting
                if (usersData.length > 0) {
                    console.log('First user data:', usersData[0]);
                    console.log('First user online_status:', usersData[0].online_status);
                }
                
                        // Render the table with the loaded data
                        window.renderTable();
                        
                        console.log('✅ Initial data loaded successfully');
                        
                        // Start auto-refresh after initial load
                        startAutoRefresh();
                        
                                // Mark page as ready for modal interactions
        isPageReady = true;
        console.log('✅ Page is now ready for modal interactions');
        
        // Ensure user details modal is hidden on page load
        const userDetailsModal = document.getElementById('userDetailsModal');
        if (userDetailsModal) {
            userDetailsModal.classList.remove('show');
            userDetailsModal.style.display = 'none';
            userDetailsModal.style.visibility = 'hidden';
            console.log('✅ User details modal hidden on page load');
        }
        
        // Debug: Check edit user modal state on page load
        const editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            console.log('🔍 Edit User Modal state on page load:');
            console.log('  - Inline display style:', editUserModal.style.display);
            console.log('  - Computed display style:', window.getComputedStyle(editUserModal).display);
            console.log('  - Data modal state:', editUserModal.getAttribute('data-modal-state'));
            console.log('  - Modal visibility:', editUserModal.style.visibility);
            console.log('  - Modal opacity:', editUserModal.style.opacity);
        } else {
            console.log('❌ Edit User Modal not found on page load');
        }
            } else {
                        console.error('❌ Failed to load initial data:', data.message);
                        userTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">Error loading users</td></tr>';
            }
        })
        .catch(error => {
                    console.error('❌ Error loading initial data:', error);
                    console.error('❌ Error details:', error.message);
                    userTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: red;">Network error loading users</td></tr>';
                });
        }

        // Set up event listeners
        if (searchInput) {
            // Remove real-time search - only search on button click or Enter key
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const query = this.value.trim();
                    console.log('🔍 Enter key pressed, searching for:', query);
                    if (query) {
                        console.log('🔍 Executing search for:', query);
                        window.filterUsers(query);
                    } else {
                        console.log('🔍 No query provided, showing all users');
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
                    console.log('Search cleared, showing all users');
                    window.filterUsers('');
                }
            });
        }
        
        // Set up search button click event
        const searchButton = document.querySelector('.search-button');
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                const query = searchInput ? searchInput.value.trim() : '';
                console.log('🔍 Search button clicked with query:', query);
                if (query) {
                    console.log('🔍 Executing search for:', query);
                    window.filterUsers(query);
                } else {
                    console.log('🔍 No query provided, showing all users');
                    window.filterUsers('');
                }
            });
        } else {
            console.error('❌ Search button not found!');
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
        
        console.log('✅ User account management initialized');
    }
});

// Function to store current user information
function storeCurrentUserInfo() {
    console.log('💾 Storing current user info for tab close logout...');
    
    // Try to get current user from various sources
    const currentUser = getCurrentUserEmployeeNo();
    
    if (currentUser) {
        // Store in both localStorage and sessionStorage for reliability
        localStorage.setItem('current_user_employee_no', currentUser);
        sessionStorage.setItem('current_user_employee_no', currentUser);
        
        // Also store in a data attribute on the body for easy access
        document.body.setAttribute('data-current-user', currentUser);
        
        console.log('✅ Current user stored for tab close logout:', currentUser);
        console.log('📦 Stored in localStorage:', localStorage.getItem('current_user_employee_no'));
        console.log('📦 Stored in sessionStorage:', sessionStorage.getItem('current_user_employee_no'));
        console.log('📦 Stored in body attribute:', document.body.getAttribute('data-current-user'));
    } else {
        console.log('⚠️ Could not determine current user for tab close logout');
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
        console.log('🔄 Auto-refreshing user list...');
        showAutoRefreshIndicator();
        autoRefreshUserList();
    }, 30000); // 30 seconds
    
    // Set next refresh time
    nextAutoRefreshTime = new Date(Date.now() + 30000);
    
    console.log('✅ Auto-refresh started (every 30 seconds)');
    console.log('🕐 Next auto-refresh at:', nextAutoRefreshTime.toLocaleTimeString());
    
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
        
        console.log('⏹️ Auto-refresh stopped');
    }
}

function autoRefreshUserList() {
    console.log('🔄 Auto-refresh triggered at:', new Date().toLocaleTimeString());
    
    // Store current search query before refreshing
    const currentSearchQuery = document.getElementById('userSearchInput') ? document.getElementById('userSearchInput').value.trim() : '';
    console.log('🔍 Preserving current search query:', currentSearchQuery);
    
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    const apiUrl = `./api/get_all_users.php?t=${timestamp}`;
    
    fetch(apiUrl)
    .then(response => response.json())
    .then(data => {
        console.log('Auto-refresh API Response:', data);
        if (data.success) {
            usersData = data.users;
            
            // Re-apply the current search filter if there was one
            if (currentSearchQuery) {
                console.log('🔍 Re-applying search filter:', currentSearchQuery);
                window.filterUsers(currentSearchQuery);
            } else {
                filteredUsers = usersData.slice();
                window.renderTable();
            }
            
            console.log('✅ Auto-refresh completed successfully at:', new Date().toLocaleTimeString());
            
            // Set next refresh time
            nextAutoRefreshTime = new Date(Date.now() + 30000);
            console.log('🕐 Next auto-refresh at:', nextAutoRefreshTime.toLocaleTimeString());
            
            // Hide auto-refresh indicator and update tooltip
            const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
                autoRefreshIndicator.classList.remove('active');
            }
            
            updateRefreshButtonTooltip();
        } else {
            console.error('❌ Auto-refresh failed:', data.message);
            const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
            if (autoRefreshIndicator) {
                autoRefreshIndicator.style.display = 'none';
                autoRefreshIndicator.classList.remove('active');
            }
        }
    })
    .catch(error => {
        console.error('❌ Auto-refresh error:', error);
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
        console.log('📱 Page hidden, pausing auto-refresh');
        stopAutoRefresh();
    } else {
        console.log('📱 Page visible, resuming auto-refresh');
        startAutoRefresh();
    }
});

// Handle tab close and logout with multiple event listeners
function handleTabClose() {
    console.log('🚪 Tab closing, logging out user...');
    
    // Get current user's employee number from session or page data
    const currentUser = getCurrentUserEmployeeNo();
    console.log('👤 Current user for logout:', currentUser);
    
    if (currentUser && currentUser !== 'SUPER_ADMIN') {
        // Send logout request
        const logoutData = {
            employee_no: currentUser
        };
        
        console.log('📤 Sending logout data:', logoutData);
        
        // Use sendBeacon for reliable delivery during page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(logoutData)], {type: 'application/json'});
            const success = navigator.sendBeacon('/super_admin-mis/logout_on_tab_close.php', blob);
            console.log('📡 SendBeacon result:', success);
            } else {
            // Fallback for older browsers
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/super_admin-mis/logout_on_tab_close.php', false); // Synchronous for beforeunload
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(logoutData));
            console.log('📡 XHR fallback used');
        }
        
        console.log('✅ Logout request sent for user:', currentUser);
            } else {
        console.log('⚠️ No valid user found for logout or user is SUPER_ADMIN');
    }
}

// Multiple event listeners for better tab close detection
window.addEventListener('beforeunload', handleTabClose);
window.addEventListener('unload', handleTabClose);
window.addEventListener('pagehide', handleTabClose);

// Also detect when page becomes hidden (tab switch or close)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        console.log('👁️ Page hidden, triggering logout...');
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
            console.log('💓 No activity detected, user may have closed tab');
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
    console.log('🔍 Getting current user employee number...');
    
    // First try to get from body data attribute (most reliable)
    const bodyUser = document.body.getAttribute('data-current-user');
    if (bodyUser) {
        console.log('✅ Found user from body data attribute:', bodyUser);
        return bodyUser;
    }
    
    // Try to get from session data or page elements
    const userInfoElement = document.querySelector('[data-employee-no]');
    if (userInfoElement) {
        console.log('✅ Found user from data-employee-no attribute:', userInfoElement.dataset.employeeNo);
        return userInfoElement.dataset.employeeNo;
    }
    
    // Try to get from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const employeeNo = urlParams.get('employee_no');
    if (employeeNo) {
        console.log('✅ Found user from URL parameter:', employeeNo);
        return employeeNo;
    }
    
    // Try to get from localStorage or sessionStorage
    const storedEmployeeNo = localStorage.getItem('current_user_employee_no') || 
                             sessionStorage.getItem('current_user_employee_no');
    if (storedEmployeeNo) {
        console.log('✅ Found user from storage:', storedEmployeeNo);
        return storedEmployeeNo;
    }
    
    // Try to get from PHP session (if available)
    const sessionUser = document.querySelector('meta[name="current-user"]');
    if (sessionUser) {
        const content = sessionUser.getAttribute('content');
        console.log('✅ Found user from meta tag:', content);
        return content;
    }
    
    // If we can't determine the user, return null
    console.log('⚠️ Could not determine current user employee number');
    return null;
}

// Helper functions for auto-refresh indicator
function showAutoRefreshIndicator() {
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
        autoRefreshIndicator.classList.remove('inactive');
        autoRefreshIndicator.classList.add('active');
        autoRefreshIndicator.title = 'Auto-refresh active (every 30s) - Click to toggle';
        console.log('⏳ Auto-refresh indicator shown');
    }
}

function hideAutoRefreshIndicator() {
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
        autoRefreshIndicator.classList.add('inactive');
        autoRefreshIndicator.classList.remove('active');
        autoRefreshIndicator.title = 'Auto-refresh inactive - Click to enable';
        console.log('⏳ Auto-refresh indicator hidden');
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
    console.log('🔍 Auto-refresh status check:');
    console.log('- Interval exists:', !!autoRefreshInterval);
    console.log('- Current time:', new Date().toLocaleTimeString());
    
    const autoRefreshIndicator = document.getElementById('autoRefreshIndicator');
    if (autoRefreshIndicator) {
        console.log('- Indicator visible:', autoRefreshIndicator.classList.contains('active'));
        console.log('- Indicator classes:', autoRefreshIndicator.className);
    }
    
    return !!autoRefreshInterval;
};

// Global function to trigger user account management initialization
window.initializeUserAccountManagement = function() {
    console.log('Initializing user account management...');
    // This will be called from global.js when navigating to user-account-management
    // The actual initialization will happen in the DOMContentLoaded event
};