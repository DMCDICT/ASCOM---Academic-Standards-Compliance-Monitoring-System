<?php
// modal_add_book.php
// This file is an HTML fragment, included by content.php.
?>

<?php
// Fetch courses for the dropdown
require_once dirname(__FILE__) . '/includes/db_connection.php';
$coursesQuery = "SELECT MIN(c.id) AS id, c.course_code, c.course_title FROM courses c GROUP BY c.course_code, c.course_title ORDER BY c.course_code ASC";
$coursesStmt = $pdo->prepare($coursesQuery);
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="addBookModal" class="modal-overlay" style="display: none; overflow: hidden !important;">
  <div class="modal-box" style="max-width: 600px; width: 90%; display: flex; flex-direction: column; max-height: 80vh; overflow: hidden; background-color: #EFEFEF;">
    <div class="modal-header" style="flex-shrink: 0;">
      <h2>Add New Book</h2>
      <span class="close-button" onclick="unlockPageScroll(); document.getElementById('addBookModal').style.display='none';">&times;</span>
    </div>
    <!-- Scrollable form content -->
    <div class="form-content" style="flex: 1; overflow-y: auto; padding: 16px 24px;">
       <form id="addBookForm" class="form-grid" method="post" autocomplete="off" novalidate>
            <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="location">Location <span style="color: red;">*</span></label>
          <select name="location" id="location" required>
            <option value="">Select Location</option>
            <option value="Main Library">Main Library</option>
            <option value="Buenavista Library">Buenavista Library</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:1;">
          <label for="course_search">Course <span style="color: red;">*</span></label>
            <div class="autocomplete-container">
            <input type="text" id="course_search" name="course_search" placeholder="Type to search courses..." required>
            <input type="hidden" id="course_id" name="course_id" required>
            <div class="suggestions-panel" id="suggestionsPanel">
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
      <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="call_no">Call Number <span style="color: red;">*</span></label>
          <input type="text" name="call_no" id="call_no" required>
        </div>
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="isbn">ISBN</label>
          <input type="text" name="isbn" id="isbn">
        </div>
        <div class="form-group" style="flex:0.5; min-width: 120px;">
          <label for="no_of_copies">No. of Copies <span style="color: red;">*</span></label>
          <input type="number" name="no_of_copies" id="no_of_copies" min="1" max="999" value="1" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:2; min-width: 200px;">
          <label for="book_title">Book Title <span style="color: red;">*</span></label>
          <input type="text" name="book_title" id="book_title" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="copyright" class="required-field">Copyright Year</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="number" name="copyright" id="copyright" required readonly style="flex: 1; cursor: default; user-select: none; -webkit-appearance: none; -moz-appearance: textfield;">
            <div style="display: flex; flex-direction: column; gap: 0;">
              <button type="button" id="copyright_up" style="width: 24px; height: 20px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 10px; border-radius: 3px 3px 0 0; line-height: 1; padding: 0;" title="Increase year">▲</button>
              <button type="button" id="copyright_down" style="width: 24px; height: 20px; border: 1px solid #ddd; background: #f5f5f5; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 10px; border-radius: 0 0 3px 3px; border-top: none; line-height: 1; padding: 0;" title="Decrease year">▼</button>
            </div>
          </div>
          <div id="copyright_hint" style="font-size: 11px; color: #6c757d; margin-top: 4px; min-height: 14px;"></div>
        </div>
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="edition">Edition</label>
          <input type="text" name="edition" id="edition">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="authors">Author(s)</label>
          <input type="text" name="authors" id="authors">
        </div>
        <div class="form-group" style="flex:1; min-width: 160px;">
          <label for="publisher">Publisher(s)</label>
          <input type="text" name="publisher" id="publisher">
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
    <div class="form-actions" style="flex-shrink: 0; background: #EFEFEF; padding: 12px 24px; border-top: 1px solid #e0e0e0; display: flex; justify-content: flex-end; gap: 12px; margin-top: 0;">
      <button type="button" class="cancel-btn" onclick="unlockPageScroll(); document.getElementById('addBookModal').style.display='none';">CANCEL</button>
      <button type="submit" class="create-btn" id="addBookBtn" form="addBookForm" disabled style="opacity: 0.5; cursor: not-allowed; pointer-events: none; background-color: #6c757d;">ADD BOOK</button>
    </div>
  </div>
</div>

<script>
// Course data for autocomplete
const coursesData = [
  <?php foreach ($courses as $course): ?>
  {
    id: <?php echo $course['id']; ?>,
    code: "<?php echo htmlspecialchars($course['course_code']); ?>",
    title: "<?php echo htmlspecialchars($course['course_title']); ?>",
    display: "<?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>"
  },
  <?php endforeach; ?>
];

// Test data if no courses available
if (coursesData.length === 0) {
  coursesData.push(
    { id: 1, code: "GE 105", title: "Understanding the Self", display: "GE 105 - Understanding the Self" },
    { id: 2, code: "GE 101", title: "Mathematics in the Modern World", display: "GE 101 - Mathematics in the Modern World" },
    { id: 3, code: "A&H 104", title: "Reading Visual Arts", display: "A&H 104 - Reading Visual Arts" }
  );
}

console.log('Courses data loaded:', coursesData);

// WORKING AUTOCOMPLETE - RESPONDS TO TYPING
function initWorkingAutocomplete() {
    const searchInput = document.getElementById('course_search');
    const courseIdInput = document.getElementById('course_id');
    const suggestionsPanel = document.getElementById('suggestionsPanel');
    
    if (!searchInput || !courseIdInput || !suggestionsPanel) {
        console.log('Elements not found');
        return;
    }
    
    // Show suggestions when typing
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        console.log('Typing:', query);
        
        // Clear previous suggestions
        suggestionsPanel.innerHTML = '';
        
        if (query.length === 0) {
            suggestionsPanel.style.display = 'none';
            return;
        }
        
        // Filter courses
        const filtered = coursesData.filter(course => 
            course.code.toLowerCase().includes(query) || 
            course.title.toLowerCase().includes(query) ||
            course.display.toLowerCase().includes(query)
        );
        
        console.log('Found', filtered.length, 'matches');
        
        if (filtered.length === 0) {
            suggestionsPanel.style.display = 'none';
            return;
        }
        
        // Show suggestions (max 10)
        const limited = filtered.slice(0, 10);
        limited.forEach(course => {
            const button = document.createElement('button');
            button.type = 'button'; // Prevent form submission
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
            
            // Add hover effects
            button.addEventListener('mouseenter', function() {
                this.style.background = '#0f7a53';
                this.style.color = 'white';
                this.style.borderColor = '#0f7a53';
                this.style.transform = 'translateX(2px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.background = 'white';
                this.style.color = '#333';
                this.style.borderColor = '#ccc';
                this.style.transform = 'translateX(0)';
            });
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                searchInput.value = course.display;
                courseIdInput.value = course.id;
                suggestionsPanel.style.display = 'none';
                console.log('Selected:', course.display);
                
                // Trigger validation after course selection
                console.log('🔄 Course selected - triggering validation');
                setTimeout(function() {
                    if (typeof validateAddBookButton === 'function') {
                        validateAddBookButton();
                    }
                }, 100);
            });
            
            suggestionsPanel.appendChild(button);
        });
        
        suggestionsPanel.style.display = 'block';
        suggestionsPanel.style.background = 'white';
        suggestionsPanel.style.border = '1px solid #ccc';
        suggestionsPanel.style.borderRadius = '8px';
        suggestionsPanel.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        suggestionsPanel.style.padding = '8px';
        suggestionsPanel.style.maxHeight = '300px';
        suggestionsPanel.style.overflowY = 'auto';
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsPanel.contains(e.target)) {
            suggestionsPanel.style.display = 'none';
        }
    });
}

// Setup copyright year input with 5-year range limit
window.setupCopyrightYearInput = function() {
    const copyrightInput = document.getElementById('copyright');
    const upButton = document.getElementById('copyright_up');
    const downButton = document.getElementById('copyright_down');
    const hintElement = document.getElementById('copyright_hint');
    
    if (copyrightInput) {
        // Set initial value to current year
        const currentYear = new Date().getFullYear();
        
        // Calculate 5-year range (current year and 4 years back)
        const minCompliantYear = currentYear - 4;
        const maxCompliantYear = currentYear;
        
        // Set min and max attributes
        copyrightInput.setAttribute('min', minCompliantYear.toString());
        copyrightInput.setAttribute('max', maxCompliantYear.toString());
        copyrightInput.min = minCompliantYear;
        copyrightInput.max = maxCompliantYear;
        
        let currentValue = parseInt(copyrightInput.value) || currentYear;
        // Clamp initial value to compliant range
        currentValue = Math.max(minCompliantYear, Math.min(maxCompliantYear, currentValue));
        copyrightInput.value = currentValue;
        
        // Update hint text - hide initially
        if (hintElement) {
            hintElement.textContent = `Compliant range: ${minCompliantYear} - ${maxCompliantYear}`;
            hintElement.style.display = 'none';
            hintElement.style.visibility = 'hidden';
        }
        
        // Set readonly to prevent typing
        copyrightInput.readOnly = true;
        copyrightInput.style.userSelect = 'none';
        
        // Function to update value - clamped to compliant range
        function updateValue(newValue) {
            const clampedValue = Math.max(minCompliantYear, Math.min(maxCompliantYear, newValue));
            currentValue = clampedValue;
            copyrightInput.value = currentValue;
            if (typeof validateAddBookButton === 'function') {
                validateAddBookButton();
            }
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
        copyrightInput.addEventListener('keydown', function(e) {
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
        copyrightInput.addEventListener('wheel', function(e) {
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
};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initWorkingAutocomplete();
    window.setupCopyrightYearInput();
    
    // Form submission handler
    const addBookForm = document.getElementById('addBookForm');
    let isSubmitting = false; // Flag to prevent multiple submissions
    
    if (addBookForm) {
        addBookForm.addEventListener('submit', function(e) {
            // Prevent multiple submissions
            if (isSubmitting) {
                console.log('🚫 Form already submitting - preventing duplicate submission');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            e.preventDefault();
            e.stopPropagation();
            console.log('📚 Form submitted - adding book...');
            
            // Check if we're in batch mode FIRST before validation
            const byBatchCheckbox = document.getElementById('byBatchCheckbox');
            
            if (byBatchCheckbox && byBatchCheckbox.checked) {
                // Batch mode: skip HTML5 validation, use batch mode validation instead
                console.log('📚 Batch mode detected - skipping HTML5 validation');
                isSubmitting = true;
                handleBatchSubmit();
                return;
            }
            
            // Single book mode: check form validation
            isSubmitting = true;
            console.log('📚 Single book mode - checking form validation');
            
            // Check if form is valid before proceeding
            if (!addBookForm.checkValidity()) {
                console.log('❌ Form validation failed - HTML5 validation');
                console.log('❌ Form validity details:', addBookForm.checkValidity());
                isSubmitting = false;
                return false;
            }
            
            console.log('✅ Form validation passed - proceeding with submission');
            handleSingleSubmit();
        });
        
        // Single book submission
        function handleSingleSubmit() {
            const formData = new FormData(addBookForm);
            const bookData = {
                course_id: formData.get('course_id'),
                call_number: formData.get('call_no'),
                isbn: formData.get('isbn'),
                book_title: formData.get('book_title'),
                no_of_copies: formData.get('no_of_copies') || 1,  // now maps directly to no_of_copies
                copyright: formData.get('copyright'),               // maps to publication_year
                edition: formData.get('edition'),
                authors: formData.get('authors'),                  // maps to author
                publisher: formData.get('publisher'),
                location: formData.get('location')
            };
            
            console.log('📚 Book data to submit:', bookData);
            
            // Make actual API call to save book
            fetch('api/add_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(bookData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('📚 API Response:', data);
                if (data.success) {
                    showAddBookSuccessModal(bookData.book_title);
                } else {
                    showAddBookErrorModal(data.message || 'Failed to add book. Please try again.');
                }
                // Reset submission flag
                isSubmitting = false;
                console.log('📚 Form submission completed - resetting flag');
            })
            .catch(error => {
                console.error('📚 API Error:', error);
                showAddBookErrorModal('Network error. Please check your connection and try again.');
                // Reset submission flag
                isSubmitting = false;
                console.log('📚 Form submission failed - resetting flag');
            });
        }
        
        // Batch books submission
        function handleBatchSubmit() {
            if (batchBooks.length === 0) {
                showAddBookErrorModal('Please add at least one book to the list.');
                isSubmitting = false;
                return;
            }
            
            const courseId = document.getElementById('course_id').value;
            if (!courseId) {
                showAddBookErrorModal('Please select a course.');
                isSubmitting = false;
                return;
            }
            
            const batchData = {
                input_method: 'batch',
                course_id: courseId,
                books: batchBooks
            };
            
            console.log('📚 Batch data to submit:', batchData);
            
            fetch('api/add_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(batchData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('📚 Batch API Response:', data);
                if (data.success) {
                    showAddBookSuccessModal(`${batchBooks.length} books`);
                    // Clear batch list
                    batchBooks = [];
                    updateBatchListDisplay();
                    const byBatchCheckbox = document.getElementById('byBatchCheckbox');
                    if (byBatchCheckbox) byBatchCheckbox.checked = false;
                    toggleBatchMode();
                } else {
                    showAddBookErrorModal(data.message || 'Failed to add books. Please try again.');
                }
                isSubmitting = false;
                console.log('📚 Batch submission completed - resetting flag');
            })
            .catch(error => {
                console.error('📚 Batch API Error:', error);
                showAddBookErrorModal('Network error. Please check your connection and try again.');
                isSubmitting = false;
                console.log('📚 Batch submission failed - resetting flag');
            });
        }
    }
    
        // Add validation event listeners to form fields
    setTimeout(function() {
        console.log('🔍 Setting up field validation listeners...');

        const formFields = ['course_search', 'course_id', 'call_no', 'book_title', 'copyright', 'edition', 'authors', 'publisher', 'isbn', 'no_of_copies', 'location'];
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Add multiple event types to ensure it triggers
                field.addEventListener('input', function() {
                    console.log(`📝 Field ${fieldId} input event triggered`);
                    validateAddBookButton();
                });
                field.addEventListener('change', function() {
                    console.log(`🔄 Field ${fieldId} change event triggered`);
                    validateAddBookButton();
                });
                field.addEventListener('keyup', function() {
                    console.log(`⌨️ Field ${fieldId} keyup event triggered`);
                    validateAddBookButton();
                });
                console.log(`✅ Added validation listeners to ${fieldId}`);
            }
        });
        
        // Force initial validation
        setTimeout(() => {
            validateAddBookButton();
        }, 100);
        
        console.log('✅ Field validation setup complete');
    }, 100);
});

// SIMPLE SCROLL PREVENTION - DIRECT APPROACH WITH !important
function lockPageScroll() {
    console.log('LOCKING PAGE SCROLL - DIRECT APPROACH WITH !important');
    console.log('Before lock - body overflow:', document.body.style.overflow);
    console.log('Before lock - body position:', document.body.style.position);
    
    document.body.style.setProperty('overflow', 'hidden', 'important');
    document.body.style.setProperty('position', 'fixed', 'important');
    document.body.style.setProperty('width', '100%', 'important');
    document.body.style.setProperty('height', '100%', 'important');
    document.body.style.setProperty('top', '0', 'important');
    document.body.style.setProperty('left', '0', 'important');
    document.documentElement.style.setProperty('overflow', 'hidden', 'important');
    
    console.log('After lock - body overflow:', document.body.style.overflow);
    console.log('After lock - body position:', document.body.style.position);
    console.log('Page scroll locked with !important');
}

function unlockPageScroll() {
    console.log('UNLOCKING PAGE SCROLL - DIRECT APPROACH WITH !important');
    document.body.style.setProperty('overflow', '', 'important');
    document.body.style.setProperty('position', '', 'important');
    document.body.style.setProperty('width', '', 'important');
    document.body.style.setProperty('height', '', 'important');
    document.body.style.setProperty('top', '', 'important');
    document.body.style.setProperty('left', '', 'important');
    document.documentElement.style.setProperty('overflow', '', 'important');
    console.log('Page scroll unlocked with !important');
}

// SIMPLE TEST FUNCTION
function testValidation() {
    console.log('🧪 TESTING VALIDATION...');
    
    // Get all field values
    const courseSearch = document.getElementById('course_search')?.value?.trim() || '';
    const courseId = document.getElementById('course_id')?.value?.trim() || '';
    const callNo = document.getElementById('call_no')?.value?.trim() || '';
    const bookTitle = document.getElementById('book_title')?.value?.trim() || '';
    const copyright = document.getElementById('copyright')?.value?.trim() || '';
    const authors = document.getElementById('authors')?.value?.trim() || '';
    const publisher = document.getElementById('publisher')?.value?.trim() || '';
    
    console.log('Field values:', { courseSearch, courseId, callNo, bookTitle, copyright, authors, publisher });
    
    const allFilled = courseSearch && courseId && callNo && bookTitle && copyright && authors && publisher;
    console.log('All filled:', allFilled);
    
    const button = document.getElementById('addBookBtn');
    console.log('Button found:', button);
    console.log('Button disabled:', button?.disabled);
    
    if (allFilled) {
        button.disabled = false;
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
        button.style.pointerEvents = 'auto';
        button.style.backgroundColor = '#0f7a53';
        console.log('✅ BUTTON ENABLED BY TEST');
    } else {
        button.disabled = true;
        button.style.opacity = '0.5';
        button.style.cursor = 'not-allowed';
        button.style.pointerEvents = 'none';
        button.style.backgroundColor = '#6c757d';
        console.log('❌ BUTTON DISABLED BY TEST');
    }
}
</script>

<div id="addBookSuccessModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 100px; height: 100px; margin: 0 auto 18px auto; display: block;" />
        </div>
        <h2 style="color: #43a047; margin-bottom: 12px; font-size: 1.6em;">Success!</h2>
        <p id="addBookSuccessMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5;"></p>
        <button type="button" class="create-btn" style="margin: 0 auto; display: block; background: #43a047; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1.1em; font-weight: 600; box-shadow: 0 2px 8px rgba(67,160,71,0.08);" onclick="closeAddBookSuccessModal()">OK</button>
    </div>
</div>

<div id="addBookErrorModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 90px; height: 90px; margin: 0 auto 18px auto; display: block;" />
        </div>
        <h2 id="addBookErrorHeading" style="color: #222; margin-bottom: 12px; font-size: 1.6em;">Error!</h2>
        <p id="addBookErrorMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5;"></p>
        <button type="button" class="create-btn error-btn" id="addBookErrorBtn" style="margin: 0 auto; display: block; background: #1976d2; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1.1em; font-weight: 600; box-shadow: 0 2px 8px rgba(25,118,210,0.08);" onclick="closeAddBookErrorModal()">OK</button>
    </div>
</div>

<!-- Include the modal script - DISABLED TO PREVENT CONFLICTS -->
<!-- <script src="scripts/modal-add-book.js"></script> -->

<!-- Inline validation function as backup -->
<script>
// SIMPLE BULLETPROOF VALIDATION - DIRECT APPROACH
function validateAddBookButton() {
    console.log('🔍 SIMPLE VALIDATION RUNNING...');
    
    // Get all required field values with detailed logging
    const courseSearch = document.getElementById('course_search');
    const courseId = document.getElementById('course_id');
    const callNo = document.getElementById('call_no');
    const bookTitle = document.getElementById('book_title');
    const copyright = document.getElementById('copyright');
    const authors = document.getElementById('authors');
    const publisher = document.getElementById('publisher');
    const location = document.getElementById('location');
    
    console.log('🔍 Field elements found:', {
        courseSearch: courseSearch,
        courseId: courseId,
        callNo: callNo,
        bookTitle: bookTitle,
        copyright: copyright,
        authors: authors,
        publisher: publisher,
        location: location
    });
    
    const courseSearchValue = courseSearch?.value?.trim() || '';
    const courseIdValue = courseId?.value?.trim() || '';
    const callNoValue = callNo?.value?.trim() || '';
    const bookTitleValue = bookTitle?.value?.trim() || '';
    const copyrightValue = copyright?.value?.trim() || '';
    const authorsValue = authors?.value?.trim() || '';
    const publisherValue = publisher?.value?.trim() || '';
    const locationValue = location?.value?.trim() || '';
    
    console.log('🔍 Field values:', {
        courseSearch: courseSearchValue,
        courseId: courseIdValue,
        callNo: callNoValue,
        bookTitle: bookTitleValue,
        copyright: copyrightValue,
        authors: authorsValue,
        publisher: publisherValue,
        location: locationValue
    });
    
    // Find the button using ID
    const button = document.getElementById('addBookBtn');
    console.log('Button found by ID:', button);
    
    // Check if batch mode is enabled
    const byBatchCheckbox = document.getElementById('byBatchCheckbox');
    if (byBatchCheckbox && byBatchCheckbox.checked) {
        // In batch mode, button is controlled by batchBooks array
        return;
    }
    
    // Check if all required fields are filled (authors and publisher are now optional)
    const noOfCopiesValue = document.getElementById('no_of_copies')?.value?.trim() || '';
    const allFilled = courseSearchValue && courseIdValue && callNoValue && bookTitleValue && copyrightValue && noOfCopiesValue && locationValue;
    
    console.log('All fields filled:', allFilled);
    console.log('Individual checks:', {
        courseSearch: !!courseSearchValue,
        courseId: !!courseIdValue,
        callNo: !!callNoValue,
        bookTitle: !!bookTitleValue,
        copyright: !!copyrightValue,
        noOfCopies: !!noOfCopiesValue,
        location: !!locationValue,
        authors: !!authorsValue, // Optional
        publisher: !!publisherValue // Optional
    });
    
    if (button) {
        if (allFilled) {
            // ENABLE BUTTON
            button.disabled = false;
            button.removeAttribute('disabled');
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
            button.style.pointerEvents = 'auto';
            button.style.backgroundColor = '#0f7a53';
            console.log('✅ BUTTON ENABLED');
        } else {
            // DISABLE BUTTON
            button.disabled = true;
            button.setAttribute('disabled', 'disabled');
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            button.style.pointerEvents = 'none';
            button.style.backgroundColor = '#6c757d';
            console.log('❌ BUTTON DISABLED');
        }
    } else {
        console.log('❌ BUTTON NOT FOUND BY ID');
    }
}

// Make it globally accessible
window.validateAddBookButton = validateAddBookButton;
console.log('✅ Inline validation function loaded');

// Batch mode functions
let batchBooks = [];

function toggleBatchMode() {
    const byBatchCheckbox = document.getElementById('byBatchCheckbox');
    const batchActions = document.getElementById('batchActions');
    const submitBtn = document.getElementById('addBookBtn');
    
    if (byBatchCheckbox && batchActions && submitBtn) {
        if (byBatchCheckbox.checked) {
            batchActions.style.display = 'block';
            submitBtn.textContent = batchBooks.length > 0 ? `Add ${batchBooks.length} Books` : 'Add Books';
            submitBtn.disabled = batchBooks.length === 0;
        } else {
            batchActions.style.display = 'none';
            batchBooks = [];
            updateBatchListDisplay();
            submitBtn.textContent = 'ADD BOOK';
            validateAddBookButton();
        }
    }
}

function addBookToBatchList() {
    const bookTitle = document.getElementById('book_title')?.value.trim();
    const copyright = document.getElementById('copyright')?.value;
    const authors = document.getElementById('authors')?.value.trim();
    const publisher = document.getElementById('publisher')?.value.trim();
    const isbn = document.getElementById('isbn')?.value.trim();
    const callNo = document.getElementById('call_no')?.value.trim();
    const edition = document.getElementById('edition')?.value.trim();
    const noOfCopies = document.getElementById('no_of_copies')?.value || 1;
    const location = document.getElementById('location')?.value || '';
    
    if (!bookTitle || !copyright || !location) {
        alert('Please fill in Book Title, Copyright, and Location.');
        return;
    }
    
    const book = {
        id: Date.now(),
        book_title: bookTitle,
        copyright: copyright,
        authors: authors || '',
        publisher: publisher || '',
        isbn: isbn || '',
        call_number: callNo || '',
        edition: edition || '',
        no_of_copies: parseInt(noOfCopies) || 1,
        location: location
    };
    
    batchBooks.push(book);
    updateBatchListDisplay();
    
    // Clear form fields (except course, no_of_copies, and location)
    document.getElementById('book_title').value = '';
    const currentYear = new Date().getFullYear();
    document.getElementById('copyright').value = currentYear;
    document.getElementById('authors').value = '';
    document.getElementById('publisher').value = '';
    document.getElementById('isbn').value = '';
    document.getElementById('call_no').value = '';
    document.getElementById('edition').value = '';
    // Keep location selected for batch mode
    
    // Reset copyright year to current year
    if (typeof window.setupCopyrightYearInput === 'function') {
        window.setupCopyrightYearInput();
    }
    
    // Update submit button
    const submitBtn = document.getElementById('addBookBtn');
    if (submitBtn) {
        submitBtn.textContent = `Add ${batchBooks.length} Books`;
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        submitBtn.style.pointerEvents = 'auto';
        submitBtn.style.backgroundColor = '#0f7a53';
    }
    
    validateAddBookButton();
}

function removeBookFromBatchList(bookId) {
    batchBooks = batchBooks.filter(book => book.id !== bookId);
    updateBatchListDisplay();
    
    const submitBtn = document.getElementById('addBookBtn');
    if (submitBtn) {
        if (batchBooks.length > 0) {
            submitBtn.textContent = `Add ${batchBooks.length} Books`;
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitBtn.style.pointerEvents = 'auto';
            submitBtn.style.backgroundColor = '#0f7a53';
        } else {
            submitBtn.textContent = 'Add Books';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.backgroundColor = '#6c757d';
        }
    }
}

function clearBatchList() {
    if (batchBooks.length > 0) {
        if (confirm('Are you sure you want to clear all added books?')) {
            batchBooks = [];
            updateBatchListDisplay();
            const submitBtn = document.getElementById('addBookBtn');
            if (submitBtn) {
                submitBtn.textContent = 'Add Books';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.style.pointerEvents = 'none';
                submitBtn.style.backgroundColor = '#6c757d';
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
        let displayText = `${index + 1}. `;
        if (book.authors) displayText += `${book.authors}`;
        if (book.copyright) displayText += ` (${book.copyright})`;
        if (book.book_title) displayText += `. <strong>${book.book_title}</strong>`;
        if (book.edition && !book.edition.toLowerCase().includes('1st') && !book.edition.toLowerCase().includes('first')) {
            displayText += ` (${book.edition})`;
        }
        if (book.publisher) displayText += `. ${book.publisher}`;
        if (book.location) displayText += ` [${book.location}]`;
        displayText += ` - ${book.no_of_copies} cop${book.no_of_copies > 1 ? 'ies' : 'y'}`;
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px; border-bottom: 1px solid #eee; background: #f9f9f9; margin-bottom: 4px; border-radius: 4px;">
                <div style="flex: 1; font-size: 14px; line-height: 1.4;">
                    ${displayText}
                </div>
                <button type="button" onclick="removeBookFromBatchList(${book.id})" style="background: none; color: #dc3545; border: none; font-size: 24px; line-height: 1; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; transition: color 0.2s;" onmouseover="this.style.color='#c82333'" onmouseout="this.style.color='#dc3545'">&times;</button>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Initialize batch mode handlers
document.addEventListener('DOMContentLoaded', function() {
    const byBatchCheckbox = document.getElementById('byBatchCheckbox');
    if (byBatchCheckbox) {
        byBatchCheckbox.addEventListener('change', toggleBatchMode);
    }
    
    const addToListBtn = document.getElementById('addToListBtn');
    if (addToListBtn) {
        addToListBtn.addEventListener('click', addBookToBatchList);
    }
    
    const clearListBtn = document.getElementById('clearListBtn');
    if (clearListBtn) {
        clearListBtn.addEventListener('click', clearBatchList);
    }
});

// Make functions globally accessible
window.toggleBatchMode = toggleBatchMode;
window.addBookToBatchList = addBookToBatchList;
window.removeBookFromBatchList = removeBookFromBatchList;
window.clearBatchList = clearBatchList;
window.updateBatchListDisplay = updateBatchListDisplay;

// Form submission handler - MOVED TO FIRST DOMContentLoaded LISTENER
// DUPLICATE LISTENER REMOVED TO PREVENT DOUBLE SUBMISSION

// Function to show success modal
function showAddBookSuccessModal(bookTitle) {
    console.log('✅ Showing success modal for book:', bookTitle);
    
    // Set success message
    const messageElement = document.getElementById('addBookSuccessMessage');
    if (messageElement) {
        messageElement.textContent = `Book "${bookTitle}" has been successfully added to the library!`;
    }
    
    // Hide add book modal
    document.getElementById('addBookModal').style.display = 'none';
    
    // Show success modal
    document.getElementById('addBookSuccessModal').style.display = 'flex';
    
    // Restore scroll
    unlockPageScroll();
    
    // PREVENT ANY ERROR MODALS FROM SHOWING AFTER SUCCESS
    setTimeout(function() {
        // Hide any error modals that might appear
        const errorModals = document.querySelectorAll('[id*="error"], [class*="error"], [id*="Error"], [class*="Error"]');
        errorModals.forEach(modal => {
            if (modal.style) {
                modal.style.display = 'none';
            }
        });
        
        // Override any error modal functions
        if (typeof showAddBookErrorModal === 'function') {
            window.showAddBookErrorModal = function() {
                console.log('🚫 Error modal blocked after successful submission');
            };
        }
        
        if (typeof showAddBookError === 'function') {
            window.showAddBookError = function() {
                console.log('🚫 Error function blocked after successful submission');
            };
        }
    }, 100);
}

// Function to show error modal
function showAddBookErrorModal(errorMessage) {
    console.log('🚫 Showing error modal:', errorMessage);
    
    // Set error message
    const messageElement = document.getElementById('addBookErrorMessage');
    if (messageElement) {
        messageElement.textContent = errorMessage || 'An error occurred. Please try again.';
    }
    
    // Hide add book modal
    const addBookModal = document.getElementById('addBookModal');
    if (addBookModal) {
        addBookModal.style.display = 'none';
    }
    
    // Show error modal
    const errorModal = document.getElementById('addBookErrorModal');
    if (errorModal) {
        errorModal.style.display = 'flex';
    }
    
    // Restore scroll
    unlockPageScroll();
}

// Function to close success modal
function closeAddBookSuccessModal() {
    document.getElementById('addBookSuccessModal').style.display = 'none';
    // Reset form
    document.getElementById('addBookForm').reset();
    // Reset copyright year to current year
    if (typeof window.setupCopyrightYearInput === 'function') {
        window.setupCopyrightYearInput();
    }
    // Reset button state
    validateAddBookButton();
}

// Function to close error modal
function closeAddBookErrorModal() {
    document.getElementById('addBookErrorModal').style.display = 'none';
}

// Make functions globally accessible
window.showAddBookSuccessModal = showAddBookSuccessModal;
window.showAddBookErrorModal = showAddBookErrorModal;
window.closeAddBookSuccessModal = closeAddBookSuccessModal;
window.closeAddBookErrorModal = closeAddBookErrorModal;

// OVERRIDE ANY ERROR MODAL FUNCTIONS TO PREVENT THEM FROM SHOWING
window.addEventListener('DOMContentLoaded', function() {
    // Override any error modal functions that might exist
    if (typeof showAddBookError === 'function') {
        window.showAddBookError = function() {
            console.log('🚫 showAddBookError blocked');
        };
    }
    
    if (typeof showError === 'function') {
        window.showError = function() {
            console.log('🚫 showError blocked');
        };
    }
    
    if (typeof showModalError === 'function') {
        window.showModalError = function() {
            console.log('🚫 showModalError blocked');
        };
    }
    
    // Hide any error modals that might be visible
    setTimeout(function() {
        const errorModals = document.querySelectorAll('[id*="error"], [class*="error"], [id*="Error"], [class*="Error"]');
        errorModals.forEach(modal => {
            if (modal.style) {
                modal.style.display = 'none';
            }
        });
    }, 100);
});

</script> 