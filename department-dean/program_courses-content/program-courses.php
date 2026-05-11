<?php
// program-courses.php for Department Dean
// This file displays courses of a specific program in a table format with back navigation

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get the program code from URL parameter
$programCode = $_GET['program'] ?? '';

// Initialize courses array
$courses = [];

// Initialize programs array for modal functionality
$programs = [];

    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
// Get programs for the current dean's department
    if ($deanDepartmentCode) {
    try {
        $programsQuery = "
            SELECT p.id, p.program_code, p.program_name, d.color_code, 
                   0 as course_count
            FROM programs p
            LEFT JOIN departments d ON p.department_id = d.id
            WHERE d.department_code = ?
            ORDER BY p.created_at DESC
        ";
        
        $programsStmt = $pdo->prepare($programsQuery);
        $programsStmt->execute([$deanDepartmentCode]);
        $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $programs = [];
    }
}

// Initialize program data
$programName = 'Unknown Program';
$programColor = '#1976d2';
$programMajor = '';

// Fetch courses for the specific program
if ($deanDepartmentCode && $programCode) {
    // Direct lookup of program - bypass complex query
    $directProgramQuery = "SELECT p.program_name, p.major, d.color_code 
                      FROM programs p 
                      INNER JOIN departments d ON p.department_id = d.id 
                      WHERE p.program_code = :prog_code AND d.department_code = :dept_code";
    $directStmt = $pdo->prepare($directProgramQuery);
    $directStmt->execute([':prog_code' => $programCode, ':dept_code' => $deanDepartmentCode]);
    $directResult = $directStmt->fetch(PDO::FETCH_ASSOC);
    
    // Directly set if found
    if ($directResult) {
        $programName = $directResult['program_name'];
        $programColor = $directResult['color_code'] ?? '#1976d2';
        $programMajor = $directResult['major'] ?? '';
    }
    
// Now get courses with compliance calculation
    // Uses the same formula as dashboard.php and get_dashboard_data.php:
    // compliant = publication_year within [currentYear-4, currentYear]
    $currentYear = date('Y');
    $fiveYearsAgo = $currentYear - 4;
    
    $coursesQuery = "
        SELECT 
            c.id as course_id,
            c.course_code,
            c.course_title,
            c.units,
            d.color_code as program_color,
            CONCAT(u.first_name, ' ', u.last_name) AS faculty_name,
            c.status,
            c.term,
            c.academic_year,
            c.year_level,
            COUNT(br.id) as total_references,
            SUM(CASE 
                WHEN br.publication_year IS NOT NULL 
                AND br.publication_year >= :five_years_ago 
                AND br.publication_year <= :current_year 
                THEN 1 
                ELSE 0 
            END) as compliant_count
        FROM 
            courses c
        JOIN 
            programs p ON c.program_id = p.id
        JOIN
            departments d ON p.department_id = d.id
        LEFT JOIN 
            users u ON c.faculty_id = u.id
                AND u.is_active = TRUE
                AND EXISTS (
                    SELECT 1
                    FROM user_roles ur
                    WHERE ur.user_id = u.id
                      AND " . ascom_user_roles_role_predicate($pdo, 'ur', 'teacher') . "
                      AND " . ascom_user_roles_active_predicate($pdo, 'ur') . "
                )
        LEFT JOIN
            book_references br ON c.id = br.course_id
        WHERE 
            d.department_code = :dept_code AND p.program_code = :prog_code
        GROUP BY c.id, c.course_code, c.course_title, c.units, d.color_code, faculty_name, c.status, c.term, c.academic_year, c.year_level
        ORDER BY 
            c.course_code ASC;
    ";
    
    $coursesStmt = $pdo->prepare($coursesQuery);
    $coursesStmt->execute([':dept_code' => $deanDepartmentCode, ':prog_code' => $programCode, ':five_years_ago' => $fiveYearsAgo, ':current_year' => $currentYear]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<style>
/* DESIGN.md Animations */
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

/* DESIGN.md Component Patterns */
.btn-primary {
    background: #0C4B34;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.2px;
    transition: all 0.22s cubic-bezier(.4, 0, .2, 1);
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

.section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.label-bar {
    width: 4px;
    height: 22px;
    border-radius: 2px;
    background: linear-gradient(180deg, #0C4B34 0%, #0F7A53 100%);
    flex-shrink: 0;
    margin-top: 2px;
}

.program-courses-container {
    margin-top: 0 !important;
    padding: 0 !important;
    font-family: 'TT Interphases', sans-serif;
}

/* Hero Card with gradient top stripe */
.program-header {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(12, 75, 52, 0.14);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    padding: 28px 30px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    animation: fadeSlideUp 0.45s ease-out both;
}

.program-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, <?php echo $programColor; ?> 0%, <?php echo $programColor; ?>cc 100%);
}

.program-title {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 6px 0;
    color: #111827;
    font-family: 'TT Interphases', sans-serif;
}

.program-subtitle {
    font-size: 15px;
    color: rgba(17, 24, 39, 0.6);
    margin: 0 0 16px 0;
    font-family: 'TT Interphases', sans-serif;
    font-weight: 500;
}

.program-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.course-count-badge {
    background: rgba(12, 75, 52, 0.08);
    color: #0C4B34;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
}

.back-button {
    background: #0C4B34;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    font-family: 'TT Interphases', sans-serif;
    transition: all 0.22s cubic-bezier(.4, 0, .2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    letter-spacing: 0.2px;
}

.back-button:hover {
    background: #0a3a28;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
    text-decoration: none;
}

/* Courses Table Container - DESIGN.md pattern */
.courses-table-container {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(12, 75, 52, 0.12);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    overflow-x: auto;
    transition: all 0.28s cubic-bezier(.4, 0, .2, 1);
    animation: fadeSlideUp 0.45s ease-out both;
    animation-delay: 0.08s;
}

.courses-table-container:hover {
    box-shadow: 0 12px 36px rgba(12, 75, 52, 0.1);
}

.courses-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-family: 'TT Interphases', sans-serif;
    table-layout: fixed;
}

/* Actions column width */
.courses-table th:last-child,
.courses-table td:last-child {
    width: 100px !important;
    max-width: 100px !important;
    min-width: 100px !important;
    position: relative;
}

.courses-table th {
    background: rgba(12, 75, 52, 0.03);
    color: rgba(17, 24, 39, 0.5);
    font-weight: 700;
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid rgba(12, 75, 52, 0.08);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

.courses-table td {
    padding: 14px 16px;
    font-size: 13px;
    color: #333;
    font-weight: 500;
    border-bottom: 1px solid rgba(12, 75, 52, 0.05);
    vertical-align: middle;
}

.courses-table td:first-child {
    font-weight: 700;
    color: #111827;
}

.courses-table tbody tr {
    transition: background-color 0.15s ease;
}

.courses-table tbody tr:hover {
    background-color: rgba(12, 75, 52, 0.03);
}

.courses-table tbody tr:nth-child(even) {
    background-color: rgba(12, 75, 52, 0.015);
}

.courses-table tbody tr:last-child td {
    border-bottom: none;
}

.course-code {
    font-weight: 700;
    color: #111827;
    font-size: 14px;
}

.course-title {
    color: rgba(17, 24, 39, 0.8);
    font-size: 14px;
    line-height: 1.4;
}

.units-badge {
    background: rgba(12, 75, 52, 0.06);
    color: #0C4B34;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'TT Interphases', sans-serif;
}

.faculty-name {
    color: rgba(17, 24, 39, 0.7);
    font-size: 13px;
    font-weight: 500;
}

/* Status Badges - DESIGN.md pattern */
.status-badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-family: 'TT Interphases', sans-serif;
}

.status-active {
    background: rgba(46, 125, 50, 0.1);
    color: #2E7D32;
}

.status-pending {
    background: rgba(255, 183, 0, 0.15);
    color: #b8860b;
}

.status-rejected {
    background: rgba(185, 28, 28, 0.08);
    color: #b91c1c;
}

.status-inactive {
    background: rgba(185, 28, 28, 0.08);
    color: #b91c1c;
}

/* Empty State - DESIGN.md pattern */
.no-courses {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 18px;
    border: 1px dashed rgba(12, 75, 52, 0.2);
    font-family: 'TT Interphases', sans-serif;
}

.no-courses h3 {
    font-size: 20px;
    margin-bottom: 8px;
    color: #111827;
    font-weight: 800;
}

.no-courses p {
    font-size: 14px;
    margin: 0;
    color: rgba(17, 24, 39, 0.5);
    font-weight: 500;
}
}

/* Action Menu Styles */
.action-menu-container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.action-menu-btn {
    background: none;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 6px;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.action-menu-btn:hover {
    background-color: rgba(12, 75, 52, 0.08);
}

.three-dots {
    font-size: 18px;
    font-weight: bold;
    color: #0C4B34;
    line-height: 1;
    display: inline-block;
    user-select: none;
    cursor: pointer;
}

.action-menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid rgba(12, 75, 52, 0.14);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(12, 75, 52, 0.15);
    z-index: 99999;
    min-width: 160px;
    padding: 6px 0;
    animation: fadeSlideUp 0.2s ease-out;
}

/* When action menu is portaled to <body> to avoid clipping by scroll containers */
.action-menu-dropdown.action-menu-portal {
    position: fixed;
    top: 0;
    left: 0;
    right: auto;
    margin-top: 0;
}

.action-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    font-size: 13px;
    color: #333;
    font-weight: 500;
}

.action-menu-item:hover {
    background-color: rgba(12, 75, 52, 0.06);
}

.action-menu-item.disabled {
    color: #999;
    cursor: not-allowed;
    opacity: 0.6;
}

.action-menu-item.disabled:hover {
    background-color: transparent;
}

.action-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.actions-cell {
    position: relative;
    text-align: center;
}

.course-count {
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 12px;
    display: inline-block;
}

.book-references-count {
    background: #e8f5e8;
    color: #2e7d32;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.view-course-btn {
    background: #1976d2;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.view-course-btn:hover {
    background: #1565c0;
}

/* Edit Program Modal Styles */
.edit-program-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.edit-program-modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
    font-family: 'TT Interphases', sans-serif;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.edit-program-modal-header {
    background: linear-gradient(135deg, <?php echo $programColor; ?>, <?php echo $programColor; ?>dd);
    color: white;
    padding: 24px 30px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.edit-program-modal-title {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
}

.edit-program-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.edit-program-modal-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.edit-program-modal-body {
    padding: 30px;
}

.edit-program-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.form-group {
    flex: 1;
     display: flex;
    flex-direction: column;
 }
 
.form-group label {
     font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
}

.form-group input {
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'TT Interphases', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: white;
}

.form-group input:focus {
    outline: none;
    border-color: <?php echo $programColor; ?>;
    box-shadow: 0 0 0 3px <?php echo $programColor; ?>20;
}

.form-group input:disabled {
    background: #f8f9fa;
    color: #6c757d;
      cursor: not-allowed;
  }
  
.form-actions {
      display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 10px;
}

.cancel-btn, .create-btn {
    padding: 12px 24px;
      border: none; 
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
      cursor: pointer;
    transition: all 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.cancel-btn {
    background: #6c757d;
    color: white;
}

.cancel-btn:hover {
    background: #5a6268;
}

.create-btn {
    background: <?php echo $programColor; ?>;
    color: white;
}

.create-btn:hover:not(:disabled) {
    background: <?php echo $programColor; ?>dd;
    transform: translateY(-1px);
}

.create-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .program-header {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .program-title {
        font-size: 24px;
    }
    
    .program-header-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .courses-table-container {
        overflow-x: auto;
    }
    
    .courses-table {
        min-width: 600px;
    }
    
    .section-header {
        flex-direction: column;
    }
    
    .label-bar {
        display: none;
    }
}

/* Dark Mode - DESIGN.md pattern */
html[data-theme="dark"] .program-header {
    background-color: #1e1e1e !important;
    border-color: #333 !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
}

html[data-theme="dark"] .program-title {
    color: #e0e0e0 !important;
}

html[data-theme="dark"] .program-subtitle {
    color: rgba(224, 224, 224, 0.65) !important;
}

html[data-theme="dark"] .course-count-badge {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #81C784 !important;
}

html[data-theme="dark"] .back-button {
    background: #0F7A53 !important;
}

html[data-theme="dark"] .courses-table-container {
    background-color: #1e1e1e !important;
    border-color: #333 !important;
}

html[data-theme="dark"] .courses-table th {
    background: rgba(255, 255, 255, 0.04) !important;
    color: rgba(224, 224, 224, 0.5) !important;
}

html[data-theme="dark"] .courses-table td {
    color: #b0b0b0 !important;
    border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

html[data-theme="dark"] .courses-table td:first-child {
    color: #e0e0e0 !important;
}

html[data-theme="dark"] .courses-table tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.03) !important;
}

html[data-theme="dark"] .course-code {
    color: #e0e0e0 !important;
}

html[data-theme="dark"] .status-active {
    background: rgba(46, 125, 50, 0.2) !important;
    color: #81C784 !important;
}

html[data-theme="dark"] .status-pending {
    background: rgba(255, 183, 0, 0.2) !important;
    color: #FFD54F !important;
}

html[data-theme="dark"] .status-rejected,
html[data-theme="dark"] .status-inactive {
    background: rgba(185, 28, 28, 0.2) !important;
    color: #EF5350 !important;
}

html[data-theme="dark"] .section-header h3 {
    color: #e0e0e0 !important;
}
</style>

<div class="program-courses-container">
    <!-- Program Header - DESIGN.md Card pattern -->
    <div class="program-header">
        <div class="program-header-row">
            <div>
                <h1 class="program-title"><?php echo htmlspecialchars($programName); ?></h1>
                <p class="program-subtitle">
                    <?php echo htmlspecialchars($programCode); ?>
                    <?php if (!empty($programMajor)): ?>
                        • <?php echo htmlspecialchars($programMajor); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <span class="course-count-badge">
                    <?php echo count($courses); ?> Course<?php echo count($courses) !== 1 ? 's' : ''; ?>
                </span>
                <a href="content.php?page=program-management" class="back-button">
                    <i data-lucide="arrow-left"></i>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Courses Table Header with Actions -->
    <div class="section-header" style="margin-bottom: 16px;">
        <div class="label-bar"></div>
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0C4B34; margin: 0 0 4px 0;">Courses</h3>
            <p style="font-size: 12px; color: rgba(17, 24, 39, 0.5); margin: 0; font-weight: 600;">Manage courses for this program</p>
        </div>
    </div>
    
    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button class="btn-primary" onclick="openAddCourseModalFromProgram('<?php echo htmlspecialchars($programCode); ?>')">
            <i data-lucide="plus"></i>
            <span>Add New Course</span>
        </button>
    </div>

    <!-- Courses Table -->
    <div class="courses-table-container">
        <?php if (count($courses) > 0): ?>
            <table class="courses-table">
            <thead>
                <tr>
                                         <th>Course Code</th>
                        <th>Course Title</th>
                     <th>Units</th>
                        <th>Faculty</th>
                        <th>Status</th>
                        <th>Term</th>
                        <th>Academic Year</th>
                        <th>Year Level</th>
                        <th>Book References</th>
                        <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></td>
                            <td>
                                <span class="units-badge"><?php echo htmlspecialchars($course['units']); ?></span>
                        </td>
                            <td class="faculty-name">
                                <?php echo htmlspecialchars($course['faculty_name'] ?? 'Unassigned'); ?>
                        </td>
                            <td>
                                <?php 
                                $courseStatus = strtolower($course['status'] ?? '');
                                if ($courseStatus === 'pending') {
                                    echo '<span class="status-badge status-pending">PENDING</span>';
                                } elseif ($courseStatus === 'rejected') {
                                    echo '<span class="status-badge status-rejected">REJECTED</span>';
                                } elseif ($courseStatus === 'active') {
                                    echo '<span class="status-badge status-active">ACTIVE</span>';
                                } else {
                                    echo '<span class="status-badge status-inactive">INACTIVE</span>';
                                }
                                ?>
                        </td>
                            <td><?php echo htmlspecialchars($course['term'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($course['academic_year'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($course['year_level'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                $totalRefs = intval($course['total_references'] ?? 0);
                                $compliantCount = intval($course['compliant_count'] ?? 0);
                                $targetCount = 5;
                                $isCompliant = $compliantCount >= $targetCount;
                                $percentage = $targetCount > 0 ? round(($compliantCount / $targetCount) * 100) : 0;
                                $percentage = min(100, $percentage); // Cap at 100%
                                
                                // Determine color based on percentage
                                if ($percentage >= 100) {
                                    $bgColor = '#d1fae5';
                                    $textColor = '#059669';
                                    $statusText = 'Compliant';
                                } elseif ($percentage >= 60) {
                                    $bgColor = '#fef3c7';
                                    $textColor = '#d97706';
                                    $statusText = 'Partial';
                                } else {
                                    $bgColor = '#fee2e2';
                                    $textColor = '#dc2626';
                                    $statusText = 'Non-Compliant';
                                }
                                ?>
                                <div class="compliance-display" style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <div class="compliance-bar-container" style="width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                        <div class="compliance-bar" style="width: <?php echo $percentage; ?>%; height: 100%; background: <?php echo $textColor; ?>; border-radius: 3px; transition: width 0.3s ease;"></div>
                                    </div>
                                    <span class="compliance-text" style="font-size: 11px; font-weight: 600; color: <?php echo $textColor; ?>;">
                                        <?php echo $compliantCount; ?>/<?php echo $targetCount; ?> (<?php echo $percentage; ?>%)
                                    </span>
                                </div>
                        </td>
                            <td class="actions-cell" onclick="event.stopPropagation();">
                                <div class="action-menu-container">
                                    <button class="action-menu-btn" onclick="toggleActionMenu(event, '<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>, '<?php echo $courseStatus; ?>', <?php echo !empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned' ? 'true' : 'false'; ?>)" title="Actions" aria-label="Actions menu">
                                        <i data-lucide="more-horizontal" style="width: 20px; height: 20px;"></i>
                                    </button>
                                    <div class="action-menu-dropdown" id="actionMenu-<?php echo htmlspecialchars($course['course_code']); ?>" style="display: none;">
                                        <?php if ($courseStatus === 'pending'): ?>
                                            <div class="action-menu-item" onclick="viewCourseDetails('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="eye" class="action-icon"></i>
                                                <span>View</span>
                                            </div>
                                            <div class="action-menu-item" onclick="editCourseFromProgram('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="pencil" class="action-icon"></i>
                                                <span>Edit</span>
                                            </div>
                                            <div class="action-menu-item" onclick="approveCourse(<?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="check-circle" class="action-icon"></i>
                                                <span>Approve</span>
                                            </div>
                                            <div class="action-menu-item" onclick="rejectCourse(<?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="x-circle" class="action-icon"></i>
                                                <span>Reject</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="action-menu-item" onclick="editCourseFromProgram('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="pencil" class="action-icon"></i>
                                                <span>Edit</span>
                                            </div>
                                            <div class="action-menu-item <?php echo (!empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned') ? 'disabled' : ''; ?>" 
                                                 onclick="<?php echo (!empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned') ? 'return false;' : 'window.location.href=\'content.php?page=faculty-assignment&course_id=' . intval($course['course_id']) . '&course_code=' . urlencode($course['course_code']) . '&course_title=' . urlencode($course['course_code']) . '\''; ?>">
                                                <i data-lucide="user-plus" class="action-icon"></i>
                                                <span>Assign</span>
                                            </div>
                                            <div class="action-menu-item" onclick="viewCourseDetails('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <i data-lucide="eye" class="action-icon"></i>
                                                <span>View Details</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
            <div class="no-courses">
                <h3>No Courses Found</h3>
                <p>This program doesn't have any courses yet.</p>
        </div>
         <?php endif; ?>
     </div>
 </div>
 
 <!-- Edit Program Modal -->
<div id="editProgramModal" class="edit-program-modal">
    <div class="edit-program-modal-content">
        <div class="edit-program-modal-header">
            <h2 class="edit-program-modal-title">Edit Program</h2>
            <button class="edit-program-modal-close" onclick="closeEditProgramModal()">&times;</button>
         </div>
        <div class="edit-program-modal-body">
            <form id="editProgramForm" class="edit-program-form">
             <div class="form-row">
                    <div class="form-group" style="width: 200px;">
                     <label>Program Code</label>
                        <input type="text" name="program_code" id="editProgramCode" readonly>
                     </div>
                    <div class="form-group" style="width: 400px;">
                     <label>Program Name</label>
                        <input type="text" name="program_name" id="editProgramName" required>
                 </div>
             </div>
                
            <div class="form-row">
                    <div class="form-group" style="width: 625px;">
                    <label>Major (Optional)</label>
                    <input type="text" name="major" id="editProgramMajor" placeholder="e.g., Software Engineering, Network Administration" autocomplete="off">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Specify the major/specialization if applicable</small>
                 </div>
             </div>
                
             <div class="form-actions">
                 <button type="button" class="cancel-btn" onclick="closeEditProgramModal()">CANCEL</button>
                 <button type="submit" class="create-btn" id="updateProgramBtn" disabled>UPDATE</button>
             </div>
         </form>
     </div>
    </div>
</div>
 
 <script>
// Program data for JavaScript
const programCode = '<?php echo addslashes($programCode); ?>';
     const programName = '<?php echo addslashes($programName); ?>';
const programColor = '<?php echo addslashes($programColor); ?>';
const programMajor = '<?php echo addslashes($programMajor); ?>';
const currentProgramMajor = '<?php echo addslashes($programMajor); ?>';

// Function to view course details
function viewCourseDetails(courseCode, courseId) {
    window.location.href = 'content.php?page=course-details&course_code=' + encodeURIComponent(courseCode) + '&course_id=' + encodeURIComponent(courseId);
}

// Edit Program Modal Functions
     function editProgram(programCode) {
         
         // Get current program data
         const currentProgramName = programName;
         const currentProgramMajorValue = currentProgramMajor;
         
         
         // Check if modal exists
         const modal = document.getElementById('editProgramModal');
         if (!modal) {
             console.error('Modal not found!');
             alert('Modal element not found. Please check the page.');
             return;
         }
         
         // Fill the modal with current data
         const codeInput = document.getElementById('editProgramCode');
         const nameInput = document.getElementById('editProgramName');
         const majorInput = document.getElementById('editProgramMajor');
         
    if (codeInput && nameInput) {
             codeInput.value = programCode;
             nameInput.value = currentProgramName;
             if (majorInput) majorInput.value = currentProgramMajorValue;
             
         } else {
        console.error('Some form fields not found:', { codeInput, nameInput });
             return;
         }
         
         // Store original values for comparison
         window.originalProgramData = {
             programCode: programCode,
             programName: currentProgramName,
             programMajor: currentProgramMajorValue
         };
         
         // Show the modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Add form change listeners
    addFormChangeListeners();
    
    // Check initial form state
         checkFormChanges();
     }
     
     function closeEditProgramModal() {
    const modal = document.getElementById('editProgramModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
         
        // Reset form to original values
         if (window.originalProgramData) {
             const codeInput = document.getElementById('editProgramCode');
             const nameInput = document.getElementById('editProgramName');
             const majorInput = document.getElementById('editProgramMajor');
             
            if (codeInput && nameInput) {
                 codeInput.value = window.originalProgramData.programCode;
                 nameInput.value = window.originalProgramData.programName;
                 if (majorInput) majorInput.value = window.originalProgramData.programMajor || '';
             }
         }
         
         // Remove event listeners
         removeFormChangeListeners();
    }
     }
     
function addFormChangeListeners() {
         const form = document.getElementById('editProgramForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[name="program_name"], input[name="major"]');
    inputs.forEach(input => {
        input.addEventListener('input', checkFormChanges);
        input.addEventListener('change', checkFormChanges);
    });
     }
     
     function removeFormChangeListeners() {
         const form = document.getElementById('editProgramForm');
    if (!form) return;
         
    const inputs = form.querySelectorAll('input[name="program_name"], input[name="major"]');
         inputs.forEach(input => {
             input.removeEventListener('input', checkFormChanges);
             input.removeEventListener('change', checkFormChanges);
         });
     }
     
     function checkFormChanges() {
         const form = document.getElementById('editProgramForm');
         const updateBtn = document.getElementById('updateProgramBtn');
         
         if (!form || !updateBtn) {
             return;
         }
         
         // Get form values using the actual field names
         const programCodeField = form.querySelector('input[name="program_code"]');
         const programNameField = form.querySelector('input[name="program_name"]');
         const majorField = form.querySelector('input[name="major"]');
         
    if (!programCodeField || !programNameField) {
             return;
         }
         
         const currentData = {
             programCode: programCodeField.value,
             programName: programNameField.value,
             programMajor: majorField ? majorField.value : ''
         };
         
         const originalData = window.originalProgramData;
         
         if (!originalData) {
             return;
         }
         
         // Check if any field has changed
         const nameChanged = currentData.programName !== originalData.programName;
         const majorChanged = currentData.programMajor !== originalData.programMajor;
         
    const hasChanges = nameChanged || majorChanged;
         
         // Enable/disable update button based on changes
         updateBtn.disabled = !hasChanges;
     }
     
     // Handle form submission
     document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editProgramForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
                 e.preventDefault();
                 
                 const formData = new FormData(this);
                 const updateBtn = document.getElementById('updateProgramBtn');
                 
            if (updateBtn.disabled) {
                return;
            }
            
            // Disable button during submission
                     updateBtn.disabled = true;
                     updateBtn.textContent = 'UPDATING...';
                 
                 fetch('process_edit_program.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                    alert(data.message);
                         closeEditProgramModal();
                    // Reload the page to show updated data
                             window.location.reload();
                     } else {
                    alert('Error: ' + data.message);
                     }
                 })
                 .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the program.');
            })
            .finally(() => {
                         updateBtn.disabled = false;
                         updateBtn.textContent = 'UPDATE';
                 });
             });
         }
     
     // Close modal when clicking outside
        const modal = document.getElementById('editProgramModal');
   if (modal) {
       modal.addEventListener('click', function(e) {
           if (e.target === modal) {
            closeEditProgramModal();
        }
    });
   }
    });

// Approve and Reject Course Functions
function approveCourse(courseId) {
    if (confirm('Are you sure you want to approve this course?')) {
        
        fetch('api/approve_reject_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'course_id=' + courseId + '&action=approve'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Course approved successfully!');
                window.location.reload();
            } else {
                console.error('Error approving course:', data.message);
                alert('Error: ' + (data.message || 'Failed to approve course'));
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Network error occurred while approving the course.');
        });
    }
}

function rejectCourse(courseId) {
    if (confirm('Are you sure you want to reject this course?')) {
        
        fetch('api/approve_reject_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'course_id=' + courseId + '&action=reject'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Course rejected successfully!');
                window.location.reload();
            } else {
                console.error('Error rejecting course:', data.message);
                alert('Error: ' + (data.message || 'Failed to reject course'));
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            alert('Network error occurred while rejecting the course.');
        });
    }
}

// Toggle action menu dropdown
function toggleActionMenu(event, courseCode, courseId, courseStatus, isAssigned) {
    event.stopPropagation();
    
    // Close all other menus first
    const allMenus = document.querySelectorAll('.action-menu-dropdown');
    allMenus.forEach(menu => {
        if (menu.id !== 'actionMenu-' + courseCode) {
            menu.style.display = 'none';
        }
    });
    
    // Toggle current menu
    const menu = document.getElementById('actionMenu-' + courseCode);
    if (!menu) return;

    const trigger = event.currentTarget;
    if (trigger && !trigger.id) {
        trigger.id = `actionMenuTrigger-${courseCode}`;
    }

    if (menu.style.display === 'none' || menu.style.display === '') {
        // Portal to body so it isn't clipped by `.courses-table-container` overflow.
        if (!menu.classList.contains('action-menu-portal')) {
            menu.classList.add('action-menu-portal');
            document.body.appendChild(menu);
        }
        if (trigger && trigger.id) {
            menu.dataset.triggerId = trigger.id;
        }

        // Show first so we can measure.
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';

        const triggerRect = trigger.getBoundingClientRect();
        const menuWidth = menu.offsetWidth;
        const menuHeight = menu.offsetHeight;

        // Default placement: below + right-aligned to trigger
        let top = triggerRect.bottom + 6;
        let left = triggerRect.right - menuWidth;

        // Keep within viewport
        const padding = 8;
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        if (left < padding) left = padding;
        if (left + menuWidth > vw - padding) left = Math.max(padding, vw - padding - menuWidth);

        // If it would overflow bottom, flip above
        if (top + menuHeight > vh - padding) {
            top = triggerRect.top - 6 - menuHeight;
        }
        if (top < padding) top = padding;

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
        menu.style.visibility = 'visible';
    } else {
        menu.style.display = 'none';
    }
}

// Close action menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.action-menu-container')) {
        const allMenus = document.querySelectorAll('.action-menu-dropdown');
        allMenus.forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Reposition any open portaled menu on resize/scroll
function ascomRepositionOpenActionMenus() {
    const openMenus = document.querySelectorAll('.action-menu-dropdown.action-menu-portal');
    openMenus.forEach(menu => {
        if (menu.style.display !== 'block') return;

        const triggerId = menu.dataset.triggerId;
        const trigger = triggerId ? document.getElementById(triggerId) : null;
        if (!trigger) return;

        const triggerRect = trigger.getBoundingClientRect();
        const menuWidth = menu.offsetWidth;
        const menuHeight = menu.offsetHeight;

        let top = triggerRect.bottom + 6;
        let left = triggerRect.right - menuWidth;

        const padding = 8;
        const vw = window.innerWidth;
        const vh = window.innerHeight;

        if (left < padding) left = padding;
        if (left + menuWidth > vw - padding) left = Math.max(padding, vw - padding - menuWidth);
        if (top + menuHeight > vh - padding) top = triggerRect.top - 6 - menuHeight;
        if (top < padding) top = padding;

        menu.style.left = `${left}px`;
        menu.style.top = `${top}px`;
    });
}

window.addEventListener('resize', ascomRepositionOpenActionMenus);
window.addEventListener('scroll', ascomRepositionOpenActionMenus, true);

// Edit course from program courses page
async function editCourseFromProgram(courseCode, courseId) {
    
    try {
        // Fetch course data
        const response = await fetch(`api/get_course_data.php?course_id=${courseId}`);
        const data = await response.json();
        
        if (!data.success) {
            alert('Error: ' + (data.message || 'Failed to fetch course data'));
            return;
        }
        
        const courseData = data.course;
        
        // Prepare course data for the edit modal
        const courseDataForModal = {
            course_code: courseData.course_code || '',
            course_title: courseData.course_title || '',
            units: courseData.units || '',
            term: courseData.term || '',
            academic_year: courseData.academic_year || '',
            year_level: courseData.year_level || '',
            programs: courseData.programs || []
        };
        
        // Close the action menu
        const menu = document.getElementById('actionMenu-' + courseCode);
        if (menu) {
            menu.style.display = 'none';
        }
        
        // Open the edit course modal
        if (typeof openEditCourseModal === 'function') {
            openEditCourseModal(courseId, courseDataForModal);
        } else {
            console.error('openEditCourseModal function not found');
            alert('Edit functionality is not available. Please refresh the page and try again.');
        }
    } catch (error) {
        console.error('Error fetching course data:', error);
        alert('Error loading course data. Please try again.');
    }
}

// Assign faculty from program courses page
function assignFacultyFromProgram(courseCode, courseId, courseTitle, courseInfo) {
    // Close the action menu
    const menu = document.getElementById('actionMenu-' + courseCode);
    if (menu) {
        menu.style.display = 'none';
    }
    // Call the global assignFaculty function
    if (typeof assignFaculty === 'function') {
        assignFaculty(courseCode, courseId, courseTitle, courseInfo);
    } else {
        alert('Assign faculty functionality not available. Please refresh the page.');
    }
}

// Open add course modal from program courses page
function openAddCourseModalFromProgram(programCode) {
    // Store the program code for use in the modal
    window.currentProgramCode = programCode;
    
    // Set context to bypass course type selection and go directly to add course modal
    window.courseSelectionContext = {
        programCode: programCode,
        skipCourseTypeSelection: true
    };
    
    // Open the add course modal
    if (typeof openAddCourseModal === 'function') {
        openAddCourseModal();
    } else {
        // Try using the modal element directly
        const addCourseModal = document.getElementById('addCourseModal');
        if (addCourseModal) {
            addCourseModal.style.display = 'flex';
            addCourseModal.style.zIndex = '10000';
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Add course modal not found');
            alert('Add course functionality is not available. Please refresh the page.');
        }
    }
    
    // Try to pre-select the program after modal opens
    setTimeout(() => {
        if (typeof preSelectProgram === 'function' && window.currentProgramCode) {
            preSelectProgram(window.currentProgramCode);
        }
    }, 800);
}

// Pre-select the program in the add course modal
function preSelectProgram(programCodeOrId) {
    if (!programCodeOrId) return;
    
    // First ensure programs are loaded
    const ensureProgramsLoaded = async () => {
        if (!window.courseSelectionPrograms || window.courseSelectionPrograms.length === 0) {
            try {
                const response = await fetch('../../department-dean/api/get_programs.php');
                const data = await response.json();
                if (data.success && data.programs) {
                    window.courseSelectionPrograms = data.programs;
                }
            } catch (error) {
                console.error('Error loading programs:', error);
            }
        }
    };
    
    // Then try to set the program
    const tryPreSelect = () => {
        const selectedProgramsInput = document.getElementById('selectedPrograms');
        const programSelectText = document.getElementById('programSelectText');
        const programSelectBtn = document.getElementById('programSelectBtn');
        
        if (!selectedProgramsInput || !window.courseSelectionPrograms) return false;
        
        const programs = window.courseSelectionPrograms;
        
        // Find by program code first
        let program = programs.find(p => p.program_code === programCodeOrId);
        
        // If not found, try by ID
        if (!program) {
            program = programs.find(p => p.id == programCodeOrId);
        }
        
        if (program) {
            selectedProgramsInput.value = program.id;
            selectedProgramsInput.setAttribute('value', program.id);
            
            if (programSelectText) {
                programSelectText.textContent = program.program_name;
            }
            
            if (programSelectBtn) {
                programSelectBtn.setAttribute('data-selected-programs', program.id);
                programSelectBtn.setAttribute('data-selected-names', program.program_name);
            }
            
            // Also update window.selectedProgramsData
            window.selectedProgramsData = {
                ids: [String(program.id)],
                names: [program.program_name]
            };
            
            console.log('Pre-selected program:', program.program_name);
            return true;
        }
        
        return false;
    };
    
    // Execute
    ensureProgramsLoaded().then(() => {
        if (!tryPreSelect()) {
            // Retry a few times
            let retries = 0;
            const checkInterval = setInterval(() => {
                if (tryPreSelect() || retries >= 5) {
                    clearInterval(checkInterval);
                }
                retries++;
            }, 200);
        }
    });
}

// Fallback openAddCourseModal function
function openAddCourseModal() {
    const addCourseModal = document.getElementById('addCourseModal');
    if (addCourseModal) {
        addCourseModal.style.display = 'flex';
        addCourseModal.style.zIndex = '10000';
        document.body.style.overflow = 'hidden';
        
        // Initialize form if needed
        if (typeof initializeCourseForm === 'function') {
            initializeCourseForm();
        }
    } else {
        console.error('Add course modal (addCourseModal) not found in DOM');
        alert('Unable to open the add course form. Please refresh the page and try again.');
    }
}

</script>

<?php 
// Include the Assign Faculty Modal
include_once __DIR__ . '/../modals/assign_faculty_modal.php';
?>
