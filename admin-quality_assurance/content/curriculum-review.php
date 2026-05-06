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

  /* DESIGN.md Section 2.1 - fadeSlideUp animation */
  @keyframes fadeSlideUp {
    from {
      opacity: 0;
      transform: translateY(18px) scale(0.985);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  /* Apply fadeSlideUp to cards with staggered delay */
  .reference-request-card {
    animation: fadeSlideUp 0.45s ease-out both;
  }
  
  .reference-request-card:nth-child(1) { animation-delay: 0.08s; }
  .reference-request-card:nth-child(2) { animation-delay: 0.16s; }
  .reference-request-card:nth-child(3) { animation-delay: 0.24s; }
  .reference-request-card:nth-child(4) { animation-delay: 0.32s; }
  .reference-request-card:nth-child(5) { animation-delay: 0.40s; }
  .reference-request-card:nth-child(6) { animation-delay: 0.48s; }
  .reference-request-card:nth-child(7) { animation-delay: 0.56s; }
  .reference-request-card:nth-child(8) { animation-delay: 0.64s; }

  /* Lucide icon sizing - DESIGN.md Section 14.5 */
  .icon-lucide-16 {
    width: 16px;
    height: 16px;
  }
  
  .icon-lucide-20 {
    width: 20px;
    height: 20px;
  }

  /* Spin animation for loading */
  i[data-lucide].spin {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</style>

<script>
  // Curriculum requests data
  let curriculumRequests = [];
  let currentFilter = 'PENDING';

  // Load data from API
  function loadCurriculumData() {
    const grid = document.getElementById('curriculumRequestsGrid');
    if (!grid) return;

    grid.innerHTML = '<div style="width: 100%; text-align: center; padding: 40px 20px; color: #666; font-family: \'TT Interphases\', sans-serif;"><i data-lucide="loader-2" class="spin" style="width: 24px; height: 24px;"></i><br>Loading curriculum data...</div>';
    
    // Initialize Lucide for loading spinner
    setTimeout(() => {
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }, 0);

    fetch('api/get_qa_curriculum.php?status=' + currentFilter)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        grid.innerHTML = '';
        
        if (data.success && data.data && data.data.length > 0) {
          curriculumRequests = data.data;
          
          data.data.forEach((request, index) => {
            const cardElement = createCurriculumCard(request);
            cardElement.style.animationDelay = (0.08 + index * 0.08) + 's';
            grid.appendChild(cardElement);
          });
          
          // Initialize Lucide icons
          setTimeout(() => {
            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }
          }, 0);
        } else {
          grid.innerHTML = `
            <div style="width: 100%; text-align: center; padding: 40px 20px; color: #666; font-family: 'TT Interphases', sans-serif;">
              <i data-lucide="file-text" style="width: 48px; height: 48px; color: #999; margin-bottom: 12px;"></i>
              <h3 style="font-size: 18px; color: #333; margin-bottom: 6px;">No Curriculum Items Found</h3>
              <p style="font-size: 14px; color: #666;">No curriculum proposals found with status: ${currentFilter}</p>
            </div>
          `;
          setTimeout(() => {
            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }
          }, 0);
        }
      })
      .catch(error => {
        console.error('Error loading curriculum data:', error);
        grid.innerHTML = '<div style="width: 100%; text-align: center; padding: 40px 20px; color: #ef4444; font-family: \'TT Interphases\', sans-serif;">Error loading curriculum data.</div>';
      });
  }

  function filterCurriculum(status) {
    // Update active button state
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    const targetButton = document.querySelector(`.filter-btn[onclick*="${status}"]`);
    if (targetButton) {
      targetButton.classList.add('active');
    }

    currentFilter = status;
    loadCurriculumData();
  }

  function createCurriculumCard(request) {
    const card = document.createElement('div');
    card.className = 'reference-request-card';
    card.setAttribute('data-proposal-id', request.proposal_id);
    card.setAttribute('data-request', JSON.stringify(request));

    const departmentColor = request.department_color || '#1976d2';
    
    // Get program code
    let programCode = request.program_code || request.department_code || 'QA';
    
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
    const referencesCount = request.references_count || 0;
    const attachmentsCount = request.attachments_count || 0;
    
    // Build summary with Lucide icons (DESIGN.md Section 14 - MANDATORY)
    let summaryHTML = '<div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; flex-wrap: wrap;">';
    if (referencesCount > 0) {
      summaryHTML += `<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
        <i data-lucide="book-open" class="icon-lucide-16" style="color: #666;"></i>
        <span>${referencesCount} reference${referencesCount !== 1 ? 's' : ''}</span>
      </div>`;
    }
    if (attachmentsCount > 0) {
      summaryHTML += `<div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
        <i data-lucide="paperclip" class="icon-lucide-16" style="color: #666;"></i>
        <span>${attachmentsCount} attachment${attachmentsCount !== 1 ? 's' : ''}</span>
      </div>`;
    }
    summaryHTML += '</div>';

    // Date display
    const dateDisplay = request.submitted_at || request.approved_at || request.rejected_at || new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    const dateLabel = request.status === 'APPROVED' ? 'Approved on' : (request.status === 'REJECTED' ? 'Rejected on' : 'Submitted on');

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

      ${request.status === 'PENDING' || request.status === 'Pending QA Review' ? `
        <div class="action-buttons">
          <button class="approve-btn" onclick="handleApprove(${request.proposal_id}, this)">
            <i data-lucide="check-circle" class="icon-lucide-16"></i> Approve
          </button>
          <button class="reject-btn" onclick="handleReject(${request.proposal_id}, this)">
            <i data-lucide="x-circle" class="icon-lucide-16"></i> Reject
          </button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">
          <i data-lucide="eye" class="icon-lucide-16"></i> View Details
        </button>
        <div class="request-date">${dateLabel}: ${dateDisplay}</div>
      ` : request.status === 'APPROVED' ? `
        <div class="action-buttons">
          <button class="status-approved-btn" disabled>
            <i data-lucide="check-circle" class="icon-lucide-16"></i> Approved
          </button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">
          <i data-lucide="eye" class="icon-lucide-16"></i> View Details
        </button>
        <div class="request-date">${dateLabel}: ${dateDisplay}</div>
      ` : `
        <div class="action-buttons">
          <button class="status-rejected-btn" disabled>
            <i data-lucide="x-circle" class="icon-lucide-16"></i> Rejected
          </button>
        </div>
        <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)" style="width: 100%; padding: 8px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: 'TT Interphases', sans-serif; margin-top: 8px; margin-bottom: 4px;">
          <i data-lucide="eye" class="icon-lucide-16"></i> View Details
        </button>
        <div class="request-date">${dateLabel}: ${dateDisplay}</div>
      `}
    `;

    return card;
  }

  // Handle approve action
  function handleApprove(proposalId, button) {
    if (!confirm('Are you sure you want to approve this curriculum proposal?')) {
      return;
    }
    
    button.disabled = true;
    button.innerHTML = '<i data-lucide="loader-2" class="icon-lucide-16 spin"></i> Approving...';
    
    fetch('api/approve_curriculum.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ proposal_id: proposalId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Curriculum proposal approved successfully!');
        loadCurriculumData(); // Reload to show updated status
      } else {
        alert('Failed to approve: ' + (data.message || 'Unknown error'));
        button.disabled = false;
        button.innerHTML = '<i data-lucide="check-circle" class="icon-lucide-16"></i> Approve';
      }
    })
    .catch(error => {
      console.error('Error approving proposal:', error);
      alert('Error approving proposal. Please try again.');
      button.disabled = false;
      button.innerHTML = '<i data-lucide="check-circle" class="icon-lucide-16"></i> Approve';
    });
  }

  // Handle reject action
  function handleReject(proposalId, button) {
    const reason = prompt('Please enter the rejection reason:');
    if (!reason || reason.trim() === '') {
      alert('Rejection reason is required.');
      return;
    }
    
    button.disabled = true;
    button.innerHTML = '<i data-lucide="loader-2" class="icon-lucide-16 spin"></i> Rejecting...';
    
    fetch('api/reject_curriculum.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ 
        proposal_id: proposalId,
        reason: reason.trim()
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Curriculum proposal rejected.');
        loadCurriculumData(); // Reload to show updated status
      } else {
        alert('Failed to reject: ' + (data.message || 'Unknown error'));
        button.disabled = false;
        button.innerHTML = '<i data-lucide="x-circle" class="icon-lucide-16"></i> Reject';
      }
    })
    .catch(error => {
      console.error('Error rejecting proposal:', error);
      alert('Error rejecting proposal. Please try again.');
      button.disabled = false;
      button.innerHTML = '<i data-lucide="x-circle" class="icon-lucide-16"></i> Reject';
    });
  }

  // Open curriculum details modal
  function openCurriculumDetailsModal(button) {
    const card = button.closest('.reference-request-card');
    const requestData = card.getAttribute('data-request');
    if (requestData) {
      const request = JSON.parse(requestData);
      alert('View Details for: ' + request.course_code + ' - ' + request.course_name + '\n\nFull implementation would show detailed modal with course information, references, and attachments.');
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadCurriculumData();
  });
</script>

<!-- Note: Include the Curriculum Details Modal from dashboard.php -->
<!-- The modal HTML structure is defined in admin-quality_assurance/content/dashboard.php -->
<!-- For full functionality, either include that modal or create a shared modal component -->
