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
<div class="back-navigation" style="margin-bottom: 32px;">
    <button class="back-button" onclick="window.history.back()" style="display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid var(--primary-border); color: var(--text-muted); padding: 10px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.3s ease;">
        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
        Back to Dashboard
    </button>
</div>

<div class="header-section" style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--primary-border); padding-bottom: 24px;">
    <div class="header-content">
        <h1 class="main-page-title" style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 8px;">Material Processing</h1>
        <p class="page-description" style="color: var(--text-muted); font-size: 15px; font-weight: 500;">Manage materials and finalize cataloging for the library collection.</p>
    </div>
    <div class="filter-buttons" style="display: flex; gap: 8px; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 14px;">
        <button class="filter-btn active" onclick="filterMaterials('PROCESSING')" style="padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.3s ease;">Processing</button>
        <button class="filter-btn" onclick="filterMaterials('COMPLETED')" style="padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.3s ease;">Completed</button>
        <button class="filter-btn" onclick="filterMaterials('DRAFTED')" style="padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; transition: all 0.3s ease;">Drafted</button>
    </div>
</div>

<div class="material-processing-container">
    <div class="material-processing-grid" id="materialProcessingGrid">
        <!-- Filtered materials will be displayed here -->
    </div>
</div>

<!-- Complete Cataloging Modal -->
<div id="completeCatalogingModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: var(--primary-tint); color: var(--primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="book-open-check"></i>
                </div>
                <h2 class="modal-title">Complete Cataloging</h2>
            </div>
            <button class="modal-close" onclick="closeCompleteCatalogingModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px; font-weight: 600;">Finalize the book details to add it to the inventory.</p>
            
            <form id="completeCatalogingForm">
                <input type="hidden" id="completingBookId" value="">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Call Number <span style="color: var(--danger);">*</span></label>
                    <div class="input-wrapper" style="position: relative;">
                        <i data-lucide="hash" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-faint);"></i>
                        <input type="text" id="callNumberInput" required class="form-input" placeholder="e.g. 004.16 B64 2023" style="padding-left: 42px; width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Number of Copies <span style="color: var(--danger);">*</span></label>
                    <div class="input-wrapper" style="position: relative;">
                        <i data-lucide="layers" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-faint);"></i>
                        <input type="number" id="noOfCopiesInput" value="1" min="1" required class="form-input" style="padding-left: 42px; width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label class="form-label">Library Location <span style="color: var(--danger);">*</span></label>
                    <div class="input-wrapper" style="position: relative;">
                        <i data-lucide="map-pin" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-faint); pointer-events: none;"></i>
                        <select id="locationInput" required class="form-input" style="padding-left: 42px; width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; background: #fff; appearance: none;">
                            <option value="">Select Location</option>
                            <option value="Main Library">Main Library</option>
                            <option value="Buenavista Library">Buenavista Library</option>
                        </select>
                        <i data-lucide="chevron-down" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-faint); pointer-events: none;"></i>
                    </div>
                </div>

                <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 12px;">
                    <button type="button" class="btn-secondary" onclick="closeCompleteCatalogingModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted); font-family: 'TT Interphases', sans-serif;">Cancel</button>
                    <button type="submit" id="completeCatalogingBtn" class="btn-primary" disabled style="padding: 12px 24px; border-radius: 10px; opacity: 0.5; cursor: not-allowed; min-width: 140px;">
                        Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal for Cataloging Completion -->
<div id="catalogingSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="success-icon-wrapper" style="width: 80px; height: 80px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="check-circle-2" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Success!</h2>
        <p id="catalogingSuccessMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;"></p>
        <button type="button" onclick="closeCatalogingSuccessModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700;">Continue</button>
    </div>
</div>

<!-- Error Modal for Cataloging Completion -->
<div id="catalogingErrorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="error-icon-wrapper" style="width: 80px; height: 80px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="alert-circle" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Error</h2>
        <p id="catalogingErrorMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;"></p>
        <button type="button" onclick="closeCatalogingErrorModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; background: #EF4444;">Close</button>
    </div>
</div>

<!-- Draft Request Modal -->
<div id="draftRequestModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: #FFFBEB; color: #D97706; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="sticky-note"></i>
                </div>
                <h2 class="modal-title">Draft Request</h2>
            </div>
            <button class="modal-close" onclick="closeDraftRequestModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px; font-weight: 600;">Move this request to draft status with a reason.</p>
            <form id="draftRequestForm">
                <input type="hidden" id="draftingBookId" value="">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Reason <span style="color: var(--danger);">*</span></label>
                    <textarea id="draftReasonInput" required rows="4" class="form-input" style="width: 100%; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 12px 14px; resize: vertical;" placeholder="e.g. Budget constraints, out of stock..."></textarea>
                </div>
                <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeDraftRequestModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted);">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding: 12px 24px; border-radius: 10px; background: #D97706;">Move to Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Premium Material Card Styling */
    .material-processing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        margin-top: 24px;
        animation: fadeSlideUp 0.6s ease-out;
    }

    .material-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--primary-border);
        padding: 24px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .material-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        border-color: var(--primary-tint);
    }

    .material-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--primary);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .material-card:hover::before {
        opacity: 1;
    }

    .material-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .requester-info {
        flex: 1;
    }

    .requester-name {
        font-weight: 800;
        color: #111827;
        font-size: 16px;
        margin-bottom: 4px;
    }

    .requester-role {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
    }

    .material-status {
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-processing { background: #FFF7ED; color: #EA580C; }
    .status-completed { background: #ECFDF5; color: #059669; }
    .status-drafted { background: #F3F4F6; color: #4B5563; }

    .course-info {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .course-code {
        background: var(--primary);
        color: #fff;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
    }

    .course-name {
        font-size: 13px;
        font-weight: 600;
        color: #4B5563;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .material-title {
        font-size: 15px;
        color: #1F2937;
        line-height: 1.6;
        font-weight: 600;
        margin-bottom: 24px;
        font-style: italic;
    }

    .material-actions {
        display: flex;
        gap: 12px;
        margin-top: auto;
    }

    .action-btn {
        flex: 1;
        height: 42px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .catalog-btn { background: var(--primary); color: #fff; }
    .catalog-btn:hover { background: var(--primary-shade); transform: translateY(-2px); }

    .draft-btn { background: #FEF3C7; color: #B45309; }
    .draft-btn:hover { background: #FDE68A; }

    .resume-btn { background: var(--primary); color: #fff; }
    .process-btn { background: #EEF2FF; color: #4338CA; }

    .request-date {
        margin-top: 16px;
        font-size: 12px;
        color: var(--text-faint);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Filter Button Styling */
    .filter-btn.active {
        background: var(--primary) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(12, 75, 52, 0.2);
    }

    .filter-btn:not(.active):hover {
        background: rgba(0,0,0,0.05);
        color: #111827;
    }

    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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
            <button class="action-btn catalog-btn" onclick="startCataloging(${material.id})">
                <i data-lucide="book-plus"></i>
                Start Cataloging
            </button>
            <button class="action-btn draft-btn" onclick="openDraftRequestModal(${material.id})">
                <i data-lucide="sticky-note"></i>
                Draft
            </button>
        `;
    } else if (material.status === 'completed') {
        actionButtons = `
            <button class="action-btn process-btn" onclick="navigateToCourseDetails('${material.courseCode}', ${material.id})">
                <i data-lucide="external-link"></i>
                Navigate
            </button>
        `;
    } else if (material.status === 'drafted') {
        actionButtons = `
            <button class="action-btn resume-btn" onclick="resumeProcessing(${material.id})">
                <i data-lucide="play"></i>
                Resume
            </button>
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
            
            <div class="material-title">"${material.materialTitle}"</div>
            
            <div class="material-actions">
                ${actionButtons}
            </div>
            
            <div class="request-date">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                <span>Submitted: ${formatDate(material.requestDate)}</span>
            </div>
        </div>
    `;
}

// Wrap displayFilteredMaterials to refresh Lucide icons
const originalDisplayFilteredMaterials = displayFilteredMaterials;
displayFilteredMaterials = function(status) {
    originalDisplayFilteredMaterials(status);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

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
