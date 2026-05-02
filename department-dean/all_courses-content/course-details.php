<?php
// course-details.php for Department Dean
// This page displays detailed course information and book references

// Get URL parameters
$courseCode = $_GET['course_code'] ?? $_GET['course'] ?? '';
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
$courseTitle = $_GET['course_title'] ?? '';
$programCode = $_GET['program'] ?? '';
$fromProgram = $_GET['from_program'] ?? '';


// If program parameter is provided, show all courses for that program
if (!empty($programCode)) {
    // This will be handled in the program courses section
    $showProgramCourses = true;
} elseif (empty($courseCode)) {
    // Redirect back to all courses if no course code provided
    echo '<script>window.location.href = "content.php?page=all-courses";</script>';
    exit;
} else {
    $showProgramCourses = false;
}

// Database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Get the current dean's department code from session for filtering
$deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;

// Fetch course details from database
$courseDetails = null;
try {
    // If course_id is provided, use it directly (more reliable)
    if ($courseId) {
        $courseQuery = "
            SELECT 
                c.id,
                c.course_code,
                c.course_title,
                c.units,
                c.program_id,
                c.faculty_id,
                c.status,
                c.term,
                COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                c.year_level,
                c.created_by,
                c.created_at,
                c.updated_at,
                CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
                p.program_code,
                p.program_name,
                p.major,
                d.color_code
            FROM courses c
            LEFT JOIN users u ON c.faculty_id = u.id
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN school_years sy ON c.academic_year = sy.id
            WHERE c.id = ?
        ";
        $courseStmt = $pdo->prepare($courseQuery);
        $courseStmt->execute([$courseId]);
        $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fallback to course_code if course_id not provided
        $courseQuery = "
            SELECT 
                c.id,
                c.course_code,
                c.course_title,
                c.units,
                c.program_id,
                c.faculty_id,
                c.status,
                c.term,
                COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                c.year_level,
                c.created_by,
                c.created_at,
                c.updated_at,
                CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
                p.program_code,
                p.program_name,
                p.major,
                d.color_code
            FROM courses c
            LEFT JOIN users u ON c.faculty_id = u.id
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN school_years sy ON c.academic_year = sy.id
            WHERE c.course_code = ? 
            AND p.department_id = (SELECT id FROM departments WHERE department_code = ?)
        ";
        $courseStmt = $pdo->prepare($courseQuery);
        $courseStmt->execute([$courseCode, $deanDepartmentCode]);
        $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
}

// If showing program courses, fetch all courses for the program
$programCourses = [];
$programInfo = null;
$allPrograms = []; // For edit course modal
if ($showProgramCourses) {
    try {
        // First get program information - filter by department code to ensure we only get programs from this dean's department
        $programQuery = "SELECT p.id, p.program_code, p.program_name, p.major, d.color_code 
                        FROM programs p 
                        JOIN departments d ON p.department_id = d.id 
                        WHERE p.program_code = ? AND d.department_code = ?";
        $programStmt = $pdo->prepare($programQuery);
        $programStmt->execute([$programCode, $deanDepartmentCode]);
        $programInfo = $programStmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch courses even if program info is not found (in case of data inconsistency)
        // Get selected term from URL parameter first, then session, then default to 'all'
        $selectedTermId = $_GET['term_id'] ?? $_SESSION['selectedTermId'] ?? 'all';
        
        // Update session with the term ID for consistency
        $_SESSION['selectedTermId'] = $selectedTermId;
        
        // Build query based on selected term - fetch courses regardless of programInfo
        if ($selectedTermId === 'all') {
            // Show all courses for this program
            $coursesQuery = "
                SELECT 
                    c.id,
                    c.course_code,
                    c.course_title,
                    c.units,
                    c.program_id,
                    c.faculty_id,
                    c.status,
                    c.term,
                    COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                    c.year_level,
                    c.created_by,
                    c.created_at,
                    c.updated_at,
                    CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
                    p.program_code,
                    p.program_name,
                    d.color_code
                FROM courses c
                LEFT JOIN users u ON c.faculty_id = u.id
                LEFT JOIN programs p ON c.program_id = p.id
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN school_years sy ON c.academic_year = sy.id
                WHERE p.program_code = ? 
                AND p.department_id = (SELECT id FROM departments WHERE department_code = ?)
                ORDER BY c.course_code, c.year_level
            ";
            $coursesStmt = $pdo->prepare($coursesQuery);
            $coursesStmt->execute([$programCode, $deanDepartmentCode]);
        } else {
            // Show courses for specific term
            $termQuery = "SELECT name FROM terms WHERE id = ?";
            $termStmt = $pdo->prepare($termQuery);
            $termStmt->execute([$selectedTermId]);
            $termData = $termStmt->fetch(PDO::FETCH_ASSOC);
            $termName = $termData['name'] ?? null;
            
            if ($termName) {
                $coursesQuery = "
                        SELECT 
                            c.id,
                            c.course_code,
                            c.course_title,
                            c.units,
                            c.program_id,
                            c.faculty_id,
                            c.status,
                            c.term,
                            COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                            c.year_level,
                            c.created_by,
                            c.created_at,
                            c.updated_at,
                            CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
                            p.program_code,
                            p.program_name,
                            d.color_code
                        FROM courses c
                        LEFT JOIN users u ON c.faculty_id = u.id
                        LEFT JOIN programs p ON c.program_id = p.id
                        LEFT JOIN departments d ON p.department_id = d.id
                        LEFT JOIN school_years sy ON c.academic_year = sy.id
                        WHERE p.program_code = ? AND c.term = ? 
                        AND p.department_id = (SELECT id FROM departments WHERE department_code = ?)
                        ORDER BY c.course_code, c.year_level
                    ";
                    $coursesStmt = $pdo->prepare($coursesQuery);
                    $coursesStmt->execute([$programCode, $termName, $deanDepartmentCode]);
                } else {
                    // If term not found, show no courses
                    $coursesStmt = $pdo->prepare("SELECT * FROM courses WHERE 1=0");
                    $coursesStmt->execute();
                }
            }
            
            // Fetch courses regardless of programInfo
            $programCourses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all programs for the edit course modal
            $allProgramsQuery = "SELECT id, program_code, program_name, d.color_code FROM programs p JOIN departments d ON p.department_id = d.id ORDER BY program_code";
            $allProgramsStmt = $pdo->prepare($allProgramsQuery);
            $allProgramsStmt->execute();
            $allPrograms = $allProgramsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
    }
}

// Only redirect if we're looking for a specific course and it's not found
if (!$showProgramCourses && !$courseDetails) {
    // Course not found, redirect back
    // Redirect to all courses if no program code provided
    // echo '<script>window.location.href = "content.php?page=all-courses";</script>';
    // exit;
}

// If showing program courses but program not found, redirect back
if ($showProgramCourses && !$programInfo) {
    // Redirect to all courses if no program code provided
    // echo '<script>window.location.href = "content.php?page=all-courses";</script>';
    // exit;
}

// Program information is now included in the course details query above

// Fetch book references for this course (only if showing individual course)
$bookReferences = [];
if (!$showProgramCourses && $courseDetails) {
    try {
        // Use course_code from URL or from courseDetails as fallback
        $searchCourseCode = $courseCode ?: ($courseDetails['course_code'] ?? '');
        
        // Debug logging
        
        if (!empty($searchCourseCode)) {
            // Get the course_id to use for querying
            $courseIdForQuery = $courseId ?? ($courseDetails['id'] ?? null);
            
            
            // Method 1: If we have a specific course_id, use it directly (matches how program-courses counts)
            if ($courseIdForQuery) {
                $refQuery = "
                    SELECT 
                        br.id,
                        br.book_title AS title,
                        br.isbn,
                        br.publisher,
                        br.publication_year AS copyright_year,
                        br.edition,
                        br.location,
                        br.call_number,
                        br.author AS author,
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
                
                $refStmt = $pdo->prepare($refQuery);
                $refStmt->execute([$courseIdForQuery]);
                $bookReferences = $refStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Try to get processing_status and status_reason separately if columns exist
                if (!empty($bookReferences)) {
                    try {
                        $statusQuery = "
                            SELECT id, processing_status, status_reason
                            FROM book_references
                            WHERE course_id = ?
                        ";
                        $statusStmt = $pdo->prepare($statusQuery);
                        $statusStmt->execute([$courseIdForQuery]);
                        $statusData = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Merge status data into book references
                        foreach ($bookReferences as &$ref) {
                            $statusInfo = array_filter($statusData, function($s) use ($ref) {
                                return $s['id'] == $ref['id'];
                            });
                            if (!empty($statusInfo)) {
                                $statusInfo = array_values($statusInfo)[0];
                                $ref['processing_status'] = $statusInfo['processing_status'] ?? null;
                                $ref['status_reason'] = $statusInfo['status_reason'] ?? null;
                            } else {
                                $ref['processing_status'] = null;
                                $ref['status_reason'] = null;
                            }
                        }
                    } catch (Exception $e) {
                        // Columns don't exist yet, set to null
                        foreach ($bookReferences as &$ref) {
                            $ref['processing_status'] = null;
                            $ref['status_reason'] = null;
                        }
                    }
                }
                
            }
            
            // Method 2: If no results, try finding all courses with same course_code and get their references
            if (empty($bookReferences)) {
                $courseIdsQuery = "SELECT id FROM courses WHERE course_code = ?";
                $courseIdsStmt = $pdo->prepare($courseIdsQuery);
                $courseIdsStmt->execute([$searchCourseCode]);
                $matchingCourseIds = $courseIdsStmt->fetchAll(PDO::FETCH_COLUMN);
                
                
                if (!empty($matchingCourseIds)) {
                    $placeholders = implode(',', array_fill(0, count($matchingCourseIds), '?'));
                    $refQuery = "
                        SELECT 
                            br.id,
                            br.book_title AS title,
                            br.isbn,
                            br.publisher,
                            br.publication_year AS copyright_year,
                            br.edition,
                            br.location,
                            br.call_number,
                            br.author AS author,
                            COALESCE(uc.first_name, '') AS created_by_first_name,
                            COALESCE(uc.last_name, '') AS created_by_last_name,
                            COALESCE(ur.first_name, '') AS requested_by_first_name,
                            COALESCE(ur.last_name, '') AS requested_by_last_name
                        FROM book_references br
                        LEFT JOIN users uc ON br.created_by = uc.id
                        LEFT JOIN users ur ON br.requested_by = ur.id
                        WHERE br.course_id IN ($placeholders)
                        ORDER BY br.book_title ASC
                    ";
                    $refStmt = $pdo->prepare($refQuery);
                    $refStmt->execute($matchingCourseIds);
                    $bookReferences = $refStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Try to get processing_status and status_reason separately if columns exist
                    if (!empty($bookReferences)) {
                        try {
                            $statusQueryPlaceholders = implode(',', array_fill(0, count($matchingCourseIds), '?'));
                            $statusQuery = "
                                SELECT id, processing_status, status_reason
                                FROM book_references
                                WHERE course_id IN ($statusQueryPlaceholders)
                            ";
                            $statusStmt = $pdo->prepare($statusQuery);
                            $statusStmt->execute($matchingCourseIds);
                            $statusData = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Merge status data into book references
                            foreach ($bookReferences as &$ref) {
                                $statusInfo = array_filter($statusData, function($s) use ($ref) {
                                    return $s['id'] == $ref['id'];
                                });
                                if (!empty($statusInfo)) {
                                    $statusInfo = array_values($statusInfo)[0];
                                    $ref['processing_status'] = $statusInfo['processing_status'] ?? null;
                                    $ref['status_reason'] = $statusInfo['status_reason'] ?? null;
                                } else {
                                    $ref['processing_status'] = null;
                                    $ref['status_reason'] = null;
                                }
                            }
                        } catch (Exception $e) {
                            // Columns don't exist yet, set to null
                            foreach ($bookReferences as &$ref) {
                                $ref['processing_status'] = null;
                                $ref['status_reason'] = null;
                            }
                        }
                    }
                    
                }
            }
        }
    } catch (Exception $e) {
    }
    
            // Format names
            foreach ($bookReferences as &$ref) {
                if (isset($ref['created_by_first_name'])) {
                    $ref['created_by_name'] = trim($ref['created_by_first_name'] . ' ' . $ref['created_by_last_name']);
                    $ref['requested_by_name'] = trim($ref['requested_by_first_name'] . ' ' . $ref['requested_by_last_name']);
                    unset($ref['created_by_first_name'], $ref['created_by_last_name'], $ref['requested_by_first_name'], $ref['requested_by_last_name']);
                }
            }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showProgramCourses ? 'Program Courses - ' . htmlspecialchars($programInfo['program_name']) : 'Course Details - ' . htmlspecialchars($courseDetails['course_code']); ?></title>
    <link rel="stylesheet" href="../super_admin-mis/styles/global.css">
    <link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
    <link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
    <style>
        /* ====== DESIGN.md COMPLIANT STYLES ====== */
        body {
            background: #EFEFEF;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .content-wrapper {
            padding: 0 !important;
        }
        
        .content-wrapper .content-wrapper {
            margin: 0 auto !important;
            padding: 20px 24px !important;
            max-width: 1200px !important;
            width: 100% !important;
        }
        
        div.course-details-container {
            width: 90% !important;
            max-width: 90% !important;
            margin: 0 auto !important;
            padding: 0 !important;
            animation: fadeSlideUp 0.45s ease-out both;
            display: block !important;
            position: relative !important;
            left: 0 !important;
            right: 0 !important;
        }
        
        /* Back Button - DESIGN.md btn-ghost pattern */
        .back-button {
            display: inline-flex;
            align-items: center;
            background: transparent;
            border: none;
            color: #0C4B34;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            border-radius: 0;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'TT Interphases', sans-serif;
            margin-bottom: 16px;
            gap: 4px;
        }
        
        .back-button:hover {
            color: #0a3a28;
            transform: translateX(-4px);
            text-decoration: none;
        }
        
        .back-button img {
            width: 18px;
            height: 18px;
            margin-right: 6px;
        }
        
        /* Course Header - DESIGN.md Card with top accent and hover */
        .course-header {
            background: #ffffff;
            border-radius: 16px 18px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            padding: 22px 24px;
            margin-bottom: 20px;
            transition: all 0.28s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }
        
        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
            border-radius: 18px 18px 0 0;
        }
        
        .course-header:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
            border-color: rgba(12, 75, 52, 0.25);
        }
        
        .course-title-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            gap: 16px;
        }
        
        .course-title {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            margin: 0;
            font-family: 'TT Interphases', sans-serif;
            letter-spacing: -0.5px;
        }
        
        .course-subtitle {
            font-size: 15px;
            color: rgba(17, 24, 39, 0.6);
            margin: 0 0 20px 0;
            font-weight: 500;
        }
        
        /* Course Actions - DESIGN.md buttons */
        .course-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .edit-course-btn, .assign-course-btn {
            background: #0C4B34;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .edit-course-btn:hover, .assign-course-btn:hover {
            background: #0a3a28;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
        }
        
        .edit-course-btn:active, .assign-course-btn:active {
            transform: translateY(0) scale(0.98);
        }
        
        .assign-course-btn {
            background: #0F7A53;
        }
        
        .assign-course-btn:hover {
            background: #0a5f42;
        }
        
        /* Course Info Grid - DESIGN.md responsive */
        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        
        .info-label {
            font-size: 11px;
            font-weight: 700;
            color: rgba(17, 24, 39, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            white-space: nowrap;
        }
        
        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Stat Pill for info items */
        .info-item .info-value {
            background: rgba(12, 75, 52, 0.04);
            border: 1px solid rgba(12, 75, 52, 0.08);
            color: rgba(17, 24, 39, 0.6);
            padding: 8px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        
        .info-item .info-value strong {
            color: #0C4B34;
            font-weight: 800;
            font-size: 14px;
            margin-right: 4px;
        }
        
        /* Program Badge - DESIGN.md code badge */
        .program-badge {
            display: inline-block;
            background: #0C4B34;
            color: white;
            padding: 5px 9px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Book References Section - DESIGN.md card */
        .book-references-section {
            background: #ffffff;
            border-radius: 16px 18px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            animation: fadeSlideUp 0.45s ease-out both;
        }
        
        .book-references-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
            border-radius: 18px 18px 0 0;
        }
        
        /* Departments Header */
        .departments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .departments-header h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0C4B34;
            margin: 0 0 4px 0;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .departments-header p {
            font-size: 12px;
            color: rgba(17, 24, 39, 0.5);
            margin: 0;
            font-weight: 600;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .add-dept-btn {
            background: #0F7A53;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            font-family: 'TT Interphases', sans-serif;
            flex-shrink: 0;
        }
        
        .add-dept-btn:hover {
            background: #0a5f42;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(15, 122, 83, 0.25);
        }
        
        /* Tabs - DESIGN.md section header pattern */
        .book-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(12, 75, 52, 0.08);
            gap: 4px;
        }
        
        .tab-button {
            background: transparent;
            border: none;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 700;
            color: rgba(17, 24, 39, 0.5);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.15s ease;
            font-family: 'TT Interphases', sans-serif;
            flex-shrink: 0;
            white-space: nowrap;
        }
        
        .tab-button:hover {
            color: #0C4B34;
            background-color: rgba(12, 75, 52, 0.04);
        }
        
        .tab-button.active {
            color: #0F7A53;
            border-bottom-color: #0F7A53;
            background-color: rgba(12, 75, 52, 0.06);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Book Reference Item - DESIGN.md card */
        .book-reference-item {
            border: 1px solid rgba(12, 75, 52, 0.12);
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
            transition: all 0.28s cubic-bezier(.4,0,.2,1);
        }
        
        .book-reference-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(12, 75, 52, 0.1);
            border-color: rgba(12, 75, 52, 0.2);
        }
        
        .book-references-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .book-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            gap: 12px;
        }
        
        .book-card-header .book-title {
            margin-bottom: 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }
        
        .book-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .book-author-publisher-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .book-details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .book-detail-item {
            font-size: 11px;
            font-weight: 600;
            color: rgba(17, 24, 39, 0.6);
            background: rgba(12, 75, 52, 0.04);
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(12, 75, 52, 0.08);
            flex: 0 0 auto;
        }
        
        .book-created-by, .book-requested-by {
            font-size: 11px;
            color: rgba(17, 24, 39, 0.4);
            margin-top: 6px;
            font-weight: 600;
        }
        
        .warning-indicator {
            background: #FFF3CD;
            color: #856404;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            border: 1px solid #FFEAA7;
            cursor: help;
            flex-shrink: 0;
        }
        
        .processing-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .processing-status-processing {
            background: #FFF3E0;
            color: #E65100;
        }
        
        .processing-status-completed {
            background: #E8F5E9;
            color: #2E7D32;
        }
        
        .processing-status-drafted {
            background: #FFEBEE;
            color: #C62828;
        }
        
        .book-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        
        .book-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .view-book-btn {
            background: rgba(12, 75, 52, 0.08);
            color: #0C4B34;
        }
        
        .view-book-btn:hover {
            background: rgba(12, 75, 52, 0.15);
        }
        
        .request-book-btn {
            background: #0F7A53;
            color: white;
        }
        
        .request-book-btn:hover {
            background: #0a5f42;
        }
        
        .no-references {
            text-align: center;
            color: rgba(17, 24, 39, 0.4);
            font-weight: 600;
            font-size: 13px;
            padding: 32px 20px;
            background: rgba(12, 75, 52, 0.04);
            border: 1px dashed rgba(12, 75, 52, 0.2);
            border-radius: 12px;
        }
        
        /* Responsive Breakpoints - DESIGN.md Section 10 */
        @media (max-width: 1100px) {
            .course-info-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .course-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .course-title-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .course-actions {
                width: 100%;
            }
            .departments-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .add-dept-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 640px) {
            .course-info-grid {
                grid-template-columns: 1fr;
            }
            .course-title {
                font-size: 22px;
            }
            .book-author-publisher-row,
            .book-details-row {
                flex-direction: column;
            }
        }
        
        /* Dark Mode - DESIGN.md Section 1.2 */
        html[data-theme="dark"] body {
            background: #1a1a1a;
        }
        
        html[data-theme="dark"] .course-header {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
        }
        
        html[data-theme="dark"] .course-header:hover {
            border-color: #444 !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4) !important;
        }
        
        html[data-theme="dark"] .course-title {
            color: #f0f0f0;
        }
        
        html[data-theme="dark"] .course-subtitle {
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .back-button {
            color: #81C784;
        }
        
        html[data-theme="dark"] .back-button:hover {
            color: #a5d6a7;
        }
        
        html[data-theme="dark"] .info-label {
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .info-item .info-value {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
        }
        
        html[data-theme="dark"] .info-item .info-value strong {
            color: #81C784;
        }
        
        html[data-theme="dark"] .book-references-section {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
        }
        
        html[data-theme="dark"] .departments-header h3 {
            color: #e0e0e0;
        }
        
        html[data-theme="dark"] .departments-header p {
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .tab-button {
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .tab-button:hover {
            color: #81C784;
            background-color: rgba(255, 255, 255, 0.06);
        }
        
        html[data-theme="dark"] .tab-button.active {
            color: #81C784;
            background-color: rgba(129, 199, 132, 0.1);
        }
        
        html[data-theme="dark"] .book-tabs {
            border-bottom-color: #404040;
        }
        
        html[data-theme="dark"] .book-reference-item {
            background: #252525;
            border-color: #333;
        }
        
        html[data-theme="dark"] .book-reference-item:hover {
            border-color: #444;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        html[data-theme="dark"] .book-title {
            color: #f0f0f0;
        }
        
        html[data-theme="dark"] .book-detail-item {
            background: rgba(255, 255, 255, 0.06);
            border-color: #404040;
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .book-created-by,
        html[data-theme="dark"] .book-requested-by {
            color: #808080;
        }
        
        html[data-theme="dark"] .no-references {
            background: rgba(255, 255, 255, 0.04);
            border-color: #404040;
            color: #808080;
        }
        
        html[data-theme="dark"] .warning-indicator {
            background: #4A3000;
            color: #FFD54F;
            border-color: #5C4000;
        }
        
        html[data-theme="dark"] .view-book-btn {
            background: rgba(255, 255, 255, 0.08);
            color: #81C784;
        }
        
        html[data-theme="dark"] .view-book-btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        /* Table Styles for Program Courses - DESIGN.md compliant */
        .courses-table-container {
            background: #ffffff;
            border-radius: 16px 18px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(12, 75, 52, 0.12);
            margin-top: 20px;
            transition: all 0.28s cubic-bezier(.4,0,.2,1);
        }
        
        .courses-table-container:hover {
            box-shadow: 0 12px 36px rgba(12, 75, 52, 0.1);
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'TT Interphases', sans-serif;
            table-layout: fixed;
        }
        
        .courses-table th {
            background-color: rgba(12, 75, 52, 0.04);
            padding: 14px 12px;
            text-align: left;
            font-weight: 700;
            color: rgba(17, 24, 39, 0.5);
            border-bottom: 1px solid rgba(12, 75, 52, 0.08);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        
        .courses-table td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(12, 75, 52, 0.05);
            font-size: 13px;
            color: #333;
            font-weight: 500;
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
        
        /* Dark Mode - Table */
        html[data-theme="dark"] .courses-table-container {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
        }
        
        html[data-theme="dark"] .courses-table th {
            background-color: rgba(255, 255, 255, 0.04);
            color: #b0b0b0;
            border-bottom-color: #404040;
        }
        
        html[data-theme="dark"] .courses-table td {
            color: #e0e0e0;
            border-bottom-color: #333;
        }
        
        html[data-theme="dark"] .courses-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }
        
        html[data-theme="dark"] .courses-table tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        /* Responsive Table Breakpoints */
        @media (max-width: 1100px) {
            .courses-table-container {
                overflow-x: auto;
            }
            .courses-table {
                min-width: 800px;
            }
        }
        
        /* Archived book items */
        .book-reference-item.archived {
            opacity: 0.7;
            background: rgba(12, 75, 52, 0.03);
        }
        
        .book-reference-item.archived .book-title {
            color: rgba(17, 24, 39, 0.4);
        }
        
        .status-reason-info {
            font-size: 14px;
            color: rgba(17, 24, 39, 0.6);
            padding: 4px 8px;
            background: rgba(12, 75, 52, 0.04);
            border-radius: 50%;
            border: 1px solid rgba(12, 75, 52, 0.12);
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: help;
        }
        
        /* Book actions */
        .book-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        
        .book-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .view-book-btn {
            background: rgba(12, 75, 52, 0.08);
            color: #0C4B34;
        }
        
        .view-book-btn:hover {
            background: rgba(12, 75, 52, 0.15);
        }
        
        .request-book-btn {
            background: #0F7A53;
            color: white;
        }
        
        .request-book-btn:hover {
            background: #0a5f42;
        }
        
        .no-references {
            text-align: center;
            color: rgba(17, 24, 39, 0.4);
            font-weight: 600;
            font-size: 13px;
            padding: 32px 20px;
            background: rgba(12, 75, 52, 0.04);
            border: 1px dashed rgba(12, 75, 52, 0.2);
            border-radius: 12px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        
/* Column width specifications - 8 COLUMNS (NO PROGRAM) */
        .courses-table th:nth-child(1),
        .courses-table td:nth-child(1) { /* Course Code */
            width: 10%;
            min-width: 90px;
            padding: 12px 8px 12px 24px;
        }

        .courses-table th:nth-child(2),
        .courses-table td:nth-child(2) { /* Course Title */
            width: 26%;
            padding: 12px 8px;
        }

        .courses-table th:nth-child(3),
        .courses-table td:nth-child(3) { /* Units */
            width: 6%;
            padding: 12px 8px;
        }

        .courses-table th:nth-child(4),
        .courses-table td:nth-child(4) { /* Term & Academic Year */
            width: 14%;
            min-width: 100px;
            padding: 12px 6px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .courses-table th:nth-child(5),
        .courses-table td:nth-child(5) { /* Year Level */
            width: 8%;
            min-width: 60px;
            padding: 12px 6px;
            text-align: center;
        }

        .courses-table th:nth-child(6),
        .courses-table td:nth-child(6) { /* Faculty */
            width: 22%;
            min-width: 130px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            padding: 12px 8px;
            text-align: left;
        }

        .courses-table th:nth-child(7),
        .courses-table td:nth-child(7) { /* References */
            width: 8%;
            padding: 12px 8px;
            text-align: center;
        }

        .courses-table th:nth-child(8),
        .courses-table td:nth-child(8) { /* Actions */
            width: 8%;
            min-width: 100px;
            padding: 12px 8px;
            text-align: right;
        }
        
        /* Search Container - DESIGN.md */
        .search-container {
            background: #ffffff;
            border-radius: 16px 18px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            padding: 18px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            animation: fadeSlideUp 0.45s ease-out both;
        }
        
        .user-search-bar {
            display: flex;
            align-items: center;
            background-color: #FFFFFF;
            height: 42px;
            padding: 0 14px;
            border-radius: 10px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            flex: 1;
            max-width: 450px;
            min-width: 200px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .user-search-bar:focus-within {
            border-color: #0C4B34;
            box-shadow: 0 0 0 3px rgba(12, 75, 52, 0.1);
        }
        
        .user-search-bar input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 14px;
            background: transparent;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .user-search-bar .magnifier-icon {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            opacity: 0.4;
        }
        
        .clear-search-btn {
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            padding: 0 4px;
            line-height: 1;
        }
        
        .clear-search-btn:hover {
            color: #333;
        }
        
        .search-button {
            background: #0C4B34;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            font-family: 'TT Interphases', sans-serif;
        }
        
        .search-button:hover {
            background: #0a3a28;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
        }
        
        /* Section Headers - DESIGN.md */
        .course-section-header {
            background: #ffffff;
            border-radius: 12px 12px 0 0;
            padding: 16px 24px;
            margin: 20px 0 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(12, 75, 52, 0.12);
            border-bottom: none;
        }

        .course-section-header:first-child {
            margin-top: 0;
        }

        .course-section-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            font-family: 'TT Interphases', sans-serif;
            color: #0C4B34;
        }

        .course-section-header .course-count {
            background: rgba(12, 75, 52, 0.08);
            color: #0C4B34;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(12, 75, 52, 0.12);
        }

        /* Section Dropdown Styles */
        .section-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-dropdown {
            padding: 8px 12px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'TT Interphases', sans-serif;
            background: white;
            color: #111827;
            cursor: pointer;
            min-width: 200px;
            transition: border-color 0.2s ease;
        }

        .section-dropdown:focus {
            border-color: #0C4B34;
            outline: none;
        }
            border-radius: 6px;
            background: white;
            color: #333;
            font-size: 14px;
            font-family: 'TT Interphases', sans-serif;
            min-width: 300px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Responsive Section Dropdown */
        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                align-items: stretch;
            }
            .user-search-bar {
                max-width: 100%;
            }
            .section-dropdown {
                min-width: 100%;
            }
        }
        
        /* Course Row - DESIGN.md Table Row */
        .course-row {
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .course-row:hover {
            background-color: rgba(12, 75, 52, 0.04) !important;
            transform: translateY(-1px);
        }
        
        .course-code {
            font-weight: 700;
            color: #111827;
        }

        .term-year {
            vertical-align: top;
            padding: 8px 8px;
        }
        
        .units-count {
            font-weight: 700;
            color: #0C4B34;
        }
        
        .term-year {
            font-size: 13px;
            color: rgba(17, 24, 39, 0.6);
        }
        
        .actions-cell {
            padding: 8px 12px;
        }
        
        /* Course Code - Left aligned */
        .courses-table th:nth-child(1) {
            text-align: left !important;
            padding: 12px 8px 12px 24px !important;
        }
        
        .courses-table td:nth-child(1) {
            text-align: left !important;
            padding: 8px 8px 8px 24px !important;
            font-weight: 700;
            color: #111827;
        }
        
        /* Year Level - Center aligned */
        .courses-table td:nth-child(5) {
            text-align: center !important;
            vertical-align: middle !important;
            padding: 6px 4px !important;
        }
        
        /* Faculty - Left aligned */
        .courses-table td:nth-child(6) {
            text-align: left !important;
            padding: 8px 6px !important;
        }
        
        /* References - Center aligned */
        .courses-table td:nth-child(7) {
            text-align: center !important;
            padding: 8px 6px !important;
        }
        
        /* Actions - Left aligned */
        .courses-table td:nth-child(8) {
            text-align: left !important;
            padding: 8px 6px !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .edit-btn {
            background-color: rgba(12, 75, 52, 0.1);
            color: #0C4B34;
        }
        
        .edit-btn:hover {
            background-color: rgba(12, 75, 52, 0.2);
        }
        
        .assign-btn {
            background-color: #0F7A53;
            color: white;
        }
        
        .assign-btn:hover:not(.disabled) {
            background-color: #0a5f42;
        }
        
        /* Action Menu Styles - DESIGN.md Button Patterns */
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
            padding: 8px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .action-menu-btn:hover {
            background-color: rgba(12, 75, 52, 0.08);
        }

        .three-dots {
            font-size: 16px;
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
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 99999;
            min-width: 140px;
            padding: 4px 0;
        }

        .action-menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 14px;
            color: #333;
        }

        .action-menu-item:hover {
            background-color: #f5f5f5;
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
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        .actions-cell {
            position: relative;
            text-align: center;
        }
        
        .references-count {
            font-weight: 600;
            color: #666;
        }
        
        /* REFERENCES - Already styled above with DESIGN.md */
        .references-count {
            font-weight: 700;
            color: #111827;
        }
        
        /* Info icon styling for edit program modal - DESIGN.md */
        .info-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(17, 24, 39, 0.4);
            font-size: 14px;
            cursor: help;
            z-index: 1;
        }
        
        .info-icon:hover {
            color: #0C4B34;
        }
        
        /* Edit Program Modal - Exact match to Add Program Modal */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #editProgramModal.department-modal-overlay {
            display: none;
            position: fixed;
            z-index: 1003;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            overscroll-behavior: contain;
        }

        #editProgramModal .department-modal-box {
            background-color: #EFEFEF;
            margin: auto;
            padding: 25px;
            border: 1px solid #888;
            border-radius: 15px;
            width: 90%;
            max-width: 650px;
            min-height: 300px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s;
        }

        /* Modal Header with Close Button */
        #editProgramModal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        #editProgramModal .modal-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }

        #editProgramModal .close-button {
            color: #aaa;
            font-size: 28px;
            font-weight: 700;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
            padding: 0;
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #editProgramModal .close-button:focus,
        #editProgramModal .close-button:hover {
            color: #000;
        }

        /* Form Styles */
        #editProgramModal .form-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        #editProgramModal .form-row {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        #editProgramModal .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        #editProgramModal .form-group label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 6px;
        }

        #editProgramModal .form-group input[type="text"],
        #editProgramModal .form-group input[type="email"],
        #editProgramModal .form-group input[type="password"],
        #editProgramModal .form-group select,
        #editProgramModal .form-group textarea {
            border-radius: 12px;
            font-size: 16px;
            height: 50px;
            padding: 10px;
            border: 1px solid #ccc;
            transition: border-color 0.2s ease;
        }

        #editProgramModal .form-group textarea {
            height: auto;
            min-height: 80px;
            resize: vertical;
        }

        #editProgramModal .form-group input:focus,
        #editProgramModal .form-group select:focus,
        #editProgramModal .form-group textarea:focus {
            outline: none;
            border-color: #4A7DFF;
            box-shadow: 0 0 0 2px rgba(74, 125, 255, 0.2);
        }

        /* Button Styles */
        #editProgramModal .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 10px;
            margin-bottom: 0;
        }

        #editProgramModal .cancel-btn,
        #editProgramModal .create-btn {
            border-radius: 10px !important;
        }

        #editProgramModal .create-btn:disabled {
            background-color: #ccc !important;
            color: #666 !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
        }

        #editProgramModal .create-btn:disabled:hover {
            background-color: #ccc !important;
            transform: none !important;
        }

        /* Color Input Styles */
        #editProgramModal .color-field-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #editProgramModal .color-input-wrapper { 
            display: flex;
            align-items: center;
            gap: 8px; 
            position: relative; 
            width: 100%;
            flex: 1;
        }

        #editProgramModal .color-swatch-display { 
            width: 36px; 
            height: 36px;
            border-radius: 50%; 
            border: 2px solid #ccc; 
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2); 
            flex-shrink: 0; 
            z-index: 3;
            transition: border-color 0.2s ease, transform 0.1s ease;
        }

        #editProgramModal .color-swatch-display:hover {
            border-color: #888; 
            transform: scale(1.05);
        }

        #editProgramModal #editColorPicker { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 36px; 
            height: 36px;
            opacity: 0; 
            cursor: pointer;
            z-index: -1; 
            border: none; 
            padding: 0;
            margin: 0; 
            pointer-events: none;
        }

        #editProgramModal #editColorPicker::-webkit-color-swatch-wrapper { padding: 0; }
        #editProgramModal #editColorPicker::-webkit-color-swatch { border: none; border-radius: 50%; }
        #editProgramModal #editColorPicker::-moz-color-swatch-wrapper { padding: 0; }
        #editProgramModal #editColorPicker::-moz-color-swatch { border: none; border-radius: 50%; }

        #editProgramModal .color-input-wrapper input[type="text"] { 
            flex-grow: 1; 
            padding-right: 30px; 
            width: auto; 
            position: relative;
            z-index: 2;
            -webkit-autocomplete: off;
            -moz-autocomplete: off;
            autocomplete: off;
            -webkit-text-security: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            spellcheck: false;
            autocorrect: off;
            autocapitalize: off;
        }

        #editProgramModal .color-buttons-wrapper {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
        }

        #editProgramModal .random-color-btn { 
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
            cursor: pointer;
            padding: 8px 10px;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-family: 'TT Interphases', sans-serif;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #editProgramModal .random-color-btn:hover {
            color: #1976d2;
            background: #e3f2fd;
            border-color: #1976d2;
        }

        #editProgramModal .clear-color-btn { 
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
            cursor: pointer;
            padding: 8px 10px;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-family: 'TT Interphases', sans-serif;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #editProgramModal .clear-color-btn:hover {
            color: #dc3545;
            background: #f8d7da;
            border-color: #dc3545;
        }

        /* Info icon styling */
        #editProgramModal .info-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 16px;
            cursor: help;
            font-weight: bold;
            transition: color 0.2s ease;
            user-select: none;
            z-index: 2;
        }

        #editProgramModal .info-icon:hover {
            color: #0056b3;
            transform: translateY(-50%) scale(1.1);
        }
        
        .course-title-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .course-actions {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Nested wrappers removed to fix offset bugs and structure -->
    <div class="course-details-container" style="margin: 0 auto !important; max-width: 90% !important; width: 90% !important;">
        <!-- Back Navigation -->
        <a href="content.php?page=<?php 
            if ($showProgramCourses) {
                echo 'dashboard';
            } elseif (!empty($fromProgram)) {
                echo 'course-details&program=' . urlencode($fromProgram);
            } else {
                echo 'all-courses';
            }
            // Preserve term_id parameter
            if (isset($_GET['term_id'])) {
                echo '&term_id=' . urlencode($_GET['term_id']);
            }
        ?>" class="back-button">
            <img src="../src/assets/icons/go-back-icon.png" alt="Back" onerror="this.style.display='none'; this.nextSibling.style.display='inline';">
            <span style="display: none;">←</span>
            <?php 
            if ($showProgramCourses) {
                echo 'Back to Dashboard';
            } elseif (!empty($fromProgram)) {
                echo 'Back to ' . htmlspecialchars($fromProgram) . ' Courses';
            } else {
                echo 'Back to All Courses';
            }
            ?>
        </a>
        
        
        <?php if ($showProgramCourses): ?>
        <!-- Program Header Card -->
        <div class="course-header">
            <div class="course-title-section">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <?php if ($programInfo): ?>
                        <span class="program-badge" style="background-color: <?php echo htmlspecialchars($programInfo['color_code']); ?>; color: white; padding: 12px 20px; border-radius: 8px; font-size: 16px; font-weight: 700;">
                            <?php echo htmlspecialchars($programInfo['program_code']); ?>
                        </span>
                        <div>
                            <h1 class="course-title" style="margin: 0;"><?php echo htmlspecialchars($programInfo['program_name']); ?></h1>
                            <?php if (!empty($programInfo['major'])): ?>
                                <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">Major in: <strong><?php echo htmlspecialchars($programInfo['major']); ?></strong></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <h1 class="course-title" style="margin: 0;">Program Courses</h1>
                    <?php endif; ?>
                </div>
                <div class="course-actions">
                    <button class="edit-course-btn" onclick="editProgram('<?php echo htmlspecialchars($programCode); ?>')">
                        Edit Program
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Bar Section -->
        <div class="search-container">
            <div style="display: flex; align-items: center; justify-content: space-between; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="user-search-bar" style="width: 450px;">
                        <img src="../src/assets/icons/magnifier-icon.png" alt="Search" class="magnifier-icon">
                        <input type="text" placeholder="Search courses by code, title, term, academic year, year level, or faculty..." id="programCourseSearch" autocomplete="off">
                        <button type="button" id="clearProgramSearch" class="clear-search-btn" style="display: none;">&times;</button>
                    </div>
                    <button class="search-button" onclick="performProgramSearch()">Search</button>
                </div>
                <div id="programCourseCountDisplay" style="color: #666; font-size: 16px; font-family: 'TT Interphases', sans-serif;">
                    <strong><?php echo count($programCourses); ?> courses</strong> found
                </div>
            </div>
        </div>
        
        <!-- Program Courses Table -->
        <div class="courses-table-container">
            <?php if (!empty($programCourses)): ?>
                <?php
                // Group courses by year level and semester
                $groupedProgramCourses = [];
                foreach ($programCourses as $course) {
                    $yearLevel = $course['year_level'];
                    $term = $course['term'];
                    $academicYear = $course['academic_year'];
                    
                    // Format year level (1 -> 1st Year, 2 -> 2nd Year, etc.)
                    $yearLevelFormatted = '';
                    if (is_numeric($yearLevel)) {
                        switch($yearLevel) {
                            case '1': $yearLevelFormatted = '1st Year'; break;
                            case '2': $yearLevelFormatted = '2nd Year'; break;
                            case '3': $yearLevelFormatted = '3rd Year'; break;
                            case '4': $yearLevelFormatted = '4th Year'; break;
                            default: $yearLevelFormatted = $yearLevel . ' Year'; break;
                        }
                    } else {
                        if ($yearLevel == '1st') $yearLevelFormatted = '1st Year';
                        elseif ($yearLevel == '2nd') $yearLevelFormatted = '2nd Year';
                        elseif ($yearLevel == '3rd') $yearLevelFormatted = '3rd Year';
                        elseif ($yearLevel == '4th') $yearLevelFormatted = '4th Year';
                        else $yearLevelFormatted = $yearLevel . ' Year';
                    }
                    
                    // Format term
                    $termFormatted = '';
                    if ($term == '1st') $termFormatted = '1st Semester';
                    elseif ($term == '2nd') $termFormatted = '2nd Semester';
                    elseif ($term == 'summer') $termFormatted = 'Summer Semester';
                    elseif (strpos($term, 'Semester') !== false) $termFormatted = $term;
                    else $termFormatted = $term ?: 'N/A';
                    
                    $sectionKey = $yearLevelFormatted . ' - ' . $termFormatted . ' of ' . $academicYear;
                    
                    if (!isset($groupedProgramCourses[$sectionKey])) {
                        $groupedProgramCourses[$sectionKey] = [];
                    }
                    $groupedProgramCourses[$sectionKey][] = $course;
                }
                
                // Sort sections by year level and term
                uksort($groupedProgramCourses, function($a, $b) {
                    // Extract year level and term for sorting
                    preg_match('/(\d+)(st|nd|rd|th) Year/', $a, $matchesA);
                    preg_match('/(\d+)(st|nd|rd|th) Year/', $b, $matchesB);
                    $yearA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
                    $yearB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;
                    
                    if ($yearA !== $yearB) {
                        return $yearA - $yearB;
                    }
                    
                    // If same year, sort by term
                    $termOrder = ['1st Semester' => 1, '2nd Semester' => 2, 'Summer Semester' => 3];
                    $termA = 0;
                    $termB = 0;
                    
                    foreach ($termOrder as $term => $order) {
                        if (strpos($a, $term) !== false) $termA = $order;
                        if (strpos($b, $term) !== false) $termB = $order;
                    }
                    
                    return $termA - $termB;
                });
                
                // Create sorted array for dropdown
                $sortedGroupedProgramCourses = [];
                
                // Get all section keys and sort them
                $sectionKeys = array_keys($groupedProgramCourses);
                usort($sectionKeys, function($a, $b) {
                    // Extract year level and term for sorting
                    preg_match('/(\d+)(st|nd|rd|th) Year/', $a, $matchesA);
                    preg_match('/(\d+)(st|nd|rd|th) Year/', $b, $matchesB);
                    $yearA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
                    $yearB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;
                    
                    if ($yearA !== $yearB) {
                        return $yearA - $yearB;
                    }
                    
                    // If same year, sort by term
                    $termOrder = ['1st Semester' => 1, '2nd Semester' => 2, 'Summer Semester' => 3];
                    $termA = 0;
                    $termB = 0;
                    
                    foreach ($termOrder as $term => $order) {
                        if (strpos($a, $term) !== false) $termA = $order;
                        if (strpos($b, $term) !== false) $termB = $order;
                    }
                    
                    return $termA - $termB;
                });
                
                // Rebuild sorted array
                foreach ($sectionKeys as $key) {
                    $sortedGroupedProgramCourses[$key] = $groupedProgramCourses[$key];
                }
                ?>
                
                <!-- Section Selection Dropdown -->
                <div class="course-section-header">
                    <div class="section-selector">
                        <label for="sectionDropdown" style="color: #000; font-weight: 600; margin-right: 12px;">View Courses:</label>
                        <select id="sectionDropdown" class="section-dropdown" onchange="filterCoursesBySection()">
                            <option value="">All Sections</option>
                            <?php foreach ($sortedGroupedProgramCourses as $sectionTitle => $sectionCourses): ?>
                                <option value="<?php echo htmlspecialchars($sectionTitle); ?>" data-count="<?php echo count($sectionCourses); ?>">
                                    <?php echo htmlspecialchars($sectionTitle); ?> (<?php echo count($sectionCourses); ?> courses)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <span class="course-count" id="totalCourseCount"><?php echo count($programCourses); ?> courses</span>
                </div>
                
                <!-- Single Table for All Courses -->
                <table class="courses-table" id="coursesTable">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Units</th>
                            <th>Term &<br>Academic Year</th>
                            <th>Year Level</th>
                            <th>Faculty</th>
                            <th>References</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programCourses as $course): ?>
                            <tr class="course-row" data-section="<?php 
                                // Create section identifier for filtering
                                $yearLevel = $course['year_level'];
                                $term = $course['term'];
                                $academicYear = $course['academic_year'];
                                
                                // Format year level
                                $yearLevelFormatted = '';
                                if (is_numeric($yearLevel)) {
                                    switch($yearLevel) {
                                        case '1': $yearLevelFormatted = '1st Year'; break;
                                        case '2': $yearLevelFormatted = '2nd Year'; break;
                                        case '3': $yearLevelFormatted = '3rd Year'; break;
                                        case '4': $yearLevelFormatted = '4th Year'; break;
                                        default: $yearLevelFormatted = $yearLevel . ' Year'; break;
                                    }
                                } else {
                                    if ($yearLevel == '1st') $yearLevelFormatted = '1st Year';
                                    elseif ($yearLevel == '2nd') $yearLevelFormatted = '2nd Year';
                                    elseif ($yearLevel == '3rd') $yearLevelFormatted = '3rd Year';
                                    elseif ($yearLevel == '4th') $yearLevelFormatted = '4th Year';
                                    else $yearLevelFormatted = $yearLevel . ' Year';
                                }
                                
                                // Format term
                                $termFormatted = '';
                                if ($term == '1st') $termFormatted = '1st Semester';
                                elseif ($term == '2nd') $termFormatted = '2nd Semester';
                                elseif ($term == 'summer') $termFormatted = 'Summer Semester';
                                elseif (strpos($term, 'Semester') !== false) $termFormatted = $term;
                                else $termFormatted = $term ?: 'N/A';
                                
                                $sectionKey = $yearLevelFormatted . ' - ' . $termFormatted . ' of ' . $academicYear;
                                echo htmlspecialchars($sectionKey);
                            ?>" onclick="navigateToCourseFromProgram('<?php echo htmlspecialchars($course['course_code']); ?>', '<?php echo htmlspecialchars($course['course_title']); ?>', '<?php echo htmlspecialchars($programCode); ?>', <?php echo intval($course['id']); ?>)" style="cursor: pointer;">
                                <td class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                <td class="units-count" style="text-align: center;"><?php echo htmlspecialchars($course['units']); ?></td>
                                <td class="term-year">
                                    <div style="font-weight: 600; color: #1976d2; margin-bottom: 2px; font-size: 13px;">
                                        <?php 
                                        // Format term: 1st -> 1st Semester, 2nd -> 2nd Semester, etc.
                                        $term = $course['term'];
                                        if ($term == '1st') echo '1st Semester';
                                        elseif ($term == '2nd') echo '2nd Semester';
                                        elseif ($term == 'summer') echo 'Summer';
                                        elseif (strpos($term, 'Semester') !== false) echo htmlspecialchars($term); // Already formatted
                                        else echo htmlspecialchars($term);
                                        ?>
                                    </div>
                                    <div style="font-size: 11px; color: #6c757d; font-weight: 500;">
                                        <?php echo htmlspecialchars($course['academic_year']); ?>
                                    </div>
                                </td>
                                <td class="year-level" style="text-align: center;">
                                    <?php 
                                    // Format year level
                                    $yearLevelFormatted = '';
                                    if (is_numeric($course['year_level'])) {
                                        switch($course['year_level']) {
                                            case '1': $yearLevelFormatted = '1st Year'; break;
                                            case '2': $yearLevelFormatted = '2nd Year'; break;
                                            case '3': $yearLevelFormatted = '3rd Year'; break;
                                            case '4': $yearLevelFormatted = '4th Year'; break;
                                            default: $yearLevelFormatted = $course['year_level'] . ' Year'; break;
                                        }
                                    } else {
                                        if ($course['year_level'] == '1st') $yearLevelFormatted = '1st Year';
                                        elseif ($course['year_level'] == '2nd') $yearLevelFormatted = '2nd Year';
                                        elseif ($course['year_level'] == '3rd') $yearLevelFormatted = '3rd Year';
                                        elseif ($course['year_level'] == '4th') $yearLevelFormatted = '4th Year';
                                        else $yearLevelFormatted = $course['year_level'] . ' Year';
                                    }
                                    echo htmlspecialchars($yearLevelFormatted);
                                    ?>
                                </td>
                                <td class="faculty-name">
                                    <?php if ($course['faculty_name']): ?>
                                        <?php echo htmlspecialchars($course['faculty_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;">Not Yet Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="references-count" style="text-align: center;">
                                    <?php 
                                    // Get compliant book references count from database
                                    $compliantCount = 0;
                                    $currentYear = date('Y');
                                    try {
                                        // Get course ID first
                                        $courseIdQuery = "SELECT id FROM courses WHERE course_code = ? LIMIT 1";
                                        $courseIdStmt = $pdo->prepare($courseIdQuery);
                                        $courseIdStmt->execute([$course['course_code']]);
                                        $courseData = $courseIdStmt->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($courseData) {
                                            // Count only compliant book references (copyright_year within 5 years)
                                            $refCountQuery = "SELECT COUNT(*) as ref_count FROM book_references WHERE course_id = ? AND publication_year > 0 AND (? - publication_year) < 5";
                                            $refCountStmt = $pdo->prepare($refCountQuery);
                                            $refCountStmt->execute([$courseData['id'], $currentYear]);
                                            $refResult = $refCountStmt->fetch(PDO::FETCH_ASSOC);
                                            $compliantCount = $refResult['ref_count'] ?? 0;
                                        }
                                    } catch (Exception $e) {
                                        // If table doesn't exist or error, show 0
                                        $compliantCount = 0;
                                    }
                                    
                                    $targetCount = 5; // Default target, QA will set this later
                                    $isCompliant = $compliantCount >= $targetCount;
                                    $displayColor = $isCompliant ? '#2e7d32' : '#FF4C4C'; // Green if compliant, Red if not
                                    ?>
                                    <span style="background: <?php echo $isCompliant ? '#e8f5e8' : '#ffeaea'; ?>; color: <?php echo $displayColor; ?>; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; font-family: 'TT Interphases', sans-serif;">
                                        <?php echo $compliantCount; ?>/<?php echo $targetCount; ?>
                                    </span>
                                </td>
                                <td class="actions-cell" onclick="event.stopPropagation();" style="position: relative;">
                                    <div class="action-menu-container">
                                        <button class="action-menu-btn" onclick="toggleActionMenu(event, '<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo !empty($course['faculty_name']) ? 'true' : 'false'; ?>)" title="Actions" aria-label="Actions menu">
                                            <span class="three-dots" style="display: block; line-height: 0.5;">⋯</span>
                                        </button>
                                        <div class="action-menu-dropdown" id="actionMenu-<?php echo htmlspecialchars($course['course_code']); ?>" style="display: none;">
                                            <div class="action-menu-item" onclick="editCourse('<?php echo htmlspecialchars($course['course_code']); ?>')">
                                                <span class="action-icon">✏️</span>
                                                <span>Edit</span>
                                            </div>
                                            <div class="action-menu-item <?php echo !empty($course['faculty_name']) ? 'disabled' : ''; ?>" 
                                                 onclick="<?php echo !empty($course['faculty_name']) ? 'return false;' : 'assignFaculty(\'' . htmlspecialchars($course['course_code']) . '\');'; ?>">
                                                <span class="action-icon">👤</span>
                                                <span>Assign</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666; background: white; border-radius: 8px; border: 1px solid #e0e0e0;">
                    <p style="margin: 0; font-size: 16px;">No courses found for this program.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Individual Course Header -->
        <div class="course-header">
            <div class="course-title-section">
                <h1 class="course-title"><?php echo htmlspecialchars($courseDetails['course_code']); ?></h1>
                <div class="course-actions">
                    <button class="edit-course-btn" onclick="editCourse('<?php echo htmlspecialchars($courseDetails['course_code']); ?>')">
                        Edit Course
                    </button>
                    <button class="assign-course-btn" onclick="assignFaculty('<?php echo htmlspecialchars($courseDetails['course_code']); ?>')">
                        Assign
                    </button>
                </div>
            </div>
            <p class="course-subtitle"><?php echo htmlspecialchars($courseDetails['course_title']); ?></p>
            
            <div class="course-info-grid">
                <div class="info-item">
                    <span class="info-label">Program</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                        <?php 
                        // GET ALL PROGRAMS FOR THIS COURSE - SAME AS ALL COURSES TABLE
                        $programs = [];
                        
                        try {
                            // Get all course entries for this course code (filtered by dean's department)
                            $query = "
                                SELECT 
                                    c.course_code,
                                    p.id as program_id,
                                    p.program_code,
                                    p.program_name,
                                    p.major,
                                    d.color_code
                                FROM courses c
                                LEFT JOIN programs p ON c.program_id = p.id
                                LEFT JOIN departments d ON p.department_id = d.id
                                WHERE c.course_code = ?
                                AND d.department_code = ?
                                ORDER BY p.program_code
                            ";
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([$courseCode, $deanDepartmentCode]);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Merge programs for this course (same logic as All Courses table)
                            foreach ($result as $row) {
                                if (!empty($row['program_code'])) {
                                    $programs[] = [
                                        'program_id' => $row['program_id'] ?? '',
                                        'program_code' => $row['program_code'],
                                        'program_name' => $row['program_name'],
                                        'program_major' => $row['major'] ?? null,
                                        'program_color' => $row['color_code'] ?? '#1976d2'
                                    ];
                                }
                            }
                            
                        } catch (Exception $e) {
                            // Fallback to single program from courseDetails
                            if (!empty($courseDetails['program_code'])) {
                                $programs[] = [
                                    'program_id' => $courseDetails['program_id'] ?? '',
                                    'program_code' => $courseDetails['program_code'],
                                    'program_name' => $courseDetails['program_name'] ?? '',
                                    'program_major' => $courseDetails['major'] ?? null,
                                    'program_color' => $courseDetails['color_code'] ?? '#1976d2'
                                ];
                            }
                        }
                        
                        // Display all program badges
                        if (!empty($programs)) {
                            foreach ($programs as $program) {
                                echo '<span class="program-badge" style="background-color: ' . htmlspecialchars($program['program_color']) . '; color: white; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-right: 4px;">';
                                echo htmlspecialchars($program['program_code']);
                                echo '</span>';
                                // Display major label if it exists
                                if (!empty($program['program_major'])) {
                                    echo '<span style="color: #6c757d; font-size: 12px; font-weight: 500; margin-right: 4px;">(Major in ' . htmlspecialchars($program['program_major']) . ')</span>';
                                }
                            }
                        } else {
                            echo '<span class="program-badge" style="background-color: #6c757d; color: white; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">N/A</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <span class="info-label">Units</span>
                    <span class="info-value"><?php echo htmlspecialchars($courseDetails['units'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Faculty</span>
                    <span class="info-value"><?php echo htmlspecialchars($courseDetails['faculty_name'] ?? 'Not Assigned'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Year Level</span>
                    <span class="info-value">
                        <?php 
                        // Format year level: 1 -> 1st Year, 2 -> 2nd Year, etc.
                        $yearLevel = $courseDetails['year_level'] ?? '';
                        if (is_numeric($yearLevel)) {
                            if ($yearLevel == 1) echo '1st Year';
                            elseif ($yearLevel == 2) echo '2nd Year';
                            elseif ($yearLevel == 3) echo '3rd Year';
                            elseif ($yearLevel == 4) echo '4th Year';
                            else echo $yearLevel . 'th Year';
                        } else {
                            echo htmlspecialchars($yearLevel ?: 'N/A');
                        }
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Term and Academic Year</span>
                    <div class="info-value" style="display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-weight: 600; color: #1976d2;">
                            <?php 
                            // Format term: 1st -> 1st Semester, 2nd -> 2nd Semester, etc.
                            $term = $courseDetails['term'] ?? '';
                            if ($term == '1st') echo '1st Semester';
                            elseif ($term == '2nd') echo '2nd Semester';
                            elseif ($term == 'summer') echo 'Summer';
                            elseif (strpos($term, 'Semester') !== false) echo htmlspecialchars($term); // Already formatted
                            else echo htmlspecialchars($term ?: 'N/A');
                            ?>
                        </span>
                        <span style="font-size: 11px; color: #6c757d;"><?php echo htmlspecialchars($courseDetails['academic_year'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Book References Section -->
        <div class="book-references-section">
            <?php 
            // TEMP DEBUG - Remove after fixing
            if (!empty($bookReferences)) {
            }
            
            // Count only compliant books (less than 5 years old)
            $currentYear = date('Y');
            $compliantBooks = array_filter($bookReferences, function($book) use ($currentYear) {
                $bookYear = intval($book['copyright_year'] ?? 0);
                return $bookYear > 0 && ($currentYear - $bookYear) < 5;
            });
            $compliantCount = count($compliantBooks);
            ?>
            <div class="departments-header">
                <div>
                    <h3>Book References (<?php echo $compliantCount; ?>)</h3>
                    <p>Manage course materials and reading resources</p>
                </div>
                <button class="add-dept-btn" onclick="openAddBookReferenceModal(<?php echo $courseDetails['id']; ?>, '<?php echo htmlspecialchars($courseDetails['course_code'] ?? ''); ?>')">Add Book Reference</button>
            </div>
            
            <?php if (empty($bookReferences)): ?>
                <div class="no-references">
                    <div class="empty-state">
                        <div class="empty-icon">📖</div>
                        <h3>No book references yet</h3>
                        <p>Add book references to help the assigned faculty staff find the right materials for this course.</p>
                        <button class="add-first-book-btn" onclick="openAddBookReferenceModal(<?php echo $courseDetails['id']; ?>, '<?php echo htmlspecialchars($courseDetails['course_code'] ?? ''); ?>')">
                            Add First Book Reference
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Book References Tabs -->
                <div class="book-tabs">
                    <button class="tab-button active" onclick="switchBookTab('compliant')" id="compliantTab">
                        Compliant References
                    </button>
                    <button class="tab-button" onclick="switchBookTab('nonCompliant')" id="nonCompliantTab">
                        Non-Compliant References
                    </button>
                </div>
                
                <!-- Compliant References Tab -->
                <div id="compliantReferences" class="tab-content active">
                    <?php 
                    $compliantRefs = array_filter($bookReferences, function($book) use ($currentYear) {
                        $bookYear = intval($book['copyright_year'] ?? 0);
                        return $bookYear > 0 && ($currentYear - $bookYear) < 5;
                    });
                    ?>
                    <?php if (count($compliantRefs) > 0): ?>
                        <div class="book-references-list">
                            <?php foreach ($compliantRefs as $book): ?>
                                <?php 
                                $bookYear = intval($book['copyright_year'] ?? 0);
                                $yearsOld = $bookYear > 0 ? ($currentYear - $bookYear) : 0;
                                $isNearExpiry = $yearsOld >= 4 && $yearsOld < 5;
                                ?>
                                <div class="book-reference-item">
                                    <div class="book-card-header">
                                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                        <?php if ($isNearExpiry): ?>
                                            <div class="warning-indicator" title="This book is approaching the 5-year copyright range">
                                                ⚠️
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Author and Publisher Row -->
                                    <div class="book-author-publisher-row">
                                        <?php if (!empty($book['author'])): ?>
                                            <span class="book-detail-item">Author: <?php echo htmlspecialchars($book['author']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['publisher'])): ?>
                                            <span class="book-detail-item">Publisher: <?php echo htmlspecialchars($book['publisher']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Details Row: Call Number, ISBN, Copyright, Edition, Location -->
                                    <div class="book-details-row">
                                        <?php if (!empty($book['call_number'])): ?>
                                            <span class="book-detail-item">Call No.: <?php echo htmlspecialchars($book['call_number']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['isbn'])): ?>
                                            <span class="book-detail-item">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['copyright_year'])): ?>
                                            <span class="book-detail-item">Copyright Year: <?php echo htmlspecialchars($book['copyright_year']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['edition'])): ?>
                                            <span class="book-detail-item">Edition: <?php echo htmlspecialchars($book['edition']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['location'])): ?>
                                            <span class="book-detail-item">Location: <?php echo htmlspecialchars($book['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Added by / Requested by with Processing Status -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                                        <div>
                                            <?php if (!empty($book['requested_by_name'])): ?>
                                                <div class="book-requested-by" style="font-size: 11px; color: #6c757d;">Requested by: <?php echo htmlspecialchars($book['requested_by_name']); ?></div>
                                            <?php elseif (!empty($book['created_by_name'])): ?>
                                                <div class="book-created-by" style="font-size: 11px; color: #6c757d;">Added by: <?php echo htmlspecialchars($book['created_by_name']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($book['processing_status']) && $book['processing_status'] !== 'completed'): ?>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div class="processing-status-badge processing-status-<?php echo htmlspecialchars($book['processing_status']); ?>">
                                                    <?php 
                                                    if ($book['processing_status'] === 'processing') {
                                                        echo 'Processing';
                                                    } elseif ($book['processing_status'] === 'drafted') {
                                                        echo 'Drafted';
                                                    }
                                                    ?>
                                                </div>
                                                <?php if ($book['processing_status'] === 'drafted' && !empty($book['status_reason'])): ?>
                                                    <div class="status-reason-info" title="<?php echo htmlspecialchars($book['status_reason']); ?>">
                                                        ℹ️
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-references">
                            <p>No compliant book references found for this course.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Non-Compliant References Tab -->
                <div id="nonCompliantReferences" class="tab-content">
                    <?php 
                    $nonCompliantRefs = array_filter($bookReferences, function($book) use ($currentYear) {
                        $bookYear = intval($book['copyright_year'] ?? 0);
                        return $bookYear > 0 && ($currentYear - $bookYear) >= 5;
                    });
                    ?>
                    <?php if (count($nonCompliantRefs) > 0): ?>
                        <div class="book-references-list">
                            <?php foreach ($nonCompliantRefs as $book): ?>
                                <div class="book-reference-item archived">
                                    <div class="book-card-header">
                                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    </div>
                                    
                                    <!-- Author and Publisher Row -->
                                    <div class="book-author-publisher-row">
                                        <?php if (!empty($book['author'])): ?>
                                            <span class="book-detail-item">Author: <?php echo htmlspecialchars($book['author']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['publisher'])): ?>
                                            <span class="book-detail-item">Publisher: <?php echo htmlspecialchars($book['publisher']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Details Row: Call Number, ISBN, Copyright, Edition, Location -->
                                    <div class="book-details-row">
                                        <?php if (!empty($book['call_number'])): ?>
                                            <span class="book-detail-item">Call No.: <?php echo htmlspecialchars($book['call_number']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['isbn'])): ?>
                                            <span class="book-detail-item">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['copyright_year'])): ?>
                                            <span class="book-detail-item">Copyright Year: <?php echo htmlspecialchars($book['copyright_year']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['edition'])): ?>
                                            <span class="book-detail-item">Edition: <?php echo htmlspecialchars($book['edition']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['location'])): ?>
                                            <span class="book-detail-item">Location: <?php echo htmlspecialchars($book['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Added by / Requested by with Processing Status -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                                        <div>
                                            <?php if (!empty($book['requested_by_name'])): ?>
                                                <div class="book-requested-by" style="font-size: 11px; color: #6c757d;">Requested by: <?php echo htmlspecialchars($book['requested_by_name']); ?></div>
                                            <?php elseif (!empty($book['created_by_name'])): ?>
                                                <div class="book-created-by" style="font-size: 11px; color: #6c757d;">Added by: <?php echo htmlspecialchars($book['created_by_name']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($book['processing_status']) && $book['processing_status'] !== 'completed'): ?>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div class="processing-status-badge processing-status-<?php echo htmlspecialchars($book['processing_status']); ?>">
                                                    <?php 
                                                    if ($book['processing_status'] === 'processing') {
                                                        echo 'Processing';
                                                    } elseif ($book['processing_status'] === 'drafted') {
                                                        echo 'Drafted';
                                                    }
                                                    ?>
                                                </div>
                                                <?php if ($book['processing_status'] === 'drafted' && !empty($book['status_reason'])): ?>
                                                    <div class="status-reason-info" title="<?php echo htmlspecialchars($book['status_reason']); ?>">
                                                        ℹ️
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-references">
                            <p>No non-compliant book references found for this course.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Program Modal -->
    <div id="editProgramModal" class="department-modal-overlay" style="display: none;">
        <div class="department-modal-box">
            <div class="modal-header">
                <h2>Edit Program</h2>
                <span class="close-button" onclick="closeEditProgramModal()">&times;</span>
            </div>
            <form id="editProgramForm" class="form-grid">
                <div class="form-row">
                    <div class="form-group" style="width: 250px;">
                        <label>Program Code</label>
                        <div style="position: relative;">
                            <input type="text" name="program_code" id="editProgramCode" required readonly autocomplete="off" style="padding-right: 35px; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                            <span class="info-icon" title="Program code cannot be changed.&#10;To modify a program, use the settings page to delete and recreate it.&#10;This ensures data integrity and prevents conflicts.">ⓘ</span>
                        </div>
                    </div>
                    <div class="form-group" style="width: 375px;">
                        <label>Program Name</label>
                        <input type="text" name="program_name" id="editProgramName" required autocomplete="off">
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
    
    <script>
        // Debug: Check if modal exists when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editCourseModal');
            if (editModal) {
            } else {
                console.error('❌ Modal not found on page load');
            }
        });
        
        // EDIT COURSE FUNCTION - CALLS openEditCourseModal FROM MODAL FILE
        function editCourse(courseCode) {
            
            // Get programs data from PHP (context-aware)
            const programsData = [
                <?php 
                if ($showProgramCourses) {
                    // For program courses page, use all programs
                    $programsJson = [];
                    foreach ($allPrograms as $program) {
                        $programsJson[] = json_encode([
                            'id' => $program['id'] ?? '',
                            'program_code' => $program['program_code'] ?? '',
                            'program_name' => $program['program_name'] ?? '',
                            'program_color' => $program['color_code'] ?? '#1976d2'
                        ]);
                    }
                    echo implode(',', $programsJson);
                } else {
                    // For individual course page, use course-specific programs
                    if (!empty($programs)) {
                        $programsJson = [];
                        foreach ($programs as $program) {
                            $programsJson[] = json_encode([
                                'id' => $program['program_id'] ?? '',
                                'program_code' => $program['program_code'] ?? '',
                                'program_name' => $program['program_name'] ?? '',
                                'program_color' => $program['program_color'] ?? '#1976d2'
                            ]);
                        }
                        echo implode(',', $programsJson);
                    }
                }
                ?>
            ];
            
            
            // Prepare course data for the edit modal
            const courseData = {
                course_code: '<?php echo htmlspecialchars($courseDetails['course_code'] ?? ''); ?>',
                course_title: '<?php echo htmlspecialchars($courseDetails['course_title'] ?? ''); ?>',
                units: '<?php echo htmlspecialchars($courseDetails['units'] ?? ''); ?>',
                term: '<?php echo htmlspecialchars($courseDetails['term'] ?? ''); ?>',
                academic_year: '<?php echo htmlspecialchars($courseDetails['academic_year'] ?? ''); ?>',
                year_level: '<?php echo htmlspecialchars($courseDetails['year_level'] ?? ''); ?>',
                programs: programsData
            };
            
            
            // Open the edit course modal using the function from edit_course_modal.php
            if (typeof openEditCourseModal === 'function') {
                openEditCourseModal(courseCode, courseData);
            } else {
                console.error('❌ openEditCourseModal function not found');
                alert('Edit functionality is not available. Please refresh the page and try again.');
            }
        }
        
        // OLD COMPLEX FUNCTION - REMOVED - NOW USING PROPER FORM SUBMISSION
        /*
        async function editCourseOLD(courseCode) {
            
            // Open the modal
            const editModal = document.getElementById('editCourseModal');
            if (!editModal) {
                alert('Edit modal not found. Please refresh the page.');
                return;
            }
            
            editModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Get the actual data from PHP
            const courseCodeValue = '<?php echo htmlspecialchars($courseDetails['course_code'] ?? ''); ?>';
            const courseTitleValue = '<?php echo htmlspecialchars($courseDetails['course_title'] ?? ''); ?>';
            const unitsValue = '<?php echo htmlspecialchars($courseDetails['units'] ?? ''); ?>';
            const termValue = '<?php echo htmlspecialchars($courseDetails['term'] ?? ''); ?>';
            const academicYearValue = '<?php echo htmlspecialchars($courseDetails['academic_year'] ?? ''); ?>';
            const yearLevelValue = '<?php echo htmlspecialchars($courseDetails['year_level'] ?? ''); ?>';
            // Get program data from course details
            const programIdValue = '<?php echo htmlspecialchars($courseDetails['program_id'] ?? ''); ?>';
            const programNameValue = '<?php echo htmlspecialchars($courseDetails['program_name'] ?? ''); ?>';
            const programCodeValue = '<?php echo htmlspecialchars($courseDetails['program_code'] ?? ''); ?>';
            const programColorValue = '<?php echo htmlspecialchars($courseDetails['color_code'] ?? ''); ?>';
            
                courseCode: courseCodeValue,
                courseTitle: courseTitleValue,
                units: unitsValue,
                term: termValue,
                academicYear: academicYearValue,
                yearLevel: yearLevelValue,
                programId: programIdValue,
                programName: programNameValue,
                programCode: programCodeValue,
                programColor: programColorValue,
            });
            
            // Populate basic fields
            document.getElementById('edit_course_code').value = courseCodeValue;
            // Decode HTML entities in course title
            const decodedCourseTitle = courseTitleValue.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#39;/g, "'");
            document.getElementById('edit_course_name').value = decodedCourseTitle;
            document.getElementById('edit_units').value = unitsValue;
            
            // Set year level - fix mapping
            const yearLevelSelect = document.getElementById('edit_year_level');
            if (yearLevelSelect && yearLevelValue) {
                
                // Try different mapping strategies
                let mappedValue = yearLevelValue;
                
                // Strategy 1: Direct match
                if (yearLevelSelect.querySelector(`option[value="${yearLevelValue}"]`)) {
                    mappedValue = yearLevelValue;
                }
                // Strategy 2: Map "1" to "1st Year", "2" to "2nd Year", etc.
                else if (yearLevelValue === '1') {
                    mappedValue = '1st Year';
                } else if (yearLevelValue === '2') {
                    mappedValue = '2nd Year';
                } else if (yearLevelValue === '3') {
                    mappedValue = '3rd Year';
                } else if (yearLevelValue === '4') {
                    mappedValue = '4th Year';
                }
                // Strategy 3: Contains match
                else {
                    for (let option of yearLevelSelect.options) {
                        if (option.value.includes(yearLevelValue) || yearLevelValue.includes(option.value)) {
                            mappedValue = option.value;
                            break;
                        }
                    }
                }
                
                yearLevelSelect.value = mappedValue;
            }
            
            // Set term - map database values to select values
            const termSelect = document.getElementById('edit_school_term');
            if (termSelect && termValue) {
                // Map database term to select value
                let selectValue = termValue;
                if (termValue === '1st') selectValue = '1st';
                else if (termValue === '2nd') selectValue = '2nd';
                else if (termValue === 'summer') selectValue = 'summer';
                
                termSelect.value = selectValue;
            }
            
            // Load school years and set the correct one
            loadSchoolYearsAndSetValue(academicYearValue);
            
            // Set programs - FETCH FROM COURSE_PROGRAMS TABLE
            let programsData = [];
            
            try {
                // Try to fetch from course_programs table
                const response = await fetch(`api/get_course_programs.php?course_code=${encodeURIComponent(courseCodeValue)}`);
                if (response.ok) {
                    const coursePrograms = await response.json();
                    if (coursePrograms && coursePrograms.length > 0) {
                        programsData = coursePrograms.map(program => ({
                            id: program.id,
                            program_code: program.program_code,
                            program_name: program.program_name,
                            program_color: program.color_code || '#1976d2'
                        }));
                    }
                }
            } catch (e) {
            }
            
            // Fallback to single program from course details
            if (programsData.length === 0 && programIdValue && programNameValue) {
                programsData = [{
                    id: programIdValue,
                    program_code: programCodeValue || programNameValue,
                    program_name: programNameValue,
                    program_color: programColorValue || '#1976d2'
                }];
            }
            
            if (programsData.length > 0) {
                document.getElementById('editSelectedProgramsInput').value = JSON.stringify(programsData);
                document.getElementById('editProgramButtonText').textContent = `Select Program(s) - ${programsData.length} Program(s) Selected`;
            } else {
                document.getElementById('editSelectedProgramsInput').value = '';
                document.getElementById('editProgramButtonText').textContent = 'Select Program(s) - No Program Selected';
            }
            
            // Store original values for comparison - IN FORM FIELD FORMAT
            window.originalEditValues = {
                course_code: courseCodeValue,
                course_title: courseTitleValue,
                units: unitsValue,
                term: termValue,
                academic_year: academicYearValue,
                year_level: yearLevelValue, // This will be the form field value after population
                programs: programIdValue ? JSON.stringify([{
                    id: programIdValue,
                    program_code: programNameValue,
                    program_name: programNameValue
                }]) : ''
            };
            
            // Update original values AFTER form fields are populated to match form field formats
            setTimeout(() => {
                window.originalEditValues = {
                    course_code: document.getElementById('edit_course_code').value,
                    course_title: document.getElementById('edit_course_name').value,
                    units: document.getElementById('edit_units').value,
                    term: document.getElementById('edit_school_term').value,
                    academic_year: document.getElementById('edit_school_year').value,
                    year_level: document.getElementById('edit_year_level').value,
                    programs: document.getElementById('editSelectedProgramsInput').value
                };
            }, 100);
            
            
            // Update button with proper disable logic
            const updateBtn = document.getElementById('updateCourseBtn');
            if (updateBtn) {
                // Start with button disabled
                updateBtn.disabled = true;
                updateBtn.style.backgroundColor = '#ccc';
                updateBtn.style.cursor = 'not-allowed';
                updateBtn.style.opacity = '0.6';
                updateBtn.title = 'No changes made';
                
                // Add form change listeners to enable button
                const formFields = [
                    'edit_course_code',
                    'edit_course_name', 
                    'edit_units',
                    'edit_school_term',
                    'edit_school_year',
                    'edit_year_level'
                ];
                
                // SIMPLE: Just add listeners to all fields
                formFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', checkEditFormChanges);
                        field.addEventListener('change', checkEditFormChanges);
                    } else {
                        console.error('❌ Field not found:', fieldId);
                    }
                });
                
                // Add programs change listener
                const programsInput = document.getElementById('editSelectedProgramsInput');
                if (programsInput) {
                    programsInput.addEventListener('change', checkEditFormChanges);
                }
                
                // UPDATE BUTTON FUNCTIONALITY
                updateBtn.onclick = function(e) {
                    e.preventDefault();
                    
                    if (updateBtn.disabled) {
                        return;
                    }
                    
                    // Collect form data
                    const formData = {
                        course_code: document.getElementById('edit_course_code').value,
                        course_title: document.getElementById('edit_course_name').value,
                        units: document.getElementById('edit_units').value,
                        term: document.getElementById('edit_school_term').value,
                        academic_year: document.getElementById('edit_school_year').value,
                        year_level: document.getElementById('edit_year_level').value,
                        programs: document.getElementById('editSelectedProgramsInput').value
                    };
                    
                    
                    // Show loading state
                    updateBtn.disabled = true;
                    updateBtn.textContent = 'UPDATING...';
                    updateBtn.style.backgroundColor = '#ffa500';
                    
                    // Make API call to update course
                    fetch('api/update_course.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        
                        if (data.success) {
                            showUpdateSuccessModal(data.data);
                            
                            // Update the original values to reflect the changes
                            window.originalEditValues = {
                                course_code: formData.course_code,
                                course_title: formData.course_title,
                                units: formData.units,
                                term: formData.term,
                                academic_year: formData.academic_year,
                                year_level: formData.year_level,
                                programs: formData.programs
                            };
                            
                            // Disable the button since no changes remain
                            setTimeout(() => {
                                checkEditFormChanges();
                            }, 100);
                            
                            // Close the edit modal after successful update
                            setTimeout(() => {
                                closeEditCourseModal();
                            }, 2000);
                            
                        } else {
                            showUpdateErrorModal(data.message || 'Update failed. Please check your data and try again.');
                        }
                    })
                    .catch(error => {
                        console.error('❌ API Error:', error);
                        let errorMessage = 'Network error occurred';
                        
                        if (error.message.includes('HTTP error')) {
                            errorMessage = 'Server error occurred. Please try again.';
                        } else if (error.message.includes('Failed to fetch')) {
                            errorMessage = 'Connection failed. Please check your internet connection.';
                        }
                        
                        showUpdateErrorModal(errorMessage);
                    })
                    .finally(() => {
                        // Reset button
                        updateBtn.disabled = false;
                        updateBtn.textContent = 'UPDATE';
                        updateBtn.style.backgroundColor = '#1976d2';
                        updateBtn.style.cursor = 'pointer';
                        updateBtn.style.opacity = '1';
                    });
                };
                
                // Test button removed - Update Button is now working!
                
                
                // START WITH BUTTON DISABLED
                setTimeout(() => {
                    const updateBtn = document.getElementById('updateCourseBtn');
                    if (updateBtn) {
                        updateBtn.disabled = true;
                        updateBtn.setAttribute('disabled', 'disabled');
                        updateBtn.style.backgroundColor = '#ccc';
                        updateBtn.style.cursor = 'not-allowed';
                        updateBtn.style.opacity = '0.6';
                        updateBtn.title = 'No changes made';
                    }
                }, 100);
                
                // PROPER INTERVAL CHECK - Check every 1 second
                const forceCheck = setInterval(() => {
                    if (document.getElementById('editCourseModal').style.display === 'flex') {
                        checkEditFormChanges();
                    } else {
                        clearInterval(forceCheck);
                    }
                }, 1000);
            } else {
                console.error('❌ Update button not found');
            }
        }
        */
        // END OF OLD COMPLEX FUNCTION
        
        // PROPER BUTTON LOGIC - Start disabled, enable only on changes
        function checkEditFormChanges() {
            
            const updateBtn = document.getElementById('updateCourseBtn');
            if (!updateBtn || !window.originalEditValues) {
                return;
            }
            
            // Get current values
            const currentValues = {
                course_code: document.getElementById('edit_course_code').value,
                course_title: document.getElementById('edit_course_name').value,
                units: document.getElementById('edit_units').value,
                term: document.getElementById('edit_school_term').value,
                academic_year: document.getElementById('edit_school_year').value,
                year_level: document.getElementById('edit_year_level').value,
                programs: document.getElementById('editSelectedProgramsInput').value
            };
            
            // DEBUG: Show all values
            
            // NORMALIZE VALUES FOR COMPARISON
            const normalizeYearLevel = (value) => {
                if (value === '1' || value === '1st Year') return '1';
                if (value === '2' || value === '2nd Year') return '2';
                if (value === '3' || value === '3rd Year') return '3';
                if (value === '4' || value === '4th Year') return '4';
                return value;
            };
            
            const normalizePrograms = (value) => {
                if (!value) return '';
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) {
                        // Remove program_color for comparison and sort by id for consistent comparison
                        const normalized = parsed.map(p => ({
                            id: p.id,
                            program_code: p.program_code,
                            program_name: p.program_name
                        })).sort((a, b) => a.id - b.id);
                        return JSON.stringify(normalized);
                    }
                } catch (e) {
                    return value;
                }
                return value;
            };
            
            // Check if ANY field is different from original
            let hasChanges = false;
            const changes = [];
            
            if (currentValues.course_code !== window.originalEditValues.course_code) {
                hasChanges = true;
                changes.push('course_code');
            }
            if (currentValues.course_title !== window.originalEditValues.course_title) {
                hasChanges = true;
                changes.push('course_title');
            }
            if (currentValues.units !== window.originalEditValues.units) {
                hasChanges = true;
                changes.push('units');
            }
            if (currentValues.term !== window.originalEditValues.term) {
                hasChanges = true;
                changes.push('term');
            }
            if (currentValues.academic_year !== window.originalEditValues.academic_year) {
                hasChanges = true;
                changes.push('academic_year');
            }
            
            // NORMALIZED YEAR LEVEL COMPARISON
            const normalizedCurrentYearLevel = normalizeYearLevel(currentValues.year_level);
            const normalizedOriginalYearLevel = normalizeYearLevel(window.originalEditValues.year_level);
            if (normalizedCurrentYearLevel !== normalizedOriginalYearLevel) {
                hasChanges = true;
                changes.push('year_level');
            }
            
            // NORMALIZED PROGRAMS COMPARISON
            const normalizedCurrentPrograms = normalizePrograms(currentValues.programs);
            const normalizedOriginalPrograms = normalizePrograms(window.originalEditValues.programs);
            
            
            if (normalizedCurrentPrograms !== normalizedOriginalPrograms) {
                hasChanges = true;
                changes.push('programs');
            } else {
            }
            
            
            if (hasChanges) {
                // ENABLE BUTTON - There are changes
                updateBtn.disabled = false;
                updateBtn.removeAttribute('disabled');
                updateBtn.style.backgroundColor = '#1976d2';
                updateBtn.style.cursor = 'pointer';
                updateBtn.style.opacity = '1';
                updateBtn.title = 'Update course';
            } else {
                // DISABLE BUTTON - No changes
                updateBtn.disabled = true;
                updateBtn.setAttribute('disabled', 'disabled');
                updateBtn.style.backgroundColor = '#ccc';
                updateBtn.style.cursor = 'not-allowed';
                updateBtn.style.opacity = '0.6';
                updateBtn.title = 'No changes made';
            }
        }
        
        // Function to load school years and set the correct value
        async function loadSchoolYearsAndSetValue(targetYear) {
            
            const schoolYearSelect = document.getElementById('edit_school_year');
            if (!schoolYearSelect) {
                console.error('❌ School year select not found');
                return;
            }
            
            try {
                const response = await fetch('api/get_school_years.php');
                const data = await response.json();
                
                if (data.success && data.school_years) {
                    // Clear existing options except first
                    schoolYearSelect.innerHTML = '<option value="">-- Select Year --</option>';
                    
                    // Add school years
                    data.school_years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.school_year;
                        option.textContent = year.display_text;
                        schoolYearSelect.appendChild(option);
                    });
                    
                    // Set the target year
                    if (targetYear) {
                        schoolYearSelect.value = targetYear;
                    }
                }
            } catch (error) {
                console.error('❌ Error loading school years:', error);
            }
        }
        
        // ORIGINAL CLOSE MODAL FUNCTION - RESTORED
        function closeEditCourseModal() {
            const editModal = document.getElementById('editCourseModal');
            if (editModal) {
                editModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // NOTE: Program selection modal functions are defined in edit_course_modal.php
        // Do not redefine them here to avoid conflicts
        
        // NOTE: All Edit Program modal helper functions are now defined
        // in edit_course_modal.php - removed from here to avoid conflicts
        
        // Make functions globally available
        window.editCourse = editCourse;
        window.closeEditCourseModal = closeEditCourseModal;
        // Note: openEditProgramSelectModal, closeEditProgramSelectModal, 
        // confirmEditProgramSelection, and resetEditProgramSelection
        // are already defined as window functions in edit_course_modal.php
        
        // Debug function to manually test button
        window.testUpdateButton = function() {
            checkEditFormChanges();
        };
        
        // Simple test function to check if listeners are working
        window.testFieldChange = function() {
            const yearLevelField = document.getElementById('edit_year_level');
            if (yearLevelField) {
                yearLevelField.value = '2';
                checkEditFormChanges();
            } else {
                console.error('❌ Year level field not found');
            }
        };
        
        // Test function for program changes
        window.testProgramChange = function() {
            const programsInput = document.getElementById('editSelectedProgramsInput');
            if (programsInput) {
                // Simulate adding a program
                const currentPrograms = JSON.parse(programsInput.value || '[]');
                currentPrograms.push({id: '2', program_code: 'TEST', program_name: 'Test Program'});
                programsInput.value = JSON.stringify(currentPrograms);
                checkEditFormChanges();
            } else {
                console.error('❌ Programs input not found');
            }
        };
        
        // Success and Error Modal Functions are now in edit_course_modal.php
        
        // Assign faculty function
        function assignFaculty(courseCode) {
            alert('Assign faculty to course: ' + courseCode + '\n\nThis will open the faculty assignment functionality.');
        }
        
        // Toggle action menu dropdown
        function toggleActionMenu(event, courseCode, isAssigned) {
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
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
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
        
        // Tab switching functionality
        function switchBookTab(tabName) {
            
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
        
        // View book details function
        function viewBookDetails(bookId) {
            alert('View book details: ' + bookId + '\n\nThis will open the book details functionality.');
        }
        
        // Edit book reference function
        function editBookReference(bookId) {
            alert('Edit book reference: ' + bookId + '\n\nThis will open the book reference edit functionality.');
        }
        
        // NOTE: openAddBookReferenceModal and closeAddBookReferenceModal functions
        // are now defined in add_book_reference_modal.php to avoid conflicts
    </script>

    <!-- Modal is already included by content.php - no need to include again -->
    
    
    <script>
        function closeEditCourseModalBackup() {
            document.getElementById('editCourseModalBackup').style.display = 'none';
        }
        
        // Test function for backup modal
        function testBackupModal() {
            const backupModal = document.getElementById('editCourseModalBackup');
            if (backupModal) {
                backupModal.style.display = 'flex';
            } else {
            }
        }
        
        // Function to navigate to individual course details
        function navigateToCourse(courseCode, courseTitle, courseId) {
            window.location.href = 'content.php?page=course-details&course_code=' + encodeURIComponent(courseCode) + '&course_title=' + encodeURIComponent(courseTitle) + '&course_id=' + encodeURIComponent(courseId);
        }
        
        // Function to navigate to individual course details from program courses
        function navigateToCourseFromProgram(courseCode, courseTitle, programCode, courseId) {
            window.location.href = 'content.php?page=course-details&course_code=' + encodeURIComponent(courseCode) + '&course_title=' + encodeURIComponent(courseTitle) + '&from_program=' + encodeURIComponent(programCode) + '&course_id=' + encodeURIComponent(courseId);
        }
        
        // Function to edit program
        function editProgram(programCode) {
            
            // Get program data from the current page
            const programName = '<?php echo $showProgramCourses && $programInfo ? addslashes($programInfo['program_name']) : ""; ?>';
            const programMajor = '<?php echo $showProgramCourses && $programInfo ? addslashes($programInfo['major'] ?? '') : ""; ?>';
            
            
            // Check if modal exists
            const modal = document.getElementById('editProgramModal');
            
            if (!modal) {
                console.error('❌ Edit program modal not found!');
                alert('Edit program modal not found. Please refresh the page.');
                return;
            }
            
            // Populate the modal with current data
            const codeInput = document.getElementById('editProgramCode');
            const nameInput = document.getElementById('editProgramName');
            const majorInput = document.getElementById('editProgramMajor');
            
            if (codeInput) codeInput.value = programCode;
            if (nameInput) nameInput.value = programName;
            if (majorInput) majorInput.value = programMajor;
            
            // Store original data for change detection
            setTimeout(() => {
                if (window.storeOriginalEditProgramData) {
                    storeOriginalEditProgramData();
                }
                if (window.checkEditProgramFormValidity) {
                    checkEditProgramFormValidity();
                }
            }, 100);
            
            // Show the modal
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
        }
        
        // Function to close edit program modal
        function closeEditProgramModal() {
            const modal = document.getElementById('editProgramModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Store original form data for comparison
        let originalEditProgramData = {};

        // Form event listeners for edit program modal
        document.addEventListener('DOMContentLoaded', function() {
            // Add form change listeners for edit program modal
            setupEditProgramFormListeners();
            
            // Handle form submission
            const editForm = document.getElementById('editProgramForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const updateBtn = document.getElementById('updateProgramBtn');
                    
                    if (updateBtn && updateBtn.disabled) {
                        return;
                    }
                    
                    // Disable button during submission
                    if (updateBtn) {
                        updateBtn.disabled = true;
                        updateBtn.textContent = 'UPDATING...';
                    }
                    
                    fetch('../process_edit_program.php', {
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
                        if (updateBtn) {
                            updateBtn.disabled = false;
                            updateBtn.textContent = 'UPDATE';
                        }
                    });
                });
            }
        });

        // Setup form listeners for edit program modal
        function setupEditProgramFormListeners() {
            const form = document.getElementById('editProgramForm');
            if (!form) return;

            const inputs = form.querySelectorAll('input[type="text"]');
            inputs.forEach(input => {
                input.addEventListener('input', checkEditProgramFormValidity);
                input.addEventListener('change', checkEditProgramFormValidity);
            });
        }

        // Check if edit program form has changes and is valid
        function checkEditProgramFormValidity() {
            const updateBtn = document.getElementById('updateProgramBtn');
            if (!updateBtn) return;

            const programName = document.getElementById('editProgramName').value.trim();
            const major = document.getElementById('editProgramMajor').value.trim();

            // Check if form is valid
            const isValid = programName.length > 0;

            // Check if there are changes from original data
            const hasChanges = originalEditProgramData.program_name !== programName ||
                              originalEditProgramData.major !== major;

                isValid: isValid,
                hasChanges: hasChanges,
                programName: programName,
                major: major,
                originalData: originalEditProgramData
            });

            // Enable button only if form is valid AND has changes
            updateBtn.disabled = !(isValid && hasChanges);
        }

        // Store original form data when modal opens
        function storeOriginalEditProgramData() {
            originalEditProgramData = {
                program_name: document.getElementById('editProgramName').value.trim(),
                major: document.getElementById('editProgramMajor').value.trim()
            };
        }
        
        // HEADER ALIGNMENT - FORCE HEADERS TO MATCH DATA
        function fixHeaderAlignment() {
            const headers = document.querySelectorAll('.courses-table th');
            headers.forEach((header, index) => {
                switch(index) {
                    case 0: // Course Code
                        header.style.textAlign = 'left';
                        header.style.setProperty('text-align', 'left', 'important');
                        break;
                    case 1: // Course Title
                        header.style.textAlign = 'left';
                        header.style.setProperty('text-align', 'left', 'important');
                        break;
                    case 2: // Units
                        header.style.textAlign = 'center';
                        header.style.setProperty('text-align', 'center', 'important');
                        break;
                    case 3: // Term & Academic Year
                        header.style.textAlign = 'left';
                        header.style.setProperty('text-align', 'left', 'important');
                        break;
                    case 4: // Year Level
                        header.style.textAlign = 'center';
                        header.style.setProperty('text-align', 'center', 'important');
                        break;
                    case 5: // Faculty
                        header.style.textAlign = 'left';
                        header.style.setProperty('text-align', 'left', 'important');
                        break;
                    case 6: // References
                        header.style.textAlign = 'center';
                        header.style.setProperty('text-align', 'center', 'important');
                        break;
                    case 7: // Actions
                        header.style.textAlign = 'left';
                        header.style.setProperty('text-align', 'left', 'important');
                        break;
                }
            });
        }

        // DATA CELLS ALIGNMENT - FORCE DATA TO MATCH HEADERS
        function fixDataAlignment() {
            const rows = document.querySelectorAll('.courses-table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    // Remove any existing text-align styles first
                    cell.style.removeProperty('text-align');
                    
                    switch(index) {
                        case 0: // Course Code
                            cell.style.setProperty('text-align', 'left', 'important');
                            cell.style.setProperty('padding', '8px 8px 8px 24px', 'important');
                            cell.style.setProperty('font-weight', '600', 'important');
                            cell.style.setProperty('color', '#333', 'important');
                            break;
                        case 1: // Course Title
                            cell.style.setProperty('text-align', 'left', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: left !important;');
                            break;
                        case 2: // Units
                            cell.style.setProperty('text-align', 'center', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: center !important;');
                            break;
                        case 3: // Term & Academic Year
                            cell.style.setProperty('text-align', 'left', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: left !important;');
                            break;
                        case 4: // Year Level
                            cell.style.setProperty('text-align', 'center', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: center !important;');
                            break;
                        case 5: // Faculty
                            cell.style.setProperty('text-align', 'left', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: left !important;');
                            break;
                        case 6: // References
                            cell.style.setProperty('text-align', 'center', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: center !important;');
                            break;
                        case 7: // Actions
                            cell.style.setProperty('text-align', 'left', 'important');
                            cell.setAttribute('style', cell.getAttribute('style') + '; text-align: left !important;');
                            break;
                    }
                });
            });
        }

        // COURSE CODE ALIGNMENT - FOCUSED FIX
        function fixCourseCodeAlignment() {
            const courseCodeCells = document.querySelectorAll('.course-code');
            courseCodeCells.forEach(cell => {
                cell.style.textAlign = 'left';
                cell.style.padding = '8px 8px 8px 24px';
                cell.style.verticalAlign = 'middle';
                cell.style.fontWeight = '600';
                cell.style.color = '#333';
            });
        }
        
        // FORCE ALIGNMENT WITH JAVASCRIPT - NUCLEAR OPTION
        function forceTableAlignment() {
            const table = document.querySelector('.courses-table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        // Force alignment based on column position
                        switch(index) {
                            case 0: // Course Code
                                cell.style.textAlign = 'left';
                                cell.style.padding = '8px 8px 8px 24px';
                                cell.style.fontWeight = '600';
                                cell.style.color = '#333';
                                break;
                            case 1: // Course Title
                                cell.style.textAlign = 'left';
                                cell.style.padding = '8px 6px';
                                break;
                            case 2: // Units
                                cell.style.textAlign = 'center';
                                cell.style.padding = '8px 6px';
                                break;
                            case 3: // Term & Academic Year
                                cell.style.textAlign = 'left';
                                cell.style.padding = '6px 4px';
                                break;
                            case 4: // Year Level
                                cell.style.textAlign = 'center';
                                cell.style.verticalAlign = 'middle';
                                cell.style.padding = '6px 4px';
                                break;
                            case 5: // Faculty
                                cell.style.textAlign = 'left';
                                cell.style.padding = '8px 6px';
                                break;
                            case 6: // References
                                cell.style.textAlign = 'center';
                                cell.style.padding = '8px 6px';
                                break;
                            case 7: // Actions
                                cell.style.textAlign = 'left';
                                cell.style.padding = '8px 6px';
                                break;
                        }
                    });
                });
            }
        }
        
        // Simple page load - no complex JavaScript needed
        document.addEventListener('DOMContentLoaded', function() {
        });

        
        // Function to perform program course search
        function performProgramSearch() {
            const searchTerm = document.getElementById('programCourseSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.courses-table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const courseCode = row.cells[0].textContent.toLowerCase();
                const courseTitle = row.cells[1].textContent.toLowerCase();
                const term = row.cells[3].textContent.toLowerCase();
                const yearLevel = row.cells[4].textContent.toLowerCase();
                const faculty = row.cells[5].textContent.toLowerCase();
                
                if (courseCode.includes(searchTerm) || 
                    courseTitle.includes(searchTerm) || 
                    term.includes(searchTerm) || 
                    yearLevel.includes(searchTerm) || 
                    faculty.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('programCourseCountDisplay').innerHTML = 
                '<strong>' + visibleCount + ' courses</strong> found';
        }
        
        // Section filtering function
        function filterCoursesBySection() {
            const dropdown = document.getElementById('sectionDropdown');
            const selectedSection = dropdown.value;
            const table = document.getElementById('coursesTable');
            const rows = table.querySelectorAll('tbody tr');
            const totalCountSpan = document.getElementById('totalCourseCount');
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                const section = row.getAttribute('data-section');
                
                if (selectedSection === '' || section === selectedSection) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update course count
            if (totalCountSpan) {
                totalCountSpan.textContent = visibleCount + ' courses';
            }
        }
        
    </script>
</body>
</html>
