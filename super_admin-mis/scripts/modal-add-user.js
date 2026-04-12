// modal-add-user.js
// Add User modal functionality

// Open Add User Modal
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Reset form
        const form = document.getElementById('addUserForm');
        if (form) {
            form.reset();
        }
        
        // Clear validation message
        hideAddUserValidation();
        
        // Focus on first input
        setTimeout(() => {
            document.getElementById('add_employee_no')?.focus();
        }, 100);
    } else {
        // Modal not found - function will be called by button but modal not loaded
    }
}

// Close Add User Modal
function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

// Show validation message
function showAddUserValidation(message, isError = true) {
    const msgDiv = document.getElementById('addUserValidationMsg');
    if (msgDiv) {
        msgDiv.textContent = message;
        msgDiv.style.display = 'block';
        msgDiv.style.backgroundColor = isError ? '#f8d7da' : '#d4edda';
        msgDiv.style.color = isError ? '#721c24' : '#155724';
        msgDiv.style.border = '1px solid ' + (isError ? '#f5c6cb' : '#c3e6cb');
    }
}

// Hide validation message
function hideAddUserValidation() {
    const msgDiv = document.getElementById('addUserValidationMsg');
    if (msgDiv) {
        msgDiv.style.display = 'none';
    }
}

// Validate form
function validateAddUserForm() {
    const form = document.getElementById('addUserForm');
    if (!form) return false;
    
    const employeeNo = document.getElementById('add_employee_no');
    const firstName = document.getElementById('add_first_name');
    const lastName = document.getElementById('add_last_name');
    const email = document.getElementById('add_institutional_email');
    const password = document.getElementById('add_password');
    const confirmPassword = document.getElementById('add_confirm_password');
    const roleId = document.getElementById('add_role_id');
    
    // Clear previous styles
    [employeeNo, firstName, lastName, email, password, confirmPassword, roleId].forEach(el => {
        if (el) el.style.borderColor = '';
    });
    
    let errors = [];
    
    // Validate required fields
    if (!employeeNo?.value.trim()) {
        errors.push('Employee No. is required');
        employeeNo.style.borderColor = '#dc3545';
    } else if (employeeNo.value.length < 6) {
        errors.push('Employee No. must be 6 digits');
        employeeNo.style.borderColor = '#dc3545';
    }
    
    if (!firstName?.value.trim()) {
        errors.push('First Name is required');
        firstName.style.borderColor = '#dc3545';
    }
    
    if (!lastName?.value.trim()) {
        errors.push('Last Name is required');
        lastName.style.borderColor = '#dc3545';
    }
    
    if (!email?.value.trim()) {
        errors.push('Institutional Email is required');
        email.style.borderColor = '#dc3545';
    } else if (!email.value.endsWith('@sccpag.edu.ph')) {
        errors.push('Email must end with @sccpag.edu.ph');
        email.style.borderColor = '#dc3545';
    }
    
    if (!password?.value) {
        errors.push('Password is required');
        password.style.borderColor = '#dc3545';
    } else if (password.value.length < 8) {
        errors.push('Password must be at least 8 characters');
        password.style.borderColor = '#dc3545';
    }
    
    if (password?.value !== confirmPassword?.value) {
        errors.push('Passwords do not match');
        confirmPassword.style.borderColor = '#dc3545';
    }
    
    if (!roleId?.value) {
        errors.push('Role is required');
        roleId.style.borderColor = '#dc3545';
    }
    
    if (errors.length > 0) {
        showAddUserValidation(errors.join('. '));
        return false;
    }
    
    return true;
}

// Submit form
function submitAddUserForm(formData) {
    const submitBtn = document.getElementById('add_create_btn');
    
    // Show loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        submitBtn.style.backgroundColor = '#6c757d';
    }
    
    fetch('./process_add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            document.getElementById('addUserSuccessMessage').textContent = data.message;
            document.getElementById('addUserModal').style.display = 'none';
            document.getElementById('addUserSuccessModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Refresh user list if function exists
            if (typeof window.refreshUserList === 'function') {
                window.refreshUserList();
            }
        } else {
            // Show error
            showAddUserValidation(data.message || 'Failed to create user');
        }
    })
    .catch(error => {
        console.error('Error creating user:', error);
        showAddUserValidation('Network error. Please try again.');
    })
    .finally(() => {
        // Reset button state
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'CREATE';
            submitBtn.style.backgroundColor = '#28a745';
        }
    });
}

// Modal functions for success/error
function openAddUserSuccessModal(message) {
    const modal = document.getElementById('addUserSuccessModal');
    const messageElement = document.getElementById('addUserSuccessMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
    } else {
        alert('Success: ' + message);
    }
}

function closeAddUserSuccessModal() {
    const modal = document.getElementById('addUserSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

function openAddUserErrorModal(message) {
    const modal = document.getElementById('addUserErrorModal');
    const messageElement = document.getElementById('addUserErrorMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
    } else {
        alert('Error: ' + message);
    }
}

function closeAddUserErrorModal() {
    const modal = document.getElementById('addUserErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

// Make functions globally available
window.openAddUserModal = openAddUserModal;
window.closeAddUserModal = closeAddUserModal;
window.openAddUserSuccessModal = openAddUserSuccessModal;
window.closeAddUserSuccessModal = closeAddUserSuccessModal;
window.openAddUserErrorModal = openAddUserErrorModal;
window.closeAddUserErrorModal = closeAddUserErrorModal;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('addUserForm');
    const clearEmailBtn = document.getElementById('clear_add_email_btn');
    
    // Clear email button
    if (clearEmailBtn) {
        clearEmailBtn.addEventListener('click', function() {
            const emailInput = document.getElementById('add_institutional_email');
            if (emailInput) {
                emailInput.value = '';
                emailInput.focus();
            }
        });
    }
    
    // Form submission
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateAddUserForm()) {
                const formData = new FormData(this);
                submitAddUserForm(formData);
            }
        });
    }
    
    // Password visibility toggles
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.style.filter = 'invert(0.4)';
                } else {
                    targetInput.type = 'password';
                    this.style.filter = 'invert(0%)';
                }
            }
        });
    });
    
    // Real-time validation on blur
    const employeeNoInput = document.getElementById('add_employee_no');
    if (employeeNoInput) {
        employeeNoInput.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                this.style.borderColor = '#dc3545';
                showAddUserValidation('Employee No. must be 6 digits', true);
            } else {
                this.style.borderColor = '';
                hideAddUserValidation();
            }
        });
    }
    
    const passwordInput = document.getElementById('add_password');
    const confirmPasswordInput = document.getElementById('add_confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('blur', function() {
            if (this.value && passwordInput.value !== this.value) {
                this.style.borderColor = '#dc3545';
                showAddUserValidation('Passwords do not match', true);
            } else {
                this.style.borderColor = '';
                hideAddUserValidation();
            }
        });
        
        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value === this.value) {
                this.style.borderColor = '#28a745';
                hideAddUserValidation();
            }
        });
    }
    
    // Auto-format email domain
    const emailInput = document.getElementById('add_institutional_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value && !this.value.includes('@')) {
                this.value = this.value + '@sccpag.edu.ph';
            }
        });
    }
});