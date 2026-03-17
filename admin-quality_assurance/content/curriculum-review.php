<!-- Curriculum Review and Approval - View All Page -->
<div class="back-navigation">
  <button class="back-button" onclick="window.location.href='content.php?page=dashboard'">
    <img src="../src/assets/icons/go-back-icon.png" alt="Back">
    Back to Dashboard
  </button>
</div>

<div class="header-section">
  <div class="header-content">
    <h1 class="main-page-title">Curriculum Review and Approval</h1>
    <p class="page-description">Review and manage curriculum and course compliance items assigned to Quality Assurance.</p>
  </div>
  <div class="filter-buttons">
    <button class="filter-btn active" onclick="filterCurriculum('PENDING')">Pending</button>
    <button class="filter-btn" onclick="filterCurriculum('APPROVED')">Approved</button>
    <button class="filter-btn" onclick="filterCurriculum('REJECTED')">Rejected</button>
  </div>
</div>

<div class="reference-requests-container">
  <div class="reference-requests-grid" id="curriculumRequestsGrid">
    <!-- Curriculum review request cards will be injected here -->
  </div>
</div>

<style>
  .back-navigation {
      margin-bottom: 20px;
  }

  .back-button {
      background: #1976d2;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
  }

  .back-button:hover {
      background: #1565c0;
  }

  .back-button img {
      width: 16px;
      height: 16px;
  }

  .header-section {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0px;
  }

  .header-content {
      flex: 1;
  }

  .filter-buttons {
      display: flex;
      gap: 10px;
      align-items: center;
  }

  .filter-btn {
      padding: 8px 16px;
      border: 1px solid #e0e0e0;
      background: white;
      color: #666;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'TT Interphases', sans-serif;
  }

  .filter-btn:hover {
      background: #f5f5f5;
      border-color: #1976d2;
      color: #1976d2;
  }

  .filter-btn.active {
      background: #1976d2;
      color: white;
      border-color: #1976d2;
  }

  .main-page-title {
      font-size: 20px;
      font-weight: 600;
      color: #333;
      margin: 0 !important;
      padding: 0 !important;
      font-family: 'TT Interphases', sans-serif;
      line-height: 1.2;
  }

  .page-description {
      font-size: 14px;
      color: #666;
      margin: 5px 0 0px 0;
      font-family: 'TT Interphases', sans-serif;
      line-height: 1.4;
  }

  .reference-requests-container {
      margin-top: 20px;
      width: 100%;
      max-width: none;
  }

  .reference-requests-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-top: 20px;
      width: 100%;
  }

  .reference-request-card {
      width: 100%;
      min-width: 250px;
      padding: 20px;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: hidden;
      box-sizing: border-box;
  }

  .reference-request-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
      border-color: #1976d2;
  }

  .request-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 5px;
      padding-bottom: 5px;
  }

  .requester-info {
      flex: 1;
  }

  .requester-name {
      font-weight: 600;
      color: #333;
      font-size: 14px;
      margin-bottom: 4px;
      font-family: 'TT Interphases', sans-serif;
  }

  .faculty-department {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 0px;
      font-family: 'TT Interphases', sans-serif;
      padding: 0;
      display: inline-block;
  }

  .course-info {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      background: #f5f5f5;
      padding: 12px;
      border-radius: 6px;
  }

  .course-code {
      background: #1976d2;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      font-family: 'TT Interphases', sans-serif;
  }

  .course-name {
      font-size: 13px;
      color: #666;
      font-family: 'TT Interphases', sans-serif;
      flex: 1;
  }

  .request-summary {
      margin-bottom: 12px;
  }

  .request-type {
      font-size: 11px;
      color: #666;
      margin-bottom: 8px;
      font-family: 'TT Interphases', sans-serif;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      background: #f0f0f0;
      padding: 4px 8px;
      border-radius: 4px;
      display: inline-block;
  }

  .material-title {
      font-size: 13px;
      color: #333;
      line-height: 1.5;
      font-style: italic;
      font-family: 'TT Interphases', sans-serif;
  }

  .action-buttons {
      display: flex;
      gap: 8px;
      margin-top: auto;
      margin-bottom: 4px;
  }

  .approve-btn,
  .reject-btn,
  .status-approved-btn,
  .status-rejected-btn {
      flex: 1;
      padding: 8px 12px;
      border: none;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: 'TT Interphases', sans-serif;
      text-align: center;
      white-space: nowrap;
  }

  .approve-btn {
      background: #4CAF50;
      color: white;
  }

  .approve-btn:hover {
      background: #45a049;
  }

  .reject-btn {
      background: #f44336;
      color: white;
  }

  .reject-btn:hover {
      background: #e53935;
  }

  .status-approved-btn {
      background: #4CAF50;
      color: white;
  }

  .status-rejected-btn {
      background: #f44336;
      color: white;
  }

  .request-date {
      font-size: 11px;
      color: #999;
      text-align: center;
      margin-top: 2px;
      margin-bottom: 2px;
  }
</style>

<script>
  // Placeholder data array; can be replaced with real API data later
  let curriculumRequests = [];

  function filterCurriculum(status) {
    // Update active button state
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    const targetButton = document.querySelector(`.filter-btn[onclick*="${status}"]`);
    if (targetButton) {
      targetButton.classList.add('active');
    }

    const grid = document.getElementById('curriculumRequestsGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const filtered = curriculumRequests.filter(r => r.status === status);
    filtered.forEach(request => {
      grid.appendChild(createCurriculumCard(request));
    });
  }

  function createCurriculumCard(request) {
    const card = document.createElement('div');
    card.className = 'reference-request-card';
    // Store request data in the card element
    card.setAttribute('data-request', JSON.stringify(request));

    const departmentColor = request.department_color || '#1976d2';
    
    // Get program code from programs array (first program)
    let programCode = 'QA';
    if (request.programs && request.programs.length > 0) {
      programCode = request.programs[0].code || request.programs[0].program_code || 'QA';
    } else if (request.program_code) {
      programCode = request.program_code;
    } else if (request.department_code) {
      programCode = request.department_code;
    }
    
    // Get course type
    const courseType = request.course_type || request.type || 'New Course Proposal';
    const courseTypeMap = {
      'new': 'New Course Proposal',
      'revision': 'Course Revision',
      'cross-department': 'Cross-Department',
      'New Course Proposal': 'New Course Proposal',
      'Course Revision': 'Course Revision',
      'Cross-Department': 'Cross-Department'
    };
    const displayCourseType = courseTypeMap[courseType] || courseType;
    
    // Count references and attachments
    const referencesCount = request.references ? (Array.isArray(request.references) ? request.references.length : 0) : 0;
    const attachmentsCount = request.attachments ? (Array.isArray(request.attachments) ? request.attachments.length : 0) : 0;
    
    // Build summary with icons
    let summaryHTML = '<div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; flex-wrap: wrap;">';
    if (referencesCount > 0) {
      summaryHTML += `<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
        <span style="font-size: 14px;">📚</span>
        <span>${referencesCount} reference${referencesCount !== 1 ? 's' : ''}</span>
      </div>`;
    }
    if (attachmentsCount > 0) {
      summaryHTML += `<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
        <span style="font-size: 14px;">📎</span>
        <span>${attachmentsCount} attachment${attachmentsCount !== 1 ? 's' : ''}</span>
      </div>`;
    }
    summaryHTML += '</div>';

    card.innerHTML = `
      <div class="request-header">
        <div class="requester-info">
          <div class="requester-name">${request.requester_name || 'Department Dean'}</div>
          <div class="faculty-department" style="color: ${departmentColor};">${programCode} PROGRAM</div>
        </div>
      </div>

      <div class="course-info">
        <div class="course-code">${request.course_code || 'COURSE'}</div>
        <div class="course-name">${request.course_name || 'Course Title'}</div>
      </div>

      <div class="request-summary">
        <div class="request-type">${displayCourseType}</div>
        ${summaryHTML}
      </div>

      ${request.status === 'PENDING' ? `
        <div class="action-buttons">
          <button class="approve-btn">Approve</button>
          <button class="reject-btn">Reject</button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">View Details</button>
        <div class="request-date">Submitted on: ${request.date || new Date().toLocaleDateString()}</div>
      ` : request.status === 'APPROVED' ? `
        <div class="action-buttons">
          <button class="status-approved-btn" disabled>Approved</button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">View Details</button>
        <div class="request-date">Approved on: ${request.date || new Date().toLocaleDateString()}</div>
      ` : `
        <div class="action-buttons">
          <button class="status-rejected-btn" disabled>Rejected</button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">View Details</button>
        <div class="request-date">Rejected on: ${request.date || new Date().toLocaleDateString()}</div>
      `}
    `;

    return card;
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Dummy data for demonstration
    curriculumRequests = [
      {
        requester_name: 'Dr. Philipcris Encarnacion',
        dean_name: 'Dr. Philipcris Encarnacion',
        department_code: 'CS',
        department_name: 'College of Computing Studies',
        department_color: '#1976d2',
        course_code: 'CS101',
        course_name: 'Introduction to Computer Science',
        units: '3',
        lecture_hours: '2',
        laboratory_hours: '3',
        prerequisites: 'None',
        year_level: '1st Year',
        term: '1st Semester',
        programs: [
          { code: 'BSCS', name: 'Bachelor of Science in Computer Science' },
          { code: 'BSIT', name: 'Bachelor of Science in Information Technology' }
        ],
        course_type: 'New Course Proposal',
        references: [
          { title: 'Introduction to Programming', author: 'Smith, J.' },
          { title: 'Data Structures and Algorithms', author: 'Johnson, M.' }
        ],
        attachments: [
          { name: 'course_syllabus.pdf', url: '#', filename: 'course_syllabus.pdf' },
          { name: 'sample_projects.zip', url: '#', filename: 'sample_projects.zip' },
          { name: 'assessment_rubric.docx', url: '#', filename: 'assessment_rubric.docx' }
        ],
        status: 'PENDING',
        date: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
      }
    ];
    
    // Load initial data
    filterCurriculum('PENDING');
  });
  
  // Note: The modal HTML and openCurriculumDetailsModal function should be included from dashboard.php
  // For now, adding a basic implementation
  function openCurriculumDetailsModal(button) {
    // This function should open the curriculum details modal
    // The modal HTML and full implementation are in dashboard.php
    // For this page to work fully, include the modal from dashboard or create a shared modal component
    alert('Modal functionality - Please include the modal from dashboard.php or create a shared component');
  }
</script>

<!-- Note: Include the Curriculum Details Modal from dashboard.php -->
<!-- The modal HTML structure is defined in admin-quality_assurance/content/dashboard.php -->
<!-- For full functionality, either include that modal or create a shared modal component -->
