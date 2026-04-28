<?php
// program-head-dashboard.php - Program Head Dashboard for teachers who are also program heads
// Shows their assigned program and allows them to manage courses and review teacher syllabi

$teacherId = $_SESSION['user_id'] ?? null;
$academicYear = $_SESSION['selected_role']['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
$term = $_SESSION['selected_role']['term'] ?? '1st Semester';

// Get program head info
$programHeadData = $_SESSION['program_head_program'] ?? null;
?>

<style>
/* Program Head Dashboard Styles */
.ph-dashboard {
  padding: 0;
}

.ph-dashboard-header {
  margin-bottom: 24px;
}

.ph-dashboard-title {
  font-size: 24px;
  font-weight: 800;
  color: #0C4B34;
  margin: 0 0 8px 0;
}

.ph-dashboard-subtitle {
  font-size: 14px;
  color: rgba(17, 24, 39, 0.6);
  margin: 0;
}

.ph-program-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #0C4B34 0%, #0F7A53 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  margin-top: 12px;
}

/* Stats Grid */
.ph-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.ph-stat-card {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.ph-stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  background: rgba(12, 75, 52, 0.1);
}

.ph-stat-info h4 {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
  color: #0C4B34;
}

.ph-stat-info p {
  margin: 4px 0 0 0;
  font-size: 13px;
  color: rgba(17, 24, 39, 0.6);
}

/* Section Styles */
.ph-section {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 24px;
}

.ph-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.ph-section-title {
  font-size: 18px;
  font-weight: 700;
  color: #0C4B34;
  margin: 0;
}

/* Course Table */
.ph-courses-table {
  width: 100%;
  border-collapse: collapse;
}

.ph-courses-table th {
  text-align: left;
  padding: 12px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(17, 24, 39, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid rgba(12, 75, 52, 0.1);
}

.ph-courses-table td {
  padding: 14px 12px;
  font-size: 13px;
  color: #333;
  border-bottom: 1px solid rgba(12, 75, 52, 0.05);
}

.ph-courses-table tbody tr:hover {
  background: rgba(12, 75, 52, 0.02);
}

.ph-course-code {
  background: #0C4B34;
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

/* Syllabus Review Section */
.syllabus-review-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.syllabus-review-item {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.syllabus-review-info h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: #333;
}

.syllabus-review-info p {
  margin: 0;
  font-size: 12px;
  color: rgba(17, 24, 39, 0.6);
}

.syllabus-review-actions {
  display: flex;
  gap: 8px;
}

.syllabus-review-btn {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.syllabus-review-btn.view {
  background: rgba(12, 75, 52, 0.1);
  color: #0C4B34;
}

.syllabus-review-btn.view:hover {
  background: rgba(12, 75, 52, 0.2);
}

.syllabus-review-btn.approve {
  background: #0C4B34;
  color: white;
}

.syllabus-review-btn.approve:hover {
  background: #0a3a28;
}

.syllabus-review-btn.reject {
  background: #dc3545;
  color: white;
}

.syllabus-review-btn.reject:hover {
  background: #c82333;
}

/* Empty State */
.ph-empty-state {
  text-align: center;
  padding: 40px 20px;
}

.ph-empty-state-icon {
  font-size: 48px;
  margin-bottom: 12px;
}

.ph-empty-state-title {
  font-size: 16px;
  font-weight: 700;
  color: #0C4B34;
}

.ph-empty-state-text {
  font-size: 13px;
  color: rgba(17, 24, 39, 0.6);
  margin-top: 8px;
}
</style>

<div class="ph-dashboard">
  <div class="ph-dashboard-header">
    <h1 class="ph-dashboard-title">Program Head Dashboard</h1>
    <p class="ph-dashboard-subtitle">Manage your program and review teacher syllabi</p>
    <?php if ($programHeadData): ?>
    <div class="ph-program-badge">
      <span>📚</span>
      <?php echo htmlspecialchars($programHeadData['program_name'] ?? $programHeadData['program_code']); ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Stats -->
  <div class="ph-stats-grid">
    <div class="ph-stat-card">
      <div class="ph-stat-icon">📖</div>
      <div class="ph-stat-info">
        <h4 id="stat-courses">0</h4>
        <p>Courses</p>
      </div>
    </div>
    <div class="ph-stat-card">
      <div class="ph-stat-icon">👨‍🏫</div>
      <div class="ph-stat-info">
        <h4 id="stat-teachers">0</h4>
        <p>Teachers</p>
      </div>
    </div>
    <div class="ph-stat-card">
      <div class="ph-stat-icon">📝</div>
      <div class="ph-stat-info">
        <h4 id="stat-pending">0</h4>
        <p>Pending Syllabi</p>
      </div>
    </div>
    <div class="ph-stat-card">
      <div class="ph-stat-icon">✅</div>
      <div class="ph-stat-info">
        <h4 id="stat-approved">0</h4>
        <p>Approved</p>
      </div>
    </div>
  </div>

  <!-- Courses Section -->
  <div class="ph-section">
    <div class="ph-section-header">
      <h2 class="ph-section-title">Program Courses</h2>
    </div>
    <div id="ph-courses-container">
      <div class="ph-empty-state">
        <div class="ph-empty-state-icon">📚</div>
        <div class="ph-empty-state-title">Loading courses...</div>
      </div>
    </div>
  </div>

  <!-- Syllabus Review Section -->
  <div class="ph-section">
    <div class="ph-section-header">
      <h2 class="ph-section-title">Syllabus Review Queue</h2>
    </div>
    <div id="ph-syllabus-review-container">
      <div class="ph-empty-state">
        <div class="ph-empty-state-icon">📋</div>
        <div class="ph-empty-state-title">No pending reviews</div>
        <div class="ph-empty-state-text">All teacher syllabi have been reviewed.</div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadProgramHeadData();
});

function loadProgramHeadData() {
  fetch('api/get_program_head_data.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Update stats
        document.getElementById('stat-courses').textContent = data.courses?.length || 0;
        document.getElementById('stat-teachers').textContent = data.teachers?.length || 0;
        
        // Update courses
        renderCourses(data.courses || []);
        
        // Update syllabus review queue
        renderSyllabusReview(data.pendingSyllabi || []);
        
        // Update stats for pending and approved
        document.getElementById('stat-pending').textContent = (data.pendingSyllabi || []).length;
        document.getElementById('stat-approved').textContent = (data.approvedSyllabi || []).length;
      }
    })
    .catch(err => console.error('Error loading program head data:', err));
}

function renderCourses(courses) {
  const container = document.getElementById('ph-courses-container');
  
  if (!courses.length) {
    container.innerHTML = `
      <div class="ph-empty-state">
        <div class="ph-empty-state-icon">📚</div>
        <div class="ph-empty-state-title">No courses in your program</div>
        <div class="ph-empty-state-text">Courses will appear here when added by the dean.</div>
      </div>
    `;
    return;
  }
  
  container.innerHTML = `
    <table class="ph-courses-table">
      <thead>
        <tr>
          <th>Course Code</th>
          <th>Course Title</th>
          <th>Year Level</th>
          <th>Teacher</th>
          <th>Syllabus Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        ${courses.map(course => `
          <tr>
            <td><span class="ph-course-code">${escapeHtml(course.course_code)}</span></td>
            <td>${escapeHtml(course.course_title)}</td>
            <td>${escapeHtml(course.year_level || '-')}</td>
            <td>${escapeHtml(course.teacher_name || 'Not assigned')}</td>
            <td>${getSyllabusStatusBadge(course.syllabus_status)}</td>
            <td>
              <button class="syllabus-review-btn view" onclick="viewSyllabus(${course.syllabus_id || 0}, ${course.id})">View</button>
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

function renderSyllabusReview(syllabi) {
  const container = document.getElementById('ph-syllabus-review-container');
  
  if (!syllabi.length) {
    container.innerHTML = `
      <div class="ph-empty-state">
        <div class="ph-empty-state-icon">✅</div>
        <div class="ph-empty-state-title">All caught up!</div>
        <div class="ph-empty-state-text">No pending syllabi to review.</div>
      </div>
    `;
    return;
  }
  
  container.innerHTML = `
    <div class="syllabus-review-list">
      ${syllabi.map(s => `
        <div class="syllabus-review-item">
          <div class="syllabus-review-info">
            <h4>${escapeHtml(s.course_code)} - ${escapeHtml(s.course_title)}</h4>
            <p>Submitted by ${escapeHtml(s.teacher_name)} on ${new Date(s.submitted_at).toLocaleDateString()}</p>
          </div>
          <div class="syllabus-review-actions">
            <button class="syllabus-review-btn view" onclick="viewSyllabus(${s.id}, ${s.course_id})">View</button>
            <button class="syllabus-review-btn approve" onclick="approveSyllabus(${s.id})">Approve</button>
            <button class="syllabus-review-btn reject" onclick="requestRevision(${s.id})">Request Revision</button>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

function getSyllabusStatusBadge(status) {
  const badges = {
    'not_started': '<span style="color: #6b7280; font-size: 12px;">Not Started</span>',
    'in_progress': '<span style="color: #d97706; font-size: 12px;">In Progress</span>',
    'submitted': '<span style="color: #2563eb; font-size: 12px;">Submitted</span>',
    'ph_review': '<span style="color: #7c3aed; font-size: 12px;">Under Review</span>',
    'ph_approved': '<span style="color: #059669; font-size: 12px;">PH Approved</span>',
    'dean_approved': '<span style="color: #0C4B34; font-size: 12px;">Approved</span>',
    'revision_requested': '<span style="color: #dc2626; font-size: 12px;">Needs Revision</span>'
  };
  return badges[status] || badges['not_started'];
}

function viewSyllabus(syllabusId, courseId) {
  // Open syllabus modal in view-only mode
  if (window.openSyllabusModal) {
    openSyllabusModal(courseId, '', '', '');
  }
}

function approveSyllabus(syllabusId) {
  if (!confirm('Are you sure you want to approve this syllabus?')) return;
  
  fetch('api/ph_approve_syllabus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ syllabus_id: syllabusId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Syllabus approved successfully!');
      loadProgramHeadData();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function requestRevision(syllabusId) {
  const note = prompt('Please provide feedback for the revision:');
  if (!note) return;
  
  fetch('api/ph_request_revision.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ syllabus_id: syllabusId, note: note })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Revision requested!');
      loadProgramHeadData();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>