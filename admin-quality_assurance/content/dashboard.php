<style>
  /* Match department-dean dashboard card + header styling */
  .dashboard-section {
    margin-top: 30px;
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    width: 100%;
    box-sizing: border-box;
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0;
    padding: 0;
    transition: all 0.3s ease;
    align-items: center;
  }

  .header-left h3 {
    margin: 0 0 8px 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
  }

  .section-description {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    align-self: flex-end;
  }

  .view-all-btn {
    display: inline-block;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0px;
    align-self: flex-start;
  }

  .view-all-btn:hover {
    background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
  }

  .section-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 0px;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
  }

  .collapse-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
  }

  .collapse-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
  }

  .collapse-icon {
    width: 14px;
    height: 14px;
    object-fit: contain;
    filter: brightness(0) saturate(100%);
    transition: filter 0.2s ease;
  }

  .collapse-btn:hover .collapse-icon,
  .expand-btn:hover .collapse-icon {
    filter: brightness(0) saturate(100%) invert(1);
  }

  .collapsed-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
  }

  .request-count-badge {
    background: #ff4c4c;
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }

  .expand-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
  }

  .expand-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
  }

  /* Curriculum Review Card Styles */
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

  .view-details-btn {
    width: 100%;
    padding: 8px 12px;
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    margin-top: 8px;
    margin-bottom: 4px;
  }

  .view-details-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
  }

  /* Curriculum Details Modal Styles */
  #curriculumDetailsModal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
  }

  #curriculumDetailsModal .modal-box {
    background-color: #fff;
    margin: auto;
    padding: 0px;
    border: none;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  #curriculumDetailsModal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0px;
    border-bottom: 1px solid #e0e0e0;
    position: sticky;
    top: 0;
    background: transparent;
    z-index: 10;
    flex-shrink: 0;
  }

  #curriculumDetailsModal .modal-header > div {
    padding: 0px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
  }

  #curriculumDetailsModal .modal-header h2 {
    margin: 0;
    padding: 0;
    font-size: 24px;
    font-weight: 700;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .close-button {
    color: #aaa;
    font-size: 32px;
    font-weight: 700;
    cursor: pointer;
    transition: color 0.2s;
    line-height: 1;
    background: none;
    border: none;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  #curriculumDetailsModal .close-button:hover {
    color: #333;
  }

  #curriculumDetailsModal .modal-content {
    padding: 0px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
  }

  #curriculumDetailsModal .modal-content .review-section {
    margin: 0px;
  }

  #curriculumDetailsModal .modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e0e0e0;
    background: #fff;
    position: sticky;
    bottom: 0;
    z-index: 10;
    flex-shrink: 0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
  }

  #curriculumDetailsModal .modal-footer-btn {
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .modal-footer-approve-btn {
    background: #4CAF50;
    color: white;
  }

  #curriculumDetailsModal .modal-footer-approve-btn:hover {
    background: #45a049;
  }

  #curriculumDetailsModal .modal-footer-reject-btn {
    background: #f44336;
    color: white;
  }

  #curriculumDetailsModal .modal-footer-reject-btn:hover {
    background: #e53935;
  }

  /* Attachment buttons styling */
  #curriculumDetailsModal .attachment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    margin-bottom: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
  }

  #curriculumDetailsModal .attachment-item:last-child {
    margin-bottom: 0;
  }

  #curriculumDetailsModal .attachment-name {
    flex: 1;
    font-size: 14px;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .attachment-actions {
    display: flex;
    gap: 8px;
  }

  #curriculumDetailsModal .attachment-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .attachment-view-btn {
    background: #1976d2;
    color: white;
  }

  #curriculumDetailsModal .attachment-view-btn:hover {
    background: #1565c0;
  }

  #curriculumDetailsModal .attachment-download-btn {
    background: #4CAF50;
    color: white;
  }

  #curriculumDetailsModal .attachment-download-btn:hover {
    background: #45a049;
  }

  /* Review Section - Match department dean styling */
  #curriculumDetailsModal .review-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 0px;
    border: none;
    border-top: 1px solid #e0e0e0;
  }

  #curriculumDetailsModal .review-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    padding-left: 0px;
    padding-right: 0px;
    border-bottom: 1px solid #e0e0e0;
  }

  #curriculumDetailsModal .review-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  #curriculumDetailsModal .review-item.full-width {
    display: flex;
    flex-direction: column;
  }

  #curriculumDetailsModal .review-item strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .review-item span {
    color: #666;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .review-text {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    background: white;
    padding: 10px;
    border-radius: 4px;
    margin-top: 5px;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .detail-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
  }

  #curriculumDetailsModal .badge-pending {
    background: #fff3cd;
    color: #856404;
  }

  #curriculumDetailsModal .badge-approved {
    background: #d4edda;
    color: #155724;
  }

  #curriculumDetailsModal .badge-rejected {
    background: #f8d7da;
    color: #721c24;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; margin-top: 0;">
  <div>
    <p style="color: #666; margin: 0;">
      <span style="font-size: 24px; font-weight: bold; font-family: 'TT Interphases', sans-serif; color: #111;">Academic Standards Compliance Monitoring</span><br>
      <span id="date-indicator" style="font-size: 16px;">Loading...</span>
    </p>
  </div>
  
  <div style="display: flex; align-items: center; gap: 10px;">
    <span style="font-weight: bold; color: #333;">Academic Term:</span>
    <select id="academicTermSelect" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
      <option value="all">All Terms (Current Academic Year)</option>
    </select>
    <button id="currentTermBtn" style="background-color: #739AFF; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer;">Current Term</button>
  </div>
</div> 

<!-- Compliance Status Card -->
<div style="width: 100%; background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 24px 18px 24px; margin-bottom: 30px; box-sizing: border-box;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
    <div>
      <div id="compliance-status-title" style="font-size: 1.6rem; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif;">All Terms (Current Academic Year) Compliance Status</div>
      <div style="font-size: 1rem; color: #6b7280; font-family: 'TT Interphases', sans-serif; margin-top: 2px;">Academic standards compliance</div>
    </div>
    <div style="display: flex; flex-direction: column; align-items: flex-end; min-width: 90px;">
      <span id="compliance-percentage" style="font-size: 2rem; font-weight: bold; color: #22c55e; font-family: 'TT Interphases', sans-serif; line-height: 1;">0%</span>
      <span style="font-size: 1rem; color: #6b7280; font-family: 'TT Interphases', sans-serif; margin-top: 2px;">Compliant</span>
    </div>
  </div>
  <div style="width: 100%; height: 14px; background: #f3f4f6; border-radius: 7px; overflow: hidden; margin-top: 8px;">
    <div id="compliance-progress-bar" style="width: 0%; height: 100%; background: #111; border-radius: 7px; transition: width 1.2s cubic-bezier(0.4,0,0.2,1);"></div>
  </div>
</div>
<script>
  // Global variables
  let currentStats = {
    compliancePercentage: 0,
    compliantCourses: 0,
    nonCompliantCourses: 0,
    totalCourses: 0,
    improvement: 0,
    termDisplayName: 'All Terms (Current Academic Year)',
    dateRange: 'Loading...'
  };
  
  let academicTerms = [];
  let currentAcademicTerm = null;

  // Animate number function
    function animateNumber(element, target, suffix = '', duration = 1200) {
    // Always start from 0 for fresh animations
      let start = 0;
    // If element already has a numeric value and we want to animate from it, use it
    // Otherwise force start from 0
    const currentText = element.textContent.trim();
    const currentNum = parseInt(currentText.replace(/[^0-9]/g, '')) || 0;
    // Only use current value if it's not 0 (to allow re-animations when term changes)
    if (currentNum > 0 && currentText !== '0' && currentText !== '0%') {
      start = currentNum;
    }
    
    if (typeof target === 'string') {
      target = parseInt(target.replace(/[^0-9]/g, '')) || 0;
    }
      let startTime = null;
      function updateNumber(timestamp) {
        if (!startTime) startTime = timestamp;
        let progress = Math.min((timestamp - startTime) / duration, 1);
        let value = Math.floor(progress * (target - start) + start);
        element.textContent = value + suffix;
        if (progress < 1) {
          requestAnimationFrame(updateNumber);
        } else {
          element.textContent = target + suffix;
        }
      }
      requestAnimationFrame(updateNumber);
    }

  // Update compliance statistics
  function updateComplianceStats(stats) {
    currentStats = stats;
    
    const progressBar = document.getElementById('compliance-progress-bar');
    const percentElem = document.getElementById('compliance-percentage');
    const compliantElem = document.getElementById('compliant-courses');
    const nonCompliantElem = document.getElementById('non-compliant-courses');
    const totalElem = document.getElementById('total-courses');
    const statusTitle = document.getElementById('compliance-status-title');
    const dateIndicator = document.getElementById('date-indicator');
    
    // Update date indicator
    if (dateIndicator && stats.dateRange) {
      dateIndicator.textContent = stats.dateRange;
    }
    
    // Update title
    if (statusTitle) {
      statusTitle.textContent = stats.termDisplayName + ' Compliance Status';
    }
    
    // Update compliance percentage
    const compliancePercent = stats.compliancePercentage || 0;
    const isCompliant = compliancePercent >= 70; // Green if >= 70%, red otherwise
    percentElem.style.color = isCompliant ? '#22c55e' : '#ef4444';
    percentElem.textContent = compliancePercent + '%';
    
    // Update progress bar
    setTimeout(function() {
      progressBar.style.width = compliancePercent + '%';
      progressBar.style.background = isCompliant ? '#22c55e' : '#111';
    }, 200);
    
    // Animate numbers
    animateNumber(percentElem, compliancePercent, '%');
    animateNumber(compliantElem, stats.compliantCourses || 0);
    animateNumber(nonCompliantElem, stats.nonCompliantCourses || 0);
    animateNumber(totalElem, stats.totalCourses || 0);
    
    // Update improvement (from API - compares with previous period)
    const improvementElem = document.getElementById('improvement');
    if (improvementElem) {
      const improvement = stats.improvement || 0;
      // Display absolute value with % sign, but keep the sign for color coding if needed
      const improvementText = Math.abs(improvement).toFixed(1) + '%';
      animateNumber(improvementElem, Math.abs(improvement), '%');
    }
  }

  // Fetch and render department compliance statistics
  function fetchDepartmentCompliance(termValue = 'all') {
    const apiUrl = 'api/get_department_compliance.php?term=' + encodeURIComponent(termValue);
    console.log('Fetching department compliance from:', apiUrl);
    
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Department compliance API response:', data);
        if (data.success) {
          renderDepartmentCards(data.data);
        } else {
          console.error('Error fetching department compliance:', data.message);
          document.getElementById('departmentCardsContainer').innerHTML = 
            '<div style="width: 100%; text-align: center; padding: 20px; color: #ef4444; font-family: \'TT Interphases\', sans-serif;">Error loading department compliance data.</div>';
        }
      })
      .catch(error => {
        console.error('Error fetching department compliance:', error);
        document.getElementById('departmentCardsContainer').innerHTML = 
          '<div style="width: 100%; text-align: center; padding: 20px; color: #ef4444; font-family: \'TT Interphases\', sans-serif;">Error loading department compliance data.</div>';
      });
  }
  
  // Render department cards
  function renderDepartmentCards(departments) {
    const container = document.getElementById('departmentCardsContainer');
    
    if (!departments || departments.length === 0) {
      container.innerHTML = 
        '<div style="width: 100%; text-align: center; padding: 20px; color: #6b7280; font-family: \'TT Interphases\', sans-serif;">No department data available for the selected term.</div>';
      return;
    }
    
    container.innerHTML = departments.map(dept => {
      // Determine text color for department badge based on background color
      const bgColor = dept.color_code || '#1976d2';
      // Simple check: if color is light, use dark text; otherwise use white text
      const rgb = hexToRgb(bgColor);
      const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
      const textColor = brightness > 128 ? '#111' : '#fff';
      
      const compliancePercent = dept.compliance_percentage || 0;
      const progressBarColor = compliancePercent >= 70 ? '#22c55e' : '#111';
      
      return `
        <div style="background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); padding: 22px 28px 16px 28px; flex: 1 1 350px; max-width: 48%; display: flex; flex-direction: column; margin-bottom: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 16px;">
              <span style="background: ${bgColor}; color: ${textColor}; font-weight: bold; font-size: 1rem; border-radius: 8px; padding: 4px 16px; font-family: 'TT Interphases', sans-serif;">${dept.department_code}</span>
              <span style="font-size: 1.25rem; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif;">${compliancePercent}% Compliant</span>
            </div>
            <div style="font-size: 1rem; color: #374151; font-family: 'TT Interphases', sans-serif; font-weight: 500;">${dept.compliant_courses}/${dept.total_courses} courses</div>
          </div>
          <div style="width: 100%; height: 10px; background: #f3f4f6; border-radius: 5px; overflow: hidden; margin-bottom: 8px;">
            <div style="width: ${compliancePercent}%; height: 100%; background: ${progressBarColor}; border-radius: 5px; transition: width 0.6s ease-in-out;"></div>
          </div>
          <div style="font-size: 1rem; color: #6b7280; font-family: 'TT Interphases', sans-serif;">${dept.courses_needing_attention} course${dept.courses_needing_attention !== 1 ? 's' : ''} need attention</div>
        </div>
      `;
    }).join('');
  }
  
  // Helper function to convert hex color to RGB
  function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16)
    } : { r: 25, g: 118, b: 210 }; // Default blue
  }

  // Fetch compliance statistics from API
  function fetchComplianceStats(termValue = 'all') {
    const apiUrl = 'api/get_compliance_stats.php?term=' + encodeURIComponent(termValue);
    console.log('Fetching compliance stats from:', apiUrl);
    fetch(apiUrl)
      .then(response => {
        console.log('Compliance stats API response status:', response.status);
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Compliance stats API response:', data);
        if (data.debug) {
          console.log('🔍 DEBUG INFO:');
          console.log('  - Total courses in DB:', data.debug.total_courses_in_db || 0);
          console.log('  - All courses in DB:', data.debug.all_courses_details || []);
          console.log('  - Courses found by query:', data.debug.courses_found?.length || 0);
          console.log('  - Courses details:', data.debug.courses_found);
          console.log('  - Term filter:', data.debug.term_filter);
          console.log('  - Calculated total:', data.debug.calculated_total);
          console.log('  - Actual total:', data.debug.actual_total);
          if (data.debug.total_courses_in_db !== data.debug.courses_found?.length) {
            console.warn('⚠️ MISMATCH: Query found', data.debug.courses_found?.length, 'courses but there are', data.debug.total_courses_in_db, 'courses in DB!');
          }
        }
        if (data.success) {
          console.log('📊 STATS RECEIVED:');
          console.log('  - Total Courses:', data.data.total_courses);
          console.log('  - Compliant Courses:', data.data.compliant_courses);
          console.log('  - Non-Compliant Courses:', data.data.non_compliant_courses);
          console.log('  - Compliance %:', data.data.compliance_percentage);
          updateComplianceStats({
            compliancePercentage: data.data.compliance_percentage,
            compliantCourses: data.data.compliant_courses,
            nonCompliantCourses: data.data.non_compliant_courses,
            totalCourses: data.data.total_courses,
            improvement: data.data.improvement || 0,
            termDisplayName: data.data.term_display_name,
            dateRange: data.data.date_range || 'Date range not available'
          });
          
          // Also fetch department compliance when stats are updated
          fetchDepartmentCompliance(termValue);
        } else {
          console.error('Error fetching compliance stats:', data.message);
        }
      })
      .catch(error => {
        console.error('Error fetching compliance stats:', error);
      });
  }

  // Load academic terms into dropdown
  function loadAcademicTerms() {
    console.log('Loading academic terms...');
    // Try different path options (since dashboard is included via PHP, paths are relative to content.php)
    const apiPaths = [
      'api/get_academic_terms.php',
      '../api/get_academic_terms.php',
      '../../admin-quality_assurance/api/get_academic_terms.php'
    ];
    
    let apiUrl = apiPaths[0];
    console.log('Loading academic terms from:', apiUrl);
    fetch(apiUrl)
      .then(response => {
        console.log('API response status:', response.status);
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Academic terms API response:', data);
        const select = document.getElementById('academicTermSelect');
        if (!select) {
          console.error('Academic term select element not found!');
          return;
        }
        
        if (data.success) {
          if (data.terms && data.terms.length > 0) {
            // Store terms globally
            academicTerms = data.terms;
            
            // Clear existing options except "All Terms"
            select.innerHTML = '<option value="all">All Terms (Current Academic Year)</option>';
            
                      // Add terms from database
          data.terms.forEach(term => {
            const option = document.createElement('option');
            option.value = term.value;
            option.textContent = term.label;
            option.setAttribute('data-term-name', term.term_name || '');
            option.setAttribute('data-school-year', term.school_year_label || '');
            option.setAttribute('data-start-date', term.start_date || '');
            option.setAttribute('data-end-date', term.end_date || '');
            select.appendChild(option);
          });
          
          console.log('Added', data.terms.length, 'terms to dropdown');
          
          // Set initial date indicator for "All Terms" if available
          if (data.all_terms_date_range) {
            const dateIndicator = document.getElementById('date-indicator');
            if (dateIndicator) {
              dateIndicator.textContent = data.all_terms_date_range;
            }
          }
            
            // Find current active term (first term or one with is_active = 1)
            if (academicTerms.length > 0) {
              // Try to find active term
              const activeTerm = academicTerms.find(t => t.status == 1 || t.status === '1' || t.status === true);
              if (activeTerm) {
                currentAcademicTerm = activeTerm;
                console.log('Found active term:', activeTerm);
              } else {
                // Use first term as default
                currentAcademicTerm = academicTerms[0];
                console.log('Using first term as default:', currentAcademicTerm);
              }
            }
          } else {
            console.warn('No terms found in API response. Debug info:', data.debug);
          }
        } else {
          console.error('API returned error:', data.message);
          if (data.error_details) {
            console.error('Error details:', data.error_details);
          }
        }
      })
      .catch(error => {
        console.error('Error loading academic terms with path:', apiUrl, error);
        // Try other paths as fallback
        let pathIndex = 1;
        function tryNextPath() {
          if (pathIndex < apiPaths.length) {
            apiUrl = apiPaths[pathIndex];
            console.log('Trying alternative path:', apiUrl);
            fetch(apiUrl)
              .then(response => response.json())
              .then(data => {
                console.log('Fallback API response:', data);
                // Process the response (same as above)
                const select = document.getElementById('academicTermSelect');
                if (select && data.success && data.terms && data.terms.length > 0) {
                  academicTerms = data.terms;
                  select.innerHTML = '<option value="all">All Terms (Current Academic Year)</option>';
                  data.terms.forEach(term => {
                    const option = document.createElement('option');
                    option.value = term.value;
                    option.textContent = term.label;
                    select.appendChild(option);
                  });
                  if (academicTerms.length > 0) {
                    const activeTerm = academicTerms.find(t => t.status == 1 || t.status === '1' || t.status === true);
                    currentAcademicTerm = activeTerm || academicTerms[0];
                  }
                }
              })
              .catch(fallbackError => {
                console.error('Path', apiUrl, 'also failed:', fallbackError);
                pathIndex++;
                tryNextPath();
              });
          } else {
            console.error('All API paths failed. Please check the API endpoint.');
          }
        }
        tryNextPath();
      });
  }

  // Set current term button functionality
  function setCurrentTerm() {
    const select = document.getElementById('academicTermSelect');
    if (!select || !currentAcademicTerm) {
      alert('No current term is active in the system.');
      return;
    }
    
    // Set to current term
    select.value = currentAcademicTerm.id;
    select.dispatchEvent(new Event('change'));
  }
  
  // Update current term button state
  function updateCurrentTermButtonState() {
    const currentTermBtn = document.getElementById('currentTermBtn');
    const select = document.getElementById('academicTermSelect');
    if (!currentTermBtn || !select) return;
    
    const selectedValue = select.value;
    
    // Disable if current term is selected or if there's no current term
    if (!currentAcademicTerm) {
      currentTermBtn.disabled = true;
      currentTermBtn.style.cursor = 'not-allowed';
      currentTermBtn.title = 'No current term available';
    } else if (selectedValue === 'all') {
      // Enable when "All Terms" is selected
      currentTermBtn.disabled = false;
      currentTermBtn.style.cursor = 'pointer';
      currentTermBtn.title = 'Jump to current term';
    } else if (selectedValue == currentAcademicTerm.id) {
      currentTermBtn.disabled = true;
      currentTermBtn.style.cursor = 'not-allowed';
      currentTermBtn.title = 'Already viewing current term';
    } else {
      currentTermBtn.disabled = false;
      currentTermBtn.style.cursor = 'pointer';
      currentTermBtn.title = 'Jump to current term';
    }
  }

  // Create curriculum review card
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
      ` : request.status === 'APPROVED' ? `
        <div class="action-buttons">
          <button class="status-approved-btn" disabled>Approved</button>
        </div>
      ` : `
        <div class="action-buttons">
          <button class="status-rejected-btn" disabled>Rejected</button>
        </div>
      `}

      <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)">View Details</button>

      ${request.status === 'PENDING' ? `
        <div class="request-date">Submitted on: ${request.date || new Date().toLocaleDateString()}</div>
      ` : request.status === 'APPROVED' ? `
        <div class="request-date">Approved on: ${request.date || new Date().toLocaleDateString()}</div>
      ` : `
        <div class="request-date">Rejected on: ${request.date || new Date().toLocaleDateString()}</div>
      `}
    `;

    return card;
  }

  // Load and display curriculum review cards
  function loadCurriculumCards() {
    const grid = document.getElementById('qaCurriculumGrid');
    const emptyState = document.getElementById('qaCurriculumEmptyState');
    
    if (!grid) return;

    // Dummy data for demonstration
    const dummyCard = {
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
      course_description: 'This course provides an introduction to the fundamental concepts of computer science, including programming basics, data structures, algorithms, and computer systems. Students will learn problem-solving techniques and develop programming skills using a modern programming language.',
      learning_outcomes: '1. Understand basic programming concepts and syntax\n2. Design and implement simple algorithms\n3. Use data structures to organize information\n4. Debug and test programs effectively\n5. Apply computational thinking to solve problems',
      course_outline: 'Week 1 (3 hrs)\n   Introduction to Computer Science\n\nWeek 2 (3 hrs)\n   Programming Fundamentals\n\nWeek 3 (3 hrs)\n   Variables and Data Types\n\nWeek 4 (3 hrs)\n   Control Structures\n\nWeek 5 (3 hrs)\n   Functions and Modules\n\nWeek 6 (3 hrs)\n   Arrays and Lists\n\nWeek 7 (3 hrs)\n   Object-Oriented Programming Basics\n\nWeek 8 (3 hrs)\n   File Handling\n\nWeek 9 (3 hrs)\n   Error Handling and Debugging\n\nWeek 10 (3 hrs)\n   Introduction to Algorithms\n\nWeek 11 (3 hrs)\n   Sorting and Searching\n\nWeek 12 (3 hrs)\n   Final Project',
      assessment: 'Quizzes: 20%\nAssignments: 30%\nMidterm Exam: 20%\nFinal Project: 20%\nClass Participation: 10%',
      materials: '[QA76.9] Introduction to Programming by Smith, J. (Pearson, 2023)\n[QA76.6] Data Structures and Algorithms by Johnson, M. (McGraw-Hill, 2022)',
      attachments: [
        { name: 'course_syllabus.pdf', url: '#', filename: 'course_syllabus.pdf' },
        { name: 'sample_projects.zip', url: '#', filename: 'sample_projects.zip' },
        { name: 'assessment_rubric.docx', url: '#', filename: 'assessment_rubric.docx' }
      ],
      justification: 'This course is essential for first-year students as it provides the foundational knowledge required for all subsequent computer science courses. The curriculum has been updated to include modern programming practices and aligns with industry standards. The course addresses the need for stronger programming fundamentals identified in our program review.',
      summary: 'New course curriculum submission for review. Includes updated learning objectives and assessment methods.',
      status: 'PENDING',
      date: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
    };

    // Clear existing cards
    grid.innerHTML = '';

    // Add dummy card
    const cardElement = createCurriculumCard(dummyCard);
    grid.appendChild(cardElement);

    // Hide empty state if cards exist
    if (emptyState) {
      emptyState.style.display = 'none';
    }
  }

  // Initialize on page load
  window.addEventListener('DOMContentLoaded', function() {
    // Initialize all counter values to 0 immediately
    const percentElem = document.getElementById('compliance-percentage');
    const compliantElem = document.getElementById('compliant-courses');
    const nonCompliantElem = document.getElementById('non-compliant-courses');
    const totalElem = document.getElementById('total-courses');
    const improvementElem = document.getElementById('improvement');
    
    if (percentElem) percentElem.textContent = '0%';
    if (compliantElem) compliantElem.textContent = '0';
    if (nonCompliantElem) nonCompliantElem.textContent = '0';
    if (totalElem) totalElem.textContent = '0';
    if (improvementElem) improvementElem.textContent = '0';
    
    // Load academic terms first, then fetch stats
    loadAcademicTerms();
    
    // Wait a bit for terms to load, then fetch initial compliance stats
    setTimeout(function() {
      // Fetch initial compliance stats for "All Terms" (this will also update the date indicator and department compliance)
      fetchComplianceStats('all');
      
      // Add event listener to term dropdown
      const termSelect = document.getElementById('academicTermSelect');
      if (termSelect) {
        termSelect.addEventListener('change', function() {
          const selectedTerm = this.value;
          console.log('Term changed to:', selectedTerm);
          
          // If a specific term is selected, optionally update date immediately from option attributes
          if (selectedTerm !== 'all' && selectedTerm) {
            const selectedOption = this.options[this.selectedIndex];
            const startDate = selectedOption.getAttribute('data-start-date');
            const endDate = selectedOption.getAttribute('data-end-date');
            
            if (startDate && endDate) {
              // Format dates immediately for better UX
              const startFormatted = new Date(startDate).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
              const endFormatted = new Date(endDate).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
              const dateIndicator = document.getElementById('date-indicator');
              if (dateIndicator) {
                dateIndicator.textContent = startFormatted + ' - ' + endFormatted;
              }
            }
          }
          
          fetchComplianceStats(selectedTerm);
          updateCurrentTermButtonState();
        });
        
        // Set default to "all" if not already set
        if (!termSelect.value) {
          termSelect.value = 'all';
        }
      }
      
      // Add event listener to "Current Term" button
      const currentTermBtn = document.getElementById('currentTermBtn');
      if (currentTermBtn) {
        currentTermBtn.addEventListener('click', setCurrentTerm);
      }
      
      // Update button state
      updateCurrentTermButtonState();

      // Load curriculum review cards (dummy data for now)
      loadCurriculumCards();

      // Start Curriculum Review and Approval section in collapsed state
      toggleCurriculumSection();
    }, 300);
  });

  function toggleCurriculumSection() {
    const section = document.getElementById('qaCurriculumSection');
    if (!section) return;
    
    const container = document.getElementById('qaCurriculumContainer');
    const footer = section.querySelector('.section-footer');
    const headerActions = section.querySelector('.header-actions');
    const toggleButton = section.querySelector('.collapse-btn span');
    
    if (!container || !footer || !headerActions) return;
    
    const isHidden = container.style.display === 'none';
    
    if (isHidden) {
      // Expand – show normal layout
      container.style.display = 'block';
      footer.style.display = 'flex';
      
      // Remove any collapsed controls in the header
      const existingCollapsedControls = section.querySelector('.collapsed-controls');
      if (existingCollapsedControls) {
        existingCollapsedControls.remove();
      }
      
      // Restore the header actions (View All)
      headerActions.style.display = 'flex';
      if (toggleButton) toggleButton.textContent = 'Collapse';
    } else {
      // Collapse – hide list and footer, replace header actions with badge + expand
      container.style.display = 'none';
      footer.style.display = 'none';
      
      // Hide header actions
      headerActions.style.display = 'none';
      
      // Build collapsed controls just like dean dashboard
      const grid = document.getElementById('qaCurriculumGrid');
      const totalItems = grid ? grid.children.length : 0;
      const collapsedControls = document.createElement('div');
      collapsedControls.className = 'collapsed-controls';
      collapsedControls.innerHTML = `
        <div class="request-count-badge">${totalItems}</div>
        <button class="expand-btn" onclick="toggleCurriculumSection()">
          <span>Expand</span>
          <img src="../src/assets/icons/right-arrow-icon.png" alt="Expand" class="collapse-icon" style="transform: rotate(90deg);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
          <span style="display: none;">^</span>
        </button>
      `;
      
      const sectionHeader = section.querySelector('.section-header');
      if (sectionHeader) {
        sectionHeader.appendChild(collapsedControls);
      }
    }
  }
</script> 

<!-- Four Stats Cards Row -->
<div style="width: 100%; display: flex; gap: 20px; margin-bottom: 24px; flex-wrap: wrap;">
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Compliant Courses</div>
    <div style="font-size: 2rem; font-weight: bold; color: #22c55e; font-family: 'TT Interphases', sans-serif;" id="compliant-courses">0</div>
  </div>
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Non-Compliant</div>
    <div style="font-size: 2rem; font-weight: bold; color: #ef4444; font-family: 'TT Interphases', sans-serif;" id="non-compliant-courses">0</div>
  </div>
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Total Courses</div>
    <div style="font-size: 2rem; font-weight: bold; color: #0C4B34; font-family: 'TT Interphases', sans-serif;" id="total-courses">0</div>
  </div>
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Improvement</div>
    <div style="font-size: 2rem; font-weight: bold; color: #3b82f6; font-family: 'TT Interphases', sans-serif;" id="improvement">0</div>
  </div>
</div> 

<!-- Curriculum Review and Approval Section -->
<div class="dashboard-section" id="qaCurriculumSection">
  <div class="section-header">
    <div class="header-left">
      <h3>Curriculum Review and Approval</h3>
      <div class="section-description">
        Review program curricula and course compliance before final Quality Assurance approval.
      </div>
    </div>
    <div class="header-actions">
      <a href="content.php?page=curriculum-review" class="view-all-btn">View All</a>
    </div>
  </div>

  <div class="reference-requests-container" id="qaCurriculumContainer">
    <div class="reference-requests-grid" id="qaCurriculumGrid">
      <!-- Curriculum review cards will be dynamically generated here -->
    </div>
    <div id="qaCurriculumEmptyState" style="text-align: center; padding: 40px 20px; color: #666;">
      <div style="font-size: 40px; margin-bottom: 12px;">📄</div>
      <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 18px; color: #333; margin-bottom: 6px;">No Curriculum Items to Review</h3>
      <p style="font-family: 'TT Interphases', sans-serif; font-size: 14px; color: #666;">
        When departments submit curriculum changes or course compliance updates, they will appear here for your review.
      </p>
    </div>
  </div>

  <div class="section-footer">
    <button class="collapse-btn" onclick="toggleCurriculumSection()">
      <span>Collapse</span>
      <img src="../src/assets/icons/right-arrow-icon.png" alt="Collapse" class="collapse-icon" style="transform: rotate(-90deg);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
      <span style="display: none;">^</span>
    </button>
  </div>
</div>

<!-- Department Compliance Status Section -->
<div style="width: 100%; margin-top: 24px; margin-bottom: 30px;">
  <div style="font-size: 24px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 2px;">Department Compliance Status</div>
  <div style="font-size: 1rem; color: #6b7280; font-family: 'TT Interphases', sans-serif; margin-bottom: 18px;">Compliance rates by department with detailed breakdown</div>

  <div id="departmentCardsContainer" style="display: flex; flex-wrap: wrap; gap: 20px;">
    <!-- Department cards will be dynamically inserted here -->
    <div style="width: 100%; text-align: center; padding: 20px; color: #6b7280; font-family: 'TT Interphases', sans-serif;">Loading department compliance data...</div>
  </div>
</div> 

<!-- Curriculum Details Modal -->
<div id="curriculumDetailsModal" class="modal-overlay" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <div>
        <h2>Course Proposal Details</h2>
        <button class="close-button" onclick="closeCurriculumDetailsModal()">&times;</button>
      </div>
    </div>
    <div class="modal-content">
      <div class="review-section">
        <div class="review-item" style="display: flex; justify-content: space-between; align-items: center;">
          <strong style="margin-bottom: 0;">Status</strong>
          <span id="modalStatus" class="detail-badge badge-pending">Pending</span>
        </div>

        <div class="review-item full-width">
          <strong>Submitted by:</strong>
          <div id="modalSubmittedBy" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Program:</strong>
          <div id="modalProgram" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Course Information:</strong>
          <div id="modalCourseInfo" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Course Description:</strong>
          <div id="modalCourseDescription" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Learning Outcomes:</strong>
          <div id="modalLearningOutcomes" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Course Outline:</strong>
          <div id="modalCourseOutline" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Assessment:</strong>
          <div id="modalAssessment" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Materials:</strong>
          <div id="modalMaterials" class="review-text">-</div>
        </div>

        <div class="review-item full-width">
          <strong>Attachments:</strong>
          <div id="modalAttachments" style="margin-top: 10px;">
            <!-- Attachment items will be dynamically generated here -->
          </div>
        </div>

        <div class="review-item full-width">
          <strong>Justification:</strong>
          <div id="modalJustification" class="review-text">-</div>
        </div>
      </div>
    </div>
    <div class="modal-footer" id="curriculumModalFooter">
      <button class="modal-footer-btn modal-footer-approve-btn" onclick="approveCurriculumProposal()">Approve</button>
      <button class="modal-footer-btn modal-footer-reject-btn" onclick="rejectCurriculumProposal()">Reject</button>
    </div>
  </div>
</div>

<script>
  // Format course outline to display as: Week 1/Topic (# Hours)\nTopic Description
  function formatCourseOutline(outline) {
    if (!outline || outline === '-' || outline.trim() === '') {
      return '-';
    }
    
    // Store original for fallback
    const originalOutline = outline;
    
    // If outline is already an array or object, format it directly
    if (Array.isArray(outline)) {
      return outline.map(item => {
        const topic = item.topic || item.week || item.week_topic || '';
        const hours = item.hours || item.hours_value || '';
        const description = item.description || item.topic_description || '';
        
        return formatOutlineItem(topic, hours, description);
      }).filter(item => item).join('\n\n');
    }
    
    // If outline is a JSON string, parse it
    if (typeof outline === 'string' && outline.trim().startsWith('[')) {
      try {
        const parsed = JSON.parse(outline);
        if (Array.isArray(parsed)) {
          return parsed.map(item => {
            const topic = item.topic || item.week || item.week_topic || '';
            const hours = item.hours || item.hours_value || '';
            const description = item.description || item.topic_description || '';
            
            return formatOutlineItem(topic, hours, description);
          }).filter(item => item).join('\n\n');
        }
      } catch (e) {
        // If parsing fails, treat as regular string
      }
    }
    
    // If outline is a string, try to parse it
    // Format might be: "Week 1: Topic (3 hrs)\n   Description" or "Week 1 (3 hrs)\n   Description" or "Week 1: Topic"
    if (typeof outline === 'string') {
      // First, try to detect if it's a simple list format (each line is a week/topic)
      // Check if lines start with "Week" or "Topic" pattern
      const allLines = outline.split('\n').filter(l => l.trim());
      const isSimpleList = allLines.length > 0 && allLines.every(line => {
        const trimmed = line.trim();
        return /^(Week\s+\d+|Topic\s+\d+)/i.test(trimmed);
      });
      
      // If it's a simple list format, process each line separately
      // But first check if it has the format "Week 1 (3 hrs)\n   Description"
      if (isSimpleList && !outline.includes('\n\n')) {
        // Check if any line has hours pattern - if so, process as structured format
        const hasHoursPattern = allLines.some(line => /\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i.test(line));
        
        if (hasHoursPattern) {
          // Process as structured format with potential multi-line items
          const formattedItems = [];
          let i = 0;
          while (i < allLines.length) {
            const line = allLines[i].trim();
            if (!line) {
              i++;
              continue;
            }
            
            // Check if this line has hours pattern - try start first, then anywhere
            let hoursMatch = line.match(/^(.+?)\s*\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
            if (!hoursMatch) {
              const hoursPattern = line.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
              if (hoursPattern) {
                const hoursIndex = line.indexOf(hoursPattern[0]);
                const topic = line.substring(0, hoursIndex).trim();
                const hours = hoursPattern[1];
                hoursMatch = { 1: topic, 2: hours, 0: hoursPattern[0] };
              }
            }
            if (hoursMatch) {
              const topic = hoursMatch[1].trim();
              const hours = hoursMatch[2];
              let description = '';
              
              // Check if next line is indented (description)
              if (i + 1 < allLines.length) {
                const nextLine = outline.split('\n')[outline.split('\n').indexOf(allLines[i]) + 1];
                if (nextLine && nextLine.match(/^\s+/)) {
                  description = nextLine.trim();
                  i++; // Skip the description line
                }
              }
              
              const formatted = formatOutlineItem(topic, hours, description);
              if (formatted) {
                formattedItems.push(formatted);
              }
            } else {
              // Try colon format
              const colonMatch = line.match(/^(.+?):\s*(.+)$/);
              if (colonMatch) {
                const topic = colonMatch[1].trim();
                let description = colonMatch[2].trim();
                let hours = '';
                
                // Check if description contains hours
                const descHoursMatch = description.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
                if (descHoursMatch) {
                  hours = descHoursMatch[1];
                  description = description.replace(descHoursMatch[0], '').trim();
                }
                
                const formatted = formatOutlineItem(topic, hours, description);
                if (formatted) {
                  formattedItems.push(formatted);
                }
              } else {
                // No special format, treat as topic only
                const formatted = formatOutlineItem(line, '', '');
                if (formatted) {
                  formattedItems.push(formatted);
                }
              }
            }
            i++;
          }
          if (formattedItems.length > 0) {
            return formattedItems.join('\n\n');
          }
        } else {
          // No hours pattern, process as simple list
          // But still check each line for hours that might be in description
          const formattedItems = [];
          for (const line of allLines) {
            const trimmed = line.trim();
            if (!trimmed) continue;
            
            // Try to parse each line - check for hours first even if no hours pattern at start
            let topic = '';
            let hours = '';
            let description = '';
            
            // Check if line has hours pattern anywhere
            const hoursMatch = trimmed.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
            if (hoursMatch) {
              // Found hours in the line
              const hoursIndex = trimmed.indexOf(hoursMatch[0]);
              const beforeHours = trimmed.substring(0, hoursIndex).trim();
              const afterHours = trimmed.substring(hoursIndex + hoursMatch[0].length).trim();
              
              // Check if before hours has a colon (format: "Week 1: Topic (3 hrs)")
              const colonMatch = beforeHours.match(/^(.+?):\s*(.+)$/);
              if (colonMatch) {
                topic = colonMatch[1].trim();
                description = (colonMatch[2].trim() + ' ' + afterHours).trim();
              } else {
                topic = beforeHours;
                description = afterHours;
              }
              hours = hoursMatch[1];
            } else {
              // No hours, try colon format
              const colonMatch = trimmed.match(/^(.+?):\s*(.+)$/);
              if (colonMatch) {
                topic = colonMatch[1].trim();
                description = colonMatch[2].trim();
                // Check if description contains hours (unlikely but possible)
                const descHoursMatch = description.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
                if (descHoursMatch) {
                  hours = descHoursMatch[1];
                  description = description.replace(descHoursMatch[0], '').trim();
                }
              } else {
                // No colon, treat whole line as topic
                topic = trimmed;
              }
            }
            
            const formatted = formatOutlineItem(topic, hours, description);
            if (formatted) {
              formattedItems.push(formatted);
            }
          }
          if (formattedItems.length > 0) {
            return formattedItems.join('\n\n');
          }
        }
      }
      
      // Split by double newlines first (separate outline items)
      const sections = outline.split('\n\n');
      const formattedItems = [];
      
      for (const section of sections) {
        // Don't trim lines yet - we need to preserve indentation to detect description lines
        const rawLines = section.split('\n');
        const lines = rawLines.map(l => l.trim()).filter(l => l);
        if (lines.length === 0) continue;
        
        let topic = '';
        let hours = '';
        let description = '';
        
        // Check first line for topic and hours pattern like "Week 1 (3 hrs)" or "Week 1: Topic (3 hrs)"
        const firstLine = lines[0];
        // Match pattern: "Week 1 (3 hrs)" or "Topic 1 (2.5 hrs)" - be flexible with spacing
        // First try to match at the start (most common format)
        let topicHoursMatch = firstLine.match(/^(.+?)\s*\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
        
        // If no match at start, try to find hours pattern anywhere in the line
        if (!topicHoursMatch) {
          const hoursMatch = firstLine.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
          if (hoursMatch) {
            // Found hours, extract topic (everything before the hours)
            const hoursIndex = firstLine.indexOf(hoursMatch[0]);
            topic = firstLine.substring(0, hoursIndex).trim();
            hours = hoursMatch[1];
            topicHoursMatch = { 1: topic, 2: hours, 0: hoursMatch[0] }; // Create match-like object
          }
        }
        
        if (topicHoursMatch) {
          // Found topic with hours - this is the format: "Week 1 (3 hrs)"
          topic = topicHoursMatch[1].trim();
          hours = topicHoursMatch[2];
          
          // Check if there's description on the same line after hours (unlikely but possible)
          const afterHours = firstLine.substring(topicHoursMatch[0].length).trim();
          if (afterHours) {
            description = afterHours;
          } else if (rawLines.length > 1) {
            // Description is on next line(s) - look for indented lines (starting with spaces)
            const descLines = [];
            for (let i = 1; i < rawLines.length; i++) {
              const line = rawLines[i];
              // If line starts with spaces (indented), it's likely a description
              if (line.match(/^\s+/)) {
                descLines.push(line.trim());
              } else if (line.trim()) {
                // Non-indented line might be a new topic, but let's include it for now
                descLines.push(line.trim());
              }
            }
            if (descLines.length > 0) {
              description = descLines.join(' ');
            }
          }
        } else {
          // No hours pattern found, try to extract topic and description
          // Check for "Week 1: Description" or "Topic 1: Description" format
          const colonMatch = firstLine.match(/^(.+?):\s*(.+)$/);
          if (colonMatch) {
            topic = colonMatch[1].trim();
            description = colonMatch[2].trim();
            // Check if description contains hours
            const hoursInDesc = description.match(/\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i);
            if (hoursInDesc) {
              hours = hoursInDesc[1];
              description = description.replace(hoursInDesc[0], '').trim();
            }
            // Add any additional description lines
            if (lines.length > 1) {
              const additionalDesc = lines.slice(1).map(l => l.replace(/^\s+/, '')).join(' ');
              if (additionalDesc) {
                description += (description ? ' ' : '') + additionalDesc;
              }
            }
          } else {
            // No colon, check if it's just a topic like "Week 1" or "Topic 1"
            const topicMatch = firstLine.match(/^(Week\s+\d+|Topic\s+\d+)/i);
            if (topicMatch) {
              topic = topicMatch[0];
              if (lines.length > 1) {
                description = lines.slice(1).map(l => l.replace(/^\s+/, '')).join(' ');
              }
            } else {
              // First line might be the topic, rest is description
              topic = firstLine;
              if (lines.length > 1) {
                description = lines.slice(1).map(l => l.replace(/^\s+/, '')).join(' ');
              }
            }
          }
        }
        
        // Clean up topic (remove trailing colon if present)
        topic = topic.replace(/:\s*$/, '').trim();
        
        const formatted = formatOutlineItem(topic, hours, description);
        if (formatted) {
          formattedItems.push(formatted);
        }
      }
      
      if (formattedItems.length > 0) {
        return formattedItems.join('\n\n');
      }
      
      // If we couldn't parse anything but the string has content, return it as-is
      // This handles cases where parsing doesn't work perfectly
      if (originalOutline.trim().length > 0) {
        return originalOutline;
      }
      
      // Final fallback: return as is
      return originalOutline;
    }
    
    // If we get here and outline is still the original, return it
    return originalOutline || outline;
  }
  
  // Helper function to format a single outline item
  function formatOutlineItem(topic, hours, description) {
    if (!topic && !description) {
      return '';
    }
    
    let formatted = '';
    if (topic) {
      formatted += topic;
      if (hours) {
        formatted += ` (${hours} hrs)`;
      }
    }
    if (description) {
      formatted += formatted ? `\n${description}` : description;
    }
    return formatted;
  }

  // Open curriculum details modal
  function openCurriculumDetailsModal(button) {
    const card = button.closest('.reference-request-card');
    if (!card) return;

    const requestData = JSON.parse(card.getAttribute('data-request'));
    const modal = document.getElementById('curriculumDetailsModal');
    
    // Update status badge
    const statusElement = document.getElementById('modalStatus');
    const status = requestData.status || 'PENDING';
    statusElement.textContent = status;
    statusElement.className = 'detail-badge';
    
    if (status === 'PENDING') {
      statusElement.classList.add('badge-pending');
    } else if (status === 'APPROVED') {
      statusElement.classList.add('badge-approved');
    } else if (status === 'REJECTED') {
      statusElement.classList.add('badge-rejected');
    }
    
    // Submitted by
    const deanName = requestData.dean_name || requestData.requester_name || requestData.submitted_by || 'Department Dean';
    const departmentName = requestData.department_name || 'College of Computing Studies';
    document.getElementById('modalSubmittedBy').innerHTML = `The Department Dean of ${departmentName}<br>${deanName}`;
    
    // Program(s)
    const programs = requestData.programs || [];
    let programText = '-';
    if (programs.length > 0) {
      const programNames = programs.map(p => {
        const code = p.code || p.program_code || '';
        return code.toUpperCase();
      });
      programText = programNames.join(', ');
    } else if (requestData.program_code) {
      programText = requestData.program_code.toUpperCase();
    }
    document.getElementById('modalProgram').textContent = programText;
    
    // Course Information
    const courseInfo = [];
    if (requestData.course_code) courseInfo.push(`Course Code: ${requestData.course_code}`);
    if (requestData.course_name) courseInfo.push(`Course Name: ${requestData.course_name}`);
    if (requestData.units) courseInfo.push(`Units: ${requestData.units}`);
    if (requestData.lecture_hours) courseInfo.push(`Lecture Hours: ${requestData.lecture_hours}`);
    if (requestData.laboratory_hours) courseInfo.push(`Laboratory Hours: ${requestData.laboratory_hours}`);
    if (requestData.prerequisites) courseInfo.push(`Prerequisites: ${requestData.prerequisites}`);
    if (requestData.year_level) courseInfo.push(`Year Level: ${requestData.year_level}`);
    if (requestData.term) courseInfo.push(`Term: ${requestData.term}`);
    document.getElementById('modalCourseInfo').textContent = courseInfo.length > 0 ? courseInfo.join('\n') : '-';
    
    // Course Description
    document.getElementById('modalCourseDescription').textContent = requestData.course_description || requestData.description || '-';
    
    // Learning Outcomes
    document.getElementById('modalLearningOutcomes').textContent = requestData.learning_outcomes || '-';
    
    // Course Outline - Format as: Week 1/Topic (# Hours)\nTopic Description
    // Check if course outline is stored as structured data (array/object) with hours
    let courseOutline = requestData.course_outline || requestData.outline || requestData.courseOutline || '-';
    let courseOutlineHours = requestData.course_outline_hours || requestData.outline_hours || null;
    
    // If course outline is an object/array, use it directly
    if (typeof courseOutline === 'object' && courseOutline !== null) {
      // It's already structured, pass it to formatter
    } else if (typeof courseOutline === 'string' && courseOutline.trim().startsWith('[')) {
      // It's a JSON string, will be parsed by formatCourseOutline
    } else {
      // It's a plain string - check if we have separate hours data
      // If hours are stored separately, we might need to merge them
      // For now, formatCourseOutline will handle parsing the string
    }
    
    let formattedOutline = '-';
    
    if (courseOutline && courseOutline !== '-') {
      try {
        formattedOutline = formatCourseOutline(courseOutline);
        // If formatting returns empty or just whitespace, fall back to original
        if (!formattedOutline || formattedOutline.trim() === '' || formattedOutline.trim() === '-') {
          formattedOutline = courseOutline;
        }
      } catch (e) {
        console.error('Error formatting course outline:', e, courseOutline);
        formattedOutline = courseOutline;
      }
    }
    
    // Debug: log the data to see what we're working with (only if hours are missing)
    if (!formattedOutline.includes('hrs') && !formattedOutline.includes('hours')) {
      console.log('Course Outline Data (no hours found):', {
        courseOutline: courseOutline,
        courseOutlineHours: courseOutlineHours,
        formattedOutline: formattedOutline.substring(0, 200) + '...',
        hasHoursInData: /\((\d+(?:\.\d+)?)\s*(?:hrs?|hours?)\)/i.test(courseOutline || '')
      });
    }
    
    document.getElementById('modalCourseOutline').textContent = formattedOutline;
    
    // Assessment
    document.getElementById('modalAssessment').textContent = requestData.assessment || requestData.assessment_methods || '-';
    
    // Materials
    document.getElementById('modalMaterials').textContent = requestData.materials || requestData.learning_materials || '-';
    
    // Attachments
    const attachments = requestData.attachments || [];
    const attachmentsContainer = document.getElementById('modalAttachments');
    attachmentsContainer.innerHTML = '';
    
    if (attachments.length > 0) {
      attachments.forEach((att, index) => {
        const name = att.name || att.filename || att;
        const url = att.url || att.path || '#';
        
        const attachmentItem = document.createElement('div');
        attachmentItem.className = 'attachment-item';
        
        const nameSpan = document.createElement('span');
        nameSpan.className = 'attachment-name';
        nameSpan.textContent = name;
        
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'attachment-actions';
        
        const viewBtn = document.createElement('button');
        viewBtn.className = 'attachment-btn attachment-view-btn';
        viewBtn.textContent = 'View';
        viewBtn.onclick = () => viewAttachment(url, name);
        
        const downloadBtn = document.createElement('button');
        downloadBtn.className = 'attachment-btn attachment-download-btn';
        downloadBtn.textContent = 'Download';
        downloadBtn.onclick = () => downloadAttachment(url, name);
        
        actionsDiv.appendChild(viewBtn);
        actionsDiv.appendChild(downloadBtn);
        
        attachmentItem.appendChild(nameSpan);
        attachmentItem.appendChild(actionsDiv);
        attachmentsContainer.appendChild(attachmentItem);
      });
    } else {
      attachmentsContainer.innerHTML = '<div class="review-text">-</div>';
    }
    
    // Justification
    document.getElementById('modalJustification').textContent = requestData.justification || '-';
    
    // Show/hide footer buttons based on status
    const footer = document.getElementById('curriculumModalFooter');
    if (footer) {
      if (status === 'PENDING') {
        footer.style.display = 'flex';
      } else {
        footer.style.display = 'none';
      }
    }
    
    // Store request data for approve/reject functions
    modal.setAttribute('data-request-id', requestData.id || '');
    modal.setAttribute('data-request-data', JSON.stringify(requestData));
    
    // Show modal
    modal.style.display = 'flex';
  }

  // Close curriculum details modal
  function closeCurriculumDetailsModal() {
    document.getElementById('curriculumDetailsModal').style.display = 'none';
  }

  // Close modal when clicking outside
  document.getElementById('curriculumDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeCurriculumDetailsModal();
    }
  });

  // Close modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('curriculumDetailsModal');
      if (modal && modal.style.display === 'flex') {
        closeCurriculumDetailsModal();
      }
    }
  });

  // View attachment function
  function viewAttachment(url, name) {
    if (url && url !== '#') {
      // Open in new tab
      window.open(url, '_blank');
    } else {
      alert(`Viewing ${name}\n\nNote: This is a demo attachment. In production, this would open the file.`);
    }
  }

  // Download attachment function
  function downloadAttachment(url, name) {
    if (url && url !== '#') {
      // Create a temporary anchor element to trigger download
      const link = document.createElement('a');
      link.href = url;
      link.download = name;
      link.target = '_blank';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } else {
      alert(`Downloading ${name}\n\nNote: This is a demo attachment. In production, this would download the file.`);
    }
  }

  // Approve curriculum proposal
  function approveCurriculumProposal() {
    const modal = document.getElementById('curriculumDetailsModal');
    const requestId = modal.getAttribute('data-request-id');
    const requestData = JSON.parse(modal.getAttribute('data-request-data') || '{}');
    
    if (confirm('Are you sure you want to approve this curriculum proposal?')) {
      // TODO: Implement API call to approve the proposal
      console.log('Approving proposal:', requestId, requestData);
      alert('Proposal approved successfully! (This is a demo - API integration needed)');
      closeCurriculumDetailsModal();
      // Reload cards to reflect the change
      loadCurriculumCards();
    }
  }

  // Reject curriculum proposal
  function rejectCurriculumProposal() {
    const modal = document.getElementById('curriculumDetailsModal');
    const requestId = modal.getAttribute('data-request-id');
    const requestData = JSON.parse(modal.getAttribute('data-request-data') || '{}');
    
    if (confirm('Are you sure you want to reject this curriculum proposal?')) {
      // TODO: Implement API call to reject the proposal
      console.log('Rejecting proposal:', requestId, requestData);
      alert('Proposal rejected. (This is a demo - API integration needed)');
      closeCurriculumDetailsModal();
      // Reload cards to reflect the change
      loadCurriculumCards();
    }
  }
</script>
