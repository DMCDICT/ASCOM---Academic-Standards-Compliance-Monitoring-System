<!-- Assign Faculty Modal - DESIGN.md Compliant -->
<div id="assignFacultyModal" class="modal-overlay" style="display: none;">
    <div class="premium-modal">
        <!-- Modal Header -->
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="icon-container" style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(12, 75, 52, 0.08); color: #0C4B34;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <h2 class="modal-title" id="assignFacultyModalTitle">Assign Faculty</h2>
                    <p style="margin: 0; font-size: 12px; color: rgba(17, 24, 39, 0.5); font-weight: 600;" id="assignFacultyModalSubtitle">Select a teacher for this course</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeAssignFacultyModal()" aria-label="Close modal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            <!-- Course Info Card -->
            <div class="modal-section" style="margin-bottom: 20px; background: rgba(12, 75, 52, 0.04);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="background: linear-gradient(135deg, #0C4B34 0%, #0F7A53 100%); color: white; padding: 8px 14px; border-radius: 8px; font-weight: 800; font-size: 14px; letter-spacing: 0.5px;" id="modalCourseCode">
                        ---
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; color: #111827; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="modalCourseTitle">
                            ---
                        </div>
                        <div style="font-size: 12px; color: rgba(17, 24, 39, 0.5); font-weight: 600;" id="modalCourseInfo">
                            ---
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div style="margin-bottom: 16px;">
                <div class="search-bar" style="display: flex; align-items: center; background-color: #FFFFFF; height: 44px; padding: 0 14px; border-radius: 10px; border: 1px solid rgba(12, 75, 52, 0.14); transition: border-color 0.2s ease, box-shadow 0.2s ease;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(17, 24, 39, 0.4)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="teacherSearchInput" placeholder="Search by name, employee number, or title..." 
                           style="border: none; outline: none; flex: 1; font-size: 14px; background: transparent; padding: 0 10px; font-family: 'TT Interphases', sans-serif;"
                           oninput="filterTeachers(this.value)">
                    <button type="button" onclick="clearTeacherSearch()" style="background: none; border: none; color: rgba(17, 24, 39, 0.4); cursor: pointer; padding: 4px; display: none;" id="clearSearchBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Teachers List Container -->
            <div id="teachersListContainer">
                <!-- Loading State -->
                <div id="teachersLoadingState" style="text-align: center; padding: 40px 20px;">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid rgba(12, 75, 52, 0.2); border-top-color: #0C4B34; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin: 16px 0 0 0; font-size: 13px; color: rgba(17, 24, 39, 0.5); font-weight: 600;">Loading teachers...</p>
                </div>

                <!-- Error State -->
                <div id="teachersErrorState" class="error-card" style="display: none; padding: 20px 14px; text-align: center; color: #b91c1c; background: rgba(185, 28, 28, 0.06); border: 1px solid rgba(185, 28, 28, 0.14); border-radius: 12px; font-weight: 700;">
                    <p style="margin: 0;">Failed to load teachers. Please try again.</p>
                    <button onclick="loadTeachersForAssignment()" style="margin-top: 12px; background: #0C4B34; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">Retry</button>
                </div>

                <!-- Empty State -->
                <div id="teachersEmptyState" class="empty-state" style="display: none; padding: 32px 16px; text-align: center; color: rgba(17, 24, 39, 0.4); font-weight: 600; font-size: 13px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display: block; margin: 0 auto 12px; opacity: 0.3;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <p style="margin: 0;">No teachers found in this department</p>
                </div>

                <!-- No Results State -->
                <div id="teachersNoResultsState" class="empty-state" style="display: none; padding: 32px 16px; text-align: center; color: rgba(17, 24, 39, 0.4); font-weight: 600; font-size: 13px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display: block; margin: 0 auto 12px; opacity: 0.3;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <p style="margin: 0;">No teachers match your search</p>
                </div>

                <!-- Teachers List -->
                <div id="teachersList" style="display: none; display: flex; flex-direction: column; gap: 10px; max-height: 320px; overflow-y: auto;">
                    <!-- Teacher items will be inserted here -->
                </div>
            </div>

            <!-- Currently Assigned Section -->
            <div id="currentAssignmentSection" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(12, 75, 52, 0.1);">
                <div class="section-header">
                    <div class="label-bar"></div>
                    <div>
                        <h4>Currently Assigned</h4>
                        <p>This course already has an assigned teacher</p>
                    </div>
                </div>
                <div id="currentAssignedTeacher" style="background: rgba(12, 75, 52, 0.04); border: 1px solid rgba(12, 75, 52, 0.12); border-radius: 10px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                    <!-- Current teacher info will be inserted here -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 16px 24px; background: linear-gradient(0deg, rgba(12, 75, 52, 0.04), rgba(12, 75, 52, 0.04)); border-top: 1px solid rgba(12, 75, 52, 0.1); display: flex; justify-content: flex-end; gap: 12px;">
            <button class="btn-cancel" onclick="closeAssignFacultyModal()">
                Cancel
            </button>
            <button class="btn-create" id="assignTeacherBtn" onclick="assignSelectedTeacher()" disabled>
                Assign Faculty
            </button>
        </div>
    </div>
</div>

<style>
    /* Premium Modal Styles (DESIGN.md) */
    .premium-modal {
        width: min(680px, calc(100vw - 28px));
        max-height: min(85vh, 720px);
        background: #ffffff;
        border: 1px solid rgba(12, 75, 52, 0.18);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 48px rgba(0, 0, 0, 0.22);
        display: flex;
        flex-direction: column;
        animation: modalPop 0.18s ease-out;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        background: linear-gradient(0deg, rgba(12, 75, 52, 0.08), rgba(12, 75, 52, 0.08)), #ffffff;
        border-bottom: 1px solid rgba(12, 75, 52, 0.14);
    }

    .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin: 0;
        font-family: 'TT Interphases', sans-serif;
    }

    .modal-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(12, 75, 52, 0.16);
        background: rgba(12, 75, 52, 0.06);
        color: #0C4B34;
        font-size: 26px;
        line-height: 1;
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: background 0.15s ease, transform 0.08s ease;
        flex: none;
    }

    .modal-close:hover {
        background: rgba(12, 75, 52, 0.1);
    }

    .modal-close:active {
        transform: scale(0.98);
        background: rgba(12, 75, 52, 0.14);
    }

    .modal-section {
        background: #ffffff;
        border: 1px solid rgba(12, 75, 52, 0.12);
        border-radius: 14px;
        padding: 14px;
    }

    .section-header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
    }

    .label-bar {
        width: 4px;
        height: 20px;
        border-radius: 2px;
        background: linear-gradient(180deg, #0C4B34 0%, #0F7A53 100%);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .section-header h3, .section-header h4 {
        font-size: 16px;
        font-weight: 800;
        color: #0C4B34;
        margin: 0 0 4px 0;
        font-family: 'TT Interphases', sans-serif;
    }

    .section-header p {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        margin: 0;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
    }

    .btn-cancel {
        background-color: #C9C9C9;
        color: black;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        height: 50px;
        transition: background-color 0.3s;
        font-family: 'TT Interphases', sans-serif;
    }

    .btn-cancel:hover {
        background-color: #B9B9B9;
    }

    .btn-create {
        background-color: #0F7A53;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        height: 50px;
        transition: background-color 0.3s;
        font-family: 'TT Interphases', sans-serif;
    }

    .btn-create:hover:enabled {
        background-color: #0a5f42;
    }

    .btn-create:disabled {
        background-color: #A5A5A5;
        color: black;
        cursor: not-allowed;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Teacher Item Styles */
    .teacher-item {
        background: #ffffff;
        border: 1px solid rgba(12, 75, 52, 0.14);
        border-radius: 12px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        display: flex;
        align-items: center;
        gap: 14px;
        animation: fadeSlideUp 0.35s ease-out both;
    }

    .teacher-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(12, 75, 52, 0.12);
        border-color: rgba(12, 75, 52, 0.25);
    }

    .teacher-item.selected {
        background: rgba(12, 75, 52, 0.08);
        border-color: #0C4B34;
        box-shadow: 0 0 0 2px rgba(12, 75, 52, 0.2);
    }

    .teacher-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .teacher-item.disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .teacher-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0C4B34 0%, #0F7A53 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 16px;
        flex-shrink: 0;
    }

    .teacher-info {
        flex: 1;
        min-width: 0;
    }

    .teacher-name {
        font-weight: 700;
        color: #111827;
        font-size: 14px;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .teacher-details {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        font-weight: 600;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .teacher-detail-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .teacher-detail-item svg {
        width: 12px;
        height: 12px;
        opacity: 0.6;
    }

    .teacher-check {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid rgba(12, 75, 52, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .teacher-item.selected .teacher-check {
        background: #0C4B34;
        border-color: #0C4B34;
    }

    .teacher-check svg {
        width: 14px;
        height: 14px;
        color: white;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .teacher-item.selected .teacher-check svg {
        opacity: 1;
    }

    /* Program Head Badge */
    .program-head-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(12, 75, 52, 0.1);
        color: #0C4B34;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .program-head-badge svg {
        width: 10px;
        height: 10px;
    }

    /* Dark Mode Styles */
    html[data-theme="dark"] .teacher-item {
        background: #252525;
        border-color: #333;
    }

    html[data-theme="dark"] .teacher-item:hover {
        border-color: #444;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    html[data-theme="dark"] .teacher-item.selected {
        background: rgba(129, 199, 132, 0.1);
        border-color: #81C784;
        box-shadow: 0 0 0 2px rgba(129, 199, 132, 0.2);
    }

    html[data-theme="dark"] .teacher-avatar {
        background: linear-gradient(135deg, #81C784 0%, #4CAF50 100%);
    }

    html[data-theme="dark"] .teacher-name {
        color: #f0f0f0;
    }

    html[data-theme="dark"] .teacher-details {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .teacher-check {
        border-color: #404040;
    }

    html[data-theme="dark"] .teacher-item.selected .teacher-check {
        background: #81C784;
        border-color: #81C784;
    }

    html[data-theme="dark"] .program-head-badge {
        background: rgba(129, 199, 132, 0.15);
        color: #81C784;
    }

    html[data-theme="dark"] .search-bar {
        background-color: #2d2d2d;
        border-color: #404040;
    }

    html[data-theme="dark"] .search-bar input {
        color: #e0e0e0;
    }

    html[data-theme="dark"] #teacherSearchInput::placeholder {
        color: #808080;
    }

    html[data-theme="dark"] .modal-section {
        background: rgba(255, 255, 255, 0.04);
        border-color: #333;
    }

    html[data-theme="dark"] #modalCourseCode {
        background: linear-gradient(135deg, #81C784 0%, #4CAF50 100%);
    }

    html[data-theme="dark"] .modal-title {
        color: #f0f0f0;
    }

    html[data-theme="dark"] .modal-section-title {
        color: #81C784;
    }

    html[data-theme="dark"] .section-header h4 {
        color: #81C784;
    }

    html[data-theme="dark"] #currentAssignmentSection {
        border-top-color: #333;
    }

    html[data-theme="dark"] #currentAssignedTeacher {
        background: rgba(255, 255, 255, 0.04);
        border-color: #333;
    }

    /* Scrollbar Styles */
    #teachersList::-webkit-scrollbar {
        width: 6px;
    }

    #teachersList::-webkit-scrollbar-track {
        background: rgba(12, 75, 52, 0.05);
        border-radius: 3px;
    }

    #teachersList::-webkit-scrollbar-thumb {
        background: rgba(12, 75, 52, 0.2);
        border-radius: 3px;
    }

    #teachersList::-webkit-scrollbar-thumb:hover {
        background: rgba(12, 75, 52, 0.3);
    }

    html[data-theme="dark"] #teachersList::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.04);
    }

    html[data-theme="dark"] #teachersList::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
    }

    html[data-theme="dark"] #teachersList::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.25);
    }
</style>

<script>
    // Global variables for faculty assignment
    let currentCourseId = null;
    let currentCourseCode = null;
    let currentCourseTitle = null;
    let currentCourseInfo = null;
    let departmentTeachers = [];
    let selectedTeacherId = null;
    let currentAssignedTeacher = null;

    // Open the assign faculty modal
    function assignFaculty(courseCode, courseId, courseTitle, courseInfo) {
        currentCourseId = courseId;
        currentCourseCode = courseCode;
        currentCourseTitle = courseTitle;
        currentCourseInfo = courseInfo;
        selectedTeacherId = null;
        currentAssignedTeacher = null;

        // Update modal content
        document.getElementById('assignFacultyModalTitle').textContent = 'Assign Faculty';
        document.getElementById('assignFacultyModalSubtitle').textContent = 'Select a teacher for this course';
        document.getElementById('modalCourseCode').textContent = courseCode;
        document.getElementById('modalCourseTitle').textContent = courseTitle;
        document.getElementById('modalCourseInfo').textContent = courseInfo || 'Course Details';

        // Reset search
        document.getElementById('teacherSearchInput').value = '';
        document.getElementById('clearSearchBtn').style.display = 'none';

        // Reset selection
        updateAssignButtonState();

        // Show modal
        const modal = document.getElementById('assignFacultyModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Load teachers
        loadTeachersForAssignment();
    }

    // Close the assign faculty modal
    function closeAssignFacultyModal() {
        const modal = document.getElementById('assignFacultyModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset variables
        currentCourseId = null;
        currentCourseCode = null;
        currentCourseTitle = null;
        currentCourseInfo = null;
        selectedTeacherId = null;
        currentAssignedTeacher = null;
    }

    // Load teachers for assignment
    function loadTeachersForAssignment() {
        // Show loading state
        document.getElementById('teachersLoadingState').style.display = 'block';
        document.getElementById('teachersErrorState').style.display = 'none';
        document.getElementById('teachersEmptyState').style.display = 'none';
        document.getElementById('teachersNoResultsState').style.display = 'none';
        document.getElementById('teachersList').style.display = 'none';
        document.getElementById('currentAssignmentSection').style.display = 'none';

        // Get department ID from session
        const departmentId = <?php echo json_encode($_SESSION['selected_role']['department_id'] ?? null); ?>;

        if (!departmentId) {
            showTeachersError('Department not found. Please refresh and try again.');
            return;
        }

        // Fetch teachers from API
        fetch(`api/get_department_teachers.php?department_id=${departmentId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('teachersLoadingState').style.display = 'none';

                if (data.success) {
                    departmentTeachers = data.teachers || [];
                    
                    if (departmentTeachers.length === 0) {
                        document.getElementById('teachersEmptyState').style.display = 'block';
                    } else {
                        // Check if course already has an assigned teacher
                        checkCurrentAssignment();
                    }
                } else {
                    showTeachersError(data.message || 'Failed to load teachers');
                }
            })
            .catch(error => {
                console.error('Error loading teachers:', error);
                document.getElementById('teachersLoadingState').style.display = 'none';
                showTeachersError('Failed to load teachers. Please try again.');
            });
    }

    // Show error state
    function showTeachersError(message) {
        document.getElementById('teachersLoadingState').style.display = 'none';
        document.getElementById('teachersErrorState').style.display = 'block';
        document.getElementById('teachersErrorState').querySelector('p').textContent = message;
    }

    // Check current assignment for this course
    function checkCurrentAssignment() {
        if (!currentCourseId) {
            renderTeachersList(departmentTeachers);
            return;
        }

        // Fetch current assignment
        fetch(`api/get_course_teachers.php?course_id=${currentCourseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.teachers && data.teachers.length > 0) {
                    currentAssignedTeacher = data.teachers[0];
                    document.getElementById('currentAssignmentSection').style.display = 'block';
                    
                    // Render current assignment
                    const container = document.getElementById('currentAssignedTeacher');
                    const teacher = currentAssignedTeacher;
                    const initials = getInitials(teacher.first_name, teacher.last_name);
                    
                    container.innerHTML = `
                        <div class="teacher-avatar" style="width: 40px; height: 40px; font-size: 14px;">${initials}</div>
                        <div class="teacher-info">
                            <div class="teacher-name">${teacher.first_name} ${teacher.last_name}</div>
                            <div class="teacher-details">
                                <span class="teacher-detail-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    ${teacher.employee_no || 'N/A'}
                                </span>
                                <span class="teacher-detail-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    ${teacher.title || 'Teacher'}
                                </span>
                            </div>
                        </div>
                        <button onclick="removeCurrentTeacher(${teacher.teacher_id})" style="background: rgba(185, 28, 28, 0.1); color: #b91c1c; border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: background 0.2s;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Remove
                        </button>
                    `;
                }
                
                // Render teachers list (excluding already assigned)
                renderTeachersList(departmentTeachers);
            })
            .catch(error => {
                console.error('Error checking assignment:', error);
                renderTeachersList(departmentTeachers);
            });
    }

    // Render teachers list
    function renderTeachersList(teachers) {
        const container = document.getElementById('teachersList');
        
        if (teachers.length === 0) {
            const searchValue = document.getElementById('teacherSearchInput').value;
            if (searchValue.trim()) {
                document.getElementById('teachersNoResultsState').style.display = 'block';
            } else {
                document.getElementById('teachersEmptyState').style.display = 'block';
            }
            return;
        }

        document.getElementById('teachersList').style.display = 'flex';
        container.innerHTML = '';

        teachers.forEach((teacher, index) => {
            const isAssigned = currentAssignedTeacher && currentAssignedTeacher.teacher_id === teacher.id;
            const initials = getInitials(teacher.first_name, teacher.last_name);
            
            const item = document.createElement('div');
            item.className = `teacher-item ${isAssigned ? 'disabled' : ''} ${selectedTeacherId === teacher.id ? 'selected' : ''}`;
            item.style.animationDelay = `${index * 0.05}s`;
            item.onclick = () => {
                if (!isAssigned) {
                    selectTeacher(teacher.id);
                }
            };

            item.innerHTML = `
                <div class="teacher-avatar">${initials}</div>
                <div class="teacher-info">
                    <div class="teacher-name">${teacher.first_name} ${teacher.last_name}</div>
                    <div class="teacher-details">
                        <span class="teacher-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            ${teacher.employee_no || 'N/A'}
                        </span>
                        <span class="teacher-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            ${teacher.title || 'Teacher'}
                        </span>
                        ${teacher.is_program_head ? `
                        <span class="program-head-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                            Program Head
                        </span>
                        ` : ''}
                    </div>
                </div>
                <div class="teacher-check">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            `;

            container.appendChild(item);
        });
    }

    // Get initials from name
    function getInitials(firstName, lastName) {
        const first = firstName ? firstName.charAt(0).toUpperCase() : '';
        const last = lastName ? lastName.charAt(0).toUpperCase() : '';
        return first + last || '?';
    }

    // Select a teacher
    function selectTeacher(teacherId) {
        selectedTeacherId = teacherId;
        
        // Update UI
        const items = document.querySelectorAll('.teacher-item');
        items.forEach(item => {
            item.classList.remove('selected');
        });
        
        // Find and select the clicked item
        const teacher = departmentTeachers.find(t => t.id === teacherId);
        if (teacher) {
            const items = document.querySelectorAll('.teacher-item');
            items.forEach((item, index) => {
                if (departmentTeachers[index].id === teacherId) {
                    item.classList.add('selected');
                }
            });
        }
        
        updateAssignButtonState();
    }

    // Update assign button state
    function updateAssignButtonState() {
        const btn = document.getElementById('assignTeacherBtn');
        btn.disabled = !selectedTeacherId;
        btn.style.opacity = selectedTeacherId ? '1' : '0.5';
    }

    // Filter teachers by search
    function filterTeachers(searchValue) {
        const clearBtn = document.getElementById('clearSearchBtn');
        clearBtn.style.display = searchValue.trim() ? 'block' : 'none';
        
        if (!searchValue.trim()) {
            renderTeachersList(departmentTeachers);
            return;
        }

        const search = searchValue.toLowerCase();
        const filtered = departmentTeachers.filter(teacher => {
            return (
                (teacher.first_name && teacher.first_name.toLowerCase().includes(search)) ||
                (teacher.last_name && teacher.last_name.toLowerCase().includes(search)) ||
                (teacher.employee_no && teacher.employee_no.toLowerCase().includes(search)) ||
                (teacher.title && teacher.title.toLowerCase().includes(search))
            );
        });

        renderTeachersList(filtered);
    }

    // Clear teacher search
    function clearTeacherSearch() {
        document.getElementById('teacherSearchInput').value = '';
        document.getElementById('clearSearchBtn').style.display = 'none';
        renderTeachersList(departmentTeachers);
    }

    // Assign selected teacher
    function assignSelectedTeacher() {
        if (!selectedTeacherId || !currentCourseId) {
            return;
        }

        // If there's already an assigned teacher and we are selecting a new one
        if (currentAssignedTeacher && currentAssignedTeacher.teacher_id !== selectedTeacherId) {
            const confirmMsg = `This course is currently assigned to ${currentAssignedTeacher.first_name} ${currentAssignedTeacher.last_name}.\n\nAre you sure you want to reassign it to the selected teacher?`;
            if (!confirm(confirmMsg)) {
                return;
            }
        }

        const btn = document.getElementById('assignTeacherBtn');
        btn.disabled = true;
        btn.textContent = 'Assigning...';

        fetch('api/assign_course_teacher.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                course_id: currentCourseId,
                teacher_id: selectedTeacherId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success and close
                showNotification('Faculty assigned successfully!', 'success');
                closeAssignFacultyModal();
                
                // Refresh the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert(data.message || 'Failed to assign faculty');
                btn.disabled = false;
                btn.textContent = 'Assign Faculty';
            }
        })
        .catch(error => {
            console.error('Error assigning teacher:', error);
            alert('Failed to assign faculty. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Assign Faculty';
        });
    }

    // Remove current teacher
    function removeCurrentTeacher(teacherId) {
        if (!currentCourseId || !teacherId) {
            return;
        }

        if (!confirm('Are you sure you want to remove this teacher from this course?')) {
            return;
        }

        fetch('api/remove_course_teacher.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                course_id: currentCourseId,
                teacher_id: teacherId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Teacher removed successfully!', 'success');
                currentAssignedTeacher = null;
                document.getElementById('currentAssignmentSection').style.display = 'none';
                
                // Refresh the page
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert(data.message || 'Failed to remove teacher');
            }
        })
        .catch(error => {
            console.error('Error removing teacher:', error);
            alert('Failed to remove teacher. Please try again.');
        });
    }

    // Show notification
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#0F7A53' : '#b91c1c'};
            color: white;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Close modal on overlay click
    document.getElementById('assignFacultyModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAssignFacultyModal();
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('assignFacultyModal');
            if (modal && modal.style.display === 'flex') {
                closeAssignFacultyModal();
            }
        }
    });
</script>