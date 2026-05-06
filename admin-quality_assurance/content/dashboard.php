<!-- QA dashboard styling lives in `admin-quality_assurance/styles/dashboard.css` -->

<!-- Header -->
<div class="qa-dashboard-header">
  <div class="qa-title-block">
    <h1>Academic Standards Compliance Monitoring</h1>
    <div class="qa-date-pill" aria-live="polite">
      <i data-lucide="calendar" class="icon-lucide-16"></i>
      <span id="date-indicator">Loading...</span>
    </div>
  </div>

  <div class="qa-term-controls" aria-label="Academic term controls">
    <label for="academicTermSelect">Academic Term</label>
    <div class="qa-select-wrap">
      <select id="academicTermSelect" class="qa-select">
        <option value="all">All Terms (Current Academic Year)</option>
      </select>
      <i data-lucide="chevrons-up-down" class="icon-lucide-16"></i>
    </div>
    <button id="currentTermBtn" class="btn-primary" type="button">
      <i data-lucide="target" class="icon-lucide-16"></i>
      Current Term
    </button>
  </div>
</div>

<!-- Compliance Status Card -->
<div class="qa-card qa-compliance" aria-label="Compliance status">
  <div class="qa-compliance-top">
    <div>
      <h2 id="compliance-status-title">All Terms (Current Academic Year) Compliance Status</h2>
      <p>Academic standards compliance</p>
    </div>
    <div class="qa-compliance-metric">
      <div id="compliance-percentage" class="value">0%</div>
      <div class="qa-dept-sub">Compliant</div>
    </div>
  </div>
  <div class="qa-progress">
    <div id="compliance-progress-bar"></div>
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
    percentElem.style.color = isCompliant ? '#0F7A53' : '#b91c1c';
    percentElem.textContent = compliancePercent + '%';
    
    // Update progress bar
    setTimeout(function() {
      progressBar.style.width = compliancePercent + '%';
      progressBar.style.background = isCompliant ? '#0F7A53' : '#b91c1c';
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
    
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          renderDepartmentCards(data.data);
        } else {
          console.error('Error fetching department compliance:', data.message);
          document.getElementById('departmentCardsContainer').innerHTML =
            '<div class="qa-card qa-dept-card" style="text-align:center;"><div class="qa-dept-sub" style="color:#b91c1c;">Error loading department compliance data.</div></div>';
        }
      })
      .catch(error => {
        console.error('Error fetching department compliance:', error);
        document.getElementById('departmentCardsContainer').innerHTML =
          '<div class="qa-card qa-dept-card" style="text-align:center;"><div class="qa-dept-sub" style="color:#b91c1c;">Error loading department compliance data.</div></div>';
      });
  }
  
  // Render department cards
  function renderDepartmentCards(departments) {
    const container = document.getElementById('departmentCardsContainer');
    
    if (!departments || departments.length === 0) {
      container.innerHTML =
        '<div class="qa-card qa-dept-card" style="text-align:center;"><div class="qa-dept-sub">No department data available for the selected term.</div></div>';
      return;
    }
    
    container.innerHTML = departments.map(dept => {
      // Determine text color for department badge based on background color
      const bgColor = dept.color_code || '#0C4B34';
      // Simple check: if color is light, use dark text; otherwise use white text
      const rgb = hexToRgb(bgColor);
      const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
      const textColor = brightness > 128 ? '#111' : '#fff';
      
      const compliancePercent = dept.compliance_percentage || 0;
      const progressBarColor = compliancePercent >= 70 ? '#0F7A53' : '#b91c1c';
      
      return `
        <div class="qa-card qa-dept-card">
          <div class="qa-dept-top">
            <div>
              <span class="qa-dept-badge" style="background:${bgColor}; color:${textColor};">${dept.department_code}</span>
              <div class="qa-dept-metric" style="margin-top:8px;">${compliancePercent}% Compliant</div>
            </div>
            <div class="qa-dept-sub">${dept.compliant_courses}/${dept.total_courses} courses</div>
          </div>
          <div class="qa-dept-progress">
            <div style="width:${compliancePercent}%; background:${progressBarColor};"></div>
          </div>
          <div class="qa-dept-sub">${dept.courses_needing_attention} course${dept.courses_needing_attention !== 1 ? 's' : ''} need attention</div>
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
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (data.debug) {
          if (data.debug.total_courses_in_db !== data.debug.courses_found?.length) {
            console.warn('⚠️ MISMATCH: Query found', data.debug.courses_found?.length, 'courses but there are', data.debug.total_courses_in_db, 'courses in DB!');
          }
        }
        if (data.success) {
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
    // Try different path options (since dashboard is included via PHP, paths are relative to content.php)
    const apiPaths = [
      'api/get_academic_terms.php',
      '../api/get_academic_terms.php',
      '../../admin-quality_assurance/api/get_academic_terms.php'
    ];
    
    let apiUrl = apiPaths[0];
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
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
              } else {
                // Use first term as default
                currentAcademicTerm = academicTerms[0];
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
            fetch(apiUrl)
              .then(response => response.json())
              .then(data => {
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

  // Create curriculum review card with Lucide icons
  function createCurriculumCard(request) {
    const card = document.createElement('div');
    card.className = 'reference-request-card';
    card.setAttribute('data-proposal-id', request.proposal_id);
    card.setAttribute('data-request', JSON.stringify(request));

    const departmentColor = request.department_color || '#0C4B34';
    
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

    // Status display
    const statusClass = request.status === 'APPROVED' ? 'badge-approved' : (request.status === 'REJECTED' ? 'badge-rejected' : 'badge-pending');
    const statusText = request.status === 'APPROVED' ? 'Approved' : (request.status === 'REJECTED' ? 'Rejected' : 'Pending');
    
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
      ` : request.status === 'APPROVED' ? `
        <div class="action-buttons">
          <button class="status-approved-btn" disabled>
            <i data-lucide="check-circle" class="icon-lucide-16"></i> Approved
          </button>
        </div>
      ` : `
        <div class="action-buttons">
          <button class="status-rejected-btn" disabled>
            <i data-lucide="x-circle" class="icon-lucide-16"></i> Rejected
          </button>
        </div>
      `}

      <button class="view-details-btn" onclick="openCurriculumDetailsModal(this)">
        <i data-lucide="eye" class="icon-lucide-16"></i> View Details
      </button>

      <div class="request-date">${dateLabel}: ${dateDisplay}</div>
    `;

    // Initialize Lucide icons for this card
    setTimeout(() => {
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }, 0);

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
        loadCurriculumCards(); // Reload to show updated status
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
        loadCurriculumCards(); // Reload to show updated status
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

  // Load and display curriculum review cards from API
  let curriculumRequests = [];
  
  function loadCurriculumCards() {
    const grid = document.getElementById('qaCurriculumGrid');
    const emptyState = document.getElementById('qaCurriculumEmptyState');
    
    if (!grid) return;

    // Fetch real data from API
    fetch('api/get_qa_curriculum.php?status=PENDING')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        // Clear existing cards
        grid.innerHTML = '';
        
        if (data.success && data.data && data.data.length > 0) {
          curriculumRequests = data.data;
          
          // Add cards for each proposal
          data.data.forEach((request, index) => {
            const cardElement = createCurriculumCard(request);
            // Add staggered animation delay
            cardElement.style.animationDelay = (0.08 + index * 0.08) + 's';
            grid.appendChild(cardElement);
          });
          
          // Hide empty state
          if (emptyState) {
            emptyState.style.display = 'none';
          }
        } else {
          // Show empty state
          if (emptyState) {
            emptyState.style.display = 'block';
          }
        }
        
        // Update collapsed badge count
        updateCurriculumBadgeCount(data.data?.length || 0);
      })
      .catch(error => {
        console.error('Error loading curriculum cards:', error);
        grid.innerHTML = '<div class="qa-card" style="text-align:center;"><div class="qa-dept-sub" style="color:#b91c1c;">Error loading curriculum data.</div></div>';
      });
  }

  // Update the collapsed badge count
  function updateCurriculumBadgeCount(count) {
    const badge = document.querySelector('.request-count-badge');
    if (badge) {
      badge.textContent = count;
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

      if (window.lucide?.createIcons) window.lucide.createIcons();
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
        <button class="btn-ghost expand-btn" type="button" onclick="toggleCurriculumSection()">
          <span>Expand</span>
          <i data-lucide="chevron-down" class="icon-lucide-16"></i>
        </button>
      `;
      
      const sectionHeader = section.querySelector('.section-header');
      if (sectionHeader) {
        sectionHeader.appendChild(collapsedControls);
      }

      if (window.lucide?.createIcons) window.lucide.createIcons();
    }
  }
</script> 

<!-- Stats -->
<div class="qa-stats-grid" aria-label="Compliance statistics">
  <div class="qa-card qa-stat is-ok">
    <div class="label">Compliant Courses</div>
    <div class="number" id="compliant-courses">0</div>
  </div>
  <div class="qa-card qa-stat is-bad">
    <div class="label">Non-Compliant</div>
    <div class="number" id="non-compliant-courses">0</div>
  </div>
  <div class="qa-card qa-stat is-neutral">
    <div class="label">Total Courses</div>
    <div class="number" id="total-courses">0</div>
  </div>
  <div class="qa-card qa-stat is-info">
    <div class="label">Improvement</div>
    <div class="number" id="improvement">0</div>
  </div>
</div>

<!-- Curriculum Review and Approval -->
<div class="qa-card qa-section" id="qaCurriculumSection">
  <div class="qa-section-header section-header">
    <div class="left header-left">
      <div class="label-bar"></div>
      <div>
        <h3>Curriculum Review and Approval</h3>
        <p class="section-description">Review program curricula and course compliance before final Quality Assurance approval.</p>
      </div>
    </div>
    <div class="header-actions">
      <a href="content.php?page=curriculum-review" class="btn-primary" style="text-decoration: none;">
        <i data-lucide="arrow-right" class="icon-lucide-16"></i>
        View All
      </a>
    </div>
  </div>

  <div class="reference-requests-container" id="qaCurriculumContainer">
    <div class="qa-curriculum-grid reference-requests-grid" id="qaCurriculumGrid" aria-live="polite">
      <!-- Curriculum review cards will be dynamically generated here -->
    </div>
    <div id="qaCurriculumEmptyState" class="qa-empty-state" style="display:none;">
      <div style="margin-bottom: 10px;">
        <i data-lucide="file-text" class="icon-lucide-22" style="width: 48px; height: 48px; color: rgba(17,24,39,0.35);"></i>
      </div>
      <h3>No Curriculum Items to Review</h3>
      <p>When departments submit curriculum changes or course compliance updates, they will appear here for your review.</p>
    </div>
  </div>

  <div class="section-footer" style="margin-top: 14px; display:flex; justify-content:flex-end;">
    <button class="btn-ghost collapse-btn" type="button" onclick="toggleCurriculumSection()">
      <span>Collapse</span>
      <i data-lucide="chevron-up" class="icon-lucide-16"></i>
    </button>
  </div>
</div>

<!-- Department Compliance Status -->
<div class="qa-section" aria-label="Department compliance status">
  <div class="qa-section-header">
    <div class="left">
      <div class="label-bar"></div>
      <div>
        <h3>Department Compliance Status</h3>
        <p>Compliance rates by department with detailed breakdown</p>
      </div>
    </div>
  </div>

  <div id="departmentCardsContainer" class="qa-dept-grid" aria-live="polite">
    <div class="qa-card qa-dept-card" style="text-align:center;">
      <div class="qa-dept-sub">Loading department compliance data...</div>
    </div>
  </div>
</div>

<!-- Curriculum Details Modal -->
<div id="curriculumDetailsModal" class="modal-overlay qa-modal" style="display: none;">
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
      console.log({
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
      alert('Proposal rejected. (This is a demo - API integration needed)');
      closeCurriculumDetailsModal();
      // Reload cards to reflect the change
      loadCurriculumCards();
    }
  }
</script>
