<!-- Academic Management Page -->
<div class="courses-section" style="margin-top: 0; margin-bottom: 18px;">
  <div class="header-section">
    <div>
      <h1 class="main-page-title">Academic Management</h1>
      <p class="page-description">Monitor course compliance and book references</p>
    </div>
  </div>

  <!-- Library Table Container -->
  <div class="library-table-container">
    <div class="table-controls">
      <div class="search-box">
        <input type="text" id="librarySearch" placeholder="Search by course code, title, or program..." onkeyup="filterLibraryItems()">
        <img src="../src/assets/icons/search-icon.png" alt="Search" class="search-icon">
    </div>
    </div>

    <div class="table-wrapper">
      <table class="library-table" id="libraryTable">
      <thead>
        <tr>
            <th style="width: 90px;">Course Code</th>
            <th style="width: 210px;">Course Title</th>
            <th style="width: 50px;">Units</th>
            <th style="width: 70px;">Programs</th>
            <th class="term-year-header" style="width: 80px;">Term &<br>Academic Year</th>
            <th style="width: 70px;">Year Level</th>
            <th style="width: 80px;">Book<br>References</th>
            <th style="width: 90px;">Compliance</th>
            <th style="width: 80px;">Actions</th>
        </tr>
      </thead>
        <tbody id="libraryTableBody">
          <!-- Course groups will be dynamically generated here -->
      </tbody>
    </table>
    </div>

    <div class="table-pagination">
      <div class="pagination-info">
        <span id="paginationInfo">Showing 1-10 of 50 items</span>
      </div>
      <div class="pagination-center">
        <div class="pagination-controls">
          <button class="pagination-btn" id="prevPageBtn" onclick="changePage(-1)">Previous</button>
          <div class="page-numbers" id="pageNumbers">
            <!-- Page numbers will be generated here -->
          </div>
          <button class="pagination-btn" id="nextPageBtn" onclick="changePage(1)">Next</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Course Details -->
<div id="courseDetailsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); z-index:9999; align-items:center; justify-content:center; pointer-events:none;">
  <div style="background:#efefef; border-radius:12px; max-width:1100px; width:95vw; margin:auto; box-shadow:0 8px 40px 8px rgba(0,0,0,0.22); padding:32px 32px 24px 32px; position:relative; pointer-events:auto; max-height:90vh; height:750px;">
    <button id="closeCourseModal" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:32px; color:#888; cursor:pointer; padding:6px 16px;">&times;</button>
    <div id="modalCourseTitle" style="font-size:1.3rem; font-weight:bold; color:#111; font-family:'TT Interphases',sans-serif; margin-bottom:2px;"></div>
    <div id="modalCourseSubtitle" style="font-size:15px; color:#374151; font-family:'TT Interphases',sans-serif; margin-bottom:18px;"></div>
    <div style="display:flex; gap:32px; flex-wrap:wrap; align-items:flex-start;">
      <!-- Left: Summary -->
      <div style="flex:0 0 340px; min-width:260px; max-width:360px;">
        <div style="display:flex; gap:18px; margin-bottom:24px; flex-wrap:wrap;">
          <div style="flex:1 1 120px; min-width:120px; background:#fafbfc; border-radius:8px; padding:16px 18px; display:flex; align-items:center; gap:10px; border:1px solid #ececec;">
            <span style="font-size:22px; color:#888;">&#128193;</span>
            <div>
              <div style="font-size:14px; color:#666;">Department</div>
              <div id="modalDepartment" style="font-size:17px; font-weight:bold; color:#111;">CCS</div>
            </div>
          </div>
          <div style="flex:1 1 120px; min-width:120px; background:#fafbfc; border-radius:8px; padding:16px 18px; display:flex; align-items:center; gap:10px; border:1px solid #ececec;">
            <span style="font-size:22px; color:#888;">&#128218;</span>
            <div>
              <div style="font-size:14px; color:#666;">References</div>
              <div id="modalReferences" style="font-size:17px; font-weight:bold; color:#111;"></div>
            </div>
          </div>
        </div>
        <!-- Merged Compliance Status Card - Updated Layout -->
        <div style="background:#fafbfc; border-radius:12px; padding:24px 20px 20px 20px; border:1px solid #ececec; margin-bottom: 18px; position:relative; overflow:hidden;">
          <!-- Background pattern for visual appeal -->
          <div style="position:absolute; top:-20px; right:-20px; width:80px; height:80px; background:rgba(0,0,0,0.03); border-radius:50%;"></div>
          <div style="position:absolute; top:10px; right:10px; width:40px; height:40px; background:rgba(0,0,0,0.02); border-radius:50%;"></div>
          
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0;">
            <div style="display:flex; flex-direction:column; justify-content:flex-start;">
              <div style="font-size:18px; font-weight:bold; color:#111; font-family:'TT Interphases',sans-serif;">Compliance Status</div>
            </div>
            <div style="text-align:right; min-width:100px; display:flex; flex-direction:column; align-items:flex-end;">
              <div id="modalCompliance" style="font-size:28px; font-weight:bold; color:#ef4444; font-family:'TT Interphases',sans-serif; line-height:1; margin-bottom:4px;">60%</div>
              <div id="modalStatusLabel" style="background:#ef4444; color:#fff; font-size:13px; font-weight:bold; border-radius:12px; padding:3px 12px; display:inline-block; font-family:'TT Interphases',sans-serif; margin-bottom:2px;">non-compliant</div>
            </div>
          </div>
          <div style="width:100%; height:10px; background:#f3f4f6; border-radius:5px; overflow:hidden; margin:12px 0 0 0;">
            <div id="modalComplianceBar" style="width:60%; height:100%; background:#111; border-radius:5px; transition:width 0.6s ease-in-out;"></div>
          </div>
          <div id="modalStatusDesc" style="font-size:15px; color:#374151; font-family:'TT Interphases',sans-serif; margin-top:10px;">This course needs 2 more references to be compliant.</div>
        </div>
        <!-- Compliance Issues Card -->
        <div id="modalComplianceIssues" style="background:#fff; border:1.5px solid #ef4444; border-radius:10px; padding:18px 18px 12px 18px; margin-bottom: 0; display:none;">
          <div style="font-size:20px; font-weight:bold; color:#ef4444; font-family:'TT Interphases',sans-serif; display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <span style='font-size:1.4em;'>&#9888;</span> Compliance Issues
          </div>
          <ul id="modalComplianceIssuesList" style="margin:0; padding-left:22px;"></ul>
        </div>
      </div>
      <!-- Right: References List -->
      <div style="flex:1 1 0; min-width:260px;">
        <div style="background:#fff; border-radius:12px; border:1px solid #ececec; padding:24px 18px 18px 18px; margin-bottom:0; height:100%; display:flex; flex-direction:column;">
          <div style="font-size:20px; font-weight:bold; color:#111; font-family:'TT Interphases',sans-serif; margin-bottom:2px;">Course References</div>
          <div style="font-size:15px; color:#6b7280; font-family:'TT Interphases',sans-serif; margin-bottom:18px;">Books and materials assigned to this course</div>
          <!-- Tabs for reference type -->
          <div id="referenceTabs" style="display:flex; gap:8px; margin-bottom:10px;">
            <button id="showCompliantRefs" type="button" style="background:#111; color:#fff; border:none; border-radius:8px; padding:6px 18px; font-size:14px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Compliant References</button>
            <button id="showOutdatedRefs" type="button" style="background:#f3f4f6; color:#222; border:none; border-radius:8px; padding:6px 18px; font-size:14px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Outdated References</button>
          </div>
          <div id="modalReferencesList" style="max-height:60vh; overflow-y:auto;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notify Librarian Modal -->
<div id="notifyLibrarianModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); z-index:99999; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; max-width:400px; width:95vw; margin:auto; box-shadow:0 8px 40px 8px rgba(0,0,0,0.22); padding:32px 32px 24px 32px; position:relative; pointer-events:auto;">
    <button id="closeNotifyModal" style="position:absolute; top:18px; right:18px; background:none; border:none; font-size:32px; color:#888; cursor:pointer; padding:6px 16px;">&times;</button>
    <div id="notifyStep1">
      <div style="font-size:1.2rem; font-weight:bold; color:#111; font-family:'TT Interphases',sans-serif; margin-bottom:18px;">Notify Librarian</div>
      <div style="font-size:16px; color:#222; font-family:'TT Interphases',sans-serif; margin-bottom:24px;">Would you like to Notify the Librarian about the Compliance Issues of this Course?</div>
      <div style="display:flex; gap:16px; justify-content:flex-end;">
        <button id="notifyCancelBtn" style="background:#bdbdbd; color:#fff; border:none; border-radius:8px; padding:8px 22px; font-size:15px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Cancel</button>
        <button id="notifyConfirmBtn" style="background:#E63946; color:#fff; border:none; border-radius:8px; padding:8px 22px; font-size:15px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Yes, Notify</button>
      </div>
    </div>
    <div id="notifyStep2" style="display:none;">
      <div style="font-size:1.1rem; font-weight:bold; color:#111; font-family:'TT Interphases',sans-serif; margin-bottom:18px;">Set Due Date</div>
      <div style="font-size:16px; color:#222; font-family:'TT Interphases',sans-serif; margin-bottom:18px;">When should the librarian complete this task?</div>
      <input type="date" id="notifyDueDate" style="font-size:16px; padding:8px 12px; border-radius:6px; border:1px solid #ccc; font-family:'TT Interphases',sans-serif; margin-bottom:18px; width:100%;">
      <div style="font-size:16px; color:#222; font-family:'TT Interphases',sans-serif; margin-bottom:8px;">Additional Notes (Optional):</div>
      <textarea id="notifyNotes" placeholder="Add any specific notes or instructions for the librarian..." style="font-size:16px; padding:8px 12px; border-radius:6px; border:1px solid #ccc; font-family:'TT Interphases',sans-serif; margin-bottom:18px; width:100%; height:80px; resize:vertical; box-sizing:border-box;"></textarea>
      <div style="display:flex; gap:16px; justify-content:flex-end;">
        <button id="notifyDueCancelBtn" style="background:#bdbdbd; color:#fff; border:none; border-radius:8px; padding:8px 22px; font-size:15px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Cancel</button>
        <button id="notifyDueConfirmBtn" style="background:#E63946; color:#fff; border:none; border-radius:8px; padding:8px 22px; font-size:15px; font-family:'TT Interphases',sans-serif; font-weight:bold; cursor:pointer;">Set Due Date</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Library Management Page Styles */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0px;
}

.main-page-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0 !important;
    padding: 0 !important;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
}

.page-description {
    font-size: 14px;
    margin: 5px 0 0px 0;
    line-height: 1.4;
    color: #666;
    font-family: 'TT Interphases', sans-serif;
}

.library-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.filter-btn {
    background: white;
    color: #333;
    border: 1px solid #e0e0e0;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

.filter-btn:hover {
    background: #f5f5f5;
    border-color: #1976d2;
    color: #1976d2;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.filter-btn:active {
    transform: translateY(0);
}

.library-table-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    overflow: hidden;
    margin-top: 20px;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0 24px;
    gap: 20px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 40px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    transition: border-color 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    opacity: 0.6;
}

.filter-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}

.course-group-row {
    background: #f8f9fa;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.course-group-row:hover {
    background: #e3f2fd;
}

.course-code-cell {
    font-weight: 600;
    color: #1976d2;
}

.course-title-cell {
    font-weight: 500;
    color: #333;
    max-width: 210px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.4;
    width: 210px;
}

.year-level-cell {
    font-weight: 500;
    color: #666;
    width: 70px;
    text-align: center;
}

.units-cell {
    font-weight: 500;
    color: #666;
    width: 50px;
    text-align: center;
}

.programs-cell {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
    align-items: center;
    min-width: 70px;
    max-width: 70px;
    overflow: hidden;
}

.program-badge {
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    color: white;
    display: inline-block;
    margin-right: 2px;
    white-space: nowrap;
    flex-shrink: 0;
}

.program-badge:last-child {
    margin-right: 0;
}

.additional-programs {
    cursor: help;
    position: relative;
    padding: 3px 6px;
    font-size: 10px;
    margin-left: 2px;
    flex-shrink: 0;
}

.additional-programs:hover {
    background-color: #5a6268 !important;
    transform: scale(1.05);
    transition: all 0.2s ease;
}

.book-count-cell {
    font-weight: 600;
    color: #1976d2;
}

.compliance-status-cell {
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
    width: 90px;
}

.compliance-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-family: 'TT Interphases', sans-serif;
    white-space: nowrap;
    min-width: 60px;
    text-align: center;
}

.compliance-badge.compliant {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.compliance-badge.non-compliant {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.term-year-cell {
    color: #666;
    font-size: 13px;
    min-width: 80px;
    max-width: 80px;
    width: 80px;
}

.library-table th.term-year-header {
    min-width: 80px;
    max-width: 80px;
    width: 80px;
}

.view-books-btn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
}

.view-books-btn:hover {
    background: #45a049;
}

.table-wrapper {
    overflow-x: hidden;
    margin: 20px 24px 0 24px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.library-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-family: 'TT Interphases', sans-serif;
    table-layout: fixed;
}

.library-table thead {
    background: #f8f9fa;
}

.library-table th {
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    font-size: 14px;
    border-bottom: 2px solid #e0e0e0;
    white-space: nowrap;
}

.library-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #555;
    vertical-align: middle;
}

.library-table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
}

.library-table tbody tr:hover {
    background: #f0f7ff;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid #f0f0f0;
    position: relative;
  }

.pagination-info {
    color: #666;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    position: absolute;
    left: 24px;
    top: 50%;
    transform: translateY(-50%);
    white-space: nowrap;
  }

.pagination-center {
    flex: 1;
    display: flex;
    justify-content: center;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
    pointer-events: auto;
    flex-shrink: 0;
}

.pagination-btn {
    background: white;
    border: 1px solid #e0e0e0;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    position: relative;
    z-index: 1;
    pointer-events: auto;
}

.pagination-btn:hover:not(:disabled) {
    background: #f5f5f5;
    border-color: #1976d2;
    color: #1976d2;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-numbers {
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
    position: relative;
    z-index: 100;
    pointer-events: auto;
}

.page-number {
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #666;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    position: relative;
    z-index: 1;
    pointer-events: auto;
    user-select: none;
}

.page-number:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}

.page-number.active {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
}
</style>

<script>
// Global variables - EXACTLY like Library Management
let allCourses = [];
let filteredCourses = [];
let currentLibraryPage = 0;
let libraryItemsPerPage = 10;
let totalPages = 0;
let totalRecords = 0;

// Group courses by course code, title, year level, and term
function groupCoursesByInfo(courses) {
    const groups = {};
    
    courses.forEach(course => {
        const key = `${course.course_code}_${course.course_title}_${course.year_level}_${course.term}`;
        
        if (!groups[key]) {
            groups[key] = {
                course_code: course.course_code,
                course_title: course.course_title,
                year_level: course.year_level,
                term: course.term,
                academic_year_label: course.academic_year_label,
                programs: [],
                courses: []
            };
        }
        
        const programInfo = {
            code: course.program_code || course.program || 'N/A',
            color: course.program_color || '#1976d2'
        };
        
        const existingProgram = groups[key].programs.find(p => p.code === programInfo.code);
        if (!existingProgram) {
            groups[key].programs.push(programInfo);
        }
        
        groups[key].courses.push(course);
    });
    
    return Object.values(groups);
}

// Format merged programs with + indicator
function formatMergedPrograms(programs, courseCount) {
    if (!programs || programs.length === 0) {
        return '<span class="program-badge" style="background-color: #666;">N/A</span>';
    }
    
    if (programs.length === 1) {
        const program = programs[0];
        const color = program.color || '#1976d2';
        return `<span class="program-badge" style="background-color: ${color};">${program.code}</span>`;
    }
    
    // Show first program + count of additional programs
    const additionalCount = programs.length - 1;
    const firstProgram = programs[0];
    const firstProgramColor = firstProgram.color || '#1976d2';
    const otherPrograms = programs.slice(1).map(p => p.code).join(', ');
    return `
        <span class="program-badge" style="background-color: ${firstProgramColor};">${firstProgram.code}</span>
        <span class="program-badge additional-programs" 
              style="background-color: #6c757d;" 
              title="${otherPrograms}">+${additionalCount}</span>
    `;
}

// Load courses data from database
async function loadCoursesData() {
    try {
        
        // NO FILTERING - Quality Assurance shows ALL courses (both compliant and non-compliant)
        // Build params with NO filter parameters - just fetch everything
        const params = new URLSearchParams();
        
        
        // API path relative to content.php (which is in admin-quality_assurance/)
        const apiPath = 'api/get_courses_data.php';
        
        const response = await fetch(apiPath + '?' + params.toString());
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get response as text first to check if it's valid JSON
        const responseText = await response.text();
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid JSON response from server: ' + responseText.substring(0, 100));
        }
        
        
        if (result.success) {
            allCourses = result.data || [];
            filteredCourses = result.data || [];
            
            // Calculate pagination based on merged courses, not individual records
            const groupedCourses = groupCoursesByInfo(allCourses);
            const mergedCourses = Object.values(groupedCourses);
            totalRecords = mergedCourses.length;
            totalPages = Math.ceil(totalRecords / libraryItemsPerPage);
            
            // Store merged courses globally for display function
            window.currentMergedCourses = mergedCourses;
            
            // Debug: Log the raw API response
            
            // Debug: Count compliant vs non-compliant
            const compliantCount = allCourses.filter(c => c.status === 'Compliant').length;
            const nonCompliantCount = allCourses.filter(c => c.status === 'Non-Compliant').length;
            
            if (allCourses.length === 0) {
            }
            
            displayCourses();
        } else {
            console.error('Failed to load courses data:', result.message);
            alert('Failed to load courses data: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading courses data:', error);
        alert('Error loading courses data: ' + error.message);
        
        // Show error in table
        const tbody = document.getElementById('libraryTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #ef4444;">
                        Error loading data: ${error.message}
                    </td>
                </tr>
            `;
        }
    }
}

// Display courses function (matching librarian format)
function displayCourses() {
    const tbody = document.getElementById('libraryTableBody');
    
    if (!tbody) {
        console.error('Table body not found');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!allCourses || allCourses.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                No courses found
            </td>
        `;
        tbody.appendChild(row);
        updateLibraryPagination();
        return;
    }
    
    // Get merged courses (either from filter or from all courses)
    let mergedCourses;
    if (typeof window.currentMergedCourses !== 'undefined') {
        // Use merged courses from filter
        mergedCourses = window.currentMergedCourses;
    } else {
        // Group courses by course code, title, year level, and semester
        const groupedCourses = groupCoursesByInfo(allCourses);
        mergedCourses = Object.values(groupedCourses);
    }
    
    // Apply pagination to merged courses
    const startIndex = currentLibraryPage * libraryItemsPerPage;
    const endIndex = startIndex + libraryItemsPerPage;
    const paginatedCourses = mergedCourses.slice(startIndex, endIndex);
    
    // Debug: Log pagination info
    
    paginatedCourses.forEach(courseGroup => {
        const row = document.createElement('tr');
        row.className = 'course-group-row';
        row.onclick = () => {
            window.location.href = `content.php?page=course-details&course_code=${encodeURIComponent(courseGroup.course_code)}`;
        };
        
        // Format year level with ordinal numbers
        let yearLevel = 'N/A';
        if (courseGroup.year_level) {
            const year = parseInt(courseGroup.year_level);
            if (year === 1) yearLevel = '1st Year';
            else if (year === 2) yearLevel = '2nd Year';
            else if (year === 3) yearLevel = '3rd Year';
            else if (year === 4) yearLevel = '4th Year';
            else yearLevel = `${year}th Year`;
        }
        
        // Format term and academic year
        const term = courseGroup.term || 'N/A';
        const academicYearLabel = courseGroup.courses[0]?.academic_year_label || 'N/A';
        
        // Format term consistently (1st -> 1st Semester, etc.)
        let formattedTerm = term;
        if (term === '1st') formattedTerm = '1st Semester';
        else if (term === '2nd') formattedTerm = '2nd Semester';
        else if (term === 'summer') formattedTerm = 'Summer';
        
        // Create HTML structure matching librarian: two-line display
        const termAndYearHTML = `
            <div style="font-weight: 600; color: #1976d2; margin-bottom: 2px; font-size: 13px;">${formattedTerm}</div>
            <div style="font-size: 11px; color: #6c757d; font-weight: 500;">${academicYearLabel}</div>
        `;
        
        // Format programs (multiple programs merged)
        const programHTML = formatMergedPrograms(courseGroup.programs, courseGroup.courses.length);
        
        // Calculate compliant book count
        // Since courses can have the same course_code but different IDs,
        // we need to find the course with the actual book count
        // (matching how course details page uses the first course's ID)
        let totalBooks = 0;
        if (courseGroup.courses && courseGroup.courses.length > 0) {
            // Debug: Log all courses in the group
                id: c.id,
                course_code: c.course_code,
                compliant_book_count: c.compliant_book_count,
                book_count: c.book_count
            })));
            
            // Try to find a course with a non-zero compliant_book_count
            // First, try the first course (matches course details page behavior)
            let selectedCourse = courseGroup.courses[0];
            let maxCount = typeof selectedCourse.compliant_book_count === 'number' ? 
                          selectedCourse.compliant_book_count : 
                          (parseInt(selectedCourse.compliant_book_count) || 0);
            
            // Check all courses to find the maximum count (in case first one is 0)
            courseGroup.courses.forEach(course => {
                const count = typeof course.compliant_book_count === 'number' ? 
                             course.compliant_book_count : 
                             (parseInt(course.compliant_book_count) || 0);
                if (count > maxCount) {
                    maxCount = count;
                    selectedCourse = course;
                }
            });
            
            totalBooks = maxCount;
            
        }
        
        // Format: X / 5 (Red if < 5, Green if >= 5)
        const minRequiredBooks = 5;
        const isCompliant = totalBooks >= minRequiredBooks;
        const bookCountDisplay = `${totalBooks} / ${minRequiredBooks}`;
        const bookCountColor = isCompliant ? '#2e7d32' : '#FF4C4C';
        const bookCountBg = isCompliant ? '#e8f5e8' : '#ffeaea';
        
        // Helper function to escape HTML
        const escapeHtml = (text) => {
            if (!text) return 'N/A';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Get units from first course
        const units = courseGroup.courses[0]?.units !== undefined && courseGroup.courses[0]?.units !== null ? courseGroup.courses[0].units : 0;
        
        row.innerHTML = `
            <td class="course-code-cell">${escapeHtml(courseGroup.course_code)}</td>
            <td class="course-title-cell">${escapeHtml(courseGroup.course_title)}</td>
            <td class="units-cell">${units}</td>
            <td class="programs-cell">${programHTML}</td>
            <td class="term-year-cell">${termAndYearHTML}</td>
            <td class="year-level-cell">${yearLevel}</td>
            <td class="book-count-cell" style="text-align: center;">
                <span style="background: ${bookCountBg}; color: ${bookCountColor}; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">
                    ${bookCountDisplay}
                </span>
            </td>
            <td class="compliance-status-cell">
                <span class="compliance-badge ${isCompliant ? 'compliant' : 'non-compliant'}">${isCompliant ? 'Compliant' : 'Non-Compliant'}</span>
            </td>
            <td>
                <button class="view-books-btn" onclick="event.stopPropagation(); window.location.href='content.php?page=course-details&course_code=${encodeURIComponent(courseGroup.course_code)}'">View</button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    updateLibraryPagination();
}

// Update pagination (matching Library Management format)
function updateLibraryPagination() {
    const startItem = currentLibraryPage * libraryItemsPerPage + 1;
    const endItem = Math.min((currentLibraryPage + 1) * libraryItemsPerPage, totalRecords);
    
    // Update pagination info
    const paginationInfo = document.getElementById('paginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${totalRecords} items`;
    }
    
    // Update pagination buttons
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.disabled = currentLibraryPage === 0;
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentLibraryPage >= totalPages - 1;
    }
    
    // Update page numbers - Use event delegation ONLY (no onclick attributes)
    const pageNumbers = document.getElementById('pageNumbers');
    if (pageNumbers && totalPages > 1) {
        let pageNumbersHTML = '';
        const maxVisiblePages = Math.min(5, totalPages);
        let startPage = Math.max(0, currentLibraryPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages - 1, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(0, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentLibraryPage;
            pageNumbersHTML += `<button class="page-number ${isActive ? 'active' : ''}" data-page="${i}" type="button">${i + 1}</button>`;
        }
        
        pageNumbers.innerHTML = pageNumbersHTML;
        
        // IMMEDIATELY attach click handlers to each button
        pageNumbers.querySelectorAll('.page-number').forEach(function(btn) {
            const page = parseInt(btn.getAttribute('data-page'));
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (page >= 0 && page < totalPages) {
                    currentLibraryPage = page;
                    displayCourses();
                }
            }, true);
        });
    } else if (pageNumbers) {
        pageNumbers.innerHTML = '';
    }
}

// Change page function - EXACT copy from Library Management
function changePage(direction) {
    const newPage = currentLibraryPage + direction;
    
    if (newPage >= 0 && newPage < totalPages) {
        currentLibraryPage = newPage;
        displayCourses();
    }
}

// Go to specific page - EXACT copy from Library Management
function goToPage(page) {
    if (page >= 0 && page < totalPages) {
        currentLibraryPage = page;
        displayCourses();
    } else {
    }
}

// Filter library items (search only - no filtering)
function filterLibraryItems() {
    // Search functionality only - no filtering
    const searchTerm = document.getElementById('librarySearch')?.value?.toLowerCase() || '';
    // Search will be handled by displayCourses function
    displayCourses();
}

// Filter button removed - no filtering functionality

// Modal functions
function openNotifyLibrarianModal() {
  document.getElementById('notifyLibrarianModal').style.display = 'flex';
  document.getElementById('notifyStep1').style.display = '';
  document.getElementById('notifyStep2').style.display = 'none';
}

function closeNotifyLibrarianModal() {
  document.getElementById('notifyLibrarianModal').style.display = 'none';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // NO FILTERING - Quality Assurance shows ALL courses
    
    // Set up event delegation for page numbers - this WILL work
    const pageNumbers = document.getElementById('pageNumbers');
    if (pageNumbers) {
        pageNumbers.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('page-number')) {
                e.preventDefault();
                e.stopPropagation();
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page) && page >= 0 && page < totalPages) {
                    goToPage(page);
                }
            }
        });
    }
    
    loadCoursesData();
    
    // Modal close handlers
    const closeNotifyModal = document.getElementById('closeNotifyModal');
    const notifyCancelBtn = document.getElementById('notifyCancelBtn');
    const notifyDueCancelBtn = document.getElementById('notifyDueCancelBtn');
    
    if (closeNotifyModal) closeNotifyModal.onclick = closeNotifyLibrarianModal;
    if (notifyCancelBtn) notifyCancelBtn.onclick = closeNotifyLibrarianModal;
    if (notifyDueCancelBtn) notifyDueCancelBtn.onclick = closeNotifyLibrarianModal;
    
    const notifyConfirmBtn = document.getElementById('notifyConfirmBtn');
    if (notifyConfirmBtn) {
        notifyConfirmBtn.onclick = function() {
            document.getElementById('notifyStep1').style.display = 'none';
            document.getElementById('notifyStep2').style.display = '';
        };
    }
    
    const notifyDueConfirmBtn = document.getElementById('notifyDueConfirmBtn');
    if (notifyDueConfirmBtn) {
        notifyDueConfirmBtn.onclick = function() {
            closeNotifyLibrarianModal();
        };
    }
});
</script>