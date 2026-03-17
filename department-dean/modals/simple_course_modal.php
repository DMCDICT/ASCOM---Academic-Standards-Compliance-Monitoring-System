<?php
// Simple Course Modal - No Complex JavaScript
?>

<!-- Simple Add Course Modal -->
<div id="addCourseModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Course</h2>
            <span class="close" onclick="closeAddCourseModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <form id="addCourseForm" method="post" action="process_course.php">
                <input type="hidden" name="input_method" value="manual">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="courseCode">Course Code *</label>
                        <input type="text" id="courseCode" name="course_code" class="form-control" placeholder="e.g., IT101" required>
                    </div>
                    <div class="form-group">
                        <label for="courseName">Course Name *</label>
                        <input type="text" id="courseName" name="course_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="programs">Program(s) *</label>
                        <button type="button" id="openProgramSelectModalBtn" class="program-select-btn" onclick="openProgramSelectModal()">
                            <span id="programButtonText">Select Program(s)</span>
                        </button>
                        <input type="hidden" id="selectedProgramsInput" name="programs" value="">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="schoolTerm">School Term *</label>
                        <select id="schoolTerm" name="school_term" class="form-control" required>
                            <option value="">Select Term</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="schoolYear">School Year *</label>
                        <select id="schoolYear" name="school_year" class="form-control" required>
                            <option value="">Select Year</option>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2025-2026">2025-2026</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="yearLevel">Year Level *</label>
                        <select id="yearLevel" name="year_level" class="form-control" required>
                            <option value="">Select Year Level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeAddCourseModal()">CANCEL</button>
                    <button type="submit" class="create-btn" id="createCourseBtn">CREATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Program Selection Modal -->
<div id="programSelectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Select Program(s)</h2>
            <span class="close" onclick="closeProgramSelectModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <div id="programsList">
                <?php
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=ascom_db;charset=utf8", "root", "");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->query("SELECT id, program_name FROM programs ORDER BY program_name ASC");
                    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($programs)) {
                        echo '<div class="program-item"><span>No programs found</span></div>';
                    } else {
                        foreach ($programs as $program) {
                            echo '<div class="program-item">';
                            echo '<label>';
                            echo '<input type="checkbox" name="programs[]" value="' . $program['id'] . '" data-program-name="' . htmlspecialchars($program['program_name']) . '">';
                            echo '<span class="program-name">' . htmlspecialchars($program['program_name']) . '</span>';
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
                <button type="button" class="cancel-btn" onclick="closeProgramSelectModal()">CANCEL</button>
                <button type="button" class="create-btn" id="confirmProgramBtn" onclick="confirmProgramSelection()">CONFIRM</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Simple Modal Styles */
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
}

.modal-content {
    background-color: #EFEFEF;
    padding: 25px;
    border-radius: 15px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.program-select-btn {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    text-align: left;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.cancel-btn, .create-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.cancel-btn {
    background-color: #6c757d;
    color: white;
}

.create-btn {
    background-color: #4CAF50;
    color: white;
}

.create-btn:hover {
    background-color: #45a049;
}

.program-item {
    padding: 10px;
    border: 1px solid #ddd;
    margin-bottom: 5px;
    border-radius: 4px;
}

.program-item label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.program-item input[type="checkbox"] {
    margin-right: 10px;
}
</style>

<script>
// Simple JavaScript - No Complex Validation
function openAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAddCourseModal() {
    document.getElementById('addCourseModal').style.display = 'none';
    document.body.style.overflow = '';
}

function openProgramSelectModal() {
    document.getElementById('programSelectModal').style.display = 'flex';
    document.getElementById('programSelectModal').style.zIndex = '10001';
}

function closeProgramSelectModal() {
    document.getElementById('programSelectModal').style.display = 'none';
}

function confirmProgramSelection() {
    const selectedPrograms = [];
    const selectedNames = [];
    
    const checkboxes = document.querySelectorAll('#programSelectModal input[name="programs[]"]:checked');
    
    checkboxes.forEach(checkbox => {
        selectedPrograms.push(checkbox.value);
        selectedNames.push(checkbox.dataset.programName);
    });
    
    if (selectedPrograms.length === 0) {
        alert('Please select at least one program.');
        return;
    }
    
    // Update hidden input
    document.getElementById('selectedProgramsInput').value = selectedPrograms.join(',');
    
    // Update button text
    const programButtonText = document.getElementById('programButtonText');
    if (selectedNames.length === 1) {
        programButtonText.textContent = selectedNames[0];
    } else {
        programButtonText.textContent = `${selectedNames.length} Programs Selected`;
    }
    
    closeProgramSelectModal();
}

// Make functions globally available
window.openAddCourseModal = openAddCourseModal;
window.closeAddCourseModal = closeAddCourseModal;
window.openProgramSelectModal = openProgramSelectModal;
window.closeProgramSelectModal = closeProgramSelectModal;
window.confirmProgramSelection = confirmProgramSelection;
</script>
