/*
 * program-management.js
 * Contains JavaScript for the program management page, including opening/closing modals,
 * form validation, color picker logic, and AJAX submission.
 * Path to assets: From department-dean/scripts/ to src/assets/
 */


// Define animated icon path relative to this JS file's location (department-dean/scripts/)
const ANIMATED_CHECK_ICON_PROGRAM = '/DataDrift/ASCOM%20Monitoring%20System/src/assets/animated_icons/check-animated-icon.gif';
const ANIMATED_ERROR_ICON_PROGRAM = '/DataDrift/ASCOM%20Monitoring%20System/src/assets/animated_icons/error2-animated-icon.gif';

// Functions to open/close modals (global scope for onclick attributes in HTML)
function openAddProgramModal() {
    // Close the No Programs modal first
    const noProgramsModal = document.getElementById('noProgramsModal');
    if (noProgramsModal) {
        noProgramsModal.classList.remove('show');
    }
    
    // Then open the Add Program modal with higher z-index
    const addProgramModal = document.getElementById('addProgramModal');
    addProgramModal.style.display = 'flex';
    addProgramModal.style.zIndex = '10001'; // Higher than No Programs modal (10000)
    
    // Prevent body scroll while preserving scroll position
    const scrollY = window.scrollY;
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Store scroll position for restoration
    document.body.setAttribute('data-scroll-y', scrollY);

    // Manually clear ALL form fields to ensure they're empty
    const programCodeInput = document.getElementById('modal_program_code');
    const programNameInput = document.getElementById('modal_program_name');
    const programMajorInput = document.getElementById('modal_program_major');
    
    if (programCodeInput) programCodeInput.value = '';
    if (programNameInput) programNameInput.value = '';
    if (programMajorInput) programMajorInput.value = '';
    
    // Check form validity after clearing all fields
    setTimeout(() => {
        if (typeof checkFormValidity === 'function') {
            checkFormValidity();
            
            // Double-check the button state
            const createBtn = document.querySelector('#addProgramModal .create-btn');
            if (createBtn) {
                    disabled: createBtn.disabled,
                    text: createBtn.textContent,
                    classes: createBtn.className
                });
            }
        } else {
            console.error("Error: checkFormValidity function not defined when openAddProgramModal is called. It needs to be global.");
        }
    }, 50); // Slightly longer delay to ensure DOM is updated
}

function closeAddProgramModal() {
    document.getElementById('addProgramModal').style.display = 'none';
    
    // Restore body scroll and scroll position when modal is closed
    const scrollY = document.body.getAttribute('data-scroll-y');
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.height = '';
    
    // Restore scroll position
    if (scrollY) {
        window.scrollTo(0, parseInt(scrollY));
        document.body.removeAttribute('data-scroll-y');
    }
}

function openSuccessModal(message) {
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').style.display = 'flex';
    
    // Prevent body scroll while preserving scroll position
    const scrollY = window.scrollY;
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Store scroll position for restoration
    document.body.setAttribute('data-scroll-y', scrollY);
    
    const modalIcon = document.getElementById('modalIcon');
    if (modalIcon) {
        modalIcon.src = ANIMATED_CHECK_ICON_PROGRAM;
    } else {
        console.error('Modal icon element not found!');
    }
    // Update modal title to "Success!"
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Success!';
        modalTitle.style.color = 'green';
    }
}

function openErrorModal(message) {
    document.getElementById('successMessage').innerText = message;
    document.getElementById('successModal').style.display = 'flex';
    
    // Prevent body scroll while preserving scroll position
    const scrollY = window.scrollY;
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Store scroll position for restoration
    document.body.setAttribute('data-scroll-y', scrollY);
    
    const modalIcon = document.getElementById('modalIcon');
    if (modalIcon) {
        modalIcon.src = ANIMATED_ERROR_ICON_PROGRAM;
    } else {
        console.error('Modal icon element not found!');
    }
    // Update modal title to "Error!"
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Error!';
        modalTitle.style.color = '#dc3545';
    }
}

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    
    // Restore body scroll and scroll position when modal is closed
    const scrollY = document.body.getAttribute('data-scroll-y');
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.height = '';
    
    // Restore scroll position
    if (scrollY) {
        window.scrollTo(0, parseInt(scrollY));
        document.body.removeAttribute('data-scroll-y');
    }
    
    // Reload the page to show the new program after user closes the modal
    window.location.reload();
}

// Function to check form validity (GLOBAL scope - called by openAddProgramModal)
function checkFormValidity() {
    
    const addProgramForm = document.getElementById("addProgramForm");
    if (!addProgramForm) {
        console.error('❌ Form not found');
        return; 
    }
    
    const createBtn = addProgramForm.querySelector(".create-btn");
    const requiredFields = Array.from(addProgramForm.querySelectorAll("input[required]"));
    
    requiredFields.forEach(field => {
    });

    const otherFieldsFilled = requiredFields.every(field => field.value.trim() !== "");

        otherFieldsFilled
    });

    if (createBtn) {
        const shouldBeDisabled = !otherFieldsFilled;
        createBtn.disabled = shouldBeDisabled;
    } else {
        console.error('❌ Create button not found');
    }
}

// All JavaScript that interacts with specific HTML elements should be inside DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
    const addProgramForm = document.getElementById("addProgramForm");
    if (!addProgramForm) { 
        return;
    }

    const createBtn = addProgramForm.querySelector(".create-btn");
    const requiredFields = Array.from(addProgramForm.querySelectorAll("input[required]"));

    checkFormValidity();

    requiredFields.forEach(field => {
        field.addEventListener("input", checkFormValidity);
        field.addEventListener("change", checkFormValidity); 
    });

    addProgramForm.addEventListener("submit", function (event) {
        event.preventDefault(); 

        fetch('./process_add_program.php', { 
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, message: ${text}`); });
            }
            return response.json(); 
        })
        .then(data => {
            if (data.success) {
                closeAddProgramModal(); 
                openSuccessModal(data.message); 
                
                // Don't reload automatically - let user close the modal manually
                // The program will be visible when they refresh or navigate back
            } else {
                // Handle error response
                openErrorModal(data.message || 'An error occurred while creating the program.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            openErrorModal('An error occurred while creating the program. Please try again.');
        });
    });

    // Expand/Collapse Programs functionality
    const viewAllProgramsButton = document.getElementById('viewAllProgramsButton');
    const collapseProgramsButton = document.getElementById('collapseProgramsButton');
    
    if (viewAllProgramsButton) {
        viewAllProgramsButton.addEventListener('click', function() {
            const hiddenCards = document.querySelectorAll('#programContainer .department-card.hidden');
            hiddenCards.forEach(card => {
                card.classList.remove('hidden');
            });
            // Hide expand button and show collapse button
            this.style.display = 'none';
            if (collapseProgramsButton) {
                collapseProgramsButton.style.display = 'inline-flex';
            }
        });
    }
    
    if (collapseProgramsButton) {
        collapseProgramsButton.addEventListener('click', function() {
            const allProgramCards = document.querySelectorAll('#programContainer .department-card:not(.all-courses-card)');
            allProgramCards.forEach((card, index) => {
                if (index >= 5) {
                    card.classList.add('hidden');
                }
            });
            // Hide collapse button and show expand button
            this.style.display = 'none';
            if (viewAllProgramsButton) {
                viewAllProgramsButton.style.display = 'inline-flex';
            }
        });
    }
});

// Add Program Button functionality
document.addEventListener("DOMContentLoaded", function () {
    const addProgramButton = document.getElementById('addProgramButton');
    if (addProgramButton) {
        addProgramButton.addEventListener('click', function() {
            openAddProgramModal();
        });
    }
}); 