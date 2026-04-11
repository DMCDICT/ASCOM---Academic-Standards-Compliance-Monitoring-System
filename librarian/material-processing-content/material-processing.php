<?php
// This file is included within the librarian system
// No need to start session or check authentication as it's handled by the parent system

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get department information from session
$departmentCode = 'CCS'; // Default fallback
$departmentColor = '#C41E3A'; // Default red color
?>

<!-- Material Processing - View All Page -->
<div class="back-navigation">
    <button class="back-button" onclick="window.history.back()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back">
        Back to Dashboard
    </button>
</div>

    <div class="header-section">
        <div class="header-content">
            <h1 class="main-page-title">Material Processing</h1>
            <p class="page-description">View and manage all materials currently being processed for library cataloging</p>
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterMaterials('PROCESSING')">Processing</button>
            <button class="filter-btn" onclick="filterMaterials('COMPLETED')">Completed</button>
            <button class="filter-btn" onclick="filterMaterials('DRAFTED')">Drafted</button>
        </div>
    </div>

<div class="material-processing-container">
    <div class="material-processing-grid" id="materialProcessingGrid">
        <!-- Filtered materials will be displayed here -->
    </div>
</div>

<!-- Complete Cataloging Modal -->
<div id="completeCatalogingModal" style="display: none;">
    <div class="modal-overlay" onclick="closeCompleteCatalogingModal()"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 style="margin: 0; font-family: 'TT Interphases', sans-serif;">Complete Cataloging</h2>
            <button class="modal-close" onclick="closeCompleteCatalogingModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="completeCatalogingForm">
                <input type="hidden" id="completingBookId" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">Call Number <span style="color: red;">*</span></label>
                    <input type="text" id="callNumberInput" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">Number of Copies <span style="color: red;">*</span></label>
                    <input type="number" id="noOfCopiesInput" value="1" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">Location <span style="color: red;">*</span></label>
                    <select id="locationInput" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif;">
                        <option value="">Select Location</option>
                        <option value="Main Library">Main Library</option>
                        <option value="Buenavista Library">Buenavista Library</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeCompleteCatalogingModal()" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-family: 'TT Interphases', sans-serif; font-weight: 600;">Cancel</button>
                    <button type="submit" id="completeCatalogingBtn" disabled style="padding: 10px 20px; border: none; background: #6c757d; color: white; border-radius: 6px; cursor: not-allowed; font-family: 'TT Interphases', sans-serif; font-weight: 600; opacity: 0.5;">Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal for Cataloging Completion -->
<div id="catalogingSuccessModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 400px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px; position: relative;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="font-size: 48px;">✅</span>
            </div>
        </div>
        <h2 style="color: #4CAF50; margin-bottom: 12px; font-size: 1.5em; font-family: 'TT Interphases', sans-serif; margin-top: 0;">Success!</h2>
        <p id="catalogingSuccessMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1em; line-height: 1.5;"></p>
        <button type="button" onclick="closeCatalogingSuccessModal()" style="margin: 0 auto; display: block; background: #4CAF50; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1em; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">OK</button>
    </div>
</div>

<!-- Error Modal for Cataloging Completion -->
<div id="catalogingErrorModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 400px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px; position: relative;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: #ffebee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="font-size: 48px;">❌</span>
            </div>
        </div>
        <h2 style="color: #f44336; margin-bottom: 12px; font-size: 1.5em; font-family: 'TT Interphases', sans-serif; margin-top: 0;">Error</h2>
        <p id="catalogingErrorMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1em; line-height: 1.5;"></p>
        <button type="button" onclick="closeCatalogingErrorModal()" style="margin: 0 auto; display: block; background: #f44336; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1em; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">OK</button>
    </div>
</div>

<!-- Draft Request Modal -->
<div id="draftRequestModal" style="display: none;">
    <div class="modal-overlay" onclick="closeDraftRequestModal()"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 style="margin: 0; font-family: 'TT Interphases', sans-serif;">Draft Request</h2>
            <button class="modal-close" onclick="closeDraftRequestModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="draftRequestForm">
                <input type="hidden" id="draftingBookId" value="">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">Reason <span style="color: red;">*</span></label>
                    <textarea id="draftReasonInput" required rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif; resize: vertical;" placeholder="Enter reason for drafting this request (e.g., Out of stock, budget constraints, etc.)"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                    <button type="button" onclick="closeDraftRequestModal()" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; font-family: 'TT Interphases', sans-serif; font-weight: 600;">Cancel</button>
                    <button type="submit" style="padding: 10px 20px; border: none; background: #ff9800; color: white; border-radius: 6px; cursor: pointer; font-family: 'TT Interphases', sans-serif; font-weight: 600;">Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}

.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 9999;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 24px;
}

.modal-body label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.modal-body input[type="text"],
.modal-body input[type="number"],
.modal-body textarea,
.modal-body select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: 'TT Interphases', sans-serif;
}

.modal-body input:focus,
.modal-body textarea:focus,
.modal-body select:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}
</style>
<style>
    /* Back navigation styling to match All Courses page */
        .back-navigation {
            margin-bottom: 20px;
        }

        .back-button {
            background: #1976d2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            background: #1565c0;
        }

        .back-button img {
            width: 16px;
            height: 16px;
        }

    /* Header section with filter buttons */
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0px;
    }

    .header-content {
        flex: 1;
    }

    .main-page-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'TT Interphases', sans-serif;
    }

    .page-description {
        font-size: 14px;
        color: #666;
        margin: 5px 0 0px 0;
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.4;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 2px solid #e0e0e0;
        background: white;
        color: #666;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'TT Interphases', sans-serif;
        font-size: 14px;
    }

    .filter-btn:hover {
        border-color: #1976d2;
        color: #1976d2;
    }

    .filter-btn.active {
        background: #1976d2;
        border-color: #1976d2;
        color: white;
    }

    /* Container styling */
    .material-processing-container {
        max-width: none;
    }
    
    .material-processing-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 20px;
        margin-bottom: 40px;
        width: 100%;
    }

    /* Material card styling */
    .material-card {
        background: white;
        border-radius: 12px;
        padding: 20px 20px 10px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 280px;
        justify-content: space-between;
    }

    .material-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .material-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .requester-info {
        flex: 1;
    }

    .requester-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .requester-role {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
        font-family: 'TT Interphases', sans-serif;
    }

    .material-status {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-processing {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-completed {
        background: #e8f5e9;
        color: #4CAF50;
    }

    .status-drafted {
        background: #f5f5f5;
        color: #757575;
    }

    .course-info {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f5f5f5;
        padding: 8px 12px;
        border-radius: 6px;
    }

    .course-code {
        background: #1976d2;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
    }

    .course-name {
        font-size: 13px;
        color: #666;
        font-family: 'TT Interphases', sans-serif;
        flex: 1;
    }

    .request-summary {
        margin-bottom: 12px;
    }

    .request-type {
        font-size: 11px;
        color: #666;
        margin-bottom: 8px;
        font-family: 'TT Interphases', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f0f0f0;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    .material-title {
        font-size: 13px;
        color: #333;
        line-height: 1.5;
        font-style: italic;
        font-family: 'TT Interphases', sans-serif;
    }


    .material-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
        margin-bottom: 8px;
    }

    .action-btn {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        white-space: nowrap;
    }

    .catalog-btn {
        flex: 1.5;
    }

    .draft-btn {
        flex: 0.8;
        font-size: 11px;
    }

    .process-btn {
        background: #4caf50;
        color: white;
    }

    .process-btn:hover {
        background: #45a049;
    }

    .catalog-btn {
        background: #2196f3;
        color: white;
    }

    .catalog-btn:hover {
        background: #1976d2;
    }

    .draft-btn {
        background: #ff9800;
        color: white;
    }

    .draft-btn:hover {
        background: #f57c00;
    }

    .resume-btn {
        background: #1976d2;
        color: white;
    }

    .resume-btn:hover {
        background: #1565c0;
    }

    .request-date {
        font-size: 11px;
        color: #999;
        text-align: center;
        margin-top: 4px;
        margin-bottom: 4px;
    }


    /* Responsive design for different screen sizes */
    @media (max-width: 1200px) {
        .material-processing-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .material-card {
            min-width: 300px;
        }
    }
    
    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            gap: 20px;
        }

        .filter-buttons {
            flex-wrap: wrap;
        }

        .material-processing-grid {
            grid-template-columns: 1fr;
        }
        .material-card {
            min-width: 100%;
        }
    }
</style>

<script>
// Material Processing Data
let allMaterials = [];
let currentFilter = 'PROCESSING';

// Load all materials from API
async function loadAllMaterials() {
    try {
        
        // Get navigated material IDs from sessionStorage
        const navigatedMaterials = JSON.parse(sessionStorage.getItem('navigatedMaterials') || '[]');
        
        const response = await fetch('api/get_processing_materials.php?status=processing');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Load completed materials
            const completedResponse = await fetch('api/get_processing_materials.php?status=completed');
            const completedResult = await completedResponse.json();
            
            // Load drafted materials
            const draftedResponse = await fetch('api/get_processing_materials.php?status=drafted');
            const draftedResult = await draftedResponse.json();
            
            // Combine all materials
            allMaterials = [
                ...result.data,
                ...(completedResult.success ? completedResult.data : []),
                ...(draftedResult.success ? draftedResult.data : [])
            ];
            
            // Filter out navigated materials (completed ones that were navigated to)
            allMaterials = allMaterials.filter(material => {
                // Don't filter processing or drafted materials
                if (material.status !== 'completed') {
                    return true;
                }
                // For completed materials, exclude if they were navigated to
                return !navigatedMaterials.includes(material.id);
            });
            
            
            // Update filter button counts
            updateFilterCounts();
        } else {
            console.error('Failed to load materials:', result.message);
            allMaterials = [];
        }
        
        // Display filtered materials based on current filter
        displayFilteredMaterials(currentFilter);
    } catch (error) {
        console.error('Error loading materials:', error);
        allMaterials = [];
        displayFilteredMaterials(currentFilter);
    }
}

// Update filter button counts
function updateFilterCounts() {
    const processingCount = allMaterials.filter(m => m.status === 'processing').length;
    const completedCount = allMaterials.filter(m => m.status === 'completed').length;
    const draftedCount = allMaterials.filter(m => m.status === 'drafted').length;
    
    // Find and update button text
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(btn => {
        const text = btn.textContent.trim();
        if (text.startsWith('Processing')) {
            btn.textContent = `Processing (${processingCount})`;
        } else if (text.startsWith('Completed')) {
            btn.textContent = `Completed (${completedCount})`;
        } else if (text.startsWith('Drafted')) {
            btn.textContent = `Drafted (${draftedCount})`;
        }
    });
    
    // Re-apply active state based on currentFilter
    buttons.forEach(btn => {
        const text = btn.textContent.trim().toLowerCase();
        btn.classList.remove('active');
        if (currentFilter === 'PROCESSING' && text.startsWith('processing')) {
            btn.classList.add('active');
        } else if (currentFilter === 'COMPLETED' && text.startsWith('completed')) {
            btn.classList.add('active');
        } else if (currentFilter === 'DRAFTED' && text.startsWith('drafted')) {
            btn.classList.add('active');
        }
    });
}

// Display filtered materials
function displayFilteredMaterials(status) {
    const filteredMaterials = allMaterials.filter(material => material.status === status.toLowerCase());
    const grid = document.getElementById('materialProcessingGrid');
    
    if (grid) {
        grid.innerHTML = filteredMaterials.map(material => createMaterialCard(material)).join('');
    }
}

// Create material card HTML
function createMaterialCard(material) {
    const statusClass = `status-${material.status}`;
    
    // Get department color from material data
    const departmentColor = material.departmentColor || '#C41E3A';
    
    let actionButtons = '';
    if (material.status === 'processing') {
        actionButtons = `
            <button class="action-btn catalog-btn" onclick="startCataloging(${material.id})">Start Cataloging</button>
            <button class="action-btn draft-btn" onclick="openDraftRequestModal(${material.id})">Draft</button>
        `;
    } else if (material.status === 'completed') {
        actionButtons = `
            <button class="action-btn process-btn" onclick="navigateToCourseDetails('${material.courseCode}', ${material.id})">Navigate</button>
        `;
    } else if (material.status === 'drafted') {
        actionButtons = `
            <button class="action-btn resume-btn" onclick="resumeProcessing(${material.id})">Resume</button>
        `;
    }

    return `
        <div class="material-card" data-material-id="${material.id}" data-course-code="${material.courseCode}">
            <div class="material-header">
                <div class="requester-info">
                    <div class="requester-name">${material.requesterName}</div>
                    <div class="requester-role" style="color: ${departmentColor};">${material.requesterRole}</div>
                </div>
                <div class="material-status ${statusClass}">${material.status}</div>
            </div>
            
            <div class="course-info">
                <div class="course-code">${material.courseCode}</div>
                <div class="course-name">${material.courseName}</div>
            </div>
            
            <div class="request-summary">
                <div class="material-title">${material.materialTitle}</div>
            </div>
            
            
            <div class="material-actions">
                ${actionButtons}
            </div>
            
            <div class="request-date">Submitted: ${formatDate(material.requestDate)}</div>
        </div>
    `;
}

// Filter materials by status
function filterMaterials(status) {
    currentFilter = status;
    
    // Update filter buttons and maintain counts
    updateFilterCounts();
    event.target.classList.add('active');
    
    // Display filtered materials
    displayFilteredMaterials(status);
}



// Action functions
async function startCataloging(materialId) {
    // Fetch book reference data from database
    try {
        const response = await fetch(`api/get_book_reference.php?id=${materialId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const book = result.data;
            
            // Set the book ID
            document.getElementById('completingBookId').value = materialId;
            
            // Pre-fill form fields with existing values if they exist
            const callNumberInput = document.getElementById('callNumberInput');
            const noOfCopiesInput = document.getElementById('noOfCopiesInput');
            const locationInput = document.getElementById('locationInput');
            
            if (callNumberInput && book.call_number) {
                callNumberInput.value = book.call_number;
            }
            
            if (noOfCopiesInput && book.no_of_copies) {
                noOfCopiesInput.value = book.no_of_copies;
            }
            
            if (locationInput && book.location) {
                locationInput.value = book.location;
            }
        } else {
            // If fetch fails, still open modal but with empty fields
            document.getElementById('completingBookId').value = materialId;
        }
    } catch (error) {
        console.error('Error fetching book reference:', error);
        // Still open modal even if fetch fails
        document.getElementById('completingBookId').value = materialId;
    }
    
    // Open modal
    document.getElementById('completeCatalogingModal').style.display = 'block';
    
    // Setup validation for the Complete button
    setTimeout(function() {
        validateCompleteCatalogingButton();
        
        // Add event listeners to the three fields
        const callNumberInput = document.getElementById('callNumberInput');
        const noOfCopiesInput = document.getElementById('noOfCopiesInput');
        const locationInput = document.getElementById('locationInput');
        
        if (callNumberInput) {
            callNumberInput.addEventListener('input', validateCompleteCatalogingButton);
            callNumberInput.addEventListener('change', validateCompleteCatalogingButton);
        }
        if (noOfCopiesInput) {
            noOfCopiesInput.addEventListener('input', validateCompleteCatalogingButton);
            noOfCopiesInput.addEventListener('change', validateCompleteCatalogingButton);
        }
        if (locationInput) {
            locationInput.addEventListener('change', validateCompleteCatalogingButton);
        }
    }, 100);
}

// Validation function for Complete Cataloging button
function validateCompleteCatalogingButton() {
    const callNumber = document.getElementById('callNumberInput')?.value?.trim() || '';
    const noOfCopies = document.getElementById('noOfCopiesInput')?.value?.trim() || '';
    const location = document.getElementById('locationInput')?.value?.trim() || '';
    const completeBtn = document.getElementById('completeCatalogingBtn');
    
    const allFilled = callNumber && noOfCopies && location;
    
    if (completeBtn) {
        if (allFilled) {
            completeBtn.disabled = false;
            completeBtn.style.opacity = '1';
            completeBtn.style.cursor = 'pointer';
            completeBtn.style.background = '#4CAF50';
        } else {
            completeBtn.disabled = true;
            completeBtn.style.opacity = '0.5';
            completeBtn.style.cursor = 'not-allowed';
            completeBtn.style.background = '#6c757d';
        }
    }
}

function closeCompleteCatalogingModal() {
    document.getElementById('completeCatalogingModal').style.display = 'none';
    document.getElementById('completeCatalogingForm').reset();
    // Reset location dropdown explicitly
    const locationInput = document.getElementById('locationInput');
    if (locationInput) {
        locationInput.value = '';
    }
    // Reset button state
    const completeBtn = document.getElementById('completeCatalogingBtn');
    if (completeBtn) {
        completeBtn.disabled = true;
        completeBtn.style.opacity = '0.5';
        completeBtn.style.cursor = 'not-allowed';
        completeBtn.style.background = '#6c757d';
    }
}

function showCatalogingSuccessModal(message) {
    const modal = document.getElementById('catalogingSuccessModal');
    const messageElement = document.getElementById('catalogingSuccessMessage');
    if (modal && messageElement) {
        messageElement.textContent = message || 'Book reference completed successfully!';
        modal.style.display = 'flex';
    }
}

function closeCatalogingSuccessModal() {
    const modal = document.getElementById('catalogingSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        // Reload materials to reflect the change
        loadAllMaterials();
    }
}

function showCatalogingErrorModal(message) {
    const modal = document.getElementById('catalogingErrorModal');
    const messageElement = document.getElementById('catalogingErrorMessage');
    if (modal && messageElement) {
        messageElement.textContent = message || 'An error occurred while completing cataloging.';
        modal.style.display = 'flex';
    }
}

function closeCatalogingErrorModal() {
    const modal = document.getElementById('catalogingErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function openDraftRequestModal(materialId) {
    // Open modal to draft request
    document.getElementById('draftingBookId').value = materialId;
    document.getElementById('draftRequestModal').style.display = 'block';
}

function closeDraftRequestModal() {
    document.getElementById('draftRequestModal').style.display = 'none';
    document.getElementById('draftRequestForm').reset();
}

function navigateToCourseDetails(courseCode, materialId = null) {
    // Find and hide the specific card that was clicked
    let cardToHide = null;
    
    if (materialId) {
        // Find card by material ID (most reliable)
        cardToHide = document.querySelector(`.material-card[data-material-id="${materialId}"]`);
    }
    
    // If not found by ID, try finding by course code
    if (!cardToHide) {
        const cards = document.querySelectorAll(`.material-card[data-course-code="${courseCode}"]`);
        if (cards.length > 0) {
            // If multiple cards, find the one with completed status
            const completedCards = Array.from(cards).filter(card => {
                const statusEl = card.querySelector('.material-status');
                return statusEl && statusEl.textContent.trim().toLowerCase() === 'completed';
            });
            cardToHide = completedCards[0] || cards[0];
        }
    }
    
    // Store navigated material ID in sessionStorage so it won't appear when coming back
    if (materialId) {
        const navigatedMaterials = JSON.parse(sessionStorage.getItem('navigatedMaterials') || '[]');
        if (!navigatedMaterials.includes(materialId)) {
            navigatedMaterials.push(materialId);
            sessionStorage.setItem('navigatedMaterials', JSON.stringify(navigatedMaterials));
        }
    }
    
    // Remove the material from allMaterials array to prevent it from reappearing
    if (materialId) {
        allMaterials = allMaterials.filter(m => m.id !== materialId);
    } else {
        // If no materialId, try to find and remove by course code and completed status
        allMaterials = allMaterials.filter(m => {
            if (m.courseCode === courseCode && m.status === 'completed') {
                return false; // Remove this material
            }
            return true; // Keep this material
        });
    }
    
    // Update the display to reflect the removal
    displayFilteredMaterials(currentFilter);
    
    // Hide the specific card with fade effect
    if (cardToHide) {
        // Add transition for smooth animation
        cardToHide.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        cardToHide.style.opacity = '0';
        cardToHide.style.transform = 'scale(0.95)';
        
        // Wait for animation, then navigate
        setTimeout(() => {
            // Navigate to course details page after card disappears
            window.location.href = `content.php?page=course-details&course_code=${courseCode}`;
        }, 300);
    } else {
        // If card not found, navigate immediately
        window.location.href = `content.php?page=course-details&course_code=${courseCode}`;
    }
}

function resumeProcessing(materialId) {
    // API call to update status back to processing
    updateProcessingStatus(materialId, 'processing')
        .then(() => {
            loadAllMaterials(); // Reload all materials
        })
        .catch(error => {
            console.error('Error resuming processing:', error);
            alert('Failed to resume processing');
        });
}

// Helper function to update processing status via API
async function updateProcessingStatus(bookId, status, callNumber = null, noOfCopies = null, statusReason = null, location = null) {
    try {
        const response = await fetch('api/update_processing_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                book_id: bookId,
                status: status,
                call_number: callNumber,
                no_of_copies: noOfCopies,
                status_reason: statusReason,
                location: location
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result;
        } else {
            throw new Error(result.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error updating processing status:', error);
        throw error;
    }
}

// Initialize the page and set up form handlers
document.addEventListener('DOMContentLoaded', function() {
    
    // Set Processing as default active filter
    currentFilter = 'PROCESSING';
    
    // Load all materials from database (this will also update counts)
    loadAllMaterials();
    
    // Complete cataloging form submission
    const completeForm = document.getElementById('completeCatalogingForm');
    if (completeForm) {
        completeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const bookId = document.getElementById('completingBookId').value;
            const callNumber = document.getElementById('callNumberInput').value;
            const noOfCopies = document.getElementById('noOfCopiesInput').value;
            const location = document.getElementById('locationInput').value;
            
            if (!location) {
                alert('Please select a location.');
                return;
            }
            
            try {
                await updateProcessingStatus(bookId, 'completed', callNumber, noOfCopies, null, location);
                closeCompleteCatalogingModal();
                showCatalogingSuccessModal('Book reference completed successfully!');
            } catch (error) {
                closeCompleteCatalogingModal();
                showCatalogingErrorModal('Failed to complete cataloging: ' + error.message);
            }
        });
    }
    
    // Draft request form submission
    const draftForm = document.getElementById('draftRequestForm');
    if (draftForm) {
        draftForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const bookId = document.getElementById('draftingBookId').value;
            const statusReason = document.getElementById('draftReasonInput').value;
            
            try {
                await updateProcessingStatus(bookId, 'drafted', null, null, statusReason);
                closeDraftRequestModal();
                loadAllMaterials(); // Reload all materials
                alert('Request has been drafted successfully!');
            } catch (error) {
                alert('Failed to draft request: ' + error.message);
            }
        });
    }
});

// Helper function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}
</script>
