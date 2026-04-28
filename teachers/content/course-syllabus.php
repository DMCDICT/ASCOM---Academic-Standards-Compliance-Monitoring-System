<?php
// course-syllabus.php - Teacher's Course Syllabi Management
// Shows assigned courses and their syllabus status

$teacherId = $_SESSION['user_id'] ?? null;
$academicYear = $_SESSION['selected_role']['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
$term = $_SESSION['selected_role']['term'] ?? '1st Semester';
?>

<style>
/* Course Syllabus Page Styles */
.syllabus-page {
  padding: 0;
}

.syllabus-page-header {
  margin-bottom: 24px;
}

.syllabus-page-title {
  font-size: 24px;
  font-weight: 800;
  color: #0C4B34;
  margin: 0 0 8px 0;
}

.syllabus-page-subtitle {
  font-size: 14px;
  color: rgba(17, 24, 39, 0.6);
  margin: 0;
}

/* Syllabus Cards Grid */
.syllabus-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.syllabus-card {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 20px;
  transition: all 0.28s cubic-bezier(.4,0,.2,1);
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.syllabus-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
}

.syllabus-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
  border-color: rgba(12, 75, 52, 0.25);
}

.syllabus-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.syllabus-card-code {
  background: #0C4B34;
  color: white;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
}

.syllabus-card-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 8px 0 4px 0;
}

.syllabus-card-program {
  font-size: 12px;
  color: rgba(17, 24, 39, 0.6);
  margin-bottom: 16px;
}

/* Syllabus Status Badge */
.syllabus-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  width: fit-content;
}

.syllabus-status-badge.not-started {
  background: rgba(156, 163, 175, 0.15);
  color: #6b7280;
}

.syllabus-status-badge.draft {
  background: rgba(245, 158, 11, 0.15);
  color: #d97706;
}

.syllabus-status-badge.submitted {
  background: rgba(59, 130, 246, 0.15);
  color: #2563eb;
}

.syllabus-status-badge.ph-review {
  background: rgba(139, 92, 246, 0.15);
  color: #7c3aed;
}

.syllabus-status-badge.ph-approved {
  background: rgba(16, 185, 129, 0.15);
  color: #059669;
}

.syllabus-status-badge.dean-approved {
  background: rgba(12, 75, 52, 0.15);
  color: #0C4B34;
}

.syllabus-status-badge.revision-requested {
  background: rgba(239, 68, 68, 0.15);
  color: #dc2626;
}

.syllabus-card-footer {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(12, 75, 52, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.syllabus-action-btn {
  padding: 8px 16px;
  background: #0C4B34;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.syllabus-action-btn:hover {
  background: #0a3a28;
}

/* Empty State */
.syllabus-empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #ffffff;
  border: 2px dashed rgba(12, 75, 52, 0.2);
  border-radius: 16px;
}

.syllabus-empty-state-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.syllabus-empty-state-title {
  font-size: 18px;
  font-weight: 700;
  color: #0C4B34;
  margin-bottom: 8px;
}

.syllabus-empty-state-text {
  font-size: 14px;
  color: rgba(17, 24, 39, 0.6);
}
</style>

<div class="syllabus-page">
  <div class="syllabus-page-header">
    <h1 class="syllabus-page-title">Course Syllabi</h1>
    <p class="syllabus-page-subtitle">Manage your course syllabi for <?php echo htmlspecialchars($term); ?>, <?php echo htmlspecialchars($academicYear); ?></p>
  </div>

  <div id="syllabus-cards-container" class="syllabus-cards-grid">
    <!-- Courses will be loaded here via JavaScript -->
    <div class="syllabus-empty-state" style="grid-column: 1 / -1;">
      <div class="syllabus-empty-state-icon">📚</div>
      <div class="syllabus-empty-state-title">Loading courses...</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadAssignedCourses();
});

function loadAssignedCourses() {
  fetch('api/get_assigned_courses.php')
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('syllabus-cards-container');
      
      if (!data.success || !data.courses || data.courses.length === 0) {
        container.innerHTML = `
          <div class="syllabus-empty-state" style="grid-column: 1 / -1;">
            <div class="syllabus-empty-state-icon">📚</div>
            <div class="syllabus-empty-state-title">No Courses Assigned</div>
            <div class="syllabus-empty-state-text">You don't have any courses assigned for this term.</div>
          </div>
        `;
        return;
      }
      
      container.innerHTML = data.courses.map(course => {
        const statusClass = getStatusClass(course.syllabus_status);
        const statusText = course.syllabus_status_text || 'Not Started';
        const actionText = course.syllabus_status === 'draft' ? 'Edit Draft' : 
                          course.syllabus_status === 'revision_requested' ? 'Revise' : 
                          course.syllabus_id ? 'View/Edit' : 'Create Syllabus';
        
        return `
          <div class="syllabus-card" onclick="openSyllabusModal(${course.id}, '${escapeHtml(course.course_title)}', '${data.academic_year || ''}', '${data.term || ''}')">
            <div class="syllabus-card-header">
              <span class="syllabus-card-code">${escapeHtml(course.course_code)}</span>
              <div class="syllabus-status-badge ${statusClass}">
                <span>${getStatusIcon(course.syllabus_status)}</span>
                ${statusText}
              </div>
            </div>
            <h3 class="syllabus-card-title">${escapeHtml(course.course_title)}</h3>
            <p class="syllabus-card-program">${escapeHtml(course.program_name || 'General')} - ${escapeHtml(course.year_level || '')}</p>
            <div class="syllabus-card-footer">
              <span style="font-size: 12px; color: rgba(17, 24, 39, 0.5);">${course.units || 3} units</span>
              <button class="syllabus-action-btn">${actionText}</button>
            </div>
          </div>
        `;
      }).join('');
    })
    .catch(err => {
      document.getElementById('syllabus-cards-container').innerHTML = `
        <div class="syllabus-empty-state" style="grid-column: 1 / -1;">
          <div class="syllabus-empty-state-icon">⚠️</div>
          <div class="syllabus-empty-state-title">Error Loading Courses</div>
          <div class="syllabus-empty-state-text">Please try refreshing the page.</div>
        </div>
      `;
    });
}

function getStatusClass(status) {
  const classes = {
    'draft': 'draft',
    'submitted': 'submitted',
    'ph_review': 'ph-review',
    'ph_approved': 'ph-approved',
    'dean_approved': 'dean-approved',
    'revision_requested': 'revision-requested'
  };
  return classes[status] || 'not-started';
}

function getStatusIcon(status) {
  const icons = {
    'draft': '📝',
    'submitted': '📤',
    'ph_review': '👁️',
    'ph_approved': '✅',
    'dean_approved': '🎓',
    'revision_requested': '⚠️'
  };
  return icons[status] || '⭕';
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>