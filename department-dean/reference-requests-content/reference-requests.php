<?php
// This file is included within the department dean system
// No need to start session or check authentication as it's handled by the parent system

// Initial statistics (these will be updated by JavaScript)
$totalRequests = 11;
$pendingRequests = 6;
$approvedRequests = 4;
$rejectedRequests = 1;
?>

<!-- Review Course Material Requests - View All Page -->
<div class="back-navigation">
    <button class="back-button" onclick="window.history.back()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back">
        Back to Dashboard
    </button>
</div>

    <div class="header-section">
        <div class="header-content">
            <h1 class="main-page-title">Review Course Material Requests</h1>
            <p class="page-description">Review and manage all course material requests from faculty.</p>
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterRequests('PENDING')">Pending</button>
            <button class="filter-btn" onclick="filterRequests('APPROVED')">Approved</button>
            <button class="filter-btn" onclick="filterRequests('REJECTED')">Rejected</button>
        </div>
    </div>

<div class="reference-requests-container">
    <div class="reference-requests-grid" id="allRequestsGrid">
        <!-- Requests will be displayed here -->
    </div>
</div>

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
        color: #1976d2;
    }

    .filter-btn.active {
        background: #1976d2;
        color: white;
        border-color: #1976d2;
    }

    /* Main page title styling to match All Courses page */
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
        gap: 15px;
        margin-top: 20px;
            width: 100%;
    }

    .reference-request-card {
        width: 100%;
        min-width: 250px;
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
    
    .requester-role {
        font-size: 11px;
        color: #1976d2;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .faculty-department {
            font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0px;
        font-family: 'TT Interphases', sans-serif;
        padding: 0;
        display: inline-block;
        }

        .priority-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        font-family: 'TT Interphases', sans-serif;
        letter-spacing: 0.5px;
        white-space: nowrap;
        }

        .priority-high {
        background: #FF4C4C;
        color: white;
        }

        .priority-medium {
        background: #FFA500;
        color: white;
        }

        .priority-low {
        background: #4CAF50;
        color: white;
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
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
    
    .approve-btn, .reject-btn, .status-approved-btn, .status-rejected-btn {
        flex: 1;
        padding: 8px 16px;
            border: none;
        border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        transition: all 0.3s ease;
        text-transform: none;
        letter-spacing: 0.5px;
        }

        .approve-btn {
        background: #4CAF50;
            color: white;
        }

        .approve-btn:hover {
        background: #45a049;
        transform: translateY(-1px);
        }

        .reject-btn {
        background: #f44336;
            color: white;
        }

        .reject-btn:hover {
        background: #da190b;
        transform: translateY(-1px);
        }

    .status-approved-btn {
        background: #e8f5e8;
        color: #2e7d32;
        cursor: default;
        opacity: 0.8;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: none;
        font-family: 'TT Interphases', sans-serif;
        border: none;
        width: 100%;
    }
    
    .status-rejected-btn {
        background: #ffebee;
        color: #c62828;
        cursor: default;
        opacity: 0.8;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: none;
        font-family: 'TT Interphases', sans-serif;
        border: none;
        width: 100%;
    }
    
    .request-date {
        font-size: 11px;
        color: #999;
        font-family: 'TT Interphases', sans-serif;
            text-align: center;
        margin-top: 8px;
        font-style: italic;
    }
    
    /* Override grid to show all cards without pagination */
    .reference-requests-container .reference-requests-grid {
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
        margin-top: 20px !important;
        gap: 15px !important;
    }
    
    .reference-requests-container .reference-request-card {
        flex: 0 0 calc(20% - 12px) !important;
        min-width: 200px !important;
        max-width: none !important;
        margin-bottom: 20px !important;
        padding: 20px !important;
        flex-grow: 1 !important;
    }
    
    /* Responsive adjustments for View All page */
    @media screen and (max-width: 1400px) {
        .reference-requests-container .reference-request-card {
            flex: 0 0 calc(33.333% - 20px) !important;
            min-width: 300px !important;
        }
    }
    
    @media screen and (max-width: 1200px) {
        .reference-requests-container .reference-request-card {
            flex: 0 0 calc(50% - 20px) !important;
        }
    }
    
    @media screen and (max-width: 768px) {
        .reference-requests-container .reference-requests-grid {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .reference-requests-container .reference-request-card {
            flex: 1 1 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
        }
        }
    /* Responsive design for different screen sizes */
    @media (max-width: 1200px) {
        .reference-requests-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .reference-request-card {
            min-width: 300px;
        }
    }
    
    @media (max-width: 768px) {
        .reference-requests-grid {
            grid-template-columns: 1fr;
        }
        .reference-request-card {
            min-width: 100%;
        }
    }

    /* Priority sections styling */
    .priority-section {
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
            background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
            padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        scroll-margin-top: 20px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .priority-indicator {
        font-size: 18px;
            font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .priority-indicator.high {
        color: #FF4C4C;
    }

    .priority-indicator.medium {
        color: #FFA500;
    }

    .priority-indicator.low {
        color: #4CAF50;
    }

    .request-count {
        background: #e9ecef;
        color: #495057;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
        min-width: 30px;
        text-align: center;
    }

    .section-content {
        padding: 20px;
        transition: all 0.3s ease;
        min-height: 120px;
    }

    .section-content .reference-requests-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        align-items: start;
        justify-content: flex-start;
    }

    .section-content .reference-requests-grid.empty {
        display: flex !important;
        align-items: center;
        justify-content: center;
        min-height: 80px;
        width: 100%;
    }

    /* Floating navigation buttons styling */
    .floating-nav {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        gap: 10px;
        z-index: 1000;
    }

    .nav-btn {
        background: white;
            color: #666;
        border: 2px solid #e0e0e0;
        padding: 12px 16px;
        border-radius: 8px;
            font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        min-width: 80px;
        justify-content: center;
    }

    .nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .nav-btn.high-btn:hover {
        background: #FF4C4C;
        color: white;
        border-color: #FF4C4C;
    }

    .nav-btn.medium-btn:hover {
        background: #FFA500;
        color: white;
        border-color: #FFA500;
    }

    .nav-btn.low-btn:hover {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .nav-btn .btn-text {
        font-family: 'TT Interphases', sans-serif;
    }

    .nav-btn .btn-count {
        background: #f0f0f0;
        color: #666;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
    }

    .nav-btn.high-btn:hover .btn-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .nav-btn.medium-btn:hover .btn-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .nav-btn.low-btn:hover .btn-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        }
    </style>

<script>
    // Load all requests data when this page is displayed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Reference Requests View All page loaded');
        displayAllRequests();
    });
    
    function filterRequests(status) {
        // Update active button state
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Find and activate the button for the given status
        const targetButton = document.querySelector(`.filter-btn[onclick*="${status}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }
        
        // Make sure we have access to allRequests
        if (!window.allRequests) {
            console.error('No requests data available');
            return;
        }
        
        // Calculate 30 days ago
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        // Filter requests by status, excluding old approved/rejected requests
        const filteredRequests = window.allRequests.filter(request => {
            if (request.status !== status) return false;
            
            if (status === 'APPROVED' || status === 'REJECTED') {
                const requestDate = new Date(request.request_date || new Date());
                return requestDate >= thirtyDaysAgo;
            }
            // For pending requests, no date restriction
            return true;
        });
        
        // Render filtered requests in the single grid
        const grid = document.getElementById('allRequestsGrid');
        if (!grid) return;
        grid.innerHTML = '';
        filteredRequests.forEach(request => {
            grid.appendChild(createRequestCard(request));
        });
        
        console.log(`Filtered to ${status}: ${filteredRequests.length} requests`);
    }

    function createRequestCard(request) {
        // Generate APA citation for display
        let apaCitation = '';
        if (request.author_first && request.author_last && request.publication_year) {
            if (request.author_first === 'Various') {
                apaCitation = `${request.author_last}, ${request.author_first}. (${request.publication_year}). ${request.book_title}.`;
            } else {
                const editionText = request.edition && request.edition !== 'Current' ? ` (${request.edition} ed.)` : '';
                apaCitation = `${request.author_last}, ${request.author_first.charAt(0)}. (${request.publication_year}). ${request.book_title}${editionText}.`;
            }
        } else {
            apaCitation = request.book_title;
        }
        
        // Get department code and color from session or use default
        const departmentCode = '<?php echo $_SESSION["selected_role"]["department_code"] ?? "CCS"; ?>';
        const departmentColor = '<?php echo $_SESSION["selected_role"]["department_color"] ?? "#1976d2"; ?>';
        const departmentName = '<?php echo $_SESSION["selected_role"]["department_name"] ?? "College of Computing Studies"; ?>';
        
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-request-id', request.id);
        
        card.innerHTML = `
            <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name">${request.requester_name}</div>
                    <div class="faculty-department" style="color: ${departmentColor};">${departmentCode} FACULTY</div>
                </div>
            </div>

            <div class="course-info">
                <div class="course-code">${request.course_code}</div>
                <div class="course-name">${request.course_name}</div>
            </div>
            
            <div class="request-summary">
                <div class="request-type">Course Material Request</div>
                <div class="material-title">${apaCitation}</div>
        </div>

            ${request.status === 'PENDING' ? `
                <div class="action-buttons">
                    <button class="approve-btn" onclick="approveRequest(${request.id})">Approve</button>
                    <button class="reject-btn" onclick="rejectRequest(${request.id})">Reject</button>
                </div>
                <div class="request-date">Requested on: ${new Date().toLocaleDateString()}</div>
            ` : request.status === 'APPROVED' ? `
                <div class="action-buttons">
                    <button class="status-approved-btn" disabled>Approved</button>
        </div>
                <div class="request-date">Approved on: ${new Date().toLocaleDateString()}</div>
            ` : `
                <div class="action-buttons">
                    <button class="status-rejected-btn" disabled>Rejected</button>
    </div>
                <div class="request-date">Rejected on: ${new Date().toLocaleDateString()}</div>
            `}
        `;
        
        return card;
    }
    
        function approveRequest(requestId) {
        if (confirm('Are you sure you want to approve this course material request?')) {
            // Here you would typically make an API call to update the database
            alert('Request approved successfully!');
            // Refresh the display
            displayAllRequests();
            }
        }

        function rejectRequest(requestId) {
        if (confirm('Are you sure you want to reject this course material request?')) {
            // Here you would typically make an API call to update the database
            alert('Request rejected successfully!');
            // Refresh the display
            displayAllRequests();
        }
    }

    function displayAllRequests() {
        // Initialize with empty array if not already set
        if (!window.allRequests) {
            window.allRequests = [];
        }
        
        // By default, show only pending requests (matching the active "Pending" button)
        // Set the Pending button as active and filter to show only pending requests
        const pendingButton = document.querySelector('.filter-btn[onclick*="PENDING"]');
        if (pendingButton) {
            pendingButton.classList.add('active');
        }
        filterRequests('PENDING');
    }

    // displayRequestsInSection and scrollToSection are no longer needed after simplifying layout
    </script>
