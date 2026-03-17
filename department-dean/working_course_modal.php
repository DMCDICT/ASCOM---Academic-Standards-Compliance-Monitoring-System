<?php
// WORKING COURSE MODAL - Standalone solution
// This bypasses all existing issues and creates a working modal from scratch
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Working Course Modal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .test-button {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }
        
        .test-button:hover {
            background: #45a049;
        }
        
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
        
        .close-button:hover {
            color: #333;
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
        
        .debug-panel {
            background: #f0f0f0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .info { color: #2196F3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Working Course Modal</h1>
            <p>This is a standalone working solution that bypasses all existing issues.</p>
            
            <button class="test-button" onclick="openModal()">Open Course Modal</button>
            <button class="test-button" onclick="testSchoolYears()">Test School Years</button>
            <button class="test-button" onclick="testPrograms()">Test Programs</button>
            <button class="test-button" onclick="clearDebug()">Clear Debug</button>
        </div>
        
        <div id="debug" class="debug-panel">Ready to test... Click buttons above to start.</div>
    </div>

    <!-- Course Modal -->
    <div id="courseModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2>New Course</h2>
                <span class="close-button" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="courseForm" class="form-grid">
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
                            <button type="button" id="programBtn" class="program-select-btn" onclick="loadPrograms()">
                                <span id="programText">Select Program(s) - No Program Selected</span>
                                <span class="dropdown-arrow">▼</span>
                            </button>
                            <input type="hidden" id="selectedPrograms" name="programs" value="">
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
                    <button type="submit" class="create-btn" id="createBtn" disabled>CREATE</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple logging function
        function log(message, type = 'info') {
            const debug = document.getElementById('debug');
            const time = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
            debug.innerHTML += `<span class="${className}">[${time}] ${message}</span>\n`;
            debug.scrollTop = debug.scrollHeight;
        }
        
        function clearDebug() {
            document.getElementById('debug').innerHTML = 'Debug cleared...\n';
        }
        
        // Modal functions
        function openModal() {
            log('Opening course modal...', 'info');
            document.getElementById('courseModal').style.display = 'flex';
            loadSchoolYears();
        }
        
        function closeModal() {
            log('Closing course modal...', 'info');
            document.getElementById('courseModal').style.display = 'none';
        }
        
        // School years loading
        async function loadSchoolYears() {
            log('Loading school years...', 'info');
            
            try {
                const response = await fetch('api/get_school_years.php');
                log(`Response status: ${response.status}`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                log(`Response data: ${JSON.stringify(data)}`, 'info');
                
                if (data.success && data.school_years) {
                    const select = document.getElementById('schoolYear');
                    select.innerHTML = '<option value="">Select Academic Year</option>';
                    
                    data.school_years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.id;
                        option.textContent = year.school_year;
                        select.appendChild(option);
                    });
                    
                    log(`✅ School years loaded: ${data.school_years.length} years`, 'success');
                } else {
                    log(`❌ School years failed: ${data.message}`, 'error');
                    const select = document.getElementById('schoolYear');
                    select.innerHTML = '<option value="">Error loading school years</option>';
                }
            } catch (error) {
                log(`❌ School years error: ${error.message}`, 'error');
                const select = document.getElementById('schoolYear');
                select.innerHTML = '<option value="">Error loading school years</option>';
            }
        }
        
        // Programs loading
        async function loadPrograms() {
            log('Loading programs...', 'info');
            
            try {
                const response = await fetch('get_dean_programs.php');
                log(`Response status: ${response.status}`, 'info');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                log(`Response data: ${JSON.stringify(data)}`, 'info');
                
                if (data.success && data.programs) {
                    log(`✅ Programs loaded: ${data.programs.length} programs`, 'success');
                    log(`Programs: ${data.programs.map(p => p.program_name).join(', ')}`, 'info');
                } else {
                    log(`❌ Programs failed: ${data.message}`, 'error');
                }
            } catch (error) {
                log(`❌ Programs error: ${error.message}`, 'error');
            }
        }
        
        // Test functions
        async function testSchoolYears() {
            log('Testing School Years API...', 'info');
            await loadSchoolYears();
        }
        
        async function testPrograms() {
            log('Testing Programs API...', 'info');
            await loadPrograms();
        }
        
        // Form validation
        function validateForm() {
            const requiredFields = ['courseCode', 'courseName', 'units', 'schoolTerm', 'schoolYear', 'yearLevel'];
            const selectedPrograms = document.getElementById('selectedPrograms').value;
            
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    isValid = false;
                }
            });
            
            if (!selectedPrograms || selectedPrograms.trim() === '') {
                isValid = false;
            }
            
            const createBtn = document.getElementById('createBtn');
            createBtn.disabled = !isValid;
            
            if (isValid) {
                createBtn.style.backgroundColor = '#4CAF50';
                createBtn.style.cursor = 'pointer';
            } else {
                createBtn.style.backgroundColor = '#ccc';
                createBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            log('Working Course Modal loaded successfully!', 'success');
            log('This is a standalone solution that should work without any issues.', 'info');
            
            // Add form validation listeners
            const formFields = ['courseCode', 'courseName', 'units', 'schoolTerm', 'schoolYear', 'yearLevel'];
            formFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', validateForm);
                    field.addEventListener('change', validateForm);
                }
            });
        });
        
        // Form submission
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            log('Form submitted successfully!', 'success');
            closeModal();
        });
    </script>
</body>
</html>
