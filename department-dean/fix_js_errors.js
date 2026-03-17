// fix_js_errors.js - Comprehensive JavaScript Error Fix
// This file contains all the necessary functions to fix the JavaScript errors

console.log('🔧 Loading JavaScript error fixes...');

// 1. FIX: Make toggleSidebar globally available
window.toggleSidebar = function() {
    console.log('🔧 toggleSidebar called');
    const sidebar = document.getElementById('sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (sidebar) {
        if (sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '298px';
            }
            localStorage.setItem('sidebarCollapsed', 'false');
            console.log('✅ Sidebar expanded');
        } else {
            sidebar.classList.add('collapsed');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '115px';
            }
            localStorage.setItem('sidebarCollapsed', 'true');
            console.log('✅ Sidebar collapsed');
        }
    } else {
        console.error('❌ Sidebar element not found');
    }
};

// 2. FIX: Make checkProgramsAndOpenCourseModal globally available
window.checkProgramsAndOpenCourseModal = function() {
    console.log('🔧 checkProgramsAndOpenCourseModal called');
    
    // Clear any draft resume data when opening a new course proposal
    window.draftToResume = null;
    if (window.courseSelectionContext) {
        delete window.courseSelectionContext.isResumingDraft;
    }
    console.log('📝 Cleared draft resume data - opening new course proposal');
    
    // Show the course selection modal first
    if (typeof openCourseSelectionModal === 'function') {
        console.log('Showing course selection modal');
        openCourseSelectionModal();
        return;
    }
    
    // Fallback: If selection modal function doesn't exist, use old behavior
    console.log('Selection modal function not found, using fallback');
    
    // Check if we're on the course-details page - just open the course modal directly
    const currentPage = window.location.search;
    console.log('🔍 Current page:', currentPage);
    if (currentPage.includes('course-details')) {
        console.log('On course details page - opening course modal directly');
        openAddCourseModal();
        return;
    }
    
    // Check if we're on the all-courses page and use its working function
    if (typeof simpleModalTest === 'function') {
        console.log('Using all-courses working function');
        simpleModalTest();
        return;
    }
    
    // If we're on a page that has the hasPrograms variable, use it
    if (typeof hasPrograms !== 'undefined') {
        if (!hasPrograms) {
            showNoProgramsModal();
            return;
        }
    } else {
        // If we don't have the hasPrograms variable, make an AJAX call to check
        fetch('check_programs.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Program check response:', data);
                if (!data.success) {
                    console.error('Program check failed:', data.message);
                    // If the check failed, try to open the modal anyway
                    openAddCourseModal();
                    return;
                }
                if (!data.hasPrograms) {
                    showNoProgramsModal();
                    return;
                }
                // If programs exist, open the course modal
                openAddCourseModal();
            })
            .catch(error => {
                console.error('Error checking programs:', error);
                // Fallback: try to open the modal anyway
                console.log('Falling back to opening course modal directly');
                openAddCourseModal();
            });
        return;
    }
    
    // If we have programs, open the course modal
    openAddCourseModal();
};

// 3. FIX: Make openAddCourseModal globally available
window.openAddCourseModal = function() {
    console.log('🔍 Attempting to open course modal...');
    const courseModal = document.getElementById('addCourseModal');
    console.log('🔍 Modal element:', courseModal);
    
    if (courseModal) {
        console.log('✅ Modal found, setting display to flex');
        courseModal.style.display = 'flex';
        courseModal.style.zIndex = '10000';
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
        
        // Reset form
        const form = document.getElementById('addCourseForm');
        if (form) {
            form.reset();
        }
        
        // Clear program selection
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const programSelectText = document.getElementById('programSelectText');
        if (selectedProgramsInput) selectedProgramsInput.value = '';
        if (programSelectText) programSelectText.textContent = 'Select Program(s)';
        
        // Clear program modal checkboxes
        const modalCheckboxes = document.querySelectorAll('#programSelectModal input[name="programs[]"]');
        modalCheckboxes.forEach(cb => { cb.checked = false; });
        
        // Disable and style create button
        const createBtn = document.getElementById('createCourseBtn');
        if (createBtn) {
            createBtn.disabled = true;
            createBtn.style.backgroundColor = '#6c757d';
            createBtn.style.cursor = 'not-allowed';
        }
        
        // Run form validation after a short delay to ensure all elements are loaded
        setTimeout(() => {
            if (typeof checkFormValidity === 'function') {
                checkFormValidity();
                console.log('✅ Form validation triggered');
            } else {
                console.log('⚠️ checkFormValidity function not found');
            }
        }, 200);
        
        console.log('✅ Course modal opened successfully');
    } else {
        console.error('❌ Course modal not found on this page');
        alert('Course modal not found. Please refresh the page.');
    }
};

// 4. FIX: Make closeAddCourseModal globally available
window.closeAddCourseModal = function() {
    console.log('🔧 closeAddCourseModal called');
    const courseModal = document.getElementById('addCourseModal');
    if (courseModal) {
        courseModal.style.display = 'none';
        
        // Restore body scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
        
        console.log('✅ Course modal closed');
    }
};

// 5. FIX: Make closeProgramSelectModal globally available
window.closeProgramSelectModal = function() {
    console.log('🔧 closeProgramSelectModal called');
    const programModal = document.getElementById('programSelectModal');
    if (programModal) {
        programModal.style.display = 'none';
        
        // Don't clear selections when closing - let the confirm function handle the state
        console.log('✅ Program selection modal closed');
    }
};

// 6. FIX: Make openProgramSelectModal globally available
window.openProgramSelectModal = function() {
    console.log('🔧 openProgramSelectModal called');
    const programModal = document.getElementById('programSelectModal');
    if (programModal) {
        programModal.style.display = 'flex';
        programModal.style.zIndex = '10001';
        
        // Force disable confirm button and clear any selections
        const confirmBtn = document.getElementById('confirmProgramBtn');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.style.backgroundColor = '#6c757d';
            confirmBtn.style.cursor = 'not-allowed';
            confirmBtn.title = 'Please select at least one program';
        }
        
        // Don't clear selections when opening - preserve current selections
        // Only clear if this is the first time opening (no current selections)
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const hasCurrentSelections = selectedProgramsInput && selectedProgramsInput.value.trim() !== '';
        
        console.log('🔍 Current selections:', selectedProgramsInput ? selectedProgramsInput.value : 'No input found');
        console.log('🔍 Has current selections:', hasCurrentSelections);
        
        if (!hasCurrentSelections) {
            console.log('🔍 No current selections - clearing all checkboxes');
            // Only clear if no current selections exist
            const checkboxes = document.querySelectorAll('#programSelectModal input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        } else {
            console.log('🔍 Restoring current selections');
            // Restore current selections
            const currentProgramIds = selectedProgramsInput.value.split(',');
            const checkboxes = document.querySelectorAll('#programSelectModal input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                const isSelected = currentProgramIds.includes(checkbox.value);
                checkbox.checked = isSelected;
                console.log('🔍 Restored checkbox:', checkbox.value, isSelected);
            });
        }
        
        // Update button text to reflect current state
        if (typeof updateProgramButtonText === 'function') {
            updateProgramButtonText();
        }
        
        // Add event listeners to checkboxes for real-time confirm button updates
        const allCheckboxes = document.querySelectorAll('#programSelectModal input[type="checkbox"]');
        allCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (typeof updateConfirmButtonState === 'function') {
                    updateConfirmButtonState();
                }
            });
        });
        
        // Update confirm button state after restoring selections
        if (typeof updateConfirmButtonState === 'function') {
            updateConfirmButtonState();
        }
        
        console.log('✅ Program selection modal opened');
    } else {
        console.error('❌ Program selection modal not found');
    }
};

// 7. FIX: Make confirmProgramSelection globally available
// UPDATED: Fixed element IDs - using 'selectedPrograms' and 'programSelectText' (v2)
window.confirmProgramSelection = function() {
    console.log('🔧 confirmProgramSelection called (v2 - fixed IDs)');
    const selectedPrograms = [];
    const selectedNames = [];
    
    const checkboxes = document.querySelectorAll('#programSelectModal input[name="programs[]"]:checked');
    console.log('🔍 Found checkboxes:', checkboxes.length);
    
    checkboxes.forEach(checkbox => {
        selectedPrograms.push(checkbox.value);
        selectedNames.push(checkbox.dataset.programName);
        console.log('🔍 Selected program:', checkbox.value, checkbox.dataset.programName);
    });
    
    if (selectedPrograms.length === 0) {
        alert('Please select at least one program.');
        return;
    }
    
    console.log('🔍 Selected programs:', selectedPrograms);
    console.log('🔍 Selected names:', selectedNames);
    
    // Update hidden input (CORRECT ID: selectedPrograms, not selectedProgramsInput)
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    console.log('🔍 selectedPrograms element:', selectedProgramsInput);
    if (selectedProgramsInput) {
        const idsStr = selectedPrograms.join(',');
        selectedProgramsInput.value = idsStr;
        selectedProgramsInput.defaultValue = idsStr;
        selectedProgramsInput.setAttribute('value', idsStr);
        selectedProgramsInput.setAttribute('data-persistent-value', idsStr);
        console.log('✅ Updated selectedPrograms value:', idsStr);
        
        // Manually trigger input event to ensure validation is called
        const inputEvent = new Event('input', { bubbles: true });
        selectedProgramsInput.dispatchEvent(inputEvent);
        console.log('✅ Dispatched input event on selectedPrograms');
    } else {
        console.error('❌ selectedPrograms element not found');
    }
    
    // Update button text (CORRECT ID: programSelectText, not programButtonText)
    const programSelectText = document.getElementById('programSelectText');
    console.log('🔍 programSelectText element:', programSelectText);
    if (programSelectText) {
        let displayText = '';
        if (selectedNames.length === 1) {
            displayText = selectedNames[0];
        } else {
            displayText = `${selectedNames.length} Programs Selected`;
        }
        programSelectText.textContent = displayText;
        programSelectText.setAttribute('data-persistent-text', displayText);
        console.log('✅ Updated button text to:', displayText);
    } else {
        console.error('❌ programSelectText element not found');
    }
    
    // Also store in window.selectedProgramsData for persistence
    window.selectedProgramsData = {
        ids: selectedPrograms,
        names: selectedNames
    };
    
    // Store in button data attributes
    const programSelectBtn = document.getElementById('programSelectBtn');
    if (programSelectBtn) {
        programSelectBtn.setAttribute('data-selected-programs', selectedPrograms.join(','));
        programSelectBtn.setAttribute('data-selected-names', JSON.stringify(selectedNames));
        const displayText = selectedNames.length === 1 ? selectedNames[0] : `${selectedNames.length} Programs Selected`;
        programSelectBtn.setAttribute('data-display-text', displayText);
    }
    
    // Store in localStorage
    try {
        localStorage.setItem('lastSelectedPrograms', JSON.stringify({
            ids: selectedPrograms,
            names: selectedNames,
            displayText: selectedNames.length === 1 ? selectedNames[0] : `${selectedNames.length} Programs Selected`,
            timestamp: Date.now()
        }));
    } catch (e) {
        console.warn('Could not save to localStorage:', e);
    }
    
    // Close modal
    closeProgramSelectModal();
    
    // PROPER VALIDATION - CHECK ALL FIELDS
    setTimeout(() => {
        const createBtn = document.getElementById('createCourseBtn');
        if (!createBtn) return;
        
        // Get all field values
        const courseCode = document.getElementById('courseCode')?.value || '';
        const courseName = document.getElementById('courseName')?.value || '';
        const schoolTerm = document.getElementById('schoolTerm')?.value || '';
        const schoolYear = document.getElementById('schoolYear')?.value || '';
        const yearLevel = document.getElementById('yearLevel')?.value || '';
        const selectedPrograms = document.getElementById('selectedPrograms')?.value || '';
        
        console.log('🔧 VALIDATION CHECK:', {
            courseCode: courseCode,
            courseName: courseName,
            schoolTerm: schoolTerm,
            schoolYear: schoolYear,
            yearLevel: yearLevel,
            selectedPrograms: selectedPrograms
        });
        
        // SERIOUS VALIDATION - CHECK ALL FIELDS
        const allFilled = courseCode.trim() && courseName.trim() && schoolTerm && schoolYear && yearLevel && selectedPrograms;
        
        if (allFilled) {
            createBtn.disabled = false;
            createBtn.style.backgroundColor = '#4CAF50';
            createBtn.style.cursor = 'pointer';
            createBtn.style.opacity = '1';
        } else {
            createBtn.disabled = true;
            createBtn.style.backgroundColor = '#6c757d';
            createBtn.style.cursor = 'not-allowed';
            createBtn.style.opacity = '0.6';
        }
    }, 100);
    
    console.log('✅ Program selection confirmed');
};

// 8. FIX: Make updateConfirmButtonState globally available
window.updateConfirmButtonState = function() {
    const checkboxes = document.querySelectorAll('#programSelectModal input[type="checkbox"]');
    const confirmBtn = document.getElementById('confirmProgramBtn');
    
    if (confirmBtn) {
        const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
        confirmBtn.disabled = !hasSelection;
        
        if (hasSelection) {
            confirmBtn.style.backgroundColor = '#4CAF50';
            confirmBtn.style.cursor = 'pointer';
            confirmBtn.title = 'Click to confirm selection';
        } else {
            confirmBtn.style.backgroundColor = '#6c757d';
            confirmBtn.style.cursor = 'not-allowed';
            confirmBtn.title = 'Please select at least one program';
        }
    }
};

// 9. FIX: Make updateProgramButtonText globally available
window.updateProgramButtonText = function() {
    const checkboxes = document.querySelectorAll('#programSelectModal input[type="checkbox"]:checked');
    const buttonText = document.getElementById('programSelectText');
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    
    if (checkboxes.length === 0) {
        if (buttonText) buttonText.textContent = 'Select Program(s)';
        if (selectedProgramsInput) {
            selectedProgramsInput.value = '';
            selectedProgramsInput.defaultValue = '';
        }
    } else if (checkboxes.length === 1) {
        const programName = checkboxes[0].dataset.programName || checkboxes[0].closest('.program-item')?.querySelector('.program-name')?.textContent?.trim() || '1 Program Selected';
        if (buttonText) buttonText.textContent = programName;
        if (selectedProgramsInput) {
            selectedProgramsInput.value = checkboxes[0].value;
            selectedProgramsInput.defaultValue = checkboxes[0].value;
        }
    } else {
        if (buttonText) buttonText.textContent = `${checkboxes.length} Programs Selected`;
        if (selectedProgramsInput) {
            const idsStr = Array.from(checkboxes).map(cb => cb.value).join(',');
            selectedProgramsInput.value = idsStr;
            selectedProgramsInput.defaultValue = idsStr;
        }
    }
    
    // Use the proper validation function instead of just checking programs
    if (typeof checkFormValidity === 'function') {
        checkFormValidity();
    }
};

console.log('✅ JavaScript error fixes loaded successfully');
