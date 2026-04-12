<?php
require_once dirname(__FILE__) . '/../super_admin_session_config.php';
require_once dirname(__FILE__) . '/../bootstrap/auth.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

ascom_require_super_admin('../super_admin_login.php');
secureSuperAdminSession();

// Check if this is an AJAX request
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if ($isAjax) {
    // Return only the content for AJAX requests
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    
    // Start output buffering to capture the content
    ob_start();
    
    switch ($page) {
      case 'user-account-management':
        include './user_account_management-content/user-account-management.php';
        break;

      case 'school-calendar':
        include './school_calendar-content/school-calendar.php';
        break;
        
      case 'settings': 
        include './settings-content/settings.php';
        break;

      case 'dashboard':
      default:
        include './dashboard-content/dashboard.php';
        break;
    }
    
    // Get the captured content
    $content = ob_get_clean();
    
    // Return the content (modals are already included in the main page)
    echo $content;
    exit; // Stop here for AJAX requests
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php if (isset($_SESSION['employee_no'])): ?>
<meta name="current-user" content="<?php echo htmlspecialchars($_SESSION['employee_no']); ?>" />
<?php endif; ?>
<title>Super Admin - Content</title>
<script>
// API base path for notifications - project root (one level up from super_admin-mis)
// Used for absolute path to root /api/ folder
window.NOTIFICATIONS_API_BASE = '<?php echo rtrim(dirname(dirname($_SERVER["SCRIPT_NAME"] ?? "")), "/"); ?>';
</script>

<!-- CLEAN MODAL FUNCTIONS -->
<script>
// Simple, clean modal functions
window.initializeFormEventListeners = function() {
    // Simple implementation - no complex logic
};

// Make function globally available
window.initializeFormEventListeners = window.initializeFormEventListeners;

// Success modal function
function showSuccessModal(message) {
    
    // Remove existing success modal if it exists
    const existingModal = document.getElementById('successModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Create success modal
    const modal = document.createElement('div');
    modal.id = 'successModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    modal.innerHTML = `
        <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
            
            <div style="text-align: center; margin-bottom: 25px;">
                <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 80px; height: 80px; margin-bottom: 15px;">
                <h2 style="margin: 0 0 15px; color: #333; font-size: 22px; font-weight: 700;">Success!</h2>
                <p style="margin: 0; color: #333; font-size: 16px; line-height: 1.5;">${message}</p>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="closeSuccessModal()" style="width: 125px; height: 50px; background-color: #4CAF50; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">OK</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Error modal function
function showErrorModal(message) {
    // Remove existing error modal if it exists
    const existingModal = document.getElementById('errorModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create error modal
    const modal = document.createElement('div');
    modal.id = 'errorModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    modal.innerHTML = `
        <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Error</h2>
                <span onclick="closeErrorModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
            </div>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="width: 80px; height: 80px; background-color: #f44336; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: white; font-size: 40px; font-weight: bold;">✕</span>
                </div>
                <p style="margin: 0; color: #333; font-size: 16px; line-height: 1.5;">${message}</p>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 10px;">
                <button onclick="closeErrorModal()" style="width: 125px; height: 50px; background-color: #f44336; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">OK</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Close success modal function
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

// Close error modal function
function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    if (modal) {
        modal.remove();
    }
}

// Define modal functions immediately so they're available for onclick handlers
window.closeAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    } else {
        console.error('❌ Modal not found for closing');
    }
};

window.closeSuccessModal = function() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    } else {
        console.error('❌ Success modal not found');
    }
};

window.openSuccessModal = function(message) {
    const modal = document.getElementById('successModal');
    const messageElement = document.getElementById('successMessage');
    if (modal && messageElement) {
        messageElement.innerText = message;
        modal.style.display = 'flex';
        // Disable body scroll
        document.body.style.overflow = 'hidden';
    } else {
        console.error('❌ Success modal or message element not found');
    }
};

// Removed orphaned code block that was causing syntax errors

window.handleDepartmentFormSubmit = function(event) {
    event.preventDefault();
    
    // Check if button is disabled
    const createBtn = event.target.querySelector(".create-btn");
    if (createBtn && createBtn.disabled) {
        console.warn('⚠️ Form submission blocked - create button is disabled');
        return;
    }
    
    const formData = new FormData(event.target);
    const departmentCode = formData.get('department_code');
    const departmentName = formData.get('department_name');
    const colorCode = formData.get('color_code');
    
    
    // Submit to backend
    fetch('./process_add_department.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('❌ HTTP error response:', text);
                throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        
        if (data.success) {
            closeAddDepartmentModal();
            openSuccessModal(data.message);
            
            // Reset form
            event.target.reset();
            
            // Update UI if department container exists
            const departmentContainer = document.getElementById('departmentContainer');
            if (departmentContainer && data.department) {
                const newDept = data.department;
                const newCard = document.createElement('div');
                newCard.className = 'department-card';
                newCard.innerHTML = `
                    <div class='dept-code' style='background-color: ${newDept.color}'>${newDept.code}</div>
                    <h3>${newDept.name}</h3>
                    <p><strong>Dean:</strong> ${newDept.dean || 'N/A'}</p>
                    <p><strong>Programs:</strong> ${newDept.programs || 0}</p>
                    <p><strong>Created By:</strong> ${newDept.created_by || 'N/A'}</p>
                `;
                departmentContainer.appendChild(newCard);
                
                // Update department count
                const deptCountElement = document.querySelector('.dashboard-container .box:nth-child(1) .amount');
                if (deptCountElement) {
                    deptCountElement.innerText = parseInt(deptCountElement.innerText) + 1;
                }
                
                // Hide no departments message if it exists
                const noDepartmentsMessage = document.getElementById('noDepartmentsMessage');
                if (noDepartmentsMessage) {
                    noDepartmentsMessage.style.display = 'none';
                }
                
                // Show view all button if needed
                const viewAllBtn = document.querySelector('.view-all-btn');
                if (viewAllBtn) {
                    const currentCards = document.querySelectorAll("#departmentContainer .department-card").length;
                    if (currentCards > 6) {
                        viewAllBtn.style.display = 'block';
                    }
                }
            }
        } else {
            console.error('❌ Department creation failed:', data.message);
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('❌ Fetch error:', error);
        alert('An unexpected error occurred during submission. Check console for details.');
    });
};

window.initializeColorPicker = function() {
    const colorPicker = document.getElementById('colorPicker');
    const colorHex = document.getElementById('colorHex');
    const colorSwatch = document.getElementById('colorSwatchDisplay');
    const clearBtn = document.getElementById('clearColorBtn');
    
    if (colorPicker && colorHex && colorSwatch) {
        // Color picker change
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value.toUpperCase();
            colorSwatch.style.backgroundColor = this.value;
            checkFormValidity();
        });
        
        // Hex input change
        colorHex.addEventListener('input', function() {
            const value = this.value;
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                colorPicker.value = value;
                colorSwatch.style.backgroundColor = value;
            }
            checkFormValidity();
        });
        
        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                colorHex.value = '#4A7DFF';
                colorPicker.value = '#4A7DFF';
                colorSwatch.style.backgroundColor = '#4A7DFF';
                checkFormValidity();
            });
        }
        
    } else {
        console.error('❌ Color picker elements not found');
    }
};

// Function to open the Add Department modal
window.openAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        // Disable body scroll
        document.body.style.overflow = 'hidden';
        
        // Reset form
        const form = document.getElementById('addDepartmentForm');
        if (form) {
            form.reset();
        }
        
        // Initialize color picker and form
        setTimeout(() => {
            const colorPicker = document.getElementById("colorPicker");
            const colorHex = document.getElementById("colorHex");
            const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
            
            if (colorPicker && colorHex && colorSwatchDisplay) {
                const defaultColor = "#4A7DFF";
                colorPicker.value = defaultColor;
                colorHex.value = defaultColor;
                colorSwatchDisplay.style.backgroundColor = defaultColor;
            }
            
            if (typeof initializeColorPicker === 'function') {
                initializeColorPicker();
            }
            
            if (typeof window.checkFormValidity === 'function') {
                window.checkFormValidity();
            }
            
            // Re-initialize event listeners for form validation
            const addDepartmentForm = document.getElementById("addDepartmentForm");
            if (addDepartmentForm) {
                const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));
                requiredFields.forEach(field => {
                    // Add listeners (multiple listeners won't cause issues)
                    field.addEventListener("input", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                    field.addEventListener("change", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                });
                
                // Re-attach color picker listeners
                const colorPicker = document.getElementById("colorPicker");
                const colorHex = document.getElementById("colorHex");
                
                if (colorPicker) {
                    colorPicker.addEventListener("input", function() {
                        if (colorHex) {
                            colorHex.value = this.value.toUpperCase();
                        }
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                }
                
                if (colorHex) {
                    colorHex.addEventListener("input", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                }
            }
        }, 100);
    } else {
        console.error('❌ Modal not found');
    }
};


// Account Access Management Functions
window.openLibrarianAccessModal = function() {
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Create librarian access modal
    const modal = document.createElement('div');
    modal.id = 'librarianAccessModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    modal.innerHTML = `
        <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 95%; max-width: 1000px; max-height: 85vh; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px; flex-shrink: 0;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Assign Librarian Access</h2>
                <span onclick="closeLibrarianAccessModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer; transition: color 0.2s;">&times;</span>
            </div>
            
            <div style="display: flex; flex: 1; gap: 25px; min-height: 0;">
                <!-- Left Side - Description and Current Access -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="background: white; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px; font-weight: 600;">Librarian Access</h3>
                        <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;">
                            Librarians can manage library books, catalogs, and resource tracking. They have full access to the library management system for processing loans, returns, and generating reports.
                        </p>
                        <div style="margin-top: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00674b;">
                            <p style="margin: 0; color: #555; font-size: 12px; font-style: italic;">
                                <strong>Note:</strong> The user list is filtered to show only those with Teacher access who are available for role assignment.
                            </p>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0; flex: 1;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px; font-weight: 600;">Current Librarian Access</h3>
                        <div id="currentLibrarianAccess" style="color: #666; font-size: 13px;">
                            <p style="margin: 0 0 10px 0;">No librarian access currently assigned.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Search and User List -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 8px; display: block;">Search Users:</label>
                        <input type="text" id="librarianSearchInput" placeholder="Search by name, email, or employee number..." style="width: 100%; height: 45px; padding: 0 15px; border: 1px solid #ccc; border-radius: 10px; box-sizing: border-box; background-color: #FFFFFF; font-size: 14px;">
                    </div>
                    
                    <div id="librarianUsersList" style="flex: 1; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 10px; background: white; min-height: 300px;">
                        <!-- Users will be loaded here -->
                    </div>
                </div>
            </div>
            

        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Load users for selection
    loadUsersForLibrarianAccess();
    loadCurrentLibrarianAccess();
    
    // Add search functionality
    const searchInput = document.getElementById('librarianSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterLibrarianUsers(this.value);
        });
    }
};

window.closeLibrarianAccessModal = function() {
    const modal = document.getElementById('librarianAccessModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
};

window.openQualityAssuranceAccessModal = function() {
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Create QA access modal
    const modal = document.createElement('div');
    modal.id = 'qualityAssuranceAccessModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    modal.innerHTML = `
        <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 95%; max-width: 1000px; max-height: 85vh; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px; flex-shrink: 0;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Assign Quality Assurance Access</h2>
                <span onclick="closeQualityAssuranceAccessModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer; transition: color 0.2s;">&times;</span>
            </div>
            
            <div style="display: flex; flex: 1; gap: 25px; min-height: 0;">
                <!-- Left Side - Description and Current Access -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="background: white; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px; font-weight: 600;">Quality Assurance Access</h3>
                        <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;">
                            Quality Assurance users can monitor academic standards, compliance, and quality assurance processes. They have access to review academic procedures and generate compliance reports.
                        </p>
                        <div style="margin-top: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00674b;">
                            <p style="margin: 0; color: #555; font-size: 12px; font-style: italic;">
                                <strong>Note:</strong> The user list is filtered to show only those with Teacher access who are available for role assignment.
                            </p>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0; flex: 1;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px; font-weight: 600;">Current QA Access</h3>
                        <div id="currentQAAccess" style="color: #666; font-size: 13px;">
                            <p style="margin: 0 0 10px 0;">No Quality Assurance access currently assigned.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Search and User List -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 8px; display: block;">Search Users:</label>
                        <input type="text" id="qaSearchInput" placeholder="Search by name, email, or employee number..." style="width: 100%; height: 45px; padding: 0 15px; border: 1px solid #ccc; border-radius: 10px; box-sizing: border-box; background-color: #FFFFFF; font-size: 14px;">
                    </div>
                    
                    <div id="qaUsersList" style="flex: 1; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 10px; background: white; min-height: 300px;">
                        <!-- Users will be loaded here -->
                    </div>
                </div>
            </div>
            

        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Load users for selection
    loadUsersForQAAccess();
    loadCurrentQAAccess();
    
    // Add search functionality
    const searchInput = document.getElementById('qaSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterQAUsers(this.value);
        });
    }
};

window.closeQualityAssuranceAccessModal = function() {
    const modal = document.getElementById('qualityAssuranceAccessModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
};

// Helper functions for loading users
function loadUsersForLibrarianAccess() {
    const usersList = document.getElementById('librarianUsersList');
    if (!usersList) return;
    
    // Show loading state
    usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Loading users...</div>';
    
    // Fetch users from API
    fetch('api/get_available_users.php?role_type=librarian')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLibrarianUsers(data.users);
            } else {
                console.error('❌ Failed to load users:', data.message);
                usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Failed to load users. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('❌ Error loading users:', error);
            usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Error loading users. Please try again.</div>';
        });
}

function loadUsersForQAAccess() {
    const usersList = document.getElementById('qaUsersList');
    if (!usersList) return;
    
    // Show loading state
    usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Loading users...</div>';
    
    // Fetch users from API
    fetch('api/get_available_users.php?role_type=quality_assurance')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayQAUsers(data.users);
            } else {
                console.error('❌ Failed to load users:', data.message);
                usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Failed to load users. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('❌ Error loading users:', error);
            usersList.innerHTML = '<div style="padding: 20px; text-align: center; color: #666;">Error loading users. Please try again.</div>';
        });
}

// Display functions for user lists
function displayLibrarianUsers(users) {
    const usersList = document.getElementById('librarianUsersList');
    if (!usersList) return;
    
    usersList.innerHTML = users.map(user => `
        <div class="user-list-item" data-user-id="${user.id}" style="padding: 15px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.2s;">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${user.name}</div>
                <div style="font-size: 12px; color: #666; margin-bottom: 2px;">${user.email}</div>
                <div style="font-size: 12px; color: #888;">${user.employee_no} • ${user.role} • ${user.department}</div>
            </div>
            <button onclick="assignLibrarianAccessToUser(${user.id}, '${user.name}')" style="padding: 8px 16px; background: #00674b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;">
                Assign Access
            </button>
        </div>
    `).join('');
}

function displayQAUsers(users) {
    const usersList = document.getElementById('qaUsersList');
    if (!usersList) return;
    
    usersList.innerHTML = users.map(user => `
        <div class="user-list-item" data-user-id="${user.id}" style="padding: 15px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.2s;">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${user.name}</div>
                <div style="font-size: 12px; color: #666; margin-bottom: 2px;">${user.email}</div>
                <div style="font-size: 12px; color: #888;">${user.employee_no} • ${user.role} • ${user.department}</div>
            </div>
            <button onclick="assignQAAccessToUser(${user.id}, '${user.name}')" style="padding: 8px 16px; background: #00674b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: all 0.2s;">
                Assign Access
            </button>
        </div>
    `).join('');
}

// Filter functions for search
function filterLibrarianUsers(searchTerm) {
    const userItems = document.querySelectorAll('#librarianUsersList .user-list-item');
    const searchLower = searchTerm.toLowerCase();
    
    userItems.forEach(item => {
        const userText = item.textContent.toLowerCase();
        if (userText.includes(searchLower)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterQAUsers(searchTerm) {
    const userItems = document.querySelectorAll('#qaUsersList .user-list-item');
    const searchLower = searchTerm.toLowerCase();
    
    userItems.forEach(item => {
        const userText = item.textContent.toLowerCase();
        if (userText.includes(searchLower)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Assignment functions for individual users
window.assignLibrarianAccessToUser = function(userId, userName) {
    
    // Show loading state on the button
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Assigning...';
    button.disabled = true;
    
    // Make API call to assign librarian role
    fetch('api/assign_librarian_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal(data.message);
            // Refresh current access display
            loadCurrentLibrarianAccess();
            // Refresh available users list
            loadUsersForLibrarianAccess();
        } else {
            showErrorModal(data.message || 'Failed to assign librarian access');
            // Reset button
            button.textContent = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Error assigning librarian access:', error);
        showErrorModal('Network error occurred. Please try again.');
        // Reset button
        button.textContent = originalText;
        button.disabled = false;
    });
};

window.assignQAAccessToUser = function(userId, userName) {
    
    // Show loading state on the button
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Assigning...';
    button.disabled = true;
    
    // Make API call to assign QA role
    fetch('api/assign_qa_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessModal(data.message);
            // Refresh current access display
            loadCurrentQAAccess();
            // Refresh available users list
            loadUsersForQAAccess();
        } else {
            showErrorModal(data.message || 'Failed to assign QA access');
            // Reset button
            button.textContent = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Error assigning QA access:', error);
        showErrorModal('Network error occurred. Please try again.');
        // Reset button
        button.textContent = originalText;
        button.disabled = false;
    });
};
</script>

<!-- Load dashboard.js immediately to avoid syntax errors -->

<!-- Department Details Modal Functions -->
<script>
// Global variable to store current department data
let currentDepartmentData = null;

// Function to open department details modal
window.openDepartmentDetailsModal = function(deptIndex) {
    
    // Get department data from the PHP array (we'll need to make this available globally)
    const deptCards = document.querySelectorAll('.department-card');
    if (deptIndex >= 0 && deptIndex < deptCards.length) {
        const deptCard = deptCards[deptIndex];
        
        // Extract department info from the card
        const deptCode = deptCard.querySelector('.dept-code').textContent;
        const deptName = deptCard.querySelector('h3').textContent;
        const deanName = deptCard.querySelector('.dean-indicator strong').nextSibling.textContent.trim();
        const staffCount = deptCard.querySelector('.staff-row-only span').textContent.split(':')[1].trim();
        
        // Store current department data
        currentDepartmentData = {
            index: deptIndex,
            code: deptCode,
            name: deptName,
            dean: deanName,
            staffCount: staffCount
        };
        
        // Populate modal with department info
        document.getElementById('deptCode').textContent = deptCode;
        document.getElementById('deptName').textContent = deptName;
        
        // Convert RGB color to hex format
        const backgroundColor = deptCard.querySelector('.dept-code').style.backgroundColor;
        let hexColor = 'N/A';
        if (backgroundColor) {
            // Convert RGB to hex
            const rgbMatch = backgroundColor.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
            if (rgbMatch) {
                const r = parseInt(rgbMatch[1]);
                const g = parseInt(rgbMatch[2]);
                const b = parseInt(rgbMatch[3]);
                hexColor = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
            } else {
                hexColor = backgroundColor; // If it's already hex or other format
            }
        }
        document.getElementById('deptColor').textContent = hexColor;
        
        document.getElementById('deptStaffCount').textContent = staffCount;
        
        // Load current dean with title
        loadCurrentDean(deptCode);
        
        // Load department teachers
        loadDepartmentTeachers(deptCode);
        
        // Show modal
        document.getElementById('departmentDetailsModal').style.display = 'flex';
        // Disable body scroll
        document.body.style.overflow = 'hidden';
        
        // Setup search input event listeners after modal is shown
        setTimeout(() => {
            if (typeof window.setupTeacherSearchInput === 'function') {
                window.setupTeacherSearchInput();
            } else {
                console.error('❌ setupTeacherSearchInput function not found');
            }
        }, 100);
    }
};

// Function to setup search input event listeners
window.setupTeacherSearchInput = function() {
    const teacherSearchInput = document.getElementById('teacherSearchInput');
    if (teacherSearchInput) {
        // Remove existing listeners to prevent duplicates
        teacherSearchInput.removeEventListener('input', window.filterTeachers);
        teacherSearchInput.removeEventListener('focus', window.handleSearchFocus);
        teacherSearchInput.removeEventListener('blur', window.handleSearchBlur);
        
        // Add event listener for teacher search input
        teacherSearchInput.addEventListener('input', function() {
            window.filterTeachers();
        });
        
        // Add focus effects
        teacherSearchInput.addEventListener('focus', function() {
            this.style.borderColor = '#739AFF';
            this.style.boxShadow = '0 0 0 3px rgba(115, 154, 255, 0.1)';
        });
        
        teacherSearchInput.addEventListener('blur', function() {
            this.style.borderColor = '#ddd';
            this.style.boxShadow = 'none';
        });
        
    } else {
    }
};

// Function to close department details modal
window.closeDepartmentDetailsModal = function() {
    document.getElementById('departmentDetailsModal').style.display = 'none';
    // Re-enable body scroll
    document.body.style.overflow = 'auto';
    currentDepartmentData = null;
};

// Function to load current dean with title
function loadCurrentDean(deptCode) {
    
    fetch(`./api/get_current_dean.php?dept_code=${encodeURIComponent(deptCode)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            
            if (data.success) {
                document.getElementById('currentDean').textContent = data.dean_name;
            } else {
                document.getElementById('currentDean').textContent = 'Error loading dean';
            }
        })
        .catch(error => {
            console.error('Error loading current dean:', error);
            document.getElementById('currentDean').textContent = 'Error loading dean';
        });
}

// Function to load department teachers
function loadDepartmentTeachers(deptCode) {
    
    // Store the department code for later use
    window.currentDepartmentCode = deptCode;
    
    // Fetch teachers from this department
    fetch(`./api/get_department_teachers.php?dept_code=${encodeURIComponent(deptCode)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            
            // Store teachers data globally for search functionality
            window.allTeachers = data.success ? data.teachers : [];
            
            
            // Display teachers
            displayFilteredTeachers(window.allTeachers);
        })
        .catch(error => {
            console.error('Error loading teachers:', error);
            document.getElementById('deptTeachersList').innerHTML = '<p style="color: #ff6b6b; text-align: center; font-size: 13px;">Error loading teachers. Please try again.</p>';
        });
}

// Function to display filtered teachers
function displayFilteredTeachers(teachers) {
    const teachersList = document.getElementById('deptTeachersList');
    const searchInput = document.getElementById('teacherSearchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    
    // Clear existing content
    teachersList.innerHTML = '';
    
    if (teachers.length > 0) {
        // Populate teachers list
        teachers.forEach((teacher, index) => {
            const teacherDiv = document.createElement('div');
            teacherDiv.className = 'teacher-item';
            
            // Add extra margin to the last item
            const isLastItem = index === teachers.length - 1;
            const bottomMargin = isLastItem ? '20px' : '10px';
            
            teacherDiv.style.cssText = `
                padding: 12px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                margin-bottom: ${bottomMargin};
                background-color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: all 0.3s ease;
                opacity: 0;
                transform: translateY(10px);
            `;
            
            // Use regular text without highlighting
            const teacherName = `${teacher.first_name} ${teacher.last_name}`;
            const employeeNo = teacher.employee_no;
            const email = teacher.institutional_email;
            
            // Format the name with title if available
            const displayName = teacher.title ? `${teacher.title} ${teacherName}` : teacherName;
            
            const teacherInfo = `
                <div>
                    <strong style="color: #333; font-size: 14px;">${displayName}</strong><br>
                    <small style="color: #666; font-size: 12px;">Employee No: ${employeeNo}</small><br>
                    <small style="color: #666; font-size: 12px;">Email: ${email}</small>
                </div>
            `;
            
            const assignButton = isDeanAssignmentMode ? `
                <button type="button" 
                        onclick="assignTeacherAsDean('${teacher.id}', '${displayName}')"
                        style="background-color: #739AFF; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                    Assign as Dean
                </button>
            ` : '';
            
            teacherDiv.innerHTML = teacherInfo + assignButton;
            teachersList.appendChild(teacherDiv);
            
            // Animate the card appearance
            setTimeout(() => {
                teacherDiv.style.opacity = '1';
                teacherDiv.style.transform = 'translateY(0)';
            }, 50);
        });
    } else {
        const searchInput = document.getElementById('teacherSearchInput');
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        
        if (searchTerm) {
            // No search results
            teachersList.innerHTML = `
                <div style="text-align: center; padding: 30px; color: #777;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🔍</div>
                    <p style="font-size: 14px; margin: 0;">No teachers found matching "${searchTerm}"</p>
                    <p style="font-size: 12px; margin: 5px 0 0 0; color: #999;">Try searching by name, employee number, or email</p>
                </div>
            `;
        } else {
            // No teachers in department
            teachersList.innerHTML = `
                <div style="text-align: center; padding: 30px; color: #777;">
                    <div style="font-size: 48px; margin-bottom: 10px;">👥</div>
                    <p style="font-size: 14px; margin: 0;">No teachers found in this department</p>
                    <p style="font-size: 12px; margin: 5px 0 0 0; color: #999;">Teachers will appear here when they are added to this department</p>
                </div>
            `;
        }
    }
}

// Function to filter teachers based on search input
window.filterTeachers = function() {
    const searchInput = document.getElementById('teacherSearchInput');
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    
    
    if (!window.allTeachers) {
        return;
    }
    
    const filteredTeachers = window.allTeachers.filter(teacher => {
        const fullName = `${teacher.first_name} ${teacher.last_name}`.toLowerCase();
        const employeeNo = teacher.employee_no.toLowerCase();
        const email = teacher.institutional_email.toLowerCase();
        
        const matches = fullName.includes(searchTerm) || 
                       employeeNo.includes(searchTerm) || 
                       email.includes(searchTerm);
        
        
        return matches;
    });
    
    displayFilteredTeachers(filteredTeachers);
};

// Global variables for dean assignment
let isDeanAssignmentMode = false;
let selectedTeacherForDean = null;

// Function to toggle dean assignment mode
window.toggleDeanAssignmentMode = function() {
    isDeanAssignmentMode = !isDeanAssignmentMode;
    const assignBtn = document.getElementById('assignNewDeanBtn');
    
    if (isDeanAssignmentMode) {
        assignBtn.textContent = 'Cancel Assignment Mode';
        assignBtn.style.backgroundColor = '#FF6B6B';
    } else {
        assignBtn.textContent = 'Assign a New Dean';
        assignBtn.style.backgroundColor = '#739AFF';
    }
    
    // Refresh the teachers list to show/hide assign buttons
    if (window.allTeachers) {
        displayFilteredTeachers(window.allTeachers);
    }
};

// Function to assign a specific teacher as dean
window.assignTeacherAsDean = function(teacherId, teacherName) {
    if (!isDeanAssignmentMode) {
        alert('Please click "Assign a New Dean" first to enter assignment mode.');
        return;
    }
    
    if (!currentDepartmentData) {
        alert('No department data available.');
        return;
    }
    
    // Store selected teacher data
    selectedTeacherForDean = {
        id: teacherId,
        name: teacherName,
        department: currentDepartmentData.name
    };
    
    // Show confirmation modal
    document.getElementById('selectedTeacherName').textContent = teacherName;
    document.getElementById('selectedDepartmentName').textContent = currentDepartmentData.name;
    document.getElementById('assignDeanConfirmationModal').style.display = 'flex';
    // Disable body scroll
    document.body.style.overflow = 'hidden';
};

// Function to confirm dean assignment
window.confirmAssignDean = function() {
    if (!selectedTeacherForDean || !currentDepartmentData) {
        alert('No teacher selected for dean assignment.');
        return;
    }
    
    
    const requestData = {
        department_code: currentDepartmentData.code,
        teacher_id: selectedTeacherForDean.id
    };
    
    
    // Send assignment request
    fetch('api/assign_department_dean.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload current dean display with updated title
            loadCurrentDean(currentDepartmentData.code);
            
            // Show success modal
            document.getElementById('deanSuccessMessage').textContent = `Dean assigned successfully! ${selectedTeacherForDean.name} is now the dean of ${currentDepartmentData.name}.`;
            document.getElementById('deanSuccessModal').style.display = 'flex';
            // Disable body scroll
            document.body.style.overflow = 'hidden';
            
            // Exit assignment mode
            toggleDeanAssignmentMode();
            
            // Close confirmation modal
            closeAssignDeanConfirmationModal();
            
            // Reset selected teacher
            selectedTeacherForDean = null;
        } else {
            console.error('❌ API Error:', data);
            alert('Error assigning dean: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('❌ Error assigning dean:', error);
        console.error('❌ Error details:', {
            message: error.message,
            stack: error.stack
        });
        alert('Error assigning dean. Please try again. Check console for details.');
    });
};

// Function to close assign dean confirmation modal
window.closeAssignDeanConfirmationModal = function() {
    document.getElementById('assignDeanConfirmationModal').style.display = 'none';
    // Re-enable body scroll
    document.body.style.overflow = 'auto';
    selectedTeacherForDean = null;
};

// Function to remove department dean
window.removeDepartmentDean = function() {
    if (!currentDepartmentData) {
        alert('No department data available.');
        return;
    }
    
    if (document.getElementById('currentDean').textContent === 'No dean assigned') {
        alert('No dean is currently assigned to this department.');
        return;
    }
    
    if (!confirm('Are you sure you want to remove the current dean from this department?')) {
        return;
    }
    
    
    // Send removal request
    fetch('api/remove_department_dean.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            department_code: currentDepartmentData.code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload current dean display
            loadCurrentDean(currentDepartmentData.code);
            
            // Show success modal
            document.getElementById('deanSuccessMessage').textContent = `Dean removed successfully from ${currentDepartmentData.name}.`;
            document.getElementById('deanSuccessModal').style.display = 'flex';
        } else {
            alert('Error removing dean: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error removing dean:', error);
        alert('Error removing dean. Please try again.');
    });
};

// Function to close dean success modal
window.closeDeanSuccessModal = function() {
    document.getElementById('deanSuccessModal').style.display = 'none';
    // Re-enable body scroll
    document.body.style.overflow = 'auto';
};
</script>

<link rel="stylesheet" href="./styles/global.css?v=1.0">

<!-- Page-specific CSS files (load first) -->
<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
switch ($page) {
    case 'dashboard':
        echo '<link rel="stylesheet" href="./styles/dashboard.css?v=1.0">';
        break;
    case 'user-account-management':
        echo '<link rel="stylesheet" href="./styles/user-account-management.css?v=1.0">';
        break;
    case 'school-calendar':
        echo '<link rel="stylesheet" href="./styles/school-calendar.css?v=1.0">';
        break;
    case 'settings':
        echo '<link rel="stylesheet" href="./styles/settings.css?v=1.0">';
        break;
    default:
        echo '<link rel="stylesheet" href="./styles/dashboard.css?v=1.0">';
        break;
}
?>

<!-- Modal CSS files (load last for higher priority) -->
<link rel="stylesheet" href="./styles/modal-add-user.css?v=1.0">
<link rel="stylesheet" href="./styles/modal-add-department.css?v=1.0">
<link rel="stylesheet" href="./styles/modals.css?v=1.0">
<link rel="stylesheet" href="./styles/notifications.css?v=1.0">

<script>
// Core functionality only
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            if (sidebar.classList.contains('collapsed')) {
                contentWrapper.style.marginLeft = '115px';
            } else {
                contentWrapper.style.marginLeft = '298px';
            }
        }
    }
}

function openAddUserModal() {
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    if (typeof createCompleteModal === 'function') {
        createCompleteModal();
    } else {
        console.error('❌ createCompleteModal function not found');
        // Fallback: create a simple modal
        const modal = document.createElement('div');
        modal.id = 'fallbackUserModal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        `;
        modal.innerHTML = `
            <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Create Teacher Account</h2>
                    <span onclick="closeFallbackModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
                </div>
                <p>Modal is working! This is a fallback modal.</p>
                <button onclick="closeFallbackModal()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

function closeAddUserModal() {
    // Re-enable body scroll
    document.body.style.overflow = 'auto';
    
    if (typeof closeCompleteModal === 'function') {
        closeCompleteModal();
    } else {
        closeFallbackModal();
    }
}

function closeAddUserModalBackup() {
    const backupModal = document.getElementById('addUserModalBackup');
    if (backupModal) {
        backupModal.style.display = 'none';
    }
}

function closeAddUserSuccessModal() {
    const modal = document.getElementById('addUserSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function closeAddUserErrorModal() {
    const modal = document.getElementById('addUserErrorModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function closeAddUserWarningModal() {
    const modal = document.getElementById('addUserWarningModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function closeFallbackModal() {
    const modal = document.getElementById('fallbackUserModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

// Define createCompleteModal early so it's available immediately
function createCompleteModal() {
    // Remove existing complete modal if it exists
    const existingModal = document.getElementById('completeUserModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Create the complete modal with all fields
    const modal = document.createElement('div');
    modal.id = 'completeUserModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    // Create modal with placeholder for departments
    modal.innerHTML = `
        <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); animation: fadeIn 0.3s; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Create Teacher Account</h2>
                <span onclick="closeCompleteModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer; transition: color 0.2s;">&times;</span>
            </div>
            
            <form id="addUserForm" style="display: flex; flex-direction: column; gap: 15px;">
                <!-- Hidden role field - always set to Teacher -->
                <input type="hidden" name="role_id" value="4">
                
                <!-- Row 1: Employee No. & Department -->
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Employee No.</label>
                        <input type="number" name="employee_no" id="employee_no" required maxlength="6" min="100000" max="999999" placeholder="6-digit number" autocomplete="off" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Department</label>
                        <select name="department_id" id="department_id" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                            <option value="">Loading departments...</option>
                        </select>
                    </div>
                </div>
                
                <!-- Row 2: First Name & Middle Name -->
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 2.5;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">First Name</label>
                        <input type="text" name="first_name" id="first_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                    </div>
                    <div style="flex: 1.2;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" id="middle_name" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                    </div>
                </div>
                
                <!-- Row 3: Last Name & Name Prefix/Title -->
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 2.5;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                    </div>
                    <div style="flex: 1.2;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Name Prefix / Title</label>
                        <select name="title" id="title" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                            <option value="">--</option>
                            <option>Mr.</option>
                            <option>Mrs.</option>
                            <option>Ms.</option>
                            <option>Dr.</option>
                            <option>Prof.</option>
                        </select>
                    </div>
                </div>
                
                <!-- Row 4: Institutional Email & Mobile Number -->
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 2.5;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">
                            Institutional Email 
                            <span style="color: #666; font-size: 12px;">@sccpag.edu.ph</span>
                            <span style="color: #007bff; cursor: help;" title="This domain is automatically added and cannot be changed">ℹ️</span>
                        </label>
                        <div style="position: relative;">
                            <input type="email" name="institutional_email" id="institutional_email" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                            <button type="button" id="clear_email_btn" title="Clear field" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #999;">✕</button>
                        </div>
                    </div>
                    <div style="flex: 1.2;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Mobile Number</label>
                        <input type="text" name="mobile_no" id="mobile_no" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                    </div>
                </div>
                
                <!-- Row 5: Password & Generate Default Password -->
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" required style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">&nbsp;</label>
                        <button type="button" id="password_action_btn" style="height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase; width: 100%; padding: 10px 20px; box-sizing: border-box;">Generate Default Password</button>
                    </div>
                </div>
                
                <!-- Row 6: Confirm Password (Hidden by default) -->
                <div id="confirm_password_group" style="display: none;">
                    <div style="flex: 1;">
                        <label style="font-size: 14px; font-weight: bold; margin-bottom: 6px; display: block;">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" name="confirm_password" id="confirm_password" style="width: 100%; height: 50px; padding: 0 12px; border: 1px solid #ccc; border-radius: 12px; box-sizing: border-box; background-color: #FFFFFF;">
                            <img src="../src/assets/icons/show_password.png" class="toggle-password" data-target="confirm_password" alt="Show/Hide Password" style="position: absolute; right: 12px; top: 25px; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px; filter: invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%) !important;">
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeCompleteModal()" style="width: 125px; height: 50px; background-color: #C9C9C9; color: black; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: bold; text-transform: uppercase;">CANCEL</button>
                    <button type="submit" id="create_btn" disabled style="width: 125px; height: 50px; background-color: #C9C9C9; color: #666; border: none; border-radius: 10px; cursor: not-allowed; font-size: 14px; font-weight: bold; text-transform: uppercase;">CREATE</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Apply dark mode styles if dark theme is active
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    if (currentTheme === 'dark') {
        applyModalDarkMode();
    }
    
    // Load departments from database and initialize modal functionality
    loadDepartmentsAndInitialize();
    
    // Initialize form event listeners with a delay to ensure elements are ready
    setTimeout(() => {
        initializeFormEventListeners();
    }, 500);
}

// Function to apply dark mode styles to the modal
function applyModalDarkMode() {
    const modal = document.getElementById('completeUserModal');
    if (!modal) return;
    
    const modalBox = modal.querySelector('div');
    if (modalBox) {
        modalBox.style.backgroundColor = '#252525';
        modalBox.style.color = '#e0e0e0';
        modalBox.style.borderColor = '#404040';
    }
    
    // Style header
    const header = modalBox?.querySelector('div:first-child');
    if (header) {
        header.style.borderBottomColor = '#404040';
        
        const h2 = header.querySelector('h2');
        if (h2) {
            h2.style.color = '#e0e0e0';
        }
        
        const closeBtn = header.querySelector('span');
        if (closeBtn) {
            closeBtn.style.color = '#e0e0e0';
        }
    }
    
    // Style all labels
    const labels = modal.querySelectorAll('label');
    labels.forEach(label => {
        label.style.color = '#e0e0e0';
    });
    
    // Style all inputs and selects
    const inputs = modal.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type !== 'hidden' && input.type !== 'submit' && input.type !== 'button') {
            input.style.backgroundColor = '#2d2d2d';
            input.style.color = '#e0e0e0';
            input.style.borderColor = '#404040';
        }
    });
    
    // Style buttons (except the create button which keeps green)
    const buttons = modal.querySelectorAll('button');
    buttons.forEach(button => {
        const bgColor = button.style.backgroundColor || window.getComputedStyle(button).backgroundColor;
        if (bgColor === 'rgb(201, 201, 201)' || bgColor === '#C9C9C9') {
            // Cancel or Generate Default Password button
            button.style.backgroundColor = '#2d2d2d';
            button.style.color = '#e0e0e0';
            button.style.borderColor = '#404040';
        } else if (bgColor === 'rgb(15, 122, 83)' || bgColor === '#0f7a53') {
            // Create button - keep green
            button.style.backgroundColor = '#0f7a53';
            button.style.color = 'white';
        }
    });
    
    // Style clear button and info icon
    const clearBtn = modal.querySelector('#clear_email_btn');
    if (clearBtn) {
        clearBtn.style.color = '#e0e0e0';
    }
    
    const infoSpan = modal.querySelector('span[title*="domain"]');
    if (infoSpan) {
        // Keep info icon blue, just adjust if needed
    }
    
    // Style toggle password icons to be white in dark mode
    const toggleIcons = modal.querySelectorAll('.toggle-password');
    toggleIcons.forEach(icon => {
        icon.style.setProperty('filter', 'invert(100%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(100%) contrast(100%)', 'important');
    });
}

function closeCompleteModal() {
    const modal = document.getElementById('completeUserModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function loadDepartmentsAndInitialize() {
    // Fetch departments from real database
    fetch('./api/get_departments.php')
        .then(response => response.json())
        .then(data => {
            
            if (data.success && data.departments.length > 0) {
                // Populate department dropdown with real data
                const departmentSelect = document.getElementById('department_id');
                if (departmentSelect) {
                    departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                    data.departments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.code;
                        departmentSelect.appendChild(option);
                    });
                }
            } else {
                console.error('No departments found or error occurred');
                // Use fallback data
                const fallbackDepartments = getDepartmentsFromDatabase();
                const departmentSelect = document.getElementById('department_id');
                if (departmentSelect) {
                    departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                    fallbackDepartments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.code;
                        departmentSelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
            // Use fallback data
            const fallbackDepartments = getDepartmentsFromDatabase();
            const departmentSelect = document.getElementById('department_id');
            if (departmentSelect) {
                departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                fallbackDepartments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.code;
                    departmentSelect.appendChild(option);
                });
            }
        })
        .finally(() => {
            // Initialize modal functionality after departments are loaded
            initializeModalFunctions();
        });
}

// Function to get departments from database (simulated)
function getDepartmentsFromDatabase() {
    // This would normally fetch from the database
    // For now, returning sample data
    return [
        { id: 1, code: 'CS', name: 'Computer Science' },
        { id: 2, code: 'IT', name: 'Information Technology' },
        { id: 3, code: 'BA', name: 'Business Administration' },
        { id: 4, code: 'ENG', name: 'Engineering' },
        { id: 5, code: 'EDU', name: 'Education' },
        { id: 6, code: 'NURS', name: 'Nursing' },
        { id: 7, code: 'ARTS', name: 'Arts and Sciences' }
    ];
}

function createDynamicModal() {
    // Remove existing dynamic modal if it exists
    const existingModal = document.getElementById('dynamicModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal dynamically
    const modal = document.createElement('div');
    modal.id = 'dynamicModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 8px; min-width: 400px; position: relative;">
            <span onclick="closeDynamicModal()" style="position: absolute; top: 10px; right: 15px; cursor: pointer; font-size: 24px;">&times;</span>
            <h2>Dynamic Modal Created!</h2>
            <p>This modal was created dynamically by JavaScript.</p>
            <button onclick="closeDynamicModal()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeDynamicModal() {
    const modal = document.getElementById('dynamicModal');
    if (modal) {
        modal.remove();
    }
}

// Removed duplicate createCompleteModal function - using the one defined earlier

function loadDepartmentsAndInitialize() {
    // Fetch departments from real database
    fetch('./api/get_departments.php')
        .then(response => response.json())
        .then(data => {
            
            if (data.success && data.departments.length > 0) {
                // Populate department dropdown with real data
                const departmentSelect = document.getElementById('department_id');
                if (departmentSelect) {
                    departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                    data.departments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.code;
                        departmentSelect.appendChild(option);
                    });
                }
            } else {
                console.error('No departments found or error occurred');
                // Use fallback data
                const fallbackDepartments = getDepartmentsFromDatabase();
                const departmentSelect = document.getElementById('department_id');
                if (departmentSelect) {
                    departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                    fallbackDepartments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.code;
                        departmentSelect.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading departments:', error);
            // Use fallback data
            const fallbackDepartments = getDepartmentsFromDatabase();
            const departmentSelect = document.getElementById('department_id');
            if (departmentSelect) {
                departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
                fallbackDepartments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.code;
                    departmentSelect.appendChild(option);
                });
            }
        })
        .finally(() => {
            // Initialize modal functionality after departments are loaded
            initializeModalFunctions();
        });
}

// Function to get departments from database (simulated)
function getDepartmentsFromDatabase() {
    // This would normally fetch from the database
    // For now, returning sample data
    return [
        { id: 1, code: 'CS', name: 'Computer Science' },
        { id: 2, code: 'IT', name: 'Information Technology' },
        { id: 3, code: 'BA', name: 'Business Administration' },
        { id: 4, code: 'ENG', name: 'Engineering' },
        { id: 5, code: 'EDU', name: 'Education' },
        { id: 6, code: 'NURS', name: 'Nursing' },
        { id: 7, code: 'ARTS', name: 'Arts and Sciences' }
    ];
}

function initializeModalFunctions() {
    // Employee No. validation (6 digits only)
    const employeeNo = document.getElementById('employee_no');
    if (employeeNo) {
        employeeNo.addEventListener('input', function() {
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
            validateForm();
        });
    }
    
    // Role selection validation
    const roleSelect = document.getElementById('role_id');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            validateForm();
        });
    }
    
    // Department selection validation
    const departmentSelect = document.getElementById('department_id');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            validateForm();
        });
    }
    
    // First name validation
    const firstName = document.getElementById('first_name');
    if (firstName) {
        firstName.addEventListener('input', function() {
            validateForm();
        });
    }
    
    // Last name validation
    const lastName = document.getElementById('last_name');
    if (lastName) {
        lastName.addEventListener('input', function() {
            validateForm();
        });
    }
    
    // Email validation
    const emailInput = document.getElementById('institutional_email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            validateForm();
        });
        
        // Auto-fill domain when @ is typed
        emailInput.addEventListener('keydown', function(e) {
            if (e.key === '@') {
                // Prevent the @ from being added normally
                e.preventDefault();
                
                // Get current value and cursor position
                const currentValue = this.value;
                const cursorPos = this.selectionStart;
                
                // Insert @sccpag.edu.ph at the end of current text
                const newValue = currentValue + '@sccpag.edu.ph';
                this.value = newValue;
                
                // Set cursor position at the end
                this.setSelectionRange(newValue.length, newValue.length);
                
                validateForm();
            }
        });
        
        // Also handle paste events
        emailInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                const value = this.value;
                if (value.includes('@') && !value.includes('@sccpag.edu.ph')) {
                    // Replace any @ with @sccpag.edu.ph
                    this.value = value.replace(/@[^@]*$/, '@sccpag.edu.ph');
                }
                validateForm();
            }, 0);
        });
    }
    
    // Password validation
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validateForm();
        });
    }
    
    // Clear email button
    const clearEmailBtn = document.getElementById('clear_email_btn');
    if (clearEmailBtn && emailInput) {
        clearEmailBtn.addEventListener('click', function() {
            emailInput.value = '';
            validateForm();
        });
    }
    
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.src = '../src/assets/icons/hide_password.png';
                } else {
                    targetInput.type = 'password';
                    this.src = '../src/assets/icons/show_password.png';
                }
                // Reapply dark mode filter if dark theme is active
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                if (currentTheme === 'dark') {
                    this.style.setProperty('filter', 'invert(100%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(100%) contrast(100%)', 'important');
                }
            }
        });
    });
    
    // Generate default password based on employee data
    const passwordActionBtn = document.getElementById('password_action_btn');
    if (passwordActionBtn) {
        passwordActionBtn.addEventListener('click', function() {
            const employeeNo = document.getElementById('employee_no').value;
            const departmentSelect = document.getElementById('department_id');
            const passwordInput = document.getElementById('password');
            
            // Validate required fields for password generation
            let missingFields = [];
            
            if (!employeeNo || employeeNo.length !== 6) {
                missingFields.push('Employee No. (6 digits)');
            }
            
            // Department is always required for Teacher accounts
            if (!departmentSelect.value) {
                missingFields.push('Department');
            }
            
            if (missingFields.length > 0) {
                // Show error modal
                showErrorModal('Password Generation Error', `Please fill in the following required field(s) before generating a password:\n\n• ${missingFields.join('\n• ')}`);
                return;
            }
            
            // Get role name - now fixed as Teacher
            const roleName = 'Teacher';
            const roleCode = getRoleCode(roleName);
            
            // Get department code from database
            let deptCode = '';
            if (departmentSelect.value) {
                const deptText = departmentSelect.options[departmentSelect.selectedIndex].text;
                deptCode = deptText; // Now just the code like "CS", "IT", etc.
            }
            
            // Generate password based on employee data (Teacher-specific)
            let generatedPassword = '';
            if (roleCode && deptCode) {
                generatedPassword = `${employeeNo}${roleCode}${deptCode}`;
            } else if (roleCode) {
                generatedPassword = `${employeeNo}${roleCode}`;
            } else {
                generatedPassword = `${employeeNo}TCH`;
            }
            
            if (passwordInput) {
                passwordInput.value = generatedPassword;
                passwordInput.type = 'text'; // Show the generated password
            }
            
            // Update toggle button icon
            const toggleButton = document.querySelector('.toggle-password[data-target="password"]');
            if (toggleButton) {
                toggleButton.src = '../src/assets/icons/hide_password.png';
            }
            
            // Hide the generate button since password is now generated
            this.style.display = 'none';
            
            validateForm();
        });
    }
    
    // Password input change handler
    const passwordInputField = document.getElementById('password');
    if (passwordInputField) {
        passwordInputField.addEventListener('input', function() {
            const passwordActionBtn = document.getElementById('password_action_btn');
            
            if (this.value === '') {
                // Password field is empty, show generate button
                if (passwordActionBtn) {
                    passwordActionBtn.style.display = 'block';
                    passwordActionBtn.textContent = 'Generate Default Password';
                    passwordActionBtn.style.backgroundColor = '#C9C9C9';
                    passwordActionBtn.style.color = 'black';
                }
            } else {
                // Check if this is a generated password (contains TCH)
                const isGeneratedPassword = this.value.includes('TCH');
                
                if (isGeneratedPassword) {
                    // Hide generate button for generated passwords
                    if (passwordActionBtn) {
                        passwordActionBtn.style.display = 'none';
                    }
                } else {
                    // Show generate button for custom passwords
                    if (passwordActionBtn) {
                        passwordActionBtn.style.display = 'block';
                        passwordActionBtn.textContent = 'Generate Default Password';
                        passwordActionBtn.style.backgroundColor = '#C9C9C9';
                        passwordActionBtn.style.color = 'black';
                    }
                }
            }
            
            validateForm();
        });
    }
    
    // Form submission handler
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            
            // Disable the submit button to prevent double submission
            const createBtn = document.getElementById('create_btn');
            if (createBtn) {
                createBtn.disabled = true;
                createBtn.textContent = 'CREATING...';
                createBtn.style.backgroundColor = '#C9C9C9';
                createBtn.style.cursor = 'not-allowed';
            }
            
            // Get form data
            const formData = new FormData(this);
            
            // Send request to process_add_user.php
            fetch('./process_add_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If not JSON, get text and try to parse
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response. Please check the server logs.');
                    });
                }
            })
            .then(data => {
                
                if (data.success) {
                    // Show success modal
                    showSuccessModal(data.message);
                    
                    // Close the create user modal
                    closeCompleteModal();
                    
                    // Refresh user list if on user account management page
                    if (typeof window.refreshUserList === 'function') {
                        setTimeout(() => {
                            window.refreshUserList();
                        }, 500);
                    }
                } else {
                    // Show error modal
                    showErrorModal('Account Creation Failed', data.message || 'An error occurred while creating the account. Please try again.');
                    
                    // Re-enable the submit button
                    if (createBtn) {
                        createBtn.disabled = false;
                        createBtn.textContent = 'CREATE';
                        if (validateForm()) {
                            createBtn.style.backgroundColor = '#739AFF';
                            createBtn.style.color = 'white';
                            createBtn.style.cursor = 'pointer';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error creating user:', error);
                showErrorModal('Account Creation Error', error.message || 'An unexpected error occurred. Please try again or check your connection.');
                
                // Re-enable the submit button
                if (createBtn) {
                    createBtn.disabled = false;
                    createBtn.textContent = 'CREATE';
                    if (validateForm()) {
                        createBtn.style.backgroundColor = '#739AFF';
                        createBtn.style.color = 'white';
                        createBtn.style.cursor = 'pointer';
                    }
                }
            });
        });
    }

}

function getRoleCode(roleName) {
    const roleCodes = {
        'Super Admin': 'SA',
        'Admin Quality Assurance': 'AQA',
        'Department Dean': 'DD',
        'Teacher': 'TCH',
        'Librarian': 'LIB'
    };
    return roleCodes[roleName] || 'EMP';
}

function validateForm() {
    const employeeNo = document.getElementById('employee_no')?.value;
    const departmentId = document.getElementById('department_id')?.value;
    const firstName = document.getElementById('first_name')?.value;
    const lastName = document.getElementById('last_name')?.value;
    const email = document.getElementById('institutional_email')?.value;
    const password = document.getElementById('password')?.value;
    
    const createBtn = document.getElementById('create_btn');
    
    // Check if all required fields are filled
    const isEmployeeNoValid = employeeNo && employeeNo.length === 6;
    const isDepartmentValid = departmentId && departmentId !== ''; // Always required for Teacher
    const isFirstNameFilled = firstName && firstName.trim() !== '';
    const isLastNameFilled = lastName && lastName.trim() !== '';
    const isEmailFilled = email && email.trim() !== '';
    const isPasswordFilled = password && password.trim() !== '';
    
    const isFormValid = isEmployeeNoValid && isDepartmentValid && isFirstNameFilled && 
                       isLastNameFilled && isEmailFilled && isPasswordFilled;
    
    if (createBtn) {
        if (isFormValid) {
            createBtn.disabled = false;
            createBtn.style.backgroundColor = '#739AFF';
            createBtn.style.color = 'white';
            createBtn.style.cursor = 'pointer';
        } else {
            createBtn.disabled = true;
            createBtn.style.backgroundColor = '#C9C9C9';
            createBtn.style.color = '#666';
            createBtn.style.cursor = 'not-allowed';
        }
    }
    
    return isFormValid;
}

function closeCompleteModal() {
    const modal = document.getElementById('completeUserModal');
    if (modal) {
        modal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}

function showErrorModal(title, message) {
    // Remove existing error modal if it exists
    const existingErrorModal = document.getElementById('errorModal');
    if (existingErrorModal) {
        existingErrorModal.remove();
    }
    
    // Disable body scroll
    document.body.style.overflow = 'hidden';
    
    // Create error modal
    const errorModal = document.createElement('div');
    errorModal.id = 'errorModal';
    errorModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    errorModal.innerHTML = `
        <div style="width: 400px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <img src="../src/assets/animated_icons/warning-animated-icon.gif" alt="Warning" style="width: 90px; height: 90px; margin: 0 auto 18px auto; display: block;" />
            </div>
            <h2 style="color: #222; margin-bottom: 12px; font-size: 1.6em;">${title}</h2>
            <p style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5; white-space: pre-line;">${message}</p>
            <button type="button" onclick="closeErrorModal()" style="margin: 0 auto; display: block; background: #1976d2; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1.1em; font-weight: 600; box-shadow: 0 2px 8px rgba(25,118,210,0.08);">OK</button>
        </div>
    `;
    
    document.body.appendChild(errorModal);
}

function closeErrorModal() {
    const errorModal = document.getElementById('errorModal');
    if (errorModal) {
        errorModal.remove();
        // Re-enable body scroll
        document.body.style.overflow = 'auto';
    }
}



// Make functions globally available
window.toggleSidebar = toggleSidebar;
window.createDynamicModal = createDynamicModal;
window.closeDynamicModal = closeDynamicModal;
window.createCompleteModal = createCompleteModal;
window.closeCompleteModal = closeCompleteModal;
window.loadDepartmentsAndInitialize = loadDepartmentsAndInitialize;
window.initializeModalFunctions = initializeModalFunctions;
window.initializeFormEventListeners = initializeFormEventListeners;
window.showSuccessModal = showSuccessModal;
window.showErrorModal = showErrorModal;
window.closeSuccessModal = closeSuccessModal;
window.closeErrorModal = closeErrorModal;
window.getRoleCode = getRoleCode;
window.validateForm = validateForm;
window.showErrorModal = showErrorModal;
window.closeErrorModal = closeErrorModal;
window.closeFallbackModal = closeFallbackModal;

    // Load users for librarian access
    window.loadUsersForLibrarianAccess = function() {
        const usersList = document.getElementById('librarianUsersList');
        usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">Loading users...</div>';
        
        fetch('api/get_available_users.php?role_type=librarian')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.librarianUsers = data.users;
                    window.displayLibrarianUsers(data.users);
                } else {
                    usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #ff4444;">Failed to load users. Please try again.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
                usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #ff4444;">Failed to load users. Please try again.</div>';
            });
    };

    // Load current librarian access
    window.loadCurrentLibrarianAccess = function() {
        const currentAccess = document.getElementById('currentLibrarianAccess');
        if (!currentAccess) return;
        
        fetch('api/get_current_librarian_access.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.count > 0) {
                        const librariansHtml = data.librarians.map(librarian => `
                            <div style="padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 8px; background: #f8f9fa;">
                                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${librarian.name}</div>
                                <div style="font-size: 11px; color: #666; margin-bottom: 2px;">${librarian.email}</div>
                                <div style="font-size: 10px; color: #888;">Employee #${librarian.employee_no} • Assigned: ${new Date(librarian.assigned_at).toLocaleDateString()}</div>
                            </div>
                        `).join('');
                        currentAccess.innerHTML = librariansHtml;
                    } else {
                        currentAccess.innerHTML = '<p style="margin: 0 0 10px 0;">No librarian access currently assigned.</p>';
                    }
                } else {
                    currentAccess.innerHTML = '<p style="margin: 0 0 10px 0; color: #ff4444;">Failed to load current access.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading current librarian access:', error);
                currentAccess.innerHTML = '<p style="margin: 0 0 10px 0; color: #ff4444;">Failed to load current access.</p>';
            });
    };

    // Load users for QA access
    window.loadUsersForQAAccess = function() {
        const usersList = document.getElementById('qaUsersList');
        usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">Loading users...</div>';
        
        fetch('api/get_available_users.php?role_type=quality_assurance')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.qaUsers = data.users;
                    window.displayQAUsers(data.users);
                } else {
                    usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #ff4444;">Failed to load users. Please try again.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
                usersList.innerHTML = '<div style="text-align: center; padding: 20px; color: #ff4444;">Failed to load users. Please try again.</div>';
            });
    };

    // Load current QA access
    window.loadCurrentQAAccess = function() {
        const currentAccess = document.getElementById('currentQAAccess');
        if (!currentAccess) return;
        
        fetch('api/get_current_qa_access.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.count > 0) {
                        const qaUsersHtml = data.qa_users.map(qaUser => `
                            <div style="padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 8px; background: #f8f9fa;">
                                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${qaUser.name}</div>
                                <div style="font-size: 11px; color: #666; margin-bottom: 2px;">${qaUser.email}</div>
                                <div style="font-size: 10px; color: #888;">Employee #${qaUser.employee_no} • Assigned: ${new Date(qaUser.assigned_at).toLocaleDateString()}</div>
                            </div>
                        `).join('');
                        currentAccess.innerHTML = qaUsersHtml;
                    } else {
                        currentAccess.innerHTML = '<p style="margin: 0 0 10px 0;">No QA access currently assigned.</p>';
                    }
                } else {
                    currentAccess.innerHTML = '<p style="margin: 0 0 10px 0; color: #ff4444;">Failed to load current access.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading current QA access:', error);
                currentAccess.innerHTML = '<p style="margin: 0 0 10px 0; color: #ff4444;">Failed to load current access.</p>';
            });
    };
</script>

<!-- Theme Toggle System - Load early -->
<script>
// Theme: always light for now (dark theme disabled, toggle kept for future use)
(function() {
    const html = document.documentElement;
    html.setAttribute('data-theme', 'light');
})();

// Helper function to remove dark mode styles (for light mode)
window.removeDarkModeStyles = function() {
    // Get all elements with inline styles
    const allElements = document.querySelectorAll('*');
    allElements.forEach(el => {
        // Only process elements that have inline styles
        if (!el.getAttribute('style')) return;
        
        // Get computed styles to check what was actually applied
        const computedStyle = window.getComputedStyle(el);
        const bgColor = computedStyle.backgroundColor;
        const textColor = computedStyle.color;
        const borderColor = computedStyle.borderColor;
        
        // Remove dark background colors
        if (bgColor === 'rgb(37, 37, 37)' || 
            bgColor === 'rgb(26, 26, 26)' ||
            bgColor === 'rgb(45, 45, 45)' ||
            bgColor === '#252525' ||
            bgColor === '#1a1a1a' ||
            bgColor === '#2d2d2d') {
            // Check if this was set via inline style
            const styleAttr = el.getAttribute('style') || '';
            // Don't remove if it's a green background (Librarian/QA icons/buttons)
            const isGreenElement = el.classList.contains('librarian-icon') ||
                                  el.classList.contains('qa-icon') ||
                                  el.classList.contains('librarian-button') ||
                                  el.classList.contains('qa-button') ||
                                  bgColor === 'rgb(0, 103, 75)' ||
                                  bgColor === '#00674b';
            if (!isGreenElement && (styleAttr.includes('background-color') || styleAttr.includes('background:'))) {
                el.style.removeProperty('background-color');
            }
        }
        
        // Remove dark text colors (but preserve green for Development)
        const inlineStyle = el.getAttribute('style') || '';
        const hasGreenColor = inlineStyle.includes('color: green') || 
                             inlineStyle.includes('color:green') ||
                             inlineStyle.includes('color:#4CAF50') ||
                             textColor === 'rgb(76, 175, 80)' ||
                             textColor === '#4CAF50';
        
        if (!hasGreenColor && (
            textColor === 'rgb(224, 224, 224)' ||
            textColor === '#e0e0e0' ||
            textColor === 'rgb(255, 255, 255)' ||
            textColor === '#ffffff')) {
            // Check if this was set via inline style
            if (inlineStyle.includes('color:')) {
                el.style.removeProperty('color');
            }
        }
        
        // Remove dark border colors
        if (borderColor === 'rgb(64, 64, 64)' ||
            borderColor === '#404040') {
            const styleAttr = el.getAttribute('style') || '';
            if (styleAttr.includes('border-color') || styleAttr.includes('border:')) {
                el.style.removeProperty('border-color');
            }
        }
    });
    
    // Also specifically reset overview cards
    const overviewBoxes = document.querySelectorAll('.box');
    overviewBoxes.forEach(box => {
        const computedBg = window.getComputedStyle(box).backgroundColor;
        if (computedBg === 'rgb(37, 37, 37)' || computedBg === '#252525') {
            box.style.removeProperty('background-color');
            box.style.removeProperty('color');
        }
        
        const h2 = box.querySelector('h2');
        if (h2) {
            const h2Color = window.getComputedStyle(h2).color;
            if (h2Color === 'rgb(255, 255, 255)' || h2Color === '#ffffff') {
                h2.style.removeProperty('color');
            }
        }
        
        const amounts = box.querySelectorAll('.amount');
        amounts.forEach(amount => {
            const amountColor = window.getComputedStyle(amount).color;
            const inlineStyle = amount.getAttribute('style') || '';
            const textContent = amount.textContent.trim();
            const hasGreenColor = inlineStyle.includes('color: green') || 
                                 inlineStyle.includes('color:green') ||
                                 inlineStyle.includes('color:#4CAF50') ||
                                 amountColor === 'rgb(76, 175, 80)' ||
                                 amountColor === '#4CAF50';
            const isDevelopmentText = textContent === 'Development' || textContent.toLowerCase().includes('development');
            
            if (hasGreenColor || isDevelopmentText) {
                // Restore original green color for Development (use "green" keyword for light mode compatibility)
                amount.style.color = 'green';
                amount.style.setProperty('color', 'green', 'important');
                // Ensure the inline style attribute is set to green
                const currentStyle = amount.getAttribute('style') || '';
                // Replace any color with green
                const updatedStyle = currentStyle.replace(/color:\s*[^;!]+(!important)?/gi, 'color: green !important');
                if (updatedStyle !== currentStyle || !currentStyle.includes('color:')) {
                    amount.setAttribute('style', updatedStyle.includes('color:') ? updatedStyle : (currentStyle + (currentStyle ? '; ' : '') + 'color: green !important;'));
                }
            } else if (amountColor === 'rgb(255, 255, 255)' || amountColor === '#ffffff') {
                amount.style.removeProperty('color');
            }
        });
    });
    
    // Preserve green backgrounds for Librarian and QA icons and buttons (ensure they remain green)
    const librarianIcon = document.querySelector('.librarian-icon');
    const qaIcon = document.querySelector('.qa-icon');
    const librarianButton = document.querySelector('.librarian-button');
    const qaButton = document.querySelector('.qa-button');
    
    if (librarianIcon) {
        librarianIcon.style.backgroundColor = '#00674b';
        librarianIcon.style.setProperty('background-color', '#00674b', 'important');
    }
    if (qaIcon) {
        qaIcon.style.backgroundColor = '#00674b';
        qaIcon.style.setProperty('background-color', '#00674b', 'important');
    }
    if (librarianButton) {
        librarianButton.style.backgroundColor = '#00674b';
        librarianButton.style.setProperty('background-color', '#00674b', 'important');
        librarianButton.style.color = 'white';
        librarianButton.style.setProperty('color', 'white', 'important');
    }
    if (qaButton) {
        qaButton.style.backgroundColor = '#00674b';
        qaButton.style.setProperty('background-color', '#00674b', 'important');
        qaButton.style.color = 'white';
        qaButton.style.setProperty('color', 'white', 'important');
    }
};

// Helper function to apply dark mode to all elements
window.applyDarkModeStyles = function() {
    // Apply to overview cards (.box)
    const overviewBoxes = document.querySelectorAll('.box');
    overviewBoxes.forEach(box => {
        box.style.backgroundColor = '#252525';
        box.style.color = '#ffffff';
        
        // Make h2 white
        const h2 = box.querySelector('h2');
        if (h2) {
            h2.style.color = '#ffffff';
        }
        
        // Make amount white (except Development which has green color)
        const amounts = box.querySelectorAll('.amount');
        amounts.forEach(amount => {
            // Check if this is the Development text (has green color or contains "Development")
            const textContent = amount.textContent.trim();
            const hasGreenColor = amount.getAttribute('style') && 
                                 (amount.getAttribute('style').includes('color: green') || 
                                  amount.getAttribute('style').includes('color:green') ||
                                  amount.getAttribute('style').includes('color:#4CAF50'));
            const isDevelopmentText = textContent === 'Development' || textContent.toLowerCase().includes('development');
            
            if (hasGreenColor || isDevelopmentText) {
                // Keep green color for Development - use a more visible green
                amount.style.color = '#4CAF50';
                amount.style.setProperty('color', '#4CAF50', 'important');
                // Also update the inline style attribute to ensure it persists
                const currentStyle = amount.getAttribute('style') || '';
                if (!currentStyle.includes('color:') || !currentStyle.includes('green')) {
                    amount.setAttribute('style', (currentStyle ? currentStyle + '; ' : '') + 'color: #4CAF50 !important;');
                }
            } else {
                amount.style.color = '#ffffff';
            }
        });
    });
    
    // Apply to all cards
    const cards = document.querySelectorAll('.card, .dashboard-card, .content-card, [class*="card"]');
    cards.forEach(card => {
        const computedStyle = window.getComputedStyle(card);
        // Check if background is white or light
        if (computedStyle.backgroundColor === 'rgb(255, 255, 255)' || 
            computedStyle.backgroundColor === 'rgb(239, 239, 239)' ||
            computedStyle.backgroundColor === 'rgb(245, 245, 245)') {
            card.style.backgroundColor = '#252525';
            card.style.color = '#e0e0e0';
            card.style.borderColor = '#404040';
        }
    });
    
    // Preserve green backgrounds for Librarian and QA icons and buttons
    const librarianIcon = document.querySelector('.librarian-icon');
    const qaIcon = document.querySelector('.qa-icon');
    const librarianButton = document.querySelector('.librarian-button');
    const qaButton = document.querySelector('.qa-button');
    
    if (librarianIcon) {
        librarianIcon.style.backgroundColor = '#00674b';
        librarianIcon.style.setProperty('background-color', '#00674b', 'important');
    }
    if (qaIcon) {
        qaIcon.style.backgroundColor = '#00674b';
        qaIcon.style.setProperty('background-color', '#00674b', 'important');
    }
    if (librarianButton) {
        librarianButton.style.backgroundColor = '#00674b';
        librarianButton.style.setProperty('background-color', '#00674b', 'important');
        librarianButton.style.color = 'white';
        librarianButton.style.setProperty('color', 'white', 'important');
    }
    if (qaButton) {
        qaButton.style.backgroundColor = '#00674b';
        qaButton.style.setProperty('background-color', '#00674b', 'important');
        qaButton.style.color = 'white';
        qaButton.style.setProperty('color', 'white', 'important');
    }
    
    // Apply to text elements
    const textElements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, label');
    textElements.forEach(el => {
        const computedStyle = window.getComputedStyle(el);
        if (computedStyle.color === 'rgb(51, 51, 51)' || 
            computedStyle.color === 'rgb(17, 17, 17)' ||
            computedStyle.color === 'rgb(34, 34, 34)') {
            el.style.color = '#e0e0e0';
        }
    });
    
    // Apply to inputs and forms
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        if (input.type !== 'button' && input.type !== 'submit') {
            input.style.backgroundColor = '#2d2d2d';
            input.style.color = '#e0e0e0';
            input.style.borderColor = '#404040';
        }
    });
    
    // Apply to tables
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        table.style.backgroundColor = '#252525';
        table.style.color = '#e0e0e0';
    });
    
    const tableHeaders = document.querySelectorAll('th');
    tableHeaders.forEach(header => {
        header.style.backgroundColor = '#2d2d2d';
        header.style.color = '#e0e0e0';
    });
    
    // Specifically style Recent Activities table
    const recentActivities = document.querySelector('.recent-activities');
    if (recentActivities) {
        recentActivities.style.backgroundColor = '#252525';
        recentActivities.style.color = '#e0e0e0';
        
        const recentActivitiesH3 = recentActivities.querySelector('h3');
        if (recentActivitiesH3) {
            recentActivitiesH3.style.color = '#e0e0e0';
        }
        
        const recentActivitiesTable = recentActivities.querySelector('table');
        if (recentActivitiesTable) {
            recentActivitiesTable.style.backgroundColor = '#252525';
            recentActivitiesTable.style.color = '#e0e0e0';
        }
        
        const recentActivitiesTh = recentActivities.querySelectorAll('th');
        recentActivitiesTh.forEach(th => {
            th.style.backgroundColor = '#2d2d2d';
            th.style.color = '#e0e0e0';
            th.style.borderBottomColor = '#404040';
        });
        
        const recentActivitiesTd = recentActivities.querySelectorAll('td');
        recentActivitiesTd.forEach(td => {
            td.style.color = '#e0e0e0';
            td.style.borderBottomColor = '#404040';
        });
    }
};

// Theme Toggle - disabled for now, button kept for future use
window.toggleTheme = function() {
    // Dark theme temporarily disabled - coming soon
    return;
    
    
    // Set theme
    html.setAttribute('data-theme', newTheme);
    
    // Verify it was set
    
    // Directly apply styles as fallback
    if (newTheme === 'dark') {
        body.style.backgroundColor = '#1a1a1a';
        body.style.color = '#e0e0e0';
        
        // Apply to main content
        const mainContent = document.getElementById('main-content');
        const contentWrapper = document.querySelector('.content-wrapper');
        if (mainContent) {
            mainContent.style.backgroundColor = '#1a1a1a';
            mainContent.style.color = '#e0e0e0';
        }
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '#1a1a1a';
            contentWrapper.style.color = '#e0e0e0';
        }
        
        // Apply to top navbar
        const topNavbar = document.querySelector('.top-navbar');
        if (topNavbar) {
            topNavbar.style.backgroundColor = '#0a3420';
        }
        
        // Apply to sidebar
        const sidebar = document.querySelector('.side-navbar');
        if (sidebar) {
            sidebar.style.backgroundColor = '#0a3420';
        }
        
        // Apply to overview cards (.box)
        const overviewBoxes = document.querySelectorAll('.box');
        overviewBoxes.forEach(box => {
            box.style.backgroundColor = '#252525';
            box.style.color = '#ffffff';
            
            // Make h2 white
            const h2 = box.querySelector('h2');
            if (h2) {
                h2.style.color = '#ffffff';
            }
            
            // Make amount white (except Development which has green color)
            const amounts = box.querySelectorAll('.amount');
            amounts.forEach(amount => {
                // Check if this is the Development text (has green color or contains "Development")
                const textContent = amount.textContent.trim();
                const hasGreenColor = amount.getAttribute('style') && 
                                     (amount.getAttribute('style').includes('color: green') || 
                                      amount.getAttribute('style').includes('color:green') ||
                                      amount.getAttribute('style').includes('color:#4CAF50'));
                const isDevelopmentText = textContent === 'Development' || textContent.toLowerCase().includes('development');
                
                if (hasGreenColor || isDevelopmentText) {
                    // Keep green color for Development - use a more visible green
                    amount.style.color = '#4CAF50';
                    amount.style.setProperty('color', '#4CAF50', 'important');
                    // Also update the inline style attribute to ensure it persists
                    const currentStyle = amount.getAttribute('style') || '';
                    if (!currentStyle.includes('color:') || !currentStyle.includes('green')) {
                        amount.setAttribute('style', (currentStyle ? currentStyle + '; ' : '') + 'color: #4CAF50 !important;');
                    }
                } else {
                    amount.style.color = '#ffffff';
                }
            });
        });
        
        // Apply to all cards
        const cards = document.querySelectorAll('.card, .dashboard-card, .content-card, [class*="card"]');
        cards.forEach(card => {
            card.style.backgroundColor = '#252525';
            card.style.color = '#e0e0e0';
            card.style.borderColor = '#404040';
        });
        
        // Preserve green backgrounds for Librarian and QA icons and buttons
        const librarianIcon = document.querySelector('.librarian-icon');
        const qaIcon = document.querySelector('.qa-icon');
        const librarianButton = document.querySelector('.librarian-button');
        const qaButton = document.querySelector('.qa-button');
        
        if (librarianIcon) {
            librarianIcon.style.backgroundColor = '#00674b';
            librarianIcon.style.setProperty('background-color', '#00674b', 'important');
        }
        if (qaIcon) {
            qaIcon.style.backgroundColor = '#00674b';
            qaIcon.style.setProperty('background-color', '#00674b', 'important');
        }
        if (librarianButton) {
            librarianButton.style.backgroundColor = '#00674b';
            librarianButton.style.setProperty('background-color', '#00674b', 'important');
            librarianButton.style.color = 'white';
            librarianButton.style.setProperty('color', 'white', 'important');
        }
        if (qaButton) {
            qaButton.style.backgroundColor = '#00674b';
            qaButton.style.setProperty('background-color', '#00674b', 'important');
            qaButton.style.color = 'white';
            qaButton.style.setProperty('color', 'white', 'important');
        }
        
        // Apply to all text elements
        const textElements = document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div, label, td, th');
        textElements.forEach(el => {
            if (el.style.backgroundColor === 'white' || el.style.backgroundColor === '#fff' || el.style.backgroundColor === '#FFFFFF') {
                el.style.backgroundColor = '#252525';
            }
            if (el.style.color === '#333' || el.style.color === '#333333' || el.style.color === '#111' || el.style.color === '#111111') {
                el.style.color = '#e0e0e0';
            }
        });
        
        // Apply to white backgrounds
        const whiteElements = document.querySelectorAll('[style*="background-color: white"], [style*="background-color: #fff"], [style*="background-color: #FFFFFF"]');
        whiteElements.forEach(el => {
            el.style.backgroundColor = '#252525';
        });
        
        // Apply to inputs and forms
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.type !== 'button' && input.type !== 'submit') {
                input.style.backgroundColor = '#2d2d2d';
                input.style.color = '#e0e0e0';
                input.style.borderColor = '#404040';
            }
        });
        
        // Apply to tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.backgroundColor = '#252525';
            table.style.color = '#e0e0e0';
        });
        
        const tableCells = document.querySelectorAll('td, th');
        tableCells.forEach(cell => {
            cell.style.borderColor = '#404040';
        });
        
        const tableHeaders = document.querySelectorAll('th');
        tableHeaders.forEach(header => {
            header.style.backgroundColor = '#2d2d2d';
            header.style.color = '#e0e0e0';
        });
        
        // Apply comprehensive dark mode styles
        setTimeout(() => {
            if (window.applyDarkModeStyles) {
                applyDarkModeStyles();
            }
        }, 200);
    } else {
        // Light mode - remove all dark mode styles
        body.style.backgroundColor = '';
        body.style.color = '';
        
        const mainContent = document.getElementById('main-content');
        const contentWrapper = document.querySelector('.content-wrapper');
        if (mainContent) {
            mainContent.style.backgroundColor = '';
            mainContent.style.color = '';
        }
        if (contentWrapper) {
            contentWrapper.style.backgroundColor = '';
            contentWrapper.style.color = '';
        }
        
        const topNavbar = document.querySelector('.top-navbar');
        if (topNavbar) {
            topNavbar.style.backgroundColor = '';
        }
        
        const sidebar = document.querySelector('.side-navbar');
        if (sidebar) {
            sidebar.style.backgroundColor = '';
        }
        
        // Use helper function to remove all dark mode styles
        setTimeout(() => {
            if (window.removeDarkModeStyles) {
                removeDarkModeStyles();
            }
        }, 100);
    }
    
    // Apply or remove dark mode from modal if it's open
    if (newTheme === 'dark') {
        setTimeout(() => {
            if (typeof applyModalDarkMode === 'function') {
                applyModalDarkMode();
            }
        }, 150);
    } else {
        // Reset modal styles for light mode
        const modal = document.getElementById('completeUserModal');
        if (modal) {
            const modalBox = modal.querySelector('div');
            if (modalBox) {
                modalBox.style.backgroundColor = '#EFEFEF';
                modalBox.style.color = '';
                modalBox.style.borderColor = '#888';
            }
            
            const inputs = modal.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type !== 'hidden' && input.type !== 'submit' && input.type !== 'button') {
                    input.style.backgroundColor = '#FFFFFF';
                    input.style.color = '';
                    input.style.borderColor = '#ccc';
                }
            });
            
            const labels = modal.querySelectorAll('label');
            labels.forEach(label => {
                label.style.color = '';
            });
            
            const h2 = modal.querySelector('h2');
            if (h2) {
                h2.style.color = '#333';
            }
            
            const closeBtn = modal.querySelector('span[onclick*="closeCompleteModal"]');
            if (closeBtn) {
                closeBtn.style.color = '#aaa';
            }
            
            // Reset toggle password icons for light mode
            const toggleIcons = modal.querySelectorAll('.toggle-password');
            toggleIcons.forEach(icon => {
                icon.style.setProperty('filter', 'invert(0%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%)', 'important');
            });
        }
    }
    
    // Save to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Update icon
    updateThemeIcon(newTheme);
    
}

window.updateThemeIcon = function(theme) {
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
        // Show opposite icon: if dark mode, show light icon (to switch to light), and vice versa
        themeIcon.src = theme === 'dark' 
            ? '../src/assets/icons/light-mode.png' 
            : '../src/assets/icons/dark-mode.png';
        // Maintain inline styles for size
        themeIcon.style.width = '40px';
        themeIcon.style.height = '40px';
        themeIcon.style.maxWidth = '40px';
        themeIcon.style.maxHeight = '40px';
    }
}

// Theme: always light for now (dark theme disabled)
document.addEventListener('DOMContentLoaded', function() {
    const html = document.documentElement;
    
    // Force light theme (dark theme disabled, toggle kept for future use)
    html.setAttribute('data-theme', 'light');
    
    // Dark mode application skipped - theme disabled
    if (false) { // was: savedTheme === 'dark'
        body.style.backgroundColor = '#1a1a1a';
        body.style.color = '#e0e0e0';
        
        setTimeout(() => {
            const mainContent = document.getElementById('main-content');
            const contentWrapper = document.querySelector('.content-wrapper');
            const topNavbar = document.querySelector('.top-navbar');
            const sidebar = document.querySelector('.side-navbar');
            
            if (mainContent) {
                mainContent.style.backgroundColor = '#1a1a1a';
                mainContent.style.color = '#e0e0e0';
            }
            if (contentWrapper) {
                contentWrapper.style.backgroundColor = '#1a1a1a';
                contentWrapper.style.color = '#e0e0e0';
            }
            if (topNavbar) {
                topNavbar.style.backgroundColor = '#0a3420';
            }
            if (sidebar) {
                sidebar.style.backgroundColor = '#0a3420';
            }
            
            // Apply to all cards
            const cards = document.querySelectorAll('.card, .dashboard-card, .content-card, [class*="card"]');
            cards.forEach(card => {
                card.style.backgroundColor = '#252525';
                card.style.color = '#e0e0e0';
                card.style.borderColor = '#404040';
            });
            
            // Apply to white backgrounds
            const whiteElements = document.querySelectorAll('[style*="background-color: white"], [style*="background-color: #fff"], [style*="background-color: #FFFFFF"]');
            whiteElements.forEach(el => {
                el.style.backgroundColor = '#252525';
            });
            
            // Apply to inputs and forms
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.type !== 'button' && input.type !== 'submit') {
                    input.style.backgroundColor = '#2d2d2d';
                    input.style.color = '#e0e0e0';
                    input.style.borderColor = '#404040';
                }
            });
            
            // Apply to tables
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                table.style.backgroundColor = '#252525';
                table.style.color = '#e0e0e0';
            });
            
            const tableHeaders = document.querySelectorAll('th');
            tableHeaders.forEach(header => {
                header.style.backgroundColor = '#2d2d2d';
                header.style.color = '#e0e0e0';
            });
            
            // Specifically style Recent Activities table
            const recentActivities = document.querySelector('.recent-activities');
            if (recentActivities) {
                recentActivities.style.backgroundColor = '#252525';
                recentActivities.style.color = '#e0e0e0';
                
                const recentActivitiesH3 = recentActivities.querySelector('h3');
                if (recentActivitiesH3) {
                    recentActivitiesH3.style.color = '#e0e0e0';
                }
                
                const recentActivitiesTable = recentActivities.querySelector('table');
                if (recentActivitiesTable) {
                    recentActivitiesTable.style.backgroundColor = '#252525';
                    recentActivitiesTable.style.color = '#e0e0e0';
                }
                
                const recentActivitiesTh = recentActivities.querySelectorAll('th');
                recentActivitiesTh.forEach(th => {
                    th.style.backgroundColor = '#2d2d2d';
                    th.style.color = '#e0e0e0';
                    th.style.borderBottomColor = '#404040';
                });
                
                const recentActivitiesTd = recentActivities.querySelectorAll('td');
                recentActivitiesTd.forEach(td => {
                    td.style.color = '#e0e0e0';
                    td.style.borderBottomColor = '#404040';
                });
            }
            
            // Apply comprehensive dark mode styles
            setTimeout(() => {
                if (window.applyDarkModeStyles) {
                    applyDarkModeStyles();
                }
            }, 300);
        }, 100);
    }
    
    // Update icon (always light mode)
    if (window.updateThemeIcon) {
        updateThemeIcon('light');
    }
    
});
</script>

</head>
<body>

<?php 
// --- Modals are now included at the bottom of the page to avoid duplicates ---
// IMPORTANT: modal_add_department.php is now included by dashboard.php, NOT here.
// REMOVED: Duplicate modal includes to prevent duplicate IDs

// Load notifications server-side (bypasses fetch/path issues)
$pageNotifications = ['data' => [], 'success' => true];
if (isset($conn) && !$conn->connect_error) {
    @$conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT,
        type VARCHAR(50) DEFAULT 'info',
        sender_id INT,
        sender_name VARCHAR(255),
        sender_role VARCHAR(100),
        recipient_type VARCHAR(100) DEFAULT 'all',
        recipient_id INT,
        is_read TINYINT(1) DEFAULT 0,
        read_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        removed_at DATETIME NULL
    )");
    $notifQuery = "SELECT id, title, message, type, sender_name, sender_role, is_read, created_at, read_at 
        FROM notifications 
        WHERE (recipient_type = 'super_admin' OR recipient_type = 'all') 
        ORDER BY created_at DESC LIMIT 10";
    $notifResult = @$conn->query($notifQuery);
    if ($notifResult) {
        $formatted = [];
        while ($row = $notifResult->fetch_assoc()) {
            $ts = isset($row['created_at']) ? strtotime($row['created_at']) : 0;
            $formatted[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'] ?? '',
                'message' => $row['message'] ?? '',
                'type' => $row['type'] ?? 'info',
                'sender_name' => $row['sender_name'] ?? '',
                'sender_role' => $row['sender_role'] ?? '',
                'is_read' => !empty($row['is_read']),
                'created_at' => $ts ? date('M d, Y H:i', $ts) : '',
                'created_at_ts' => $ts,
                'read_at' => !empty($row['read_at']) ? date('M d, Y H:i', strtotime($row['read_at'])) : null
            ];
        }
        $pageNotifications = ['data' => $formatted, 'success' => true];
    }
}
?>

<?php
$notifList = $pageNotifications['data'] ?? [];
$unreadCount = count(array_filter($notifList, function($n) { return !($n['is_read'] ?? false); }));
$displayCount = $unreadCount > 99 ? '99+' : $unreadCount;
$notifUnread = array_filter($notifList, function($n) { return !($n['is_read'] ?? false); });
$notifRead = array_filter($notifList, function($n) { return ($n['is_read'] ?? false); });
?>
<div class="top-navbar">
  <div class="top-navbar-content">
    <!-- TOP, LEFT group -->
    <div class="top-nav-left">
      <div class="hamburger" onclick="window.toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <img src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Logo" class="logo-img" /> 
      <div class="search-bar">
        <img src="../src/assets/icons/search-icon.png" alt="Search Icon" /> 
        <input type="text" placeholder="Search Here..." />
      </div>
    </div>
    <!-- TOP, RIGHT - notification icon -->
    <div class="notification-icon" id="notificationIconBtn" onclick="event.stopPropagation(); var d=document.getElementById('notificationDropdown'); if(d) d.style.display=d.style.display==='block'?'none':'block'">
      <img src="../src/assets/icons/notifications-icon.png" alt="Notifications" /> 
      <div class="notification-count" data-count="<?php echo $unreadCount; ?>" style="<?php echo $unreadCount > 0 ? '' : 'display:none;'; ?>"><?php echo htmlspecialchars($displayCount); ?></div>
    </div>
  </div>
  <div class="notification-dropdown" id="notificationDropdown">
    <h3>Notifications</h3>
    <?php if (empty($notifList)): ?>
    <div class="notification-empty">No new notifications</div>
    <?php else: ?>
    <div class="notification-list" id="notificationListContainer">
      <?php if (!empty($notifUnread)): ?>
        <div class="notification-section notification-unread-section">
          <div class="notification-section-title">Unread</div>
          <?php foreach ($notifUnread as $n): ?>
          <div class="notification-item unread" data-id="<?php echo (int)$n['id']; ?>">
            <div class="notification-content">
              <div class="notification-title"><?php echo htmlspecialchars($n['title'] ?? ''); ?></div>
              <div class="notification-message"><?php echo htmlspecialchars($n['message'] ?? ''); ?></div>
              <div class="notification-meta">
                <span class="notification-sender"><?php echo htmlspecialchars($n['sender_name'] ?? ''); ?> (<?php echo htmlspecialchars($n['sender_role'] ?? ''); ?>)</span>
                <span class="notification-time"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></span>
              </div>
            </div>
            <div class="notification-actions">
              <div class="notification-dot"></div>
              <div class="notification-menu">
                <button class="notification-menu-btn" onclick="event.stopPropagation(); if(window.toggleNotificationMenu) toggleNotificationMenu(<?php echo (int)$n['id']; ?>)">⋯</button>
                <div class="notification-menu-dropdown" id="menu-<?php echo (int)$n['id']; ?>">
                  <button class="notification-menu-item mark-read" onclick="if(window.toggleNotificationRead) toggleNotificationRead(<?php echo (int)$n['id']; ?>)"><i>📬</i> Mark as Read</button>
                  <button class="notification-menu-item remove" onclick="if(window.removeNotification) removeNotification(<?php echo (int)$n['id']; ?>)"><i>🗑️</i> Remove</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if (!empty($notifRead)): ?>
        <div class="notification-toggle-read-wrap">
          <button type="button" class="notification-toggle-read-btn" id="toggleReadBtn" onclick="if(window.toggleAlreadyReadSection) toggleAlreadyReadSection()">View Already Read Notifications</button>
        </div>
        <div class="notification-section notification-read-section" id="notificationReadSection" style="display:none;">
          <div class="notification-section-title">Already Read</div>
          <?php foreach ($notifRead as $n): ?>
          <div class="notification-item" data-id="<?php echo (int)$n['id']; ?>">
            <div class="notification-content">
              <div class="notification-title"><?php echo htmlspecialchars($n['title'] ?? ''); ?></div>
              <div class="notification-message"><?php echo htmlspecialchars($n['message'] ?? ''); ?></div>
              <div class="notification-meta">
                <span class="notification-sender"><?php echo htmlspecialchars($n['sender_name'] ?? ''); ?> (<?php echo htmlspecialchars($n['sender_role'] ?? ''); ?>)</span>
                <span class="notification-time"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></span>
              </div>
            </div>
            <div class="notification-actions">
              <div class="notification-menu">
                <button class="notification-menu-btn" onclick="event.stopPropagation(); if(window.toggleNotificationMenu) toggleNotificationMenu(<?php echo (int)$n['id']; ?>)">⋯</button>
                <div class="notification-menu-dropdown" id="menu-<?php echo (int)$n['id']; ?>">
                  <button class="notification-menu-item mark-unread" onclick="if(window.toggleNotificationRead) toggleNotificationRead(<?php echo (int)$n['id']; ?>)"><i>📧</i> Mark as Unread</button>
                  <button class="notification-menu-item remove" onclick="if(window.removeNotification) removeNotification(<?php echo (int)$n['id']; ?>)"><i>🗑️</i> Remove</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="notification-toggle-read-wrap">
            <button type="button" class="notification-toggle-read-btn notification-hide-read-btn" onclick="if(window.toggleAlreadyReadSection) toggleAlreadyReadSection()">Hide Already Read Notifications</button>
          </div>
        </div>
        <?php endif; ?>
      <?php elseif (!empty($notifRead)): ?>
        <div class="notification-section notification-read-section" id="notificationReadSection">
          <div class="notification-section-title">Already Read</div>
          <?php foreach ($notifRead as $n): ?>
          <div class="notification-item" data-id="<?php echo (int)$n['id']; ?>">
            <div class="notification-content">
              <div class="notification-title"><?php echo htmlspecialchars($n['title'] ?? ''); ?></div>
              <div class="notification-message"><?php echo htmlspecialchars($n['message'] ?? ''); ?></div>
              <div class="notification-meta">
                <span class="notification-sender"><?php echo htmlspecialchars($n['sender_name'] ?? ''); ?> (<?php echo htmlspecialchars($n['sender_role'] ?? ''); ?>)</span>
                <span class="notification-time"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></span>
              </div>
            </div>
            <div class="notification-actions">
              <div class="notification-menu">
                <button class="notification-menu-btn" onclick="event.stopPropagation(); if(window.toggleNotificationMenu) toggleNotificationMenu(<?php echo (int)$n['id']; ?>)">⋯</button>
                <div class="notification-menu-dropdown" id="menu-<?php echo (int)$n['id']; ?>">
                  <button class="notification-menu-item mark-unread" onclick="if(window.toggleNotificationRead) toggleNotificationRead(<?php echo (int)$n['id']; ?>)"><i>📧</i> Mark as Unread</button>
                  <button class="notification-menu-item remove" onclick="if(window.removeNotification) removeNotification(<?php echo (int)$n['id']; ?>)"><i>🗑️</i> Remove</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="notification-actions">
      <button class="mark-all-read-btn" onclick="if(window.notificationSystem&&window.notificationSystem.markAllAsRead) notificationSystem.markAllAsRead()">Mark All Read</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<nav class="side-navbar" id="sidebar" aria-label="Sidebar navigation">
  <div class="nav-buttons">
    <a href="#" class="nav-button new-account-button" id="newAccountBtn" onclick="openAddUserModal()">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>New Account</span>
    </a>
    


    <?php 
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    $sidebarState = isset($_GET['sidebar']) ? $_GET['sidebar'] : 'expanded';
    
    // DEBUG: Show what we're getting
    ?>
    
    <script>
    // RESTORE ORIGINAL WORKING SIDEBAR FUNCTIONALITY
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        if (sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '298px';
            }
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            sidebar.classList.add('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '115px';
            }
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    }
    
    // Restore sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '115px';
            }
        } else {
            sidebar.classList.remove('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '298px';
            }
        }
        
        // Initialize tooltips for collapsed sidebar
        initializeTooltips();
    });
    
    // Simple tooltip system for collapsed sidebar
    function initializeTooltips() {
        const navButtons = document.querySelectorAll('.nav-button');
        let tooltip = null;
        
        navButtons.forEach(function(button, index) {
            
            button.addEventListener('mouseenter', function() {
                const sidebar = document.getElementById('sidebar');
                const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
                
                if (isCollapsed) {
                    const spanElement = button.querySelector('span:not(.nav-icon-wrapper)');
                    let tooltipText = 'Unknown';
                    if (spanElement) {
                        // Get innerHTML and replace <br> tags with spaces
                        tooltipText = spanElement.innerHTML.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
                    }
                    
                    if (tooltip) tooltip.remove();
                    
                    tooltip = document.createElement('div');
                    tooltip.className = 'nav-tooltip';
                    tooltip.innerHTML = `
                        <div style="
                            position: absolute;
                            left: -8px;
                            top: 50%;
                            transform: translateY(-50%);
                            width: 0;
                            height: 0;
                            border-top: 8px solid transparent;
                            border-bottom: 8px solid transparent;
                            border-right: 8px solid #f8f9fa;
                            filter: drop-shadow(-2px 0 4px rgba(0,0,0,0.1));
                        "></div>
                        ${tooltipText}
                    `;
                    tooltip.style.cssText = `
                        position: fixed !important;
                        background: #f8f9fa !important;
                        color: #000000 !important;
                        padding: 12px 20px !important;
                        border-radius: 12px !important;
                        font-size: 14px !important;
                        font-weight: 600 !important;
                        white-space: nowrap !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        display: block !important;
                        pointer-events: none !important;
                        z-index: 999999 !important;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
                        border: 1px solid #e0e0e0 !important;
                        font-family: 'TT Interphases', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                        letter-spacing: 0.5px !important;
                        text-shadow: none !important;
                    `;
                    
                    const rect = button.getBoundingClientRect();
                    tooltip.style.left = (rect.right + 10) + 'px';
                    tooltip.style.top = (rect.top + rect.height / 2 - 15) + 'px';
                    
                    document.body.appendChild(tooltip);
                }
            });
            
            button.addEventListener('mouseleave', function() {
                if (tooltip) {
                    tooltip.remove();
                    tooltip = null;
                }
            });
        });
        
    }
    </script>
    

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard') echo 'active'; ?>" data-page="dashboard">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
    </a>

    <a href="content.php?page=user-account-management" class="nav-button hoverable <?php if ($currentPage == 'user-account-management') echo 'active'; ?>" data-page="user-account-management" style="height: 76px;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/users-icon.png" alt="Users Icon" class="nav-icon" />
      </span>
      <span style="line-height: 1.2;">
        User Account<br />Management
      </span>
    </a>

    <a href="content.php?page=school-calendar" class="nav-button hoverable <?php if ($currentPage == 'school-calendar') echo 'active'; ?>" data-page="school-calendar">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/calendar-icon.png" alt="Calendar Icon" class="nav-icon" />
      </span>
      <span>School Calendar</span>
    </a>

    <a href="content.php?page=settings" class="nav-button hoverable <?php if ($currentPage == 'settings') echo 'active'; ?>" data-page="settings">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/settings-icon.png" alt="Settings Icon" class="nav-icon" />
      </span>
      <span>Settings</span>
    </a>
  </div>

  <a href="./logout.php" class="nav-button logout-button">
    <span class="nav-icon-wrapper">
      <img src="../src/assets/icons/logout-icon.png" class="nav-icon" />
    </span>
    <span>Log Out</span>
  </a>
</nav>

<div class="content-wrapper">
    <!-- Loading Screen -->
    <div id="loading-screen" class="loading-screen" style="display: none !important; opacity: 0 !important; pointer-events: none !important;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div id="main-content" class="main-content" style="display: block !important; opacity: 1 !important;">
  <?php
    // Load content based on the requested page
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    
    switch ($page) {
      case 'user-account-management':
        include './user_account_management-content/user-account-management.php';
        break;

      case 'school-calendar':
        include './school_calendar-content/school-calendar.php';
        break;
        
      case 'settings': 
        include './settings-content/settings.php';
        break;

      case 'dashboard':
      default:
        include './dashboard-content/dashboard.php';
        break;
    }
  ?>
    </div>
</div>

<!-- Include Modals -->
<?php 
// Check if modal files exist and include them
$modalFiles = [
    './modal_user_details.php',
    './modal_add_user.php',  
    './modal_edit_user.php', 
    './modal_delete_user.php'
];

$currentDir = getcwd();
foreach ($modalFiles as $modalFile) {
    $fullPath = __DIR__ . '/' . $modalFile;
    if (file_exists($fullPath)) {
        try {
            include $fullPath;
        } catch (Exception $e) {
            echo "<!-- ERROR including $modalFile: " . $e->getMessage() . " -->";
        } catch (ParseError $e) {
            echo "<!-- PARSE ERROR in $modalFile: " . $e->getMessage() . " -->";
        }
    } else {
        echo "<!-- DEBUG: $modalFile not found at: $fullPath (cwd: $currentDir) -->";
    }
}
?>



<script>

// Define functions directly in global scope

// Wrap everything in a function to avoid global scope issues
(function() {

// Add Department Modal Functions
window.openAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Reset form
        const form = document.getElementById('addDepartmentForm');
        if (form) {
            form.reset();
        }
        
        // Initialize color picker and form
        setTimeout(() => {
            const colorPicker = document.getElementById("colorPicker");
            const colorHex = document.getElementById("colorHex");
            const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
            
            if (colorPicker && colorHex && colorSwatchDisplay) {
                const defaultColor = "#4A7DFF";
                colorPicker.value = defaultColor;
                colorHex.value = defaultColor;
                colorSwatchDisplay.style.backgroundColor = defaultColor;
            }
            
            if (typeof initializeColorPicker === 'function') {
                initializeColorPicker();
            }
            
            if (typeof window.checkFormValidity === 'function') {
                window.checkFormValidity();
            }
            
            // Re-initialize event listeners for form validation
            const addDepartmentForm = document.getElementById("addDepartmentForm");
            if (addDepartmentForm) {
                const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));
                requiredFields.forEach(field => {
                    // Add listeners (multiple listeners won't cause issues)
                    field.addEventListener("input", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                    field.addEventListener("change", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                });
                
                // Re-attach color picker listeners
                const colorPicker = document.getElementById("colorPicker");
                const colorHex = document.getElementById("colorHex");
                
                if (colorPicker) {
                    colorPicker.addEventListener("input", function() {
                        if (colorHex) {
                            colorHex.value = this.value.toUpperCase();
                        }
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                }
                
                if (colorHex) {
                    colorHex.addEventListener("input", function() {
                        if (typeof window.checkFormValidity === 'function') {
                            window.checkFormValidity();
                        }
                    });
                }
            }
        }, 100);
    } else {
        console.error('❌ Modal not found');
    }
};

window.closeAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
};

window.openSuccessModal = function(message) {
    const successMessage = document.getElementById('successMessage');
    const successModal = document.getElementById('successModal');
    if (successMessage && successModal) {
        successMessage.innerText = message;
        successModal.style.display = 'flex';
        successModal.style.zIndex = '10000';
        // Disable body scroll
        document.body.style.overflow = 'hidden';
    }
};

window.closeSuccessModal = function() {
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
};



// Removed problematic window statement and its orphaned function body



    // Teacher Account Creation Functions
    window.openAddUserModal = function() {
        if (typeof createCompleteModal === 'function') {
            createCompleteModal();
        } else {
            console.error('❌ createCompleteModal function not found');
            // Fallback: create a simple modal
            const modal = document.createElement('div');
            modal.id = 'fallbackUserModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.6);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            `;
            modal.innerHTML = `
                <div style="background-color: #EFEFEF; padding: 25px; border: 1px solid #888; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 15px; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Create Teacher Account</h2>
                        <span onclick="closeFallbackModal()" style="color: #aaa; font-size: 28px; font-weight: 700; cursor: pointer;">&times;</span>
                    </div>
                    <p>Modal is working! This is a fallback modal.</p>
                    <button onclick="closeFallbackModal()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                </div>
            `;
            document.body.appendChild(modal);
        }
    };
    
    window.closeFallbackModal = function() {
        const modal = document.getElementById('fallbackUserModal');
        if (modal) {
            modal.remove();
        }
    };

    window.closeAddUserModal = function() {
        closeCompleteModal();
    };

    window.openAddUserSuccessModal = function(message) {
        const successMessage = document.getElementById('addUserSuccessMessage');
        const successModal = document.getElementById('addUserSuccessModal');
        
        if (successMessage && successModal) {
            successMessage.innerText = message;
            successModal.style.display = 'flex';
            successModal.style.zIndex = '10000';
        } else {
            console.error('❌ Teacher account success modal elements not found');
            alert('Success: ' + message);
        }
    };

    window.closeAddUserSuccessModal = function() {
        const successModal = document.getElementById('addUserSuccessModal');
        if (successModal) {
            successModal.style.display = 'none';
        }
    };

}

// Define functions immediately to prevent ReferenceError
window.openAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'flex';
        
        // Ensure consistent styling across all pages
        modal.style.zIndex = '9999';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.6)';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        
        
        // Initialize form
        const form = document.getElementById('addDepartmentForm');
        if (form) {
            form.reset();
        }
        
        // Initialize color picker
        const colorPicker = document.getElementById("colorPicker");
        const colorHex = document.getElementById("colorHex");
        const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
        
        if (colorPicker && colorHex && colorSwatchDisplay) {
            const defaultColor = "#4A7DFF";
            colorPicker.value = defaultColor;
            colorHex.value = defaultColor;
            colorSwatchDisplay.style.backgroundColor = defaultColor;
        }
        
        // Call checkFormValidity after a short delay to ensure DOM is ready
        setTimeout(() => {
            // Ensure button is enabled for demonstration
            const createBtn = document.querySelector('#addDepartmentForm .create-btn');
            if (createBtn) {
                createBtn.disabled = false;
                createBtn.style.backgroundColor = '#4CAF50';
                createBtn.style.color = 'white';
                createBtn.style.cursor = 'pointer';
            }
            
            if (typeof window.checkFormValidity === 'function') {
                window.checkFormValidity();
            } else {
                console.error('❌ checkFormValidity function not available');
            }
        }, 50);
    } else {
        console.error('❌ Modal element not found in immediate function');
    }
};

window.closeAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'none';
        // Re-enable body scroll
        document.body.style.overflow = '';
    }
};

window.openSuccessModal = function(message) {
    const successMessage = document.getElementById('successMessage');
    const successModal = document.getElementById('successModal');
    if (successMessage && successModal) {
        successMessage.innerText = message;
        successModal.style.display = 'flex';
    }
};

window.closeSuccessModal = function() {
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.style.display = 'none';
    }
};

// Removed problematic window statement and its orphaned function body




// Function to check form validity for department creation
// TEMPORARILY DISABLED FOR DEMONSTRATION - Button always enabled
window.checkFormValidity = function() {
    const addDepartmentForm = document.getElementById("addDepartmentForm");
    if (!addDepartmentForm) {
        console.warn('⚠️ Department form not found');
        return;
    }
    
    const createBtn = addDepartmentForm.querySelector(".create-btn");
    if (!createBtn) {
        console.warn('⚠️ Create button not found');
        return;
    }
    
    // TEMPORARY: Always enable button for demonstration
    createBtn.disabled = false;
    createBtn.style.backgroundColor = '#4CAF50';
    createBtn.style.color = 'white';
    createBtn.style.cursor = 'pointer';
    
    /* ORIGINAL VALIDATION CODE - COMMENTED OUT FOR DEMO
    const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));
    const colorHexInput = document.getElementById("colorHex");
    
    // Check if color hex is filled and valid
    const isColorHexValid = colorHexInput ? colorHexInput.value.trim() !== '' : false;
    const isColorHexFormatValid = colorHexInput ? (colorHexInput.value.trim() === '' || /^#([A-Fa-f0-9]{6})$/.test(colorHexInput.value.trim())) : false;
    
    // Check if other required fields (excluding colorHex) are filled
    const otherFieldsFilled = requiredFields
        .filter(field => field.id !== 'colorHex')
        .every(field => field.value.trim() !== "");
    
    // Enable button only if all conditions are met
    const isValid = otherFieldsFilled && isColorHexValid && isColorHexFormatValid;
    
    createBtn.disabled = !isValid;
    
    // Update button styling
    if (isValid) {
        createBtn.style.backgroundColor = '#4CAF50';
        createBtn.style.color = 'white';
        createBtn.style.cursor = 'pointer';
    } else {
        createBtn.style.backgroundColor = '#C9C9C9';
        createBtn.style.color = '#666';
        createBtn.style.cursor = 'not-allowed';
    }
    
        otherFieldsFilled,
        isColorHexValid,
        isColorHexFormatValid,
        isValid,
        buttonDisabled: createBtn.disabled
    });
    */
};

// Duplicate function removed - using the one defined earlier

// Duplicate functions removed - using the ones defined earlier


// REMOVED COMPLEX TOGGLE FUNCTION - USING SIMPLE APPROACH ABOVE

})(); // End of wrapper function
</script>
<!-- Removed modal-add-department.js to prevent conflicts - functions are now defined directly in content.php -->
<script>
// Initialize form validation listeners when DOM is ready
document.addEventListener("DOMContentLoaded", function() {
    
    const addDepartmentForm = document.getElementById("addDepartmentForm");
    if (addDepartmentForm) {
        const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));
        
        // Add event listeners to all required fields
        requiredFields.forEach(field => {
            field.addEventListener("input", function() {
                if (typeof window.checkFormValidity === 'function') {
                    window.checkFormValidity();
                }
            });
            field.addEventListener("change", function() {
                if (typeof window.checkFormValidity === 'function') {
                    window.checkFormValidity();
                }
            });
        });
        
        // Also listen to color picker changes
        const colorPicker = document.getElementById("colorPicker");
        const colorHex = document.getElementById("colorHex");
        
        if (colorPicker) {
            colorPicker.addEventListener("input", function() {
                if (colorHex) {
                    colorHex.value = this.value.toUpperCase();
                }
                if (typeof window.checkFormValidity === 'function') {
                    window.checkFormValidity();
                }
            });
        }
        
        if (colorHex) {
            colorHex.addEventListener("input", function() {
                if (typeof window.checkFormValidity === 'function') {
                    window.checkFormValidity();
                }
            });
        }
        
        // Initial validation check
        if (typeof window.checkFormValidity === 'function') {
            window.checkFormValidity();
        }
        
    } else {
        console.warn('⚠️ Department form not found during DOMContentLoaded');
    }
});

// Force define the function if it's not available
if (typeof window.openAddDepartmentModal === 'undefined') {
    window.openAddDepartmentModal = function() {
        const modal = document.getElementById('addDepartmentModal');
        if (modal) {
            modal.style.display = 'flex';
            
            // Initialize form
            const form = document.getElementById('addDepartmentForm');
            if (form) {
                form.reset();
            }
            
            // Initialize color picker
            const colorPicker = document.getElementById("colorPicker");
            const colorHex = document.getElementById("colorHex");
            const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
            
            if (colorPicker && colorHex && colorSwatchDisplay) {
                const defaultColor = "#4A7DFF";
                colorPicker.value = defaultColor;
                colorHex.value = defaultColor;
                colorSwatchDisplay.style.backgroundColor = defaultColor;
            }
            
            // Call checkFormValidity if available
            if (typeof window.checkFormValidity === 'function') {
                window.checkFormValidity();
            }
        } else {
            console.error('❌ Modal element not found in fallback');
        }
    };
}

// Also ensure other functions are available
if (typeof window.closeAddDepartmentModal === 'undefined') {
    window.closeAddDepartmentModal = function() {
        const modal = document.getElementById('addDepartmentModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
}

if (typeof window.openSuccessModal === 'undefined') {
    window.openSuccessModal = function(message) {
        const successMessage = document.getElementById('successMessage');
        const successModal = document.getElementById('successModal');
        if (successMessage && successModal) {
            successMessage.innerText = message;
            successModal.style.display = 'flex';
        }
    };
}

if (typeof window.closeSuccessModal === 'undefined') {
    window.closeSuccessModal = function() {
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.style.display = 'none';
        }
    };
}

// Removed orphaned code block that was causing syntax errors

// Fallback functions removed to fix syntax error
</script>
<script>
// Modal functions are already defined in the earlier script block
</script>
<script src="./scripts/modal-add-user.js?v=<?php echo time(); ?>&ultra=1"></script>
<script src="./scripts/user-account-management.js?v=2.8"></script>
<script src="./scripts/modal-edit-user.js?v=<?php echo time(); ?>&edit=1"></script>
<script>
// Ensure modal functions are available with proper data fetching
window.ensureModalFunctions = function() {
    // Add User - just opens modal
    if (!window.openAddUserModal) {
        window.openAddUserModal = function() {
            var m = document.getElementById('addUserModal');
            if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
        };
    }
    // Edit User - MUST fetch and populate data
    if (!window.openEditUserModal) {
        window.openEditUserModal = async function(userId) {
            var m = document.getElementById('editUserModal');
            if (m) { 
                m.style.display = 'flex'; 
                document.body.style.overflow = 'hidden';
                // Fetch user data
                try {
                    var resp = await fetch('./api/get_user_data.php?employee_no=' + userId);
                    var data = await resp.json();
                    if (data.success && data.data) {
                        // Populate form fields
                        document.getElementById('edit_employee_no').value = data.data.employee_no || '';
                        document.getElementById('employee_no_original').value = data.data.employee_no || '';
                        document.getElementById('edit_department_id').value = data.data.department_id || '';
                        document.getElementById('edit_first_name').value = data.data.first_name || '';
                        document.getElementById('edit_middle_name').value = data.data.middle_name || '';
                        document.getElementById('edit_last_name').value = data.data.last_name || '';
                        document.getElementById('edit_title').value = data.data.title || '';
                        document.getElementById('edit_institutional_email').value = data.data.institutional_email || '';
                        document.getElementById('edit_mobile_no').value = data.data.mobile_no || '';
                        document.getElementById('edit_password').value = data.data.current_password || '';
                    }
                } catch(e) { console.error('Error:', e); }
            }
        };
    }
    // Delete User
    if (!window.openDeleteUserModal) {
        window.openDeleteUserModal = function(userId) {
            var m = document.getElementById('deleteUserModal');
            if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
            var btn = document.getElementById('confirmDeleteBtn');
            if (btn) btn.setAttribute('data-employee-no', userId);
        };
    }
    // Close functions
    if (!window.closeAddUserModal) {
        window.closeAddUserModal = function() {
            var m = document.getElementById('addUserModal');
            if (m) { m.style.display = 'none'; }
            document.body.style.overflow = '';
        };
    }
    if (!window.closeEditUserModal) {
        window.closeEditUserModal = function() {
            var m = document.getElementById('editUserModal');
            if (m) { m.style.display = 'none'; }
            document.body.style.overflow = '';
        };
    }
    if (!window.closeDeleteUserModal) {
        window.closeDeleteUserModal = function() {
            var m = document.getElementById('deleteUserModal');
            if (m) { m.style.display = 'none'; }
            document.body.style.overflow = '';
        };
    }
    if (!window.closeUserDetailsModal) {
        window.closeUserDetailsModal = function() {
            var m = document.getElementById('userDetailsModal');
            if (m) { m.style.display = 'none'; m.classList.remove('show'); }
            document.body.style.overflow = '';
        };
    }
};
window.ensureModalFunctions();
</script>
<?php if (isset($_GET['page']) && $_GET['page'] === 'school-calendar'): ?>
<script src="./scripts/school-calendar.js"></script>
<?php endif; ?>

<!-- Simple initialization -->
<script>

// Test if we can create a simple function
window.testFunction = function() {
    alert('JavaScript is working!');
};

// Simple test function for modal
// window.testModalShow = function() {
//     const modal = document.getElementById('addDepartmentModal');
//     if (modal) {
//         modal.style.display = 'flex';
        
//         // Initialize color picker
//         setTimeout(() => {
//             initializeColorPicker();
//             checkFormValidity();
//         }, 100);
//     } else {
//         console.error('❌ Modal not found');
//     }
// };

// Simple test to see if DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    
    if (typeof initializeTooltips === 'function') {
        initializeTooltips();
    }
});

// IMMEDIATE TEST - This should run as soon as the script loads

// Back to Top functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Get current page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'dashboard';
    
    // Exclude back-to-top button from course material requests pages and book requests page (dashboard.js handles all pages now)
    const excludedPages = ['reference-requests', 'course-material-requests', 'my-requests', 'book-requests'];
    
    if (!excludedPages.includes(currentPage)) {
        
        // Create back to top button with icon and text
        const backToTopButton = document.createElement('button');
        backToTopButton.className = 'back-to-top';
        backToTopButton.setAttribute('aria-label', 'Back to top');
        
        // Create icon element
        const icon = document.createElement('img');
        icon.src = '../src/assets/icons/go-back-icon.png';
        icon.alt = 'Back to Top';
        icon.className = 'arrow';
        
        // Create text element
        const text = document.createElement('span');
        text.className = 'text';
        text.textContent = 'Back to Top';
        
        // Append icon and text to button
        backToTopButton.appendChild(icon);
        backToTopButton.appendChild(text);
        
        // Append button to body
        document.body.appendChild(backToTopButton);

        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Scroll to top when clicked
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Test button removed - back-to-top functionality is working
        
        // Back-to-top button ready - will show when scrolling down
    } else {
    }
});

// Fallback: Try to create button immediately if DOM is already ready
if (document.readyState === 'loading') {
} else {
    
    // Get current page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'dashboard';
    
    // Exclude back-to-top button from course material requests pages and book requests page (dashboard.js handles all pages now)
    const excludedPages = ['reference-requests', 'course-material-requests', 'my-requests', 'book-requests'];
    
    if (!excludedPages.includes(currentPage)) {
        
        // Create back to top button with icon and text
        const backToTopButton = document.createElement('button');
        backToTopButton.className = 'back-to-top';
        backToTopButton.setAttribute('aria-label', 'Back to top');
        
        // Create icon element
        const icon = document.createElement('img');
        icon.src = '../src/assets/icons/go-back-icon.png';
        icon.alt = 'Back to Top';
        icon.className = 'arrow';
        
        // Create text element
        const text = document.createElement('span');
        text.className = 'text';
        text.textContent = 'Back to Top';
        
        // Append icon and text to button
        backToTopButton.appendChild(icon);
        backToTopButton.appendChild(text);
        
        // Append button to body
        document.body.appendChild(backToTopButton);

        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Scroll to top when clicked
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Fallback test button removed - back-to-top functionality is working
        
        // Back-to-top button ready - will show when scrolling down
    }
}
</script>

<!-- Notification System - pass server data to avoid fetch issues -->
<script>
window.INITIAL_NOTIFICATIONS = <?php echo json_encode($pageNotifications); ?>;
</script>
<script src="./js/notifications.js?v=<?php echo time(); ?>"></script>
</body>
</html>
