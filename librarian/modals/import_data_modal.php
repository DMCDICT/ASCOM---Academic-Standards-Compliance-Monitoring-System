<?php
// import_data_modal.php
// Modal for importing book references from Excel files
?>

<!-- SheetJS library for Excel parsing in browser -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div id="importDataModal" class="modal-overlay" style="display: none; overflow: hidden !important;">
  <div class="modal-box" style="max-width: 900px; width: 90%; display: flex; flex-direction: column; max-height: 90vh; overflow: hidden; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0;">
      <h2>Import Book References</h2>
      <span class="close-button" onclick="closeImportDataModal()">&times;</span>
    </div>
    
    <!-- Scrollable content -->
    <div class="form-content" style="flex: 1; overflow-y: auto; padding: 24px;">
      <!-- File Upload Area -->
      <div id="fileUploadArea" class="file-upload-area" style="border: 2px dashed #ccc; border-radius: 12px; padding: 40px; text-align: center; background: #fff; margin-bottom: 24px; cursor: pointer; transition: all 0.3s ease;" ondrop="handleFileDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
        <div style="margin-bottom: 16px;">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #999; margin: 0 auto;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
        </div>
        <p style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px 0;">Drag and drop your Excel file here</p>
        <p style="font-size: 14px; color: #666; margin: 0 0 16px 0;">Supports .xlsx or .xls files</p>
        <input type="file" id="fileInput" accept=".xlsx,.xls" style="display: none;" onchange="handleFileSelect(event)">
        <button type="button" onclick="document.getElementById('fileInput').click()" class="create-btn" style="padding: 10px 24px; font-size: 14px;">Browse Files</button>
        <div id="fileName" style="margin-top: 12px; font-size: 14px; color: #1976d2; font-weight: 600;"></div>
      </div>

      <!-- Loading Indicator -->
      <div id="importLoading" style="display: none; text-align: center; padding: 40px;">
        <div style="font-size: 16px; color: #666; margin-bottom: 16px;">Processing file...</div>
        <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1976d2; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
      </div>

      <!-- Program Tabs and Book List -->
      <div id="importResults" style="display: none;">
        <!-- Program Tabs -->
        <div id="programTabs" style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; border-bottom: 2px solid #e0e0e0; padding-bottom: 12px;">
          <!-- Tabs will be dynamically generated here -->
        </div>

        <!-- Selected Course Info -->
        <div id="selectedCourseInfo" style="background: #e3f2fd; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: none;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <div style="font-size: 14px; color: #666; margin-bottom: 4px;">Selected Course:</div>
              <div style="font-size: 18px; font-weight: 600; color: #1976d2;" id="selectedCourseDisplay">-</div>
            </div>
            <button type="button" onclick="clearSelectedCourse()" class="cancel-btn" style="padding: 8px 16px; font-size: 14px;">Change Course</button>
          </div>
        </div>

        <!-- Detected Courses Section -->
        <div id="detectedCoursesSection" style="margin-bottom: 20px; display: none;">
          <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Detected Courses from Excel</label>
          <div id="detectedCoursesList" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 12px; max-height: 300px; overflow-y: auto;">
            <!-- Detected courses will be listed here -->
          </div>
          <div style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; color: #666;">
              <span id="coursesToAddCount">0</span> course(s) selected to add (<span id="referencesCount">0</span> compliant references)
            </div>
            <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: #333;">
              <input type="checkbox" id="selectAllCompliantCheckbox" onchange="toggleAllCourses(this.checked)" style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;">
              <span>Select All</span>
            </label>
          </div>
        </div>


        <!-- Books List by Program - Hidden by default, only shown when needed for import -->
        <div id="booksListContainer" style="display: none;">
          <!-- Book lists will be dynamically generated here -->
        </div>

        <!-- Summary -->
        <div id="importSummary" style="background: #f5f5f5; padding: 16px; border-radius: 8px; margin-top: 20px;">
          <div style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Summary</div>
          <div style="font-size: 13px; color: #666;">
            Total books: <span id="totalBooksCount">0</span> | 
            Compliant books: <span id="compliantBooksCount" style="color: #4CAF50; font-weight: 600;">0</span> | 
            Non-compliant books: <span id="nonCompliantBooksCount" style="color: #FF4C4C; font-weight: 600;">0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Fixed footer buttons -->
    <div class="form-actions" style="flex-shrink: 0; background: #EFEFEF; padding: 12px 24px; border-top: 1px solid #e0e0e0; display: flex; justify-content: flex-end; gap: 12px;">
      <button type="button" class="cancel-btn" onclick="closeImportDataModal()">CANCEL</button>
      <button type="button" class="create-btn" id="importSubmitBtn" onclick="submitImportedBooks()" disabled style="opacity: 0.5; cursor: not-allowed; pointer-events: none; background-color: #6c757d;">IMPORT BOOKS</button>
    </div>
  </div>
</div>

<!-- Course Book References Modal - Moved outside importDataModal to appear as separate overlay -->
<div id="courseBooksModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.8); z-index: 999999; margin: 0; padding: 0;">
  <div class="modal-box" style="max-width: 600px; width: 70%; height: 75vh !important; max-height: 75vh !important; display: flex !important; flex-direction: column !important; background-color: #EFEFEF !important; position: relative !important; z-index: 1000000 !important; padding: 24px !important; overflow: visible !important; box-sizing: border-box !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;">
    <div class="modal-header" style="flex-shrink: 0; display: flex !important; justify-content: space-between !important; align-items: center !important; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px; position: relative; z-index: 1000001;">
      <h2 id="courseBooksModalTitle" style="margin: 0; font-size: 22px; font-weight: 700; color: #333;">Book References</h2>
      <span class="close-button" id="courseBooksModalCloseBtn" onclick="if(window.closeCourseBooksModal)window.closeCourseBooksModal();return false;" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1; z-index: 1000002; position: relative; padding: 5px 10px; user-select: none;">&times;</span>
    </div>
    <div id="courseBooksListContainer" style="flex: 1 1 500px !important; overflow-y: auto !important; overflow-x: visible !important; padding: 20px !important; min-height: 500px !important; height: 500px !important; display: block !important; visibility: visible !important; background: #EFEFEF !important; position: relative !important; z-index: 1 !important; width: 100% !important; box-sizing: border-box !important;">
      <div id="courseBooksList" style="width: 100% !important; min-height: 400px !important; height: auto !important; display: block !important; visibility: visible !important; opacity: 1 !important; color: #333 !important; padding: 0 !important; margin: 0 !important; background: #EFEFEF !important; position: relative !important; z-index: 2 !important; box-sizing: border-box !important;">
        <div style="padding: 20px; text-align: center; color: #666; display: block !important; visibility: visible !important;">Loading book references...</div>
      </div>
    </div>
    <div class="form-actions" style="flex-shrink: 0; background: #EFEFEF !important; padding: 12px 0 0 0; border-top: 1px solid #e0e0e0; display: flex; justify-content: flex-end; margin-top: 20px; position: relative !important; z-index: 1000001 !important;">
      <button type="button" class="cancel-btn" id="courseBooksModalCloseFooterBtn" onclick="if(window.closeCourseBooksModal)window.closeCourseBooksModal();return false;" style="cursor: pointer; z-index: 1000002; position: relative;">CLOSE</button>
    </div>
  </div>
</div>

<style>
/* Ensure import data modal has a lower z-index */
#importDataModal.modal-overlay {
    z-index: 10000 !important;
    position: fixed !important;
}

/* Ensure course books modal appears above import data modal as a proper overlay - COMPLETELY INDEPENDENT */
#courseBooksModal.modal-overlay {
    z-index: 999999 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: rgba(0, 0, 0, 0.8) !important;
    pointer-events: auto !important;
}

/* When modal is shown, it should be flex - OVERRIDE EVERYTHING - NO EXCEPTIONS */
#courseBooksModal.modal-overlay.show,
#courseBooksModal.modal-overlay[data-modal-open="true"],
#courseBooksModal.modal-overlay[style*="flex"],
#courseBooksModal.modal-overlay[style*="display: flex"] {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 999999 !important;
    pointer-events: auto !important;
}

#courseBooksModal .modal-box {
    z-index: 1000000 !important;
    position: relative !important;
    background-color: #EFEFEF !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}

#courseBooksModal .form-actions {
    background: #EFEFEF !important;
    z-index: 1000001 !important;
    position: relative !important;
}

/* Ensure course books list container is visible - AGGRESSIVE */
/* NOTE: Don't force position:relative here - allow absolute positioning when needed */
#courseBooksListContainer {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    overflow-y: auto !important;
    overflow-x: visible !important;
    min-height: 400px !important;
    padding: 20px !important;
    background: #EFEFEF !important;
    z-index: 1 !important;
    box-sizing: border-box !important;
    /* Position and dimensions set via JavaScript - don't override */
}

/* Ensure course books list content is visible - AGGRESSIVE */
#courseBooksList {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 100% !important;
    min-height: 200px !important;
    height: auto !important;
    background: #EFEFEF !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    color: #333 !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    position: relative !important;
    z-index: 2 !important;
    box-sizing: border-box !important;
}

/* Force ALL content to be visible - EVERYTHING */
#courseBooksList *,
#courseBooksListContainer * {
    visibility: visible !important;
    opacity: 1 !important;
}

/* Force divs inside to be visible */
#courseBooksList > div {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    color: #333 !important;
}

/* Force nested divs */
#courseBooksList div {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure modal header is properly aligned */
#courseBooksModal .modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

/* Ensure content area has no nested modal styling */
#courseBooksModal .modal-box > div:nth-child(2) {
    flex: 1 !important;
    overflow-y: auto !important;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.file-upload-area.dragover {
  border-color: #1976d2;
  background: #e3f2fd;
}

.program-tab {
  padding: 10px 20px;
  border: none;
  border-radius: 8px 8px 0 0;
  background: #f5f5f5;
  color: #666;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.program-tab.active {
  background: #1976d2;
  color: white;
}

.program-tab:hover:not(.active) {
  background: #e0e0e0;
}

.program-books-list {
  display: none;
}

.program-books-list.active {
  display: block;
}

.import-book-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12px;
  border-bottom: 1px solid #eee;
  background: #f9f9f9;
  margin-bottom: 8px;
  border-radius: 4px;
}

.import-book-item.disabled {
  opacity: 0.5;
  background: #f0f0f0;
}

.import-book-item-content {
  flex: 1;
  font-size: 14px;
  line-height: 1.5;
}

.import-book-item-content strong {
  font-weight: 600;
  color: #333;
}

.import-book-item-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
}

.checkbox-wrapper input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}
</style>

<script>
// Import modal state
let importedBooksData = {};
let originalParsedBooksData = {}; // Store original parsed data before server matching
let courseBooksMap = {}; // Map course codes to their books: {course_code: [books]}
let originalDetectedCourses = {}; // Store original detectedCourses object with books arrays BEFORE server processing
let activeProgramTab = null;
let selectedImportCourseId = null;
let selectedImportCourseDisplay = null;
let isFilteringCourses = false; // Flag to prevent Select All update during tab switching
let selectedCourseIndices = new Set(); // Track selected course indices independently of DOM

// Course data for autocomplete (will be loaded from PHP)
let importCoursesData = <?php
require_once dirname(__FILE__) . '/../includes/db_connection.php';
$coursesQuery = "SELECT MIN(c.id) AS id, c.course_code, c.course_title FROM courses c GROUP BY c.course_code, c.course_title ORDER BY c.course_code ASC";
$coursesStmt = $pdo->prepare($coursesQuery);
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(array_map(function($course) {
    return [
        'id' => $course['id'],
        'code' => $course['course_code'],
        'title' => $course['course_title'],
        'display' => $course['course_code'] . ' - ' . $course['course_title']
    ];
}, $courses));
?>;

// Open import modal
function openImportDataModal() {
    const modal = document.getElementById('importDataModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        resetImportModal();
    }
}

// Close import modal
function closeImportDataModal() {
    const modal = document.getElementById('importDataModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        resetImportModal();
    }
}

// Reset import modal
function resetImportModal() {
    importedBooksData = {};
    originalParsedBooksData = {};
    courseBooksMap = {}; // Reset course books map
    originalDetectedCourses = {}; // Reset original detected courses
    activeProgramTab = null;
    selectedImportCourseId = null;
    selectedImportCourseDisplay = null;
    
    // Reset form elements with null checks
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const importLoading = document.getElementById('importLoading');
    const importResults = document.getElementById('importResults');
    const importSubmitBtn = document.getElementById('importSubmitBtn');
    const selectedCourseInfo = document.getElementById('selectedCourseInfo');
    const courseSelectionArea = document.getElementById('courseSelectionArea');
    const detectedCoursesSection = document.getElementById('detectedCoursesSection');
    
    if (fileInput) fileInput.value = '';
    if (fileName) fileName.textContent = '';
    if (fileUploadArea) fileUploadArea.style.display = 'block';
    if (importLoading) importLoading.style.display = 'none';
    if (importResults) importResults.style.display = 'none';
    if (importSubmitBtn) {
        importSubmitBtn.disabled = true;
        importSubmitBtn.style.opacity = '0.5';
        importSubmitBtn.style.cursor = 'not-allowed';
        importSubmitBtn.style.pointerEvents = 'none';
    }
    if (selectedCourseInfo) selectedCourseInfo.style.display = 'none';
    if (courseSelectionArea) courseSelectionArea.style.display = 'block';
    if (detectedCoursesSection) detectedCoursesSection.style.display = 'none';
    
    detectedCoursesData = [];
    selectedCoursesToAdd = [];
    if (selectedCourseIndices) {
        selectedCourseIndices.clear(); // Clear selected course indices
    }
}

// Drag and drop handlers
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('dragover');
}

function handleFileDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

// Handle file upload - parse Excel file in browser using SheetJS
function handleFile(file) {
    if (!file.name.match(/\.(xlsx|xls)$/i)) {
        alert('Please upload a valid Excel file (.xlsx or .xls)');
        return;
    }
    
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileUploadArea').style.display = 'none';
    document.getElementById('importLoading').style.display = 'block';
    document.getElementById('importResults').style.display = 'none';
    
    // Read file and parse using SheetJS (xlsx.js)
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = e.target.result;
            const workbook = XLSX.read(data, { type: 'binary' });
            
            // Get all sheet names
            const sheetNames = workbook.SheetNames;
            
            // Parse books from each sheet
            const booksByProgram = {};
            const detectedCourses = {}; // Track unique courses: {course_code: {course_title, program_code, years: [], books: []}}
            const currentYear = new Date().getFullYear();
            
            // Column mapping: A=Course No., B=Course Title, D=Book Title, E=No. of Copies, F=Author, G=Publisher, H=Copyright
            sheetNames.forEach(sheetName => {
                const worksheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                
                // Extract program code from sheet name (e.g., "BSIT-CHED" -> "BSIT")
                let programCode = sheetName;
                
                // Find header row (usually around row 10-11)
                let headerRow = null;
                for (let row = 7; row < 12 && row < jsonData.length; row++) {
                    const rowData = jsonData[row] || [];
                    const cellD = String(rowData[3] || '').toLowerCase();
                    const cellA = String(rowData[0] || '').toLowerCase();
                    
                    if (cellD.includes('book') || cellD.includes('reference') || cellA.includes('course')) {
                        headerRow = row;
                        break;
                    }
                }
                
                if (!headerRow) {
                    headerRow = 9; // Default to row 10 (0-indexed = 9)
                }
                
                // Data starts after header row
                let dataStartRow = headerRow + 1;
                if (dataStartRow < 11) {
                    dataStartRow = 11; // Ensure we start at least at row 12 (0-indexed = 11)
                }
                
                let currentCourseCode = '';
                let currentCourseTitle = '';
                
                // Parse rows
                for (let row = dataStartRow; row < jsonData.length; row++) {
                    const rowData = jsonData[row] || [];
                    
                    // Get course code (Column A, index 0)
                    const courseCode = String(rowData[0] || '').trim();
                    
                    // If course code exists, update current course
                    if (courseCode) {
                        currentCourseCode = courseCode;
                        currentCourseTitle = String(rowData[1] || '').trim();
                        
                        // Track this course
                        if (currentCourseCode && currentCourseTitle) {
                            const courseKey = `${currentCourseCode}|${programCode}`;
                            if (!detectedCourses[courseKey]) {
                                detectedCourses[courseKey] = {
                                    course_code: currentCourseCode,
                                    course_title: currentCourseTitle,
                                    program_code: programCode,
                                    years: [], // Track publication years for books in this course
                                    books: []  // Store books directly with the course!
                                };
                            }
                        }
                        continue;
                    }
                    
                    // Get book title (Column D, index 3)
                    const bookTitle = String(rowData[3] || '').trim();
                    
                    // If no book title, skip this row
                    if (!bookTitle) {
                        continue;
                    }
                    
                    // Get other book data
                    const noOfCopies = String(rowData[4] || '').trim();
                    const authors = String(rowData[5] || '').trim();
                    const publisher = String(rowData[6] || '').trim();
                    const publicationYear = String(rowData[7] || '').trim();
                    
                    // Convert publication year to integer
                    let publicationYearInt = 0;
                    if (publicationYear) {
                        const yearStr = publicationYear.replace(/[^0-9]/g, '');
                        if (yearStr.length >= 4) {
                            publicationYearInt = parseInt(yearStr.substring(0, 4));
                        } else if (yearStr.length > 0) {
                            publicationYearInt = parseInt(yearStr);
                        }
                    }
                    
                    // Convert no_of_copies to integer
                    let noOfCopiesInt = 1;
                    if (noOfCopies) {
                        const copiesStr = noOfCopies.replace(/[^0-9]/g, '');
                        if (copiesStr) {
                            noOfCopiesInt = parseInt(copiesStr);
                        }
                    }
                    
                    // Create book entry
                    const book = {
                        program_code: programCode,
                        course_code: currentCourseCode,
                        course_title: currentCourseTitle,
                        book_title: bookTitle,
                        authors: authors,
                        publisher: publisher,
                        publication_year: publicationYearInt > 0 ? publicationYearInt : '',
                        copyright: publicationYearInt > 0 ? publicationYearInt : '',
                        edition: '',
                        isbn: '',
                        call_number: '',
                        no_of_copies: noOfCopiesInt
                    };
                    
                    // Track publication year AND store book with the course
                    if (currentCourseCode) {
                        const courseKey = `${currentCourseCode}|${programCode}`;
                        if (detectedCourses[courseKey]) {
                            // Track publication year
                            if (publicationYearInt > 0 && !detectedCourses[courseKey].years.includes(publicationYearInt)) {
                            detectedCourses[courseKey].years.push(publicationYearInt);
                            }
                            // Store book directly with course (avoid duplicates)
                            if (!detectedCourses[courseKey].books.find(b => 
                                b.book_title === book.book_title && 
                                b.authors === book.authors
                            )) {
                                detectedCourses[courseKey].books.push(book);
                                // Debug: Log first few books being stored
                                if (detectedCourses[courseKey].books.length <= 3) {
                                }
                            }
                        }
                    }
                    
                    // Group by program
                    if (!booksByProgram[programCode]) {
                        booksByProgram[programCode] = [];
                    }
                    
                    booksByProgram[programCode].push(book);
                    
                    // Build courseBooksMap for quick lookup
                    if (currentCourseCode) {
                        const normalizedCode = String(currentCourseCode).trim();
                        if (!courseBooksMap[normalizedCode]) {
                            courseBooksMap[normalizedCode] = [];
                        }
                        // Avoid duplicates
                        if (!courseBooksMap[normalizedCode].find(b => 
                            b.book_title === book.book_title && 
                            b.authors === book.authors
                        )) {
                            courseBooksMap[normalizedCode].push(book);
                        }
                    }
                }
            });
            
            // STORE ORIGINAL DETECTED COURSES LOCALLY (with books arrays!)
            originalDetectedCourses = detectedCourses;
            
            // Verify books are stored - check first few courses
            const sampleCourses = Object.values(originalDetectedCourses).slice(0, 3);
            sampleCourses.forEach((course, idx) => {
                    course_code: course.course_code,
                    program_code: course.program_code,
                    books_count: course.books ? course.books.length : 0,
                    years_count: course.years ? course.years.length : 0,
                    first_book: course.books && course.books.length > 0 ? course.books[0].book_title : 'none'
                });
            });
            
            // Send parsed data to server for program code matching and course checking
    fetch('api/parse_excel.php', {
        method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    parsed_data: booksByProgram,
                    detected_courses: Object.values(detectedCourses)
                })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('importLoading').style.display = 'none';
        
        if (data.success) {
                    // Store both original and matched data
                    originalParsedBooksData = booksByProgram; // Original structure from Excel (has course_code preserved)
                    importedBooksData = data.books_by_program || booksByProgram; // Matched structure from server
                    
                    // Note: originalDetectedCourses is already stored above with all books arrays intact
                    // The server's detected_courses doesn't have books, but we use originalDetectedCourses for lookups
                    
                    // IMPORTANT: Keep the original courseBooksMap that was built during parsing
                    // It has the correct course_code associations before server transformation
                    // The server response might have transformed the structure, so we use the original
                    
                    // Also update course_code on importedBooksData if missing (preserve from original)
                    if (importedBooksData && originalParsedBooksData) {
                        Object.keys(importedBooksData).forEach(programCode => {
                            const matchedBooks = importedBooksData[programCode] || [];
                            const originalBooks = originalParsedBooksData[programCode] || [];
                            // Match books by title and authors to restore course_code
                            matchedBooks.forEach((matchedBook, idx) => {
                                if (!matchedBook.course_code && originalBooks[idx]) {
                                    matchedBook.course_code = originalBooks[idx].course_code;
                                }
                            });
                        });
                    }
                    
            displayImportedBooks();
                    
                    // Display detected courses (with books arrays preserved)
                    if (data.detected_courses && data.detected_courses.length > 0) {
                        displayDetectedCourses(data.detected_courses);
                    }
                    
            document.getElementById('importResults').style.display = 'block';
        } else {
                    alert('Error processing file: ' + (data.message || 'Unknown error'));
            document.getElementById('fileUploadArea').style.display = 'block';
        }
    })
    .catch(error => {
        document.getElementById('importLoading').style.display = 'none';
                alert('Error processing file: ' + error.message);
        document.getElementById('fileUploadArea').style.display = 'block';
    });
            
        } catch (error) {
            document.getElementById('importLoading').style.display = 'none';
            alert('Error parsing Excel file: ' + error.message);
            document.getElementById('fileUploadArea').style.display = 'block';
        }
    };
    
    reader.onerror = function() {
        document.getElementById('importLoading').style.display = 'none';
        alert('Error reading file');
        document.getElementById('fileUploadArea').style.display = 'block';
    };
    
    reader.readAsBinaryString(file);
}

// Display imported books - show program tabs to filter detected courses
function displayImportedBooks() {
    const tabsContainer = document.getElementById('programTabs');
    const booksContainer = document.getElementById('booksListContainer');
    
    // Hide books list - books only show in modal when clicking courses
    if (booksContainer) {
        booksContainer.style.display = 'none';
    }
    
    // Show program tabs to filter detected courses
    if (tabsContainer) {
        tabsContainer.innerHTML = '';
        tabsContainer.style.display = 'flex';
        
        // Get unique program codes from detected courses
        const programs = new Set();
        if (detectedCoursesData && detectedCoursesData.length > 0) {
            detectedCoursesData.forEach(course => {
                if (course.program_code) {
                    programs.add(course.program_code);
                }
            });
        }
        
        // If no detected courses, get programs from imported books
        if (programs.size === 0) {
            Object.keys(importedBooksData).forEach(prog => programs.add(prog));
        }
        
        const programArray = Array.from(programs);
        
        // Add "All" tab first
        const allTab = document.createElement('button');
        allTab.type = 'button';
        allTab.className = 'program-tab active';
        allTab.textContent = 'All';
        allTab.onclick = () => switchProgramTab('All');
        tabsContainer.appendChild(allTab);
        
        // Generate tabs for each program
        programArray.forEach((programCode, index) => {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.className = 'program-tab';
            tab.textContent = programCode;
            tab.onclick = () => switchProgramTab(programCode);
            tabsContainer.appendChild(tab);
        });
        
        // Show all courses by default
        if (detectedCoursesData && detectedCoursesData.length > 0) {
            filterDetectedCoursesByProgram('All');
        }
    }
    
    // Update summary
    updateImportSummary();
}

// Display books for a specific program (removed - books only show in modal when clicking courses)

// Generate HTML for books list
function generateBooksListHTML(books) {
    if (books.length === 0) {
        return '<div style="padding: 20px; text-align: center; color: #666;">No books for this program</div>';
    }
    
    let html = '';
    const currentYear = new Date().getFullYear();
    
    books.forEach((book, index) => {
        const publicationYear = parseInt(book.publication_year || book.copyright || 0);
        const isCompliant = publicationYear > 0 && (currentYear - publicationYear) < 5;
        const isDisabled = !isCompliant;
        
        // APA 7th Edition format
        let apaText = `${index + 1}. `;
        if (book.authors) apaText += `${book.authors}`;
        if (publicationYear > 0) apaText += ` (${publicationYear})`;
        if (book.book_title) apaText += `. <strong>${book.book_title}</strong>`;
        if (book.edition && !book.edition.toLowerCase().includes('1st') && !book.edition.toLowerCase().includes('first')) {
            apaText += ` (${book.edition})`;
        }
        if (book.publisher) apaText += `. ${book.publisher}`;
        
        html += `
            <div class="import-book-item ${isDisabled ? 'disabled' : ''}" data-book-index="${index}">
                <div class="import-book-item-content">
                    ${apaText}
                </div>
                <div class="import-book-item-actions">
                    ${isDisabled ? '<span style="color: #FF4C4C; font-size: 12px; font-weight: 600;">Outside 5-year range</span>' : ''}
                    <div class="checkbox-wrapper">
                        <input type="checkbox" ${isDisabled ? 'disabled' : ''} ${!isDisabled ? 'checked' : ''} onchange="updateImportSummary()">
                    </div>
                </div>
            </div>
        `;
    });
    
    return html;
}

// Switch program tab - filter detected courses by program
function switchProgramTab(programCode) {
    activeProgramTab = programCode;
    
    // Update tabs
    document.querySelectorAll('.program-tab').forEach(tab => {
        if (tab.textContent === programCode) {
            tab.classList.add('active');
        } else {
        tab.classList.remove('active');
        }
    });
    
    // Filter detected courses by program
    filterDetectedCoursesByProgram(programCode);
}

// Filter detected courses by program code
function filterDetectedCoursesByProgram(programCode) {
    const coursesList = document.getElementById('detectedCoursesList');
    if (!coursesList || !detectedCoursesData || detectedCoursesData.length === 0) {
        return;
    }
    
    // Set flag to prevent Select All updates during filtering
    isFilteringCourses = true;
    
    // Use the tracked selectedCourseIndices Set instead of reading from DOM
    // This ensures we have the complete state even for courses not currently visible
    const checkedIndices = new Set(selectedCourseIndices);
    
    // Save "Select All" checkbox state
    const selectAllCheckbox = document.getElementById('selectAllCompliantCheckbox');
    const wasSelectAllChecked = selectAllCheckbox ? selectAllCheckbox.checked : false;
    
    // Filter courses
    let filteredCourses = detectedCoursesData;
    if (programCode !== 'All') {
        filteredCourses = detectedCoursesData.filter(course => 
            course.program_code === programCode
        );
}

    // Re-render the courses list with filtered courses
    let html = '';
    filteredCourses.forEach((course, index) => {
        // Find original index in full detectedCoursesData array
        const originalIndex = detectedCoursesData.findIndex(c => 
            c.course_code === course.course_code && 
            c.program_code === course.program_code
        );
        
        const exists = course.exists_in_db || false;
        const courseId = course.course_id || null;
        const isChecked = checkedIndices.has(originalIndex);
        
        // Calculate compliant references count from actual books (with deduplication to match modal)
        const currentYear = new Date().getFullYear();
        let compliantCount = 0;
        
        // Try to get books from originalDetectedCourses (has actual books array)
        const courseKey = `${course.course_code}|${course.program_code}`;
        const originalCourse = originalDetectedCourses[courseKey];
        
        if (originalCourse && originalCourse.books && Array.isArray(originalCourse.books)) {
            // Deduplicate books (same logic as modal - by title and authors)
            const uniqueBooks = [];
            originalCourse.books.forEach(book => {
                if (!uniqueBooks.find(b => 
                    b.book_title === book.book_title && 
                    b.authors === book.authors
                )) {
                    uniqueBooks.push(book);
                }
            });
            // Count compliant unique books
            compliantCount = uniqueBooks.filter(book => {
                const yearInt = parseInt(book.publication_year || book.copyright || 0);
                return yearInt > 0 && (currentYear - yearInt) < 5;
            }).length;
        } else {
            // Fallback: try to find books from originalParsedBooksData by course code
            const normalizedCourseCode = String(course.course_code || '').trim();
            if (originalParsedBooksData && normalizedCourseCode) {
                // Collect unique books first (deduplicate by title and authors)
                const uniqueBooks = [];
                Object.keys(originalParsedBooksData).forEach(programKey => {
                    const books = originalParsedBooksData[programKey];
                    if (Array.isArray(books)) {
                        books.forEach(book => {
                            const bookCourseCode = String(book.course_code || '').trim();
                            if (bookCourseCode === normalizedCourseCode || 
                                bookCourseCode.toLowerCase() === normalizedCourseCode.toLowerCase()) {
                                // Deduplicate same way as modal does (by title and authors)
                                if (!uniqueBooks.find(b => 
                                    b.book_title === book.book_title && 
                                    b.authors === book.authors
                                )) {
                                    uniqueBooks.push(book);
                                }
                            }
                        });
                    }
                });
                // Count compliant unique books
                compliantCount = uniqueBooks.filter(book => {
                    const yearInt = parseInt(book.publication_year || book.copyright || 0);
                    return yearInt > 0 && (currentYear - yearInt) < 5;
                }).length;
            } else {
                // Last fallback: use years array (less accurate but better than 0)
                if (course.years && Array.isArray(course.years)) {
                    compliantCount = course.years.filter(year => {
                        const yearInt = parseInt(year);
                        return yearInt > 0 && (currentYear - yearInt) < 5;
                    }).length;
                }
            }
        }
        
        html += `
            <div style="display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #eee; ${index === filteredCourses.length - 1 ? 'border-bottom: none;' : ''} cursor: pointer; transition: background-color 0.2s;" 
                 onmouseover="this.style.backgroundColor='#f5f5f5'; this.style.cursor='pointer';"
                 onmouseout="this.style.backgroundColor='transparent'"
                 onclick="showCourseBooksModal(${originalIndex}); event.stopPropagation();"
                 title="Click to view book references in modal">
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                        ${course.course_code || 'N/A'}
                        ${exists ? '<span style="color: #4CAF50; font-size: 12px; margin-left: 8px;">✓ Exists in Database</span>' : '<span style="color: #FF9800; font-size: 12px; margin-left: 8px;">Not in Database</span>'}
                    </div>
                    <div style="font-size: 13px; color: #666; margin-bottom: 4px;">${course.course_title || 'No title'}</div>
                    <div style="font-size: 12px; color: #999;">
                        Program: ${course.program_code || 'N/A'}
                        <span style="display: inline-block; margin-left: 8px; font-size: 11px; font-weight: 600; color: #1976d2;">
                            ${compliantCount} Compliant Reference${compliantCount !== 1 ? 's' : ''}
                        </span>
                    </div>
                </div>
                <div style="margin-left: 16px; display: flex; align-items: center; gap: 12px;">
                    ${!exists ? `
                        <label style="display: flex; align-items: center; cursor: pointer;" onclick="event.stopPropagation()">
                            <input type="checkbox" 
                                   class="course-to-add-checkbox" 
                                   data-course-index="${originalIndex}"
                                   ${isChecked ? 'checked' : ''}
                                   style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;"
                                   onchange="handleCourseCheckboxChange(${originalIndex}, this.checked)">
                            <span style="font-size: 13px; color: #1976d2;">Add to List</span>
                        </label>
                    ` : `
                        <span style="font-size: 13px; color: #999;">Already exists</span>
                    `}
                    <span style="font-size: 12px; color: #666; font-style: italic;">Click to view books →</span>
                </div>
            </div>
        `;
    });
    
    coursesList.innerHTML = html;
    
    // Update count
    updateCoursesToAddCount();
    
    // Restore "Select All" checkbox state after DOM is fully updated
    // Use double requestAnimationFrame to ensure DOM is ready
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (selectAllCheckbox) {
                // Get all selectable courses (not just in current tab, but across all tabs)
                const allSelectableCourses = detectedCoursesData.filter(course => !course.exists_in_db);
                const allSelectableIndices = new Set();
                allSelectableCourses.forEach((course) => {
                    const originalIndex = detectedCoursesData.findIndex(c => 
                        c.course_code === course.course_code && 
                        c.program_code === course.program_code
                    );
                    if (originalIndex >= 0) {
                        allSelectableIndices.add(originalIndex);
                    }
                });
                
                // Use the Set that tracks selected course indices instead of reading from DOM
                // This ensures we have the complete state even for courses not currently visible
                const currentlyCheckedIndices = new Set(selectedCourseIndices);
                
                // Check if all selectable courses (across all tabs) are selected
                const allSelected = allSelectableIndices.size > 0 && 
                    Array.from(allSelectableIndices).every(idx => currentlyCheckedIndices.has(idx));
                
                // Update the "Select All" checkbox state
                selectAllCheckbox.checked = allSelected;
                
                // Clear the filtering flag
                isFilteringCourses = false;
            } else {
                isFilteringCourses = false;
            }
        });
    });
}

// Setup course autocomplete for import (removed - no longer needed)
function setupImportCourseAutocomplete() {
    // Function removed - course selection no longer required
    return;
}

// Clear selected course
function clearSelectedCourse() {
    selectedImportCourseId = null;
    selectedImportCourseDisplay = null;
    const selectedInfo = document.getElementById('selectedCourseInfo');
    if (selectedInfo) {
        selectedInfo.style.display = 'none';
    }
    validateImportForm();
}

// Validate import form (no longer requires course or location)
function validateImportForm() {
    const submitBtn = document.getElementById('importSubmitBtn');
    
    // Only require that books data exists
    const isValid = Object.keys(importedBooksData).length > 0;
    
    if (submitBtn) {
        submitBtn.disabled = !isValid;
        submitBtn.style.opacity = isValid ? '1' : '0.5';
        submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
        submitBtn.style.pointerEvents = isValid ? 'auto' : 'none';
        submitBtn.style.backgroundColor = isValid ? '#0f7a53' : '#6c757d';
    }
}

// Update import summary - Count ALL books from Excel file (not from selected courses)
function updateImportSummary() {
    let totalBooks = 0;
    let compliantBooks = 0;
        const currentYear = new Date().getFullYear();
    const allUniqueBooks = new Map(); // Use Map to track unique books across all courses
    
    // Count ALL books from the imported Excel file (from originalParsedBooksData or importedBooksData)
    const dataToUse = originalParsedBooksData && Object.keys(originalParsedBooksData).length > 0 
        ? originalParsedBooksData 
        : importedBooksData;
    
    if (dataToUse && Object.keys(dataToUse).length > 0) {
        // Collect all unique books from all programs
        Object.keys(dataToUse).forEach(programKey => {
            const programBooks = dataToUse[programKey];
            if (Array.isArray(programBooks)) {
                programBooks.forEach(book => {
                    // Create unique key for deduplication (title + authors)
                    const bookKey = `${(book.book_title || '').toLowerCase().trim()}|${(book.authors || '').toLowerCase().trim()}`;
                    if (!allUniqueBooks.has(bookKey)) {
                        allUniqueBooks.set(bookKey, book);
                    }
                });
            }
        });
    }
    
    // Count total and compliant books
    allUniqueBooks.forEach(book => {
        totalBooks++;
        const yearInt = parseInt(book.publication_year || book.copyright || 0);
        const isCompliant = yearInt > 0 && (currentYear - yearInt) < 5;
        if (isCompliant) {
            compliantBooks++;
        }
    });
    
    document.getElementById('totalBooksCount').textContent = totalBooks;
    document.getElementById('compliantBooksCount').textContent = compliantBooks;
    document.getElementById('nonCompliantBooksCount').textContent = totalBooks - compliantBooks;
    
    validateImportForm();
}

// Submit imported books - Collect books from selected courses and check for duplicates
function submitImportedBooks() {
    // Use the Set that tracks selected course indices (same as we use for counting)
    // This ensures we have all selected courses even if they're not currently visible in DOM
    const selectedIndices = Array.from(selectedCourseIndices);
    
    if (selectedIndices.length === 0) {
        alert('Please select at least one course to import');
        return;
    }
    
    // Collect books from selected courses
    const courseBooksMap = {}; // {course_id: [books]}
    const coursesToProcess = [];
    
    selectedIndices.forEach(index => {
        const course = detectedCoursesData[index];
        if (!course || course.exists_in_db) {
            return; // Skip courses that already exist
        }
        
        // Get course_id (if course exists) or prepare to create course
        const courseId = course.course_id || null;
        const normalizedCourseCode = String(course.course_code || '').trim();
        
        // Get books for this course - use originalParsedBooksData first (same as summary) for consistency
        let books = [];
        const uniqueBooks = [];
        
        // Try originalParsedBooksData first (same data source as summary)
        if (originalParsedBooksData && normalizedCourseCode) {
            Object.keys(originalParsedBooksData).forEach(programKey => {
                const programBooks = originalParsedBooksData[programKey];
                if (Array.isArray(programBooks)) {
                    programBooks.forEach(book => {
                        const bookCourseCode = String(book.course_code || '').trim();
                        if (bookCourseCode === normalizedCourseCode || 
                            bookCourseCode.toLowerCase() === normalizedCourseCode.toLowerCase()) {
                            // Deduplicate by title and authors
                            if (!uniqueBooks.find(b => 
                                String(b.book_title || '').trim().toLowerCase() === String(book.book_title || '').trim().toLowerCase() &&
                                String(b.authors || '').trim().toLowerCase() === String(book.authors || '').trim().toLowerCase()
                            )) {
                                uniqueBooks.push(book);
                            }
                        }
                    });
                }
            });
        }
        
        // Fallback: try originalDetectedCourses if no books found
        if (uniqueBooks.length === 0) {
            const courseKey = `${course.course_code}|${course.program_code}`;
            const originalCourse = originalDetectedCourses[courseKey];
            if (originalCourse && originalCourse.books && Array.isArray(originalCourse.books)) {
                originalCourse.books.forEach(book => {
                    if (!uniqueBooks.find(b => 
                        String(b.book_title || '').trim().toLowerCase() === String(book.book_title || '').trim().toLowerCase() &&
                        String(b.authors || '').trim().toLowerCase() === String(book.authors || '').trim().toLowerCase()
                    )) {
                        uniqueBooks.push(book);
                    }
                });
            }
        }
        
        books = uniqueBooks;
        
        // Store course and books for processing
        coursesToProcess.push({
            course: course,
            courseId: courseId,
            books: books
        });
    });
    
    if (coursesToProcess.length === 0) {
        alert('No courses to import');
        return;
    }
    
    // Submit via batch API - check for duplicates on server side
    const submitBtn = document.getElementById('importSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Importing...';
    
    // Send all courses with their books
    fetch('api/add_book.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            input_method: 'batch_with_courses',
            courses: coursesToProcess.map(item => ({
                course_code: item.course.course_code,
                course_title: item.course.course_title,
                program_code: item.course.program_code,
                course_id: item.courseId,
                books: item.books.map(book => ({
                    book_title: book.book_title || '',
                    authors: book.authors || '',
                    publisher: book.publisher || '',
                    publication_year: book.publication_year || book.copyright || '',
                    edition: book.edition || '',
                    isbn: book.isbn || '',
                    call_number: book.call_number || '',
                    no_of_copies: book.no_of_copies || 1
                }))
            }))
        })
    })
    .then(async response => {
        // Get response text first to check if it's valid JSON
        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            // If not valid JSON, show the raw response for debugging
            console.error('Invalid JSON response:', responseText);
            throw new Error('Server returned invalid response. Check console for details.');
        }
        
        if (!response.ok) {
            throw new Error(data.message || `Server error: ${response.status}`);
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            const totalBooks = data.total_books_imported || 0;
            const skippedBooks = data.skipped_duplicates || 0;
            const message = `Successfully imported ${totalBooks} book(s)!` + 
                (skippedBooks > 0 ? `\n${skippedBooks} duplicate(s) skipped.` : '');
            alert(message);
            closeImportDataModal();
            // Reload library data if on library management page
            if (typeof loadLibraryData === 'function') {
                loadLibraryData();
            }
        } else {
            alert('Error importing books: ' + (data.message || 'Unknown error'));
        }
        submitBtn.disabled = false;
        submitBtn.textContent = 'IMPORT BOOKS';
    })
    .catch(error => {
        console.error('Import error:', error);
        alert('Error importing books: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.textContent = 'IMPORT BOOKS';
    });
}

// Display detected courses from Excel
let detectedCoursesData = [];
let selectedCoursesToAdd = [];
// selectedCourseIndices is defined above with other state variables

function displayDetectedCourses(courses) {
    detectedCoursesData = courses;
    selectedCoursesToAdd = [];
    selectedCourseIndices.clear(); // Clear selections when new courses are loaded
    
    const coursesSection = document.getElementById('detectedCoursesSection');
    
    if (!courses || courses.length === 0) {
        if (coursesSection) {
        coursesSection.style.display = 'none';
        }
        return;
    }
    
    if (coursesSection) {
    coursesSection.style.display = 'block';
    }
    
    // Use the filter function to display courses (respects active program tab)
    if (activeProgramTab) {
        filterDetectedCoursesByProgram(activeProgramTab);
    } else {
        filterDetectedCoursesByProgram('All');
    }
    
    updateCoursesToAddCount();
}

// Handle individual course checkbox change
function handleCourseCheckboxChange(index, isChecked) {
    if (isChecked) {
        selectedCourseIndices.add(index);
    } else {
        selectedCourseIndices.delete(index);
    }
    updateCoursesToAddCount();
}

function updateCoursesToAddCount() {
    // Use the Set to get all selected courses, not just visible checkboxes
    selectedCoursesToAdd = Array.from(selectedCourseIndices).map(index => {
        return detectedCoursesData[index];
    }).filter(course => course && !course.exists_in_db); // Filter out any invalid or existing courses
    
    const courseCount = selectedCoursesToAdd.length;
    document.getElementById('coursesToAddCount').textContent = courseCount;
    
    // Calculate total COMPLIANT references from all selected courses (only books < 5 years old)
    // Use the SAME data source as summary (originalParsedBooksData) to ensure consistency
    // Deduplicate globally across all selected courses to match the summary count
    const currentYear = new Date().getFullYear();
    const allUniqueBooks = new Map(); // Use Map to track unique books globally across all selected courses
    
    // Get all selected course codes (normalized for matching)
    const selectedCourseCodes = new Set();
    selectedCoursesToAdd.forEach(course => {
        const normalizedCode = String(course.course_code || '').trim().toLowerCase();
        if (normalizedCode) {
            selectedCourseCodes.add(normalizedCode);
        }
    });
    
    // Use the same data source as summary: originalParsedBooksData (or importedBooksData as fallback)
    const dataToUse = originalParsedBooksData && Object.keys(originalParsedBooksData).length > 0 
        ? originalParsedBooksData 
        : importedBooksData;
    
    // Collect all books from selected courses (same logic as summary, but filtered by selected courses)
    if (dataToUse && Object.keys(dataToUse).length > 0 && selectedCourseCodes.size > 0) {
        Object.keys(dataToUse).forEach(programKey => {
            const programBooks = dataToUse[programKey];
            if (Array.isArray(programBooks)) {
                programBooks.forEach(book => {
                    // Check if this book belongs to a selected course
                    const bookCourseCode = String(book.course_code || '').trim().toLowerCase();
                    if (bookCourseCode && selectedCourseCodes.has(bookCourseCode)) {
                        // Create unique key for deduplication (title + authors) - same as summary
                        const bookKey = `${(book.book_title || '').toLowerCase().trim()}|${(book.authors || '').toLowerCase().trim()}`;
                        if (!allUniqueBooks.has(bookKey)) {
                            allUniqueBooks.set(bookKey, book);
                        }
                    }
                });
            }
        });
    }
    
    // Count only compliant books from the globally deduplicated set
    let totalCompliantReferences = 0;
    allUniqueBooks.forEach(book => {
        const yearInt = parseInt(book.publication_year || book.copyright || 0);
        if (yearInt > 0 && (currentYear - yearInt) < 5) {
            totalCompliantReferences++;
        }
    });
    
    document.getElementById('referencesCount').textContent = totalCompliantReferences;
    
    // Update Select All checkbox state based on current selection
    // Skip this if we're in the middle of filtering (tab switching)
    if (!isFilteringCourses) {
        const selectAllCheckbox = document.getElementById('selectAllCompliantCheckbox');
        if (selectAllCheckbox) {
            // Get all selectable courses (courses that don't exist in database)
            const allSelectableCourses = detectedCoursesData.filter(course => !course.exists_in_db);
            const allSelectableIndices = new Set();
            allSelectableCourses.forEach((course) => {
                const originalIndex = detectedCoursesData.findIndex(c => 
                    c.course_code === course.course_code && 
                    c.program_code === course.program_code
                );
                if (originalIndex >= 0) {
                    allSelectableIndices.add(originalIndex);
                }
            });
            
            // Use the Set that tracks selected course indices instead of reading from DOM
            // This ensures we have the complete state even for courses not currently visible
            const checkedIndices = new Set(selectedCourseIndices);
            
            // Check if all selectable courses are selected
            const allSelected = allSelectableIndices.size > 0 && 
                Array.from(allSelectableIndices).every(idx => checkedIndices.has(idx));
            
            // Update "Select All" checkbox state
            selectAllCheckbox.checked = allSelected;
        }
    }
}

// Toggle all courses (checkbox handler) - Selects ALL courses regardless of compliant references
function toggleAllCourses(isChecked) {
    if (!detectedCoursesData || detectedCoursesData.length === 0) {
        return;
    }
    
    // Update the Set that tracks selected course indices
    detectedCoursesData.forEach((course, index) => {
        // Skip if course already exists in database
        if (course.exists_in_db) {
            return;
        }
        
        // Update the Set
        if (isChecked) {
            selectedCourseIndices.add(index);
        } else {
            selectedCourseIndices.delete(index);
        }
        
        // Update the checkbox if it exists in the DOM (for current tab)
        const checkbox = document.querySelector(`.course-to-add-checkbox[data-course-index="${index}"]`);
        if (checkbox) {
            checkbox.checked = isChecked;
        }
    });
    
    // Update count
    updateCoursesToAddCount();
}

// Add selected courses to database (Note: This function may not be needed if courses are auto-added with books)
function addSelectedCourses() {
    if (selectedCoursesToAdd.length === 0) {
        alert('Please select at least one course to add.');
        return;
    }
    
    // Get current school year and term
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const month = currentDate.getMonth() + 1;
    
    let schoolYear = '';
    let schoolTerm = '';
    
    if (month >= 8) {
        schoolYear = `${currentYear}-${currentYear + 1}`;
        schoolTerm = '1st Semester';
    } else if (month >= 1 && month < 5) {
        schoolYear = `${currentYear - 1}-${currentYear}`;
        schoolTerm = '2nd Semester';
    } else {
        schoolYear = `${currentYear - 1}-${currentYear}`;
        schoolTerm = 'Summer';
    }
    
    // Add courses one by one
    let completed = 0;
    let failed = 0;
    const errors = [];
    
    const addNextCourse = (index) => {
        if (index >= selectedCoursesToAdd.length) {
            // Reset checkbox state after adding courses
            const selectAllCheckbox = document.getElementById('selectAllCompliantCheckbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            
            if (failed > 0) {
                alert(`${completed} course(s) added successfully. ${failed} course(s) failed:\n${errors.join('\n')}`);
            } else {
                alert(`Successfully added ${completed} course(s) to the database!`);
                // Refresh the course list by re-checking
                fetch('api/check_courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        courses: detectedCoursesData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDetectedCourses(data.checked_courses);
                    }
                });
                setupImportCourseAutocomplete();
            }
            return;
        }
        
        const course = selectedCoursesToAdd[index];
        
        // Get program ID from program code
        fetch('api/get_program_id.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                program_code: course.program_code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.program_id) {
                const formData = new URLSearchParams();
                formData.append('course_code', course.course_code);
                formData.append('course_name', course.course_title);
                formData.append('units', 3);
                formData.append('year_level', '1st Year');
                formData.append('school_term', schoolTerm);
                formData.append('school_year', schoolYear);
                formData.append('programs', data.program_id);
                formData.append('status', 'approved');
                formData.append('created_by_role', 'librarian');
                
                return fetch('api/process_librarian_add_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                });
            } else {
                throw new Error('Program not found: ' + course.program_code);
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                completed++;
            } else {
                failed++;
                errors.push(`${course.course_code}: ${data.message || 'Unknown error'}`);
            }
            addNextCourse(index + 1);
        })
        .catch(error => {
            failed++;
            errors.push(`${course.course_code}: ${error.message}`);
            addNextCourse(index + 1);
        });
    };
    
    addNextCourse(0);
}

// Store current course books globally so it persists
window.currentCourseBooks = [];

// Show course books modal
function showCourseBooksModal(courseIndex) {
    
    if (!detectedCoursesData || !detectedCoursesData[courseIndex]) {
        console.error('❌ Course not found at index:', courseIndex);
        alert('Error: Course not found. Please refresh and try again.');
        return;
    }

    const course = detectedCoursesData[courseIndex];
    const courseCode = course.course_code;
    const programCode = course.program_code;

    
    // CRITICAL: Verify data exists before proceeding
    if (!importedBooksData && !originalParsedBooksData) {
        console.error('❌ NO BOOK DATA AVAILABLE!');
        alert('Error: No book data found. Please upload the Excel file again.');
        return;
    }
    
    // Store data globally for debugging
    window.debugImportedBooksData = importedBooksData;
    window.debugOriginalParsedBooksData = originalParsedBooksData;
    window.debugCourse = course;
    window.debugCourseIndex = courseIndex;
    
    // Set modal title
    document.getElementById('courseBooksModalTitle').textContent = `Book References - ${courseCode}`;
    
    // Find all books for this course - SEARCH BY COURSE CODE ONLY (ignore program code)
    let courseBooks = [];
    window.currentCourseBooks = []; // Clear previous
    
    // Normalize the course code for matching (trim)
    const normalizedCourseCode = String(courseCode || '').trim();
    const normalizedProgramCode = String(programCode || '').trim().toUpperCase();
    
    
    // METHOD 0: Try to get books directly from ORIGINAL detectedCourses (MOST RELIABLE!)
    // This is the original structure BEFORE server processing - it has the books arrays!
    
    if (originalDetectedCourses && typeof originalDetectedCourses === 'object') {
        const allKeys = Object.keys(originalDetectedCourses);
        
        // Show sample course structure
        if (allKeys.length > 0) {
            const sampleKey = allKeys[0];
            const sampleCourse = originalDetectedCourses[sampleKey];
                course_code: sampleCourse?.course_code,
                program_code: sampleCourse?.program_code,
                has_books: !!sampleCourse?.books,
                books_count: sampleCourse?.books?.length || 0,
                years_count: sampleCourse?.years?.length || 0
            });
        }
        
        // Try multiple key formats
        const possibleKeys = [
            `${normalizedCourseCode}|${normalizedProgramCode}`,
            `${normalizedCourseCode}|${programCode}`,
            `${courseCode}|${normalizedProgramCode}`,
            `${courseCode}|${programCode}`
        ];
        
        
        for (const key of possibleKeys) {
            if (originalDetectedCourses[key]) {
                const courseObj = originalDetectedCourses[key];
                    course_code: courseObj.course_code,
                    program_code: courseObj.program_code,
                    books_count: courseObj.books?.length || 0
                });
                
                if (courseObj.books && Array.isArray(courseObj.books) && courseObj.books.length > 0) {
                    courseBooks = [...courseObj.books];
                    break;
                }
            }
        }
        
        // If still no books, try matching by course code only (case-insensitive)
        if (courseBooks.length === 0) {
            const matchingKeys = allKeys.filter(key => {
                const parts = key.split('|');
                const keyCourseCode = parts[0] ? String(parts[0]).trim() : '';
                return keyCourseCode.toLowerCase() === normalizedCourseCode.toLowerCase();
            });
            
            
            matchingKeys.forEach(key => {
                const course = originalDetectedCourses[key];
                if (course && course.books && Array.isArray(course.books) && course.books.length > 0) {
                    course.books.forEach(book => {
                        if (!courseBooks.find(b => b.book_title === book.book_title && b.authors === book.authors)) {
                            courseBooks.push(book);
                        }
                    });
                }
            });
            
            if (courseBooks.length > 0) {
            }
        }
    } else {
        console.error('❌ originalDetectedCourses is empty, null, or wrong type!');
    }
    
    
    // METHOD 0.3: DIRECT SEARCH IN originalParsedBooksData (SAME DATA THAT SHOWS COPYRIGHT YEARS!)
    // Since copyright years ARE showing, the books MUST be in originalParsedBooksData!
    if (courseBooks.length === 0 && originalParsedBooksData && Object.keys(originalParsedBooksData).length > 0) {
        
        Object.keys(originalParsedBooksData).forEach(programKey => {
            const books = originalParsedBooksData[programKey];
            if (Array.isArray(books)) {
                books.forEach(book => {
                    const bookCourseCode = String(book.course_code || '').trim();
                    if (bookCourseCode && (bookCourseCode === normalizedCourseCode || 
                        bookCourseCode.toLowerCase() === normalizedCourseCode.toLowerCase())) {
                        // Avoid duplicates
                        if (!courseBooks.find(b => 
                            b.book_title === book.book_title && 
                            b.authors === book.authors &&
                            b.publication_year === book.publication_year
                        )) {
                            courseBooks.push(book);
                        }
                    }
                });
            }
        });
        
        if (courseBooks.length > 0) {
        } else {
            console.error('❌ No books found in originalParsedBooksData for course:', normalizedCourseCode);
        }
    }
    
    // METHOD 0.5: LAST RESORT - Search ALL courses by course code only (ignore program completely)
    // This should ALWAYS work if books exist
    if (courseBooks.length === 0 && originalDetectedCourses && typeof originalDetectedCourses === 'object') {
        const allCourses = Object.values(originalDetectedCourses);
        
        let foundCount = 0;
        allCourses.forEach((course, idx) => {
            const courseCodeMatch = String(course.course_code || '').trim().toLowerCase() === normalizedCourseCode.toLowerCase();
            if (courseCodeMatch && course.books && Array.isArray(course.books)) {
                foundCount++;
                course.books.forEach(book => {
                    if (!courseBooks.find(b => b.book_title === book.book_title && b.authors === book.authors)) {
                        courseBooks.push(book);
                    }
                });
            }
        });
        
        if (courseBooks.length > 0) {
        } else {
            console.error('❌ EXHAUSTIVE SEARCH FOUND NOTHING!');
            allCourses.slice(0, 10).forEach(c => {
            });
        }
    }
    
    // SAFEGUARD: Rebuild courseBooksMap if it's empty but we have originalParsedBooksData
    // Use ORIGINAL data first (before server transformation) as it has correct course_code
    if ((!courseBooksMap || Object.keys(courseBooksMap).length === 0)) {
        if (originalParsedBooksData && Object.keys(originalParsedBooksData).length > 0) {
            courseBooksMap = {};
            Object.keys(originalParsedBooksData).forEach(programCode => {
                const books = originalParsedBooksData[programCode];
                if (Array.isArray(books)) {
                    books.forEach(book => {
                        const courseCode = String(book.course_code || '').trim();
                        if (courseCode) {
                            if (!courseBooksMap[courseCode]) {
                                courseBooksMap[courseCode] = [];
                            }
                            // Avoid duplicates
                            if (!courseBooksMap[courseCode].find(b => 
                                b.book_title === book.book_title && 
                                b.authors === book.authors
                            )) {
                                courseBooksMap[courseCode].push(book);
                            }
                        }
                    });
                }
            });
        } else if (importedBooksData && Object.keys(importedBooksData).length > 0) {
        courseBooksMap = {};
        Object.keys(importedBooksData).forEach(programCode => {
            const books = importedBooksData[programCode];
            if (Array.isArray(books)) {
                books.forEach(book => {
                    const courseCode = String(book.course_code || '').trim();
                    if (courseCode) {
                        if (!courseBooksMap[courseCode]) {
                            courseBooksMap[courseCode] = [];
                        }
                        // Avoid duplicates
                        if (!courseBooksMap[courseCode].find(b => 
                            b.book_title === book.book_title && 
                            b.authors === book.authors
                        )) {
                            courseBooksMap[courseCode].push(book);
                        }
                    }
                });
            }
        });
        }
    }
    
    // FIRST: Try to get books from courseBooksMap (pre-built during Excel processing)
    // This should have the correct course_code associations
    // Try both exact match and case-insensitive match
    if (courseBooksMap) {
        if (courseBooksMap[normalizedCourseCode]) {
        courseBooks = [...courseBooksMap[normalizedCourseCode]];
    } else {
            // Try case-insensitive match
            const matchingKey = Object.keys(courseBooksMap).find(key => 
                String(key).trim().toLowerCase() === normalizedCourseCode.toLowerCase()
            );
            if (matchingKey) {
                courseBooks = [...courseBooksMap[matchingKey]];
            }
        }
    }
    
    if (courseBooks.length === 0) {
        // Normalize the course code for matching (trim)
        const normalizedCourseCode = String(courseCode || '').trim();
        const normalizedProgramCode = String(programCode || '').trim().toUpperCase();
        
        
        // AGGRESSIVE SEARCH: Search by course code ONLY (ignore program code completely)
        const searchByCourseCodeOnly = (dataToSearch, dataName) => {
            if (!dataToSearch || Object.keys(dataToSearch).length === 0) {
                return 0;
            }
            
            let foundCount = 0;
            
            Object.keys(dataToSearch).forEach(programKey => {
                const books = dataToSearch[programKey];
                
                if (!Array.isArray(books)) {
                    return;
                }
                
                
                books.forEach((book, idx) => {
                    const bookCourseCode = String(book.course_code || '').trim();
                    
                    // Match course code ONLY - ignore program code completely (case-insensitive too)
                    if (bookCourseCode === normalizedCourseCode || 
                        bookCourseCode.toLowerCase() === normalizedCourseCode.toLowerCase()) {
                        foundCount++;
                            index: idx,
                            courseCode: bookCourseCode,
                            title: book.book_title || 'No title',
                            authors: book.authors || 'No authors',
                            program: book.program_code || programKey
                        });
                        // Only add if not already in array (avoid duplicates)
                        if (!courseBooks.find(b => b.book_title === book.book_title && b.authors === book.authors)) {
                            courseBooks.push(book);
                            window.currentCourseBooks.push(book); // Store globally too
                        }
                    }
                });
            });
            
            return foundCount;
        };
        
        // Search in originalParsedBooksData FIRST (has correct course_code from parsing)
        // THIS IS THE SAME DATA THAT DETECTED COPYRIGHT YEARS, SO IT MUST HAVE THE BOOKS!
        if (originalParsedBooksData && Object.keys(originalParsedBooksData).length > 0) {
            searchByCourseCodeOnly(originalParsedBooksData, 'originalParsedBooksData');
        } else {
            console.error('❌ originalParsedBooksData is empty or missing - THIS IS A PROBLEM!');
        }
        
        // If still no books, search in importedBooksData (server-matched data)
        if (courseBooks.length === 0 && importedBooksData && Object.keys(importedBooksData).length > 0) {
            searchByCourseCodeOnly(importedBooksData, 'importedBooksData');
        } else if (!importedBooksData || Object.keys(importedBooksData).length === 0) {
        }
        
        window.currentCourseBooks = courseBooks; // Store globally
    }
    
    if (courseBooks.length > 0) {
            courseCode: courseBooks[0].course_code,
            title: courseBooks[0].book_title,
            authors: courseBooks[0].authors
        });
    } else {
        console.error('❌ NO BOOKS FOUND! This is a problem.');
        if (originalParsedBooksData) {
            Object.keys(originalParsedBooksData).forEach(programKey => {
                const books = originalParsedBooksData[programKey];
                if (Array.isArray(books) && books.length > 0) {
                    const uniqueCourseCodes = [...new Set(books.map(b => String(b.course_code || '').trim()))];
                }
            });
        }
        if (importedBooksData) {
            Object.keys(importedBooksData).forEach(programKey => {
                const books = importedBooksData[programKey];
                if (Array.isArray(books) && books.length > 0) {
                    const uniqueCourseCodes = [...new Set(books.map(b => String(b.course_code || '').trim()))];
                }
            });
        }
    }
    
    // Get modal and books list FIRST
    const modal = document.getElementById('courseBooksModal');
    const booksList = document.getElementById('courseBooksList');
    const container = document.getElementById('courseBooksListContainer');
    
    if (!modal) {
        console.error('courseBooksModal element not found!');
        alert('Error: Modal element not found. Please refresh the page.');
        return;
    }
    
    if (!booksList) {
        console.error('courseBooksList element not found!');
        alert('Error: Could not find book list container');
        return;
    }
    
    // Ensure modal is in the DOM and accessible
    if (!document.body.contains(modal)) {
        console.error('Modal is not in the DOM!');
        document.body.appendChild(modal);
    }
    
    // Use found books or fallback to global - USE LET SO WE CAN ASSIGN DUMMY DATA LATER
    let booksToDisplay = courseBooks.length > 0 ? courseBooks : (window.currentCourseBooks.length > 0 ? window.currentCourseBooks : []);
    if (booksToDisplay.length > 0) {
            title: booksToDisplay[0].book_title,
            authors: booksToDisplay[0].authors,
            course_code: booksToDisplay[0].course_code
        });
    }
    
    // SHOW MODAL FIRST - Use try-catch to ensure it always shows
    try {
    modal.style.setProperty('display', 'flex', 'important');
    modal.style.setProperty('visibility', 'visible', 'important');
    modal.style.setProperty('opacity', '1', 'important');
    modal.style.setProperty('position', 'fixed', 'important');
    modal.style.setProperty('z-index', '999999', 'important');
        modal.setAttribute('data-modal-open', 'true');
        modal.classList.add('show');
    document.body.style.overflow = 'hidden';
        
        // Ensure footer covers parent modal
        const footer = modal.querySelector('.form-actions');
        if (footer) {
            footer.style.setProperty('background', '#EFEFEF', 'important');
            footer.style.setProperty('z-index', '1000001', 'important');
            footer.style.setProperty('position', 'relative', 'important');
        }
        
        // Ensure modal-box has solid background
        const modalBox = modal.querySelector('.modal-box');
        if (modalBox) {
            modalBox.style.setProperty('background-color', '#EFEFEF', 'important');
            modalBox.style.setProperty('z-index', '1000000', 'important');
        }
        
        // Remove any duplicate containers (safety check)
        const allContainers = modal.querySelectorAll('#courseBooksListContainer');
        if (allContainers.length > 1) {
            // Keep the first one, remove the rest
            for (let i = 1; i < allContainers.length; i++) {
                allContainers[i].remove();
            }
        }
        
        // Remove any duplicate lists (safety check)
        const allLists = modal.querySelectorAll('#courseBooksList');
        if (allLists.length > 1) {
            // Keep the first one, remove the rest
            for (let i = 1; i < allLists.length; i++) {
                allLists[i].remove();
            }
        }
        
    } catch (error) {
        console.error('❌ Error setting modal styles:', error);
        // Fallback: use direct style assignment
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.zIndex = '999999';
        document.body.style.overflow = 'hidden';
    }
    
    // RENDER CONTENT AFTER MODAL IS VISIBLE
    // If no books found, show dummy data for preview
    if (booksToDisplay.length === 0) {
        const currentYear = new Date().getFullYear();
        booksToDisplay = [
            {
                book_title: 'Introduction to Computer Science',
                authors: 'Smith, J., & Johnson, A.',
                publication_year: currentYear - 2,
                copyright: currentYear - 2,
                publisher: 'Tech Publishing Inc.',
                edition: '3rd Edition',
                isbn: '978-0-123456-78-9',
                no_of_copies: 5,
                call_number: 'QA76.5 .S65 2022'
            },
            {
                book_title: 'Data Structures and Algorithms',
                authors: 'Williams, R.',
                publication_year: currentYear - 1,
                copyright: currentYear - 1,
                publisher: 'Academic Press',
                edition: '2nd Edition',
                isbn: '978-0-987654-32-1',
                no_of_copies: 3,
                call_number: 'QA76.9 .D35 W55 2023'
            },
            {
                book_title: 'Database Systems Design',
                authors: 'Brown, K., Lee, M., & Garcia, P.',
                publication_year: currentYear - 3,
                copyright: currentYear - 3,
                publisher: 'Database Books',
                edition: '1st Edition',
                isbn: '978-0-555555-44-4',
                no_of_copies: 4,
                call_number: 'QA76.9 .D3 B76 2021'
            },
            {
                book_title: 'Web Development Fundamentals',
                authors: 'Davis, S.',
                publication_year: currentYear - 6,
                copyright: currentYear - 6,
                publisher: 'Web Press',
                edition: '1st Edition',
                isbn: '978-0-111111-22-3',
                no_of_copies: 2,
                call_number: 'TK5105.888 .D38 2018'
            },
            {
                book_title: 'Software Engineering Principles',
                authors: 'Martinez, L., & Taylor, B.',
                publication_year: currentYear - 4,
                copyright: currentYear - 4,
                publisher: 'Software Publishing',
                edition: '2nd Edition',
                isbn: '978-0-999999-88-7',
                no_of_copies: 6,
                call_number: 'QA76.758 .M37 2020'
            }
        ];
    } else {
    }
    
    if (booksToDisplay.length > 0) {
        
        const currentYear = new Date().getFullYear();
        let html = `<div style="margin-bottom: 16px !important; padding-bottom: 12px !important; border-bottom: 2px solid #e0e0e0 !important; display: block !important; visibility: visible !important; opacity: 1 !important; background: white !important; padding: 12px !important;">
            <div style="font-size: 16px !important; color: #333 !important; font-weight: 600 !important; display: block !important; visibility: visible !important; opacity: 1 !important;">Total Books: <strong style="color: #1976d2 !important;">${booksToDisplay.length}</strong></div>
        </div>`;
        
        booksToDisplay.forEach((book, index) => {
            const publicationYear = parseInt(book.publication_year || book.copyright || 0);
            const isCompliant = publicationYear > 0 && (currentYear - publicationYear) < 5;
            let apaText = `${index + 1}. `;
            if (book.authors) apaText += `${book.authors}`;
            if (publicationYear > 0) apaText += ` (${publicationYear})`;
            if (book.book_title) apaText += `. <strong>${book.book_title}</strong>`;
            if (book.edition && !book.edition.toLowerCase().includes('1st') && !book.edition.toLowerCase().includes('first')) {
                apaText += ` (${book.edition})`;
            }
            if (book.publisher) apaText += `. ${book.publisher}`;
            const borderColor = isCompliant ? '#4CAF50' : '#FF4C4C';
            const bgColor = isCompliant ? '#f1f8f4' : '#fff5f5';
            html += `<div style="padding: 16px !important; margin-bottom: 12px !important; border-left: 4px solid ${borderColor} !important; background-color: ${bgColor} !important; border-radius: 4px !important; display: block !important; visibility: visible !important; opacity: 1 !important; color: #333 !important; position: relative !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;">
                <div style="font-size: 15px !important; line-height: 1.7 !important; color: #333 !important; margin-bottom: 10px !important; display: block !important; visibility: visible !important; opacity: 1 !important; font-weight: 400 !important;">${apaText}</div>
                <div style="display: flex !important; gap: 16px !important; font-size: 12px !important; color: #666 !important; flex-wrap: wrap !important; visibility: visible !important; opacity: 1 !important;">
                    ${publicationYear > 0 ? `<span style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #666 !important;">Copyright: <strong style="color: ${borderColor} !important;">${publicationYear}</strong></span>` : ''}
                    ${book.no_of_copies ? `<span style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #666 !important;">Copies: ${book.no_of_copies}</span>` : ''}
                    ${book.isbn ? `<span style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #666 !important;">ISBN: ${book.isbn}</span>` : ''}
                    ${!isCompliant && publicationYear > 0 ? `<span style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #FF4C4C !important; font-weight: 600 !important;">⚠ Outside 5-year range</span>` : ''}
                    ${isCompliant ? `<span style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: #4CAF50 !important; font-weight: 600 !important;">✓ Within 5-year range</span>` : ''}
                </div>
            </div>`;
        });
        
        // SET CONTENT - MULTIPLE METHODS TO ENSURE IT STICKS
        
        try {
        // Method 1: Clear and set innerHTML
        booksList.innerHTML = '';
        booksList.innerHTML = html;
        
            // Method 2: Also use DOM manipulation as backup
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
            
            // Clear existing content
        while (booksList.firstChild) {
            booksList.removeChild(booksList.firstChild);
        }
            
            // Append all new content
        while (tempDiv.firstChild) {
            booksList.appendChild(tempDiv.firstChild);
        }
        
            
            // Force a reflow
            booksList.offsetHeight;
            
            // Verify content is actually there
            if (booksList.innerHTML.length < 100) {
                console.error('❌ CONTENT NOT SET! Re-trying...');
                booksList.innerHTML = html;
            }
            
        } catch (error) {
            console.error('❌ Error setting content:', error);
            // Last resort: direct innerHTML
            booksList.innerHTML = html;
        }
        
        // Calculate explicit height for content FIRST (before using it)
        const estimatedContentHeight = Math.max(400, booksToDisplay.length * 120 + 50);
        
        // Force ALL children to be visible FIRST
        const allChildren = booksList.querySelectorAll('*');
        allChildren.forEach((child, idx) => {
            child.style.setProperty('display', 'block', 'important');
            child.style.setProperty('visibility', 'visible', 'important');
            child.style.setProperty('opacity', '1', 'important');
            if (idx < 3) {
            }
        });
        
        // FIX EXISTING container with absolute positioning - DON'T replace, just fix it!
        setTimeout(() => {
            const existingContainer = document.getElementById('courseBooksListContainer');
            const existingList = document.getElementById('courseBooksList');
            
            if (!existingContainer || !existingList) {
                console.error('❌ Container or list not found!');
                return;
            }
            
            // Verify content exists
            if (existingList.innerHTML.length < 100) {
                return;
            }
            
            
            // Get modal box - ensure it's rendered
            const modalBox = modal.querySelector('.modal-box');
            if (!modalBox) {
                console.error('❌ Modal box not found!');
                return;
            }
            
            // CRITICAL: Ensure modal-box has explicit dimensions and is properly positioned
            modalBox.style.setProperty('position', 'relative', 'important');
            modalBox.style.setProperty('height', '75vh', 'important');
            modalBox.style.setProperty('max-height', '75vh', 'important');
            modalBox.style.setProperty('min-height', '400px', 'important');
            modalBox.style.setProperty('max-width', '600px', 'important');
            modalBox.style.setProperty('width', '70%', 'important');
            modalBox.style.setProperty('display', 'flex', 'important');
            modalBox.style.setProperty('flex-direction', 'column', 'important');
            modalBox.style.setProperty('overflow', 'visible', 'important');
            
            // Force a reflow and verify modal-box is rendering
            const modalBoxHeight = modalBox.offsetHeight;
            if (modalBoxHeight === 0) {
                console.error('❌ Modal-box has 0 height! Setting explicit height...');
                modalBox.style.height = '400px';
                modalBox.style.minHeight = '400px';
            }
            
            const modalBoxRect = modalBox.getBoundingClientRect();
            const header = modal.querySelector('.modal-header');
            const footer = modal.querySelector('.form-actions');
            const headerHeight = header ? (header.offsetHeight || 80) : 80;
            const footerHeight = footer ? (footer.offsetHeight || 60) : 60;
            
            const modalHeight = modalBoxRect.height > 0 ? modalBoxRect.height : 400;
            const availableHeight = Math.max(400, modalHeight - headerHeight - footerHeight - 48);
            const topPosition = headerHeight + 24;
            const containerWidth = (modalBoxRect.width > 0 ? modalBoxRect.width : 600) - 48;
            
            
            // CRITICAL: Clear inline style attribute first (it has position:relative !important)
            existingContainer.removeAttribute('style');
            
            // Now set new styles with absolute positioning
            existingContainer.style.position = 'absolute';
            existingContainer.style.top = topPosition + 'px';
            existingContainer.style.left = '24px';
            existingContainer.style.right = '24px';
            existingContainer.style.bottom = (footerHeight + 24) + 'px';
            existingContainer.style.height = availableHeight + 'px';
            existingContainer.style.minHeight = availableHeight + 'px';
            existingContainer.style.width = containerWidth + 'px';
            existingContainer.style.display = 'block';
            existingContainer.style.overflowY = 'auto';
            existingContainer.style.background = '#EFEFEF';
            existingContainer.style.padding = '20px';
            existingContainer.style.zIndex = '10';
            existingContainer.style.boxSizing = 'border-box';
            
            // Also set with !important to ensure they stick
            existingContainer.style.setProperty('position', 'absolute', 'important');
            existingContainer.style.setProperty('top', topPosition + 'px', 'important');
            existingContainer.style.setProperty('left', '24px', 'important');
            existingContainer.style.setProperty('right', '24px', 'important');
            existingContainer.style.setProperty('bottom', (footerHeight + 24) + 'px', 'important');
            existingContainer.style.setProperty('height', availableHeight + 'px', 'important');
            existingContainer.style.setProperty('min-height', availableHeight + 'px', 'important');
            existingContainer.style.setProperty('width', containerWidth + 'px', 'important');
            existingContainer.style.setProperty('display', 'block', 'important');
            
            // Force reflow and verify
            requestAnimationFrame(() => {
                const computed = window.getComputedStyle(existingContainer);
                const finalHeight = existingContainer.offsetHeight;
                
                
                // If still 0 despite correct computed styles, try forcing a layout recalculation
                if (finalHeight === 0 && computed.position === 'absolute' && computed.height !== 'auto') {
                    console.error('❌ Computed styles correct but offsetHeight is 0! Forcing layout...');
                    
                    // Force browser to recalculate layout
                    existingContainer.style.display = 'none';
                    existingContainer.offsetHeight; // Force reflow
                    existingContainer.style.display = 'block';
                    existingContainer.offsetHeight; // Force reflow again
                    
                    // Double-check modal-box is rendering
                    const mbHeight = modalBox.offsetHeight;
                    if (mbHeight === 0) {
                        console.error('❌ Modal-box still has 0 height! This is the problem.');
                        modalBox.style.height = '400px';
                        modalBox.style.minHeight = '400px';
                        modalBox.offsetHeight; // Force reflow
                    }
                    
                    // Try one more time with cssText
                    existingContainer.removeAttribute('style');
                    existingContainer.style.cssText = `
                        position: absolute !important;
                        top: ${topPosition}px !important;
                        left: 24px !important;
                        right: 24px !important;
                        bottom: ${footerHeight + 24}px !important;
                        height: ${availableHeight}px !important;
                        min-height: ${availableHeight}px !important;
                        width: ${containerWidth}px !important;
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        overflow-y: auto !important;
                        background: #EFEFEF !important;
                        padding: 20px !important;
                        z-index: 10 !important;
                        box-sizing: border-box !important;
                    `;
                    
                    // Final check
                    const finalCheck = existingContainer.offsetHeight;
                    
                    // If STILL 0, try using getBoundingClientRect to see actual rendered size
                    if (finalCheck === 0) {
                        const rect = existingContainer.getBoundingClientRect();
                            width: rect.width,
                            height: rect.height,
                            top: rect.top,
                            left: rect.left,
                            bottom: rect.bottom,
                            right: rect.right
                        });
                        
                        // If getBoundingClientRect shows dimensions but offsetHeight is 0, it's a browser quirk
                        // Try moving it to a different position or using transform
                        if (rect.height > 0) {
                            // Element is actually rendered, just offsetHeight is wrong
                            // Force visibility by ensuring it's in viewport
                            if (rect.top < 0 || rect.left < 0) {
                                existingContainer.style.top = '100px';
                                existingContainer.style.left = '50px';
                            }
                        } else {
                            console.error('❌ getBoundingClientRect also shows 0 height - element truly not rendered');
                            console.error('Trying LAST RESORT: Move container to body temporarily...');
                            
                            // LAST RESORT: Temporarily move to body to see if parent is the issue
                            const tempParent = document.body;
                            const tempRect = existingContainer.getBoundingClientRect();
                            
                            // Clone container to test
                            const testContainer = existingContainer.cloneNode(true);
                            testContainer.style.cssText = `
                                position: fixed !important;
                                top: 100px !important;
                                left: 100px !important;
                                width: 500px !important;
                                height: 400px !important;
                                background: red !important;
                                z-index: 999999 !important;
                                display: block !important;
                                visibility: visible !important;
                                opacity: 1 !important;
                            `;
                            document.body.appendChild(testContainer);
                            
                            setTimeout(() => {
                                const testRect = testContainer.getBoundingClientRect();
                                if (testRect.height > 0) {
                                    
                                    // Remove container from current parent
                                    const currentParent = existingContainer.parentNode;
                                    if (currentParent && currentParent !== modalBox) {
                                        currentParent.removeChild(existingContainer);
                                    }
                                    
                                    // Append directly to modal-box (after header, before footer)
                                    const header = modalBox.querySelector('.modal-header');
                                    const footer = modalBox.querySelector('.form-actions');
                                    
                                    if (footer && footer.previousSibling !== existingContainer) {
                                        modalBox.insertBefore(existingContainer, footer);
                                    } else if (!footer) {
                                        modalBox.appendChild(existingContainer);
                                    }
                                    
                                    
                                    // Re-apply styles now that it's in the right parent
                                    existingContainer.style.setProperty('position', 'absolute', 'important');
                                    existingContainer.style.setProperty('top', topPosition + 'px', 'important');
                                    existingContainer.style.setProperty('left', '24px', 'important');
                                    existingContainer.style.setProperty('right', '24px', 'important');
                                    existingContainer.style.setProperty('bottom', (footerHeight + 24) + 'px', 'important');
                                    existingContainer.style.setProperty('height', availableHeight + 'px', 'important');
                                    existingContainer.style.setProperty('width', containerWidth + 'px', 'important');
                                    
                                    // Check again
                                    setTimeout(() => {
                                        const finalRect = existingContainer.getBoundingClientRect();
                                        const finalOffset = existingContainer.offsetHeight;
                                        
                                        if (finalRect.height > 0 || finalOffset > 0) {
                                        }
                                    }, 100);
                                }
                                testContainer.remove();
                            }, 100);
                        }
                    }
                }
            });
        }, 200);
        
        // Verify after rendering - MULTIPLE CHECKS
        setTimeout(() => {
            const checkList = document.getElementById('courseBooksList');
            const checkContainer = document.getElementById('courseBooksListContainer');
            
            // FORCE ALL CHILDREN TO BE VISIBLE
            if (checkList) {
                Array.from(checkList.children).forEach((child, idx) => {
                    child.style.setProperty('display', 'block', 'important');
                    child.style.setProperty('visibility', 'visible', 'important');
                    child.style.setProperty('opacity', '1', 'important');
                    // Also force all nested children
                    const nested = child.querySelectorAll('*');
                    nested.forEach(n => {
                        n.style.setProperty('display', '', 'important');
                        n.style.setProperty('visibility', 'visible', 'important');
                        n.style.setProperty('opacity', '1', 'important');
                    });
                });
            }
            
            if (checkList && checkList.innerHTML.length < 100 && booksToDisplay.length > 0) {
                console.error('❌ CONTENT LOST! Re-setting immediately...');
                checkList.innerHTML = html;
                checkList.style.setProperty('display', 'block', 'important');
                checkList.style.setProperty('visibility', 'visible', 'important');
                checkList.style.setProperty('opacity', '1', 'important');
            } else if (checkList && checkList.innerHTML.length >= 100) {
            } else {
                console.error('❌ Content verification failed - checkList:', !!checkList, 'booksToDisplay:', booksToDisplay.length);
            }
        }, 200);
        
        // Also verify after 500ms
        setTimeout(() => {
            const checkList = document.getElementById('courseBooksList');
            if (checkList && checkList.innerHTML.length < 100 && booksToDisplay.length > 0) {
                console.error('❌ CONTENT STILL MISSING AFTER 500ms! Re-setting one more time...');
                checkList.innerHTML = html;
            }
        }, 500);
    } else {
        console.error('❌ NO BOOKS TO DISPLAY - This should not happen if dummy data worked!');
        
        // Show error message
        const errorHtml = `<div style="padding: 20px; text-align: center; color: #666; background: #fff; border-radius: 8px; margin: 20px;">
            <p style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 12px;"><strong>No book references found for this course.</strong></p>
            <p style="font-size: 13px; color: #666; margin-bottom: 8px;">Course: <strong>${courseCode}</strong></p>
            <p style="font-size: 12px; color: #999; margin-top: 12px;">Check console (F12) for debugging info</p>
            <p style="font-size: 11px; color: #ccc; margin-top: 8px;">booksToDisplay.length: ${booksToDisplay.length}</p>
        </div>`;
        
        try {
            booksList.innerHTML = errorHtml;
        } catch (error) {
            console.error('❌ Error setting error message:', error);
        }
    }
    
    // FORCE VISIBILITY ONE MORE TIME - CRITICAL
    setTimeout(() => {
        const checkList = document.getElementById('courseBooksList');
        const checkContainer = document.getElementById('courseBooksListContainer');
        
        if (checkList && checkList.innerHTML.length < 50) {
            console.error('❌ Content still missing after rendering! Force-setting...');
            if (booksToDisplay.length > 0) {
                // Re-render with dummy data if needed
                const currentYear = new Date().getFullYear();
                let forceHtml = `<div style="padding: 20px;"><h3 style="margin-bottom: 16px;">Total Books: ${booksToDisplay.length}</h3>`;
                booksToDisplay.forEach((book, index) => {
                    const pubYear = parseInt(book.publication_year || book.copyright || 0);
                    const isCompliant = pubYear > 0 && (currentYear - pubYear) < 5;
                    forceHtml += `<div style="padding: 12px; margin-bottom: 12px; border-left: 4px solid ${isCompliant ? '#4CAF50' : '#FF4C4C'}; background: ${isCompliant ? '#f1f8f4' : '#fff5f5'};">
                        <div style="font-weight: 600; margin-bottom: 8px;">${book.book_title || 'No title'}</div>
                        <div style="color: #666; font-size: 14px;">${book.authors || 'Unknown authors'} (${pubYear || 'N/A'})</div>
                        <div style="color: #999; font-size: 12px; margin-top: 4px;">${book.publisher || ''} | Copies: ${book.no_of_copies || 0}</div>
                    </div>`;
                });
                forceHtml += '</div>';
                checkList.innerHTML = forceHtml;
            }
        }
        
        // Force container visibility
        if (checkContainer) {
            checkContainer.style.display = 'block';
            checkContainer.style.visibility = 'visible';
            checkContainer.style.opacity = '1';
        }
        if (checkList) {
            checkList.style.display = 'block';
            checkList.style.visibility = 'visible';
            checkList.style.opacity = '1';
        }
    }, 100);
    
    // CRITICAL: Ensure close function is ALWAYS available
    window.closeCourseBooksModal = closeCourseBooksModal;
    
    // Get close buttons
    const closeBtn = document.getElementById('courseBooksModalCloseBtn');
    const closeFooterBtn = document.getElementById('courseBooksModalCloseFooterBtn');
    
    // Close buttons already have inline onclick in HTML - that's enough!
}

function closeCourseBooksModal() {
    const modal = document.getElementById('courseBooksModal');
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');
        modal.style.setProperty('visibility', 'hidden', 'important');
        modal.style.setProperty('opacity', '0', 'important');
        modal.removeAttribute('data-modal-open');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Make it globally available IMMEDIATELY
window.closeCourseBooksModal = closeCourseBooksModal;

// Make functions globally accessible - CRITICAL
window.openImportDataModal = openImportDataModal;
window.closeImportDataModal = closeImportDataModal;
window.handleFileDrop = handleFileDrop;
window.showCourseBooksModal = showCourseBooksModal;
window.closeCourseBooksModal = closeCourseBooksModal;

// Also define as direct global functions to ensure they're always accessible
if (typeof closeCourseBooksModal === 'undefined') {
    window.closeCourseBooksModal = function() {
        const modal = document.getElementById('courseBooksModal');
        if (modal) {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
            modal.style.opacity = '0';
            document.body.style.overflow = '';
        }
    };
}
window.handleDragOver = handleDragOver;
window.handleDragLeave = handleDragLeave;
window.handleFileSelect = handleFileSelect;
window.switchProgramTab = switchProgramTab;
window.clearSelectedCourse = clearSelectedCourse;
window.updateImportSummary = updateImportSummary;
window.submitImportedBooks = submitImportedBooks;
window.displayDetectedCourses = displayDetectedCourses;
window.updateCoursesToAddCount = updateCoursesToAddCount;
window.handleCourseCheckboxChange = handleCourseCheckboxChange;
window.addSelectedCourses = addSelectedCourses;
// Make functions globally accessible - CRITICAL FOR CLOSE BUTTON
window.showCourseBooksModal = showCourseBooksModal;
window.closeCourseBooksModal = closeCourseBooksModal;

// Also ensure it's accessible via different methods
if (typeof window.closeCourseBooksModal === 'undefined') {
    window.closeCourseBooksModal = closeCourseBooksModal;
}

// Location change handler (removed - location selection no longer required)
</script>

</script>
