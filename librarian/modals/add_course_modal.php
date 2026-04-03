<?php
// add_course_modal.php for Librarian
// This modal allows librarians to create courses that require dean approval
?>

<!-- Add Course Modal -->
<div id="librarianAddCourseModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Course</h2>
            <span class="close" onclick="closeLibrarianAddCourseModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <form id="librarianAddCourseForm" class="form-grid" method="post" autocomplete="off">
                <input type="hidden" name="input_method" value="manual">
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="created_by_role" value="librarian">
                
                <!-- Manual Input Section -->
                <div id="manualCourseSection" class="input-section active">
                    <div class="form-row">
                        <div class="form-group course-code-width">
                            <label for="librarianCourseCode">Course Code <span class="required">*</span></label>
                            <input type="text" id="librarianCourseCode" name="course_code" class="form-control" placeholder="e.g., IT101" required>
                        </div>
                        <div class="form-group course-name-width">
                            <label for="librarianCourseName">Course Name <span class="required">*</span></label>
                            <input type="text" id="librarianCourseName" name="course_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group long-width">
                            <label for="librarianPrograms">Program(s) <span class="required">*</span></label>
                            <div class="program-select-container">
                                <button type="button" id="openLibrarianProgramSelectBtn" class="program-select-btn" onclick="openLibrarianProgramSelectModal()">
                                    <span id="librarianProgramButtonText">Select Program(s) - No Program Selected</span>
                                    <span class="dropdown-arrow">▼</span>
                                </button>
                                <input type="hidden" id="librarianSelectedProgramsInput" name="programs" value="">
                            </div>
                        </div>
                        <div class="form-group small-width">
                            <label for="librarianUnits">Units</label>
                            <input type="number" id="librarianUnits" name="units" class="form-control" min="1" max="6" placeholder="3">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group medium-width">
                            <label for="librarianSchoolTerm">Academic Term <span class="required">*</span></label>
                            <select id="librarianSchoolTerm" name="school_term" class="form-control" required>
                                <option value="">Select Term</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer Semester">Summer Semester</option>
                            </select>
                        </div>
                        <div class="form-group medium-width">
                            <label for="librarianLocation">Location <span class="required">*</span></label>
                            <select id="librarianLocation" name="location" class="form-control" required>
                                <option value="">Select Location</option>
                                <option value="Main Library">Main Library</option>
                                <option value="Buenavista Library">Buenavista Library</option>
                            </select>
                        </div>
                        <div class="form-group medium-width">
                            <label for="librarianSchoolYear">Academic Year <span class="required">*</span></label>
                            <select id="librarianSchoolYear" name="school_year" class="form-control" required>
                                <option value="">Select Academic Year</option>
                                <?php
                                // Get real database data
                                try {
                                    require_once dirname(__DIR__, 2) . '/bootstrap/database.php';
                                    $pdo = ascom_get_pdo();
                                    
                                    $stmt = $pdo->query("SELECT * FROM school_years ORDER BY id DESC LIMIT 10");
                                    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (empty($years)) {
                                        echo '<option value="">No school years found in database</option>';
                                    } else {
                                        foreach ($years as $year) {
                                            $id = $year['id'];
                                            $label = $year['school_year_label'] ?? $year['school_year'] ?? 'Year ' . $id;
                                            echo "<option value='$id'>$label</option>";
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo '<option value="">Database connection failed</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="closeLibrarianAddCourseModal()">CANCEL</button>
                        <button type="submit" class="create-btn" id="librarianCreateCourseBtn" disabled onclick="handleLibrarianCreateButtonClick(event)">CREATE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Program Selection Modal -->
<div id="librarianProgramSelectModal" class="modal" style="display: none; z-index: 10001;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Select Program(s)</h2>
            <span class="close" onclick="closeLibrarianProgramSelectModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="program-search-container">
                <input type="text" id="librarianProgramSearch" class="program-search-input" placeholder="Search programs by code or name..." autocomplete="off">
            </div>
            <div id="librarianProgramsList">
                <?php
                // REAL DATABASE DATA ONLY
                try {
                    require_once dirname(__DIR__, 2) . '/bootstrap/database.php';
                    $pdo = ascom_get_pdo();
                    
                    // Get all programs (librarians can see all)
                    $stmt = $pdo->query("SELECT id, program_code, program_name FROM programs ORDER BY program_code ASC");
                    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($programs)) {
                        echo '<div class="program-item"><span>No programs found in database</span></div>';
                    } else {
                        foreach ($programs as $program) {
                            echo '<div class="program-item">';
                            echo '<label>';
                            echo '<input type="checkbox" name="programs[]" value="' . $program['id'] . '" data-program-name="' . htmlspecialchars($program['program_code'] . ' - ' . $program['program_name']) . '">';
                            echo '<span class="program-name">' . htmlspecialchars($program['program_code'] . ' - ' . $program['program_name']) . '</span>';
                            echo '</label>';
                            echo '</div>';
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="program-item"><span>Database connection failed</span></div>';
                }
                ?>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeLibrarianProgramSelectModal()">CANCEL</button>
                <button type="submit" class="create-btn" id="librarianConfirmProgramBtn" onclick="confirmLibrarianProgramSelection()" disabled>CONFIRM</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="librarianCourseSuccessModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content success-modal">
        <div class="modal-header">
            <h2>✅ Course Created Successfully!</h2>
            <span class="close" onclick="closeLibrarianCourseSuccessModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon">📋</div>
                <p class="success-message">Your course has been created successfully and is now available in the system.</p>
                <div class="success-details">
                    <p><strong>Course Code:</strong> <span id="librarianSuccessCourseCode"></span></p>
                    <p><strong>Course Name:</strong> <span id="librarianSuccessCourseName"></span></p>
                    <p><strong>Program:</strong> <span id="librarianSuccessProgram"></span></p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center;">
            <button type="button" class="create-btn" onclick="closeLibrarianCourseSuccessModal()">OK</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="librarianCourseErrorModal" class="modal" style="display: none; z-index: 10002;">
    <div class="modal-content error-modal">
        <div class="modal-header">
            <h2>❌ Course Submission Failed</h2>
            <span class="close" onclick="closeLibrarianCourseErrorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-content">
                <div class="error-icon">⚠️</div>
                <p class="error-message" id="librarianErrorMessage">An error occurred while submitting the course. Please try again.</p>
                <div class="error-details" id="librarianErrorDetails" style="display: none;">
                    <p><strong>Error Details:</strong></p>
                    <pre id="librarianErrorDetailsText"></pre>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" onclick="closeLibrarianCourseErrorModal()">Close</button>
            <button type="button" class="create-btn" onclick="retryLibrarianCourseCreation()">Retry</button>
        </div>
    </div>
</div>

<style>
/* Reuse modal styles from department-dean modal */
.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: auto;
    overscroll-behavior: contain;
}

.modal-content {
    background-color: #EFEFEF;
    margin: auto;
    padding: 25px;
    border: 1px solid #888;
    border-radius: 15px;
    width: 80%;
    max-width: 600px;
    min-height: 300px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.cancel-btn, .create-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: all 0.3s ease;
}

.cancel-btn {
    background-color: #6c757d;
    color: white;
}

.create-btn {
    background-color: #4CAF50;
    color: white;
}

.create-btn:hover:not(:disabled) {
    background-color: #45a049;
}

.cancel-btn:hover {
    background-color: #5a6268;
}

.create-btn:disabled {
    background-color: #6c757d;
    color: white;
    cursor: not-allowed;
    opacity: 0.6;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 0px 0px;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.required {
    color: red;
}

.form-control {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

.form-group.small-width {
    flex: 0 0 15%;
    max-width: 15%;
}

.form-group.medium-width {
    flex: 0 0 30%;
    max-width: 30%;
}

.form-group.long-width {
    flex: 0 0 80%;
    max-width: 80%;
}

.form-group.course-code-width {
    flex: 0 0 20%;
    max-width: 20%;
}

.form-group.course-name-width {
    flex: 0 0 75%;
    max-width: 75%;
}

.program-select-container {
    position: relative;
}

.program-select-btn {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
    text-align: left;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    height: 50px;
    transition: border-color 0.2s ease;
}

.program-select-btn:hover {
    border-color: #007bff;
}

.program-select-btn:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

.dropdown-arrow {
    font-size: 12px;
    color: #666;
}

/* Program Selection Modal - Fixed height with scrollable list */
#librarianProgramSelectModal .modal-body {
    display: flex;
    flex-direction: column;
    max-height: 70vh;
    overflow: hidden;
}

.program-search-container {
    flex-shrink: 0;
    margin-bottom: 15px;
}

.program-search-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

.program-search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

#librarianProgramsList {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    max-height: calc(70vh - 160px);
    margin-bottom: 15px;
    padding-right: 5px;
}

#librarianProgramSelectModal .form-actions {
    flex-shrink: 0;
    margin-top: 0;
}

.program-item.hidden {
    display: none;
}

/* Custom scrollbar for programs list */
#librarianProgramsList::-webkit-scrollbar {
    width: 8px;
}

#librarianProgramsList::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#librarianProgramsList::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#librarianProgramsList::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.program-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.program-item:last-child {
    border-bottom: none;
}

.program-item label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin: 0;
}

.program-item input[type="checkbox"] {
    margin-right: 10px;
}

.program-name {
    font-weight: normal;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    margin-right: 0;
    padding-right: 0;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.success-modal .modal-content {
    background-color: #EFEFEF;
    border: 1px solid #888;
    border-radius: 15px;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.success-content {
    text-align: center;
    padding: 20px 0;
}

.success-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.success-message {
    color: #333;
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.success-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    text-align: left;
    margin-top: 15px;
    border: 1px solid #ddd;
}

.success-details p {
    margin: 5px 0;
    color: #495057;
}

.error-modal .modal-content {
    background-color: #EFEFEF;
    border: 1px solid #888;
    border-radius: 15px;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.error-content {
    text-align: center;
    padding: 20px 0;
}

.error-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.error-message {
    color: #333;
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.error-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    text-align: left;
    margin-top: 15px;
    border: 1px solid #ddd;
}

.error-details pre {
    background-color: #e9ecef;
    padding: 10px;
    border-radius: 3px;
    font-size: 12px;
    color: #495057;
    white-space: pre-wrap;
    word-break: break-word;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
</style>
