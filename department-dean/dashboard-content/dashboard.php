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
.dashboard-container {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.main-page-title {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.content-wrapper {
    margin-top: 102px !important;
    padding-top: 0 !important;
}
.department-card {
    /* Removed cursor: pointer - cards are no longer clickable */
}
    .program-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Empty Program Card Styling */
    .empty-program-card {
        background: #e3f2fd;
        border: 2px dashed #90caf9;
        cursor: default;
        transition: all 0.3s ease;
    }

    .empty-program-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(33, 150, 243, 0.2);
        border-color: #64b5f6;
    }

    .empty-program-card h3 {
        color: #1976d2;
    }

    .empty-program-card p {
        color: #64b5f6;
        margin-bottom: 15px;
    }

    /* View Details Button Styling */
    .view-details-btn {
        background: #1976d2;
        color: white;
        border: none;
        padding: 6px 18px;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'TT Interphases', sans-serif;
        margin-top: auto;
        align-self: flex-end;
        width: auto;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }

    .view-details-btn:hover {
        background: #1565c0;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
    }

    /* Reference Requests Section Styling */
    .dashboard-section {
        margin-top: 30px;
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        align-self: flex-end;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0;
        padding: 0;
        transition: all 0.3s ease;
        align-items: center;
    }

    .header-left {
        flex: 1;
    }

    .section-description {
        color: #666;
        font-size: 14px;
        margin-top: 4px;
        font-family: 'TT Interphases', sans-serif;
    }

    .header-left h3 {
        margin: 0 0 8px 0;
        color: #333;
        font-size: 20px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
    }

    .section-description {
        font-size: 14px;
        color: #666;
        font-family: 'TT Interphases', sans-serif;
        line-height: 1.4;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .view-all-btn {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #666;
        text-decoration: none;
        transition: all 0.2s ease;
        font-family: 'TT Interphases', sans-serif;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 0;
        margin-bottom: 0;
        margin-left: 0;
        margin-right: 0;
        cursor: pointer;
    }
    
    /* Button container for program management expand/collapse */
    .program-buttons-container {
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 10px;
        margin-top: 20px;
        margin-bottom: 80px;
        text-align: center;
    }

    .view-all-btn:hover {
        color: #1565c0;
        background: #1976d2;
        color: white;
        border-color: #1976d2;
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

    .section-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
        gap: 12px;
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
        font-size: 12px;
        font-weight: bold;
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
        font-size: 14px;
        font-weight: 700;
        font-family: 'TT Interphases', sans-serif;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .collapsed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        margin: 10px 0;
    }

    .collapsed-header-left {
        flex: 1;
    }

    .collapsed-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .request-count {
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        min-width: 20px;
        text-align: center;
        display: inline-block;
        margin-left: 8px;
        vertical-align: middle;
        order: 2;
    }

    .reference-requests-container {
        background: transparent;
        border-radius: 12px;
        padding: 10px;
        margin: 10px 0;
        border: none;
        overflow: visible;
        position: relative;
        width: 100%;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    .reference-requests-grid {
        display: flex;
        gap: 15px;
        padding: 0;
        overflow: visible;
        flex-wrap: nowrap;
        align-items: stretch;
        min-height: 200px;
        justify-content: center;
        width: 100%;
        max-width: none;
        margin: 0 auto;
        margin-top: 20px !important;
    }

    .reference-request-card {
        min-width: 280px;
        max-width: none;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        flex-shrink: 0;
        flex-basis: 280px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        margin: 0;
    }

    .reference-request-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1976d2, #42a5f5, #90caf9);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .reference-request-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        border-color: #1976d2;
    }

    .reference-request-card:hover::before {
        opacity: 1;
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .requester-info {
        flex: 1;
    }

    .requester-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
        font-family: 'TT Interphases', sans-serif;
    }

    .requester-role {
        font-size: 11px;
        color: #1976d2;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 2px;
        font-family: 'TT Interphases', sans-serif;
    }

    .faculty-department {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 4px;
        font-family: 'TT Interphases', sans-serif;
        padding: 0;
        display: inline-block;
    }

    .requester-id {
        font-size: 12px;
        color: #666;
        font-family: 'TT Interphases', sans-serif;
    }

    .priority-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        font-family: 'TT Interphases', sans-serif;
        white-space: nowrap;
        letter-spacing: 0.5px;
    }

    .priority-high {
        background: #FF4C4C;
        color: white;
    }

    .priority-medium {
        background: #FFA500;
        color: white;
    }

    .priority-low {
        background: #4CAF50;
        color: white;
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

    .semester-info {
        font-size: 11px;
        color: #666;
        font-family: 'TT Interphases', sans-serif;
        font-style: italic;
    }





    .request-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .status-badge {
        padding: 6px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        font-family: 'TT Interphases', sans-serif;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff3e0;
        color: #ef6c00;
    }

    .status-pending-approval {
        background: #fff3e0;
        color: #ef6c00;
    }

    .status-approved {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .status-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .status-draft {
        background: #C0C0C0;
        color: white;
    }

    .resume-draft-btn:hover {
        background: #1565c0 !important;
    }

    .delete-draft-btn:hover {
        background: #c82333 !important;
    }

    .request-date {
        font-size: 11px;
        color: #999;
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        margin-top: 8px;
        font-style: italic;
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
    }

    .view-all-btn:hover {
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
    }

    .request-summary {
        margin-bottom: 16px;
        flex: 1;
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
        font-family: 'Georgia', serif;
        line-height: 1.4;
        font-style: italic;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }

    .approve-btn, .reject-btn {
        flex: 1;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .approve-btn {
        background: #4caf50;
        color: white;
    }

    .approve-btn:hover {
        background: #45a049;
        transform: translateY(-1px);
    }

    .reject-btn {
        background: #f44336;
        color: white;
    }

    .reject-btn:hover {
        background: #da190b;
        transform: translateY(-1px);
    }

    .status-display {
        width: 100%;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
        text-align: center;
        text-transform: uppercase;
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .collapsed-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
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

    .nav-icon {
        width: 16px;
        height: 16px;
        object-fit: contain;
    }
        
    .collapse-icon {
        width: 14px;
        height: 14px;
        object-fit: contain;
    }
        
        /* Color effects ONLY for the specific Course Material Requests icons */
        .course-nav-btn .nav-icon,
        .course-nav-btn .collapse-icon {
            filter: brightness(0) saturate(100%);
            transition: filter 0.2s ease;
        }
        
        /* Hover effects ONLY for Course Material Requests navigation and collapse/expand icons */
        .course-nav-btn:hover .nav-icon,
        .course-nav-btn:hover .collapse-icon {
            filter: brightness(0) saturate(100%) invert(1);
        }
        
        /* Course Material Requests Section - Full Width Layout */
        .reference-requests-container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            margin-top: 20px !important;
            box-sizing: border-box !important;
        }
        
        .reference-requests-container .reference-requests-grid {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
            justify-content: flex-start !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .reference-requests-container .reference-request-card {
            flex: 0 0 calc((100% - 60px) / 4) !important;
            min-width: calc((100% - 60px) / 4) !important;
            max-width: calc((100% - 60px) / 4) !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            transition: all 0.3s ease !important;
            width: calc((100% - 60px) / 4) !important;
            flex-grow: 0 !important;
            flex-shrink: 0 !important;
        }
        
        /* Specifically for course proposals grid - ensure fixed width */
        #courseProposalsGrid .reference-request-card {
            width: 280px !important;
            min-width: 280px !important;
            max-width: 280px !important;
            flex: 0 0 280px !important;
            flex-grow: 0 !important;
            flex-shrink: 0 !important;
        }
        
        /* Cards automatically adjust to fill available space */
        .reference-requests-container .reference-request-card:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        
        /* Responsive breakpoints for 4-card layout */
        @media screen and (max-width: 1400px) {
            .reference-requests-container .reference-request-card {
                flex: 0 0 calc(33.333% - 20px) !important;
                min-width: 300px !important;
            }
        }
        
        @media screen and (max-width: 1200px) {
            .reference-requests-container .reference-request-card {
                flex: 0 0 calc(50% - 20px) !important;
            }
        }
        
        @media screen and (max-width: 768px) {
            .reference-requests-container .reference-requests-grid {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            
            .reference-requests-container .reference-request-card {
                flex: 1 1 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                padding: 20px !important;
            }
        }
        
        /* Very small screens */
        @media screen and (max-width: 480px) {
            .reference-requests-container .reference-request-card {
                padding: 16px !important;
                border: 2px solid #2196F3 !important;
            }
        }

        /* Summary line styling */
        .reference-requests-summary {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .summary-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .summary-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: 700;
            color: #1976d2;
        }
        
        .summary-label {
        font-size: 14px;
            color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

        .summary-divider {
            width: 1px;
            height: 40px;
            background: #dee2e6;
        }
    
    .status-approved-btn {
        background: #e8f5e8;
        color: #2e7d32;
        cursor: default;
        opacity: 0.8;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: none;
        font-family: 'TT Interphases', sans-serif;
        border: none;
        width: 100%;
    }
    
    .status-rejected-btn {
        background: #ffebee;
        color: #c62828;
        cursor: default;
        opacity: 0.8;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: none;
        font-family: 'TT Interphases', sans-serif;
        border: none;
        width: 100%;
    }

    /* Floating Back to Top Button Styles */
    .back-to-top-btn {
        position: fixed !important;
        bottom: 30px !important;
        right: 30px !important;
        left: auto !important;
        width: 50px;
        height: 50px;
        background: #1976d2;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        overflow: hidden;
        white-space: nowrap;
    }
    
    /* Ensure no duplicate buttons appear on the left */
    .back-to-top-btn:not(:first-of-type) {
        display: none !important;
    }

    .back-to-top-btn:hover {
        background: #1565c0;
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(25, 118, 210, 0.4);
        width: 140px;
        border-radius: 25px;
    }

    .back-to-top-btn:active {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    }

    .back-to-top-btn.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .back-to-top-btn .arrow {
        width: 20px;
        height: 20px;
        transition: all 0.3s ease;
        position: absolute;
        left: 50%;
        transform: translateX(-50%) rotate(90deg);
        filter: brightness(0) invert(1);
    }

    .back-to-top-btn .text {
        position: absolute;
        left: 50%;
        transform: translateX(-50%) translateX(-10px);
        font-size: 14px;
        font-weight: 500;
        font-family: 'TT Interphases', sans-serif;
        opacity: 0;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .back-to-top-btn:hover .arrow {
        left: 20px;
        transform: translateX(0) rotate(90deg);
        margin-right: 8px;
    }

    .back-to-top-btn:hover .text {
        opacity: 1;
        left: 43px;
        transform: translateX(0);
    }

    /* Responsive floating back to top button */
    @media (max-width: 768px) {
        .back-to-top-btn {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            font-size: 18px;
        }
        
        .back-to-top-btn:hover {
            width: 120px;
        }
        
        .back-to-top-btn .arrow {
            width: 18px;
            height: 18px;
        }
        
        .back-to-top-btn .text {
            font-size: 13px;
        }
    }

    /* Academic Term Selector Styles */
    .term-selector-container {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .term-selector-label {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        font-family: 'TT Interphases', sans-serif;
    }

    .term-dropdown {
        flex: 1;
        max-width: 300px;
        padding: 10px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        font-family: 'TT Interphases', sans-serif;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }

    .term-dropdown:hover {
        border-color: #1976d2;
    }

    .term-dropdown:focus {
        border-color: #1976d2;
        box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    }

    .current-term-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'TT Interphases', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
        white-space: nowrap;
    }

    .current-term-btn:hover {
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
    }

    .current-term-btn:active {
        transform: translateY(0);
    }

    .current-term-btn:disabled {
        background: #9e9e9e;
        color: #ffffff;
        cursor: not-allowed;
        opacity: 0.85;
        box-shadow: none;
    }

    .current-term-btn:disabled:hover {
        background: #9e9e9e;
        color: #ffffff;
        transform: none;
        box-shadow: none;
    }

    .selected-term-display {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #1976d2;
        font-family: 'TT Interphases', sans-serif;
    }

    .selected-term-display .term-icon {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 768px) {
        .term-selector-container {
            flex-direction: column;
            align-items: stretch;
        }

        .term-dropdown {
            max-width: 100%;
        }
    }
    
    /* Loading animation for term selector */
    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
    /* Course Proposal Details Modal */
    .course-proposal-details-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .course-proposal-details-modal .modal-content-details {
        animation: slideUp 0.3s ease;
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
    
    .view-attachment-btn:hover {
        background: #1565c0 !important;
    }
    
    .download-attachment-btn:hover {
        background: #45a049 !important;
    }
    
    .close-modal:hover {
        color: #333 !important;
    }
</style>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; margin-top: 120px;">
  <div>
                 <div style="font-size: 1.15rem; font-weight: bold; color: #053423;">Panagdait Department Dean of <?php echo htmlspecialchars($departmentName); ?></div>
    <div style="font-size: 1.05rem; color: #222;"><?php echo htmlspecialchars($deanName); ?></div>
  </div>
  <div style="display: flex; align-items: center;">
    <span style="display: inline-block; background: <?php echo htmlspecialchars($departmentColor); ?>; color: #fff; font-weight: bold; border-radius: 8px; padding: 8px 18px; font-size: 1.1rem; letter-spacing: 1px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);"><?php echo htmlspecialchars($departmentCode); ?></span>
  </div>
</div>

<!-- Overview Header with Academic Term Selector -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; margin-top: 30px; gap: 20px;">
    <h2 class="main-page-title" style="padding-left: 0px; margin: 0; line-height: 1;">Overview</h2>
    
    <!-- Academic Term Selector -->
    <div class="term-selector-container" style="margin: 0; display: flex; align-items: center;">
        <label class="term-selector-label" for="academicTermSelect" style="margin-bottom: 0;">Academic Term:</label>
        <select id="academicTermSelect" class="term-dropdown" style="margin-top: 0; margin-bottom: 0;" onchange="handleTermChangeFromDropdown()">
            <option value="">Select a term...</option>
            <option value="all" <?php echo ($selectedTermId === 'all') ? 'selected' : ''; ?>>All Terms (Current Academic Year)</option>
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
        <button id="currentTermBtn" class="current-term-btn" onclick="selectCurrentTerm()" style="margin-top: 0; margin-bottom: 0;">
            Current Term
        </button>
    </div>
</div>

<div class="dashboard-container" style="margin-top: 15px;">
  <div class="box stat-box stat-box-row">
    <span class="stat-title">Programs</span>
    <span class="stat-amount"><?php echo $totalPrograms; ?></span>
  </div>
  <div class="box stat-box stat-box-row">
    <span class="stat-title">Courses</span>
    <span class="stat-amount"><?php echo $totalCourses; ?></span>
  </div>
  <div class="box stat-box stat-box-row">
    <span class="stat-title">Faculty Members</span>
    <span class="stat-amount"><?php echo $totalFaculty; ?></span>
  </div>
</div>

<!-- Review Course Material Requests Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="header-left">
            <h3>Review Course Material Requests</h3>
            <div class="section-description">Review and manage all course material requests from faculty.</div>
    </div>
        <div class="header-actions">
            <a href="content.php?page=reference-requests" class="view-all-btn">View All</a>
            <button class="nav-btn prev-btn course-nav-btn" id="prevBtn" onclick="showPreviousRequests()">
                <img src="../src/assets/icons/left-arrow-icon.png" alt="Previous" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display: none;">&lt;</span>
                            </button>
            <button class="nav-btn next-btn course-nav-btn" id="nextBtn" onclick="showNextRequests()">
                <img src="../src/assets/icons/right-arrow-icon.png" alt="Next" class="nav-icon" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display: none;">&gt;</span>
            </button>
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
            <img src="../src/assets/icons/right-arrow-icon.png" alt="Collapse" class="collapse-icon" style="transform: rotate(-90deg);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
            <span style="display: none;">^</span>
        </button>
    </div>
</div>

<!-- Course Proposals & Revisions Section -->
<div class="dashboard-section">
    <div class="section-header">
        <div class="header-left">
            <h3>Course Proposals & Revisions</h3>
            <div class="section-description">Review and manage new course proposals and course revision requests from faculty.</div>
        </div>
        <div class="header-actions">
            <a href="content.php?page=course-proposals" class="view-all-btn">View All</a>
        </div>
    </div>
    
    <div class="reference-requests-container" style="padding: 10px; margin: 10px 0; position: relative;">
        <div class="reference-requests-grid" id="courseProposalsGrid" style="min-height: 230px; flex-wrap: wrap; justify-content: flex-start; gap: 20px; align-items: flex-start;">
            <!-- Course proposal cards will be dynamically generated by JavaScript -->
        </div>
        
        <!-- Empty state -->
        <div id="courseProposalsEmptyState" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; padding: 0; color: #666; width: 100%;">
            <div style="font-size: 28px; margin-bottom: 8px;">📝</div>
            <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 15px; color: #333; margin-bottom: 6px;">No Course Proposals</h3>
            <p style="font-family: 'TT Interphases', sans-serif; font-size: 13px; color: #666; margin: 0;">
                View and track the Department Dean's submitted new courses and course revisions for QA review.
            </p>
        </div>
    </div>
</div>

<div class="departments-section">
    <div class="departments-header">
        <div>
            <h3>Program & Courses Management</h3>
            <p>Manage academic programs and their configurations</p>
        </div>
        <button class="add-dept-btn" id="addProgramButton" style="display: block;">Add Program</button>
    </div>

    <div class="departments-container" id="programContainer">
        <!-- All Courses Card -->
        <div class='department-card all-courses-card'>
            <div class='dept-code' style='background-color: #6c757d;'>ALL</div>
            <h3>All Courses</h3>
            <p style='font-weight: bold; color: #333;'>View All Courses</p>
            <button class='view-details-btn' onclick="window.location.href='content.php?page=all-courses'">View Details</button>
        </div>
        
        <?php
        if (!empty($programs)) {
            $index = 0;
            foreach ($programs as $program) {
                $hidden = ($index >= 5) ? "hidden" : "";
                echo "<div class='department-card " . htmlspecialchars($hidden) . "'>";
                echo "<div class='dept-code' style='background-color: " . htmlspecialchars($program['color_code']) . "'>" . htmlspecialchars($program['program_code']) . "</div>";
                echo "<h3>" . htmlspecialchars($program['program_name']) . "</h3>";
                echo "<p style='font-weight: bold; color: #333;'>" . htmlspecialchars($program['course_count']) . " Courses</p>";
                echo "<button class='view-details-btn' onclick=\"window.location.href='content.php?page=program-courses&program=" . urlencode($program['program_code']) . "'\">View Courses</button>";
                echo "</div>";
                $index++;
            }
        } else {
            // No programs found - show empty state card
            echo "<div class='department-card empty-program-card'>";
            echo "<div style='display: flex; justify-content: space-between; align-items: center;'><div class='dept-code' style='background-color: #1976d2; color: white; font-weight: bold;'>NEW</div><span style='font-size: 1.5rem;'>📁</span></div>";
            echo "<h3>No Programs Yet</h3>";
            echo "<p style='font-weight: bold; color: #333;'>Start building your programs</p>";
            echo "<button class='view-details-btn' onclick='openAddProgramModal()'>Create First Program</button>";
            echo "</div>";
        }
        ?>
    </div>

    <div class="program-buttons-container">
        <button class="view-all-btn" id="viewAllProgramsButton" 
                style="display: <?php echo (count($programs) > 5) ? 'inline-flex' : 'none'; ?>;">Expand to View More</button>
        <button class="view-all-btn" id="collapseProgramsButton" 
                style="display: none;">Collapse to View Less</button>
    </div>
</div>




<script>
    // This variable will be accessed by scripts/program-management.js
    const programs = <?php echo json_encode($programs); ?>;
    const recentActivities = <?php echo json_encode($recentActivities); ?>;
    
    // Set hasPrograms variable for the global checkProgramsAndOpenCourseModal function
    // Always allow course creation regardless of filtered program count
    const hasPrograms = true;
    
    // Academic terms data
    const academicTerms = <?php echo json_encode($academicTerms); ?>;
    const currentAcademicTerm = <?php echo json_encode($currentAcademicTerm); ?>;
    
    // Selected term ID (stored in session storage for persistence)
    // Default to current term if no session storage exists
    let selectedTermId = sessionStorage.getItem('selectedTermId') || (currentAcademicTerm ? String(currentAcademicTerm.id) : null);
    
    // Handle term selection change
    async function handleTermChange(termId) {
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
            
            // Show notification
            showTermChangeNotification('All Terms (Current Academic Year)');
            
            // Refresh dashboard data for all terms
            refreshDashboardData(termId);
        } else {
            // Find the selected term
            const selectedTerm = academicTerms.find(t => t.id == termId);
            if (selectedTerm) {
                
                // Show notification
                showTermChangeNotification(selectedTerm.display_name);
                
                // Refresh dashboard data for the selected term
                refreshDashboardData(termId);
            } else {
                console.error('Term not found:', termId);
            }
        }
        
        updateSelectedTermDisplay();
        updateCurrentTermButtonState();
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
    function refreshDashboardData(termId) {
        
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
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update dashboard statistics
                updateDashboardStats(data.stats);
                
                // Update Program & Courses Management section
                if (data.programs) {
                    updateProgramsSection(data.programs);
                } else {
                }
                
                // Update course material requests
                updateCourseMaterialRequests(data.requests);
                
                // Update selected term display
                updateSelectedTermDisplay();
                
            } else {
                console.error('Error refreshing dashboard data:', data.message);
                // Fallback: reload the page
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard data:', error);
            // Fallback: reload the page
            window.location.reload();
        })
        .finally(() => {
            hideLoadingIndicator();
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
                    <span style='font-size: 1.5rem;'>📁</span>
                </div>
                <h3>No Programs Yet</h3>
                <p style='font-weight: bold; color: #333;'>Start building your programs</p>
                <button class='view-details-btn' onclick='openAddProgramModal()'>Create First Program</button>
            `;
            programContainer.appendChild(emptyCard);
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
                <span style="font-size: 20px;">📅</span>
                <div>
                    <div style="font-weight: 700; margin-bottom: 4px;">Term Selected</div>
                    <div style="font-weight: 500; opacity: 0.9;">${termName}</div>
                </div>
            </div>
        `;
        
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
                <img src="../src/assets/icons/right-arrow-icon.png" alt="Expand" class="collapse-icon" style="transform: rotate(90deg);">
            </button>
        `;
        
        // Insert the collapsed controls in the same header area
        const sectionHeader = section.querySelector('.section-header');
        sectionHeader.appendChild(collapsedControls);
        
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
            handleTermChangeFromDropdown();
        });
        
        // Load data for the selected term on page load
        if (selectedTermId) {
            // Update server session first, then refresh data
            updateServerSession(selectedTermId).then(() => {
                refreshDashboardData(selectedTermId);
            });
        }
    }
        
        // Display current selected term info and update button state
        updateSelectedTermDisplay();
        updateCurrentTermButtonState();
        
        // Debug icon paths
        const testImg = new Image();
        testImg.onload = function() {
        };
        testImg.onerror = function() {
            
            // Try alternative paths
            const altPaths = [
                '../src/assets/icons/left-arrow-icon.png',
                '../../src/assets/icons/left-arrow-icon.png',
                'src/assets/icons/left-arrow-icon.png',
                '/src/assets/icons/left-arrow-icon.png'
            ];
            
            altPaths.forEach((path, index) => {
                const testAlt = new Image();
                testAlt.onload = function() {
                };
                testAlt.onerror = function() {
                };
                testAlt.src = path;
            });
        };
        testImg.src = '../src/assets/icons/left-arrow-icon.png';
        
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
            
            if (data.debug) {
            }
            
            // Store data globally for access by viewCourseProposalDetails
            window.courseProposalsData = proposals;
        
        // Clear existing content
            proposalsGrid.innerHTML = '';
            
            if (proposals.length === 0) {
                
                // Show empty state
                emptyState.style.display = 'block';
                proposalsGrid.style.minHeight = '230px';
                
                // If debug info shows drafts exist, show a message
                if (data.debug && data.debug.total_drafts_in_db > 0) {
                    console.error('ISSUE DETECTED: Drafts exist in DB but not returned!');
                    console.error('Debug info:', data.debug);
                    proposalsGrid.innerHTML = '<div style="text-align: center; padding: 20px; color: #f44336;"><p>Drafts exist in database but could not be loaded.</p></div>';
                }
            } else {
                // Hide empty state
                emptyState.style.display = 'none';
        
                // Create and append cards
                proposals.forEach(function(cardData, index) {
                    try {
                        var card = createCourseProposalCard(cardData);
                        if (card) {
                            proposalsGrid.appendChild(card);
                        } else {
                            console.error('Card ' + (index + 1) + ' creation returned null/undefined');
                        }
                    } catch (error) {
                        console.error('Error creating card ' + (index + 1) + ':', error, cardData);
                    }
                });
        
                // Ensure grid is set up to match other cards
                proposalsGrid.style.flexWrap = 'wrap';
                proposalsGrid.style.justifyContent = 'flex-start';
                proposalsGrid.style.gap = '20px';
                proposalsGrid.style.width = '100%';
                proposalsGrid.style.maxWidth = '100%';
            
                // Adjust card widths to match other reference request cards (fixed 250px)
                var cards = proposalsGrid.querySelectorAll('.reference-request-card');
                cards.forEach(function(card) {
                    // Force fixed width - override any CSS that might make it grow
                    card.style.minWidth = '250px';
                    card.style.maxWidth = '250px';
                    card.style.width = '250px';
                    card.style.flex = '0 0 250px';
                    card.style.flexGrow = '0';
                    card.style.flexShrink = '0';
                    card.style.flexBasis = '250px';
                    card.style.boxSizing = 'border-box';
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
        
        // Ensure card has consistent sizing with other cards (same as when 4 cards)
        card.style.minWidth = '250px';
        card.style.maxWidth = '250px'; // Fixed width to match other cards
        card.style.flex = '0 0 250px';
        card.style.flexGrow = '0'; // Don't grow - keep fixed width
        card.style.flexShrink = '0'; // Don't shrink
        card.style.flexBasis = '250px';
        card.style.width = '250px'; // Fixed width
        card.style.boxSizing = 'border-box'; // Include padding in width
        
        // Check if this is a draft
        const isDraft = cardData.isDraft === true || cardData.status === 'Draft' || cardData.status.toLowerCase().includes('draft');
        
        // Determine status class based on status text
        let statusClass = 'status-pending';
        if (isDraft) {
            statusClass = 'status-draft';
        } else if (cardData.status.toLowerCase().includes('approved') || cardData.status.toLowerCase().includes('added')) {
            statusClass = 'status-approved';
        } else if (cardData.status.toLowerCase().includes('rejected')) {
            statusClass = 'status-rejected';
        } else if (cardData.status.toLowerCase().includes('review') || cardData.status.toLowerCase().includes('pending')) {
            statusClass = 'status-pending';
        }
        
        // Format date
        const date = new Date(cardData.submittedDate || cardData.createdAt || new Date());
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Get course information - use first course if courses array exists, otherwise use direct properties
        const courseCode = cardData.courseCode || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseCode : 'N/A');
        const courseName = cardData.courseName || (cardData.courses && cardData.courses.length > 0 ? cardData.courses[0].courseName : 'N/A');
        
        // Get type color
        let typeColor = '#1976d2';
        if (cardData.courseType === 'Cross-Department') {
            typeColor = '#42a5f5';
        } else if (cardData.courseType === 'Course Revision') {
            typeColor = '#66bb6a';
        }
        
        // Buttons - Resume/Delete for drafts, View Details for submitted
        const actionButtons = isDraft ? `
            <div style="display: flex; gap: 8px; width: 100%; margin-bottom: 8px;">
                <button class="resume-draft-btn" onclick="event.stopPropagation(); resumeDraft('${cardData.id}', event);" style="flex: 1; background: #1976d2; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'TT Interphases', sans-serif;">
                    Resume
                </button>
                <button class="delete-draft-btn" onclick="event.stopPropagation(); deleteDraft('${cardData.id}', event);" style="flex: 1; background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'TT Interphases', sans-serif;">
                    Delete
                </button>
            </div>
        ` : `
            <button class="view-details-btn" onclick="event.stopPropagation(); viewCourseProposalDetails('${cardData.id}');" style="width: 100%; margin-bottom: 8px;">
                View Details
            </button>
        `;
        
        card.innerHTML = `
            <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name" style="color: ${typeColor}; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center;">
                        ${cardData.courseType || 'Course Proposal'}
                    </div>
                    <div class="faculty-department" style="color: #666; font-size: 11px;">
                        ${cardData.programCode || 'N/A'} Program
                    </div>
                </div>
            </div>
            
            <div class="course-info" style="margin-bottom: 12px;">
                <div class="course-code" style="background: #1976d2; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; font-family: 'TT Interphases', sans-serif; display: inline-block; margin-bottom: 8px;">
                    ${courseCode}
                </div>
                <div class="course-name" style="font-size: 13px; color: #666; font-family: 'TT Interphases', sans-serif;">
                    ${courseName}
                </div>
            </div>
            
            <div class="request-summary" style="margin-bottom: 16px; flex: 1;">
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; flex-wrap: wrap;">
                    ${cardData.totalReferences > 0 ? `
                        <div class="references-indicator" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
                            <span style="font-size: 14px;">📚</span>
                            <span>${cardData.totalReferences} reference${cardData.totalReferences > 1 ? 's' : ''}</span>
                        </div>
                    ` : ''}
                    ${cardData.totalAttachments > 0 ? `
                        <div class="attachments-indicator" style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #666;">
                            <span style="font-size: 14px;">📎</span>
                            <span>${cardData.totalAttachments} attachment${cardData.totalAttachments > 1 ? 's' : ''}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="status-display ${statusClass}" style="margin-bottom: 12px; width: 100%; box-sizing: border-box;">
                ${isDraft ? 'Draft' : cardData.status}
            </div>
            
            ${actionButtons}
            
            <div class="request-date">${isDraft ? 'Drafted on: ' : 'Submitted on: '}${formattedDate}</div>
        `;
        
        // Add click handler to view details
        card.addEventListener('click', function() {
            // TODO: Navigate to details page or open modal
        });
        
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
        
        // Get file icon
        function getFileIcon(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            const icons = {
                'pdf': '📄',
                'doc': '📝',
                'docx': '📝',
                'xls': '📊',
                'xlsx': '📊',
                'txt': '📄',
                'jpg': '🖼️',
                'jpeg': '🖼️',
                'png': '🖼️',
                'gif': '🖼️'
            };
            return icons[ext] || '📎';
        }
        
        // Build attachments HTML
        let attachmentsHTML = '';
        if (proposal.attachments && proposal.attachments.length > 0) {
            attachmentsHTML = proposal.attachments.map((attachment, index) => `
                <div class="attachment-item-detail" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f9f9f9; border-radius: 6px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <span style="font-size: 24px;">${getFileIcon(attachment.name)}</span>
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

        // Update navigation buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const totalRequests = allRequests.length;
        const totalPages = Math.ceil(totalRequests / requestsPerPage);

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

        // Hide both buttons if there's only one page or no requests
        if (totalRequests <= requestsPerPage) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }

    function showNextRequests() {
            currentPage++;
            displayCurrentPage();
    }

    function showPreviousRequests() {
            currentPage--;
            displayCurrentPage();
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
        
        // Get department code and color from session or use default
        const departmentCode = '<?php echo $_SESSION["selected_role"]["department_code"] ?? "CCS"; ?>';
        const departmentColor = '<?php echo $_SESSION["selected_role"]["department_color"] ?? "#1976d2"; ?>';
        
        const card = document.createElement('div');
        card.className = 'reference-request-card';
        card.setAttribute('data-request-id', request.id);
        
        card.innerHTML = `
                <div class="request-header">
                <div class="requester-info">
                    <div class="requester-name">${request.requester_name}</div>
                    <div class="faculty-department" style="color: ${departmentColor};">${departmentCode} FACULTY</div>
                    </div>
                </div>
                
            <div class="course-info">
                <div class="course-code">${request.course_code}</div>
                <div class="course-name">${request.course_name}</div>
                </div>
                
            <div class="request-summary">
                <div class="request-type">Course Material Request</div>
                <div class="material-title">${apaCitation}</div>
                    </div>
            
            <div class="action-buttons">
                <button class="approve-btn" onclick="approveRequest(${request.id})">Approve</button>
                <button class="reject-btn" onclick="rejectRequest(${request.id})">Reject</button>
                    </div>
                <div class="request-date">Requested on: ${new Date().toLocaleDateString()}</div>
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
<button id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()">
    <img src="../src/assets/icons/go-back-icon.png" alt="Back to Top" class="arrow">
    <span class="text">Back to Top</span>
</button> 