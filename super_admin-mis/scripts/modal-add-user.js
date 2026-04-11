
// Ultra simple modal functions
function openAddUserModal() {
    if (typeof createCompleteModal === 'function') {
        createCompleteModal();
    } else {
        console.error('❌ createCompleteModal function not found');
    }
}

function closeAddUserModal() {
    if (typeof closeCompleteModal === 'function') {
        closeCompleteModal();
    }
}

function openAddUserSuccessModal(message) {
    const modal = document.getElementById('addUserSuccessModal');
    const messageElement = document.getElementById('addUserSuccessMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
    } else {
        console.error('❌ Success modal elements not found');
        alert('Success: ' + message);
    }
}

function closeAddUserSuccessModal() {
    const modal = document.getElementById('addUserSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function openAddUserErrorModal(message) {
    const modal = document.getElementById('addUserErrorModal');
    const messageElement = document.getElementById('addUserErrorMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
    } else {
        console.error('❌ Error modal elements not found');
        alert('Error: ' + message);
    }
}

function closeAddUserErrorModal() {
    const modal = document.getElementById('addUserErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function checkUserFormValidity() {
    const form = document.getElementById('addUserForm');
    if (!form) {
        console.error('❌ addUserForm not found');
        return false;
    }
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'red';
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Make functions globally available
window.openAddUserModal = openAddUserModal;
window.closeAddUserModal = closeAddUserModal;
window.openAddUserSuccessModal = openAddUserSuccessModal;
window.closeAddUserSuccessModal = closeAddUserSuccessModal;
window.openAddUserErrorModal = openAddUserErrorModal;
window.closeAddUserErrorModal = closeAddUserErrorModal;
window.checkUserFormValidity = checkUserFormValidity;


// Wait for DOM and set up form handling when modal is created
document.addEventListener('DOMContentLoaded', function() {
});

