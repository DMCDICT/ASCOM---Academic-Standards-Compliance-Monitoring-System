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
    error_log("Error fetching programs in program-courses.php: " . $e->getMessage());
    $programs = [];
    }
}

// Initialize program data
$programName = 'Unknown Program';
$programColor = '#1976d2';
$programMajor = '';

        // Fetch courses for the specific program
if ($deanDepartmentCode && $programCode) {
    try {
        $currentYear = date('Y');
        $query = "
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
                COUNT(CASE WHEN br.id IS NOT NULL AND br.copyright_year > 0 AND (? - br.copyright_year) < 5 THEN 1 END) as book_references_count
            FROM 
                courses c
            JOIN 
                programs p ON c.program_id = p.id
            JOIN
                departments d ON p.department_id = d.id
            LEFT JOIN 
                users u ON c.faculty_id = u.id AND u.is_active = TRUE
            LEFT JOIN 
                user_roles ur ON u.id = ur.user_id AND ur.role_name = 'teacher' AND ur.is_active = 1
            LEFT JOIN
                book_references br ON c.id = br.course_id
            WHERE 
                d.department_code = ? AND p.program_code = ?
            GROUP BY c.id, c.course_code, c.course_title, c.units, d.color_code, faculty_name, c.status, c.term, c.academic_year, c.year_level
            ORDER BY 
                c.course_code ASC;
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$currentYear, $deanDepartmentCode, $programCode]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $courses = $result;
        }

        // Get program name, major, and department color from database
        $programQuery = "SELECT p.program_name, p.major, d.color_code as department_color 
                        FROM programs p 
                        JOIN departments d ON p.department_id = d.id 
                        WHERE p.program_code = ? AND d.department_code = ?";
        $stmt = $pdo->prepare($programQuery);
        $stmt->execute([$programCode, $deanDepartmentCode]);
        $programResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($programResult) > 0) {
            $row = $programResult[0];
            $programName = $row['program_name'];
            $programColor = $row['department_color'];
            $programMajor = $row['major'] ?? '';
        }
    } catch (Exception $e) {
        error_log("Error fetching courses in program-courses.php: " . $e->getMessage());
    }
}

?>

<style>
.program-courses-container {
    margin-top: 0 !important;
    padding: 0 !important;
}

.program-header {
    background: linear-gradient(135deg, <?php echo $programColor; ?>, <?php echo $programColor; ?>dd);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.program-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    font-family: 'TT Interphases', sans-serif;
}

.program-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
    font-family: 'TT Interphases', sans-serif;
}

.back-button {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
    transition: all 0.3s ease;
    display: inline-block;
    margin-top: 20px;
}

.back-button:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    color: white;
    text-decoration: none;
}

.courses-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.courses-table {
    width: 100%;
    border-collapse: collapse;
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
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 20px 16px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.courses-table td {
    padding: 16px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.courses-table tr:hover {
    background: #f8f9fa;
}

.course-code {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.course-title {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.4;
}

.units-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'TT Interphases', sans-serif;
}

.faculty-name {
    color: #495057;
    font-size: 14px;
    font-weight: 500;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'TT Interphases', sans-serif;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.no-courses {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
    font-family: 'TT Interphases', sans-serif;
}

.no-courses h3 {
    font-size: 24px;
    margin-bottom: 12px;
    color: #495057;
}

.no-courses p {
    font-size: 16px;
    margin: 0;
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
    background-color: #f0f0f0;
}

.three-dots {
    font-size: 18px;
    font-weight: bold;
    color: #666;
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
    
    .edit-program-modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .edit-program-modal-body {
        padding: 20px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .courses-table-container {
        overflow-x: auto;
    }
    
    .courses-table {
        min-width: 600px;
    }
}
</style>

<div class="program-courses-container">
    <!-- Program Header -->
<div class="program-header">
        <h1 class="program-title"><?php echo htmlspecialchars($programName); ?></h1>
        <p class="program-subtitle">
            <?php echo htmlspecialchars($programCode); ?>
            <?php if (!empty($programMajor)): ?>
                • <?php echo htmlspecialchars($programMajor); ?>
                            <?php endif; ?>
        </p>
        <div class="course-count">
            <?php echo count($courses); ?> Course<?php echo count($courses) !== 1 ? 's' : ''; ?>
</div>
        <a href="content.php?page=dashboard" class="back-button">
            ← Back to Dashboard
        </a>
</div>

    <!-- Courses Table Header with Actions -->
    <div class="courses-table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="flex: 1;">
            <!-- Search can go here if needed in future -->
        </div>
        <button class="add-course-btn" onclick="openAddCourseModalFromProgram('<?php echo htmlspecialchars($programCode); ?>')" style="background: <?php echo $programColor; ?>; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'TT Interphases', sans-serif; transition: all 0.2s;" onmouseover="this.style.background='<?php echo $programColor; ?>dd'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='<?php echo $programColor; ?>'; this.style.transform='translateY(0)';">
            + Add New Course
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
                                $compliantCount = intval($course['book_references_count']);
                                $targetCount = 5; // Default target, QA will set this later
                                $isCompliant = $compliantCount >= $targetCount;
                                $displayColor = $isCompliant ? '#2e7d32' : '#FF4C4C'; // Green if compliant, Red if not
                                ?>
                                <span class="book-references-count" style="background: <?php echo $isCompliant ? '#e8f5e8' : '#ffeaea'; ?>; color: <?php echo $displayColor; ?>;">
                                    <?php echo $compliantCount; ?>/<?php echo $targetCount; ?>
                                </span>
                        </td>
                            <td class="actions-cell" onclick="event.stopPropagation();">
                                <div class="action-menu-container">
                                    <button class="action-menu-btn" onclick="toggleActionMenu(event, '<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>, '<?php echo $courseStatus; ?>', <?php echo !empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned' ? 'true' : 'false'; ?>)" title="Actions" aria-label="Actions menu">
                                        <span class="three-dots" style="display: block; line-height: 0.5;">⋯</span>
                                    </button>
                                    <div class="action-menu-dropdown" id="actionMenu-<?php echo htmlspecialchars($course['course_code']); ?>" style="display: none;">
                                        <?php if ($courseStatus === 'pending'): ?>
                                            <div class="action-menu-item" onclick="viewCourseDetails('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">👁️</span>
                                                <span>View</span>
                                            </div>
                                            <div class="action-menu-item" onclick="editCourseFromProgram('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">✏️</span>
                                                <span>Edit</span>
                                            </div>
                                            <div class="action-menu-item" onclick="approveCourse(<?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">✅</span>
                                                <span>Approve</span>
                                            </div>
                                            <div class="action-menu-item" onclick="rejectCourse(<?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">❌</span>
                                                <span>Reject</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="action-menu-item" onclick="editCourseFromProgram('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">✏️</span>
                                                <span>Edit</span>
                                            </div>
                                            <div class="action-menu-item <?php echo (!empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned') ? 'disabled' : ''; ?>" 
                                                 onclick="<?php echo (!empty($course['faculty_name']) && $course['faculty_name'] !== 'Unassigned') ? 'return false;' : 'assignFacultyFromProgram(\'' . htmlspecialchars($course['course_code']) . '\');'; ?>">
                                                <span class="action-icon">👤</span>
                                                <span>Assign</span>
                                            </div>
                                            <div class="action-menu-item" onclick="viewCourseDetails('<?php echo htmlspecialchars($course['course_code']); ?>', <?php echo intval($course['course_id']); ?>)">
                                                <span class="action-icon">👁️</span>
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
    console.log('Viewing course details for:', courseCode, 'ID:', courseId);
    window.location.href = 'content.php?page=course-details&course_code=' + encodeURIComponent(courseCode) + '&course_id=' + encodeURIComponent(courseId);
}

// Edit Program Modal Functions
     function editProgram(programCode) {
         console.log('editProgram function called with:', programCode);
         
         // Get current program data
         const currentProgramName = programName;
         const currentProgramMajorValue = currentProgramMajor;
         
    console.log('Current data:', { currentProgramName, currentProgramMajorValue });
         
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
        console.log('Approving course:', courseId);
        
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
                console.log('Course approved successfully');
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
        console.log('Rejecting course:', courseId);
        
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
                console.log('Course rejected successfully');
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

// Edit course from program courses page
async function editCourseFromProgram(courseCode, courseId) {
    console.log('Edit course from program:', courseCode, courseId);
    
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
function assignFacultyFromProgram(courseCode) {
    console.log('Assign faculty to course:', courseCode);
    // Close the action menu
    const menu = document.getElementById('actionMenu-' + courseCode);
    if (menu) {
        menu.style.display = 'none';
    }
    // TODO: Implement assign faculty functionality
    alert('Assign faculty to course: ' + courseCode + '\n\nThis functionality will be implemented soon.');
}

// Open add course modal from program courses page
function openAddCourseModalFromProgram(programCode) {
    console.log('Opening add course modal from program:', programCode);
    // Store the program code for use in the modal
    window.currentProgramCode = programCode;
    // Open the add course modal
    if (typeof checkProgramsAndOpenCourseModal === 'function') {
        checkProgramsAndOpenCourseModal();
        // After modal opens, pre-select the current program
        setTimeout(() => {
            if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                preSelectProgram(window.currentProgramCode);
            }
        }, 500);
    } else if (typeof openAddCourseModal === 'function') {
        openAddCourseModal();
        setTimeout(() => {
            if (window.currentProgramCode && typeof preSelectProgram === 'function') {
                preSelectProgram(window.currentProgramCode);
            }
        }, 500);
    } else {
        console.error('Add course modal function not found');
        alert('Add course functionality is not available. Please refresh the page.');
    }
}

</script>