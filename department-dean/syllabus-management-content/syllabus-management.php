<?php
// syllabus-management.php - Dean's Syllabus Management Interface
// Allows deans to view and approve syllabi after program head review

$departmentId = $_SESSION['selected_role']['department_id'] ?? null;
$academicYear = $_SESSION['selected_role']['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
$term = $_SESSION['selected_role']['term'] ?? '1st Semester';
?>

<style>
/* Syllabus Management Styles */
.syllabus-mgmt {
  padding: 0;
}

.syllabus-mgmt-header {
  margin-bottom: 24px;
}

.syllabus-mgmt-title {
  font-size: 24px;
  font-weight: 800;
  color: #0C4B34;
  margin: 0 0 8px 0;
}

.syllabus-mgmt-subtitle {
  font-size: 14px;
  color: rgba(17, 24, 39, 0.6);
  margin: 0;
}

/* Stats Cards */
.syllabus-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.syllabus-stat-card {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.28s cubic-bezier(.4,0,.2,1);
}

.syllabus-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(12, 75, 52, 0.1);
}

.syllabus-stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(12, 75, 52, 0.08);
}

.syllabus-stat-icon i[data-lucide] {
  width: 22px;
  height: 22px;
  color: #0C4B34;
}

.syllabus-stat-info h4 {
  margin: 0;
  font-size: 26px;
  font-weight: 800;
  color: #0C4B34;
}

.syllabus-stat-info p {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: rgba(17, 24, 39, 0.6);
}

/* Section */
.syllabus-section {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.12);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 24px;
}

.syllabus-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.syllabus-section-title {
  font-size: 18px;
  font-weight: 700;
  color: #0C4B34;
  margin: 0;
}

.syllabus-section-badge {
  background: #dc3545;
  color: white;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Syllabus List */
.syllabus-approval-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.syllabus-approval-item {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  transition: all 0.2s;
}

.syllabus-approval-item:hover {
  background: #f0f4f2;
  border-color: rgba(12, 75, 52, 0.2);
}

.syllabus-item-info {
  flex: 1;
}

.syllabus-item-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.syllabus-course-code {
  background: #0C4B34;
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.syllabus-item-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.syllabus-item-meta {
  display: flex;
  gap: 16px;
  font-size: 13px;
  color: rgba(17, 24, 39, 0.6);
}

.syllabus-item-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.syllabus-item-meta i[data-lucide] {
  width: 14px;
  height: 14px;
  opacity: 0.7;
}

.syllabus-empty-icon i[data-lucide] {
  width: 44px;
  height: 44px;
  color: rgba(12, 75, 52, 0.75);
}

.syllabus-empty-icon svg.lucide-loader-circle {
  animation: syllabusSpin 1s linear infinite;
}

.syllabus-empty-icon svg.lucide-loader,
.syllabus-empty-icon svg.lucide-loader-2 {
  animation: syllabusSpin 1s linear infinite;
}

.syllabus-stat-icon svg.lucide-loader-circle,
.syllabus-stat-icon svg.lucide-loader,
.syllabus-stat-icon svg.lucide-loader-2 {
  animation: syllabusSpin 1s linear infinite;
}

@keyframes syllabusSpin {
  to { transform: rotate(360deg); }
}

.status-badge i[data-lucide] {
  width: 13px;
  height: 13px;
  opacity: 0.85;
}

.syllabus-review-info {
  background: rgba(12, 75, 52, 0.05);
  border-radius: 8px;
  padding: 12px;
  margin-top: 10px;
  font-size: 13px;
}

.syllabus-review-info strong {
  color: #0C4B34;
}

.syllabus-item-actions {
  display: flex;
  gap: 10px;
  margin-left: 20px;
}

.syllabus-action-btn {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.syllabus-action-btn.view {
  background: rgba(12, 75, 52, 0.1);
  color: #0C4B34;
}

.syllabus-action-btn.view:hover {
  background: rgba(12, 75, 52, 0.2);
}

.syllabus-action-btn.approve {
  background: #0C4B34;
  color: white;
}

.syllabus-action-btn.approve:hover {
  background: #0a3a28;
}

.syllabus-action-btn.reject {
  background: #dc3545;
  color: white;
}

.syllabus-action-btn.reject:hover {
  background: #c82333;
}

/* All Syllabi Table */
.syllabus-table {
  width: 100%;
  border-collapse: collapse;
}

.syllabus-table th {
  text-align: left;
  padding: 12px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(17, 24, 39, 0.5);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid rgba(12, 75, 52, 0.1);
}

.syllabus-table td {
  padding: 14px 12px;
  font-size: 13px;
  color: #333;
  border-bottom: 1px solid rgba(12, 75, 52, 0.05);
}

.syllabus-table tbody tr:hover {
  background: rgba(12, 75, 52, 0.02);
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.status-badge.submitted {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.status-badge.ph-review {
  background: rgba(139, 92, 246, 0.1);
  color: #7c3aed;
}

.status-badge.ph-approved {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.status-badge.dean-approved {
  background: rgba(12, 75, 52, 0.1);
  color: #0C4B34;
}

.status-badge.revision-requested {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.status-badge.not-started {
  background: rgba(156, 163, 175, 0.1);
  color: #6b7280;
}

/* Empty State */
.syllabus-empty {
  text-align: center;
  padding: 40px;
}

.syllabus-empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.syllabus-empty-title {
  font-size: 16px;
  font-weight: 700;
  color: #0C4B34;
}

.syllabus-empty-text {
  font-size: 13px;
  color: rgba(17, 24, 39, 0.6);
  margin-top: 8px;
}

/* Filter Tabs */
.filter-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.syllabus-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
  padding: 10px 12px;
  background: rgba(12, 75, 52, 0.04);
  border: 1px solid rgba(12, 75, 52, 0.1);
  border-radius: 12px;
  flex-wrap: wrap;
}

.syllabus-count {
  font-size: 12px;
  font-weight: 800;
  color: rgba(17, 24, 39, 0.6);
  letter-spacing: 0.2px;
  white-space: nowrap;
}

.filter-chips {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.filter-chip {
  border: 1px solid rgba(12, 75, 52, 0.16);
  background: rgba(12, 75, 52, 0.06);
  color: #0C4B34;
  padding: 8px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.2px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-chip:hover {
  background: rgba(12, 75, 52, 0.1);
  transform: translateY(-1px);
}

.filter-chip.active {
  background: linear-gradient(135deg, rgba(12, 75, 52, 0.14) 0%, rgba(15, 122, 83, 0.14) 100%);
  border-color: rgba(12, 75, 52, 0.28);
  box-shadow: 0 10px 22px rgba(12, 75, 52, 0.12);
}

.filter-chip:focus-visible {
  outline: 2px solid rgba(12, 75, 52, 0.45);
  outline-offset: 2px;
}

/* Search bar (DESIGN.md inspired) */
.syllabus-search {
  display: flex;
  align-items: center;
  background-color: #FFFFFF;
  height: 44px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid rgba(12, 75, 52, 0.14);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  margin-bottom: 12px;
}

.syllabus-search:focus-within {
  border-color: rgba(12, 75, 52, 0.28);
  box-shadow: 0 0 0 3px rgba(12, 75, 52, 0.08);
}

.syllabus-search input {
  border: none;
  outline: none;
  flex: 1;
  font-size: 14px;
  background: transparent;
  padding: 0 10px;
  font-family: 'TT Interphases', sans-serif;
}

.syllabus-search-clear {
  background: none;
  border: none;
  color: rgba(17, 24, 39, 0.4);
  cursor: pointer;
  padding: 4px;
  display: none;
}

/* Dark mode */
html[data-theme="dark"] .syllabus-toolbar {
  background: rgba(129, 199, 132, 0.08);
  border-color: #333;
}
html[data-theme="dark"] .syllabus-count {
  color: rgba(224, 224, 224, 0.75);
}
html[data-theme="dark"] .filter-chip {
  background: rgba(129, 199, 132, 0.12);
  border-color: #333;
  color: #81C784;
}
html[data-theme="dark"] .filter-chip.active {
  background: rgba(129, 199, 132, 0.18);
  border-color: rgba(129, 199, 132, 0.35);
  box-shadow: 0 10px 22px rgba(0, 0, 0, 0.35);
}
html[data-theme="dark"] .syllabus-search {
  background-color: #2d2d2d;
  border-color: #404040;
}
html[data-theme="dark"] .syllabus-search input {
  color: #e0e0e0;
}
html[data-theme="dark"] .syllabus-search input::placeholder {
  color: #808080;
}
</style>

<div class="syllabus-mgmt">
  <div class="syllabus-mgmt-header">
    <h1 class="syllabus-mgmt-title">Syllabus Management</h1>
    <p class="syllabus-mgmt-subtitle">Review and approve course syllabi after Program Head review</p>
  </div>

  <!-- Stats Cards -->
  <div class="syllabus-stats">
    <div class="syllabus-stat-card">
      <div class="syllabus-stat-icon"><i data-lucide="library" aria-hidden="true"></i></div>
      <div class="syllabus-stat-info">
        <h4 id="stat-total">0</h4>
        <p>Total Courses</p>
      </div>
    </div>
    <div class="syllabus-stat-card">
      <div class="syllabus-stat-icon"><i data-lucide="clock" aria-hidden="true"></i></div>
      <div class="syllabus-stat-info">
        <h4 id="stat-pending">0</h4>
        <p>Awaiting Dean Approval</p>
      </div>
    </div>
    <div class="syllabus-stat-card">
      <div class="syllabus-stat-icon"><i data-lucide="badge-check" aria-hidden="true"></i></div>
      <div class="syllabus-stat-info">
        <h4 id="stat-approved">0</h4>
        <p>Approved</p>
      </div>
    </div>
    <div class="syllabus-stat-card">
      <div class="syllabus-stat-icon"><i data-lucide="triangle-alert" aria-hidden="true"></i></div>
      <div class="syllabus-stat-info">
        <h4 id="stat-revision">0</h4>
        <p>Needs Revision</p>
      </div>
    </div>
  </div>

  <!-- Pending Dean Approval Section -->
  <div class="syllabus-section" id="pending-section">
    <div class="syllabus-section-header">
      <h2 class="syllabus-section-title">Pending Your Approval</h2>
      <span class="syllabus-section-badge" id="pending-count">0</span>
    </div>
    <div id="pending-syllabi-list" class="syllabus-approval-list">
      <div class="syllabus-empty">
        <div class="syllabus-empty-icon"><i data-lucide="clock" aria-hidden="true"></i></div>
        <div class="syllabus-empty-title">No Pending Reviews</div>
        <div class="syllabus-empty-text">All syllabi have been reviewed.</div>
      </div>
    </div>
  </div>

  <!-- All Syllabi Section -->
  <div class="syllabus-section">
    <div class="syllabus-section-header">
      <h2 class="syllabus-section-title">All Syllabi</h2>
    </div>
    
    <!-- Search -->
    <div class="syllabus-search">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(17, 24, 39, 0.4)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      <input id="syllabusSearchInput" type="text" placeholder="Search by course, teacher, program, or status..." oninput="onSyllabusSearchInput(this.value)">
      <button type="button" class="syllabus-search-clear" id="syllabusSearchClearBtn" onclick="clearSyllabusSearch()" aria-label="Clear search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Toolbar -->
    <div class="syllabus-toolbar" aria-live="polite">
      <div class="syllabus-count" id="allSyllabiCount">—</div>
      <div class="filter-chips" role="tablist" aria-label="Syllabi filters">
        <button class="filter-chip active" data-filter="all" role="tab" aria-selected="true">All</button>
        <button class="filter-chip" data-filter="ph_approved" role="tab" aria-selected="false">Ready for Approval</button>
        <button class="filter-chip" data-filter="dean_approved" role="tab" aria-selected="false">Approved</button>
        <button class="filter-chip" data-filter="revision_requested" role="tab" aria-selected="false">Needs Revision</button>
      </div>
    </div>
    
    <div id="all-syllabi-list">
      <div class="syllabus-empty">
        <div class="syllabus-empty-icon"><i data-lucide="loader-circle" aria-hidden="true"></i></div>
        <div class="syllabus-empty-title">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Syllabus View Modal -->
<div id="syllabusViewModal" class="modal-overlay" style="display: none;">
  <div class="premium-modal" style="width: min(980px, calc(100vw - 28px)); max-height: min(90vh, 840px);">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-title">Syllabus Review</h2>
      <button class="modal-close" type="button" onclick="closeSyllabusViewModal()" aria-label="Close modal">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    <div id="syllabus-modal-content" style="padding: 20px; overflow-y: auto;">
      <!-- Content loaded dynamically -->
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadSyllabusSummary();
  
  // Filter chip click handlers (no extra fetch; just re-render from cached payload)
  document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', function() {
      document.querySelectorAll('.filter-chip').forEach(t => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
      });
      this.classList.add('active');
      this.setAttribute('aria-selected', 'true');
      renderFromCache();
    });
  });
});

let syllabusSummaryCache = null;
let syllabusSearchDebounce = null;

function initLucideIcons() {
  if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
  }
}

function loadSyllabusSummary() {
  fetch('api/get_syllabus_summary.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        syllabusSummaryCache = data;
        // Update stats
        document.getElementById('stat-total').textContent = data.stats.total_courses || 0;
        document.getElementById('stat-pending').textContent = (data.pendingDeanApproval || []).length;
        document.getElementById('stat-approved').textContent = (data.stats.syllabi_dean_approved || 0);
        document.getElementById('stat-revision').textContent = (data.stats.syllabi_needs_revision || 0);
        document.getElementById('pending-count').textContent = (data.pendingDeanApproval || []).length;
        
        // Render pending approval list
        renderPendingApproval(data.pendingDeanApproval || []);
        
        renderFromCache();
        initLucideIcons();
      }
    })
    .catch(err => console.error('Error loading summary:', err));
}

function renderFromCache() {
  if (!syllabusSummaryCache) return;
  const activeFilter = document.querySelector('.filter-chip.active')?.dataset?.filter || 'all';
  renderAllSyllabi(syllabusSummaryCache.allSyllabi || [], activeFilter);
  initLucideIcons();
}

function onSyllabusSearchInput(value) {
  const clearBtn = document.getElementById('syllabusSearchClearBtn');
  clearBtn.style.display = value.trim() ? 'block' : 'none';
  if (syllabusSearchDebounce) clearTimeout(syllabusSearchDebounce);
  syllabusSearchDebounce = setTimeout(() => renderFromCache(), 120);
}

function clearSyllabusSearch() {
  const input = document.getElementById('syllabusSearchInput');
  if (input) input.value = '';
  const clearBtn = document.getElementById('syllabusSearchClearBtn');
  if (clearBtn) clearBtn.style.display = 'none';
  renderFromCache();
}

function renderPendingApproval(syllabi) {
  const container = document.getElementById('pending-syllabi-list');
  
  if (!syllabi.length) {
    container.innerHTML = `
      <div class="syllabus-empty">
        <div class="syllabus-empty-icon"><i data-lucide="badge-check" aria-hidden="true"></i></div>
        <div class="syllabus-empty-title">All Caught Up!</div>
        <div class="syllabus-empty-text">No syllabi awaiting your final approval.</div>
      </div>
    `;
    initLucideIcons();
    return;
  }
  
  container.innerHTML = syllabi.map(s => `
    <div class="syllabus-approval-item">
      <div class="syllabus-item-info">
        <div class="syllabus-item-header">
          <span class="syllabus-course-code">${escapeHtml(s.course_code)}</span>
          <span class="syllabus-item-title">${escapeHtml(s.course_title)}</span>
        </div>
        <div class="syllabus-item-meta">
          <span><i data-lucide="user" aria-hidden="true"></i>${escapeHtml(s.teacher_name)}</span>
          <span><i data-lucide="graduation-cap" aria-hidden="true"></i>${escapeHtml(s.program_name || 'General')}</span>
          <span><i data-lucide="calendar-days" aria-hidden="true"></i>PH Approved: ${new Date(s.ph_approved_at).toLocaleDateString()}</span>
        </div>
        ${s.ph_review_comments ? `
          <div class="syllabus-review-info">
            <strong>PH Comments:</strong> ${escapeHtml(s.ph_review_comments)}
          </div>
        ` : ''}
      </div>
      <div class="syllabus-item-actions">
        <button class="syllabus-action-btn view" onclick="viewSyllabus(${s.id})">View</button>
        <button class="syllabus-action-btn approve" onclick="approveSyllabus(${s.id})">Approve</button>
        <button class="syllabus-action-btn reject" onclick="requestRevision(${s.id})">Request Revision</button>
      </div>
    </div>
  `).join('');
  initLucideIcons();
}

function renderAllSyllabi(syllabi, filter) {
  const container = document.getElementById('all-syllabi-list');
  
  // Filter syllabi based on active filter + search
  let filtered = syllabi;
  if (filter === 'ph_approved') {
    filtered = syllabi.filter(s => s.status === 'ph_approved');
  } else if (filter === 'dean_approved') {
    filtered = syllabi.filter(s => s.status === 'dean_approved');
  } else if (filter === 'revision_requested') {
    filtered = syllabi.filter(s => s.status === 'revision_requested');
  }

  const searchValue = document.getElementById('syllabusSearchInput')?.value ?? '';
  const search = searchValue.trim().toLowerCase();
  if (search) {
    filtered = filtered.filter(s => {
      const haystack = [
        s.course_code,
        s.course_title,
        s.teacher_name,
        s.program_name,
        s.status
      ].filter(Boolean).join(' ').toLowerCase();
      return haystack.includes(search);
    });
  }

  updateAllSyllabiCount(filtered.length, syllabi.length);
  
  if (!filtered.length) {
    container.innerHTML = `
      <div class="syllabus-empty">
        <div class="syllabus-empty-icon"><i data-lucide="file-search" aria-hidden="true"></i></div>
        <div class="syllabus-empty-title">No Syllabi Found</div>
        <div class="syllabus-empty-text">${search ? 'No syllabi match your search.' : 'No syllabi match the selected filter.'}</div>
      </div>
    `;
    initLucideIcons();
    return;
  }
  
  container.innerHTML = `
    <table class="syllabus-table">
      <thead>
        <tr>
          <th>Course</th>
          <th>Teacher</th>
          <th>Program</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        ${filtered.map(s => `
          <tr>
            <td>
              <span style="background: #0C4B34; color: white; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                ${escapeHtml(s.course_code)}
              </span>
              ${escapeHtml(s.course_title)}
            </td>
            <td>${escapeHtml(s.teacher_name)}</td>
            <td>${escapeHtml(s.program_name || '-')}</td>
            <td>${getStatusBadge(s.status)}</td>
            <td>${s.submitted_at ? new Date(s.submitted_at).toLocaleDateString() : '-'}</td>
            <td>
              <button class="syllabus-action-btn view" onclick="viewSyllabus(${s.id})">View</button>
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

function updateAllSyllabiCount(visibleCount, totalCount) {
  const el = document.getElementById('allSyllabiCount');
  if (!el) return;
  if (!totalCount) {
    el.textContent = '0 syllabi';
    return;
  }
  if (visibleCount === totalCount) {
    el.textContent = `${totalCount} syllabi`;
    return;
  }
  el.textContent = `${visibleCount} of ${totalCount} syllabi`;
}

function getStatusBadge(status) {
  const badges = {
    'draft': '<span class="status-badge not-started"><i data-lucide="pencil" aria-hidden="true"></i>Draft</span>',
    'submitted': '<span class="status-badge submitted"><i data-lucide="send" aria-hidden="true"></i>Submitted</span>',
    'ph_review': '<span class="status-badge ph-review"><i data-lucide="scan-eye" aria-hidden="true"></i>PH Review</span>',
    'ph_approved': '<span class="status-badge ph-approved"><i data-lucide="badge-check" aria-hidden="true"></i>PH Approved</span>',
    'dean_approved': '<span class="status-badge dean-approved"><i data-lucide="award" aria-hidden="true"></i>Approved</span>',
    'revision_requested': '<span class="status-badge revision-requested"><i data-lucide="triangle-alert" aria-hidden="true"></i>Revision</span>'
  };
  return badges[status] || badges['not-started'];
}

function viewSyllabus(syllabusId) {
  fetch(`api/get_syllabus_details.php?syllabus_id=${syllabusId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showSyllabusModal(data.syllabus);
      }
    });
}

function showSyllabusModal(syllabus) {
  document.getElementById('modal-title').textContent = `${syllabus.course_code} - ${syllabus.course_title}`;
  
  const content = `
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Course Information</h3>
      <p><strong>Teacher:</strong> ${escapeHtml(syllabus.teacher_name)}</p>
      <p><strong>Program:</strong> ${escapeHtml(syllabus.program_name || 'General')}</p>
      <p><strong>Units:</strong> ${syllabus.units}</p>
      <p><strong>Year Level:</strong> ${escapeHtml(syllabus.year_level || '-')}</p>
    </div>
    
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Course Description</h3>
      <p style="background: #f8f9fa; padding: 12px; border-radius: 8px;">${escapeHtml(syllabus.course_description || 'Not provided')}</p>
    </div>
    
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Expected Course Outcomes (PEO)</h3>
      <p style="background: #f8f9fa; padding: 12px; border-radius: 8px;">${escapeHtml(syllabus.expected_course_outcomes || 'Not provided')}</p>
    </div>
    
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Grading System</h3>
      <div style="background: #f8f9fa; padding: 12px; border-radius: 8px;">
        ${formatGradingSystem(syllabus.grading_system)}
      </div>
    </div>
    
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Course Requirements</h3>
      <p style="background: #f8f9fa; padding: 12px; border-radius: 8px;">${escapeHtml(syllabus.course_requirements || 'Not provided')}</p>
    </div>
    
    <div style="margin-bottom: 20px;">
      <h3 style="color: #0C4B34; margin-bottom: 12px;">Remote/Online Policies</h3>
      <p style="background: #f8f9fa; padding: 12px; border-radius: 8px;">${escapeHtml(syllabus.remote_policies || 'Not provided')}</p>
    </div>
    
    ${syllabus.ph_review_comments ? `
      <div style="margin-bottom: 20px; background: rgba(16, 185, 129, 0.1); padding: 12px; border-radius: 8px; border-left: 4px solid #059669;">
        <h3 style="color: #059669; margin-bottom: 8px;">Program Head Review</h3>
        <p>${escapeHtml(syllabus.ph_review_comments)}</p>
      </div>
    ` : ''}
  `;
  
  document.getElementById('syllabus-modal-content').innerHTML = content;
  document.getElementById('syllabusViewModal').style.display = 'flex';
  initLucideIcons();
}

function formatGradingSystem(gradingJson) {
  try {
    const grading = JSON.parse(gradingJson);
    if (grading.components) {
      return grading.components.map(c => `
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
          <span>${escapeHtml(c.name)}</span>
          <span style="font-weight: 700; color: #0C4B34;">${c.percentage}%</span>
        </div>
      `).join('');
    }
  } catch (e) {}
  return 'Not provided';
}

function closeSyllabusViewModal() {
  document.getElementById('syllabusViewModal').style.display = 'none';
}

function approveSyllabus(syllabusId) {
  const comments = prompt('Add approval comments (optional):');
  
  fetch('api/dean_approve_syllabus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ syllabus_id: syllabusId, comments: comments })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Syllabus approved successfully!');
      loadSyllabusSummary();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function requestRevision(syllabusId) {
  const note = prompt('Please provide feedback for the revision:');
  if (!note) return;
  
  fetch('api/dean_request_revision.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ syllabus_id: syllabusId, note: note })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Revision requested!');
      loadSyllabusSummary();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

// Close modal when clicking outside
document.getElementById('syllabusViewModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeSyllabusViewModal();
  }
});

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>
