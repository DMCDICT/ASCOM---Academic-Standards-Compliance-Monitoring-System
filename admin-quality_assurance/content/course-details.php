<?php
// admin-quality_assurance/content/course-details.php
// Display course details and book references for Quality Assurance

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get course code from URL
$courseCode = $_GET['course_code'] ?? '';

if (empty($courseCode)) {
    echo '<script>window.location.href = "content.php?page=academic-management";</script>';
    exit;
}

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
            p.color_code as program_color,
            d.department_name,
            d.department_code
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN departments d ON p.department_id = d.id
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
    }
} catch (Exception $e) {
    error_log("Error fetching course details: " . $e->getMessage());
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
        error_log("Error fetching book references: " . $e->getMessage());
    }
}

// Calculate compliance status
$currentYear = date('Y');
$compliantBooks = array_filter($bookReferences, function($book) use ($currentYear) {
    $bookYear = intval($book['publication_year']);
    return ($currentYear - $bookYear) < 5;
});
$compliantCount = count($compliantBooks);
$totalBooks = count($bookReferences);
$isCompliant = $compliantCount >= 5;
$compliancePercentage = $totalBooks > 0 ? round(($compliantCount / 5) * 100) : 0;
if ($compliancePercentage > 100) $compliancePercentage = 100;

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
    margin-right: 4px;
    margin-bottom: 4px;
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

.compliance-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
}

.compliance-bar {
    width: 100%;
    height: 24px;
    background: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 8px;
}

.compliance-bar-fill {
    height: 100%;
    background: #4CAF50;
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
}

.compliance-bar-fill.warning {
    background: #FFA500;
}

.compliance-bar-fill.danger {
    background: #FF4C4C;
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
    
    .course-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div style="padding: 20px;">
    <div class="back-navigation">
        <button class="back-button" onclick="window.location.href='content.php?page=academic-management'">
            ← Back to Academic Management
        </button>
    </div>

    <?php if ($courseDetails): ?>
        <div class="course-header-section">
            <div class="compliance-status <?php echo $isCompliant ? 'compliant' : 'non-compliant'; ?>">
                <?php echo $isCompliant ? 'Compliant' : 'Non-Compliant'; ?>
            </div>
            
            <h1 class="course-title"><?php echo htmlspecialchars($courseDetails['course_code']); ?> - <?php echo htmlspecialchars($courseDetails['course_title']); ?></h1>
            <p class="course-subtitle">Course Book References and Compliance Details</p>
            
            <div class="compliance-info">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 600; color: #6c757d;">Compliance Status</span>
                    <span style="font-size: 16px; font-weight: 700; color: <?php echo $isCompliant ? '#4CAF50' : '#FF4C4C'; ?>;">
                        <?php echo $compliantCount; ?> / 5 Compliant References
                    </span>
                </div>
                <div class="compliance-bar">
                    <div class="compliance-bar-fill <?php 
                        echo $compliancePercentage >= 100 ? '' : ($compliancePercentage >= 80 ? 'warning' : 'danger'); 
                    ?>" style="width: <?php echo $compliancePercentage; ?>%;">
                        <?php echo $compliancePercentage; ?>%
                    </div>
                </div>
            </div>
            
            <div class="course-info-grid">
                <div class="info-card">
                    <div class="info-label">Programs</div>
                    <div class="info-value">
                        <?php if (!empty($courseDetails['all_programs']) && count($courseDetails['all_programs']) > 0): ?>
                            <?php foreach ($courseDetails['all_programs'] as $program): ?>
                                <span class="program-badge" style="background-color: <?php echo htmlspecialchars($program['program_color']); ?>;">
                                    <?php echo htmlspecialchars($program['program_code']); ?>
                                </span>
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
            <div class="section-header">
                <h2 class="section-title">Book References (<?php echo $totalBooks; ?> total, <?php echo $compliantCount; ?> compliant)</h2>
            </div>
            
            <!-- Book References Tabs -->
            <div class="book-tabs">
                <button class="tab-button active" onclick="switchTab('compliant')" id="compliantTab">
                    Compliant References (<?php echo $compliantCount; ?>)
                </button>
                <button class="tab-button" onclick="switchTab('nonCompliant')" id="nonCompliantTab">
                    Non-Compliant References (<?php echo $totalBooks - $compliantCount; ?>)
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
                                    <span class="book-detail-item">Copyright Year: <?php echo htmlspecialchars($book['publication_year']); ?> (<?php echo $yearsOld; ?> years old)</span>
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
                            <?php
                            $currentYear = date('Y');
                            $bookYear = intval($book['publication_year']);
                            $yearsOld = $currentYear - $bookYear;
                            ?>
                            <div class="book-card archived">
                                <div class="book-card-header">
                                    <h3 class="book-title"><?php echo htmlspecialchars($book['book_title']); ?></h3>
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
                                            <span class="book-detail-item" style="background: #ffeaea; color: #FF4C4C; border-color: #FF4C4C;">
                                                Copyright Year: <?php echo htmlspecialchars($book['publication_year']); ?> (<?php echo $yearsOld; ?> years old - OUTDATED)
                                            </span>
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

<script>
// Tab switching functionality
function switchTab(tabName) {
    console.log('🔄 Switching to tab:', tabName);
    
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
    
    console.log('✅ Tab switched to:', tabName);
}
</script>
