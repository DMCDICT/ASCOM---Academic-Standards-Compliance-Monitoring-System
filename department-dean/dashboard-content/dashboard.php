<?php
// dashboard.php for Department Dean
// This file is an HTML fragment, included by content.php.
// It will fetch dashboard data from the database and display it.

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get dean information and department
$deanName = 'Dean';
$departmentName = 'College of Computing Studies';
$departmentCode = 'CCS';
$departmentColor = '#C41E3A'; // Default red color

try {
	if (isset($_SESSION['user_id'])) {
		// Get dean's information from session data
		if (isset($_SESSION['user_title']) && isset($_SESSION['user_first_name']) && isset($_SESSION['user_last_name'])) {
			$title = $_SESSION['user_title'] ? $_SESSION['user_title'] . ' ' : '';
			$firstName = $_SESSION['user_first_name'] ?? '';
			$lastName = $_SESSION['user_last_name'] ?? '';
			$deanName = $title . $firstName . ' ' . $lastName;
		}

		// Get department information from selected_role
		if (isset($_SESSION['selected_role']['department_name'])) {
			$departmentName = $_SESSION['selected_role']['department_name'];
		}
		if (isset($_SESSION['selected_role']['department_code'])) {
			$departmentCode = $_SESSION['selected_role']['department_code'];
		}
		if (isset($_SESSION['selected_role']['department_color'])) {
			$departmentColor = $_SESSION['selected_role']['department_color'];
		} else {
			// Fallback: fetch department color from database if not in session
			try {
				if (isset($pdo) && isset($departmentCode)) {
					$query = "SELECT color_code FROM departments WHERE department_code = ? LIMIT 1";
					$stmt = $pdo->prepare($query);
					$stmt->execute([$departmentCode]);
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($row && $row['color_code']) {
						$departmentColor = $row['color_code'];
						// Update session with the color for future use
						if (!isset($_SESSION['selected_role'])) {
							$_SESSION['selected_role'] = [];
						}
						$_SESSION['selected_role']['department_color'] = $departmentColor;
					}
				}
			} catch (Exception $e) {
				// Keep default color if database query fails
			}
		}
	}
} catch (Exception $e) {
	// Keep default values on error
}

// Initialize overview values
$totalPrograms = 0;
$totalCourses = 0;
$totalFaculty = 0;

// Initialize programs array
$programs = [];

// Initialize recent activities
$recentActivities = [];

// Fetch academic terms for dropdown - only current academic year
$academicTerms = [];
$currentAcademicTerm = null;
$selectedTermId = null;

try {
    // First, get the current academic year
    $currentYearQuery = "
        SELECT id, start_date, end_date, school_year_label
        FROM school_years 
        WHERE status = 'Active' 
        ORDER BY start_date DESC 
        LIMIT 1
    ";
    $currentYearStmt = $pdo->prepare($currentYearQuery);
    $currentYearStmt->execute();
    $currentAcademicYear = $currentYearStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($currentAcademicYear) {
        // Get only the 3 terms for the current academic year from the 'terms' table
        $termsQuery = "
            SELECT 
                t.id,
                t.name as term_name,
                t.school_year_id,
                sy.school_year_label,
                t.start_date,
                t.end_date,
                t.is_active as status,
                CONCAT(t.name, ' ', sy.school_year_label) as display_name
            FROM terms t
            INNER JOIN school_years sy ON t.school_year_id = sy.id
            WHERE sy.id = ?
            ORDER BY 
                CASE t.name 
                    WHEN '1st Semester' THEN 1
                    WHEN '2nd Semester' THEN 2
                    WHEN 'Summer Semester' THEN 3
                    ELSE 4
                END
        ";
        $termsStmt = $pdo->prepare($termsQuery);
        $termsStmt->execute([$currentAcademicYear['id']]);
        $academicTerms = $termsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Find current active term
        foreach ($academicTerms as $term) {
            if ($term['status'] == 1 || $term['status'] === '1' || $term['status'] === true) {
                $currentAcademicTerm = $term;
                break;
            }
        }
        
        // If no active term, find the one with today's date in range
        if (!$currentAcademicTerm && !empty($academicTerms)) {
            $today = date('Y-m-d');
            foreach ($academicTerms as $term) {
                if ($term['start_date'] <= $today && $term['end_date'] >= $today) {
                    $currentAcademicTerm = $term;
                    break;
                }
            }
        }
        
        // If still no current term, use the first term (1st Semester)
        if (!$currentAcademicTerm && !empty($academicTerms)) {
            $currentAcademicTerm = $academicTerms[0];
        }
        
        // Set selected term ID (from session or default to current term)
        $selectedTermId = $currentAcademicTerm['id'] ?? null;
    }
} catch (Exception $e) {
}

// --- DATABASE CODE FOR REAL DATA ---
try {
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if ($deanDepartmentCode) {
        // Fetch real programs from database with actual course counts filtered by selected term
        $programsQuery = "
            SELECT p.id, p.program_code, p.program_name, p.major, p.color_code, 
                   COUNT(c.id) as course_count
            FROM programs p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN courses c ON p.id = c.program_id
            WHERE d.department_code = ?
            " . ($selectedTermId ? "AND c.term_id = ?" : "") . "
            GROUP BY p.id, p.program_code, p.program_name, p.major, p.color_code
            ORDER BY p.created_at DESC
        ";
        
        $programsStmt = $pdo->prepare($programsQuery);
        if ($selectedTermId) {
            $programsStmt->execute([$deanDepartmentCode, $selectedTermId]);
        } else {
            $programsStmt->execute([$deanDepartmentCode]);
        }
        $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update overview counts
        $totalPrograms = count($programs);
        
        // Count unique courses for this department (not just sum of program counts)
        if ($selectedTermId === 'all') {
            $uniqueCoursesQuery = "
                SELECT COUNT(DISTINCT c.course_code) as unique_course_count
                FROM courses c
                INNER JOIN programs p ON c.program_id = p.id
                INNER JOIN departments d ON p.department_id = d.id
                WHERE d.department_code = ?
            ";
            $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
            $uniqueCoursesStmt->execute([$deanDepartmentCode]);
            $totalCourses = $uniqueCoursesStmt->fetchColumn();
        } else {
            $uniqueCoursesQuery = "
                SELECT COUNT(DISTINCT c.course_code) as unique_course_count
                FROM courses c
                INNER JOIN programs p ON c.program_id = p.id
                INNER JOIN departments d ON p.department_id = d.id
                WHERE d.department_code = ? AND c.term = ?
            ";
            $uniqueCoursesStmt = $pdo->prepare($uniqueCoursesQuery);
            $uniqueCoursesStmt->execute([$deanDepartmentCode, $termName]);
            $totalCourses = $uniqueCoursesStmt->fetchColumn();
        }
        
        // Fetch total faculty count for this department (not filtered by academic term)
        // Faculty members remain the same across all academic terms
        try {
            $facultyQuery = "
                SELECT COUNT(DISTINCT u.id) AS total_faculty 
                FROM users u 
                JOIN user_roles ur ON u.id = ur.user_id 
                JOIN departments d ON u.department_id = d.id 
                WHERE ur.role_name = 'teacher' 
                AND d.department_code = ? 
                AND ur.is_active = 1 
                AND u.is_active = 1
            ";
            $stmt = $pdo->prepare($facultyQuery);
            $stmt->execute([$deanDepartmentCode]);
            $facultyResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalFaculty = $facultyResult['total_faculty'];
        } catch (Exception $e) {
            $totalFaculty = 0;
        }
        
        // Fetch recent activities (if activity_logs table exists) filtered by selected term
        try {
            $activitiesQuery = "
                SELECT username, description, activity_timestamp
                FROM activity_logs
                WHERE department_id = (SELECT id FROM departments WHERE department_code = ?)
                " . ($selectedTermId ? "AND term_id = ?" : "") . "
                ORDER BY activity_timestamp DESC
                LIMIT 5
            ";
            $activitiesStmt = $pdo->prepare($activitiesQuery);
            if ($selectedTermId) {
                $activitiesStmt->execute([$deanDepartmentCode, $selectedTermId]);
            } else {
                $activitiesStmt->execute([$deanDepartmentCode]);
            }
            $recentActivities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // If activity_logs table doesn't exist, use empty array
            $recentActivities = [];
        }
    }
} catch (Exception $e) {
    // Keep default values if database query fails
}


?>

<style>
/* 
 * Dean Dashboard Modernized Styles
 * Aligned with DESIGN.md tokens
 */

/* 1. Entrance Animations */
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
    0%, 100% { opacity: 1; }
    50% { opacity: 0.55; }
}

@keyframes modalPop {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.985);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* 2. Page Container & Layout Overrides */
.content-wrapper {
    margin-top: 102px !important;
    padding-top: 0 !important;
    padding-bottom: 40px !important;
    transition: all 0.3s ease;
    height: auto !important;
    min-height: unset !important;
    overflow: visible !important;
}

/* Ensure children don't cause overflow */
.content-wrapper > * {
    overflow: visible;
}

/* 3. Personalized Greeting Banner - DESIGN.md premium */
.dashboard-greeting {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    margin-top: 0;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(12, 75, 52, 0.1);
    animation: fadeSlideUp 0.4s ease-out both;
    gap: 16px;
    flex-wrap: wrap;
}

.greeting-text {
    flex: 1;
    min-width: 250px;
}

.greeting-text h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0C4B34;
    margin: 0 0 6px 0;
    font-family: 'TT Interphases', sans-serif;
    line-height: 1.25;
    letter-spacing: -0.3px;
}

.greeting-text p {
    font-size: 14px;
    color: rgba(17, 24, 39, 0.55);
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.2px;
}

.dept-badge-container {
    display: flex;
    align-items: center;
    gap: 12px;
}

.greeting-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

/* Department badge - DESIGN.md section 3.4 pattern */
.dept-badge {
    background: linear-gradient(135deg, #0C4B34 0%, #0F7A53 100%);
    color: #ffffff;
    font-weight: 800;
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 14px;
    letter-spacing: 1px;
    box-shadow: 0 6px 20px rgba(12, 75, 52, 0.25);
    text-transform: uppercase;
}

.dept-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(12, 75, 52, 0.35);
}

html[data-theme="dark"] .greeting-text h2 {
    color: #f0f0f0;
}

/* 4. Overview Section & Stats */
.section-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    margin-top: 32px;
    animation: fadeSlideUp 0.4s ease-out 0.05s both;
    gap: 14px;
}

.label-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.label-left h2 {
    font-size: 18px;
    font-weight: 800;
    color: #0C4B34;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
}

.label-bar {
    width: 4px;
    height: 20px;
    border-radius: 2px;
    background: linear-gradient(180deg, #0C4B34 0%, #0F7A53 100%);
    flex-shrink: 0;
    margin-top: 2px;
}

/* Section Header pattern - DESIGN.md section 3.3 */
/* Section Header - DESIGN.md section 3.3 pattern */
.dashboard-section .section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.dashboard-section .section-header .label-bar {
    width: 4px;
    height: 22px;
    border-radius: 2px;
    background: linear-gradient(180deg, #0C4B34 0%, #0F7A53 100%);
    flex-shrink: 0;
    margin-top: 2px;
}

.dashboard-section .header-left {
    flex: 1;
    min-width: 200px;
}

.dashboard-section .header-left h3 {
    font-size: 18px;
    font-weight: 800;
    color: #0C4B34;
    margin: 0 0 6px 0;
    font-family: 'TT Interphases', sans-serif;
    letter-spacing: -0.3px;
}

.dashboard-section .header-left .section-description {
    font-size: 13px;
    color: rgba(17, 24, 39, 0.5);
    margin: 0;
    font-weight: 600;
    line-height: 1.5;
}

html[data-theme="dark"] .dashboard-section .header-left h3 {
    color: #e0e0e0;
}

html[data-theme="dark"] .dashboard-section .header-left .section-description {
    color: rgba(255,255,255,0.5);
}

.dashboard-section .header-left .section-description {
    font-size: 12px;
    color: rgba(17, 24, 39, 0.5);
    margin: 0;
    font-weight: 600;
}

/* 5. Overview Cards System */
.dashboard-stats-grid { 
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px; 
    padding: 0;
    margin-bottom: 28px;
    height: auto;
}

/* Quick Actions grid */
.quick-actions { 
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin: 8px 0 4px 0;
    height: auto;
}

/* Today's date display */
.greeting-date {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(12, 75, 52, 0.06);
    border: 1px solid rgba(12, 75, 52, 0.12);
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #0C4B34;
}

.greeting-date svg {
    flex-shrink: 0;
}

html[data-theme="dark"] .greeting-date {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
    color: #81C784;
}

.box {
    background-color: #ffffff; 
    padding: 24px;
    border-radius: 18px; 
    border: 1px solid rgba(12, 75, 52, 0.14);
    box-shadow: 0 4px 18px rgba(0,0,0,0.04); 
    display: flex;
    align-items: center;
    gap: 18px;
    position: relative;
    overflow: hidden;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
    animation: fadeSlideUp 0.45s ease-out both;
}

.box.box-link {
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.box.box-link:focus-visible {
    outline: 3px solid rgba(12, 75, 52, 0.28);
    outline-offset: 3px;
}

.box:nth-child(1) { animation-delay: 0.1s; }
.box:nth-child(2) { animation-delay: 0.2s; }
.box:nth-child(3) { animation-delay: 0.3s; }

.box:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
    border-color: rgba(12, 75, 52, 0.25);
}

/* Remove green top border accent from box elements */
.box::before {
    display: none !important;
}

.box {
    padding: 18px;
}

.box-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 22px;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
}

/* Programs card - Primary brand color */
.box:nth-child(1) .box-icon {
    background: rgba(12, 75, 52, 0.08);
    color: #0C4B34;
}

/* Active Courses card - Accent Blue */
.box:nth-child(2) .box-icon {
    background: rgba(21, 101, 192, 0.08);
    color: #1565C0;
}

/* Faculty card - Accent Green */
.box:nth-child(3) .box-icon {
    background: rgba(46, 125, 50, 0.08);
    color: #2E7D32;
}

.box:hover .box-icon {
    transform: scale(1.05);
}

html[data-theme="dark"] .box-icon {
    background: rgba(129, 199, 132, 0.10);
    color: #81C784;
}

html[data-theme="dark"] .box:nth-child(2) .box-icon {
    background: rgba(100, 181, 246, 0.10);
    color: #64B5F6;
}

html[data-theme="dark"] .box:nth-child(3) .box-icon {
    background: rgba(129, 199, 132, 0.10);
    color: #81C784;
}

.box-icon svg,
.qa-icon svg,
.nav-btn svg,
.collapse-btn svg,
.expand-btn svg,
.back-to-top-btn svg,
.lucide-inline-icon svg {
    width: 20px;
    height: 20px;
}

.box-icon svg {
    width: 22px;
    height: 22px;
}

.qa-icon svg {
    width: 18px;
    height: 18px;
}

.nav-btn svg {
    width: 18px;
    height: 18px;
    opacity: 0.7;
}

.course-nav-btn svg {
    width: 18px;
    height: 18px;
    opacity: 0.8;
}

.box-content {
    display: flex;
    flex-direction: column;
}

.box-label {
    font-size: 12px;
    font-weight: 700;
    color: rgba(17, 24, 39, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 4px;
}

.amount {
    font-size: 32px;
    font-weight: 800;
    color: #111827;
    line-height: 1;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
    letter-spacing: -0.5px;
}

.box-content .amount {
    font-size: 32px;
    font-weight: 800;
    color: #111827;
    line-height: 1.1;
    margin: 0;
}

.box-content .amount-sub {
    font-size: 12px;
    font-weight: 600;
    color: rgba(17, 24, 39, 0.4);
    margin-top: 4px;
}

html[data-theme="dark"] .box-content .amount {
    color: #f0f0f0;
}

html[data-theme="dark"] .box-content .amount-sub {
    color: rgba(255,255,255,0.4);
}

/* 6. Dashboard Sections Components - DESIGN.md section 3.1 */
.dashboard-section {
    position: relative;
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(12, 75, 52, 0.14);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    padding: 18px;
    margin-top: 28px;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
}

/* Remove green top border accent from dashboard-section elements */
.dashboard-section::before {
    display: none !important;
}

.dashboard-section {
    padding: 18px;
}

.dashboard-section:hover {
    box-shadow: 0 12px 36px rgba(12, 75, 52, 0.1);
    border-color: rgba(12, 75, 52, 0.25);
}

/* Section Footer */
.dashboard-section .section-footer {
    display: flex;
    justify-content: center;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid rgba(12, 75, 52, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.header-left h3 {
    margin: 0 0 4px 0;
    color: #053423;
    font-size: 20px;
    font-weight: 800;
}

.section-description {
    color: rgba(17, 24, 39, 0.55);
    font-size: 13px;
    font-weight: 600;
}

/* 7. Action Buttons & Controls - DESIGN.md section 4 */
.view-all-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(12, 75, 52, 0.06);
    color: #0C4B34;
    border: 1px solid rgba(12, 75, 52, 0.14);
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.22s cubic-bezier(.4,0,.2,1);
    letter-spacing: 0.1px;
}

.view-all-btn:focus-visible {
    outline: 2px solid rgba(12, 75, 52, 0.45);
    outline-offset: 2px;
}

.view-all-btn:hover {
    background: #0C4B34;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(12, 75, 52, 0.2);
}

.view-all-btn:active {
    transform: translateY(0) scale(0.98);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: auto;
}

/* Icons inside header-actions */
.header-actions i[data-lucide],
.header-actions svg {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

html[data-theme="dark"] .view-all-btn {
    background: rgba(255,255,255,0.05);
    color: #81C784;
    border-color: #333;
}

html[data-theme="dark"] .view-all-btn:hover {
    background: #81C784;
    color: #1a1a1a;
}

.nav-btn {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Lucide icon rendering in navigation buttons */
.nav-btn i[data-lucide],
.nav-btn svg,
.nav-btn .lucide {
    width: 18px;
    height: 18px;
    opacity: 0.7;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Refresh button spinning animation */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Course nav button icons */
.course-nav-btn {
    background: #0C4B34;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(.4,0,.2,1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'TT Interphases', sans-serif;
}

.course-nav-btn:hover {
    background: #0a3a28;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

/* Lucide icon rendering in course nav buttons */
.course-nav-btn i[data-lucide],
.course-nav-btn svg,
.course-nav-btn .lucide {
    width: 16px;
    height: 16px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-btn:hover:not(:disabled) {
    border-color: #0C4B34;
    color: #0C4B34;
    background: rgba(12, 75, 52, 0.04);
}

.nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Term Selector - DESIGN.md section 7 */
.term-selector-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid rgba(12, 75, 52, 0.14);
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
}

.term-label {
    font-size: 12px;
    font-weight: 700;
    color: rgba(12, 75, 52, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.term-dropdown {
    border: none;
    background: transparent;
    color: #0C4B34;
    font-weight: 800;
    font-size: 13px;
    padding: 6px 8px;
    border-radius: 10px;
    outline: none;
    cursor: pointer;
}

.term-dropdown:focus-visible {
    outline: 3px solid rgba(12, 75, 52, 0.22);
    outline-offset: 2px;
}

.current-term-btn {
    background: #0C4B34;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.2px;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(.4,0,.2,1);
}

.current-term-btn:hover:not(:disabled) {
    background: #0a3a28;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(12, 75, 52, 0.16);
}

.current-term-btn:focus-visible {
    outline: 3px solid rgba(12, 75, 52, 0.28);
    outline-offset: 3px;
}

.current-term-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin: 8px 0 4px 0;
    animation: fadeSlideUp 0.45s ease-out 0.12s both;
}

/* Quick Actions - DESIGN.md section 4 pattern */
.quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: #ffffff;
    border: 1px solid rgba(12, 75, 52, 0.14);
    border-radius: 14px;
    padding: 14px 18px;
    color: #0C4B34;
    font-weight: 800;
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 4px 18px rgba(0,0,0,0.03);
    animation: fadeSlideUp 0.45s ease-out both;
}

.quick-action:nth-child(1) { animation-delay: 0.08s; }
.quick-action:nth-child(2) { animation-delay: 0.12s; }
.quick-action:nth-child(3) { animation-delay: 0.16s; }
.quick-action:nth-child(4) { animation-delay: 0.20s; }

.quick-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(12, 75, 52, 0.10);
    border-color: rgba(12, 75, 52, 0.25);
    background: #0C4B34;
    color: #ffffff;
}

.quick-action:active {
    transform: translateY(0) scale(0.98);
}

.quick-action:focus-visible {
    outline: 2px solid rgba(12, 75, 52, 0.45);
    outline-offset: 2px;
}

.quick-action:focus-visible {
    outline: 3px solid rgba(12, 75, 52, 0.28);
    outline-offset: 3px;
}

/* Quick Action Icon - DESIGN.md section 3.2 pattern */
.quick-action .qa-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(12, 75, 52, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0C4B34;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
}

.quick-action:hover .qa-icon {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    transform: scale(1.1);
}

html[data-theme="dark"] .quick-action .qa-icon {
    background: rgba(255,255,255,0.06);
    color: #81C784;
}

html[data-theme="dark"] .quick-action:hover .qa-icon {
    background: rgba(129, 199, 132, 0.2);
}

/* 8. Card Grid Systems */
.reference-requests-container {
    overflow-x: auto;
    overflow-y: visible;
    min-height: unset;
    height: auto;
}

.reference-requests-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    min-height: unset;
    height: auto;
    overflow: visible;
}

/* Reference Request Cards - DESIGN.md section 3.1 pattern */
.reference-request-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(12, 75, 52, 0.12);
    padding: 18px 20px;
    transition: all 0.28s cubic-bezier(.4,0,.2,1);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: visible;
    min-height: unset;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    animation: fadeSlideUp 0.45s ease-out both;
}

.reference-request-card:nth-child(1) { animation-delay: 0.08s; }
.reference-request-card:nth-child(2) { animation-delay: 0.12s; }
.reference-request-card:nth-child(3) { animation-delay: 0.16s; }
.reference-request-card:nth-child(4) { animation-delay: 0.20s; }

.reference-request-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
    border-color: rgba(12, 75, 52, 0.25);
}

/* Remove green top border accent from reference-request-card elements */
.reference-request-card::before {
    display: none !important;
}

.reference-request-card {
    padding: 18px;
}

/* Status-based accent colors */
.reference-request-card[data-status="APPROVED"]::before {
    background: linear-gradient(90deg, #2E7D32 0%, #66BB6A 100%);
}

.reference-request-card[data-status="PENDING"]::before {
    background: linear-gradient(90deg, #F59E0B 0%, #FBBF24 100%);
}

.reference-request-card[data-status="REJECTED"]::before {
    background: linear-gradient(90deg, #b91c1c 0%, #ef4444 100%);
}

.reference-request-card[data-status="DRAFT"]::before {
    background: linear-gradient(90deg, #6b7280 0%, #9ca3af 100%);
}

/* Dark mode */
html[data-theme="dark"] .reference-request-card {
    background: #1e1e1e !important;
    border-color: #333 !important;
    box-shadow: 0 4px 18px rgba(0,0,0,0.25) !important;
}

html[data-theme="dark"] .reference-request-card:hover {
    border-color: #444 !important;
    box-shadow: 0 12px 36px rgba(0,0,0,0.4) !important;
}

/* 9. Specialized Badges & Indicators */
.status-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: inline-flex;
    align-items: center;
    width: fit-content;
}

.status-pending { background: #fff7ed; color: #9a3412; border: 1px solid #ffedd5; }
.status-approved { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
.status-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
.status-draft { background: #f9fafb; color: #374151; border: 1px solid #f3f4f6; }

/* 10. Custom Scrollbar for Grids */
.reference-requests-container::-webkit-scrollbar {
    height: 6px;
}

.reference-requests-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.reference-requests-container::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.reference-requests-container::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Empty States - DESIGN.md section 8 */
.empty-state {
    padding: 40px 24px;
    text-align: center;
    color: rgba(17, 24, 39, 0.4);
    font-weight: 600;
    font-size: 14px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px dashed rgba(12, 75, 52, 0.2);
}

.empty-state svg {
    display: block;
    margin: 0 auto 16px;
    opacity: 0.25;
    color: #0C4B34;
}

.empty-state h4 {
    font-size: 16px;
    font-weight: 800;
    color: #0C4B34;
    margin: 0 0 8px 0;
    font-family: 'TT Interphases', sans-serif;
}

.empty-state p {
    font-size: 13px;
    color: rgba(17, 24, 39, 0.5);
    margin: 0;
    font-weight: 500;
    line-height: 1.5;
}

.empty-dashed {
    padding: 16px;
    border-radius: 12px;
    background: rgba(12, 75, 52, 0.04);
    border: 1px dashed rgba(12, 75, 52, 0.18);
    color: rgba(17, 24, 39, 0.6);
    font-style: normal;
    font-weight: 600;
    font-size: 13px;
}

html[data-theme="dark"] .empty-state { 
    color: rgba(255,255,255,0.4);
    background: #1e1e1e !important;
    border-color: #333;
}

html[data-theme="dark"] .empty-state svg { 
    color: #81C784;
}

html[data-theme="dark"] .empty-dashed { 
    background: rgba(255,255,255,0.04); 
    border-color: rgba(255,255,255,0.15); 
    color: rgba(255,255,255,0.6); 
}

/* 12. Floating Back to Top Modernization */
.back-to-top-btn {
    position: fixed !important;
    bottom: 32px !important;
    right: 32px !important;
    width: 48px !important;
    height: 48px !important;
    background: #0C4B34 !important;
    color: white !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    box-shadow: 0 8px 25px rgba(12, 75, 52, 0.3) !important;
    transition: all 0.3s cubic-bezier(.4,0,.2,1) !important;
    z-index: 999 !important;
    border: none !important;
    padding: 0 !important;
}

.back-to-top-btn:hover {
    transform: translateY(-5px) scale(1.05) !important;
    box-shadow: 0 12px 30px rgba(12, 75, 52, 0.4) !important;
    background: #0a3a28 !important;
}

.back-to-top-btn span {
    display: none !important; /* Hide the legacy text */
}

.back-to-top-btn img {
    width: 22px !important;
    height: 22px !important;
    filter: brightness(0) invert(1) !important;
    transform: rotate(270deg) !important;
}

.back-to-top-btn .arrow {
    width: 22px !important;
    height: 22px !important;
    color: #ffffff !important;
}

/* Dark Mode Integration - DESIGN.md all elements */
html[data-theme="dark"] .dashboard-greeting { 
    flex-wrap: wrap; 
    gap: 12px; 
    border-bottom-color: #333;
}
html[data-theme="dark"] .dashboard-greeting h2 { color: #f0f0f0; }
html[data-theme="dark"] .greeting-text p { color: rgba(255,255,255,0.5); }
html[data-theme="dark"] .greeting-meta { flex-wrap: wrap; gap: 10px; }
html[data-theme="dark"] .label-left h2 { color: #e0e0e0; }
html[data-theme="dark"] .box { 
    background: #1e1e1e !important; 
    border-color: #333 !important; 
    box-shadow: 0 4px 18px rgba(0,0,0,0.25) !important; 
}
html[data-theme="dark"] .box:hover { 
    border-color: #444 !important; 
    box-shadow: 0 12px 36px rgba(0,0,0,0.4) !important; 
}
html[data-theme="dark"] .amount { color: #f0f0f0; }
html[data-theme="dark"] .box-label { color: rgba(255,255,255,0.5); }
html[data-theme="dark"] .box-icon { color: #81C784; background: rgba(129, 199, 132, 0.10); }
html[data-theme="dark"] .dashboard-section { 
    background: #1e1e1e !important; 
    border-color: #333 !important; 
    box-shadow: 0 4px 18px rgba(0,0,0,0.25) !important;
}
html[data-theme="dark"] .dashboard-section:hover {
    box-shadow: 0 12px 36px rgba(0,0,0,0.35) !important;
    border-color: #444 !important;
}
html[data-theme="dark"] .section-header { border-bottom-color: #333; }
html[data-theme="dark"] .header-left h3 { color: #f0f0f0; }
html[data-theme="dark"] .section-description { color: rgba(255,255,255,0.5); }
html[data-theme="dark"] .reference-request-card { 
    background: #1a1a1a !important; 
    border-color: #333 !important;
    box-shadow: 0 4px 18px rgba(0,0,0,0.2) !important;
}
html[data-theme="dark"] .reference-request-card:hover { border-color: #444 !important; }
html[data-theme="dark"] .view-all-btn { 
    background: rgba(255,255,255,0.05); 
    color: #81C784; 
    border-color: #333; 
}
html[data-theme="dark"] .view-all-btn:hover { 
    background: #81C784; 
    color: #1a1a1a; 
}
html[data-theme="dark"] .term-selector-wrapper { 
    background: #1e1e1e; 
    border-color: #333; 
    box-shadow: 0 4px 18px rgba(0,0,0,0.2);
}
html[data-theme="dark"] .term-label { color: rgba(255,255,255,0.6); }
html[data-theme="dark"] .term-dropdown { 
    color: #81C784; 
    background: transparent; 
}
html[data-theme="dark"] .current-term-btn { 
    background: #81C784; 
    color: #1a1a1a; 
}
html[data-theme="dark"] .current-term-btn:hover { 
    background: #66BB6A; 
}
html[data-theme="dark"] .quick-action { 
    background: #1e1e1e !important; 
    border-color: #333 !important; 
    color: #81C784; 
    box-shadow: none; 
}
html[data-theme="dark"] .quick-action:hover { 
    border-color: #444 !important; 
    box-shadow: 0 12px 28px rgba(0,0,0,0.3);
    background: #81C784 !important;
    color: #1a1a1a;
}
html[data-theme="dark"] .quick-action .qa-icon { 
    background: rgba(255,255,255,0.06); 
    color: #81C784;
}
html[data-theme="dark"] .qa-icon { color: #81C784; }
html[data-theme="dark"] .nav-btn { 
    border-color: #333; 
    background: rgba(255,255,255,0.02); 
    color: #b0b0b0; 
}
html[data-theme="dark"] .nav-btn:hover:not(:disabled) { 
    border-color: #81C784; 
    color: #81C784; 
    background: rgba(255,255,255,0.04); 
}
html[data-theme="dark"] .dept-badge { 
    background: linear-gradient(135deg, #81C784 0%, #66BB6A 100%) !important; 
    color: #1a1a1a; 
}
html[data-theme="dark"] .status-badge { 
    background: rgba(255,255,255,0.08) !important; 
    border-color: #333 !important; 
}
html[data-theme="dark"] .section-footer { 
    border-top-color: #333; 
}
html[data-theme="dark"] .collapse-btn { 
    background: rgba(255,255,255,0.05); 
    border-color: #333; 
    color: #b0b0b0; 
}
html[data-theme="dark"] .collapse-btn:hover { 
    background: rgba(255,255,255,0.08); 
    color: #81C784; 
}
html[data-theme="dark"] .term-dropdown { 
    color: #e0e0e0;
}
html[data-theme="dark"] .term-dropdown option {
    background: #1e1e1e;
    color: #e0e0e0;
}
html[data-theme="dark"] .request-count-badge {
    background: #ef4444;
}

/* Collapse/Expand button */
.collapse-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #0C4B34;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.2px;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(.4,0,.2,1);
    font-family: 'TT Interphases', sans-serif;
}

.collapse-btn:hover {
    background: #0a3a28;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

.collapse-btn:active {
    transform: translateY(0) scale(0.98);
}

/* Lucide icon in collapse button */
.collapse-btn i[data-lucide],
.collapse-btn svg,
.collapse-btn .collapse-icon {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.22s cubic-bezier(.4,0,.2,1);
}

html[data-theme="dark"] .collapse-btn {
    background: rgba(255,255,255,0.05);
    border-color: #333;
    color: #81C784;
}

html[data-theme="dark"] .collapse-btn:hover {
    background: #81C784;
    color: #1a1a1a;
}

/* Collapsed controls container - DESIGN.md section header pattern */
.collapsed-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: auto;
    padding: 8px 0;
}

/* Collapsed state badge - DESIGN.md stat pill style */
.request-count-badge {
    background: #b91c1c;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 6px 10px;
    border-radius: 8px;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
}

/* Expand button for collapsed state - DESIGN.md button style */
.expand-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #0C4B34;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.2px;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(.4,0,.2,1);
    font-family: 'TT Interphases', sans-serif;
}

.expand-btn:hover {
    background: #0a3a28;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

.expand-btn:active {
    transform: translateY(0) scale(0.98);
}

/* Responsive - DESIGN.md breakpoints */
@media (max-width: 1100px) {
    .dashboard-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .reference-requests-grid { grid-template-columns: repeat(2, 1fr); }
    .quick-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 768px) {
    .dashboard-greeting { flex-direction: column; align-items: flex-start; gap: 12px; }
    .greeting-meta { flex-wrap: wrap; gap: 10px; }
    .section-label { flex-direction: column; align-items: flex-start; }
    .term-selector-wrapper { width: 100%; justify-content: space-between; flex-wrap: wrap; }
    .dashboard-stats-grid { grid-template-columns: 1fr; }
    .reference-requests-grid { grid-template-columns: 1fr; }
    .header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-start; }
    .dashboard-section .section-header { flex-direction: column; gap: 12px; }
    .dashboard-section .header-actions { width: 100%; justify-content: flex-start; }
}

@media (max-width: 640px) {
    .quick-actions { grid-template-columns: 1fr; }
    .section-label { flex-direction: column; align-items: flex-start; }
    .term-selector-wrapper { flex-direction: column; align-items: flex-start; }
    .box { padding: 18px; }
    .box-icon { width: 40px; height: 40px; }
    .amount { font-size: 24px; }
}

</style>

<div class="dashboard-greeting">
    <div class="greeting-text">
        <h2>Welcome back, <?php echo htmlspecialchars($deanName); ?></h2>
        <p>Department Dean • <?php echo htmlspecialchars($departmentName); ?></p>
    </div>
    <div class="greeting-meta">
        <span class="dept-badge" style="background: <?php echo htmlspecialchars($departmentColor); ?>;"><?php echo htmlspecialchars($departmentCode); ?></span>
        <div class="greeting-date">
            <i data-lucide="calendar"></i>
            <span><?php echo date('F j, Y'); ?></span>
        </div>
    </div>
</div>

<!-- Overview Header with Academic Term Selector -->
<div class="section-label">
    <div class="label-left">
        <div class="label-bar"></div>
        <h2>Dashboard Overview</h2>
    </div>
    
    <!-- Academic Term Selector Premium -->
    <div class="term-selector-wrapper">
        <span class="term-label">Term</span>
        <select id="academicTermSelect" class="term-dropdown" onchange="handleTermChangeFromDropdown()">
            <option value="">Select a term...</option>
            <option value="all" <?php echo ($selectedTermId === 'all') ? 'selected' : ''; ?>>All Terms (<?php echo htmlspecialchars($currentAcademicYear['school_year_label'] ?? 'Current Year'); ?>)</option>
            <?php foreach ($academicTerms as $term): ?>
                <option value="<?php echo htmlspecialchars($term['id']); ?>" 
                        data-term-name="<?php echo htmlspecialchars($term['term_name']); ?>"
                        data-school-year="<?php echo htmlspecialchars($term['school_year_label']); ?>"
                        data-display="<?php echo htmlspecialchars($term['display_name']); ?>"
                        <?php echo ($selectedTermId && $term['id'] == $selectedTermId) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($term['display_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button id="currentTermBtn" class="current-term-btn" onclick="selectCurrentTerm()">
            Current Term
        </button>
    </div>
</div>

<div class="dashboard-stats-grid">
  <button type="button" class="box box-link" onclick="window.location.href='content.php?page=academic-management'" aria-label="View total programs">
    <div class="box-icon"><i data-lucide="folder-kanban"></i></div>
    <div class="box-content">
        <span class="box-label">Total Programs</span>
        <span class="amount"><?php echo $totalPrograms; ?></span>
        <span class="amount-sub">Active programs</span>
    </div>
  </button>
  <button type="button" class="box box-link" onclick="navigateToAllCourses()" aria-label="View active courses">
    <div class="box-icon"><i data-lucide="book-open"></i></div>
    <div class="box-content">
        <span class="box-label">Active Courses</span>
        <span class="amount"><?php echo $totalCourses; ?></span>
        <span class="amount-sub">This term</span>
    </div>
  </button>
  <button type="button" class="box box-link" onclick="window.location.href='content.php?page=faculty-management'" aria-label="View faculty members">
    <div class="box-icon"><i data-lucide="users"></i></div>
    <div class="box-content">
        <span class="box-label">Faculty Members</span>
        <span class="amount"><?php echo $totalFaculty; ?></span>
        <span class="amount-sub">Enrolled teachers</span>
    </div>
  </button>
</div>

<div class="quick-actions" aria-label="Quick actions">
    <button type="button" class="quick-action" onclick="openAddProgramModal()">
        <span class="qa-icon"><i data-lucide="plus"></i></span>
        Add Program
    </button>
    <button type="button" class="quick-action" onclick="openCourseSelectionModal()">
        <span class="qa-icon"><i data-lucide="file-plus-2"></i></span>
        Add Course
    </button>
    <a class="quick-action" href="content.php?page=reference-requests">
        <span class="qa-icon"><i data-lucide="inbox"></i></span>
        Material Requests
    </a>
    <a class="quick-action" href="content.php?page=school-calendar">
        <span class="qa-icon"><i data-lucide="calendar"></i></span>
        School Calendar
    </a>
</div>

<!-- Review Course Material Requests Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="label-bar"></div>
        <div class="header-left">
            <h3>Review Course Material Requests</h3>
            <div class="section-description">Review and manage all course material requests from faculty.</div>
        </div>
        <div class="header-actions">
            <a href="content.php?page=reference-requests" class="view-all-btn">View All</a>
        </div>
    </div>
                            
    <div class="reference-requests-container">
                            <div class="reference-requests-grid" id="referenceRequestsGrid">
                                <!-- Cards will be dynamically generated by JavaScript -->
                            </div>
                        
                        <!-- Hidden data for JavaScript -->
                        <div id="allRequestsData" style="display: none;">
                            <?php
            // Define the reference requests data first
                            $referenceRequests = [
                                [
                                    'id' => 1,
                    'requester_name' => 'Dr. James Wilson',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Computer Networks: A Top-Down Approach',
                    'author_first' => 'James',
                    'author_last' => 'Kurose',
                    'publication_year' => '2021',
                    'edition' => '8th',
                    'publisher' => 'Pearson',
                    'isbn' => '978-0135928615',
                    'course_code' => 'CS 301',
                    'course_name' => 'Computer Networks',
                    'status' => 'APPROVED',
                    'priority' => 'MEDIUM',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Required textbook for Computer Networks course'
                                ],
                                [
                                    'id' => 2,
                    'requester_name' => 'Prof. Sarah Johnson',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'IEEE Transactions on Software Engineering',
                    'author_first' => 'Various',
                    'author_last' => 'Authors',
                    'publication_year' => '2024',
                    'edition' => 'Current',
                    'publisher' => 'IEEE',
                    'isbn' => 'N/A',
                    'course_code' => 'CS 401',
                    'course_name' => 'Software Engineering',
                    'status' => 'PENDING',
                    'priority' => 'HIGH',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Research material for advanced software engineering topics'
                                ],
                                [
                                    'id' => 3,
                    'requester_name' => 'Santos',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Database Management Systems',
                    'author_first' => 'Ramez',
                    'author_last' => 'Elmasri',
                    'publication_year' => '2023',
                    'edition' => '7th',
                    'publisher' => 'Pearson',
                    'isbn' => '978-0133970777',
                    'course_code' => 'CS 201',
                    'course_name' => 'Database Systems',
                    'status' => 'PENDING',
                    'priority' => 'HIGH',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'KTBOOK REQUEST for Database Management course'
                                ],
                                [
                                    'id' => 4,
                    'requester_name' => 'Dr. Michael Chen',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Artificial Intelligence: A Modern Approach',
                    'author_first' => 'Stuart',
                    'author_last' => 'Russell',
                    'publication_year' => '2022',
                    'edition' => '4th',
                    'publisher' => 'Pearson',
                    'isbn' => '978-0134610993',
                    'course_code' => 'CS 501',
                    'course_name' => 'Artificial Intelligence',
                    'status' => 'PENDING',
                    'priority' => 'MEDIUM',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Core textbook for AI course'
                                ],
                                [
                                    'id' => 5,
                    'requester_name' => 'Prof. Emily Rodriguez',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Data Structures and Algorithms',
                    'author_first' => 'Thomas',
                    'author_last' => 'Cormen',
                    'publication_year' => '2021',
                    'edition' => '4th',
                    'publisher' => 'MIT Press',
                    'isbn' => '978-0262033848',
                    'course_code' => 'CS 202',
                    'course_name' => 'Data Structures',
                    'status' => 'APPROVED',
                    'priority' => 'LOW',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Reference material for algorithms course'
                                ],
                                [
                                    'id' => 6,
                    'requester_name' => 'Dr. Robert Kim',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Operating System Concepts',
                    'author_first' => 'Abraham',
                    'author_last' => 'Silberschatz',
                    'publication_year' => '2023',
                    'edition' => '10th',
                    'publisher' => 'Wiley',
                    'isbn' => '978-1118063330',
                    'course_code' => 'CS 302',
                    'course_name' => 'Operating Systems',
                    'status' => 'PENDING',
                    'priority' => 'HIGH',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Required textbook for OS course'
                                ],
                                [
                                    'id' => 7,
                    'requester_name' => 'Prof. Lisa Thompson',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Computer Organization and Design',
                    'author_first' => 'David',
                    'author_last' => 'Patterson',
                    'publication_year' => '2022',
                    'edition' => '6th',
                    'publisher' => 'Morgan Kaufmann',
                    'isbn' => '978-0128201091',
                    'course_code' => 'CS 303',
                    'course_name' => 'Computer Architecture',
                    'status' => 'REJECTED',
                    'priority' => 'MEDIUM',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Hardware architecture reference'
                                ],
                                [
                                    'id' => 8,
                    'requester_name' => 'Dr. David Park',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Software Engineering: Principles and Practice',
                    'author_first' => 'Hans',
                    'author_last' => 'van Vliet',
                    'publication_year' => '2024',
                    'edition' => '4th',
                    'publisher' => 'Wiley',
                    'isbn' => '978-1118967624',
                    'course_code' => 'CS 402',
                    'course_name' => 'Advanced Software Engineering',
                    'status' => 'PENDING',
                    'priority' => 'LOW',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Advanced software development practices'
                                ],
                                [
                                    'id' => 9,
                    'requester_name' => 'Prof. Amanda White',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Machine Learning: A Probabilistic Perspective',
                    'author_first' => 'Kevin',
                    'author_last' => 'Murphy',
                    'publication_year' => '2023',
                    'edition' => '2nd',
                    'publisher' => 'MIT Press',
                    'isbn' => '978-0262018029',
                    'course_code' => 'CS 502',
                    'course_name' => 'Machine Learning',
                    'status' => 'PENDING',
                    'priority' => 'HIGH',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Core ML textbook for graduate course'
                                ],
                                [
                                    'id' => 10,
                    'requester_name' => 'Dr. Carlos Martinez',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Computer Graphics: Principles and Practice',
                    'author_first' => 'John',
                    'author_last' => 'Hughes',
                    'publication_year' => '2022',
                    'edition' => '4th',
                    'publisher' => 'Addison-Wesley',
                    'isbn' => '978-0321399526',
                    'course_code' => 'CS 304',
                    'course_name' => 'Computer Graphics',
                    'status' => 'APPROVED',
                    'priority' => 'MEDIUM',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Graphics programming reference'
                                ],
                                [
                                    'id' => 11,
                    'requester_name' => 'Prof. Jennifer Lee',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Network Security: Private Communication in a Public World',
                    'author_first' => 'Charlie',
                    'author_last' => 'Kaufman',
                    'publication_year' => '2024',
                    'edition' => '3rd',
                    'publisher' => 'Addison-Wesley',
                    'isbn' => '978-0321513075',
                    'course_code' => 'CS 305',
                    'course_name' => 'Network Security',
                    'status' => 'PENDING',
                    'priority' => 'HIGH',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Cybersecurity course material'
                ],
                [
                    'id' => 12,
                    'requester_name' => 'Dr. Thomas Anderson',
                    'requester_role' => 'FACULTY',
                    'book_title' => 'Distributed Systems: Concepts and Design',
                    'author_first' => 'George',
                    'author_last' => 'Coulouris',
                    'publication_year' => '2023',
                    'edition' => '6th',
                    'publisher' => 'Pearson',
                    'isbn' => '978-0132143011',
                    'course_code' => 'CS 503',
                    'course_name' => 'Distributed Systems',
                    'status' => 'PENDING',
                    'priority' => 'MEDIUM',
                    'requested_by' => 'Librarian Sarah',
                    'justification' => 'Advanced distributed computing concepts'
                ]
            ];
            
            // Filter only pending requests that need approval/rejection
            $pendingRequests = array_filter($referenceRequests, function($request) {
                return $request['status'] === 'PENDING';
            });
            
            $pendingRequests = array_values($pendingRequests); // Re-index array
            
            echo json_encode($pendingRequests);
                            ?>
                        </div>
    </div>
    
    <div class="section-footer">
        <button class="collapse-btn course-nav-btn" onclick="toggleSection()">
            <span>Collapse</span>
            <i data-lucide="chevron-up" class="collapse-icon" aria-hidden="true"></i>
        </button>
    </div>
</div>

<!-- Course Proposals & Revisions Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="label-bar"></div>
        <div class="header-left">
            <h3>Course Proposals & Revisions</h3>
            <div class="section-description">Review and manage new course proposals and course revision requests from faculty.</div>
        </div>
        <div class="header-actions">
            <a href="content.php?page=all-courses" class="view-all-btn">View All Proposals</a>
        </div>
    </div>
    
    <div class="reference-requests-container" style="padding: 10px; margin: 10px 0; position: relative;">
        <div class="reference-requests-grid" id="courseProposalsGrid" style="min-height: 230px; flex-wrap: wrap; justify-content: flex-start; gap: 20px; align-items: flex-start;">
            <!-- Course proposal cards will be dynamically generated by JavaScript -->
        </div>
        
        <!-- Empty state -->
        <div id="courseProposalsEmptyState" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; padding: 0; color: #666; width: 100%;">
            <div style="margin-bottom: 8px; display: flex; justify-content: center;">
                <i data-lucide="file-text" style="width: 28px; height: 28px; color: #0C4B34;"></i>
            </div>
            <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 15px; color: #333; margin-bottom: 6px;">No Course Proposals</h3>
            <p style="font-family: 'TT Interphases', sans-serif; font-size: 13px; color: #666; margin: 0;">
                View and track the Department Dean's submitted new courses and course revisions for QA review.
            </p>
        </div>
    </div>
</div>

<!-- Program & Courses Management Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="label-bar"></div>
        <div class="header-left">
            <h3>Program & Courses Management</h3>
            <div class="section-description">Manage academic programs and their configurations</div>
        </div>
        <button class="view-all-btn" id="addProgramButton" style="background: #0C4B34; color: white; border: none;">Add Program</button>
    </div>

    <div class="reference-requests-grid" id="programContainer" style="margin-top: 10px;">
        <!-- All Courses Card -->
        <div class="reference-request-card">
            <div class="status-badge" style="background: #6c757d20; color: #6c757d; border: 1px solid #6c757d40;">ALL</div>
            <div style="margin-top: 12px; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 16px; color: #111827;">All Courses</h3>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280; font-weight: 600;">View complete catalog</p>
            </div>
            <button class="view-all-btn" style="width: 100%; border-radius: 8px;" onclick="window.location.href='content.php?page=all-courses'">View Details</button>
        </div>
        
        <?php
        if (!empty($programs)) {
            $index = 0;
            foreach ($programs as $program) {
                // Show 4 per row (including "All Courses" as the 1st one, so 3 more)
                $isHidden = ($index >= 3); 
                echo "<div class='reference-request-card " . ($isHidden ? "hidden" : "") . "' style='display: " . ($isHidden ? "none" : "flex") . "'>";
                echo "<div class='status-badge' style='background: " . htmlspecialchars($program['color_code']) . "20; color: " . htmlspecialchars($program['color_code']) . "; border: 1px solid " . htmlspecialchars($program['color_code']) . "40;'>" . htmlspecialchars($program['program_code']) . "</div>";
                echo "<div style='margin-top: 12px; margin-bottom: 20px;'>";
                echo "<h3 style='margin: 0; font-size: 16px; color: #111827;'>" . htmlspecialchars($program['program_name']) . "</h3>";
                echo "<p style='margin: 4px 0 0 0; font-size: 13px; color: #6b7280; font-weight: 600;'>" . htmlspecialchars($program['course_count']) . " Courses</p>";
                echo "</div>";
                echo "<button class='view-all-btn' style='width: 100%; border-radius: 8px;' onclick=\"window.location.href='content.php?page=program-courses&program=" . urlencode($program['program_code']) . "'\">View Courses</button>";
                echo "</div>";
                $index++;
            }
        }
        ?>
    </div>

    <div class="program-buttons-container">
        <button class="view-all-btn" id="viewAllProgramsButton" 
                style="display: <?php echo (count($programs) > 3) ? 'inline-flex' : 'none'; ?>;">Expand to View More</button>
        <button class="view-all-btn" id="collapseProgramsButton" 
                style="display: none;">Collapse to View Less</button>
    </div>
</div>




<script>
    // This variable will be accessed by scripts/program-management.js
    const programs = <?php 
        try { echo json_encode($programs ?? []); } 
        catch (Exception $e) { echo '[]'; }
    ?>;
    const recentActivities = <?php 
        try { echo json_encode($recentActivities ?? []); } 
        catch (Exception $e) { echo '[]'; }
    ?>;
    
    // Set hasPrograms variable for the global checkProgramsAndOpenCourseModal function
    // Always allow course creation regardless of filtered program count
    const hasPrograms = true;
    
    // Academic terms data
    const academicTerms = <?php 
        try { echo json_encode($academicTerms ?? []); } 
        catch (Exception $e) { echo '[]'; }
    ?>;
    const currentAcademicTerm = <?php 
        try { echo json_encode($currentAcademicTerm ?? null); } 
        catch (Exception $e) { echo 'null'; }
    ?>;
    
    // Selected term ID (stored in session storage for persistence)
    // Default to current term if no session storage exists
    let selectedTermId = sessionStorage.getItem('selectedTermId') || (currentAcademicTerm ? String(currentAcademicTerm.id) : null);
    
    // Handle term selection change - with loop prevention
    async function handleTermChange(termId) {
        // Prevent multiple concurrent term changes
        if (window.__termChangeInProgress) {
            return;
        }
        window.__termChangeInProgress = true;
        
        try {
            if (!termId) {
                sessionStorage.removeItem('selectedTermId');
                selectedTermId = null;
                updateCurrentTermButtonState();
                return;
            }

            selectedTermId = termId;
            sessionStorage.setItem('selectedTermId', termId);

            // Update server-side session and wait for completion
            const sessionUpdated = await updateServerSession(termId);
            if (!sessionUpdated) {
                console.error('Failed to update server session');
                return;
            }

            // Handle "All Terms" option
            if (termId === 'all') {
                showTermChangeNotification('All Terms (Current Academic Year)');
                refreshDashboardData(termId);
            } else {
                const selectedTerm = academicTerms.find(t => t.id == termId);
                if (selectedTerm) {
                    showTermChangeNotification(selectedTerm.display_name);
                    refreshDashboardData(termId);
                } else {
                    console.error('Term not found:', termId);
                }
            }

            updateSelectedTermDisplay();
            updateCurrentTermButtonState();
        } finally {
            window.__termChangeInProgress = false;
        }
    }
    
    // New function to handle term change from dropdown
    async function handleTermChangeFromDropdown() {
        const termSelect = document.getElementById('academicTermSelect');
        const termId = termSelect.value;
        
        
        if (termId) {
            if (termId === 'all') {
            } else {
            }
            await handleTermChange(termId);
        } else {
            await handleTermChange(null);
        }
    }
    
    // Function to update server-side session (synchronous)
    async function updateServerSession(termId) {
        
        try {
            const response = await fetch('update_selected_term.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'term_id=' + encodeURIComponent(termId)
            });
            
            const data = await response.json();
            
            if (data.success) {
                return true;
            } else {
                console.error('Error updating server session:', data.message);
                return false;
            }
        } catch (error) {
            console.error('Error updating server session:', error);
            return false;
        }
    }
    
    // Function to refresh dashboard data based on selected term
    function refreshDashboardData(termId, isFromServer = false) {
        
        // If already loading, skip
        if (window.__dashboardRefreshing) {
            return;
        }
        window.__dashboardRefreshing = true;
        
        // Show loading indicator
        showLoadingIndicator();
        
        // Make AJAX request to get filtered data
        fetch('get_dashboard_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'term_id=' + encodeURIComponent(termId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update dashboard statistics
                updateDashboardStats(data.stats);
                
                // Update Program & Courses Management section
                if (data.programs) {
                    updateProgramsSection(data.programs);
                }
                
                // Update course material requests
                updateCourseMaterialRequests(data.requests);
                
                // Update selected term display
                updateSelectedTermDisplay();
            } else {
                console.error('Error refreshing dashboard data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard data:', error);
        })
        .finally(() => {
            hideLoadingIndicator();
            window.__dashboardRefreshing = false;
        });
    }
    
    // Function to show loading indicator
    function showLoadingIndicator() {
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (dashboardContainer) {
            dashboardContainer.style.opacity = '0.5';
            dashboardContainer.style.pointerEvents = 'none';
        }
        
        // Add loading text to the term selector
        const termSelect = document.getElementById('academicTermSelect');
        if (termSelect) {
            termSelect.style.background = 'linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%)';
            termSelect.style.backgroundSize = '200% 100%';
            termSelect.style.animation = 'loading 1.5s infinite';
        }
    }
    
    // Function to hide loading indicator
    function hideLoadingIndicator() {
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (dashboardContainer) {
            dashboardContainer.style.opacity = '1';
            dashboardContainer.style.pointerEvents = 'auto';
        }
        
        // Remove loading animation from term selector
        const termSelect = document.getElementById('academicTermSelect');
        if (termSelect) {
            termSelect.style.background = '';
            termSelect.style.backgroundSize = '';
            termSelect.style.animation = '';
        }
    }
    
    // Function to update dashboard statistics
    function updateDashboardStats(stats) {
        
        // Update programs count
        const programsElement = document.querySelector('.stat-box:nth-child(1) .stat-amount');
        if (programsElement) {
            programsElement.textContent = stats.totalPrograms || 0;
        } else {
            console.error('Programs element not found!');
        }
        
        // Update courses count
        const coursesElement = document.querySelector('.stat-box:nth-child(2) .stat-amount');
        if (coursesElement) {
            coursesElement.textContent = stats.totalCourses || 0;
        } else {
            console.error('Courses element not found!');
        }
        
        // Update faculty count
        const facultyElement = document.querySelector('.stat-box:nth-child(3) .stat-amount');
        if (facultyElement) {
            facultyElement.textContent = stats.totalFaculty || 0;
        } else {
            console.error('Faculty element not found!');
        }
    }
    
    // Function to update Program & Courses Management section
    function updateProgramsSection(programs) {
        
        const programContainer = document.getElementById('programContainer');
        if (!programContainer) {
            console.error('Program container not found!');
            return;
        }
        
        // Clear existing content
        programContainer.innerHTML = '';
        
        // Add "All Courses" card
        const allCoursesCard = document.createElement('div');
        allCoursesCard.className = 'department-card all-courses-card';
        allCoursesCard.innerHTML = `
            <div class='dept-code' style='background-color: #6c757d;'>ALL</div>
            <h3>All Courses</h3>
            <p style='font-weight: bold; color: #333;'>View All Courses</p>
            <button class='view-details-btn' onclick="navigateToAllCourses()">View Details</button>
        `;
        programContainer.appendChild(allCoursesCard);
        
        // Add program cards
        if (programs && programs.length > 0) {
            programs.forEach((program, index) => {
                const programCard = document.createElement('div');
                programCard.className = `department-card ${index >= 5 ? 'hidden' : ''}`;
                // Removed click functionality - program cards are no longer clickable
                
                programCard.innerHTML = `
                    <div class='dept-code' style='background-color: ${program.color_code}'>${program.program_code}</div>
                    <h3>${program.program_name}</h3>
                    ${program.major ? `<p style='margin: 4px 0; font-size: 12px; color: #666;'>Major in: <strong>${program.major}</strong></p>` : ''}
                    <p style='font-weight: bold; color: #333;'>${program.course_count} Courses</p>
                    <button class='view-details-btn' onclick="navigateToProgramCourses('${program.program_code}')">View Courses</button>
                `;
                
                programContainer.appendChild(programCard);
            });
            
            // Show/hide "Expand to View More" button based on number of programs
            const viewAllButton = document.getElementById('viewAllProgramsButton');
            const collapseButton = document.getElementById('collapseProgramsButton');
            const viewAllContainer = document.querySelector('.program-buttons-container');
            if (viewAllButton && collapseButton && viewAllContainer) {
                if (programs.length > 5) {
                    viewAllButton.style.display = 'inline-flex';
                    collapseButton.style.display = 'none';
                    viewAllContainer.style.display = 'flex';
                } else {
                    viewAllButton.style.display = 'none';
                    collapseButton.style.display = 'none';
                    viewAllContainer.style.display = 'none';
                }
            } else if (viewAllButton) {
                viewAllButton.style.display = programs.length > 5 ? 'inline-flex' : 'none';
                if (viewAllContainer) {
                    viewAllContainer.style.display = programs.length > 5 ? 'flex' : 'none';
                }
            }
        } else {
            // No programs found - show empty state card
            const emptyCard = document.createElement('div');
            emptyCard.className = 'department-card empty-program-card';
            emptyCard.innerHTML = `
                <div style='display: flex; justify-content: space-between; align-items: center;'>
                    <div class='dept-code' style='background-color: #1976d2; color: white; font-weight: bold;'>NEW</div>
                    <span class="lucide-inline-icon"><i data-lucide="folder-plus"></i></span>
                </div>
                <h3>No Programs Yet</h3>
                <p style='font-weight: bold; color: #333;'>Start building your programs</p>
                <button class='view-details-btn' onclick='openAddProgramModal()'>Create First Program</button>
            `;
            programContainer.appendChild(emptyCard);
            if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
        }
        
    }
    
    // Function to update course material requests
    function updateCourseMaterialRequests(requests) {
        const requestsGrid = document.getElementById('referenceRequestsGrid');
        if (requestsGrid && requests) {
            // Clear existing content
            requestsGrid.innerHTML = '';
            
            // Add new requests
            requests.forEach(request => {
                const requestCard = createRequestCard(request);
                requestsGrid.appendChild(requestCard);
            });
        }
    }
    
    // Select current term
    function selectCurrentTerm() {
        if (!currentAcademicTerm) {
            alert('No current term is active in the system.');
            return;
        }
        
        const termSelect = document.getElementById('academicTermSelect');
        if (termSelect) {
            termSelect.value = currentAcademicTerm.id;
            handleTermChange(currentAcademicTerm.id);
        }
    }
    
    // Update the selected term display
    function updateSelectedTermDisplay() {
        if (selectedTermId) {
            if (selectedTermId === 'all') {
            } else {
                const selectedTerm = academicTerms.find(t => t.id == selectedTermId);
                if (selectedTerm) {
                }
            }
        }
    }
    
    // Show term change notification
    function showTermChangeNotification(termName) {
        // Clean up any existing notifications first
        const existingNotifications = document.querySelectorAll('.term-change-notification');
        existingNotifications.forEach(notif => {
            if (notif.parentNode) {
                notif.remove();
            }
        });
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'term-change-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
            z-index: 10000;
            font-family: 'TT Interphases', sans-serif;
            font-size: 14px;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            will-change: transform, opacity;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="lucide-inline-icon"><i data-lucide="calendar-days"></i></span>
                <div>
                    <div style="font-weight: 700; margin-bottom: 4px;">Term Selected</div>
                    <div style="font-weight: 500; opacity: 0.9;">${termName}</div>
                </div>
            </div>
        `;
        if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
        
        // Add animation keyframes
        if (!document.getElementById('notificationStyles')) {
            const style = document.createElement('style');
            style.id = 'notificationStyles';
            style.textContent = `
                @keyframes slideIn {
                    0% {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    100% {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOut {
                    0% {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    100% {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds with improved animation handling
        setTimeout(() => {
            // Add animation end event listener to ensure proper cleanup
            const handleAnimationEnd = () => {
                if (notification.parentNode) {
                    notification.remove();
                }
            };
            
            // Listen for animation end event
            notification.addEventListener('animationend', handleAnimationEnd, { once: true });
            
            // Apply slide out animation
            notification.style.animation = 'slideOut 0.3s ease forwards';
            
            // Fallback: remove after animation duration + buffer
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 400); // 300ms animation + 100ms buffer
        }, 3000);
    }
    
    // Get selected term ID (helper function for other scripts)
    function getSelectedTermId() {
        return selectedTermId;
    }
    
    // Get selected term object (helper function for other scripts)
    function getSelectedTerm() {
        if (!selectedTermId) return null;
        if (selectedTermId === 'all') {
            return {
                id: 'all',
                term_name: 'All Terms',
                display_name: 'All Terms (Current Academic Year)'
            };
        }
        return academicTerms.find(t => t.id == selectedTermId);
    }
    
    // Update Current Term button state (enabled/disabled)
    function updateCurrentTermButtonState() {
        const currentTermBtn = document.getElementById('currentTermBtn');
        if (!currentTermBtn) return;
        
        // Disable if current term is selected or if there's no current term
        if (!currentAcademicTerm) {
            currentTermBtn.disabled = true;
            currentTermBtn.style.cursor = 'not-allowed';
            currentTermBtn.title = 'No current term available';
        } else if (selectedTermId === 'all') {
            // Enable when "All Terms" is selected
            currentTermBtn.disabled = false;
            currentTermBtn.style.cursor = 'pointer';
            currentTermBtn.title = 'Jump to current term';
        } else if (selectedTermId == currentAcademicTerm.id) {
            currentTermBtn.disabled = true;
            currentTermBtn.style.cursor = 'not-allowed';
            currentTermBtn.title = 'Already viewing current term';
        } else {
            currentTermBtn.disabled = false;
            currentTermBtn.style.cursor = 'pointer';
            currentTermBtn.title = 'Jump to current term';
        }
    }



// Navigate to program courses page when a program card is clicked (event delegation for dynamic content)
// Removed click event listener since cards now have View Details buttons

    // Reference Request Functions
    function viewReferenceRequest(requestId) {
        // For now, just show an alert. This can be expanded to show a modal or navigate to a detail page
        alert('Viewing Reference Request #' + requestId + '\n\nThis functionality can be expanded to show detailed information or navigate to a dedicated page.');
        
        // Future implementation could include:
        // - Show a modal with request details
        // - Navigate to a reference request detail page
        // - Open an edit form for the request
    }

function toggleSection() {
        const section = document.querySelector('.dashboard-section');
        const container = section.querySelector('.reference-requests-container');
        const footer = section.querySelector('.section-footer');
        const collapseBtn = section.querySelector('.collapse-btn');
        const headerActions = section.querySelector('.header-actions');
        
        
        // Check if container is currently hidden
        const isCurrentlyHidden = container.style.display === 'none';
        
        
        if (isCurrentlyHidden) {
            // Expand - show normal layout
            container.style.display = 'block';
            footer.style.display = 'flex';
            
            // Remove the collapsed controls if they exist
            const existingCollapsedControls = section.querySelector('.collapsed-controls');
            if (existingCollapsedControls) {
                existingCollapsedControls.remove();
            }
            
            // Restore the navigation buttons
            headerActions.style.display = 'flex';
            
            // Display the current page of requests
            displayCurrentPage();
        
    } else {
        // Collapse - just replace navigation buttons with red badge + expand button
        container.style.display = 'none';
        footer.style.display = 'none';
        
        // Hide the navigation buttons and replace with red badge + expand button
        headerActions.style.display = 'none';
        
        // Create simple red badge + expand button in the same header area
        const totalRequests = allRequests.length;
        const collapsedControls = document.createElement('div');
        collapsedControls.className = 'collapsed-controls';
        collapsedControls.innerHTML = `
            <div class="request-count-badge">${totalRequests}</div>
            <button class="expand-btn course-nav-btn" onclick="toggleSection()">
                <span>Expand</span>
                <i data-lucide="chevron-down" class="collapse-icon" aria-hidden="true"></i>
            </button>
        `;
        
        // Insert the collapsed controls in the same header area
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
        if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
        
    }
}

    // Reference Requests Variables
    let allRequests = [];
    let currentPage = 0;
    let requestsPerPage = 4;

    document.addEventListener('DOMContentLoaded', function() {
        
        // Initialize term selector
        const termSelect = document.getElementById('academicTermSelect');
        
    // Set initial selection: prioritize current term if no session storage
    if (termSelect) {
        // If no term selected in session storage, default to current term
        if (!sessionStorage.getItem('selectedTermId') && currentAcademicTerm) {
            selectedTermId = currentAcademicTerm.id;
            sessionStorage.setItem('selectedTermId', selectedTermId);
        }
        
        // Set the dropdown value
        if (selectedTermId) {
            termSelect.value = selectedTermId;
        }
        
        // Add change event listener for term selector
        termSelect.addEventListener('change', function() {
            if (!window.__termChangeInProgress) {
                handleTermChangeFromDropdown();
            }
        });
        
        // Load data for the selected term on page load - only if not already loaded by PHP
        if (selectedTermId && !window.__dashboardInitialized) {
            window.__dashboardInitialized = true;
            // Data is already loaded via PHP, no need to reload
        }
    }
        
        // Display current selected term info and update button state
        updateSelectedTermDisplay();
        updateCurrentTermButtonState();
        
        // Load data from PHP
        const requestsData = document.getElementById('allRequestsData');
        
        
        if (requestsData) {
            allRequests = JSON.parse(requestsData.textContent);
            
            
            // Display summary instead of all cards
            displayCurrentPage();
            
            // Auto-collapse the section on page load
            setTimeout(() => {
                toggleSection();
            }, 100);
            
        } else {
            console.error('Failed to find required data elements');
        }
        
        // Initialize Course Proposals & Revisions section
        initializeCourseProposals();
    });
    
// Initialize Course Proposals & Revisions section
    async function initializeCourseProposals() {
        const proposalsGrid = document.getElementById('courseProposalsGrid');
        const emptyState = document.getElementById('courseProposalsEmptyState');
        
        if (!proposalsGrid || !emptyState) {
            console.error('Course proposals elements not found');
            return;
        }
        
        // Show loading state
        proposalsGrid.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">Loading proposals...</div>';
        
        try {
            // Fetch proposals from API
            const response = await fetch('api/get_course_proposals.php?limit=10', {
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch course proposals');
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch course proposals');
            }
            
            const proposals = data.proposals || [];
            
            // Store data globally for access by viewCourseProposalDetails
            window.courseProposalsData = proposals;
            
            // Clear existing content
            proposalsGrid.innerHTML = '';
            
            if (proposals.length === 0) {
                // Show empty state
                emptyState.style.display = 'block';
                proposalsGrid.style.minHeight = '230px';
            } else {
                // Hide empty state
                emptyState.style.display = 'none';
                
                // Create and append cards
                proposals.forEach(function(cardData, index) {
                    try {
                        var card = createCourseProposalCard(cardData);
                        if (card) {
                            proposalsGrid.appendChild(card);
                        }
                    } catch (error) {
                        console.error('Error creating card ' + (index + 1) + ':', error, cardData);
                    }
                });
            }
        } catch (error) {
            console.error('Error loading course proposals:', error);
            proposalsGrid.innerHTML = '<div style="text-align: center; padding: 20px; color: #f44336;">Error loading proposals. Please try again.</div>';
            emptyState.style.display = 'none';
        }
    }
    
    // Create a course proposal card
    function createCourseProposalCard(cardData) {
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-proposal-id', cardData.id || cardData.programCode);
        
        // Check if this is a draft
        const isDraft = cardData.isDraft === true || cardData.status === 'Draft' || cardData.status.toLowerCase().includes('draft');
        
        // Determine status class and label
        let statusClass = 'status-pending';
        let statusLabel = cardData.status;
        
        if (isDraft) {
            statusClass = 'status-draft';
            statusLabel = 'Draft';
        } else if (cardData.status.toLowerCase().includes('approved') || cardData.status.toLowerCase().includes('added')) {
            statusClass = 'status-approved';
        } else if (cardData.status.toLowerCase().includes('rejected')) {
            statusClass = 'status-rejected';
        }
        
        // Format date
        const date = new Date(cardData.submittedDate || cardData.createdAt || new Date());
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Get course information
        const courseCode = cardData.courseCode || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseCode : 'N/A');
        const courseName = cardData.courseName || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseName : 'N/A');
        
        // Action Buttons
        const actionButtons = isDraft ? `
            <div style="display: flex; gap: 8px; width: 100%; margin-top: auto;">
                <button class="view-all-btn" onclick="event.stopPropagation(); resumeDraft('${cardData.id}', event);" style="flex: 1; height: 36px; padding: 0;">Resume</button>
                <button class="view-all-btn" onclick="event.stopPropagation(); deleteDraft('${cardData.id}', event);" style="flex: 1; height: 36px; padding: 0; background: #fee2e2; color: #991b1b; border-color: #fecaca;">Delete</button>
            </div>
        ` : `
            <button class="view-all-btn" onclick="event.stopPropagation(); viewCourseProposalDetails('${cardData.id}');" style="width: 100%; margin-top: auto;">
                View Details
            </button>
        `;
        
        card.innerHTML = `
            <div class="status-badge ${statusClass}">${statusLabel}</div>
            
            <div style="margin-top: 16px; margin-bottom: 12px; height: 40px; display: flex; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; color: #0C4B34; background: rgba(12, 75, 52, 0.08); padding: 4px 8px; border-radius: 6px; letter-spacing: 0.5px;">${courseCode}</span>
                <span style="margin-left: auto; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase;">${cardData.programCode || 'PROPOSAL'}</span>
            </div>
            
            <h3 style="margin: 0 0 4px 0; font-size: 15px; color: #111827; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px;">${courseName}</h3>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #6b7280; font-weight: 600;">
                    <span class="lucide-inline-icon"><i data-lucide="calendar-days"></i></span>
                    <span>${formattedDate}</span>
                </div>
                ${cardData.totalReferences > 0 ? `
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #6b7280; font-weight: 600;">
                        <span class="lucide-inline-icon"><i data-lucide="book"></i></span>
                        <span>${cardData.totalReferences} Ref</span>
                    </div>
                ` : ''}
            </div>
            
            ${actionButtons}
        `;
        if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
        
        return card;
    }
    
    // View course proposal details
    function viewCourseProposalDetails(proposalId) {
        
        // Find the proposal data
        let proposal = null;
        
        // Try to get from global course proposals data
        if (window.courseProposalsData && Array.isArray(window.courseProposalsData)) {
            proposal = window.courseProposalsData.find(p => (p.id === proposalId || p.courseCode === proposalId));
        }
        
        // Fallback: try to get from allProposals if available (from course-proposals page)
        if (!proposal && typeof allProposals !== 'undefined' && Array.isArray(allProposals)) {
            proposal = allProposals.find(p => (p.id === proposalId || p.courseCode === proposalId));
        }
        
        if (!proposal) {
            alert('Course proposal not found: ' + proposalId);
            return;
        }
        
        // Open the details modal
        if (typeof openCourseProposalDetailsModal === 'function') {
            openCourseProposalDetailsModal(proposal);
        } else {
            // Modal function not available, define it
            openCourseProposalDetailsModal(proposal);
        }
    }
    
    // Resume draft - load draft data back into the form
    function resumeDraft(proposalId, event) {
        if (event) {
            event.stopPropagation();
        }
        
        
        // Find the draft proposal
        let proposal = null;
        if (window.courseProposalsData && Array.isArray(window.courseProposalsData)) {
            proposal = window.courseProposalsData.find(p => (p.id === proposalId || p.courseCode === proposalId) && (p.isDraft === true || p.status === 'Draft'));
        }
        
        if (!proposal) {
            alert('Draft not found: ' + proposalId);
            return;
        }
        
        
        // Get the first course data from the draft
        const draftData = proposal._rawCoursesData && proposal._rawCoursesData.length > 0 
            ? proposal._rawCoursesData[0] 
            : null;
        
        if (!draftData) {
            alert('Draft course data not available. Please recreate the course.');
            return;
        }
        
        // Store draft data globally for the modal to access
        window.draftToResume = {
            proposal: proposal,
            courseData: draftData,
            formData: proposal._formData || {}
        };
            
        // Set course selection context from draft (this allows modal to skip course type selection)
            if (proposal.programId && proposal.academicTerm && proposal.academicYear && proposal.yearLevel) {
                window.courseSelectionContext = {
                    programId: proposal.programId,
                    programName: proposal.programName,
                programCode: proposal.programCode,
                    term: proposal.academicTerm,
                    academicYear: proposal.academicYear,
                    yearLevel: proposal.yearLevel,
                courseType: proposal.courseType || 'proposal',
                skipCourseTypeSelection: true, // Flag to skip course type modal
                isResumingDraft: true // Additional flag to indicate we're resuming
            };
            
            // Also set program selection in the hidden input
            const selectedProgramsInput = document.getElementById('selectedPrograms');
            if (selectedProgramsInput) {
                selectedProgramsInput.value = proposal.programId;
            }
            
            // Set program display text
            const programSelectText = document.getElementById('programSelectText');
            if (programSelectText) {
                programSelectText.textContent = proposal.programName || proposal.programCode;
            }
        }
        
        // Open the add course modal directly (skip course type selection)
                    if (typeof openAddCourseModal === 'function') {
            
            // Ensure isResumingDraft flag is set before opening modal
            if (window.courseSelectionContext) {
                window.courseSelectionContext.isResumingDraft = true;
            }
            
                        openAddCourseModal();
            
            // Load draft data into form after modal is fully open
            // Wait for modal and all containers to be ready
            const waitForContainers = () => {
                const requiredContainers = [
                    'courseOutlineTableBody',
                    'assessmentTableBody', 
                    'learningMaterialsTableBody',
                    'learningOutcomesContainer'
                ];
                
                let allReady = true;
                requiredContainers.forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) {
                        console.warn(`⚠️ Container ${id} not ready yet`);
                        allReady = false;
                    }
                });
                
                return allReady;
            };
            
            // Try loading immediately, then retry if containers aren't ready
            const tryLoad = async () => {
                let attempts = 0;
                const maxAttempts = 10;
                
                while (attempts < maxAttempts) {
                    if (waitForContainers()) {
                        await loadDraftIntoForm(draftData, proposal);
                        break;
                    }
                    attempts++;
                    if (attempts < maxAttempts) {
                        await new Promise(resolve => setTimeout(resolve, 200));
                    }
                }
                
                if (attempts >= maxAttempts) {
                    console.error('❌ Containers not ready after', maxAttempts, 'attempts. Loading anyway...');
                    await loadDraftIntoForm(draftData, proposal);
                }
            };
            
            // Start loading process
            setTimeout(() => {
                tryLoad();
            }, 300);
        } else {
            alert('Unable to open course modal. Please refresh the page and try again.');
        }
    }
    
    // Load draft data into the form
    async function loadDraftIntoForm(draftData, proposal) {
        console.log('Loading draft data:', {
            has_outcomes: !!(draftData.learning_outcomes && draftData.learning_outcomes.length),
            outcomes_count: draftData.learning_outcomes?.length || 0,
            has_outline: !!(draftData.course_outline && draftData.course_outline.length),
            outline_count: draftData.course_outline?.length || 0,
            has_assessments: !!(draftData.assessment_methods && draftData.assessment_methods.length),
            assessments_count: draftData.assessment_methods?.length || 0,
            has_materials: !!(draftData.learning_materials && draftData.learning_materials.length),
            materials_count: draftData.learning_materials?.length || 0,
            has_justification: !!draftData.justification
        });
        
        // Store the loaded draft data globally so we can merge with it when saving again
        window.loadedDraftData = JSON.parse(JSON.stringify(draftData)); // Deep copy
        
        const form = document.getElementById('addCourseForm');
        if (!form) {
            console.error('❌ Form not found, retrying...');
            setTimeout(() => loadDraftIntoForm(draftData, proposal), 200);
            return;
        }
        
        // Make sure modal is visible
        const modal = document.getElementById('addCourseModal');
        if (!modal || modal.style.display === 'none') {
            setTimeout(() => loadDraftIntoForm(draftData, proposal), 200);
            return;
        }
        
        // Wait a bit to ensure all containers are in the DOM
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Ensure all step containers are accessible (even if hidden)
        const allSteps = document.querySelectorAll('.form-step');
        const originalStyles = [];
        allSteps.forEach((step, idx) => {
            // Store original computed styles
            const computed = window.getComputedStyle(step);
            originalStyles[idx] = {
                display: step.style.display || computed.display,
                visibility: step.style.visibility || computed.visibility,
                position: step.style.position || computed.position,
                height: step.style.height || computed.height,
                opacity: step.style.opacity || computed.opacity
            };
            // Temporarily make step accessible (but keep it visually hidden)
            if (!step.classList.contains('active')) {
                step.style.position = 'absolute';
                step.style.left = '-9999px';
                step.style.visibility = 'visible'; // Changed to visible so elements are accessible
                step.style.display = 'block';
                step.style.height = 'auto';
                step.style.opacity = '0';
                step.style.pointerEvents = 'none';
            }
        });
        
        // Verify critical containers exist
        const criticalContainers = {
            'courseOutlineTableBody': '#courseOutlineTableBody',
            'assessmentTableBody': '#assessmentTableBody',
            'learningMaterialsTableBody': '#learningMaterialsTableBody',
            'learningOutcomesContainer': '#learningOutcomesContainer'
        };
        
        let allContainersReady = true;
        for (const [name, selector] of Object.entries(criticalContainers)) {
            const element = document.querySelector(selector);
            if (element) {
            } else {
                console.warn(`  ⚠️ ${name} NOT FOUND!`);
                allContainersReady = false;
            }
        }
        
        if (!allContainersReady) {
            console.warn('⚠️ Some containers not ready, waiting 200ms and retrying...');
            await new Promise(resolve => setTimeout(resolve, 200));
            // Retry checking
            for (const [name, selector] of Object.entries(criticalContainers)) {
                const element = document.querySelector(selector);
                if (element) {
                } else {
                    console.error(`  ❌ ${name} still not found after retry!`);
                }
            }
        }
        
        try {
            // Step 1: Course Information
            const courseCodeEl = document.getElementById('courseCode');
            const courseNameEl = document.getElementById('courseName');
            const unitsEl = document.getElementById('units');
            const lectureHoursEl = document.getElementById('lectureHours');
            const laboratoryHoursEl = document.getElementById('laboratoryHours');
            const prerequisitesEl = document.getElementById('prerequisites');
            
            if (courseCodeEl && draftData.course_code) courseCodeEl.value = draftData.course_code;
            if (courseNameEl && draftData.course_name) courseNameEl.value = draftData.course_name;
            if (unitsEl && draftData.units) unitsEl.value = draftData.units;
            if (lectureHoursEl && draftData.lecture_hours) lectureHoursEl.value = draftData.lecture_hours;
            if (laboratoryHoursEl && draftData.laboratory_hours) laboratoryHoursEl.value = draftData.laboratory_hours;
            if (prerequisitesEl && draftData.prerequisites) prerequisitesEl.value = draftData.prerequisites;
            
            // Populate Academic Term, Academic Year, and Year Level from proposal context
            // When resuming a draft, we want to show and populate these fields
            const academicFieldsRow = document.getElementById('academicFieldsRow');
            if (academicFieldsRow) {
                // Make sure the fields are visible when resuming (they might be hidden by course selection context)
                academicFieldsRow.style.display = '';
                academicFieldsRow.style.visibility = 'visible';
                academicFieldsRow.style.height = '';
                academicFieldsRow.style.maxHeight = '';
                academicFieldsRow.style.margin = '';
                academicFieldsRow.style.padding = '';
                academicFieldsRow.style.overflow = '';
                academicFieldsRow.style.opacity = '1';
                academicFieldsRow.style.position = '';
                academicFieldsRow.style.width = '';
                academicFieldsRow.style.minWidth = '';
                academicFieldsRow.style.maxWidth = '';
                academicFieldsRow.style.border = '';
                academicFieldsRow.style.lineHeight = '';
            }
            
            // Populate Academic Term
            if (proposal.academicTerm) {
                const academicTermEl = document.getElementById('academicTerm');
                if (academicTermEl) {
                    academicTermEl.value = proposal.academicTerm;
                    // Trigger change event to ensure any listeners are notified
                    academicTermEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
            
            // Populate Academic Year
            if (proposal.academicYear) {
                const academicYearEl = document.getElementById('academicYear');
                if (academicYearEl) {
                    academicYearEl.value = proposal.academicYear;
                    // Trigger change event to ensure any listeners are notified
                    academicYearEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
            
            // Populate Year Level
            if (proposal.yearLevel) {
                const yearLevelEl = document.getElementById('yearLevel');
                if (yearLevelEl) {
                    yearLevelEl.value = proposal.yearLevel;
                    // Trigger change event to ensure any listeners are notified
                    yearLevelEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
            
            // Step 2: Course Description
            const courseDescriptionEl = document.getElementById('courseDescription');
            if (courseDescriptionEl && draftData.course_description) {
                courseDescriptionEl.value = draftData.course_description;
            }
            
            // Step 3: Learning Outcomes
            if (draftData.learning_outcomes && Array.isArray(draftData.learning_outcomes) && draftData.learning_outcomes.length > 0) {
                const outcomesContainer = document.getElementById('learningOutcomesContainer');
                if (outcomesContainer) {
                    outcomesContainer.innerHTML = ''; // Clear existing
                    let outcomeIndex = 0;
                    draftData.learning_outcomes.forEach((outcome, idx) => {
                        if (outcome && outcome.trim()) {
                            // Escape HTML to prevent XSS
                            const escapeHtml = (text) => {
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            };
                            const outcomeField = document.createElement('div');
                            outcomeField.className = 'outcome-field';
                            outcomeField.dataset.outcomeIndex = outcomeIndex;
                            outcomeField.innerHTML = `
                                <div class="outcome-label">Outcome ${outcomeIndex + 1}:</div>
                                <div class="outcome-input-wrapper">
                                    <textarea class="outcome-input" name="learning_outcomes[]" placeholder="Enter learning outcome..." rows="2">${escapeHtml(outcome)}</textarea>
                                    <button type="button" class="remove-outcome-btn" onclick="if(typeof window.removeLearningOutcome==='function'){window.removeLearningOutcome(${outcomeIndex});}else{this.closest('.outcome-field').remove();}">Remove</button>
                                </div>
                            `;
                            outcomesContainer.appendChild(outcomeField);
                            outcomeIndex++;
                        }
                    });
                    // Update the global counter
                    if (typeof window.learningOutcomesCount !== 'undefined') {
                        window.learningOutcomesCount = outcomeIndex;
                    }
                    if (typeof learningOutcomesCount !== 'undefined') {
                        learningOutcomesCount = outcomeIndex;
                    }
                } else {
                    console.error('❌ Learning outcomes container not found!');
                }
            } else {
            }
            
            // Step 4: Course Outline
            if (draftData.course_outline && Array.isArray(draftData.course_outline) && draftData.course_outline.length > 0) {
                const outlineTableBody = document.getElementById('courseOutlineTableBody');
                if (outlineTableBody) {
                    outlineTableBody.innerHTML = ''; // Clear existing
                    let topicIndex = 0;
                    draftData.course_outline.forEach((outline, idx) => {
                        const topic = outline.topic || outline.week_or_topic || outline.week || outline.week_number || '';
                        const description = outline.description || outline.topic_description || '';
                        const hours = outline.hours || outline.week_hours || 0.5;
                        
                        if (topic || description || hours) {
                            const row = document.createElement('tr');
                            row.dataset.topicIndex = topicIndex;
                            // Escape HTML to prevent XSS
                            const escapeHtml = (text) => {
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            };
                            row.innerHTML = `
                                <td><input type="text" class="topic-input" name="course_outline[${topicIndex}][topic]" value="${escapeHtml(topic)}" placeholder="e.g., Week 1 or Topic 1"></td>
                                <td><textarea class="topic-description" name="course_outline[${topicIndex}][description]" placeholder="Topic description..." rows="2">${escapeHtml(description)}</textarea></td>
                                <td><input type="number" class="topic-hours" name="course_outline[${topicIndex}][hours]" value="${hours}" placeholder="Hours" min="0.5" step="0.5"></td>
                                <td><button type="button" class="remove-topic-btn" onclick="if(typeof window.removeCourseTopic==='function'){window.removeCourseTopic(${topicIndex});}else{this.closest('tr').remove();}">Remove</button></td>
                            `;
                            outlineTableBody.appendChild(row);
                            topicIndex++;
                        }
                    });
                    // Update the global counter
                    if (typeof window.courseTopicsCount !== 'undefined') {
                        window.courseTopicsCount = topicIndex;
                    }
                    if (typeof courseTopicsCount !== 'undefined') {
                        courseTopicsCount = topicIndex;
                    }
                } else {
                    console.error('❌ Course outline table body not found!');
                }
            } else {
            }
            
            // Step 5: Assessment Methods
            if (draftData.assessment_methods && Array.isArray(draftData.assessment_methods)) {
                const assessmentTableBody = document.getElementById('assessmentTableBody');
                if (assessmentTableBody) {
                    assessmentTableBody.innerHTML = ''; // Clear existing
                    let assessmentIndex = 0;
                    draftData.assessment_methods.forEach((method) => {
                        const row = document.createElement('tr');
                        row.dataset.assessmentIndex = assessmentIndex;
                        row.innerHTML = `
                            <td><input type="text" class="assessment-type-input" name="assessment[${assessmentIndex}][type]" value="${method.method || method.type || ''}" placeholder="e.g., Midterm Exam, Final Project"></td>
                            <td><input type="number" class="assessment-percentage-input" name="assessment[${assessmentIndex}][percentage]" value="${method.weight || method.percentage || ''}" placeholder="%" min="0" max="100"></td>
                            <td><button type="button" class="remove-assessment-btn" onclick="if(typeof window.removeAssessmentMethod==='function'){window.removeAssessmentMethod(${assessmentIndex});}else{this.closest('tr').remove();}">Remove</button></td>
                        `;
                        assessmentTableBody.appendChild(row);
                        assessmentIndex++;
                    });
                    // Update the global counter
                    if (typeof window.assessmentMethodsCount !== 'undefined') {
                        window.assessmentMethodsCount = assessmentIndex;
                    }
                }
            }
            
            // Step 6: Learning Materials
            if (draftData.learning_materials && Array.isArray(draftData.learning_materials) && draftData.learning_materials.length > 0) {
                const materialsTableBody = document.getElementById('learningMaterialsTableBody');
                if (materialsTableBody) {
                    materialsTableBody.innerHTML = ''; // Clear existing
                    // Reset material count
                    if (typeof window.materialCount !== 'undefined') {
                        window.materialCount = 0;
                    }
                    
                    draftData.learning_materials.forEach((material) => {
                        // Handle both field name variations
                        const callNumber = material.call_number || material.material_call_number || '';
                        const title = material.title || material.material_title || '';
                        const author = material.author || material.material_author || '';
                        const publisher = material.publisher || material.material_publisher || '';
                        const year = material.year || material.publication_year || material.material_year || '';
                        const type = material.type || material.material_type || '';
                        const remarks = material.remarks || material.material_remarks || '';
                        
                        if (title || callNumber || author || publisher || year || type) {
                            if (typeof window.addMaterialRow === 'function') {
                                window.addMaterialRow();
                                // Get the last added row
                                const rows = materialsTableBody.querySelectorAll('tr');
                                const lastRow = rows[rows.length - 1];
                                if (lastRow) {
                                    const callNumberInput = lastRow.querySelector('.material-call-number-input');
                                    const titleInput = lastRow.querySelector('.material-title-input');
                                    const authorInput = lastRow.querySelector('.material-author-input');
                                    const publisherInput = lastRow.querySelector('.material-publisher-input');
                                    const yearInput = lastRow.querySelector('.material-year-input');
                                    const typeInput = lastRow.querySelector('.material-type-input');
                                    const remarksInput = lastRow.querySelector('.material-remarks-input');
                                    
                                    if (callNumberInput && callNumber) callNumberInput.value = callNumber;
                                    if (titleInput && title) titleInput.value = title;
                                    if (authorInput && author) authorInput.value = author;
                                    if (publisherInput && publisher) publisherInput.value = publisher;
                                    if (yearInput && year) yearInput.value = year;
                                    if (typeInput && type) typeInput.value = type;
                                    if (remarksInput && remarks) remarksInput.value = remarks;
                                    
                                }
                            }
                        }
                    });
                }
            }
            
            // Step 8: Justification
            const justificationEl = document.getElementById('justification');
            if (justificationEl && draftData.justification) {
                justificationEl.value = draftData.justification;
            }
            
            // Set program selection if available
            if (proposal.programId && proposal.programName) {
                const selectedProgramsInput = document.getElementById('selectedPrograms');
                const programSelectText = document.getElementById('programSelectText');
                if (selectedProgramsInput) {
                    selectedProgramsInput.value = proposal.programId;
                }
                if (programSelectText) {
                    programSelectText.textContent = proposal.programName;
                }
            }
            
            // FIRST: Restore saved step BEFORE restoring visibility
            // This ensures the correct step is active when styles are restored
            if (draftData.saved_step) {
                const targetStep = parseInt(draftData.saved_step) || 1;
                window._courseFormStep = targetStep;
                
                // Hide all steps first
                const steps = document.querySelectorAll('.form-step');
                steps.forEach(step => {
                    step.classList.remove('active');
                });
                
                // Show target step
                const targetStepEl = document.getElementById(`step${targetStep}`);
                if (targetStepEl) {
                    targetStepEl.classList.add('active');
                } else {
                    console.error(`❌ Step ${targetStep} element not found!`);
                }
                
                // Update progress steps
                const progressSteps = document.querySelectorAll('.progress-step');
                progressSteps.forEach((progressStep, index) => {
                    const stepNum = index + 1;
                    progressStep.classList.remove('active', 'completed');
                    if (stepNum < targetStep) {
                        progressStep.classList.add('completed');
                    } else if (stepNum === targetStep) {
                        progressStep.classList.add('active');
                    }
                });
            } else {
                window._courseFormStep = 1;
            }
            
            // THEN: Restore step visibility (clear inline styles)
            // IMPORTANT: Don't clear display from active step - let CSS handle it
            allSteps.forEach((step, idx) => {
                const isActive = step.classList.contains('active');
                if (originalStyles[idx] && !isActive) {
                    // Clear all inline styles from NON-ACTIVE steps only
                    step.style.position = '';
                    step.style.left = '';
                    step.style.visibility = '';
                    step.style.display = '';
                    step.style.height = '';
                    step.style.opacity = '';
                    step.style.pointerEvents = '';
                    step.style.zIndex = '';
                } else if (isActive) {
                    // For active step, just clear positioning styles but keep display
                    step.style.position = '';
                    step.style.left = '';
                    step.style.opacity = '';
                    step.style.pointerEvents = '';
                    step.style.zIndex = '';
                    // Let CSS handle display for active step
                    step.style.display = '';
                    step.style.visibility = '';
                }
            });
            
            // Update progress bar and navigation
            setTimeout(() => {
                if (typeof updateProgress === 'function') {
                    updateProgress();
                }
                
                if (typeof updateNavigationButtons === 'function') {
                    updateNavigationButtons();
                }
                
                // Explicitly show Previous button if on step 2 or later
                if (window._courseFormStep > 1) {
                    const prevBtn = document.getElementById('prevStepBtn');
                    if (prevBtn) {
                        prevBtn.style.display = 'inline-flex';
                        prevBtn.style.visibility = 'visible';
                        prevBtn.style.opacity = '1';
                    }
                }
                
            }, 100);
            
            // Step 7: Load attachments if available
            if (draftData.attachments && Array.isArray(draftData.attachments) && draftData.attachments.length > 0) {
                // Store attachments in global array (convert metadata to File-like objects if needed)
                if (!window.attachmentFiles) {
                    window.attachmentFiles = [];
                }
                
                // Convert attachment metadata to file-like objects
                draftData.attachments.forEach(att => {
                    if (att && (att.name || att.filename)) {
                        window.attachmentFiles.push({
                            name: att.name || att.filename,
                            size: att.size || 0,
                            type: att.type || 'application/octet-stream'
                        });
                    }
                });
                
                // Update attachment list using the proper function
                if (typeof window.updateAttachmentList === 'function') {
                    window.updateAttachmentList();
                } else {
                    console.warn('⚠️ updateAttachmentList function not available');
                    const attachmentList = document.getElementById('attachmentList');
                    if (attachmentList) {
                        attachmentList.innerHTML = '<p>Attachments loaded: ' + window.attachmentFiles.length + '</p>';
                    }
                }
            } else {
            }
            
            // (Duplicate restoration code removed - already handled above)
            
            // Verify data was loaded
            const outlineTableBody = document.getElementById('courseOutlineTableBody');
            const outcomesContainer = document.getElementById('learningOutcomesContainer');
            const assessmentTableBody = document.getElementById('assessmentTableBody');
            const materialsTableBody = document.getElementById('learningMaterialsTableBody');
            
            const loadedOutlineRows = outlineTableBody ? outlineTableBody.querySelectorAll('tr').length : 0;
            const loadedOutcomes = outcomesContainer ? outcomesContainer.querySelectorAll('.outcome-input').length : 0;
            const loadedAssessments = assessmentTableBody ? assessmentTableBody.querySelectorAll('tr').length : 0;
            const loadedMaterials = materialsTableBody ? materialsTableBody.querySelectorAll('tr').length : 0;
            console.log('Verification data:', {
                course_outline_rows: loadedOutlineRows,
                learning_outcomes: loadedOutcomes,
                assessment_methods: loadedAssessments,
                learning_materials: loadedMaterials
            });
            
            if (loadedOutlineRows !== (draftData.course_outline?.length || 0)) {
                console.warn('⚠️ Course outline count mismatch! Expected:', draftData.course_outline?.length, 'Got:', loadedOutlineRows);
            }
            if (loadedOutcomes !== (draftData.learning_outcomes?.length || 0)) {
                console.warn('⚠️ Learning outcomes count mismatch! Expected:', draftData.learning_outcomes?.length, 'Got:', loadedOutcomes);
            }
            
            
        } catch (error) {
            console.error('❌ Error loading draft data:', error);
            console.error('Error stack:', error.stack);
            
            // Restore step visibility even on error
            if (typeof allSteps !== 'undefined' && typeof originalStyles !== 'undefined') {
                allSteps.forEach((step, idx) => {
                    if (originalStyles[idx]) {
                        const orig = originalStyles[idx];
                        step.style.position = orig.position;
                        step.style.left = '';
                        step.style.visibility = orig.visibility;
                        step.style.display = orig.display;
                        step.style.height = orig.height;
                    }
                });
            }
            alert('Error loading draft data. Some fields may not be populated.');
        }
    }
    
    // Delete draft
    function deleteDraft(proposalId, event) {
        if (event) {
            event.stopPropagation();
        }
        
        // Find proposal to get program info for message
        const proposal = window.courseProposalsData && Array.isArray(window.courseProposalsData) 
            ? window.courseProposalsData.find(p => (p.id === proposalId || p.courseCode === proposalId))
            : null;
        
        // Store proposal ID for the modal
        window.pendingDeleteProposalId = proposalId;
        window.pendingDeleteProposal = proposal;
        
        // Show delete confirmation modal
        showDeleteConfirmationModal(proposal);
    }
    
    function showDeleteConfirmationModal(proposal) {
        const modal = document.getElementById('deleteConfirmationModal');
        if (!modal) {
            console.error('Delete confirmation modal not found');
            // Fallback to confirm dialog
            const programInfo = proposal ? `${proposal.programCode} - ${proposal.academicTerm}, ${proposal.yearLevel}` : window.pendingDeleteProposalId;
            if (confirm(`Are you sure you want to delete the draft for "${programInfo}"? This action cannot be undone.`)) {
                confirmDeleteDraft();
            }
            return;
        }
        
        // Update modal content with proposal info
        const programInfo = proposal ? `${proposal.programCode} - ${proposal.academicTerm}, ${proposal.yearLevel}` : 'this draft';
        const messageEl = document.getElementById('deleteConfirmationMessage');
        if (messageEl) {
            messageEl.textContent = `Are you sure you want to delete the draft for "${programInfo}"?`;
        }
        
        const warningEl = document.getElementById('deleteConfirmationWarning');
        if (warningEl) {
            warningEl.textContent = `This action cannot be undone. All draft data will be permanently deleted.`;
        }
        
        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Close modal when clicking outside (on the backdrop)
        modal.addEventListener('click', function closeOnBackdropClick(event) {
            if (event.target === modal) {
                closeDeleteConfirmationModal();
                modal.removeEventListener('click', closeOnBackdropClick);
            }
        });
        
        // Close modal on Escape key
        const escapeHandler = function(event) {
            if (event.key === 'Escape') {
                closeDeleteConfirmationModal();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }
    
    function closeDeleteConfirmationModal() {
        const modal = document.getElementById('deleteConfirmationModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
        // Clear pending delete data
        window.pendingDeleteProposalId = null;
        window.pendingDeleteProposal = null;
    }
    
    function confirmDeleteDraft() {
        const proposalId = window.pendingDeleteProposalId;
        const proposal = window.pendingDeleteProposal;
        
        if (!proposalId) {
            console.error('No proposal ID to delete');
            return;
        }
        
        
        // Close modal
        closeDeleteConfirmationModal();
        
        // Remove from local array
        if (window.courseProposalsData && Array.isArray(window.courseProposalsData)) {
            const index = window.courseProposalsData.findIndex(p => (p.id === proposalId || p.courseCode === proposalId) && (p.isDraft === true || p.status === 'Draft'));
            if (index !== -1) {
                window.courseProposalsData.splice(index, 1);
                // Re-render the grid
                initializeCourseProposals();
            }
        }
        
        // Delete from backend
        fetch('api/delete_course_draft.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                proposal_id: proposalId,
                program_id: proposal ? proposal.programId : null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message (optional)
                // You could add a toast notification here if desired
            } else {
                console.error('Error deleting draft:', data.message);
                alert('Error deleting draft: ' + (data.message || 'Unknown error'));
                // Re-render to restore the card if deletion failed
                initializeCourseProposals();
            }
        })
        .catch(error => {
            console.error('Error deleting draft:', error);
            alert('Error deleting draft. Please try again.');
            // Re-render to restore the card if deletion failed
            initializeCourseProposals();
        });
    }
    
    // Make functions globally available
    window.resumeDraft = resumeDraft;
    window.deleteDraft = deleteDraft;
    window.showDeleteConfirmationModal = showDeleteConfirmationModal;
    window.closeDeleteConfirmationModal = closeDeleteConfirmationModal;
    window.confirmDeleteDraft = confirmDeleteDraft;
    
    // Store course proposals data globally for access
    window.courseProposalsData = [];
    
    // Open course proposal details modal (shared function)
    window.openCourseProposalDetailsModal = function(proposal) {
        // Create or get modal
        let modal = document.getElementById('courseProposalDetailsModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'courseProposalDetailsModal';
            modal.className = 'course-proposal-details-modal';
            document.body.appendChild(modal);
        }
        
        // Format date
        const date = new Date(proposal.submittedDate);
        const formattedDate = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Get file icon (Lucide name)
        function getFileIcon(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            const icons = {
                pdf: 'file-text',
                doc: 'file-text',
                docx: 'file-text',
                xls: 'file-spreadsheet',
                xlsx: 'file-spreadsheet',
                txt: 'file-text',
                jpg: 'image',
                jpeg: 'image',
                png: 'image',
                gif: 'image'
            };
            return icons[ext] || 'paperclip';
        }
        
        // Build attachments HTML
        let attachmentsHTML = '';
        if (proposal.attachments && proposal.attachments.length > 0) {
            attachmentsHTML = proposal.attachments.map((attachment, index) => `
                <div class="attachment-item-detail" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f9f9f9; border-radius: 6px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <span class="lucide-inline-icon"><i data-lucide="${getFileIcon(attachment.name)}"></i></span>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 4px; word-break: break-word;">${attachment.name}</div>
                            <div style="font-size: 11px; color: #666;">${formatFileSize(attachment.size)}</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="viewAttachmentFile('${attachment.path}', '${attachment.name}')" class="view-attachment-btn" style="padding: 6px 12px; background: #1976d2; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">
                            View
                        </button>
                        <button onclick="downloadAttachmentFile('${attachment.path}', '${attachment.name}')" class="download-attachment-btn" style="padding: 6px 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif;">
                            Download
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            attachmentsHTML = '<div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">No attachments available</div>';
        }
        
        // Set modal content
        modal.innerHTML = `
            <div class="modal-content-details" style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
                <div class="modal-header-details" style="padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #333; font-family: 'TT Interphases', sans-serif;">Course Proposal Details</h2>
                    <span class="close-modal" onclick="closeCourseProposalDetailsModal()" style="font-size: 28px; font-weight: 300; color: #999; cursor: pointer; line-height: 1;">&times;</span>
                </div>
                
                <div class="modal-body-details" style="padding: 20px;">
                    <!-- Course Information -->
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Course Information</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Course Code</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.courseCode}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Program</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.program}</div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Course Name</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.courseName}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Units</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.units || 'N/A'}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Hours</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.lectureHours || 0}L / ${proposal.laboratoryHours || 0}Lab</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Type</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.type}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Submitted Date</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${formattedDate}</div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block;">Status</label>
                                <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif; font-weight: 600; color: ${proposal.statusColor || '#666'};">${proposal.status}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Learning Materials -->
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Learning Materials</h3>
                        <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif;">${proposal.materialCount || 'No materials specified'}</div>
                    </div>
                    
                    <!-- Justification -->
                    ${proposal.justification ? `
                    <div class="details-section" style="margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Justification</h3>
                        <div style="font-size: 14px; color: #333; font-family: 'TT Interphases', sans-serif; line-height: 1.6; background: #f9f9f9; padding: 12px; border-radius: 6px;">${proposal.justification}</div>
                    </div>
                    ` : ''}
                    
                    <!-- Attachments -->
                    <div class="details-section">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; font-family: 'TT Interphases', sans-serif; border-bottom: 2px solid #1976d2; padding-bottom: 8px;">Attachments</h3>
                        <div class="attachments-list-detail">
                            ${attachmentsHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Show modal
        modal.style.display = 'flex';
        if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
    };
    
    // Close course proposal details modal
    window.closeCourseProposalDetailsModal = function() {
        const modal = document.getElementById('courseProposalDetailsModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    // View attachment file
    window.viewAttachmentFile = function(path, fileName) {
        // In a real implementation, this would open the file from the server
        window.open(path, '_blank');
    };
    
    // Download attachment file
    window.downloadAttachmentFile = function(path, fileName) {
        // In a real implementation, this would download the file from the server
        const link = document.createElement('a');
        link.href = path;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('courseProposalDetailsModal');
        if (modal && event.target === modal) {
            window.closeCourseProposalDetailsModal();
        }
    });

    function displayCurrentPage() {
        const grid = document.getElementById('referenceRequestsGrid');
        const start = currentPage * requestsPerPage;
        const end = start + requestsPerPage;
        const requestsToDisplay = allRequests.slice(start, end);

        grid.innerHTML = ''; // Clear existing cards

        requestsToDisplay.forEach(request => {
            const card = createRequestCard(request);
            grid.appendChild(card);
        });
        if (typeof window.ascomRefreshIcons === 'function') window.ascomRefreshIcons();
    }

    function createRequestCard(request) {
        // Generate simplified APA citation for display
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
        
        // Get department code
        const departmentCode = '<?php echo $_SESSION["selected_role"]["department_code"] ?? "CCS"; ?>';
        
        // Create card element
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-request-id', request.id);
        
        // Set status badge class and text
        const statusClass = request.status ? `status-${request.status.toLowerCase()}` : 'status-pending';
        const statusText = request.status || 'PENDING';
        
        // Set priority badge
        const priorityBadge = request.priority ? `<span style="font-size: 10px; font-weight: 700; padding: 3px 6px; border-radius: 4px; background: ${request.priority === 'HIGH' ? '#fee2e2' : request.priority === 'MEDIUM' ? '#fef3c7' : '#e0e7ff'}; color: ${request.priority === 'HIGH' ? '#991b1b' : request.priority === 'MEDIUM' ? '#92400e' : '#4338ca'};">${request.priority}</span>` : '';
        
        // Action buttons based on status
        let actionButtons = '';
        if (request.status === 'PENDING') {
            actionButtons = `
                <div class="action-buttons" style="margin-top: auto; display: flex; gap: 8px; width: 100%;">
                    <button class="view-all-btn" onclick="approveRequest(${request.id})" style="flex: 1; height: 36px; padding: 0; background: #0C4B34; color: white;">Approve</button>
                    <button class="view-all-btn" onclick="rejectRequest(${request.id})" style="flex: 1; height: 36px; padding: 0; background: #fee2e2; color: #991b1b; border-color: #fecaca;">Reject</button>
                </div>
            `;
        } else if (request.status === 'APPROVED') {
            actionButtons = `
                <div class="action-buttons" style="margin-top: auto; display: flex; gap: 8px; width: 100%;">
                    <button class="view-all-btn" onclick="rejectRequest(${request.id})" style="flex: 1; height: 36px; padding: 0; background: #fee2e2; color: #991b1b; border-color: #fecaca;">Reject</button>
                </div>
            `;
        } else if (request.status === 'REJECTED') {
            actionButtons = `
                <div class="action-buttons" style="margin-top: auto; display: flex; gap: 8px; width: 100%;">
                    <button class="view-all-btn" onclick="approveRequest(${request.id})" style="flex: 1; height: 36px; padding: 0; background: #0C4B34; color: white;">Approve</button>
                </div>
            `;
        }
        
        card.innerHTML = `
            <div class="status-badge ${statusClass}">${statusText}</div>
            
            <div style="margin-top: 16px; margin-bottom: 12px; height: 40px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 11px; font-weight: 800; color: #0C4B34; background: rgba(12, 75, 52, 0.08); padding: 4px 8px; border-radius: 6px; letter-spacing: 0.5px;">${request.course_code}</span>
                ${priorityBadge}
                <span style="margin-left: auto; font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase;">${departmentCode} FACULTY</span>
            </div>
            
            <div style="margin-bottom: 8px;">
                <span style="font-size: 12px; font-weight: 700; color: #111827;">${request.requester_name}</span>
            </div>
            
            <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #4b5563; font-style: italic; font-family: 'TT Interphases', sans-serif; height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                ${apaCitation}
            </h3>
            
            ${actionButtons}
            
            <div style="margin-top: 12px; font-size: 11px; color: #9ca3af; font-weight: 600; text-align: center;">
                Requested on: ${new Date().toLocaleDateString()}
            </div>
        `;
        
        return card;
    }

    function approveRequest(requestId) {
        if (confirm('Are you sure you want to approve this course material request?')) {
            // Find the request in the data
            const requestIndex = allRequests.findIndex(req => req.id === requestId);
            if (requestIndex !== -1) {
                // Update the status
                allRequests[requestIndex].status = 'APPROVED';
                
                // Refresh the display
                displayCurrentPage();
                
                // Show success message
                alert('Request approved successfully!');
                
                // Here you would typically make an API call to update the database
                // updateRequestStatus(requestId, 'APPROVED');
            }
        }
    }

    function rejectRequest(requestId) {
        if (confirm('Are you sure you want to reject this course material request?')) {
            // Find the request in the data
            const requestIndex = allRequests.findIndex(req => req.id === requestId);
            if (requestIndex !== -1) {
                // Update the status
                allRequests[requestIndex].status = 'REJECTED';
                
                // Refresh the display
                displayCurrentPage();
                
                // Show success message
                alert('Request rejected successfully!');
                
                // Here you would typically make an API call to update the database
                // updateRequestStatus(requestId, 'REJECTED');
            }
        }
    }
    
    // Back to top functionality
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Remove any duplicate back to top buttons
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('#backToTopBtn');
        if (buttons.length > 1) {
            // Keep only the first one, remove the rest
            for (let i = 1; i < buttons.length; i++) {
                buttons[i].remove();
            }
        }
    });
    
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        const backToTopBtn = document.getElementById('backToTopBtn');
        if (backToTopBtn && window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else if (backToTopBtn) {
            backToTopBtn.classList.remove('show');
        }
    });
    
    // Function to navigate to all courses (with term parameter)
    async function navigateToAllCourses() {
        // Update session with current term selection
        await updateServerSession(selectedTermId);
        // Pass term as URL parameter
        window.location.href = 'content.php?page=all-courses&term_id=' + encodeURIComponent(selectedTermId);
    }
    
    // Function to navigate to program courses (with term parameter)
    async function navigateToProgramCourses(programCode) {
        // Update session with current term selection
        await updateServerSession(selectedTermId);
        // Pass term as URL parameter
        window.location.href = 'content.php?page=course-details&program=' + encodeURIComponent(programCode) + '&term_id=' + encodeURIComponent(selectedTermId);
    }
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="modal" style="display: none; z-index: 10008;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>⚠️ Delete Draft?</h2>
            <span class="close" onclick="if(typeof window.closeDeleteConfirmationModal==='function'){window.closeDeleteConfirmationModal();}else{console.error('closeDeleteConfirmationModal not found');}">&times;</span>
        </div>
        <div class="modal-body">
            <div style="padding: 20px;">
                <p id="deleteConfirmationMessage" style="font-size: 16px; margin-bottom: 15px; color: #333; line-height: 1.6;">
                    Are you sure you want to delete this draft?
                </p>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p id="deleteConfirmationWarning" style="margin: 0; font-size: 14px; color: #856404; line-height: 1.5;">
                        This action cannot be undone. All draft data will be permanently deleted.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-actions" style="justify-content: center; gap: 15px; padding: 20px;">
            <button type="button" class="cancel-btn" onclick="if(typeof window.closeDeleteConfirmationModal==='function'){window.closeDeleteConfirmationModal();}else{console.error('closeDeleteConfirmationModal not found');}" style="padding: 12px 30px; font-size: 16px; background-color: #757575; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
                Cancel
            </button>
            <button type="button" class="delete-confirm-btn" onclick="if(typeof window.confirmDeleteDraft==='function'){window.confirmDeleteDraft();}else{console.error('confirmDeleteDraft not found');}" style="padding: 12px 30px; font-size: 16px; background-color: #dc3545; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; text-transform: uppercase;">
                Delete
            </button>
        </div>
    </div>
</div>

<style>
    /* Delete Confirmation Modal Styles */
    #deleteConfirmationModal {
        position: fixed;
        z-index: 10008;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }
    
    #deleteConfirmationModal .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    
    #deleteConfirmationModal .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    #deleteConfirmationModal .modal-header h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
    }
    
    #deleteConfirmationModal .modal-header .close {
        font-size: 28px;
        font-weight: 300;
        color: #999;
        cursor: pointer;
        line-height: 1;
        transition: color 0.3s ease;
    }
    
    #deleteConfirmationModal .modal-header .close:hover {
        color: #333;
    }
    
    #deleteConfirmationModal .modal-body {
        flex: 1;
        overflow-y: auto;
    }
    
    #deleteConfirmationModal .modal-actions {
        padding: 20px 25px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        gap: 15px;
    }
    
    #deleteConfirmationModal .cancel-btn:hover {
        background-color: #616161 !important;
    }
    
    #deleteConfirmationModal .delete-confirm-btn {
        text-transform: uppercase !important;
    }
    
    #deleteConfirmationModal .delete-confirm-btn:hover {
        background-color: #c62828 !important;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<!-- Floating Back to Top Button -->
<button id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()" aria-label="Back to top">
    <i data-lucide="arrow-up" class="arrow" aria-hidden="true"></i>
    <span class="text">Back to Top</span>
</button> 
