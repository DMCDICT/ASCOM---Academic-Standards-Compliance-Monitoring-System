<?php
// librarian/library-management-content/course-details.php
// Display course details and book references for librarian

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get course code from URL
$courseCode = $_GET['course_code'] ?? '';

if (empty($courseCode)) {
    echo '<script>window.location.href = "content.php?page=library-management";</script>';
    exit;
}

// Debug: Log the course code

// Fetch course details and all programs - get all course entries for this course code
$courseDetails = null;
$allPrograms = [];

try {
    // First, get all course entries for this course code to fetch all programs
    $allCoursesQuery = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.year_level,
            c.term,
            COALESCE(sy.school_year_label, CONCAT('A.Y. ', sy.year_start, ' - ', sy.year_end)) as academic_year,
            p.id as program_id,
            p.program_code,
            p.program_name,
            p.major,
            p.color_code as program_color
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN school_years sy ON c.academic_year = sy.id
        WHERE c.course_code = ?
        ORDER BY p.program_code
    ";
    $allCoursesStmt = $pdo->prepare($allCoursesQuery);
    $allCoursesStmt->execute([$courseCode]);
    $allCourses = $allCoursesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($allCourses && count($allCourses) > 0) {
        // Use the first course for basic details
        $courseDetails = $allCourses[0];
        
        // Extract all unique programs
        foreach ($allCourses as $course) {
            if (!empty($course['program_code'])) {
                // Check if program already exists in the array
                $exists = false;
                foreach ($allPrograms as $existingProgram) {
                    if ($existingProgram['program_code'] === $course['program_code']) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $allPrograms[] = [
                        'program_id' => $course['program_id'],
                        'program_code' => $course['program_code'],
                        'program_name' => $course['program_name'],
                        'program_major' => $course['major'] ?? null,
                        'program_color' => $course['program_color'] ?? '#1976d2'
                    ];
                }
            }
        }
        
        // Store all programs in courseDetails for display
        $courseDetails['all_programs'] = $allPrograms;
        
        // Debug: Log if course was found
    } else {
    }
} catch (Exception $e) {
}

// Fetch book references
$bookReferences = [];
if ($courseDetails && isset($courseDetails['id'])) {
    try {
        $bookQuery = "
            SELECT 
                br.id,
                br.book_title,
                br.author,
                br.isbn,
                br.publisher,
                br.publication_year,
                br.call_number,
                br.no_of_copies,
                br.edition,
                br.processing_status,
                br.status_reason,
                br.created_by,
                br.requested_by,
                COALESCE(uc.first_name, '') AS created_by_first_name,
                COALESCE(uc.last_name, '') AS created_by_last_name,
                COALESCE(ur.first_name, '') AS requested_by_first_name,
                COALESCE(ur.last_name, '') AS requested_by_last_name
            FROM book_references br
            LEFT JOIN users uc ON br.created_by = uc.id
            LEFT JOIN users ur ON br.requested_by = ur.id
            WHERE br.course_id = ?
            ORDER BY br.book_title ASC
        ";
        $bookStmt = $pdo->prepare($bookQuery);
        $bookStmt->execute([$courseDetails['id']]);
        $bookReferences = $bookStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format names for each book reference
        foreach ($bookReferences as &$ref) {
            if (!empty($ref['created_by_first_name']) || !empty($ref['created_by_last_name'])) {
                $ref['created_by_name'] = trim($ref['created_by_first_name'] . ' ' . $ref['created_by_last_name']);
            } else {
                $ref['created_by_name'] = '';
            }
            
            if (!empty($ref['requested_by_first_name']) || !empty($ref['requested_by_last_name'])) {
                $ref['requested_by_name'] = trim($ref['requested_by_first_name'] . ' ' . $ref['requested_by_last_name']);
            } else {
                $ref['requested_by_name'] = '';
            }
        }
    } catch (Exception $e) {
    }
}

?>
<style>
.back-navigation {
    margin-bottom: 20px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    background: #1976d2;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.back-button:hover {
    background-color: #1565c0;
    color: white;
    text-decoration: none;
}

.course-header-section {
    background: white;
    border-radius: 12px;
    padding: 16px 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: relative;
}

.course-title {
    font-size: 24px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 8px 0;
    font-family: 'TT Interphases', sans-serif;
}

.course-subtitle {
    font-size: 16px;
    color: #6c757d;
    margin: 0 0 20px 0;
    font-weight: 500;
}

.course-info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.info-value.term-academic-year {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-value.term-academic-year .term-line {
    font-weight: 600;
    color: #1976d2;
    font-size: 14px;
}

.info-value.term-academic-year .academic-year-line {
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
}

.info-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.info-label {
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.info-value {
    font-size: 18px;
    font-weight: 500;
    color: #212529;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.program-badge {
    display: inline-block;
    background: #1976d2;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.compliance-status {
    position: absolute;
    top: 16px;
    right: 24px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'TT Interphases', sans-serif;
}

.compliance-status.compliant {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.compliance-status.non-compliant {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive compliance status */
@media (max-width: 768px) {
    .compliance-status {
        position: static;
        display: inline-block;
        margin-bottom: 16px;
        margin-top: 8px;
    }
    
    .course-header-section {
        text-align: center;
    }
}

.book-references-section {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
}

.add-book-btn {
    background: #1976d2;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.add-book-btn:hover {
    background-color: #1565c0;
}

.book-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.book-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    background: #f8f9fa;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.book-title {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 8px;
}

.book-author {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 8px;
}

.book-details {
    display: block;
}

.book-detail-item {
    font-size: 11px;
    color: #495057;
    background: white;
    padding: 3px 6px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
}

.book-detail-item.availability-info {
    background: #e8f5e8;
    color: #2e7d32;
    border-color: #4caf50;
    font-weight: 600;
    font-size: 12px;
}

.book-detail-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 10px;
    align-items: center;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.book-detail-row:last-child {
    margin-bottom: 0;
}

.book-detail-row .book-detail-item {
    flex: 0 0 auto;
    white-space: nowrap;
}

/* Tab Styles */
.book-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.tab-button {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
}

.tab-button:hover {
    color: #0f7a53;
    background-color: #f8f9fa;
}

.tab-button.active {
    color: #0f7a53;
    border-bottom-color: #0f7a53;
    background-color: #f8f9fa;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Book Card Header */
.book-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.warning-indicator {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 16px;
    border: 1px solid #ffeaa7;
    cursor: help;
    margin-left: 8px;
    flex-shrink: 0;
}

/* Archive Button */
.archive-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.archive-btn:hover {
    background: #5a6268;
}

/* Archived Book Cards */
.book-card.archived {
    background: #f8f9fa;
    border-color: #dee2e6;
    opacity: 0.8;
}

.book-card.archived .book-title {
    color: #6c757d;
}

.no-books {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 30px 20px;
}

.not-found {
    text-align: center;
    padding: 60px 20px;
}

.not-found h2 {
    font-size: 24px;
    color: #999;
    margin-bottom: 8px;
}

.not-found p {
    color: #666;
}

/* Processing Status Buttons */
.btn-catalog,
.btn-draft,
.btn-resume {
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.btn-catalog {
    background: #1976d2;
    color: white;
}

.btn-catalog:hover {
    background: #1565c0;
}

.btn-draft {
    background: #ff9800;
    color: white;
}

.btn-draft:hover {
    background: #f57c00;
}

.btn-resume {
    background: #1565c0;
    color: white;
}

.btn-resume:hover {
    background: #0d47a1;
}

.info-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #f8f9fa;
    border-radius: 50%;
    cursor: help;
    font-size: 14px;
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
</style>

<div style="padding: 20px;">
    <div class="back-navigation">
        <button class="back-button" onclick="window.location.href='content.php?page=library-management'">
            ← Back to Library Management
        </button>
    </div>

    <?php if ($courseDetails): ?>
        <div class="course-header-section">
            <?php 
            // Calculate compliance status
            $currentYear = date('Y');
            $compliantBooks = array_filter($bookReferences, function($book) use ($currentYear) {
                $bookYear = intval($book['publication_year']);
                return ($currentYear - $bookYear) < 5;
            });
            $compliantCount = count($compliantBooks);
            $isCompliant = $compliantCount >= 5;
            ?>
            
            <div class="compliance-status <?php echo $isCompliant ? 'compliant' : 'non-compliant'; ?>">
                <?php echo $isCompliant ? 'Compliant' : 'Non-Compliant'; ?>
            </div>
            
            <h1 class="course-title"><?php echo htmlspecialchars($courseDetails['course_code']); ?> - <?php echo htmlspecialchars($courseDetails['course_title']); ?></h1>
            <p class="course-subtitle">Course Book References</p>
            
            <div class="course-info-grid">
                <div class="info-card">
                    <div class="info-label">Programs</div>
                    <div class="info-value">
                        <?php if (!empty($courseDetails['all_programs']) && count($courseDetails['all_programs']) > 0): ?>
                            <?php foreach ($courseDetails['all_programs'] as $program): ?>
                                <span class="program-badge" style="background-color: <?php echo htmlspecialchars($program['program_color']); ?>;">
                                    <?php echo htmlspecialchars($program['program_code']); ?>
                                </span>
                                <?php if (!empty($program['program_major'])): ?>
                                    <span style="color: #6c757d; font-size: 12px; font-weight: 500; margin-right: 4px;">(Major in <?php echo htmlspecialchars($program['program_major']); ?>)</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color: #999;">N/A</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Units</div>
                    <div class="info-value"><?php echo htmlspecialchars($courseDetails['units'] ?? 'N/A'); ?></div>
                </div>
                
                <?php if (!empty($courseDetails['year_level'])): ?>
                    <div class="info-card">
                        <div class="info-label">Year Level</div>
                        <div class="info-value">
                            <?php 
                                $year = $courseDetails['year_level'];
                                if ($year == 1) echo '1st Year';
                                elseif ($year == 2) echo '2nd Year';
                                elseif ($year == 3) echo '3rd Year';
                                elseif ($year == 4) echo '4th Year';
                                else echo $year . ' Year';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($courseDetails['term']) || !empty($courseDetails['academic_year'])): ?>
                    <div class="info-card">
                        <div class="info-label">Term & Academic Year</div>
                        <div class="info-value term-academic-year">
                            <?php 
                                $term = $courseDetails['term'] ?? '';
                                $academicYear = $courseDetails['academic_year'] ?? '';
                                
                                if ($term == '1st') {
                                    echo '<span class="term-line">1st Semester</span>';
                                } elseif ($term == '2nd') {
                                    echo '<span class="term-line">2nd Semester</span>';
                                } elseif ($term == 'summer') {
                                    echo '<span class="term-line">Summer</span>';
                                } else {
                                    echo '<span class="term-line">' . htmlspecialchars($term) . '</span>';
                                }
                                
                                if ($academicYear) {
                                    echo '<span class="academic-year-line">' . htmlspecialchars($academicYear) . '</span>';
                                }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="book-references-section">
            <?php 
            // Count only compliant books (less than 5 years old)
            $currentYear = date('Y');
            $compliantBooks = array_filter($bookReferences, function($book) use ($currentYear) {
                $bookYear = intval($book['publication_year']);
                return ($currentYear - $bookYear) < 5;
            });
            $compliantCount = count($compliantBooks);
            ?>
            <div class="section-header">
                <h2 class="section-title">Book References (<?php echo $compliantCount; ?>)</h2>
                <button class="add-book-btn" onclick="openAddBookModal()">
                    + Add Book Reference
                </button>
            </div>
            
            <!-- Book References Tabs -->
            <div class="book-tabs">
                <button class="tab-button active" onclick="switchTab('compliant')" id="compliantTab">
                    Compliant References
                </button>
                <button class="tab-button" onclick="switchTab('nonCompliant')" id="nonCompliantTab">
                    Non-Compliant References
                </button>
            </div>
            
            <!-- Compliant References Tab -->
            <div id="compliantReferences" class="tab-content active">
            <?php if (count($bookReferences) > 0): ?>
                <div class="book-grid">
                    <?php foreach ($bookReferences as $book): ?>
                            <?php 
                            $currentYear = date('Y');
                            $bookYear = intval($book['publication_year']);
                            $yearsOld = $currentYear - $bookYear;
                            $isNearExpiry = $yearsOld >= 4 && $yearsOld < 5;
                            $isExpired = $yearsOld >= 5;
                            
                            // Only show current references (less than 5 years old)
                            if (!$isExpired): 
                            ?>
                        <div class="book-card">
                                    <div class="book-card-header">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['book_title']); ?></h3>
                                        <?php if ($isNearExpiry): ?>
                                            <div class="warning-indicator" title="This book is approaching the 5-year copyright range">
                                                ⚠️
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            
                            <div class="book-details">
                                        <div class="book-detail-row">
                                            <?php if (!empty($book['author'])): ?>
                                                <span class="book-detail-item">Author: <?php echo htmlspecialchars($book['author']); ?></span>
                                            <?php endif; ?>
                                
                                <?php if (!empty($book['publisher'])): ?>
                                    <span class="book-detail-item">Publisher: <?php echo htmlspecialchars($book['publisher']); ?></span>
                                <?php endif; ?>
                                        </div>
                                        
                                        <div class="book-detail-row">
                                            <?php if (!empty($book['call_number'])): ?>
                                                <span class="book-detail-item">Call No.: <?php echo htmlspecialchars($book['call_number']); ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($book['isbn'])): ?>
                                                <span class="book-detail-item">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
                                            <?php endif; ?>
                                
                                <?php if (!empty($book['publication_year'])): ?>
                                    <span class="book-detail-item">Copyright Year: <?php echo htmlspecialchars($book['publication_year']); ?></span>
                                <?php endif; ?>
                                
                                            <?php if (!empty($book['no_of_copies'])): ?>
                                                <span class="book-detail-item availability-info">
                                                    <strong>Copies Available: <?php echo htmlspecialchars($book['no_of_copies']); ?></strong>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                            <div>
                                                <?php if (!empty($book['requested_by_name'])): ?>
                                                    <div style="font-size: 11px; color: #6c757d;">Requested by: <?php echo htmlspecialchars($book['requested_by_name']); ?></div>
                                                <?php elseif (!empty($book['created_by_name'])): ?>
                                                    <div style="font-size: 11px; color: #6c757d;">Added by: <?php echo htmlspecialchars($book['created_by_name']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($book['processing_status']) && ($book['processing_status'] === 'processing' || $book['processing_status'] === 'drafted')): ?>
                                                <div style="display: flex; gap: 8px; align-items: center;">
                                                    <?php if ($book['processing_status'] === 'processing'): ?>
                                                        <button class="btn-catalog" onclick="startCataloging(<?php echo $book['id']; ?>)">Start Cataloging</button>
                                                        <button class="btn-draft" onclick="draftBook(<?php echo $book['id']; ?>)">Draft</button>
                                                    <?php elseif ($book['processing_status'] === 'drafted'): ?>
                                                        <?php if (!empty($book['status_reason'])): ?>
                                                            <span class="info-icon" title="<?php echo htmlspecialchars($book['status_reason']); ?>">ℹ️</span>
                                                        <?php endif; ?>
                                                        <button class="btn-resume" onclick="resumeProcessing(<?php echo $book['id']; ?>)">Resume</button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-books">
                        <p>No compliant book references found for this course.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Non-Compliant References Tab -->
            <div id="nonCompliantReferences" class="tab-content">
                <?php 
                $archivedBooks = array_filter($bookReferences, function($book) {
                    $currentYear = date('Y');
                    $bookYear = intval($book['publication_year']);
                    return ($currentYear - $bookYear) >= 5;
                });
                ?>
                <?php if (count($archivedBooks) > 0): ?>
                    <div class="book-grid">
                        <?php foreach ($archivedBooks as $book): ?>
                            <div class="book-card archived">
                                <div class="book-card-header">
                                    <h3 class="book-title"><?php echo htmlspecialchars($book['book_title']); ?></h3>
                                    <button class="archive-btn" onclick="archiveBook(<?php echo $book['id']; ?>)">
                                        📦 Archive
                                    </button>
                                </div>
                                
                                <div class="book-details">
                                    <div class="book-detail-row">
                                        <?php if (!empty($book['author'])): ?>
                                            <span class="book-detail-item">Author: <?php echo htmlspecialchars($book['author']); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($book['publisher'])): ?>
                                            <span class="book-detail-item">Publisher: <?php echo htmlspecialchars($book['publisher']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="book-detail-row">
                                <?php if (!empty($book['call_number'])): ?>
                                            <span class="book-detail-item">Call No.: <?php echo htmlspecialchars($book['call_number']); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($book['isbn'])): ?>
                                            <span class="book-detail-item">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($book['publication_year'])): ?>
                                            <span class="book-detail-item">Copyright Year: <?php echo htmlspecialchars($book['publication_year']); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($book['no_of_copies'])): ?>
                                            <span class="book-detail-item availability-info">
                                                <strong>Copies Available: <?php echo htmlspecialchars($book['no_of_copies']); ?></strong>
                                            </span>
                                <?php endif; ?>
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                        <?php if (!empty($book['requested_by_name']) || !empty($book['created_by_name'])): ?>
                                            <div>
                                                <?php if (!empty($book['requested_by_name'])): ?>
                                                    <div style="font-size: 11px; color: #6c757d;">Requested by: <?php echo htmlspecialchars($book['requested_by_name']); ?></div>
                                                <?php elseif (!empty($book['created_by_name'])): ?>
                                                    <div style="font-size: 11px; color: #6c757d;">Added by: <?php echo htmlspecialchars($book['created_by_name']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-books">
                        <p>No non-compliant book references found for this course.</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="not-found">
            <h2>Course Not Found</h2>
            <p>The course "<?php echo htmlspecialchars($courseCode); ?>" could not be found.</p>
        </div>
    <?php endif; ?>
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

<!-- Complete Cataloging Modal -->
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

<script>
// Store course information globally for modal use
const currentCourseCode = <?php echo json_encode($courseDetails['course_code'] ?? ''); ?>;
const currentCourseTitle = <?php echo json_encode($courseDetails['course_title'] ?? ''); ?>;
const currentCourseId = <?php echo $courseDetails['id'] ?? 0; ?>;

function openAddBookModal() {
    
    // Show modal
    const modal = document.getElementById('addBookModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.style.setProperty('overflow', 'hidden', 'important');
        
        // Prevent body scroll - SAME AS WORKING TEST BUTTONS
        if (typeof lockPageScroll === 'function') {
            lockPageScroll();
        } else {
            // Fallback
            document.body.style.setProperty('overflow', 'hidden', 'important');
            document.body.style.setProperty('position', 'fixed', 'important');
            document.body.style.setProperty('width', '100%', 'important');
            document.body.style.setProperty('height', '100%', 'important');
            document.body.style.setProperty('top', '0', 'important');
            document.body.style.setProperty('left', '0', 'important');
            document.documentElement.style.setProperty('overflow', 'hidden', 'important');
        }
        
    } else {
    }
    
    // Set course information in the form if available
    const courseSearchInput = document.getElementById('course_search');
    const courseIdInput = document.getElementById('course_id');
    if (currentCourseId && courseSearchInput && courseIdInput) {
        // Set the course search input to display the course code and title
        courseSearchInput.value = currentCourseCode + ' - ' + currentCourseTitle;
        // Set the hidden course_id field
        courseIdInput.value = currentCourseId;
    }
    
    // Don't reset the form - keep the course filled in
    
    // Trigger validation after modal opens
    setTimeout(function() {
        if (typeof validateAddBookButton === 'function') {
            validateAddBookButton();
        }
        if (typeof setupCopyrightYearInput === 'function') {
            setupCopyrightYearInput();
        }
    }, 100);
}

// Tab switching functionality
function switchTab(tabName) {
    
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    let selectedContentId;
    if (tabName === 'compliant') {
        selectedContentId = 'compliantReferences';
    } else if (tabName === 'nonCompliant') {
        selectedContentId = 'nonCompliantReferences';
    }
    
    const selectedContent = document.getElementById(selectedContentId);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }
    
    // Activate selected tab button
    let selectedButtonId;
    if (tabName === 'compliant') {
        selectedButtonId = 'compliantTab';
    } else if (tabName === 'nonCompliant') {
        selectedButtonId = 'nonCompliantTab';
    }
    
    const selectedButton = document.getElementById(selectedButtonId);
    if (selectedButton) {
        selectedButton.classList.add('active');
    }
    
}

// Archive book functionality
function archiveBook(bookId) {
    
    if (confirm('Are you sure you want to archive this book reference? This action can be undone later.')) {
        // Here you would make an API call to archive the book
        // For now, we'll just show a success message
        alert('Book reference has been archived successfully!');
        
        // In a real implementation, you would:
        // 1. Make an API call to archive the book
        // 2. Refresh the page or update the UI
        // 3. Show success/error messages
        
    } else {
    }
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
        // Reload the page to reflect the change
        window.location.reload();
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

// Processing status functions
async function startCataloging(bookId) {
    
    // Fetch book reference data from database
    try {
        const response = await fetch(`api/get_book_reference.php?id=${bookId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const book = result.data;
            
            // Set the book ID
            document.getElementById('completingBookId').value = bookId;
            
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
            document.getElementById('completingBookId').value = bookId;
        }
    } catch (error) {
        console.error('Error fetching book reference:', error);
        // Still open modal even if fetch fails
        document.getElementById('completingBookId').value = bookId;
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

function draftBook(bookId) {
    // Navigate to Material Processing page
    window.location.href = 'content.php?page=material-processing';
}

function resumeProcessing(bookId) {
    // API call to update status back to processing
    updateProcessingStatus(bookId, 'processing')
        .then(() => {
            // Reload the page to reflect the change
            window.location.reload();
        })
        .catch(error => {
            console.error('Error resuming processing:', error);
            alert('Failed to resume processing');
        });
}

// Initialize form submission handler
document.addEventListener('DOMContentLoaded', function() {
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
</script>
