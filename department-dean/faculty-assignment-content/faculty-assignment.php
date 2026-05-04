<?php
// faculty-assignment.php
// Page for assigning faculties to courses

// Note: session_config.php, db_connection.php, and auth are included by content.php before this file

// Get department info
$deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
$deanDepartmentName = $_SESSION['selected_role']['department_name'] ?? '';

// Get course info from URL if provided
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
$courseCode = isset($_GET['course_code']) ? $_GET['course_code'] : '';
$courseTitle = isset($_GET['course_title']) ? $_GET['course_title'] : '';

// Fetch course details if course_id provided
$courseDetails = null;
if ($courseId) {
    try {
        $courseQuery = "
            SELECT c.*, p.program_code, p.program_name, d.department_code
            FROM courses c
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN departments d ON p.department_id = d.id
            WHERE c.id = ? AND d.department_code = ?
        ";
        $courseStmt = $pdo->prepare($courseQuery);
        $courseStmt->execute([$courseId, $deanDepartmentCode]);
        $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($courseDetails) {
            $courseCode = $courseDetails['course_code'];
            $courseTitle = $courseDetails['course_title'];
        }
    } catch (Exception $e) {
    }
}

// Fetch all courses for the department
$courses = [];
try {
    $coursesQuery = "
        SELECT c.id, c.course_code, c.course_title, c.term, c.academic_year,
               CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
               p.program_code, p.program_name
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN departments d ON p.department_id = d.id
        LEFT JOIN users u ON c.faculty_id = u.id
        WHERE d.department_code = ?
        ORDER BY c.course_code ASC
    ";
    $coursesStmt = $pdo->prepare($coursesQuery);
    $coursesStmt->execute([$deanDepartmentCode]);
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

// Fetch all teachers/faculty for the department
$teachers = [];
try {
    $teachersQuery = "
        SELECT u.id, u.first_name, u.last_name, u.email, u.position
        FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN roles r ON ur.role_id = r.id
        WHERE r.name = 'teacher' AND u.status = 'active'
        ORDER BY u.last_name ASC
    ";
    $teachersStmt = $pdo->prepare($teachersQuery);
    $teachersStmt->execute();
    $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Assignment - <?php echo htmlspecialchars($deanDepartmentName); ?></title>
    <link rel="stylesheet" href="../super_admin-mis/styles/global.css">
    <link rel="stylesheet" href="../super_admin-mis/styles/dashboard.css">
    <link rel="stylesheet" href="../super_admin-mis/styles/user-account-management.css">
    <script defer src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        /* ====== DESIGN.md COMPLIANT STYLES ====== */
        body {
            background: #EFEFEF;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .content-wrapper {
            padding: 24px;
            width: 90%;
            max-width: none;
            margin: 0 auto;
        }
        
        /* Back Button */
        .back-navigation {
            margin-bottom: 20px;
        }
        
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
            transition: all 0.2s ease;
            font-family: 'TT Interphases', sans-serif;
            gap: 6px;
        }
        
        .back-button:hover {
            color: #0a3a28;
            transform: translateX(-4px);
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .header-content h1 {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 6px 0;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .header-content p {
            font-size: 14px;
            color: rgba(17, 24, 39, 0.6);
            margin: 0;
            font-weight: 500;
        }
        
        /* Course Selector */
        .course-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .course-selector select {
            padding: 10px 16px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'TT Interphases', sans-serif;
            background: white;
            min-width: 300px;
            cursor: pointer;
        }
        
        .course-selector select:focus {
            outline: none;
            border-color: #0C4B34;
        }
        
        /* Selected Course Card */
        .selected-course-card {
            background: #ffffff;
            border-radius: 16px 18px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            padding: 20px 24px;
            margin-bottom: 24px;
            animation: fadeSlideUp 0.45s ease-out both;
            position: relative;
            overflow: hidden;
        }
        
        .selected-course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
            border-radius: 16px 18px 0 0;
        }
        
        .selected-course-card h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0C4B34;
            margin: 0 0 8px 0;
        }
        
        .selected-course-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .info-tag {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: rgba(17, 24, 39, 0.6);
            background: rgba(12, 75, 52, 0.04);
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid rgba(12, 75, 52, 0.08);
        }
        
        .info-tag strong {
            color: #0C4B34;
        }
        
        /* Faculty List */
        .faculty-section {
            background: #ffffff;
            border-radius: 16px 18px;
            border: 1px solid rgba(12, 75, 52, 0.14);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            padding: 20px 24px;
            animation: fadeSlideUp 0.45s ease-out both;
            animation-delay: 0.1s;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h3 {
            font-size: 16px;
            font-weight: 800;
            color: #0C4B34;
            margin: 0;
        }
        
        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        
        .faculty-card {
            border: 1px solid rgba(12, 75, 52, 0.12);
            border-radius: 12px;
            padding: 16px;
            transition: all 0.28s cubic-bezier(.4,0,.2,1);
            cursor: pointer;
        }
        
        .faculty-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(12, 75, 52, 0.1);
            border-color: rgba(12, 75, 52, 0.2);
        }
        
        .faculty-card.selected {
            border-color: #0C4B34;
            background: rgba(12, 75, 52, 0.04);
        }
        
        .faculty-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .faculty-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(12, 75, 52, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0C4B34;
        }
        
        .faculty-info h4 {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        
        .faculty-info p {
            font-size: 12px;
            color: rgba(17, 24, 39, 0.5);
            margin: 4px 0 0 0;
        }
        
        .faculty-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: rgba(17, 24, 39, 0.6);
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4CAF50;
        }
        
        /* Assign Button */
        .assign-btn {
            background: #0F7A53;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'TT Interphases', sans-serif;
        }
        
        .assign-btn:hover {
            background: #0a5f42;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(15, 122, 83, 0.25);
        }
        
        .assign-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(17, 24, 39, 0.4);
        }
        
        .empty-state svg {
            display: block;
            margin: 0 auto 16px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 8px 0;
        }
        
        .empty-state p {
            font-size: 13px;
            color: rgba(17, 24, 39, 0.6);
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
            }
            
            .course-selector {
                width: 100%;
            }
            
            .course-selector select {
                width: 100%;
            }
            
            .selected-course-info {
                flex-direction: column;
            }
            
            .faculty-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Dark Mode */
        html[data-theme="dark"] .back-button {
            color: #81C784;
        }
        
        html[data-theme="dark"] .back-button:hover {
            color: #a5d6a7;
        }
        
        html[data-theme="dark"] .header-content h1 {
            color: #f0f0f0;
        }
        
        html[data-theme="dark"] .header-content p {
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .course-selector select {
            background: #2d2d2d;
            border-color: #404040;
            color: #e0e0e0;
        }
        
        html[data-theme="dark"] .selected-course-card {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
        }
        
        html[data-theme="dark"] .selected-course-card h2 {
            color: #e0e0e0;
        }
        
        html[data-theme="dark"] .info-tag {
            background: rgba(255, 255, 255, 0.06);
            border-color: #404040;
            color: #b0b0b0;
        }
        
        html[data-theme="dark"] .info-tag strong {
            color: #81C784;
        }
        
        html[data-theme="dark"] .faculty-section {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
        }
        
        html[data-theme="dark"] .section-header h3 {
            color: #e0e0e0;
        }
        
        html[data-theme="dark"] .faculty-card {
            background: #252525;
            border-color: #333;
        }
        
        html[data-theme="dark"] .faculty-card:hover {
            border-color: #444;
        }
        
        html[data-theme="dark"] .faculty-card.selected {
            background: rgba(129, 199, 132, 0.1);
            border-color: #81C784;
        }
        
        html[data-theme="dark"] .faculty-avatar {
            background: rgba(255, 255, 255, 0.06);
            color: #81C784;
        }
        
        html[data-theme="dark"] .faculty-info h4 {
            color: #f0f0f0;
        }
        
        html[data-theme="dark"] .faculty-info p {
            color: #b0b0b0;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Back Button -->
        <div class="back-navigation">
            <button class="back-button" onclick="window.location.href='content.php?page=all-courses'">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                Back to Courses
            </button>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1>Faculty Assignment</h1>
                <p>Assign teachers to courses in your department</p>
            </div>
            
            <!-- Course Selector -->
            <div class="course-selector">
                <select id="courseSelect" onchange="selectCourse(this.value)">
                    <option value="">Select a course...</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                data-code="<?php echo htmlspecialchars($course['course_code']); ?>"
                                data-title="<?php echo htmlspecialchars($course['course_title']); ?>"
                                data-term="<?php echo htmlspecialchars($course['term'] ?? ''); ?>"
                                data-program="<?php echo htmlspecialchars($course['program_name'] ?? ''); ?>"
                                data-faculty="<?php echo htmlspecialchars($course['faculty_name'] ?? ''); ?>"
                                <?php echo ($courseId == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Selected Course Card -->
        <div id="selectedCourseCard" class="selected-course-card" style="<?php echo $courseDetails ? 'display: block;' : 'display: none;'; ?>">
            <h2><?php echo $courseDetails ? htmlspecialchars($courseDetails['course_code'] . ' - ' . $courseDetails['course_title']) : ''; ?></h2>
            <div class="selected-course-info">
                <div class="info-tag">
                    <i data-lucide="book-open" style="width: 14px; height: 14px;"></i>
                    <span>Program: <strong><?php echo htmlspecialchars($courseDetails['program_name'] ?? 'N/A'); ?></strong></span>
                </div>
                <div class="info-tag">
                    <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                    <span>Term: <strong><?php echo htmlspecialchars($courseDetails['term'] ?? 'N/A'); ?></strong></span>
                </div>
                <div class="info-tag">
                    <i data-lucide="user" style="width: 14px; height: 14px;"></i>
                    <span>Current: <strong><?php echo htmlspecialchars($courseDetails['faculty_name'] ?? 'Unassigned'); ?></strong></span>
                </div>
            </div>
        </div>
        
        <!-- Faculty List -->
        <div class="faculty-section">
            <div class="section-header">
                <h3>Available Faculty Members</h3>
                <button class="assign-btn" id="assignBtn" onclick="assignFaculty()" disabled>
                    <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                    Assign Selected
                </button>
            </div>
            
            <?php if (empty($teachers)): ?>
                <div class="empty-state">
                    <i data-lucide="users" style="width: 48px; height: 48px; color: #0C4B34;"></i>
                    <h3>No Faculty Available</h3>
                    <p>There are no active teachers in your department.</p>
                </div>
            <?php else: ?>
                <div class="faculty-grid" id="facultyGrid">
                    <?php foreach ($teachers as $teacher): ?>
                        <div class="faculty-card" onclick="selectFaculty(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['last_name'] . ', ' . $teacher['first_name']); ?>')" data-teacher-id="<?php echo $teacher['id']; ?>">
                            <div class="faculty-card-header">
                                <div class="faculty-avatar">
                                    <i data-lucide="user" style="width: 22px; height: 22px;"></i>
                                </div>
                                <div class="faculty-info">
                                    <h4><?php echo htmlspecialchars($teacher['last_name'] . ', ' . $teacher['first_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($teacher['position'] ?? 'Teacher'); ?></p>
                                </div>
                            </div>
                            <div class="faculty-status">
                                <span class="status-dot"></span>
                                <span>Available for assignment</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        let selectedCourseId = <?php echo $courseId ? $courseId : 'null'; ?>;
        let selectedTeacherId = null;
        let selectedTeacherName = '';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            
            // If course is pre-selected, show the card
            if (selectedCourseId) {
                const select = document.getElementById('courseSelect');
                if (select.value) {
                    document.getElementById('selectedCourseCard').style.display = 'block';
                }
            }
        });
        
        function selectCourse(courseId) {
            if (!courseId) {
                selectedCourseId = null;
                document.getElementById('selectedCourseCard').style.display = 'none';
                updateAssignButton();
                return;
            }
            
            selectedCourseId = parseInt(courseId);
            const select = document.getElementById('courseSelect');
            const option = select.options[select.selectedIndex];
            
            // Update the selected course card
            const card = document.getElementById('selectedCourseCard');
            card.innerHTML = `
                <h2>${option.dataset.code} - ${option.dataset.title}</h2>
                <div class="selected-course-info">
                    <div class="info-tag">
                        <i data-lucide="book-open" style="width: 14px; height: 14px;"></i>
                        <span>Program: <strong>${option.dataset.program || 'N/A'}</strong></span>
                    </div>
                    <div class="info-tag">
                        <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                        <span>Term: <strong>${option.dataset.term || 'N/A'}</strong></span>
                    </div>
                    <div class="info-tag">
                        <i data-lucide="user" style="width: 14px; height: 14px;"></i>
                        <span>Current: <strong>${option.dataset.faculty || 'Unassigned'}</strong></span>
                    </div>
                </div>
            `;
            card.style.display = 'block';
            lucide.createIcons();
            
            updateAssignButton();
        }
        
        function selectFaculty(teacherId, teacherName) {
            // Deselect all cards
            document.querySelectorAll('.faculty-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select the clicked card
            const selectedCard = document.querySelector(`[data-teacher-id="${teacherId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            
            selectedTeacherId = teacherId;
            selectedTeacherName = teacherName;
            
            updateAssignButton();
        }
        
        function updateAssignButton() {
            const btn = document.getElementById('assignBtn');
            btn.disabled = !selectedCourseId || !selectedTeacherId;
        }
        
        function assignFaculty() {
            if (!selectedCourseId || !selectedTeacherId) {
                alert('Please select both a course and a faculty member.');
                return;
            }
            
            if (!confirm(`Are you sure you want to assign ${selectedTeacherName} to this course?`)) {
                return;
            }
            
            // Make API call to assign faculty (send as JSON)
            fetch('api/assign_course_teacher.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    course_id: selectedCourseId,
                    teacher_id: selectedTeacherId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Faculty assigned successfully!');
                    // Refresh the page
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to assign faculty.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to assign faculty. Please try again.');
            });
        }
    </script>
</body>
</html>