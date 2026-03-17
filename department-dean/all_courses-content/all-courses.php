<?php
// all-courses.php for Department Dean
// This file displays all courses in a table format with back navigation

// Initialize courses array
$courses = [];

// Database courses will be loaded below
/*
$courses = [
    [
        'course_code' => 'IT101',
        'course_title' => 'Introduction to Information Technology',
        'program_code' => 'BSIT',
        'program_name' => 'Bachelor of Science in Information Technology',
        'program_color' => '#4A7AF2', // BLUE
        'units' => 3,
        'faculty' => 'Prof. Alice Smith',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ],
    [
        'course_code' => 'CS101',
        'course_title' => 'Introduction to Computer Science',
        'program_code' => 'BSCS',
        'program_name' => 'Bachelor of Science in Computer Science',
        'program_color' => '#14A338', // GREEN
        'units' => 3,
        'faculty' => 'Prof. Carol Lee',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ],
    [
        'course_code' => 'MATH101',
        'course_title' => 'Calculus I',
        'program_code' => 'BSIT, BSCS, BSIS',
        'program_name' => 'Shared Foundation Course',
        'program_color' => '#9C27B0', // PURPLE for shared courses
        'units' => 4,
        'faculty' => 'Prof. David Wilson',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ],
    [
        'course_code' => 'ENG101',
        'course_title' => 'Technical Writing',
        'program_code' => 'BSIT, BSCS, BSIS, BLIS',
        'program_name' => 'Common Core Course',
        'program_color' => '#FF9800', // ORANGE for common core
        'units' => 3,
        'faculty' => 'Prof. Emma Davis',
        'status' => 'Active',
        'term' => '2nd Semester',
        'academic_year' => 'A.Y. 2024-2025',
        'year_level' => '2nd Year'
    ],
    [
        'course_code' => 'IT201',
        'course_title' => 'Web Development Fundamentals',
        'program_code' => 'BSIT',
        'program_name' => 'Bachelor of Science in Information Technology',
        'program_color' => '#4A7AF2', // BLUE
        'units' => 3,
        'faculty' => '',
        'status' => 'Active',
        'term' => '2nd Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '2nd Year'
    ],
    [
        'course_code' => 'STAT101',
        'course_title' => 'Statistics for Computing',
        'program_code' => 'BSIT, BSCS, BSIS',
        'program_name' => 'Shared Foundation Course',
        'program_color' => '#9C27B0', // PURPLE for shared courses
        'units' => 3,
        'faculty' => 'Prof. Frank Miller',
        'status' => 'Active',
        'term' => '2nd Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '2nd Year'
    ],
    [
        'course_code' => 'IT301',
        'course_title' => 'Database Management Systems',
        'program_code' => 'BSIT',
        'program_name' => 'Bachelor of Science in Information Technology',
        'program_color' => '#4A7AF2', // BLUE
        'units' => 3,
        'faculty' => 'Prof. Bob Garcia',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2024-2025',
        'year_level' => '3rd Year'
    ],
    [
        'course_code' => 'ETH101',
        'course_title' => 'Ethics in Information Technology',
        'program_code' => 'BSIT, BSCS, BSIS, BLIS',
        'program_name' => 'Common Core Course',
        'program_color' => '#FF9800', // ORANGE for common core
        'units' => 2,
        'faculty' => 'Prof. Grace Johnson',
        'status' => 'Active',
        'term' => 'Summer',
        'academic_year' => 'A.Y. 2024-2025',
        'year_level' => '4th Year'
    ],
    [
        'course_code' => 'CS101',
        'course_title' => 'Introduction to Computer Science',
        'program_code' => 'BSCS',
        'program_name' => 'Bachelor of Science in Computer Science',
        'program_color' => '#14A338', // GREEN
        'units' => 3,
        'faculty' => 'Prof. Carol Lee',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ],
    [
        'course_code' => 'CS201',
        'course_title' => 'Data Structures and Algorithms',
        'program_code' => 'BSCS',
        'program_name' => 'Bachelor of Science in Computer Science',
        'program_color' => '#14A338', // GREEN
        'units' => 3,
        'faculty' => '',
        'status' => 'Active',
        'term' => '2nd Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '2nd Year'
    ],
    [
        'course_code' => 'IS101',
        'course_title' => 'Fundamentals of Information Systems',
        'program_code' => 'BSIS',
        'program_name' => 'Bachelor of Science in Information Systems',
        'program_color' => '#E6AA28', // YELLOW
        'units' => 3,
        'faculty' => '',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ],
    [
        'course_code' => 'LIS101',
        'course_title' => 'Introduction to Library and Information Science',
        'program_code' => 'BLIS',
        'program_name' => 'Bachelor of Library and Information Science',
        'program_color' => '#CD2323', // RED
        'units' => 3,
        'faculty' => '',
        'status' => 'Active',
        'term' => '1st Semester',
        'academic_year' => 'A.Y. 2025-2026',
        'year_level' => '1st Year'
    ]
];
*/

// --- DATABASE CODE FOR REAL DATA ---
// Check if the connection was successful before proceeding
if (isset($pdo)) { 
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    // Get selected term from URL parameter first, then session, then default to 'all'
    $selectedTermId = $_GET['term_id'] ?? $_SESSION['selectedTermId'] ?? 'all';
    
    // Update session with the term ID for consistency
    $_SESSION['selectedTermId'] = $selectedTermId;
    
    if ($deanDepartmentCode) {
        // Build query based on selected term
        if ($selectedTermId === 'all') {
            // Show all courses for current academic year - FILTERED BY DEPARTMENT
            $query = "
                SELECT 
                    c.id,
                    c.course_code,
                    c.course_title,
                    c.units,
                    c.status,
                    c.term,
                    COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                    c.year_level,
                    c.faculty_id,
                    p.program_code,
                    p.program_name,
                    p.major,
                    p.color_code,
                    CONCAT(u.first_name, ' ', u.last_name) AS faculty_name
                FROM 
                    courses c
                LEFT JOIN 
                    programs p ON c.program_id = p.id
                LEFT JOIN 
                    users u ON c.faculty_id = u.id
                LEFT JOIN 
                    school_years sy ON c.academic_year = sy.id
                WHERE 
                    p.department_id = (SELECT id FROM departments WHERE department_code = ?)
                ORDER BY 
                    c.year_level ASC, c.term ASC, c.course_code ASC, p.program_code ASC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$deanDepartmentCode]);
        } else {
            // Show courses for specific term
            // First get the term name from the term ID
            $termQuery = "SELECT name FROM terms WHERE id = ?";
            $termStmt = $pdo->prepare($termQuery);
            $termStmt->execute([$selectedTermId]);
            $termData = $termStmt->fetch(PDO::FETCH_ASSOC);
            $termName = $termData['name'] ?? null;
            
            if ($termName) {
                $query = "
                    SELECT 
                        c.id,
                        c.course_code,
                        c.course_title,
                        c.units,
                        c.status,
                        c.term,
                        COALESCE(sy.school_year_label, c.academic_year) as academic_year,
                        c.year_level,
                        c.faculty_id,
                        p.program_code,
                        p.program_name,
                        p.major,
                        p.color_code,
                        CONCAT(u.first_name, ' ', u.last_name) AS faculty_name
                    FROM 
                        courses c
                    LEFT JOIN 
                        programs p ON c.program_id = p.id
                    LEFT JOIN 
                        users u ON c.faculty_id = u.id
                    LEFT JOIN 
                        school_years sy ON c.academic_year = sy.id
                    WHERE 
                        c.term = ? 
                        AND p.department_id = (SELECT id FROM departments WHERE department_code = ?)
                    ORDER BY 
                        c.year_level ASC, c.term ASC, c.course_code ASC, p.program_code ASC
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$termName, $deanDepartmentCode]);
            } else {
                // If term not found, show no courses
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE 1=0");
                $stmt->execute();
            }
        }
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $courses = []; // Clear dummy data
            $mergedCourses = []; // Array to store merged courses
            
            foreach ($result as $row) {
                $courseCode = $row['course_code'];
                
                if (isset($mergedCourses[$courseCode])) {
                    // Course already exists, merge the programs
                    $mergedCourses[$courseCode]['programs'][] = [
                        'program_code' => $row['program_code'],
                        'program_name' => $row['program_name'],
                        'program_major' => $row['major'] ?? null,
                        'program_color' => $row['color_code'] ?? '#1976d2' // Use database color or default
                    ];
                } else {
                    // New course, create entry
                    $mergedCourses[$courseCode] = $row;
                    $mergedCourses[$courseCode]['programs'] = [[
                        'program_code' => $row['program_code'],
                        'program_name' => $row['program_name'],
                        'program_major' => $row['major'] ?? null,
                        'program_color' => $row['color_code'] ?? '#1976d2' // Use database color or default
                    ]];
                }
            }
            
            // Convert merged courses back to array format
            foreach ($mergedCourses as $course) {
                $courses[] = $course;
            }
        } else {
            $courses = []; // Use empty array if no courses found
        }
    }
}
?>

<style>
.all-courses-container {
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
.back-navigation {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 10px 0;
}
.back-button {
    display: flex;
    align-items: center;
    background: #1976d2;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    padding: 10px 16px;
    border-radius: 8px;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}
.back-button:hover {
    background-color: #1565c0;
}
.back-button img {
    width: 20px;
    height: 20px;
    margin-right: 8px;
}
.courses-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 20px;
}
.courses-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'TT Interphases', sans-serif;
    table-layout: fixed;
}
.courses-table th {
    background-color: #f8f9fa;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
    font-size: 14px;
}
.courses-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    color: #555;
}

/* Column width specifications - UPDATED FOR 9 COLUMNS */
.courses-table th:nth-child(1),
.courses-table td:nth-child(1) { /* Course Code */
    width: 10%;
    min-width: 90px;
    padding: 12px 8px 12px 24px; /* Left padding matches section header padding */
}

.courses-table th:nth-child(2),
.courses-table td:nth-child(2) { /* Course Title */
    width: 30%; /* Extended from 26% to 30% to use freed space from Faculty */
    padding: 12px 8px;
}

.courses-table th:nth-child(3),
.courses-table td:nth-child(3) { /* Units */
    width: 6%;
}

.courses-table th:nth-child(4),
.courses-table td:nth-child(4) { /* Program */
    width: 12%; /* Extended from 10% to 12% */
    padding: 12px 8px; /* Added padding for spacing */
}

.courses-table th:nth-child(5),
.courses-table td:nth-child(5) { /* Term & Academic Year */
    width: 11%; /* Reduced from 12% to 11% to accommodate Course Title extension */
    min-width: 110px;
    padding: 12px 8px; /* Added padding for spacing */
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
}

.courses-table th:nth-child(6),
.courses-table td:nth-child(6) { /* Year Level */
    width: 8%;
    min-width: 70px;
    padding: 10px 6px;
}

.courses-table th:nth-child(7),
.courses-table td:nth-child(7) { /* Faculty */
    width: 20%; /* Reduced from 24% to 20% for more compact Faculty column */
    min-width: 130px; /* Reduced from 150px to 130px */
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    padding: 12px 8px;
}

.courses-table th:nth-child(8),
.courses-table td:nth-child(8) { /* References */
    width: 8%; /* Extended from 6% to 8% */
    padding: 12px 12px 12px 8px; /* Fine-tuned right padding for better flow */
    text-align: center; /* Center align for better visual connection */
}

.courses-table th:nth-child(9),
.courses-table td:nth-child(9) { /* Actions */
    width: 10%; /* Reduced width to minimize right gap */
    min-width: 100px; /* Reduced min-width */
    padding: 12px 0px 12px 8px; /* Zero right padding to eliminate right gap */
    text-align: right;
    position: relative; /* Added relative positioning for absolute child */
}

/* Force Actions header to align with button */
.courses-table th:nth-child(9) {
    position: relative;
}

.courses-table th:nth-child(9) {
    color: transparent; /* Hide original text */
}

.courses-table th:nth-child(9)::after {
    content: "Actions";
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 600;
    color: #333;
}
.courses-table tr:hover {
    background-color: #e3f2fd;
}

/* Visual connection between References and Actions columns */
.courses-table th:nth-child(8),
.courses-table td:nth-child(8) { /* References */
    border-right: 1px solid #e9ecef; /* Subtle border to connect with Actions */
}

.courses-table th:nth-child(9),
.courses-table td:nth-child(9) { /* Actions */
    border-left: 1px solid #e9ecef; /* Subtle border to connect with References */
}

/* Section Headers */
.course-section-header {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
    padding: 16px 24px;
    margin: 20px 0 0 0;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    border: 1px solid #FFD700;
}

.course-section-header:first-child {
    margin-top: 0;
}

.course-section-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    color: #000;
}

.course-section-header .course-count {
    background: rgba(0, 0, 0, 0.1);
    color: #000;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid rgba(0, 0, 0, 0.2);
}

/* Section Selector Styles */
.section-selector {
    display: flex;
    align-items: center;
}

.section-dropdown {
    background: white;
    border: 2px solid rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    min-width: 300px;
    cursor: pointer;
    transition: border-color 0.2s ease;
}

.section-dropdown:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

.section-dropdown:hover {
    border-color: rgba(0, 0, 0, 0.3);
}

/* Action Menu Styles */
.action-menu-container {
    position: absolute;
    right: 32px; /* Positioned 32px from the right edge */
    top: 50%;
    transform: translateY(-50%);
    width: auto;
    text-align: right;
    z-index: 1000; /* Ensure container is above table rows */
}

.action-menu-btn {
    background: none;
    border: none;
    padding: 10px; /* Increased from 8px to 10px for better touch area */
    cursor: pointer;
    border-radius: 6px; /* Increased from 4px to 6px for better appearance */
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px; /* Increased from 32px to 36px for better button size */
    height: 36px; /* Increased from 32px to 36px for better button size */
}

.action-menu-btn:hover {
    background-color: #f0f0f0;
}

.three-dots {
    font-size: 18px;
    font-weight: bold;
    color: #666;
    line-height: 1;
}

.action-menu-dropdown {
    position: absolute;
    top: 50%;
    right: 100%; /* Positioned to the left of the button */
    transform: translateY(-50%);
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 99999; /* Maximum z-index to ensure it appears above hovered rows */
    min-width: 120px;
    padding: 4px 0;
    margin-right: 8px; /* Small gap between button and dropdown */
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

.course-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.course-row:hover {
    background-color: #e3f2fd !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.course-code {
    font-weight: 600;
    color: #1976d2;
}
           .program-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }
    
    .program-display {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Removed clickable-program styles - program badges are no longer clickable */
    
    .additional-programs {
        color: #666;
        font-size: 11px;
        font-weight: 700;
        cursor: help;
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        margin-left: 4px;
        display: inline-block;
        min-width: 20px;
        text-align: center;
    }
    
    .additional-programs:hover {
        background: #e9ecef;
        color: #495057;
        border-color: #adb5bd;
    }
.faculty-name {
    font-weight: 500;
    color: #333;
}
.unassigned {
    color: #dc3545;
    font-style: italic;
}
.status-active {
    color: #28a745;
    font-weight: 600;
}
.status-inactive {
    color: #6c757d;
    font-weight: 600;
}
.no-courses {
    text-align: center;
    padding: 40px;
    color: #777;
    font-size: 16px;
}

/* Empty State Styling */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
    margin: 20px 0;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.6;
}

.empty-state h3 {
    color: #495057;
    font-size: 1.5rem;
    margin-bottom: 10px;
    font-family: 'TT Interphases', sans-serif;
}

.empty-state p {
    color: #6c757d;
    font-size: 1rem;
    margin-bottom: 25px;
    font-family: 'TT Interphases', sans-serif;
}

.empty-state .create-first-course-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'TT Interphases', sans-serif;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
    position: relative;
    overflow: hidden;
    margin: 0 auto;
}

.empty-state .create-first-course-btn:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
}

.empty-state .create-first-course-btn .btn-icon {
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease;
}

.empty-state .create-first-course-btn:hover .btn-icon {
    transform: rotate(90deg);
}

.empty-state .create-first-course-btn .btn-text {
    font-weight: 600;
}

.search-container {
    margin: 0;
}
.user-search-bar {
    display: flex;
    align-items: center;
    background-color: #FFFFFF;
    height: 38px;
    width: 450px;
    border-radius: 6px;
    padding: 0 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.user-search-bar .magnifier-icon {
    width: 20px;
    height: 20px;
    margin-right: 8px;
}
.user-search-bar input {
    border: none;
    outline: none;
    font-size: 16px;
    color: #333;
    flex: 1;
    background: transparent;
}
.user-search-bar input::placeholder {
    color: #D9D9D9;
    font-size: 16px;
}
.search-button {
    background-color: #0077FF;
    color: #FFFFFF;
    font-size: 16px;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}
.search-button:hover {
    opacity: 0.9;
}
.clear-search-btn {
    background: none;
    border: none;
    color: #999;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 8px;
    transition: color 0.2s ease;
}
.clear-search-btn:hover {
    color: #666;
}
.clear-search-btn:active {
    color: #333;
}
 .hidden-row {
     display: none;
 }
 .actions-cell {
     text-align: center;
 }
 .action-btn {
     padding: 6px 12px;
     border: none;
     border-radius: 4px;
     font-size: 12px;
     font-weight: 600;
     cursor: pointer;
     transition: all 0.2s ease;
     font-family: 'TT Interphases', sans-serif;
 }
 .edit-btn {
     background-color: #1976d2;
     color: white;
 }
 .edit-btn:hover {
     background-color: #1565c0;
 }
 .assign-btn {
     background-color: #28a745;
     color: white;
 }
 .assign-btn:hover:not(.disabled) {
     background-color: #218838;
 }
 .assign-btn.disabled {
     background-color: #6c757d;
     color: #adb5bd;
     cursor: not-allowed;
     opacity: 0.6;
 }
/* Force table layout and column widths */
.courses-table {
    table-layout: fixed !important;
    width: 100% !important;
}

/* Column width overrides */
.courses-table th:nth-child(5),  /* Term & Academic Year */
.courses-table td:nth-child(5) {
    width: 120px !important;
    max-width: 120px !important;
    min-width: 120px !important;
}

.courses-table th:nth-child(7),  /* Faculty */
.courses-table td:nth-child(7) {
    width: 250px !important;
    max-width: 250px !important;
    min-width: 250px !important;
}

.courses-table th:nth-child(8),  /* References */
.courses-table td:nth-child(8) {
    width: 80px !important;
    max-width: 80px !important;
    min-width: 80px !important;
}

.courses-table th:nth-child(9),  /* Actions */
.courses-table td:nth-child(9) {
    width: 100px !important;
    max-width: 100px !important;
    min-width: 100px !important;
}

/* Override specific columns to be centered */
.courses-table th:nth-child(3),  /* Units */
.courses-table th:nth-child(6),  /* Year Level */
.courses-table th:nth-child(8),  /* References */
.courses-table th:nth-child(9) { /* Actions */
    text-align: center !important;
}

/* Override specific columns to be left-aligned */
.courses-table th:nth-child(7) { /* Faculty */
    text-align: left !important;
}

/* Override specific data cells to be centered */
.courses-table td:nth-child(3) { /* Units */
    text-align: center !important;
}

.courses-table td:nth-child(6) { /* Year Level */
    text-align: center !important;
}

.courses-table td:nth-child(8) { /* References */
    text-align: center !important;
}

/* Override specific data cells to be left-aligned */
.courses-table td:nth-child(7) { /* Faculty */
    text-align: left !important;
}

.courses-table td:nth-child(9) { /* Actions */
    text-align: left !important;
}

/* Floating Back to Top Button Styles */
.back-to-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
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

/* Book References Modal Styles */
.references-btn {
    background: #4CAF50 !important;
    color: white !important;
    border: none !important;
    padding: 6px 12px !important;
    border-radius: 4px !important;
    cursor: pointer !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    font-family: 'TT Interphases', sans-serif !important;
}

.references-btn:hover {
    background: #45a049 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3) !important;
}

.book-reference-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.book-reference-item:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.book-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-family: 'TT Interphases', sans-serif;
}

.book-author {
    font-size: 14px;
    color: #666;
    margin-bottom: 6px;
    font-family: 'TT Interphases', sans-serif;
}

.book-details {
    display: flex;
    gap: 16px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}

.book-detail-item {
    font-size: 12px;
    color: #666;
    background: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    font-family: 'TT Interphases', sans-serif;
}

.book-availability {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.availability-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.availability-available {
    background: #e8f5e8;
    color: #2e7d32;
}

.availability-checked-out {
    background: #fff3e0;
    color: #f57c00;
}

.availability-reserved {
    background: #e3f2fd;
    color: #1976d2;
}

.book-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.book-action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'TT Interphases', sans-serif;
}

.view-book-btn {
    background: #1976d2;
    color: white;
}

.view-book-btn:hover {
    background: #1565c0;
}

.request-book-btn {
    background: #4CAF50;
    color: white;
}

.request-book-btn:hover {
    background: #45a049;
}

.no-references {
    text-align: center;
    padding: 40px 20px;
    color: #666;
    font-style: italic;
    font-family: 'TT Interphases', sans-serif;
}
</style>

<div class="back-navigation">
    <button class="back-button" onclick="window.location.href='content.php?page=dashboard<?php echo isset($_GET['term_id']) ? '&term_id=' . urlencode($_GET['term_id']) : ''; ?>'">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back" onerror="this.style.display='none'; this.nextSibling.style.display='inline';">
        <span style="display: none;">←</span>
        Back to Dashboard
    </button>
</div>

<div style="margin: 20px 0;">
    <h2 class="main-page-title" style="padding-left: 0px; margin: 0 0 20px 0;">All Courses</h2>
    
    <div class="search-container">
        <div style="display: flex; align-items: center; justify-content: space-between; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="user-search-bar" style="width: 450px;">
                    <img src="../src/assets/icons/magnifier-icon.png" alt="Search" class="magnifier-icon">
                    <input type="text" placeholder="Search courses by code, title, program, term, academic year, year level, or faculty..." id="courseSearch" autocomplete="off">
                    <button type="button" id="clearSearch" class="clear-search-btn" style="display: none;">&times;</button>
                </div>
                <button class="search-button" onclick="performSearch()">Search</button>
            </div>
            <div id="courseCountDisplay" style="color: #666; font-size: 16px; font-family: 'TT Interphases', sans-serif;">
                <strong><?php echo count($courses); ?> courses</strong> found
            </div>
        </div>
    </div>
</div>

<div class="courses-table-container">
    <?php if (!empty($courses)): ?>
        <?php
        // Group courses by year level and semester
        $groupedCourses = [];
        foreach ($courses as $course) {
            $yearLevel = $course['year_level'];
            $term = $course['term'];
            $academicYear = $course['academic_year'];
            
            // Format year level (1 -> 1st Year, 2 -> 2nd Year, etc.)
            $yearLevelFormatted = '';
            switch($yearLevel) {
                case '1': $yearLevelFormatted = '1st Year'; break;
                case '2': $yearLevelFormatted = '2nd Year'; break;
                case '3': $yearLevelFormatted = '3rd Year'; break;
                case '4': $yearLevelFormatted = '4th Year'; break;
                default: $yearLevelFormatted = $yearLevel . ' Year'; break;
            }
            
            // Format term for consistent section key
            $termFormatted = '';
            if ($term == '1st') $termFormatted = '1st Semester';
            elseif ($term == '2nd') $termFormatted = '2nd Semester';
            elseif ($term == 'summer') $termFormatted = 'Summer Semester';
            elseif (strpos($term, 'Semester') !== false) $termFormatted = $term;
            else $termFormatted = $term ?: 'N/A';
            
            $sectionKey = $yearLevelFormatted . ' - ' . $termFormatted . ' of ' . $academicYear;
            
            if (!isset($groupedCourses[$sectionKey])) {
                $groupedCourses[$sectionKey] = [];
            }
            $groupedCourses[$sectionKey][] = $course;
        }
        
        // Create sorted array for dropdown
        $sortedGroupedCourses = [];
        
        // Get all section keys and sort them
        $sectionKeys = array_keys($groupedCourses);
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
            $sortedGroupedCourses[$key] = $groupedCourses[$key];
        }
        ?>
        
        <!-- Section Selection Dropdown -->
        <div class="course-section-header">
            <div class="section-selector">
                <label for="sectionDropdown" style="color: #000; font-weight: 600; margin-right: 12px;">View Courses:</label>
                <select id="sectionDropdown" class="section-dropdown" onchange="filterCoursesBySection()">
                    <option value="">All Sections</option>
                    <?php foreach ($sortedGroupedCourses as $sectionTitle => $sectionCourses): ?>
                        <option value="<?php echo htmlspecialchars($sectionTitle); ?>" data-count="<?php echo count($sectionCourses); ?>">
                            <?php echo htmlspecialchars($sectionTitle); ?> (<?php echo count($sectionCourses); ?> courses)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="course-count" id="totalCourseCount"><?php echo count($courses); ?> courses</span>
        </div>
        
        <!-- Single Table for All Courses -->
        <table class="courses-table" id="coursesTable" style="table-layout: fixed !important; width: 100% !important;">
            <colgroup>
                <col style="width: auto;">
                <col style="width: auto;">
                <col style="width: 60px;">
                <col style="width: auto;">
                <col style="width: 120px;">
                <col style="width: 80px;">
                <col style="width: 180px;">
                <col style="width: 90px;">
                <col style="width: 90px;">
            </colgroup>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Units</th>
                    <th>Program</th>
                    <th style="width: 120px !important; max-width: 120px !important; min-width: 120px !important;">Term &<br>Academic Year</th>
                    <th>Year Level</th>
                    <th style="width: 180px !important; max-width: 180px !important; min-width: 180px !important;">Faculty</th>
                    <th style="width: 90px !important; max-width: 90px !important; min-width: 90px !important;">References</th>
                    <th style="width: 90px !important; max-width: 90px !important; min-width: 90px !important; text-align: center !important;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr class="course-row" data-section="<?php 
                        // Create section identifier for filtering
                        $yearLevel = $course['year_level'];
                        $term = $course['term'];
                        $academicYear = $course['academic_year'];
                        
                        // Format year level
                        $yearLevelFormatted = '';
                        switch($yearLevel) {
                            case '1': $yearLevelFormatted = '1st Year'; break;
                            case '2': $yearLevelFormatted = '2nd Year'; break;
                            case '3': $yearLevelFormatted = '3rd Year'; break;
                            case '4': $yearLevelFormatted = '4th Year'; break;
                            default: $yearLevelFormatted = $yearLevel . ' Year'; break;
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
                    ?>" onclick="navigateToCourse('<?php echo htmlspecialchars($course['course_code']); ?>', '<?php echo htmlspecialchars($course['course_title']); ?>', <?php echo intval($course['id']); ?>)" style="cursor: pointer;">
                        <td class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                        <td class="units-count" style="text-align: center;"><?php echo htmlspecialchars($course['units']); ?></td>
                        <td>
                             <?php
                             // Check if course has multiple programs (merged structure)
                             if (isset($course['programs']) && count($course['programs']) > 1) {
                                 $primaryProgram = $course['programs'][0];
                                 $additionalCount = count($course['programs']) - 1;
                                 
                                 echo '<div class="program-display">';
                                 echo '<div>';
                                 echo '<span class="program-badge" style="background-color: ' . $primaryProgram['program_color'] . '; color: white;">';
                                 echo htmlspecialchars($primaryProgram['program_code']);
                                 echo '</span>';
                                 if (!empty($primaryProgram['program_major'])) {
                                     echo '<span style="color: #6c757d; font-size: 11px; font-weight: 500; margin-left: 4px;">(Major in ' . htmlspecialchars($primaryProgram['program_major']) . ')</span>';
                                 }
                                 echo '</div>';
                                 echo '<span class="additional-programs" title="' . htmlspecialchars(implode(', ', array_column(array_slice($course['programs'], 1), 'program_code'))) . '">';
                                 echo ' +' . $additionalCount;
                                 echo '</span>';
                                 echo '</div>';
                             } else {
                                 // Single program or fallback to old structure
                                 $program = isset($course['programs'][0]) ? $course['programs'][0] : $course;
                                 $programColor = $program['program_color'] ?? '#1976d2';
                                 $programCode = $program['program_code'] ?? $course['program_code'];
                                 $programMajor = $program['program_major'] ?? null;
                                 
                                 echo '<div>';
                                 echo '<span class="program-badge" style="background-color: ' . $programColor . '; color: white;">';
                                 echo htmlspecialchars($programCode);
                                 echo '</span>';
                                 if (!empty($programMajor)) {
                                     echo '<span style="color: #6c757d; font-size: 11px; font-weight: 500; margin-left: 4px;">(Major in ' . htmlspecialchars($programMajor) . ')</span>';
                                 }
                                 echo '</div>';
                             }
                             ?>
                         </td>
                        <td class="term-year" style="width: 120px !important; max-width: 120px !important; min-width: 120px !important;">
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
                            // Format year level: 1 -> 1st Year, 2 -> 2nd Year, etc.
                            $yearLevel = $course['year_level'];
                            if (is_numeric($yearLevel)) {
                                if ($yearLevel == 1) echo '1st Year';
                                elseif ($yearLevel == 2) echo '2nd Year';
                                elseif ($yearLevel == 3) echo '3rd Year';
                                elseif ($yearLevel == 4) echo '4th Year';
                                else echo $yearLevel . 'th Year';
                            } else {
                                echo htmlspecialchars($yearLevel);
                            }
                            ?>
                        </td>
                        <td class="faculty-name" style="width: 180px !important; max-width: 180px !important; min-width: 180px !important;">
                             <?php if (!empty($course['faculty'])): ?>
                                 <?php echo htmlspecialchars($course['faculty']); ?>
                             <?php else: ?>
                                 <span style="color: #999; font-style: italic;">Not Yet Assigned</span>
                             <?php endif; ?>
                         </td>
                         <td class="references-count" style="text-align: center; width: 90px !important; max-width: 90px !important; min-width: 90px !important;">
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
                         <td class="actions-cell" onclick="event.stopPropagation();" style="width: 90px !important; max-width: 90px !important; min-width: 90px !important;">
                             <div class="action-menu-container">
                                 <button class="action-menu-btn" onclick="toggleActionMenu(event, '<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo !empty($course['faculty']) ? 'true' : 'false'; ?>)">
                                     <span class="three-dots">⋯</span>
                                 </button>
                                 <div class="action-menu-dropdown" id="actionMenu-<?php echo htmlspecialchars($course['course_code']); ?>" style="display: none;">
                                     <div class="action-menu-item" onclick="editCourse('<?php echo htmlspecialchars($course['course_code']); ?>')">
                                         <span class="action-icon">✏️</span>
                                         <span>Edit</span>
                                     </div>
                                     <div class="action-menu-item <?php echo !empty($course['faculty']) ? 'disabled' : ''; ?>" 
                                          onclick="<?php echo !empty($course['faculty']) ? 'return false;' : 'assignFaculty(\'' . htmlspecialchars($course['course_code']) . '\');'; ?>">
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
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <h3>No Courses Yet</h3>
            <p>Start building your curriculum by creating your first course.</p>
            <button class="create-first-course-btn" onclick="simpleModalTest();">
                <img src="../src/assets/icons/add-icon.png" alt="Add" class="btn-icon">
                <span class="btn-text">Create First Course</span>
            </button>
        </div>
    <?php endif; ?>
    



    <!-- WORKING COURSE MODAL - EXACT COPY OF ORIGINAL -->
    <div id="workingCourseModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h2>New Course</h2>
                <span class="close-button" onclick="closeWorkingCourseModal()">&times;</span>
            </div>
            <form id="workingCourseForm" class="form-grid" method="post" autocomplete="off">
                <div class="form-row">
                    <div class="form-group" style="flex:1; min-width: 160px;">
                        <label for="working_course_code">Course Code</label>
                        <input type="text" name="course_code" id="working_course_code" required>
                    </div>
                    <div class="form-group" style="flex:2; min-width: 200px;">
                        <label for="working_course_name">Course Name</label>
                        <input type="text" name="course_name" id="working_course_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:2; min-width: 200px;">
                        <label>Program(s)</label>
                        <button type="button" id="workingOpenProgramSelectModalBtn" style="padding:12px 16px; border-radius:8px; border:1px solid #ccc; background:#f5f5f5; font-size:1rem; font-family:'TT Interphases',sans-serif; cursor:pointer; width:100%; text-align:left; position:relative; min-height:48px; display:flex; align-items:center;">
                            <span id="workingProgramButtonText">Select Program(s) - No Program Selected</span>
                            <span style="position:absolute; right:12px; font-size:12px; color:#666;">▼</span>
                        </button>
                        <input type="hidden" name="programs" id="workingSelectedProgramsInput">
                    </div>
                    <div class="form-group" style="flex:0 0 80px; min-width: 80px; max-width: 100px;">
                        <label for="working_units">Units</label>
                        <input type="number" name="units" id="working_units" min="1" max="10" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1; min-width: 120px;">
                        <label for="working_school_term">School Term</label>
                        <div class="custom-select-wrapper">
                            <select name="school_term" id="working_school_term" required>
                                <option value="">-- Select Term --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="flex:1.2; min-width: 150px;">
                        <label for="working_school_year">School Year</label>
                        <div class="custom-select-wrapper">
                            <select name="school_year" id="working_school_year" required>
                                <option value="">-- Select Year --</option>
                                <option value="2024-2025">A.Y. 2024-2025</option>
                                <option value="2025-2026">A.Y. 2025-2026</option>
                                <option value="2026-2027">A.Y. 2026-2027</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="flex:1; min-width: 120px;">
                        <label for="working_year_level">Year Level</label>
                        <div class="custom-select-wrapper">
                            <select name="year_level" id="working_year_level" required>
                                <option value="">-- Select Level --</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeWorkingCourseModal()">CANCEL</button>
                    <button type="submit" class="create-btn" id="workingCreateCourseBtn" disabled>CREATE</button>
                </div>
            </form>
        </div>
    </div>


    <!-- SIMPLE TEST MODAL - GUARANTEED TO WORK -->
    <div id="testModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 99999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px;">
            <h2 style="color: #333; margin-bottom: 20px;">Test Modal</h2>
            <p style="color: #666; margin-bottom: 20px;">This modal is guaranteed to work!</p>
            <button onclick="closeTestModal()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Close</button>
        </div>
    </div>

    <!-- Course Details Modal -->
    <div id="courseDetailsModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 1000px; width: 90vw; max-height: 90vh; overflow: hidden;">
            <div class="modal-header">
                <h2 id="courseDetailsTitle">Course Details</h2>
                <img class="close-button" src="../src/assets/icons/close-icon.png" alt="Close" onclick="closeCourseDetailsModal()" onmouseover="this.src='../src/assets/icons/close-hover-icon.png'; this.style.filter='none';" onmouseout="this.src='../src/assets/icons/close-icon.png'; this.style.filter='brightness(0) saturate(100%) invert(62%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0.62) contrast(0.62)';" style="cursor: pointer; width: 40px; height: 40px; filter: brightness(0) saturate(100%) invert(62%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0.62) contrast(0.62);">
            </div>
            <div class="modal-content" style="padding: 12px 8px;">
                <div id="courseDetailsContent">
                    <!-- Course details and book references will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back to Top" class="arrow">
        <span class="text">Back to Top</span>
    </button>
</div>

<script>
    // This variable will be accessed by scripts if needed
    const courses = <?php echo json_encode($courses); ?>;
    const hasPrograms = <?php echo json_encode(count($programs) > 0); ?>;
    const programsCount = <?php echo json_encode(count($programs)); ?>;
    const programs = <?php echo json_encode($programs); ?>;
    
    console.log('Programs count:', programsCount);
    console.log('Programs array:', programs);
    console.log('Has programs:', hasPrograms);

    // Global Modal Scroll Prevention - SIMPLE AND EFFECTIVE
    function preventBodyScroll() {
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
    }

    function restoreBodyScroll() {
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
    }


    function goToAddProgram() {
        closeNoProgramsModal();
        // Navigate to program management page
        window.location.href = 'content.php?page=program-management';
    }

    function openAddProgramModal() {
        closeNoProgramsModal();
        // Open the add program modal
        const programModal = document.getElementById('addProgramModal');
        if (programModal) {
            programModal.style.display = 'flex';
        }
    }

    // WORKING FUNCTION - CHECK PROGRAMS AND SHOW APPROPRIATE MODAL
    function simpleModalTest() {
        console.log('=== CHECKING PROGRAMS AND SHOWING MODAL ===');
        console.log('Button clicked!');
        console.log('hasPrograms:', hasPrograms);
        
        // Check if we have programs
        if (!hasPrograms) {
            console.log('No programs - calling global showNoProgramsModal function');
            
            // Use the global function that creates the original modal
            if (typeof showNoProgramsModal === 'function') {
                showNoProgramsModal();
                console.log('Global showNoProgramsModal called!');
            } else {
                console.log('Global showNoProgramsModal not found - showing test modal');
                // Fallback to test modal
                const testModal = document.getElementById('testModal');
                if (testModal) {
                    testModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    document.body.style.position = 'fixed';
                    document.body.style.width = '100%';
                    document.body.style.height = '100%';
                }
            }
        } else {
            console.log('Has programs - trying to show course modal');
            
            // Try to show the original course modal
            const courseModal = document.getElementById('addCourseModal');
            console.log('Course modal found:', !!courseModal);
            
            if (courseModal) {
                courseModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.height = '100%';
                console.log('Course modal shown!');
            } else {
                console.log('Course modal not found - showing test modal');
                // Fallback to test modal
                const testModal = document.getElementById('testModal');
                if (testModal) {
                    testModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    document.body.style.position = 'fixed';
                    document.body.style.width = '100%';
                    document.body.style.height = '100%';
                }
            }
        }
    }
    
    // Show working course modal
    function showWorkingCourseModal() {
        const modal = document.getElementById('workingCourseModal');
        if (modal) {
            modal.style.display = 'flex';
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            console.log('Working course modal shown!');
        } else {
            console.log('Working course modal not found, using test modal');
            // Fallback to test modal
            const testModal = document.getElementById('testModal');
            if (testModal) {
                testModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.height = '100%';
                console.log('Test modal shown as fallback!');
            }
        }
    }
    
    
    // Close working course modal
    function closeWorkingCourseModal() {
        const modal = document.getElementById('workingCourseModal');
        if (modal) {
            modal.style.display = 'none';
        }
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
        console.log('Working course modal closed!');
    }
    
    
    // Simple close function for test modal
    function closeTestModal() {
        const testModal = document.getElementById('testModal');
        if (testModal) {
            testModal.style.display = 'none';
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
        
        console.log('Test modal closed');
    }

    // BULLETPROOF SOLUTION - NO MORE COMPLEXITY
    function openCourseModalFromAllCourses() {
        console.log('=== BULLETPROOF MODAL OPENING ===');
        console.log('hasPrograms:', hasPrograms);
        
        if (!hasPrograms) {
            console.log('No programs - showing no programs modal');
            showNoProgramsModal();
            return;
        }
        
        console.log('Has programs - opening course modal');
        
        // Method 1: Try to find and open the modal directly
        let courseModal = document.getElementById('addCourseModal');
        console.log('Modal found (method 1):', !!courseModal);
        
        if (courseModal) {
            console.log('Opening modal with method 1');
            courseModal.style.display = 'flex';
            courseModal.style.zIndex = '10000';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.height = '100%';
            
            // Reset form
            const form = document.getElementById('addCourseForm');
            if (form) form.reset();
            
            console.log('Modal should be visible now');
            return;
        }
        
        // Method 2: Try calling the original function
        console.log('Method 1 failed, trying method 2');
        if (typeof openAddCourseModal === 'function') {
            console.log('Calling openAddCourseModal function');
            openAddCourseModal();
            return;
        }
        
        // Method 3: Create modal if it doesn't exist
        console.log('Method 2 failed, creating modal');
        const modalHTML = `
            <div id="addCourseModal" class="modal-overlay" style="display: flex; z-index: 10000;">
                <div class="modal-box">
                    <div class="modal-header">
                        <h2>New Course</h2>
                        <span class="close-button" onclick="closeAddCourseModal()">&times;</span>
                    </div>
                    <div style="padding: 20px; text-align: center;">
                        <p>Course creation form will be loaded here.</p>
                        <button onclick="closeAddCourseModal()">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
        
        console.log('Modal created and should be visible');
    }
    
    // Close function for the real modal
    function closeAddCourseModal() {
        const modal = document.getElementById('addCourseModal');
        if (modal) {
            modal.style.display = 'none';
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
        
        console.log('Real modal closed');
    }
    
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('courseSearch');
        const clearSearchBtn = document.getElementById('clearSearch');
        const tableRows = document.querySelectorAll('.courses-table tbody tr');
        const courseCount = document.getElementById('courseCountDisplay');
        
        console.log('DOM loaded');
        console.log('Search input found:', searchInput);
        console.log('Clear button found:', clearSearchBtn);
        console.log('Table rows found:', tableRows);
        console.log('Course count element found:', courseCount);
        
        function performSearch() {
            alert('Search function called!');
            console.log('=== PERFORM SEARCH FUNCTION CALLED ===');
            console.log('=== SEARCH BUTTON WAS CLICKED ===');
            const searchTerm = searchInput.value.toLowerCase().trim();
            console.log('Searching for:', searchTerm);
            console.log('Total table rows found:', tableRows.length);
            let visibleCount = 0;
            
            tableRows.forEach((row, index) => {
                console.log(`Row ${index}:`, row);
                console.log(`Row cells:`, row.cells);
                
                if (row.cells && row.cells.length >= 9) {
                     const courseCode = row.cells[0].textContent.toLowerCase();
                     const courseTitle = row.cells[1].textContent.toLowerCase();
                     const units = row.cells[2].textContent.toLowerCase();
                     const program = row.cells[3].textContent.toLowerCase();
                     const termYear = row.cells[4].textContent.toLowerCase();
                     const yearLevel = row.cells[5].textContent.toLowerCase();
                     const faculty = row.cells[6].textContent.toLowerCase();
                     const references = row.cells[7].textContent.toLowerCase();
                     
                     console.log(`Course Code: ${courseCode}, Title: ${courseTitle}, Units: ${units}, Program: ${program}, Term/Year: ${termYear}, Year Level: ${yearLevel}, Faculty: ${faculty}, References: ${references}`);
                     
                     const matches = courseCode.includes(searchTerm) || 
                                   courseTitle.includes(searchTerm) || 
                                   units.includes(searchTerm) ||
                                   program.includes(searchTerm) ||
                                   termYear.includes(searchTerm) ||
                                   yearLevel.includes(searchTerm) ||
                                   faculty.includes(searchTerm) ||
                                   references.includes(searchTerm);
                    
                    if (matches) {
                        row.style.display = '';
                        visibleCount++;
                        console.log(`Row ${index} matches - showing`);
                    } else {
                        row.style.display = 'none';
                        console.log(`Row ${index} doesn't match - hiding`);
                    }
                } else {
                    console.log(`Row ${index} has insufficient cells:`, row.cells?.length);
                }
            });
            
            // Update course count
            courseCount.innerHTML = `<strong>${visibleCount} courses</strong> found`;
            console.log('Updated count to:', visibleCount);
            
            // Show/hide clear button
            clearSearchBtn.style.display = searchTerm ? 'flex' : 'none';
        }
        
        // Search on input - removed to only search on button click
        
        // Clear search
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            // Reset to show all courses
            tableRows.forEach(row => {
                row.style.display = '';
            });
            // Reset course count
            courseCount.innerHTML = `<strong>${courses.length} courses</strong> found`;
            // Hide clear button
            clearSearchBtn.style.display = 'none';
            searchInput.focus();
        });
        
        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
        
                 // Show/hide clear button based on input text
         searchInput.addEventListener('input', function() {
             clearSearchBtn.style.display = searchInput.value.trim() ? 'flex' : 'none';
         });
     });
     
     // Back to top functionality
     function scrollToTop() {
         window.scrollTo({
             top: 0,
             behavior: 'smooth'
         });
     }
     
     // Show/hide back to top button based on scroll position
     window.addEventListener('scroll', function() {
         const backToTopBtn = document.getElementById('backToTopBtn');
         if (window.pageYOffset > 300) {
             backToTopBtn.classList.add('show');
         } else {
             backToTopBtn.classList.remove('show');
         }
     });
     
     // Action functions
     function editCourse(courseCode) {
         console.log('Edit course:', courseCode);
         // Close the current modal first
         closeCourseDetailsModal();
         
         // Find the course data
         const course = courses.find(c => c.course_code === courseCode);
         if (!course) {
             console.error('Course not found:', courseCode);
             alert('Course not found. Please refresh the page and try again.');
             return;
         }
         
         // Prepare course data for the edit modal
         const courseData = {
             course_code: course.course_code,
             course_title: course.course_title,
             units: course.units || '',
             term: course.term || '',
             academic_year: course.academic_year || '',
             year_level: course.year_level || '',
             programs: course.programs || []
         };
         
         // Open the edit course modal
         if (typeof openEditCourseModal === 'function') {
             openEditCourseModal(course.id || courseCode, courseData);
         } else {
             console.error('openEditCourseModal function not found');
             alert('Edit functionality is not available. Please refresh the page and try again.');
         }
     }
     
     function assignFaculty(courseCode) {
         alert('Assign faculty to course: ' + courseCode);
         // TODO: Implement assign functionality
     }

     // Course Details Modal Functions
     function showCourseDetails(courseCode, courseTitle) {
         console.log('Showing course details for:', courseCode, courseTitle);
         
         // Find the course data
         const course = courses.find(c => c.course_code === courseCode);
         if (!course) {
             console.error('Course not found:', courseCode);
             return;
         }
         
         // Update modal title
         document.getElementById('courseDetailsTitle').textContent = `${courseCode} - ${courseTitle}`;
         
         // Get book references for this course
         const bookReferences = getBookReferencesForCourse(courseCode);
         
         // Create course details HTML
         const courseDetailsHTML = createCourseDetailsHTML(course, bookReferences);
         
         // Update modal content
         const contentDiv = document.getElementById('courseDetailsContent');
         contentDiv.innerHTML = courseDetailsHTML;
         
         // Show modal
         const modal = document.getElementById('courseDetailsModal');
         modal.style.display = 'flex';
         
         // Prevent body scroll
         document.body.style.overflow = 'hidden';
         document.body.style.position = 'fixed';
         document.body.style.width = '100%';
         document.body.style.height = '100%';
     }

     function closeCourseDetailsModal() {
         const modal = document.getElementById('courseDetailsModal');
         modal.style.display = 'none';
         
         // Restore body scroll
         document.body.style.overflow = '';
         document.body.style.position = '';
         document.body.style.width = '';
         document.body.style.height = '';
     }

     function createCourseDetailsHTML(course, bookReferences) {
         // Get program information
         let programInfo = '';
         if (course.programs && course.programs.length > 0) {
             const primaryProgram = course.programs[0];
             programInfo = `
                 <div class="program-badge" style="background-color: ${primaryProgram.program_color}; color: white; display: inline-block; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                     ${primaryProgram.program_code}
                 </div>
             `;
             if (course.programs.length > 1) {
                 const additionalCount = course.programs.length - 1;
                 programInfo += ` <span style="color: #666; font-size: 11px; font-weight: 600; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #e9ecef;">+${additionalCount} more</span>`;
             }
         } else {
             programInfo = '<span style="color: #999;">No program assigned</span>';
         }

        return `
            <div class="course-details-container" style="display: flex; gap: 24px; height: 500px;">
                <!-- Left Side - Course Information (Fixed Width) -->
                <div class="course-info-section" style="width: 400px; flex-shrink: 0; height: 100%;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <h3 style="margin: 0; color: #333; font-size: 20px; font-family: 'TT Interphases', sans-serif; font-weight: 600;">Course Information</h3>
                        <button class="edit-course-btn" onclick="editCourse('${course.course_code}')" style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.background='#0056b3';" onmouseout="this.style.background='#007bff';">Edit</button>
                    </div>
                    <div class="course-details-list" style="display: flex; flex-direction: column; gap: 12px; height: calc(100% - 40px);">
                        <!-- Course Code & Title Card -->
                        <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px; display: flex; align-items: center; gap: 12px;">
                            <div class="course-code-badge" style="background: #1976d2; color: white; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 600;">${course.course_code}</div>
                            <div class="course-title" style="font-size: 15px; color: #333; font-weight: 500;">${course.course_title}</div>
                        </div>

                        <!-- Programs Card -->
                        <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <label style="font-weight: 600; color: #333; font-size: 13px;">Programs :</label>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    ${programInfo}
                                </div>
                            </div>
                        </div>

                        <!-- Faculty Assigned Card -->
                        <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                                <div>
                                    <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 6px;">Faculty Assigned :</div>
                                    <div style="color: #dc3545; font-size: 13px; font-weight: 500;">${course.faculty || 'Unassigned'}</div>
                                </div>
                                <button class="assign-btn" style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Assign</button>
                            </div>
                        </div>

                        <!-- Units and Year Level Cards Container -->
                        <div style="display: flex; gap: 12px;">
                            <!-- Units Card -->
                            <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px; flex: 1;">
                                <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 6px;">Units :</div>
                                <div style="font-size: 15px; color: #333; font-weight: 500;">${course.units}</div>
                            </div>

                            <!-- Year Level Card -->
                            <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px; flex: 1;">
                                <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 6px;">Year Level :</div>
                                <div style="font-size: 15px; color: #333; font-weight: 500;">${course.year_level}</div>
                            </div>
                        </div>

                        <!-- Term and Academic Year Card -->
                        <div class="detail-card" style="background: #f8f9fa; border-radius: 8px; padding: 16px;">
                            <div style="font-weight: 600; color: #333; font-size: 13px; margin-bottom: 6px;">Term and Academic Year :</div>
                            <div style="font-size: 15px; color: #333; font-weight: 500;">${course.term} - ${course.academic_year}</div>
                        </div>
                    </div>
                </div>

                 <!-- Right Side - Book References (Scrollable) -->
                 <div class="book-references-section" style="flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100%;">
                     <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #1976d2; flex-shrink: 0; height: 50px;">
                         <h3 style="margin: 0; color: #333; font-size: 20px; font-family: 'TT Interphases', sans-serif; font-weight: 600;">Book References</h3>
                         <span style="background: #1976d2; color: white; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: 600;">${bookReferences.length} Books</span>
                     </div>
                     <div class="book-references-list" style="height: calc(100% - 66px); overflow-y: auto; padding-right: 8px;">
                         ${bookReferences.length > 0 ? 
                             bookReferences.map(book => createBookReferenceHTML(book)).join('') : 
                             '<div class="no-references" style="text-align: center; padding: 60px 20px; color: #666; font-style: italic; background: #f8f9fa; border-radius: 12px; border: 2px dashed #dee2e6; font-size: 16px;"><div style="font-size: 48px; margin-bottom: 16px;">📚</div>No book references available for this course.</div>'
                         }
                     </div>
                 </div>
             </div>
         `;
     }

     function getBookReferencesForCourse(courseCode) {
         // TODO: Fetch book references from database via AJAX
         // For now, return empty array
         return [];
         
         // Old sample book references data (kept for reference)
         /*
         const bookReferencesData = {
             'IT101': [
                 {
                     id: 1,
                     title: 'Introduction to Information Technology',
                     author: 'Dr. Sarah Johnson',
                     isbn: '978-0123456789',
                     publisher: 'Tech Press',
                     year: '2023',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 001.1 J64 2023'
                 },
                 {
                     id: 2,
                     title: 'Fundamentals of Computing',
                     author: 'Prof. Michael Brown',
                     isbn: '978-0123456790',
                     publisher: 'Academic Press',
                     year: '2022',
                     edition: '2nd Edition',
                     availability: 'checked-out',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 001.2 B76 2022'
                 }
             ],
             'CS101': [
                 {
                     id: 3,
                     title: 'Computer Science: An Overview',
                     author: 'Dr. Emily Davis',
                     isbn: '978-0123456791',
                     publisher: 'CS Publications',
                     year: '2024',
                     edition: '4th Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 001.1 D38 2024'
                 },
                 {
                     id: 4,
                     title: 'Programming Fundamentals',
                     author: 'Prof. David Wilson',
                     isbn: '978-0123456792',
                     publisher: 'Code Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'reserved',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 001.3 W55 2023'
                 },
                 {
                     id: 5,
                     title: 'Introduction to Algorithms',
                     author: 'Dr. Thomas Cormen',
                     isbn: '978-0123456795',
                     publisher: 'MIT Press',
                     year: '2022',
                     edition: '4th Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.1 C67 2022'
                 },
                 {
                     id: 6,
                     title: 'Data Structures and Algorithms',
                     author: 'Prof. Mark Allen',
                     isbn: '978-0123456796',
                     publisher: 'Algorithm Press',
                     year: '2023',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.7 A44 2023'
                 }
             ],
             'CS102': [
                 {
                     id: 7,
                     title: 'C++ Programming Guide',
                     author: 'Dr. Mike Johnson',
                     isbn: '978-0123456797',
                     publisher: 'Programming Press',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.1 J64 2023'
                 },
                 {
                     id: 8,
                     title: 'Object-Oriented Programming in C++',
                     author: 'Prof. Lisa Chen',
                     isbn: '978-0123456798',
                     publisher: 'Code Masters',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.1 C44 2022'
                 },
                 {
                     id: 9,
                     title: 'C++ Data Structures and Algorithms',
                     author: 'Dr. David Wilson',
                     isbn: '978-0123456799',
                     publisher: 'Algorithm Books',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'checked-out',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.7 W55 2023'
                 },
                 {
                     id: 10,
                     title: 'Modern C++ Programming',
                     author: 'Prof. Emily Davis',
                     isbn: '978-0123456800',
                     publisher: 'Modern Tech',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.1 D38 2023'
                 }
             ],
             'MATH101': [
                 {
                     id: 5,
                     title: 'Calculus: Early Transcendentals',
                     author: 'Dr. James Stewart',
                     isbn: '978-0123456793',
                     publisher: 'Math Publishers',
                     year: '2023',
                     edition: '8th Edition',
                     availability: 'available',
                     location: 'Main Library - Math Section',
                     callNumber: 'MATH 515 S84 2023'
                 }
             ],
             'ENG101': [
                 {
                     id: 11,
                     title: 'Technical Writing for Engineers',
                     author: 'Dr. Lisa Anderson',
                     isbn: '978-0123456794',
                     publisher: 'Writing Press',
                     year: '2022',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - English Section',
                     callNumber: 'ENG 808 A53 2022'
                 },
                 {
                     id: 12,
                     title: 'Communication Skills for IT Professionals',
                     author: 'Prof. Sarah Miller',
                     isbn: '978-0123456801',
                     publisher: 'Communication Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - English Section',
                     callNumber: 'ENG 808 M55 2023'
                 }
             ],
             'IT102': [
                 {
                     id: 13,
                     title: 'Database Management Systems',
                     author: 'Dr. Alex Rodriguez',
                     isbn: '978-0123456802',
                     publisher: 'Database Solutions',
                     year: '2023',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 R63 2023'
                 },
                 {
                     id: 14,
                     title: 'SQL Programming Guide',
                     author: 'Prof. Maria Garcia',
                     isbn: '978-0123456803',
                     publisher: 'Query Press',
                     year: '2022',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 G37 2022'
                 },
                 {
                     id: 15,
                     title: 'Database Design Principles',
                     author: 'Dr. James Anderson',
                     isbn: '978-0123456804',
                     publisher: 'Design Books',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'checked-out',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 A53 2023'
                 },
                 {
                     id: 16,
                     title: 'NoSQL Databases',
                     author: 'Prof. Rachel Kim',
                     isbn: '978-0123456805',
                     publisher: 'Modern Data',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 K56 2023'
                 }
             ],
             'IS101': [
                 {
                     id: 17,
                     title: 'Information Systems Analysis',
                     author: 'Dr. Amanda White',
                     isbn: '978-0123456806',
                     publisher: 'System Analysis',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 001.1 W45 2023'
                 },
                 {
                     id: 18,
                     title: 'Business Process Management',
                     author: 'Prof. Thomas Clark',
                     isbn: '978-0123456807',
                     publisher: 'Business Tech',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 001.1 C53 2022'
                 },
                 {
                     id: 19,
                     title: 'System Design and Architecture',
                     author: 'Dr. Nicole Adams',
                     isbn: '978-0123456808',
                     publisher: 'Architecture Books',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'reserved',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 001.1 A33 2023'
                 }
             ],
             'IS102': [
                 {
                     id: 20,
                     title: 'Software Engineering Principles',
                     author: 'Dr. Daniel Martinez',
                     isbn: '978-0123456809',
                     publisher: 'Engineering Press',
                     year: '2022',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 005.1 M37 2022'
                 },
                 {
                     id: 21,
                     title: 'Project Management in IT',
                     author: 'Prof. Sophie Turner',
                     isbn: '978-0123456810',
                     publisher: 'Project Books',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 005.1 T87 2023'
                 },
                 {
                     id: 22,
                     title: 'Agile Development Methods',
                     author: 'Dr. Kevin Park',
                     isbn: '978-0123456811',
                     publisher: 'Agile Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IS Section',
                     callNumber: 'IS 005.1 P37 2023'
                 }
             ],
             'IT201': [
                 {
                     id: 23,
                     title: 'Web Development Fundamentals',
                     author: 'Dr. Jennifer Webber',
                     isbn: '978-0123456812',
                     publisher: 'Web Press',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 006.7 W43 2023'
                 },
                 {
                     id: 24,
                     title: 'HTML5 and CSS3 Complete Guide',
                     author: 'Prof. Mark Styles',
                     isbn: '978-0123456813',
                     publisher: 'Frontend Books',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 006.7 S85 2023'
                 },
                 {
                     id: 25,
                     title: 'JavaScript Programming',
                     author: 'Dr. Sarah Script',
                     isbn: '978-0123456814',
                     publisher: 'Script Press',
                     year: '2022',
                     edition: '3rd Edition',
                     availability: 'checked-out',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 006.7 S47 2022'
                 }
             ],
             'STAT101': [
                 {
                     id: 26,
                     title: 'Statistics for Computing',
                     author: 'Dr. Robert Stats',
                     isbn: '978-0123456815',
                     publisher: 'Math Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - Math Section',
                     callNumber: 'MATH 519 S73 2023'
                 },
                 {
                     id: 27,
                     title: 'Probability and Statistics',
                     author: 'Prof. Lisa Probability',
                     isbn: '978-0123456816',
                     publisher: 'Academic Press',
                     year: '2022',
                     edition: '4th Edition',
                     availability: 'available',
                     location: 'Main Library - Math Section',
                     callNumber: 'MATH 519 P76 2022'
                 }
             ],
             'IT301': [
                 {
                     id: 28,
                     title: 'Advanced Database Management',
                     author: 'Dr. Alex Database',
                     isbn: '978-0123456817',
                     publisher: 'Database Solutions',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 D38 2023'
                 },
                 {
                     id: 29,
                     title: 'Database Administration',
                     author: 'Prof. Maria Admin',
                     isbn: '978-0123456818',
                     publisher: 'Admin Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 A35 2023'
                 },
                 {
                     id: 30,
                     title: 'Data Warehousing and Mining',
                     author: 'Dr. Kevin Data',
                     isbn: '978-0123456819',
                     publisher: 'Data Press',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'reserved',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 005.7 D38 2022'
                 }
             ],
             'ETH101': [
                 {
                     id: 31,
                     title: 'Ethics in Information Technology',
                     author: 'Dr. Ethics Professor',
                     isbn: '978-0123456820',
                     publisher: 'Ethics Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - Philosophy Section',
                     callNumber: 'PHIL 174 E84 2023'
                 },
                 {
                     id: 32,
                     title: 'Digital Ethics and Privacy',
                     author: 'Prof. Privacy Expert',
                     isbn: '978-0123456821',
                     publisher: 'Privacy Books',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - Philosophy Section',
                     callNumber: 'PHIL 174 P75 2022'
                 }
             ],
             'CS201': [
                 {
                     id: 33,
                     title: 'Data Structures and Algorithms',
                     author: 'Dr. Algorithm Expert',
                     isbn: '978-0123456822',
                     publisher: 'Algorithm Press',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.7 A44 2023'
                 },
                 {
                     id: 34,
                     title: 'Algorithm Design and Analysis',
                     author: 'Prof. Design Master',
                     isbn: '978-0123456823',
                     publisher: 'Design Press',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'checked-out',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.7 D47 2022'
                 },
                 {
                     id: 35,
                     title: 'Advanced Programming Techniques',
                     author: 'Dr. Advanced Coder',
                     isbn: '978-0123456824',
                     publisher: 'Advanced Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - CS Section',
                     callNumber: 'CS 005.1 A38 2023'
                 }
             ],
             'LIS101': [
                 {
                     id: 36,
                     title: 'Introduction to Library Science',
                     author: 'Dr. Library Expert',
                     isbn: '978-0123456825',
                     publisher: 'Library Press',
                     year: '2023',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - LIS Section',
                     callNumber: 'LIS 020 L47 2023'
                 },
                 {
                     id: 37,
                     title: 'Information Organization',
                     author: 'Prof. Organization Master',
                     isbn: '978-0123456826',
                     publisher: 'Organization Books',
                     year: '2022',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - LIS Section',
                     callNumber: 'LIS 025 O73 2022'
                 },
                 {
                     id: 38,
                     title: 'Digital Libraries',
                     author: 'Dr. Digital Librarian',
                     isbn: '978-0123456827',
                     publisher: 'Digital Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - LIS Section',
                     callNumber: 'LIS 025.04 D54 2023'
                 }
             ],
             'BLIS103': [
                 {
                     id: 39,
                     title: 'Foundations of Library and Information Science',
                     author: 'Dr. Sarah Library',
                     isbn: '978-0123456828',
                     publisher: 'Library Foundation Press',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 020 L47 2023'
                 },
                 {
                     id: 40,
                     title: 'Introduction to Information Science',
                     author: 'Prof. Information Expert',
                     isbn: '978-0123456829',
                     publisher: 'Information Press',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 020 I47 2022'
                 },
                 {
                     id: 41,
                     title: 'Library Management Principles',
                     author: 'Dr. Management Specialist',
                     isbn: '978-0123456830',
                     publisher: 'Management Books',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'checked-out',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 025.1 M35 2023'
                 },
                 {
                     id: 42,
                     title: 'Information Retrieval Systems',
                     author: 'Prof. Retrieval Master',
                     isbn: '978-0123456831',
                     publisher: 'Retrieval Press',
                     year: '2022',
                     edition: '3rd Edition',
                     availability: 'available',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 025.04 R47 2022'
                 }
             ],
             'BLIS302': [
                 {
                     id: 43,
                     title: 'Advanced Library Science',
                     author: 'Dr. Advanced Librarian',
                     isbn: '978-0123456832',
                     publisher: 'Advanced Library Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 020 A38 2023'
                 },
                 {
                     id: 44,
                     title: 'Digital Information Management',
                     author: 'Prof. Digital Manager',
                     isbn: '978-0123456833',
                     publisher: 'Digital Press',
                     year: '2023',
                     edition: '2nd Edition',
                     availability: 'available',
                     location: 'Main Library - BLIS Section',
                     callNumber: 'BLIS 025.04 D54 2023'
                 }
             ],
             'IT203': [
                 {
                     id: 45,
                     title: 'Advanced Web Technologies',
                     author: 'Dr. Web Expert',
                     isbn: '978-0123456834',
                     publisher: 'Web Tech Press',
                     year: '2023',
                     edition: '1st Edition',
                     availability: 'available',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 006.7 W43 2023'
                 },
                 {
                     id: 46,
                     title: 'Full-Stack Development',
                     author: 'Prof. Full Stack Developer',
                     isbn: '978-0123456835',
                     publisher: 'Stack Press',
                     year: '2022',
                     edition: '1st Edition',
                     availability: 'reserved',
                     location: 'Main Library - IT Section',
                     callNumber: 'IT 006.7 F85 2022'
                 }
             ]
         };
         
         return bookReferencesData[courseCode] || [];
         */
     }


     function createBookReferenceHTML(book) {
         return `
             <div class="book-reference-item">
                 <div class="book-title">${book.title}</div>
                 ${book.created_by_name ? `<div class="book-created-by" style="font-size: 12px; color: #666; margin-bottom: 4px;">Created by: ${book.created_by_name}</div>` : ''}
                 ${book.requested_by_name ? `<div class="book-requested-by" style="font-size: 12px; color: #666; margin-bottom: 4px;">Requested by: ${book.requested_by_name}</div>` : ''}
                 <div class="book-details">
                     ${book.isbn ? `<span class="book-detail-item">ISBN: ${book.isbn}</span>` : ''}
                     ${book.publisher ? `<span class="book-detail-item">Publisher: ${book.publisher}</span>` : ''}
                     ${book.copyright_year ? `<span class="book-detail-item">Copyright: ${book.copyright_year}</span>` : ''}
                     ${book.edition ? `<span class="book-detail-item">Edition: ${book.edition}</span>` : ''}
                 </div>
                 ${book.location ? `<div style="font-size: 12px; color: #666; margin-top: 8px;">Location: ${book.location}</div>` : ''}
                 ${book.call_number ? `<div style="font-size: 11px; color: #999; margin-top: 4px;">Call Number: ${book.call_number}</div>` : ''}
                 <div class="book-actions">
                     <button class="book-action-btn view-book-btn" onclick="viewBookDetails(${book.id})">View Details</button>
                     <button class="book-action-btn request-book-btn" onclick="editBookReference(${book.id})">Edit</button>
                 </div>
             </div>
         `;
     }

     function viewBookDetails(bookId) {
         alert('View book details for ID: ' + bookId);
         // TODO: Implement view book details functionality
     }

     function editBookReference(bookId) {
         alert('Edit book reference ID: ' + bookId);
         // TODO: Implement edit book reference functionality
     }

     // Close modal when clicking outside
     document.addEventListener('click', function(event) {
         const modal = document.getElementById('courseDetailsModal');
         if (event.target === modal) {
             closeCourseDetailsModal();
         }
     });
     
     // Function to navigate to course details
     function navigateToCourse(courseCode, courseTitle, courseId) {
         console.log('Navigating to course:', courseCode, courseTitle, 'ID:', courseId);
         window.location.href = 'content.php?page=course-details&course_code=' + encodeURIComponent(courseCode) + '&course_title=' + encodeURIComponent(courseTitle) + '&course_id=' + encodeURIComponent(courseId);
     }
    
    // Function to filter courses by section
    function filterCoursesBySection() {
        const dropdown = document.getElementById('sectionDropdown');
        const selectedSection = dropdown.value;
        const courseRows = document.querySelectorAll('#coursesTable tbody tr');
        const courseCount = document.getElementById('totalCourseCount');
        
        let visibleCount = 0;
        
        courseRows.forEach(row => {
            const section = row.getAttribute('data-section');
            
            if (selectedSection === '' || section === selectedSection) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update course count
        courseCount.textContent = visibleCount + ' course' + (visibleCount !== 1 ? 's' : '');
        
        console.log('Filtered courses:', visibleCount, 'for section:', selectedSection);
    }
    
    // Force column widths with JavaScript
    function forceColumnWidths() {
        const table = document.getElementById('coursesTable');
        if (table) {
            // Force table layout
            table.style.tableLayout = 'fixed';
            table.style.width = '100%';
            
            // Force column widths
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            // Set header widths and alignment
            if (headers[4]) headers[4].style.width = '120px'; // Term & Academic Year
            if (headers[6]) headers[6].style.width = '250px'; // Faculty
            if (headers[7]) headers[7].style.width = '80px';  // References
            if (headers[8]) headers[8].style.width = '100px'; // Actions
            
            // Force Actions header to be centered
            if (headers[8]) {
                headers[8].style.textAlign = 'center';
                headers[8].style.setProperty('text-align', 'center', 'important');
            }
            
            // Set data cell widths
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells[4]) cells[4].style.width = '120px'; // Term & Academic Year
                if (cells[6]) cells[6].style.width = '250px'; // Faculty
                if (cells[7]) cells[7].style.width = '80px';  // References
                if (cells[8]) cells[8].style.width = '100px'; // Actions
            });
            
            console.log('Column widths forced with JavaScript');
        }
    }
    
    // Specific function to force Actions header centering
    function forceActionsCentering() {
        const actionsHeader = document.querySelector('th:nth-child(9)');
        if (actionsHeader) {
            actionsHeader.style.cssText = 'text-align: center !important; width: 100px !important; max-width: 100px !important; min-width: 100px !important;';
            console.log('FORCED ACTIONS HEADER CENTERING');
        }
        
        // Also try by text content
        const allHeaders = document.querySelectorAll('th');
        allHeaders.forEach(header => {
            if (header.textContent.trim() === 'Actions') {
                header.style.cssText = 'text-align: center !important; width: 100px !important; max-width: 100px !important; min-width: 100px !important;';
                console.log('FORCED ACTIONS HEADER BY TEXT CONTENT');
            }
        });
    }
    
    // Run immediately and with delays
    forceColumnWidths();
    forceActionsCentering();
    setTimeout(forceColumnWidths, 100);
    setTimeout(forceActionsCentering, 100);
    setTimeout(forceColumnWidths, 500);
    setTimeout(forceActionsCentering, 500);
    setTimeout(forceColumnWidths, 1000);
    setTimeout(forceActionsCentering, 1000);
    
    // Function to toggle action menu
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
     
     // Function to navigate to program courses
     function navigateToProgramCourses(programCode) {
         console.log('Navigating to program courses:', programCode);
         window.location.href = 'content.php?page=course-details&program=' + encodeURIComponent(programCode);
     }
 </script>
