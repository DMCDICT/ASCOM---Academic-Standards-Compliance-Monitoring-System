<?php
// Book Requests - Librarian View (Enhanced UX)
// Following DESIGN.md patterns
?>

<!-- Page Header -->
<div class="page-header-section">
    <div class="header-content">
        <h1 class="main-page-title">Book Requests</h1>
        <p class="page-description">Process book requests from department deans</p>
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
            <span class="stat-label">Done</span>
            <strong id="doneCount">0</strong>
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
        <button class="filter-tab" onclick="filterRequests('DONE')" data-status="DONE">
            <i data-lucide="check-circle" class="tab-icon"></i>
            <span class="tab-label">Done</span>
            <span class="tab-count" id="doneTabCount">0</span>
        </button>
    </div>
    
    <div class="search-filter-row">
        <div class="search-container">
            <div class="search-bar">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="requestSearchInput" placeholder="Search by title, author, or department..." oninput="searchRequests(this.value)">
            </div>
        </div>
        
        <div class="filter-group">
            <select id="departmentFilter" class="department-select" onchange="filterByDepartment(this.value)">
                <option value="">All Departments</option>
            </select>
        </div>
    </div>
</div>

<!-- Requests Container -->
<div class="requests-container">
    <!-- Department Groups -->
    <div id="departmentGroups" class="department-groups">
        <!-- Grouped requests will be displayed here -->
    </div>
    
    <!-- Empty State -->
    <div class="empty-state" id="requestsEmptyState" style="display: none;">
        <i data-lucide="inbox" class="empty-icon"></i>
        <h3>No requests found</h3>
        <p id="emptyStateMessage">No pending requests to process.</p>
    </div>
</div>

<!-- Request Details Modal -->
<div id="requestDetailsModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 600px; width: 95%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2 class="modal-title">Request Details</h2>
            <button class="modal-close" onclick="closeRequestDetailsModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <!-- Dean Info Section -->
            <div class="detail-section">
                <div class="detail-label">Requested By</div>
                <div class="dean-detail-card">
                    <div class="dean-avatar-large">
                        <i data-lucide="user"></i>
                    </div>
                    <div class="dean-info-content">
                        <div class="dean-name-large" id="detailDeanName">-</div>
                        <div class="dean-meta">
                            <span class="department-badge" id="detailDepartment">-</span>
                            <span class="request-date-detail" id="detailRequestDate">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Book Details -->
            <div class="detail-section">
                <div class="detail-label">Book Information</div>
                <div class="book-detail-card">
                    <div class="book-title-large" id="detailBookTitle">-</div>
                    <div class="book-author" id="detailAuthor">-</div>
                    
                    <div class="book-metadata" id="detailMetadata">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            
            <!-- Reason -->
            <div class="detail-section">
                <div class="detail-label">Reason for Request</div>
                <div class="reason-detail-card" id="detailReason">
                    -
                </div>
            </div>
            
            <!-- Processing Info (for done requests) -->
            <div class="detail-section" id="processingSection" style="display: none;">
                <div class="detail-label">Processing Info</div>
                <div class="processing-detail-card">
                    <div class="processing-status">
                        <i data-lucide="check-circle"></i>
                        <span>Completed</span>
                    </div>
                    <div class="processing-date" id="detailProcessedDate">-</div>
                </div>
            </div>
        </div>
        
        <div class="modal-actions" id="detailModalActions">
            <button type="button" class="btn-cancel" onclick="closeRequestDetailsModal()">Close</button>
            <button type="button" class="btn-create" id="detailActionBtn" onclick="processFromDetails()">
                <i data-lucide="check"></i>
                Mark as Done
            </button>
        </div>
    </div>
</div>

<!-- Confirm Done Modal -->
<div id="confirmDoneModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 480px; width: 90%;">
        <div class="modal-header">
            <h2 class="modal-title">Confirm Completion</h2>
            <button class="modal-close" onclick="closeConfirmDoneModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon">
                <i data-lucide="check-circle"></i>
            </div>
            <p class="confirm-message">Mark this book request as done?</p>
            <div id="doneRequestDetails" class="confirm-details">
                <!-- Populated by JS -->
            </div>
            <p class="confirm-note">The dean will be notified that their request has been processed.</p>
            <input type="hidden" id="doneRequestId">
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeConfirmDoneModal()">Cancel</button>
            <button type="button" class="btn-create" onclick="confirmMarkDone()">
                <i data-lucide="check"></i>
                Mark as Done
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 380px; width: 90%;">
        <div class="modal-content-center">
            <div class="icon-container" style="width: 64px; height: 64px; margin: 0 auto 16px;">
                <i data-lucide="check-circle" style="width: 32px; height: 32px; color: #2E7D32;"></i>
            </div>
            <h3 id="successTitle" style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 8px 0;">Success!</h3>
            <p id="successMessage" style="font-size: 14px; color: rgba(17, 24, 39, 0.6); margin: 0 0 24px 0;">Request processed successfully.</p>
            <button class="btn-primary" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 380px; width: 90%;">
        <div class="modal-content-center">
            <div class="icon-container" style="width: 64px; height: 64px; margin: 0 auto 16px; background: rgba(185, 28, 28, 0.1);">
                <i data-lucide="alert-circle" style="width: 32px; height: 32px; color: #b91c1c;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 8px 0;">Error</h3>
            <p id="errorMessage" style="font-size: 14px; color: rgba(17, 24, 39, 0.6); margin: 0 0 24px 0;">An error occurred.</p>
            <button class="btn-cancel" onclick="closeErrorModal()">Close</button>
        </div>
    </div>
</div>

<style>
    /* ====== DESIGN.md COMPLIANT STYLES ====== */
    
    /* Page Header Section */
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
    
    /* Header Stats - Single row with gap */
    .header-stats {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }
    
    /* Stats Pills */
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
    
    .stat-pill.stat-pending strong {
        color: #e65100;
    }
    
    .stat-pill.stat-approved strong {
        color: #2E7D32;
    }
    
    /* Action Bar */
    .action-bar {
        margin-bottom: 20px;
        animation: slideDown 0.25s ease-out;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .bulk-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 16px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 12px;
        border: 1px solid rgba(12, 75, 52, 0.12);
    }
    
    .selected-count {
        font-size: 14px;
        font-weight: 700;
        color: #0C4B34;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-bulk-done {
        background: #0F7A53;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'TT Interphases', sans-serif;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
    }
    
    .btn-bulk-done:hover {
        background: #0a5f42;
        transform: translateY(-1px);
    }
    
    .btn-bulk-done i[data-lucide] {
        width: 16px;
        height: 16px;
    }
    
    .btn-bulk-clear {
        background: transparent;
        color: rgba(17, 24, 39, 0.6);
        border: 1px solid rgba(17, 24, 39, 0.2);
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'TT Interphases', sans-serif;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
    }
    
    .btn-bulk-clear:hover {
        background: rgba(17, 24, 39, 0.04);
    }
    
    .btn-bulk-clear i[data-lucide] {
        width: 16px;
        height: 16px;
    }
    
    /* Filter Tabs */
    .filter-tabs-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    
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
    
    /* Search and Filter Row */
    .search-filter-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
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
        min-width: 280px;
    }
    
    .search-bar:focus-within {
        border-color: #0C4B34;
        box-shadow: 0 0 0 3px rgba(12, 75, 52, 0.1);
    }
    
    .search-bar .search-icon {
        color: rgba(17, 24, 39, 0.4);
        margin-right: 10px;
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
    
    .filter-group {
        flex-shrink: 0;
    }
    
    .department-select {
        height: 44px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid rgba(12, 75, 52, 0.14);
        background: #FFFFFF;
        font-size: 14px;
        font-family: 'TT Interphases', sans-serif;
        color: rgba(17, 24, 39, 0.7);
        cursor: pointer;
        min-width: 180px;
    }
    
    .department-select:focus {
        outline: none;
        border-color: #0C4B34;
        box-shadow: 0 0 0 3px rgba(12, 75, 52, 0.1);
    }
    
    /* Department Groups */
    .department-groups {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    
    .department-group {
        background: transparent;
    }
    
    .department-group-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid rgba(12, 75, 52, 0.1);
    }
    
    .department-group-icon {
        width: 40px;
        height: 40px;
        background: rgba(12, 75, 52, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .department-group-icon i[data-lucide] {
        width: 20px;
        height: 20px;
        color: #0C4B34;
    }
    
    .department-group-title {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .department-group-count {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        background: rgba(12, 75, 52, 0.06);
        padding: 4px 10px;
        border-radius: 8px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }
    
    /* Request Card */
    .request-card {
        background: #ffffff;
        border-radius: 16px 18px;
        border: 1px solid rgba(12, 75, 52, 0.14);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        padding: 20px 22px;
        transition: all 0.28s cubic-bezier(.4,0,.2,1);
        animation: fadeSlideUp 0.45s ease-out both;
        position: relative;
        cursor: pointer;
    }
    
    .request-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
        border-color: rgba(12, 75, 52, 0.25);
    }
    
    .request-card:nth-child(1) { animation-delay: 0.08s; }
    .request-card:nth-child(2) { animation-delay: 0.16s; }
    .request-card:nth-child(3) { animation-delay: 0.24s; }
    .request-card:nth-child(4) { animation-delay: 0.32s; }
    
    /* Dean Info */
    .dean-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(12, 75, 52, 0.08);
    }
    
    .dean-avatar {
        width: 36px;
        height: 36px;
        background: rgba(12, 75, 52, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .dean-avatar i {
        width: 18px;
        height: 18px;
        color: #0C4B34;
    }
    
    .dean-name {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .department-badge {
        font-size: 11px;
        color: #0C4B34;
        background: rgba(12, 75, 52, 0.1);
        padding: 3px 8px;
        border-radius: 6px;
        font-family: 'TT Interphases', sans-serif;
        font-weight: 700;
    }
    
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    
    .request-book-title {
        font-size: 15px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 4px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .request-author {
        font-size: 13px;
        color: rgba(17, 24, 39, 0.6);
        font-family: 'TT Interphases', sans-serif;
    }
    
    .status-badge {
        padding: 5px 10px;
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
    
    .status-badge.done {
        background: rgba(46, 125, 50, 0.12);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.2);
    }
    
    .request-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 10px 0;
    }
    
    .request-detail-item {
        font-size: 11px;
        color: rgba(17, 24, 39, 0.6);
        background: rgba(12, 75, 52, 0.04);
        padding: 4px 10px;
        border-radius: 6px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .request-reason {
        font-size: 13px;
        color: rgba(17, 24, 39, 0.7);
        font-family: 'TT Interphases', sans-serif;
        margin: 12px 0;
        padding: 12px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 8px;
    }
    
    .request-reason-label {
        font-size: 10px;
        font-weight: 700;
        color: rgba(17, 24, 39, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .request-date {
        font-size: 11px;
        color: rgba(17, 24, 39, 0.4);
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        margin-top: 12px;
    }
    
    /* Action Buttons */
    .request-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(12, 75, 52, 0.08);
    }
    
    .action-btn {
        flex: 1;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .action-btn i[data-lucide] {
        width: 18px;
        height: 18px;
    }
    
    .done-action-btn {
        background: #0F7A53;
        color: white;
        border: none;
    }
    
    .done-action-btn:hover {
        background: #0a5f42;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(15, 122, 83, 0.25);
    }
    
    .view-btn {
        background: #fff;
        color: #0C4B34;
        border: 1px solid #0C4B34;
    }
    
    .view-btn:hover {
        background: rgba(12, 75, 52, 0.05);
    }
    
    .done-btn {
        background: rgba(46, 125, 50, 0.12);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.2);
        cursor: default;
    }
    
    /* Buttons */
    .btn-primary {
        background: #0C4B34;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-primary:hover {
        background: #0a3a28;
    }
    
    .btn-cancel {
        background-color: #C9C9C9;
        color: black;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-cancel:hover {
        background-color: #B9B9B9;
    }
    
    .btn-create {
        background-color: #0F7A53;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-create:hover {
        background-color: #0a5f42;
    }
    
    .btn-create i[data-lucide] {
        width: 18px;
        height: 18px;
    }
    
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 20px;
    }
    
    .modal-box {
        background-color: #ffffff;
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        animation: modalPop 0.18s ease-out;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 24px;
        background: linear-gradient(0deg, rgba(12, 75, 52, 0.08), rgba(12, 75, 52, 0.08)), #ffffff;
        border-bottom: 1px solid rgba(12, 75, 52, 0.14);
    }
    
    .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }
    
    .modal-close {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid rgba(12, 75, 52, 0.16);
        background: rgba(12, 75, 52, 0.06);
        color: #0C4B34;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-close:hover {
        background: rgba(12, 75, 52, 0.1);
    }
    
    .modal-close i[data-lucide] {
        width: 20px;
        height: 20px;
    }
    
    .modal-body {
        padding: 24px;
    }
    
    .modal-body p {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.7);
        margin: 0;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .modal-content-center {
        text-align: center;
        padding: 32px;
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(12, 75, 52, 0.1);
    }
    
    /* Detail Modal Styles */
    .detail-section {
        margin-bottom: 20px;
    }
    
    .detail-label {
        font-size: 11px;
        font-weight: 700;
        color: rgba(17, 24, 39, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .dean-detail-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 12px;
    }
    
    .dean-avatar-large {
        width: 48px;
        height: 48px;
        background: rgba(12, 75, 52, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .dean-avatar-large i[data-lucide] {
        width: 24px;
        height: 24px;
        color: #0C4B34;
    }
    
    .dean-info-content {
        flex: 1;
    }
    
    .dean-name-large {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .dean-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
    }
    
    .request-date-detail {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        font-family: 'TT Interphases', sans-serif;
    }
    
    .book-detail-card {
        padding: 16px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 12px;
    }
    
    .book-title-large {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        font-family: 'TT Interphases', sans-serif;
        margin-bottom: 4px;
    }
    
    .book-author {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.6);
        font-family: 'TT Interphases', sans-serif;
    }
    
    .book-metadata {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    
    .book-metadata-item {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.6);
        background: rgba(12, 75, 52, 0.08);
        padding: 4px 10px;
        border-radius: 6px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .reason-detail-card {
        padding: 16px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 12px;
        font-size: 14px;
        color: rgba(17, 24, 39, 0.7);
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.5;
    }
    
    .processing-detail-card {
        padding: 16px;
        background: rgba(46, 125, 50, 0.08);
        border-radius: 12px;
        border: 1px solid rgba(46, 125, 50, 0.2);
    }
    
    .processing-status {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #2E7D32;
        font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .processing-status i[data-lucide] {
        width: 20px;
        height: 20px;
    }
    
    .processing-date {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        margin-top: 6px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    /* Confirm Modal */
    .confirm-icon {
        width: 64px;
        height: 64px;
        background: rgba(12, 75, 52, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    
    .confirm-icon i[data-lucide] {
        width: 32px;
        height: 32px;
        color: #0C4B34;
    }
    
    .confirm-message {
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }
    
    .confirm-details {
        padding: 16px;
        background: rgba(12, 75, 52, 0.04);
        border-radius: 12px;
        margin-bottom: 12px;
    }
    
    .confirm-note {
        text-align: center;
        font-size: 13px;
        color: rgba(17, 24, 39, 0.5);
    }
    
    .icon-container {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(12, 75, 52, 0.08);
        color: #0C4B34;
    }
    
    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    
    .empty-icon {
        width: 64px;
        height: 64px;
        color: rgba(17, 24, 39, 0.2);
        margin-bottom: 16px;
    }
    
    .empty-state h3 {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px 0;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .empty-state p {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.6);
        margin: 0;
        font-family: 'TT Interphases', sans-serif;
    }
    
    /* Animations */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(18px) scale(0.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    @keyframes modalPop {
        from { opacity: 0; transform: translateY(10px) scale(0.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-tabs-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-tabs {
            width: 100%;
            overflow-x: auto;
        }
        
        .search-filter-row {
            flex-direction: column;
        }
        
        .search-container, .filter-group {
            width: 100%;
        }
        
        .search-bar, .department-select {
            min-width: 100%;
        }
        
        .requests-grid {
            grid-template-columns: 1fr;
        }
        
        /* (batch actions removed) */
    }
</style>

<script>
    let currentFilter = 'PENDING';
    let searchQuery = '';
    let departmentFilter = '';
    let allRequests = [];
    let currentDetailRequest = null;
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadBookRequests();
    });
    
    // Load book requests
    function loadBookRequests(status = 'PENDING') {
        fetch(`api/get_pending_book_requests.php?status=${status}`, {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allRequests = data.data;
                    updateStats(data.counts);
                    populateDepartmentFilter();
                    applyFilters();
                }
            })
            .catch(error => {
                console.error('Error loading requests:', error);
            });
    }
    
    // Update stats
    function updateStats(counts) {
        document.getElementById('totalCount').textContent = counts.total || 0;
        document.getElementById('pendingCount').textContent = counts.pending || 0;
        document.getElementById('doneCount').textContent = counts.done || 0;
        
        document.getElementById('pendingTabCount').textContent = counts.pending || 0;
        document.getElementById('doneTabCount').textContent = counts.done || 0;
    }
    
    // Populate department filter
    function populateDepartmentFilter() {
        const select = document.getElementById('departmentFilter');
        const departments = [...new Set(allRequests.map(r => r.department_code).filter(Boolean))];
        
        // Keep first option
        select.innerHTML = '<option value="">All Departments</option>';
        
        departments.forEach(dept => {
            const option = document.createElement('option');
            option.value = dept;
            option.textContent = dept;
            select.appendChild(option);
        });
    }
    
    // Filter requests
    function filterRequests(status) {
        currentFilter = status;
        
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        const targetTab = document.querySelector(`.filter-tab[data-status="${status}"]`);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        
        loadBookRequests(status);
    }
    
    // Filter by department
    function filterByDepartment(dept) {
        departmentFilter = dept;
        applyFilters();
    }
    
    // Search requests
    function searchRequests(query) {
        searchQuery = query.trim().toLowerCase();
        applyFilters();
    }
    
    // Apply filters
    function applyFilters() {
        let filteredRequests = allRequests;
        
        // Apply search filter
        if (searchQuery) {
            filteredRequests = filteredRequests.filter(r => {
                const searchFields = [
                    r.book_title,
                    r.author,
                    r.department_code,
                    r.dean_name
                ].map(f => (f || '').toLowerCase());
                return searchFields.some(field => field.includes(searchQuery));
            });
        }
        
        // Apply department filter
        if (departmentFilter) {
            filteredRequests = filteredRequests.filter(r => r.department_code === departmentFilter);
        }
        
        renderRequests(filteredRequests);
    }
    
    // Render requests grouped by department
    function renderRequests(requests) {
        const container = document.getElementById('departmentGroups');
        const emptyState = document.getElementById('requestsEmptyState');
        
        if (!container) return;
        
        container.innerHTML = '';
        
        if (requests.length === 0) {
            container.style.display = 'none';
            emptyState.style.display = 'block';
            
            const messages = {
                'PENDING': 'No pending requests to process.',
                'DONE': 'No completed requests.'
            };
            document.getElementById('emptyStateMessage').textContent = messages[currentFilter] || 'No requests found.';
            return;
        }
        
        container.style.display = 'flex';
        emptyState.style.display = 'none';
        
        // Group by department
        const grouped = {};
        requests.forEach(request => {
            const dept = request.department_code || 'Unknown';
            if (!grouped[dept]) {
                grouped[dept] = [];
            }
            grouped[dept].push(request);
        });
        
        // Render groups
        Object.keys(grouped).forEach((dept, groupIndex) => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'department-group';
            
            const groupRequests = grouped[dept];
            
            groupDiv.innerHTML = `
                <div class="department-group-header">
                    <div class="department-group-icon">
                        <i data-lucide="building-2"></i>
                    </div>
                    <span class="department-group-title">${dept}</span>
                    <span class="department-group-count">${groupRequests.length} request${groupRequests.length !== 1 ? 's' : ''}</span>
                </div>
                <div class="requests-grid">
                    ${groupRequests.map((request, index) => createRequestCardHTML(request, groupIndex * 10 + index)).join('')}
                </div>
            `;
            
            container.appendChild(groupDiv);
        });
        
        // Re-initialize icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }
    
    // Create request card HTML
    function createRequestCardHTML(request, index) {
        const isPending = request.status === 'PENDING';
        
        const requestedDate = new Date(request.requested_at);
        const formattedDate = requestedDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        let actionButtonsHtml = '';
        if (isPending) {
            actionButtonsHtml = `
                <div class="request-actions">
                    <button class="action-btn view-btn" onclick="event.stopPropagation(); openRequestDetailsModal(${request.id})">
                        <i data-lucide="eye"></i>
                        View Details
                    </button>
                    <button class="action-btn done-action-btn" onclick="event.stopPropagation(); openConfirmDoneModal(${request.id})">
                        <i data-lucide="check"></i>
                        Mark Done
                    </button>
                </div>
            `;
        } else {
            actionButtonsHtml = `
                <div class="request-actions">
                    <button class="action-btn view-btn" onclick="event.stopPropagation(); openRequestDetailsModal(${request.id})">
                        <i data-lucide="eye"></i>
                        View Details
                    </button>
                    <button class="action-btn done-btn" disabled>
                        <i data-lucide="check-circle"></i>
                        Completed
                    </button>
                </div>
            `;
        }
        
        return `
            <div class="request-card ${request.status.toLowerCase()}" 
                 style="animation-delay: ${index * 0.08}s"
                 onclick="openRequestDetailsModal(${request.id})">
                
                <div class="dean-info">
                    <div class="dean-avatar">
                        <i data-lucide="user"></i>
                    </div>
                    <div>
                        <div class="dean-name">${request.dean_name || 'Unknown Dean'}</div>
                        <span class="department-badge">${request.department_code}</span>
                    </div>
                </div>
                
                <div class="request-header">
                    <div>
                        <div class="request-book-title">${request.book_title}</div>
                        <div class="request-author">by ${request.author}</div>
                    </div>
                    <span class="status-badge ${request.status.toLowerCase()}">${request.status === 'DONE' ? 'Done' : 'Pending'}</span>
                </div>
                
                <div class="request-details">
                    ${request.isbn ? `<span class="request-detail-item">ISBN: ${request.isbn}</span>` : ''}
                    ${request.publication_year ? `<span class="request-detail-item">Year: ${request.publication_year}</span>` : ''}
                    ${request.publisher ? `<span class="request-detail-item">${request.publisher}</span>` : ''}
                </div>
                
                <div class="request-reason">
                    <div class="request-reason-label">Reason for Request</div>
                    ${request.reason}
                </div>
                
                <div class="request-date">Requested on: ${formattedDate}</div>
                
                ${actionButtonsHtml}
            </div>
        `;
    }
    
    // Open request details modal
    function openRequestDetailsModal(requestId) {
        const request = allRequests.find(r => r.id === requestId);
        if (!request) return;
        
        currentDetailRequest = request;
        
        // Populate details
        document.getElementById('detailDeanName').textContent = request.dean_name || 'Unknown Dean';
        document.getElementById('detailDepartment').textContent = request.department_code || '-';
        
        const requestedDate = new Date(request.requested_at);
        document.getElementById('detailRequestDate').textContent = requestedDate.toLocaleDateString('en-US', { 
            year: 'numeric', month: 'long', day: 'numeric' 
        });
        
        document.getElementById('detailBookTitle').textContent = request.book_title || '-';
        document.getElementById('detailAuthor').textContent = `by ${request.author || '-'}`;
        
        // Build metadata
        let metadataHtml = '';
        if (request.isbn) metadataHtml += `<span class="book-metadata-item">ISBN: ${request.isbn}</span>`;
        if (request.publication_year) metadataHtml += `<span class="book-metadata-item">Year: ${request.publication_year}</span>`;
        if (request.publisher) metadataHtml += `<span class="book-metadata-item">${request.publisher}</span>`;
        if (request.edition) metadataHtml += `<span class="book-metadata-item">${request.edition}</span>`;
        document.getElementById('detailMetadata').innerHTML = metadataHtml || '<span class="book-metadata-item">No additional info</span>';
        
        document.getElementById('detailReason').textContent = request.reason || '-';
        
        // Show/hide processing section
        const processingSection = document.getElementById('processingSection');
        const detailModalActions = document.getElementById('detailModalActions');
        const detailActionBtn = document.getElementById('detailActionBtn');
        
        if (request.status === 'DONE') {
            processingSection.style.display = 'block';
            if (request.processed_at) {
                const processedDate = new Date(request.processed_at);
                document.getElementById('detailProcessedDate').textContent = `Completed on ${processedDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
            }
            detailActionBtn.style.display = 'none';
        } else {
            processingSection.style.display = 'none';
            detailActionBtn.style.display = 'flex';
        }
        
        document.getElementById('requestDetailsModal').style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    
    // Close request details modal
    function closeRequestDetailsModal() {
        document.getElementById('requestDetailsModal').style.display = 'none';
        currentDetailRequest = null;
    }
    
    // Process from details
    function processFromDetails() {
        if (currentDetailRequest) {
            openConfirmDoneModal(currentDetailRequest.id);
            closeRequestDetailsModal();
        }
    }
    
    // Open confirm done modal
    function openConfirmDoneModal(requestId) {
        const request = allRequests.find(r => r.id === requestId);
        if (!request) return;
        
        document.getElementById('doneRequestId').value = requestId;
        document.getElementById('doneRequestDetails').innerHTML = `
            <strong>${request.book_title}</strong><br>
            <span style="color: rgba(17,24,39,0.6); font-size: 13px;">by ${request.author}</span><br>
            <span style="color: rgba(17,24,39,0.5); font-size: 12px;">From: ${request.department_code}</span>
        `;
        document.getElementById('confirmDoneModal').style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    
    // Close confirm done modal
    function closeConfirmDoneModal() {
        document.getElementById('confirmDoneModal').style.display = 'none';
    }
    
    // Confirm mark as done
    function confirmMarkDone() {
        const requestId = document.getElementById('doneRequestId').value;
        
        fetch('api/mark_book_request_done.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ request_id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeConfirmDoneModal();
                showSuccessModal('Request Completed', data.message);
                loadBookRequests(currentFilter);
            } else {
                showErrorModal(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('Failed to process request. Please try again.');
        });
    }
    
    // Show success modal
    function showSuccessModal(title, message) {
        document.getElementById('successTitle').textContent = title;
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    
    // Close success modal
    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }
    
    // Show error modal
    function showErrorModal(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('errorModal').style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    
    // Close error modal
    function closeErrorModal() {
        document.getElementById('errorModal').style.display = 'none';
    }
</script>
