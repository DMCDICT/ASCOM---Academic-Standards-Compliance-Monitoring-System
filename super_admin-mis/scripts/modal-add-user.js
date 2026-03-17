console.log('🚀 MODAL-ADD-USER.JS LOADING - ULTRA CLEAN VERSION');

// Ultra simple modal functions
function openAddUserModal() {
    console.log('✅ openAddUserModal called');
    if (typeof createCompleteModal === 'function') {
        createCompleteModal();
        console.log('✅ Modal opened via createCompleteModal');
    } else {
        console.error('❌ createCompleteModal function not found');
    }
}

function closeAddUserModal() {
    console.log('✅ closeAddUserModal called');
    if (typeof closeCompleteModal === 'function') {
        closeCompleteModal();
        console.log('✅ Modal closed via closeCompleteModal');
    }
}

function openAddUserSuccessModal(message) {
    console.log('✅ openAddUserSuccessModal called');
    const modal = document.getElementById('addUserSuccessModal');
    const messageElement = document.getElementById('addUserSuccessMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
        console.log('✅ Success modal opened');
    } else {
        console.error('❌ Success modal elements not found');
        alert('Success: ' + message);
    }
}

function closeAddUserSuccessModal() {
    console.log('✅ closeAddUserSuccessModal called');
    const modal = document.getElementById('addUserSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        console.log('✅ Success modal closed');
    }
}

function openAddUserErrorModal(message) {
    console.log('✅ openAddUserErrorModal called');
    const modal = document.getElementById('addUserErrorModal');
    const messageElement = document.getElementById('addUserErrorMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        modal.style.display = 'flex';
        console.log('✅ Error modal opened');
    } else {
        console.error('❌ Error modal elements not found');
        alert('Error: ' + message);
    }
}

function closeAddUserErrorModal() {
    console.log('✅ closeAddUserErrorModal called');
    const modal = document.getElementById('addUserErrorModal');
    if (modal) {
        modal.style.display = 'none';
        console.log('✅ Error modal closed');
    }
}

function checkUserFormValidity() {
    console.log('✅ checkUserFormValidity called');
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
    
    console.log('✅ Form validation result:', isValid);
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

console.log('✅ All modal functions defined and made global');

// Wait for DOM and set up form handling when modal is created
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM ready - modal functions available');
});

console.log('✅ MODAL-ADD-USER.JS LOADED SUCCESSFULLY');