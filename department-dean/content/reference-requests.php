<?php
// This file handles the "View All" page for course material requests
// It will be included by content.php when ?page=reference-requests is requested

// Check if user is logged in and has proper access
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'DEAN') {
    // Redirect to login if not properly authenticated
    header('Location: ../login.php');
    exit();
}
?>

<!-- Review Course Material Requests - View All Page -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="header-left">
            <h3>Review Course Material Requests</h3>
            <div class="section-description">Review and manage all course material requests from faculty.</div>
        </div>
        <div class="header-actions">
            <a href="content.php?page=dashboard" class="back-btn">← Back to Dashboard</a>
        </div>
    </div>
    
    <div class="reference-requests-container">
        <div class="reference-requests-grid" id="allRequestsGrid">
            <!-- All requests will be displayed here -->
        </div>
    </div>
</div>

<style>
    .back-btn {
        background: #1976d2;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .back-btn:hover {
        background: #1565c0;
    }
    
    /* Override grid to show all cards without pagination */
    .reference-requests-container .reference-requests-grid {
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
        margin-top: 20px !important;
    }
    
    .reference-requests-container .reference-request-card {
        flex: 0 0 calc(25% - 15px) !important;
        min-width: 250px !important;
        max-width: none !important;
        margin-bottom: 20px !important;
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
</style>

<script>
    // Load all requests data when this page is displayed
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Reference Requests View All page loaded');
        displayAllRequests();
    });
    
    function displayAllRequests() {
        const grid = document.getElementById('allRequestsGrid');
        if (!grid) {
            console.error('Grid element not found');
            return;
        }
        
        // Get the data from the dashboard (if available)
        let allRequests = [];
        const requestsData = document.getElementById('allRequestsData');
        
        if (requestsData) {
            try {
                allRequests = JSON.parse(requestsData.textContent);
                console.log('Loaded requests data:', allRequests);
            } catch (e) {
                console.error('Error parsing requests data:', e);
            }
        }
        
        // If no data from dashboard, use empty array
        if (!allRequests || allRequests.length === 0) {
            allRequests = [];
        }
        
        grid.innerHTML = '';
        
        if (allRequests && allRequests.length > 0) {
            allRequests.forEach(request => {
                const card = createRequestCard(request);
                grid.appendChild(card);
            });
            console.log(`Displayed ${allRequests.length} requests in View All mode`);
        } else {
            grid.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">No requests found.</div>';
        }
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
        
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-request-id', request.id);
        
        card.innerHTML = `
            <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name">${request.requester_name}</div>
                    <div class="requester-role">${request.requester_role}</div>
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
            ` : `
                <div class="status-info">
                    <strong>Status:</strong> <span class="status-${request.status.toLowerCase()}">${request.status}</span>
                </div>
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
</script>
