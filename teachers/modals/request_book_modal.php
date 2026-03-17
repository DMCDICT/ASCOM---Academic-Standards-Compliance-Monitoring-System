<?php
// request_book_modal.php
// Modal for requesting books/reference materials (Teacher)
?>

<div id="requestBookModal" class="modal-overlay" style="display: none; overflow: hidden !important; overflow-y: hidden !important; overflow-x: hidden !important;">
  <div class="modal-box" style="max-width: 700px;">
    <div class="modal-header">
      <h2>Request for Book / Reference Material</h2>
      <span class="close-button" onclick="closeRequestBookModal()">&times;</span>
    </div>
    
    <div class="modal-content">
      <p style="margin-bottom: 20px; color: #666;">Fill in the details below to submit a material request to the Department Dean for review.</p>
      
      <form id="requestBookForm" class="form-grid">
        <div class="form-group">
          <label for="book_title">Book Title <span style="color: red;">*</span></label>
          <input type="text" name="book_title" id="book_title" required placeholder="Enter book title">
        </div>
        
        <div class="form-group">
          <label for="author">Author(s) <span style="color: red;">*</span></label>
          <input type="text" name="author" id="author" required placeholder="Enter author name(s)">
        </div>
        
        <div class="form-group">
          <label for="edition">Edition <span style="color: red;">*</span></label>
          <input type="text" name="edition" id="edition" required placeholder="e.g., 3rd Edition">
        </div>
        
        <div class="form-group">
          <label for="publication_year">Publication Year <span style="color: red;">*</span></label>
          <input type="text" name="publication_year" id="publication_year" required placeholder="e.g., 2023" maxlength="4">
        </div>
        
        <div class="form-group">
          <label for="publisher">Publisher <span style="color: red;">*</span></label>
          <input type="text" name="publisher" id="publisher" required placeholder="Enter publisher name">
        </div>
        
        <div class="form-group">
          <label for="material_type">Material Type <span style="color: red;">*</span></label>
          <select name="material_type" id="material_type" required>
            <option value="">Select material type</option>
            <option value="Textbook">Textbook</option>
            <option value="Reference Book">Reference Book</option>
            <option value="Supplementary Material">Supplementary Material</option>
            <option value="Digital Material">Digital Material</option>
            <option value="Journal">Journal</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="course_code_title">Course Code & Title <span style="color: red;">*</span></label>
          <input type="text" name="course_code_title" id="course_code_title" required placeholder="e.g., CS 101 - Introduction to Programming">
        </div>
        
        <div class="form-group">
          <label for="supporting_file">Upload Supporting File (Optional)</label>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <label for="supporting_file" style="padding: 10px 20px; background-color: #C9C9C9; color: black; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; font-family: 'TT Interphases', sans-serif; text-transform: uppercase; display: inline-block; margin: 0;">
              Choose File
            </label>
            <input type="file" name="supporting_file" id="supporting_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="handleFileSelect(this)" style="display: none;">
            <span id="fileName" style="font-size: 14px; color: #333; font-weight: 500; flex: 1; min-width: 150px; display: none;"></span>
            <button type="button" class="file-view-btn" onclick="viewSelectedFile()" style="padding: 8px 16px; background-color: #1976d2; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; font-family: 'TT Interphases', sans-serif; display: none;">View</button>
            <button type="button" class="file-remove-btn" onclick="removeSelectedFile()" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; font-family: 'TT Interphases', sans-serif; display: none;">Remove</button>
          </div>
          <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">e.g., book cover, sample pages, TOC</small>
        </div>
        
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="justification">Justification / Purpose of Material <span style="color: red;">*</span></label>
          <textarea name="justification" id="justification" required rows="4" placeholder="Example: 'This book aligns with Course Outcome 2 and supports lectures on Catholic theological methods.'"></textarea>
        </div>
      </form>
    </div>
    
    <div class="modal-footer">
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="closeRequestBookModal()">CANCEL</button>
        <button type="submit" class="create-btn" id="submitRequestBtn" form="requestBookForm">SUBMIT REQUEST</button>
      </div>
    </div>
  </div>
</div>

<style>
/* Request Book Modal Styling - Prevent duplicate scrollbars */
/* Override global modal-overlay overflow with maximum specificity */
.modal-overlay#requestBookModal,
#requestBookModal.modal-overlay,
div#requestBookModal.modal-overlay {
    overflow: hidden !important;
    overflow-y: hidden !important;
    overflow-x: hidden !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Default hidden state - but allow inline style to override */
#requestBookModal:not([style*="flex"]) {
    display: none !important;
}

#requestBookModal {
    overflow: hidden !important;
    overflow-y: hidden !important;
    overflow-x: hidden !important;
}

/* Ensure no scrolling on any parent */
body:has(#requestBookModal[style*="flex"]),
html:has(#requestBookModal[style*="flex"]) {
    overflow: hidden !important;
}

#requestBookModal .modal-box {
    position: relative !important;
    background-color: #EFEFEF !important;
    padding: 0 !important;
    border-radius: 15px !important;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    border: 1px solid #888 !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    max-height: 85vh !important;
    height: 85vh !important;
    margin: auto !important;
    width: 90% !important;
    max-width: 700px !important;
}

#requestBookModal .modal-header {
    position: sticky !important;
    top: 0 !important;
    background-color: #EFEFEF !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-bottom: 1px solid #e5e5e5 !important;
    padding: 25px 25px 15px 25px !important;
    margin-bottom: 0 !important;
    z-index: 10 !important;
    flex-shrink: 0 !important;
}

#requestBookModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
    padding: 25px !important;
    background-color: #EFEFEF !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    width: auto !important;
    max-width: none !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
    -webkit-overflow-scrolling: touch !important;
}

#requestBookModal .form-grid {
    overflow: visible !important;
    display: block !important;
}

#requestBookModal .modal-content::-webkit-scrollbar {
    width: 8px;
}

#requestBookModal .modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#requestBookModal .modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#requestBookModal .modal-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

#requestBookModal .modal-footer {
    position: sticky !important;
    bottom: 0 !important;
    background-color: #EFEFEF !important;
    border-top: 1px solid #e5e5e5 !important;
    padding: 0 !important;
    z-index: 10 !important;
    flex-shrink: 0 !important;
}

#requestBookModal .modal-header h2 {
    margin: 0 !important;
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #333 !important;
}

#requestBookModal .close-button {
    color: #aaa !important;
    font-size: 28px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    line-height: 1 !important;
    padding: 0 !important;
    background: none !important;
    border: none !important;
    width: 30px !important;
    height: 30px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#requestBookModal .close-button:hover {
    color: #000 !important;
}

#requestBookModal .form-group {
    margin-bottom: 20px;
}

#requestBookModal .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

#requestBookModal .form-group input[type="text"],
#requestBookModal .form-group input[type="file"],
#requestBookModal .form-group select,
#requestBookModal .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    box-sizing: border-box;
}

#requestBookModal .form-group input[type="text"]:focus,
#requestBookModal .form-group select:focus,
#requestBookModal .form-group textarea:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

#requestBookModal .form-group textarea {
    resize: vertical;
    min-height: 100px;
}

#requestBookModal .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding: 20px 25px;
    margin: 0;
}

#requestBookModal .cancel-btn,
#requestBookModal .create-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    font-family: 'TT Interphases', sans-serif;
    transition: background-color 0.3s ease;
    height: 50px;
}

#requestBookModal .cancel-btn {
    width: 125px;
    background-color: #C9C9C9;
    color: black;
}

#requestBookModal .cancel-btn:hover {
    background-color: #B9B9B9;
}

#requestBookModal .create-btn {
    width: auto;
    min-width: 180px;
    padding: 10px 30px;
    background-color: #0f7a53;
    color: white;
    white-space: nowrap;
    text-align: center;
}

#requestBookModal .create-btn:hover {
    background-color: #0a5f42;
}

#requestBookModal .create-btn:disabled {
    background-color: #A5A5A5;
    color: black;
    cursor: not-allowed;
}

#requestBookModal .create-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

/* File preview styling */
#requestBookModal .file-view-btn:hover {
    background-color: #1565c0 !important;
}

#requestBookModal .file-remove-btn:hover {
    background-color: #c82333 !important;
}
</style>

<script>
let selectedFile = null;

function handleFileSelect(input) {
  const file = input.files[0];
  if (file) {
    selectedFile = file;
    const fileName = document.getElementById('fileName');
    const viewBtn = document.querySelector('.file-view-btn');
    const removeBtn = document.querySelector('.file-remove-btn');
    
    fileName.textContent = file.name;
    fileName.style.display = 'inline-block';
    if (viewBtn) viewBtn.style.display = 'inline-block';
    if (removeBtn) removeBtn.style.display = 'inline-block';
  } else {
    removeSelectedFile();
  }
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function viewSelectedFile() {
  if (!selectedFile) return;
  
  const fileUrl = URL.createObjectURL(selectedFile);
  
  // Check file type to determine how to view it
  const fileType = selectedFile.type;
  if (fileType === 'application/pdf' || fileType.startsWith('image/')) {
    // Open PDF or images in new tab
    window.open(fileUrl, '_blank');
  } else {
    // For other file types, trigger download
    const a = document.createElement('a');
    a.href = fileUrl;
    a.download = selectedFile.name;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }
  
  // Clean up the object URL after a delay
  setTimeout(() => URL.revokeObjectURL(fileUrl), 100);
}

function removeSelectedFile() {
  const fileInput = document.getElementById('supporting_file');
  const fileName = document.getElementById('fileName');
  const viewBtn = document.querySelector('.file-view-btn');
  const removeBtn = document.querySelector('.file-remove-btn');
  
  fileInput.value = '';
  selectedFile = null;
  if (fileName) fileName.style.display = 'none';
  if (viewBtn) viewBtn.style.display = 'none';
  if (removeBtn) removeBtn.style.display = 'none';
}

function openRequestBookModal() {
  const modal = document.getElementById('requestBookModal');
  modal.style.display = 'flex';
  modal.style.overflow = 'hidden';
  modal.style.overflowY = 'hidden';
  modal.style.overflowX = 'hidden';
  document.getElementById('requestBookForm').reset();
  removeSelectedFile(); // Clear file preview when opening modal
  document.body.style.overflow = 'hidden';
  document.documentElement.style.overflow = 'hidden';
}

function closeRequestBookModal() {
  document.getElementById('requestBookModal').style.display = 'none';
  document.body.style.overflow = '';
  document.documentElement.style.overflow = '';
  document.getElementById('requestBookForm').reset();
  removeSelectedFile(); // Clear file preview when closing modal
}

// Ensure modal is hidden on page load
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('requestBookModal');
  if (modal) {
    modal.style.display = 'none';
  }
  
  // Initialize form handler
  initializeRequestBookModal();
});

function initializeRequestBookModal() {
  if (window.requestBookModalInitialized) {
    return;
  }
  window.requestBookModalInitialized = true;
  
  const requestBookForm = document.getElementById('requestBookForm');
  if (requestBookForm) {
    requestBookForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const submitBtn = document.getElementById('submitRequestBtn');
      const originalText = submitBtn.textContent;
      
      // Disable button and show loading
      submitBtn.disabled = true;
      submitBtn.textContent = 'SUBMITTING...';
      
      // Get form data
      const formData = new FormData(requestBookForm);
      
      try {
        const response = await fetch('api/submit_book_request.php', {
          method: 'POST',
          body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
          alert('Book request submitted successfully!');
          closeRequestBookModal();
          // Optionally refresh the page or update the requests list
          if (typeof refreshBookRequests === 'function') {
            refreshBookRequests();
          }
        } else {
          alert('Failed to submit request: ' + (data.error || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error submitting book request:', error);
        alert('An error occurred while submitting the request. Please try again.');
      } finally {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }
  
  // Close modal when clicking outside
  const requestBookModal = document.getElementById('requestBookModal');
  if (requestBookModal) {
    requestBookModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeRequestBookModal();
      }
    });
  }
}
</script>

