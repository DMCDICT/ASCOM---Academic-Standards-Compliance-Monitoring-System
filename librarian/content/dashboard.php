<?php
// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get librarian information and department from session
$librarianName = 'Ms. Dummy Librarian';
$departmentCode = 'CCS'; // Default fallback
$departmentColor = '#C41E3A'; // Default red color

// Fetch dashboard statistics from database
$stats = array(
    'total_books' => 0,
    'pending_requests' => 0,
    'processed_this_month' => 0
);

try {
    // 1. Total Books in Library
    $totalBooksQuery = "SELECT COUNT(*) as total_books FROM library_books WHERE status = 'available'";
    $totalBooksStmt = $pdo->prepare($totalBooksQuery);
    $totalBooksStmt->execute();
    $totalBooksResult = $totalBooksStmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_books'] = (int)$totalBooksResult['total_books'];
    
    // 2. Pending Book Requests from Deans
    $pendingRequestsQuery = "SELECT COUNT(*) as pending_count FROM book_requests WHERE status = 'PENDING'";
    $pendingRequestsStmt = $pdo->prepare($pendingRequestsQuery);
    $pendingRequestsStmt->execute();
    $pendingRequestsResult = $pendingRequestsStmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending_requests'] = (int)$pendingRequestsResult['pending_count'];
    
    // 3. Processed This Month (completed book requests)
    $processedThisMonthQuery = "
        SELECT COUNT(*) as processed_count 
        FROM book_requests 
        WHERE status = 'DONE' 
        AND MONTH(processed_at) = MONTH(CURDATE()) 
        AND YEAR(processed_at) = YEAR(CURDATE())
    ";
    $processedThisMonthStmt = $pdo->prepare($processedThisMonthQuery);
    $processedThisMonthStmt->execute();
    $processedThisMonthResult = $processedThisMonthStmt->fetch(PDO::FETCH_ASSOC);
    $stats['processed_this_month'] = (int)$processedThisMonthResult['processed_count'];
    
} catch (Exception $e) {
    // Use default values if database query fails
}
?>

<?php
// Get personalized name from session
$firstName = $_SESSION['user_first_name'] ?? 'Librarian';
$lastName = $_SESSION['user_last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);
$currentDate = date('F j, Y');
?>

<div class="librarian-dashboard">
    <!-- Greeting Section -->
    <div class="dashboard-greeting">
        <div class="greeting-text">
            <h2>Welcome back, <?php echo htmlspecialchars($fullName); ?>!</h2>
            <p>Here's what's happening in the library today.</p>
        </div>
        <div class="greeting-meta">
            <div class="greeting-date">
                <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                <?php echo $currentDate; ?>
            </div>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="dashboard-stats-grid">
        <div class="box">
            <div class="box-icon">
                <i data-lucide="library"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Total Books</span>
                <h3 class="amount"><?php echo number_format($stats['total_books']); ?></h3>
                <span class="amount-sub">In library collection</span>
            </div>
        </div>
        
        <div class="box">
            <div class="box-icon" style="background: rgba(21, 101, 192, 0.08); color: #1565C0;">
                <i data-lucide="shopping-cart"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Pending Requests</span>
                <h3 class="amount" style="color: #1565C0;"><?php echo number_format($stats['pending_requests']); ?></h3>
                <span class="amount-sub">From department deans</span>
            </div>
        </div>

        <div class="box">
            <div class="box-icon" style="background: rgba(185, 28, 28, 0.08); color: #b91c1c;">
                <i data-lucide="check-circle"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Processed This Month</span>
                <h3 class="amount" style="color: #b91c1c;"><?php echo number_format($stats['processed_this_month']); ?></h3>
                <span class="amount-sub">Book requests completed</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="quick-action" onclick="openAddBookModal()">
            <div class="qa-icon"><i data-lucide="plus"></i></div>
            <span>Add New Book</span>
        </button>
        <a href="content.php?page=book-requests" class="quick-action">
            <div class="qa-icon"><i data-lucide="clipboard-list"></i></div>
            <span>Process Requests</span>
        </a>
        <button class="quick-action" onclick="openAddClassificationModal()">
            <div class="qa-icon"><i data-lucide="tags"></i></div>
            <span>New Classification</span>
        </button>
        <button class="quick-action" onclick="openReportsModal()">
            <div class="qa-icon"><i data-lucide="file-text"></i></div>
            <span>View Reports</span>
        </button>
    </div>

    <!-- Classification Management Section -->
    <div class="dashboard-section" style="margin-bottom: 40px;">
        <div class="dashboard-section__top">
            <div class="section-header">
                <div class="label-bar" style="background: linear-gradient(180deg, #4CAF50 0%, #81C784 100%);"></div>
                <div class="header-content">
                    <h3>Classification Management</h3>
                    <p>Manage library classification systems and cataloging standards</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="filter-pills">
                    <button type="button" class="filter-pill active" data-location="all" onclick="filterClassificationsByLocation('all')">All</button>
                    <button type="button" class="filter-pill" data-location="Main Library" onclick="filterClassificationsByLocation('Main Library')">Main Library</button>
                    <button type="button" class="filter-pill" data-location="Buenavista Library" onclick="filterClassificationsByLocation('Buenavista Library')">Buenavista Library</button>
                </div>
                <button class="btn-primary" onclick="openAddClassificationModal()">
                    <i data-lucide="plus"></i>
                    Add Classification
                </button>
            </div>
        </div>
        
        <div class="material-processing-container">
            <div class="material-processing-grid" id="classificationContainer">
                <!-- Classification cards will be dynamically generated here -->
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>











<style>
/* Loading Spinner Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Modal styles for Complete Cataloging */
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
.modal-body select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: 'TT Interphases', sans-serif;
}

.modal-body input:focus,
.modal-body select:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
}

/* Override for Add Classification Modal to use flexbox centering */
#addClassificationModal.modal-overlay {
    display: none;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

#addClassificationModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
    max-width: 600px !important;
    width: 90% !important;
}

/* Override for Classification Success and Error Modals */
#classificationSuccessModal.modal-overlay,
#classificationErrorModal.modal-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

#classificationSuccessModal.modal-overlay[style*="display: none"],
#classificationErrorModal.modal-overlay[style*="display: none"] {
    display: none !important;
}

#classificationSuccessModal.modal-overlay[style*="display: flex"],
#classificationErrorModal.modal-overlay[style*="display: flex"] {
    display: flex !important;
}

#classificationSuccessModal .modal-content,
#classificationErrorModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
}

/* Override for Cataloging Success and Error Modals */
#catalogingSuccessModal.modal-overlay,
#catalogingErrorModal.modal-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

#catalogingSuccessModal.modal-overlay[style*="display: none"],
#catalogingErrorModal.modal-overlay[style*="display: none"] {
    display: none !important;
}

#catalogingSuccessModal.modal-overlay[style*="display: flex"],
#catalogingErrorModal.modal-overlay[style*="display: flex"] {
    display: flex !important;
}

#catalogingSuccessModal .modal-content,
#catalogingErrorModal .modal-content {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    margin: 0 !important;
}

</style>





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
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px; font-weight: 600;">Please finalize the book details to complete the cataloging process.</p>
            
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
                        Complete Process
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
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Process Complete!</h2>
        <p id="catalogingSuccessMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">The book has been successfully cataloged and added to the inventory.</p>
        <button type="button" onclick="closeCatalogingSuccessModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700;">Great, thanks!</button>
    </div>
</div>

<!-- Error Modal for Cataloging Completion -->
<div id="catalogingErrorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="error-icon-wrapper" style="width: 80px; height: 80px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="alert-circle" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Something went wrong</h2>
        <p id="catalogingErrorMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">We encountered an error while processing your request. Please try again.</p>
        <button type="button" onclick="closeCatalogingErrorModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; background: #EF4444;">Close</button>
    </div>
</div>



<!-- Add Classification Modal -->
<div id="addClassificationModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: var(--primary-tint); color: var(--primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="tag"></i>
                </div>
                <h2 class="modal-title">New Classification</h2>
            </div>
            <button class="modal-close" onclick="closeAddClassificationModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px; font-weight: 600;">Define a new library classification to organize your collection better.</p>
            
            <form id="addClassificationForm">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Classification Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="classificationName" name="name" required class="form-input" placeholder="e.g. Computer Science" style="width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 0 14px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Call Number Range <span style="color: var(--danger);">*</span></label>
                    <div class="input-wrapper" style="position: relative;">
                        <input type="text" id="callNumberRange" name="call_number_range" required placeholder="000-099" pattern="\d{3}-\d{3}" class="form-input" style="width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 0 14px;">
                        <small style="display: block; margin-top: 6px; color: var(--text-faint); font-size: 12px;">Format: XXX-XXX (e.g., 000-099, 100-199)</small>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Library Location <span style="color: var(--danger);">*</span></label>
                    <div class="input-wrapper" style="position: relative;">
                        <select id="classificationLocation" name="location" required class="form-input" style="width: 100%; height: 48px; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 0 14px; background: #fff; appearance: none;">
                            <option value="">Select Location</option>
                            <option value="Main Library">Main Library</option>
                            <option value="Buenavista Library">Buenavista Library</option>
                        </select>
                        <i data-lucide="chevron-down" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-faint); pointer-events: none;"></i>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Description</label>
                    <textarea id="classificationDescription" name="description" rows="3" class="form-input" placeholder="Enter brief description of this classification..." style="width: 100%; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 12px 14px; resize: vertical;"></textarea>
                </div>
                
                <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeAddClassificationModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted); font-family: 'TT Interphases', sans-serif;">Cancel</button>
                    <button type="submit" id="submitClassificationBtn" class="btn-primary" style="padding: 12px 24px; border-radius: 10px; min-width: 160px;">
                        Add Classification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal for Classification -->
<div id="classificationSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="success-icon-wrapper" style="width: 80px; height: 80px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="check-circle-2" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Classification Added!</h2>
        <p id="classificationSuccessMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">The new classification system has been successfully created.</p>
        <button type="button" onclick="closeClassificationSuccessModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700;">Continue</button>
    </div>
</div>

<!-- Error Modal for Classification -->
<div id="classificationErrorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="error-icon-wrapper" style="width: 80px; height: 80px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="alert-circle" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Failed to add</h2>
        <p id="classificationErrorMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">We couldn't save the classification at this time. Please check your inputs.</p>
        <button type="button" onclick="closeClassificationErrorModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; background: #EF4444;">Try Again</button>
    </div>
</div>

<!-- Reports Modal -->
<div id="reportsModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 600px; width: 95%;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: var(--primary-tint); color: var(--primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file-text"></i>
                </div>
                <h2 class="modal-title">Library Reports</h2>
            </div>
            <button class="modal-close" onclick="closeReportsModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <div class="reports-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px;">
                <div class="report-card" onclick="generateReport('compliance')" style="padding: 20px; border: 1px solid var(--primary-border); border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center;">
                    <i data-lucide="shield-check" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                    <div style="font-weight: 700; color: #111827; margin-bottom: 4px;">Compliance Report</div>
                    <div style="font-size: 12px; color: #666;">Course compliance status</div>
                </div>
                <div class="report-card" onclick="generateReport('books')" style="padding: 20px; border: 1px solid var(--primary-border); border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center;">
                    <i data-lucide="book-open" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                    <div style="font-weight: 700; color: #111827; margin-bottom: 4px;">Book Inventory</div>
                    <div style="font-size: 12px; color: #666;">Complete book list</div>
                </div>
                <div class="report-card" onclick="generateReport('requests')" style="padding: 20px; border: 1px solid var(--primary-border); border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center;">
                    <i data-lucide="shopping-cart" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                    <div style="font-weight: 700; color: #111827; margin-bottom: 4px;">Request Summary</div>
                    <div style="font-size: 12px; color: #666;">Book requests overview</div>
                </div>
                <div class="report-card" onclick="generateReport('classification')" style="padding: 20px; border: 1px solid var(--primary-border); border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center;">
                    <i data-lucide="tags" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                    <div style="font-weight: 700; color: #111827; margin-bottom: 4px;">Classification</div>
                    <div style="font-size: 12px; color: #666;">Library classifications</div>
                </div>
            </div>
            
            <div id="reportContent" style="display: none; margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 id="reportTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;"></h4>
                    <button onclick="downloadReport()" style="padding: 8px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px;">
                        <i data-lucide="download" style="width: 14px; height: 14px; margin-right: 6px;"></i> Download
                    </button>
                </div>
                <div id="reportData" style="font-size: 14px; color: #4b5563;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Process Material Request Modal -->
<div id="processRequestModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: #EEF2FF; color: #4338CA; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="check-circle"></i>
                </div>
                <h2 class="modal-title">Process Request</h2>
            </div>
            <button class="modal-close" onclick="closeProcessRequestModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px; font-weight: 600;">Mark this material request as being processed.</p>
            <input type="hidden" id="processingRequestId" value="">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Notes (optional)</label>
                <textarea id="processingNotes" rows="3" class="form-input" placeholder="Add any notes about this request..." style="width: 100%; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 12px 14px; resize: vertical;"></textarea>
            </div>
            <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeProcessRequestModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted); font-family: 'TT Interphases', sans-serif;">Cancel</button>
                <button type="button" onclick="confirmProcessRequest()" class="btn-primary" style="padding: 12px 24px; border-radius: 10px;">Process Request</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Material Request Modal -->
<div id="completeRequestModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: #ECFDF5; color: #059669; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="check-circle-2"></i>
                </div>
                <h2 class="modal-title">Complete Request</h2>
            </div>
            <button class="modal-close" onclick="closeCompleteRequestModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px; font-weight: 600;">Mark this material request as completed.</p>
            <input type="hidden" id="completingRequestId" value="">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Completion Notes</label>
                <textarea id="completionNotes" rows="3" class="form-input" placeholder="Add completion notes..." style="width: 100%; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 12px 14px; resize: vertical;"></textarea>
            </div>
            <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeCompleteRequestModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted); font-family: 'TT Interphases', sans-serif;">Cancel</button>
                <button type="button" onclick="confirmCompleteRequest()" class="btn-primary" style="padding: 12px 24px; border-radius: 10px; background: #059669;">Complete</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Material Request Modal -->
<div id="rejectRequestModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div class="modal-title-wrapper" style="display: flex; align-items: center; gap: 12px;">
                <div class="modal-icon-header" style="background: #FEF2F2; color: #DC2626; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x-circle"></i>
                </div>
                <h2 class="modal-title">Reject Request</h2>
            </div>
            <button class="modal-close" onclick="closeRejectRequestModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px; font-weight: 600;">Please provide a reason for rejecting this request.</p>
            <input type="hidden" id="rejectingRequestId" value="">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Rejection Reason <span style="color: var(--danger);">*</span></label>
                <textarea id="rejectionReason" rows="4" required class="form-input" placeholder="Enter reason for rejection..." style="width: 100%; border-radius: 10px; border: 1px solid var(--primary-border); font-family: 'TT Interphases', sans-serif; padding: 12px 14px; resize: vertical;"></textarea>
            </div>
            <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-secondary" onclick="closeRejectRequestModal()" style="padding: 12px 24px; background: #fff; border: 1px solid var(--primary-border); border-radius: 10px; cursor: pointer; font-weight: 700; color: var(--text-muted); font-family: 'TT Interphases', sans-serif;">Cancel</button>
                <button type="button" onclick="confirmRejectRequest()" class="btn-primary" style="padding: 12px 24px; border-radius: 10px; background: #DC2626;">Reject Request</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="dashboardSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="success-icon-wrapper" style="width: 80px; height: 80px; background: #ECFDF5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="check-circle-2" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;" id="dashboardSuccessTitle">Success!</h2>
        <p id="dashboardSuccessMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">Operation completed successfully.</p>
        <button type="button" onclick="closeDashboardSuccessModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700;">Great!</button>
    </div>
</div>

<!-- Error Modal -->
<div id="dashboardErrorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 40px 32px;">
        <div class="error-icon-wrapper" style="width: 80px; height: 80px; background: #FEF2F2; color: #EF4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i data-lucide="alert-circle" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 12px;">Error</h2>
        <p id="dashboardErrorMessage" style="font-size: 15px; color: #4B5563; line-height: 1.6; margin-bottom: 32px;">An error occurred.</p>
        <button type="button" onclick="closeDashboardErrorModal()" class="btn-primary" style="width: 100%; height: 48px; border-radius: 10px; font-weight: 700; background: #EF4444;">Close</button>
    </div>
</div>


<script>
// Classification Management Variables
let allClassifications = [];
let currentClassificationPage = 0;
let classificationsPerPage = 3;
let isLoadingClassifications = false;

// Material Requests Variables
let isLoadingMaterialRequests = false;


// (Sample classification data removed – dashboard now shows only real DB data)


// Initialize both sections
document.addEventListener('DOMContentLoaded', function() {
    
    // Load classifications from database
    loadClassificationsFromDatabase();
    
    // Set up form submission
    const addClassificationForm = document.getElementById('addClassificationForm');
    if (addClassificationForm) {
        addClassificationForm.addEventListener('submit', submitAddClassification);
    }
    
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
});

// Classification Management Functions - Program Management Style
let classificationLocationFilter = 'all';

function filterClassificationsByLocation(location) {
    classificationLocationFilter = location;
    
    // Update active state of filter pills
    document.querySelectorAll('.filter-pill').forEach(pill => {
        const pillLocation = pill.getAttribute('data-location');
        if (pillLocation === location) {
            pill.classList.add('active');
        } else {
            pill.classList.remove('active');
        }
    });
    
    // Re-render classifications
    displayAllClassifications();
}

function displayAllClassifications() {
    const container = document.getElementById('classificationContainer');
    if (!container) return;
    
    let toRender = allClassifications;
    if (classificationLocationFilter !== 'all') {
        toRender = allClassifications.filter(c => {
            // Support both API data (snake_case) and sample data (no location)
            const loc = c.location || c.library_location || null;
            if (!loc) return false;
            return loc === classificationLocationFilter;
        });
    }
    
    if (toRender.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #666; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
                <i data-lucide="tag" style="width: 48px; height: 48px; margin-bottom: 16px; color: #ccc;"></i>
                <h3 style="margin-bottom: 8px;">No Classifications Found</h3>
                <p style="margin-bottom: 16px;">No classifications for this library location yet.</p>
                <button class="btn-primary" onclick="openAddClassificationModal()">
                    <i data-lucide="plus"></i>
                    Add Classification
                </button>
            </div>
        `;
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        return;
    }
    
    container.innerHTML = toRender.map(classification => createClassificationCard(classification)).join('');
    
    // Re-initialize Lucide icons for the new elements
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function createClassificationCard(classification) {
    const locationBadge = classification.location ? 
        `<span class="location-badge" style="display: inline-block; padding: 4px 8px; background: #e3f2fd; color: #1565c0; border-radius: 4px; font-size: 12px; margin-left: 8px;">${classification.location}</span>` : '';
    
    const typeBadge = classification.type ? 
        `<span style="display: inline-block; padding: 2px 6px; background: #f5f5f5; color: #666; border-radius: 3px; font-size: 11px;">${classification.type}</span>` : '';
    
    // Fix items count - use totalItems from API or fallback to 0
    const itemsCount = classification.totalItems !== undefined && classification.totalItems !== null ? classification.totalItems : 0;
    
    return `
        <div class='department-card' style='border: 1px solid #0c4b3424;'>
            <div class='dept-code' style='background-color: #4CAF50;'>${classification.callNumberRange || 'N/A'}</div>
            <div style='display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;'>
                <h3 style='margin: 0; font-size: 18px;'>${classification.name || 'Unnamed'}</h3>
                ${locationBadge}
            </div>
            <p style='font-weight: 500; color: #555; margin: 8px 0;'>${classification.description || 'No description'}</p>
            <div style='display: flex; gap: 12px; margin-top: 12px; font-size: 13px; color: #666;'>
                <span><i data-lucide="book" style="width: 14px; height: 14px;"></i> ${itemsCount} items</span>
                <span>${typeBadge}</span>
            </div>
            <div style='margin-top: 12px; text-align: right; padding-bottom: 8px; display: flex; justify-content: space-between; align-items: center;'>
                <button class='delete-classification-btn' onclick="deleteClassification('${classification.id}')" style='background: none; border: 1px solid #ffcdd2; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #c62828;'>
                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Delete
                </button>
                <div style='display: flex; gap: 8px;'>
                    <button class='edit-classification-btn' onclick="editClassification('${classification.id}')" style='background: none; border: 1px solid #ddd; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #666;'>
                        <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i> Edit
                    </button>
                    <button class='view-details-btn' onclick="viewClassificationDetails('${classification.id}')">View Shelf</button>
                </div>
            </div>
        </div>
    `;
}

function viewClassificationDetails(classificationId) {
    // Find the classification data
    const classification = allClassifications.find(c => c.id == classificationId);
    if (!classification) {
        console.error('Classification not found:', classificationId);
        return;
    }
    
    // Navigate to classification details page
    const range = encodeURIComponent(classification.callNumberRange);
    const name = encodeURIComponent(classification.name);
    window.location.href = `content.php?page=classification-details&range=${range}&name=${name}`;
}

function editClassification(classificationId) {
    const classification = allClassifications.find(c => c.id == classificationId);
    if (!classification) {
        console.error('Classification not found:', classificationId);
        return;
    }
    
    // Populate the add classification form with existing data
    document.getElementById('classificationName').value = classification.name || '';
    document.getElementById('callNumberRange').value = classification.callNumberRange || '';
    document.getElementById('classificationLocation').value = classification.location || '';
    document.getElementById('classificationDescription').value = classification.description || '';
    
    // Store the ID being edited
    document.getElementById('classificationName').dataset.editId = classificationId;
    
    // Change button text to indicate update
    const submitBtn = document.getElementById('submitClassificationBtn');
    submitBtn.textContent = 'Update Classification';
    submitBtn.dataset.mode = 'edit';
    
    // Open the modal
    openAddClassificationModal();
}

async function deleteClassification(classificationId) {
    if (!confirm('Are you sure you want to delete this classification? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_classification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: classificationId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showClassificationSuccessModal('Classification deleted successfully!');
            await loadClassificationsFromDatabase();
        } else {
            showClassificationErrorModal('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting classification:', error);
        showClassificationErrorModal('Failed to delete classification. Please try again.');
    }
}

function openAddClassificationModal() {
    const modal = document.getElementById('addClassificationModal');
    if (modal) {
        // Explicitly set all centering properties
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.setProperty('position', 'fixed', 'important');
        modal.style.setProperty('top', '0', 'important');
        modal.style.setProperty('left', '0', 'important');
        modal.style.setProperty('right', '0', 'important');
        modal.style.setProperty('bottom', '0', 'important');
        modal.style.setProperty('align-items', 'center', 'important');
        modal.style.setProperty('justify-content', 'center', 'important');
        // Reset form
        document.getElementById('addClassificationForm').reset();
    }
}

function closeAddClassificationModal() {
    const modal = document.getElementById('addClassificationModal');
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');
        // Reset form
        document.getElementById('addClassificationForm').reset();
        
        // Clear edit mode
        const nameInput = document.getElementById('classificationName');
        delete nameInput.dataset.editId;
        
        const submitBtn = document.getElementById('submitClassificationBtn');
        submitBtn.dataset.mode = '';
        submitBtn.textContent = 'Add Classification';
    }
}

async function loadClassificationsFromDatabase() {
    try {
        const response = await fetch('api/get_classifications.php');
        const result = await response.json();
        
        if (result.success) {
            allClassifications = result.data.map(c => ({
                id: c.id,
                name: c.name,
                type: c.type,
                callNumberRange: c.call_number_range,
                description: c.description,
                status: c.status,
                location: c.location || null,
                totalItems: c.totalItems || 0,
                lastUpdated: c.lastUpdated || c.updated_at || c.created_at
            }));
            
        } else {
            console.warn('Failed to load classifications from database');
            allClassifications = [];
        }
    } catch (error) {
        console.error('Error loading classifications:', error);
        allClassifications = [];
    }
    
    // Always render whatever we have (DB data or empty)
    displayAllClassifications();
}

async function submitAddClassification(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('submitClassificationBtn');
    const originalText = submitBtn.textContent;
    const isEdit = submitBtn.dataset.mode === 'edit';
    const editId = document.getElementById('classificationName').dataset.editId;
    
    submitBtn.disabled = true;
    submitBtn.textContent = isEdit ? 'Updating...' : 'Adding...';
    
    const formData = {
        name: document.getElementById('classificationName').value.trim(),
        call_number_range: document.getElementById('callNumberRange').value.trim(),
        location: document.getElementById('classificationLocation').value.trim(),
        type: 'DDC', // Default to DDC
        description: document.getElementById('classificationDescription').value.trim(),
        status: 'active' // Default to active
    };
    
    try {
        let response, result;
        
        if (isEdit && editId) {
            // Update existing classification
            formData.id = editId;
            response = await fetch('api/update_classification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            result = await response.json();
            
            if (result.success) {
                closeAddClassificationModal();
                showClassificationSuccessModal('Classification updated successfully!');
                // Clear edit mode
                submitBtn.dataset.mode = '';
                submitBtn.textContent = 'Add Classification';
                delete document.getElementById('classificationName').dataset.editId;
            } else {
                closeAddClassificationModal();
                showClassificationErrorModal('Error: ' + result.message);
            }
        } else {
            // Add new classification
            response = await fetch('api/add_classification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            result = await response.json();
            
            if (result.success) {
                closeAddClassificationModal();
                showClassificationSuccessModal('Classification added successfully!');
            } else {
                closeAddClassificationModal();
                showClassificationErrorModal('Error: ' + result.message);
            }
        }
        
        // Reload classifications from database
        await loadClassificationsFromDatabase();
        
    } catch (error) {
        console.error('Error saving classification:', error);
        closeAddClassificationModal();
        showClassificationErrorModal('Failed to save classification. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = isEdit ? 'Update Classification' : 'Add Classification';
    }
}

function showClassificationSuccessModal(message) {
    const modal = document.getElementById('classificationSuccessModal');
    const messageElement = document.getElementById('classificationSuccessMessage');
    if (modal && messageElement) {
        messageElement.textContent = message || 'Classification added successfully!';
        modal.style.display = 'flex';
    }
}

function closeClassificationSuccessModal() {
    const modal = document.getElementById('classificationSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function showClassificationErrorModal(message) {
    const modal = document.getElementById('classificationErrorModal');
    const messageElement = document.getElementById('classificationErrorMessage');
    if (modal && messageElement) {
        messageElement.textContent = message || 'An error occurred while adding the classification.';
        modal.style.display = 'flex';
    }
}

function closeClassificationErrorModal() {
    const modal = document.getElementById('classificationErrorModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Handle classification location tabs
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('classification-location-tab')) {
        const selectedLocation = e.target.getAttribute('data-location') || 'all';
        classificationLocationFilter = selectedLocation;
        
        // Update active state
        document.querySelectorAll('.classification-location-tab').forEach(tab => {
            tab.classList.toggle('active', tab === e.target);
        });
        
        // Re-render classifications
        displayAllClassifications();
    }
});

// ===== Reports Modal Functions =====
function openReportsModal() {
    document.getElementById('reportsModal').style.display = 'flex';
    document.getElementById('reportContent').style.display = 'none';
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function closeReportsModal() {
    document.getElementById('reportsModal').style.display = 'none';
}

async function generateReport(type) {
    const reportTitle = document.getElementById('reportTitle');
    const reportData = document.getElementById('reportData');
    const reportContent = document.getElementById('reportContent');
    
    reportContent.style.display = 'block';
    reportData.innerHTML = '<div style="text-align: center; padding: 20px;"><i data-lucide="loader-2" class="spin-icon" style="width: 24px; height: 24px; color: var(--primary);"></i><p>Loading report data...</p></div>';
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    try {
        let data = {};
        let title = '';
        
        switch(type) {
            case 'compliance':
                title = 'Course Compliance Report';
                const complianceResponse = await fetch('api/get_dashboard_stats.php');
                const complianceResult = await complianceResponse.json();
                data = complianceResult;
                break;
            case 'books':
                title = 'Book Inventory Report';
                const booksResponse = await fetch('api/get_books.php?limit=100');
                const booksResult = await booksResponse.json();
                data = booksResult;
                break;
            case 'requests':
                title = 'Book Requests Summary';
                const requestsResponse = await fetch('api/get_pending_book_requests.php?status=PENDING');
                const requestsResult = await requestsResponse.json();
                data = requestsResult;
                break;
            case 'classification':
                title = 'Classification Report';
                const classResponse = await fetch('api/get_classifications.php');
                const classResult = await classResponse.json();
                data = classResult;
                break;
        }
        
        reportTitle.textContent = title;
        
        // Format the data based on type
        let html = '';
        if (type === 'compliance') {
            html = `
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
                    <div style="padding: 16px; background: white; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: var(--primary);">${data.totalBooks || 0}</div>
                        <div style="font-size: 12px; color: #666;">Compliant Books</div>
                    </div>
                    <div style="padding: 16px; background: white; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: #1565C0;">${data.compliantCourses || 0}</div>
                        <div style="font-size: 12px; color: #666;">Compliant Courses</div>
                    </div>
                    <div style="padding: 16px; background: white; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: #b91c1c;">${data.nonCompliantCourses || 0}</div>
                        <div style="font-size: 12px; color: #666;">Non-Compliant</div>
                    </div>
                </div>
                <p style="font-size: 13px; color: #666;">Report generated on ${new Date().toLocaleDateString()}</p>
            `;
        } else if (type === 'books') {
            const books = data.data || [];
            html = `
                <div style="padding: 12px; background: white; border-radius: 8px; margin-bottom: 12px;">
                    <strong>Total Books:</strong> ${books.length}
                </div>
                <div style="max-height: 200px; overflow-y: auto;">
                    ${books.slice(0, 10).map(book => `
                        <div style="padding: 8px; border-bottom: 1px solid #eee; font-size: 13px;">
                            <strong>${book.title || 'N/A'}</strong> - ${book.author || 'Unknown'}
                        </div>
                    `).join('')}
                    ${books.length > 10 ? `<div style="padding: 8px; color: #666; font-size: 12px;">...and ${books.length - 10} more</div>` : ''}
                </div>
            `;
        } else if (type === 'requests') {
            const requests = data.data || [];
            html = `
                <div style="padding: 12px; background: white; border-radius: 8px; margin-bottom: 12px;">
                    <strong>Pending Requests:</strong> ${requests.length}
                </div>
                <div style="max-height: 200px; overflow-y: auto;">
                    ${requests.slice(0, 10).map(req => `
                        <div style="padding: 8px; border-bottom: 1px solid #eee; font-size: 13px;">
                            <strong>${req.book_title || 'N/A'}</strong> - ${req.department_code || ''}
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (type === 'classification') {
            const classifications = data.data || [];
            html = `
                <div style="padding: 12px; background: white; border-radius: 8px; margin-bottom: 12px;">
                    <strong>Total Classifications:</strong> ${classifications.length}
                </div>
                <div style="max-height: 200px; overflow-y: auto;">
                    ${classifications.map(c => `
                        <div style="padding: 8px; border-bottom: 1px solid #eee; font-size: 13px;">
                            <strong>${c.name || 'N/A'}</strong> (${c.call_number_range || 'N/A'}) - ${c.location || 'No location'}
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        reportData.innerHTML = html;
        
    } catch (error) {
        reportData.innerHTML = `<div style="color: red; padding: 20px; text-align: center;">Error generating report: ${error.message}</div>`;
    }
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function downloadReport() {
    const reportTitle = document.getElementById('reportTitle').textContent;
    const reportData = document.getElementById('reportData').innerText;
    
    // Simple text download
    const blob = new Blob([`${reportTitle}\n\n${reportData}`], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${reportTitle.toLowerCase().replace(/\s+/g, '-')}-${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ===== Add Book Modal Function =====
function openAddBookModal() {
    const m = document.getElementById('addBookModal');
    if (m) {
        m.style.display = 'flex';
        m.style.setProperty('overflow', 'hidden', 'important');
        setTimeout(function(){ 
            if(typeof validateAddBookButton === 'function') validateAddBookButton(); 
        }, 100);
    }
}

// ===== Material Request Modal Functions =====
let currentMaterialRequest = null;

function openProcessRequestModal(requestId) {
    document.getElementById('processingRequestId').value = requestId;
    document.getElementById('processRequestModal').style.display = 'flex';
}

function closeProcessRequestModal() {
    document.getElementById('processRequestModal').style.display = 'none';
    document.getElementById('processingNotes').value = '';
}

async function confirmProcessRequest() {
    const requestId = document.getElementById('processingRequestId').value;
    const notes = document.getElementById('processingNotes').value;
    
    try {
        const response = await fetch('api/update_material_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: requestId,
                status: 'processing',
                librarian_notes: notes || 'Request is being processed'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeProcessRequestModal();
            showDashboardSuccessModal('Success', 'Request marked as processing');
            loadMaterialRequests();
        } else {
            closeProcessRequestModal();
            showDashboardErrorModal(result.message || 'Failed to process request');
        }
    } catch (error) {
        closeProcessRequestModal();
        showDashboardErrorModal('Failed to process request: ' + error.message);
    }
}

function openCompleteRequestModal(requestId) {
    document.getElementById('completingRequestId').value = requestId;
    document.getElementById('completeRequestModal').style.display = 'flex';
}

function closeCompleteRequestModal() {
    document.getElementById('completeRequestModal').style.display = 'none';
    document.getElementById('completionNotes').value = '';
}

async function confirmCompleteRequest() {
    const requestId = document.getElementById('completingRequestId').value;
    const notes = document.getElementById('completionNotes').value;
    
    try {
        const response = await fetch('api/update_material_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: requestId,
                status: 'completed',
                librarian_notes: notes || 'Request completed'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeCompleteRequestModal();
            showDashboardSuccessModal('Success', 'Request completed successfully!');
            loadMaterialRequests();
        } else {
            closeCompleteRequestModal();
            showDashboardErrorModal(result.message || 'Failed to complete request');
        }
    } catch (error) {
        closeCompleteRequestModal();
        showDashboardErrorModal('Failed to complete request: ' + error.message);
    }
}

function openRejectRequestModal(requestId) {
    document.getElementById('rejectingRequestId').value = requestId;
    document.getElementById('rejectRequestModal').style.display = 'flex';
}

function closeRejectRequestModal() {
    document.getElementById('rejectRequestModal').style.display = 'none';
    document.getElementById('rejectionReason').value = '';
}

async function confirmRejectRequest() {
    const requestId = document.getElementById('rejectingRequestId').value;
    const reason = document.getElementById('rejectionReason').value.trim();
    
    if (!reason) {
        showDashboardErrorModal('Rejection reason is required');
        return;
    }
    
    try {
        const response = await fetch('api/update_material_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: requestId,
                status: 'rejected',
                librarian_notes: reason
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeRejectRequestModal();
            showDashboardSuccessModal('Success', 'Request rejected');
            loadMaterialRequests();
        } else {
            closeRejectRequestModal();
            showDashboardErrorModal(result.message || 'Failed to reject request');
        }
    } catch (error) {
        closeRejectRequestModal();
        showDashboardErrorModal('Failed to reject request: ' + error.message);
    }
}

// ===== Success/Error Modal Functions =====
function showDashboardSuccessModal(title, message) {
    document.getElementById('dashboardSuccessTitle').textContent = title || 'Success!';
    document.getElementById('dashboardSuccessMessage').textContent = message || 'Operation completed successfully.';
    document.getElementById('dashboardSuccessModal').style.display = 'flex';
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function closeDashboardSuccessModal() {
    document.getElementById('dashboardSuccessModal').style.display = 'none';
}

function showDashboardErrorModal(message) {
    document.getElementById('dashboardErrorMessage').textContent = message || 'An error occurred.';
    document.getElementById('dashboardErrorModal').style.display = 'flex';
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function closeDashboardErrorModal() {
    document.getElementById('dashboardErrorModal').style.display = 'none';
}

// ===== Update Material Request Action Buttons =====
function updateMaterialRequestActions() {
    // Override the old alert-based functions with modal-based ones
    window.processMaterialRequest = function(requestId) {
        openProcessRequestModal(requestId);
    };
    
    window.completeMaterialRequest = function(requestId) {
        openCompleteRequestModal(requestId);
    };
    
    window.rejectMaterialRequest = function(requestId) {
        openRejectRequestModal(requestId);
    };
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    updateMaterialRequestActions();
});

</script> 
