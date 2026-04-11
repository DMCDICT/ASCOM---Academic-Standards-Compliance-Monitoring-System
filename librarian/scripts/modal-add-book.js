/*
 * modal-add-book.js
 * Contains JavaScript functionality for the Add Book modal.
 */

// UNIVERSAL MODAL FUNCTIONS - Works for ALL modals
function preventBackgroundScroll() {
    
    const scrollY = window.scrollY;
    
    // Apply styles with !important to override any conflicting CSS
    document.body.style.setProperty('position', 'fixed', 'important');
    document.body.style.setProperty('top', `-${scrollY}px`, 'important');
    document.body.style.setProperty('left', '0', 'important');
    document.body.style.setProperty('right', '0', 'important');
    document.body.style.setProperty('overflow', 'hidden', 'important');
    document.body.style.setProperty('height', '100vh', 'important');
    document.body.style.setProperty('width', '100%', 'important');
    
    document.documentElement.style.setProperty('overflow', 'hidden', 'important');
    
    window.scrollPosition = scrollY;
}

function restoreBackgroundScroll() {
    
    // Remove all the fixed positioning styles
    document.body.style.removeProperty('position');
    document.body.style.removeProperty('top');
    document.body.style.removeProperty('left');
    document.body.style.removeProperty('right');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('height');
    document.body.style.removeProperty('width');
    
    document.documentElement.style.removeProperty('overflow');
    
    if (window.scrollPosition !== undefined) {
        window.scrollTo(0, window.scrollPosition);
    }
}

// Modal functions
// Make sure the function is globally available
window.openAddBookModal = function() {
    
    // Show modal
    const modal = document.getElementById('addBookModal');
    
    if (modal) {
        modal.style.display = 'flex';
        modal.style.setProperty('overflow', 'hidden', 'important');
        
        // USE THE EXACT SAME FUNCTION AS THE TEST BUTTONS
        if (typeof lockPageScroll === 'function') {
            lockPageScroll();
        } else {
            // Fallback
            document.body.style.setProperty('overflow', 'hidden', 'important');
            document.body.style.setProperty('position', 'fixed', 'important');
            document.body.style.setProperty('width', '100%', 'important');
            document.body.style.setProperty('height', '100%', 'important');
            document.body.style.setProperty('top', '0', 'important');
            document.body.style.setProperty('left', '0', 'important');
            document.documentElement.style.setProperty('overflow', 'hidden', 'important');
        }
        
        
        // CHECK IF SOMETHING IS OVERRIDING - Wait 1 second and check again
        setTimeout(function() {
            
            if (document.body.style.overflow !== 'hidden') {
                if (typeof lockPageScroll === 'function') {
                    lockPageScroll();
                }
            }
        }, 1000);
    } else {
    }
    
    // Reset form
    document.getElementById('addBookForm').reset();
    
    // Setup copyright year input (needs to be called after reset)
    if (typeof window.setupCopyrightYearInput === 'function') {
        window.setupCopyrightYearInput();
    }
    
    // FORCE DISABLE SUBMIT BUTTON INITIALLY
    const submitBtn = document.querySelector('.create-btn');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.style.pointerEvents = 'none';
        submitBtn.style.backgroundColor = '#6c757d';
    } else {
        const altBtn = document.querySelector('button[type="submit"]');
    }
    
    // Initialize autocomplete
    initCourseAutocomplete();
    
    // Initialize form validation - EXACT SAME AS SWITCH ROLE MODAL
    setTimeout(function() {
        
        // DISABLE BUTTON IMMEDIATELY (SAME AS SWITCH ROLE MODAL)
        const button = document.getElementById('addBookBtn');
        if (button) {
            button.disabled = true;
            button.setAttribute('disabled', 'disabled');
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            button.style.pointerEvents = 'none';
            button.style.backgroundColor = '#6c757d';
        }
        
        // Add event listeners to all form fields - FORCE IMMEDIATE VALIDATION
        const formFields = ['course_search', 'course_id', 'call_no', 'book_title', 'copyright', 'edition', 'authors', 'publisher', 'isbn', 'no_of_copies', 'location'];
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Add multiple event types to ensure it triggers
                field.addEventListener('input', function() {
                    validateAddBookButton();
                });
                field.addEventListener('change', function() {
                    validateAddBookButton();
                });
                field.addEventListener('keyup', function() {
                    validateAddBookButton();
                });
            }
        });
        
        // Force validation after a short delay (SAME AS SWITCH ROLE MODAL)
        setTimeout(() => {
            validateAddBookButton();
        }, 100);
    }, 100);
}

// Make sure the function is globally available
window.closeAddBookModal = function() {
    const modal = document.getElementById('addBookModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Restore body scroll - WITH !important TO OVERRIDE ANY CONFLICTING CSS
        document.body.style.setProperty('overflow', '', 'important');
        document.body.style.setProperty('position', '', 'important');
        document.body.style.setProperty('width', '', 'important');
        document.body.style.setProperty('height', '', 'important');
        document.body.style.setProperty('top', '', 'important');
        document.body.style.setProperty('left', '', 'important');
        
        // Also restore html element
        document.documentElement.style.setProperty('overflow', '', 'important');
        
    }
}

// Safety: Restore scroll on page unload
window.addEventListener('beforeunload', function() {
    document.body.classList.remove('modal-open');
    document.documentElement.classList.remove('modal-open');
});

// NUCLEAR OPTION - Override any conflicting scroll settings
setInterval(function() {
    // Check if ANY modal is open
    const modals = document.querySelectorAll('.modal-overlay[style*="flex"]');
    if (modals.length > 0) {
        // Force the styles every 50ms to override any conflicting changes
        document.body.style.setProperty('overflow', 'hidden', 'important');
        document.body.style.setProperty('position', 'fixed', 'important');
        document.body.style.setProperty('width', '100%', 'important');
        document.body.style.setProperty('height', '100%', 'important');
        document.body.style.setProperty('top', '0', 'important');
        document.body.style.setProperty('left', '0', 'important');
        document.body.style.setProperty('right', '0', 'important');
        document.body.style.setProperty('bottom', '0', 'important');
        document.documentElement.style.setProperty('overflow', 'hidden', 'important');
        
        // Also try to hide scrollbars on all possible elements
        const allElements = document.querySelectorAll('*');
        allElements.forEach(el => {
            if (el.style.overflow === 'auto' || el.style.overflow === 'scroll') {
                el.style.setProperty('overflow', 'hidden', 'important');
            }
        });
    }
}, 50);

function closeAddBookSuccessModal() {
    document.getElementById('addBookSuccessModal').style.display = 'none';
}

function closeAddBookErrorModal() {
    document.getElementById('addBookErrorModal').style.display = 'none';
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const addBookForm = document.getElementById('addBookForm');
    
    if (addBookForm) {
        // Get all form inputs
        const formInputs = addBookForm.querySelectorAll('input, select');
        const submitBtn = addBookForm.querySelector('.create-btn');
        
        // VALIDATION FUNCTION DISABLED - HANDLED BY INLINE SCRIPT IN MODAL
        // function checkFormValidity() {
        //     const courseId = document.getElementById('course_id').value;
        //     const callNo = document.getElementById('call_no').value.trim();
        //     const isbn = document.getElementById('isbn').value.trim();
        //     const bookTitle = document.getElementById('book_title').value.trim();
        //     const noOfCopies = document.getElementById('no_of_copies').value;
        //     const copyright = document.getElementById('copyright').value;
        //     const authors = document.getElementById('authors').value.trim();
        //     const publisher = document.getElementById('publisher').value.trim();
        //     
        //     // Check if all required fields are filled
        //     const isFormValid = courseId && callNo && isbn && bookTitle && noOfCopies && copyright && authors && publisher;
        //     
        //     // Enable/disable submit button
        //     if (submitBtn) {
        //         submitBtn.disabled = !isFormValid;
        //         if (isFormValid) {
        //             submitBtn.style.opacity = '1';
        //             submitBtn.style.cursor = 'pointer';
        //         } else {
        //             submitBtn.style.opacity = '0.6';
        //             submitBtn.style.cursor = 'not-allowed';
        //         }
        //     }
        // }
        
        // EVENT LISTENERS DISABLED - HANDLED BY INLINE SCRIPT IN MODAL
        // formInputs.forEach(input => {
        //     input.addEventListener('input', checkFormValidity);
        //     input.addEventListener('change', checkFormValidity);
        // });
        
        // Initial check disabled
        // checkFormValidity();
        
        // FORM SUBMISSION HANDLER DISABLED - HANDLED BY INLINE SCRIPT IN MODAL
        // addBookForm.addEventListener('submit', function(e) {
        //     e.preventDefault();
        //     // ... validation and submission code moved to modal_add_book.php
        // });
    }
});

// FUNCTION DISABLED - HANDLED BY INLINE SCRIPT IN MODAL
// function submitAddBookForm(formData) {
//     // ... submission code moved to modal_add_book.php
// }

function showAddBookSuccess(message) {
    document.getElementById('addBookSuccessMessage').textContent = message;
    document.getElementById('addBookSuccessModal').style.display = 'flex';
}

function showAddBookError(message) {
    document.getElementById('addBookErrorMessage').textContent = message;
    document.getElementById('addBookErrorModal').style.display = 'flex';
}

function updateTotalBooksCount() {
    // This function can be used to update the Total Books count on the dashboard
    // For now, we'll just increment the displayed number
    const totalBooksElement = document.querySelector('.dashboard-container .stat-card:first-child .stat-number');
    if (totalBooksElement) {
        const currentCount = parseInt(totalBooksElement.textContent.replace(/,/g, ''));
        totalBooksElement.textContent = (currentCount + 1).toLocaleString();
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const addBookModal = document.getElementById('addBookModal');
    const addBookSuccessModal = document.getElementById('addBookSuccessModal');
    const addBookErrorModal = document.getElementById('addBookErrorModal');
    
    if (event.target === addBookModal) {
        closeAddBookModal();
    }
    if (event.target === addBookSuccessModal) {
        closeAddBookSuccessModal();
    }
    if (event.target === addBookErrorModal) {
        closeAddBookErrorModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddBookModal();
        closeAddBookSuccessModal();
        closeAddBookErrorModal();
    }
});

// Course Autocomplete Function
function initCourseAutocomplete() {
    const searchInput = document.getElementById('course_search');
    const courseIdInput = document.getElementById('course_id');
    const suggestionsPanel = document.getElementById('suggestionsPanel');
    let currentSuggestions = [];
    let selectedIndex = -1;


    if (!searchInput || !courseIdInput || !suggestionsPanel) {
        return;
    }

    if (!coursesData || coursesData.length === 0) {
        return;
    }

    // Show all courses initially
    showSuggestions(coursesData);

    // Handle input typing
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length === 0) {
            showSuggestions(coursesData);
        } else {
            const filtered = coursesData.filter(course => 
                course.code.toLowerCase().includes(query) || 
                course.title.toLowerCase().includes(query) ||
                course.display.toLowerCase().includes(query)
            );
            showSuggestions(filtered);
        }
        selectedIndex = -1;
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (!suggestionsPanel.classList.contains('show')) return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
                updateSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && currentSuggestions[selectedIndex]) {
                    selectCourse(currentSuggestions[selectedIndex]);
                }
                break;
            case 'Escape':
                hideSuggestions();
                break;
        }
    });

    // Handle focus
    searchInput.addEventListener('focus', function() {
        showSuggestions(coursesData);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsPanel.contains(e.target)) {
            hideSuggestions();
        }
    });

    function showSuggestions(suggestions) {
        currentSuggestions = suggestions.slice(0, 10); // Limit to 10 items
        suggestionsPanel.innerHTML = '';
        
        if (currentSuggestions.length === 0) {
            hideSuggestions();
            return;
        }

        currentSuggestions.forEach((course, index) => {
            const item = document.createElement('button');
            item.className = 'suggestion-item';
            item.textContent = course.display;
            item.addEventListener('click', () => selectCourse(course));
            suggestionsPanel.appendChild(item);
        });

        suggestionsPanel.classList.add('show');
        
        // Force visibility for debugging
        suggestionsPanel.style.display = 'block';
        suggestionsPanel.style.border = '2px solid blue';
        suggestionsPanel.style.background = '#f0f0f0';
    }

    function updateSelection() {
        const items = suggestionsPanel.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === selectedIndex);
        });
    }

    function selectCourse(course) {
        searchInput.value = course.display;
        courseIdInput.value = course.id;
        hideSuggestions();
        
        // Trigger validation immediately
        validateAddBookButton();
    }

    function hideSuggestions() {
        suggestionsPanel.classList.remove('show');
        selectedIndex = -1;
    }
}

// EXACT SAME APPROACH AS WORKING SWITCH ROLE MODAL
function validateAddBookButton() {
    
    // Get all required field values with detailed logging
    const courseSearch = document.getElementById('course_search');
    const courseId = document.getElementById('course_id');
    const callNo = document.getElementById('call_no');
    const bookTitle = document.getElementById('book_title');
    const copyright = document.getElementById('copyright');
    const authors = document.getElementById('authors');
    const publisher = document.getElementById('publisher');
    
        courseSearch: courseSearch,
        courseId: courseId,
        callNo: callNo,
        bookTitle: bookTitle,
        copyright: copyright,
        authors: authors,
        publisher: publisher
    });
    
    const courseSearchValue = courseSearch?.value?.trim() || '';
    const courseIdValue = courseId?.value?.trim() || '';
    const callNoValue = callNo?.value?.trim() || '';
    const bookTitleValue = bookTitle?.value?.trim() || '';
    const copyrightValue = copyright?.value?.trim() || '';
    const authorsValue = authors?.value?.trim() || '';
    const publisherValue = publisher?.value?.trim() || '';
    
        courseSearch: courseSearchValue,
        courseId: courseIdValue,
        callNo: callNoValue,
        bookTitle: bookTitleValue,
        copyright: copyrightValue,
        authors: authorsValue,
        publisher: publisherValue
    });
    
    // Find the button using ID (SAME AS SWITCH ROLE MODAL)
    const button = document.getElementById('addBookBtn');
    
    // Check if all required fields are filled
    const allFilled = courseSearchValue && courseIdValue && callNoValue && bookTitleValue && copyrightValue && authorsValue && publisherValue;
    
        courseSearch: !!courseSearchValue,
        courseId: !!courseIdValue,
        callNo: !!callNoValue,
        bookTitle: !!bookTitleValue,
        copyright: !!copyrightValue,
        authors: !!authorsValue,
        publisher: !!publisherValue
    });
    
    if (button) {
        if (allFilled) {
            // ENABLE BUTTON (SAME AS SWITCH ROLE MODAL)
            button.disabled = false;
            button.removeAttribute('disabled');
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
            button.style.pointerEvents = 'auto';
            button.style.backgroundColor = '#0f7a53';
        } else {
            // DISABLE BUTTON (SAME AS SWITCH ROLE MODAL)
            button.disabled = true;
            button.setAttribute('disabled', 'disabled');
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            button.style.pointerEvents = 'none';
            button.style.backgroundColor = '#6c757d';
        }
    } else {
    }
}

// Make it globally accessible
window.validateAddBookButton = validateAddBookButton;

// Test if function is available




