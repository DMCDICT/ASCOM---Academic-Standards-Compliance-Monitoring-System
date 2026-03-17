<?php
// edit_course_modal.php
// Modal for editing an existing course (Department Dean)
?>

<div id="editCourseModal" class="modal-overlay" style="display: none;">
  <div class="modal-box">
    <div class="modal-header">
      <h2>Edit Course</h2>
      <span class="close-button" onclick="closeEditCourseModal()">&times;</span>
    </div>
    <form id="editCourseForm" class="form-grid" method="post" autocomplete="off">
      <input type="hidden" name="course_id" id="edit_course_id">
      <input type="hidden" name="action" value="update_course">
      
      <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="edit_course_code">Course Code</label>
          <input type="text" name="course_code" id="edit_course_code" required>
        </div>
        <div class="form-group" style="flex:2; min-width: 200px;">
          <label for="edit_course_name">Course Name</label>
          <input type="text" name="course_title" id="edit_course_name" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:2; min-width: 200px;">
          <label>Program(s)</label>
          <button type="button" id="editOpenProgramSelectModalBtn" style="padding:12px 16px; border-radius:8px; border:1px solid #ccc; background:#f5f5f5; font-size:1rem; font-family:'TT Interphases',sans-serif; cursor:pointer; width:100%; text-align:left; position:relative; min-height:48px; display:flex; align-items:center;">
            <span id="editProgramButtonText">Select Program(s) - No Program Selected</span>
            <span style="position:absolute; right:12px; font-size:12px; color:#666;">▼</span>
          </button>
          <input type="hidden" name="programs" id="editSelectedProgramsInput">
        </div>
        <div class="form-group" style="flex:0 0 80px; min-width: 80px; max-width: 100px;">
          <label for="edit_units">Units</label>
          <input type="number" name="units" id="edit_units" min="1" max="10" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 120px;">
          <label for="edit_school_term">School Term</label>
          <div class="custom-select-wrapper">
            <select name="school_term" id="edit_school_term" required>
              <option value="">-- Select Term --</option>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer Semester">Summer Semester</option>
            </select>
          </div>
        </div>
         <div class="form-group" style="flex:1.2; min-width: 150px;">
           <label for="edit_school_year">School Year</label>
           <div class="custom-select-wrapper">
             <select name="school_year" id="edit_school_year" required>
               <option value="">-- Select Year --</option>
             </select>
           </div>
         </div>
        <div class="form-group" style="flex:1; min-width: 120px;">
          <label for="edit_year_level">Year Level</label>
          <div class="custom-select-wrapper">
            <select name="year_level" id="edit_year_level" required>
              <option value="">-- Select Level --</option>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
              <option value="4th Year">4th Year</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="closeEditCourseModal()">CANCEL</button>
        <button type="submit" class="create-btn" id="updateCourseBtn" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.6;">UPDATE</button>
      </div>
    </form>
  </div>
</div>

<!-- Program Select Modal for Edit Course - EXACT COPY OF NEW COURSE MODAL -->
<div id="editProgramSelectModal" class="modal-overlay" style="display:none; z-index:2000;">
  <div class="modal-box">
    <div class="modal-header">
      <h2>Select Program(s)</h2>
      <span class="close-button" onclick="closeEditProgramSelectModal()">&times;</span>
    </div>
    <form id="editProgramSelectForm" class="form-grid" onsubmit="return false;">
      <div class="form-group" id="editProgramCheckboxes">
        <?php
        // Fetch programs from database - with robust fallback
        $programs = [];
        $useFallback = false;
        
        try {
            if (isset($pdo)) {
                // Try to get programs from database
                $query = "SELECT p.id, p.program_code, p.program_name, p.color_code FROM programs p ORDER BY p.program_code ASC";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($programs)) {
                    $useFallback = true;
                }
            } else {
                $useFallback = true;
            }
        } catch (Exception $e) {
            $useFallback = true;
            echo "<p style='color: orange;'>Database error: " . htmlspecialchars($e->getMessage()) . " - Using fallback data</p>";
        }
        
        // Use fallback data if database failed or returned no results
        if ($useFallback) {
            $programs = [
                ['id' => '1', 'program_code' => 'BLIS', 'program_name' => 'Bachelor of Library and Information Science', 'color_code' => '#FF9800'],
                ['id' => '2', 'program_code' => 'BSCS', 'program_name' => 'Bachelor of Science in Computer Science', 'color_code' => '#1976d2'],
                ['id' => '3', 'program_code' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology', 'color_code' => '#4CAF50'],
                ['id' => '4', 'program_code' => 'BSCE', 'program_name' => 'Bachelor of Science in Civil Engineering', 'color_code' => '#9C27B0']
            ];
        }
        
        // Always show programs (either from database or fallback)
        foreach ($programs as $program) {
            $programId = $program['id'];
            $programCode = htmlspecialchars($program['program_code']);
            $programName = htmlspecialchars($program['program_name']);
            $displayName = $programName;
            
            echo "<label><input type='checkbox' name='programs[]' value='$programId' id='edit_modal_program_$programId'> $programCode - $displayName</label>";
        }
        ?>
      </div>
      <div class="form-actions">
        <button type="button" class="reset-btn" onclick="resetEditProgramSelection()">RESET</button>
        <button type="button" class="cancel-btn" onclick="closeEditProgramSelectModal()">CANCEL</button>
        <button type="button" class="create-btn" id="confirmEditProgramSelectBtn">CONFIRM</button>
      </div>
    </form>
  </div>
</div>

<style>
/* Edit Program Select Modal - EXACT COPY FROM course-modal.css */
#editProgramSelectModal .modal-box {
  max-width: 600px !important; /* Extended width */
  max-height: 80vh !important; /* Limit height to viewport */
  display: flex !important;
  flex-direction: column !important;
}

#editProgramSelectModal .modal-header {
  flex-shrink: 0 !important; /* Keep header fixed */
}

#editProgramSelectModal .form-grid {
  display: flex !important;
  flex-direction: column !important;
  height: 100% !important;
  max-height: calc(80vh - 120px) !important; /* Account for header and buttons */
}

#editProgramSelectModal .form-group {
  flex: 1 !important;
  overflow-y: auto !important; /* Add scroll to program list */
  max-height: calc(80vh - 200px) !important; /* Leave space for buttons */
  padding: 10px 0 !important;
  border: 1px solid #e0e0e0 !important;
  border-radius: 8px !important;
  margin: 10px 0 !important;
}

/* Custom scrollbar styling for program list */
#editProgramSelectModal .form-group::-webkit-scrollbar {
  width: 8px;
}

#editProgramSelectModal .form-group::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

#editProgramSelectModal .form-group::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

#editProgramSelectModal .form-group::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

#editProgramSelectModal .form-actions {
  flex-shrink: 0 !important; /* Keep buttons fixed at bottom */
  margin-top: auto !important;
  padding-top: 15px !important;
  border-top: 1px solid #e0e0e0 !important;
}

/* Improved checkbox styling for program selection - SPECIFIC TO PROGRAM MODAL ONLY */
#editProgramSelectModal .form-group input[type='checkbox'] {
  width: 20px !important;
  height: 20px !important;
  margin: 0 !important;
  padding: 0 !important;
  cursor: pointer !important;
  flex-shrink: 0 !important;
  accent-color: #1976d2 !important;
  transform: scale(1) !important;
  border: none !important;
  background: none !important;
  border-radius: 0 !important;
  box-sizing: border-box !important;
  min-width: unset !important;
  max-width: unset !important;
}

#editProgramSelectModal .form-group label {
  font-size: 1.1rem !important;
  font-weight: normal !important;
  margin-bottom: 8px !important;
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  padding: 8px 12px !important;
  cursor: pointer !important;
  font-family: 'TT Interphases', sans-serif !important;
  transition: all 0.2s ease !important;
  user-select: none !important;
  line-height: 1.4 !important;
  height: auto !important;
  min-height: unset !important;
  border-radius: 4px !important;
  margin-left: 8px !important;
  margin-right: 8px !important;
}

#editProgramSelectModal .form-group label:hover {
  background-color: #f8f9fa !important;
}

#editProgramSelectModal .form-group label:active {
  background-color: #e9ecef !important;
}

/* Reset button styling */
#editProgramSelectModal .reset-btn {
  background-color: #ff6b6b !important;
  color: white !important;
  border: none !important;
  border-radius: 10px !important;
  padding: 10px 20px !important;
  font-size: 14px !important;
  font-weight: bold !important;
  text-transform: uppercase !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
  height: 50px !important;
}

#editProgramSelectModal .reset-btn:hover {
  background-color: #ff5252 !important;
}

#editProgramSelectModal .reset-btn:disabled {
  background-color: #ccc !important;
  color: #999 !important;
  cursor: not-allowed !important;
}

/* Confirm button styling */
#editProgramSelectModal .create-btn {
  background-color: #4CAF50 !important;
  color: white !important;
  border: none !important;
  border-radius: 10px !important;
  padding: 10px 20px !important;
  font-size: 14px !important;
  font-weight: bold !important;
  text-transform: uppercase !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
  height: 50px !important;
}

#editProgramSelectModal .create-btn:hover:not(:disabled) {
  background-color: #45a049 !important;
}

#editProgramSelectModal .create-btn:disabled {
  background-color: #ccc !important;
  color: #999 !important;
  cursor: not-allowed !important;
}
</style>

<script>
// Edit Course Modal Functions - Make this function globally available
window.openEditCourseModal = async function(courseId, courseData) {
    console.log('Opening edit course modal for:', courseId, courseData);
    console.log('Full course data object:', JSON.stringify(courseData, null, 2));
    
    // Store original data for comparison
    window.originalCourseData = {
        course_code: courseData.course_code || '',
        course_title: courseData.course_title || '',
        units: courseData.units || '',
        term: courseData.term || '',
        academic_year: courseData.academic_year || '',
        year_level: courseData.year_level || '',
        programs: courseData.programs || []
    };
    
    // Fill form with course data
    document.getElementById('edit_course_id').value = courseId;
    document.getElementById('edit_course_code').value = courseData.course_code || '';
    document.getElementById('edit_course_name').value = courseData.course_title || '';
    document.getElementById('edit_units').value = courseData.units || '';
    
    // Map term values
    const termValue = courseData.term || '';
    console.log('Term value from database:', termValue);
    
    const termSelect = document.getElementById('edit_school_term');
    
    // Direct mapping based on database values - now using full semester names
    if (termValue.includes('1st') || termValue.includes('First')) {
        termSelect.value = '1st Semester';
        console.log('Set term to: 1st Semester');
    } else if (termValue.includes('2nd') || termValue.includes('Second')) {
        termSelect.value = '2nd Semester';
        console.log('Set term to: 2nd Semester');
    } else if (termValue.includes('Summer') || termValue.includes('summer')) {
        termSelect.value = 'Summer Semester';
        console.log('Set term to: Summer Semester');
    } else {
        console.log('No match found for term:', termValue);
    }
    
    // Map academic year values
    const academicYear = courseData.academic_year || '';
    console.log('=== SCHOOL YEAR DEBUG ===');
    console.log('Raw academic year value:', `"${academicYear}"`);
    console.log('Academic year length:', academicYear.length);
    console.log('Academic year type:', typeof academicYear);
    console.log('Academic year char codes:', Array.from(academicYear).map(c => c.charCodeAt(0)));
    
    const schoolYearSelect = document.getElementById('edit_school_year');
    console.log('Available school year options:');
    for (let i = 0; i < schoolYearSelect.options.length; i++) {
        console.log(`  Option ${i}: value="${schoolYearSelect.options[i].value}", text="${schoolYearSelect.options[i].textContent}"`);
    }
    
    // Try multiple matching strategies
    let yearRange = '';
    
    // Strategy 1: Direct contains check
    console.log('Strategy 1: Direct contains check');
    if (academicYear.includes('2024-2025')) {
        yearRange = '2024-2025';
        console.log('Match found via contains 2024-2025');
    } else if (academicYear.includes('2025-2026')) {
        yearRange = '2025-2026';
        console.log('Match found via contains 2025-2026');
    } else if (academicYear.includes('2026-2027')) {
        yearRange = '2026-2027';
        console.log('Match found via contains 2026-2027');
    } else if (academicYear.includes('2023-2024')) {
        yearRange = '2023-2024';
        console.log('Match found via contains 2023-2024');
    } else {
        console.log('No direct contains match found');
    }
    
    // Strategy 2: Try to extract year pattern with more flexible regex
    if (!yearRange) {
        console.log('Strategy 2: Regex extraction');
        const yearMatch = academicYear.match(/(\d{4}-\d{4})/);
        if (yearMatch) {
            yearRange = yearMatch[1];
            console.log('Match found via regex:', yearRange);
        } else {
            console.log('No regex match found');
        }
    }
    
    // Strategy 3: Check if any option value is contained in the academic year
    if (!yearRange) {
        console.log('Strategy 3: Option contains check');
        for (let i = 1; i < schoolYearSelect.options.length; i++) { // Skip first empty option
            const optionValue = schoolYearSelect.options[i].value;
            console.log(`Checking option "${optionValue}" against "${academicYear}"`);
            if (academicYear.includes(optionValue)) {
                yearRange = optionValue;
                console.log('Match found via option contains:', yearRange);
                break;
            }
        }
    }
    
    // Strategy 4: Try to extract just the year numbers
    if (!yearRange) {
        console.log('Strategy 4: Extract year numbers');
        const numbers = academicYear.match(/\d{4}/g);
        if (numbers && numbers.length >= 2) {
            const extracted = `${numbers[0]}-${numbers[1]}`;
            console.log('Extracted year range:', extracted);
            // Check if this extracted range matches any option
            for (let i = 1; i < schoolYearSelect.options.length; i++) {
                if (schoolYearSelect.options[i].value === extracted) {
                    yearRange = extracted;
                    console.log('Match found via extracted numbers:', yearRange);
                    break;
                }
            }
        }
    }
    
    // Strategy 5: Try to match any part of the academic year
    if (!yearRange) {
        console.log('Strategy 5: Partial matching');
        const academicYearLower = academicYear.toLowerCase();
        for (let i = 1; i < schoolYearSelect.options.length; i++) {
            const optionValue = schoolYearSelect.options[i].value;
            const optionLower = optionValue.toLowerCase();
            if (academicYearLower.includes(optionLower) || optionLower.includes(academicYearLower)) {
                yearRange = optionValue;
                console.log('Match found via partial matching:', yearRange);
                break;
            }
        }
    }
    
    if (yearRange) {
        schoolYearSelect.value = yearRange;
        console.log('✅ Set academic year to:', yearRange);
    } else {
        console.log('❌ No match found for academic year:', academicYear);
        console.log('Available options:', Array.from(schoolYearSelect.options).map(opt => opt.value));
        console.log('Trying to set first available option as fallback...');
        if (schoolYearSelect.options.length > 1) {
            schoolYearSelect.value = schoolYearSelect.options[1].value;
            console.log('Fallback: Set to first available option:', schoolYearSelect.options[1].value);
        }
    }
    
    // Map year level values
    const yearLevel = courseData.year_level || '';
    console.log('=== YEAR LEVEL DEBUG ===');
    console.log('Raw year level value:', `"${yearLevel}"`);
    console.log('Year level length:', yearLevel.length);
    console.log('Year level type:', typeof yearLevel);
    
    const yearLevelSelect = document.getElementById('edit_year_level');
    console.log('Available year level options:');
    for (let i = 0; i < yearLevelSelect.options.length; i++) {
        console.log(`  Option ${i}: value="${yearLevelSelect.options[i].value}", text="${yearLevelSelect.options[i].textContent}"`);
    }
    
    // Try multiple matching strategies
    let selectedYearLevel = '';
    
    // Strategy 1: Direct contains check
    if (yearLevel.includes('1st Year') || yearLevel.includes('1st')) {
        selectedYearLevel = '1st Year';
        console.log('Match found via contains 1st');
    } else if (yearLevel.includes('2nd Year') || yearLevel.includes('2nd')) {
        selectedYearLevel = '2nd Year';
        console.log('Match found via contains 2nd');
    } else if (yearLevel.includes('3rd Year') || yearLevel.includes('3rd')) {
        selectedYearLevel = '3rd Year';
        console.log('Match found via contains 3rd');
    } else if (yearLevel.includes('4th Year') || yearLevel.includes('4th')) {
        selectedYearLevel = '4th Year';
        console.log('Match found via contains 4th');
    }
    
    // Strategy 2: Check if any option value is contained in the year level
    if (!selectedYearLevel) {
        for (let i = 1; i < yearLevelSelect.options.length; i++) { // Skip first empty option
            const optionValue = yearLevelSelect.options[i].value;
            if (yearLevel.includes(optionValue) || optionValue.includes(yearLevel)) {
                selectedYearLevel = optionValue;
                console.log('Match found via option contains:', selectedYearLevel);
                break;
            }
        }
    }
    
    // Strategy 3: Try to extract year pattern
    if (!selectedYearLevel) {
        const yearMatch = yearLevel.match(/(\d+(?:st|nd|rd|th)?\s*Year)/i);
        if (yearMatch) {
            const extracted = yearMatch[1];
            console.log('Extracted year level:', extracted);
            // Try to match with available options
            for (let i = 1; i < yearLevelSelect.options.length; i++) {
                if (yearLevelSelect.options[i].value.toLowerCase().includes(extracted.toLowerCase())) {
                    selectedYearLevel = yearLevelSelect.options[i].value;
                    console.log('Match found via extracted pattern:', selectedYearLevel);
                    break;
                }
            }
        }
    }
    
    if (selectedYearLevel) {
        yearLevelSelect.value = selectedYearLevel;
        console.log('✅ Set year level to:', selectedYearLevel);
    } else {
        console.log('❌ No match found for year level:', yearLevel);
        console.log('Available options:', Array.from(yearLevelSelect.options).map(opt => opt.value));
    }
    
    // Handle programs
    if (courseData.programs && courseData.programs.length > 0) {
        const programCodes = courseData.programs.map(p => p.program_code).join(', ');
        document.getElementById('editProgramButtonText').textContent = `Selected: ${programCodes}`;
        document.getElementById('editSelectedProgramsInput').value = JSON.stringify(courseData.programs);
    } else {
        document.getElementById('editProgramButtonText').textContent = 'Select Program(s) - No Program Selected';
        document.getElementById('editSelectedProgramsInput').value = '';
    }
    
    // Attach program selection button event listener
    const programBtn = document.getElementById('editOpenProgramSelectModalBtn');
    if (programBtn) {
        // Remove existing listeners by cloning
        const newProgramBtn = programBtn.cloneNode(true);
        programBtn.parentNode.replaceChild(newProgramBtn, programBtn);
        // Add fresh listener
        newProgramBtn.addEventListener('click', window.openEditProgramSelectModal);
        console.log('✅ Program selection button event listener attached');
    }
    
    // Show modal
    document.getElementById('editCourseModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Debug the course data
    console.log('🔍 Course data academic_year:', courseData.academic_year);
    console.log('🔍 Course data keys:', Object.keys(courseData));
    console.log('🔍 Full course data:', courseData);
    
    // Test if academic_year is actually a string
    console.log('🔍 Academic year type:', typeof courseData.academic_year);
    console.log('🔍 Academic year length:', courseData.academic_year ? courseData.academic_year.length : 'null/undefined');
    console.log('🔍 Academic year char codes:', courseData.academic_year ? courseData.academic_year.split('').map(c => c.charCodeAt(0)) : 'null/undefined');
    
    // Populate school years from database and then set the correct value
    await populateEditSchoolYearsAndSetValue(courseData.academic_year);
    
    // FORCE button to be disabled initially - no matter what
    setTimeout(() => {
        const updateBtn = document.getElementById('updateCourseBtn');
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.style.backgroundColor = '#ccc';
            updateBtn.style.cursor = 'not-allowed';
            updateBtn.style.opacity = '0.6';
            updateBtn.title = 'No changes made';
            console.log('🔧 FORCED button to be DISABLED');
        }
    }, 300);
    
    // Ensure Update button starts disabled (like New Course modal)
    const updateBtn = document.getElementById('updateCourseBtn');
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.style.backgroundColor = '#ccc';
        updateBtn.style.cursor = 'not-allowed';
        updateBtn.style.opacity = '0.6';
        updateBtn.title = 'No changes made';
        console.log('🔧 Initial button state: DISABLED');
    }
}

window.closeEditCourseModal = function() {
    document.getElementById('editCourseModal').style.display = 'none';
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.height = '';
}

// Function to populate school years from database and set the correct value
window.populateEditSchoolYearsAndSetValue = async function(academicYear) {
    const schoolYearSelect = document.getElementById('edit_school_year');
    
    // Clear existing options except the first one
    while (schoolYearSelect.children.length > 1) {
        schoolYearSelect.removeChild(schoolYearSelect.lastChild);
    }
    
    try {
        console.log('🔍 Fetching school years from database...');
        // Fetch school years from the database
        const response = await fetch('api/get_school_years.php');
        const data = await response.json();
        
        console.log('🔍 API response:', data);
        
        if (data.success && data.school_years) {
            console.log('🔍 Adding school years to dropdown...');
            // Add school years from database
            data.school_years.forEach(year => {
                const option = document.createElement('option');
                option.value = year.school_year;
                option.textContent = year.display_text;
                schoolYearSelect.appendChild(option);
                console.log(`🔍 Added option: value="${year.school_year}", text="${year.display_text}"`);
            });
            console.log('✅ School years loaded from database:', data.school_years);
            
            // Now set the correct value based on the course's academic year
            console.log('🔍 About to set school year value for:', academicYear);
            setSchoolYearValue(academicYear);
            
        } else {
            // Fallback to hardcoded values if API fails
            console.warn('Failed to load school years from database, using fallback values');
            const fallbackYears = [
                { school_year: '2024-2025', display_text: '2024-2025' },
                { school_year: '2025-2026', display_text: '2025-2026' },
                { school_year: '2026-2027', display_text: '2026-2027' }
            ];
            
            fallbackYears.forEach(year => {
                const option = document.createElement('option');
                option.value = year.school_year;
                option.textContent = year.display_text;
                schoolYearSelect.appendChild(option);
            });
            
            // Set the value with fallback options
            setSchoolYearValue(academicYear);
        }
    } catch (error) {
        console.error('Error loading school years:', error);
        // Fallback to hardcoded values
        const fallbackYears = [
            { school_year: '2024-2025', display_text: '2024-2025' },
            { school_year: '2025-2026', display_text: '2025-2026' },
            { school_year: '2026-2027', display_text: '2026-2027' }
        ];
        
        fallbackYears.forEach(year => {
            const option = document.createElement('option');
            option.value = year.school_year;
            option.textContent = year.display_text;
            schoolYearSelect.appendChild(option);
        });
        
        // Set the value with fallback options
        setSchoolYearValue(academicYear);
    }
}

// Function to set the school year value based on the course's academic year
window.setSchoolYearValue = function(academicYear) {
    const schoolYearSelect = document.getElementById('edit_school_year');
    console.log('🔍 Setting school year value for:', academicYear);
    console.log('🔍 Available options:', Array.from(schoolYearSelect.options).map(opt => ({value: opt.value, text: opt.textContent})));
    
    // Simple direct matching - try exact match first
    let matched = false;
    
    // Strategy 1: Exact match
    for (let i = 0; i < schoolYearSelect.options.length; i++) {
        const option = schoolYearSelect.options[i];
        if (option.value === academicYear) {
            schoolYearSelect.value = option.value;
            console.log('✅ Exact match found:', option.value);
            matched = true;
            break;
        }
    }
    
    // Strategy 2: Contains match (if exact fails)
    if (!matched) {
        for (let i = 0; i < schoolYearSelect.options.length; i++) {
            const option = schoolYearSelect.options[i];
            if (academicYear.includes(option.value) || option.value.includes(academicYear)) {
                schoolYearSelect.value = option.value;
                console.log('✅ Contains match found:', option.value);
                matched = true;
                break;
            }
        }
    }
    
    // Strategy 3: Extract year numbers and match
    if (!matched) {
        const yearNumbers = academicYear.match(/\d{4}/g);
        if (yearNumbers) {
            console.log('🔍 Found year numbers:', yearNumbers);
            for (let i = 0; i < schoolYearSelect.options.length; i++) {
                const option = schoolYearSelect.options[i];
                for (let j = 0; j < yearNumbers.length; j++) {
                    if (option.value.includes(yearNumbers[j])) {
                        schoolYearSelect.value = option.value;
                        console.log('✅ Year number match found:', option.value, 'using:', yearNumbers[j]);
                        matched = true;
                        break;
                    }
                }
                if (matched) break;
            }
        }
    }
    
    if (!matched) {
        console.log('❌ No match found, using first available option');
        if (schoolYearSelect.options.length > 1) {
            schoolYearSelect.selectedIndex = 1;
            console.log('✅ Fallback selected:', schoolYearSelect.value);
        }
    }
    
    console.log('✅ Final selected school year:', schoolYearSelect.value);
    
    // Force update button state after setting school year
    setTimeout(() => {
        updateButtonState();
    }, 100);
}

// Function to check if there are any changes
function hasChanges() {
    if (!window.originalCourseData) {
        console.log('🔍 No original data - no changes');
        return false;
    }
    
    // Add a simple flag to track if we've detected any changes
    let hasAnyChanges = false;
    
    // Simple field-by-field comparison
    const fields = [
        { name: 'course_code', current: document.getElementById('edit_course_code').value, original: window.originalCourseData.course_code },
        { name: 'course_title', current: document.getElementById('edit_course_name').value, original: window.originalCourseData.course_title },
        { name: 'units', current: document.getElementById('edit_units').value, original: window.originalCourseData.units },
        { name: 'term', current: getCurrentTermDisplay(), original: window.originalCourseData.term },
        { name: 'academic_year', current: getCurrentAcademicYearDisplay(), original: window.originalCourseData.academic_year },
        { name: 'year_level', current: document.getElementById('edit_year_level').value, original: window.originalCourseData.year_level }
    ];
    
    console.log('🔍 Checking for changes:');
    
    for (let field of fields) {
        const isDifferent = field.current !== field.original;
        console.log(`🔍 ${field.name}: current="${field.current}" vs original="${field.original}" = ${isDifferent ? 'DIFFERENT' : 'SAME'}`);
        
        if (isDifferent) {
            console.log(`✅ Change detected in ${field.name}`);
            hasAnyChanges = true;
        }
    }
    
    // Check for program selection changes
    const currentProgramsInput = document.getElementById('editSelectedProgramsInput');
    console.log('🔍 Program change check:');
    console.log('🔍 currentProgramsInput exists:', !!currentProgramsInput);
    console.log('🔍 originalCourseData.programs exists:', !!window.originalCourseData.programs);
    
    if (currentProgramsInput && window.originalCourseData.programs) {
        const currentPrograms = currentProgramsInput.value;
        const originalPrograms = JSON.stringify(window.originalCourseData.programs);
        
        console.log(`🔍 programs: current="${currentPrograms}" vs original="${originalPrograms}"`);
        console.log(`🔍 programs match: ${currentPrograms === originalPrograms}`);
        
        // Compare the program selections
        if (currentPrograms !== originalPrograms) {
            console.log(`✅ Change detected in programs`);
            hasAnyChanges = true;
        } else {
            console.log(`🔍 No program changes detected`);
        }
    } else {
        console.log('🔍 Skipping program check - missing data');
    }
    
    if (hasAnyChanges) {
        console.log('✅ Overall: CHANGES DETECTED');
        return true;
    } else {
        console.log('✅ Overall: NO CHANGES - all fields match original');
        return false;
    }
}

// Function to get current term display format
function getCurrentTermDisplay() {
    const termValue = document.getElementById('edit_school_term').value;
    // Since we now store full names directly, just return the value
    return termValue;
}

// Function to get current academic year display format
function getCurrentAcademicYearDisplay() {
    const yearValue = document.getElementById('edit_school_year').value;
    // If the value already contains "A.Y.", return it as is
    if (yearValue && yearValue.includes('A.Y.')) {
        return yearValue;
    }
    // Otherwise, add the A.Y. prefix
    return yearValue ? `A.Y. ${yearValue}` : '';
}

// Function to check form validity (like New Course modal)
function checkEditFormValidity() {
    const form = document.getElementById('editCourseForm');
    const updateBtn = document.getElementById('updateCourseBtn');
    if (!form || !updateBtn) return;
    
    const requiredFields = [
        'course_code',
        'course_title',
        'units',
        'year_level',
        'school_term',
        'school_year'
    ];
    
    // Check if all required fields are filled
    let allFilled = true;
    let missingFields = [];
    
    for (const name of requiredFields) {
        const el = form.querySelector(`[name="${name}"]`);
        if (!el || el.value.trim() === '') {
            allFilled = false;
            missingFields.push(name);
        }
    }
    
    // Check if programs are selected
    const selectedPrograms = document.getElementById('editSelectedProgramsInput').value;
    const hasPrograms = selectedPrograms && selectedPrograms.trim().length > 0;
    
    console.log('🔍 Program validation:');
    console.log('🔍 selectedPrograms value:', selectedPrograms);
    console.log('🔍 hasPrograms:', hasPrograms);
    
    if (!hasPrograms) {
        missingFields.push('programs');
    }
    
    // Check if there are changes
    const hasChangesMade = hasChanges();
    console.log('🔍 Form validity check:');
    console.log(`🔍 All fields filled: ${allFilled}`);
    console.log(`🔍 Has programs: ${hasPrograms}`);
    console.log(`🔍 Has changes: ${hasChangesMade}`);
    
    // STRICT: Button should ONLY be enabled if ALL conditions are met AND there are actual changes
    const isValid = allFilled && hasPrograms && hasChangesMade;
    console.log(`🔍 Final isValid: ${isValid}`);
    
    // FORCE disable if no changes
    if (!hasChangesMade) {
        updateBtn.disabled = true;
        updateBtn.style.backgroundColor = '#ccc';
        updateBtn.style.cursor = 'not-allowed';
        updateBtn.style.opacity = '0.6';
        updateBtn.title = 'No changes made';
        console.log('🔧 FORCED DISABLE - No changes detected');
        return;
    }
    
    // Additional check: if all fields match original, force disable
    const fields = [
        { current: document.getElementById('edit_course_code').value, original: window.originalCourseData.course_code },
        { current: document.getElementById('edit_course_name').value, original: window.originalCourseData.course_title },
        { current: document.getElementById('edit_units').value, original: window.originalCourseData.units },
        { current: getCurrentTermDisplay(), original: window.originalCourseData.term },
        { current: getCurrentAcademicYearDisplay(), original: window.originalCourseData.academic_year },
        { current: document.getElementById('edit_year_level').value, original: window.originalCourseData.year_level }
    ];
    
    const allFieldsMatch = fields.every(field => field.current === field.original);
    
    // Also check if programs have changed
    let programsChanged = false;
    const currentProgramsInput = document.getElementById('editSelectedProgramsInput');
    if (currentProgramsInput && window.originalCourseData.programs) {
        const currentPrograms = currentProgramsInput.value;
        const originalPrograms = JSON.stringify(window.originalCourseData.programs);
        programsChanged = (currentPrograms !== originalPrograms);
    }
    
    if (allFieldsMatch && !programsChanged) {
        updateBtn.disabled = true;
        updateBtn.style.backgroundColor = '#ccc';
        updateBtn.style.cursor = 'not-allowed';
        updateBtn.style.opacity = '0.6';
        updateBtn.title = 'No changes made';
        console.log('🔧 FORCED DISABLE - All fields match original and no program changes');
        return;
    }
    
    // Only enable if all conditions are met
    updateBtn.disabled = !isValid;
    
    // Update button styling based on validity (exactly like New Course modal)
    if (isValid) {
        updateBtn.style.backgroundColor = '#1976d2';
        updateBtn.style.cursor = 'pointer';
        updateBtn.style.opacity = '1';
        updateBtn.title = 'Update course with changes';
        console.log('✅ Button enabled - blue color');
    } else {
        updateBtn.style.backgroundColor = '#ccc';
        updateBtn.style.cursor = 'not-allowed';
        updateBtn.style.opacity = '0.6';
        updateBtn.title = hasChangesMade ? 'Please fill in all required fields' : 'No changes made';
        console.log('❌ Button disabled - grey color');
    }
}

// Function to update button state (simplified)
function updateButtonState() {
    checkEditFormValidity();
}

// Success and Error Modal Functions - MATCHING EXISTING DESIGN
function showUpdateSuccessModal(updatedData = null) {
    const modal = document.createElement('div');
    modal.id = 'updateSuccessModal';
    modal.className = 'department-modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    modal.innerHTML = `
        <div class="department-modal-box" style="
            width: 500px; 
            text-align: center; 
            animation: fadeIn 0.3s;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
        ">
             <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success Icon" style="width: 64px; height: 64px; margin-bottom: 8px;">
            <h2 style="color: #4CAF50; margin-bottom: 10px; font-size: 20px; font-weight: 700;">Update Successful!</h2>
            <p style="font-size: 16px; margin-bottom: 20px; color: #333;">The course has been updated successfully.</p>
            ${updatedData ? `
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; border-left: 4px solid #4CAF50;">
                    <strong style="color: #4CAF50; display: block; margin-bottom: 10px;">Updated Course Details:</strong>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 14px;">
                        <div><strong>Code:</strong> ${updatedData.course_code}</div>
                        <div><strong>Units:</strong> ${updatedData.units}</div>
                        <div><strong>Title:</strong> ${updatedData.course_title}</div>
                        <div><strong>Term:</strong> ${updatedData.term}</div>
                        <div><strong>Academic Year:</strong> ${updatedData.academic_year}</div>
                        <div><strong>Year Level:</strong> ${updatedData.year_level}</div>
                        ${updatedData.programs ? `<div style="grid-column: 1 / -1;"><strong>Programs:</strong> ${Array.isArray(updatedData.programs) ? updatedData.programs.map(p => p.program_code || p.program_name).join(', ') : updatedData.programs}</div>` : ''}
                    </div>
                </div>
            ` : ''}
            <button type="button" class="create-btn" id="successModalOkBtn" onclick="closeUpdateSuccessModal()">OK (3)</button>
        </div>
    `;
    
    document.body.appendChild(modal);
    console.log('✅ Success modal displayed');
    
    // Add countdown timer to OK button
    let countdown = 3;
    const okButton = document.getElementById('successModalOkBtn');
    
    const countdownInterval = setInterval(() => {
        countdown--;
        if (countdown > 0) {
            okButton.textContent = `OK (${countdown})`;
        } else {
            okButton.textContent = 'OK';
            clearInterval(countdownInterval);
            // Auto-close after countdown reaches 0
            setTimeout(() => {
                closeUpdateSuccessModal();
            }, 1000);
        }
    }, 1000);
}

function showUpdateErrorModal(message = 'There was an error updating the course. Please try again.') {
    const modal = document.createElement('div');
    modal.id = 'updateErrorModal';
    modal.className = 'department-modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    `;
    
    modal.innerHTML = `
        <div class="department-modal-box" style="
            width: 500px; 
            text-align: center; 
            animation: fadeIn 0.3s;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
        ">
             <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error Icon" style="width: 64px; height: 64px; margin-bottom: 8px;">
            <h2 style="color: #ff6b6b; margin-bottom: 10px; font-size: 20px; font-weight: 700;">Update Failed!</h2>
            <p style="font-size: 16px; margin-bottom: 20px; color: #333;">${message}</p>
            <button type="button" class="reset-btn" onclick="closeUpdateErrorModal()">Close</button>
        </div>
    `;
    
    document.body.appendChild(modal);
    console.log('❌ Error modal displayed');
}

// Close functions for the modals
function closeUpdateSuccessModal() {
    const modal = document.getElementById('updateSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
    
    // Auto-refresh the page to show updated data
    console.log('🔄 Refreshing page to show updated course data...');
    setTimeout(() => {
        window.location.reload();
    }, 500); // Small delay to ensure modal is fully closed
}

function closeUpdateErrorModal() {
    const modal = document.getElementById('updateErrorModal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}


// Program Selection Modal Functions
window.openEditProgramSelectModal = function() {
    console.log('🔧 Opening Edit Program Selection Modal');
    const programModal = document.getElementById('editProgramSelectModal');
    if (programModal) {
        programModal.style.display = 'flex';
        // Don't change body overflow since Edit Course Modal is already open
        console.log('✅ Edit Program Selection Modal opened');
        
        // Initialize checkboxes with event listeners and attach confirm button
        setTimeout(() => {
            // First initialize checkboxes
            initializeEditProgramCheckboxes();
            
            // Then attach Confirm button event listener
            const confirmBtn = document.getElementById('confirmEditProgramSelectBtn');
            if (confirmBtn) {
                // Remove existing listener by cloning
                const newConfirmBtn = confirmBtn.cloneNode(true);
                confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
                // Attach fresh listener
                newConfirmBtn.addEventListener('click', function(e) {
                    console.log('🔧 Confirm button CLICKED via event listener');
                    window.confirmEditProgramSelection(e);
                });
                console.log('✅ Confirm button event listener attached');
                
                // Add test function
                window.testConfirmButton = function() {
                    console.log('🔧 Manual test of Confirm button');
                    const btn = document.getElementById('confirmEditProgramSelectBtn');
                    console.log('🔧 Button found:', btn);
                    console.log('🔧 Button disabled:', btn ? btn.disabled : 'N/A');
                    console.log('🔧 Button innerHTML:', btn ? btn.innerHTML : 'N/A');
                    if (btn) {
                        console.log('🔧 Manually calling confirmEditProgramSelection...');
                        window.confirmEditProgramSelection();
                    }
                };
                
                // Update button state immediately after attaching
                setTimeout(() => {
                    updateEditProgramConfirmButton();
                    console.log('🔧 Button state after init:', {
                        disabled: document.getElementById('confirmEditProgramSelectBtn').disabled,
                        backgroundColor: document.getElementById('confirmEditProgramSelectBtn').style.backgroundColor
                    });
                }, 50);
            } else {
                console.error('❌ Confirm button not found!');
            }
        }, 100);
    } else {
        console.error('❌ Edit Program Selection Modal not found!');
    }
}

window.closeEditProgramSelectModal = function() {
    console.log('🔧 Closing Edit Program Selection Modal');
    const programModal = document.getElementById('editProgramSelectModal');
    if (programModal) {
        programModal.style.display = 'none';
        // Don't restore body overflow since Edit Course Modal should remain open
        console.log('✅ Edit Program Selection Modal closed');
    }
}

function loadEditProgramList() {
    const programList = document.getElementById('editProgramList');
    programList.innerHTML = '';
    
    // TODO: Replace with API call to get_programs.php
    const programs = [];
    
    programs.forEach(program => {
        const programItem = document.createElement('div');
        programItem.className = 'program-item';
        programItem.style.cssText = `
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        `;
        
        programItem.innerHTML = `
            <input type="checkbox" value="${program.id}" data-code="${program.code}" data-name="${program.name}" data-color="${program.color}" style="margin-right: 12px;">
            <div style="flex: 1;">
                <div style="font-weight: 600; color: #333;">${program.code}</div>
                <div style="font-size: 12px; color: #666;">${program.name}</div>
            </div>
        `;
        
        programItem.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            }
        });
        
        programList.appendChild(programItem);
    });
}

window.confirmEditProgramSelection = function(e) {
    console.log('🔧 ✨ CONFIRM BUTTON CLICKED! ✨');
    console.log('🔧 Event:', e);
    
    // Prevent any default behavior
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    const checkboxes = document.querySelectorAll('#editProgramCheckboxes input[type="checkbox"]:checked');
    const selectedPrograms = [];
    
    console.log(`🔧 Found ${checkboxes.length} checked checkboxes`);
    
    checkboxes.forEach(checkbox => {
        const label = checkbox.closest('label');
        const programText = label.textContent.trim();
        const [programCode, programName] = programText.split(' - ', 2);
        
        selectedPrograms.push({
            id: checkbox.value,
            program_code: programCode,
            program_name: programName || programText
        });
        
        console.log('🔧 Selected program:', programCode, '-', programName);
    });
    
    if (selectedPrograms.length > 0) {
        const programCodes = selectedPrograms.map(p => p.program_code).join(', ');
        document.getElementById('editProgramButtonText').textContent = `Selected: ${programCodes}`;
        document.getElementById('editSelectedProgramsInput').value = JSON.stringify(selectedPrograms);
        console.log('✅ Programs selected:', selectedPrograms);
    } else {
        document.getElementById('editProgramButtonText').textContent = 'Select Program(s) - No Program Selected';
        document.getElementById('editSelectedProgramsInput').value = '';
        console.log('✅ No programs selected');
    }
    
    // Trigger change event to update button state
    const programsInput = document.getElementById('editSelectedProgramsInput');
    if (programsInput) {
        const changeEvent = new Event('change', { bubbles: true });
        programsInput.dispatchEvent(changeEvent);
    }
    
    // Update button state after program selection
    updateButtonState();
    
    closeEditProgramSelectModal();
}

window.resetEditProgramSelection = function() {
    console.log('🔧 Resetting Edit Program Selection');
    const checkboxes = document.querySelectorAll('#editProgramCheckboxes input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Update confirm button state
    updateEditProgramConfirmButton();
    
    console.log('✅ Program selection reset');
}

// Function to update the confirm button state based on checkbox selection
function updateEditProgramConfirmButton() {
    const confirmBtn = document.getElementById('confirmEditProgramSelectBtn');
    if (!confirmBtn) return;
    
    const checkedBoxes = document.querySelectorAll('#editProgramCheckboxes input[type="checkbox"]:checked');
    const currentSelection = Array.from(checkedBoxes).map(cb => cb.value.toString()).sort();
    const originalSelection = (window.originalProgramSelection || []).map(id => id.toString()).sort();
    
    console.log('🔧 Current selection:', currentSelection);
    console.log('🔧 Original selection:', originalSelection);
    
    // Check if at least one program is selected
    const hasSelection = currentSelection.length > 0;
    
    // Check if selection has changed from original
    const hasChanges = JSON.stringify(currentSelection) !== JSON.stringify(originalSelection);
    
    console.log('🔧 Has selection:', hasSelection);
    console.log('🔧 Has changes:', hasChanges);
    
    if (!hasSelection) {
        // No programs selected - disable
        confirmBtn.disabled = true;
        confirmBtn.style.backgroundColor = '#ccc';
        confirmBtn.style.cursor = 'not-allowed';
        confirmBtn.style.opacity = '0.6';
        confirmBtn.title = 'Please select at least one program';
        console.log('✅ Confirm button DISABLED - no programs selected');
    } else if (!hasChanges) {
        // Programs selected but no changes - disable
        confirmBtn.disabled = true;
        confirmBtn.style.backgroundColor = '#ccc';
        confirmBtn.style.cursor = 'not-allowed';
        confirmBtn.style.opacity = '0.6';
        confirmBtn.title = 'No changes to confirm';
        console.log('✅ Confirm button DISABLED - no changes from original');
    } else {
        // Programs selected AND changes detected - enable
        confirmBtn.disabled = false;
        confirmBtn.style.backgroundColor = '#4CAF50';
        confirmBtn.style.cursor = 'pointer';
        confirmBtn.style.opacity = '1';
        confirmBtn.title = 'Confirm program selection';
        console.log(`✅ Confirm button ENABLED - ${currentSelection.length} programs selected with changes`);
    }
}

// Function to initialize program selection checkboxes with event listeners
function initializeEditProgramCheckboxes() {
    const checkboxes = document.querySelectorAll('#editProgramCheckboxes input[type="checkbox"]');
    console.log(`🔧 Initializing ${checkboxes.length} program checkboxes with event listeners`);
    
    // First, uncheck all checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Get current programs from the hidden input
    const programsInput = document.getElementById('editSelectedProgramsInput');
    let selectedProgramIds = [];
    
    if (programsInput && programsInput.value) {
        try {
            const programs = JSON.parse(programsInput.value);
            selectedProgramIds = programs.map(p => p.id.toString());
            console.log('🔧 Current programs to pre-select:', programs);
            console.log('🔧 Program IDs:', selectedProgramIds);
        } catch (e) {
            console.error('❌ Failed to parse programs JSON:', e);
        }
    } else {
        console.log('🔧 No programs data found in hidden input');
    }
    
    // Pre-select checkboxes based on current programs
    checkboxes.forEach(checkbox => {
        const checkboxValue = checkbox.value.toString();
        const shouldBeChecked = selectedProgramIds.includes(checkboxValue);
        
        if (shouldBeChecked) {
            checkbox.checked = true;
            console.log(`✅ Pre-selected program ID: ${checkboxValue}`);
        }
        
        // Remove existing listeners by cloning
        const newCheckbox = checkbox.cloneNode(true);
        newCheckbox.checked = checkbox.checked; // Preserve checked state
        checkbox.parentNode.replaceChild(newCheckbox, checkbox);
        
        // Add new listener
        newCheckbox.addEventListener('change', function(e) {
            // Stop event propagation to prevent interference
            e.stopPropagation();
            console.log(`🔧 Checkbox changed: ${newCheckbox.value}, checked: ${newCheckbox.checked}`);
            updateEditProgramConfirmButton();
        });
    });
    
    // Store original selection for comparison
    window.originalProgramSelection = [...selectedProgramIds];
    console.log('🔧 Stored original program selection:', window.originalProgramSelection);
    
    // Initial button state
    updateEditProgramConfirmButton();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Edit course form submission (like New Course modal)
    document.getElementById('editCourseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const updateBtn = document.getElementById('updateCourseBtn');
        if (updateBtn.disabled) return; // Don't submit if form is invalid (like New Course modal)
        
        const formData = new FormData(this);
        const courseData = Object.fromEntries(formData);
        
        console.log('Updating course:', courseData);
        
        // Show loading state
        updateBtn.disabled = true;
        updateBtn.textContent = 'UPDATING...';
        updateBtn.style.backgroundColor = '#ffa500';
        
        // Make API call to update course
        fetch('api/update_course.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('API Response Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response Data:', data);
            
            if (data.success) {
                // Close the edit modal immediately
                closeEditCourseModal();
                
                // Show success modal
                showUpdateSuccessModal(data.data);
                
                // Update the original values to reflect the changes
                window.originalCourseData = {
                    course_code: courseData.course_code,
                    course_title: courseData.course_title,
                    units: courseData.units,
                    term: courseData.term,
                    academic_year: courseData.academic_year,
                    year_level: courseData.year_level,
                    programs: JSON.parse(courseData.programs || '[]')
                };
                
                // Disable the button since no changes remain
                setTimeout(() => {
                    updateButtonState();
                }, 100);
                
            } else {
                showUpdateErrorModal(data.message || 'Update failed. Please check your data and try again.');
            }
        })
        .catch(error => {
            console.error('API Error:', error);
            let errorMessage = 'Network error occurred';
            
            if (error.message.includes('HTTP error')) {
                errorMessage = 'Server error occurred. Please try again.';
            } else if (error.message.includes('Failed to fetch')) {
                errorMessage = 'Connection failed. Please check your internet connection.';
            }
            
            showUpdateErrorModal(errorMessage);
        })
        .finally(() => {
            // Reset button
            updateBtn.disabled = false;
            updateBtn.textContent = 'UPDATE';
            updateBtn.style.backgroundColor = '#1976d2';
            updateBtn.style.cursor = 'pointer';
            updateBtn.style.opacity = '1';
        });
    });
    
    // Note: Program selection button event listener is now attached
    // in the openEditCourseModal function to avoid conflicts
    
    // Add change listeners to all form fields
    const formFields = [
        'edit_course_code',
        'edit_course_name', 
        'edit_units',
        'edit_school_term',
        'edit_school_year',
        'edit_year_level'
    ];
    
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            console.log('Adding listeners to field:', fieldId);
            field.addEventListener('input', function() {
                console.log('Input event on:', fieldId);
                updateButtonState();
            });
            field.addEventListener('change', function() {
                console.log('Change event on:', fieldId);
                updateButtonState();
            });
        } else {
            console.warn('Field not found:', fieldId);
        }
    });
    
    // Test function to manually trigger button state
    window.testButtonState = function() {
        console.log('Manual button state test');
        updateButtonState();
    };
    
    // Test function to force disable button
    window.forceDisableButton = function() {
        const updateBtn = document.getElementById('updateCourseBtn');
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.setAttribute('disabled', 'disabled');
            updateBtn.style.backgroundColor = '#ccc';
            updateBtn.style.cursor = 'not-allowed';
            updateBtn.style.opacity = '0.6';
            console.log('Button force disabled');
        }
    };
    
    // Test function to force enable button
    window.forceEnableButton = function() {
        const updateBtn = document.getElementById('updateCourseBtn');
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.removeAttribute('disabled');
            updateBtn.style.backgroundColor = '#007bff';
            updateBtn.style.cursor = 'pointer';
            updateBtn.style.opacity = '1';
            console.log('Button force enabled');
        }
    };
});
</script>

<?php
// End of edit_course_modal.php
?>
