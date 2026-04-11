/*
 * modal-add-department.js
 * Contains JavaScript for the add department modal, including opening/closing,
 * form validation, color picker logic, and AJAX submission.
 */


// Define animated icon paths
const ANIMATED_CHECK_ICON_DEPT = '../src/assets/animated_icons/check-animated-icon.gif';
const ANIMATED_ERROR_ICON_DEPT = '../src/assets/animated_icons/error2-animated-icon.gif';

// Make functions globally available immediately
window.openAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    
    if (modal) {
        modal.style.display = 'flex';
    } else {
        console.error('Modal element not found');
    }
    
    const form = document.getElementById('addDepartmentForm');
    if (form) {
        form.reset();
    }
    
    const defaultColor = "#4A7DFF"; 
    const colorPicker = document.getElementById("colorPicker");
    const colorHex = document.getElementById("colorHex");
    const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
    
    if (colorPicker && colorHex && colorSwatchDisplay) {
        colorPicker.value = defaultColor;
        colorHex.value = defaultColor;
        colorSwatchDisplay.style.backgroundColor = defaultColor;
    }
    
    if (typeof checkFormValidity === 'function') {
        checkFormValidity();
    }
    
};

window.closeAddDepartmentModal = function() {
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
        modal.style.display = 'none';
    }
};

window.openSuccessModal = function(message) {
    const successMessage = document.getElementById('successMessage');
    const successModal = document.getElementById('successModal');
    if (successMessage && successModal) {
        successMessage.innerText = message;
        successModal.style.display = 'flex';
        const successIcon = document.querySelector('#successModal img');
        if (successIcon) {
            successIcon.src = ANIMATED_CHECK_ICON_DEPT;
        }
    }
};

window.closeSuccessModal = function() {
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.style.display = 'none';
    }
};

// Function to check form validity
window.checkFormValidity = function() {
    const addDepartmentForm = document.getElementById("addDepartmentForm");
    if (!addDepartmentForm) return; 
    
    const createBtn = addDepartmentForm.querySelector(".create-btn");
    const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));
    const colorHexInput = document.getElementById("colorHex");
    
    const isColorHexValid = colorHexInput ? colorHexInput.value.trim() !== '' : false;
    const otherFieldsFilled = requiredFields.filter(field => field.id !== 'colorHex').every(field => field.value.trim() !== "");
    const isColorHexFormatValid = colorHexInput ? (colorHexInput.value.trim() === '' || /^#([A-Fa-f0-9]{6})$/.test(colorHexInput.value.trim())) : false;

    if (createBtn) {
        createBtn.disabled = !(otherFieldsFilled && isColorHexValid && isColorHexFormatValid);
    }
};

// Test that functions are available

if (typeof window.openAddDepartmentModal === 'function') {
} else {
    console.error('❌ openAddDepartmentModal is NOT available');
}


// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    
    const modal = document.getElementById('addDepartmentModal');
    if (modal) {
    } else {
        console.error('Modal not found in DOM');
    }

    const addDepartmentForm = document.getElementById("addDepartmentForm");
    if (!addDepartmentForm) { 
        console.error('Add Department Form not found');
        return;
    }

    const createBtn = addDepartmentForm.querySelector(".create-btn");
    const requiredFields = Array.from(addDepartmentForm.querySelectorAll("input[required]"));

    checkFormValidity();

    requiredFields.forEach(field => {
        field.addEventListener("input", checkFormValidity);
        field.addEventListener("change", checkFormValidity); 
    });

    const colorPicker = document.getElementById("colorPicker");
    const colorHex = document.getElementById("colorHex");
    const colorSwatchDisplay = document.getElementById("colorSwatchDisplay");
    const clearColorBtn = document.getElementById("clearColorBtn");

    if (!colorPicker || !colorHex || !colorSwatchDisplay || !clearColorBtn) {
        console.error('Color picker elements not found');
        return;
    }

    const colorNameToHex = {
        'red': '#FF0000', 'green': '#008000', 'blue': '#0000FF', 'yellow': '#FFFF00', 'orange': '#FFA500',
        'purple': '#800080', 'pink': '#FFC0CB', 'black': '#000000', 'white': '#FFFFFF', 'gray': '#808080',
        'brown': '#A52A2A', 'cyan': '#00FFFF', 'magenta': '#FF00FF', 'lime': '#00FF00', 'silver': '#C0C0C0',
        'gold': '#FFD700', 'teal': '#008080', 'navy': '#000080', 'maroon': '#800000', 'olive': '#808000'
    };

    function updateColorDisplay(value, isFromClear = false) { 
        let hexValue = value;
        const isValidHex = /^#([A-Fa-f0-9]{6})$/.test(value);

        if (isValidHex) {
            colorPicker.value = hexValue;
            colorHex.value = hexValue.toUpperCase();
            colorSwatchDisplay.style.backgroundColor = hexValue;
        } else if (isFromClear) {
            colorHex.value = '';
            colorPicker.value = '#000000'; 
            colorSwatchDisplay.style.backgroundColor = '#CCC'; 
        } else {
            colorPicker.value = '#000000'; 
            colorSwatchDisplay.style.backgroundColor = '#CCC'; 
        }
        checkFormValidity(); 
    }

    colorPicker.addEventListener("input", () => { updateColorDisplay(colorPicker.value); });
    colorHex.addEventListener("input", () => {
        let val = colorHex.value.trim();
        const normalizedVal = val.toLowerCase();
        if (colorNameToHex[normalizedVal]) {
            val = colorNameToHex[normalizedVal];
            colorHex.value = val; 
            updateColorDisplay(val); 
            return; 
        }
        if (/^#([A-Fa-f0-9]{6})$/.test(val)) {
            updateColorDisplay(val); 
        } else {
            updateColorDisplay(val, false);
        }
    });
    clearColorBtn.addEventListener("click", () => { updateColorDisplay('', true); });

    updateColorDisplay(colorHex.value); // Initial setup

    addDepartmentForm.addEventListener("submit", function (event) {
        event.preventDefault(); 
        
        
        // Log form data
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
        }

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

                    const deptCountElement = document.querySelector('.dashboard-container .box:nth-child(1) .amount');
                    if (deptCountElement) {
                        deptCountElement.innerText = parseInt(deptCountElement.innerText) + 1;
                    }

                    const noDepartmentsMessage = document.getElementById('noDepartmentsMessage');
                    if (noDepartmentsMessage) { 
                        noDepartmentsMessage.style.display = 'none'; 
                    }

                    const viewAllBtn = document.querySelector('.view-all-btn');
                    if (viewAllBtn) {
                        const currentCards = document.querySelectorAll("#departmentContainer .department-card").length;
                        if (currentCards > 6) { 
                            viewAllBtn.style.display = 'block'; 
                        }
                    }
                } else {
                    console.warn('⚠️ Department container not found or no department data');
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
    });
});

