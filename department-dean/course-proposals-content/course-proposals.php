<?php
// This file is included within the department dean system
// No need to start session or check authentication as it's handled by the parent system
?>

<!-- Course Proposals & Revisions - View All Page -->
<div class="back-navigation">
    <button class="back-button" onclick="window.history.back()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back">
        Back to Dashboard
    </button>
</div>

<div class="header-section">
    <div class="header-content">
        <h1 class="main-page-title">Course Proposals & Revisions</h1>
        <p class="page-description">Review and manage all course proposals and revision requests.</p>
    </div>
    <div style="display: flex; flex-direction: column; gap: 15px; align-items: flex-end;">
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterProposals('all')">All</button>
            <button class="filter-btn" onclick="filterProposals('New Course Proposal')">New Course Proposal</button>
            <button class="filter-btn" onclick="filterProposals('Cross-Department')">Cross-Department</button>
            <button class="filter-btn" onclick="filterProposals('Course Revision')">Course Revision</button>
        </div>
        <div class="hide-completed-option">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: 'TT Interphases', sans-serif; font-size: 13px; color: #666;">
                <input type="checkbox" id="hideCompletedCheckbox" onchange="toggleHideCompleted()" style="width: 16px; height: 16px; cursor: pointer;">
                <span>Hide Approved/Completed</span>
            </label>
        </div>
    </div>
</div>

<div class="reference-requests-container">
    <div class="reference-requests-grid" id="allProposalsGrid">
        <!-- Course proposals will be displayed here -->
    </div>
</div>

<style>
    /* Back navigation styling */
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

    .filter-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #e0e0e0;
        background: white;
        color: #666;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'TT Interphases', sans-serif;
    }

    .filter-btn:hover {
        background: #f5f5f5;
        border-color: #1976d2;
    }

    .filter-btn.active {
        background: #1976d2;
        color: white;
        border-color: #1976d2;
    }

    .hide-completed-option {
        display: flex;
        align-items: center;
    }

    /* Main page title styling */
    .main-page-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.2;
    }

    .page-description {
        font-size: 14px;
        color: #666;
        margin: 5px 0 0px 0;
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.4;
    }
    
    /* Complete card styling for View All page */
    .reference-requests-container {
        margin-top: 20px;
        width: 100%;
        max-width: none;
    }
    
    .reference-requests-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 20px;
        width: 100%;
    }

    .reference-request-card {
        width: 100%;
        min-width: 220px;
        padding: 20px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
    }
    
    .reference-request-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        border-color: #1976d2;
    }
    
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 5px;
        padding-bottom: 5px;
    }
    
    .requester-info {
        flex: 1;
    }
    
    .requester-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .faculty-department {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #666;
        font-family: 'TT Interphases', sans-serif;
    }

    .course-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
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
        margin-bottom: 16px;
        flex: 1;
    }

    .material-title {
        font-size: 12px;
        color: #666;
        line-height: 1.4;
        font-family: 'TT Interphases', sans-serif;
    }

    .status-display {
        width: 100%;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        text-transform: uppercase;
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 12px;
    }

    .status-pending {
        background: #fff3e0;
        color: #ef6c00;
    }

    .status-approved {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .status-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .status-draft {
        background: #C0C0C0;
        color: white;
    }

    .resume-draft-btn:hover {
        background: #1565c0 !important;
    }

    .delete-draft-btn:hover {
        background: #c82333 !important;
    }

    .view-details-btn {
        background: #1976d2;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'TT Interphases', sans-serif;
        width: 100%;
        margin-bottom: 8px;
    }

    .view-details-btn:hover {
        background: #1565c0;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
    }
    
    .request-date {
        font-size: 11px;
        color: #999;
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        margin-top: 8px;
        font-style: italic;
    }
    
    /* Responsive adjustments for View All page */
    @media screen and (max-width: 1400px) {
        .reference-requests-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media screen and (max-width: 1200px) {
        .reference-requests-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media screen and (max-width: 768px) {
        .reference-requests-grid {
            grid-template-columns: 1fr;
        }
        
        .header-section {
            flex-direction: column;
            gap: 15px;
        }
        
        .filter-buttons {
            flex-wrap: wrap;
        }
    }
    
    /* Course Proposal Details Modal */
    .course-proposal-details-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .course-proposal-details-modal .modal-content-details {
        animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .view-attachment-btn:hover {
        background: #1565c0 !important;
    }
    
    .download-attachment-btn:hover {
        background: #45a049 !important;
    }
    
    .close-modal:hover {
        color: #333 !important;
    }
</style>

<script>
    // Course proposals data loaded from API
    let allProposals = [];
    let currentFilter = 'all';
    let hideCompleted = false;

    // Initialize page
    document.addEventListener('DOMContentLoaded', async function() {
        await loadCourseProposals();
    });
    
    // Load course proposals from API
    async function loadCourseProposals() {
        const grid = document.getElementById('allProposalsGrid');
        
        // Show loading state
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;"><p>Loading course proposals...</p></div>';
        
        try {
            // Fetch all proposals (no limit for View All page)
            const response = await fetch('api/get_course_proposals.php?limit=1000', {
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch course proposals');
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch course proposals');
            }
            
            allProposals = data.proposals || [];
            
            console.log('Total proposals loaded:', allProposals.length);
            console.log('Draft proposals:', allProposals.filter(p => p.isDraft === true || p.status === 'Draft'));
            
            // Display all proposals
            displayProposals(allProposals);
        } catch (error) {
            console.error('Error loading course proposals:', error);
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #f44336;"><p>Error loading course proposals. Please try again.</p></div>';
        }
    }

    // Filter proposals by type
    function filterProposals(type) {
        currentFilter = type;
        
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        applyFilters();
    }

    // Toggle hide completed
    function toggleHideCompleted() {
        hideCompleted = document.getElementById('hideCompletedCheckbox').checked;
        applyFilters();
    }

    // Apply all filters
    function applyFilters() {
        let filteredProposals = allProposals;
        
        // Filter by type
        if (currentFilter !== 'all') {
            // Map filter types to courseType
            const filterMap = {
                'New Course Proposal': 'New Course Proposal',
                'Cross-Department': 'Cross-Department',
                'Course Revision': 'Course Revision'
            };
            const mappedFilter = filterMap[currentFilter] || currentFilter;
            filteredProposals = filteredProposals.filter(proposal => proposal.courseType === mappedFilter);
        }
        
        // Filter out completed/approved if checkbox is checked
        if (hideCompleted) {
            filteredProposals = filteredProposals.filter(proposal => {
                const status = proposal.status.toLowerCase();
                return !status.includes('approved') && 
                       !status.includes('added to program') && 
                       !status.includes('completed');
            });
        }
        
        displayProposals(filteredProposals);
    }

    // Display proposals in grid
    function displayProposals(proposals) {
        const grid = document.getElementById('allProposalsGrid');
        grid.innerHTML = '';
        
        console.log('Displaying proposals:', proposals.length);
        console.log('Proposals data:', proposals);
        
        if (proposals.length === 0) {
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;"><p>No course proposals found.</p></div>';
            return;
        }
        
        proposals.forEach(proposal => {
            console.log('Creating card for:', proposal.id || proposal.programCode, 'isDraft:', proposal.isDraft, 'status:', proposal.status);
            const card = createProposalCard(proposal);
            grid.appendChild(card);
        });
    }

    // Create proposal card
    function createProposalCard(cardData) {
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-proposal-id', cardData.id || cardData.programCode);
        
        // Check if this is a draft
        const isDraft = cardData.isDraft === true || cardData.status === 'Draft' || cardData.status.toLowerCase().includes('draft');
        
        // Determine status class
        let statusClass = 'status-pending';
        if (isDraft) {
            statusClass = 'status-draft';
        } else if (cardData.status.toLowerCase().includes('approved') || cardData.status.toLowerCase().includes('added')) {
            statusClass = 'status-approved';
        } else if (cardData.status.toLowerCase().includes('rejected')) {
            statusClass = 'status-rejected';
        } else if (cardData.status.toLowerCase().includes('review') || cardData.status.toLowerCase().includes('pending')) {
            statusClass = 'status-pending';
        }
        
        // Format date
        const date = new Date(cardData.submittedDate || cardData.createdAt || new Date());
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Get course information - use first course if courses array exists, otherwise use direct properties
        const courseCode = cardData.courseCode || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseCode : 'N/A');
        const courseName = cardData.courseName || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseName : 'N/A');
        
        // Get type color
        let typeColor = '#1976d2';
        if (cardData.courseType === 'Cross-Department') {
            typeColor = '#42a5f5';
        } else if (cardData.courseType === 'Course Revision') {
            typeColor = '#66bb6a';
        }
        
        // Buttons - Resume/Delete for drafts, View Details for submitted
        const actionButtons = isDraft ? `
            <div style="display: flex; gap: 8px; width: 100%;">
                <button class="resume-draft-btn" onclick="resumeDraft('${cardData.id}', event)" style="flex: 1; background: #1976d2; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'TT Interphases', sans-serif;">
                    Resume
                </button>
                <button class="delete-draft-btn" onclick="deleteDraft('${cardData.id}', event)" style="flex: 1; background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'TT Interphases', sans-serif;">
                    Delete
                </button>
            </div>
        ` : `
            <button class="view-details-btn" onclick="viewCourseProposalDetails('${cardData.id}')">
                View Details
            </button>
        `;
        
        card.innerHTML = `
            <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name" style="color: ${typeColor}; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center;">
                        ${cardData.courseType || 'Course Proposal'}
                    </div>
                    <div class="faculty-department" style="color: #666; font-size: 11px;">
                        ${cardData.programCode || 'N/A'} Program
                    </div>
                </div>
            </div>
            
            <div class="course-info" style="margin-bottom: 12px;">
                <div class="course-code" style="background: #1976d2; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; font-family: 'TT Interphases', sans-serif; display: inline-block; margin-bottom: 8px;">
                    ${courseCode}
                </div>
                <div class="course-name" style="font-size: 13px; color: #666; font-family: 'TT Interphases', sans-serif;">
                    ${courseName}
                </div>
            </div>
            
            <div class="request-summary" style="margin-bottom: 16px; flex: 1;">
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; flex-wrap: wrap;">
                    ${cardData.totalReferences > 0 ? `
                        <div class="references-indicator" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
                            <span style="font-size: 14px;">📚</span>
                            <span>${cardData.totalReferences} reference${cardData.totalReferences > 1 ? 's' : ''}</span>
                        </div>
                    ` : ''}
                    ${cardData.totalAttachments > 0 ? `
                        <div class="attachments-indicator" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
                            <span style="font-size: 14px;">📎</span>
                            <span>${cardData.totalAttachments} attachment${cardData.totalAttachments > 1 ? 's' : ''}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="status-display ${statusClass}">
                ${isDraft ? 'Draft' : cardData.status}
            </div>
            
            ${actionButtons}
            
            <div class="request-date">${isDraft ? 'Drafted on: ' : 'Submitted on: '}${formattedDate}</div>
        `;
        
        return card;
    }

    // View course proposal details
    function viewCourseProposalDetails(proposalId) {
        console.log('View details for course proposal:', proposalId);
        
        // Find the proposal data
        const proposal = allProposals.find(p => (p.id === proposalId || p.courseCode === proposalId));
        if (!proposal) {
            alert('Course proposal not found: ' + proposalId);
            return;
        }
        
        // Open the details modal
        openCourseProposalDetailsModal(proposal);
    }
    
    // Open course proposal details modal (global function)
    window.openCourseProposalDetailsModal = function(proposal) {
        // Create or get modal
        let modal = document.getElementById('courseProposalDetailsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'courseProposalDetailsModal';
            modal.className = 'course-proposal-details-modal';
            document.body.appendChild(modal);
        }
        
        // Format date
        const date = new Date(proposal.submittedDate);
        const formattedDate = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Get file icon
        function getFileIcon(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            const icons = {
                'pdf': '📄',
                'doc': '📝',
                'docx': '📝',
                'xls': '📊',
                'xlsx': '📊',
                'txt': '📄',
                'jpg': '🖼️',
                'jpeg': '🖼️',
                'png': '🖼️',
                'gif': '🖼️'
            };
            return icons[ext] || '📎';
        }
        
        // Build attachments HTML
        let attachmentsHTML = '';
        if (proposal.attachments && proposal.attachments.length > 0) {
            attachmentsHTML = proposal.attachments.map((attachment, index) => `
                <div class="attachment-item-detail" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f9f9f9; border-radius: 6px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <span style="font-size: 24px;">${getFileIcon(attachment.name)}</span>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 4px; word-break: break-word;">${attachment.name}</div>
                            <div style="font-size: 11px; color: #666;">${formatFileSize(attachment.size)}</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="viewAttachmentFile('${attachment.path}', '${attachment.name}')" class="view-attachment-btn" style="padding: 6px 12px; background: #1976d2; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">
                            View
                        </button>
                        <button onclick="downloadAttachmentFile('${attachment.path}', '${attachment.name}')" class="download-attachment-btn" style="padding: 6px 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">
                            Download
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            attachmentsHTML = '<div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">No attachments available</div>';
        }
        
        // Set modal content
        modal.innerHTML = `
            <div class="modal-content-details" style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
                <div class="modal-header-details" style="padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #333; font-family: 'TT Interphases', sans-serif;">Course Proposal Details</h2>
                    <span class="close-modal" onclick="closeCourseProposalDetailsModal()" style="font-size: 28px; font-weight: 300; color: #999; cursor: pointer; line-height: 1;">&times;</span>
                </div>
                
                <div class="modal-body-details" style="padding: 20px;">
                    <!-- Course Information -->
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Course Information</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Course Code</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.courseCode}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Program</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.program}</div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Course Name</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.courseName}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Units</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.units || 'N/A'}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Hours</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.lectureHours || 0}L / ${proposal.laboratoryHours || 0}Lab</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Type</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.type}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Submitted Date</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${formattedDate}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Status</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif; font-weight: 600; color: ${proposal.statusColor || '#666'};">${proposal.status}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Learning Materials -->
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Learning Materials</h3>
                        <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.materialCount || 'No materials specified'}</div>
                    </div>
                    
                    <!-- Justification -->
                    ${proposal.justification ? `
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Justification</h3>
                        <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif; line-height: 1.6; background: #f9f9f9; padding: 12px; border-radius: 6px;">${proposal.justification}</div>
                    </div>
                    ` : ''}
                    
                    <!-- Attachments -->
                    <div class="details-section">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Attachments</h3>
                        <div class="attachments-list-detail">
                            ${attachmentsHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Show modal
        modal.style.display = 'flex';
    };
    
    // Close course proposal details modal (global function)
    window.closeCourseProposalDetailsModal = function() {
        const modal = document.getElementById('courseProposalDetailsModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    // View attachment file (global function)
    window.viewAttachmentFile = function(path, fileName) {
        // In a real implementation, this would open the file from the server
        // For now, we'll simulate it
        console.log('Viewing attachment:', path, fileName);
        window.open(path, '_blank');
    };
    
    // Download attachment file (global function)
    window.downloadAttachmentFile = function(path, fileName) {
        // In a real implementation, this would download the file from the server
        // For now, we'll simulate it
        console.log('Downloading attachment:', path, fileName);
        const link = document.createElement('a');
        link.href = path;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Resume draft - load draft data back into the form
    function resumeDraft(proposalId, event) {
        if (event) {
            event.stopPropagation();
        }
        
        console.log('Resuming draft:', proposalId);
        
        // Find the draft proposal
        const proposal = allProposals.find(p => (p.id === proposalId || p.courseCode === proposalId) && (p.isDraft === true || p.status === 'Draft'));
        
        if (!proposal) {
            alert('Draft not found: ' + proposalId);
            return;
        }
        
        // Load draft data into the course selection context and open the add course modal
        if (proposal._formData) {
            // Store draft data for loading
            window.draftToResume = proposal;
            
            // Set course selection context from draft
            if (proposal.programId && proposal.academicTerm && proposal.academicYear && proposal.yearLevel) {
                window.courseSelectionContext = {
                    programId: proposal.programId,
                    programName: proposal.programName,
                    term: proposal.academicTerm,
                    academicYear: proposal.academicYear,
                    yearLevel: proposal.yearLevel,
                    courseType: proposal.courseType || 'proposal'
                };
            }
            
            // Open Manage Program Courses modal first
            if (typeof openCourseSelectionModal === 'function') {
                openCourseSelectionModal();
                
                // Then open add course modal with draft data
                setTimeout(() => {
                    if (typeof openAddCourseModal === 'function') {
                        openAddCourseModal();
                        // Load draft data will be handled in the modal
                    }
                }, 500);
            }
        } else {
            alert('Draft data not available. Please recreate the course.');
        }
    }
    
    // Delete draft
    function deleteDraft(proposalId, event) {
        if (event) {
            event.stopPropagation();
        }
        
        // Find proposal to get program info for message
        const proposal = allProposals.find(p => (p.id === proposalId || p.courseCode === proposalId));
        const programInfo = proposal ? `${proposal.programCode} - ${proposal.academicTerm}, ${proposal.yearLevel}` : proposalId;
        
        if (!confirm(`Are you sure you want to delete the draft for "${programInfo}"? This action cannot be undone.`)) {
            return;
        }
        
        console.log('Deleting draft:', proposalId);
        
        // Remove from local array
        const index = allProposals.findIndex(p => (p.id === proposalId || p.courseCode === proposalId) && (p.isDraft === true || p.status === 'Draft'));
        if (index !== -1) {
            allProposals.splice(index, 1);
            applyFilters(); // Re-apply filters to update display
        }
        
        // Delete from backend
        fetch('api/delete_course_draft.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                proposal_id: proposalId,
                program_id: proposal ? proposal.programId : null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Draft deleted successfully');
            } else {
                console.error('Error deleting draft:', data.message);
                alert('Error deleting draft: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting draft:', error);
            alert('Error deleting draft. Please try again.');
        });
    }
    
    // Make functions globally available
    window.resumeDraft = resumeDraft;
    window.deleteDraft = deleteDraft;
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('courseProposalDetailsModal');
        if (modal && event.target === modal) {
            window.closeCourseProposalDetailsModal();
        }
    });
</script>

