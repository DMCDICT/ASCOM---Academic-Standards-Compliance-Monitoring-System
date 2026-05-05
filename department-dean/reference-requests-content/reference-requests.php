<?php
// Book Request System - Dean View
// Following DESIGN.md patterns

$departmentCode = $_SESSION["selected_role"]["department_code"] ?? "CCS";
?>

<!-- Page Header -->
<div class="page-header-section">
    <div class="header-content">
        <h1 class="main-page-title">Book Requests</h1>
        <p class="page-description">Request books from the library to maintain department compliance</p>
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

<div class="main-page-content-container">
    <!-- Action Bar -->
    <div class="action-bar">
        <button class="btn-primary" onclick="openRequestModal()">
            <i data-lucide="plus"></i>
            Request New Book
        </button>
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
    </div>
    
    <!-- Requests Grid -->
    <div class="requests-container">
        <div id="requestsGrid" class="requests-grid">
            <!-- Requests will be displayed here -->
        </div>
        
        <!-- Empty State -->
        <div class="empty-state" id="requestsEmptyState" style="display: none;">
            <div class="empty-icon-wrapper">
                <i data-lucide="inbox" class="empty-icon"></i>
            </div>
            <h3>No requests found</h3>
            <p id="emptyStateMessage">You don't have any pending requests.</p>
        </div>
    </div>
</div>

<!-- Request Book Modal -->
<div id="requestBookModal" class="modal-overlay" style="display: none;">
    <div class="premium-modal">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="icon-container" style="width: 40px; height: 40px; background: rgba(12, 75, 52, 0.08); color: #0C4B34; border-radius: 10px;">
                    <i data-lucide="book-plus"></i>
                </div>
                <h2 class="modal-title" id="modalTitleText">Request New Book</h2>
            </div>
            <button class="modal-close" onclick="closeRequestModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body" style="overflow-y: auto; padding: 28px;">
            <p class="modal-description">Fill out the details below to request a new book. Our library team will review your request for department compliance requirements.</p>
            
            <form id="requestBookForm" onsubmit="submitBookRequest(event)">
                <input type="hidden" id="editRequestId" name="request_id">
                
                <div class="form-group">
                    <label class="required-field">Book Title</label>
                    <input type="text" id="requestBookTitle" name="book_title" required placeholder="Enter the full title of the book">
                </div>
                
                <div class="form-group">
                    <label class="required-field">Primary Author</label>
                    <input type="text" id="requestAuthor" name="author" required placeholder="Enter the main author's name">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ISBN (if known)</label>
                        <input type="text" id="requestIsbn" name="isbn" placeholder="e.g., 978-0-123456-78-9">
                    </div>
                    <div class="form-group">
                        <label>Publication Year</label>
                        <input type="text" id="requestYear" name="publication_year" placeholder="e.g., 2024">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" id="requestPublisher" name="publisher" placeholder="Enter publisher name">
                    </div>
                    <div class="form-group">
                        <label>Edition</label>
                        <input type="text" id="requestEdition" name="edition" placeholder="e.g., 2nd Edition">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="required-field">Reason for Request</label>
                    <textarea id="requestReason" name="reason" required rows="4" placeholder="Briefly explain why this book is needed for compliance or a specific course..."></textarea>
                    <span class="form-hint">This helps prioritize requests based on academic needs.</span>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRequestModal()" style="height: 48px; min-width: 120px;">Cancel</button>
                    <button type="submit" class="btn-create" id="submitBtnText" style="height: 48px; min-width: 160px;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Cancel Modal -->
<div id="confirmCancelModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; width: 90%;">
        <div class="modal-header">
            <h2 class="modal-title">Cancel Request?</h2>
            <button class="modal-close" onclick="closeConfirmCancelModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel this book request? This action cannot be undone.</p>
            <input type="hidden" id="cancelRequestId">
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeConfirmCancelModal()">Keep Request</button>
            <button type="button" class="btn-danger" onclick="confirmCancelRequest()">Yes, Cancel</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 400px; width: 90%;">
        <div class="modal-content-center">
            <div class="success-icon-wrapper" style="margin-bottom: 20px;">
                <div class="icon-container" style="width: 80px; height: 80px; margin: 0 auto; background: rgba(46, 125, 50, 0.1); border-radius: 50%;">
                    <i data-lucide="check-circle" style="width: 40px; height: 40px; color: #2E7D32;"></i>
                </div>
            </div>
            <h3 id="successTitle" style="font-size: 20px; font-weight: 800; color: #111827; margin: 0 0 10px 0;">Success!</h3>
            <p id="successMessage" style="font-size: 15px; color: rgba(17, 24, 39, 0.6); margin: 0 0 30px 0; line-height: 1.5;">Request submitted successfully.</p>
            <button class="btn-primary" onclick="closeSuccessModal()" style="width: 100%; justify-content: center; height: 48px;">Continue</button>
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
    
    /* Header Stats - DESIGN.md Section 3.4 */
    .header-stats {
        display: flex;
        gap: 12px;
        flex-shrink: 0;
    }
    
    .stat-pill {
        background: rgba(12, 75, 52, 0.04);
        border: 1px solid rgba(12, 75, 52, 0.08);
        color: rgba(17, 24, 39, 0.6);
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.22s ease;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .stat-pill:hover {
        background: rgba(12, 75, 52, 0.08);
        transform: translateY(-1px);
    }
    
    .stat-pill strong {
        color: #0C4B34;
        font-weight: 800;
        font-size: 13px;
    }
    
    .stat-pill.stat-pending {
        border-color: rgba(230, 81, 0, 0.15);
        background: rgba(230, 81, 0, 0.04);
    }
    
    .stat-pill.stat-pending strong {
        color: #e65100;
    }
    
    .stat-pill.stat-approved {
        border-color: rgba(46, 125, 50, 0.15);
        background: rgba(46, 125, 50, 0.04);
    }
    
    .stat-pill.stat-approved strong {
        color: #2E7D32;
    }
    
    /* Action Bar */
    .action-bar {
        margin-bottom: 24px;
    }
    
    .btn-primary {
        background: #0C4B34;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.2px;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-primary:hover {
        background: #0a3a28;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
    }
    
    .btn-primary:active {
        transform: translateY(0) scale(0.98);
    }
    
    .btn-primary i[data-lucide] {
        width: 18px;
        height: 18px;
    }
    
    /* Cancel Button */
    .btn-cancel {
        background-color: #C9C9C9;
        color: black;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: background-color 0.3s;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-cancel:hover {
        background-color: #B9B9B9;
    }
    
    /* Create/Submit Button */
    .btn-create {
        background-color: #0F7A53;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: background-color 0.3s;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-create:hover:enabled {
        background-color: #0a5f42;
    }
    
    /* Danger Button */
    .btn-danger {
        background-color: #b91c1c;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        transition: background-color 0.3s;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .btn-danger:hover {
        background-color: #991b1b;
    }
    
    /* Filter Tabs - DESIGN.md Section 3.4 */
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
        gap: 6px;
        background: rgba(12, 75, 52, 0.04);
        padding: 5px;
        border-radius: 14px;
        border: 1px solid rgba(12, 75, 52, 0.06);
    }
    
    .filter-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        background: transparent;
        color: rgba(17, 24, 39, 0.5);
        border-radius: 11px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
    }
    
    .filter-tab:hover {
        color: #0C4B34;
        background: rgba(12, 75, 52, 0.06);
    }
    
    .filter-tab.active {
        background: #0C4B34;
        color: white;
        box-shadow: 0 4px 15px rgba(12, 75, 52, 0.22);
    }
    
    .filter-tab .tab-icon {
        width: 17px;
        height: 17px;
    }
    
    .filter-tab .tab-count {
        background: rgba(12, 75, 52, 0.12);
        padding: 2px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
        color: #0C4B34;
    }
    
    .filter-tab.active .tab-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    /* Search Bar - DESIGN.md Section 7.2 */
    .search-container {
        flex-shrink: 0;
    }
    
    .search-bar {
        display: flex;
        align-items: center;
        background-color: #FFFFFF;
        height: 48px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid rgba(12, 75, 52, 0.12);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        transition: all 0.25s ease;
        min-width: 300px;
    }
    
    .search-bar:focus-within {
        border-color: #0C4B34;
        box-shadow: 0 8px 24px rgba(12, 75, 52, 0.1);
        transform: translateY(-1px);
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
    
    /* Requests Grid - DESIGN.md Section 3.1 Cards */
    .requests-container {
        margin-top: 0;
    }
    
    .requests-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }
    
    /* Request Card - DESIGN.md Section 3.1 */
    .request-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid rgba(12, 75, 52, 0.12);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
        padding: 16px;
        transition: all 0.28s cubic-bezier(.4,0,.2,1);
        animation: fadeSlideUp 0.45s ease-out both;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .request-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 40px rgba(12, 75, 52, 0.12);
        border-color: rgba(12, 75, 52, 0.22);
    }
    
    /* Stagger animation - 80ms increments */
    .request-card:nth-child(1) { animation-delay: 0.08s; }
    .request-card:nth-child(2) { animation-delay: 0.16s; }
    .request-card:nth-child(3) { animation-delay: 0.24s; }
    .request-card:nth-child(4) { animation-delay: 0.32s; }
    .request-card:nth-child(5) { animation-delay: 0.40s; }
    .request-card:nth-child(6) { animation-delay: 0.48s; }
    
    .request-header {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
    }
    
    .request-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(12, 75, 52, 0.08);
        color: #0C4B34;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s cubic-bezier(.4,0,.2,1);
        border: 1px solid rgba(12, 75, 52, 0.05);
    }
    
    .request-card:hover .request-icon-box {
        transform: scale(1.05) rotate(-3deg);
        background: rgba(12, 75, 52, 0.12);
    }
    
    .request-card.pending .request-icon-box {
        background: rgba(230, 81, 0, 0.08);
        color: #e65100;
        border-color: rgba(230, 81, 0, 0.1);
    }
    
    .request-card.done .request-icon-box {
        background: rgba(46, 125, 50, 0.08);
        color: #2E7D32;
        border-color: rgba(46, 125, 50, 0.1);
    }
    
    .request-icon-box i[data-lucide] {
        width: 18px;
        height: 18px;
    }
    
    .request-info {
        flex: 1;
        min-width: 0;
    }
    
    .request-book-title {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 2px;
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
    }
    
    .request-author {
        font-size: 12px;
        color: rgba(17, 24, 39, 0.5);
        font-family: 'TT Interphases', sans-serif;
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    
    /* Status Badge - DESIGN.md Section 3.4 */
    .status-badge {
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-family: 'TT Interphases', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .status-badge.pending {
        background: rgba(230, 81, 0, 0.1);
        color: #e65100;
        border: 1px solid rgba(230, 81, 0, 0.15);
    }
    
    .status-badge.pending::before {
        content: '';
        width: 6px;
        height: 6px;
        background: #e65100;
        border-radius: 50%;
        animation: statusPulse 2s ease-in-out infinite;
    }
    
    .status-badge.done {
        background: rgba(46, 125, 50, 0.1);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.15);
    }
    
    .request-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 12px 0;
    }
    
    .request-detail-item {
        font-size: 10px;
        color: rgba(17, 24, 39, 0.6);
        background: rgba(12, 75, 52, 0.04);
        padding: 3px 8px;
        border-radius: 6px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .request-reason {
        font-size: 12px;
        color: #374151;
        font-family: 'TT Interphases', sans-serif;
        margin: 12px 0;
        padding: 10px 12px;
        background: rgba(12, 75, 52, 0.03);
        border-radius: 10px;
        line-height: 1.4;
        font-weight: 500;
        position: relative;
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
        text-align: right;
        font-weight: 600;
    }
    
    .processed-on {
        font-size: 11px;
        color: #2E7D32;
        font-family: 'TT Interphases', sans-serif;
        text-align: right;
        font-weight: 700;
        margin-top: 2px;
    }
    
    /* Action Buttons */
    .request-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(12, 75, 52, 0.08);
    }
    
    .action-btn {
        flex: 1;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
        font-family: 'TT Interphases', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .action-btn i[data-lucide] {
        width: 16px;
        height: 16px;
    }
    
    .edit-btn {
        background: #fff;
        color: #0C4B34;
        border: 1px solid #0C4B34;
    }
    
    .edit-btn:hover {
        background: rgba(12, 75, 52, 0.05);
    }
    
    .cancel-btn-action {
        background: #fff;
        color: #b91c1c;
        border: 1px solid #b91c1c;
    }
    
    .cancel-btn-action:hover {
        background: #b91c1c;
        color: white;
    }
    
    .done-btn {
        background: rgba(46, 125, 50, 0.12);
        color: #2E7D32;
        border: 1px solid rgba(46, 125, 50, 0.2);
        cursor: default;
    }
    
    /* Modal Styles - DESIGN.md Section 5 */
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
    
    .modal-overlay.is-open {
        display: flex;
    }
    
    .modal-box {
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        animation: modalPop 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(12, 75, 52, 0.1);
    }
    
    .premium-modal {
        width: min(720px, calc(100vw - 28px));
        max-height: min(90vh, 850px);
        background: #ffffff;
        border: 1px solid rgba(12, 75, 52, 0.18);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 22px 64px rgba(0, 0, 0, 0.28);
        display: flex;
        flex-direction: column;
        animation: modalPop 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 28px;
        background: linear-gradient(0deg, rgba(12, 75, 52, 0.05), rgba(12, 75, 52, 0.05)), #ffffff;
        border-bottom: 1px solid rgba(12, 75, 52, 0.12);
    }
    
    .modal-title {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin: 0;
        letter-spacing: -0.3px;
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
        transition: background 0.15s ease;
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
    
    .modal-description {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.6);
        margin: 0 0 20px 0;
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
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid rgba(12, 75, 52, 0.1);
    }
    
    /* Form Styles - DESIGN.md Section 7 */
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: rgba(17, 24, 39, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .form-group label.required-field::after {
        content: " *";
        color: #b91c1c;
    }
    
    .form-group input,
    .form-group textarea {
        background-color: #FFFFFF;
        border: 1px solid #ccc;
        border-radius: 12px;
        height: 48px;
        padding: 0 14px;
        font-size: 14px;
        font-family: 'TT Interphases', sans-serif;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.2s ease;
    }
    
    .form-group textarea {
        height: auto;
        padding: 12px 14px;
        resize: vertical;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #0C4B34;
        outline: none;
    }
    
    .form-hint {
        display: block;
        font-size: 11px;
        color: rgba(17, 24, 39, 0.5);
        margin-top: 4px;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .form-row {
        display: flex;
        gap: 16px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .empty-state {
        padding: 80px 20px;
        text-align: center;
        background: rgba(12, 75, 52, 0.02);
        border-radius: 20px;
        border: 2px dashed rgba(12, 75, 52, 0.08);
        margin-top: 20px;
    }
    
    .empty-icon-wrapper {
        width: 80px;
        height: 80px;
        background: rgba(12, 75, 52, 0.05);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: rgba(12, 75, 52, 0.2);
    }
    
    .empty-icon {
        width: 40px;
        height: 40px;
    }
    
    .empty-state h3 {
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 10px 0;
        font-family: 'TT Interphases', sans-serif;
    }
    
    .empty-state p {
        font-size: 14px;
        color: rgba(17, 24, 39, 0.5);
        margin: 0;
        max-width: 300px;
        margin: 0 auto;
        line-height: 1.5;
        font-weight: 500;
    }
        font-family: 'TT Interphases', sans-serif;
    }
    
    /* Animations - DESIGN.md Section 2 */
    @keyframes fadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.985);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes statusPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
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
        
        .search-container {
            width: 100%;
        }
        
        .search-bar {
            min-width: 100%;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .requests-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Dark Mode - DESIGN.md Section 1.2 */
    html[data-theme="dark"] .request-card {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
    }
    
    html[data-theme="dark"] .search-bar {
        background: #2d2d2d;
        border-color: #404040;
    }
    
    html[data-theme="dark"] .search-bar input {
        color: #e0e0e0;
    }
    
    html[data-theme="dark"] .form-group input,
    html[data-theme="dark"] .form-group textarea {
        background: #2d2d2d;
        border-color: #404040;
        color: #e0e0e0;
    }
    
    /* Main Content Container Wrapper */
    .main-page-content-container {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 20px;
        border: 1px solid rgba(12, 75, 52, 0.12);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
        margin-top: 10px;
        animation: fadeSlideUp 0.45s ease-out both;
    }
    
    html[data-theme="dark"] .main-page-content-container {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2) !important;
    }
    
    /* Adjustments for white container */
    .filter-tabs-container {
        margin-bottom: 28px;
    }
    
    .action-bar {
        margin-bottom: 28px;
    }
    
    html[data-theme="dark"] .premium-modal {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        box-shadow: 0 22px 64px rgba(0, 0, 0, 0.45) !important;
    }
    
    html[data-theme="dark"] .modal-header {
        background: linear-gradient(0deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.03)), #1e1e1e;
        border-bottom-color: #333;
    }
    
    html[data-theme="dark"] .modal-title {
        color: #f0f0f0;
    }
    
    html[data-theme="dark"] .stat-pill {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.6);
    }
    
    html[data-theme="dark"] .stat-pill strong {
        color: #81C784;
    }
    
    html[data-theme="dark"] .request-icon-box {
        background: rgba(255, 255, 255, 0.05);
        color: #81C784;
    }
    
    html[data-theme="dark"] .request-card.pending .request-icon-box {
        color: #ffb74d;
    }
    
    html[data-theme="dark"] .request-reason {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    html[data-theme="dark"] .empty-state {
        background: rgba(255, 255, 255, 0.01);
        border-color: rgba(255, 255, 255, 0.05);
    }
    
    html[data-theme="dark"] .empty-icon-wrapper {
        background: rgba(255, 255, 255, 0.03);
        color: rgba(255, 255, 255, 0.1);
    }
    
    html[data-theme="dark"] .status-badge.pending {
        background: rgba(255, 183, 77, 0.1);
        color: #ffb74d;
        border-color: rgba(255, 183, 77, 0.2);
    }
    
    html[data-theme="dark"] .status-badge.done {
        background: rgba(129, 199, 132, 0.1);
        color: #81C784;
        border-color: rgba(129, 199, 132, 0.2);
    }
    
    html[data-theme="dark"] .processed-on {
        color: #81C784;
    }
</style>

<script>
    // Global variables
    let currentFilter = 'PENDING';
    let allRequests = [];
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadBookRequests();
    });
    
    // Load book requests
    function loadBookRequests(status = 'PENDING') {
        fetch(`api/get_dean_book_requests.php?status=${status}`, {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allRequests = data.data;
                    updateStats(data.counts);
                    renderRequests(allRequests);
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
    
    // Apply filters
    function applyRequestFilters() {
        renderRequests(allRequests);
    }
    
    // Render requests
    function renderRequests(requests) {
        const grid = document.getElementById('requestsGrid');
        const emptyState = document.getElementById('requestsEmptyState');
        
        if (!grid) return;
        
        grid.innerHTML = '';
        
        if (requests.length === 0) {
            grid.style.display = 'none';
            emptyState.style.display = 'block';
            
            const messages = {
                'PENDING': "You don't have any pending requests.",
                'DONE': "You don't have any completed requests."
            };
            document.getElementById('emptyStateMessage').textContent = messages[currentFilter] || 'No requests found.';
            return;
        }
        
        grid.style.display = 'grid';
        emptyState.style.display = 'none';
        
        requests.forEach((request, index) => {
            const card = createRequestCard(request, index);
            grid.appendChild(card);
        });
        
        // Re-initialize icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }
    
    // Create request card
    function createRequestCard(request, index) {
        const card = document.createElement('div');
        card.className = `request-card ${request.status.toLowerCase()}`;
        card.style.animationDelay = (index * 0.08) + 's';
        
        const requestedDate = new Date(request.requested_at);
        const formattedReqDate = requestedDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        
        let processedHtml = '';
        if (request.status === 'DONE' && request.processed_at) {
            const procDate = new Date(request.processed_at);
            const formattedProcDate = procDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            processedHtml = `<div class="processed-on">Processed: ${formattedProcDate}</div>`;
        }
        
        let actionButtonsHtml = '';
        if (request.status === 'PENDING') {
            actionButtonsHtml = `
                <div class="request-actions">
                    <button class="action-btn edit-btn" onclick="openEditModal(${request.id})">
                        <i data-lucide="pencil"></i>
                        Edit
                    </button>
                    <button class="action-btn cancel-btn-action" onclick="openConfirmCancelModal(${request.id})">
                        <i data-lucide="trash-2"></i>
                        Remove
                    </button>
                </div>
            `;
        } else {
            actionButtonsHtml = `
                <div class="request-actions">
                    <button class="action-btn done-btn" disabled>
                        <i data-lucide="check-circle-2"></i>
                        Request Completed
                    </button>
                </div>
            `;
        }
        
        card.innerHTML = `
            <div class="request-header">
                <div class="request-icon-box">
                    <i data-lucide="book-open"></i>
                </div>
                <div class="request-info">
                    <div class="request-book-title" title="${request.book_title}">${request.book_title}</div>
                    <div class="request-author">by ${request.author}</div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 10px;">
                <span class="status-badge ${request.status.toLowerCase()}">${request.status === 'DONE' ? 'Done' : 'Pending'}</span>
                <div style="text-align: right;">
                    <div class="request-date">Req: ${formattedReqDate}</div>
                    ${processedHtml}
                </div>
            </div>
            
            <div class="request-details">
                ${request.isbn ? `<span class="request-detail-item">ISBN: ${request.isbn}</span>` : ''}
                ${request.publication_year ? `<span class="request-detail-item">Year: ${request.publication_year}</span>` : ''}
                ${request.publisher ? `<span class="request-detail-item">${request.publisher}</span>` : ''}
                ${request.edition ? `<span class="request-detail-item">${request.edition}</span>` : ''}
            </div>
            
            <div class="request-reason">
                <div class="request-reason-label">Requirement Rationale</div>
                ${request.reason}
            </div>
            
            <div style="margin-top: auto;">
                ${actionButtonsHtml}
            </div>
        `;
        
        return card;
    }
    
    // Open request modal (new request)
    function openRequestModal() {
        document.getElementById('editRequestId').value = '';
        document.getElementById('modalTitleText').textContent = 'Request New Book';
        document.getElementById('requestBookForm').reset();
        document.getElementById('submitBtnText').textContent = 'Submit Request';
        document.getElementById('requestBookModal').style.display = 'flex';
    }
    
    // Open edit modal
    function openEditModal(requestId) {
        const request = allRequests.find(r => r.id === requestId);
        if (!request) return;
        
        document.getElementById('editRequestId').value = requestId;
        document.getElementById('modalTitleText').textContent = 'Edit Book Request';
        document.getElementById('requestBookTitle').value = request.book_title;
        document.getElementById('requestAuthor').value = request.author;
        document.getElementById('requestIsbn').value = request.isbn || '';
        document.getElementById('requestYear').value = request.publication_year || '';
        document.getElementById('requestPublisher').value = request.publisher || '';
        document.getElementById('requestEdition').value = request.edition || '';
        document.getElementById('requestReason').value = request.reason || '';
        
        document.getElementById('submitBtnText').textContent = 'Save Changes';
        document.getElementById('requestBookModal').style.display = 'flex';
    }
    
    // Close request modal
    function closeRequestModal() {
        document.getElementById('requestBookModal').style.display = 'none';
    }
    
    // Submit book request (new or edit)
    function submitBookRequest(event) {
        event.preventDefault();
        
        const editRequestId = document.getElementById('editRequestId').value;
        const isEdit = editRequestId !== '';
        
        const formData = {
            request_id: editRequestId,
            book_title: document.getElementById('requestBookTitle').value,
            author: document.getElementById('requestAuthor').value,
            isbn: document.getElementById('requestIsbn').value,
            publication_year: document.getElementById('requestYear').value,
            publisher: document.getElementById('requestPublisher').value,
            edition: document.getElementById('requestEdition').value,
            reason: document.getElementById('requestReason').value
        };
        
        const endpoint = isEdit ? 'api/update_book_request.php' : 'api/submit_book_request.php';
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    closeRequestModal();
                    showSuccessModal(isEdit ? 'Request Updated' : 'Request Submitted', data.message);
                    loadBookRequests(currentFilter);
                } else {
                    showErrorModal(data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                showErrorModal('Server error: ' + text.substring(0, 100));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('Failed to submit request. Please try again.');
        });
    }
    
    // Open confirm cancel modal
    function openConfirmCancelModal(requestId) {
        document.getElementById('cancelRequestId').value = requestId;
        document.getElementById('confirmCancelModal').style.display = 'flex';
    }
    
    // Close confirm cancel modal
    function closeConfirmCancelModal() {
        document.getElementById('confirmCancelModal').style.display = 'none';
    }
    
    // Confirm cancel request
    function confirmCancelRequest() {
        const requestId = document.getElementById('cancelRequestId').value;
        
        fetch('api/cancel_book_request.php', {
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
                closeConfirmCancelModal();
                showSuccessModal('Request Cancelled', data.message);
                loadBookRequests(currentFilter);
            } else {
                showErrorModal(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorModal('Failed to cancel request. Please try again.');
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
