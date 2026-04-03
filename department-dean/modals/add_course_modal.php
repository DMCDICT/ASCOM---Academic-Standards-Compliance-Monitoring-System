<?php
// add_course_modal.php - MULTI-STEP PROGRESS FORM VERSION
// 
// Features:
// - 9-step progress form for course proposal submission
// - Progress indicator showing current step
// - Step-by-step validation
// - Review/Summary
// 
// Steps:
// 1. Course Information
// 2. Course Description
// 3. Learning Outcomes
// 4. Course Outline
// 5. Assessment
// 6. Materials
// 7. Attachments
// 8. Justification
// 9. Summary

// Fetch school years from database (like academic terms are fetched)
$schoolYears = [];
try {
    if (isset($pdo)) {
        $schoolYearsQuery = "SELECT id, school_year_label, year_start, year_end, status FROM school_years ORDER BY year_start DESC LIMIT 10";
        $schoolYearsStmt = $pdo->prepare($schoolYearsQuery);
        $schoolYearsStmt->execute();
        $schoolYears = $schoolYearsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error fetching school years in modal: " . $e->getMessage());
}
?>

<!-- Add Course Modal -->
<script>
// DEFINE FUNCTION IMMEDIATELY - BEFORE MODAL HTML LOADS
window._courseFormStep = window._courseFormStep || 1;
window._courseTotalSteps = window._courseTotalSteps || 9;

// Ensure showCloseConfirmationModal is always available (fallback definition)
// This will be overridden by the full definition later, but ensures it exists for onclick handlers
window.showCloseConfirmationModal = window.showCloseConfirmationModal || function(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    console.log('🔔 showCloseConfirmationModal called (fallback)');
    const modal = document.getElementById('closeConfirmationModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.style.zIndex = '10010';
        modal.style.visibility = 'visible';
    } else {
        console.warn('Close confirmation modal not found, closing main modal directly');
        if (window.closeAddCourseModal) {
            window.closeAddCourseModal();
        }
    }
    return false;
};

// Define validation error modal functions FIRST (before they're used)
window.showValidationErrorModal = function(message, missingFields) {
    const modal = document.getElementById('validationErrorModal');
    if (modal) {
        const messageEl = document.getElementById('validationErrorMessage');
        
        if (messageEl) {
            messageEl.textContent = message || 'Please fill in all required fields before proceeding.';
        }
        
        modal.style.display = 'flex';
    } else {
        // Fallback to alert if modal not found
        alert(message || 'Please fill in all required fields before proceeding.');
    }
};

window.closeValidationErrorModal = function() {
    const modal = document.getElementById('validationErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
};

// Define closeCourseDraftSavedModal early so it's available for onclick handlers
window.closeCourseDraftSavedModal = window.closeCourseDraftSavedModal || function() {
    console.log('🔴 closeCourseDraftSavedModal called (early definition)');
    const modal = document.getElementById('courseDraftSavedModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        modal.style.pointerEvents = 'none';
        window._draftSuccessModalOpen = false;
        if (typeof closeAddCourseModal === 'function') {
            closeAddCourseModal();
        }
    }
};

// Define showCourseDraftSavedModal early as a placeholder - will be overridden with full implementation later
// This ensures it exists when saveCourseAsDraft tries to call it
window.showCourseDraftSavedModal = function(courseData) {
    console.log('💾 showCourseDraftSavedModal called (placeholder - will be overridden)');
    // Try to show the modal even with placeholder - in case real function didn't load
    const modal = document.getElementById('courseDraftSavedModal');
    if (modal) {
        const courseCodeEl = document.getElementById('draftCourseCode');
        const courseNameEl = document.getElementById('draftCourseName');
        if (courseCodeEl && courseData) courseCodeEl.textContent = courseData.course_code || '—';
        if (courseNameEl && courseData) courseNameEl.textContent = courseData.course_name || '—';
        modal.style.display = 'flex';
        modal.style.zIndex = '10020';
        modal.style.visibility = 'visible';
        console.log('✅ Placeholder modal shown');
    } else {
        console.error('❌ Draft saved modal not found in DOM (placeholder)');
    }
};

// Check if step 1 has any filled fields
window.hasStep1Data = function() {
    // Check course code
    const courseCode = document.getElementById('courseCode');
    if (courseCode && courseCode.value && courseCode.value.trim() !== '') {
        return true;
    }
    
    // Check course name
    const courseName = document.getElementById('courseName');
    if (courseName && courseName.value && courseName.value.trim() !== '') {
        return true;
    }
    
    // Check selected programs
    const selectedPrograms = document.getElementById('selectedPrograms');
    if (selectedPrograms && selectedPrograms.value && selectedPrograms.value.trim() !== '') {
        return true;
    }
    
    // Check units
    const units = document.getElementById('units');
    if (units && units.value && units.value.trim() !== '' && units.value !== '0') {
        return true;
    }
    
    // Check academic term
    const academicTerm = document.getElementById('academicTerm');
    if (academicTerm && academicTerm.value && academicTerm.value !== '' && academicTerm.value !== '0') {
        return true;
    }
    
    // Check academic year
    const academicYear = document.getElementById('academicYear');
    if (academicYear && academicYear.value && academicYear.value !== '' && academicYear.value !== '0') {
        return true;
    }
    
    // Check year level
    const yearLevel = document.getElementById('yearLevel');
    if (yearLevel && yearLevel.value && yearLevel.value !== '' && yearLevel.value !== '0') {
        return true;
    }
    
    // Check lecture hours
    const lectureHours = document.getElementById('lectureHours');
    if (lectureHours && lectureHours.value && lectureHours.value.trim() !== '' && lectureHours.value !== '0') {
        return true;
    }
    
    // Check laboratory hours
    const laboratoryHours = document.getElementById('laboratoryHours');
    if (laboratoryHours && laboratoryHours.value && laboratoryHours.value.trim() !== '' && laboratoryHours.value !== '0') {
        return true;
    }
    
    // Check prerequisites
    const prerequisites = document.getElementById('prerequisites');
    if (prerequisites && prerequisites.value && prerequisites.value.trim() !== '') {
        return true;
    }
    
    return false;
};

// Define close confirmation functions EARLY - before modal HTML loads
// Check if form has any data entered
window.hasFormData = function() {
    // Check if any form field has been filled
    const form = document.getElementById('addCourseForm');
    if (!form) return false;
    
    // Check various form fields
    const inputs = form.querySelectorAll('input[type="text"], input[type="number"], textarea, select');
    for (let input of inputs) {
        if (input.value && input.value.trim() !== '' && input.value !== '0') {
            return true;
        }
    }
    
    // Check learning outcomes
    const outcomes = form.querySelectorAll('.outcome-input');
    for (let outcome of outcomes) {
        if (outcome.value && outcome.value.trim() !== '') {
            return true;
        }
    }
    
    // Check course outline
    const topics = form.querySelectorAll('.topic-input, .topic-description');
    for (let topic of topics) {
        if (topic.value && topic.value.trim() !== '') {
            return true;
        }
    }
    
    // Check assessment
    const assessments = form.querySelectorAll('.assessment-type-input');
    for (let assessment of assessments) {
        if (assessment.value && assessment.value.trim() !== '') {
            return true;
        }
    }
    
    // Check materials
    const materials = form.querySelectorAll('.material-title-input');
    for (let material of materials) {
        if (material.value && material.value.trim() !== '') {
            return true;
        }
    }
    
    // Check attachments
    if (window.attachmentFiles && window.attachmentFiles.length > 0) {
        return true;
    }
    
    return false;
};

// Wrapper function for onclick handlers (event may not be available in HTML onclick)
window.handleCloseClick = function(e) {
    e = e || window.event;
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    return window.showCloseConfirmationModal(e);
};

// Show confirmation modal before closing
window.showCloseConfirmationModal = function(event) {
    // Prevent any default behavior and stop propagation
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    console.log('🔔 showCloseConfirmationModal called');
    
    // Check if step 1 has any data - if not, close directly without confirmation
    if (typeof window.hasStep1Data === 'function') {
        const hasStep1 = window.hasStep1Data();
        console.log('📋 Step 1 has data?', hasStep1);
        
        if (!hasStep1) {
            console.log('✅ Step 1 is empty, closing modal directly without confirmation');
            // Step 1 is empty, close directly without showing confirmation
            if (typeof window.closeAddCourseModal === 'function') {
                window.closeAddCourseModal();
            }
            return false;
        }
    }
    
    // Check if modal element exists
    const modal = document.getElementById('closeConfirmationModal');
    if (!modal) {
        console.error('❌ closeConfirmationModal element not found in DOM');
        // Fallback: just close the modal directly
        if (typeof window.closeAddCourseModal === 'function') {
            window.closeAddCourseModal();
        }
        return false;
    }
    
    console.log('✅ Modal element found - showing confirmation modal');
    
    // Step 1 has data, so show the confirmation modal
    // This gives them the option to Save as Draft, Discard, or Cancel
    // Ensure it appears above the main modal with high z-index
    
    // Show the modal - use multiple methods to ensure it's visible
    // Set all styles via setAttribute to ensure they apply
    modal.setAttribute('style', 
        'display: flex !important; ' +
        'z-index: 10015 !important; ' +
        'visibility: visible !important; ' +
        'opacity: 1 !important; ' +
        'pointer-events: auto !important;'
    );
    
    // Also set individual styles as backup
    modal.style.display = 'flex';
    modal.style.zIndex = '10015';
    modal.style.visibility = 'visible';
    modal.style.opacity = '1';
    modal.style.pointerEvents = 'auto';
    
    // Force the modal content to be visible and clickable
    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.style.zIndex = '10016';
        modalContent.style.position = 'relative';
        modalContent.style.pointerEvents = 'auto';
    }
    
    // Ensure the main modal stays open (don't close it)
    const mainModal = document.getElementById('addCourseModal');
    if (mainModal) {
        // Keep main modal open but ensure confirmation modal is on top
        console.log('Main modal is open, showing confirmation on top');
    }
    
    console.log('✅ Close confirmation modal should now be visible');
    console.log('Modal display:', modal.style.display);
    console.log('Modal z-index:', modal.style.zIndex);
    
    return false;
};

// Close confirmation modal
window.closeCloseConfirmationModal = function() {
    const modal = document.getElementById('closeConfirmationModal');
    if (modal) {
        // Use setAttribute to ensure styles are applied
        modal.setAttribute('style', 
            'display: none !important; ' +
            'z-index: 10015 !important; ' +
            'visibility: hidden !important; ' +
            'opacity: 0 !important; ' +
            'pointer-events: none !important;'
        );
        // Also set individual styles as backup
        modal.style.display = 'none';
        modal.style.pointerEvents = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    }
};

// Close modal without confirmation (used after confirmation)
window.closeAddCourseModal = function() {
    // Prevent closing if draft success modal is currently open
    if (window._draftSuccessModalOpen === true) {
        const draftModal = document.getElementById('courseDraftSavedModal');
        if (draftModal && window.getComputedStyle(draftModal).display !== 'none') {
            console.log('⚠️ Preventing main modal close - draft success modal is still open');
            return;
        }
    }
    
    const modal = document.getElementById('addCourseModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Clear draft resume data when closing modal
    window.draftToResume = null;
    window.loadedDraftData = null;
    if (window.courseSelectionContext) {
        delete window.courseSelectionContext.isResumingDraft;
        delete window.courseSelectionContext.proposalId;
    }
    console.log('📝 Cleared draft resume data on modal close');
};

// Save as draft
window.saveCourseAsDraft = async function(event) {
    console.log('💾 Saving course as draft...');
    
    const triggerBtn = event?.currentTarget || document.querySelector('[data-save-draft-btn]');
    const originalText = triggerBtn ? triggerBtn.textContent : null;
    
    if (triggerBtn) {
        triggerBtn.disabled = true;
        triggerBtn.textContent = 'Saving...';
    }
    
    const { payload, courseSummary, error } = buildCourseDraftPayload();
    
    if (error) {
        console.warn('Draft payload error:', error);
        if (typeof window.showValidationErrorModal === 'function') {
            window.showValidationErrorModal(error, []);
        } else {
            alert(error);
        }
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.textContent = originalText || 'Save as Draft';
        }
        return;
    }
    
    try {
        const response = await fetch('api/save_course_draft.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include', // Include cookies for session authentication
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        console.log('Draft save response:', data);
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to save course draft.');
        }
        
        if (data.success) {
            console.log('✅ Draft saved successfully, showing success modal first...');
            
            // Close confirmation modal first
            if (typeof window.closeCloseConfirmationModal === 'function') {
                window.closeCloseConfirmationModal();
            }
            
            // Show the success modal FIRST before clearing the form
            console.log('showCourseDraftSavedModal function exists?', typeof window.showCourseDraftSavedModal);
            
            // Function to show the modal (with retry if function not loaded yet)
            const showDraftSavedModal = (retryCount = 0) => {
                if (typeof window.showCourseDraftSavedModal === 'function') {
                    console.log('Calling showCourseDraftSavedModal...');
                    try {
                        // Call the modal function - it has its own error handling
                        window.showCourseDraftSavedModal({
                            course_code: courseSummary?.course_code || '',
                            course_name: courseSummary?.course_name || '',
                            program: courseSummary?.program_display || ''
                        });
                        console.log('✅ showCourseDraftSavedModal called successfully');
                        
                        // Clear the form AFTER showing the modal, with a small delay to ensure modal is visible
                        setTimeout(() => {
                            if (typeof window.clearCourseFormAfterDraftSave === 'function') {
                                window.clearCourseFormAfterDraftSave();
                            }
                        }, 300);
                    } catch (modalError) {
                        console.error('❌ Error calling showCourseDraftSavedModal:', modalError);
                        console.error('Error stack:', modalError.stack);
                        // Don't show alert here - let the modal function handle it
                        // Just close the main modal
                        if (typeof window.closeAddCourseModal === 'function') {
                            window.closeAddCourseModal();
                        }
                    }
                } else if (retryCount < 5) {
                    // Retry after a short delay - function might still be loading
                    console.log(`⏳ showCourseDraftSavedModal not found, retrying... (${retryCount + 1}/5)`);
                    setTimeout(() => showDraftSavedModal(retryCount + 1), 100);
                } else {
                    console.error('❌ showCourseDraftSavedModal function not found after 5 retries');
                    console.error('This should not happen - modal function should be defined');
                    // Try to show modal manually as last resort
                    const modal = document.getElementById('courseDraftSavedModal');
                    if (modal) {
                        const courseCodeEl = document.getElementById('draftCourseCode');
                        const courseNameEl = document.getElementById('draftCourseName');
                        if (courseCodeEl) courseCodeEl.textContent = courseSummary?.course_code || '—';
                        if (courseNameEl) courseNameEl.textContent = courseSummary?.course_name || '—';
                        modal.style.display = 'flex';
                        modal.style.zIndex = '10020';
                        console.log('✅ Manually showed draft saved modal as fallback');
                    } else {
                        console.error('❌ Draft saved modal element not found in DOM');
                    }
                    // Clear the form
                    if (typeof window.clearCourseFormAfterDraftSave === 'function') {
                        window.clearCourseFormAfterDraftSave();
                    }
                }
            };
            
            // Start trying to show the modal
            showDraftSavedModal();
        } else {
            const message = data.message || 'Failed to save course draft.';
            if (typeof showCourseErrorModal === 'function') {
                showCourseErrorModal(message);
            } else {
                alert(message);
            }
        }
    } catch (err) {
        console.error('Error saving course draft:', err);
        if (typeof showCourseErrorModal === 'function') {
            showCourseErrorModal('An unexpected error occurred while saving the draft. Please try again.');
        } else {
            alert('An unexpected error occurred while saving the draft. Please try again.');
        }
    } finally {
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.textContent = originalText || 'Save as Draft';
        }
    }
};

function buildCourseDraftPayload() {
    const form = document.getElementById('addCourseForm');
    if (!form) {
        return { error: 'Course form not found. Please refresh the page and try again.' };
    }
    
    const formData = new FormData(form);
    const getValue = (name) => {
        const value = formData.get(name);
        if (value === null || value === undefined) return '';
        return typeof value === 'string' ? value.trim() : value;
    };
    
    // Check if we're resuming a draft - if so, get previous draft data to merge
    let previousDraftData = null;
    // First check for loaded draft data (from when draft was loaded into form)
    if (window.loadedDraftData) {
        previousDraftData = window.loadedDraftData;
        console.log('📝 Found loaded draft data to merge with current form data');
        console.log('  Previous draft has:', {
            outcomes: previousDraftData.learning_outcomes?.length || 0,
            outline: previousDraftData.course_outline?.length || 0,
            assessments: previousDraftData.assessment_methods?.length || 0,
            materials: previousDraftData.learning_materials?.length || 0
        });
    } else if (window.draftToResume && window.draftToResume.courseData) {
        previousDraftData = window.draftToResume.courseData;
        console.log('📝 Found draftToResume data to merge with current form data');
    }
    
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    let programIds = [];
    if (selectedProgramsInput && selectedProgramsInput.value) {
        programIds = selectedProgramsInput.value.split(',').map(id => id.trim()).filter(Boolean);
    }
    
    const context = window.courseSelectionContext || {};
    if (programIds.length === 0 && context.programId) {
        programIds = [String(context.programId)];
    }
    
    const programNames = [];
    if (window.selectedProgramsData && Array.isArray(window.selectedProgramsData.names)) {
        programNames.push(...window.selectedProgramsData.names);
    } else if (context.programName) {
        programNames.push(context.programName);
    }
    
    let programDisplay = programNames.length === 1 
        ? programNames[0] 
        : (programNames.length > 1 ? `${programNames.length} Programs Selected` : '');
    
    if (!programDisplay) {
        const programSelectText = document.getElementById('programSelectText');
        if (programSelectText && programSelectText.textContent && programSelectText.textContent.trim() !== 'Select Program(s)') {
            programDisplay = programSelectText.textContent.trim();
        }
    }
    
    if (programIds.length === 0) {
        return { error: 'Please select at least one program before saving a draft.' };
    }
    
    const academicTerm = context.term || getValue('academic_term');
    const academicYear = context.academicYear || getValue('academic_year');
    const yearLevel = context.yearLevel || getValue('year_level');
    const courseType = context.courseType || 'proposal';
    
    const academicTermSelect = document.getElementById('academicTerm');
    const academicTermLabel = getSelectedOptionLabel(academicTermSelect);
    
    const academicYearSelect = document.getElementById('academicYear');
    const academicYearLabel = getSelectedOptionLabel(academicYearSelect);
    
    const yearLevelLabel = getYearLevelLabel(yearLevel);
    
    console.log('🔍 Building draft payload - Current step:', window._courseFormStep);
    console.log('🔍 Collecting data from all steps...');
    
    // Collect data from DOM first
    let learningOutcomes = collectLearningOutcomes();
    let courseOutline = collectCourseOutline();
    let assessmentMethods = collectAssessmentMethods();
    let learningMaterials = collectLearningMaterials();
    const attachments = collectAttachmentMetadata();
    
    // Fallback: If collections are empty, try to extract from FormData
    if (learningOutcomes.length === 0) {
        console.log('⚠️ No learning outcomes found in DOM, checking FormData...');
        const formOutcomes = formData.getAll('learning_outcomes[]');
        learningOutcomes = formOutcomes.filter(o => o && o.trim());
        console.log('  Found', learningOutcomes.length, 'outcomes in FormData');
    }
    
    // Merge with previous draft data: use current if exists, otherwise use previous
    if (learningOutcomes.length === 0 && previousDraftData && previousDraftData.learning_outcomes) {
        const prevOutcomes = Array.isArray(previousDraftData.learning_outcomes) 
            ? previousDraftData.learning_outcomes.filter(o => o && o.trim())
            : [];
        if (prevOutcomes.length > 0) {
            learningOutcomes = prevOutcomes;
            console.log('  ✅ Using', learningOutcomes.length, 'outcomes from previous draft (merge)');
        }
    }
    
    if (courseOutline.length === 0) {
        console.log('⚠️ No course outline found in DOM, checking FormData...');
        // Try to extract from FormData - course_outline is an array
        const outlineKeys = Array.from(formData.keys()).filter(k => k.startsWith('course_outline['));
        if (outlineKeys.length > 0) {
            const outlineMap = {};
            outlineKeys.forEach(key => {
                const match = key.match(/course_outline\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const index = match[1];
                    const field = match[2];
                    if (!outlineMap[index]) outlineMap[index] = {};
                    outlineMap[index][field] = formData.get(key);
                }
            });
            courseOutline = Object.values(outlineMap).filter(o => o.topic || o.description || o.hours);
            console.log('  Found', courseOutline.length, 'outline entries in FormData');
        }
    }
    
    // Merge with previous draft data: use current if exists, otherwise use previous
    if (courseOutline.length === 0 && previousDraftData && previousDraftData.course_outline) {
        const prevOutline = Array.isArray(previousDraftData.course_outline) 
            ? previousDraftData.course_outline
            : [];
        if (prevOutline.length > 0) {
            courseOutline = prevOutline;
            console.log('  ✅ Using', courseOutline.length, 'outline entries from previous draft (merge)');
        }
    }
    
    // Merge assessment methods: use current if exists, otherwise use previous
    if (assessmentMethods.length === 0 && previousDraftData && previousDraftData.assessment_methods) {
        const prevAssessments = Array.isArray(previousDraftData.assessment_methods) 
            ? previousDraftData.assessment_methods
            : [];
        if (prevAssessments.length > 0) {
            assessmentMethods = prevAssessments;
            console.log('  ✅ Using', assessmentMethods.length, 'assessment methods from previous draft (merge)');
        }
    }
    
    // Merge learning materials: use current if exists, otherwise use previous
    if (learningMaterials.length === 0 && previousDraftData && previousDraftData.learning_materials) {
        const prevMaterials = Array.isArray(previousDraftData.learning_materials) 
            ? previousDraftData.learning_materials
            : [];
        if (prevMaterials.length > 0) {
            learningMaterials = prevMaterials;
            console.log('  ✅ Using', learningMaterials.length, 'learning materials from previous draft (merge)');
        }
    }
    
    console.log('📊 Draft data summary:');
    console.log('  - Learning Outcomes:', learningOutcomes.length);
    console.log('  - Course Outline entries:', courseOutline.length);
    console.log('  - Assessment Methods:', assessmentMethods.length);
    console.log('  - Learning Materials:', learningMaterials.length);
    console.log('  - Attachments:', attachments.length);
    
    const rawFormSnapshot = snapshotFormData(formData);
    
    const courseCode = getValue('course_code');
    const courseName = getValue('course_name');
    
    const courseEntry = {
        id: 'draft_' + Date.now(),
        course_code: courseCode,
        course_name: courseName,
        units: getValue('units'),
        lecture_hours: getValue('lecture_hours'),
        laboratory_hours: getValue('laboratory_hours'),
        prerequisites: getValue('prerequisites'),
        course_description: getValue('course_description'),
        learning_outcomes: learningOutcomes,
        learning_outcomes_count: learningOutcomes.length,
        course_outline: courseOutline,
        assessment_methods: assessmentMethods,
        learning_materials: learningMaterials,
        learning_materials_count: learningMaterials.length,
        justification: getValue('justification'),
        attachments: attachments,
        attachments_count: attachments.length,
        programs: programIds,
        program_names: programNames,
        program_display: programDisplay,
        academic_term: academicTerm,
        academic_term_label: academicTermLabel,
        academic_year: academicYear,
        academic_year_label: academicYearLabel,
        year_level: yearLevel,
        year_level_label: yearLevelLabel,
        course_type: courseType,
        status: 'Draft',
        isDraft: true,
        saved_step: window._courseFormStep || 1,
        saved_at: new Date().toISOString(),
        _form_snapshot: rawFormSnapshot,
        _context: context
    };
    
    const payload = {
        program_id: programIds[0],
        program_ids: programIds,
        program_names: programNames,
        program_display: programDisplay,
        term: academicTerm,
        academic_term_label: academicTermLabel,
        academic_year: academicYear,
        academic_year_label: academicYearLabel,
        year_level: yearLevel,
        year_level_label: yearLevelLabel,
        course_type: courseType,
        metadata: {
            saved_step: window._courseFormStep || 1,
            saved_at: courseEntry.saved_at
        },
        context: context,
        courses: [courseEntry]
    };
    
    return {
        payload,
        courseSummary: {
            course_code: courseCode,
            course_name: courseName,
            program_display: programDisplay
        }
    };
}

function getSelectedOptionLabel(selectElement) {
    if (!selectElement || !selectElement.options || selectElement.selectedIndex < 0) {
        return '';
    }
    return selectElement.options[selectElement.selectedIndex].textContent.trim();
}

function getYearLevelLabel(value) {
    if (!value) return '';
    const map = {
        '1': '1st Year',
        '2': '2nd Year',
        '3': '3rd Year',
        '4': '4th Year'
    };
    return map[value] || value;
}

function collectLearningOutcomes() {
    const outcomes = [];
    // Use querySelectorAll to find ALL outcome inputs, even if hidden
    const fields = document.querySelectorAll('#learningOutcomesContainer .outcome-input, .outcome-input');
    console.log('📝 Collecting learning outcomes, found', fields.length, 'fields');
    fields.forEach((field, index) => {
        const value = field.value?.trim();
        if (value) {
            outcomes.push(value);
            console.log(`  Outcome ${index + 1}:`, value.substring(0, 50));
        }
    });
    console.log('✅ Collected', outcomes.length, 'learning outcomes');
    return outcomes;
}

function collectCourseOutline() {
    // Use querySelectorAll to find ALL rows, even if hidden
    const rows = document.querySelectorAll('#courseOutlineTableBody tr, .course-outline-row');
    const outline = [];
    console.log('📝 Collecting course outline, found', rows.length, 'rows');
    rows.forEach((row, index) => {
        const topic = row.querySelector('.topic-input')?.value?.trim() || '';
        const description = row.querySelector('.topic-description')?.value?.trim() || '';
        const hours = row.querySelector('.topic-hours')?.value?.trim() || '';
        if (topic || description || hours) {
            outline.push({
                topic: topic,
                week_or_topic: topic, // Keep for backward compatibility
                description: description,
                hours: hours ? parseFloat(hours) : 0.5
            });
            console.log(`  Topic ${index + 1}:`, topic || '(no topic)', '-', description ? description.substring(0, 30) : '(no description)');
        }
    });
    console.log('✅ Collected', outline.length, 'course outline entries');
    return outline;
}

function collectAssessmentMethods() {
    // Use querySelectorAll to find ALL assessment rows, even if hidden
    const rows = document.querySelectorAll('#assessmentTableBody tr, .assessment-method-row');
    const assessments = [];
    console.log('📝 Collecting assessment methods, found', rows.length, 'rows');
    rows.forEach((row, index) => {
        const type = row.querySelector('.assessment-type-input')?.value?.trim() || '';
        const percentage = row.querySelector('.assessment-percentage-input')?.value?.trim() || '';
        const weight = percentage ? parseFloat(percentage) : 0;
        if (type || percentage) {
            assessments.push({
                type: type,
                method: type, // Keep for backward compatibility
                assessment_type: type, // Keep for backward compatibility
                weight: weight,
                percentage: percentage // Keep for backward compatibility
            });
            console.log(`  Assessment ${index + 1}:`, type || '(no type)', '-', percentage || '0', '%');
        }
    });
    console.log('✅ Collected', assessments.length, 'assessment methods');
    return assessments;
}

function collectLearningMaterials() {
    // Use querySelectorAll to find ALL material rows, even if hidden
    const rows = document.querySelectorAll('#learningMaterialsTableBody tr, .material-row');
    const materials = [];
    console.log('📝 Collecting learning materials, found', rows.length, 'rows');
    rows.forEach((row, index) => {
        const callNumber = row.querySelector('.material-call-number-input')?.value?.trim() || '';
        const title = row.querySelector('.material-title-input')?.value?.trim() || '';
        const author = row.querySelector('.material-author-input')?.value?.trim() || '';
        const publisher = row.querySelector('.material-publisher-input')?.value?.trim() || '';
        const year = row.querySelector('.material-year-input')?.value?.trim() || '';
        const type = row.querySelector('.material-type-input')?.value?.trim() || '';
        const remarks = row.querySelector('.material-remarks-input')?.value?.trim() || '';
        
        if (callNumber || title || author || publisher || year || type || remarks) {
            materials.push({
                call_number: callNumber,
                title: title,
                author: author,
                publisher: publisher,
                year: year,
                type: type,
                remarks: remarks
            });
            console.log(`  Material ${index + 1}:`, title || '(no title)', '-', author || '(no author)');
        }
    });
    console.log('✅ Collected', materials.length, 'learning materials');
    return materials;
}

function collectAttachmentMetadata() {
    const attachments = [];
    if (window.attachmentFiles && window.attachmentFiles.length > 0) {
        window.attachmentFiles.forEach(file => {
            attachments.push({
                name: file.name,
                size: file.size,
                type: file.type
            });
        });
        return attachments;
    }
    
    const attachmentInput = document.getElementById('courseAttachments');
    if (attachmentInput && attachmentInput.files && attachmentInput.files.length > 0) {
        Array.from(attachmentInput.files).forEach(file => {
            attachments.push({
                name: file.name,
                size: file.size,
                type: file.type
            });
        });
    }
    
    return attachments;
}

function snapshotFormData(formData) {
    const snapshot = {};
    formData.forEach((value, key) => {
        const normalizedValue = value instanceof File ? value.name : value;
        if (snapshot[key] !== undefined) {
            if (!Array.isArray(snapshot[key])) {
                snapshot[key] = [snapshot[key]];
            }
            snapshot[key].push(normalizedValue);
        } else {
            snapshot[key] = normalizedValue;
        }
    });
    return snapshot;
}

// Discard/Delete proposal - Completely reset modal to fresh state
window.discardCourseProposal = function() {
    console.log('🗑️ Discarding course proposal - resetting everything to fresh state...');
    
    // STEP 0: Close the close confirmation modal first
    if (typeof window.closeCloseConfirmationModal === 'function') {
        window.closeCloseConfirmationModal();
    }
    
    // STEP 1: Clear ALL draft resume data completely - Reset context to fresh state
    window.draftToResume = null;
    window.loadedDraftData = null;
    // Completely reset courseSelectionContext to prevent any resume behavior
    // This ensures that when opening a new course proposal, it's completely fresh
    if (window.courseSelectionContext) {
        const courseType = window.courseSelectionContext.courseType || 'proposal';
        // Create a completely fresh context with only courseType
        window.courseSelectionContext = {
            courseType: courseType
        };
        // Ensure isResumingDraft is completely removed
        delete window.courseSelectionContext.isResumingDraft;
        delete window.courseSelectionContext.proposalId;
        delete window.courseSelectionContext.skipCourseTypeSelection;
    } else {
        // If no context exists, create a fresh one
        window.courseSelectionContext = {
            courseType: 'proposal'
        };
    }
    console.log('✅ Cleared draft resume data on discard - context reset to fresh state');
    
    // STEP 2: Reset form completely
    const form = document.getElementById('addCourseForm');
    if (form) {
        // Reset all form fields
        const fieldsToReset = form.querySelectorAll('input, select, textarea');
        fieldsToReset.forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
        console.log('✅ Reset all form fields');
    }
    
    // STEP 3: Clear Learning Outcomes
    const learningOutcomesContainer = document.getElementById('learningOutcomesContainer');
    if (learningOutcomesContainer) {
        learningOutcomesContainer.innerHTML = '';
        if (typeof window.learningOutcomesCount !== 'undefined') {
            window.learningOutcomesCount = 0;
        }
        if (typeof learningOutcomesCount !== 'undefined') {
            learningOutcomesCount = 0;
        }
        console.log('✅ Cleared learning outcomes');
    }
    
    // STEP 4: Clear Course Outline
    const courseOutlineTableBody = document.getElementById('courseOutlineTableBody');
    if (courseOutlineTableBody) {
        courseOutlineTableBody.innerHTML = '';
        if (typeof window.courseTopicsCount !== 'undefined') {
            window.courseTopicsCount = 0;
        }
        if (typeof courseTopicsCount !== 'undefined') {
            courseTopicsCount = 0;
        }
        console.log('✅ Cleared course outline');
    }
    
    // STEP 5: Clear Assessment Methods
    const assessmentMethodsContainer = document.querySelectorAll('.assessment-method-row, .assessment-type-input, .assessment-weight-input');
    assessmentMethodsContainer.forEach(element => {
        if (element && element.parentElement) {
            element.parentElement.remove();
        }
    });
    const assessmentContainer = document.getElementById('assessmentMethodsContainer');
    if (assessmentContainer) {
        assessmentContainer.innerHTML = '';
    }
    console.log('✅ Cleared assessment methods');
    
    // STEP 6: Clear Learning Materials
    const learningMaterialsTableBody = document.getElementById('learningMaterialsTableBody');
    if (learningMaterialsTableBody) {
        learningMaterialsTableBody.innerHTML = '';
        if (typeof window.materialCount !== 'undefined') {
            window.materialCount = 0;
        }
        if (typeof materialCount !== 'undefined') {
            materialCount = 0;
        }
        console.log('✅ Cleared learning materials');
    }
    
    // STEP 7: Clear Attachments
    if (window.attachmentFiles) {
        window.attachmentFiles = [];
    }
    const attachmentList = document.getElementById('attachmentList');
    if (attachmentList) {
        attachmentList.innerHTML = '';
    }
    const fileInput = document.getElementById('courseAttachments');
    if (fileInput) {
        fileInput.value = '';
    }
    console.log('✅ Cleared attachments');
    
    // STEP 8: Reset all step indicators and progress
    window._courseFormStep = 1;
    if (typeof currentStep !== 'undefined') {
        currentStep = 1;
    }
    
    // Reset form steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    const step1 = document.getElementById('step1');
    if (step1) {
        step1.classList.add('active');
    }
    
    // Reset progress steps
    document.querySelectorAll('.progress-step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    const firstProgressStep = document.querySelector('.progress-step[data-step="1"]');
    if (firstProgressStep) {
        firstProgressStep.classList.add('active');
    }
    
    if (typeof updateProgress === 'function') {
        updateProgress();
    }
    if (typeof updateNavigationButtons === 'function') {
        updateNavigationButtons();
    }
    console.log('✅ Reset all steps and progress indicators');
    
    // STEP 9: Clear localStorage related to drafts (optional - can be commented out if needed)
    try {
        // Only clear if you want to reset program selection too, otherwise skip this
        // localStorage.removeItem('lastSelectedPrograms');
    } catch (e) {
        console.warn('Could not clear localStorage:', e);
    }
    
    // STEP 10: Set flag to indicate modal was discarded - ensures fresh start next time
    window._modalWasDiscarded = true;
    
    // STEP 11: Re-attach event listeners after form is cleared
    if (typeof attachNavigationEventListeners === 'function') {
        setTimeout(() => {
            attachNavigationEventListeners();
        }, 50);
    }
    
    // STEP 12: Close modals
    if (typeof window.closeCloseConfirmationModal === 'function') {
        window.closeCloseConfirmationModal();
    }
    
    // STEP 13: Close the main course modal after discarding
    setTimeout(() => {
        if (typeof window.closeAddCourseModal === 'function') {
            window.closeAddCourseModal();
        }
        console.log('✅ Modal closed after discard - ready for fresh start');
    }, 100);
    
    console.log('✅ Discard complete - modal is now completely empty and ready for new course proposal');
};

// Clear form after successful draft save (keeps modal open but form empty)
window.clearCourseFormAfterDraftSave = function() {
    console.log('🧹 Clearing form after draft save...');
    
    // STEP 0: Clear ALL draft resume data and reset context completely
    window.draftToResume = null;
    window.loadedDraftData = null;
    if (window.courseSelectionContext) {
        const courseType = window.courseSelectionContext.courseType || 'proposal';
        // Create a completely fresh context with only courseType
        window.courseSelectionContext = {
            courseType: courseType
        };
        // Ensure isResumingDraft is completely removed
        delete window.courseSelectionContext.isResumingDraft;
        delete window.courseSelectionContext.proposalId;
        delete window.courseSelectionContext.skipCourseTypeSelection;
    }
    console.log('✅ Cleared draft resume data and reset context');
    
    // STEP 1: Reset form completely
    const form = document.getElementById('addCourseForm');
    if (form) {
        // Reset all form fields
        const fieldsToReset = form.querySelectorAll('input, select, textarea');
        fieldsToReset.forEach(field => {
            // Skip the program selection hidden input to preserve it
            if (field.id === 'selectedPrograms') {
                return;
            }
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
        console.log('✅ Reset all form fields');
    }
    
    // STEP 2: Clear Learning Outcomes
    const learningOutcomesContainer = document.getElementById('learningOutcomesContainer');
    if (learningOutcomesContainer) {
        learningOutcomesContainer.innerHTML = '';
        if (typeof window.learningOutcomesCount !== 'undefined') {
            window.learningOutcomesCount = 0;
        }
        if (typeof learningOutcomesCount !== 'undefined') {
            learningOutcomesCount = 0;
        }
        console.log('✅ Cleared learning outcomes');
    }
    
    // STEP 3: Clear Course Outline
    const courseOutlineTableBody = document.getElementById('courseOutlineTableBody');
    if (courseOutlineTableBody) {
        courseOutlineTableBody.innerHTML = '';
        if (typeof window.courseTopicsCount !== 'undefined') {
            window.courseTopicsCount = 0;
        }
        if (typeof courseTopicsCount !== 'undefined') {
            courseTopicsCount = 0;
        }
        console.log('✅ Cleared course outline');
    }
    
    // STEP 4: Clear Assessment Methods
    const assessmentMethodsContainer = document.querySelectorAll('.assessment-method-row, .assessment-type-input, .assessment-weight-input');
    assessmentMethodsContainer.forEach(element => {
        if (element && element.parentElement) {
            element.parentElement.remove();
        }
    });
    const assessmentContainer = document.getElementById('assessmentMethodsContainer');
    if (assessmentContainer) {
        assessmentContainer.innerHTML = '';
    }
    console.log('✅ Cleared assessment methods');
    
    // STEP 5: Clear Learning Materials
    const learningMaterialsTableBody = document.getElementById('learningMaterialsTableBody');
    if (learningMaterialsTableBody) {
        learningMaterialsTableBody.innerHTML = '';
        if (typeof window.materialCount !== 'undefined') {
            window.materialCount = 0;
        }
        if (typeof materialCount !== 'undefined') {
            materialCount = 0;
        }
        console.log('✅ Cleared learning materials');
    }
    
    // STEP 6: Clear Attachments
    if (window.attachmentFiles) {
        window.attachmentFiles = [];
    }
    const attachmentList = document.getElementById('attachmentList');
    if (attachmentList) {
        attachmentList.innerHTML = '';
    }
    const fileInput = document.getElementById('courseAttachments');
    if (fileInput) {
        fileInput.value = '';
    }
    console.log('✅ Cleared attachments');
    
    // STEP 7: Reset all step indicators and progress
    window._courseFormStep = 1;
    if (typeof currentStep !== 'undefined') {
        currentStep = 1;
    }
    
    // Reset form steps
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });
    const step1 = document.getElementById('step1');
    if (step1) {
        step1.classList.add('active');
    }
    
    // Reset progress steps
    document.querySelectorAll('.progress-step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    const firstProgressStep = document.querySelector('.progress-step[data-step="1"]');
    if (firstProgressStep) {
        firstProgressStep.classList.add('active');
    }
    
    if (typeof updateProgress === 'function') {
        updateProgress();
    }
    if (typeof updateNavigationButtons === 'function') {
        updateNavigationButtons();
    }
    
    // Re-attach event listeners after form is cleared
    if (typeof attachNavigationEventListeners === 'function') {
        setTimeout(() => {
            attachNavigationEventListeners();
        }, 50);
    }
    
    console.log('✅ Reset all steps and progress indicators');
    console.log('✅ Form cleared after draft save');
};


// Define validateCurrentStep BEFORE nextStep so it's available
window.validateCurrentStep = function() {
    try {
        // Get current step from window variable
        const currentStep = window._courseFormStep || 1;
        console.log('=== VALIDATION START ===');
        console.log('validateCurrentStep called for step:', currentStep);
        
        const currentStepElement = document.getElementById(`step${currentStep}`);
        if (!currentStepElement) {
            console.error('Current step element not found for step:', currentStep);
            if (typeof window.showValidationErrorModal === 'function') {
                window.showValidationErrorModal('Error: Could not find form step. Please refresh the page.', []);
            } else {
                alert('Error: Could not find form step. Please refresh the page.');
            }
            return false;
        }
        
        console.log('Step element found:', currentStepElement);
        
        // Clear previous error states
        currentStepElement.querySelectorAll('.form-control, select, textarea, input').forEach(field => {
            if (field.style.borderColor === 'rgb(244, 67, 54)' || field.style.borderColor === '#f44336') {
                field.style.borderColor = '';
                field.style.borderWidth = '';
            }
            field.classList.remove('field-error');
        });
        
        let isValid = true;
        const missingFields = [];
        let firstInvalidField = null;
    
        // Step-specific validations
        if (currentStep === 1) {
            const selectedProgramsInput = document.getElementById('selectedPrograms');
            const programSelectText = document.getElementById('programSelectText');
            if (selectedProgramsInput && (!selectedProgramsInput.value || selectedProgramsInput.value.trim() === '')) {
                isValid = false;
                missingFields.push('Program(s)');
                if (programSelectText) {
                    programSelectText.style.borderColor = '#f44336';
                    programSelectText.style.borderWidth = '2px';
                    if (!firstInvalidField) firstInvalidField = programSelectText;
                }
            }
        }
        
        if (currentStep === 3) {
            const outcomeInputs = currentStepElement.querySelectorAll('.outcome-input');
            let hasAtLeastOneOutcome = false;
            outcomeInputs.forEach(input => {
                if (input.value && input.value.trim()) {
                    hasAtLeastOneOutcome = true;
                }
            });
            if (!hasAtLeastOneOutcome) {
                isValid = false;
                missingFields.push('At least one Learning Outcome');
                const container = document.getElementById('learningOutcomesContainer');
                if (container) {
                    container.style.border = '2px solid #f44336';
                    container.style.borderRadius = '4px';
                    container.style.padding = '10px';
                    if (!firstInvalidField) firstInvalidField = container;
                }
            } else {
                const container = document.getElementById('learningOutcomesContainer');
                if (container) {
                    container.style.border = '';
                    container.style.padding = '';
                }
            }
        }
        
        if (currentStep === 5) {
            // Get all assessment rows from the table body (they may not have .assessment-row class)
            const assessmentTableBody = currentStepElement.querySelector('#assessmentTableBody');
            let hasAtLeastOneAssessment = false;
            
            if (assessmentTableBody) {
                const assessmentRows = assessmentTableBody.querySelectorAll('tr');
                assessmentRows.forEach(row => {
                    // Use class selectors which are consistently used: .assessment-type-input and .assessment-percentage-input
                    // These match both assessment[0][type] and assessment_type[] formats
                    const typeInput = row.querySelector('.assessment-type-input');
                    const percentageInput = row.querySelector('.assessment-percentage-input');
                    if (typeInput && typeInput.value && typeInput.value.trim() && 
                        percentageInput && percentageInput.value && percentageInput.value.trim()) {
                        hasAtLeastOneAssessment = true;
                    }
                });
            }
            
            if (!hasAtLeastOneAssessment) {
                isValid = false;
                missingFields.push('At least one Assessment');
                const table = currentStepElement.querySelector('#assessmentTable');
                if (table) {
                    table.style.border = '2px solid #f44336';
                    table.style.borderRadius = '4px';
                    if (!firstInvalidField) firstInvalidField = table;
                }
            } else {
                const table = currentStepElement.querySelector('#assessmentTable');
                if (table) {
                    table.style.border = '';
                }
            }
        }
        
        // General validation for all required fields
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        console.log('Found required fields:', requiredFields.length);
        
        if (requiredFields.length === 0) {
            console.warn('⚠️ No required fields found in step', currentStep);
        }
        
        requiredFields.forEach((field, index) => {
            const isHidden = field.offsetParent === null || 
                           field.style.display === 'none' || 
                           field.style.visibility === 'hidden' ||
                           field.hasAttribute('hidden');
            
            if (isHidden) {
                console.log(`Skipping hidden required field ${index + 1}:`, field.id || field.name);
                return;
            }
            
            let fieldValue = '';
            let isEmpty = false;
            
            if (field.tagName === 'SELECT') {
                fieldValue = field.value;
                isEmpty = !fieldValue || fieldValue === '' || fieldValue === '0';
            } else if (field.type === 'checkbox' || field.type === 'radio') {
                const name = field.name;
                const checkedFields = currentStepElement.querySelectorAll(`input[name="${name}"]:checked`);
                isEmpty = checkedFields.length === 0;
            } else if (field.type === 'number') {
                fieldValue = field.value;
                isEmpty = fieldValue === '' || fieldValue === null || fieldValue === undefined;
            } else {
                fieldValue = field.value ? field.value.trim() : '';
                isEmpty = !fieldValue;
            }
            
            const fieldName = field.getAttribute('name') || field.getAttribute('id') || `Field ${index + 1}`;
            let fieldLabel = field.closest('.form-group')?.querySelector('label')?.textContent?.replace('*', '').trim() || 
                           field.previousElementSibling?.textContent?.replace('*', '').trim() || 
                           fieldName;
            
            fieldLabel = fieldLabel.replace(/\s+/g, ' ').trim();
            
            console.log(`Checking field ${index + 1}:`, {
                name: fieldName,
                label: fieldLabel,
                value: fieldValue,
                isEmpty: isEmpty,
                type: field.type || field.tagName
            });
            
            if (isEmpty) {
                isValid = false;
                field.style.borderColor = '#f44336';
                field.style.borderWidth = '2px';
                field.classList.add('field-error');
                missingFields.push(fieldLabel);
                
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
                
                const clearError = () => {
                    field.style.borderColor = '';
                    field.style.borderWidth = '';
                    field.classList.remove('field-error');
                };
                
                field.removeEventListener('input', clearError);
                field.removeEventListener('change', clearError);
                field.addEventListener('input', clearError, { once: true });
                field.addEventListener('change', clearError, { once: true });
            } else {
                field.style.borderColor = '';
                field.style.borderWidth = '';
                field.classList.remove('field-error');
            }
        });
    
        console.log('Validation result:', isValid);
        console.log('Missing fields:', missingFields);
    
        if (!isValid) {
            if (firstInvalidField) {
                setTimeout(() => {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
            
            if (typeof window.showValidationErrorModal === 'function') {
                window.showValidationErrorModal('Please fill in all required fields before proceeding.', missingFields);
            } else {
                const missingList = missingFields.length > 0 ? '\n\nPlease fill in:\n• ' + missingFields.join('\n• ') : '';
                alert('Please fill in all required fields before proceeding.' + missingList);
            }
            console.log('Validation failed. Missing fields:', missingFields);
        } else {
            console.log('✅ Validation passed for step', currentStep);
        }
        
        console.log('=== VALIDATION END ===');
        return isValid;
    } catch (error) {
        console.error('Error in validateCurrentStep:', error);
        if (typeof window.showValidationErrorModal === 'function') {
            window.showValidationErrorModal('An error occurred during validation: ' + error.message, []);
        } else {
            alert('An error occurred during validation: ' + error.message);
        }
        return false;
    }
};

window.nextStep = function(event) {
    console.log('🔵 NEXT BUTTON CLICKED');
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    try {
        const currentStep = window._courseFormStep || 1;
        const totalSteps = window._courseTotalSteps || 9;
        
        // ALWAYS validate before proceeding - REQUIRED
        if (typeof window.validateCurrentStep === 'function' || typeof validateCurrentStep === 'function') {
            console.log('🔍 Running validation for step', currentStep);
            const validateFunc = window.validateCurrentStep || validateCurrentStep;
            const isValid = validateFunc();
            console.log('🔍 Validation result:', isValid);
            if (!isValid) {
                console.log('❌ Validation failed - blocking navigation');
                return false;
            }
            console.log('✅ Validation passed - proceeding to next step');
        } else {
            console.error('❌ validateCurrentStep function not found!');
            if (typeof showValidationErrorModal === 'function') {
                showValidationErrorModal('Validation function not loaded. Please refresh the page.', []);
            } else {
                alert('Validation function not loaded. Please refresh the page.');
            }
            return false;
        }
        
        // Only proceed if validation passed
        if (currentStep < totalSteps) {
            const currentStepEl = document.getElementById('step' + currentStep);
            const nextStepEl = document.getElementById('step' + (currentStep + 1));
            
            if (currentStepEl) currentStepEl.classList.remove('active');
            if (nextStepEl) {
                window._courseFormStep = currentStep + 1;
                nextStepEl.classList.add('active');
                
                const currentProgressStep = document.querySelector('.progress-step[data-step="' + currentStep + '"]');
                const nextProgressStep = document.querySelector('.progress-step[data-step="' + (currentStep + 1) + '"]');
                if (currentProgressStep) {
                    currentProgressStep.classList.remove('active');
                    currentProgressStep.classList.add('completed');
                }
                if (nextProgressStep) {
                    nextProgressStep.classList.add('active');
                }
                
                if (typeof updateProgress === 'function') updateProgress();
                if (typeof updateNavigationButtons === 'function') {
                    updateNavigationButtons();
                }
                
                // Explicitly ensure Previous button is shown when on step 2 or later (with multiple attempts)
                if (window._courseFormStep > 1) {
                    const showPrevButton = () => {
                        const prevBtn = document.getElementById('prevStepBtn');
                        if (prevBtn) {
                            // Remove any inline style that might be hiding it
                            if (prevBtn.style.display === 'none') {
                                prevBtn.removeAttribute('style');
                            }
                            prevBtn.style.display = 'inline-flex';
                            prevBtn.style.visibility = 'visible';
                            prevBtn.style.opacity = '1';
                            console.log('✅ Explicitly showing Previous button on step', window._courseFormStep, 'display:', prevBtn.style.display);
                            return true;
                        } else {
                            console.warn('⚠️ Previous button not found yet, will retry...');
                            return false;
                        }
                    };
                    
                    // Try immediately
                    if (!showPrevButton()) {
                        // Retry after a short delay
                        setTimeout(() => {
                            if (!showPrevButton()) {
                                // One more retry
                                setTimeout(showPrevButton, 50);
                            }
                        }, 10);
                    }
                }
                
                if (typeof scrollToTop === 'function') scrollToTop();
                
                // Restore program selection when navigating (especially when returning to step 1)
                if (typeof restoreProgramSelection === 'function') {
                    setTimeout(() => restoreProgramSelection(), 50);
                }
                
                // If we moved to step 3 (Learning Outcomes), initialize with 3 default fields
                // BUT skip if resuming a draft (data will be loaded by loadDraftIntoForm)
                if (window._courseFormStep === 3) {
                    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
                    if (!isResumingDraft) {
                        setTimeout(() => {
                            if (typeof window.initializeLearningOutcomes === 'function') {
                                window.initializeLearningOutcomes();
                            }
                        }, 100);
                    } else {
                        console.log('📝 Skipping learning outcomes initialization - resuming draft');
                    }
                }
                
                // If we moved to step 4 (Course Outline), initialize with 3 default rows
                // BUT skip if resuming a draft (data will be loaded by loadDraftIntoForm)
                if (window._courseFormStep === 4) {
                    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
                    if (!isResumingDraft) {
                        console.log('📍 Navigated to step 4, initializing course outline...');
                        // Call immediately
                        if (typeof window.initializeCourseOutline === 'function') {
                            window.initializeCourseOutline();
                        }
                        // Also call with delays to ensure it works
                        setTimeout(() => {
                            if (typeof window.initializeCourseOutline === 'function') {
                                window.initializeCourseOutline();
                            } else {
                                console.error('❌ initializeCourseOutline function not found!');
                            }
                        }, 150);
                        setTimeout(() => {
                            if (typeof window.initializeCourseOutline === 'function') {
                                window.initializeCourseOutline();
                            }
                        }, 300);
                    } else {
                        console.log('📝 Skipping course outline initialization - resuming draft');
                    }
                }
                
                // If we moved to step 5 (Assessment), initialize with 3 default rows
                // BUT skip if resuming a draft (data will be loaded by loadDraftIntoForm)
                if (window._courseFormStep === 5) {
                    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
                    if (!isResumingDraft) {
                        console.log('📍 Navigated to step 5, initializing assessment...');
                        // Call immediately
                        if (typeof window.initializeAssessment === 'function') {
                            window.initializeAssessment();
                        }
                        // Also call with delays to ensure it works
                        setTimeout(() => {
                            if (typeof window.initializeAssessment === 'function') {
                                window.initializeAssessment();
                            } else {
                                console.error('❌ initializeAssessment function not found!');
                            }
                        }, 150);
                        setTimeout(() => {
                            if (typeof window.initializeAssessment === 'function') {
                                window.initializeAssessment();
                            }
                        }, 300);
                    } else {
                        console.log('📝 Skipping assessment initialization - resuming draft');
                    }
                }
                
                // If we moved to step 7 (Attachments), update attachment list and ensure file input is ready
                if (window._courseFormStep === 7) {
                    if (typeof window.updateAttachmentList === 'function') {
                        console.log('📎 Step 7 active, updating attachment list');
                        // Small delay to ensure DOM is ready
                        setTimeout(() => {
                            window.updateAttachmentList();
                            // Ensure the list is visible
                            const attachmentList = document.getElementById('attachmentList');
                            if (attachmentList) {
                                attachmentList.style.display = 'flex';
                                attachmentList.style.visibility = 'visible';
                                attachmentList.style.opacity = '1';
                            }
                            
                            // Re-initialize file drop zone to ensure handlers are attached when step 7 is visible
                            // Try multiple times with increasing delays to ensure function is available
                            let retryCount = 0;
                            const maxRetries = 5;
                            const tryInit = () => {
                                const initFunc = typeof window.initializeFileDropZone === 'function' ? window.initializeFileDropZone : 
                                               (typeof initializeFileDropZone === 'function' ? initializeFileDropZone : null);
                                
                                if (initFunc) {
                                    console.log('🔄 Re-initializing file drop zone on step 7 activation');
                                    initFunc();
                                } else if (retryCount < maxRetries) {
                                    retryCount++;
                                    console.log(`⏳ initializeFileDropZone not found yet, retrying (${retryCount}/${maxRetries})...`);
                                    setTimeout(tryInit, 100);
                                } else {
                                    console.error('❌ initializeFileDropZone not found after', maxRetries, 'retries');
                                    console.error('Available on window:', typeof window.initializeFileDropZone);
                                    console.error('Available globally:', typeof initializeFileDropZone);
                                }
                            };
                            tryInit();
                        }, 50);
                    }
                }
                
                // If we moved to step 9 (Summary), populate review data and update buttons
                if (window._courseFormStep === 9) {
                    // Update navigation buttons immediately to show "Submit to QA"
                    if (typeof updateNavigationButtons === 'function') {
                        updateNavigationButtons();
                    }
                    
                    // Small delay to ensure DOM is ready
                    setTimeout(() => {
                        const populateFunc = typeof window.populateReviewData === 'function' ? window.populateReviewData : 
                                           (typeof populateReviewData === 'function' ? populateReviewData : null);
                        if (populateFunc) {
                            populateFunc();
                        } else {
                            console.error('❌ populateReviewData function not found');
                        }
                        
                        // Ensure buttons are updated after populating data
                        if (typeof updateNavigationButtons === 'function') {
                            updateNavigationButtons();
                        }
                    }, 50);
                }
            }
        }
        return false;
    } catch (error) {
        console.error('Error in nextStep:', error);
        alert('Error: ' + error.message);
        return false;
    }
};

window.previousStep = function() {
    const currentStep = window._courseFormStep || 1;
    if (currentStep > 1) {
        const currentStepEl = document.getElementById('step' + currentStep);
        const prevStepEl = document.getElementById('step' + (currentStep - 1));
        
        if (currentStepEl) currentStepEl.classList.remove('active');
        if (prevStepEl) {
            window._courseFormStep = currentStep - 1;
            prevStepEl.classList.add('active');
            
            const currentProgressStep = document.querySelector('.progress-step[data-step="' + currentStep + '"]');
            const prevProgressStep = document.querySelector('.progress-step[data-step="' + (currentStep - 1) + '"]');
            if (currentProgressStep) {
                currentProgressStep.classList.remove('active');
                currentProgressStep.classList.remove('completed');
            }
            if (prevProgressStep) {
                prevProgressStep.classList.add('active');
                prevProgressStep.classList.remove('completed');
            }
            
            if (typeof updateProgress === 'function') updateProgress();
            if (typeof updateNavigationButtons === 'function') updateNavigationButtons();
            if (typeof scrollToTop === 'function') scrollToTop();
            
            // If we moved back to step 3 (Learning Outcomes), initialize with 3 default fields
            // BUT skip if resuming a draft (data will be loaded by loadDraftIntoForm)
            if (window._courseFormStep === 3) {
                const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
                if (!isResumingDraft) {
                    setTimeout(() => {
                        if (typeof window.initializeLearningOutcomes === 'function') {
                            window.initializeLearningOutcomes();
                        }
                    }, 100);
                } else {
                    console.log('📝 Skipping learning outcomes initialization - resuming draft');
                }
            }
            
            // If we moved back to step 4 (Course Outline), initialize with 3 default rows
            // BUT skip if resuming a draft (data will be loaded by loadDraftIntoForm)
            if (window._courseFormStep === 4) {
                const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
                if (!isResumingDraft) {
                    console.log('📍 Navigated back to step 4, initializing course outline...');
                    // Call immediately
                    if (typeof window.initializeCourseOutline === 'function') {
                        window.initializeCourseOutline();
                    }
                    // Also call with delays to ensure it works
                    setTimeout(() => {
                        if (typeof window.initializeCourseOutline === 'function') {
                            window.initializeCourseOutline();
                        } else {
                            console.error('❌ initializeCourseOutline function not found!');
                        }
                    }, 150);
                    setTimeout(() => {
                        if (typeof window.initializeCourseOutline === 'function') {
                            window.initializeCourseOutline();
                        }
                    }, 300);
                } else {
                    console.log('📝 Skipping course outline initialization - resuming draft');
                }
            }
        }
    }
};

console.log('✅ Navigation functions defined - window.nextStep type:', typeof window.nextStep);

// Learning Materials Management - Define BEFORE modal HTML
window.materialCount = window.materialCount || 0;

window.addMaterialRow = function() {
    window.materialCount = (window.materialCount || 0) + 1;
    const materialIndex = window.materialCount; // Use local variable for template literal
    const tbody = document.getElementById('learningMaterialsTableBody');
    if (!tbody) {
        console.error('Learning materials table body not found');
        return;
    }
    
    const row = document.createElement('tr');
    row.className = 'material-row';
    row.dataset.materialIndex = materialIndex;
    
    row.innerHTML = `
        <td>
            <div class="call-number-autocomplete">
                <input type="text" class="material-call-number-input" name="material_call_number[]" placeholder="e.g., QA76.9" data-row-index="${materialIndex}">
                <div class="call-number-suggestions" id="suggestions-${materialIndex}"></div>
            </div>
        </td>
        <td>
            <input type="text" class="material-title-input" name="material_title[]" placeholder="e.g., Data Analytics: Concepts & Techniques">
        </td>
        <td>
            <input type="text" class="material-author-input" name="material_author[]" placeholder="e.g., Smith">
        </td>
        <td>
            <input type="text" class="material-publisher-input" name="material_publisher[]" placeholder="e.g., Pearson">
        </td>
        <td>
            <input type="number" class="material-year-input" name="material_year[]" placeholder="2021" min="1900" max="2100">
        </td>
        <td>
            <select class="material-type-input" name="material_type[]">
                <option value="">Select Type</option>
                <option value="Book">Book</option>
                <option value="Journal">Journal</option>
                <option value="Article">Article</option>
                <option value="Online Resource">Online Resource</option>
                <option value="Other">Other</option>
            </select>
        </td>
        <td>
            <button type="button" class="remove-material-btn" onclick="if(typeof window.removeMaterialRow==='function'){window.removeMaterialRow(${materialIndex});}else{console.error('removeMaterialRow not found');} return false;">Remove</button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    // Setup autocomplete for the new call number input
    setTimeout(() => {
        if (typeof setupCallNumberAutocomplete === 'function') {
            setupCallNumberAutocomplete(materialIndex);
        }
    }, 100);
    
    console.log('✅ Material row added, count:', window.materialCount);
};

window.removeMaterialRow = function(index) {
    const row = document.querySelector(`.material-row[data-material-index="${index}"]`);
    if (row) {
        row.remove();
    }
};

// Also define without window for backward compatibility
function addMaterialRow() {
    if (typeof window.addMaterialRow === 'function') {
        window.addMaterialRow();
    }
}

function removeMaterialRow(index) {
    if (typeof window.removeMaterialRow === 'function') {
        window.removeMaterialRow(index);
    }
}

console.log('✅ Material functions defined - window.addMaterialRow type:', typeof window.addMaterialRow);

// Learning Outcomes Functions
let learningOutcomesCount = 0;
window.addLearningOutcome = function() {
    const container = document.getElementById('learningOutcomesContainer');
    if (!container) return;
    
    const outcomeIndex = learningOutcomesCount++;
    const outcomeDiv = document.createElement('div');
    outcomeDiv.className = 'outcome-field';
    outcomeDiv.dataset.outcomeIndex = outcomeIndex;
    outcomeDiv.innerHTML = `
        <div class="outcome-label">Outcome ${outcomeIndex + 1}:</div>
        <div class="outcome-input-wrapper">
            <textarea class="outcome-input" name="learning_outcomes[]" placeholder="Enter learning outcome..." rows="2"></textarea>
            <button type="button" class="remove-outcome-btn" onclick="removeLearningOutcome(${outcomeIndex})">Remove</button>
        </div>
    `;
    container.appendChild(outcomeDiv);
};

// Initialize learning outcomes with 3 default fields
window.initializeLearningOutcomes = function() {
    const container = document.getElementById('learningOutcomesContainer');
    if (!container) {
        console.warn('Learning outcomes container not found');
        return;
    }
    
    // Only initialize if container is empty (don't overwrite existing data)
    const existingOutcomes = container.querySelectorAll('.outcome-field');
    if (existingOutcomes.length === 0) {
        learningOutcomesCount = 0; // Reset counter
        
        // Add 3 learning outcome fields
        for (let i = 0; i < 3; i++) {
            window.addLearningOutcome();
        }
        console.log('Initialized 3 learning outcome fields');
    } else {
        console.log('Learning outcomes already exist, skipping initialization');
    }
};

window.removeLearningOutcome = function(index) {
    const container = document.getElementById('learningOutcomesContainer');
    if (!container) return;
    
    const outcomeField = container.querySelector(`[data-outcome-index="${index}"]`);
    if (outcomeField) {
        outcomeField.remove();
        // Renumber remaining outcomes
        const outcomes = container.querySelectorAll('.outcome-field');
        outcomes.forEach((outcome, idx) => {
            const label = outcome.querySelector('.outcome-label');
            if (label) {
                label.textContent = `Outcome ${idx + 1}:`;
            }
            outcome.dataset.outcomeIndex = idx;
            const removeBtn = outcome.querySelector('.remove-outcome-btn');
            if (removeBtn) {
                removeBtn.setAttribute('onclick', `removeLearningOutcome(${idx})`);
            }
        });
    }
};

function addLearningOutcome() {
    if (typeof window.addLearningOutcome === 'function') {
        window.addLearningOutcome();
    }
}

function removeLearningOutcome(index) {
    if (typeof window.removeLearningOutcome === 'function') {
        window.removeLearningOutcome(index);
    }
}

// Course Outline Functions
let courseTopicsCount = 0;
window.addCourseTopic = function() {
    const tbody = document.getElementById('courseOutlineTableBody');
    if (!tbody) return;
    
    const topicIndex = courseTopicsCount++;
    const row = document.createElement('tr');
    row.dataset.topicIndex = topicIndex;
    row.innerHTML = `
        <td><input type="text" class="topic-input" name="course_outline[${topicIndex}][topic]" placeholder="e.g., Week 1 or Topic 1"></td>
        <td><textarea class="topic-description" name="course_outline[${topicIndex}][description]" placeholder="Topic description..." rows="2"></textarea></td>
        <td><input type="number" class="topic-hours" name="course_outline[${topicIndex}][hours]" placeholder="Hours" min="0.5" step="0.5" value="0.5"></td>
        <td><button type="button" class="remove-topic-btn" onclick="removeCourseTopic(${topicIndex})">Remove</button></td>
    `;
    tbody.appendChild(row);
};

window.removeCourseTopic = function(index) {
    const tbody = document.getElementById('courseOutlineTableBody');
    if (!tbody) return;
    
    const row = tbody.querySelector(`[data-topic-index="${index}"]`);
    if (row) {
        row.remove();
    }
};

function addCourseTopic() {
    if (typeof window.addCourseTopic === 'function') {
        window.addCourseTopic();
    }
}

function removeCourseTopic(index) {
    if (typeof window.removeCourseTopic === 'function') {
        window.removeCourseTopic(index);
    }
}

// Initialize Course Outline - Define early so it's available when navigation code runs
window.initializeCourseOutline = function() {
    console.log('🔄 initializeCourseOutline called');
    const tbody = document.getElementById('courseOutlineTableBody');
    if (!tbody) {
        console.warn('⚠️ Course outline table body not found, retrying...');
        // Retry after a short delay
        setTimeout(() => {
            window.initializeCourseOutline();
        }, 200);
        return;
    }
    
    // Check if we're resuming a draft - if so, don't clear existing data
    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
    const hasExistingData = tbody.querySelectorAll('tr').length > 0;
    const hasLoadedDraftData = window.loadedDraftData && window.loadedDraftData.course_outline && window.loadedDraftData.course_outline.length > 0;
    
    if (isResumingDraft || hasExistingData || hasLoadedDraftData) {
        console.log('📝 Skipping course outline initialization - draft data exists or already populated');
        console.log('  - isResumingDraft:', isResumingDraft);
        console.log('  - hasExistingData:', hasExistingData, '(rows:', tbody.querySelectorAll('tr').length, ')');
        console.log('  - hasLoadedDraftData:', hasLoadedDraftData);
        return; // Don't clear existing data when resuming draft
    }
    
    console.log('✅ Table body found, clearing and initializing...');
    
    // ALWAYS clear any existing rows first to ensure fresh initialization
    tbody.innerHTML = '';
    courseTopicsCount = 0; // Reset counter
    
    // DIRECTLY create 3 topic rows - don't rely on any other functions
    for (let i = 0; i < 3; i++) {
        const topicIndex = courseTopicsCount++;
        const row = document.createElement('tr');
        row.dataset.topicIndex = topicIndex;
        row.innerHTML = `
            <td><input type="text" class="topic-input" name="course_outline[${topicIndex}][topic]" placeholder="e.g., Week 1 or Topic 1" required></td>
            <td><textarea class="topic-description" name="course_outline[${topicIndex}][description]" placeholder="Topic description..." rows="2" required></textarea></td>
            <td><input type="number" class="topic-hours" name="course_outline[${topicIndex}][hours]" placeholder="Hours" min="0.5" step="0.5" value="0.5" required></td>
            <td><button type="button" class="remove-topic-btn" onclick="removeCourseTopic(${topicIndex})">Remove</button></td>
        `;
        tbody.appendChild(row);
        console.log(`✅ Added topic row ${i + 1}/3`);
    }
    
    // Verify rows were added
    const rows = tbody.querySelectorAll('tr');
    console.log(`✅ Initialized course outline: ${rows.length} rows added`);
    if (rows.length !== 3) {
        console.warn(`⚠️ Expected 3 rows but found ${rows.length}, retrying...`);
        setTimeout(() => {
            window.initializeCourseOutline();
        }, 200);
    }
};

// Assessment Functions
let assessmentMethodsCount = 0;
window.addAssessmentMethod = function() {
    const tbody = document.getElementById('assessmentTableBody');
    if (!tbody) return;
    
    const assessmentIndex = assessmentMethodsCount++;
    const row = document.createElement('tr');
    row.dataset.assessmentIndex = assessmentIndex;
    row.innerHTML = `
        <td><input type="text" class="assessment-type-input" name="assessment[${assessmentIndex}][type]" placeholder="e.g., Midterm Exam, Final Project" required></td>
        <td><input type="number" class="assessment-percentage-input" name="assessment[${assessmentIndex}][percentage]" placeholder="%" min="0" max="100" required></td>
        <td><button type="button" class="remove-assessment-btn" onclick="removeAssessmentMethod(${assessmentIndex})">Remove</button></td>
    `;
    tbody.appendChild(row);
};

window.removeAssessmentMethod = function(index) {
    const tbody = document.getElementById('assessmentTableBody');
    if (!tbody) return;
    
    const row = tbody.querySelector(`[data-assessment-index="${index}"]`);
    if (row) {
        row.remove();
    }
};

function addAssessmentMethod() {
    if (typeof window.addAssessmentMethod === 'function') {
        window.addAssessmentMethod();
    }
}

function removeAssessmentMethod(index) {
    if (typeof window.removeAssessmentMethod === 'function') {
        window.removeAssessmentMethod(index);
    }
}

// Initialize Assessment - Define early so it's available when navigation code runs
window.initializeAssessment = function() {
    console.log('🔄 initializeAssessment called');
    const tbody = document.getElementById('assessmentTableBody');
    if (!tbody) {
        console.warn('⚠️ Assessment table body not found, retrying...');
        // Retry after a short delay
        setTimeout(() => {
            window.initializeAssessment();
        }, 200);
        return;
    }
    
    // Check if we're resuming a draft - if so, don't clear existing data
    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
    const hasExistingData = tbody.querySelectorAll('tr').length > 0;
    const hasLoadedDraftData = window.loadedDraftData && window.loadedDraftData.assessment_methods && window.loadedDraftData.assessment_methods.length > 0;
    
    if (isResumingDraft || hasExistingData || hasLoadedDraftData) {
        console.log('📝 Skipping assessment initialization - draft data exists or already populated');
        console.log('  - isResumingDraft:', isResumingDraft);
        console.log('  - hasExistingData:', hasExistingData, '(rows:', tbody.querySelectorAll('tr').length, ')');
        console.log('  - hasLoadedDraftData:', hasLoadedDraftData);
        return; // Don't clear existing data when resuming draft
    }
    
    console.log('✅ Table body found, clearing and initializing...');
    
    // ALWAYS clear any existing rows first to ensure fresh initialization
    tbody.innerHTML = '';
    assessmentMethodsCount = 0; // Reset counter
    
    // DIRECTLY create 3 assessment rows - don't rely on any other functions
    for (let i = 0; i < 3; i++) {
        const assessmentIndex = assessmentMethodsCount++;
        const row = document.createElement('tr');
        row.dataset.assessmentIndex = assessmentIndex;
        row.innerHTML = `
            <td><input type="text" class="assessment-type-input" name="assessment[${assessmentIndex}][type]" placeholder="e.g., Midterm Exam, Final Project" required></td>
            <td><input type="number" class="assessment-percentage-input" name="assessment[${assessmentIndex}][percentage]" placeholder="%" min="0" max="100" required></td>
            <td><button type="button" class="remove-assessment-btn" onclick="removeAssessmentMethod(${assessmentIndex})">Remove</button></td>
        `;
        tbody.appendChild(row);
        console.log(`✅ Added assessment row ${i + 1}/3`);
    }
    
    // Verify rows were added
    const rows = tbody.querySelectorAll('tr');
    console.log(`✅ Initialized assessment: ${rows.length} rows added`);
    if (rows.length !== 3) {
        console.warn(`⚠️ Expected 3 rows but found ${rows.length}, retrying...`);
        setTimeout(() => {
            window.initializeAssessment();
        }, 200);
    }
};

// Load academic years for the course form - SIMPLE VERSION
window.loadAcademicYears = (function() {
    let loaded = false;
    let loading = false;
    
    return async function() {
        // Prevent multiple calls
        if (loading) {
            console.log('⏸️ Already loading academic years...');
            return;
        }
        
        // Check if already loaded
        const checkSelect = document.getElementById('academicYear');
        if (loaded && checkSelect && checkSelect.options.length > 1) {
            console.log('✅ Academic years already loaded');
            return;
        }
        
        loading = true;
        console.log('🔄 Loading academic years...');
        console.log('🔄 Current URL:', window.location.href);
        
        try {
            // Find select element first
            let select = document.getElementById('academicYear');
            console.log('🔄 Select element found:', !!select);
            
            if (!select) {
                // Wait and try again
                console.log('⏳ Waiting for select element...');
                await new Promise(resolve => setTimeout(resolve, 200));
                select = document.getElementById('academicYear');
                console.log('🔄 Select element found after wait:', !!select);
            }
            
            if (!select) {
                console.error('❌ academicYear select element not found after waiting!');
                console.error('Modal exists:', !!document.getElementById('addCourseModal'));
                return;
            }
            
            console.log('✅ Select element found, fetching data...');
            
            // Fetch data
            const response = await fetch('api/get_school_years.php');
            console.log('🔄 Response status:', response.status, response.statusText);
            console.log('🔄 Response URL:', response.url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('🔄 API response data:', JSON.stringify(data, null, 2));
            
            if (data.success && data.school_years && Array.isArray(data.school_years) && data.school_years.length > 0) {
                console.log('✅ Data is valid, populating dropdown...');
                console.log('🔄 Number of years:', data.school_years.length);
                
                // Clear and populate
                select.innerHTML = '<option value="">Select Academic Year</option>';
                
                data.school_years.forEach((year, index) => {
                    const option = document.createElement('option');
                    option.value = year.id;
                    option.textContent = year.school_year || year.school_year_label || 'Academic Year';
                    select.appendChild(option);
                    console.log(`  ✅ Added option ${index + 1}: ${option.textContent} (id: ${year.id})`);
                });
                
                console.log('✅ Academic years loaded successfully! Total options:', select.options.length);
                loaded = true;
            } else {
                console.error('❌ Invalid data structure:');
                console.error('  - success:', data.success);
                console.error('  - school_years exists:', !!data.school_years);
                console.error('  - is array:', Array.isArray(data.school_years));
                console.error('  - length:', data.school_years?.length || 0);
                console.error('  - message:', data.message);
                console.error('  - Full data:', data);
                select.innerHTML = '<option value="">No academic years available</option>';
            }
        } catch (error) {
            console.error('❌ Error loading academic years:', error);
            const select = document.getElementById('academicYear');
            if (select) {
                select.innerHTML = '<option value="">Error loading years</option>';
            }
        } finally {
            loading = false;
        }
    };
})();

function populateAcademicYearSelect(select, data) {
    console.log('📋 Populating academic year select with data:', data);
    
    if (!select) {
        console.error('❌ Select element is null or undefined');
        return;
    }
    
    if (!data) {
        console.error('❌ Data is null or undefined');
        select.innerHTML = '<option value="">Error: No data received</option>';
        return;
    }
    
    // Check if data has the expected structure
    console.log('📋 Checking data structure...');
    console.log('  - data.success:', data.success);
    console.log('  - data.school_years exists:', !!data.school_years);
    console.log('  - data.school_years is array:', Array.isArray(data.school_years));
    console.log('  - data.school_years length:', data.school_years?.length || 0);
    
    // Only log full data if debugging is needed (can be large)
    if (!data.success || !data.school_years || data.school_years.length === 0) {
        console.log('  - Full data:', JSON.stringify(data, null, 2));
    }
    
    // Validate and populate
    if (data.success && data.school_years && Array.isArray(data.school_years) && data.school_years.length > 0) {
        // Clear existing options
        select.innerHTML = '<option value="">Select Academic Year</option>';
        
        // Add each year
        data.school_years.forEach((year, index) => {
            try {
                const option = document.createElement('option');
                option.value = year.id;
                // Handle different possible field names for school year label
                const yearLabel = year.school_year || year.school_year_label || `A.Y. ${year.year_start}-${year.year_end}` || 'Academic Year';
                option.textContent = yearLabel;
                select.appendChild(option);
                console.log(`  ✅ Added: ${yearLabel} (id: ${year.id})`);
            } catch (err) {
                console.error(`  ❌ Error adding year ${index + 1}:`, err, year);
            }
        });
        
        console.log('✅ Academic years loaded successfully:', data.school_years.length, 'years');
        console.log('📋 Final select options count:', select.options.length);
    } else {
        const errorMsg = data.message || data.error || 'No data returned';
        console.error('❌ Failed to load academic years:', errorMsg);
        console.error('❌ Data structure:', {
            success: data.success,
            hasSchoolYears: !!data.school_years,
            isArray: Array.isArray(data.school_years),
            length: data.school_years?.length || 0
        });
        select.innerHTML = '<option value="">No academic years available</option>';
    }
}

// Material Modal Functions
window.openAddMaterialModal = function() {
    const modal = document.getElementById('addMaterialModal');
    if (modal) {
        // Reset form if not editing
        if (window.editingMaterialIndex === undefined || window.editingMaterialIndex === null) {
            const form = document.getElementById('addMaterialForm');
            if (form) {
                form.reset();
            }
            // Update modal title and button for new material
            const modalHeader = document.querySelector('#addMaterialModal .modal-header h2');
            const saveButton = document.querySelector('#addMaterialModal .create-btn');
            if (modalHeader) {
                modalHeader.textContent = 'Add Learning Material';
            }
            if (saveButton) {
                saveButton.textContent = 'Add Material';
            }
        }
        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        setTimeout(() => {
            const firstInput = document.getElementById('materialTitle');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
};

window.closeAddMaterialModal = function() {
    const modal = document.getElementById('addMaterialModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Clear editing index
        window.editingMaterialIndex = undefined;
        
        // Reset form
        const form = document.getElementById('addMaterialForm');
        if (form) {
            form.reset();
        }
        
        // Reset modal title and button
        const modalHeader = document.querySelector('#addMaterialModal .modal-header h2');
        const saveButton = document.querySelector('#addMaterialModal .create-btn');
        if (modalHeader) {
            modalHeader.textContent = 'Add Learning Material';
        }
        if (saveButton) {
            saveButton.textContent = 'Add Material';
        }
    }
};

window.saveMaterial = function() {
    const form = document.getElementById('addMaterialForm');
    if (!form) {
        console.error('Material form not found');
        return;
    }
    
    // Get form values
    const callNumber = document.getElementById('materialCallNumber').value.trim();
    const title = document.getElementById('materialTitle').value.trim();
    const author = document.getElementById('materialAuthor').value.trim();
    const publisher = document.getElementById('materialPublisher').value.trim();
    const year = document.getElementById('materialYear').value.trim();
    const type = document.getElementById('materialType').value.trim();
    
    // Validate required fields
    if (!title) {
        alert('Please enter a title for the material.');
        document.getElementById('materialTitle').focus();
        return;
    }
    
    const tbody = document.getElementById('learningMaterialsTableBody');
    if (!tbody) {
        console.error('Learning materials table body not found');
        return;
    }
    
    // Check if we're editing an existing material
    const editingIndex = window.editingMaterialIndex;
    let materialIndex;
    let row;
    
    if (editingIndex !== undefined && editingIndex !== null) {
        // Editing existing material
        materialIndex = editingIndex;
        row = document.querySelector(`.material-row[data-material-index="${materialIndex}"]`);
        if (!row) {
            console.error('Material row not found for editing');
            return;
        }
    } else {
        // Adding new material
        window.materialCount = (window.materialCount || 0) + 1;
        materialIndex = window.materialCount;
        row = document.createElement('tr');
        row.className = 'material-row';
        row.dataset.materialIndex = materialIndex;
        tbody.appendChild(row);
    }
    
    // Store data as data attributes for form submission
    row.dataset.callNumber = callNumber;
    row.dataset.title = title;
    row.dataset.author = author;
    row.dataset.publisher = publisher;
    row.dataset.year = year;
    row.dataset.type = type;
    
    // Display as read-only fields
    row.innerHTML = `
        <td>${callNumber || '-'}</td>
        <td>${title}</td>
        <td>${author || '-'}</td>
        <td>${publisher || '-'}</td>
        <td>${year || '-'}</td>
        <td>${type || '-'}</td>
        <td>
            <div style="display: flex; gap: 5px;">
                <button type="button" class="edit-material-btn" onclick="if(typeof window.editMaterial==='function'){window.editMaterial(${materialIndex});}else{console.error('editMaterial not found');} return false;">Edit</button>
                <button type="button" class="remove-material-btn" onclick="if(typeof window.removeMaterialRow==='function'){window.removeMaterialRow(${materialIndex});}else{console.error('removeMaterialRow not found');} return false;">Remove</button>
            </div>
        </td>
    `;
    
    // Add hidden inputs for form submission
    const hiddenInputs = `
        <input type="hidden" name="material_call_number[]" value="${callNumber}">
        <input type="hidden" name="material_title[]" value="${title}">
        <input type="hidden" name="material_author[]" value="${author}">
        <input type="hidden" name="material_publisher[]" value="${publisher}">
        <input type="hidden" name="material_year[]" value="${year}">
        <input type="hidden" name="material_type[]" value="${type}">
    `;
    row.insertAdjacentHTML('beforeend', hiddenInputs);
    
    // Clear editing index
    window.editingMaterialIndex = undefined;
    
    // Close modal
    window.closeAddMaterialModal();
    
    console.log(editingIndex !== undefined ? '✅ Material updated via modal' : '✅ Material added via modal', 'count:', materialIndex);
};

window.editMaterial = function(index) {
    const row = document.querySelector(`.material-row[data-material-index="${index}"]`);
    if (!row) {
        console.error('Material row not found');
        return;
    }
    
    // Get data from row
    const callNumber = row.dataset.callNumber || '';
    const title = row.dataset.title || '';
    const author = row.dataset.author || '';
    const publisher = row.dataset.publisher || '';
    const year = row.dataset.year || '';
    const type = row.dataset.type || '';
    
    // Set editing index
    window.editingMaterialIndex = index;
    
    // Populate form
    document.getElementById('materialCallNumber').value = callNumber;
    document.getElementById('materialTitle').value = title;
    document.getElementById('materialAuthor').value = author;
    document.getElementById('materialPublisher').value = publisher;
    document.getElementById('materialYear').value = year;
    document.getElementById('materialType').value = type;
    
    // Update modal title and button
    const modalHeader = document.querySelector('#addMaterialModal .modal-header h2');
    const saveButton = document.querySelector('#addMaterialModal .create-btn');
    if (modalHeader) {
        modalHeader.textContent = 'Edit Learning Material';
    }
    if (saveButton) {
        saveButton.textContent = 'Update Material';
    }
    
    // Open modal
    window.openAddMaterialModal();
};

console.log('✅ Material modal functions defined');

// File Management Functions - Define BEFORE modal HTML
window.attachmentFiles = window.attachmentFiles || [];

window.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

window.getFileIcon = function(fileName) {
    const ext = fileName.split('.').pop().toLowerCase();
    const icons = {
        'pdf': '📄',
        'doc': '📝',
        'docx': '📝',
        'xls': '📊',
        'xlsx': '📊',
        'txt': '📄',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️',
        'gif': '🖼️'
    };
    return icons[ext] || '📎';
};

window.updateAttachmentList = function() {
    // Try to find the attachment list element - retry if not found
    let attachmentList = document.getElementById('attachmentList');
    
    // If element not found, try again after a short delay (for timing issues)
    if (!attachmentList) {
        console.warn('⚠️ attachmentList element not found, retrying...');
        setTimeout(() => {
            attachmentList = document.getElementById('attachmentList');
            if (attachmentList) {
                window.updateAttachmentList();
            } else {
                console.error('❌ attachmentList element still not found after retry');
            }
        }, 100);
        return;
    }
    
    // Ensure the list is visible
    attachmentList.style.display = 'flex';
    attachmentList.style.flexDirection = 'column';
    attachmentList.style.gap = '10px';
    
    console.log('📋 Updating attachment list. Files count:', window.attachmentFiles ? window.attachmentFiles.length : 0);
    
    attachmentList.innerHTML = '';
    
    if (!window.attachmentFiles || window.attachmentFiles.length === 0) {
        attachmentList.innerHTML = '<p style="text-align: center; color: #999; padding: 20px; margin: 0;">No files uploaded yet</p>';
        return;
    }
    
    // Add a header showing total file count
    const header = document.createElement('div');
    header.style.cssText = 'display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding: 8px 12px; background: #f5f5f5; border-radius: 4px;';
    header.innerHTML = `
        <span style="font-weight: 600; color: #333; font-size: 13px;">Selected Files (${window.attachmentFiles.length})</span>
        <span style="font-size: 12px; color: #666;">Ready to upload</span>
    `;
    attachmentList.appendChild(header);
    
    window.attachmentFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'attachment-item';
        item.dataset.fileIndex = index;
        item.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; background: #fff; border: 1px solid #ddd; border-radius: 6px; transition: all 0.2s ease;';
        item.innerHTML = `
            <div class="file-info" style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <div class="file-icon" style="font-size: 24px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 4px;">${window.getFileIcon ? window.getFileIcon(file.name) : '📎'}</div>
                <div class="file-details" style="flex: 1; min-width: 0;">
                    <span class="file-name" title="${file.name}" style="font-weight: 500; color: #333; font-size: 14px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${file.name}</span>
                    <span class="file-size" style="font-size: 12px; color: #666; margin-top: 2px; display: block;">${window.formatFileSize ? window.formatFileSize(file.size) : (file.size + ' bytes')}</span>
                </div>
            </div>
            <div class="file-actions" style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="view-file-btn" onclick="if(typeof window.viewAttachment==='function'){window.viewAttachment(${index});}" style="padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; transition: all 0.2s ease; font-weight: 500; background: #1976d2; color: white; font-family: 'TT Interphases', sans-serif;">View</button>
                <button type="button" class="remove-file-btn" onclick="if(typeof window.removeAttachment==='function'){window.removeAttachment(${index});}" style="padding: 6px 12px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; transition: all 0.2s ease; font-weight: 500; background: #f44336; color: white; font-family: 'TT Interphases', sans-serif;">Remove</button>
            </div>
        `;
        attachmentList.appendChild(item);
    });
    
    console.log('✅ Attachment list updated with', window.attachmentFiles.length, 'files');
};

window.viewAttachment = function(index) {
    const file = window.attachmentFiles[index];
    if (!file) return;
    
    // Create a temporary URL for the file
    const url = URL.createObjectURL(file);
    
    // Open in new tab if it's a PDF or image, otherwise download
    if (file.type === 'application/pdf' || file.type.startsWith('image/')) {
        window.open(url, '_blank');
    } else {
        const a = document.createElement('a');
        a.href = url;
        a.download = file.name;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    
    // Clean up the URL after a delay
    setTimeout(() => URL.revokeObjectURL(url), 100);
};

window.removeAttachment = function(index) {
    if (confirm('Are you sure you want to remove this file?')) {
        window.attachmentFiles.splice(index, 1);
        window.updateAttachmentList();
        window.updateFileInput();
    }
};

window.updateFileInput = function() {
    const input = document.getElementById('courseAttachments');
    if (!input) return;
    
    // Create a new DataTransfer object
    const dt = new DataTransfer();
    
    // Add all files from our array
    window.attachmentFiles.forEach(file => {
        dt.items.add(file);
    });
    
    // Update the input's files
    input.files = dt.files;
};

window.handleFiles = function(files) {
    console.log('📁 handleFiles called with', files.length, 'files');
    
    // Initialize attachmentFiles array if it doesn't exist
    if (!window.attachmentFiles) {
        window.attachmentFiles = [];
        console.log('✅ Initialized attachmentFiles array');
    }
    
    const maxSize = 50 * 1024 * 1024; // 50MB
    const allowedTypes = ['.pdf', '.doc', '.docx', '.xls', '.xlsx'];
    let addedCount = 0;
    let skippedCount = 0;
    
    Array.from(files).forEach(file => {
        console.log('Processing file:', file.name, 'Size:', file.size);
        
        // Check file size
        if (file.size > maxSize) {
            alert(`File "${file.name}" is too large. Maximum size is 50MB.`);
            skippedCount++;
            return;
        }
        
        // Check file type
        const fileName = file.name.toLowerCase();
        const isValidType = allowedTypes.some(type => fileName.endsWith(type));
        
        if (!isValidType) {
            alert(`File "${file.name}" is not a supported type. Allowed types: PDF, Word, Excel.`);
            skippedCount++;
            return;
        }
        
        // Add to array if not duplicate
        const isDuplicate = window.attachmentFiles.some(f => f.name === file.name && f.size === file.size);
        if (!isDuplicate) {
            window.attachmentFiles.push(file);
            addedCount++;
            console.log('✅ Added file:', file.name);
        } else {
            console.log('⚠️ Skipped duplicate file:', file.name);
            skippedCount++;
        }
    });
    
    console.log('📊 Files processed - Added:', addedCount, 'Skipped:', skippedCount);
    console.log('📊 Total files in array:', window.attachmentFiles.length);
    
    // Update debug info
    const debugText = document.getElementById('attachmentDebugText');
    if (debugText) {
        debugText.textContent = `Files: ${window.attachmentFiles.length} (Added: ${addedCount}, Skipped: ${skippedCount})`;
    }
    
    // Update the file input first
    if (typeof window.updateFileInput === 'function') {
        window.updateFileInput();
    } else {
        console.warn('⚠️ updateFileInput function not found');
    }
    
    // Update the display - force update even if step 7 is not active
    if (typeof window.updateAttachmentList === 'function') {
        // Call immediately
        window.updateAttachmentList();
        
        // Also ensure the list is visible by checking if step 7 exists and making it visible
        const step7 = document.getElementById('step7');
        const attachmentList = document.getElementById('attachmentList');
        
        if (attachmentList && window.attachmentFiles && window.attachmentFiles.length > 0) {
            // Make sure the list container is visible
            attachmentList.style.display = 'flex';
            attachmentList.style.visibility = 'visible';
            attachmentList.style.opacity = '1';
            
            // If we're on step 7, scroll to the list
            if (step7 && step7.classList.contains('active')) {
                setTimeout(() => {
                    attachmentList.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        }
    } else {
        console.warn('⚠️ updateAttachmentList function not found');
    }
    
    if (addedCount > 0) {
        console.log('✅ Successfully added', addedCount, 'file(s)');
        
        // Show a visual feedback with animation
        const attachmentList = document.getElementById('attachmentList');
        if (attachmentList) {
            // Briefly highlight the list area
            attachmentList.style.transition = 'background-color 0.3s ease, box-shadow 0.3s ease';
            attachmentList.style.backgroundColor = '#e3f2fd';
            attachmentList.style.boxShadow = '0 0 10px rgba(25, 118, 210, 0.2)';
            setTimeout(() => {
                attachmentList.style.backgroundColor = '';
                attachmentList.style.boxShadow = '';
            }, 800);
        }
        
        // Show a success message
        if (addedCount === 1) {
            console.log(`✅ File "${window.attachmentFiles[window.attachmentFiles.length - 1].name}" added successfully`);
        } else {
            console.log(`✅ ${addedCount} files added successfully`);
        }
    }
    
    // Clear the file input so the same file can be selected again if needed
    const fileInput = document.getElementById('courseAttachments');
    if (fileInput) {
        fileInput.value = '';
    }
};

console.log('✅ File management functions defined');

// Initialize file drop zone - Define it early so it's always available
window.initializeFileDropZone = window.initializeFileDropZone || function initializeFileDropZone() {
    console.log('🔍 initializeFileDropZone called');
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('courseAttachments');
    const attachmentList = document.getElementById('attachmentList');
    
    console.log('🔍 Initializing file drop zone...', {
        dropZone: !!dropZone,
        fileInput: !!fileInput,
        attachmentList: !!attachmentList,
        dropZoneElement: dropZone,
        fileInputElement: fileInput,
        attachmentListElement: attachmentList
    });
    
    if (!dropZone || !fileInput) {
        // Retry if elements not ready yet
        console.log('⏳ Elements not ready, retrying in 100ms...');
        setTimeout(window.initializeFileDropZone, 100);
        return;
    }
    
    console.log('✅ All required elements found, proceeding with initialization...');
    
    // Initialize attachment files array
    if (!window.attachmentFiles) {
        window.attachmentFiles = [];
        console.log('✅ Initialized attachmentFiles array');
    }
    
    // Update attachment list if element exists
    if (attachmentList) {
        // Ensure the list is visible and properly styled
        attachmentList.style.display = 'flex';
        attachmentList.style.flexDirection = 'column';
        attachmentList.style.gap = '10px';
        attachmentList.style.visibility = 'visible';
        attachmentList.style.opacity = '1';
        
        // Update the list content
        if (typeof window.updateAttachmentList === 'function') {
            window.updateAttachmentList();
        }
    } else {
        console.warn('⚠️ attachmentList element not found during initialization');
    }
    
    // Store handlers to prevent duplicate listeners
    if (!dropZone._fileDropZoneInitialized) {
        // Click to browse
        dropZone.addEventListener('click', function(e) {
            if (e.target !== fileInput && !e.target.closest('.file-actions')) {
                console.log('🖱️ Drop zone clicked, triggering file input');
                fileInput.click();
            }
        });
        
        // Drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
            
            console.log('📥 Files dropped, count:', e.dataTransfer.files.length);
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const filesArray = Array.from(e.dataTransfer.files);
                console.log('📥 Processing dropped files:', filesArray.length, 'files');
                if (typeof window.handleFiles === 'function') {
                    window.handleFiles(filesArray);
                }
                // Clear the data transfer
                e.dataTransfer.clearData();
            }
        });
        
        dropZone._fileDropZoneInitialized = true;
    }
    
    // File input change - CRITICAL: This must work for files to be added
    // Remove old listener if exists and add new one
    if (fileInput._changeHandler) {
        fileInput.removeEventListener('change', fileInput._changeHandler);
    }
    
    fileInput._changeHandler = function(e) {
        console.log('📁 File input changed, files selected:', e.target.files ? e.target.files.length : 0);
        console.log('📁 File input files:', e.target.files);
        console.log('📁 Current attachmentFiles count:', window.attachmentFiles ? window.attachmentFiles.length : 0);
        
        if (e.target.files && e.target.files.length > 0) {
            console.log('📁 Calling handleFiles with', e.target.files.length, 'files');
            console.log('📁 File names:', Array.from(e.target.files).map(f => f.name));
            
            if (typeof window.handleFiles === 'function') {
                // Create a copy of the FileList to ensure we can process it
                const filesArray = Array.from(e.target.files);
                console.log('📁 Processing files array:', filesArray.length, 'files');
                window.handleFiles(filesArray);
            } else {
                console.error('❌ window.handleFiles is not a function!', typeof window.handleFiles);
            }
        } else {
            console.warn('⚠️ No files selected or files array is empty');
        }
    };
    
    fileInput.addEventListener('change', fileInput._changeHandler);
    
    // Test that the file input is accessible
    const fileInputStyle = window.getComputedStyle(fileInput);
    const parentStyle = fileInput.parentElement ? window.getComputedStyle(fileInput.parentElement) : null;
    console.log('📎 File input element:', {
        id: fileInput.id,
        type: fileInput.type,
        multiple: fileInput.multiple,
        accept: fileInput.accept,
        disabled: fileInput.disabled,
        display: fileInputStyle.display,
        visibility: fileInputStyle.visibility,
        opacity: fileInputStyle.opacity,
        pointerEvents: fileInputStyle.pointerEvents,
        parentDisplay: parentStyle ? parentStyle.display : 'N/A',
        zIndex: fileInputStyle.zIndex
    });
    
    // Also attach a direct click handler to the file input for debugging
    fileInput.addEventListener('click', function(e) {
        console.log('🖱️ File input clicked directly');
        console.log('🖱️ Click event details:', {
            target: e.target,
            currentTarget: e.currentTarget,
            bubbles: e.bubbles
        });
    });
    
    // Update debug info
    const debugInfo = document.getElementById('attachmentDebugInfo');
    const debugText = document.getElementById('attachmentDebugText');
    if (debugInfo && debugText) {
        debugInfo.style.display = 'block';
        debugText.textContent = `File input ready. Current files: ${window.attachmentFiles ? window.attachmentFiles.length : 0}`;
    }
    
    console.log('✅ File drop zone initialized successfully');
    console.log('✅ File input change handler attached:', !!fileInput._changeHandler);
};

console.log('✅ initializeFileDropZone function defined');

// Populate Review Data - Define it early so it's always available
window.populateReviewData = window.populateReviewData || function populateReviewData() {
    console.log('📋 Populating review data...');
    
    try {
        // Get all form values from Step 1
        const courseCodeEl = document.getElementById('courseCode');
        const courseNameEl = document.getElementById('courseName');
        const unitsEl = document.getElementById('units');
        const lectureHoursEl = document.getElementById('lectureHours');
        const laboratoryHoursEl = document.getElementById('laboratoryHours');
        const prerequisitesEl = document.getElementById('prerequisites');
        const courseDescriptionEl = document.getElementById('courseDescription');
        
        const courseCode = courseCodeEl ? courseCodeEl.value.trim() : '-';
        const courseName = courseNameEl ? courseNameEl.value.trim() : '-';
        const units = unitsEl ? unitsEl.value.trim() : '-';
        const lectureHours = lectureHoursEl ? lectureHoursEl.value.trim() : '-';
        const laboratoryHours = laboratoryHoursEl ? laboratoryHoursEl.value.trim() : '-';
        const prerequisites = prerequisitesEl ? prerequisitesEl.value.trim() : '-';
        const courseDescription = courseDescriptionEl ? courseDescriptionEl.value.trim() : '-';
        
        console.log('Step 1 data:', { courseCode, courseName, units, lectureHours, laboratoryHours, prerequisites, courseDescription });
        
        // Get Learning Outcomes (Step 3)
        const outcomeInputs = document.querySelectorAll('.outcome-input');
        const learningOutcomes = Array.from(outcomeInputs)
            .map(input => input.value.trim())
            .filter(outcome => outcome.length > 0)
            .map((outcome, index) => `${index + 1}. ${outcome}`)
            .join('\n') || 'None specified';
        console.log('Learning Outcomes:', learningOutcomes);
        
        // Get Course Outline (Step 4)
        const courseOutlineTableBody = document.querySelector('#courseOutlineTableBody');
        let courseOutline = 'None specified';
        if (courseOutlineTableBody) {
            const courseOutlineRows = courseOutlineTableBody.querySelectorAll('tr');
            const outlineItems = Array.from(courseOutlineRows)
                .map(row => {
                    // Use class selectors which are consistently used
                    const topicInput = row.querySelector('.topic-input');
                    const descriptionInput = row.querySelector('.topic-description');
                    const hoursInput = row.querySelector('.topic-hours');
                    
                    const topic = topicInput ? topicInput.value.trim() : '';
                    const description = descriptionInput ? descriptionInput.value.trim() : '';
                    const hours = hoursInput ? hoursInput.value.trim() : '';
                    
                    if (topic || description || hours) {
                        let outlineItem = '';
                        if (topic) outlineItem += `${topic}`;
                        if (hours) outlineItem += ` (${hours} hrs)`;
                        if (description) {
                            outlineItem += `\n   ${description}`;
                        }
                        return outlineItem;
                    }
                    return null;
                })
                .filter(item => item !== null);
            
            if (outlineItems.length > 0) {
                courseOutline = outlineItems.join('\n\n');
            }
        }
        console.log('Course Outline:', courseOutline);
        
        // Get Assessment Methods (Step 5)
        const assessmentTableBody = document.querySelector('#assessmentTableBody');
        let assessmentMethods = 'None specified';
        if (assessmentTableBody) {
            const assessmentRows = assessmentTableBody.querySelectorAll('tr');
            const assessments = Array.from(assessmentRows)
                .map(row => {
                    const typeInput = row.querySelector('.assessment-type-input');
                    const percentageInput = row.querySelector('.assessment-percentage-input');
                    
                    const type = typeInput ? typeInput.value.trim() : '';
                    const percentage = percentageInput ? percentageInput.value.trim() : '';
                    
                    if (type || percentage) {
                        return `${type}${percentage ? ` - ${percentage}%` : ''}`;
                    }
                    return null;
                })
                .filter(item => item !== null);
            
            if (assessments.length > 0) {
                assessmentMethods = assessments.join('\n');
                // Calculate total percentage
                const totalPercentage = assessments.reduce((sum, item) => {
                    const match = item.match(/(\d+(?:\.\d+)?)%/);
                    return sum + (match ? parseFloat(match[1]) : 0);
                }, 0);
                if (totalPercentage > 0) {
                    assessmentMethods += `\n\nTotal: ${totalPercentage}%`;
                }
            }
        }
        console.log('Assessment Methods:', assessmentMethods);
        
        // Get learning materials from table (Step 6)
        const materialRows = document.querySelectorAll('.material-row');
        console.log('Found material rows:', materialRows.length);
        
        const materialList = Array.from(materialRows)
            .map(row => {
                // Try to get from dataset first, then from input fields
                let callNumber = row.dataset.callNumber || '';
                let title = row.dataset.title || '';
                let author = row.dataset.author || '';
                let publisher = row.dataset.publisher || '';
                let year = row.dataset.year || '';
                let type = row.dataset.type || '';
                
                // If dataset is empty, try to get from input fields
                if (!title) {
                    const titleInput = row.querySelector('.material-title-input');
                    if (titleInput) title = titleInput.value.trim();
                }
                if (!callNumber) {
                    const callNumberInput = row.querySelector('.material-call-number-input');
                    if (callNumberInput) callNumber = callNumberInput.value.trim();
                }
                if (!author) {
                    const authorInput = row.querySelector('.material-author-input');
                    if (authorInput) author = authorInput.value.trim();
                }
                if (!publisher) {
                    const publisherInput = row.querySelector('.material-publisher-input');
                    if (publisherInput) publisher = publisherInput.value.trim();
                }
                if (!year) {
                    const yearInput = row.querySelector('.material-year-input');
                    if (yearInput) year = yearInput.value.trim();
                }
                if (!type) {
                    const typeInput = row.querySelector('.material-type-input');
                    if (typeInput) type = typeInput.value.trim();
                }
                
                if (callNumber || title || author || publisher || year || type) {
                    const parts = [];
                    if (callNumber) parts.push(`[${callNumber}]`);
                    if (title) parts.push(title);
                    if (author) parts.push(`by ${author}`);
                    if (publisher) parts.push(`(${publisher})`);
                    if (year) parts.push(`(${year})`);
                    if (type) parts.push(`[${type}]`);
                    return parts.join(' ');
                }
                return null;
            })
            .filter(item => item !== null)
            .join('\n') || 'None specified';
        
        console.log('Materials list:', materialList);
        
        // Get justification (Step 8)
        const justificationEl = document.getElementById('justification');
        const justification = justificationEl ? justificationEl.value.trim() : '-';
        console.log('Justification:', justification);
        
        // Populate review fields
        const reviewCourseCode = document.getElementById('reviewCourseCode');
        const reviewCourseName = document.getElementById('reviewCourseName');
        const reviewUnits = document.getElementById('reviewUnits');
        const reviewLectureHours = document.getElementById('reviewLectureHours');
        const reviewLaboratoryHours = document.getElementById('reviewLaboratoryHours');
        const reviewPrerequisites = document.getElementById('reviewPrerequisites');
        const reviewDescription = document.getElementById('reviewDescription');
        const reviewLearningOutcomes = document.getElementById('reviewLearningOutcomes');
        const reviewCourseOutline = document.getElementById('reviewCourseOutline');
        const reviewAssessment = document.getElementById('reviewAssessment');
        const reviewMaterials = document.getElementById('reviewMaterials');
        const reviewJustification = document.getElementById('reviewJustification');
        
        if (reviewCourseCode) reviewCourseCode.textContent = courseCode || '-';
        if (reviewCourseName) reviewCourseName.textContent = courseName || '-';
        if (reviewUnits) reviewUnits.textContent = units || '-';
        if (reviewLectureHours) reviewLectureHours.textContent = lectureHours || '-';
        if (reviewLaboratoryHours) reviewLaboratoryHours.textContent = laboratoryHours || '-';
        if (reviewPrerequisites) reviewPrerequisites.textContent = prerequisites || 'None';
        if (reviewDescription) reviewDescription.textContent = courseDescription || '-';
        if (reviewLearningOutcomes) reviewLearningOutcomes.textContent = learningOutcomes;
        if (reviewCourseOutline) reviewCourseOutline.textContent = courseOutline;
        if (reviewAssessment) reviewAssessment.textContent = assessmentMethods;
        if (reviewMaterials) reviewMaterials.textContent = materialList;
        if (reviewJustification) reviewJustification.textContent = justification || '-';
        
        // Get attachments (Step 7) - from window.attachmentFiles array
        const reviewAttachments = document.getElementById('reviewAttachments');
        if (reviewAttachments) {
            if (window.attachmentFiles && window.attachmentFiles.length > 0) {
                // Create HTML with view buttons for each attachment
                let attachmentsHTML = '';
                window.attachmentFiles.forEach((file, index) => {
                    const fileIcon = window.getFileIcon ? window.getFileIcon(file.name) : '📎';
                    const fileSize = window.formatFileSize ? window.formatFileSize(file.size) : (file.size + ' bytes');
                    attachmentsHTML += `
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 4px 8px; background: #f5f5f5; border-radius: 4px; margin-bottom: 4px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                <span style="font-size: 16px;">${fileIcon}</span>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 500; color: #333; font-size: 12px; margin-bottom: 1px; word-break: break-word;">${file.name}</div>
                                    <div style="font-size: 10px; color: #666;">${fileSize}</div>
                                </div>
                            </div>
                            <button type="button" onclick="if(typeof window.viewAttachment==='function'){window.viewAttachment(${index});}" style="padding: 4px 10px; background: #1976d2; color: white; border: none; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer; font-family: 'TT Interphases', sans-serif; white-space: nowrap;">View</button>
                        </div>
                    `;
                });
                reviewAttachments.innerHTML = attachmentsHTML;
                console.log('Attachments from window.attachmentFiles:', window.attachmentFiles.length, 'files');
            } else {
                // Fallback: try to get from file input
                const attachmentInput = document.getElementById('courseAttachments');
                if (attachmentInput && attachmentInput.files && attachmentInput.files.length > 0) {
                    const files = Array.from(attachmentInput.files);
                    let attachmentsHTML = '';
                    files.forEach((file, index) => {
                        const fileIcon = window.getFileIcon ? window.getFileIcon(file.name) : '📎';
                        const fileSize = window.formatFileSize ? window.formatFileSize(file.size) : (file.size + ' bytes');
                        attachmentsHTML += `
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 4px 8px; background: #f5f5f5; border-radius: 4px; margin-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                    <span style="font-size: 16px;">${fileIcon}</span>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 500; color: #333; font-size: 12px; margin-bottom: 1px; word-break: break-word;">${file.name}</div>
                                        <div style="font-size: 10px; color: #666;">${fileSize}</div>
                                    </div>
                                </div>
                                <button type="button" onclick="alert('File preview not available from file input. Please use the attachment list in step 7.');" style="padding: 4px 10px; background: #1976d2; color: white; border: none; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer; font-family: 'TT Interphases', sans-serif; white-space: nowrap;">View</button>
                            </div>
                        `;
                    });
                    reviewAttachments.innerHTML = attachmentsHTML;
                    console.log('Attachments from file input:', files.length, 'files');
                } else {
                    reviewAttachments.textContent = 'None';
                }
            }
        }
        
        // Force update navigation buttons when review data is populated (step 9)
        console.log('🔄 Forcing button update on step 9...');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitToQABtn');
        
        if (nextBtn) {
            nextBtn.style.display = 'none';
            nextBtn.textContent = 'Next';
        }
        
        if (submitBtn) {
            submitBtn.style.display = 'block';
            submitBtn.textContent = 'Submit to QA';
            console.log('✅ Submit button forced to show with "Submit to QA" text');
        }
        
        // Also call updateNavigationButtons to ensure consistency
        if (typeof updateNavigationButtons === 'function') {
            setTimeout(() => {
                updateNavigationButtons();
            }, 10);
        }
        
        console.log('✅ Review data populated successfully');
    } catch (error) {
        console.error('❌ Error populating review data:', error);
        alert('Error loading review data: ' + error.message);
    }
};

console.log('✅ populateReviewData function defined');
</script>

<div id="addCourseModal" class="modal course-proposal-modal" style="display: none; z-index: 10005;">
    <div class="modal-content">
    <div class="modal-header">
            <h2>New Course Proposal</h2>
            <span class="close" onclick="event.preventDefault(); event.stopPropagation(); if(typeof window.showCloseConfirmationModal==='function'){window.showCloseConfirmationModal(event);} return false;">&times;</span>
    </div>
        
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-steps">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Course Information</div>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Course Description</div>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Learning Outcomes</div>
                </div>
                <div class="progress-step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Course Outline</div>
                </div>
                <div class="progress-step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-label">Assessment</div>
                </div>
                <div class="progress-step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-label">Materials</div>
                </div>
                <div class="progress-step" data-step="7">
                    <div class="step-number">7</div>
                    <div class="step-label">Attachments</div>
                </div>
                <div class="progress-step" data-step="8">
                    <div class="step-number">8</div>
                    <div class="step-label">Justification</div>
                </div>
                <div class="progress-step" data-step="9">
                    <div class="step-number">9</div>
                    <div class="step-label">Summary</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
        
        <div class="modal-body">
            <form id="addCourseForm" class="course-proposal-form" method="post" action="process_course.php" autocomplete="off" onsubmit="event.preventDefault(); if(typeof handleSubmitToQA==='function'){handleSubmitToQA(event);}else{console.error('handleSubmitToQA not found');} return false;">
                <input type="hidden" name="input_method" id="courseInputMethod" value="manual">
                
                <div class="form-steps-container">
                <!-- Step 1: Course Information -->
                <div class="form-step active" id="step1" data-step="1" data-from-course-selection="<?php echo isset($_GET['from_course_selection']) ? 'true' : 'false'; ?>">
                    <h3 class="step-title">Course Information</h3>
                    
                    <!-- Row 1: Course Code | Course Name -->
      <div class="form-row">
                        <div class="form-group course-code-width">
                            <label for="courseCode">Course Code <span class="required">*</span></label>
                            <input type="text" id="courseCode" name="course_code" class="form-control" placeholder="e.g., IT101" required>
        </div>
                        <div class="form-group course-name-width">
                            <label for="courseName">Course Name <span class="required">*</span></label>
                            <input type="text" id="courseName" name="course_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
        </div>
      </div>
                    
                    <!-- Row 2: Programs | Units -->
                    <div class="form-row">
                        <div class="form-group programs-width">
                            <label for="courseProgramSelect">Program(s) <span class="required">*</span></label>
                            <div class="program-select-container">
                                <button type="button" class="program-select-btn" id="programSelectBtn" onclick="if(typeof openProgramSelectModal==='function'){openProgramSelectModal();}else{alert('Program selection modal not loaded');} return false;">
                                    <span id="programSelectText">Select Program(s)</span>
                                    <span>▼</span>
                                </button>
                                <input type="hidden" id="selectedPrograms" name="programs[]" value="">
                            </div>
                            <small class="form-hint">Click to select one or more programs for this course</small>
                        </div>
                        <div class="form-group small-width">
                            <label for="units">Units</label>
                            <input type="number" id="units" name="units" class="form-control" min="1" max="6" placeholder="3">
        </div>
                    </div>
                    
                    <!-- Row 3: Academic Term | Academic Year | Year Level | Lec. Hours | Lab. Hours | Prerequisites -->
                    <div class="form-row">
                        <div class="form-group term-width">
                            <label for="academicTerm">Academic Term <span class="required">*</span></label>
                            <select id="academicTerm" name="academic_term" class="form-control" required>
                                <option value="">Select Term</option>
                                <option value="1">1st Semester</option>
                                <option value="2">2nd Semester</option>
                                <option value="3">Summer</option>
                            </select>
                        </div>
                        <div class="form-group year-width">
                            <label for="academicYear">Academic Year <span class="required">*</span></label>
                            <select id="academicYear" name="academic_year" class="form-control" required>
                                <option value="">Select Academic Year</option>
                                <?php foreach ($schoolYears as $year): ?>
                                    <option value="<?php echo htmlspecialchars($year['id']); ?>">
                                        <?php echo htmlspecialchars($year['school_year_label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group year-level-width">
                            <label for="yearLevel">Year Level <span class="required">*</span></label>
                            <select id="yearLevel" name="year_level" class="form-control" required>
                                <option value="">Select Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div class="form-group hours-width">
                            <label for="lectureHours">Lec. Hours</label>
                            <input type="number" id="lectureHours" name="lecture_hours" class="form-control" min="0" max="10" placeholder="3">
                        </div>
                        <div class="form-group hours-width">
                            <label for="laboratoryHours">Lab. Hours</label>
                            <input type="number" id="laboratoryHours" name="laboratory_hours" class="form-control" min="0" max="10" placeholder="2">
                        </div>
                        <div class="form-group prerequisites-width">
                            <label for="prerequisites">Prerequisites</label>
                            <input type="text" id="prerequisites" name="prerequisites" class="form-control" placeholder="e.g., CS 101">
                            <small class="form-hint">Course codes</small>
                        </div>
          </div>
        </div>
                
                <!-- Step 2: Course Description -->
                <div class="form-step" id="step2" data-step="2">
                    <h3 class="step-title">Course Description</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="courseDescription">Course Description <span class="required">*</span></label>
                            <textarea id="courseDescription" name="course_description" class="form-control" rows="10" placeholder="Provide a comprehensive description of the course, including its purpose, scope, and key topics covered..." required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Learning Outcomes -->
                <div class="form-step" id="step3" data-step="3">
                    <h3 class="step-title">Learning Outcomes</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Learning Outcomes</label>
                            <div class="outcomes-container" id="learningOutcomesContainer">
                                <!-- Learning outcomes will be dynamically added here -->
                            </div>
                            <button type="button" class="add-outcome-btn" onclick="addLearningOutcome()">
                                <span>+</span> Add Learning Outcome
                            </button>
                            <small class="form-hint">Define what students will be able to do after completing this course</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Course Outline -->
                <div class="form-step" id="step4" data-step="4">
                    <h3 class="step-title">Course Outline</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Course Topics</label>
                            <div class="course-outline-container">
                                <table class="course-outline-table">
                                    <thead>
                                        <tr>
                                            <th>Week/Topic</th>
                                            <th>Topic Description</th>
                                            <th>Hours</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="courseOutlineTableBody">
                                        <!-- Course outline rows will be dynamically added here -->
                                    </tbody>
                                </table>
                                <button type="button" class="add-topic-btn" onclick="addCourseTopic()">
                                    <span>+</span> Add Topic
                                </button>
                            </div>
                            <small class="form-hint">Outline the topics and schedule for this course</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 5: Assessment -->
                <div class="form-step" id="step5" data-step="5">
                    <h3 class="step-title">Assessment</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Assessment Methods</label>
                            <div class="assessment-container">
                                <table class="assessment-table">
                                    <thead>
                                        <tr>
                                            <th>Assessment Type</th>
                                            <th>Percentage</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="assessmentTableBody">
                                        <!-- Assessment rows will be dynamically added here -->
                                    </tbody>
                                </table>
                                <button type="button" class="add-assessment-btn" onclick="addAssessmentMethod()">
                                    <span>+</span> Add Assessment Method
                                </button>
                            </div>
                            <small class="form-hint">Define how students will be assessed in this course</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 6: Required Learning Materials -->
                <div class="form-step" id="step6" data-step="6">
                    <h3 class="step-title">Books / References Needed</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Required Learning Materials</label>
                            <div class="learning-materials-container">
                                <table class="learning-materials-table">
                                    <thead>
                                        <tr>
                                            <th>Call Number</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Publisher</th>
                                            <th>Year</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="learningMaterialsTableBody">
                                        <!-- Learning material rows will be dynamically added here -->
                                    </tbody>
                                </table>
                                <button type="button" class="add-material-btn" onclick="if(typeof window.openAddMaterialModal==='function'){window.openAddMaterialModal();}else{console.error('openAddMaterialModal not found');alert('Add Material modal function not loaded');} return false;">
                                    <span>+</span> Add Material
                                </button>
                            </div>
                            <small class="form-hint">This list will inform the Librarian later. Add books, references, journals, and other learning materials needed for this course.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 7: Attachments -->
                <div class="form-step" id="step7" data-step="7">
                    <h3 class="step-title">Attachments</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Upload Supporting Documents</label>
                            
                            <!-- Drag and Drop Zone -->
                            <div class="file-drop-zone" id="fileDropZone">
                                <div class="drop-zone-content">
                                    <div class="drop-zone-icon">📎</div>
                                    <p class="drop-zone-text">Drag and drop files here or click to browse</p>
                                    <p class="drop-zone-hint">Supports: PDF, Word, Excel (Max 50MB per file)</p>
                                    <input type="file" id="courseAttachments" name="course_attachments[]" class="file-input-hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10; pointer-events: auto;">
                                </div>
                            </div>
                            
                            <!-- File List -->
                            <div id="attachmentList" class="attachment-list"></div>
                            
                            <!-- Debug info (remove in production) -->
                            <div id="attachmentDebugInfo" style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px; font-size: 12px; color: #666; display: none;">
                                <strong>Debug:</strong> <span id="attachmentDebugText">No files selected</span>
                            </div>
                            
                            <small class="form-hint">You can upload multiple files. Click on a file to view or remove it.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 8: Justification -->
                <div class="form-step" id="step8" data-step="8">
                    <h3 class="step-title">Justification</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="justification">Justification for Course Proposal <span class="required">*</span></label>
                            <textarea id="justification" name="justification" class="form-control" rows="8" placeholder="Provide a clear justification for why this course should be added or revised. Include alignment with program objectives, industry needs, or curriculum gaps..." required></textarea>
                            <small class="form-hint">Explain why this course is needed and how it aligns with the program objectives.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Step 9: Summary -->
                <div class="form-step" id="step9" data-step="9">
                    <h3 class="step-title">Summary</h3>
                    <div class="review-section">
                        <div class="review-item">
                            <strong>Course Code:</strong>
                            <span id="reviewCourseCode">-</span>
                        </div>
                        <div class="review-item">
                            <strong>Course Name:</strong>
                            <span id="reviewCourseName">-</span>
                        </div>
                        <div class="review-item">
                            <strong>Units:</strong>
                            <span id="reviewUnits">-</span>
                        </div>
                        <div class="review-item">
                            <strong>Lecture Hours:</strong>
                            <span id="reviewLectureHours">-</span>
                        </div>
                        <div class="review-item">
                            <strong>Laboratory Hours:</strong>
                            <span id="reviewLaboratoryHours">-</span>
                        </div>
                        <div class="review-item full-width">
                            <strong>Prerequisites:</strong>
                            <span id="reviewPrerequisites">-</span>
                        </div>
                        <div class="review-item full-width">
                            <strong>Course Description:</strong>
                            <div id="reviewDescription" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Learning Outcomes:</strong>
                            <div id="reviewLearningOutcomes" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Course Outline:</strong>
                            <div id="reviewCourseOutline" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Assessment Methods:</strong>
                            <div id="reviewAssessment" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Learning Materials:</strong>
                            <div id="reviewMaterials" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Attachments:</strong>
                            <div id="reviewAttachments" class="review-text">-</div>
                        </div>
                        <div class="review-item full-width">
                            <strong>Justification:</strong>
                            <div id="reviewJustification" class="review-text">-</div>
                        </div>
                    </div>
                </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="form-navigation">
        <button type="button" class="cancel-btn" onclick="event.preventDefault(); event.stopPropagation(); if(typeof window.showCloseConfirmationModal==='function'){window.showCloseConfirmationModal(event);} return false;">CANCEL</button>
                    <div class="nav-buttons">
                        <button type="button" class="prev-btn" id="prevStepBtn" style="display: none;" onclick="if(typeof window.previousStep==='function'){window.previousStep();}else{console.error('previousStep not found');}return false;">Previous</button>
                        <button type="button" class="next-btn" id="nextStepBtn" onclick="if(typeof window.nextStep==='function'){window.nextStep(event);}else{alert('nextStep not loaded. Please refresh.');} return false;">Next</button>
                        <button type="button" class="submit-btn" id="submitToQABtn" style="display: none;" onclick="if(typeof handleSubmitToQA==='function'){handleSubmitToQA(event);}else{console.error('handleSubmitToQA not found');} return false;">Submit</button>
                    </div>
      </div>
    </form>
        </div>
  </div>
</div>

<!-- Program Selection Modal (keep existing) -->
<div id="programSelectModal" class="modal" style="display: none; z-index: 10010;">
    <div class="modal-content">
    <div class="modal-header">
      <h2>Select Program(s)</h2>
            <span class="close" onclick="closeProgramSelectModal()">&times;</span>
    </div>
        
        <div class="modal-body">
            <div class="program-search-container">
                <input type="text" id="programSearch" class="program-search-input" placeholder="Search programs by name..." autocomplete="off">
            </div>
            <div id="programsList">
        <?php
                try {
                    require_once dirname(__DIR__, 2) . '/bootstrap/database.php';
                    $pdo = ascom_get_pdo();
                    
                    $currentDepartmentId = null;
                    if (isset($_SESSION['selected_role']['department_id'])) {
                        $currentDepartmentId = $_SESSION['selected_role']['department_id'];
                    } elseif (isset($_SESSION['user_id'])) {
                        $userStmt = $pdo->prepare("SELECT id FROM departments WHERE dean_user_id = ?");
                        $userStmt->execute([$_SESSION['user_id']]);
                        $deptResult = $userStmt->fetch(PDO::FETCH_ASSOC);
                        if ($deptResult) {
                            $currentDepartmentId = $deptResult['id'];
                        }
                    }
                    
                    if ($currentDepartmentId) {
                        $stmt = $pdo->prepare("SELECT id, program_name FROM programs WHERE department_id = ? ORDER BY program_name ASC");
                        $stmt->execute([$currentDepartmentId]);
                    } else {
                    $stmt = $pdo->query("SELECT id, program_name FROM programs ORDER BY program_name ASC");
                    }
                    
                $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    if (empty($programs)) {
                        echo '<div class="program-item"><span>No programs found</span></div>';
                    } else {
                    foreach ($programs as $program) {
                            echo '<div class="program-item">';
                            echo '<label>';
                            echo '<input type="checkbox" name="programs[]" value="' . $program['id'] . '" data-program-name="' . htmlspecialchars($program['program_name']) . '">';
                            echo '<span class="program-name">' . htmlspecialchars($program['program_name']) . '</span>';
                            echo '</label>';
                            echo '</div>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="program-item"><span>Database connection failed</span></div>';
        }
        ?>
      </div>
            
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="closeProgramSelectModal()">CANCEL</button>
                <button type="button" class="create-btn" id="confirmProgramBtn" onclick="confirmProgramSelection()" disabled>CONFIRM</button>
      </div>
</div>
    </div>
</div>

<!-- Success Modal -->
<div id="courseSuccessModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content success-modal">
        <div class="modal-header">
            <h2>✅ Course Proposal Submitted Successfully!</h2>
            <span class="close" onclick="closeCourseSuccessModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon">🎉</div>
                <p class="success-message">Your course proposal has been submitted to Quality Assurance for review.</p>
                <div class="success-details">
                    <p><strong>Course Code:</strong> <span id="successCourseCode"></span></p>
                    <p><strong>Course Name:</strong> <span id="successCourseName"></span></p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="create-btn" onclick="closeCourseSuccessModal()">OK</button>
        </div>
    </div>
</div>

<!-- Draft Saved Modal -->
<div id="courseDraftSavedModal" class="modal" style="display: none; z-index: 10020; position: fixed;" onclick="if(event.target === this) { return false; }">
    <div class="modal-content success-modal" style="max-width: 400px; pointer-events: auto;" onclick="event.stopPropagation();">
        <div class="modal-header">
            <h2>✅ Draft Saved Successfully!</h2>
            <span class="close" onclick="closeCourseDraftSavedModal(); return false;" style="pointer-events: auto; cursor: pointer;">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon">💾</div>
                <p class="success-message">Your course proposal has been saved as a draft successfully! You can resume editing it anytime from the Course Proposals dashboard.</p>
                <div class="success-details">
                    <p><strong>Course Code:</strong> <span id="draftCourseCode"></span></p>
                    <p><strong>Course Name:</strong> <span id="draftCourseName"></span></p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="create-btn" onclick="closeCourseDraftSavedModal(); return false;" style="pointer-events: auto; cursor: pointer;">OK</button>
        </div>
    </div>
</div>

<!-- Add Material Modal -->
<div id="addMaterialModal" class="modal" style="display: none; z-index: 10006;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Add Learning Material</h2>
            <span class="close" onclick="if(typeof window.closeAddMaterialModal==='function'){window.closeAddMaterialModal();}">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addMaterialForm">
                <div class="form-group full-width">
                    <label for="materialCallNumber">Call Number</label>
                    <input type="text" id="materialCallNumber" class="form-control" placeholder="e.g., QA76.9">
                </div>
                
                <div class="form-group full-width">
                    <label for="materialTitle">Title <span class="required">*</span></label>
                    <input type="text" id="materialTitle" class="form-control" placeholder="e.g., Data Analytics: Concepts & Techniques" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="materialAuthor">Author</label>
                        <input type="text" id="materialAuthor" class="form-control" placeholder="e.g., Smith">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="materialPublisher">Publisher</label>
                        <input type="text" id="materialPublisher" class="form-control" placeholder="e.g., Pearson">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="materialYear">Year</label>
                        <input type="number" id="materialYear" class="form-control" placeholder="2021" min="1900" max="2100">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="materialType">Type</label>
                        <select id="materialType" class="form-control">
                            <option value="">Select Type</option>
                            <option value="Book">Book</option>
                            <option value="Journal">Journal</option>
                            <option value="Article">Article</option>
                            <option value="Online Resource">Online Resource</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-actions" style="justify-content: flex-end; gap: 10px; padding: 20px;">
            <button type="button" class="cancel-btn" onclick="if(typeof window.closeAddMaterialModal==='function'){window.closeAddMaterialModal();}">Cancel</button>
            <button type="button" class="create-btn" onclick="if(typeof window.saveMaterial==='function'){window.saveMaterial();}else{console.error('saveMaterial not found');}">Add Material</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="courseErrorModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content error-modal">
        <div class="modal-header">
            <h2>❌ Submission Failed</h2>
            <span class="close" onclick="closeCourseErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-content">
                <div class="error-icon">⚠️</div>
                <p class="error-message" id="errorMessage">An error occurred while submitting the course proposal. Please try again.</p>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" onclick="closeCourseErrorModal()">Close</button>
            <button type="button" class="create-btn" onclick="retryCourseCreation()">Retry</button>
</div>
    </div>
</div>

<!-- Add to List Success Modal -->
<div id="addToListSuccessModal" class="modal" style="display: none; z-index: 10006;">
    <div class="modal-content success-modal" style="max-width: 500px;">
        <div class="modal-header">
            <h2>✅ Course Added to List!</h2>
            <span class="close" onclick="closeAddToListSuccessModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon" style="font-size: 64px; margin-bottom: 20px;">✓</div>
                <p class="success-message" style="font-size: 16px; margin-bottom: 15px;">The course has been successfully added to your course list.</p>
                <div class="success-details" style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="margin: 5px 0;"><strong>Course Code:</strong> <span id="addToListSuccessCourseCode"></span></p>
                    <p style="margin: 5px 0;"><strong>Course Name:</strong> <span id="addToListSuccessCourseName"></span></p>
                </div>
                <p style="font-size: 14px; color: #666; margin-top: 15px;">You can continue adding more courses or submit all courses together when ready.</p>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center; padding: 20px;">
            <button type="button" class="create-btn" onclick="closeAddToListSuccessModal()" style="padding: 12px 30px; font-size: 16px;">OK</button>
        </div>
    </div>
</div>

<!-- Add to List Error Modal -->
<div id="addToListErrorModal" class="modal" style="display: none; z-index: 10006;">
    <div class="modal-content error-modal" style="max-width: 500px;">
        <div class="modal-header">
            <h2>❌ Failed to Add Course</h2>
            <span class="close" onclick="closeAddToListErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-content">
                <div class="error-icon" style="font-size: 64px; margin-bottom: 20px;">⚠️</div>
                <p class="error-message" id="addToListErrorMessage" style="font-size: 16px;">An error occurred while adding the course to your list. Please try again.</p>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center; padding: 20px;">
            <button type="button" class="cancel-btn" onclick="closeAddToListErrorModal()" style="padding: 12px 30px; font-size: 16px;">Close</button>
        </div>
    </div>
</div>

<!-- Validation Error Modal -->
<div id="validationErrorModal" class="modal" style="display: none; z-index: 10007;">
    <div class="modal-content error-modal" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Required Fields Missing</h2>
            <span class="close" onclick="closeValidationErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-content" style="text-align: center; padding: 12px 20px;">
                <div class="error-icon" style="font-size: 48px; margin-bottom: 12px; color: #f44336;">⚠️</div>
                <p class="error-message" id="validationErrorMessage" style="font-size: 16px; line-height: 1.6; color: #333;">Please fill in all required fields before proceeding.</p>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center; padding: 12px 20px;">
            <button type="button" class="cancel-btn" onclick="closeValidationErrorModal()" style="padding: 12px 30px; font-size: 16px; background-color: #1976d2; color: white;">OK</button>
        </div>
    </div>
</div>

<!-- Close Confirmation Modal -->
<div id="closeConfirmationModal" class="modal" style="display: none; z-index: 10015;" onclick="if(event.target === this && typeof window.closeCloseConfirmationModal==='function'){window.closeCloseConfirmationModal();}">
    <div class="modal-content" style="max-width: 500px;" onclick="event.stopPropagation();">
        <div class="modal-header">
            <h2>⚠️ Close Course Proposal?</h2>
            <span class="close" onclick="if(typeof window.closeCloseConfirmationModal==='function'){window.closeCloseConfirmationModal();}else{alert('Function not loaded. Please refresh the page.');}">&times;</span>
        </div>
        <div class="modal-body">
            <div style="padding: 20px;">
                <p style="font-size: 16px; margin-bottom: 20px; color: #333; line-height: 1.6;">
                    You have unsaved changes. What would you like to do?
                </p>
                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 0; font-size: 14px; color: #666;">
                        <strong>Save as Draft:</strong> Your progress will be saved and you can continue later.<br>
                        <strong>Discard:</strong> All your changes will be lost.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center; gap: 15px; padding: 20px;">
            <button type="button" class="cancel-btn" onclick="if(typeof window.closeCloseConfirmationModal==='function'){window.closeCloseConfirmationModal();}else{alert('Function not loaded. Please refresh the page.');}" style="padding: 12px 30px; font-size: 16px; background-color: #757575; color: white;">Cancel</button>
            <button type="button" class="cancel-btn" onclick="if(typeof window.discardCourseProposal==='function'){window.discardCourseProposal();}else{alert('Function not loaded. Please refresh the page.');}" style="padding: 12px 30px; font-size: 16px; background-color: #f44336; color: white;">Discard</button>
            <button type="button" class="create-btn" data-save-draft-btn onclick="if(typeof window.saveCourseAsDraft==='function'){window.saveCourseAsDraft(event);}else{alert('Function not loaded. Please refresh the page.');}" style="padding: 12px 30px; font-size: 16px;">Save as Draft</button>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: auto;
    overscroll-behavior: contain;
    pointer-events: auto !important; /* Ensure modal can receive clicks */
}

/* CRITICAL: Modal background should NOT block clicks to content */
.modal-content {
    pointer-events: auto !important; /* Ensure content can receive clicks */
    position: relative;
    z-index: 10000 !important;
    pointer-events: auto !important;
}

/* Ensure modal body and all children are interactive */
.modal-body,
.modal-body * {
    pointer-events: auto !important;
}

.modal-body {
    pointer-events: auto !important; /* Ensure body can receive clicks */
    position: relative;
    z-index: 10010 !important;
}

/* Force all inputs to be interactive */
#addCourseModal input:not([disabled]),
#addCourseModal textarea:not([disabled]),
#addCourseModal select:not([disabled]) {
    pointer-events: auto !important;
    z-index: 10002 !important;
    position: relative !important;
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    user-select: text !important;
}

.form-steps-container {
    pointer-events: auto !important; /* Ensure container can receive clicks */
    position: relative;
    z-index: 1;
}

.form-step {
    pointer-events: auto !important; /* Ensure form steps can receive clicks */
    position: relative;
    z-index: 1;
}

.form-step.active {
    pointer-events: auto !important; /* Ensure active step can receive clicks */
}

/* CRITICAL: All form elements must be clickable */
.form-control,
input,
textarea,
select,
button,
.form-group input,
.form-group textarea,
.form-group select,
.form-group button,
#addCourseModal input,
#addCourseModal textarea,
#addCourseModal select,
#addCourseModal button {
    pointer-events: auto !important; /* Ensure all form elements can receive clicks */
    position: relative !important;
    z-index: 999 !important;
    cursor: text !important;
    -webkit-user-select: text !important;
    user-select: text !important;
}

input[type="text"],
input[type="number"],
input[type="email"],
textarea {
    cursor: text !important;
    pointer-events: auto !important;
    z-index: 999 !important;
}

button {
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 999 !important;
}

/* Ensure course proposal modal appears above course selection modal */
#addCourseModal {
    z-index: 10005 !important;
}

/* Ensure program selection modal appears above course proposal modal */
#programSelectModal {
    z-index: 10008 !important;
}

/* Ensure close confirmation modal appears above everything */
#closeConfirmationModal {
    z-index: 10015 !important;
}

/* When hidden, ensure it doesn't block clicks */
#closeConfirmationModal[style*="display: none"],
#closeConfirmationModal[style*="display:none"] {
    pointer-events: none !important;
    visibility: hidden !important;
}

/* When shown, ensure it can receive clicks */
#closeConfirmationModal[style*="display: flex"] {
    pointer-events: auto !important;
    visibility: visible !important;
}

#closeConfirmationModal .modal-content {
    z-index: 10016 !important;
    position: relative;
}

.modal-content {
    background-color: #efefef !important;
    margin: auto;
    padding: 25px;
    border: 1px solid #888;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s;
}

.course-proposal-modal .modal-content {
    background-color: #efefef !important;
    width: 95%;
    max-width: 1100px;
    max-height: 90vh;
    height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 0;
}

.modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    flex: 1;
    overflow: hidden;
    padding: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

/* Progress Indicator */
.progress-indicator {
    margin-bottom: 30px;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    position: relative;
}

.progress-step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.progress-step .step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ddd;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    font-size: 16px;
    transition: all 0.3s ease;
    border: 3px solid #ddd;
}

.progress-step.active .step-number {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

.progress-step.completed .step-number {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

.progress-step .step-label {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

.progress-step.active .step-label {
    color: #1976d2;
    font-weight: 600;
}

.progress-bar {
    height: 4px;
    background: #ddd;
    border-radius: 2px;
    position: relative;
    margin-top: -20px;
    z-index: 1;
}

.progress-fill {
    height: 100%;
    background: #1976d2;
    border-radius: 2px;
    transition: width 0.3s ease;
    width: 0%;
}

/* Form Steps */
.course-proposal-form {
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
}

.form-steps-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    padding-right: 5px;
    pointer-events: auto !important; /* Ensure container can receive clicks */
    position: relative;
    z-index: 1;
}

.form-step {
    display: none;
    animation: fadeIn 0.3s;
    pointer-events: auto !important; /* Ensure form steps can receive clicks */
    position: relative;
    z-index: 1;
}

.form-step.active {
    display: block;
    pointer-events: auto !important; /* Ensure active step can receive clicks */
}

/* Ensure all form elements are clickable */
.form-step input,
.form-step textarea,
.form-step select,
.form-step button,
.form-group input,
.form-group textarea,
.form-group select,
.form-group button,
#addCourseModal .form-step input,
#addCourseModal .form-step textarea,
#addCourseModal .form-step select,
#addCourseModal .form-step button {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 999 !important;
    -webkit-user-select: text !important;
    user-select: text !important;
}

.step-title {
    font-size: 20px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #1976d2;
}

/* Form Styles */
.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    flex: 1;
    width: 100%;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.required {
    color: red;
}

.form-control {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.form-control.field-error,
select.field-error,
textarea.field-error,
input.field-error {
    border-color: #f44336 !important;
    border-width: 2px !important;
    box-shadow: 0 0 5px rgba(244, 67, 54, 0.3);
}

.form-control.field-error:focus,
select.field-error:focus,
textarea.field-error:focus,
input.field-error:focus {
    border-color: #f44336 !important;
    box-shadow: 0 0 8px rgba(244, 67, 54, 0.5);
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

/* Learning Outcomes Container */
.outcomes-container {
    margin-bottom: 15px;
}

.outcome-field {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.outcome-label {
    flex-shrink: 0;
    font-weight: 600;
    color: #333;
    padding-top: 10px;
    min-width: 90px;
    font-size: 14px;
}

.outcome-input-wrapper {
    flex: 1;
    display: flex;
    gap: 8px;
    align-items: flex-start;
}

.outcome-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 50px;
}

.outcome-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.remove-outcome-btn {
    flex-shrink: 0;
    background: #f44336;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    height: 38px;
    align-self: flex-start;
}

.remove-outcome-btn:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.add-outcome-btn {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    max-width: fit-content;
}

.add-outcome-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

.add-outcome-btn span {
    font-size: 18px;
    font-weight: bold;
}

/* Course Outline Table */
.course-outline-container {
    margin-bottom: 15px;
}

.course-outline-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.course-outline-table thead {
    background: #f5f5f5;
}

.course-outline-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #333;
    border-bottom: 2px solid #ddd;
}

.course-outline-table th:first-child {
    width: 20%;
}

.course-outline-table th:nth-child(2) {
    width: 50%;
}

.course-outline-table th:nth-child(3) {
    width: 15%;
}

.course-outline-table th:last-child {
    width: 15%;
    text-align: center;
}

.course-outline-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s ease;
}

.course-outline-table tbody tr:hover {
    background-color: #f8f9fa;
}

.course-outline-table tbody tr:last-child {
    border-bottom: none;
}

.course-outline-table td {
    padding: 12px;
    vertical-align: top;
}

.course-outline-table td:last-child {
    text-align: center;
}

.topic-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.topic-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.topic-description {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 50px;
    box-sizing: border-box;
}

.topic-description:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.topic-hours {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.topic-hours:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.remove-topic-btn {
    background: #f44336;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.remove-topic-btn:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.add-topic-btn {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.add-topic-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

.add-topic-btn span {
    font-size: 18px;
    font-weight: bold;
}

/* Assessment Methods Table */
.assessment-container {
    margin-bottom: 15px;
}

.assessment-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.assessment-table thead {
    background: #f5f5f5;
}

.assessment-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #333;
    border-bottom: 2px solid #ddd;
}

.assessment-table th:first-child {
    width: 60%;
}

.assessment-table th:nth-child(2) {
    width: 25%;
}

.assessment-table th:last-child {
    width: 15%;
    text-align: center;
}

.assessment-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s ease;
}

.assessment-table tbody tr:hover {
    background-color: #f8f9fa;
}

.assessment-table tbody tr:last-child {
    border-bottom: none;
}

.assessment-table td {
    padding: 12px;
    vertical-align: middle;
}

.assessment-table td:last-child {
    text-align: center;
}

.assessment-type-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.assessment-type-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.assessment-percentage-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.assessment-percentage-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

.remove-assessment-btn {
    background: #f44336;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.remove-assessment-btn:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.add-assessment-btn {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.add-assessment-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

.add-assessment-btn span {
    font-size: 18px;
    font-weight: bold;
}

/* Learning Materials Table */
.learning-materials-container {
    margin-bottom: 15px;
}

.learning-materials-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    font-size: 13px;
}

.learning-materials-table thead {
    background: #f5f5f5;
}

.learning-materials-table th {
    padding: 10px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    color: #333;
    border-bottom: 2px solid #ddd;
}

.learning-materials-table th:first-child {
    width: 15%;
}

.learning-materials-table th:nth-child(2) {
    width: 22%;
}

.learning-materials-table th:nth-child(3) {
    width: 15%;
}

.learning-materials-table th:nth-child(4) {
    width: 8%;
}

.learning-materials-table th:nth-child(5) {
    width: 12%;
}

.learning-materials-table th:nth-child(6) {
    width: 18%;
}

.learning-materials-table th:last-child {
    width: 10%;
    text-align: center;
}

.learning-materials-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s ease;
}

.learning-materials-table tbody tr:hover {
    background-color: #f8f9fa;
}

.learning-materials-table tbody tr:last-child {
    border-bottom: none;
}

.learning-materials-table td {
    padding: 10px 8px;
    vertical-align: middle;
}

.learning-materials-table td:last-child {
    text-align: center;
}

.material-call-number-input,
.material-title-input,
.material-author-input,
.material-publisher-input,
.material-year-input,
.material-type-input,
.material-remarks-input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 13px;
    font-family: inherit;
    box-sizing: border-box;
    position: relative;
}

.material-call-number-input:focus,
.material-title-input:focus,
.material-author-input:focus,
.material-publisher-input:focus,
.material-year-input:focus,
.material-type-input:focus,
.material-remarks-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
}

/* Autocomplete dropdown for call number */
.call-number-autocomplete {
    position: relative;
}

.call-number-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 5px 5px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: none;
}

.call-number-suggestions.show {
    display: block;
}

.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s ease;
}

.suggestion-item:hover,
.suggestion-item.selected {
    background-color: #f0f7ff;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-call-number {
    font-weight: 600;
    color: #1976d2;
    font-size: 12px;
}

.suggestion-title {
    font-size: 13px;
    color: #333;
    margin-top: 2px;
}

.suggestion-author {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.material-year-input {
    text-align: center;
}

.material-type-input {
    padding: 6px 4px;
}

.remove-material-btn {
    background: #f44336;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.remove-material-btn:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.edit-material-btn {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    margin-right: 5px;
}

.edit-material-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

/* Material table display styles */
.learning-materials-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.learning-materials-table tbody tr:hover {
    background-color: #f5f5f5;
}

.add-material-btn {
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
}

.add-material-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

.add-material-btn span {
    font-size: 18px;
    font-weight: bold;
}

.form-hint {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
    font-style: italic;
}

/* Width classes */
.form-group.small-width {
    flex: 0 0 10%;
    max-width: 10%;
}

.form-group.medium-width {
    flex: 0 0 30%;
    max-width: 30%;
}

.form-group.long-width {
    flex: 0 0 80%;
    max-width: 80%;
}

.form-group.course-code-width {
    flex: 0 0 20%;
    max-width: 20%;
}

.form-group.course-name-width {
    flex: 0 0 75%;
    max-width: 75%;
}

.form-group.programs-width {
    flex: 0 0 85%;
    max-width: 85%;
}

.form-group.term-width {
    flex: 0 0 16%;
    max-width: 16%;
}

.form-group.year-width {
    flex: 0 0 18%;
    max-width: 18%;
}

.form-group.year-level-width {
    flex: 0 0 16%;
    max-width: 16%;
}

.form-group.hours-width {
    flex: 0 0 10%;
    max-width: 10%;
}

.form-group.prerequisites-width {
    flex: 0 0 18%;
    max-width: 18%;
}

/* Program Selection */
.program-select-container {
    position: relative;
}

.program-select-btn {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
    text-align: left;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    height: 50px;
    transition: border-color 0.2s ease;
}

.program-select-btn:hover {
    border-color: #1976d2;
}

.dropdown-arrow {
    font-size: 12px;
    color: #666;
}

/* Review Section */
.review-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.review-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.review-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.review-item.full-width {
    display: flex;
    flex-direction: column;
}

.review-item strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.review-item span {
    color: #666;
    font-size: 14px;
}

.review-text {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    background: white;
    padding: 10px;
    border-radius: 4px;
    margin-top: 5px;
}

/* Override white-space for attachments review section */
#reviewAttachments {
    white-space: normal !important;
}

/* Hide course selection fields when opened from Manage Program Courses */
/* Use very specific selectors with high specificity */
#addCourseModal.from-course-selection #step1 #programField,
#addCourseModal.from-course-selection #step1 #academicFieldsRow,
#addCourseModal.from-course-selection #step1 .form-row #programField,
#addCourseModal.from-course-selection #step1 .form-row #academicFieldsRow,
#addCourseModal.from-course-selection .form-step.active #programField,
#addCourseModal.from-course-selection .form-step.active #academicFieldsRow,
#addCourseModal.from-course-selection .course-selection-review-field,
#addCourseModal.from-course-selection [data-course-selection-field="true"],
#addCourseModal.from-course-selection [data-course-selection-row="true"] #programField,
.course-selection-field-hidden,
[data-course-selection-field="true"].course-selection-field-hidden,
#addCourseModal.from-course-selection #programField,
#addCourseModal.from-course-selection #academicFieldsRow,
#programField[style*="display: none"],
#academicFieldsRow[style*="display: none"] {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    max-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    opacity: 0 !important;
    position: absolute !important;
    width: 0 !important;
    min-width: 0 !important;
    max-width: 0 !important;
    border: none !important;
    line-height: 0 !important;
}

/* Navigation Buttons */
.form-navigation {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 20px 0 0 0;
    border-top: 1px solid #eee;
    flex-shrink: 0;
    background-color: #efefef;
    position: relative;
    z-index: 10;
}

.form-navigation .nav-buttons {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
}

.cancel-btn, .prev-btn, .next-btn, .submit-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.3s ease;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.cancel-btn {
    background-color: #6c757d;
    color: white;
}

.cancel-btn:hover {
    background-color: #5a6268;
}

.prev-btn {
    background-color: #6c757d;
    color: white;
}

.prev-btn:hover {
    background-color: #5a6268;
}

.next-btn, .submit-btn {
    background-color: #1976d2;
    color: white;
}

.next-btn:hover, .submit-btn:hover {
    background-color: #1565c0;
}

.submit-btn {
    background-color: #4CAF50;
}

.submit-btn:hover {
    background-color: #45a049;
}

/* Attachment Preview */
/* File Drop Zone Styles */
.file-drop-zone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: #fafafa;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 20px;
    position: relative;
}

.file-drop-zone:hover {
    border-color: #1976d2;
    background: #f0f7ff;
}

.file-drop-zone.drag-over {
    border-color: #1976d2;
    background: #e3f2fd;
    border-style: solid;
}

.drop-zone-content {
    pointer-events: none;
    position: relative;
}

.drop-zone-content input[type="file"] {
    pointer-events: auto !important;
}

.drop-zone-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.drop-zone-text {
    font-size: 16px;
    color: #333;
    margin: 10px 0;
    font-weight: 500;
}

.drop-zone-hint {
    font-size: 12px;
    color: #666;
    margin: 5px 0;
}

.file-input-hidden {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
    pointer-events: auto;
}

/* Attachment List Styles */
.attachment-list {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 50px;
    visibility: visible;
    opacity: 1;
}

.attachment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.attachment-item:hover {
    border-color: #1976d2;
    box-shadow: 0 2px 4px rgba(25, 118, 210, 0.1);
}

.file-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.file-icon {
    font-size: 24px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 4px;
}

.file-details {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-weight: 500;
    color: #333;
    font-size: 14px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}

.file-actions {
    display: flex;
    gap: 8px;
}

.view-file-btn,
.remove-file-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
}

.view-file-btn {
    background: #1976d2;
    color: white;
}

.view-file-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
}

.remove-file-btn {
    background: #f44336;
    color: white;
}

.remove-file-btn:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.attachment-preview {
    margin-top: 10px;
}
    margin-bottom: 5px;
}

.attachment-item .file-name {
    flex: 1;
    font-size: 13px;
    color: #333;
}

.attachment-item .remove-file {
    background: #f44336;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

/* Program Selection Modal Styles */
#programSelectModal .modal-body {
    display: flex;
    flex-direction: column;
    max-height: 70vh;
    overflow: hidden;
}

.program-search-container {
    flex-shrink: 0;
    margin-bottom: 15px;
}

.program-search-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

#programsList {
    flex: 1;
    overflow-y: auto;
    max-height: calc(70vh - 160px);
    margin-bottom: 15px;
}

.program-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.program-item label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin: 0;
}

.program-item input[type="checkbox"] {
    margin-right: 10px;
}

/* Success/Error Modal Styles */
.success-modal .modal-content, .error-modal .modal-content {
    max-width: 500px;
}

.success-content, .error-content {
    text-align: center;
    padding: 20px 0;
}

.success-icon, .error-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.success-message, .error-message {
    color: #333;
    font-size: 16px;
    margin-bottom: 20px;
}

.success-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    text-align: left;
    margin-top: 15px;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.create-btn {
    background-color: #4CAF50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
}

.create-btn:hover {
    background-color: #45a049;
}

.create-btn:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<script>
// ============================================
// CRITICAL: Define nextStep IMMEDIATELY - NO CONDITIONS
// ============================================
window._courseFormStep = window._courseFormStep || 1;
window._courseTotalSteps = window._courseTotalSteps || 9;

// FORCE DEFINE - Don't check if it exists, just define it
window.nextStep = function(event) {
        console.log('🔵🔵🔵 NEXT BUTTON CLICKED - FUNCTION EXECUTING');
        console.log('Function is running!');
        console.log('Event object:', event);
        console.log('Current step:', window._courseFormStep);
        console.log('Total steps:', window._courseTotalSteps);
        
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        try {
            const currentStep = window._courseFormStep || 1;
            const totalSteps = window._courseTotalSteps || 9;
            
            console.log('Current step:', currentStep, 'Total steps:', totalSteps);
            
        // ALWAYS validate before proceeding - REQUIRED
        if (typeof window.validateCurrentStep === 'function' || typeof validateCurrentStep === 'function') {
            console.log('🔍 Running validation for step', currentStep);
            const validateFunc = window.validateCurrentStep || validateCurrentStep;
            const isValid = validateFunc();
            console.log('🔍 Validation result:', isValid);
            if (!isValid) {
                console.log('❌ Validation failed - blocking navigation');
                return false;
            }
            console.log('✅ Validation passed - proceeding to next step');
        } else {
            console.error('❌ validateCurrentStep function not found!');
            showValidationErrorModal('Validation function not loaded. Please refresh the page.', []);
            return false;
        }
            
            // Move to next step
            if (currentStep < totalSteps) {
                const currentStepEl = document.getElementById('step' + currentStep);
                const nextStepEl = document.getElementById('step' + (currentStep + 1));
                
                console.log('Current step element:', currentStepEl);
                console.log('Next step element:', nextStepEl);
                
                if (currentStepEl) {
                    currentStepEl.classList.remove('active');
                }
                
                if (nextStepEl) {
                    window._courseFormStep = currentStep + 1;
                    nextStepEl.classList.add('active');
                    
                    // Update progress indicator
                    const currentProgressStep = document.querySelector('.progress-step[data-step="' + currentStep + '"]');
                    const nextProgressStep = document.querySelector('.progress-step[data-step="' + (currentStep + 1) + '"]');
                    if (currentProgressStep) {
                        currentProgressStep.classList.remove('active');
                        currentProgressStep.classList.add('completed');
                    }
                    if (nextProgressStep) {
                        nextProgressStep.classList.add('active');
                    }
                    
                    // Call helper functions if they exist
                    if (typeof updateProgress === 'function') updateProgress();
                    if (typeof updateNavigationButtons === 'function') updateNavigationButtons();
                    if (typeof scrollToTop === 'function') scrollToTop();
                    
                    // If we moved to step 5 (Review), ensure buttons are updated
                    if (window._courseFormStep === 5) {
                        // Force update navigation buttons to show "Submit to QA"
                        setTimeout(() => {
                            if (typeof updateNavigationButtons === 'function') {
                                updateNavigationButtons();
                            }
                            // Also ensure submit button is visible and next button is hidden
                            const nextBtn = document.getElementById('nextStepBtn');
                            const submitBtn = document.getElementById('submitToQABtn');
                            if (nextBtn) nextBtn.style.display = 'none';
                            if (submitBtn) {
                                submitBtn.style.display = 'block';
                                submitBtn.textContent = 'Submit to QA';
                            }
                        }, 50);
                    }
                    
                    console.log('✅ Moved to step', window._courseFormStep);
                } else {
                    console.error('Next step element not found!');
                    alert('Error: Next step not found');
                }
            } else {
                console.log('Already on last step');
            }
            
            return false;
        } catch (error) {
            console.error('Error in nextStep:', error);
            alert('Error: ' + error.message);
            return false;
        }
    };
    console.log('✅ window.nextStep function DEFINED and assigned');
} else {
    console.log('⚠️ window.nextStep already exists, not redefining');
}

// Verify function is defined - IMMEDIATELY after definition
console.log('✅ window.nextStep function DEFINED');
console.log('Function type:', typeof window.nextStep);
console.log('Function exists?', !!window.nextStep);
console.log('Function test:', window.nextStep ? window.nextStep.toString().substring(0, 50) : 'NULL');

// CRITICAL: Test that the function can be called immediately
if (typeof window.nextStep === 'function') {
    console.log('✅✅✅ window.nextStep is DEFINED and is a FUNCTION - READY TO USE');
    // Make it available globally immediately
    window['nextStep'] = window.nextStep; // Ensure it's accessible
} else {
    console.error('❌❌❌ CRITICAL ERROR: window.nextStep is NOT a function!');
    console.error('Type:', typeof window.nextStep);
    console.error('Value:', window.nextStep);
}

// Test if it's accessible
if (typeof window.nextStep === 'function') {
    console.log('✅ nextStep is accessible as a function');
    // Test call to make sure it works
    console.log('Testing function call...');
    try {
        console.log('Function can be called:', typeof window.nextStep === 'function');
    } catch(e) {
        console.error('Error testing function:', e);
    }
} else {
    console.error('❌ nextStep is NOT a function! Type:', typeof window.nextStep);
    console.error('Value:', window.nextStep);
}

console.log('🚨 MODAL SCRIPT FINISHED LOADING');
console.log('Final check - window.nextStep:', typeof window.nextStep, window.nextStep);

// EMERGENCY FALLBACK: If function still isn't defined, define a simple one
if (typeof window.nextStep !== 'function') {
    console.error('🚨 EMERGENCY: nextStep still not defined! Creating fallback...');
    window.nextStep = function(event) {
        alert('Emergency fallback: Moving to next step...');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        if (step1 && step2) {
            step1.classList.remove('active');
            step2.classList.add('active');
            window._courseFormStep = 2;
            console.log('Emergency: Moved to step 2');
        }
        return false;
    };
    console.log('✅ Emergency fallback function created');
}

// Also define variables for backward compatibility  
let currentStep = window._courseFormStep || 1;
const totalSteps = window._courseTotalSteps || 5;

// Make sure currentStep and totalSteps are also on window for global access
window.currentStep = window.currentStep || currentStep;
window.totalSteps = window.totalSteps || totalSteps;

console.log('✅ Variables defined - currentStep:', currentStep, 'totalSteps:', totalSteps);
console.log('window.nextStep type:', typeof window.nextStep);
console.log('window.currentStep:', window.currentStep);
console.log('window.totalSteps:', window.totalSteps);

// Use global variables
let currentStep = window.currentStep || 1;
const totalSteps = window.totalSteps || 5;

// Learning Outcomes Management
let outcomeCount = 0;

function addOutcomeField() {
    outcomeCount++;
    const container = document.getElementById('learningOutcomesContainer');
    const outcomeField = document.createElement('div');
    outcomeField.className = 'outcome-field';
    outcomeField.dataset.outcomeIndex = outcomeCount;
    
    outcomeField.innerHTML = `
        <div class="outcome-label">Outcome ${outcomeCount}:</div>
        <div class="outcome-input-wrapper">
            <textarea class="outcome-input" name="learning_outcomes[]" placeholder="Enter learning outcome ${outcomeCount}..." required></textarea>
            <button type="button" class="remove-outcome-btn" onclick="removeOutcomeField(${outcomeCount})">Remove</button>
        </div>
    `;
    
    container.appendChild(outcomeField);
    updateOutcomeLabels();
}

function removeOutcomeField(index) {
    const field = document.querySelector(`.outcome-field[data-outcome-index="${index}"]`);
    if (field) {
        field.remove();
        updateOutcomeLabels();
    }
}

function updateOutcomeLabels() {
    const fields = document.querySelectorAll('.outcome-field');
    fields.forEach((field, index) => {
        const label = field.querySelector('.outcome-label');
        if (label) {
            label.textContent = `Outcome ${index + 1}:`;
        }
    });
}

function initializeOutcomes() {
    const container = document.getElementById('learningOutcomesContainer');
    if (container && container.children.length === 0) {
        // Initialize with 3 outcome fields
        for (let i = 0; i < 3; i++) {
            addOutcomeField();
        }
    }
}

// Course Outline Table Management
let topicCount = 0;

function addTopicRow() {
    topicCount++;
    const tbody = document.getElementById('courseOutlineTableBody');
    const row = document.createElement('tr');
    row.className = 'topic-row';
    row.dataset.topicIndex = topicCount;
    
    row.innerHTML = `
        <td>
            <input type="text" class="topic-input" name="course_outline_week[]" placeholder="e.g., Week 1" required>
        </td>
        <td>
            <textarea class="topic-description" name="course_outline_description[]" placeholder="Enter description or subtopics..." required></textarea>
        </td>
        <td>
            <input type="number" class="topic-hours" name="course_outline_hours[]" placeholder="3" min="3" step="0.5" required>
        </td>
        <td>
            <button type="button" class="remove-topic-btn" onclick="removeTopicRow(${topicCount})">Remove</button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeTopicRow(index) {
    const row = document.querySelector(`.topic-row[data-topic-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

// Assessment Methods Table Management
let assessmentCount = 0;

function addAssessmentRow() {
    assessmentCount++;
    const tbody = document.getElementById('assessmentTableBody');
    const row = document.createElement('tr');
    row.className = 'assessment-row';
    row.dataset.assessmentIndex = assessmentCount;
    
    row.innerHTML = `
        <td>
            <input type="text" class="assessment-type-input" name="assessment_type[]" placeholder="e.g., Quizzes" required>
        </td>
        <td>
            <input type="number" class="assessment-percentage-input" name="assessment_percentage[]" placeholder="20" min="0" max="100" step="0.1" required>
        </td>
        <td>
            <button type="button" class="remove-assessment-btn" onclick="removeAssessmentRow(${assessmentCount})">Remove</button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeAssessmentRow(index) {
    const row = document.querySelector(`.assessment-row[data-assessment-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

// Learning Materials Table Management
// Functions are now defined at the top of the script (before modal HTML)
// This section is kept for backward compatibility and initialization

function initializeLearningMaterials() {
    const tbody = document.getElementById('learningMaterialsTableBody');
    if (tbody && tbody.children.length === 0) {
        // Initialize with 2 example rows
        const defaultMaterials = [
            { title: 'Data Analytics: Concepts & Techniques', author: 'Smith', publisher: 'Pearson', year: 2021, type: 'Book', remarks: 'Required' },
            { title: 'Journal of ____', author: 'Doe', publisher: 'Academic Press', year: 2023, type: 'Journal', remarks: 'Optional' }
        ];
        
        defaultMaterials.forEach(item => {
            materialCount++;
            const row = document.createElement('tr');
            row.className = 'material-row';
            row.dataset.materialIndex = materialCount;
            
            row.innerHTML = `
                <td>
                    <div class="call-number-autocomplete">
                        <input type="text" class="material-call-number-input" name="material_call_number[]" placeholder="e.g., QA76.9" data-row-index="${materialCount}">
                        <div class="call-number-suggestions" id="suggestions-${materialCount}"></div>
                    </div>
                </td>
                <td>
                    <input type="text" class="material-title-input" name="material_title[]" placeholder="e.g., Data Analytics: Concepts & Techniques" value="${item.title}">
                </td>
                <td>
                    <input type="text" class="material-author-input" name="material_author[]" placeholder="e.g., Smith" value="${item.author}">
                </td>
                <td>
                    <input type="text" class="material-publisher-input" name="material_publisher[]" placeholder="e.g., Pearson" value="${item.publisher || ''}">
                </td>
                <td>
                    <input type="number" class="material-year-input" name="material_year[]" placeholder="2021" min="1900" max="2100" value="${item.year}">
                </td>
                <td>
                    <select class="material-type-input" name="material_type[]">
                        <option value="">Select Type</option>
                        <option value="Book" ${item.type === 'Book' ? 'selected' : ''}>Book</option>
                        <option value="Journal" ${item.type === 'Journal' ? 'selected' : ''}>Journal</option>
                        <option value="Article" ${item.type === 'Article' ? 'selected' : ''}>Article</option>
                        <option value="Online Resource" ${item.type === 'Online Resource' ? 'selected' : ''}>Online Resource</option>
                        <option value="Other" ${item.type === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="material-remarks-input" name="material_remarks[]" placeholder="e.g., Required" value="${item.remarks}">
                </td>
                <td>
                    <button type="button" class="remove-material-btn" onclick="removeMaterialRow(${materialCount})">Remove</button>
                </td>
            `;
            
            tbody.appendChild(row);
            
            // Setup autocomplete for the new call number input
            setTimeout(() => setupCallNumberAutocomplete(materialCount), 100);
        });
        */
    }
}

// Call Number Autocomplete Functionality
let autocompleteTimeout = null;
let selectedSuggestionIndex = -1;

function setupCallNumberAutocomplete(rowIndex) {
    const callNumberInput = document.querySelector(`.material-call-number-input[data-row-index="${rowIndex}"]`);
    if (!callNumberInput) return;
    
    const suggestionsDiv = document.getElementById(`suggestions-${rowIndex}`);
    if (!suggestionsDiv) return;
    
    const row = callNumberInput.closest('.material-row');
    const titleInput = row.querySelector('.material-title-input');
    const authorInput = row.querySelector('.material-author-input');
    const yearInput = row.querySelector('.material-year-input');
    
    callNumberInput.addEventListener('input', function() {
        const query = this.value.trim();
        selectedSuggestionIndex = -1;
        
        if (query.length < 2) {
            suggestionsDiv.classList.remove('show');
            suggestionsDiv.innerHTML = '';
            return;
        }
        
        // Clear previous timeout
        if (autocompleteTimeout) {
            clearTimeout(autocompleteTimeout);
        }
        
        // Debounce the search
        autocompleteTimeout = setTimeout(() => {
            searchLibraryBooksByCallNumber(query, rowIndex, titleInput, authorInput, yearInput);
        }, 300);
    });
    
    callNumberInput.addEventListener('blur', function() {
        // Delay hiding suggestions to allow click events
        setTimeout(() => {
            suggestionsDiv.classList.remove('show');
        }, 200);
    });
    
    callNumberInput.addEventListener('keydown', function(e) {
        const suggestions = suggestionsDiv.querySelectorAll('.suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, suggestions.length - 1);
            updateSuggestionSelection(suggestions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
            updateSuggestionSelection(suggestions);
        } else if (e.key === 'Enter' && selectedSuggestionIndex >= 0) {
            e.preventDefault();
            const selectedItem = suggestions[selectedSuggestionIndex];
            if (selectedItem) {
                selectSuggestion(selectedItem, titleInput, authorInput, yearInput, callNumberInput);
            }
        } else if (e.key === 'Escape') {
            suggestionsDiv.classList.remove('show');
        }
    });
}

function updateSuggestionSelection(suggestions) {
    suggestions.forEach((item, index) => {
        if (index === selectedSuggestionIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

function searchLibraryBooksByCallNumber(query, rowIndex, titleInput, authorInput, yearInput) {
    const suggestionsDiv = document.getElementById(`suggestions-${rowIndex}`);
    
    fetch(`api/search_library_books_by_call_number.php?call_number=${encodeURIComponent(query)}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.books.length > 0) {
                displaySuggestions(data.books, suggestionsDiv, titleInput, authorInput, yearInput, rowIndex);
            } else {
                suggestionsDiv.classList.remove('show');
                suggestionsDiv.innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error searching library books:', error);
            suggestionsDiv.classList.remove('show');
        });
}

function displaySuggestions(books, suggestionsDiv, titleInput, authorInput, yearInput, rowIndex) {
    suggestionsDiv.innerHTML = '';
    
    books.forEach(book => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.innerHTML = `
            <div class="suggestion-call-number">${book.call_number}</div>
            <div class="suggestion-title">${book.title}</div>
            <div class="suggestion-author">${book.authors}${book.copyright_year ? ' (' + book.copyright_year + ')' : ''}</div>
        `;
        
        // Store book data for auto-fill
        item.dataset.callNumber = book.call_number;
        item.dataset.title = book.title;
        item.dataset.author = book.authors;
        item.dataset.year = book.copyright_year || '';
        
        item.addEventListener('click', function() {
            selectSuggestion(item, titleInput, authorInput, yearInput, document.querySelector(`.material-call-number-input[data-row-index="${rowIndex}"]`));
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    suggestionsDiv.classList.add('show');
}

function selectSuggestion(item, titleInput, authorInput, yearInput, callNumberInput) {
    // Get data from dataset
    const callNumber = item.dataset.callNumber;
    const title = item.dataset.title;
    const author = item.dataset.author;
    const year = item.dataset.year;
    
    // Auto-fill the fields
    if (callNumberInput) callNumberInput.value = callNumber;
    if (titleInput) titleInput.value = title;
    if (authorInput) authorInput.value = author;
    if (yearInput) yearInput.value = year;
    
    // Hide suggestions
    const suggestionsDiv = item.closest('.call-number-suggestions');
    if (suggestionsDiv) {
        suggestionsDiv.classList.remove('show');
    }
}

// Function to attach navigation event listeners
function attachNavigationEventListeners() {
    console.log('=== ATTACHING NAVIGATION EVENT LISTENERS ===');
    console.log('window.nextStep type:', typeof window.nextStep);
    console.log('window.nextStep value:', window.nextStep);
    
    const nextBtn = document.getElementById('nextStepBtn');
    const prevBtn = document.getElementById('prevStepBtn');
    const submitBtn = document.getElementById('submitToQABtn');
    
    if (!nextBtn) {
        console.error('❌ Next button not found!');
        return;
    }
    
    console.log('✅ Next button found:', nextBtn);
    
    // Remove any existing click handlers by cloning
    if (nextBtn) {
        const newNextBtn = nextBtn.cloneNode(true);
        // Keep onclick as fallback, but event listener will handle it
        // Don't remove onclick - let both work together
        nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
        
        // Use a direct, simple event listener - don't prevent default to allow onclick to work too
        newNextBtn.addEventListener('click', function(event) {
            console.log('🔵🔵🔵 NEXT BUTTON CLICKED - EVENT LISTENER FIRED!');
            console.log('Event:', event);
            console.log('window.nextStep type:', typeof window.nextStep);
            console.log('window.nextStep:', window.nextStep);
            
            // Don't prevent default - let onclick handler work as fallback
            // Only prevent if we successfully handle it
            if (typeof window.nextStep === 'function') {
                console.log('✅✅✅ Calling window.nextStep...');
                try {
                    event.preventDefault();
                    event.stopPropagation();
                    const result = window.nextStep(event);
                    console.log('nextStep returned:', result);
                    // If validation failed (returns false), don't proceed
                    if (result === false) {
                        console.log('❌ Navigation blocked due to validation failure');
                        return false;
                    }
                } catch (e) {
                    console.error('❌ Error calling nextStep:', e);
                    if (typeof showValidationErrorModal === 'function') {
                        showValidationErrorModal('Error calling nextStep: ' + e.message, []);
                    } else {
                        alert('Error calling nextStep: ' + e.message);
                    }
                    return false;
                }
            } else {
                console.error('❌❌❌ nextStep function not found! Type:', typeof window.nextStep);
                console.error('Available on window:', Object.keys(window).filter(k => k.includes('next')));
                // Don't prevent default - let onclick handler try
                return true;
            }
        }, false);
        
        console.log('✅ Next button event listener attached');
        console.log('Button onclick attribute:', newNextBtn.getAttribute('onclick'));
    }
    
    if (prevBtn) {
        const newPrevBtn = prevBtn.cloneNode(true);
        // Keep onclick as fallback, but event listener will handle it
        // Don't remove onclick - let both work together
        prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
        
        // Remove inline style attribute so JavaScript can control display completely
        newPrevBtn.removeAttribute('style');
        
        // Set correct display state based on current step
        const currentStep = window._courseFormStep || 1;
        if (currentStep > 1) {
            newPrevBtn.style.display = 'inline-flex';
            newPrevBtn.style.visibility = 'visible';
            newPrevBtn.style.opacity = '1';
            console.log('✅ Previous button shown after cloning (step > 1)');
        } else {
            newPrevBtn.style.display = 'none';
            console.log('Previous button hidden after cloning (step 1)');
        }
        
        newPrevBtn.addEventListener('click', function(event) {
            console.log('🔵 PREVIOUS BUTTON CLICKED - EVENT LISTENER FIRED!');
            if (typeof window.previousStep === 'function') {
                event.preventDefault();
                event.stopPropagation();
                window.previousStep();
            } else {
                console.error('previousStep function not found!');
                // Don't prevent default - let onclick handler try
                return true;
            }
        });
        console.log('✅ Previous button event listener attached, final display:', newPrevBtn.style.display);
    } else {
        console.error('❌ Previous button not found in attachNavigationEventListeners!');
    }
    
    if (submitBtn) {
        const newSubmitBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
        newSubmitBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof handleSubmitToQA === 'function') {
                handleSubmitToQA(event);
            } else {
                console.error('handleSubmitToQA function not found!');
            }
        });
        console.log('✅ Submit button event listener attached');
    }
    
    console.log('=== NAVIGATION EVENT LISTENERS COMPLETE ===');
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    updateNavigationButtons();
    setupFormValidation();
    initializeLearningMaterials();
    
    // Initialize file drop zone
    if (typeof window.initializeFileDropZone === 'function') {
        window.initializeFileDropZone();
    } else {
        console.warn('⚠️ initializeFileDropZone not available on DOMContentLoaded, will retry...');
        setTimeout(() => {
            if (typeof window.initializeFileDropZone === 'function') {
                window.initializeFileDropZone();
            }
        }, 100);
    }
    
    // Attach event listeners to navigation buttons
    attachNavigationEventListeners();
    
    // Update navigation buttons after event listeners are attached
    if (typeof updateNavigationButtons === 'function') {
        updateNavigationButtons();
    }
    
    // Watch for step3 activation and initialize learning outcomes
    const step3 = document.getElementById('step3');
    if (step3) {
        const step3Observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const isActive = step3.classList.contains('active');
                    if (isActive) {
                        console.log('🔍 Step 3 became active, initializing learning outcomes...');
                        setTimeout(() => {
                            if (typeof window.initializeLearningOutcomes === 'function') {
                                window.initializeLearningOutcomes();
                            }
                        }, 100);
                    }
                }
            });
        });
        step3Observer.observe(step3, { attributes: true, attributeFilter: ['class'] });
    }
    
    // Watch for step4 activation and initialize course outline
    const step4 = document.getElementById('step4');
    if (step4) {
        // Check if step 4 is already active when observer is set up
        if (step4.classList.contains('active')) {
            const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
            const tbody = document.getElementById('courseOutlineTableBody');
            const hasExistingData = tbody && tbody.querySelectorAll('tr').length > 0;
            
            if (!isResumingDraft && !hasExistingData) {
                console.log('🔍 Step 4 is already active on page load, initializing course outline...');
                setTimeout(() => {
                    if (typeof window.initializeCourseOutline === 'function') {
                        window.initializeCourseOutline();
                    }
                }, 200);
            } else {
                console.log('📝 Skipping course outline initialization on page load - draft data exists or already populated');
            }
        }
        
        const step4Observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const isActive = step4.classList.contains('active');
                    if (isActive) {
                        console.log('🔍 Step 4 became active (via observer), initializing course outline...');
                        // Call immediately
                        if (typeof window.initializeCourseOutline === 'function') {
                            window.initializeCourseOutline();
                        }
                        // Also call with delays
                        setTimeout(() => {
                            if (typeof window.initializeCourseOutline === 'function') {
                                window.initializeCourseOutline();
                            } else {
                                console.error('❌ initializeCourseOutline function not found in observer!');
                            }
                        }, 200);
                        setTimeout(() => {
                            if (typeof window.initializeCourseOutline === 'function') {
                                window.initializeCourseOutline();
                            }
                        }, 400);
                    }
                }
            });
        });
        step4Observer.observe(step4, { attributes: true, attributeFilter: ['class'] });
    } else {
        console.warn('⚠️ Step 4 element not found for observer setup');
    }
    
    // Watch for step5 activation and force button update
    const step5 = document.getElementById('step5');
    if (step5) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const isActive = step5.classList.contains('active');
                    if (isActive) {
                        console.log('🔍 Step 5 became active, forcing button update...');
                        // Force button update immediately
                        const nextBtn = document.getElementById('nextStepBtn');
                        const submitBtn = document.getElementById('submitToQABtn');
                        
                        if (nextBtn) {
                            nextBtn.style.display = 'none';
                            nextBtn.textContent = 'Next';
                        }
                        
                        if (submitBtn) {
                            submitBtn.style.display = 'block';
                            submitBtn.textContent = 'Submit to QA';
                        }
                        
                        // Also call updateNavigationButtons
                        if (typeof updateNavigationButtons === 'function') {
                            setTimeout(() => updateNavigationButtons(), 10);
                        }
                    }
                }
            });
        });
        
        observer.observe(step5, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        console.log('✅ MutationObserver set up to watch step5 activation');
    }
});

function updateProgress() {
    // Use window._courseFormStep if available, otherwise fall back to currentStep
    const step = window._courseFormStep || currentStep || 1;
    const total = window._courseTotalSteps || totalSteps || 9;
    const progress = ((step - 1) / (total - 1)) * 100;
    const progressFill = document.getElementById('progressFill');
    if (progressFill) {
        progressFill.style.width = progress + '%';
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitToQABtn');
    
    if (!nextBtn) {
        console.error('Next button not found!');
        return;
    }
    
    // Determine current step - check active step element first, then fall back to window variables
    let step = window._courseFormStep || 1;
    for (let i = 1; i <= 9; i++) {
        const stepEl = document.getElementById('step' + i);
        if (stepEl && stepEl.classList.contains('active')) {
            step = i;
            break;
        }
    }
    
    // If no active step found, use window variables
    if (step === 1 && !document.getElementById('step1')?.classList.contains('active')) {
        step = window._courseFormStep || 1;
    }
    
    const total = window._courseTotalSteps || 9;
    
    console.log('updateNavigationButtons - step:', step, 'total:', total, 'window._courseFormStep:', window._courseFormStep);
    
    // Show/hide Previous button
    if (prevBtn) {
        if (step === 1) {
            prevBtn.style.display = 'none';
            console.log('Previous button hidden (step 1)');
        } else {
            prevBtn.style.display = 'inline-flex'; // Use inline-flex to match CSS class
            prevBtn.style.visibility = 'visible';
            prevBtn.style.opacity = '1';
            console.log('✅ Previous button shown (step > 1), display:', prevBtn.style.display);
        }
    } else {
        console.error('❌ Previous button (prevStepBtn) not found!');
    }
    
    // Show/hide Next and Submit buttons
    if (step >= total || step === 9) {
        // On last step (step 9), hide Next and show Submit
        if (nextBtn) {
            nextBtn.style.display = 'none';
            nextBtn.textContent = 'Next'; // Reset text in case it was changed
        }
        if (submitBtn) {
            submitBtn.style.display = 'inline-flex'; // Use inline-flex to match CSS class
            submitBtn.textContent = 'Submit to QA';
            console.log('✅ Submit button shown with text: Submit to QA');
        }
    } else {
        // On steps 1-4, show Next and hide Submit
        if (nextBtn) {
            nextBtn.style.display = 'inline-flex'; // Use inline-flex to match CSS class
            nextBtn.disabled = false;
            nextBtn.style.pointerEvents = 'auto';
            nextBtn.style.opacity = '1';
            nextBtn.textContent = 'Next';
        }
        if (submitBtn) {
            submitBtn.style.display = 'none';
        }
    }
    
    console.log('Navigation buttons updated. Step:', step, 'Previous button display:', prevBtn?.style.display, 'Next button display:', nextBtn.style.display, 'Submit button display:', submitBtn?.style.display);
}

// validateCurrentStep function moved to top of file (line ~88) - removed duplicate here

function scrollToTop() {
    const stepsContainer = document.querySelector('.form-steps-container');
    if (stepsContainer) {
        stepsContainer.scrollTop = 0;
    }
}

function setupFormValidation() {
    // Add validation listeners to all required fields
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#4CAF50';
            } else {
                this.style.borderColor = '#f44336';
            }
        });
    });
}

// File Management Functions - Already defined above in the script section

// Handle form submission
function handleSubmitToQA(event) {
    event.preventDefault();
    
    if (!validateCurrentStep()) {
        return;
    }
    
    const form = document.getElementById('addCourseForm');
    const formData = new FormData(form);
    
    // Check if we're in course selection context (from the new modal)
    const isFromCourseSelection = window.courseSelectionContext !== undefined && window.courseSelectionContext !== null;
    
    if (isFromCourseSelection) {
        // Add to course selection list temporarily (without backend submission)
        const submitBtn = document.getElementById('submitToQABtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';
        
        try {
            // Collect all form data
            const courseData = {
                course_code: formData.get('course_code') || '',
                course_title: formData.get('course_name') || '',
                course_name: formData.get('course_name') || '',
                units: formData.get('units') || '',
                lecture_hours: formData.get('lecture_hours') || '',
                laboratory_hours: formData.get('laboratory_hours') || '',
                prerequisites: formData.get('prerequisites') || 'None',
                course_description: formData.get('course_description') || '',
                learning_materials: [],
                justification: formData.get('justification') || '',
                attachments: window.attachmentFiles || [],
                // Store form data for later submission
                _formData: Object.fromEntries(formData),
                _isDraft: true,
                _tempId: 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
            };
            
            // Get learning materials
            if (window.materialList && Array.isArray(window.materialList)) {
                courseData.learning_materials = window.materialList.map(m => ({
                    material_type: m.type || '',
                    material_title: m.title || '',
                    author: m.author || '',
                    publication_year: m.year || '',
                    edition: m.edition || '',
                    publisher: m.publisher || '',
                    isbn: m.isbn || ''
                }));
                courseData.learning_materials_count = window.materialList.length;
            }
            
            // Add to selection list
            if (typeof addCourseToSelectionList === 'function') {
                addCourseToSelectionList(courseData);
                
                // Show success modal
                showAddToListSuccessModal(courseData.course_code, courseData.course_name);
                
                // Close add course modal but keep Manage Program Courses modal open
                closeAddCourseModal();
                
                // Ensure Manage Program Courses modal stays open
                setTimeout(() => {
                    const courseSelectionModal = document.getElementById('courseSelectionModal');
                    if (courseSelectionModal && courseSelectionModal.style.display === 'none') {
                        if (typeof openCourseSelectionModal === 'function') {
                            openCourseSelectionModal();
                        }
                    }
                }, 100);
            } else {
                showAddToListErrorModal('Failed to add course. Please try again.');
            }
        } catch (error) {
            console.error('Error adding course to list:', error);
            showAddToListErrorModal('An error occurred while adding the course. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit to QA';
        }
    } else {
        // Original behavior: submit directly to QA
        if (confirm('Are you sure you want to submit this course proposal to Quality Assurance for review?')) {
            const submitBtn = document.getElementById('submitToQABtn');
            if (!submitBtn) {
                console.error('Submit button not found!');
                alert('Error: Submit button not found. Please refresh the page.');
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            // Submit to backend
            console.log('Submitting course to QA...');
            console.log('Form data:', formData);
            
            fetch('process_course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response.status, response.statusText);
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Get response text first to check if it's valid JSON
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed JSON data:', data);
                        return data;
                    } catch (parseError) {
                        console.error('Failed to parse JSON response:', text);
                        console.error('Parse error:', parseError);
                        throw new Error('Invalid response from server. Please try again.');
                    }
                });
            })
            .then(data => {
                console.log('Processing response data:', data);
                if (data.success) {
                    console.log('Success! Showing success modal...');
                    const courseCode = formData.get('course_code');
                    const courseName = formData.get('course_name');
                    console.log('Course code:', courseCode, 'Course name:', courseName);
                    
                    if (typeof showCourseSuccessModal === 'function') {
                        showCourseSuccessModal({
                            course_code: courseCode,
                            course_name: courseName
                        });
                        console.log('Success modal should be shown');
                    } else {
                        console.error('showCourseSuccessModal function not found!');
                        alert('Successfully submitted course proposal to QA!');
                    }
                } else {
                    console.log('Submission failed. Showing error modal...');
                    if (typeof showCourseErrorModal === 'function') {
                        showCourseErrorModal(data.message || 'Failed to submit course proposal.');
                    } else {
                        console.error('showCourseErrorModal function not found!');
                        alert('Error: ' + (data.message || 'Failed to submit course proposal.'));
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting course:', error);
                console.error('Error stack:', error.stack);
                if (typeof showCourseErrorModal === 'function') {
                    showCourseErrorModal(error.message || 'Network error occurred. Please try again.');
                } else {
                    console.error('showCourseErrorModal function not found!');
                    alert('Error: ' + (error.message || 'Network error occurred. Please try again.'));
                }
            })
            .finally(() => {
                console.log('Resetting submit button...');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit to QA';
                }
            });
        } else {
            console.log('User cancelled submission');
        }
    }
    
    return false; // Always prevent form submission
}

// Modal functions
window.openAddCourseModal = function() {
        console.log('=== openAddCourseModal CALLED ===');
        console.log('window.courseSelectionContext:', window.courseSelectionContext);
        console.log('typeof window.courseSelectionContext:', typeof window.courseSelectionContext);
        console.log('window.courseSelectionContext === undefined:', window.courseSelectionContext === undefined);
        console.log('window.courseSelectionContext === null:', window.courseSelectionContext === null);
        console.log('!!window.courseSelectionContext:', !!window.courseSelectionContext);
        
        const modal = document.getElementById('addCourseModal');
        if (!modal) {
            console.error('Modal not found!');
            return;
        }
        
        // Check if modal was discarded - if so, ensure completely fresh start
        if (window._modalWasDiscarded) {
            window.draftToResume = null;
            if (window.courseSelectionContext) {
                delete window.courseSelectionContext.isResumingDraft;
                delete window.courseSelectionContext.proposalId;
                delete window.courseSelectionContext.skipCourseTypeSelection;
            }
            window._modalWasDiscarded = false; // Clear the flag
            console.log('📝 Modal was discarded - ensuring completely fresh start');
        }
        
        // Check if we're resuming a draft - if not, clear any draft-related flags
        const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
        
        // Always clear draft resume data unless we're explicitly resuming
        if (!isResumingDraft) {
            // Clear draft resume data when opening a fresh modal
            window.draftToResume = null;
            // Clear the isResumingDraft flag if it exists in the context
            if (window.courseSelectionContext) {
                delete window.courseSelectionContext.isResumingDraft;
            }
            console.log('📝 Cleared draft resume data - opening fresh modal');
        } else {
            // Only keep draft data if we're explicitly resuming
            console.log('📝 Resuming draft - keeping draft data');
        }
        
        // Check if we have course selection context FIRST, before showing modal
        // Apply class BEFORE modal is displayed so CSS can take effect immediately
        const hasContext = window.courseSelectionContext !== undefined && window.courseSelectionContext !== null && Object.keys(window.courseSelectionContext).length > 0;
        console.log('hasContext:', hasContext);
        console.log('Context object:', JSON.stringify(window.courseSelectionContext, null, 2));
        
        if (hasContext) {
            modal.classList.add('from-course-selection');
            console.log('✓ Course selection context detected, class added to modal');
            console.log('Context details:', JSON.stringify(window.courseSelectionContext, null, 2));
        } else {
            modal.classList.remove('from-course-selection');
            console.log('✗ No course selection context');
        }
            
            // Inject CSS directly if context exists
            if (window.courseSelectionContext) {
                // Remove any existing injected style
                const existingStyle = document.getElementById('hide-course-selection-fields-style');
                if (existingStyle) {
                    existingStyle.remove();
                }
                
                // Create and inject style tag
                const style = document.createElement('style');
                style.id = 'hide-course-selection-fields-style';
                style.textContent = `
                    #addCourseModal.from-course-selection #programField,
                    #addCourseModal.from-course-selection #academicFieldsRow {
                        display: none !important;
                        visibility: hidden !important;
                        height: 0 !important;
                        max-height: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        overflow: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        width: 0 !important;
                        min-width: 0 !important;
                        max-width: 0 !important;
                        border: none !important;
                        line-height: 0 !important;
                    }
                `;
                document.head.appendChild(style);
                console.log('✓ Injected CSS style tag to hide fields');
            } else {
                // Remove injected style if no context
                const existingStyle = document.getElementById('hide-course-selection-fields-style');
                if (existingStyle) {
                    existingStyle.remove();
                }
            }
            
            // Show modal
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Academic years are now populated server-side in PHP (like academic terms)
            
            // Learning outcomes will be initialized when step 3 becomes active (in nextStep/previousStep functions)
            
            // Initialize file drop zone - simple one time only
            setTimeout(() => {
                const testDropZone = document.getElementById('fileDropZone');
                const testFileInput = document.getElementById('courseAttachments');
                const testAttachmentList = document.getElementById('attachmentList');
                if (testDropZone && testFileInput && testAttachmentList && typeof window.initializeFileDropZone === 'function') {
                    window.initializeFileDropZone();
                }
            }, 600);
            
            // IMMEDIATELY hide fields if context exists - use multiple methods
            if (hasContext) {
                // Method 1: Direct style application
                requestAnimationFrame(() => {
                    const pf = document.getElementById('programField');
                    const afr = document.getElementById('academicFieldsRow');
                    console.log('FRAME 1 - programField:', !!pf, 'academicFieldsRow:', !!afr);
                    if (pf) {
                        pf.style.setProperty('display', 'none', 'important');
                        pf.style.setProperty('visibility', 'hidden', 'important');
                    }
                    if (afr) {
                        afr.style.setProperty('display', 'none', 'important');
                        afr.style.setProperty('visibility', 'hidden', 'important');
                    }
                });
                
                // Method 2: After a tiny delay
                setTimeout(() => {
                    const pf = document.getElementById('programField');
                    const afr = document.getElementById('academicFieldsRow');
                    console.log('TIMEOUT 1 - programField:', !!pf, 'academicFieldsRow:', !!afr);
                    if (pf) {
                        pf.style.setProperty('display', 'none', 'important');
                        pf.style.setProperty('visibility', 'hidden', 'important');
                    }
                    if (afr) {
                        afr.style.setProperty('display', 'none', 'important');
                        afr.style.setProperty('visibility', 'hidden', 'important');
                    }
                }, 0);
            }
            
            // Force a reflow to ensure CSS is applied
            void modal.offsetHeight;
  
  // Reset form (but skip if resuming draft)
        const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft;
        
        if (!isResumingDraft) {
            window._courseFormStep = 1;
            document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
            const step1 = document.getElementById('step1');
            if (step1) {
                step1.classList.add('active');
            }
            document.querySelectorAll('.progress-step').forEach(step => {
                step.classList.remove('active', 'completed');
            });
            const firstProgressStep = document.querySelector('.progress-step[data-step="1"]');
            if (firstProgressStep) {
                firstProgressStep.classList.add('active');
            }
            updateProgress();
            updateNavigationButtons();
        } else {
            console.log('📝 Resuming draft - skipping form reset');
            // Don't reset step - it will be set by loadDraftIntoForm
        }
        
        // Save program selection before reset (check multiple sources)
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const programSelectText = document.getElementById('programSelectText');
        const programSelectBtn = document.getElementById('programSelectBtn');
        
        let savedProgramIds = '';
        let savedProgramNames = [];
        let savedProgramText = '';
        
        // Check localStorage first
        try {
            const saved = localStorage.getItem('lastSelectedPrograms');
            if (saved) {
                const savedData = JSON.parse(saved);
                if (Date.now() - savedData.timestamp < 3600000) { // Within last hour
                    savedProgramIds = savedData.ids ? savedData.ids.join(',') : '';
                    savedProgramNames = savedData.names || [];
                }
            }
        } catch (e) {
            console.warn('Could not read from localStorage:', e);
        }
        
        // Check window.selectedProgramsData
        if (!savedProgramIds && window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
            savedProgramIds = window.selectedProgramsData.ids.join(',');
            savedProgramNames = window.selectedProgramsData.names || [];
        }
        
        // Check button data attributes
        if (!savedProgramIds && programSelectBtn) {
            const btnIds = programSelectBtn.getAttribute('data-selected-programs');
            if (btnIds) {
                savedProgramIds = btnIds;
                try {
                    const btnNames = programSelectBtn.getAttribute('data-selected-names');
                    savedProgramNames = btnNames ? JSON.parse(btnNames) : [];
                } catch (e) {}
            }
        }
        
        // Check current input value
        if (!savedProgramIds && selectedProgramsInput && selectedProgramsInput.value) {
            savedProgramIds = selectedProgramsInput.value;
        }
        
        // Check current button text
        if (!savedProgramText && programSelectText && programSelectText.textContent !== 'Select Program(s)') {
            savedProgramText = programSelectText.textContent;
        }
        
        // Generate text from names if we have names but not text
        if (!savedProgramText && savedProgramNames.length > 0) {
            savedProgramText = savedProgramNames.length === 1 ? savedProgramNames[0] : `${savedProgramNames.length} Programs Selected`;
        }
        
        // Store program selection data globally before any reset
        if (savedProgramIds) {
            window.selectedProgramsData = {
                ids: savedProgramIds.split(',').filter(v => v),
                names: savedProgramNames.length > 0 ? savedProgramNames : []
            };
        }
        
        // Reset form fields MANUALLY (excluding program selection) instead of using form.reset()
        // This gives us complete control and ensures program selection is NEVER touched
        // BUT skip reset if we're resuming a draft (draft data will be loaded instead)
        const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft;
        
        if (!isResumingDraft) {
            const form = document.getElementById('addCourseForm');
            if (form) {
                // Store the program selection value (we'll preserve it)
                const programValueBeforeReset = savedProgramIds || (selectedProgramsInput ? selectedProgramsInput.value : '');
                const programTextBeforeReset = savedProgramText || (programSelectText ? programSelectText.textContent : '');
                
                // Manually reset ALL form fields EXCEPT the program selection
                const fieldsToReset = form.querySelectorAll('input, select, textarea');
                fieldsToReset.forEach(field => {
                    // Skip the program selection hidden input
                    if (field.id === 'selectedPrograms') {
                        return; // Don't reset this field
                    }
                    
                    // Reset based on field type
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = false;
                    } else if (field.tagName === 'SELECT') {
                        field.selectedIndex = 0;
                    } else {
                        field.value = '';
                    }
                });
                
                console.log('✅ Manually reset all form fields except program selection');
                
                // Now ensure program selection is preserved
                if (programValueBeforeReset) {
                    if (selectedProgramsInput) {
                        selectedProgramsInput.value = programValueBeforeReset;
                        selectedProgramsInput.defaultValue = programValueBeforeReset;
                        selectedProgramsInput.setAttribute('value', programValueBeforeReset);
                    }
                    if (programSelectText && programTextBeforeReset && programTextBeforeReset !== 'Select Program(s)') {
                        programSelectText.textContent = programTextBeforeReset;
                    }
                    if (programSelectBtn) {
                        programSelectBtn.setAttribute('data-selected-programs', programValueBeforeReset);
                    }
                    console.log('✅ Preserved program selection:', programValueBeforeReset);
                }
            }
        } else {
            console.log('📝 Resuming draft - skipping form field reset (draft data will be loaded)');
        }
        
        // Program selection is already preserved above (we didn't reset it)
        // But ensure it's still visible with a delayed check
        if (savedProgramIds) {
            setTimeout(() => {
                const input = document.getElementById('selectedPrograms');
                const text = document.getElementById('programSelectText');
                const btn = document.getElementById('programSelectBtn');
                
                // Double-check that program selection is still there
                if (input && (!input.value || input.value !== savedProgramIds)) {
                    input.value = savedProgramIds;
                    input.defaultValue = savedProgramIds;
                    input.setAttribute('value', savedProgramIds);
                    console.log('🔄 Re-restored program selection (delayed check):', savedProgramIds);
                }
                if (text && savedProgramText && text.textContent !== savedProgramText && text.textContent === 'Select Program(s)') {
                    text.textContent = savedProgramText;
                    console.log('🔄 Re-restored program text (delayed check):', savedProgramText);
                }
            }, 100);
        }
        // Clear attachment files
        window.attachmentFiles = [];
        const attachmentList = document.getElementById('attachmentList');
        if (attachmentList) {
            attachmentList.innerHTML = '';
        }
        const fileInput = document.getElementById('courseAttachments');
        if (fileInput) {
            fileInput.value = '';
        }
        
        // Re-initialize file drop zone when modal opens to ensure event listeners are attached
        setTimeout(() => {
            console.log('🔄 Attempting to re-initialize file drop zone after modal open');
            console.log('🔄 initializeFileDropZone type:', typeof initializeFileDropZone);
            console.log('🔄 window.initializeFileDropZone type:', typeof window.initializeFileDropZone);
            
            // Try both scopes
            const initFunc = typeof initializeFileDropZone === 'function' ? initializeFileDropZone : 
                           (typeof window.initializeFileDropZone === 'function' ? window.initializeFileDropZone : null);
            
            if (initFunc) {
                console.log('🔄 Re-initializing file drop zone after modal open');
                initFunc();
            } else {
                console.error('❌ initializeFileDropZone function not found!');
                // Try to find and initialize manually
                const dropZone = document.getElementById('fileDropZone');
                const fileInput = document.getElementById('courseAttachments');
                console.log('🔍 Manual check - dropZone:', !!dropZone, 'fileInput:', !!fileInput);
            }
        }, 200);
        
        // IMPORTANT: Remove fields AFTER form reset when context exists
        const hasContextAfterReset = window.courseSelectionContext !== undefined && window.courseSelectionContext !== null;
        console.log('=== CHECKING CONTEXT AFTER FORM RESET ===');
        console.log('hasContextAfterReset:', hasContextAfterReset);
        console.log('window.courseSelectionContext:', window.courseSelectionContext);
        
        // Fields have been removed from the form - no need to remove/hide them
        
        // Function to show course selection fields by removing CSS class
        function showCourseSelectionFields() {
            const programField = document.getElementById('programField');
            const academicFieldsRow = document.getElementById('academicFieldsRow');
            const reviewFields = document.querySelectorAll('.course-selection-review-field');
            
            if (programField) {
                programField.classList.remove('course-selection-field-hidden');
            }
            if (academicFieldsRow) {
                academicFieldsRow.classList.remove('course-selection-field-hidden');
            }
            reviewFields.forEach(field => {
                field.classList.remove('course-selection-field-hidden');
            });
        }
        
        // Fields have been removed from the form - no need to hide/show them
        
        
        // Reset learning materials
        const materialsTableBody = document.getElementById('learningMaterialsTableBody');
        if (materialsTableBody) {
            materialsTableBody.innerHTML = '';
            materialCount = 0;
            initializeLearningMaterials();
        }
        
        // Update submit button text based on context
        const submitBtn = document.getElementById('submitToQABtn');
        if (submitBtn) {
            if (window.courseSelectionContext !== undefined) {
                submitBtn.textContent = 'Submit to QA';
            } else {
                submitBtn.textContent = 'Submit to QA';
            }
        }
        
        // Reset to step 1
        currentStep = 1;
        document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
        document.querySelectorAll('.progress-step').forEach(step => {
            step.classList.remove('active', 'completed');
        });
        const step1 = document.getElementById('step1');
        const progressStep1 = document.querySelector('.progress-step[data-step="1"]');
        if (step1) step1.classList.add('active');
        if (progressStep1) progressStep1.classList.add('active');
        updateProgress();
        updateNavigationButtons();
        
        // When step 1 becomes visible, restore program selection display
        setTimeout(() => {
            if (typeof window.restoreProgramSelection === 'function') {
                window.restoreProgramSelection();
            }
        }, 100);
        
        // Ensure event listeners are attached (in case modal was opened dynamically)
        setTimeout(() => {
            attachNavigationEventListeners();
            // Update navigation buttons after event listeners are attached to ensure correct display state
            if (typeof updateNavigationButtons === 'function') {
                updateNavigationButtons();
            }
        }, 100);
        
        // Set up MutationObserver to protect program selection
        setTimeout(() => {
            if (typeof setupProgramSelectionObserver === 'function') {
                setupProgramSelectionObserver();
            }
        }, 150);
        
        // Start monitoring for program selection persistence
        if (typeof startProgramSelectionMonitor === 'function') {
            setTimeout(() => {
                startProgramSelectionMonitor();
            }, 100);
        }
        
        // Restore program selection after all initialization is complete (check multiple sources)
        setTimeout(() => {
            const selectedProgramsInput = document.getElementById('selectedPrograms');
            const programSelectText = document.getElementById('programSelectText');
            const programSelectBtn = document.getElementById('programSelectBtn');
            
            // Try to restore from multiple sources in order of preference
            let programIds = [];
            let programNames = [];
            let displayText = '';
            
            // 1. Check localStorage first (most persistent)
            try {
                const saved = localStorage.getItem('lastSelectedPrograms');
                if (saved) {
                    const savedData = JSON.parse(saved);
                    // Only use if it's recent (within last hour)
                    if (Date.now() - savedData.timestamp < 3600000) {
                        programIds = savedData.ids || [];
                        programNames = savedData.names || [];
                    }
                }
            } catch (e) {
                console.warn('Could not read from localStorage:', e);
            }
            
            // 2. Check window.selectedProgramsData
            if (programIds.length === 0 && window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
                programIds = window.selectedProgramsData.ids;
                programNames = window.selectedProgramsData.names || [];
            }
            
            // 3. Check button data attributes
            if (programIds.length === 0 && programSelectBtn) {
                const btnDataIds = programSelectBtn.getAttribute('data-selected-programs');
                const btnDataNames = programSelectBtn.getAttribute('data-selected-names');
                if (btnDataIds) {
                    programIds = btnDataIds.split(',').filter(v => v);
                    try {
                        programNames = btnDataNames ? JSON.parse(btnDataNames) : [];
                    } catch (e) {
                        console.warn('Could not parse button data names:', e);
                    }
                }
            }
            
            // 4. Check hidden input value
            if (programIds.length === 0 && selectedProgramsInput && selectedProgramsInput.value) {
                programIds = selectedProgramsInput.value.split(',').filter(v => v);
            }
            
            // Restore if we have data
            if (programIds.length > 0) {
                if (selectedProgramsInput) {
                    selectedProgramsInput.value = programIds.join(',');
                    console.log('✅ Restored program IDs:', selectedProgramsInput.value);
                }
                
                if (programNames.length > 0) {
                    displayText = programNames.length === 1 ? programNames[0] : `${programNames.length} Programs Selected`;
                } else {
                    displayText = programIds.length === 1 ? '1 Program Selected' : `${programIds.length} Programs Selected`;
                }
                
                if (programSelectText) {
                    programSelectText.textContent = displayText;
                    console.log('✅ Restored program text:', displayText);
                }
                
                // Update window.selectedProgramsData for consistency
                window.selectedProgramsData = {
                    ids: programIds,
                    names: programNames
                };
            }
        }, 300);
        
        // Pre-fill form fields if we have course selection context
        // Use setTimeout to ensure DOM is ready after form reset
        setTimeout(() => {
            console.log('Checking courseSelectionContext:', window.courseSelectionContext);
            if (window.courseSelectionContext) {
                const context = window.courseSelectionContext;
                
                // Add class to modal to trigger CSS hiding
                const modal = document.getElementById('addCourseModal');
                if (modal) {
                    modal.classList.add('from-course-selection');
                    console.log('Added from-course-selection class to modal');
                }
                
                // Fields have been removed from the form
                
            } else {
                // Remove class from modal if not from course selection
                const modal = document.getElementById('addCourseModal');
                if (modal) {
                    modal.classList.remove('from-course-selection');
                }
                
                // Fields have been removed from the form
                
            }
        }, 200);
    }
}

// Functions moved to top of file for early availability

// Program selection functions (keep existing)
function openProgramSelectModal() {
    // FIRST: Restore the button text to show current selection before opening modal
    if (typeof window.restoreProgramSelection === 'function') {
        window.restoreProgramSelection();
    }
    
    const modal = document.getElementById('programSelectModal');
    if (modal) {
        // Restore previously selected programs
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        
        // Check multiple sources for saved selection
        let previouslySelected = [];
        if (selectedProgramsInput && selectedProgramsInput.value) {
            previouslySelected = selectedProgramsInput.value.split(',').filter(v => v);
        }
        
        // Also check window.selectedProgramsData
        if (previouslySelected.length === 0 && window.selectedProgramsData && window.selectedProgramsData.ids) {
            previouslySelected = window.selectedProgramsData.ids;
        }
        
        // Also check button data attributes
        const programSelectBtn = document.getElementById('programSelectBtn');
        if (previouslySelected.length === 0 && programSelectBtn) {
            const btnIds = programSelectBtn.getAttribute('data-selected-programs');
            if (btnIds) {
                previouslySelected = btnIds.split(',').filter(v => v);
            }
        }
        
        // Check the checkboxes that were previously selected
        if (previouslySelected.length > 0) {
            previouslySelected.forEach(programId => {
                const checkbox = document.querySelector(`#programSelectModal input[name="programs[]"][value="${programId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            updateConfirmButton();
        }
        
        modal.style.display = 'flex';
        const searchInput = document.getElementById('programSearch');
        if (searchInput) {
            searchInput.value = '';
            filterPrograms('');
            setTimeout(() => searchInput.focus(), 100);
        }
    }
}

function closeProgramSelectModal() {
    document.getElementById('programSelectModal').style.display = 'none';
}

function filterPrograms(searchTerm) {
    const searchLower = searchTerm.toLowerCase().trim();
    const programItems = document.querySelectorAll('#programsList .program-item');
    let visibleCount = 0;
    
    programItems.forEach(item => {
        const programName = item.querySelector('.program-name');
        if (programName) {
            const text = programName.textContent.toLowerCase();
            if (text.includes(searchLower)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        }
    });
}

function confirmProgramSelection() {
    const selectedPrograms = [];
    const selectedNames = [];
    
    const checkboxes = document.querySelectorAll('#programSelectModal input[name="programs[]"]:checked');
    checkboxes.forEach(checkbox => {
        selectedPrograms.push(checkbox.value);
        const programName = checkbox.closest('.program-item')?.querySelector('.program-name')?.textContent?.trim() || checkbox.dataset.programName || 'Program';
        selectedNames.push(programName);
    });
    
    if (selectedPrograms.length === 0) {
        alert('Please select at least one program.');
        return;
    }
    
    // Store in global variable FIRST (most important - before any DOM updates)
    window.selectedProgramsData = {
        ids: selectedPrograms,
        names: selectedNames
    };
    
    // Generate display text
    let displayText = '';
    if (selectedNames.length === 1) {
        displayText = selectedNames[0];
    } else {
        displayText = `${selectedNames.length} Programs Selected`;
    }
    
    // Update the hidden input field
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    if (selectedProgramsInput) {
        const idsStr = selectedPrograms.join(',');
        selectedProgramsInput.value = idsStr;
        selectedProgramsInput.defaultValue = idsStr; // CRITICAL: Set defaultValue so form.reset() resets TO this value, not empty
        selectedProgramsInput.setAttribute('value', idsStr);
        selectedProgramsInput.setAttribute('data-persistent-value', idsStr);
        console.log('✅ Updated hidden input selectedPrograms with value:', idsStr);
        console.log('✅ Set defaultValue to:', idsStr, '(form.reset() will now reset TO this value)');
    } else {
        console.error('❌ selectedPrograms input field not found!');
    }
    
    // Update the button text to show selected program(s)
    const programSelectText = document.getElementById('programSelectText');
    const programSelectBtn = document.getElementById('programSelectBtn');
    
    if (programSelectText) {
        programSelectText.textContent = displayText;
        // Set persistent attribute
        programSelectText.setAttribute('data-persistent-text', displayText);
        console.log('✅ Updated button text to:', displayText);
    } else {
        console.error('❌ programSelectText element not found!');
    }
    
    // Store selection in data attribute on button (won't be cleared by form.reset())
    if (programSelectBtn) {
        programSelectBtn.setAttribute('data-selected-programs', selectedPrograms.join(','));
        programSelectBtn.setAttribute('data-selected-names', JSON.stringify(selectedNames));
        programSelectBtn.setAttribute('data-display-text', displayText);
        console.log('✅ Stored program selection in button data attributes');
    }
    
    // Make sure it persists even after modal operations
    try {
        localStorage.setItem('lastSelectedPrograms', JSON.stringify({
            ids: selectedPrograms,
            names: selectedNames,
            displayText: displayText,
            timestamp: Date.now()
        }));
    } catch (e) {
        console.warn('Could not save to localStorage:', e);
    }
    
    console.log('✅ Program selection confirmed:', selectedNames);
    console.log('✅ Selected program IDs:', selectedPrograms);
    console.log('✅ Stored in window.selectedProgramsData:', window.selectedProgramsData);
    
    // Start monitoring to ensure it persists
    if (typeof startProgramSelectionMonitor === 'function') {
        startProgramSelectionMonitor();
    }
    
    // Set up MutationObserver to watch for changes to the button text
    setupProgramSelectionObserver();
    
    // Also protect the button text by overriding its textContent setter
    protectButtonText();
    
    closeProgramSelectModal();
}

// Protect the button text from being cleared
function protectButtonText() {
    const programSelectText = document.getElementById('programSelectText');
    if (!programSelectText) return;
    
    // Store the original textContent descriptor
    const descriptor = Object.getOwnPropertyDescriptor(Node.prototype, 'textContent') || 
                      Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'textContent');
    
    if (!descriptor) return;
    
    // Only protect if we have saved data
    if (window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
        // Create a custom setter that prevents clearing if we have saved data
        const savedData = window.selectedProgramsData;
        const savedNames = savedData.names || [];
        const expectedText = savedNames.length === 1 ? savedNames[0] : `${savedNames.length} Programs Selected`;
        
        // Override textContent setter for this specific element
        Object.defineProperty(programSelectText, 'textContent', {
            get: descriptor.get,
            set: function(value) {
                // If trying to set to "Select Program(s)" and we have saved data, restore it instead
                if ((value === 'Select Program(s)' || value === '' || !value) && savedData.ids.length > 0) {
                    console.log('🛡️ Blocked attempt to clear program selection, restoring:', expectedText);
                    descriptor.set.call(this, expectedText);
                    return;
                }
                // Otherwise, allow the change
                descriptor.set.call(this, value);
            },
            configurable: true
        });
        
        console.log('🛡️ Protected button text from being cleared');
    }
}

// Set up MutationObserver to prevent program selection from being cleared
function setupProgramSelectionObserver() {
    const programSelectText = document.getElementById('programSelectText');
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    
    if (!programSelectText || !selectedProgramsInput) {
        return;
    }
    
    // Clear any existing observer
    if (window.programSelectionObserver) {
        window.programSelectionObserver.disconnect();
    }
    
    // Create observer to watch for text changes
    window.programSelectionObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' || mutation.type === 'characterData') {
                const currentText = programSelectText.textContent || '';
                const currentValue = selectedProgramsInput.value || '';
                
                // If text was changed to "Select Program(s)" but we have saved data, restore it
                if (currentText === 'Select Program(s)' || currentText.trim() === '') {
                    if (window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
                        setTimeout(() => {
                            if (typeof window.restoreProgramSelection === 'function') {
                                window.restoreProgramSelection();
                            }
                        }, 0);
                    }
                }
            }
        });
    });
    
    // Observe the text node and the span element
    window.programSelectionObserver.observe(programSelectText, {
        childList: true,
        characterData: true,
        subtree: true
    });
    
    // Also watch the input value
    const inputObserver = new MutationObserver((mutations) => {
        const currentValue = selectedProgramsInput.value || '';
        if (!currentValue && window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
            setTimeout(() => {
                if (typeof window.restoreProgramSelection === 'function') {
                    window.restoreProgramSelection();
                }
            }, 0);
        }
    });
    
    inputObserver.observe(selectedProgramsInput, {
        attributes: true,
        attributeFilter: ['value']
    });
    
    // Store both observers
    window.programSelectionInputObserver = inputObserver;
    
    console.log('✅ Set up MutationObserver to protect program selection');
}

function updateConfirmButton() {
    const checkboxes = document.querySelectorAll('#programSelectModal input[name="programs[]"]');
    const confirmBtn = document.getElementById('confirmProgramBtn');
    
    if (confirmBtn) {
        const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
        confirmBtn.disabled = !hasSelection;
    }
}

// Function to restore program selection from saved data
window.restoreProgramSelection = function() {
    const selectedProgramsInput = document.getElementById('selectedPrograms');
    const programSelectText = document.getElementById('programSelectText');
    const programSelectBtn = document.getElementById('programSelectBtn');
    
    if (!selectedProgramsInput || !programSelectText) {
        return false; // Elements not found
    }
    
    // Check multiple sources for saved data
    let savedIds = [];
    let savedNames = [];
    let savedText = '';
    
    // 1. Check window.selectedProgramsData
    if (window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
        savedIds = window.selectedProgramsData.ids;
        savedNames = window.selectedProgramsData.names || [];
    }
    
    // 2. Check button data attribute
    if (savedIds.length === 0 && programSelectBtn) {
        const btnIds = programSelectBtn.getAttribute('data-selected-programs');
        if (btnIds) {
            savedIds = btnIds.split(',').filter(v => v);
            const btnNames = programSelectBtn.getAttribute('data-selected-names');
            if (btnNames) {
                try {
                    savedNames = JSON.parse(btnNames);
                } catch (e) {}
            }
            savedText = programSelectBtn.getAttribute('data-display-text') || '';
        }
    }
    
    // 3. Check localStorage
    if (savedIds.length === 0) {
        try {
            const saved = localStorage.getItem('lastSelectedPrograms');
            if (saved) {
                const savedData = JSON.parse(saved);
                if (Date.now() - savedData.timestamp < 3600000) {
                    savedIds = savedData.ids || [];
                    savedNames = savedData.names || [];
                    savedText = savedData.displayText || '';
                }
            }
        } catch (e) {}
    }
    
    // 4. Check input's data attribute
    if (savedIds.length === 0 && selectedProgramsInput) {
        const persistentValue = selectedProgramsInput.getAttribute('data-persistent-value');
        if (persistentValue) {
            savedIds = persistentValue.split(',').filter(v => v);
        }
    }
    
    // Restore if we have saved data and UI doesn't show it
    if (savedIds.length > 0) {
        const currentIds = selectedProgramsInput.value || '';
        const currentText = programSelectText.textContent || '';
        
        // Check if restoration is needed
        const needsRestore = !currentIds || 
                            currentIds === '' || 
                            currentIds.split(',').sort().join(',') !== savedIds.sort().join(',') ||
                            currentText === 'Select Program(s)' ||
                            (savedText && currentText !== savedText);
        
        if (needsRestore) {
            // Restore input
            const idsStr = savedIds.join(',');
            selectedProgramsInput.value = idsStr;
            selectedProgramsInput.defaultValue = idsStr;
            selectedProgramsInput.setAttribute('value', idsStr);
            selectedProgramsInput.setAttribute('data-persistent-value', idsStr);
            
            // Restore button text
            if (savedText) {
                programSelectText.textContent = savedText;
            } else if (savedNames.length > 0) {
                programSelectText.textContent = savedNames.length === 1 
                    ? savedNames[0] 
                    : `${savedNames.length} Programs Selected`;
            } else if (savedIds.length > 0) {
                programSelectText.textContent = savedIds.length === 1 
                    ? '1 Program Selected' 
                    : `${savedIds.length} Programs Selected`;
            }
            programSelectText.setAttribute('data-persistent-text', programSelectText.textContent);
            
            // Update button data attributes
            if (programSelectBtn) {
                programSelectBtn.setAttribute('data-selected-programs', idsStr);
                if (savedNames.length > 0) {
                    programSelectBtn.setAttribute('data-selected-names', JSON.stringify(savedNames));
                }
                programSelectBtn.setAttribute('data-display-text', programSelectText.textContent);
            }
            
            // Update window.selectedProgramsData
            window.selectedProgramsData = {
                ids: savedIds,
                names: savedNames
            };
            
            console.log('✅ Restored program selection:', savedIds);
            return true;
        }
    }
    return false;
};

// Monitor and restore program selection if it gets cleared
function startProgramSelectionMonitor() {
    // Clear any existing monitor
    if (window.programSelectionMonitorInterval) {
        clearInterval(window.programSelectionMonitorInterval);
    }
    
    // Monitor every 500ms to check if selection was cleared
    window.programSelectionMonitorInterval = setInterval(() => {
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const programSelectText = document.getElementById('programSelectText');
        
        // If we have saved data but the UI doesn't show it, restore it
        if (window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
            const currentIds = selectedProgramsInput ? selectedProgramsInput.value : '';
            const currentText = programSelectText ? programSelectText.textContent : '';
            
            // Check if selection was cleared
            if (!currentIds || currentIds === '' || currentText === 'Select Program(s)') {
                // Restore from saved data
                if (selectedProgramsInput) {
                    selectedProgramsInput.value = window.selectedProgramsData.ids.join(',');
                }
                
                if (programSelectText && window.selectedProgramsData.names && window.selectedProgramsData.names.length > 0) {
                    const displayText = window.selectedProgramsData.names.length === 1 
                        ? window.selectedProgramsData.names[0] 
                        : `${window.selectedProgramsData.names.length} Programs Selected`;
                    programSelectText.textContent = displayText;
                }
                
                console.log('🔧 Restored program selection from monitor');
            }
        }
    }, 500);
    
    // Stop monitoring when modal closes
    const modal = document.getElementById('addCourseModal');
    if (modal) {
        const observer = new MutationObserver((mutations) => {
            if (modal.style.display === 'none') {
                if (window.programSelectionMonitorInterval) {
                    clearInterval(window.programSelectionMonitorInterval);
                    window.programSelectionMonitorInterval = null;
                }
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    }
}

// Monitor and restore program selection if it gets cleared
function startProgramSelectionMonitor() {
    // Clear any existing monitor
    if (window.programSelectionMonitorInterval) {
        clearInterval(window.programSelectionMonitorInterval);
    }
    
    // Monitor every 500ms to check if selection was cleared
    window.programSelectionMonitorInterval = setInterval(() => {
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const programSelectText = document.getElementById('programSelectText');
        
        // If we have saved data but the UI doesn't show it, restore it
        if (window.selectedProgramsData && window.selectedProgramsData.ids && window.selectedProgramsData.ids.length > 0) {
            const currentIds = selectedProgramsInput ? selectedProgramsInput.value : '';
            const currentText = programSelectText ? programSelectText.textContent : '';
            
            // Check if selection was cleared
            if (!currentIds || currentIds === '' || currentText === 'Select Program(s)') {
                // Restore from saved data
                if (selectedProgramsInput) {
                    selectedProgramsInput.value = window.selectedProgramsData.ids.join(',');
                }
                
                if (programSelectText && window.selectedProgramsData.names && window.selectedProgramsData.names.length > 0) {
                    const displayText = window.selectedProgramsData.names.length === 1 
                        ? window.selectedProgramsData.names[0] 
                        : `${window.selectedProgramsData.names.length} Programs Selected`;
                    programSelectText.textContent = displayText;
                }
                
                console.log('🔧 Restored program selection from monitor');
            }
        }
    }, 500);
    
    // Stop monitoring when modal closes
    const modal = document.getElementById('addCourseModal');
    if (modal) {
        const observer = new MutationObserver((mutations) => {
            if (modal.style.display === 'none') {
                if (window.programSelectionMonitorInterval) {
                    clearInterval(window.programSelectionMonitorInterval);
                    window.programSelectionMonitorInterval = null;
                }
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    }
}

// Success/Error modal functions
function showCourseSuccessModal(courseData) {
    console.log('showCourseSuccessModal called with:', courseData);
    const modal = document.getElementById('courseSuccessModal');
    if (modal) {
        const codeEl = document.getElementById('successCourseCode');
        const nameEl = document.getElementById('successCourseName');
        if (codeEl) codeEl.textContent = courseData.course_code || '';
        if (nameEl) nameEl.textContent = courseData.course_name || '';
        modal.style.display = 'flex';
        modal.style.zIndex = '10002';
        console.log('Success modal displayed');
    } else {
        console.error('courseSuccessModal element not found!');
    }
}

function closeCourseSuccessModal() {
    const modal = document.getElementById('courseSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        closeAddCourseModal();
        if (window.autoRefreshAfterCourseCreation !== false) {
            setTimeout(() => window.location.reload(), 1000);
        }
    }
}

// Close draft saved modal function (accessible globally)
// Override the early definition with full implementation
window.closeCourseDraftSavedModal = function() {
    console.log('🔴 closeCourseDraftSavedModal called (full implementation)');
    const modal = document.getElementById('courseDraftSavedModal');
    if (modal) {
        console.log('✅ Modal found, closing...');
        modal.setAttribute('style', 
            'display: none !important; ' +
            'z-index: 10020 !important; ' +
            'visibility: hidden !important; ' +
            'opacity: 0 !important; ' +
            'pointer-events: none !important;'
        );
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        modal.style.pointerEvents = 'none';
    } else {
        console.error('❌ Modal not found when trying to close');
    }
    
    // Clear the flag that prevents main modal from closing
    window._draftSuccessModalOpen = false;
    
    // Clear ALL draft-related state before closing the main modal
    window.draftToResume = null;
    window.loadedDraftData = null;
    if (window.courseSelectionContext) {
        delete window.courseSelectionContext.isResumingDraft;
        delete window.courseSelectionContext.proposalId;
    }
    console.log('📝 Cleared all draft-related state after saving draft');
    
    // Close the main course modal after closing the success modal
    if (typeof closeAddCourseModal === 'function') {
        closeAddCourseModal();
    }
    
    // Refresh the course proposals section if on dashboard
    if (typeof initializeCourseProposals === 'function') {
        console.log('Refreshing course proposals after draft save...');
        initializeCourseProposals();
    }
    
    if (window.autoRefreshAfterCourseCreation !== false) {
        setTimeout(() => window.location.reload(), 1000);
    }
};

// Also define as regular function for compatibility
function closeCourseDraftSavedModal() {
    window.closeCourseDraftSavedModal();
}

// Override placeholder with full implementation - this MUST override the placeholder
window.showCourseDraftSavedModal = function(courseData) {
    console.log('💾 Showing draft saved modal (FULL IMPLEMENTATION - placeholder overridden)...', courseData);
    
    // Function to actually show the modal
    function showModal() {
        let modal = document.getElementById('courseDraftSavedModal');
        
        if (!modal) {
            console.error('❌ Draft saved modal not found in DOM');
            // Try waiting a bit more and retry
            setTimeout(function() {
                modal = document.getElementById('courseDraftSavedModal');
                if (!modal) {
                    console.error('❌ Modal still not found after retry');
                    console.error('All modals in document:', Array.from(document.querySelectorAll('.modal')).map(m => m.id));
                    // Don't show alert - just log and return
                    return;
                }
                showModalElement(modal);
            }, 200);
            return;
        }
        
        showModalElement(modal);
    }
    
    function showModalElement(modal) {
        try {
            // Close any confirmation modals first
            if (typeof window.closeCloseConfirmationModal === 'function') {
                window.closeCloseConfirmationModal();
            }
            
            // Ensure main modal stays open - prevent it from closing
            const mainModal = document.getElementById('addCourseModal');
            if (mainModal) {
                // Keep main modal open and visible, just lower its z-index
                if (mainModal.style.display === 'none') {
                    mainModal.style.display = 'block';
                }
                mainModal.style.zIndex = '10010';
                // Prevent main modal from closing while success modal is shown
                window._draftSuccessModalOpen = true;
            }
            
            // Update course details
            const courseCodeEl = document.getElementById('draftCourseCode');
            const courseNameEl = document.getElementById('draftCourseName');
            
            if (courseCodeEl) {
                courseCodeEl.textContent = (courseData && courseData.course_code) ? courseData.course_code : '—';
            }
            if (courseNameEl) {
                courseNameEl.textContent = (courseData && courseData.course_name) ? courseData.course_name : '—';
            }
            
            // Ensure modal is directly in body if needed
            if (modal.parentElement && modal.parentElement !== document.body) {
                const parent = modal.parentElement;
                const parentStyle = window.getComputedStyle(parent);
                if (parentStyle.display === 'none' || parentStyle.visibility === 'hidden') {
                    console.log('Moving modal to body');
                    document.body.appendChild(modal);
                }
            }
            
            // Show the modal with maximum priority - do this immediately
            modal.style.cssText = '';
            modal.style.setProperty('display', 'flex', 'important');
            modal.style.setProperty('z-index', '10020', 'important');
            modal.style.setProperty('visibility', 'visible', 'important');
            modal.style.setProperty('opacity', '1', 'important');
            modal.style.setProperty('pointer-events', 'auto', 'important');
            modal.style.setProperty('position', 'fixed', 'important');
            modal.style.setProperty('left', '0', 'important');
            modal.style.setProperty('top', '0', 'important');
            modal.style.setProperty('width', '100%', 'important');
            modal.style.setProperty('height', '100%', 'important');
            modal.style.setProperty('background-color', 'rgba(0,0,0,0.6)', 'important');
            
            // Also set via setAttribute for maximum priority
            modal.setAttribute('style', 
                'display: flex !important; ' +
                'z-index: 10020 !important; ' +
                'visibility: visible !important; ' +
                'opacity: 1 !important; ' +
                'pointer-events: auto !important; ' +
                'position: fixed !important; ' +
                'left: 0 !important; ' +
                'top: 0 !important; ' +
                'width: 100% !important; ' +
                'height: 100% !important; ' +
                'background-color: rgba(0,0,0,0.6) !important;'
            );
            
            // Force a reflow to ensure styles are applied
            void modal.offsetHeight;
            
            console.log('✅ Modal display set to:', modal.style.display);
            console.log('✅ Modal z-index:', modal.style.zIndex);
            console.log('✅ Modal visibility:', modal.style.visibility);
            
            // Verify visibility immediately and retry if needed
            const computed = window.getComputedStyle(modal);
            if (computed.display === 'none' || computed.visibility === 'hidden') {
                console.warn('⚠️ Modal not visible on first try, forcing display...');
                modal.style.display = 'flex';
                modal.style.visibility = 'visible';
                modal.style.zIndex = '10020';
                modal.style.opacity = '1';
            } else {
                console.log('✅ Draft saved modal is visible');
            }
            
            // Double-check after a brief delay
            setTimeout(function() {
                const computed2 = window.getComputedStyle(modal);
                if (computed2.display === 'none' || computed2.visibility === 'hidden') {
                    console.warn('⚠️ Modal disappeared, restoring...');
                    modal.style.display = 'flex';
                    modal.style.visibility = 'visible';
                    modal.style.zIndex = '10020';
                    modal.style.opacity = '1';
                } else {
                    console.log('✅ Draft saved modal confirmed visible');
                }
            }, 50);
            
            // Ensure modal content can receive clicks
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.pointerEvents = 'auto';
                modalContent.style.position = 'relative';
                modalContent.style.zIndex = '10021';
            }
            
            // Attach event listeners to buttons to ensure they work
            const closeBtn = modal.querySelector('.close');
            const okBtn = modal.querySelector('.create-btn');
            
            if (closeBtn) {
                // Remove existing listeners by cloning
                const newCloseBtn = closeBtn.cloneNode(true);
                closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
                
                // Add click event listener
                newCloseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('✅ Close button clicked via event listener');
                    if (typeof closeCourseDraftSavedModal === 'function') {
                        closeCourseDraftSavedModal();
                    } else if (typeof window.closeCourseDraftSavedModal === 'function') {
                        window.closeCourseDraftSavedModal();
                    }
                });
                console.log('✅ Close button event listener attached');
            }
            
            if (okBtn) {
                // Remove existing listeners by cloning
                const newOkBtn = okBtn.cloneNode(true);
                okBtn.parentNode.replaceChild(newOkBtn, okBtn);
                
                // Add click event listener
                newOkBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('✅ OK button clicked via event listener');
                    if (typeof closeCourseDraftSavedModal === 'function') {
                        closeCourseDraftSavedModal();
                    } else if (typeof window.closeCourseDraftSavedModal === 'function') {
                        window.closeCourseDraftSavedModal();
                    }
                });
                console.log('✅ OK button event listener attached');
            }
            
        } catch (error) {
            console.error('❌ Error in showModalElement:', error);
            console.error('Error details:', error.message, error.stack);
        }
    }
    
    // Start showing the modal immediately
    showModal();
    
    // Also try again after a short delay in case DOM wasn't ready
    setTimeout(showModal, 100);
};

// Confirm function is loaded
console.log('✅ showCourseDraftSavedModal function defined:', typeof window.showCourseDraftSavedModal);
console.log('✅ closeCourseDraftSavedModal function defined:', typeof window.closeCourseDraftSavedModal);

function showCourseErrorModal(errorMessage) {
    console.log('showCourseErrorModal called with:', errorMessage);
    const modal = document.getElementById('courseErrorModal');
    if (modal) {
        const errorEl = document.getElementById('errorMessage');
        if (errorEl) errorEl.textContent = errorMessage;
        modal.style.display = 'flex';
        modal.style.zIndex = '10002';
        console.log('Error modal displayed');
    } else {
        console.error('courseErrorModal element not found!');
    }
}

function closeCourseErrorModal() {
    document.getElementById('courseErrorModal').style.display = 'none';
}

function retryCourseCreation() {
    closeCourseErrorModal();
}

// Validation Error Modal Functions moved to top of file (line ~42) - removed duplicate here

// Add to List Success/Error Modal Functions
function showAddToListSuccessModal(courseCode, courseName) {
    const modal = document.getElementById('addToListSuccessModal');
    if (modal) {
        document.getElementById('addToListSuccessCourseCode').textContent = courseCode || '';
        document.getElementById('addToListSuccessCourseName').textContent = courseName || '';
        modal.style.display = 'flex';
    }
}

function closeAddToListSuccessModal() {
    const modal = document.getElementById('addToListSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function showAddToListErrorModal(errorMessage) {
    const modal = document.getElementById('addToListErrorModal');
    if (modal) {
        document.getElementById('addToListErrorMessage').textContent = errorMessage || 'An error occurred while adding the course. Please try again.';
        modal.style.display = 'flex';
    }
}

function closeAddToListErrorModal() {
    const modal = document.getElementById('addToListErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Event listeners
    document.addEventListener('change', function(e) {
    if (e.target.matches('#programSelectModal input[name="programs[]"]')) {
        updateConfirmButton();
    }
});

document.getElementById('programSearch')?.addEventListener('input', function(e) {
    filterPrograms(e.target.value);
});

console.log('✅ Multi-step Course Proposal Form Loaded');

// The onclick handlers in the HTML should work - no need for additional event listeners
</script> 
