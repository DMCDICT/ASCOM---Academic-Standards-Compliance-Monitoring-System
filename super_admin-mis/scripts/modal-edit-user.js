/*
 * modal-edit-user.js
 * Contains JavaScript for the edit user modal, including opening/closing,
 * form validation, password toggling, and AJAX submission.
 */


// Debug: Check if editUserModal exists when this script loads
document.addEventListener('DOMContentLoaded', function() {
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
    } else {
    }
});



// Functions to open/close user modals (global scope for onclick attributes in HTML)
window.openEditUserModal = function(userId) {
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Show the modal
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Simple protection - prevent clicks outside from closing the modal
        modal.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Add a simple outside click handler
        if (!window.editModalOutsideClickHandler) {
            window.editModalOutsideClickHandler = function(e) {
                if (e.target === modal) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            };
            document.addEventListener('click', window.editModalOutsideClickHandler);
        }
    } else {
        console.error('❌ Modal not found!');
        return;
    }
    
    // Fetch user data and populate form
    fetchUserDataForEdit(userId);
};

window.closeEditUserModal = function() {
    
    // Remove the outside click handler
    if (window.editModalOutsideClickHandler) {
        document.removeEventListener('click', window.editModalOutsideClickHandler);
        window.editModalOutsideClickHandler = null;
    }
    
    // Re-enable body scroll
    document.body.style.overflow = '';
    
    // Hide the modal
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // Reset form
    resetEditForm();
};

// Function to fetch user data for editing
async function fetchUserDataForEdit(userId) {
    try {
        const response = await fetch(`./api/get_user_data.php?employee_no=${userId}`);
        const data = await response.json();
        
            success: data.success,
            hasData: !!data.data,
            dataType: typeof data.data,
            message: data.message
        });
        
        if (data.success && data.data) {
                hasCurrentPassword: !!data.data.current_password,
                currentPasswordLength: data.data.current_password ? data.data.current_password.length : 0,
                currentPasswordValue: data.data.current_password ? '***' + data.data.current_password.slice(-3) : 'null'
            });
            populateEditForm(data.data);
        } else {
            console.error('❌ Failed to fetch user data:', data.message);
            console.error('Full response:', data);
            openEditUserErrorModal('Failed to fetch user data: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
        openEditUserErrorModal('Error fetching user data. Please try again.');
    }
}

// Function to populate the edit form
function populateEditForm(user) {
    
    // Populate form fields
    document.getElementById('edit_employee_no').value = user.employee_no || '';
    document.getElementById('employee_no_original').value = user.employee_no || '';
    document.getElementById('edit_department_id').value = user.department_id || '';
    document.getElementById('edit_first_name').value = user.first_name || '';
    document.getElementById('edit_middle_name').value = user.middle_name || '';
    document.getElementById('edit_last_name').value = user.last_name || '';
    document.getElementById('edit_title').value = user.title || '';
    document.getElementById('edit_institutional_email').value = user.institutional_email || '';
    document.getElementById('edit_mobile_no').value = user.mobile_no || '';
    // Set password field - show current password if it exists
    if (user.current_password) {
        document.getElementById('edit_password').value = user.current_password;
    } else {
    document.getElementById('edit_password').value = '';
    }
    
    
    // Small delay to ensure DOM is fully ready
    setTimeout(() => {
        setupPasswordToggle();
        
        setupInstitutionalEmail();
        
        setupResetPassword();
        
        setupEmployeeNumberValidation();
        
        setupMobileNumberValidation();
        
    setupFormChangeDetection();
    
        window.storeEditFormOriginalValues();
        
        // Check if user has a default password and update reset button accordingly
        if (user.current_password) {
            const employeeNo = user.employee_no || '';
            const departmentSelect = document.getElementById('edit_department_id');
            if (departmentSelect && departmentSelect.options[departmentSelect.selectedIndex]) {
                const departmentCode = departmentSelect.options[departmentSelect.selectedIndex].textContent;
                const expectedDefaultPassword = generateDefaultPassword(employeeNo, departmentCode);
                
                
                if (user.current_password === expectedDefaultPassword) {
                    // User has default password, disable reset button
                    window.editFormDefaultPassword = expectedDefaultPassword;
                } else {
                    // User has custom password, enable reset button
                }
            }
        } else {
        }
        
        updateUpdateButton();
        
        // Force update reset button state after a short delay to ensure DOM is ready
        setTimeout(() => {
            updateResetButtonState();
        }, 200);
        
        
        // Debug: Check if password toggle is working
        const passwordInput = document.getElementById('edit_password');
        const toggleIcon = document.querySelector('.toggle-password[data-target="edit_password"]');
        
        // Additional check: Verify password field hasn't been cleared
        if (user.current_password && passwordInput && passwordInput.value !== user.current_password) {
            console.error('❌ WARNING: Password field was cleared after population!');
            console.error('  - Expected:', user.current_password);
            console.error('  - Actual:', passwordInput.value);
            // Restore the password
            passwordInput.value = user.current_password;
        }
    }, 100);
}

// Function to setup password toggle functionality
function setupPasswordToggle() {
    const passwordInput = document.getElementById('edit_password');
    const toggleIcon = document.querySelector('.toggle-password[data-target="edit_password"]');
    
    
    // Debug: Check all elements with toggle-password class
    const allToggleIcons = document.querySelectorAll('.toggle-password');
    allToggleIcons.forEach((icon, index) => {
            classList: icon.classList.toString(),
            dataTarget: icon.getAttribute('data-target'),
            src: icon.src,
            visible: icon.offsetParent !== null
        });
    });
    
    if (toggleIcon && passwordInput) {
        
        // Remove any existing event listeners to prevent duplicates
        toggleIcon.removeEventListener('click', handlePasswordToggle);
        toggleIcon.addEventListener('click', handlePasswordToggle);
        
        // Add input event listener to password field for change detection
        passwordInput.removeEventListener('input', handlePasswordInput);
        passwordInput.addEventListener('input', handlePasswordInput);
        
        
        // Test if the icon is clickable
            src: toggleIcon.src,
            width: toggleIcon.offsetWidth,
            height: toggleIcon.offsetHeight,
            cursor: window.getComputedStyle(toggleIcon).cursor,
            pointerEvents: window.getComputedStyle(toggleIcon).pointerEvents
        });
    } else {
        console.error('❌ Password input or toggle icon not found!');
        console.error('❌ Password input:', passwordInput);
        console.error('❌ Toggle icon:', toggleIcon);
        
        // Additional debugging
        if (!toggleIcon) {
            console.error('❌ Toggle icon not found. Checking for elements with similar selectors...');
            const similarIcons = document.querySelectorAll('[class*="toggle"], [class*="password"]');
            console.error('❌ Similar elements found:', similarIcons.length);
            similarIcons.forEach((el, index) => {
                console.error(`❌ Similar element ${index}:`, {
                    tagName: el.tagName,
                    className: el.className,
                    id: el.id,
                    src: el.src
                });
            });
        }
    }
}

// Separate function for password toggle handling
function handlePasswordToggle(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const passwordInput = document.getElementById('edit_password');
    const toggleIcon = document.querySelector('.toggle-password[data-target="edit_password"]');
    
    
    if (passwordInput && toggleIcon) {
        
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.src = '../src/assets/icons/hide_password.png';
            } else {
                passwordInput.type = 'password';
                toggleIcon.src = '../src/assets/icons/show_password.png';
        }
        
        // Trigger form change detection
        passwordInput.dispatchEvent(new Event('input'));
    } else {
        console.error('❌ Password input or toggle icon not found in handler!');
    }
}

// Separate function for password input handling
function handlePasswordInput() {
    updateUpdateButton();
    // Also update reset button state when password changes
    updateResetButtonState();
}

// Function to setup institutional email functionality
function setupInstitutionalEmail() {
    const emailInput = document.getElementById('edit_institutional_email');
    const clearBtn = document.getElementById('clear_edit_email_btn');
    const institutionalEmailDomain = '@sccpag.edu.ph';
    
    if (emailInput && clearBtn) {
        // Handle input events for automatic domain filling
        emailInput.addEventListener('input', function() {
            let val = this.value;
            const atIndex = val.indexOf('@');
            
            if (atIndex !== -1) {
                const usernamePart = val.substring(0, atIndex).trim();
                this.value = usernamePart + institutionalEmailDomain;
            } else if (val.endsWith(institutionalEmailDomain.substring(1))) {
                this.value = val.substring(0, val.length - institutionalEmailDomain.length + 1);
            }
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
        
        // Handle blur events for validation
        emailInput.addEventListener('blur', function() {
            let val = this.value.trim();
            const atIndex = val.indexOf('@');
            
            if (val.length === 0) {
                this.value = '';
            } else if (!val.endsWith(institutionalEmailDomain)) {
                const usernamePart = (atIndex !== -1) ? val.substring(0, atIndex).trim() : val.trim();
                if (usernamePart.length > 0) {
                    this.value = usernamePart + institutionalEmailDomain;
                } else {
                    this.value = '';
                }
            } else if (val.endsWith(institutionalEmailDomain)) {
                const usernamePart = val.substring(0, val.length - institutionalEmailDomain.length).trim();
                if (usernamePart.length === 0) {
                    this.value = '';
                } else {
                    this.value = usernamePart + institutionalEmailDomain;
                }
            }
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
        
        // Clear button functionality
        clearBtn.addEventListener('click', function() {
            emailInput.value = '';
            emailInput.focus();
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
    }
}

// Function to update reset button state based on current password
function updateResetButtonState() {
    const resetBtn = document.getElementById('reset_default_password_btn');
    const passwordInput = document.getElementById('edit_password');
    
    if (!resetBtn || !passwordInput) {
        return;
    }
    
    const currentPassword = passwordInput.value.trim();
    const defaultPassword = window.editFormDefaultPassword;
    
        currentPassword: currentPassword ? '***' : '(empty)',
        defaultPassword: defaultPassword,
        isDefault: currentPassword === defaultPassword,
        buttonElement: resetBtn,
        passwordElement: passwordInput
    });
    
    // If no default password is set yet, disable the button
    if (!defaultPassword) {
        resetBtn.disabled = true;
        resetBtn.style.background = '#e9ecef';
        resetBtn.style.color = '#6c757d';
        resetBtn.style.cursor = 'not-allowed';
        resetBtn.title = 'Default password not calculated yet';
        return;
    }
    
    // If password is empty or matches default, disable reset button
    if (!currentPassword || currentPassword === defaultPassword) {
        // Password is empty or still default, disable reset button
        resetBtn.disabled = true;
        resetBtn.style.background = '#e9ecef';
        resetBtn.style.color = '#6c757d';
        resetBtn.style.cursor = 'not-allowed';
        resetBtn.title = currentPassword ? 'Password is already at default value' : 'Password field is empty';
    } else {
        // Password has been changed, enable reset button
        resetBtn.disabled = false;
        resetBtn.style.background = '#C9C9C9';
        resetBtn.style.color = 'black';
        resetBtn.style.cursor = 'pointer';
        resetBtn.title = 'Reset password to default value';
    }
}

// Function to setup reset password functionality
function setupResetPassword() {
    const resetBtn = document.getElementById('reset_default_password_btn');
    const passwordInput = document.getElementById('edit_password');
    const employeeNoInput = document.getElementById('edit_employee_no');
    const departmentSelect = document.getElementById('edit_department_id');
    
    if (resetBtn && passwordInput && employeeNoInput && departmentSelect) {
        resetBtn.addEventListener('click', function() {
            const employeeNo = employeeNoInput.value.trim();
            const departmentId = departmentSelect.value;
            
            if (!employeeNo) {
                openEditUserErrorModal('Please enter an Employee Number to generate a default password.');
                return;
            }
            
            if (!departmentId) {
                openEditUserErrorModal('Please select a Department to generate a default password.');
                return;
            }
            
            // Get department code from selected option
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            const departmentCode = selectedOption.textContent;
            
            // Generate default password: employee_no + TCH + department_code
            const defaultPassword = generateDefaultPassword(employeeNo, departmentCode);
            
            // Set password and provide visual feedback
            passwordInput.value = defaultPassword;
            passwordInput.type = 'text';
            
            // Update toggle icon to show password
            const toggleIcon = document.querySelector('.toggle-password[data-target="edit_password"]');
            if (toggleIcon) {
                toggleIcon.src = '../src/assets/icons/hide_password.png';
            }
            
            // Update stored default password
            window.editFormDefaultPassword = defaultPassword;
            
            // Visual feedback on button
            resetBtn.textContent = 'Password Generated!';
            resetBtn.style.background = '#28a745';
            resetBtn.style.color = 'white';
            
            // Reset button after 2 seconds
            setTimeout(() => {
                resetBtn.textContent = 'Reset to Default';
                // Update button state based on new password
                updateResetButtonState();
            }, 2000);
            
            // Trigger form change detection
            passwordInput.dispatchEvent(new Event('input'));
        });
    }
}

// Function to setup form change detection
function setupFormChangeDetection() {
    const form = document.getElementById('editUserForm');
    const updateBtn = document.getElementById('edit_update_btn');
    
    if (!form || !updateBtn) return;
    
    // Get all fields to monitor (including optional fields)
    const monitoredFields = [
        'edit_employee_no',
        'edit_department_id', 
        'edit_first_name',
        'edit_last_name',
        'edit_middle_name',
        'edit_title',
        'edit_institutional_email',
        'edit_mobile_no',
        'edit_password'
    ];
    
    // Add event listeners to monitored fields
    monitoredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            
            field.addEventListener('input', () => {
                updateUpdateButton();
                // Update reset button state for employee number and department changes
                if (fieldId === 'edit_employee_no' || fieldId === 'edit_department_id') {
                    updateResetButtonState();
                }
            });
            field.addEventListener('change', () => {
                updateUpdateButton();
                // Update reset button state for employee number and department changes
                if (fieldId === 'edit_employee_no' || fieldId === 'edit_department_id') {
                    updateResetButtonState();
                }
            });
            field.addEventListener('blur', () => {
                updateUpdateButton();
                // Update reset button state for employee number and department changes
                if (fieldId === 'edit_employee_no' || fieldId === 'edit_department_id') {
                    updateResetButtonState();
                }
            });
        } else {
            console.error(`❌ Field ${fieldId} not found during event listener setup!`);
        }
    });
}

// Function to update the UPDATE button state
function updateUpdateButton() {
    const updateBtn = document.getElementById('edit_update_btn');
    if (!updateBtn) {
        console.error('Update button not found!');
        return;
    }
    
    const hasChanges = checkFormChanges();
    const isFormValid = checkFormValidity();
    
        hasChanges,
        isFormValid,
        buttonDisabled: updateBtn.disabled,
        buttonElement: updateBtn
    });
    
    // Debug: Show what fields are being monitored
        'edit_employee_no',
        'edit_department_id', 
        'edit_first_name',
        'edit_last_name',
        'edit_middle_name',
        'edit_title',
        'edit_institutional_email',
        'edit_mobile_no',
        'edit_password'
    ]);
    
    if (hasChanges && isFormValid) {
        updateBtn.disabled = false;
        updateBtn.style.background = '#007bff';
        updateBtn.style.color = 'white';
        updateBtn.style.cursor = 'pointer';
    } else {
        updateBtn.disabled = true;
        updateBtn.style.background = '#C9C9C9';
        updateBtn.style.color = '#666';
        updateBtn.style.cursor = 'not-allowed';
    }
}

// Function to check if form has changes
function checkFormChanges() {
    if (!window.editFormOriginalValues) {
        return false;
    }
    
    
    // Check all monitored fields for changes (including optional fields)
    const monitoredFields = [
        'edit_employee_no',
        'edit_department_id',
        'edit_first_name', 
        'edit_last_name',
        'edit_middle_name',
        'edit_title',
        'edit_institutional_email',
        'edit_mobile_no',
        'edit_password'
    ];
    
    for (const fieldId of monitoredFields) {
        const field = document.getElementById(fieldId);
        if (field) {
            const currentValue = field.value;
            const originalValue = window.editFormOriginalValues[fieldId] || '';
            
                current: currentValue,
                original: originalValue,
                changed: currentValue !== originalValue,
                fieldType: field.type,
                fieldRequired: field.required
            });
            
            if (currentValue !== originalValue) {
                return true;
            }
        } else {
            console.error(`❌ Field ${fieldId} not found!`);
        }
    }
    
    return false;
}

// Function to check form validity
function checkFormValidity() {
    const requiredFields = [
        'edit_employee_no',
        'edit_department_id',
        'edit_first_name',
        'edit_last_name',
        'edit_institutional_email'
    ];
    
    // Check if all required fields are filled
    for (const fieldId of requiredFields) {
        const field = document.getElementById(fieldId);
        if (field && field.value.trim() === '') {
            return false;
        }
    }
    
    // Check employee number length (must be exactly 6 digits)
    const employeeNoField = document.getElementById('edit_employee_no');
    if (employeeNoField && employeeNoField.value.trim() !== '') {
        const employeeNo = employeeNoField.value.trim();
        if (employeeNo.length !== 6 || !/^\d{6}$/.test(employeeNo)) {
            return false;
        }
    }
    
    // Check institutional email format
    const emailField = document.getElementById('edit_institutional_email');
    if (emailField && emailField.value.trim() !== '') {
        const emailValue = emailField.value.trim();
        const institutionalEmailDomain = '@sccpag.edu.ph';
        
        if (!emailValue.endsWith(institutionalEmailDomain) || 
            emailValue.substring(0, emailValue.length - institutionalEmailDomain.length).length === 0) {
            return false;
        }
    }
    
    return true;
}

// Function to generate default password based on employee number and department
function generateDefaultPassword(employeeNo, departmentCode) {
    if (!employeeNo || !departmentCode) return null;
    return `${employeeNo}TCH${departmentCode}`;
}

// Function to store original form values
function storeOriginalValues() {
    // Store all monitored fields (including optional fields)
    const monitoredFields = [
        'edit_employee_no',
        'edit_department_id',
        'edit_first_name',
        'edit_last_name',
        'edit_middle_name',
        'edit_title',
        'edit_institutional_email',
        'edit_mobile_no',
        'edit_password'
    ];
    
    window.editFormOriginalValues = {};
    
    // Get employee number and department for default password calculation
    const employeeNoInput = document.getElementById('edit_employee_no');
    const departmentSelect = document.getElementById('edit_department_id');
    
    let defaultPassword = null;
    if (employeeNoInput && departmentSelect) {
        const employeeNo = employeeNoInput.value.trim();
        const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
        if (selectedOption) {
            const departmentCode = selectedOption.textContent;
            defaultPassword = generateDefaultPassword(employeeNo, departmentCode);
        }
    }
    
    // Store default password for comparison
    window.editFormDefaultPassword = defaultPassword;
    
    monitoredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            // Store the actual current value for all fields including password
            window.editFormOriginalValues[fieldId] = field.value;
        }
    });
    
    
    // Debug: Show what was stored for each field
    monitoredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
        } else {
        }
    });
    
    // Update reset button state after storing values
    updateResetButtonState();
}

// Expose storeOriginalValues function globally so it can be called when form is populated
window.storeEditFormOriginalValues = storeOriginalValues;



// Function to reset the edit form
function resetEditForm() {
    const form = document.getElementById('editUserForm');
    if (form) {
        form.reset();
    }
    
    // Reset password field type
    const passwordInput = document.getElementById('edit_password');
    if (passwordInput) {
        passwordInput.type = 'password';
    }
    
    // Reset toggle icon
    const toggleIcon = document.querySelector('.toggle-password[data-target="edit_password"]');
    if (toggleIcon) {
        toggleIcon.src = '../src/assets/icons/show_password.png';
    }
    
    // Reset reset button
    const resetBtn = document.getElementById('reset_default_password_btn');
    if (resetBtn) {
        resetBtn.textContent = 'Reset to Default';
        resetBtn.style.background = '#C9C9C9';
        resetBtn.style.color = 'black';
        resetBtn.disabled = false;
        resetBtn.style.cursor = 'pointer';
        resetBtn.title = 'Reset password to default value';
    }
    
    // Clear stored values
    window.editFormOriginalValues = {};
    window.editFormDefaultPassword = null; // Clear default password
    
    // Disable update button
    const updateBtn = document.getElementById('edit_update_btn');
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.style.background = '#C9C9C9';
        updateBtn.style.color = '#666';
        updateBtn.style.cursor = 'not-allowed';
    }
    
    // Setup form change detection and validation
    setupFormChangeDetection();
    setupEmployeeNumberValidation();
    setupInstitutionalEmail();
}

// Function to open success modal
window.openEditUserSuccessModal = function(message) {
    const modal = document.getElementById('editUserSuccessModal');
    const messageElement = document.getElementById('editUserSuccessMessage');
    
    if (modal && messageElement) {
        if (message) {
            messageElement.textContent = message;
        }
        modal.style.display = 'block';
        
        // Disable body scroll for success modal
        document.body.style.overflow = 'hidden';
    }
};

// Function to close success modal
window.closeEditUserSuccessModal = function() {
    const modal = document.getElementById('editUserSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
};

// Function to open error modal
window.openEditUserErrorModal = function(message) {
    const modal = document.getElementById('editUserErrorModal');
    const messageElement = document.getElementById('editUserErrorMessage');
    
    if (modal && messageElement) {
        if (message) {
            messageElement.textContent = message;
        }
        modal.style.display = 'block';
        
        // Disable body scroll for error modal
        document.body.style.overflow = 'hidden';
    }
};

// Function to close error modal
window.closeEditUserErrorModal = function() {
    const modal = document.getElementById('editUserErrorModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
};

// Function to setup employee number validation
function setupEmployeeNumberValidation() {
    const employeeNoInput = document.getElementById('edit_employee_no');
    
    if (employeeNoInput) {
        // Prevent non-numeric input
        employeeNoInput.addEventListener('keypress', function(e) {
            if (e.charCode < 48 || e.charCode > 57) {
                e.preventDefault();
            }
        });
        
        // Handle input to ensure only numbers and limit to 6 digits
        employeeNoInput.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 6 digits
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
        
        // Handle paste events
        employeeNoInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 6);
            this.value = numericOnly;
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
    }
}

// Function to setup mobile number validation
function setupMobileNumberValidation() {
    const mobileNoInput = document.getElementById('edit_mobile_no');
    
    if (mobileNoInput) {
        
        // Prevent non-numeric input
        mobileNoInput.addEventListener('keypress', function(e) {
            if (e.charCode < 48 || e.charCode > 57) {
                e.preventDefault();
            }
        });
        
        // Handle input to ensure only numbers and limit to 11 digits
        mobileNoInput.addEventListener('input', function() {
            const originalValue = this.value;
            
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 11 digits
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
            
            // Log if value was changed
            if (originalValue !== this.value) {
            }
            
            // Add visual validation feedback
            if (this.value.length === 0) {
                this.classList.remove('valid', 'invalid');
            } else if (this.value.length === 11) {
                this.classList.remove('invalid');
                this.classList.add('valid');
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
            }
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
        
        // Handle paste events
        mobileNoInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 11);
            
            this.value = numericOnly;
            
            // Add visual validation feedback
            if (numericOnly.length === 0) {
                this.classList.remove('valid', 'invalid');
            } else if (numericOnly.length === 11) {
                this.classList.remove('invalid');
                this.classList.add('valid');
            } else {
                this.classList.remove('valid');
                this.classList.add('invalid');
            }
            
            // Trigger form change detection without infinite loop
            updateUpdateButton();
        });
        
        // Add visual feedback for length
        mobileNoInput.addEventListener('blur', function() {
            if (this.value.length > 0 && this.value.length < 11) {
            }
        });
        
    } else {
        console.error('❌ Mobile number input field not found!');
    }
}

// Setup form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    if (form) {
        form.addEventListener('submit', handleEditFormSubmit);
    }
    
    // Setup validation for when modal is opened
    setupEmployeeNumberValidation();
    setupMobileNumberValidation();
    setupInstitutionalEmail();
    
    // Add event delegation for password toggle (in case elements are not immediately available)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('toggle-password') && e.target.getAttribute('data-target') === 'edit_password') {
            e.preventDefault();
            e.stopPropagation();
            
            const passwordInput = document.getElementById('edit_password');
            if (passwordInput) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    e.target.src = '../src/assets/icons/hide_password.png';
                } else {
                    passwordInput.type = 'password';
                    e.target.src = '../src/assets/icons/show_password.png';
                }
                
                // Trigger form change detection
                passwordInput.dispatchEvent(new Event('input'));
            }
        }
    });
    
    // Add event delegation for password input changes
    document.addEventListener('input', function(e) {
        if (e.target.id === 'edit_password') {
            updateUpdateButton();
            // Also update reset button state when password changes
            updateResetButtonState();
        }
    });
    
});

// Function to handle form submission
async function handleEditFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    
    try {
        const response = await fetch('./process_edit_user.php', {
            method: 'POST',
            body: formData
        });
        
        
        const responseText = await response.text();
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('❌ Failed to parse JSON response:', parseError);
            console.error('❌ Raw response that failed to parse:', responseText);
            openEditUserErrorModal('Invalid response from server. Please try again.');
            return;
        }
        
        
        if (data.success === true) {
            openEditUserSuccessModal(data.message || 'User updated successfully!');
                
                // Refresh the user list if the function exists
                if (typeof loadInitialData === 'function') {
                    loadInitialData();
                }
        } else {
            openEditUserErrorModal(data.message || 'Failed to update user. Please try again.');
        }
    } catch (error) {
        console.error('❌ Error updating user:', error);
        openEditUserErrorModal('An error occurred while updating the user. Please try again.');
    }
}

// Cleanup function to prevent memory leaks
window.addEventListener('beforeunload', function() {
    if (window.editModalOutsideClickHandler) {
        document.removeEventListener('click', window.editModalOutsideClickHandler);
        window.editModalOutsideClickHandler = null;
    }
});