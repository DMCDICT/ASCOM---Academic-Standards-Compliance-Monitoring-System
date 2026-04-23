<style>
/* Faculty Details Page - DESIGN.md Aligned Styles */

/* Back Navigation */
.back-navigation {
	display: flex;
	align-items: center;
	margin-bottom: 20px;
	padding: 10px 0;
}

/* Back Button - Ghost Style */
.back-button {
	display: flex;
	align-items: center;
	background: transparent;
	border: none;
	color: #0C4B34;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	padding: 10px 16px;
	border-radius: 10px;
	transition: all 0.2s ease;
	font-family: 'TT Interphases', sans-serif;
	gap: 6px;
}

.back-button:hover {
	background: rgba(12, 75, 52, 0.06);
	color: #0a3a28;
	transform: translateX(-4px);
}

.back-button img {
	width: 18px;
	height: 18px;
}

/* Main Container */
.faculty-details-container {
	margin-top: 20px;
	background-color: #EFEFEF;
	min-height: 100vh;
	padding: 10px;
}

/* Faculty Header Card - Premium Style */
.faculty-header-card {
	background: #ffffff;
	border-radius: 16px-18px;
	border: 1px solid rgba(12, 75, 52, 0.14);
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
	padding: 24px 26px;
	position: relative;
	overflow: hidden;
	animation: fadeSlideUp 0.45s ease-out both;
}

/* Accent Stripe */
.faculty-header-card::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 3px;
	background: linear-gradient(90deg, #0C4B34 0%, #0F7A53 100%);
	border-radius: 18px 18px 0 0;
}

.faculty-header-card:hover {
	transform: translateY(-3px);
	box-shadow: 0 12px 36px rgba(12, 75, 52, 0.12);
	border-color: rgba(12, 75, 52, 0.25);
}

/* Faculty Title */
.faculty-name-title {
	margin: 0 0 8px 0;
	color: #111827;
	font-size: 1.7rem;
	font-weight: 800;
	font-family: 'TT Interphases', sans-serif;
}

/* Department Badge */
.department-badge {
	display: inline-block;
	padding: 6px 12px;
	border-radius: 8px;
	color: #ffffff;
	font-weight: 800;
	font-size: 12px;
	font-family: 'TT Interphases', sans-serif;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Position Text */
.faculty-position {
	margin: 0 0 16px 0;
	color: rgba(17, 24, 39, 0.6);
	font-size: 14px;
	font-weight: 600;
	font-family: 'TT Interphases', sans-serif;
}

/* Contact Info Grid */
.contact-info-grid {
	display: flex;
	gap: 30px;
	flex-wrap: wrap;
	margin-top: 12px;
}

.contact-item {
	display: flex;
	align-items: center;
	gap: 8px;
}

.contact-label {
	color: rgba(17, 24, 39, 0.5);
	font-weight: 700;
	font-size: 12px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.contact-value {
	color: #111827;
	font-weight: 600;
	font-size: 14px;
}

/* Status Row */
.status-row {
	display: flex;
	gap: 16px;
	align-items: center;
	margin-top: 14px;
	flex-wrap: wrap;
}

/* Status Pill */
.status-pill {
	background: rgba(76, 175, 80, 0.1);
	color: #2E7D32;
	padding: 5px 12px;
	border-radius: 8px;
	font-size: 12px;
	font-weight: 700;
	display: flex;
	align-items: center;
	gap: 6px;
}

.status-pill.active {
	background: rgba(76, 175, 80, 0.1);
}

.status-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #2E7D32;
	animation: statusPulse 2s ease-in-out infinite;
}

/* Employee Info */
.employee-info {
	color: #111827;
	font-size: 14px;
	font-weight: 600;
}

/* Teaching Loads Card */
.teaching-loads-card {
	background: #ffffff;
	border-radius: 16px-18px;
	border: 1px solid rgba(12, 75, 52, 0.14);
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
	padding: 24px 26px;
	animation: fadeSlideUp 0.45s ease-out both;
	animation-delay: 0.08s;
	animation-fill-mode: both;
}

.teaching-loads-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 24px;
	padding-bottom: 16px;
	border-bottom: 1px solid rgba(12, 75, 52, 0.08);
}

.teaching-loads-title {
	margin: 0;
	color: #111827;
	font-size: 18px;
	font-weight: 800;
	font-family: 'TT Interphases', sans-serif;
}

/* Add New Load Button - Primary */
.add-load-btn {
	background: #0C4B34;
	color: white;
	border: none;
	padding: 10px 18px;
	border-radius: 10px;
	cursor: pointer;
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.2px;
	transition: all 0.22s cubic-bezier(.4, 0, .2, 1);
	display: flex;
	align-items: center;
	gap: 6px;
	font-family: 'TT Interphases', sans-serif;
}

.add-load-btn:hover {
	background: #0a3a28;
	transform: translateY(-1px);
	box-shadow: 0 6px 18px rgba(12, 75, 52, 0.25);
}

.add-load-btn:active {
	transform: translateY(0) scale(0.98);
}

/* Semester Section */
.semester-section {
	border: 1px solid rgba(12, 75, 52, 0.12);
	border-radius: 12px;
	overflow: hidden;
	margin-bottom: 16px;
}

.semester-header {
	background: rgba(12, 75, 52, 0.02);
	padding: 16px 20px;
	cursor: pointer;
	display: flex;
	justify-content: space-between;
	align-items: center;
	transition: background 0.15s ease;
}

.semester-header:hover {
	background: rgba(12, 75, 52, 0.06);
}

.semester-title {
	margin: 0;
	color: #111827;
	font-size: 15px;
	font-weight: 800;
	font-family: 'TT Interphases', sans-serif;
}

.semester-meta {
	margin: 4px 0 0 0;
	color: rgba(17, 24, 39, 0.5);
	font-size: 13px;
	font-weight: 600;
}

.semester-toggle {
	font-size: 18px;
	color: rgba(17, 24, 39, 0.4);
	transition: transform 0.2s ease;
}

.semester-toggle.open {
	transform: rotate(180deg);
}

/* Course Item */
.semester-content {
	background: #ffffff;
	border-top: 1px solid rgba(12, 75, 52, 0.08);
}

.course-item {
	padding: 16px 20px;
	border-bottom: 1px solid rgba(12, 75, 52, 0.05);
	cursor: pointer;
	transition: background 0.15s ease;
}

.course-item:last-child {
	border-bottom: none;
}

.course-item:hover {
	background: rgba(12, 75, 52, 0.03);
}

.course-info {
	flex: 1;
}

.course-code-title {
	margin: 0 0 4px 0;
	color: #111827;
	font-size: 14px;
	font-weight: 700;
	font-family: 'TT Interphases', sans-serif;
}

.course-meta {
	margin: 0;
	color: rgba(17, 24, 39, 0.5);
	font-size: 12px;
	font-weight: 600;
}

.course-badges {
	display: flex;
	gap: 10px;
	align-items: center;
}

/* Units Badge */
.units-badge {
	background: rgba(12, 75, 52, 0.08);
	color: #0C4B34;
	padding: 4px 10px;
	border-radius: 8px;
	font-size: 12px;
	font-weight: 700;
}

/* Status Badge */
.status-badge {
	background: rgba(76, 175, 80, 0.1);
	color: #2E7D32;
	padding: 4px 10px;
	border-radius: 8px;
	font-size: 11px;
	font-weight: 700;
	display: flex;
	align-items: center;
	gap: 4px;
}

.course-arrow {
	color: rgba(17, 24, 39, 0.3);
	font-size: 16px;
	transition: transform 0.15s ease;
}

.course-item:hover .course-arrow {
	transform: translateX(4px);
	color: #0C4B34;
}

/* Empty State */
.empty-state {
	padding: 48px 20px;
	text-align: center;
}

.empty-state svg {
	display: block;
	margin: 0 auto 16px;
	opacity: 0.3;
}

.empty-state h3 {
	margin: 0 0 8px 0;
	color: #333;
	font-size: 16px;
	font-weight: 800;
	font-family: 'TT Interphases', sans-serif;
}

.empty-state p {
	margin: 0;
	color: rgba(17, 24, 39, 0.4);
	font-size: 14px;
	font-weight: 600;
}

/* Modal Overlay */
.course-references-overlay {
	position: fixed;
	inset: 0;
	background-color: rgba(0, 0, 0, 0.8);
	display: none;
	align-items: center;
	justify-content: center;
	z-index: 2000;
	animation: fadeIn 0.15s ease;
}

.course-references-overlay.show {
	display: flex;
}

/* Modal Box */
.course-references-modal {
	background-color: #ffffff;
	border-radius: 16px;
	width: 90%;
	max-width: 600px;
	max-height: 80vh;
	overflow-y: auto;
	box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
	animation: modalPop 0.18s ease-out;
}

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 14px;
	padding: 18px 20px;
	background: linear-gradient(0deg, rgba(12, 75, 52, 0.08), rgba(12, 75, 52, 0.08)), #ffffff;
	border-bottom: 1px solid rgba(12, 75, 52, 0.14);
}

.modal-title {
	font-size: 18px;
	font-weight: 800;
	color: #111827;
	margin: 0;
}

.modal-close {
	width: 40px;
	height: 40px;
	border-radius: 12px;
	border: 1px solid rgba(12, 75, 52, 0.16);
	background: rgba(12, 75, 52, 0.06);
	color: #0C4B34;
	font-size: 26px;
	line-height: 1;
	cursor: pointer;
	display: grid;
	place-items: center;
	transition: background 0.15s ease, transform 0.08s ease;
	flex: none;
}

.modal-close:hover {
	background: rgba(12, 75, 52, 0.1);
}

.modal-close:active {
	transform: scale(0.98);
	background: rgba(12, 75, 52, 0.14);
}

.modal-content {
	padding: 20px;
}

/* Course Title in Modal */
.course-modal-title {
	margin: 0 0 6px 0;
	color: #111827;
	font-size: 16px;
	font-weight: 800;
	font-family: 'TT Interphases', sans-serif;
}

.course-modal-subtitle {
	margin: 0 0 20px 0;
	color: rgba(17, 24, 39, 0.5);
	font-size: 13px;
	font-weight: 600;
}

/* Reference Items */
.reference-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.reference-item {
	background: #f9f9f9;
	border: 1px solid rgba(12, 75, 52, 0.08);
	border-radius: 12px;
	padding: 16px;
	transition: all 0.2s ease;
}

.reference-item:hover {
	border-color: rgba(12, 75, 52, 0.2);
	box-shadow: 0 4px 12px rgba(12, 75, 52, 0.08);
}

.reference-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 8px;
}

.reference-title {
	margin: 0 0 4px 0;
	color: #111827;
	font-size: 14px;
	font-weight: 700;
}

.reference-author {
	margin: 0;
	color: rgba(17, 24, 39, 0.5);
	font-size: 13px;
	font-weight: 600;
}

/* Reference Type Badge */
.reference-type {
	background: rgba(12, 75, 52, 0.08);
	color: #0C4B34;
	padding: 3px 8px;
	border-radius: 6px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.4px;
}

/* Animations */
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

@keyframes statusPulse {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.55; }
}

@keyframes fadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
	.faculty-header-card,
	.teaching-loads-card {
		padding: 18px;
	}
	
	.contact-info-grid {
		flex-direction: column;
		gap: 12px;
	}
	
	.status-row {
		flex-direction: column;
		align-items: flex-start;
		gap: 10px;
	}
	
	.course-badges {
		flex-wrap: wrap;
	}
}

/* Dark Mode */
html[data-theme="dark"] .back-button {
	color: #81C784;
}

html[data-theme="dark"] .back-button:hover {
	background: rgba(129, 199, 132, 0.08);
}

html[data-theme="dark"] .faculty-header-card,
html[data-theme="dark"] .teaching-loads-card {
	background-color: #1e1e1e !important;
	border-color: #333 !important;
	box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
}

html[data-theme="dark"] .faculty-name-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .department-badge {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

html[data-theme="dark"] .faculty-position,
html[data-theme="dark"] .contact-label,
html[data-theme="dark"] .semester-meta,
html[data-theme="dark"] .course-meta,
html[data-theme="dark"] .course-modal-subtitle {
	color: rgba(224, 224, 224, 0.6);
}

html[data-theme="dark"] .contact-value,
html[data-theme="dark"] .employee-info {
	color: #e0e0e0;
}

html[data-theme="dark"] .teaching-loads-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .add-load-btn {
	background: #0F7A53 !important;
}

html[data-theme="dark"] .semester-section {
	border-color: #333;
}

html[data-theme="dark"] .semester-header {
	background: rgba(255, 255, 255, 0.03);
}

html[data-theme="dark"] .semester-header:hover {
	background: rgba(255, 255, 255, 0.06);
}

html[data-theme="dark"] .semester-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .semester-content {
	background: #1a1a1a;
	border-color: #333;
}

html[data-theme="dark"] .course-item {
	border-color: #333;
}

html[data-theme="dark"] .course-item:hover {
	background: rgba(255, 255, 255, 0.04);
}

html[data-theme="dark"] .course-code-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .units-badge {
	background: rgba(129, 199, 132, 0.15);
	color: #81C784;
}

html[data-theme="dark"] .modal-header {
	background: linear-gradient(0deg, rgba(129, 199, 132, 0.1), rgba(129, 199, 132, 0.1)), #1e1e1e;
	border-color: #333;
}

html[data-theme="dark"] .modal-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .modal-close {
	background: rgba(129, 199, 132, 0.1);
	color: #81C784;
	border-color: rgba(129, 199, 132, 0.2);
}

html[data-theme="dark"] .course-references-modal {
	background: #1e1e1e;
}

html[data-theme="dark"] .course-modal-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .reference-item {
	background: #252525;
	border-color: #333;
}

html[data-theme="dark"] .reference-title {
	color: #e0e0e0;
}

html[data-theme="dark"] .reference-type {
	background: rgba(129, 199, 132, 0.15);
	color: #81C784;
}
</style>

<?php
// Get faculty ID from URL parameter
$facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

if ($facultyId === 0) {
    echo '<div class="empty-state" style="padding: 40px;"><p>Invalid faculty ID</p></div>';
    exit;
}

// Fetch real faculty data from database
// $pdo is already available from content.php

try {
    // Get faculty data from database
    $facultyQuery = "
        SELECT 
            u.id,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.title,
            u.institutional_email,
            u.mobile_no,
            u.created_at,
            u.role_id,
            d.department_name,
            d.department_code,
            d.color_code,
            CASE WHEN d.dean_user_id = u.id THEN 1 ELSE 0 END as is_department_dean
        FROM 
            users u
        LEFT JOIN 
            departments d ON u.department_id = d.id
        WHERE 
            u.id = ? 
            AND u.role_id IN (2, 4)
            AND u.is_active = 1
    ";
    
    $facultyStmt = $pdo->prepare($facultyQuery);
    $facultyStmt->execute([$facultyId]);
    $facultyData = $facultyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$facultyData) {
        echo '<div class="empty-state" style="padding: 40px;"><p>Faculty member not found</p></div>';
        exit;
    }
    
} catch (Exception $e) {
    echo '<div class="empty-state" style="padding: 40px;"><p>Error loading faculty data: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
    exit;
}

// Sample semester loads data (replace with real data from database)
$semesterLoads = [
    '1st Semester' => [
        [
            'id' => 1,
            'course_code' => 'IT101',
            'course_title' => 'Introduction to Information Technology',
            'units' => 3,
            'program' => 'BSIT',
            'year_level' => '1st Year',
            'academic_year' => 'A.Y. 2025-2026',
            'status' => 'Active',
            'references' => [
                ['title' => 'Introduction to Computing', 'author' => 'Smith, J.', 'year' => '2023', 'type' => 'Textbook'],
                ['title' => 'IT Fundamentals', 'author' => 'Johnson, M.', 'year' => '2022', 'type' => 'Reference Book'],
                ['title' => 'Computer Science Basics', 'author' => 'Williams, R.', 'year' => '2024', 'type' => 'Online Resource']
            ]
        ],
        [
            'id' => 2,
            'course_code' => 'CS201',
            'course_title' => 'Data Structures and Algorithms',
            'units' => 4,
            'program' => 'BSCS',
            'year_level' => '2nd Year',
            'academic_year' => 'A.Y. 2025-2026',
            'status' => 'Active',
            'references' => [
                ['title' => 'Data Structures and Algorithms', 'author' => 'Cormen, T.', 'year' => '2022', 'type' => 'Textbook'],
                ['title' => 'Algorithm Design Manual', 'author' => 'Skiena, S.', 'year' => '2023', 'type' => 'Reference Book'],
                ['title' => 'Introduction to Algorithms', 'author' => 'Leiserson, C.', 'year' => '2021', 'type' => 'Textbook']
            ]
        ]
    ],
    '2nd Semester' => [
        [
            'id' => 3,
            'course_code' => 'IT301',
            'course_title' => 'Database Management Systems',
            'units' => 3,
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'academic_year' => 'A.Y. 2025-2026',
            'status' => 'Active',
            'references' => [
                ['title' => 'Database System Concepts', 'author' => 'Silberschatz, A.', 'year' => '2023', 'type' => 'Textbook'],
                ['title' => 'SQL for Beginners', 'author' => 'Brown, K.', 'year' => '2022', 'type' => 'Reference Book'],
                ['title' => 'Database Design', 'author' => 'Davis, L.', 'year' => '2024', 'type' => 'Online Resource']
            ]
        ]
    ]
];

function formatName($faculty) {
    $name = '';
    if (!empty($faculty['title'])) {
        $name .= $faculty['title'] . ' ';
    }
    if (!empty($faculty['first_name'])) {
        $name .= $faculty['first_name'] . ' ';
    }
    if (!empty($faculty['last_name'])) {
        $name .= $faculty['last_name'];
    }
    return trim($name);
}
?>

<div class="faculty-details-container">
    <!-- Back Button -->
    <div class="back-navigation">
        <button class="back-button" onclick="goBack()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Faculty List
        </button>
    </div>

    <!-- Faculty Details Card -->
    <div class="faculty-header-card">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <!-- Title and Department Code -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <h1 class="faculty-name-title">
                    <?php echo formatName($facultyData); ?>
                </h1>
                <span class="department-badge" style="background: <?php echo htmlspecialchars($facultyData['color_code'] ?? '#0C4B34'); ?>;">
                    <?php echo htmlspecialchars($facultyData['department_code'] ?? 'N/A'); ?>
                </span>
            </div>
            
            <!-- Position and Department -->
            <p class="faculty-position">
                <?php 
                $roleId = $facultyData['role_id'] ?? 4;
                $isDepartmentDean = $facultyData['is_department_dean'] ?? 0;
                
                // Determine position: either role_id = 3 OR assigned as dean in departments table
                $position = ($roleId == 3 || $isDepartmentDean == 1) ? 'Department Dean' : 'Faculty Member';
                echo htmlspecialchars($position . ' • ' . $facultyData['department_name']); 
                ?>
            </p>
            
            <!-- Contact Information -->
            <div class="contact-info-grid">
                <div class="contact-item">
                    <span class="contact-label">Email:</span>
                    <span class="contact-value"><?php echo htmlspecialchars($facultyData['institutional_email']); ?></span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Mobile:</span>
                    <span class="contact-value"><?php echo htmlspecialchars($facultyData['mobile_no']); ?></span>
                </div>
            </div>
            
            <!-- Status and Employee Number -->
            <div class="status-row">
                <span class="status-pill active">
                    <span class="status-dot"></span>
                    Active
                </span>
                <span class="employee-info">
                    <strong>Employee No:</strong> <?php echo htmlspecialchars($facultyData['employee_no']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Teaching Loads Section -->
    <div class="teaching-loads-card" style="margin-top: 24px;">
        <div class="teaching-loads-header">
            <h2 class="teaching-loads-title">Teaching Loads by Term</h2>
            <button class="add-load-btn" onclick="addNewLoad()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add New Load
            </button>
        </div>

        <?php if (empty($semesterLoads)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                <h3>No Teaching Loads</h3>
                <p>This faculty member doesn't have any course loads assigned yet.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($semesterLoads as $semester => $courses): ?>
                    <div class="semester-section">
                        <!-- Semester Header -->
                        <div class="semester-header" onclick="toggleSemester('<?php echo str_replace(' ', '_', $semester); ?>')">
                            <div>
                                <h3 class="semester-title"><?php echo htmlspecialchars($semester); ?> (<?php echo count($courses); ?> courses)</h3>
                                <p class="semester-meta">Total Units: <?php echo array_sum(array_column($courses, 'units')); ?></p>
                            </div>
                            <span class="semester-toggle" id="toggle_<?php echo str_replace(' ', '_', $semester); ?>">▼</span>
                        </div>
                        
                        <!-- Courses List -->
                        <div id="semester_<?php echo str_replace(' ', '_', $semester); ?>" class="semester-content" style="display: none;">
                            <?php foreach ($courses as $course): ?>
                                <div class="course-item" onclick="showCourseReferences(<?php echo $course['id']; ?>)">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="course-info">
                                            <h4 class="course-code-title"><?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></h4>
                                            <p class="course-meta"><?php echo htmlspecialchars($course['program']); ?> • <?php echo htmlspecialchars($course['year_level']); ?> • <?php echo htmlspecialchars($course['academic_year']); ?></p>
                                        </div>
                                        <div class="course-badges">
                                            <span class="units-badge"><?php echo $course['units']; ?> units</span>
                                            <span class="status-badge">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#2E7D32;"></span>
                                                <?php echo htmlspecialchars($course['status']); ?>
                                            </span>
                                            <span class="course-arrow">→</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Course References Modal -->
<div id="courseReferencesModal" class="course-references-overlay" onclick="closeModalOnOverlay(event)">
    <div class="course-references-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Course References</h3>
            <button class="modal-close" onclick="closeCourseReferencesModal()">&times;</button>
        </div>
        <div class="modal-content" id="courseReferencesContent">
            <!-- Course references will be loaded here -->
        </div>
    </div>
</div>

<script>
// Navigate back to faculty list
function goBack() {
    window.location.href = 'content.php?page=academic-management';
}

// Toggle semester visibility
function toggleSemester(semesterId) {
    const content = document.getElementById('semester_' + semesterId);
    const toggle = document.getElementById('toggle_' + semesterId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        toggle.classList.add('open');
    } else {
        content.style.display = 'none';
        toggle.classList.remove('open');
    }
}

// Course references data (sample)
const courseReferences = {
    1: {
        course_code: 'IT101',
        course_title: 'Introduction to Information Technology',
        references: [
            { title: 'Introduction to Computing', author: 'Smith, J.', year: '2023', type: 'Textbook' },
            { title: 'IT Fundamentals', author: 'Johnson, M.', year: '2022', type: 'Reference Book' },
            { title: 'Computer Science Basics', author: 'Williams, R.', year: '2024', type: 'Online Resource' }
        ]
    },
    2: {
        course_code: 'CS201',
        course_title: 'Data Structures and Algorithms',
        references: [
            { title: 'Data Structures and Algorithms', author: 'Cormen, T.', year: '2022', type: 'Textbook' },
            { title: 'Algorithm Design Manual', author: 'Skiena, S.', year: '2023', type: 'Reference Book' },
            { title: 'Introduction to Algorithms', author: 'Leiserson, C.', year: '2021', type: 'Textbook' }
        ]
    },
    3: {
        course_code: 'IT301',
        course_title: 'Database Management Systems',
        references: [
            { title: 'Database System Concepts', author: 'Silberschatz, A.', year: '2023', type: 'Textbook' },
            { title: 'SQL for Beginners', author: 'Brown, K.', year: '2022', type: 'Reference Book' },
            { title: 'Database Design', author: 'Davis, L.', year: '2024', type: 'Online Resource' }
        ]
    }
};

// Show course references modal
function showCourseReferences(courseId) {
    const course = courseReferences[courseId];
    if (!course) {
        alert('Course references not found!');
        return;
    }
    
    // Show modal
    const modal = document.getElementById('courseReferencesModal');
    modal.classList.add('show');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Populate modal content
    const content = `
        <div style="margin-bottom: 20px;">
            <h3 class="course-modal-title">${course.course_code} - ${course.course_title}</h3>
            <p class="course-modal-subtitle">Course References and Materials</p>
        </div>
        
        <div class="reference-list">
            ${course.references.map(ref => `
                <div class="reference-item">
                    <div class="reference-header">
                        <div>
                            <h5 class="reference-title">${ref.title}</h5>
                            <p class="reference-author">by ${ref.author} (${ref.year})</p>
                        </div>
                        <span class="reference-type">${ref.type}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    document.getElementById('courseReferencesContent').innerHTML = content;
}

// Close modal when clicking outside
function closeModalOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeCourseReferencesModal();
    }
}

// Close course references modal
function closeCourseReferencesModal() {
    const modal = document.getElementById('courseReferencesModal');
    modal.classList.remove('show');
    
    // Restore body scroll
    document.body.style.overflow = '';
}

// Add new load (placeholder)
function addNewLoad() {
    alert('Add new load functionality will be implemented soon!');
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCourseReferencesModal();
    }
});
</script>