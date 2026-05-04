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
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Back to Dashboard
    </button>
</div>

<div class="page-header-section">
    <div class="header-content">
        <h1 class="main-page-title">Course Material Requests</h1>
        <p class="page-description">Review and manage course material requests from faculty members</p>
    </div>
    
    <!-- Stats Pills -->
    <div class="header-stats">
        <div class="stat-pill">
            <span class="stat-label">Total</span>
            <strong id="totalCount">0</strong>
        </div>
        <div class="stat-pill stat-pending">
            <span class="stat-label">Pending</span>
            <strong id="pendingCount">0</strong>
        </div>
        <div class="stat-pill stat-approved">
            <span class="stat-label">Approved</span>
            <strong id="approvedCount">0</strong>
        </div>
        <div class="stat-pill stat-rejected">
            <span class="stat-label">Rejected</span>
            <strong id="rejectedCount">0</strong>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs-container">
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterRequests('PENDING')" data-status="PENDING">
            <i data-lucide="clock" class="tab-icon"></i>
            <span class="tab-label">Pending</span>
            <span class="tab-count" id="pendingTabCount">0</span>
        </button>
        <button class="filter-tab" onclick="filterRequests('APPROVED')" data-status="APPROVED">
            <i data-lucide="check-circle" class="tab-icon"></i>
            <span class="tab-label">Approved</span>
            <span class="tab-count" id="approvedTabCount">0</span>
        </button>
        <button class="filter-tab" onclick="filterRequests('REJECTED')" data-status="REJECTED">
            <i data-lucide="x-circle" class="tab-icon"></i>
            <span class="tab-label">Rejected</span>
            <span class="tab-count" id="rejectedTabCount">0</span>
        </button>
    </div>
    
    <!-- Search -->
    <div class="search-container">
        <div class="search-bar">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" id="requestSearchInput" placeholder="Search by course, title, or requester..." oninput="searchRequests(this.value)">
            <button class="clear-search-btn" onclick="clearSearch()" style="display: none;" id="clearSearchBtn"><i data-lucide="x"></i></button>
        </div>
    </div>
</div>

<div class="reference-requests-container">
    <div class="reference-requests-grid" id="allRequestsGrid">
        <!-- Requests will be displayed here -->
    </div>
    
    <!-- Empty State -->
    <div class="empty-state" id="emptyState" style="display: none;">
        <i data-lucide="book-open" class="empty-icon"></i>
        <h3>No requests found</h3>
        <p>There are no course material requests matching your criteria.</p>
    </div>
</div>

<style>
    /* ====== DESIGN.md COMPLIANT STYLES ====== */
    
    /* Back navigation - DESIGN.md btn-ghost pattern */
    .back-navigation {
        margin-bottom: 20px;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        background: transparent;
        border: none;
        color: #0C4B34;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        padding: 0;
        border-radius: 0;
        text-decoration: none;
        transition: all 0.2s ease;
        font-family: 'TT Interphases', sans-serif;
        gap: 6px;
    }

    .back-button:hover {
        color: #0a3a28;
        transform: translateX(-4px);
        text-decoration: none;
    }

    .back-button svg {
        width: 18px;
        height: 18px;
    }

    /* Page Header Section - DESIGN.md Section Header pattern */
    .page-header-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .header-content {
        flex: 1;
        min-width: 250px;
    }

    .main-page-title {
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 6px 0;
        font-family: 'TT Interphases', sans-serif;
        letter-spacing: -0.5px;
    }

    .page-description {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.6);
        margin: 0;
        font-family: 'TT Interphases', sans-serif;
        font-weight: 500;
    }

    /* Header Stats - DESIGN.md Stat Pills pattern */
    .header-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .stat-pill {
        background: rgba(12, 75, 52, 0.04);
        border: 1px solid rgba(12, 75, 52, 0.08);
        color: rgba(17, 24, 39, 0.6);
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'TT Interphases', sans-serif;
    }

    .stat-pill strong {
        color: #0C4B34;
        font-weight: 800;
        font-size: 14px;
    }

    .stat-pill .stat-label {
        color: rgba(17, 24, 39, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 10px;
    }

    .stat-pill.stat-pending {
        background: rgba(255, 165, 0, 0.1);
        border-color: rgba(255, 165, 0, 0.2);
    }

    .stat-pill.stat-pending strong {
        color: #e65100;
    }

    .stat-pill.stat-approved {
        background: rgba(46, 125, 50, 0.1);
        border-color: rgba(46, 125, 50, 0.2);
    }

    .stat-pill.stat-approved strong {
        color: #2E7D32;
    }

    .stat-pill.stat-rejected {
        background: rgba(185, 28, 28, 0.1);
        border-color: rgba(185, 28, 28, 0.2);
    }

    .stat-pill.stat-rejected strong {
        color: #b91c1c;
    }

    /* Filter Tabs Container */
    .filter-tabs-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    /* Filter Tabs - DESIGN.md tabs pattern */
    .filter-tabs {
        display: flex;
        gap: 4px;
        background: rgba(12, 75, 52, 0.04);
        padding: 4px;
        border-radius: 12px;
    }

    .filter-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border: none;
        background: transparent;
        color: rgba(17, 24, 39, 0.5);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
    }

    .filter-tab:hover {
        color: #0C4B34;
        background: rgba(12, 75, 52, 0.06);
    }

    .filter-tab.active {
        background: #0C4B34;
        color: white;
        box-shadow: 0 4px 12px rgba(12, 75, 52, 0.25);
    }

    .filter-tab .tab-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    
    .filter-tab .tab-icon[data-lucide] {
        color: inherit;
    }

    .filter-tab .tab-count {
        background: rgba(12, 75, 52, 0.1);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
    }

    .filter-tab.active .tab-count {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Search Bar - DESIGN.md search pattern */
    .search-container {
        flex-shrink: 0;
    }

    .search-bar {
        display: flex;
        align-items: center;
        background-color: #FFFFFF;
        height: 44px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid rgba(12, 75, 52, 0.14);
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        min-width: 280px;
    }

    .search-bar:focus-within {
        border-color: #0C4B34;
        box-shadow: 0 0 0 3px rgba(12, 75, 52, 0.1);
    }

    .search-bar .search-icon {
        color: rgba(17, 24, 39, 0.4);
        margin-right: 10px;
        flex-shrink: 0;
        width: 18px;
        height: 18px;
    }

    .search-bar input {
        border: none;
        outline: none;
        flex: 1;
        font-size: 14px;
        background: transparent;
        font-family: 'TT Interphases', sans-serif;
    }

    .search-bar input::placeholder {
        color: rgba(17, 24, 39, 0.4);
    }

    .clear-search-btn {
        background: none;
        border: none;
        font-size: 16px;
        color: rgba(17, 24, 39, 0.4);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.15s ease;
    }

    .clear-search-btn:hover {
        background: rgba(12, 75, 52, 0.08);
        color: #0C4B34;
    }

    /* Reference Requests Container */
    .reference-requests-container {
        margin-top: 0;
        width: 100%;
    }

    .reference-requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 18px;
        width: 100%;
    }

    /* Reference Request Card - DESIGN.md Card pattern */
    .reference-request-card {
        background: #ffffff;
        border-radius: 16px 18px;
        border: 1px solid rgba(12, 75, 52, 0.14);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        padding: 20px 22px;
        transition: all 0.28s cubic-bezier(.4,0,.2,1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        animation: fadeSlideUp 0.45s ease-out both;
    }

    .reference-request-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
        border-radius: 16px 18px 0 0;
    }

    .reference-request-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
        border-color: rgba(12, 75, 52, 0.25);
    }

    /* Stagger animation for cards */
    .reference-request-card:nth-child(1) { animation-delay: 0.08s; }
    .reference-request-card:nth-child(2) { animation-delay: 0.16s; }
    .reference-request-card:nth-child(3) { animation-delay: 0.24s; }
    .reference-request-card:nth-child(4) { animation-delay: 0.32s; }
    .reference-request-card:nth-child(5) { animation-delay: 0.40s; }
    .reference-request-card:nth-child(6) { animation-delay: 0.48s; }
    .reference-request-card:nth-child(7) { animation-delay: 0.56s; }
    .reference-request-card:nth-child(8) { animation-delay: 0.64s; }

    /* Request Header */
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(12, 75, 52, 0.08);
    }

    .requester-info {
        flex: 1;
    }

    .requester-name {
        font-weight: 700;
        color: #111827;
        font-size: 14px;
        margin-bottom: 4px;
        font-family: 'TT Interphases', sans-serif;
    }

    .requester-role {
        font-size: 11px;
        color: #0C4B34;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'TT Interphases', sans-serif;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'TT Interphases', sans-serif;
    }

    .status-badge.pending {
        background: rgba(255, 165, 0, 0.12);
        color: #e65100;
        border: 1px solid rgba(255, 165, 0, 0.2);
    }

    .status-badge.approved {
        background: rgba(46, 125, 50, 0.12);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.2);
    }

    .status-badge.rejected {
        background: rgba(185, 28, 28, 0.12);
        color: #b91c1c;
        border: 1px solid rgba(185, 28, 28, 0.2);
    }

    /* Course Info - DESIGN.md code badge pattern */
    .course-info {
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(12, 75, 52, 0.04);
        padding: 10px 14px;
        border-radius: 10px;
    }

    .course-code {
        display: inline-block;
        background: #0C4B34;
        color: white;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-family: 'TT Interphases', sans-serif;
    }

    .course-name {
        font-size: 13px;
        color: rgba(17, 24, 39, 0.7);
        font-family: 'TT Interphases', sans-serif;
        flex: 1;
        font-weight: 500;
    }

    /* Request Summary */
    .request-summary {
        margin-bottom: 16px;
        flex: 1;
    }

    .request-type {
        font-size: 11px;
        color: rgba(17, 24, 39, 0.5);
        margin-bottom: 8px;
        font-family: 'TT Interphases', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
    }

    .material-title {
        font-size: 14px;
        color: #111827;
        line-height: 1.5;
        font-family: 'TT Interphases', sans-serif;
        font-weight: 500;
    }

    .material-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .material-detail-item {
        font-size: 11px;
        color: rgba(17, 24, 39, 0.6);
        background: rgba(12, 75, 52, 0.04);
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid rgba(12, 75, 52, 0.08);
        font-family: 'TT Interphases', sans-serif;
        font-weight: 600;
    }

    /* Action Buttons - DESIGN.md button patterns */
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid rgba(12, 75, 52, 0.08);
    }

    .approve-btn, .reject-btn {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .approve-btn {
        background: #0F7A53;
        color: white;
    }

    .approve-btn:hover {
        background: #0a5f42;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(15, 122, 83, 0.25);
    }

    .approve-btn:active {
        transform: translateY(0) scale(0.98);
    }

    .reject-btn {
        background: #b91c1c;
        color: white;
    }

    .reject-btn:hover {
        background: #991b1b;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(185, 28, 28, 0.25);
    }

    .reject-btn:active {
        transform: translateY(0) scale(0.98);
    }

    /* Status Buttons (disabled) */
    .status-approved-btn, .status-rejected-btn {
        flex: 1;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
        border: none;
        cursor: default;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .status-approved-btn {
        background: rgba(46, 125, 50, 0.12);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.2);
    }

    .status-rejected-btn {
        background: rgba(185, 28, 28, 0.12);
        color: #b91c1c;
        border: 1px solid rgba(185, 28, 28, 0.2);
    }

    /* Request Date */
    .request-date {
        font-size: 11px;
        color: rgba(17, 24, 39, 0.4);
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        margin-top: 10px;
        font-weight: 600;
    }

    /* Empty State - DESIGN.md empty state pattern */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(17, 24, 39, 0.4);
    }

    .empty-state svg {
        display: block;
        margin: 0 auto 16px;
        opacity: 0.3;
        color: #0C4B34;
    }

    .empty-state h3 {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px 0;
        font-family: 'TT Interphases', sans-serif;
    }

    .empty-state p {
        font-size: 13px;
        color: rgba(17, 24, 39, 0.6);
        margin: 0;
        font-family: 'TT Interphases', sans-serif;
        font-weight: 500;
    }

    /* Responsive Design - DESIGN.md breakpoints */
    @media (max-width: 1100px) {
        .reference-requests-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-header-section {
            flex-direction: column;
            align-items: stretch;
        }
        
        .header-stats {
            justify-content: flex-start;
        }
        
        .filter-tabs-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-tabs {
            width: 100%;
            overflow-x: auto;
        }
        
        .search-container {
            width: 100%;
        }
        
        .search-bar {
            min-width: 100%;
        }
        
        .reference-requests-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .main-page-title {
            font-size: 20px;
        }
        
        .header-stats {
            flex-direction: column;
        }
        
        .stat-pill {
            width: 100%;
            justify-content: space-between;
        }
    }

    /* ====== DARK MODE - DESIGN.md Section 1.2 ====== */
    html[data-theme="dark"] .back-button {
        color: #81C784;
    }

    html[data-theme="dark"] .back-button:hover {
        color: #a5d6a7;
    }

    html[data-theme="dark"] .main-page-title {
        color: #f0f0f0;
    }

    html[data-theme="dark"] .page-description {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .stat-pill {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.1);
        color: #e0e0e0;
    }

    html[data-theme="dark"] .stat-pill strong {
        color: #81C784;
    }

    html[data-theme="dark"] .stat-pill .stat-label {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .filter-tabs {
        background: rgba(255, 255, 255, 0.04);
    }

    html[data-theme="dark"] .filter-tab {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .filter-tab:hover {
        color: #81C784;
        background: rgba(255, 255, 255, 0.06);
    }

    html[data-theme="dark"] .filter-tab.active {
        background: #0F7A53;
    }

    html[data-theme="dark"] .filter-tab .tab-count {
        background: rgba(255, 255, 255, 0.1);
    }

    html[data-theme="dark"] .search-bar {
        background: #2d2d2d;
        border-color: #404040;
    }

    html[data-theme="dark"] .search-bar input {
        color: #e0e0e0;
    }

    html[data-theme="dark"] .search-bar .search-icon {
        color: #808080;
    }

    html[data-theme="dark"] .reference-request-card {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-theme="dark"] .reference-request-card:hover {
        border-color: #444 !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-theme="dark"] .requester-name {
        color: #f0f0f0;
    }

    html[data-theme="dark"] .requester-role {
        color: #81C784;
    }

    html[data-theme="dark"] .course-info {
        background: rgba(255, 255, 255, 0.04);
    }

    html[data-theme="dark"] .course-code {
        background: #0F7A53;
    }

    html[data-theme="dark"] .course-name {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .request-type {
        color: #b0b0b0;
    }

    html[data-theme="dark"] .material-title {
        color: #e0e0e0;
    }

    html[data-theme="dark"] .material-detail-item {
        background: rgba(255, 255, 255, 0.06);
        border-color: #404040;
        color: #b0b0b0;
    }

    html[data-theme="dark"] .request-header {
        border-bottom-color: #333;
    }

    html[data-theme="dark"] .action-buttons {
        border-top-color: #333;
    }

    html[data-theme="dark"] .request-date {
        color: #808080;
    }

    html[data-theme="dark"] .empty-state svg {
        color: #81C784;
    }

    html[data-theme="dark"] .empty-state h3 {
        color: #f0f0f0;
    }

    html[data-theme="dark"] .empty-state p {
        color: #b0b0b0;
    }
</style>

<script>
    // Global variables
    let currentFilter = 'PENDING';
    let searchQuery = '';
    
    // Load all requests data when this page is displayed
    document.addEventListener('DOMContentLoaded', function() {
        displayAllRequests();
    });
    
    function filterRequests(status) {
        currentFilter = status;
        
        // Update active tab state
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        const targetTab = document.querySelector(`.filter-tab[data-status="${status}"]`);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        
        // Apply filter and search
        applyFilters();
    }
    
    function searchRequests(query) {
        searchQuery = query.trim().toLowerCase();
        
        // Show/hide clear button
        const clearBtn = document.getElementById('clearSearchBtn');
        clearBtn.style.display = searchQuery ? 'block' : 'none';
        
        // Apply filter and search
        applyFilters();
    }
    
    function clearSearch() {
        document.getElementById('requestSearchInput').value = '';
        searchQuery = '';
        document.getElementById('clearSearchBtn').style.display = 'none';
        applyFilters();
    }
    
    function applyFilters() {
        // Make sure we have access to allRequests
        if (!window.allRequests) {
            console.error('No requests data available');
            return;
        }
        
        // Calculate 30 days ago
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        // Filter requests by status and search query
        let filteredRequests = window.allRequests.filter(request => {
            // Status filter
            if (request.status !== currentFilter) return false;
            
            // Date filter for approved/rejected
            if (currentFilter === 'APPROVED' || currentFilter === 'REJECTED') {
                const requestDate = new Date(request.request_date || new Date());
                if (requestDate < thirtyDaysAgo) return false;
            }
            
            // Search filter
            if (searchQuery) {
                const searchFields = [
                    request.course_code,
                    request.course_name,
                    request.book_title,
                    request.requester_name,
                    request.author_last,
                    request.isbn
                ].map(f => (f || '').toLowerCase());
                
                const matchesSearch = searchFields.some(field => field.includes(searchQuery));
                if (!matchesSearch) return false;
            }
            
            return true;
        });
        
        // Update stats
        updateStats();
        
        // Render filtered requests
        renderRequests(filteredRequests);
    }
    
    function updateStats() {
        if (!window.allRequests) return;
        
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        const total = window.allRequests.length;
        const pending = window.allRequests.filter(r => r.status === 'PENDING').length;
        const approved = window.allRequests.filter(r => {
            if (r.status !== 'APPROVED') return false;
            const requestDate = new Date(r.request_date || new Date());
            return requestDate >= thirtyDaysAgo;
        }).length;
        const rejected = window.allRequests.filter(r => {
            if (r.status !== 'REJECTED') return false;
            const requestDate = new Date(r.request_date || new Date());
            return requestDate >= thirtyDaysAgo;
        }).length;
        
        // Update header stats
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pendingCount').textContent = pending;
        document.getElementById('approvedCount').textContent = approved;
        document.getElementById('rejectedCount').textContent = rejected;
        
        // Update tab counts
        document.getElementById('pendingTabCount').textContent = pending;
        document.getElementById('approvedTabCount').textContent = approved;
        document.getElementById('rejectedTabCount').textContent = rejected;
    }
    
    function renderRequests(requests) {
        const grid = document.getElementById('allRequestsGrid');
        const emptyState = document.getElementById('emptyState');
        
        if (!grid) return;
        
        grid.innerHTML = '';
        
        if (requests.length === 0) {
            grid.style.display = 'none';
            emptyState.style.display = 'block';
            return;
        }
        
        grid.style.display = 'grid';
        emptyState.style.display = 'none';
        
        requests.forEach((request, index) => {
            const card = createRequestCard(request, index);
            grid.appendChild(card);
        });
    }
    
    function createRequestCard(request, index) {
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
            apaCitation = request.book_title || 'Unknown Title';
        }
        
        // Get department info from session
        const departmentCode = '<?php echo $_SESSION["selected_role"]["department_code"] ?? "CCS"; ?>';
        
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-request-id', request.id);
        card.style.animationDelay = (index * 0.08) + 's';
        
        // Format date
        const requestDate = new Date(request.request_date || new Date());
        const formattedDate = requestDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        // Status badge class
        const statusClass = request.status.toLowerCase();
        
        card.innerHTML = `
            <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name">${request.requester_name || 'Unknown Requester'}</div>
                    <div class="requester-role">${departmentCode} Faculty</div>
                </div>
                <span class="status-badge ${statusClass}">${request.status}</span>
            </div>

            <div class="course-info">
                <span class="course-code">${request.course_code || 'N/A'}</span>
                <span class="course-name">${request.course_name || 'Unknown Course'}</span>
            </div>
            
            <div class="request-summary">
                <div class="request-type">Course Material Request</div>
                <div class="material-title">${apaCitation}</div>
                <div class="material-details">
                    ${request.isbn ? `<span class="material-detail-item">ISBN: ${request.isbn}</span>` : ''}
                    ${request.publication_year ? `<span class="material-detail-item">Year: ${request.publication_year}</span>` : ''}
                    ${request.edition ? `<span class="material-detail-item">Edition: ${request.edition}</span>` : ''}
                </div>
            </div>

            ${request.status === 'PENDING' ? `
                <div class="action-buttons">
                    <button class="approve-btn" onclick="approveRequest(${request.id})">
                        <i data-lucide="check"></i>
                        Approve
                    </button>
                    <button class="reject-btn" onclick="rejectRequest(${request.id})">
                        <i data-lucide="x"></i>
                        Reject
                    </button>
                </div>
                <div class="request-date">Requested on: ${formattedDate}</div>
            ` : request.status === 'APPROVED' ? `
                <div class="action-buttons">
                    <button class="status-approved-btn" disabled>
                        <i data-lucide="check-circle"></i>
                        Approved
                    </button>
                </div>
                <div class="request-date">Approved on: ${formattedDate}</div>
            ` : `
                <div class="action-buttons">
                    <button class="status-rejected-btn" disabled>
                        <i data-lucide="x-circle"></i>
                        Rejected
                    </button>
                </div>
                <div class="request-date">Rejected on: ${formattedDate}</div>
            `}
        `;
        
        return card;
    }
    
    function approveRequest(requestId) {
        if (confirm('Are you sure you want to approve this course material request?')) {
            // Here you would typically make an API call to update the database
            // For now, just show success and refresh
            showNotification('Request approved successfully!', 'success');
            
            // Update the request status locally
            if (window.allRequests) {
                const request = window.allRequests.find(r => r.id === requestId);
                if (request) {
                    request.status = 'APPROVED';
                    request.request_date = new Date().toISOString();
                }
            }
            
            // Refresh the display
            applyFilters();
        }
    }
    
    function rejectRequest(requestId) {
        if (confirm('Are you sure you want to reject this course material request?')) {
            // Here you would typically make an API call to update the database
            // For now, just show success and refresh
            showNotification('Request rejected successfully!', 'error');
            
            // Update the request status locally
            if (window.allRequests) {
                const request = window.allRequests.find(r => r.id === requestId);
                if (request) {
                    request.status = 'REJECTED';
                    request.request_date = new Date().toISOString();
                }
            }
            
            // Refresh the display
            applyFilters();
        }
    }
    
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 12px;
            font-family: 'TT Interphases', sans-serif;
            font-weight: 600;
            font-size: 14px;
            z-index: 10000;
            animation: fadeSlideUp 0.3s ease-out;
            ${type === 'success' ? 'background: #0F7A53; color: white; border: 1px solid #0a5f42;' : 'background: #b91c1c; color: white; border: 1px solid #991b1b;'}
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'fadeSlideUp 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    function displayAllRequests() {
        // Initialize with empty array if not already set
        if (!window.allRequests) {
            window.allRequests = [];
        }
        
        // Update stats
        updateStats();
        
        // Set the Pending button as active and filter to show only pending requests
        const pendingTab = document.querySelector('.filter-tab[data-status="PENDING"]');
        if (pendingTab) {
            pendingTab.classList.add('active');
        }
        
        filterRequests('PENDING');
    }
</script>