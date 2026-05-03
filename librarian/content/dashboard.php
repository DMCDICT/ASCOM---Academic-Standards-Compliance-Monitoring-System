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
    'compliant_courses' => 0,
    'non_compliant_courses' => 0
);

try {
    // Calculate statistics directly
    // 1. Total Books - Count only compliant book references (within 5 years)
    $totalBooksQuery = "
        SELECT COUNT(*) as total_books
        FROM book_references 
        WHERE (YEAR(CURDATE()) - publication_year) < 5
    ";
    $totalBooksStmt = $pdo->prepare($totalBooksQuery);
    $totalBooksStmt->execute();
    $totalBooksResult = $totalBooksStmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_books'] = (int)$totalBooksResult['total_books'];
    
    // 2. Compliant Courses - Count courses with 5 or more compliant books
    $compliantCoursesQuery = "
        SELECT COUNT(DISTINCT c.id) as compliant_courses
        FROM courses c
        INNER JOIN (
            SELECT course_id, COUNT(*) as compliant_count
            FROM book_references 
            WHERE (YEAR(CURDATE()) - publication_year) < 5
            GROUP BY course_id
            HAVING compliant_count >= 5
        ) compliant ON c.id = compliant.course_id
    ";
    $compliantCoursesStmt = $pdo->prepare($compliantCoursesQuery);
    $compliantCoursesStmt->execute();
    $compliantCoursesResult = $compliantCoursesStmt->fetch(PDO::FETCH_ASSOC);
    $stats['compliant_courses'] = (int)$compliantCoursesResult['compliant_courses'];
    
    // 3. Non-Compliant Courses - Count courses with less than 5 compliant books
    $nonCompliantCoursesQuery = "
        SELECT COUNT(DISTINCT c.id) as non_compliant_courses
        FROM courses c
        LEFT JOIN (
            SELECT course_id, COUNT(*) as compliant_count
            FROM book_references 
            WHERE (YEAR(CURDATE()) - publication_year) < 5
            GROUP BY course_id
        ) compliant ON c.id = compliant.course_id
        WHERE COALESCE(compliant.compliant_count, 0) < 5
    ";
    $nonCompliantCoursesStmt = $pdo->prepare($nonCompliantCoursesQuery);
    $nonCompliantCoursesStmt->execute();
    $nonCompliantCoursesResult = $nonCompliantCoursesStmt->fetch(PDO::FETCH_ASSOC);
    $stats['non_compliant_courses'] = (int)$nonCompliantCoursesResult['non_compliant_courses'];
    
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
            <div class="dept-badge">
                <?php echo htmlspecialchars($departmentCode); ?> Librarian
            </div>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="dashboard-stats-grid">
        <div class="box">
            <div class="box-icon">
                <i data-lucide="book-check"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Compliant Books</span>
                <h3 class="amount"><?php echo number_format($stats['total_books']); ?></h3>
                <span class="amount-sub">Within 5-year range</span>
            </div>
        </div>
        
        <div class="box">
            <div class="box-icon" style="background: rgba(21, 101, 192, 0.08); color: #1565C0;">
                <i data-lucide="graduation-cap"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Compliant Courses</span>
                <h3 class="amount" style="color: #1565C0;"><?php echo number_format($stats['compliant_courses']); ?></h3>
                <span class="amount-sub">5+ compliant books</span>
            </div>
        </div>

        <div class="box">
            <div class="box-icon" style="background: rgba(185, 28, 28, 0.08); color: #b91c1c;">
                <i data-lucide="alert-circle"></i>
            </div>
            <div class="box-content">
                <span class="box-label">Non-Compliant</span>
                <h3 class="amount" style="color: #b91c1c;"><?php echo number_format($stats['non_compliant_courses']); ?></h3>
                <span class="amount-sub">Needs attention</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="content.php?page=library-management" class="quick-action">
            <div class="qa-icon"><i data-lucide="plus"></i></div>
            <span>Add New Book</span>
        </a>
        <a href="content.php?page=material-processing" class="quick-action">
            <div class="qa-icon"><i data-lucide="clipboard-list"></i></div>
            <span>Process Requests</span>
        </a>
        <button class="quick-action" onclick="openAddClassificationModal()">
            <div class="qa-icon"><i data-lucide="tags"></i></div>
            <span>New Classification</span>
        </button>
        <a href="content.php?page=reports" class="quick-action">
            <div class="qa-icon"><i data-lucide="file-text"></i></div>
            <span>Generate Reports</span>
        </a>
    </div>

    <!-- Material Processing Section -->
    <div class="dashboard-section">
        <div class="dashboard-section__top">
            <div class="section-header">
                <div class="label-bar"></div>
                <div class="header-content">
                    <h3>Material Processing</h3>
                    <p>Books and materials currently being processed for library cataloging</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="content.php?page=material-processing" class="view-all-btn">
                    <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
                    View All
                </a>
                <div class="nav-controls">
                    <button class="nav-btn" id="prevBtn" onclick="showPreviousMaterials()">
                        <i data-lucide="chevron-left"></i>
                    </button>
                    <button class="nav-btn" id="nextBtn" onclick="showNextMaterials()">
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        </div>
        
        <div class="material-processing-container" id="materialProcessingContainer">
            <div class="material-processing-grid" id="materialProcessingGrid">
                <!-- Material processing cards will be dynamically generated here -->
            </div>
            <!-- Empty state message -->
            <div id="emptyStateMessage" class="empty-state" style="display: none;">
                <i data-lucide="check-circle" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>
                <h3>All Caught Up!</h3>
                <p>There are no pending materials to process at the moment.</p>
            </div>
        </div>
        
        <div class="section-footer">
            <button
                type="button"
                id="materialSectionToggleBtn"
                class="btn-ghost section-toggle-btn"
                aria-controls="materialProcessingContainer"
                aria-expanded="true"
                onclick="toggleMaterialSection()"
                title="Collapse/expand this section"
            >
                <span class="toggle-label">Collapse</span>
                <i data-lucide="chevron-up" class="toggle-icon" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
    </div>

    <!-- Material Requests from Deans Section -->
    <div class="dashboard-section" style="margin-bottom: 40px;">
        <div class="dashboard-section__top">
            <div class="section-header">
                <div class="label-bar" style="background: linear-gradient(180deg, #7C3AED 0%, #A78BFA 100%);"></div>
                <div class="header-content">
                    <h3>Material Requests from Deans</h3>
                    <p>Book requests from department deans for non-compliant courses</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="filter-buttons" style="display: flex; gap: 8px; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 14px;">
                    <button class="filter-btn active" onclick="filterMaterialRequests('pending')" id="mrPendingBtn" style="padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer;">Pending</button>
                    <button class="filter-btn" onclick="filterMaterialRequests('processing')" id="mrProcessingBtn" style="padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer;">Processing</button>
                    <button class="filter-btn" onclick="filterMaterialRequests('completed')" id="mrCompletedBtn" style="padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer;">Completed</button>
                </div>
            </div>
        </div>
        
        <div class="material-processing-container">
            <div class="material-processing-grid" id="materialRequestsGrid">
                <!-- Material requests will be dynamically generated here -->
            </div>
            <div id="materialRequestsEmptyState" class="empty-state" style="display: none;">
                <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>
                <h3>No Requests</h3>
                <p>No material requests at the moment.</p>
            </div>
        </div>
    </div>

    <!-- Newly Acquired Books Section -->
    <div class="dashboard-section">
        <div class="dashboard-section__top">
            <div class="section-header">
                <div class="label-bar" style="background: linear-gradient(180deg, #1565C0 0%, #42A5F5 100%);"></div>
                <div class="header-content">
                    <h3>Newly Acquired Books</h3>
                    <p>Recent additions to the collection from all library locations</p>
                </div>
            </div>
        </div>

        <div class="material-processing-container">
            <div class="newly-acquired-container" id="newlyAcquiredBooksGrid">
                <!-- Newly acquired book cards will be dynamically generated here -->
            </div>
            <div id="newlyAcquiredEmptyState" class="empty-state">
                <i data-lucide="book" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>
                <h3>No New Additions</h3>
                <p>Newly cataloged books will appear here for quick review.</p>
            </div>
        </div>
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


<script>
// Material Processing Variables
let allMaterials = [];
let currentPage = 0;
let materialsPerPage = 4;

// Classification Management Variables
let allClassifications = [];
let currentClassificationPage = 0;
let classificationsPerPage = 3;


// (Sample classification data removed – dashboard now shows only real DB data)


// Initialize both sections
document.addEventListener('DOMContentLoaded', function() {
    
    // Load processing materials from database
    loadProcessingMaterials();
    
    
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

function displayCurrentPage() {
    const grid = document.getElementById('materialProcessingGrid');
    const emptyStateMessage = document.getElementById('emptyStateMessage');
    if (!grid) return;
    
    // allMaterials is already filtered to only include processing materials
    const startIndex = currentPage * materialsPerPage;
    const endIndex = startIndex + materialsPerPage;
    const currentMaterials = allMaterials.slice(startIndex, endIndex);
    
    // Show empty state message if no materials and container is expanded
    if (allMaterials.length === 0) {
        grid.innerHTML = '';
        const container = document.querySelector('.material-processing-container');
        if (emptyStateMessage && container && container.style.display !== 'none') {
            emptyStateMessage.style.display = 'block';
        }
    } else {
        grid.innerHTML = currentMaterials.map(material => createMaterialCard(material)).join('');
        if (emptyStateMessage) {
            emptyStateMessage.style.display = 'none';
        }
    }
    
}

function updateSectionState() {
    const section = document.querySelector('.dashboard-section');
    const container = section.querySelector('.material-processing-container');
    const footer = section.querySelector('.section-footer');
    const headerActions = section.querySelector('.header-actions');
    const emptyStateMessage = document.getElementById('emptyStateMessage');
    const existingCollapsedControls = section.querySelector('.collapsed-controls');
    
    // Remove any dynamically created collapsed controls
    if (existingCollapsedControls) {
        existingCollapsedControls.remove();
    }
    
    if (allMaterials.length === 0) {
        // Empty state: collapse and show badge + expand button in header actions area
        container.style.display = 'none';
        footer.style.display = 'none';
        
        // Hide navigation buttons and replace with collapsed controls
        headerActions.style.display = 'none';
        
        // Create collapsed controls in header actions area
        const collapsedControls = document.createElement('div');
        collapsedControls.className = 'collapsed-controls';
        collapsedControls.style.display = 'flex';
        collapsedControls.innerHTML = `
            <div class="request-count-badge" style="background: #95a5a6; color: white;">0</div>
            <button class="expand-btn" onclick="toggleMaterialSection()">
                <span>Expand</span>
                <i data-lucide="chevron-down" style="width: 14px; height: 14px;"></i>
            </button>
        `;
        
        // Re-initialize Lucide icons for the newly injected content
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: 'lucide'
                },
                nameAttr: 'data-lucide',
                target: collapsedControls
            });
        }
        
        // Insert collapsed controls where header-actions was
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
    } else {
        // Has materials: expand and show list
        container.style.display = 'block';
        footer.style.display = 'flex';
        headerActions.style.display = 'flex';

        const toggleBtn = document.getElementById('materialSectionToggleBtn');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'true');
            const label = toggleBtn.querySelector('.toggle-label');
            if (label) label.textContent = 'Collapse';
        }
        
        // Hide empty state message
        if (emptyStateMessage) {
            emptyStateMessage.style.display = 'none';
        }
    }
}

function createMaterialCard(material) {
    const statusClass = `status-${material.status}`;
    const statusText = material.status.charAt(0).toUpperCase() + material.status.slice(1);
    
    // Get department color from material data
    const departmentColor = material.departmentColor || '#C41E3A';
    
    // Create action buttons based on status
    let actionButtons = '';
    if (material.status === 'processing') {
        actionButtons = `
            <button class="action-btn catalog-btn" onclick="startCataloging(${material.id})">Start Cataloging</button>
            <button class="action-btn draft-btn" onclick="draftRequest(${material.id})">Draft</button>
        `;
    } else if (material.status === 'completed') {
        actionButtons = `
            <button class="action-btn process-btn" onclick="navigateToCourseDetails('${material.courseCode}')">Navigate</button>
        `;
    } else if (material.status === 'drafted') {
        actionButtons = `
            <button class="action-btn resume-btn" onclick="resumeProcessing(${material.id})">Resume</button>
        `;
    }
    
    return `
        <div class="material-card">
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

function formatAPACitation(material) {
    // APA 7th Edition format: Author, A. A. (Year). Title of work. Publisher.
    const authorParts = material.author.split(' ');
    const lastName = authorParts[authorParts.length - 1];
    const firstName = authorParts.slice(0, -1).join(' ');
    const initials = firstName.split(' ').map(name => name.charAt(0) + '.').join(' ');
    
    const year = new Date().getFullYear(); // Using current year as placeholder
    const title = material.title;
    
    return `${lastName}, ${initials} (${year}). ${title}.`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function showPreviousMaterials() {
    if (currentPage > 0) {
        currentPage--;
        displayCurrentPage();
        updateNavigationButtons();
    }
}

function showNextMaterials() {
    const maxPage = Math.ceil(allMaterials.length / materialsPerPage) - 1;
    if (currentPage < maxPage) {
        currentPage++;
        displayCurrentPage();
        updateNavigationButtons();
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const totalMaterials = allMaterials.length;
    const totalPages = Math.ceil(totalMaterials / materialsPerPage);

    // Hide prev button on first page
    if (currentPage === 0) {
        prevBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'inline-flex';
    }

    // Hide next button on last page
    if (currentPage >= totalPages - 1) {
        nextBtn.style.display = 'none';
    } else {
        nextBtn.style.display = 'inline-flex';
    }

    // Hide both buttons if there's only one page or no materials
    if (totalMaterials <= materialsPerPage) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    }
}

function toggleMaterialSection() {
    
    const section = document.querySelector('.dashboard-section');
    const container = section.querySelector('.material-processing-container');
    const footer = section.querySelector('.section-footer');
    const collapseBtn = section.querySelector('.collapse-btn');
    const headerActions = section.querySelector('.header-actions');
    const emptyStateMessage = document.getElementById('emptyStateMessage');
    const existingCollapsedControls = section.querySelector('.collapsed-controls');
    
    
    
    // Check if container is currently hidden
    const isCurrentlyHidden = container.style.display === 'none';
    
    const toggleBtn = document.getElementById('materialSectionToggleBtn');
    
    if (isCurrentlyHidden) {
        // Expand - show normal layout
        container.style.display = 'block';
        footer.style.display = 'flex';
        
        // Remove collapsed controls from header
        if (existingCollapsedControls) {
            existingCollapsedControls.remove();
        }
        
        // Restore the navigation buttons
        headerActions.style.display = 'flex';

        // Update footer toggle button state
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'true');
            const label = toggleBtn.querySelector('.toggle-label');
            if (label) label.textContent = 'Collapse';
        }
        
        // Show empty state message if no materials
        if (allMaterials.length === 0 && emptyStateMessage) {
            emptyStateMessage.style.display = 'block';
        }
        
    } else {
        // Collapse - just replace navigation buttons with red badge + expand button
        container.style.display = 'none';
        footer.style.display = 'none';
        
        // Hide the navigation buttons
        headerActions.style.display = 'none';

        // Update footer toggle button state (even though footer is hidden)
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
            const label = toggleBtn.querySelector('.toggle-label');
            if (label) label.textContent = 'Expand';
        }
        
        // Hide empty state message
        if (emptyStateMessage) {
            emptyStateMessage.style.display = 'none';
        }
        
        // Create collapsed controls in header actions area
        const totalMaterials = allMaterials.length;
        const collapsedControls = document.createElement('div');
        collapsedControls.className = 'collapsed-controls';
        collapsedControls.style.display = 'flex';
        const badgeColor = totalMaterials === 0 ? '#95a5a6' : '#ff4c4c';
        collapsedControls.innerHTML = `
            <div class="request-count-badge" style="background: ${badgeColor}; color: white;">${totalMaterials}</div>
            <button class="expand-btn" type="button" aria-controls="materialProcessingContainer" aria-expanded="false" onclick="toggleMaterialSection()">
                <span>Expand</span>
                <i data-lucide="chevron-down" style="width: 14px; height: 14px;"></i>
            </button>
        `;
        
        // Re-initialize Lucide icons for the newly injected content
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({
                attrs: {
                    class: 'lucide'
                },
                nameAttr: 'data-lucide',
                target: collapsedControls
            });
        }
        
        // Insert the collapsed controls in the header (where header-actions was)
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
        
    }
}

// Load processing materials from API
async function loadProcessingMaterials() {
    try {
        
        const response = await fetch('api/get_processing_materials.php?status=processing');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            allMaterials = result.data;
        } else {
            console.error('Failed to load processing materials:', result.message);
            // Fallback to empty array
            allMaterials = [];
        }
        
        // Display the first page
        displayCurrentPage();
        updateNavigationButtons();
        
        // Update section state (collapsed if empty, expanded if has items)
        updateSectionState();
    } catch (error) {
        console.error('Error loading processing materials:', error);
        // Fallback to empty array
        allMaterials = [];
        displayCurrentPage();
        updateNavigationButtons();
        
        // Update section state (collapsed if empty)
        updateSectionState();
    }
}

// Helper function to update processing status via API (same as material-processing page)
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

// Material Requests from Deans Functions
let materialRequests = [];
let currentMRStatus = 'pending';

async function loadMaterialRequests() {
    try {
        const response = await fetch('api/get_material_requests.php?status=' + currentMRStatus);
        const result = await response.json();
        
        if (result.success) {
            materialRequests = result.data;
            displayMaterialRequests();
        } else {
            console.error('Failed to load material requests:', result.message);
        }
    } catch (error) {
        console.error('Error loading material requests:', error);
    }
}

function filterMaterialRequests(status) {
    currentMRStatus = status;
    
    // Update button states
    document.querySelectorAll('.filter-buttons .filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('mr' + status.charAt(0).toUpperCase() + status.slice(1) + 'Btn').classList.add('active');
    
    loadMaterialRequests();
}

function displayMaterialRequests() {
    const container = document.getElementById('materialRequestsGrid');
    const emptyState = document.getElementById('materialRequestsEmptyState');
    
    if (!container) return;
    
    if (materialRequests.length === 0) {
        container.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }
    
    if (emptyState) emptyState.style.display = 'none';
    
    container.innerHTML = materialRequests.map(request => {
        const statusBadge = getStatusBadge(request.status);
        return `
            <div class="material-card" style="border-left: 4px solid #7C3AED;">
                <div class="material-header">
                    <div class="requester-info">
                        <div class="requester-name">${request.requester_name || 'Dean'}</div>
                        <div class="requester-role" style="color: ${request.department_color || '#7C3AED'};">${request.department_code || 'Dean'} DEAN</div>
                    </div>
                    <div class="material-status" style="background: ${statusBadge.bg}; color: ${statusBadge.color};">${request.status}</div>
                </div>
                
                <div class="course-info">
                    <div class="course-code">${request.course_code || 'N/A'}</div>
                    <div class="course-name">${request.course_title || 'N/A'}</div>
                </div>
                
                <div class="request-summary">
                    <div class="material-title">
                        <strong>${request.book_title}</strong>
                        ${request.author ? '<br><span style="color: #666; font-size: 13px;">by ' + request.author + '</span>' : ''}
                        ${request.publication_year ? '<span style="color: #888; font-size: 12px;"> (' + request.publication_year + ')</span>' : ''}
                    </div>
                </div>
                
                ${request.justification ? '<div style="margin-top: 8px; padding: 8px; background: #f9f9f9; border-radius: 6px; font-size: 13px; color: #666;"><strong>Justification:</strong> ' + request.justification + '</div>' : ''}
                
                <div class="material-actions">
                    ${getActionButtons(request)}
                </div>
                
                <div class="request-date">Requested: ${formatDate(request.created_at)}</div>
            </div>
        `;
    }).join('');
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function getStatusBadge(status) {
    switch(status) {
        case 'pending':
            return { bg: '#FEF3C7', color: '#D97706' };
        case 'processing':
            return { bg: '#DBEAFE', color: '#2563EB' };
        case 'completed':
            return { bg: '#D1FAE5', color: '#059669' };
        case 'rejected':
            return { bg: '#FEE2E2', color: '#DC2626' };
        default:
            return { bg: '#F3F4F6', color: '#6B7280' };
    }
}

function getActionButtons(request) {
    if (request.status === 'pending') {
        return `
            <button class="action-btn process-btn" onclick="processMaterialRequest(${request.id})">Process</button>
            <button class="action-btn draft-btn" onclick="rejectMaterialRequest(${request.id})">Reject</button>
        `;
    } else if (request.status === 'processing') {
        return `
            <button class="action-btn catalog-btn" onclick="completeMaterialRequest(${request.id})">Complete</button>
        `;
    } else {
        return '';
    }
}

async function processMaterialRequest(requestId) {
    try {
        const response = await fetch('api/update_material_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: requestId,
                status: 'processing',
                librarian_notes: 'Request is being processed'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Request marked as processing');
            loadMaterialRequests();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error processing request:', error);
        alert('Failed to process request');
    }
}

async function completeMaterialRequest(requestId) {
    const notes = prompt('Add completion notes (optional):');
    
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
            alert('Request completed successfully!');
            loadMaterialRequests();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error completing request:', error);
        alert('Failed to complete request');
    }
}

async function rejectMaterialRequest(requestId) {
    const reason = prompt('Enter rejection reason:');
    
    if (!reason) {
        alert('Rejection reason is required');
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
            alert('Request rejected');
            loadMaterialRequests();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error rejecting request:', error);
        alert('Failed to reject request');
    }
}

// Initialize material requests on page load
document.addEventListener('DOMContentLoaded', function() {
    loadMaterialRequests();
});
        console.error('Error updating processing status:', error);
        throw error;
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
        loadProcessingMaterials().then(() => {
            // Update section state after reload
            updateSectionState();
        });
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

// Material processing action functions
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

function navigateToCourseDetails(courseCode) {
    // Navigate to course details page
    window.location.href = `content.php?page=course-details&course_code=${courseCode}`;
}

function draftRequest(materialId) {
    const material = allMaterials.find(m => m.id === materialId);
    if (material) {
        // Remove from dashboard when drafted
        allMaterials = allMaterials.filter(m => m.id !== materialId);
        displayCurrentPage();
        updateNavigationButtons();
        alert('Request has been drafted. Reason: Out of stock, budget constraints, or other issues.');
    }
}

function resumeProcessing(materialId) {
    // This function shouldn't be needed on dashboard since drafted materials don't show
    // But kept for consistency with Material Processing page
}

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

</script> 
