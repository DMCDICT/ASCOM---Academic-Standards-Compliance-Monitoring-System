<?php
// Minimal Course Modal - No complex JavaScript dependencies
?>
<!DOCTYPE html>
<html>
<head>
    <title>Minimal Course Modal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test-button { 
            background: #4CAF50; 
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin: 10px;
            font-size: 16px;
        }
        .test-button:hover { background: #45a049; }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e9ecef;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        .close-button {
            font-size: 24px;
            cursor: pointer;
            color: #999;
            line-height: 1;
        }
        .form-grid {
            padding: 24px;
        }
        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        .form-group {
            flex: 1;
        }
        .form-group.course-code-width {
            flex: 0 0 20%;
            max-width: 20%;
        }
        .form-group.course-name-width {
            flex: 0 0 73%;
            max-width: 73%;
        }
        .form-group.long-width {
            flex: 0 0 78%;
            max-width: 78%;
        }
        .form-group.small-width {
            flex: 0 0 15%;
            max-width: 15%;
        }
        .form-group.medium-width {
            flex: 0 0 30%;
            max-width: 30%;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }
        .required {
            color: #e74c3c;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #6f42c1;
            box-shadow: 0 0 0 2px rgba(111, 66, 193, 0.1);
        }
        .program-select-container {
            position: relative;
        }
        .program-select-btn {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            text-align: left;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: border-color 0.2s;
        }
        .program-select-btn:hover {
            border-color: #6f42c1;
        }
        .dropdown-arrow {
            color: #999;
            font-size: 12px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .cancel-btn, .create-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .cancel-btn {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        .create-btn {
            background: #4CAF50;
            color: white;
        }
        .create-btn:hover:not(:disabled) {
            background: #45a049;
        }
        .create-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .result {
            background: #f0f0f0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <h1>Minimal Course Modal Test</h1>
    <p>This is a standalone test to verify the modal works without any JavaScript conflicts.</p>
    
    <button class="test-button" onclick="openModal()">Open Course Modal</button>
    <button class="test-button" onclick="testSchoolYears()">Test School Years API</button>
    <button class="test-button" onclick="testPrograms()">Test Programs API</button>
    <button class="test-button" onclick="clearResults()">Clear Results</button>
    
    <div id="result" class="result">Click buttons to test...</div>

    <!-- Course Modal -->
    <div id="addCourseModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2>New Course</h2>
                <span class="close-button" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="addCourseForm" class="form-grid">
                <div class="form-row">
                    <div class="form-group course-code-width">
                        <label for="courseCode">Course Code <span class="required">*</span></label>
                        <input type="text" id="courseCode" name="course_code" class="form-control" placeholder="e.g., IT101" required>
                    </div>
                    <div class="form-group course-name-width">
                        <label for="courseName">Course Name <span class="required">*</span></label>
                        <input type="text" id="courseName" name="course_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group long-width">
                        <label for="programs">Program(s) <span class="required">*</span></label>
                        <div class="program-select-container">
                            <button type="button" id="openProgramSelectModalBtn" class="program-select-btn" onclick="loadPrograms()">
                                <span id="programButtonText">Select Program(s) - No Program Selected</span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <input type="hidden" id="selectedProgramsInput" name="programs" value="">
                        </div>
                    </div>
                    <div class="form-group small-width">
                        <label for="units">Units <span class="required">*</span></label>
                        <input type="number" id="units" name="units" class="form-control" min="1" max="6" placeholder="3" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group medium-width">
                        <label for="schoolTerm">Academic Term <span class="required">*</span></label>
                        <select id="schoolTerm" name="school_term" class="form-control" required>
                            <option value="">Select Term</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer Semester">Summer Semester</option>
                        </select>
                    </div>
                    <div class="form-group medium-width">
                        <label for="schoolYear">Academic Year <span class="required">*</span></label>
                        <select id="schoolYear" name="school_year" class="form-control" required>
                            <option value="">Select Academic Year</option>
                        </select>
                    </div>
                    <div class="form-group medium-width">
                        <label for="yearLevel">Year Level <span class="required">*</span></label>
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
                    <button type="button" class="cancel-btn" onclick="closeModal()">CANCEL</button>
                    <button type="submit" class="create-btn" id="createCourseBtn" disabled>CREATE</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function log(message) {
            const result = document.getElementById('result');
            const timestamp = new Date().toLocaleTimeString();
            result.textContent += `[${timestamp}] ${message}\n`;
            result.scrollTop = result.scrollHeight;
        }
        
        function clearResults() {
            document.getElementById('result').textContent = 'Results cleared...\n';
        }
        
        function openModal() {
            log('Opening modal...');
            document.getElementById('addCourseModal').style.display = 'flex';
            loadSchoolYears();
        }
        
        function closeModal() {
            log('Closing modal...');
            document.getElementById('addCourseModal').style.display = 'none';
        }
        
        async function loadSchoolYears() {
            log('Loading school years...');
            
            try {
                const response = await fetch('api/get_school_years.php');
                log(`School years response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                log(`School years data: ${JSON.stringify(data)}`);
                
                if (data.success && data.school_years) {
                    const schoolYearSelect = document.getElementById('schoolYear');
                    schoolYearSelect.innerHTML = '<option value="">Select Academic Year</option>';
                    
                    data.school_years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.id;
                        option.textContent = year.school_year;
                        schoolYearSelect.appendChild(option);
                    });
                    
                    log(`✅ School years loaded successfully: ${data.school_years.length} years`);
                } else {
                    log(`❌ Failed to load school years: ${JSON.stringify(data)}`);
                    const schoolYearSelect = document.getElementById('schoolYear');
                    schoolYearSelect.innerHTML = '<option value="">Error loading school years</option>';
                }
            } catch (error) {
                log(`❌ Error loading school years: ${error.message}`);
                const schoolYearSelect = document.getElementById('schoolYear');
                schoolYearSelect.innerHTML = '<option value="">Error loading school years</option>';
            }
        }
        
        async function loadPrograms() {
            log('Loading programs...');
            
            try {
                const response = await fetch('get_dean_programs.php');
                log(`Programs response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                log(`Programs data: ${JSON.stringify(data)}`);
                
                if (data.success && data.programs) {
                    log(`✅ Programs loaded successfully: ${data.programs.length} programs`);
                    log(`Programs: ${data.programs.map(p => p.program_name).join(', ')}`);
                } else {
                    log(`❌ Failed to load programs: ${JSON.stringify(data)}`);
                }
            } catch (error) {
                log(`❌ Error loading programs: ${error.message}`);
            }
        }
        
        async function testSchoolYears() {
            log('Testing School Years API...');
            await loadSchoolYears();
        }
        
        async function testPrograms() {
            log('Testing Programs API...');
            await loadPrograms();
        }
        
        // Initialize
        log('Minimal Course Modal Test Ready');
        log('This test bypasses all complex JavaScript and focuses on the core functionality');
    </script>
</body>
</html>
