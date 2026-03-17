<?php
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    // Configure session before starting
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Debug: log session information
file_put_contents('../login_debug.txt', 'department-dean/content.php - session_id=' . session_id() . ' dean_logged_in=' . ($_SESSION['dean_logged_in'] ?? 'NOT_SET') . ' is_authenticated=' . ($_SESSION['is_authenticated'] ?? 'NOT_SET') . PHP_EOL, FILE_APPEND);

// Simplified authentication check - focus on the most reliable method
$isAuthenticated = false;

// Primary check: dean_logged_in flag
if (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    $isAuthenticated = true;
    file_put_contents('../login_debug.txt', 'department-dean/content.php - dean_logged_in found' . PHP_EOL, FILE_APPEND);
}
// Secondary check: selected_role
elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role']['type'] === 'dean') {
    $isAuthenticated = true;
    $_SESSION['dean_logged_in'] = true; // Set the flag for future requests
    file_put_contents('../login_debug.txt', 'department-dean/content.php - recovered from selected_role' . PHP_EOL, FILE_APPEND);
}
// Tertiary check: user_id and username exist (basic session validation)
elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // For now, just check if basic session data exists
    $isAuthenticated = true;
    $_SESSION['dean_logged_in'] = true; // Assume dean if we have basic session
    file_put_contents('../login_debug.txt', 'department-dean/content.php - recovered from basic session data' . PHP_EOL, FILE_APPEND);
}

if (!$isAuthenticated) {
    file_put_contents('../login_debug.txt', 'department-dean/content.php - REDIRECTING TO LOGIN - no valid session found' . PHP_EOL, FILE_APPEND);
    header("Location: ../user_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Department Dean - Content</title>

<!-- Use shared styles from super_admin-mis -->
<link rel="stylesheet" href="../super_admin-mis/styles/global.css">
<link rel="stylesheet" href="../super_admin-mis/styles/modals.css">
<link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
<link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
<link rel="stylesheet" href="../super_admin-mis/styles/school-calendar.css">
<link rel="stylesheet" href="../super_admin-mis/styles/settings.css">
<link rel="stylesheet" href="./styles/program-management.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="./styles/course-modal.css?v=<?php echo time(); ?>&r=<?php echo rand(1000, 9999); ?>">
<link rel="stylesheet" href="./styles/notifications.css?v=<?php echo time(); ?>">

<!-- FORCE FACULTY HEADER LEFT ALIGNMENT - LOADS LAST -->
<style>
/* All conflicting CSS rules removed to allow individual program table to work properly */
</style>

<script>
/* All conflicting JavaScript removed to allow individual program table to work properly */
</script>

</head>
<body>

<?php include './modals/add_faculty_modal.php'; ?>
<?php include './modals/add_course_modal.php'; ?>
<?php include './modals/add_program_modal.php'; ?>
<?php include './modals/switch_role_modal.php'; ?>

<!-- Course Selection Modal -->
<div id="courseSelectionModal" class="modal" style="display: none; z-index: 10003;">
    <div class="modal-content" style="max-width: 500px; width: 85%; max-height: 85vh;">
        <div class="modal-header">
            <h2>Select Course Type</h2>
            <span class="close" onclick="closeCourseSelectionModal()">&times;</span>
        </div>
        <div class="modal-body" style="padding: 0px;">
            <!-- Course Type Options -->
            <div style="display: flex; flex-direction: column; gap: 15px; padding: 0px; margin-top: 10px;">
                <button type="button" class="course-type-option" onclick="selectCourseType('proposal')">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px;">📝</div>
                        <div style="text-align: left; flex: 1;">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #333;">New Course Proposal</h3>
                            <p style="margin: 0; font-size: 14px; color: #666;">Create a new course proposal to submit to Quality Assurance for review</p>
                        </div>
                    </div>
                </button>
                
                <button type="button" class="course-type-option" onclick="selectCourseType('cross-department')">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px;">🔗</div>
                        <div style="text-align: left; flex: 1;">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #333;">Add Cross-Department Courses</h3>
                            <p style="margin: 0; font-size: 14px; color: #666;">Add existing courses from other departments to your program</p>
                        </div>
                    </div>
                </button>
                
                <button type="button" class="course-type-option" onclick="selectCourseType('course-revision')">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px;">✏️</div>
                        <div style="text-align: left; flex: 1;">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #333;">Request Course Revision</h3>
                            <p style="margin: 0; font-size: 14px; color: #666;">Request revisions to existing courses, update content, or modify course requirements</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.course-type-option {
    width: 100%;
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
}

.course-type-option:hover {
    border-color: #1976d2;
    background: #f0f7ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.15);
}

.course-type-option:active {
    transform: translateY(0);
}

.program-dropdown .program-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.program-dropdown .program-item:last-child {
    border-bottom: none;
}

.program-dropdown .program-item:hover {
    background-color: #f0f7ff;
}

.program-dropdown .program-item.selected {
    background-color: #e3f2fd;
    font-weight: 600;
}

.course-list-item {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.course-list-item:hover {
    border-color: #1976d2;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.1);
}

.course-list-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.course-list-item-code {
    font-weight: 600;
    font-size: 16px;
    color: #1976d2;
}

.course-list-item-remove {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.course-list-item-remove:hover {
    background: #fee;
    color: #c82333;
}

.course-list-item-title {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.course-list-item-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #999;
}

.submit-courses-btn:hover:not(:disabled) {
    background: #1565c0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
}

.submit-courses-btn:disabled {
    background: #ccc !important;
    cursor: not-allowed;
    opacity: 0.6;
}

.required {
    color: #dc3545;
}

/* Course selection modal - single column layout */
#courseSelectionModal .modal-body {
    display: block !important;
    overflow: auto !important;
}

#courseSelectionModal .modal-content {
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Ensure scrollbar is visible on course list */
#courseSelectionCourseList {
    overflow-y: scroll !important;
    overflow-x: hidden !important;
}

/* Custom scrollbar styling for course list */
#courseSelectionCourseList::-webkit-scrollbar {
    width: 8px;
}

#courseSelectionCourseList::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#courseSelectionCourseList::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#courseSelectionCourseList::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
// Course Selection Modal Functions
let courseSelectionPrograms = [];
let courseSelectionCourses = [];

function openCourseSelectionModal() {
    const modal = document.getElementById('courseSelectionModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Load programs and academic years
        loadCourseSelectionPrograms();
        loadCourseSelectionAcademicYears();
        
        // Load courses for the selected program
        // Use a longer delay to ensure modal is fully rendered
        setTimeout(() => {
            loadCourseSelectionCourses();
        }, 200);
    }
}

function closeCourseSelectionModal() {
    // Simply close the modal since course list was removed
    const modal = document.getElementById('courseSelectionModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Close program dropdown if it exists
        const dropdown = document.getElementById('courseSelectionProgramDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }
}

// Load programs for the department
async function loadCourseSelectionPrograms() {
    try {
        const response = await fetch('api/get_programs.php');
        const data = await response.json();
        
        console.log('Programs API response:', data);
        
        const select = document.getElementById('courseSelectionProgram');
        if (!select) return;
        
        if (data.success && data.programs) {
            courseSelectionPrograms = data.programs;
            console.log('Loaded programs:', courseSelectionPrograms.length);
            
            // Clear existing options except the first one
            select.innerHTML = '<option value="">Select Program</option>';
            
            // Add programs to dropdown
            courseSelectionPrograms.forEach(program => {
                const option = document.createElement('option');
                option.value = program.id;
                option.textContent = program.program_name + (program.program_code ? ` (${program.program_code})` : '');
                option.setAttribute('data-program-name', program.program_name);
                if (program.program_code) {
                    option.setAttribute('data-program-code', program.program_code);
                }
                select.appendChild(option);
            });
        } else {
            console.error('Failed to load programs:', data.message || 'Unknown error');
            select.innerHTML = '<option value="">Failed to load programs</option>';
        }
    } catch (error) {
        console.error('Error loading programs:', error);
        const select = document.getElementById('courseSelectionProgram');
        if (select) {
            select.innerHTML = '<option value="">Error loading programs</option>';
        }
    }
}

// Handle program selection change
function handleCourseSelectionProgramChange() {
    const select = document.getElementById('courseSelectionProgram');
    const hiddenInput = document.getElementById('courseSelectionProgramId');
    
    if (select && hiddenInput) {
        const selectedValue = select.value;
        hiddenInput.value = selectedValue;
    }
}

// Load academic years
async function loadCourseSelectionAcademicYears() {
    try {
        const response = await fetch('api/get_school_years.php');
        const data = await response.json();
        
        const select = document.getElementById('courseSelectionYear');
        if (select && data.success && data.school_years) {
            select.innerHTML = '<option value="">Select Academic Year</option>';
            data.school_years.forEach(year => {
                const option = document.createElement('option');
                option.value = year.id;
                option.textContent = year.school_year || `A.Y. ${year.year_start}-${year.year_end}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading academic years:', error);
    }
}

// Load courses from sessionStorage
function loadCourseSelectionCourses() {
    try {
        // Load from sessionStorage (keeping for compatibility, but not rendering since list was removed)
        const stored = sessionStorage.getItem('courseSelectionCourses');
        if (stored) {
            courseSelectionCourses = JSON.parse(stored);
            console.log('Loaded courses from sessionStorage:', courseSelectionCourses.length);
        } else {
            courseSelectionCourses = [];
            console.log('No courses in sessionStorage');
        }
        // No need to render since the course list element was removed from the modal
    } catch (error) {
        console.error('Error loading courses:', error);
        courseSelectionCourses = [];
    }
}

// Save courses to sessionStorage
function saveCourseSelectionCourses() {
    try {
        sessionStorage.setItem('courseSelectionCourses', JSON.stringify(courseSelectionCourses));
        renderCourseSelectionCourses();
    } catch (error) {
        console.error('Error saving courses:', error);
    }
}

// Add course to list
function addCourseToSelectionList(courseData) {
    // Use context values if available, otherwise fall back to form values
    let programId, programName, programCode, term, year, yearLevel, courseType;
    
    if (window.courseSelectionContext) {
        programId = window.courseSelectionContext.programId;
        programName = window.courseSelectionContext.programName;
        programCode = window.courseSelectionContext.programCode || '';
        term = window.courseSelectionContext.term;
        year = window.courseSelectionContext.academicYear;
        yearLevel = window.courseSelectionContext.yearLevel;
        courseType = window.courseSelectionContext.courseType;
    } else {
        // Fallback to form values
        const programSelect = document.getElementById('courseSelectionProgram');
        programId = document.getElementById('courseSelectionProgramId')?.value || programSelect?.value || '';
        const selectedOption = programSelect?.options[programSelect?.selectedIndex];
        const programText = selectedOption?.textContent || '';
        programName = selectedOption?.getAttribute('data-program-name') || programText;
        // Extract program code from text (format: "Program Name (CODE)" or just "CODE")
        programCode = selectedOption?.getAttribute('data-program-code') || 
                     (programText.includes('(') ? programText.match(/\(([^)]+)\)/)?.[1] || '' : '') || '';
        term = document.getElementById('courseSelectionTerm')?.value || '';
        year = document.getElementById('courseSelectionYear')?.value || '';
        yearLevel = document.getElementById('courseSelectionYearLevel')?.value || '';
        courseType = 'proposal'; // Default to proposal if no context
    }
    
    // Format year level
    let yearLevelFormatted = yearLevel;
    if (yearLevel === '1') yearLevelFormatted = '1st Year';
    else if (yearLevel === '2') yearLevelFormatted = '2nd Year';
    else if (yearLevel === '3') yearLevelFormatted = '3rd Year';
    else if (yearLevel === '4') yearLevelFormatted = '4th Year';
    
    const course = {
        ...courseData,
        program_id: programId,
        program_name: programName,
        program_code: programCode,
        term: term,
        academic_year: year,
        year_level: yearLevel,
        year_level_formatted: yearLevelFormatted,
        course_type: courseType, // Store course type
        created_at: new Date().toISOString()
    };
    
    courseSelectionCourses.push(course);
    saveCourseSelectionCourses();
}

// Remove course from list
function removeCourseFromSelectionList(index) {
    courseSelectionCourses.splice(index, 1);
    saveCourseSelectionCourses();
}

// Group courses by program, term, year, and year level
function groupCoursesByProgramInfo(courses) {
    const groups = {};
    
    courses.forEach((course, index) => {
        // Create a unique key for grouping
        const programId = course.program_id || '';
        const term = course.term || '';
        const academicYear = course.academic_year || '';
        const yearLevel = course.year_level || '';
        const courseType = course.course_type || 'proposal';
        
        const groupKey = `${programId}_${term}_${academicYear}_${yearLevel}_${courseType}`;
        
        if (!groups[groupKey]) {
            groups[groupKey] = {
                id: 'group_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                programId: programId,
                programName: course.program_name || '',
                programCode: course.program_code || '',
                academicTerm: term,
                academicYear: academicYear,
                yearLevel: yearLevel,
                courseType: courseType,
                courses: [],
                courseIndices: [], // Store original indices for removal
                created_at: course.created_at || new Date().toISOString()
            };
        }
        
        // Add course to group
        groups[groupKey].courses.push(course);
        groups[groupKey].courseIndices.push(index);
    });
    
    return Object.values(groups);
}

// Render courses list - now grouped by program info
function renderCourseSelectionCourses() {
    // This function is no longer needed since we removed the course list from the modal
    // Keeping it as a no-op to prevent errors if it's called elsewhere
    return;
}

// Remove a single course from a group
function removeCourseFromGroup(groupId, courseCode) {
    // Find the course in courseSelectionCourses and remove it
    const index = courseSelectionCourses.findIndex(c => c.course_code === courseCode);
    if (index !== -1) {
        courseSelectionCourses.splice(index, 1);
        saveCourseSelectionCourses();
    }
}

// Remove entire course group
function removeCourseGroup(groupId) {
    // Find all courses in this group and remove them
    const groupedCourses = groupCoursesByProgramInfo(courseSelectionCourses);
    const group = groupedCourses.find(g => g.id === groupId);
    
    if (group && group.courseIndices) {
        // Remove courses in reverse order to maintain indices
        const indicesToRemove = [...group.courseIndices].sort((a, b) => b - a);
        indicesToRemove.forEach(index => {
            courseSelectionCourses.splice(index, 1);
        });
        saveCourseSelectionCourses();
    }
}

// Submit all courses to Quality Assurance
async function submitCoursesToQA() {
    if (courseSelectionCourses.length === 0) {
        showQASubmitErrorModal('No courses to submit');
        return;
    }
    
    if (!confirm(`Submit ${courseSelectionCourses.length} course(s) to Quality Assurance?`)) {
        return;
    }
    
    try {
        const response = await fetch('api/submit_courses_to_qa.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                courses: courseSelectionCourses
            })
        });
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response text first to check if it's valid JSON
        const responseText = await response.text();
        let data;
        
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON response:', responseText);
            showQASubmitErrorModal('Invalid response from server. Please try again.');
            return;
        }
        
        if (data.success) {
            showQASubmitSuccessModal(data.submitted_count || courseSelectionCourses.length);
            courseSelectionCourses = [];
            saveCourseSelectionCourses();
            closeCourseSelectionModal();
        } else {
            showQASubmitErrorModal(data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Error submitting courses:', error);
        showQASubmitErrorModal('Error submitting courses. Please try again.');
    }
}

// Show success modal for QA submission
function showQASubmitSuccessModal(count) {
    const modal = document.getElementById('qaSubmitSuccessModal');
    if (modal) {
        const countElement = document.getElementById('qaSubmitSuccessCount');
        if (countElement) {
            countElement.textContent = count;
        }
        modal.style.display = 'flex';
    } else {
        // Fallback to alert if modal not found
        alert(`Successfully submitted ${count} course(s) to Quality Assurance!`);
    }
}

// Close success modal
function closeQASubmitSuccessModal() {
    const modal = document.getElementById('qaSubmitSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Show error modal for QA submission
function showQASubmitErrorModal(message) {
    const modal = document.getElementById('qaSubmitErrorModal');
    if (modal) {
        const messageElement = document.getElementById('qaSubmitErrorMessage');
        if (messageElement) {
            messageElement.textContent = message || 'An error occurred while submitting courses.';
        }
        modal.style.display = 'flex';
    } else {
        // Fallback to alert if modal not found
        alert('Error submitting courses: ' + (message || 'Unknown error'));
    }
}

// Close error modal
function closeQASubmitErrorModal() {
    const modal = document.getElementById('qaSubmitErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Save courses as draft (no longer used since course list was removed, but keeping for compatibility)
async function saveCoursesAsDraft() {
    // This function is no longer needed since we removed the course list from the modal
    console.log('saveCoursesAsDraft called but course list was removed from modal');
    return;
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function selectCourseType(type) {
    // Clear any previous draft resume data when starting a new course proposal
    window.draftToResume = null;
    
    // Store course type in context (Program, Term, Year, Year Level will be set in the Course Information step)
    window.courseSelectionContext = {
        courseType: type // Store the course type
    };
    
    // Explicitly ensure isResumingDraft is not set for new proposals (even though we created a new object)
    if (window.courseSelectionContext.isResumingDraft) {
        delete window.courseSelectionContext.isResumingDraft;
    }
    
    // Double-check: Clear draft resume data again right before opening modal to prevent any race conditions
    window.draftToResume = null;
    
    // Close the course selection modal
    closeCourseSelectionModal();
    
    if (type === 'proposal') {
        // Open the existing course proposal modal with program check
        console.log('=== selectCourseType: proposal ===');
        console.log('Context BEFORE opening modal:', window.courseSelectionContext);
        console.log('Context type:', typeof window.courseSelectionContext);
        console.log('Context keys:', window.courseSelectionContext ? Object.keys(window.courseSelectionContext) : 'null');
        
        // Double-check context is set
        if (!window.courseSelectionContext) {
            console.error('ERROR: courseSelectionContext is not set!');
        }
        
        openCourseProposalModal();
        
        // Check context AFTER opening
        setTimeout(() => {
            console.log('Context AFTER opening modal:', window.courseSelectionContext);
        }, 100);
    } else if (type === 'cross-department') {
        // Open cross-department courses modal (to be implemented)
        alert('Cross-Department Courses feature is coming soon!');
        // TODO: Implement cross-department courses modal
        // openCrossDepartmentCoursesModal();
    } else if (type === 'course-revision') {
        // Open course revision modal (to be implemented)
        alert('Course Revision feature is coming soon!');
        // TODO: Implement course revision modal
        // openCourseRevisionModal();
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Program select dropdown
    const programSelect = document.getElementById('courseSelectionProgram');
    if (programSelect) {
        programSelect.addEventListener('change', handleCourseSelectionProgramChange);
    }
    
    // Submit button
    const submitBtn = document.getElementById('submitCoursesToQA');
    if (submitBtn) {
        submitBtn.addEventListener('click', submitCoursesToQA);
    }
});

function openCourseProposalModal() {
    console.log('openCourseProposalModal called, context:', window.courseSelectionContext);
    
    // Clear draft resume data unless we're explicitly resuming a draft
    const isResumingDraft = window.courseSelectionContext && window.courseSelectionContext.isResumingDraft === true;
    if (!isResumingDraft) {
        window.draftToResume = null;
        if (window.courseSelectionContext) {
            delete window.courseSelectionContext.isResumingDraft;
        }
        console.log('📝 Cleared draft resume data in openCourseProposalModal - opening fresh modal');
    }
    
    // Check if we're on the course-details page - just open the course modal directly
    const currentPage = window.location.search;
    if (currentPage.includes('course-details')) {
        if (typeof openAddCourseModal === 'function') {
            console.log('Calling openAddCourseModal from course-details page');
            openAddCourseModal();
        }
        return;
    }
    
    // Check if we're on the all-courses page and use its working function
    if (typeof simpleModalTest === 'function') {
        simpleModalTest();
        return;
    }
    
    // If we have the hasPrograms variable, use it
    if (typeof hasPrograms !== 'undefined') {
        if (!hasPrograms) {
            if (typeof showNoProgramsModal === 'function') {
                showNoProgramsModal();
                return;
            }
        }
        // If programs exist, open the course modal
        if (typeof openAddCourseModal === 'function') {
            openAddCourseModal();
            // If there's a program code stored, pre-select it
            if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                setTimeout(() => {
                    preSelectProgram(window.currentProgramCode);
                }, 500);
            }
        }
    } else {
        // Make an AJAX call to check programs
        fetch('check_programs.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    console.error('Program check failed:', data.message);
                    // If the check failed, try to open the modal anyway
                    if (typeof openAddCourseModal === 'function') {
                        openAddCourseModal();
                        // If there's a program code stored, pre-select it
                        if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                            setTimeout(() => {
                                preSelectProgram(window.currentProgramCode);
                            }, 500);
                        }
                    }
                    return;
                }
                if (!data.hasPrograms) {
                    if (typeof showNoProgramsModal === 'function') {
                        showNoProgramsModal();
                    }
                    return;
                }
                // If programs exist, open the course modal
                if (typeof openAddCourseModal === 'function') {
                    openAddCourseModal();
                    // If there's a program code stored, pre-select it
                    if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                        setTimeout(() => {
                            preSelectProgram(window.currentProgramCode);
                        }, 500);
                    }
                }
            })
            .catch(error => {
                console.error('Error checking programs:', error);
                // Fallback: try to open the modal anyway
                if (typeof openAddCourseModal === 'function') {
                    openAddCourseModal();
                    // If there's a program code stored, pre-select it
                    if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                        setTimeout(() => {
                            preSelectProgram(window.currentProgramCode);
                        }, 500);
                    }
                }
            });
    }
}

// Make functions globally available immediately
window.openCourseSelectionModal = openCourseSelectionModal;
window.closeCourseSelectionModal = closeCourseSelectionModal;
window.selectCourseType = selectCourseType;
window.openCourseProposalModal = openCourseProposalModal;
window.addCourseToSelectionList = addCourseToSelectionList;
window.removeCourseFromSelectionList = removeCourseFromSelectionList;
window.removeCourseFromGroup = removeCourseFromGroup;
window.removeCourseGroup = removeCourseGroup;
window.handleCourseSelectionProgramChange = handleCourseSelectionProgramChange;
window.renderCourseSelectionCourses = renderCourseSelectionCourses;
window.loadCourseSelectionCourses = loadCourseSelectionCourses;

// Override checkProgramsAndOpenCourseModal after all scripts load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for fix_js_errors.js to load
    setTimeout(() => {
        if (typeof window.checkProgramsAndOpenCourseModal === 'function') {
            const originalCheckProgramsAndOpenCourseModal = window.checkProgramsAndOpenCourseModal;
            window.checkProgramsAndOpenCourseModal = function() {
                console.log('🔧 checkProgramsAndOpenCourseModal called - showing selection modal');
                
                // Clear any draft resume data when opening a new course proposal
                window.draftToResume = null;
                if (window.courseSelectionContext) {
                    delete window.courseSelectionContext.isResumingDraft;
                }
                console.log('📝 Cleared draft resume data - opening new course proposal');
                
                // Show selection modal instead of directly opening course modal
                if (typeof openCourseSelectionModal === 'function') {
                    openCourseSelectionModal();
                } else {
                    // Fallback to original if selection modal function not available
                    originalCheckProgramsAndOpenCourseModal();
                }
            };
            console.log('✅ Overrode checkProgramsAndOpenCourseModal to show selection modal');
        }
    }, 200);
});
</script>
<?php include './modals/add_book_reference_modal.php'; ?>

<div class="top-navbar">
  <div class="top-navbar-content">
    <div class="hamburger" onclick="toggleSidebar()" role="button" tabindex="0" aria-label="Toggle sidebar">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <img src="../src/assets/images/ASCOM_Monitoring_System.png" alt="Logo" class="logo-img" />
    <div class="search-bar">
      <img src="../src/assets/icons/search-icon.png" alt="Search Icon" />
      <input type="text" placeholder="Search Here..." />
    </div>
    <div class="chats-icon">
      <img src="../src/assets/icons/chats-icon.png" alt="Chats" />
      <div class="chat-count">0</div>
      <div class="chat-dropdown" id="chatsDropdown">
        <h3>Chats</h3>
        <div class="chats-empty">No new messages</div>
      </div>
    </div>
    <div class="notification-icon">
      <img src="../src/assets/icons/notifications-icon.png" alt="Notifications" />
      <div class="notification-count">0</div>
      <div class="notification-dropdown" id="notificationDropdown">
        <h3>Notifications</h3>
        <div class="notification-empty">No new notifications</div>
      </div>
    </div>
  </div>
</div>

<nav class="side-navbar" id="sidebar" aria-label="Sidebar navigation">
  <div class="nav-buttons">
    <a href="#" class="nav-button new-account-button" id="newCourseBtn" onclick="checkProgramsAndOpenCourseModal(); return false;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/add-icon.png" alt="Add Icon" class="nav-icon" />
      </span>
      <span>Add Course</span>
    </a>

    <?php $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>

    <a href="content.php?page=dashboard" class="nav-button hoverable <?php if ($currentPage == 'dashboard' || $currentPage == 'all-courses' || $currentPage == 'course-details' || $currentPage == 'program-courses' || $currentPage == 'reference-requests' || $currentPage == 'course-proposals') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/dashboard-icon.png" alt="Dashboard Icon" class="nav-icon" />
      </span>
      <span>Dashboard</span>
    </a>



    <a href="content.php?page=faculty-management" class="nav-button hoverable <?php if ($currentPage == 'academic-management' || $currentPage == 'faculty-management' || $currentPage == 'faculty-details') echo 'active'; ?>" style="height: 76px;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/faculty-icon.png" alt="Faculty Management Icon" class="nav-icon" />
      </span>
      <span style="line-height: 1.2;">
        Faculty<br />Management
      </span>
    </a>

    <a href="content.php?page=school-calendar" class="nav-button hoverable <?php if ($currentPage == 'school-calendar') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/calendar-icon.png" alt="Calendar Icon" class="nav-icon" />
      </span>
      <span>School Calendar</span>
    </a>

    <a href="content.php?page=settings" class="nav-button hoverable <?php if ($currentPage == 'settings') echo 'active'; ?>">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/settings-icon.png" alt="Settings Icon" class="nav-icon" />
      </span>
      <span>Settings</span>
    </a>
  </div>

  <div class="bottom-nav-buttons">
    <a href="#" class="nav-button switch-role-button" onclick="openSwitchRoleModal(); return false;">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/switch.png" class="nav-icon" />
      </span>
      <span>Switch Role</span>
    </a>

    <a href="./logout.php" class="nav-button logout-button">
      <span class="nav-icon-wrapper">
        <img src="../src/assets/icons/logout-icon.png" class="nav-icon" />
      </span>
      <span>Log Out</span>
    </a>
  </div>
</nav>

<div class="content-wrapper">
  <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    
    switch ($page) {
      case 'faculty-management':
      case 'academic-management':
        include './academic_management-content/academic-management.php';
        break;
      case 'faculty-details':
        include './faculty_details-content/faculty-details.php';
        break;
      case 'school-calendar':
        include './school_calendar-content/school-calendar.php';
        break;
      case 'settings':
        include './settings-content/settings.php';
        break;
      case 'all-courses':
        include './all_courses-content/all-courses.php';
        include './modals/edit_course_modal.php';
        break;
      case 'course-details':
        include './all_courses-content/course-details.php';
        include './modals/edit_course_modal.php';
        include './modals/add_book_reference_modal.php';
        break;
      case 'program-courses':
        include './program_courses-content/program-courses.php';
        include './modals/edit_course_modal.php';
        // add_course_modal.php is already included at the top level (line 82)
        include './modals/add_book_reference_modal.php';
        break;
      case 'reference-requests':
        include './reference-requests-content/reference-requests.php';
        break;
      case 'course-proposals':
        include './course-proposals-content/course-proposals.php';
        break;
      case 'dashboard':
      default:
        include './dashboard-content/dashboard.php';
        break;
    }
  ?>
</div>

<!-- Use shared scripts -->
<script src="../session_manager.js"></script>
<script src="./fix_js_errors.js?v=<?php echo time(); ?>"></script>
<script src="../scripts/global.js?v=<?php echo time(); ?>"></script>
<script src="../super_admin-mis/scripts/dashboard.js"></script>
<script src="../super_admin-mis/scripts/user-account-management.js"></script>
<script src="../super_admin-mis/scripts/school-calendar.js"></script>
<script src="./scripts/program-management.js?v=<?php echo time(); ?>"></script>

<script>
// All functions are now defined in fix_js_errors.js to avoid conflicts

// Global modal functions (will be overridden by page-specific functions if they exist)
function showNoProgramsModal() {
    // Create modal if it doesn't exist
    if (!document.getElementById('noProgramsModal')) {
        const modal = document.createElement('div');
        modal.id = 'noProgramsModal';
        modal.className = 'modal';
        modal.style.display = 'none';
        modal.innerHTML = `
            <div class="modal-box">
                <div class="modal-content">
                    <img src="../src/assets/animated_icons/info-animated-icon.gif" alt="Info" style="width: 64px; height: 64px; margin-bottom: 16px;">
                    <h2>No Programs Available</h2>
                    <p>You need to create at least one program before you can add courses. Programs help organize your courses by department and specialization.</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeNoProgramsModal()">Cancel</button>
                    <button type="button" class="create-btn" onclick="closeNoProgramsModal(); openAddProgramModal();">Create</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add CSS if not already added
        if (!document.getElementById('noProgramsModalCSS')) {
            const style = document.createElement('style');
            style.id = 'noProgramsModalCSS';
            style.textContent = `
                #noProgramsModal {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background-color: rgba(0, 0, 0, 0.5) !important;
                    display: none !important;
                    justify-content: center !important;
                    align-items: center !important;
                    z-index: 10000 !important;
                }
                #noProgramsModal.show {
                    display: flex !important;
                }
                #noProgramsModal .modal-box {
                    background: white !important;
                    border-radius: 12px !important;
                    padding: 0 !important;
                    max-width: 500px !important;
                    width: 90% !important;
                    max-height: 80vh !important;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
                    display: flex !important;
                    flex-direction: column !important;
                }
                #noProgramsModal .modal-content {
                    padding: 30px !important;
                    flex: 1 !important;
                    text-align: center !important;
                }
                #noProgramsModal .modal-content h2 {
                    margin: 0 0 20px 0 !important;
                    font-size: 1.4rem !important;
                    font-weight: bold !important;
                    color: #333 !important;
                    text-align: center !important;
                }
                #noProgramsModal .modal-content p {
                    margin: 0 0 5px 0 !important;
                    font-size: 1rem !important;
                    color: #666 !important;
                    line-height: 1.5 !important;
                }
                #noProgramsModal .modal-actions {
                    padding: 0 20px 20px 20px !important;
                    display: flex !important;
                    justify-content: center !important;
                    gap: 10px !important;
                    flex-shrink: 0 !important;
                }
                #noProgramsModal .modal-actions button {
                    padding: 12px 24px !important;
                    border: none !important;
                    border-radius: 8px !important;
                    font-size: 14px !important;
                    font-weight: bold !important;
                    cursor: pointer !important;
                    transition: all 0.3s ease !important;
                    min-width: 100px !important;
                }
                #noProgramsModal .modal-actions .create-btn {
                    min-width: 100px !important;
                }
                #noProgramsModal .modal-actions .cancel-btn {
                    background-color: #6c757d !important;
                    color: white !important;
                }
                #noProgramsModal .modal-actions .create-btn {
                    background-color: #4CAF50 !important;
                    color: white !important;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    const modal = document.getElementById('noProgramsModal');
    modal.classList.add('show');
    
    // Prevent body scroll while preserving scroll position
    const scrollY = window.scrollY;
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Store scroll position for restoration
    document.body.setAttribute('data-scroll-y', scrollY);
}

function closeNoProgramsModal() {
    const modal = document.getElementById('noProgramsModal');
    if (modal) {
        modal.classList.remove('show');
    }
    
    // Don't restore scroll position here - let the next modal handle it
    // This prevents conflicts when transitioning between modals
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.height = '';
    
    // Keep the scroll position stored for the next modal to use
    // Don't remove data-scroll-y attribute yet
}

function goToAddProgram() {
    closeNoProgramsModal();
    window.location.href = 'content.php?page=program-management';
}

// openAddProgramModal function is defined in scripts/program-management.js
// Back to Top functionality is handled by dashboard.js

// SIDEBAR TOGGLE FUNCTION
// Make toggleSidebar globally available
window.toggleSidebar = function() {
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
};

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
});

// TOOLTIP SYSTEM - Fixed and working
console.log('🚨 DEAN: Tooltip system enabled');

function createEmergencyTooltips() {
    console.log('🚨 DEAN: Creating tooltips...');
    
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`🚨 DEAN: Found ${navButtons.length} nav buttons`);
    
    if (navButtons.length === 0) {
        console.log('🚨 DEAN: No nav buttons found');
        return;
    }
    
    let emergencyTooltip = null;
    
    navButtons.forEach(function(button, index) {
        const spanElement = button.querySelector('span:not(.nav-icon-wrapper)');
        let tooltipText = 'Unknown';
        if (spanElement) {
            // Get innerHTML and replace <br> tags with spaces
            tooltipText = spanElement.innerHTML.replace(/<br\s*\/?>/gi, ' ').replace(/\s+/g, ' ').trim();
        }
        console.log(`🚨 DEAN: Setting up tooltip for button ${index + 1}: "${tooltipText}"`);
        
        button.addEventListener('mouseenter', function() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
            
            console.log(`🚨 DEAN: Hover on button ${index + 1}, sidebar collapsed: ${isCollapsed}`);
            
            if (isCollapsed) {
                if (emergencyTooltip) {
                    emergencyTooltip.remove();
                }
                
                const buttonRect = button.getBoundingClientRect();
                console.log(`🚨 DEAN: Button ${index + 1} position:`, buttonRect);
                
                emergencyTooltip = document.createElement('div');
                emergencyTooltip.innerHTML = `
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
                        filter: drop-shadow(-2px 0 4px rgba(0,0,0,0.2));
                    "></div>
                    ${tooltipText}
                `;
                
                emergencyTooltip.style.cssText = `
                    position: fixed !important;
                    left: ${buttonRect.right + 20}px !important;
                    top: ${buttonRect.top + buttonRect.height / 2}px !important;
                    transform: translateY(-50%) !important;
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
                
                document.body.appendChild(emergencyTooltip);
                console.log(`🚨 DEAN: Tooltip created for button ${index + 1}`);
            }
        });
        
        button.addEventListener('mouseleave', function() {
            console.log(`🚨 DEAN: Leave button ${index + 1}`);
            if (emergencyTooltip) {
                emergencyTooltip.remove();
                emergencyTooltip = null;
                console.log(`🚨 DEAN: Tooltip removed for button ${index + 1}`);
            }
        });
    });
    
    console.log('🚨 DEAN: Emergency tooltips created successfully');
}

// Create emergency tooltips multiple times
setTimeout(createEmergencyTooltips, 1000);
setTimeout(createEmergencyTooltips, 3000);
setTimeout(createEmergencyTooltips, 5000);

// Make it available globally
window.createEmergencyTooltips = createEmergencyTooltips;

console.log('🚨 DEAN: Emergency tooltip system ready');

// CHAT AND NOTIFICATION FUNCTIONALITY
console.log('🚨 DEAN: Initializing chat and notification functionality');

// Initialize chats and notifications
const chatsIcon = document.querySelector('.chats-icon');
if (chatsIcon) {
    console.log('🚨 DEAN: Chats icon found, adding click handler');
    chatsIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🚨 DEAN: Chats icon clicked');
        const dropdown = document.getElementById('chatsDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
            console.log('🚨 DEAN: Chats dropdown toggled:', dropdown.style.display);
        }
    };
    chatsIcon.style.cursor = 'pointer';
    chatsIcon.style.pointerEvents = 'auto';
    console.log('🚨 DEAN: Chats initialized');
} else {
    console.log('🚨 DEAN: Chats icon not found');
}

const notificationIcon = document.querySelector('.notification-icon');
if (notificationIcon) {
    console.log('🚨 DEAN: Notification icon found, adding click handler');
    notificationIcon.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🚨 DEAN: Notification icon clicked');
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            const currentDisplay = dropdown.style.display;
            dropdown.style.display = currentDisplay === 'block' ? 'none' : 'block';
            console.log('🚨 DEAN: Notification dropdown toggled:', dropdown.style.display);
        }
    };
    notificationIcon.style.cursor = 'pointer';
    notificationIcon.style.pointerEvents = 'auto';
    console.log('🚨 DEAN: Notifications initialized');
} else {
    console.log('🚨 DEAN: Notification icon not found');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.chats-icon')) {
        const chatsDropdown = document.getElementById('chatsDropdown');
        if (chatsDropdown) {
            chatsDropdown.style.display = 'none';
        }
    }
    
    if (!e.target.closest('.notification-icon')) {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.style.display = 'none';
        }
    }
});

console.log('🚨 DEAN: Chat and notification functionality ready');

// DIAGNOSTIC: Check if all critical functions are available
console.log('🔍 DIAGNOSTIC: Checking critical functions...');
const criticalFunctions = ['openAddCourseModal', 'toggleSidebar', 'checkProgramsAndOpenCourseModal'];
criticalFunctions.forEach(funcName => {
    if (typeof window[funcName] === 'function') {
        console.log('✅ Function available:', funcName);
    } else {
        console.error('❌ Function missing:', funcName);
    }
});

// Note: The override is handled in DOMContentLoaded event above

// DIAGNOSTIC: Check if modal elements exist
console.log('🔍 DIAGNOSTIC: Checking modal elements...');
const modalElements = ['addCourseModal', 'programSelectModal', 'courseSelectionModal'];
modalElements.forEach(elementId => {
    const element = document.getElementById(elementId);
    if (element) {
        console.log('✅ Element found:', elementId);
    } else {
        console.error('❌ Element missing:', elementId);
    }
});

// DIAGNOSTIC: Check for JavaScript errors
window.addEventListener('error', function(e) {
    console.error('🚨 JAVASCRIPT ERROR:', e.message, 'at', e.filename, 'line', e.lineno);
});

console.log('🔍 DIAGNOSTIC: All checks complete');
</script>

<!-- Notification System -->
<script src="./js/notifications.js?v=<?php echo time(); ?>"></script>

<!-- QA Submit Success Modal -->
<div id="qaSubmitSuccessModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content success-modal" style="max-width: 500px;">
        <div class="modal-header">
            <h2>✅ Successfully Submitted to QA!</h2>
            <span class="close" onclick="closeQASubmitSuccessModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon">🎉</div>
                <p class="success-message">Your course proposal(s) have been submitted to Quality Assurance for review.</p>
                <div class="success-details">
                    <p><strong>Courses Submitted:</strong> <span id="qaSubmitSuccessCount">0</span></p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="create-btn" onclick="closeQASubmitSuccessModal()">OK</button>
        </div>
    </div>
</div>

<!-- QA Submit Error Modal -->
<div id="qaSubmitErrorModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content error-modal" style="max-width: 500px;">
        <div class="modal-header">
            <h2>❌ Submission Failed</h2>
            <span class="close" onclick="closeQASubmitErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-content">
                <div class="error-icon">⚠️</div>
                <p class="error-message" id="qaSubmitErrorMessage">An error occurred while submitting courses to Quality Assurance.</p>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="cancel-btn" onclick="closeQASubmitErrorModal()">Close</button>
        </div>
    </div>
</div>

<style>
.success-modal .success-content {
    text-align: center;
    padding: 20px;
}

.success-modal .success-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.success-modal .success-message {
    font-size: 16px;
    color: #333;
    margin-bottom: 16px;
}

.success-modal .success-details {
    background-color: #f5f5f5;
    padding: 12px;
    border-radius: 6px;
    margin-top: 16px;
}

.success-modal .success-details p {
    margin: 8px 0;
    color: #666;
}

.error-modal .error-content {
    text-align: center;
    padding: 20px;
}

.error-modal .error-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.error-modal .error-message {
    font-size: 16px;
    color: #333;
    margin-bottom: 16px;
}
</style>

</body>
</html>