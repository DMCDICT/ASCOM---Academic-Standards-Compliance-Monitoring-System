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
    error_log('Dashboard stats calculation failed: ' . $e->getMessage());
}
?>

<div class="dashboard-container" style="display: flex; justify-content: flex-start; align-items: center; gap: 16px; margin-bottom: 0;">
  <h2 class="main-page-title" style="margin: 0; padding-top: 6px;">Overview</h2>
</div>

<!-- Three Stats Cards Row -->
<div style="width: 100%; display: flex; gap: 20px; margin-bottom: 20px; margin-top: 6px; flex-wrap: wrap;">
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Compliant Books</div>
    <div style="font-size: 2rem; font-weight: bold; color: #4CAF50; font-family: 'TT Interphases', sans-serif;"><?php echo number_format($stats['total_books']); ?></div>
  </div>
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Compliant Courses</div>
    <div style="font-size: 2rem; font-weight: bold; color: #22c55e; font-family: 'TT Interphases', sans-serif;"><?php echo number_format($stats['compliant_courses']); ?></div>
      </div>
  <div style="flex: 1 1 200px; min-width: 180px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 20px 18px; display: flex; flex-direction: column; align-items: flex-start;">
    <div style="font-size: 18px; font-weight: bold; color: #111; font-family: 'TT Interphases', sans-serif; margin-bottom: 8px;">Non-Compliant Courses</div>
    <div style="font-size: 2rem; font-weight: bold; color: #FF4C4C; font-family: 'TT Interphases', sans-serif;"><?php echo number_format($stats['non_compliant_courses']); ?></div>
  </div>
</div>

<style>
/* Material Processing Section Styles */
.dashboard-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-bottom: 12px;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px 24px 16px 24px;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 0;
    align-items: center;
}

.header-left h3 {
    margin: 0 0 8px 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    font-family: 'TT Interphases', sans-serif;
}

.section-description {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.view-all-btn {
    display: inline-block;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0px;
    align-self: flex-start;
}

.view-all-btn:hover {
    background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
}

.nav-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    font-family: 'TT Interphases', sans-serif;
}

.nav-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

.nav-btn:disabled {
    background: #f0f0f0;
    color: #ccc;
    cursor: not-allowed;
    transform: none;
}

.nav-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
}

.material-nav-btn .nav-icon {
    filter: brightness(0) saturate(100%);
    transition: filter 0.2s ease;
}

.material-nav-btn:hover .nav-icon {
    filter: brightness(0) saturate(100%) invert(1);
}

.material-processing-container {
    padding: 0 24px 24px 24px;
}

.material-processing-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 8px;
}

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
    margin-bottom: 12px;
}

.requester-info {
    flex: 1;
}

.requester-name {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 4px 0;
    font-family: 'TT Interphases', sans-serif;
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

.material-details {
    margin-bottom: 16px;
    flex-grow: 1;
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

.material-author {
    color: #666;
    font-size: 0.9rem;
    margin: 4px 0;
    font-family: 'TT Interphases', sans-serif;
}

.material-isbn {
    color: #888;
    font-size: 0.8rem;
    font-family: 'TT Interphases', sans-serif;
}

.material-progress {
    margin-bottom: 16px;
    margin-top: auto;
}

.progress-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 8px;
    font-family: 'TT Interphases', sans-serif;
}

.progress-bar {
    background: #e9ecef;
    border-radius: 10px;
    height: 8px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.material-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    margin-bottom: 4px;
}

.request-date {
    font-size: 11px;
    color: #999;
    text-align: center;
    margin-top: 2px;
    margin-bottom: 2px;
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

.process-btn {
    background: #4CAF50;
    color: white;
}

.process-btn:hover {
    background: #45a049;
}

.catalog-btn {
    background: #2196F3;
    color: white;
}

.catalog-btn:hover {
    background: #1976d2;
}

.resume-btn {
    background: #1976d2;
    color: white;
}

.resume-btn:hover {
    background: #1565c0;
}

.catalog-btn {
    flex: 1.5;
}

.draft-btn {
    flex: 0.8;
    font-size: 11px;
    background: #ff9800;
    color: white;
}

.draft-btn:hover {
    background: #f57c00;
}

.section-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 0px;
    gap: 12px;
    padding: 12px 24px;
    border-top: 1px solid #f0f0f0;
}

.collapse-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.collapse-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

.collapse-icon {
    width: 14px;
    height: 14px;
    object-fit: contain;
}

.material-nav-btn .collapse-icon {
    filter: brightness(0) saturate(100%);
    transition: filter 0.2s ease;
}

.material-nav-btn:hover .collapse-icon {
    filter: brightness(0) saturate(100%) invert(1);
}

.collapsed-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.request-count-badge {
    background: #ff4c4c;
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.expand-btn {
    background: #f5f5f5;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.expand-btn:hover {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    transform: translateY(-1px);
}

/* Classification Management Section Styles - Program Management Style */
.department-card {
    cursor: pointer;
}

/* Classification location tabs */
.classification-location-tabs {
    display: inline-flex;
    gap: 8px;
    margin-top: 6px;
}

.classification-location-tab {
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid #ddd;
    background: #f9fafb;
    font-size: 13px;
    font-family: 'TT Interphases', sans-serif;
    color: #555;
    cursor: pointer;
    transition: all 0.15s ease;
}

.classification-location-tab:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
}

.classification-location-tab.active {
    background: #1d4ed8;
    border-color: #1d4ed8;
    color: #fff;
}

/* Newly Acquired Books Section Styles */
.newly-acquired-section {
    margin-top: 8px;
}

.newly-acquired-container {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.newly-book-card {
    background: #f9fafb;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-family: 'TT Interphases', sans-serif;
}

.newly-book-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #111827;
}

.newly-book-meta {
    font-size: 0.8rem;
    color: #6b7280;
}

.newly-book-badge {
    align-self: flex-start;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #d1fae5;
    color: #047857;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
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
    display: flex !important;
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

<!-- Material Processing Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="header-left">
            <h3>Material Processing</h3>
            <div class="section-description">Books and materials currently being processed for library cataloging</div>
        </div>
               <div class="header-actions">
                   <a href="content.php?page=material-processing" class="view-all-btn">View All</a>
            <button class="nav-btn prev-btn material-nav-btn" id="prevBtn" onclick="showPreviousMaterials()">
                <img src="../src/assets/icons/left-arrow-icon.png" alt="Previous" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display: none;">&lt;</span>
            </button>
            <button class="nav-btn next-btn material-nav-btn" id="nextBtn" onclick="showNextMaterials()">
                <img src="../src/assets/icons/right-arrow-icon.png" alt="Next" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display: none;">&gt;</span>
            </button>
        </div>
    </div>
    
    <div class="material-processing-container">
        <div class="material-processing-grid" id="materialProcessingGrid">
            <!-- Material processing cards will be dynamically generated here -->
        </div>
        <!-- Empty state message (shown when expanded and empty) -->
        <div id="emptyStateMessage" style="display: none; text-align: center; padding: 60px 20px; color: #666;">
            <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
            <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 18px; color: #333; margin-bottom: 8px;">No Materials to Process</h3>
            <p style="font-family: 'TT Interphases', sans-serif; font-size: 14px; color: #666;">All materials have been processed or there are no pending requests at the moment.</p>
        </div>
    </div>
    
    <div class="section-footer">
        <button class="collapse-btn material-nav-btn" onclick="toggleMaterialSection()">
            <span>Collapse</span>
            <img src="../src/assets/icons/right-arrow-icon.png" alt="Collapse" class="collapse-icon" style="transform: rotate(-90deg);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display: none;">^</span>
        </button>
    </div>
</div>

<!-- Newly Acquired Books Section -->
<div class="dashboard-section newly-acquired-section">
    <div class="section-header">
        <div class="header-left">
            <h3>Newly Acquired Books</h3>
            <div class="section-description">Recent additions to the collection from all library locations</div>
        </div>
    </div>

    <div class="material-processing-container">
        <div class="newly-acquired-container" id="newlyAcquiredBooksGrid">
            <!-- Newly acquired book cards will be dynamically generated here -->
        </div>
        <div id="newlyAcquiredEmptyState" style="display: block; text-align: center; padding: 28px 16px; color: #666;">
            <div style="font-size: 32px; margin-bottom: 10px;">📗</div>
            <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 16px; color: #333; margin-bottom: 4px;">No Newly Acquired Books</h3>
            <p style="font-family: 'TT Interphases', sans-serif; font-size: 13px; color: #666; margin-bottom: 4px;">When new books are cataloged, they’ll appear here for quick review.</p>
            <p style="font-family: 'TT Interphases', sans-serif; font-size: 12px; color: #9ca3af; font-style: italic;">Note: This section only shows books that have been fully cataloged and marked as available by the librarian.</p>
        </div>
    </div>
</div>

<!-- Complete Cataloging Modal (same as material-processing page) -->
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

<!-- Classification Management Section -->
<div class="departments-section" style="margin-top: 16px; padding-bottom: 16px; margin-bottom: 24px;">
    <div class="departments-header">
        <div>
            <h3>Classification Management</h3>
            <p>Manage library classification systems and cataloging standards</p>
            <div class="classification-location-tabs">
                <button type="button" class="classification-location-tab active" data-location="Main Library">Main Library</button>
                <button type="button" class="classification-location-tab" data-location="Buenavista Library">Buenavista Library</button>
            </div>
        </div>
        <button class="add-dept-btn" id="addClassificationButton" onclick="openAddClassificationModal()">Add Classification</button>
    </div>
    
    <div class="departments-container" id="classificationContainer">
        <!-- Classification cards will be dynamically generated here -->
    </div>

</div>

<!-- Add Classification Modal -->
<div id="addClassificationModal" class="modal-overlay" style="display: none !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; background: rgba(0, 0, 0, 0.5) !important; align-items: center !important; justify-content: center !important; z-index: 10000 !important;">
    <div class="modal-content" id="addClassificationModalContent" style="max-width: 600px !important; width: 90% !important; background: white !important; border-radius: 12px !important; box-shadow: 0 8px 32px rgba(0,0,0,0.18) !important; padding: 0 !important; position: relative !important; display: flex !important; flex-direction: column !important; max-height: 90vh !important; overflow: hidden !important; margin: 0 !important;">
        <div class="modal-header" style="flex-shrink: 0; padding: 20px 24px; border-bottom: 1px solid #e0e0e0;">
            <h2 style="margin: 0; font-family: 'TT Interphases', sans-serif; font-size: 20px; color: #333;">Add New Classification</h2>
            <button class="modal-close" onclick="closeAddClassificationModal()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; line-height: 1; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>
        
        <form id="addClassificationForm" style="flex: 1; overflow-y: auto; padding: 24px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif; color: #333;">Classification Name *</label>
                <input type="text" id="classificationName" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif; color: #333;">Call Number Range *</label>
                <input type="text" id="callNumberRange" name="call_number_range" required placeholder="000-099" pattern="\d{3}-\d{3}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; box-sizing: border-box;">
                <small style="display: block; margin-top: 4px; color: #666; font-family: 'TT Interphases', sans-serif; font-size: 12px;">Format: XXX-XXX (e.g., 000-099, 100-199)</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif; color: #333;">Library Location *</label>
                <select id="classificationLocation" name="location" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; box-sizing: border-box; background: #fff;">
                    <option value="">Select Location</option>
                    <option value="Main Library">Main Library</option>
                    <option value="Buenavista Library">Buenavista Library</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-family: 'TT Interphases', sans-serif; color: #333;">Description</label>
                <textarea id="classificationDescription" name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <button type="button" onclick="closeAddClassificationModal()" style="padding: 10px 20px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; font-weight: 600; color: #333; cursor: pointer;">Cancel</button>
                <button type="submit" id="submitClassificationBtn" style="padding: 10px 20px; background: #4CAF50; border: none; border-radius: 6px; font-family: 'TT Interphases', sans-serif; font-size: 14px; font-weight: 600; color: white; cursor: pointer;">Add Classification</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal for Classification -->
<div id="classificationSuccessModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 10001;">
    <div class="modal-content" style="max-width: 400px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px; position: relative;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="font-size: 48px;">✅</span>
            </div>
        </div>
        <h2 style="color: #4CAF50; margin-bottom: 12px; font-size: 1.5em; font-family: 'TT Interphases', sans-serif; margin-top: 0;">Success!</h2>
        <p id="classificationSuccessMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1em; line-height: 1.5;"></p>
        <button type="button" onclick="closeClassificationSuccessModal()" style="margin: 0 auto; display: block; background: #4CAF50; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1em; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">OK</button>
    </div>
</div>

<!-- Error Modal for Classification -->
<div id="classificationErrorModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 10001;">
    <div class="modal-content" style="max-width: 400px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px; position: relative;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; background: #ffebee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <span style="font-size: 48px;">❌</span>
            </div>
        </div>
        <h2 style="color: #f44336; margin-bottom: 12px; font-size: 1.5em; font-family: 'TT Interphases', sans-serif; margin-top: 0;">Error</h2>
        <p id="classificationErrorMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1em; line-height: 1.5;"></p>
        <button type="button" onclick="closeClassificationErrorModal()" style="margin: 0 auto; display: block; background: #f44336; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1em; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">OK</button>
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
    console.log('Initializing Material Processing section...');
    
    // Load processing materials from database
    loadProcessingMaterials();
    
    console.log('Initializing Classification Management section...');
    
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
    
    console.log(`Displaying materials ${startIndex + 1}-${Math.min(endIndex, allMaterials.length)} of ${allMaterials.length} processing materials`);
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
            <button class="expand-btn material-nav-btn" onclick="toggleMaterialSection()">
                <span>Expand</span>
                <img src="../src/assets/icons/right-arrow-icon.png" alt="Expand" class="collapse-icon" style="transform: rotate(90deg);">
            </button>
        `;
        
        // Insert collapsed controls where header-actions was
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
    } else {
        // Has materials: expand and show list
        container.style.display = 'block';
        footer.style.display = 'flex';
        headerActions.style.display = 'flex';
        
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
    console.log('toggleMaterialSection called');
    
    const section = document.querySelector('.dashboard-section');
    const container = section.querySelector('.material-processing-container');
    const footer = section.querySelector('.section-footer');
    const collapseBtn = section.querySelector('.collapse-btn');
    const headerActions = section.querySelector('.header-actions');
    const emptyStateMessage = document.getElementById('emptyStateMessage');
    const existingCollapsedControls = section.querySelector('.collapsed-controls');
    
    console.log('Section found:', section);
    console.log('Header actions found:', headerActions);
    
    console.log('Elements found:', { container, footer, collapseBtn, headerActions });
    console.log('Container current display:', container.style.display);
    console.log('allMaterials length:', allMaterials.length);
    
    // Check if container is currently hidden
    const isCurrentlyHidden = container.style.display === 'none';
    
    console.log('Is currently hidden:', isCurrentlyHidden);
    
    if (isCurrentlyHidden) {
        // Expand - show normal layout
        console.log('Expanding section...');
        container.style.display = 'block';
        footer.style.display = 'flex';
        
        // Remove collapsed controls from header
        if (existingCollapsedControls) {
            existingCollapsedControls.remove();
        }
        
        // Restore the navigation buttons
        headerActions.style.display = 'flex';
        
        // Show empty state message if no materials
        if (allMaterials.length === 0 && emptyStateMessage) {
            emptyStateMessage.style.display = 'block';
        }
        
        console.log('Restored navigation buttons and removed collapsed controls');
    } else {
        // Collapse - just replace navigation buttons with red badge + expand button
        console.log('Collapsing section...');
        container.style.display = 'none';
        footer.style.display = 'none';
        
        // Hide the navigation buttons
        headerActions.style.display = 'none';
        
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
            <button class="expand-btn material-nav-btn" onclick="toggleMaterialSection()">
                <span>Expand</span>
                <img src="../src/assets/icons/right-arrow-icon.png" alt="Expand" class="collapse-icon" style="transform: rotate(90deg);">
            </button>
        `;
        
        // Insert the collapsed controls in the header (where header-actions was)
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
        
        console.log('Replaced navigation with badge + expand button in header');
    }
}

// Load processing materials from API
async function loadProcessingMaterials() {
    try {
        console.log('Loading processing materials from database...');
        
        const response = await fetch('api/get_processing_materials.php?status=processing');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            allMaterials = result.data;
            console.log(`Loaded ${allMaterials.length} processing materials from database`);
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
        console.log(`Drafted request: ${material.materialTitle}`);
        alert('Request has been drafted. Reason: Out of stock, budget constraints, or other issues.');
    }
}

function resumeProcessing(materialId) {
    // This function shouldn't be needed on dashboard since drafted materials don't show
    // But kept for consistency with Material Processing page
    console.log('Resume processing called');
}

// Classification Management Functions - Program Management Style
let classificationLocationFilter = 'all';

function displayAllClassifications() {
    const container = document.getElementById('classificationContainer');
    if (!container) return;
    
    let toRender = allClassifications;
    if (classificationLocationFilter !== 'all') {
        toRender = allClassifications.filter(c => {
            // Support both API data (snake_case) and sample data (no location)
            const loc = c.location || c.library_location || null;
            if (!loc) return classificationLocationFilter === 'all';
            return loc === classificationLocationFilter;
        });
    }
    
    container.innerHTML = toRender.map(classification => createClassificationCard(classification)).join('');
    
    console.log(`Displaying ${toRender.length} classifications (filter: ${classificationLocationFilter})`);
}

function createClassificationCard(classification) {
    return `
        <div class='department-card'>
            <div class='dept-code' style='background-color: #4CAF50;'>${classification.callNumberRange}</div>
            <h3>${classification.name}</h3>
            <p style='font-weight: bold; color: #333;'>${classification.description}</p>
            <div style='margin-top: 12px; text-align: right; padding-bottom: 8px;'>
                <button class='view-details-btn' onclick="viewClassificationDetails('${classification.id}')">View Shelf</button>
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

function openAddClassificationModal() {
    console.log('Opening Add Classification modal...');
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
            
            console.log(`Loaded ${allClassifications.length} classifications from database`);
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
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';
    
    const formData = {
        name: document.getElementById('classificationName').value.trim(),
        call_number_range: document.getElementById('callNumberRange').value.trim(),
        location: document.getElementById('classificationLocation').value.trim(),
        type: 'DDC', // Default to DDC
        description: document.getElementById('classificationDescription').value.trim(),
        status: 'active' // Default to active
    };
    
    try {
        const response = await fetch('api/add_classification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeAddClassificationModal();
            showClassificationSuccessModal('Classification added successfully!');
            // Reload classifications from database
            await loadClassificationsFromDatabase();
        } else {
            closeAddClassificationModal();
            showClassificationErrorModal('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error adding classification:', error);
        closeAddClassificationModal();
        showClassificationErrorModal('Failed to add classification. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
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