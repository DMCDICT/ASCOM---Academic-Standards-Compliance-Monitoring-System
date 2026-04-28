<?php
// syllabus_modal.php
// Comprehensive Course Syllabus Modal for Teachers
// Includes: Books, E-books, Web Resources, Course Description, PEO, Learning Plan, Exams, Grading, Policies, References
?>

<div id="syllabusModal" class="syllabus-modal-overlay" style="display: none;">
  <div class="syllabus-modal-box">
    <div class="syllabus-modal-header">
      <div>
        <h2 class="syllabus-modal-title" id="syllabusModalTitle">Course Syllabus</h2>
        <p class="syllabus-modal-subtitle" id="syllabusModalSubtitle">Fill in the course syllabus details</p>
      </div>
      <button class="syllabus-modal-close" type="button" onclick="closeSyllabusModal()">&times;</button>
    </div>
    
    <div class="syllabus-modal-content">
      <form id="syllabusForm">
        <!-- Hidden fields -->
        <input type="hidden" id="syllabus_course_id" name="course_id">
        <input type="hidden" id="syllabus_teacher_id" name="teacher_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">
        <input type="hidden" id="syllabus_academic_year" name="academic_year">
        <input type="hidden" id="syllabus_term" name="term">
        
        <!-- Navigation Tabs -->
        <div class="syllabus-tabs">
          <button type="button" class="syllabus-tab active" data-tab="overview">Course Overview</button>
          <button type="button" class="syllabus-tab" data-tab="resources">Resources</button>
          <button type="button" class="syllabus-tab" data-tab="learning">Learning Plan</button>
          <button type="button" class="syllabus-tab" data-tab="exams">Exams</button>
          <button type="button" class="syllabus-tab" data-tab="grading">Grading</button>
          <button type="button" class="syllabus-tab" data-tab="policies">Policies</button>
          <button type="button" class="syllabus-tab" data-tab="references">References</button>
        </div>
        
        <!-- Tab Content: Course Overview -->
        <div class="syllabus-tab-content active" id="tab-overview">
          <div class="form-section">
            <h3 class="form-section-title">Course Information</h3>
            
            <div class="form-group">
              <label for="course_description">Course Description <span class="required">*</span></label>
              <textarea id="course_description" name="course_description" rows="4" placeholder="Provide a detailed description of this course..." required></textarea>
            </div>
            
            <div class="form-group">
              <label for="course_objectives">Course Objectives</label>
              <textarea id="course_objectives" name="course_objectives" rows="3" placeholder="What are the main objectives of this course?"></textarea>
            </div>
            
            <div class="form-group">
              <label for="expected_course_outcomes">Expected Course Outcomes (PEO/Program Outcomes) <span class="required">*</span></label>
              <textarea id="expected_course_outcomes" name="expected_course_outcomes" rows="4" placeholder="List the expected course outcomes or Program Educational Objectives (PEO)..." required></textarea>
              <small class="form-hint">Describe what students will be able to do after completing this course.</small>
            </div>
          </div>
        </div>
        
        <!-- Tab Content: Resources -->
        <div class="syllabus-tab-content" id="tab-resources">
          <div class="form-section">
            <h3 class="form-section-title">Learning Resources</h3>
            
            <!-- Books Section -->
            <div class="resource-section">
              <div class="resource-header">
                <h4>Textbooks</h4>
                <button type="button" class="add-resource-btn" onclick="addBookField()">+ Add Book</button>
              </div>
              <div id="books-container" class="resource-container">
                <!-- Book fields will be added here -->
              </div>
            </div>
            
            <!-- E-books Section -->
            <div class="resource-section">
              <div class="resource-header">
                <h4>E-Books / Digital Resources</h4>
                <button type="button" class="add-resource-btn" onclick="addEbookField()">+ Add E-Book</button>
              </div>
              <div id="ebooks-container" class="resource-container">
                <!-- E-book fields will be added here -->
              </div>
            </div>
            
            <!-- Web Resources Section -->
            <div class="resource-section">
              <div class="resource-header">
                <h4>Web Resources / Online Articles</h4>
                <button type="button" class="add-resource-btn" onclick="addWebResourceField()">+ Add Web Resource</button>
              </div>
              <div id="web-resources-container" class="resource-container">
                <!-- Web resource fields will be added here -->
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tab Content: Learning Plan -->
        <div class="syllabus-tab-content" id="tab-learning">
          <div class="form-section">
            <h3 class="form-section-title">Weekly Learning Plan</h3>
            <p class="form-description">Outline the topics, activities, and assessments for each week.</p>
            
            <div id="learning-plan-container">
              <!-- Learning plan weeks will be added here -->
            </div>
            
            <button type="button" class="add-week-btn" onclick="addLearningWeek()">+ Add Week</button>
          </div>
        </div>
        
        <!-- Tab Content: Exams -->
        <div class="syllabus-tab-content" id="tab-exams">
          <div class="form-section">
            <h3 class="form-section-title">Exam Schedules</h3>
            <p class="form-description">Define the exam periods with duration and covered topics.</p>
            
            <div class="exam-sections">
              <!-- Prelim Exam -->
              <div class="exam-section">
                <div class="exam-header">
                  <h4>Prelim Examination</h4>
                </div>
                <div class="exam-fields">
                  <div class="form-row">
                    <div class="form-group">
                      <label>Duration (minutes)</label>
                      <input type="number" id="prelim_duration" name="prelim_duration" min="30" max="180" placeholder="e.g., 60">
                    </div>
                    <div class="form-group">
                      <label>Week(s) Covered</label>
                      <input type="text" id="prelim_weeks" name="prelim_weeks" placeholder="e.g., Weeks 1-4">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Topics Covered</label>
                    <textarea id="prelim_topics" name="prelim_topics" rows="3" placeholder="List the topics to be covered in the Prelim exam..."></textarea>
                  </div>
                </div>
              </div>
              
              <!-- Midterm Exam -->
              <div class="exam-section">
                <div class="exam-header">
                  <h4>Midterm Examination</h4>
                </div>
                <div class="exam-fields">
                  <div class="form-row">
                    <div class="form-group">
                      <label>Duration (minutes)</label>
                      <input type="number" id="midterm_duration" name="midterm_duration" min="30" max="180" placeholder="e.g., 60">
                    </div>
                    <div class="form-group">
                      <label>Week(s) Covered</label>
                      <input type="text" id="midterm_weeks" name="midterm_weeks" placeholder="e.g., Weeks 5-8">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Topics Covered</label>
                    <textarea id="midterm_topics" name="midterm_topics" rows="3" placeholder="List the topics to be covered in the Midterm exam..."></textarea>
                  </div>
                </div>
              </div>
              
              <!-- Prefinal Exam -->
              <div class="exam-section">
                <div class="exam-header">
                  <h4>Prefinal Examination</h4>
                </div>
                <div class="exam-fields">
                  <div class="form-row">
                    <div class="form-group">
                      <label>Duration (minutes)</label>
                      <input type="number" id="prefinal_duration" name="prefinal_duration" min="30" max="180" placeholder="e.g., 60">
                    </div>
                    <div class="form-group">
                      <label>Week(s) Covered</label>
                      <input type="text" id="prefinal_weeks" name="prefinal_weeks" placeholder="e.g., Weeks 9-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Topics Covered</label>
                    <textarea id="prefinal_topics" name="prefinal_topics" rows="3" placeholder="List the topics to be covered in the Prefinal exam..."></textarea>
                  </div>
                </div>
              </div>
              
              <!-- Final Exam -->
              <div class="exam-section">
                <div class="exam-header">
                  <h4>Final Examination</h4>
                </div>
                <div class="exam-fields">
                  <div class="form-row">
                    <div class="form-group">
                      <label>Duration (minutes)</label>
                      <input type="number" id="final_duration" name="final_duration" min="30" max="180" placeholder="e.g., 90">
                    </div>
                    <div class="form-group">
                      <label>Week(s) Covered</label>
                      <input type="text" id="final_weeks" name="final_weeks" placeholder="e.g., Weeks 13-16">
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Topics Covered</label>
                    <textarea id="final_topics" name="final_topics" rows="3" placeholder="List the topics to be covered in the Final exam..."></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tab Content: Grading -->
        <div class="syllabus-tab-content" id="tab-grading">
          <div class="form-section">
            <h3 class="form-section-title">Grading System</h3>
            <p class="form-description">Define the grading components and their weights.</p>
            
            <div id="grading-components-container">
              <!-- Grading components will be added here -->
            </div>
            
            <button type="button" class="add-week-btn" onclick="addGradingComponent()">+ Add Grading Component</button>
            
            <div class="grading-summary">
              <strong>Total: </strong><span id="grading-total">0%</span>
            </div>
          </div>
          
          <div class="form-section" style="margin-top: 20px;">
            <h3 class="form-section-title">Course Requirements</h3>
            <div class="form-group">
              <label for="course_requirements">Course Requirements <span class="required">*</span></label>
              <textarea id="course_requirements" name="course_requirements" rows="4" placeholder="List all course requirements (attendance, projects, labs, etc.)..." required></textarea>
            </div>
          </div>
          
          <div class="form-section" style="margin-top: 20px;">
            <h3 class="form-section-title">Course Expectations</h3>
            <div class="form-group">
              <label for="course_expectations">Course Expectations</label>
              <textarea id="course_expectations" name="course_expectations" rows="4" placeholder="What do you expect from students in this course?"></textarea>
            </div>
          </div>
        </div>
        
        <!-- Tab Content: Policies -->
        <div class="syllabus-tab-content" id="tab-policies">
          <div class="form-section">
            <h3 class="form-section-title">Remote / Online Classroom Policies</h3>
            <div class="form-group">
              <label for="remote_policies">Online Classroom Policies <span class="required">*</span></label>
              <textarea id="remote_policies" name="remote_policies" rows="8" placeholder="Define policies for online learning, virtual classroom etiquette, attendance, participation, etc..." required></textarea>
              <small class="form-hint">Include guidelines for video conferencing, submission of assignments, communication, etc.</small>
            </div>
          </div>
        </div>
        
        <!-- Tab Content: References -->
        <div class="syllabus-tab-content" id="tab-references">
          <div class="form-section">
            <h3 class="form-section-title">References (APA 7th Edition)</h3>
            <p class="form-description">Add all references used in this course syllabus in APA 7 format.</p>
            
            <div id="references-container">
              <!-- Reference fields will be added here -->
            </div>
            
            <button type="button" class="add-week-btn" onclick="addReference()">+ Add Reference</button>
            
            <div class="apa-guide">
              <h4>APA 7th Edition Format Guide</h4>
              <ul>
                <li><strong>Book:</strong> Author, A. A. (Year). <em>Title of work: Capital letter also for subtitle</em>. Publisher.</li>
                <li><strong>Journal Article:</strong> Author, A. A., & Author, B. B. (Year). Title of article. <em>Journal Name, Volume</em>(Issue), pages. DOI</li>
                <li><strong>Website:</strong> Author, A. A. (Year, Month Day). <em>Title of page</em>. Site Name. URL</li>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- Form Actions -->
        <div class="syllabus-form-actions">
          <button type="button" class="syllabus-btn-cancel" onclick="closeSyllabusModal()">CANCEL</button>
          <button type="button" class="syllabus-btn-draft" onclick="saveSyllabusDraft()">SAVE DRAFT</button>
          <button type="button" class="syllabus-btn-submit" onclick="submitSyllabus()">SUBMIT FOR APPROVAL</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Syllabus Modal Styles */
.syllabus-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  overflow-y: auto;
  padding: 20px;
}

.syllabus-modal-box {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 900px;
  max-height: 95vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.syllabus-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: linear-gradient(0deg, rgba(12, 75, 52, 0.05), rgba(12, 75, 52, 0.02)), #ffffff;
  border-bottom: 1px solid rgba(12, 75, 52, 0.12);
}

.syllabus-modal-title {
  margin: 0;
  color: #0C4B34;
  font-size: 20px;
  font-weight: 800;
}

.syllabus-modal-subtitle {
  margin: 4px 0 0 0;
  color: rgba(17, 24, 39, 0.6);
  font-size: 14px;
}

.syllabus-modal-close {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid rgba(12, 75, 52, 0.2);
  background: rgba(12, 75, 52, 0.05);
  color: #0C4B34;
  font-size: 22px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.syllabus-modal-close:hover {
  background: rgba(12, 75, 52, 0.1);
}

.syllabus-modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
}

/* Tabs */
.syllabus-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 20px;
  border-bottom: 2px solid #eee;
  overflow-x: auto;
  padding-bottom: 2px;
}

.syllabus-tab {
  padding: 10px 16px;
  background: none;
  border: none;
  border-radius: 8px 8px 0 0;
  color: rgba(17, 24, 39, 0.6);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.syllabus-tab:hover {
  background: rgba(12, 75, 52, 0.05);
  color: #0C4B34;
}

.syllabus-tab.active {
  background: #0C4B34;
  color: white;
}

/* Tab Content */
.syllabus-tab-content {
  display: none;
}

.syllabus-tab-content.active {
  display: block;
  animation: fadeSlideUp 0.3s ease-out;
}

.form-section {
  background: #ffffff;
  border: 1px solid rgba(12, 75, 52, 0.1);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.form-section-title {
  margin: 0 0 16px 0;
  color: #0C4B34;
  font-size: 16px;
  font-weight: 700;
}

.form-description {
  color: rgba(17, 24, 39, 0.6);
  font-size: 13px;
  margin-bottom: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  color: #333;
  font-size: 13px;
  font-weight: 600;
}

.form-group label .required {
  color: #dc3545;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  font-family: 'TT Interphases', sans-serif;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: #0C4B34;
  outline: none;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-hint {
  display: block;
  margin-top: 4px;
  color: rgba(17, 24, 39, 0.5);
  font-size: 12px;
}

.form-row {
  display: flex;
  gap: 16px;
}

.form-row .form-group {
  flex: 1;
}

/* Resource Sections */
.resource-section {
  margin-bottom: 20px;
}

.resource-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.resource-header h4 {
  margin: 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.add-resource-btn {
  padding: 6px 12px;
  background: rgba(12, 75, 52, 0.1);
  border: 1px solid rgba(12, 75, 52, 0.2);
  border-radius: 6px;
  color: #0C4B34;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.add-resource-btn:hover {
  background: rgba(12, 75, 52, 0.2);
}

.resource-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.resource-item {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 12px;
}

.resource-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.resource-item-number {
  font-weight: 700;
  color: #0C4B34;
  font-size: 13px;
}

.remove-resource-btn {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 18px;
  cursor: pointer;
  padding: 2px 6px;
}

.resource-item-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

/* Learning Plan */
.add-week-btn {
  width: 100%;
  padding: 12px;
  background: rgba(12, 75, 52, 0.08);
  border: 2px dashed rgba(12, 75, 52, 0.3);
  border-radius: 8px;
  color: #0C4B34;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.add-week-btn:hover {
  background: rgba(12, 75, 52, 0.15);
}

.learning-week {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 12px;
}

.week-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.week-number {
  font-weight: 700;
  color: #0C4B34;
  font-size: 14px;
}

.week-fields {
  display: grid;
  gap: 10px;
}

/* Exam Sections */
.exam-sections {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.exam-section {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 10px;
  overflow: hidden;
}

.exam-header {
  background: rgba(12, 75, 52, 0.1);
  padding: 12px 16px;
}

.exam-header h4 {
  margin: 0;
  color: #0C4B34;
  font-size: 14px;
  font-weight: 700;
}

.exam-fields {
  padding: 16px;
}

/* Grading */
.grading-component {
  background: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
}

.grading-component-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.grading-component-number {
  font-weight: 700;
  color: #0C4B34;
  font-size: 13px;
}

.grading-component-fields {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 10px;
  align-items: end;
}

.grading-component-fields input {
  padding: 8px 10px;
}

.grading-summary {
  margin-top: 16px;
  padding: 12px;
  background: rgba(12, 75, 52, 0.1);
  border-radius: 8px;
  text-align: right;
  font-size: 14px;
}

.grading-summary span {
  color: #0C4B34;
  font-weight: 700;
  font-size: 18px;
}

/* APA Guide */
.apa-guide {
  margin-top: 20px;
  padding: 16px;
  background: #f0f7f4;
  border-radius: 8px;
  border-left: 4px solid #0C4B34;
}

.apa-guide h4 {
  margin: 0 0 10px 0;
  color: #0C4B34;
  font-size: 14px;
}

.apa-guide ul {
  margin: 0;
  padding-left: 20px;
}

.apa-guide li {
  margin-bottom: 6px;
  color: rgba(17, 24, 39, 0.8);
  font-size: 12px;
}

/* Form Actions */
.syllabus-form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #eee;
  margin-top: 20px;
}

.syllabus-btn-cancel {
  padding: 10px 20px;
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 8px;
  color: #666;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.syllabus-btn-cancel:hover {
  background: #eee;
}

.syllabus-btn-draft {
  padding: 10px 20px;
  background: #fff;
  border: 2px solid #0C4B34;
  border-radius: 8px;
  color: #0C4B34;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.syllabus-btn-draft:hover {
  background: rgba(12, 75, 52, 0.05);
}

.syllabus-btn-submit {
  padding: 10px 24px;
  background: #0C4B34;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.syllabus-btn-submit:hover {
  background: #0a3a28;
}

/* Responsive */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }
  
  .resource-item-fields {
    grid-template-columns: 1fr;
  }
  
  .grading-component-fields {
    grid-template-columns: 1fr;
  }
  
  .syllabus-tabs {
    flex-wrap: nowrap;
  }
}
</style>

<script>
// Global variables
let currentSyllabusData = null;
let bookCount = 0;
let ebookCount = 0;
let webResourceCount = 0;
let learningWeekCount = 0;
let gradingComponentCount = 0;
let referenceCount = 0;

// Initialize the syllabus modal
function openSyllabusModal(courseId, courseName, academicYear, term) {
  document.getElementById('syllabus_course_id').value = courseId;
  document.getElementById('syllabus_academic_year').value = academicYear || '';
  document.getElementById('syllabus_term').value = term || '';
  
  document.getElementById('syllabusModalTitle').textContent = courseName || 'Course Syllabus';
  document.getElementById('syllabusModalSubtitle').textContent = academicYear && term ? `${term}, ${academicYear}` : 'Fill in the course syllabus details';
  
  // Load existing syllabus if available
  loadSyllabusData(courseId);
  
  // Reset form
  document.getElementById('syllabusForm').reset();
  
  // Reset counters
  bookCount = 0;
  ebookCount = 0;
  webResourceCount = 0;
  learningWeekCount = 0;
  gradingComponentCount = 0;
  referenceCount = 0;
  
  // Clear containers
  document.getElementById('books-container').innerHTML = '';
  document.getElementById('ebooks-container').innerHTML = '';
  document.getElementById('web-resources-container').innerHTML = '';
  document.getElementById('learning-plan-container').innerHTML = '';
  document.getElementById('grading-components-container').innerHTML = '';
  document.getElementById('references-container').innerHTML = '';
  
  // Add default grading components
  addDefaultGradingComponents();
  
  // Show modal
  document.getElementById('syllabusModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  
  // Switch to first tab
  switchTab('overview');
}

function closeSyllabusModal() {
  document.getElementById('syllabusModal').style.display = 'none';
  document.body.style.overflow = '';
}

// Tab switching
function switchTab(tabName) {
  // Update tab buttons
  document.querySelectorAll('.syllabus-tab').forEach(tab => {
    tab.classList.remove('active');
    if (tab.dataset.tab === tabName) {
      tab.classList.add('active');
    }
  });
  
  // Update tab content
  document.querySelectorAll('.syllabus-tab-content').forEach(content => {
    content.classList.remove('active');
  });
  document.getElementById('tab-' + tabName).classList.add('active');
}

// Add event listeners for tabs
document.querySelectorAll('.syllabus-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    switchTab(this.dataset.tab);
  });
});

// Load existing syllabus data
function loadSyllabusData(courseId) {
  fetch(`api/get_syllabus_data.php?course_id=${courseId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.syllabus) {
        currentSyllabusData = data.syllabus;
        populateForm(data.syllabus);
      }
    })
    .catch(err => console.error('Error loading syllabus:', err));
}

// Populate form with existing data
function populateForm(syllabus) {
  // Overview
  document.getElementById('course_description').value = syllabus.course_description || '';
  document.getElementById('course_objectives').value = syllabus.course_objectives || '';
  document.getElementById('expected_course_outcomes').value = syllabus.expected_course_outcomes || '';
  
  // Books
  if (syllabus.books) {
    try {
      const books = JSON.parse(syllabus.books);
      books.forEach(book => addBookField(book));
    } catch (e) {}
  }
  
  // E-books
  if (syllabus.ebooks) {
    try {
      const ebooks = JSON.parse(syllabus.ebooks);
      ebooks.forEach(ebook => addEbookField(ebook));
    } catch (e) {}
  }
  
  // Web Resources
  if (syllabus.web_resources) {
    try {
      const webResources = JSON.parse(syllabus.web_resources);
      webResources.forEach(web => addWebResourceField(web));
    } catch (e) {}
  }
  
  // Learning Plan
  if (syllabus.learning_plan) {
    try {
      const plan = JSON.parse(syllabus.learning_plan);
      plan.forEach(week => addLearningWeek(week));
    } catch (e) {}
  }
  
  // Exam Schedules
  if (syllabus.exam_schedules) {
    try {
      const exams = JSON.parse(syllabus.exam_schedules);
      if (exams.prelim) {
        document.getElementById('prelim_duration').value = exams.prelim.duration || '';
        document.getElementById('prelim_weeks').value = exams.prelim.weeks || '';
        document.getElementById('prelim_topics').value = exams.prelim.topics || '';
      }
      if (exams.midterm) {
        document.getElementById('midterm_duration').value = exams.midterm.duration || '';
        document.getElementById('midterm_weeks').value = exams.midterm.weeks || '';
        document.getElementById('midterm_topics').value = exams.midterm.topics || '';
      }
      if (exams.prefinal) {
        document.getElementById('prefinal_duration').value = exams.prefinal.duration || '';
        document.getElementById('prefinal_weeks').value = exams.prefinal.weeks || '';
        document.getElementById('prefinal_topics').value = exams.prefinal.topics || '';
      }
      if (exams.final) {
        document.getElementById('final_duration').value = exams.final.duration || '';
        document.getElementById('final_weeks').value = exams.final.weeks || '';
        document.getElementById('final_topics').value = exams.final.topics || '';
      }
    } catch (e) {}
  }
  
  // Grading
  if (syllabus.grading_system) {
    try {
      const grading = JSON.parse(syllabus.grading_system);
      if (grading.components) {
        document.getElementById('grading-components-container').innerHTML = '';
        gradingComponentCount = 0;
        grading.components.forEach(comp => addGradingComponent(comp));
      }
    } catch (e) {}
  }
  
  // Policies and requirements
  document.getElementById('course_requirements').value = syllabus.course_requirements || '';
  document.getElementById('course_expectations').value = syllabus.course_expectations || '';
  document.getElementById('remote_policies').value = syllabus.remote_policies || '';
  
  // References
  if (syllabus.references) {
    try {
      const refs = JSON.parse(syllabus.references);
      refs.forEach(ref => addReference(ref));
    } catch (e) {}
  }
}

// Add Book Field
function addBookField(book = null) {
  bookCount++;
  const container = document.getElementById('books-container');
  const div = document.createElement('div');
  div.className = 'resource-item';
  div.dataset.id = bookCount;
  div.innerHTML = `
    <div class="resource-item-header">
      <span class="resource-item-number">Book #${bookCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('books', ${bookCount})">&times;</button>
    </div>
    <div class="resource-item-fields">
      <input type="text" placeholder="Book Title" class="book-title" value="${book?.title || ''}">
      <input type="text" placeholder="Author (Last, First)" class="book-author" value="${book?.author || ''}">
      <input type="text" placeholder="ISBN" class="book-isbn" value="${book?.isbn || ''}">
      <input type="text" placeholder="Publisher" class="book-publisher" value="${book?.publisher || ''}">
      <input type="number" placeholder="Year" class="book-year" value="${book?.year || ''}">
      <input type="text" placeholder="Edition" class="book-edition" value="${book?.edition || ''}">
    </div>
  `;
  container.appendChild(div);
}

// Add E-book Field
function addEbookField(ebook = null) {
  ebookCount++;
  const container = document.getElementById('ebooks-container');
  const div = document.createElement('div');
  div.className = 'resource-item';
  div.dataset.id = ebookCount;
  div.innerHTML = `
    <div class="resource-item-header">
      <span class="resource-item-number">E-Book #${ebookCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('ebooks', ${ebookCount})">&times;</button>
    </div>
    <div class="resource-item-fields">
      <input type="text" placeholder="E-Book Title" class="ebook-title" value="${ebook?.title || ''}">
      <input type="text" placeholder="Author" class="ebook-author" value="${ebook?.author || ''}">
      <input type="url" placeholder="URL" class="ebook-url" value="${ebook?.url || ''}">
      <input type="text" placeholder="Publisher" class="ebook-publisher" value="${ebook?.publisher || ''}">
      <input type="number" placeholder="Year" class="ebook-year" value="${ebook?.year || ''}">
      <input type="date" placeholder="Access Date" class="ebook-access-date" value="${ebook?.access_date || ''}">
    </div>
  `;
  container.appendChild(div);
}

// Add Web Resource Field
function addWebResourceField(webResource = null) {
  webResourceCount++;
  const container = document.getElementById('web-resources-container');
  const div = document.createElement('div');
  div.className = 'resource-item';
  div.dataset.id = webResourceCount;
  div.innerHTML = `
    <div class="resource-item-header">
      <span class="resource-item-number">Web Resource #${webResourceCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('web_resources', ${webResourceCount})">&times;</button>
    </div>
    <div class="resource-item-fields">
      <input type="text" placeholder="Title" class="web-title" value="${webResource?.title || ''}">
      <input type="url" placeholder="URL" class="web-url" value="${webResource?.url || ''}">
      <input type="date" placeholder="Access Date" class="web-access-date" value="${webResource?.access_date || ''}">
      <input type="text" placeholder="Description" class="web-description" value="${webResource?.description || ''}">
    </div>
  `;
  container.appendChild(div);
}

// Add Learning Week
function addLearningWeek(week = null) {
  learningWeekCount++;
  const container = document.getElementById('learning-plan-container');
  const div = document.createElement('div');
  div.className = 'learning-week';
  div.dataset.week = learningWeekCount;
  div.innerHTML = `
    <div class="week-header">
      <span class="week-number">Week ${learningWeekCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('learning_week', ${learningWeekCount})">&times;</button>
    </div>
    <div class="week-fields">
      <input type="text" placeholder="Week Topic/Title" class="week-topic" value="${week?.topic || ''}">
      <textarea placeholder="Learning Activities" class="week-activities" rows="2">${week?.activities || ''}</textarea>
      <textarea placeholder="Assessment/Quiz/Lab" class="week-assessment" rows="2">${week?.assessment || ''}</textarea>
    </div>
  `;
  container.appendChild(div);
}

// Default Grading Components
function addDefaultGradingComponents() {
  const defaults = [
    { name: 'Written Exams', percentage: 40 },
    { name: 'Performance Tasks', percentage: 30 },
    { name: 'Quizzes', percentage: 20 },
    { name: 'Participation', percentage: 10 }
  ];
  defaults.forEach(comp => addGradingComponent(comp));
}

// Add Grading Component
function addGradingComponent(component = null) {
  gradingComponentCount++;
  const container = document.getElementById('grading-components-container');
  const div = document.createElement('div');
  div.className = 'grading-component';
  div.dataset.id = gradingComponentCount;
  div.innerHTML = `
    <div class="grading-component-header">
      <span class="grading-component-number">Component #${gradingComponentCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('grading', ${gradingComponentCount})">&times;</button>
    </div>
    <div class="grading-component-fields">
      <input type="text" placeholder="Component Name" class="grading-name" value="${component?.name || ''}">
      <input type="number" placeholder="%" class="grading-percentage" value="${component?.percentage || 0}" min="0" max="100" onchange="updateGradingTotal()">
      <input type="text" placeholder="Breakdown (optional)" class="grading-breakdown" value="${component?.breakdown || ''}">
    </div>
  `;
  container.appendChild(div);
  updateGradingTotal();
}

// Update Grading Total
function updateGradingTotal() {
  let total = 0;
  document.querySelectorAll('.grading-percentage').forEach(input => {
    total += parseFloat(input.value) || 0;
  });
  document.getElementById('grading-total').textContent = total + '%';
  document.getElementById('grading-total').style.color = total === 100 ? '#0C4B34' : '#dc3545';
}

// Add Reference
function addReference(ref = null) {
  referenceCount++;
  const container = document.getElementById('references-container');
  const div = document.createElement('div');
  div.className = 'resource-item';
  div.dataset.id = referenceCount;
  div.innerHTML = `
    <div class="resource-item-header">
      <span class="resource-item-number">Reference #${referenceCount}</span>
      <button type="button" class="remove-resource-btn" onclick="removeField('reference', ${referenceCount})">&times;</button>
    </div>
    <div class="resource-item-fields">
      <textarea placeholder="Reference in APA 7th Edition format" class="ref-text" rows="2">${ref || ''}</textarea>
    </div>
  `;
  container.appendChild(div);
}

// Remove Field
function removeField(type, id) {
  let container;
  switch (type) {
    case 'books':
      container = document.getElementById('books-container');
      break;
    case 'ebooks':
      container = document.getElementById('ebooks-container');
      break;
    case 'web_resources':
      container = document.getElementById('web-resources-container');
      break;
    case 'learning_week':
      container = document.getElementById('learning-plan-container');
      break;
    case 'grading':
      container = document.getElementById('grading-components-container');
      break;
    case 'reference':
      container = document.getElementById('references-container');
      break;
  }
  
  if (container) {
    const item = container.querySelector(`[data-id="${id}"], [data-week="${id}"]`);
    if (item) {
      item.remove();
      if (type === 'grading') updateGradingTotal();
    }
  }
}

// Collect form data
function collectFormData() {
  // Books
  const books = [];
  document.querySelectorAll('#books-container .resource-item').forEach(item => {
    books.push({
      title: item.querySelector('.book-title').value,
      author: item.querySelector('.book-author').value,
      isbn: item.querySelector('.book-isbn').value,
      publisher: item.querySelector('.book-publisher').value,
      year: item.querySelector('.book-year').value,
      edition: item.querySelector('.book-edition').value
    });
  });
  
  // E-books
  const ebooks = [];
  document.querySelectorAll('#ebooks-container .resource-item').forEach(item => {
    ebooks.push({
      title: item.querySelector('.ebook-title').value,
      author: item.querySelector('.ebook-author').value,
      url: item.querySelector('.ebook-url').value,
      publisher: item.querySelector('.ebook-publisher').value,
      year: item.querySelector('.ebook-year').value,
      access_date: item.querySelector('.ebook-access-date').value
    });
  });
  
  // Web Resources
  const webResources = [];
  document.querySelectorAll('#web-resources-container .resource-item').forEach(item => {
    webResources.push({
      title: item.querySelector('.web-title').value,
      url: item.querySelector('.web-url').value,
      access_date: item.querySelector('.web-access-date').value,
      description: item.querySelector('.web-description').value
    });
  });
  
  // Learning Plan
  const learningPlan = [];
  document.querySelectorAll('#learning-plan-container .learning-week').forEach(week => {
    learningPlan.push({
      week: week.dataset.week,
      topic: week.querySelector('.week-topic').value,
      activities: week.querySelector('.week-activities').value,
      assessment: week.querySelector('.week-assessment').value
    });
  });
  
  // Exam Schedules
  const examSchedules = {
    prelim: {
      duration: document.getElementById('prelim_duration').value,
      weeks: document.getElementById('prelim_weeks').value,
      topics: document.getElementById('prelim_topics').value
    },
    midterm: {
      duration: document.getElementById('midterm_duration').value,
      weeks: document.getElementById('midterm_weeks').value,
      topics: document.getElementById('midterm_topics').value
    },
    prefinal: {
      duration: document.getElementById('prefinal_duration').value,
      weeks: document.getElementById('prefinal_weeks').value,
      topics: document.getElementById('prefinal_topics').value
    },
    final: {
      duration: document.getElementById('final_duration').value,
      weeks: document.getElementById('final_weeks').value,
      topics: document.getElementById('final_topics').value
    }
  };
  
  // Grading Components
  const gradingComponents = [];
  document.querySelectorAll('#grading-components-container .grading-component').forEach(comp => {
    gradingComponents.push({
      name: comp.querySelector('.grading-name').value,
      percentage: comp.querySelector('.grading-percentage').value,
      breakdown: comp.querySelector('.grading-breakdown').value
    });
  });
  
  // References
  const references = [];
  document.querySelectorAll('#references-container .resource-item').forEach(item => {
    const refText = item.querySelector('.ref-text').value;
    if (refText) references.push(refText);
  });
  
  return {
    course_id: document.getElementById('syllabus_course_id').value,
    teacher_id: document.getElementById('syllabus_teacher_id').value,
    academic_year: document.getElementById('syllabus_academic_year').value,
    term: document.getElementById('syllabus_term').value,
    course_description: document.getElementById('course_description').value,
    course_objectives: document.getElementById('course_objectives').value,
    expected_course_outcomes: document.getElementById('expected_course_outcomes').value,
    books: JSON.stringify(books),
    ebooks: JSON.stringify(ebooks),
    web_resources: JSON.stringify(webResources),
    learning_plan: JSON.stringify(learningPlan),
    exam_schedules: JSON.stringify(examSchedules),
    grading_system: JSON.stringify({ components: gradingComponents }),
    course_requirements: document.getElementById('course_requirements').value,
    course_expectations: document.getElementById('course_expectations').value,
    remote_policies: document.getElementById('remote_policies').value,
    references: JSON.stringify(references)
  };
}

// Save as Draft
function saveSyllabusDraft() {
  const data = collectFormData();
  data.status = 'draft';
  
  fetch('api/save_syllabus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      alert('Syllabus saved as draft!');
      closeSyllabusModal();
    } else {
      alert('Error saving syllabus: ' + result.message);
    }
  })
  .catch(err => {
    alert('Error saving syllabus');
    console.error(err);
  });
}

// Submit for Approval
function submitSyllabus() {
  // Validate required fields
  const required = ['course_description', 'expected_course_outcomes', 'course_requirements', 'remote_policies'];
  for (let field of required) {
    if (!document.getElementById(field).value.trim()) {
      alert('Please fill in all required fields.');
      switchTab('overview');
      return;
    }
  }
  
  if (!confirm('Are you sure you want to submit this syllabus for approval? You cannot edit it after submission.')) {
    return;
  }
  
  const data = collectFormData();
  data.status = 'submitted';
  
  fetch('api/submit_syllabus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      alert('Syllabus submitted for approval!');
      closeSyllabusModal();
      // Reload to see updated status
      location.reload();
    } else {
      alert('Error submitting syllabus: ' + result.message);
    }
  })
  .catch(err => {
    alert('Error submitting syllabus');
    console.error(err);
  });
}

// Close modal when clicking outside
document.getElementById('syllabusModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeSyllabusModal();
  }
});
</script>