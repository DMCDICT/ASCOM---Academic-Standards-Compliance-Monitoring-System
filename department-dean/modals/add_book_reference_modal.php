<?php
// add_book_reference_modal.php
// Enhanced modal for adding book references with multiple input methods

// Fetch courses for the department dean
require_once dirname(__FILE__) . '/../includes/db_connection.php';
$deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;

$coursesData = [];
if ($deanDepartmentCode) {
    try {
        $coursesQuery = "SELECT DISTINCT c.id, c.course_code, c.course_title 
                        FROM courses c 
                        LEFT JOIN programs p ON c.program_id = p.id 
                        LEFT JOIN departments d ON p.department_id = d.id 
                        WHERE (d.department_code = ? OR c.program_id IS NULL)
                        ORDER BY c.course_code ASC";
        $coursesStmt = $pdo->prepare($coursesQuery);
        $coursesStmt->execute([$deanDepartmentCode]);
        $coursesData = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching courses for book reference modal: " . $e->getMessage());
    }
}
?>

<div id="addBookReferenceModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 600px; width: 90%; display: flex; flex-direction: column; max-height: 80vh; overflow: hidden; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0;">
      <h2>Add Book Reference</h2>
      <span class="close-button" id="closeBookRefModal" onclick="window.closeAddBookReferenceModal()">&times;</span>
    </div>
    
    <!-- Scrollable form content -->
    <div class="form-content" style="flex: 1; overflow-y: auto; padding: 16px 24px;">
      <form id="addBookReferenceForm" class="form-grid" method="post" autocomplete="off" novalidate>
      <input type="hidden" name="input_method" id="inputMethod" value="manual">
      
      <!-- Course Selection with Suggestions and By Batch Checkbox -->
      <div class="form-row" style="margin-bottom: 12px;">
        <div class="form-group" style="flex: 1; min-width: 200px; position: relative;">
          <label>Course <span style="color: red;">*</span></label>
          <div class="autocomplete-container" style="position: relative;">
            <input type="text" id="bookRefCourseSearch" name="course_search" placeholder="Type to search courses..." style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 16px; font-family: 'TT Interphases', sans-serif;">
            <input type="hidden" id="bookRefCourseId" name="course_id">
            <div class="suggestions-panel" id="bookRefCourseSuggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; margin-top: 4px;">
              <!-- Course suggestions will appear here when you type -->
            </div>
          </div>
        </div>
        <div class="form-group" style="flex: 0 0 auto; align-self: flex-end; padding-bottom: 0;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="byBatchCheckbox" style="width: 18px; height: 18px; cursor: pointer;">
            <span>By Batch</span>
          </label>
        </div>
      </div>
      
      <!-- Manual Input Section -->
      <div id="manualInputSection" class="input-section" style="display: block;">
        <div class="form-row">
          <div class="form-group" style="flex:1; min-width: 160px; position: relative;">
            <label for="call_number">Call Number</label>
            <input type="text" name="call_number" id="call_number" placeholder="e.g., CS 001.1 I58 2023">
            <div id="callNumberSuggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
          </div>
          <div class="form-group" style="flex:1; min-width: 160px;">
            <label for="isbn">ISBN</label>
            <input type="text" name="isbn" id="isbn" placeholder="978-0-123456-78-9">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group" style="flex:1; min-width: 200px;">
            <label for="book_title" class="required-field">Book Title</label>
            <input type="text" name="book_title" id="book_title" required>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group" style="flex:1; min-width: 160px;">
            <label for="copyright_year" class="required-field">Copyright Year</label>
            <div style="display: flex; align-items: center; gap: 8px;">
              <input type="number" name="copyright_year" id="copyright_year" required readonly style="flex: 1; cursor: default; user-select: none; -webkit-appearance: none; -moz-appearance: textfield;">
              <div style="display: flex; flex-direction: column; gap: 0;">
                <button type="button" id="copyright_year_up" style="width: 24px; height: 20px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 10px; border-radius: 3px 3px 0 0; line-height: 1; padding: 0;" title="Increase year">▲</button>
                <button type="button" id="copyright_year_down" style="width: 24px; height: 20px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 10px; border-radius: 0 0 3px 3px; border-top: none; line-height: 1; padding: 0;" title="Decrease year">▼</button>
              </div>
            </div>
            <div id="copyright_year_hint" style="font-size: 11px; color: #6c757d; margin-top: 4px; min-height: 14px;"></div>
          </div>
          <div class="form-group" style="flex:1; min-width: 160px;">
            <label for="edition">Edition</label>
            <input type="text" name="edition" id="edition" placeholder="1st, 2nd, etc.">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group" style="flex:1; min-width: 160px;">
            <label for="authors" class="required-field">Author(s)</label>
            <input type="text" name="authors" id="authors" placeholder="Last Name, First Name">
          </div>
          <div class="form-group" style="flex:1; min-width: 160px;">
            <label for="publisher" class="required-field">Publisher(s)</label>
            <input type="text" name="publisher" id="publisher">
          </div>
        </div>
      </div>
      
      <!-- Batch Mode Actions -->
      <div id="batchActions" style="display: none; margin-top: 16px;">
        <button type="button" id="addToListBtn" class="create-btn" style="width: 100%; margin-bottom: 16px;">Add to List</button>
        
        <!-- Batch List -->
        <div id="batchList" style="margin-bottom: 16px;">
          <h4 style="margin-bottom: 8px; font-size: 14px; font-weight: 600;">Added Books</h4>
          <div id="batchListContainer" style="border: 1px solid #ddd; border-radius: 8px; padding: 8px;"></div>
        </div>
        
        <button type="button" id="clearListBtn" class="cancel-btn" style="width: 100%;">Clear List</button>
      </div>
      </form>
    </div>
    
    <!-- Fixed footer buttons - not in scrollable area -->
    <div class="modal-footer" style="flex-shrink: 0; background: #EFEFEF; padding: 12px 24px; border-top: 1px solid #e0e0e0; display: flex; justify-content: flex-end; gap: 12px; margin-top: 0;">
      <button type="button" class="cancel-btn" onclick="window.closeAddBookReferenceModal()">Cancel</button>
      <button type="submit" form="addBookReferenceForm" class="create-btn" id="submitBtn" disabled>Add Book</button>
    </div>
  </div>
</div>

<!-- Confirmation Modal for Empty Fields -->
<div id="confirmationModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 450px; width: 90%; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0;">
      <h2>Confirm Action</h2>
      <span class="close-button" id="closeConfirmationModal" onclick="window.closeConfirmationModal()">&times;</span>
    </div>
    <div style="padding: 24px;">
      <p id="confirmationMessage" style="margin: 0 0 24px 0; font-size: 14px; color: #333; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; justify-content: flex-end; gap: 12px; padding: 0 24px 24px; border-top: none;">
      <button type="button" class="cancel-btn" id="confirmationCancelBtn">Cancel</button>
      <button type="button" class="create-btn" id="confirmationConfirmBtn">Proceed</button>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 400px; width: 90%; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0; border-bottom: 1px solid #e0e0e0;">
      <h2 style="color: #28a745;">Success</h2>
      <span class="close-button" onclick="window.closeSuccessModal()">&times;</span>
    </div>
    <div style="padding: 24px; text-align: center;">
      <div style="font-size: 48px; margin-bottom: 16px;">✓</div>
      <p id="successMessage" style="margin: 0; font-size: 14px; color: #333; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; justify-content: center; padding: 0 24px 24px; border-top: none;">
      <button type="button" class="create-btn" onclick="window.closeSuccessModal()">OK</button>
    </div>
  </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal-overlay" style="display: none;">
  <div class="modal-box" style="max-width: 450px; width: 90%; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0; border-bottom: 1px solid #e0e0e0;">
      <h2 style="color: #dc3545;">Error</h2>
      <span class="close-button" onclick="window.closeErrorModal()">&times;</span>
    </div>
    <div style="padding: 24px; text-align: center;">
      <div style="font-size: 48px; color: #dc3545; margin-bottom: 16px;">✗</div>
      <p id="errorMessage" style="margin: 0; font-size: 14px; color: #333; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; justify-content: center; padding: 0 24px 24px; border-top: none;">
      <button type="button" class="cancel-btn" onclick="window.closeErrorModal()">OK</button>
    </div>
  </div>
</div>

<style>
#addBookReferenceModal .input-method-tabs {
  display: flex;
  border-bottom: 2px solid #e9ecef;
  margin-bottom: 20px;
  gap: 0;
}

#addBookReferenceModal .input-method-tabs .tab-button {
  flex: 1;
  padding: 12px 16px;
  border: none;
  background: #f8f9fa;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-family: 'TT Interphases', sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

#addBookReferenceModal .input-method-tabs .tab-button:hover {
  background: #e9ecef;
  color: #495057;
}

#addBookReferenceModal .input-method-tabs .tab-button.active {
  background: white;
  color: #1976d2;
  border-bottom-color: #1976d2;
}

.tab-icon {
  font-size: 16px;
}

.input-section {
  display: none;
  min-height: 300px;
}

.input-section.active {
  display: block;
}

#addBookReferenceModal .search-section {
  margin-bottom: 20px;
}

#addBookReferenceModal .search-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

#addBookReferenceModal .search-bar input {
  flex: 1;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

#addBookReferenceModal .search-btn {
  padding: 12px 20px;
  background: #1976d2;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.search-filters select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}


.files-upload-area {
  border: 2px dashed #ddd;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.files-upload-area:hover {
  border-color: #6f42c1;
  background: #f8f5ff;
}

.files-upload-area.dragover {
  border-color: #6f42c1;
  background: #f0e6ff;
  transform: scale(1.02);
}

.upload-hint {
  color: #6c757d;
  font-size: 12px;
  margin-top: 5px;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  margin-top: 15px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff, #0056b3);
  border-radius: 3px;
  transition: width 0.3s ease;
  width: 0%;
}

.extraction-header {
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e9ecef;
}

.extraction-header h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.extraction-header p {
  margin: 0;
  color: #6c757d;
  font-size: 14px;
}

.extracted-books-list {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 10px;
  background: #f8f9fa;
}

.extracted-book-item {
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  margin-bottom: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.extracted-book-item:hover {
  border-color: #007bff;
  box-shadow: 0 2px 4px rgba(0,123,255,0.1);
}

.extracted-book-item.selected {
  border-color: #007bff;
  background: #e7f3ff;
}

.extraction-actions {
  margin-top: 15px;
  text-align: center;
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}

.select-all-btn, .add-selected-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  margin: 0 5px;
  transition: background-color 0.2s ease;
  min-width: 120px;
  white-space: nowrap;
  text-align: center;
}

.select-all-btn {
  background: #6c757d;
  color: white;
}

.select-all-btn:hover {
  background: #5a6268;
}

.add-selected-btn {
  background: #28a745;
  color: white;
}

.add-selected-btn:hover {
  background: #218838;
}

.upload-icon {
  font-size: 48px;
  margin-bottom: 15px;
}

.browse-btn {
  padding: 12px 24px;
  background: #1976d2;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  margin-top: 15px;
}

.processing-status {
  text-align: center;
  padding: 40px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #1976d2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 15px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.auto-generate-info {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.generation-options {
  margin-bottom: 20px;
}

.generation-options label {
  display: block;
  margin-bottom: 10px;
  cursor: pointer;
}

.generate-btn {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #1976d2, #42a5f5);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 20px;
}

.generated-results, .pdf-results {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 15px;
  background: white;
}

.no-results {
  text-align: center;
  color: #6c757d;
  padding: 40px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.cancel-btn {
  padding: 10px 20px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.create-btn {
  padding: 10px 20px;
  background: #28a745;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.create-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

/* Copyright Year input spinner buttons - HIDE native spinners */
#copyright_year::-webkit-inner-spin-button,
#copyright_year::-webkit-outer-spin-button {
  -webkit-appearance: none;
  opacity: 0;
  cursor: default;
}

#copyright_year {
  -moz-appearance: textfield;
}

#copyright_year::-webkit-outer-spin-button,
#copyright_year::-webkit-inner-spin-button {
  -webkit-appearance: none;
  opacity: 0;
  cursor: default;
}

/* Override global form spacing for book reference modal */
#addBookReferenceModal .form-row {
  margin-bottom: 5px !important;
}

#addBookReferenceModal .form-group {
  margin-bottom: 5px !important;
}

/* Required field asterisk styling */
#addBookReferenceModal label.required-field::after {
  content: " *";
  color: #FF4C4C;
  margin-left: 2px;
}

/* Book References List Styles */
.book-references-list-section {
  margin-top: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.list-header h4 {
  margin: 0;
  color: #343a40;
  font-size: 16px;
}

.book-count {
  background: #e3f2fd;
  color: #1976d2;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

.requirements-info {
  margin-bottom: 15px;
  padding: 10px;
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 6px;
}

.requirements-info p {
  margin: 0;
  font-size: 13px;
  color: #856404;
}

.added-books-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  background: white;
}

.no-books-message {
  text-align: center;
  padding: 20px;
  color: #6c757d;
  font-style: italic;
}

.book-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #f1f3f4;
  transition: background-color 0.2s ease;
}

.book-item:hover {
  background: #f8f9fa;
}

.book-item:last-child {
  border-bottom: none;
}

.book-info {
  flex: 1;
}

.book-title {
  font-weight: 600;
  color: #343a40;
  margin-bottom: 4px;
}

.book-details {
  font-size: 13px;
  color: #6c757d;
}

.book-actions {
  display: flex;
  gap: 8px;
}

.remove-book-btn {
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.remove-book-btn:hover {
  background: #c82333;
}

.submit-section {
  margin-top: 20px;
  padding: 15px;
  background: #e8f5e8;
  border: 1px solid #c3e6c3;
  border-radius: 8px;
  text-align: center;
}

.submit-info {
  margin-bottom: 10px;
  color: #155724;
  font-size: 14px;
}

.submit-btn {
  background: #28a745;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 12px 30px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.submit-btn:hover {
  background: #218838;
}

.submit-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.add-to-list-btn {
  background: #17a2b8;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 10px 20px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s ease;
}

.add-to-list-btn:hover {
  background: #138496;
}

.add-to-list-section {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #e9ecef;
  text-align: center;
}

.add-to-list-section .add-to-list-btn {
  width: 100%;
  max-width: 200px;
}

/* Sticky modal actions */
.modal-actions {
  position: sticky;
  bottom: 0;
  background: white;
  border-top: 1px solid #e9ecef;
  padding: 15px 0;
  margin-top: 20px;
  z-index: 10;
}

/* Modal header */
.modal-header {
  margin-bottom: 10px;
}

/* Clean modal layout structure */
.modal-box {
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
}

/* Tabs - Fixed at top */
.input-method-tabs {
  flex-shrink: 0;
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
  margin-bottom: 0px;
}

/* Override global modal form styles for this modal */
#addBookReferenceModal form {
  overflow-y: visible !important;
  max-height: none !important;
}

/* Book References List - Part of scrollable content */
.book-references-list-section {
  margin: 30px 0 20px 0;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.added-books-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  background: white;
}

/* Add to List Button */
.add-to-list-section {
  margin-top: 0px;
  margin-bottom: 20px;
  text-align: center;
}

.add-to-list-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.add-to-list-btn:hover {
  background: #0056b3;
}

/* Action Buttons - Fixed at bottom */
.modal-actions {
  flex-shrink: 0;
  position: sticky;
  bottom: 0;
  border-top: 1px solid #e9ecef;
  padding: 20px 0;
  margin-top: 0px;
  z-index: 10;
  background: transparent;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.modal-actions .cancel-btn {
  white-space: nowrap;
  text-align: center;
}

.modal-actions .create-btn {
  white-space: nowrap !important;
  text-align: center;
  padding: 10px 20px !important;
  min-width: auto !important;
  width: auto !important;
  max-width: none !important;
  flex-shrink: 0;
  display: inline-block !important;
  box-sizing: content-box !important;
}

/* Override any global button styles */
#submitBtn {
  white-space: nowrap !important;
  width: auto !important;
  min-width: auto !important;
  max-width: none !important;
  padding: 10px 20px !important;
  height: auto !important;
  line-height: normal !important;
}

#submitBtn:disabled {
  cursor: not-allowed !important;
}

/* Call number suggestions styles */
#callNumberSuggestions div:hover {
  background: #f0f0f0 !important;
}

/* Override global create-btn styles specifically for modal actions */
.modal-actions .create-btn {
  padding: 10px 20px !important;
  height: auto !important;
  line-height: normal !important;
}
</style>

<script>
// Global variables
let generatedBooks = [];
let addedBooks = []; // Track added book references
const MIN_BOOKS = 5;
const MAX_BOOKS = 10;
const CURRENT_YEAR = new Date().getFullYear();

// Initialize button text when modal opens
function initializeButtonText() {
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    submitBtn.textContent = 'Add Book';
  }
}

// Switch between input methods
window.switchInputMethod = function(method) {
  console.log('Switching to method:', method);
  
  // Update tabs
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
  const activeTab = document.getElementById(method + 'Tab');
  if (activeTab) {
    activeTab.classList.add('active');
  }
  
  // Update sections
  document.querySelectorAll('.input-section').forEach(section => section.classList.remove('active'));
  if (method === 'files') {
    const filesSection = document.getElementById('filesImportSection');
    if (filesSection) filesSection.classList.add('active');
  } else if (method === 'auto') {
    const autoSection = document.getElementById('autoGenerateSection');
    if (autoSection) autoSection.classList.add('active');
  } else if (method === 'manual') {
    const manualSection = document.getElementById('manualInputSection');
    if (manualSection) manualSection.classList.add('active');
  }
  
  // Update hidden input
  const inputMethodField = document.getElementById('inputMethod');
  if (inputMethodField) {
    inputMethodField.value = method;
  }
  
  // Update submit button text based on tab
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    switch(method) {
      case 'manual':
        submitBtn.textContent = 'Add Book';
        break;
      case 'files':
        submitBtn.textContent = 'Add Extracted References';
        break;
      case 'auto':
        submitBtn.textContent = 'Add Generated References';
        break;
    }
  }
}




// PDF processing (mock implementation)
window.processFiles = function() {
  const files = document.getElementById('importFiles').files;
  if (files.length === 0) {
    window.showErrorModal('Please select files to upload');
    return;
  }
  
  // Show processing status
  document.getElementById('filesProcessingStatus').style.display = 'block';
  document.getElementById('extractedReferences').style.display = 'none';
  
  // Simulate progress
  let progress = 0;
  const progressInterval = setInterval(() => {
    progress += Math.random() * 15;
    if (progress > 90) progress = 90;
    document.getElementById('progressFill').style.width = progress + '%';
  }, 200);
  
  // Simulate PDF analysis (replace with actual PDF processing)
  setTimeout(() => {
    clearInterval(progressInterval);
    document.getElementById('progressFill').style.width = '100%';
    
    setTimeout(() => {
      document.getElementById('filesProcessingStatus').style.display = 'none';
      document.getElementById('extractedReferences').style.display = 'block';
      
      // PDF processing: return empty until real implementation is added
      const extractedBooks = [];
      displayExtractedBooks(extractedBooks);
    }, 500);
  }, 3000);
}

function displayExtractedBooks(books) {
  const booksList = document.getElementById('extractedBooksList');
  
  if (books.length === 0) {
    booksList.innerHTML = '<div class="no-results"><p>No book references found in the uploaded PDF files.</p></div>';
    return;
  }
  
  const booksHTML = books.map(book => `
    <div class="extracted-book-item" onclick="toggleExtractedBook('${book.id}')" data-book-id="${book.id}">
      <div class="book-title">${book.title}</div>
      <div class="book-authors">${book.authors}</div>
      <div class="book-details">
        <span class="isbn">ISBN: ${book.isbn}</span>
        <span class="publisher">Publisher: ${book.publisher}</span>
        <span class="year">Year: ${book.copyright_year}</span>
        ${book.edition ? `<span class="edition">Edition: ${book.edition}</span>` : ''}
      </div>
      <div class="book-source">Source: ${book.source}</div>
    </div>
  `).join('');
  
  booksList.innerHTML = booksHTML;
}

// Track selected extracted books
let selectedExtractedBooks = [];

window.toggleExtractedBook = function(bookId) {
  const bookItem = document.querySelector(`[data-book-id="${bookId}"]`);
  bookItem.classList.toggle('selected');
  
  const index = selectedExtractedBooks.findIndex(book => book.id === bookId);
  if (index > -1) {
    selectedExtractedBooks.splice(index, 1);
  } else {
    // Find the book data and add to selected
    const bookData = document.querySelector(`[data-book-id="${bookId}"]`);
    const title = bookData.querySelector('.book-title').textContent;
    const authors = bookData.querySelector('.book-authors').textContent;
    const isbn = bookData.querySelector('.isbn').textContent.replace('ISBN: ', '');
    const publisher = bookData.querySelector('.publisher').textContent.replace('Publisher: ', '');
    const year = bookData.querySelector('.year').textContent.replace('Year: ', '');
    const edition = bookData.querySelector('.edition') ? bookData.querySelector('.edition').textContent.replace('Edition: ', '') : '';
    
    selectedExtractedBooks.push({
      id: bookId,
      title: title,
      authors: authors,
      isbn: isbn,
      publisher: publisher,
      copyright_year: year,
      edition: edition
    });
  }
  
  updateExtractionActions();
};

function updateExtractionActions() {
  const addBtn = document.getElementById('addSelectedExtractedBtn');
  if (selectedExtractedBooks.length > 0) {
    addBtn.textContent = `Add ${selectedExtractedBooks.length} Selected Books`;
    addBtn.disabled = false;
  } else {
    addBtn.textContent = 'Add Selected to List';
    addBtn.disabled = true;
  }
}

// Select all extracted books
window.selectAllExtracted = function() {
  const bookItems = document.querySelectorAll('.extracted-book-item');
  selectedExtractedBooks = [];
  
  bookItems.forEach(item => {
    item.classList.add('selected');
    const bookId = item.getAttribute('data-book-id');
    const title = item.querySelector('.book-title').textContent;
    const authors = item.querySelector('.book-authors').textContent;
    const isbn = item.querySelector('.isbn').textContent.replace('ISBN: ', '');
    const publisher = item.querySelector('.publisher').textContent.replace('Publisher: ', '');
    const year = item.querySelector('.year').textContent.replace('Year: ', '');
    const edition = item.querySelector('.edition') ? item.querySelector('.edition').textContent.replace('Edition: ', '') : '';
    
    selectedExtractedBooks.push({
      id: bookId,
      title: title,
      authors: authors,
      isbn: isbn,
      publisher: publisher,
      copyright_year: year,
      edition: edition
    });
  });
  
  updateExtractionActions();
};

// Add selected extracted books to the main list
window.addSelectedExtractedBooks = function() {
  if (selectedExtractedBooks.length === 0) {
    window.showErrorModal('Please select books to add');
    return;
  }
  
  const count = selectedExtractedBooks.length;
  selectedExtractedBooks.forEach(book => {
    addBookToList(book);
  });
  
  // Clear selections
  selectedExtractedBooks = [];
  document.querySelectorAll('.extracted-book-item.selected').forEach(item => {
    item.classList.remove('selected');
  });
  
  updateExtractionActions();
  window.showSuccessModal(`Successfully added ${count} books to your list!`);
};

// Auto-generation functionality
window.generateBookReferences = function() {
  const includeTextbooks = document.getElementById('includeTextbooks').checked;
  const includeReferences = document.getElementById('includeReferences').checked;
  const includeJournals = document.getElementById('includeJournals').checked;
  
  // Show loading
  const resultsDiv = document.getElementById('generatedResults');
  resultsDiv.style.display = 'block';
  resultsDiv.innerHTML = '<div class="processing-status"><div class="spinner"></div><p>Generating book references...</p></div>';
  
  // Simulate AI generation
  setTimeout(() => {
    const generatedBooks = [
      {
        title: 'Computer Science: An Overview',
        authors: 'Brookshear, Glenn',
        isbn: '978-0133760064',
        publisher: 'Pearson',
        copyright_year: 2023,
        reason: 'Core textbook for computer science fundamentals'
      },
      {
        title: 'Introduction to Algorithms',
        authors: 'Cormen, Thomas H.',
        isbn: '978-0262033848',
        publisher: 'MIT Press',
        copyright_year: 2022,
        reason: 'Essential reference for algorithm design'
      }
    ];
    
    displayGeneratedResults(generatedBooks);
  }, 2500);
}

function displayGeneratedResults(books) {
  const container = document.getElementById('generatedBooksList');
  let html = '';
  
  books.forEach((book, index) => {
    html += `
      <div class="generated-book-item" style="border: 1px solid #e9ecef; padding: 15px; margin-bottom: 10px; border-radius: 6px; background: #f8f9fa;">
        <div style="font-weight: 600; margin-bottom: 5px;">${book.title}</div>
        <div style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">${book.authors} | ${book.publisher} (${book.copyright_year})</div>
        <div style="color: #28a745; font-size: 12px; font-style: italic; margin-bottom: 10px;">💡 ${book.reason}</div>
        <div>
          <label><input type="checkbox" checked> Include this book</label>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = html;
}

// File upload handling - moved to event listener attachment

// Add manual book to list
window.addManualBook = function() {
  const title = document.getElementById('book_title').value.trim();
  const authors = document.getElementById('authors').value.trim();
  const isbn = document.getElementById('isbn').value.trim();
  const publisher = document.getElementById('publisher').value.trim();
  const copyrightYear = document.getElementById('copyright_year').value;
  const edition = document.getElementById('edition').value.trim();
  
  // Validate required fields
  if (!title || !authors) {
    window.showErrorModal('Book title and authors are required.');
    return;
  }
  
  const bookData = {
    title: title,
    authors: authors,
    isbn: isbn,
    publisher: publisher,
    copyright_year: copyrightYear ? parseInt(copyrightYear) : null,
    edition: edition
  };
  
  if (addBookToList(bookData)) {
    // Success - form will be cleared by addBookToList
    console.log('Book added to list:', bookData);
  }
}

// Add book to list function
function addBookToList(bookData) {
  // Validate copyright year (within 5 years)
  if (bookData.copyright_year && bookData.copyright_year < (CURRENT_YEAR - 5)) {
    window.showErrorModal(`Book "${bookData.title}" copyright year (${bookData.copyright_year}) is older than 5 years. Please select a more recent book.`);
    return false;
  }
  
  // Check if we've reached maximum
  if (addedBooks.length >= MAX_BOOKS) {
    window.showErrorModal(`Maximum ${MAX_BOOKS} books allowed. Please remove a book before adding another.`);
    return false;
  }
  
  // Add book to list
  const bookId = Date.now(); // Simple ID generation
  bookData.id = bookId;
  addedBooks.push(bookData);
  
  // Update UI
  updateAddedBooksList();
  updateSubmitButton();
  
  // Clear form
  document.getElementById('addBookReferenceForm').reset();
  
  return true;
}

// Remove book from list
window.removeBookFromList = function(bookId) {
  addedBooks = addedBooks.filter(book => book.id !== bookId);
  updateAddedBooksList();
  updateSubmitButton();
}

// Update book list display
function updateAddedBooksList() {
  const container = document.getElementById('addedBooksList');
  
  if (addedBooks.length === 0) {
    container.innerHTML = '<div class="no-books-message"><p>No book references added yet. Add at least 5 books to proceed.</p></div>';
    return;
  }
  
  let html = '';
  addedBooks.forEach(book => {
    html += `
      <div class="book-item">
        <div class="book-info">
          <div class="book-title">${book.title}</div>
          <div class="book-details">
            ${book.authors ? `by ${book.authors}` : ''}
            ${book.publisher ? ` • ${book.publisher}` : ''}
            ${book.copyright_year ? ` • ${book.copyright_year}` : ''}
            ${book.edition ? ` • ${book.edition}` : ''}
          </div>
        </div>
        <div class="book-actions">
          <button class="remove-book-btn" onclick="removeBookFromList(${book.id})">Remove</button>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = html;
  
  // Update book count
  const countElement = document.getElementById('bookCount');
  if (countElement) {
    countElement.textContent = addedBooks.length;
    
    // Update color based on count
    if (addedBooks.length < MIN_BOOKS) {
      countElement.style.color = '#dc3545'; // Red
    } else if (addedBooks.length >= MIN_BOOKS && addedBooks.length < MAX_BOOKS) {
      countElement.style.color = '#28a745'; // Green
    } else {
      countElement.style.color = '#ffc107'; // Yellow (at max)
    }
  }
}


// Update submit button state
function updateSubmitButton() {
  const submitBtn = document.getElementById('submitBtn');
  const canSubmit = addedBooks.length >= MIN_BOOKS && addedBooks.length <= MAX_BOOKS;
  
  submitBtn.disabled = !canSubmit;
  
  if (addedBooks.length < MIN_BOOKS) {
    submitBtn.textContent = `Add ${MIN_BOOKS - addedBooks.length} more books`;
  } else if (addedBooks.length >= MAX_BOOKS) {
    submitBtn.textContent = 'Maximum books reached';
  } else {
    // Get current input method and set appropriate text
    const currentMethod = document.getElementById('inputMethod').value;
    switch(currentMethod) {
      case 'manual':
        submitBtn.textContent = 'Add Book';
        break;
      case 'files':
        submitBtn.textContent = 'Add Extracted References';
        break;
      case 'auto':
        submitBtn.textContent = 'Add Generated References';
        break;
      default:
        submitBtn.textContent = 'Add Book';
    }
  }
}

// Form submission - simple direct submit like library
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('addBookReferenceForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const byBatchCheckbox = document.getElementById('byBatchCheckbox');
      
      // Check if we're in batch mode
      if (byBatchCheckbox && byBatchCheckbox.checked) {
        handleBatchSubmit();
      } else {
        handleSingleSubmit();
      }
    });
    
    // Single book submission
    function handleSingleSubmit() {
      // Get form data
      const formData = new FormData(form);
      
      // Validate required fields
      const bookTitle = formData.get('book_title');
      const copyrightYear = formData.get('copyright_year');
      const authors = formData.get('authors');
      const publisher = formData.get('publisher');
      
      if (!bookTitle || !copyrightYear) {
        window.showErrorModal('Please fill in all required fields (marked with *).');
        return;
      }
      
      // Check if authors or publisher are empty and show confirmation
      const emptyFields = [];
      if (!authors || authors.trim() === '') {
        emptyFields.push('Author(s)');
      }
      if (!publisher || publisher.trim() === '') {
        emptyFields.push('Publisher(s)');
      }
      
      // Function to submit the form
      const submitForm = function() {
        fetch('process_add_book_reference.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Response data:', data);
          if (data && data.success) {
            window.closeAddBookReferenceModal();
            window.showSuccessModal(data.message || 'Book reference added successfully!');
            // Reload page to show new book reference after a short delay
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            const errorMsg = data && data.message ? data.message : 'Failed to add book reference.';
            console.error('Submission failed:', errorMsg);
            window.showErrorModal(errorMsg);
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          window.showErrorModal('An error occurred while adding the book reference. Please try again.');
        });
      };
      
      // If there are empty fields, show confirmation modal
      if (emptyFields.length > 0) {
        const fieldsList = emptyFields.join(' and ');
        const confirmMessage = `The ${fieldsList} field${emptyFields.length > 1 ? 's are' : ' is'} still empty. Are you sure you still want to proceed anyway?`;
        window.showConfirmationModal(confirmMessage, submitForm);
      } else {
        submitForm();
      }
    }
    
    // Batch books submission
    function handleBatchSubmit() {
      if (batchBooks.length === 0) {
        window.showErrorModal('Please add at least one book to the list.');
        return;
      }
      
      // Check for empty author/publisher fields
      const booksWithEmptyFields = [];
      batchBooks.forEach((book, index) => {
        const emptyFields = [];
        if (!book.authors || book.authors.trim() === '') {
          emptyFields.push('Author(s)');
        }
        if (!book.publisher || book.publisher.trim() === '') {
          emptyFields.push('Publisher(s)');
        }
        if (emptyFields.length > 0) {
          booksWithEmptyFields.push({ index: index + 1, fields: emptyFields });
        }
      });
      
      // Function to submit batch
      const submitBatch = function() {
        const courseId = document.getElementById('bookRefCourseId').value;
        const formData = new FormData();
        formData.append('course_id', courseId);
        formData.append('input_method', 'batch');
        formData.append('books', JSON.stringify(batchBooks));
        
        fetch('process_add_book_reference.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          console.log('Batch response:', data);
          if (data && data.success) {
            window.closeAddBookReferenceModal();
            window.showSuccessModal(data.message || `Successfully added ${batchBooks.length} books!`);
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            const errorMsg = data && data.message ? data.message : 'Failed to add books.';
            console.error('Batch submission failed:', errorMsg);
            window.showErrorModal(errorMsg);
          }
        })
        .catch(error => {
          console.error('Batch fetch error:', error);
          window.showErrorModal('An error occurred while adding the books. Please try again.');
        });
      };
      
      // Show confirmation if any books have empty fields
      if (booksWithEmptyFields.length > 0) {
        let confirmMessage = 'The following books have empty optional fields:\n\n';
        booksWithEmptyFields.forEach(item => {
          confirmMessage += `Book #${item.index}: ${item.fields.join(' and ')}\n`;
        });
        confirmMessage += '\nDo you want to proceed anyway?';
        window.showConfirmationModal(confirmMessage, submitBatch);
      } else {
        submitBatch();
      }
    }
  }
});

// Course data for autocomplete
const bookRefCoursesData = [
  <?php foreach ($coursesData as $course): ?>
  {
    id: <?php echo $course['id']; ?>,
    code: "<?php echo htmlspecialchars($course['course_code']); ?>",
    title: "<?php echo htmlspecialchars($course['course_title']); ?>",
    display: "<?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>"
  },
  <?php endforeach; ?>
];

console.log('Book reference courses data loaded:', bookRefCoursesData);

// Initialize course autocomplete
function initBookRefCourseAutocomplete() {
    const searchInput = document.getElementById('bookRefCourseSearch');
    const courseIdInput = document.getElementById('bookRefCourseId');
    const suggestionsPanel = document.getElementById('bookRefCourseSuggestions');
    
    if (!searchInput || !courseIdInput || !suggestionsPanel) {
        console.log('Course autocomplete elements not found');
        return;
    }
    
    // Show suggestions when typing
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        console.log('Typing course search:', query);
        
        // Clear previous suggestions
        suggestionsPanel.innerHTML = '';
        
        if (query.length === 0) {
            suggestionsPanel.style.display = 'none';
            courseIdInput.value = '';
            return;
        }
        
        // Filter courses
        const filtered = bookRefCoursesData.filter(course => 
            course.code.toLowerCase().includes(query) || 
            course.title.toLowerCase().includes(query) ||
            course.display.toLowerCase().includes(query)
        );
        
        console.log('Found', filtered.length, 'course matches');
        
        if (filtered.length === 0) {
            suggestionsPanel.style.display = 'none';
            return;
        }
        
        // Show suggestions (max 10)
        const limited = filtered.slice(0, 10);
        limited.forEach(course => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'suggestion-item';
            button.textContent = course.display;
            button.style.display = 'block';
            button.style.width = '100%';
            button.style.padding = '8px 12px';
            button.style.margin = '2px 0';
            button.style.border = '1px solid #ccc';
            button.style.background = 'white';
            button.style.cursor = 'pointer';
            button.style.textAlign = 'left';
            button.style.fontFamily = 'TT Interphases, sans-serif';
            button.style.fontSize = '14px';
            button.style.transition = 'all 0.2s ease';
            button.style.borderRadius = '4px';
            
            // Add hover effects
            button.addEventListener('mouseenter', function() {
                this.style.background = '#1976d2';
                this.style.color = 'white';
                this.style.borderColor = '#1976d2';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.background = 'white';
                this.style.color = '#333';
                this.style.borderColor = '#ccc';
            });
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                searchInput.value = course.display;
                courseIdInput.value = course.id;
                suggestionsPanel.style.display = 'none';
                console.log('Selected course:', course.display, 'ID:', course.id);
                
                // Trigger form validation
                if (typeof setupFormValidation === 'function') {
                    setupFormValidation();
                }
            });
            
            suggestionsPanel.appendChild(button);
        });
        
        suggestionsPanel.style.display = 'block';
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsPanel.contains(e.target)) {
            suggestionsPanel.style.display = 'none';
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBookRefCourseAutocomplete);
} else {
    initBookRefCourseAutocomplete();
}

// Modal functions - Make them globally accessible
window.openAddBookReferenceModal = function(courseId, courseCode) {
  console.log('Opening modal for course:', courseId, 'Course Code:', courseCode);
  
  // Set the course ID and search input if provided
  const courseIdField = document.getElementById('bookRefCourseId');
  const courseSearchInput = document.getElementById('bookRefCourseSearch');
  
  if (courseId && courseCode && courseIdField && courseSearchInput) {
    // Pre-fill course if provided
    courseIdField.value = courseId;
    courseSearchInput.value = courseCode + ' - ' + (bookRefCoursesData.find(c => c.id == courseId)?.title || '');
  } else {
    // Clear course selection if opening from sidebar
    if (courseIdField) courseIdField.value = '';
    if (courseSearchInput) courseSearchInput.value = '';
  }
  
  // Show the modal
  document.getElementById('addBookReferenceModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  
  // Initialize button text
  initializeButtonText();
  
  // Re-attach event listeners for the modal
  setTimeout(() => {
    attachEventListeners();
    setupCopyrightYearInput();
    setupFormValidation();
    initBookRefCourseAutocomplete(); // Re-initialize autocomplete
  }, 100);
  
  // Load faculty members for requested_by dropdown
  loadFacultyMembers();
}

window.closeAddBookReferenceModal = function() {
  document.getElementById('addBookReferenceModal').style.display = 'none';
  document.body.style.overflow = '';
  
  // Reset form
  document.getElementById('addBookReferenceForm').reset();
  addedBooks = [];
  generatedBooks = [];
  batchBooks = [];
  
  // Reset batch mode
  const byBatchCheckbox = document.getElementById('byBatchCheckbox');
  const batchActions = document.getElementById('batchActions');
  if (byBatchCheckbox) {
    byBatchCheckbox.checked = false;
  }
  if (batchActions) {
    batchActions.style.display = 'none';
  }
  
  // Reset copyright year to current year
  const copyrightYearInput = document.getElementById('copyright_year');
  const hintElement = document.getElementById('copyright_year_hint');
  if (copyrightYearInput) {
    const currentYear = new Date().getFullYear();
    copyrightYearInput.value = currentYear;
    // Update hint
    if (hintElement) {
      const minCompliantYear = currentYear - 4;
      hintElement.textContent = `Compliant range: ${minCompliantYear} - ${currentYear}`;
    }
  }
  
  // Reset button state
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    submitBtn.disabled = true;
  }
  
  // Reset to manual input
  switchInputMethod('manual');
}

// Confirmation Modal Functions
window.showConfirmationModal = function(message, callback) {
  const modal = document.getElementById('confirmationModal');
  const messageElement = document.getElementById('confirmationMessage');
  
  if (modal && messageElement) {
    messageElement.textContent = message;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Get buttons and attach event listeners
    const confirmBtn = document.getElementById('confirmationConfirmBtn');
    const cancelBtn = document.getElementById('confirmationCancelBtn');
    
    // Remove any existing event listeners by cloning the buttons
    const newConfirmBtn = confirmBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    
    // Add event listeners
    newConfirmBtn.addEventListener('click', function() {
      window.closeConfirmationModal();
      if (callback) callback();
    });
    
    newCancelBtn.addEventListener('click', function() {
      window.closeConfirmationModal();
    });
  }
}

window.closeConfirmationModal = function() {
  const modal = document.getElementById('confirmationModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

// Success Modal Functions
window.showSuccessModal = function(message) {
  const modal = document.getElementById('successModal');
  const messageElement = document.getElementById('successMessage');
  
  if (modal && messageElement) {
    messageElement.textContent = message;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

window.closeSuccessModal = function() {
  const modal = document.getElementById('successModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

// Error Modal Functions
window.showErrorModal = function(message) {
  const modal = document.getElementById('errorModal');
  const messageElement = document.getElementById('errorMessage');
  
  if (modal && messageElement) {
    messageElement.textContent = message;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

window.closeErrorModal = function() {
  const modal = document.getElementById('errorModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}

window.loadFacultyMembers = function() {
  // Check if requested_by field exists in the modal
  const select = document.getElementById('requested_by');
  if (!select) {
    console.log('requested_by field not found in modal, skipping loadFacultyMembers');
    return;
  }
  
  // TODO: Replace with actual API call to fetch department faculty
  const faculty = [];
  
  select.innerHTML = '<option value="">Select Faculty Member</option>';
  
  faculty.forEach(member => {
    const option = document.createElement('option');
    option.value = member.id;
    option.textContent = member.name;
    select.appendChild(option);
  });
}

// Function to attach event listeners
function attachEventListeners() {
  console.log('Attaching event listeners...');
  
  // Close modal button
  const closeBtn = document.getElementById('closeBookRefModal');
  if (closeBtn) {
    closeBtn.addEventListener('click', window.closeAddBookReferenceModal);
    console.log('Close button listener attached');
  }
  
  // Tab buttons
  const tabButtons = document.querySelectorAll('.tab-button');
  console.log('Found tab buttons:', tabButtons.length);
  tabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      console.log('Tab clicked:', this);
      const method = this.getAttribute('data-method');
      console.log('Method:', method);
      if (method) {
        window.switchInputMethod(method);
      }
    });
  });
  
  // Cancel button
  const cancelBtn = document.getElementById('cancelBookRefBtn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', window.closeAddBookReferenceModal);
    console.log('Cancel button listener attached');
  }
  
  // By Batch checkbox
  const byBatchCheckbox = document.getElementById('byBatchCheckbox');
  if (byBatchCheckbox) {
    byBatchCheckbox.addEventListener('change', toggleBatchMode);
  }
  
  // Add to List button
  const addToListBtn = document.getElementById('addToListBtn');
  if (addToListBtn) {
    addToListBtn.addEventListener('click', addBookToBatchList);
  }
  
  // Clear List button
  const clearListBtn = document.getElementById('clearListBtn');
  if (clearListBtn) {
    clearListBtn.addEventListener('click', clearBatchList);
  }
  
  // Call number search
  const callNumberInput = document.getElementById('call_number');
  if (callNumberInput) {
    callNumberInput.addEventListener('input', searchCallNumber);
    callNumberInput.addEventListener('blur', function() {
      // Hide suggestions when user clicks away
      setTimeout(() => {
        const suggestions = document.getElementById('callNumberSuggestions');
        if (suggestions) suggestions.style.display = 'none';
      }, 200);
    });
  }
}

// Batch mode functions
let batchBooks = [];

function toggleBatchMode() {
  const byBatchCheckbox = document.getElementById('byBatchCheckbox');
  const batchActions = document.getElementById('batchActions');
  const submitBtn = document.getElementById('submitBtn');
  
  if (byBatchCheckbox && batchActions && submitBtn) {
    if (byBatchCheckbox.checked) {
      batchActions.style.display = 'block';
      submitBtn.textContent = batchBooks.length > 0 ? `Add ${batchBooks.length} Books` : 'Add Books';
      submitBtn.disabled = batchBooks.length === 0;
    } else {
      batchActions.style.display = 'none';
      batchBooks = [];
      updateBatchListDisplay();
      submitBtn.textContent = 'Add Book';
      // Use validateForm for single mode instead of updateSubmitButton
      validateForm();
    }
  }
}

function addBookToBatchList() {
  const bookTitle = document.getElementById('book_title')?.value.trim();
  const copyrightYear = document.getElementById('copyright_year')?.value;
  const authors = document.getElementById('authors')?.value.trim();
  const publisher = document.getElementById('publisher')?.value.trim();
  const isbn = document.getElementById('isbn')?.value.trim();
  const callNumber = document.getElementById('call_number')?.value.trim();
  const edition = document.getElementById('edition')?.value.trim();
  
  if (!bookTitle || !copyrightYear) {
    window.showErrorModal('Please fill in Book Title and Copyright Year.');
    return;
  }
  
  const book = {
    id: Date.now(),
    book_title: bookTitle,
    publication_year: copyrightYear,
    authors: authors,
    publisher: publisher,
    isbn: isbn,
    call_number: callNumber,
    edition: edition
  };
  
  batchBooks.push(book);
  updateBatchListDisplay();
  
  // Clear form fields
  document.getElementById('book_title').value = '';
  document.getElementById('copyright_year').value = new Date().getFullYear();
  document.getElementById('authors').value = '';
  document.getElementById('publisher').value = '';
  document.getElementById('isbn').value = '';
  document.getElementById('call_number').value = '';
  document.getElementById('edition').value = '';
  
  // Update submit button
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    submitBtn.textContent = `Add ${batchBooks.length} Books`;
    submitBtn.disabled = false;
  }
}

function removeBookFromBatchList(bookId) {
  batchBooks = batchBooks.filter(book => book.id !== bookId);
  updateBatchListDisplay();
  
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    if (batchBooks.length > 0) {
      submitBtn.textContent = `Add ${batchBooks.length} Books`;
      submitBtn.disabled = false;
    } else {
      submitBtn.textContent = 'Add Books';
      submitBtn.disabled = true;
    }
  }
}

function clearBatchList() {
  if (batchBooks.length > 0) {
    if (confirm('Are you sure you want to clear all added books?')) {
      batchBooks = [];
      updateBatchListDisplay();
      const submitBtn = document.getElementById('submitBtn');
      if (submitBtn) {
        submitBtn.textContent = 'Add Books';
        submitBtn.disabled = true;
      }
    }
  }
}

function updateBatchListDisplay() {
  const container = document.getElementById('batchListContainer');
  if (!container) return;
  
  if (batchBooks.length === 0) {
    container.innerHTML = '<div style="padding: 8px; color: #999; text-align: center;">No books added yet</div>';
    return;
  }
  
  let html = '';
  batchBooks.forEach((book, index) => {
    // Format as APA 7th edition
    let apaFormat = '';
    
    // Author(s) - last name, initials
    if (book.authors && book.authors.trim() !== '') {
      apaFormat += book.authors.trim();
    }
    
    // Publication year
    if (book.publication_year) {
      apaFormat += ` (${book.publication_year})`;
    } else if (book.authors && book.authors.trim() !== '') {
      // Only add (n.d.) if we have an author
      apaFormat += ' (n.d.)';
    }
    
    // Book title - italicized
    if (book.book_title && book.book_title.trim() !== '') {
      apaFormat += `. <i>${book.book_title.trim()}</i>`;
    }
    
    // Edition (if not 1st)
    if (book.edition && book.edition.trim() !== '' && !book.edition.toLowerCase().includes('1st') && !book.edition.toLowerCase().includes('first')) {
      apaFormat += ` (${book.edition.trim()})`;
    }
    
    // Publisher
    if (book.publisher && book.publisher.trim() !== '') {
      apaFormat += `. ${book.publisher.trim()}`;
    }
    
    html += `
      <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px; border-bottom: 1px solid #eee; background: #f9f9f9; margin-bottom: 4px; border-radius: 4px;">
        <div style="flex: 1; font-size: 14px; line-height: 1.4;">
          <span style="font-weight: 600;">${index + 1}.</span> ${apaFormat}
        </div>
        <button type="button" onclick="removeBookFromBatchList(${book.id})" style="background: none; color: #dc3545; border: none; font-size: 24px; line-height: 1; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; transition: color 0.2s;" onmouseover="this.style.color='#c82333'" onmouseout="this.style.color='#dc3545'">&times;</button>
      </div>
    `;
  });
  
  container.innerHTML = html;
}

// Call number search function
function searchCallNumber() {
  const callNumberInput = document.getElementById('call_number');
  const suggestionsContainer = document.getElementById('callNumberSuggestions');
  
  if (!callNumberInput || !suggestionsContainer) return;
  
  const searchTerm = callNumberInput.value.trim();
  
  if (searchTerm.length < 2) {
    suggestionsContainer.style.display = 'none';
    return;
  }
  
  // Search in library_books table
  fetch('search_call_number.php?call_number=' + encodeURIComponent(searchTerm))
    .then(response => response.json())
    .then(data => {
      if (data.success && data.books && data.books.length > 0) {
        let html = '';
        data.books.forEach(book => {
          html += `
            <div onclick="selectBookSuggestion(${JSON.stringify(book).replace(/"/g, '&quot;')})" 
                 style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; hover: background: #f0f0f0;">
              <strong>${book.call_number || ''}</strong><br>
              <span style="color: #666;">${book.book_title || ''}</span>
            </div>
          `;
        });
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
      } else {
        suggestionsContainer.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error searching call number:', error);
      suggestionsContainer.style.display = 'none';
    });
}

function selectBookSuggestion(book) {
  document.getElementById('call_number').value = book.call_number || '';
  document.getElementById('book_title').value = book.book_title || '';
  document.getElementById('isbn').value = book.isbn || '';
  document.getElementById('publisher').value = book.publisher || '';
  document.getElementById('copyright_year').value = book.publication_year || new Date().getFullYear();
  document.getElementById('edition').value = book.edition || '';
  document.getElementById('authors').value = book.author || '';
  
  // Hide suggestions
  document.getElementById('callNumberSuggestions').style.display = 'none';
  
  window.showSuccessModal('Book details filled from library database!');
}

// Form validation and button enable/disable
function validateForm() {
  const bookTitle = document.getElementById('book_title')?.value.trim();
  const copyrightYear = document.getElementById('copyright_year')?.value;
  const submitBtn = document.getElementById('submitBtn');
  const byBatchCheckbox = document.getElementById('byBatchCheckbox');
  
  if (submitBtn) {
    // Check if we're in batch mode
    if (byBatchCheckbox && byBatchCheckbox.checked) {
      // In batch mode, only enable if there are books in the list
      submitBtn.disabled = batchBooks.length === 0;
    } else {
      // In single mode, enable based on form fields
      if (bookTitle && copyrightYear) {
        submitBtn.disabled = false;
      } else {
        submitBtn.disabled = true;
      }
    }
  }
}

// Handle copyright year input with up/down arrows only
function setupCopyrightYearInput() {
  const copyrightYearInput = document.getElementById('copyright_year');
  const upButton = document.getElementById('copyright_year_up');
  const downButton = document.getElementById('copyright_year_down');
  const hintElement = document.getElementById('copyright_year_hint');
  
  if (copyrightYearInput) {
    // Set initial value to current year
    const currentYear = new Date().getFullYear();
    
    // Calculate 5-year range (current year and 4 years back)
    const minCompliantYear = currentYear - 4;
    const maxCompliantYear = currentYear;
    
    // Set min and max attributes
    copyrightYearInput.setAttribute('min', minCompliantYear.toString());
    copyrightYearInput.setAttribute('max', maxCompliantYear.toString());
    copyrightYearInput.min = minCompliantYear;
    copyrightYearInput.max = maxCompliantYear;
    
    let currentValue = parseInt(copyrightYearInput.value) || currentYear;
    // Clamp initial value to compliant range
    currentValue = Math.max(minCompliantYear, Math.min(maxCompliantYear, currentValue));
    copyrightYearInput.value = currentValue;
    
    // Update hint text immediately - hide initially
    if (hintElement) {
      hintElement.textContent = `Compliant range: ${minCompliantYear} - ${maxCompliantYear}`;
      hintElement.style.display = 'none';
      hintElement.style.visibility = 'hidden';
    }
    
    // Set readonly to prevent typing
    copyrightYearInput.readOnly = true;
    copyrightYearInput.style.userSelect = 'none';
    
    // Function to update value - clamped to compliant range
    function updateValue(newValue) {
      const clampedValue = Math.max(minCompliantYear, Math.min(maxCompliantYear, newValue));
      currentValue = clampedValue;
      copyrightYearInput.value = currentValue;
      validateForm();
    }
    
    // Function to show hint text
    function showHint() {
      if (hintElement) {
        hintElement.style.display = 'block';
        hintElement.style.visibility = 'visible';
      }
    }
    
    // Up button click handler
    if (upButton) {
      upButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentValue < maxCompliantYear) {
          updateValue(currentValue + 1);
        }
        showHint();
      });
    }
    
    // Down button click handler
    if (downButton) {
      downButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (currentValue > minCompliantYear) {
          updateValue(currentValue - 1);
        }
        showHint();
      });
    }
    
    // Handle arrow keys
    copyrightYearInput.addEventListener('keydown', function(e) {
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (currentValue < maxCompliantYear) {
          updateValue(currentValue + 1);
          showHint();
        }
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (currentValue > minCompliantYear) {
          updateValue(currentValue - 1);
          showHint();
        }
      }
    });
    
    // Handle mouse wheel
    copyrightYearInput.addEventListener('wheel', function(e) {
      e.preventDefault();
      if (e.deltaY < 0 && currentValue < maxCompliantYear) {
        updateValue(currentValue + 1);
        showHint();
      } else if (e.deltaY > 0 && currentValue > minCompliantYear) {
        updateValue(currentValue - 1);
        showHint();
      }
    }, { passive: false });
  }
}

// Add event listeners for form validation
function setupFormValidation() {
  const requiredFields = ['call_number', 'book_title', 'copyright_year'];
  
  requiredFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('input', validateForm);
      field.addEventListener('change', validateForm);
    }
  });
  
  // Initial validation
  validateForm();
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  attachEventListeners();
  setupCopyrightYearInput();
  setupFormValidation();
});

// Also try to attach immediately
attachEventListeners();

// Add event delegation for dynamic content
document.addEventListener('click', function(e) {
  // Handle tab button clicks
  if (e.target.closest('.tab-button')) {
    const button = e.target.closest('.tab-button');
    const method = button.getAttribute('data-method');
    console.log('Tab clicked via delegation:', method);
    if (method) {
      window.switchInputMethod(method);
    }
  }
  
  // Handle cancel button clicks
  if (e.target.id === 'cancelBookRefBtn' || e.target.closest('#cancelBookRefBtn')) {
    console.log('Cancel button clicked via delegation');
    window.closeAddBookReferenceModal();
  }
  
  // Handle close button clicks
  if (e.target.id === 'closeBookRefModal' || e.target.closest('#closeBookRefModal')) {
    console.log('Close button clicked via delegation');
    window.closeAddBookReferenceModal();
  }
});

// Use MutationObserver to detect when modal is added to DOM
const observer = new MutationObserver(function(mutations) {
  mutations.forEach(function(mutation) {
    if (mutation.type === 'childList') {
      mutation.addedNodes.forEach(function(node) {
        if (node.nodeType === 1) { // Element node
          if (node.id === 'addBookReferenceModal' || node.querySelector && node.querySelector('#addBookReferenceModal')) {
            console.log('Modal detected in DOM, attaching listeners...');
            setTimeout(attachEventListeners, 50);
          }
        }
      });
    }
  });
});

// Start observing
observer.observe(document.body, {
  childList: true,
  subtree: true
});

// Also try to attach listeners every 500ms for the first 5 seconds
let attempts = 0;
const intervalId = setInterval(() => {
  attempts++;
  if (attempts > 10) {
    clearInterval(intervalId);
    return;
  }
  
  const modal = document.getElementById('addBookReferenceModal');
  if (modal) {
    console.log('Modal found, attaching listeners...');
    attachEventListeners();
    clearInterval(intervalId);
  }
}, 500);
</script>

<?php
// End of add_book_reference_modal.php
?>
