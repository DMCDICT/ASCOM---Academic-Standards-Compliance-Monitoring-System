<?php
// faculty-details.php for Department Dean
// This file displays faculty details and their course loads
?>
<style>
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
</style>
<?php

// Get faculty ID from URL parameter
$facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

if ($facultyId === 0) {
    echo '<div style="text-align: center; padding: 40px; color: #666;">Invalid faculty ID</div>';
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
        echo '<div style="text-align: center; padding: 40px; color: #666;">Faculty member not found</div>';
        exit;
    }
    
} catch (Exception $e) {
    echo '<div style="text-align: center; padding: 40px; color: #666;">Error loading faculty data: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

// Sample semester loads data
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
                ['title' => 'Introduction to Computing', 'author' => 'Smith, J.', 'year' => '2023'],
                ['title' => 'IT Fundamentals', 'author' => 'Johnson, M.', 'year' => '2022'],
                ['title' => 'Computer Science Basics', 'author' => 'Williams, R.', 'year' => '2024']
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
                ['title' => 'Data Structures and Algorithms', 'author' => 'Cormen, T.', 'year' => '2022'],
                ['title' => 'Algorithm Design Manual', 'author' => 'Skiena, S.', 'year' => '2023'],
                ['title' => 'Introduction to Algorithms', 'author' => 'Leiserson, C.', 'year' => '2021']
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
                ['title' => 'Database System Concepts', 'author' => 'Silberschatz, A.', 'year' => '2023'],
                ['title' => 'SQL for Beginners', 'author' => 'Brown, K.', 'year' => '2022'],
                ['title' => 'Database Design', 'author' => 'Davis, L.', 'year' => '2024']
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

<div class="faculty-details-container" style="margin-top: 20px; background-color: #EFEFEF; min-height: 100vh; padding: 10px;">
    <!-- Back Button -->
    <div class="back-navigation">
        <button class="back-button" onclick="goBack()">
            <img src="../src/assets/icons/go-back-icon.png" alt="Back" onerror="this.style.display='none'; this.nextSibling.style.display='inline';">
            <span style="display: none;">←</span>
            Back to Faculty List
        </button>
    </div>

    <!-- Faculty Details Section -->
    <div style="background: white; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <!-- Faculty Header -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Title/Username and Department Code -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; color: #333; font-size: 1.8rem; font-family: 'TT Interphases', Arial, sans-serif; font-weight: 600;">
                    <?php echo formatName($facultyData); ?>
                </h1>
                <span style="background: <?php echo htmlspecialchars($facultyData['color_code'] ?? '#e3f2fd'); ?>; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.9rem; font-weight: 700; font-family: 'TT Interphases', Arial, sans-serif; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                    <?php echo htmlspecialchars($facultyData['department_code'] ?? 'N/A'); ?>
                </span>
            </div>
            
            <!-- Position and Department -->
            <p style="margin: 0; color: #666; font-size: 1rem; font-family: 'TT Interphases', Arial, sans-serif;">
                <?php 
                $roleId = $facultyData['role_id'] ?? 4;
                $isDepartmentDean = $facultyData['is_department_dean'] ?? 0;
                
                // Determine position: either role_id = 3 OR assigned as dean in departments table
                $position = ($roleId == 3 || $isDepartmentDean == 1) ? 'Department Dean' : 'Faculty Member';
                echo htmlspecialchars($position . ' • ' . $facultyData['department_name']); 
                ?>
            </p>
            
            <!-- Contact Information -->
            <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 5px;">
                <span style="color: #333; font-size: 0.95rem; font-family: 'TT Interphases', Arial, sans-serif;">
                    <strong>Email:</strong> <?php echo htmlspecialchars($facultyData['institutional_email']); ?>
                </span>
                <span style="color: #333; font-size: 0.95rem; font-family: 'TT Interphases', Arial, sans-serif;">
                    <strong>Mobile Number:</strong> <?php echo htmlspecialchars($facultyData['mobile_no']); ?>
                </span>
            </div>
            
            <!-- Status and Employee Number -->
            <div style="display: flex; gap: 20px; align-items: center; margin-top: 8px;">
                <span style="background: #e8f5e8; color: #4CAF50; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; font-family: 'TT Interphases', Arial, sans-serif;">
                    Active
                </span>
                <span style="color: #333; font-size: 0.95rem; font-family: 'TT Interphases', Arial, sans-serif;">
                    <strong>Employee No:</strong> <?php echo htmlspecialchars($facultyData['employee_no']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Semester Loads Section -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: #333; font-size: 1.5rem; font-family: 'TT Interphases', sans-serif;">Teaching Loads by Term</h2>
            <button onclick="addNewLoad()" style="background: #4CAF50; color: white; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease;" onmouseover="this.style.background='#45a049'" onmouseout="this.style.background='#4CAF50'">
                + Add New Load
            </button>
        </div>

        <?php if (empty($semesterLoads)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <div style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;">📚</div>
                <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.3rem;">No Teaching Loads</h3>
                <p style="margin: 0; font-size: 1rem;">This faculty member doesn't have any course loads assigned yet.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($semesterLoads as $semester => $courses): ?>
                    <div class="semester-section" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                        <!-- Semester Header -->
                        <div class="semester-header" onclick="toggleSemester('<?php echo str_replace(' ', '_', $semester); ?>')" style="background: #f8f9fa; padding: 15px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                            <div>
                                <h3 style="margin: 0; color: #333; font-size: 1.2rem; font-family: 'TT Interphases', sans-serif;">
                                    <?php echo htmlspecialchars($semester); ?> (<?php echo count($courses); ?> courses)
                                </h3>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
                                    Total Units: <?php echo array_sum(array_column($courses, 'units')); ?>
                                </p>
                            </div>
                            <span class="semester-toggle" style="font-size: 18px; color: #666; transition: transform 0.2s ease;">▼</span>
                        </div>
                        
                        <!-- Courses List -->
                        <div id="semester_<?php echo str_replace(' ', '_', $semester); ?>" class="semester-content" style="display: none; background: white;">
                            <?php foreach ($courses as $course): ?>
                                <div class="course-item" onclick="showCourseReferences(<?php echo $course['id']; ?>)" style="padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 5px 0; color: #333; font-size: 1rem; font-weight: 600;">
                                                <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>
                                            </h4>
                                            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($course['program']); ?> • <?php echo htmlspecialchars($course['year_level']); ?> • <?php echo htmlspecialchars($course['academic_year']); ?>
                                            </p>
                                        </div>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">
                                                <?php echo $course['units']; ?> units
                                            </span>
                                            <span style="color: #4CAF50; font-size: 0.8rem; font-weight: 500;">
                                                ● <?php echo htmlspecialchars($course['status']); ?>
                                            </span>
                                            <span style="color: #666; font-size: 0.9rem;">→</span>
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
<div id="courseReferencesModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; animation: fadeIn 0.3s;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <h2 style="margin: 0; color: #333; font-size: 1.5rem; font-family: 'TT Interphases', sans-serif;">Course References</h2>
            <button onclick="closeCourseReferencesModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 5px; border-radius: 50%; transition: all 0.2s ease;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='none'">&times;</button>
        </div>
        
        <div id="courseReferencesContent">
            <!-- Course references will be loaded here -->
        </div>
    </div>
</div>

<script>
// Faculty Details Page JavaScript

function goBack() {
    window.location.href = 'content.php?page=academic-management';
}

function toggleSemester(semesterId) {
    const content = document.getElementById('semester_' + semesterId);
    const toggle = document.querySelector(`[onclick="toggleSemester('${semesterId}')"] .semester-toggle`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        toggle.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        toggle.style.transform = 'rotate(0deg)';
    }
}

function showCourseReferences(courseId) {
    // Sample course references data
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
    
    const course = courseReferences[courseId];
    if (!course) {
        alert('Course references not found!');
        return;
    }
    
    // Show modal
    document.getElementById('courseReferencesModal').style.display = 'flex';
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.height = '100%';
    
    // Populate modal content
    const content = `
        <div style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.2rem; font-family: 'TT Interphases', sans-serif;">
                ${course.course_code} - ${course.course_title}
            </h3>
            <p style="margin: 0; color: #666; font-size: 0.9rem;">Course References and Materials</p>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            ${course.references.map(ref => `
                <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#4CAF50'; this.style.boxShadow='0 2px 8px rgba(76, 175, 80, 0.1)'" onmouseout="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 5px 0; color: #333; font-size: 1rem; font-weight: 600;">${ref.title}</h5>
                            <p style="margin: 0; color: #666; font-size: 0.9rem;">by ${ref.author} (${ref.year})</p>
                        </div>
                        <span style="background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">${ref.type}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    document.getElementById('courseReferencesContent').innerHTML = content;
}

function closeCourseReferencesModal() {
    document.getElementById('courseReferencesModal').style.display = 'none';
    
    // Restore body scroll
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.height = '';
}

function addNewLoad() {
    // TODO: Implement add new load functionality
    alert('Add new load functionality will be implemented soon!');
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('courseReferencesModal');
    if (event.target === modal) {
        closeCourseReferencesModal();
    }
});
</script>
