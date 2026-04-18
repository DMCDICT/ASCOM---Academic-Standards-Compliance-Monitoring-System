/*
 * modal-edit-user.js
 * Edit user modal functionality
 */

// Make functions globally available
window.openEditUserModal = function(employeeNo) {
    const modal = document.getElementById('editUserModal');
    if (!modal) {
        console.error('Edit modal not found');
        return;
    }
    
    document.body.style.overflow = 'hidden';
    modal.style.display = 'flex';
    
    // Fetch user data
    fetchUserDataForEdit(employeeNo);
};

window.closeEditUserModal = function() {
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
    
    const form = document.getElementById('editUserForm');
    if (form) form.reset();
};

window.closeEditUserSuccessModal = function() {
    const modal = document.getElementById('editUserSuccessModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
    location.reload();
};

window.closeEditUserErrorModal = function() {
    const modal = document.getElementById('editUserErrorModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
};

// Fetch user data for editing
async function fetchUserDataForEdit(employeeNo) {
    try {
        const response = await fetch(`./api/get_user_data.php?employee_no=${encodeURIComponent(employeeNo)}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            populateEditForm(data.data);
        } else {
            alert('Error: ' + (data.message || 'Failed to load user data'));
            closeEditUserModal();
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
        alert('Error loading user data');
        closeEditUserModal();
    }
}

function populateEditForm(user) {
    // Set original employee number for updating
    document.getElementById('employee_no_original').value = user.employee_no || '';
    
    // Populate form fields
    document.getElementById('edit_employee_no').value = user.employee_no || '';
    document.getElementById('edit_first_name').value = user.first_name || '';
    document.getElementById('edit_last_name').value = user.last_name || '';
    document.getElementById('edit_middle_name').value = user.middle_name || '';
    document.getElementById('edit_title').value = user.title || '';
    
    // Use email or institutional_email
    const emailValue = user.email || user.institutional_email || '';
    document.getElementById('edit_institutional_email').value = emailValue;
    
    document.getElementById('edit_mobile_no').value = user.mobile_no || '';
    document.getElementById('edit_password').value = '';
    
    // Set department
    const deptSelect = document.getElementById('edit_department_id');
    if (deptSelect && user.department_id) {
        deptSelect.value = user.department_id;
    }
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('edit_update_btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('./process_edit_user.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('editUserSuccessMessage').textContent = result.message || 'User updated successfully!';
                document.getElementById('editUserSuccessModal').style.display = 'flex';
            } else {
                document.getElementById('editUserErrorMessage').textContent = result.message || 'Failed to update user';
                document.getElementById('editUserErrorModal').style.display = 'flex';
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            document.getElementById('editUserErrorMessage').textContent = 'Network error. Please try again.';
            document.getElementById('editUserErrorModal').style.display = 'flex';
        }
        
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'UPDATE';
        }
    });
    
    // Password reset button
    const resetBtn = document.getElementById('reset_default_password_btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const passwordInput = document.getElementById('edit_password');
            if (passwordInput) {
                passwordInput.value = 'password123';
                resetBtn.textContent = 'Reset!';
                setTimeout(() => {
                    resetBtn.textContent = 'Reset to Default';
                }, 1500);
            }
        });
    }
});